<?php

/**
 * PetFinder - Controller de Doações
 * Gerencia o fluxo de doações, resumo para dashboards e histórico do usuário.
 */
class DoacaoController
{
    private $doacaoModel;

    public function __construct()
    {
        $this->doacaoModel = new Doacao();
    }

    public function criar(array $dados)
    {
        $dados = sanitize($dados);
        $erros = $this->validarDados($dados);

        if (!empty($erros)) {
            return ['success' => false, 'errors' => $erros];
        }

        $payload = $this->converterPayload($dados);

        try {
            $doacaoId = $this->doacaoModel->create($payload);

            $metodo = strtolower((string)($payload['metodo_pagamento'] ?? ''));
            if ($metodo === 'pix') {
                $pagamentoController = new PagamentoController();

                $doacaoParaPix = $payload;
                $doacaoParaPix['id'] = $doacaoId;

                $pix = $pagamentoController->criarCobrancaPix($doacaoParaPix, 'Doação PetFinder #' . $doacaoId);

                $this->doacaoModel->updateStatus(
                    (int)$doacaoId,
                    'pendente',
                    [
                        'gateway' => 'efi',
                        'transaction_id' => (string)$pix['txid'],
                    ]
                );

                if (!isset($_SESSION['pix_doacoes']) || !is_array($_SESSION['pix_doacoes'])) {
                    $_SESSION['pix_doacoes'] = [];
                }

                $_SESSION['pix_doacoes'][(int)$doacaoId] = [
                    'txid' => (string)$pix['txid'],
                    'qrcode' => $pix['qrcode'],
                ];

                return ['success' => true, 'id' => $doacaoId, 'redirect' => '/doacao-pix?id=' . $doacaoId];
            }

            if ($metodo === 'cartao_avista') {
                $pagamentoController = new PagamentoController();
                $resp = $pagamentoController->criarLinkPagamentoDoacao((int)$doacaoId, (float)($payload['valor'] ?? 0), 'cartao_avista');
                $paymentUrl = (string)($resp['data']['payment_url'] ?? ($resp['payment_url'] ?? ''));
                $chargeId = (string)($resp['data']['charge_id'] ?? ($resp['data']['charge']['id'] ?? ''));

                $this->doacaoModel->updateStatus(
                    (int)$doacaoId,
                    'pendente',
                    [
                        'gateway' => 'efi',
                        'payment_url' => $paymentUrl !== '' ? $paymentUrl : null,
                        'efi_charge_id' => $chargeId !== '' ? $chargeId : null,
                    ]
                );

                if ($paymentUrl !== '') {
                    return ['success' => true, 'id' => $doacaoId, 'redirect' => $paymentUrl];
                }

                throw new Exception('Não foi possível gerar o link de pagamento do cartão.');
            }

            if ($metodo === 'cartao_recorrente') {
                $usuarioId = (int)(getUserId() ?? 0);
                $pagamentoController = new PagamentoController();
                $resp = $pagamentoController->criarAssinaturaCartaoDoacao($usuarioId, (int)$doacaoId, (float)($payload['valor'] ?? 0));

                $paymentUrl = (string)($resp['data']['payment_url'] ?? '');
                $subscriptionId = (string)($resp['data']['subscription_id'] ?? '');
                $chargeId = (string)($resp['data']['charge']['id'] ?? ($resp['data']['charge_id'] ?? ''));
                $planId = (int)($resp['_petfinder_plan_id'] ?? 0);

                $this->doacaoModel->updateStatus(
                    (int)$doacaoId,
                    'pendente',
                    [
                        'gateway' => 'efi',
                        'payment_url' => $paymentUrl !== '' ? $paymentUrl : null,
                        'efi_subscription_id' => $subscriptionId !== '' ? $subscriptionId : null,
                        'efi_charge_id' => $chargeId !== '' ? $chargeId : null,
                        'efi_plan_id' => $planId > 0 ? $planId : null,
                        'proxima_cobranca' => date('Y-m-d', strtotime('+30 days')),
                    ]
                );

                if ($paymentUrl !== '') {
                    return ['success' => true, 'id' => $doacaoId, 'redirect' => $paymentUrl];
                }

                throw new Exception('Não foi possível gerar o link de pagamento do cartão recorrente.');
            }

            return ['success' => true, 'id' => $doacaoId];
        } catch (Exception $e) {
            error_log('[DoacaoController] Erro ao registrar doação: ' . $e->getMessage());
            return ['success' => false, 'errors' => ['Não foi possível registrar a doação. Tente novamente.']];
        }
    }

    public function listarHistorico(int $usuarioId, int $pagina = 1)
    {
        $pagina = max(1, $pagina);
        $limite = 20;
        $offset = ($pagina - 1) * $limite;

        return $this->doacaoModel->findByUser($usuarioId, $limite, $offset);
    }

    public function resumoDashboard()
    {
        return $this->doacaoModel->getDashboardSummary();
    }

    public function mural(int $limite = 20)
    {
        return $this->doacaoModel->getMural($limite);
    }

    public function metaAtual()
    {
        return $this->doacaoModel->getCurrentGoalProgress();
    }

    private function validarDados(array $dados): array
    {
        $erros = [];

        if (empty($dados['valor']) || !is_numeric($dados['valor']) || $dados['valor'] < MIN_DONATION_AMOUNT) {
            $erros[] = 'Valor da doação inválido. O mínimo é R$ ' . number_format(MIN_DONATION_AMOUNT, 2, ',', '.');
        }

        if (empty($dados['metodo_pagamento'])) {
            $erros[] = 'Selecione um método de pagamento.';
        } else {
            $metodo = strtolower((string)$dados['metodo_pagamento']);
            $validMetodos = ['pix', 'cartao_avista', 'cartao_recorrente'];
            if (!in_array($metodo, $validMetodos, true)) {
                $erros[] = 'Método de pagamento inválido.';
            }

            if (!empty($dados['recorrente']) && $metodo !== 'cartao_recorrente') {
                $erros[] = 'Para doação mensal, use Cartão (mensal).';
            }

            if ($metodo === 'cartao_recorrente' && !isLoggedIn()) {
                $erros[] = 'Para doação mensal é necessário estar logado.';
            }
        }

        if (!empty($dados['email_doador']) && !isValidEmail($dados['email_doador'])) {
            $erros[] = 'Email informado é inválido.';
        }

        if (!empty($dados['cpf_doador']) && !$this->validarCPF($dados['cpf_doador'])) {
            $erros[] = 'CPF informado é inválido.';
        }

        if (!empty($dados['nome_doador']) && strlen($dados['nome_doador']) < 3) {
            $erros[] = 'Nome do doador deve conter pelo menos 3 caracteres.';
        }

        return $erros;
    }

    private function converterPayload(array $dados): array
    {
        $usuarioId = getUserId();
        $valor = (float)$dados['valor'];

        $metodo = strtolower((string)($dados['metodo_pagamento'] ?? 'pix'));
        $isRecorrente = !empty($dados['recorrente']) || $metodo === 'cartao_recorrente';

        $payload = [
            'usuario_id' => $usuarioId ?: null,
            'valor' => $valor,
            'tipo' => $isRecorrente ? 'mensal' : 'unica',
            'metodo_pagamento' => $metodo,
            'gateway' => $dados['gateway'] ?? 'manual',
            'nome_doador' => $dados['nome_doador'] ?? null,
            'email_doador' => $dados['email_doador'] ?? null,
            'cpf_doador' => $dados['cpf_doador'] ?? null,
            'mensagem' => $dados['mensagem'] ?? null,
            'exibir_mural' => !empty($dados['exibir_mural']) ? 1 : 0,
            'status' => 'pendente',
            'data_doacao' => date('Y-m-d H:i:s')
        ];

        return $payload;
    }

    private function validarCPF(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpf) != 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }
}

