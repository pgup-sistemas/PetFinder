<?php

use Efi\EfiPay;
use Efi\Exception\EfiException;

class PagamentoController
{
    private function getApi(): EfiPay
    {
        if (!class_exists(EfiPay::class)) {
            throw new Exception('SDK Efí não instalada. Execute "composer install" na raiz do projeto.');
        }

        $clientId = (string)EFI_CLIENT_ID;
        $clientSecret = (string)EFI_CLIENT_SECRET;
        $certificate = (string)EFI_CERTIFICATE_PATH;

        if ($clientId === '' || $clientSecret === '') {
            throw new Exception('Credenciais da Efí não configuradas (EFI_CLIENT_ID/EFI_CLIENT_SECRET).');
        }

        if ($certificate === '' || !file_exists($certificate)) {
            throw new Exception('Certificado da Efí não encontrado em EFI_CERTIFICATE_PATH.');
        }

        $options = [
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'certificate' => realpath($certificate),
            'pwdCertificate' => (string)EFI_CERTIFICATE_PASSWORD,
            'sandbox' => (bool)EFI_SANDBOX,
            'debug' => false,
            'cache' => true,
            'timeout' => 30,
            'responseHeaders' => false,
        ];

        return new EfiPay($options);
    }

    public function criarCobrancaPix(array $doacao, string $descricao = 'Doação PetFinder')
    {
        $pixKey = (string)EFI_PIX_KEY;
        if ($pixKey === '') {
            throw new Exception('Chave Pix não configurada (EFI_PIX_KEY).');
        }

        $valor = number_format((float)($doacao['valor'] ?? 0), 2, '.', '');

        $body = [
            'calendario' => [
                'expiracao' => 3600,
            ],
            'valor' => [
                'original' => (string)$valor,
            ],
            'chave' => $pixKey,
            'solicitacaoPagador' => $descricao,
            'infoAdicionais' => [
                [
                    'nome' => 'Doação',
                    'valor' => 'PetFinder',
                ],
                [
                    'nome' => 'ID',
                    'valor' => (string)($doacao['id'] ?? ''),
                ],
            ],
        ];

        $cpf = preg_replace('/[^0-9]/', '', (string)($doacao['cpf_doador'] ?? ''));
        $nome = trim((string)($doacao['nome_doador'] ?? ''));
        if ($cpf !== '' && strlen($cpf) === 11 && $nome !== '') {
            $body['devedor'] = [
                'cpf' => $cpf,
                'nome' => $nome,
            ];
        }

        try {
            $api = $this->getApi();
            $responsePix = $api->pixCreateImmediateCharge($params = [], $body);

            if (empty($responsePix['txid']) || empty($responsePix['loc']['id'])) {
                throw new Exception('Resposta inválida da Efí ao criar cobrança Pix.');
            }

            $paramsQr = [
                'id' => $responsePix['loc']['id'],
            ];

            $responseQr = $api->pixGenerateQRCode($paramsQr);

            if (empty($responseQr['imagemQrcode']) || empty($responseQr['qrcode'])) {
                throw new Exception('Resposta inválida da Efí ao gerar QR Code.');
            }

            return [
                'txid' => $responsePix['txid'],
                'charge' => $responsePix,
                'qrcode' => $responseQr,
            ];
        } catch (EfiException $e) {
            error_log('[PagamentoController] EfiException: ' . $e->code . ' - ' . $e->error . ' - ' . $e->errorDescription);
            throw new Exception('Erro da Efí ao gerar cobrança Pix.');
        }
    }

    public function detalharCobrancaPix(string $txid): array
    {
        $txid = trim($txid);
        if ($txid === '') {
            throw new Exception('TXID inválido.');
        }

        try {
            $api = $this->getApi();
            return $api->pixDetailCharge(['txid' => $txid]);
        } catch (EfiException $e) {
            error_log('[PagamentoController] EfiException (detail): ' . $e->code . ' - ' . $e->error . ' - ' . $e->errorDescription);
            throw new Exception('Erro da Efí ao consultar cobrança Pix.');
        }
    }

    public function sincronizarStatusDoacaoPix(int $doacaoId, string $txid)
    {
        $doacaoModel = new Doacao();
        $doacao = $doacaoModel->findById($doacaoId);
        if (empty($doacao)) {
            return null;
        }

        if (($doacao['status'] ?? '') === 'aprovada') {
            return $doacao;
        }

        $detail = $this->detalharCobrancaPix($txid);
        $statusCobranca = strtoupper((string)($detail['status'] ?? ''));

        if ($statusCobranca === 'CONCLUIDA') {
            $doacaoModel->updateStatus((int)$doacaoId, 'aprovada');
            $doacaoModel->updateGoalProgress((float)($doacao['valor'] ?? 0));
        } elseif (str_starts_with($statusCobranca, 'REMOVIDA')) {
            $doacaoModel->updateStatus((int)$doacaoId, 'cancelada', ['cancelada_em' => date('Y-m-d H:i:s')]);
        }

        return $doacaoModel->findById($doacaoId);
    }
}
