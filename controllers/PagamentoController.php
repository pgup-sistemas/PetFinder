<?php

use Efi\EfiPay;
use Efi\Exception\EfiException;

class PagamentoController
{
    public function getApi(): EfiPay
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

    public function criarLinkPagamentoDoacao(int $doacaoId, float $valor, string $gatewayTipo)
    {
        $valorCentavos = (int)round($valor * 100);
        if ($valorCentavos < 1) {
            throw new Exception('Valor inválido para pagamento.');
        }

        $paymentMethod = 'all';
        if ($gatewayTipo === 'cartao_avista') {
            $paymentMethod = 'credit_card';
        } elseif ($gatewayTipo === 'pix') {
            $paymentMethod = 'pix';
        }

        $metadata = [
            'custom_id' => 'DOACAO_' . (int)$doacaoId,
        ];
        if ($this->shouldSendBillingNotificationUrl()) {
            $metadata['notification_url'] = $this->getBillingNotificationUrl();
        }

        $body = [
            'items' => [
                [
                    'name' => 'Doação PetFinder',
                    'amount' => 1,
                    'value' => $valorCentavos,
                ],
            ],
            'metadata' => $metadata,
            'settings' => [
                'payment_method' => $paymentMethod,
                'request_delivery_address' => false,
                'expire_at' => $this->getBillingExpireAt(),
            ],
        ];

        try {
            $api = $this->getApi();
            return $api->createOneStepLink($params = [], $body);
        } catch (EfiException $e) {
            error_log('[PagamentoController] EfiException (billing link doacao): ' . $e->code . ' - ' . $e->error . ' - ' . $e->errorDescription);
            throw new Exception('Erro da Efí ao gerar link de pagamento.');
        }
    }

    public function criarAssinaturaCartaoDoacao(int $usuarioId, int $doacaoId, float $valorMensal)
    {
        $usuarioId = (int)$usuarioId;
        if ($usuarioId <= 0) {
            throw new Exception('Para doação recorrente é necessário estar logado.');
        }

        $api = $this->getApi();
        $notificationUrl = $this->getBillingNotificationUrl();

        $planResp = $api->createPlan($params = [], [
            'name' => 'Doação PetFinder - Mensal',
            'interval' => 1,
            'repeats' => null,
        ]);
        $planId = (int)($planResp['data']['plan_id'] ?? 0);
        if ($planId <= 0) {
            throw new Exception('Não foi possível criar o plano de assinatura de doação na Efí.');
        }

        $valorCentavos = (int)round($valorMensal * 100);
        if ($valorCentavos < 1) {
            throw new Exception('Valor inválido para assinatura.');
        }

        $metadata = [
            'custom_id' => 'DOACAO_ASSINATURA_' . (int)$doacaoId,
        ];
        if ($this->shouldSendBillingNotificationUrl()) {
            $metadata['notification_url'] = $notificationUrl;
        }

        $resp = $api->createOneStepSubscriptionLink(['id' => $planId], [
            'items' => [
                [
                    'name' => 'Doação PetFinder (mensal)',
                    'amount' => 1,
                    'value' => $valorCentavos,
                ],
            ],
            'metadata' => $metadata,
            'settings' => [
                'payment_method' => 'credit_card',
                'request_delivery_address' => false,
                'expire_at' => $this->getBillingExpireAt(),
            ],
        ]);

        $resp['_petfinder_plan_id'] = $planId;
        return $resp;
    }

    private function getBillingNotificationUrl(): string
    {
        $token = (string)envValue('EFI_BILLING_WEBHOOK_TOKEN', (string)EFI_WEBHOOK_TOKEN);
        $url = rtrim((string)BASE_URL, '/') . '/api/efi-billing-notification.php';
        if ($token !== '') {
            $url .= '?token=' . urlencode($token);
        }
        return $url;
    }

    private function shouldSendBillingNotificationUrl(): bool
    {
        $baseUrl = strtolower((string)BASE_URL);

        if (str_contains($baseUrl, 'localhost') || str_contains($baseUrl, '127.0.0.1')) {
            return false;
        }

        return true;
    }

    private function getBillingExpireAt(): string
    {
        return date('Y-m-d', strtotime('+7 days'));
    }

    public function criarLinkPagamentoParceiro(int $pagamentoId, float $valor, string $gatewayTipo)
    {
        $valorCentavos = (int)round($valor * 100);
        if ($valorCentavos < 1) {
            throw new Exception('Valor inválido para pagamento.');
        }

        $paymentMethod = 'all';
        if ($gatewayTipo === 'pix') {
            $paymentMethod = 'pix';
        } elseif ($gatewayTipo === 'cartao_avista') {
            $paymentMethod = 'credit_card';
        }

        $metadata = [
            'custom_id' => 'PARCEIRO_PAGAMENTO_' . (int)$pagamentoId,
        ];
        if ($this->shouldSendBillingNotificationUrl()) {
            $metadata['notification_url'] = $this->getBillingNotificationUrl();
        }

        $body = [
            'items' => [
                [
                    'name' => 'Assinatura Parceiro PetFinder',
                    'amount' => 1,
                    'value' => $valorCentavos,
                ],
            ],
            'metadata' => $metadata,
            'settings' => [
                'payment_method' => $paymentMethod,
                'request_delivery_address' => false,
                'expire_at' => $this->getBillingExpireAt(),
            ],
        ];

        try {
            $api = $this->getApi();
            return $api->createOneStepLink($params = [], $body);
        } catch (EfiException $e) {
            error_log('[PagamentoController] EfiException (billing link): ' . $e->code . ' - ' . $e->error . ' - ' . $e->errorDescription);
            throw new Exception('Erro da Efí ao gerar link de pagamento.');
        }
    }

    public function criarAssinaturaCartaoParceiro(int $usuarioId, int $pagamentoId, float $valorMensal, string $plano)
    {
        $assinaturaModel = new ParceiroAssinatura();
        $assinatura = $assinaturaModel->findByUserId($usuarioId);

        $api = $this->getApi();
        $notificationUrl = $this->getBillingNotificationUrl();

        $planId = (int)($assinatura['efi_plan_id'] ?? 0);
        if ($planId <= 0) {
            $planResp = $api->createPlan($params = [], [
                'name' => 'Parceiro PetFinder - ' . ($plano === 'destaque' ? 'Destaque' : 'Básico'),
                'interval' => 1,
                'repeats' => null,
            ]);
            $planId = (int)($planResp['data']['plan_id'] ?? 0);
            if ($planId <= 0) {
                throw new Exception('Não foi possível criar o plano de assinatura na Efí.');
            }
            $assinaturaModel->updateForUser($usuarioId, ['efi_plan_id' => $planId]);
        }

        $valorCentavos = (int)round($valorMensal * 100);
        $metadata = [
            'custom_id' => 'PARCEIRO_ASSINATURA_' . (int)$usuarioId,
        ];
        if ($this->shouldSendBillingNotificationUrl()) {
            $metadata['notification_url'] = $notificationUrl;
        }
        $resp = $api->createOneStepSubscriptionLink(['id' => $planId], [
            'items' => [
                [
                    'name' => 'Assinatura Parceiro PetFinder',
                    'amount' => 1,
                    'value' => $valorCentavos,
                ],
            ],
            'metadata' => $metadata,
            'settings' => [
                'payment_method' => 'credit_card',
                'request_delivery_address' => false,
                'expire_at' => $this->getBillingExpireAt(),
            ],
        ]);

        return $resp;
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

    public function criarCobrancaPixParceiro(int $pagamentoId, float $valor, string $descricao)
    {
        $pixKey = (string)EFI_PIX_KEY;
        if ($pixKey === '') {
            throw new Exception('Chave Pix não configurada (EFI_PIX_KEY).');
        }

        $valorFormatado = number_format((float)$valor, 2, '.', '');

        $body = [
            'calendario' => [
                'expiracao' => 3600,
            ],
            'valor' => [
                'original' => (string)$valorFormatado,
            ],
            'chave' => $pixKey,
            'solicitacaoPagador' => $descricao,
            'infoAdicionais' => [
                [
                    'nome' => 'Tipo',
                    'valor' => 'Parceiro',
                ],
                [
                    'nome' => 'PagamentoID',
                    'valor' => (string)$pagamentoId,
                ],
            ],
        ];

        try {
            $api = $this->getApi();
            $responsePix = $api->pixCreateImmediateCharge($params = [], $body);

            if (empty($responsePix['txid']) || empty($responsePix['loc']['id'])) {
                throw new Exception('Resposta inválida da Efí ao criar cobrança Pix (parceiro).');
            }

            $paramsQr = [
                'id' => $responsePix['loc']['id'],
            ];

            $responseQr = $api->pixGenerateQRCode($paramsQr);
            if (empty($responseQr['imagemQrcode']) || empty($responseQr['qrcode'])) {
                throw new Exception('Resposta inválida da Efí ao gerar QR Code (parceiro).');
            }

            return [
                'txid' => $responsePix['txid'],
                'charge' => $responsePix,
                'qrcode' => $responseQr,
            ];
        } catch (EfiException $e) {
            error_log('[PagamentoController] EfiException (parceiro): ' . $e->code . ' - ' . $e->error . ' - ' . $e->errorDescription);
            throw new Exception('Erro da Efí ao gerar cobrança Pix (parceiro).');
        }
    }

    public function cancelarAssinaturaGateway(string $subscriptionId): bool
    {
        if (empty($subscriptionId)) {
            throw new Exception('ID da assinatura não fornecido.');
        }

        try {
            $api = $this->getApi();
            
            $params = ['id' => $subscriptionId];
            $response = $api->cancelSubscription($params);
            
            // Verificar se o cancelamento foi bem-sucedido
            if (isset($response['data'])) {
                $status = strtolower((string)($response['data']['status'] ?? ''));
                return in_array($status, ['canceled', 'cancelled', 'inactive']);
            }
            
            return false;
            
        } catch (EfiException $e) {
            error_log('[PagamentoController] EfiException (cancel subscription): ' . $e->code . ' - ' . $e->error . ' - ' . $e->errorDescription);
            
            // Se já estiver cancelada, considerar sucesso
            if (in_array($e->code, [400, 404]) && str_contains(strtolower($e->error), 'cancel')) {
                return true;
            }
            
            throw new Exception('Erro ao cancelar assinatura no gateway.');
        } catch (Exception $e) {
            error_log('[PagamentoController] Erro ao cancelar assinatura: ' . $e->getMessage());
            throw new Exception('Erro ao processar cancelamento no gateway.');
        }
    }

    public function sincronizarStatusParceiroPix(int $pagamentoId, string $txid)
    {
        $txid = trim($txid);
        if ($txid === '') {
            throw new Exception('TXID inválido.');
        }

        $pagamentoModel = new ParceiroPagamento();
        $pagamento = $pagamentoModel->findById($pagamentoId);
        if (empty($pagamento)) {
            return null;
        }

        if (($pagamento['status'] ?? '') === 'aprovado') {
            return $pagamento;
        }

        $detail = $this->detalharCobrancaPix($txid);
        $statusCobranca = strtoupper((string)($detail['status'] ?? ''));

        if ($statusCobranca === 'CONCLUIDA') {
            $usuarioId = (int)($pagamento['usuario_id'] ?? 0);

            $pagamentoModel->update((int)$pagamentoId, [
                'status' => 'aprovado',
                'aprovado_em' => date('Y-m-d H:i:s'),
            ]);

            $assinaturaModel = new ParceiroAssinatura();
            $perfilModel = new ParceiroPerfil();

            $periodicidade = (string)($pagamento['periodicidade'] ?? 'mensal');
            $pagoAte = $periodicidade === 'anual'
                ? date('Y-m-d', strtotime('+1 year'))
                : date('Y-m-d', strtotime('+30 days'));
            $assinaturaModel->updateForUser($usuarioId, [
                'status' => 'ativa',
                'ultimo_pagamento_em' => date('Y-m-d H:i:s'),
                'pago_ate' => $pagoAte,
                'proxima_cobranca' => $pagoAte,
                'metodo_pagamento' => 'gateway',
            ]);

            $perfilModel->publishForUser($usuarioId, true);
            $perfilModel->setHighlightForUser($usuarioId, ($pagamento['plano'] ?? '') === 'destaque');

            $adminEmail = (string)envValue('DEFAULT_ADMIN_EMAIL', 'admin@petfinder.com');
            if ($adminEmail !== '') {
                $subject = 'Pagamento PIX confirmado (Parceiro) - PetFinder';
                $message = "<html><body style='font-family: Arial, sans-serif;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                        <h2 style='color:#00CC66;'>Pagamento confirmado</h2>
                        <p>Um pagamento de parceiro foi confirmado via PIX (Efí).</p>
                        <p><strong>Pagamento ID:</strong> " . (int)$pagamentoId . "</p>
                        <p><strong>TXID:</strong> " . sanitize($txid) . "</p>
                        <p><a href='" . BASE_URL . "/admin/parceiros?tab=pagamentos'>Abrir Admin Parceiros</a></p>
                    </div>
                </body></html>";
                sendEmail($adminEmail, $subject, $message);
            }
        }

        return $pagamentoModel->findById($pagamentoId);
    }
}
