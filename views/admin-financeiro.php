<?php
require_once __DIR__ . '/../config.php';

requireAdmin();

$pageTitle = 'Admin - Financeiro - PetFinder';

$doacaoController = new DoacaoController();
$doacaoModel = new Doacao();

$metaAtual = $doacaoController->metaAtual();

$status = isset($_GET['status']) ? trim((string)$_GET['status']) : '';
$allowedStatus = ['', 'pendente', 'aprovada', 'cancelada', 'estornada'];
if (!in_array($status, $allowedStatus, true)) {
    $status = '';
}

$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$pagina = max(1, $pagina);
$limite = 30;
$offset = ($pagina - 1) * $limite;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('Falha na validação do formulário. Recarregue a página.', MSG_ERROR);
        redirect('/admin/financeiro');
    }

    $acao = (string)($_POST['acao'] ?? '');

    if ($acao === 'update_goal') {
        $metaId = (int)($_POST['meta_id'] ?? 0);
        $valorMeta = (float)($_POST['valor_meta'] ?? 0);
        $custosServidor = (float)($_POST['custos_servidor'] ?? 0);
        $custosManutencao = (float)($_POST['custos_manutencao'] ?? 0);
        $custosOutros = (float)($_POST['custos_outros'] ?? 0);
        $descricao = sanitize($_POST['descricao'] ?? '');

        if ($metaId > 0) {
            $db = getDB();
            $db->update(
                'metas_financeiras',
                [
                    'valor_meta' => $valorMeta,
                    'custos_servidor' => $custosServidor,
                    'custos_manutencao' => $custosManutencao,
                    'custos_outros' => $custosOutros,
                    'descricao' => $descricao,
                ],
                'id = ?',
                [$metaId]
            );
            setFlashMessage('Meta financeira atualizada.', MSG_SUCCESS);
        }

        redirect('/admin/financeiro');
    }

    if ($acao === 'set_donation_status') {
        $id = (int)($_POST['id'] ?? 0);
        $novoStatus = (string)($_POST['status'] ?? '');
        if ($id > 0 && in_array($novoStatus, ['pendente', 'aprovada', 'cancelada', 'estornada'], true)) {
            $doacao = $doacaoModel->findById($id);
            if ($doacao) {
                $doacaoModel->updateStatus($id, $novoStatus);
                setFlashMessage('Status da doação atualizado.', MSG_SUCCESS);
            }
        }
        redirect('/admin/financeiro');
    }

    setFlashMessage('Ação inválida.', MSG_ERROR);
    redirect('/admin/financeiro');
}

$total = $doacaoModel->countAll($status !== '' ? $status : null);
$doacoes = $doacaoModel->findAll($limite, $offset, $status !== '' ? $status : null);
$totalPaginas = (int)ceil($total / $limite);

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Admin · Financeiro</h1>
            <p class="text-muted mb-0">Meta mensal e doações.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo BASE_URL; ?>/admin" class="btn btn-outline-secondary">Voltar</a>
            <a href="<?php echo BASE_URL; ?>/admin/usuarios" class="btn btn-outline-primary">Usuários</a>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h2 class="h6 fw-bold mb-3">Meta financeira atual</h2>

            <?php if (empty($metaAtual)): ?>
                <p class="text-muted mb-0">Nenhuma meta ativa cadastrada.</p>
            <?php else: ?>
                <form method="POST" action="" class="row g-3">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="acao" value="update_goal">
                    <input type="hidden" name="meta_id" value="<?php echo (int)($metaAtual['id'] ?? 0); ?>">

                    <div class="col-md-3">
                        <label class="form-label">Valor meta</label>
                        <input type="number" step="0.01" name="valor_meta" class="form-control" value="<?php echo sanitize((string)($metaAtual['valor_meta'] ?? 0)); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Custos servidor</label>
                        <input type="number" step="0.01" name="custos_servidor" class="form-control" value="<?php echo sanitize((string)($metaAtual['custos_servidor'] ?? 0)); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Custos manutenção</label>
                        <input type="number" step="0.01" name="custos_manutencao" class="form-control" value="<?php echo sanitize((string)($metaAtual['custos_manutencao'] ?? 0)); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Custos outros</label>
                        <input type="number" step="0.01" name="custos_outros" class="form-control" value="<?php echo sanitize((string)($metaAtual['custos_outros'] ?? 0)); ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Descrição</label>
                        <textarea name="descricao" class="form-control" rows="2"><?php echo sanitize((string)($metaAtual['descricao'] ?? '')); ?></textarea>
                    </div>

                    <div class="col-12 d-grid d-md-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Salvar meta</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h2 class="h6 fw-bold mb-0">Doações</h2>
                <form method="GET" action="" class="d-flex gap-2">
                    <select name="status" class="form-select">
                        <?php foreach ($allowedStatus as $s): ?>
                            <option value="<?php echo sanitize($s); ?>" <?php echo $s === $status ? 'selected' : ''; ?>>
                                <?php echo $s === '' ? 'Todos' : ucfirst($s); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-outline-primary" type="submit">Filtrar</button>
                </form>
            </div>

            <?php if (empty($doacoes)): ?>
                <p class="text-muted mb-0">Nenhuma doação encontrada.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Data</th>
                                <th>Valor</th>
                                <th>Doador</th>
                                <th>Método</th>
                                <th>Status</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($doacoes as $d): ?>
                                <tr>
                                    <td><?php echo (int)$d['id']; ?></td>
                                    <td><?php echo !empty($d['data_doacao']) ? date('d/m/Y H:i', strtotime($d['data_doacao'])) : '-'; ?></td>
                                    <td><?php echo formatMoney((float)($d['valor'] ?? 0)); ?></td>
                                    <td><?php echo sanitize($d['nome_doador'] ?? ''); ?></td>
                                    <td><?php echo sanitize($d['metodo_pagamento'] ?? ''); ?></td>
                                    <td><span class="badge bg-light text-dark"><?php echo sanitize($d['status'] ?? ''); ?></span></td>
                                    <td class="text-end">
                                        <form method="POST" action="" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="acao" value="set_donation_status">
                                            <input type="hidden" name="id" value="<?php echo (int)$d['id']; ?>">
                                            <select name="status" class="form-select form-select-sm d-inline" style="width: auto; display: inline-block;">
                                                <?php foreach (['pendente', 'aprovada', 'cancelada', 'estornada'] as $st): ?>
                                                    <option value="<?php echo $st; ?>" <?php echo ($d['status'] ?? '') === $st ? 'selected' : ''; ?>><?php echo ucfirst($st); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button class="btn btn-sm btn-outline-primary" type="submit">Salvar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPaginas > 1): ?>
                    <?php
                        $prev = max(1, $pagina - 1);
                        $next = min($totalPaginas, $pagina + 1);
                        $qs = $status !== '' ? '&status=' . urlencode($status) : '';
                    ?>
                    <nav class="mt-3" aria-label="Paginação">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?php echo $pagina <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo BASE_URL . '/admin/financeiro?pagina=' . $prev . $qs; ?>">Anterior</a>
                            </li>
                            <li class="page-item disabled"><span class="page-link"><?php echo $pagina; ?> / <?php echo $totalPaginas; ?></span></li>
                            <li class="page-item <?php echo $pagina >= $totalPaginas ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo BASE_URL . '/admin/financeiro?pagina=' . $next . $qs; ?>">Próxima</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
