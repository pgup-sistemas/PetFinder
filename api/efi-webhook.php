<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

$token = '';

if (isset($_SERVER['HTTP_X_WEBHOOK_TOKEN'])) {
    $token = (string)$_SERVER['HTTP_X_WEBHOOK_TOKEN'];
} elseif (isset($_SERVER['HTTP_X_EFI_WEBHOOK_TOKEN'])) {
    $token = (string)$_SERVER['HTTP_X_EFI_WEBHOOK_TOKEN'];
} elseif (isset($_GET['token'])) {
    $token = (string)$_GET['token'];
}

if ((string)EFI_WEBHOOK_TOKEN !== '' && !hash_equals((string)EFI_WEBHOOK_TOKEN, $token)) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'unauthorized']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode((string)$raw, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'invalid_json']);
    exit;
}

$txid = '';

if (!empty($data['pix'][0]['txid'])) {
    $txid = (string)$data['pix'][0]['txid'];
} elseif (!empty($data['txid'])) {
    $txid = (string)$data['txid'];
} elseif (!empty($data['cob']['txid'])) {
    $txid = (string)$data['cob']['txid'];
}

if ($txid === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'missing_txid']);
    exit;
}

$doacaoModel = new Doacao();
$doacao = $doacaoModel->findByTransactionId($txid);

try {
    $pagamentoController = new PagamentoController();
    if (!empty($doacao)) {
        if (($doacao['status'] ?? '') === 'aprovada') {
            echo json_encode(['ok' => true, 'status' => 'already_approved']);
            exit;
        }

        $atualizada = $pagamentoController->sincronizarStatusDoacaoPix((int)$doacao['id'], $txid);
        echo json_encode(['ok' => true, 'tipo' => 'doacao', 'status' => $atualizada['status'] ?? 'pendente']);
        exit;
    }

    $parceiroPagamentoModel = new ParceiroPagamento();
    $pagamentoParceiro = $parceiroPagamentoModel->findByReferencia($txid);
    if (empty($pagamentoParceiro)) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'transaction_not_found']);
        exit;
    }

    if (($pagamentoParceiro['status'] ?? '') === 'aprovado') {
        echo json_encode(['ok' => true, 'tipo' => 'parceiro', 'status' => 'already_approved']);
        exit;
    }

    $atualizada = $pagamentoController->sincronizarStatusParceiroPix((int)$pagamentoParceiro['id'], $txid);
    echo json_encode(['ok' => true, 'tipo' => 'parceiro', 'status' => $atualizada['status'] ?? 'pendente']);
    exit;
} catch (Exception $e) {
    error_log('[efi-webhook] Falha ao sincronizar cobrança Pix: ' . $e->getMessage());
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'sync_failed']);
    exit;
}
