<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

$tokenAuth = '';
if (isset($_SERVER['HTTP_X_WEBHOOK_TOKEN'])) {
    $tokenAuth = (string)$_SERVER['HTTP_X_WEBHOOK_TOKEN'];
} elseif (isset($_SERVER['HTTP_X_EFI_WEBHOOK_TOKEN'])) {
    $tokenAuth = (string)$_SERVER['HTTP_X_EFI_WEBHOOK_TOKEN'];
} elseif (isset($_GET['token'])) {
    $tokenAuth = (string)$_GET['token'];
}

$expectedToken = (string)envValue('EFI_BILLING_WEBHOOK_TOKEN', (string)EFI_WEBHOOK_TOKEN);
if ($expectedToken !== '' && !hash_equals($expectedToken, $tokenAuth)) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'unauthorized']);
    exit;
}

$notificationToken = '';

if (isset($_POST['notification'])) {
    $notificationToken = (string)$_POST['notification'];
} else {
    $raw = file_get_contents('php://input');
    $data = json_decode((string)$raw, true);
    if (is_array($data) && isset($data['notification'])) {
        $notificationToken = (string)$data['notification'];
    }
}

$notificationToken = trim($notificationToken);
if ($notificationToken === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'missing_notification_token']);
    exit;
}

try {
    $pagamentoController = new PagamentoController();

    $efi = $pagamentoController->getApi();

    $chargeNotification = $efi->getNotification(['token' => $notificationToken], []);
    $dataList = $chargeNotification['data'] ?? [];

    if (!is_array($dataList) || empty($dataList)) {
        echo json_encode(['ok' => true, 'status' => 'no_data']);
        exit;
    }

    $ultimo = $dataList[count($dataList) - 1];
    $statusAtual = strtoupper((string)($ultimo['status']['current'] ?? ''));

    $identifiers = (array)($ultimo['identifiers'] ?? []);
    $chargeId = $identifiers['charge_id'] ?? null;
    $subscriptionId = $identifiers['subscription_id'] ?? null;

    $parceiroPagamentoModel = new ParceiroPagamento();
    $doacaoModel = new Doacao();

    $pagamento = null;
    if (!empty($chargeId)) {
        $pagamento = $parceiroPagamentoModel->findByEfiChargeId((string)$chargeId);
    }
    if (!$pagamento && !empty($subscriptionId)) {
        $pagamento = $parceiroPagamentoModel->findLastBySubscriptionId((string)$subscriptionId);
    }

    $doacao = null;
    if (!$pagamento && !empty($chargeId)) {
        $doacao = $doacaoModel->findByEfiChargeId((string)$chargeId);
    }
    if (!$pagamento && !$doacao && !empty($subscriptionId)) {
        $doacao = $doacaoModel->findLastBySubscriptionId((string)$subscriptionId);
    }

    if (empty($pagamento) && empty($doacao)) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'transaction_not_found']);
        exit;
    }

    $novoStatus = null;
    if (in_array($statusAtual, ['PAID', 'SETTLED'], true)) {
        $novoStatus = 'aprovado';
    } elseif (in_array($statusAtual, ['UNPAID', 'CANCELED', 'CANCELLED', 'REFUSED', 'REJECTED'], true)) {
        $novoStatus = 'recusado';
    }

    if ($novoStatus && !empty($pagamento)) {
        $update = [];
        if (!empty($chargeId)) {
            $update['efi_charge_id'] = (string)$chargeId;
        }
        if (!empty($subscriptionId)) {
            $update['efi_subscription_id'] = (string)$subscriptionId;
        }

        if (($pagamento['status'] ?? '') !== $novoStatus) {
            $update['status'] = $novoStatus;
            if ($novoStatus === 'aprovado') {
                $update['aprovado_em'] = date('Y-m-d H:i:s');
            } elseif ($novoStatus === 'recusado') {
                $update['recusado_em'] = date('Y-m-d H:i:s');
            }
        }

        if (!empty($update)) {
            $parceiroPagamentoModel->update((int)$pagamento['id'], $update);
        }

        if ($novoStatus === 'aprovado') {
            $assinaturaModel = new ParceiroAssinatura();
            $perfilModel = new ParceiroPerfil();

            $usuarioId = (int)($pagamento['usuario_id'] ?? 0);
            $periodicidade = (string)($pagamento['periodicidade'] ?? 'mensal');
            $pagoAte = $periodicidade === 'anual'
                ? date('Y-m-d', strtotime('+1 year'))
                : date('Y-m-d', strtotime('+30 days'));

            $assinaturaUpdate = [
                'status' => 'ativa',
                'ultimo_pagamento_em' => date('Y-m-d H:i:s'),
                'pago_ate' => $pagoAte,
                'proxima_cobranca' => $pagoAte,
                'metodo_pagamento' => 'gateway',
            ];
            if (!empty($subscriptionId)) {
                $assinaturaUpdate['efi_subscription_id'] = (int)$subscriptionId;
            }
            $assinaturaModel->updateForUser($usuarioId, $assinaturaUpdate);

            $perfilModel->publishForUser($usuarioId, true);
            $perfilModel->setHighlightForUser($usuarioId, ($pagamento['plano'] ?? '') === 'destaque');
        }
    }

    if ($novoStatus && !empty($doacao)) {
        $update = [];
        if (!empty($chargeId)) {
            $update['efi_charge_id'] = (string)$chargeId;
        }
        if (!empty($subscriptionId)) {
            $update['efi_subscription_id'] = (string)$subscriptionId;
        }

        if ($novoStatus === 'aprovado') {
            if (($doacao['status'] ?? '') !== 'aprovada') {
                $update['status'] = 'aprovada';
                $update['ultimo_pagamento_em'] = date('Y-m-d H:i:s');
            }
        } elseif ($novoStatus === 'recusado') {
            if (($doacao['status'] ?? '') !== 'cancelada') {
                $update['status'] = 'cancelada';
                $update['cancelada_em'] = date('Y-m-d H:i:s');
            }
        }

        if (!empty($update)) {
            $doacaoModel->update((int)$doacao['id'], $update);
        }

        if (($update['status'] ?? '') === 'aprovada') {
            $doacaoAtual = $doacaoModel->findById((int)$doacao['id']);
            if (!empty($doacaoAtual)) {
                $doacaoModel->updateGoalProgress((float)($doacaoAtual['valor'] ?? 0));
            }
        }
    }

    echo json_encode([
        'ok' => true,
        'status' => $statusAtual,
        'mapped_status' => $novoStatus,
        'charge_id' => $chargeId,
        'subscription_id' => $subscriptionId,
        'pagamento_id' => $pagamento['id'] ?? null,
        'doacao_id' => $doacao['id'] ?? null,
    ]);
    exit;
} catch (Exception $e) {
    error_log('[efi-billing-notification] Falha: ' . $e->getMessage());
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'notification_failed']);
    exit;
}
