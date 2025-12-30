<?php
require_once __DIR__ . '/../config.php';

$pageTitle = 'Pagamento via Pix - PetFinder';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$doacaoModel = new Doacao();
$doacao = $id > 0 ? $doacaoModel->findById($id) : null;

if (empty($doacao)) {
    http_response_code(404);
    die('Doação não encontrada.');
}

$usuarioId = getUserId();
$sessionAllowed = !empty($_SESSION['pix_doacoes'][$id]);
if (!empty($doacao['usuario_id'])) {
    if ((int)$doacao['usuario_id'] !== (int)$usuarioId) {
        http_response_code(403);
        die('Acesso negado.');
    }
} else {
    if (!$sessionAllowed) {
        http_response_code(403);
        die('Acesso negado.');
    }
}

$pix = null;
if (!empty($_SESSION['pix_doacoes'][$id]) && is_array($_SESSION['pix_doacoes'][$id])) {
    $pix = $_SESSION['pix_doacoes'][$id];
}

if (($doacao['status'] ?? '') === 'pendente' && !empty($doacao['transaction_id'])) {
    try {
        $pagamentoController = new PagamentoController();
        $atualizada = $pagamentoController->sincronizarStatusDoacaoPix((int)$doacao['id'], (string)$doacao['transaction_id']);
        if (!empty($atualizada)) {
            $doacao = $atualizada;
        }
    } catch (Exception $e) {
        error_log('[doacao-pix] Falha ao sincronizar status Pix: ' . $e->getMessage());
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h4 fw-bold mb-3">Pagamento via Pix</h1>

                    <?php if (($doacao['status'] ?? '') === 'aprovada'): ?>
                        <div class="alert alert-success">
                            Pagamento confirmado. Obrigado por apoiar o PetFinder!
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-4">Escaneie o QR Code abaixo no seu app do banco, ou copie o código Pix “copia e cola”.</p>

                        <?php if (!empty($pix['qrcode']['imagemQrcode'])): ?>
                            <div class="text-center mb-4">
                                <img src="<?php echo sanitize($pix['qrcode']['imagemQrcode']); ?>" alt="QR Code Pix" style="max-width: 260px; width: 100%;" />
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Pix copia e cola</label>
                            <textarea class="form-control" rows="4" readonly><?php echo sanitize($pix['qrcode']['qrcode'] ?? ''); ?></textarea>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <a class="btn btn-outline-primary" href="<?php echo url('/doacao-pix?id=' . $id); ?>">Atualizar status</a>
                            <a class="btn btn-link" href="<?php echo url('/doar'); ?>">Voltar</a>
                        </div>
                    <?php endif; ?>

                    <hr class="my-4" />

                    <div class="small text-muted">
                        <div><strong>ID da doação:</strong> <?php echo (int)$doacao['id']; ?></div>
                        <div><strong>Status:</strong> <?php echo sanitize($doacao['status'] ?? ''); ?></div>
                        <?php if (!empty($doacao['transaction_id'])): ?>
                            <div><strong>TXID:</strong> <?php echo sanitize($doacao['transaction_id']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
