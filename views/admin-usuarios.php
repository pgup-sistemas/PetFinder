<?php
require_once __DIR__ . '/../config.php';

requireAdmin();

$pageTitle = 'Admin - Usuários - PetFinder';

$usuarioModel = new Usuario();

$search = isset($_GET['q']) ? trim((string)$_GET['q']) : '';

$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$pagina = max(1, $pagina);
$limite = 20;
$offset = ($pagina - 1) * $limite;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('Falha na validação do formulário. Recarregue a página.', MSG_ERROR);
        redirect('/admin/usuarios');
    }

    $acao = (string)($_POST['acao'] ?? '');
    $id = (int)($_POST['id'] ?? 0);

    if ($id <= 0) {
        setFlashMessage('Usuário inválido.', MSG_ERROR);
        redirect('/admin/usuarios');
    }

    $alvo = $usuarioModel->findById($id);
    if (!$alvo) {
        setFlashMessage('Usuário não encontrado.', MSG_ERROR);
        redirect('/admin/usuarios');
    }

    if ($acao === 'toggle_active') {
        $usuarioModel->setActive($id, !(bool)$alvo['ativo']);
        setFlashMessage('Status do usuário atualizado.', MSG_SUCCESS);
        redirect('/admin/usuarios');
    }

    if ($acao === 'toggle_admin') {
        $currentUserId = (int)(getUserId() ?? 0);
        if ($currentUserId === $id) {
            setFlashMessage('Você não pode remover seu próprio acesso admin.', MSG_ERROR);
            redirect('/admin/usuarios');
        }

        $usuarioModel->setAdmin($id, !(bool)$alvo['is_admin']);
        setFlashMessage('Permissão admin atualizada.', MSG_SUCCESS);
        redirect('/admin/usuarios');
    }

    setFlashMessage('Ação inválida.', MSG_ERROR);
    redirect('/admin/usuarios');
}

$total = $usuarioModel->countAll($search);
$usuarios = $usuarioModel->findAll($limite, $offset, $search);
$totalPaginas = (int)ceil($total / $limite);

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Admin · Usuários</h1>
            <p class="text-muted mb-0">Listagem e ações básicas.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo BASE_URL; ?>/admin" class="btn btn-outline-secondary">Voltar</a>
            <a href="<?php echo BASE_URL; ?>/admin/financeiro" class="btn btn-primary">Financeiro</a>
        </div>
    </div>

    <form method="GET" action="" class="row g-2 mb-3">
        <div class="col-md-8">
            <input type="text" name="q" class="form-control" placeholder="Buscar por nome ou email" value="<?php echo sanitize($search); ?>">
        </div>
        <div class="col-md-4 d-grid">
            <button class="btn btn-outline-primary" type="submit">Buscar</button>
        </div>
    </form>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <?php if (empty($usuarios)): ?>
                <p class="text-muted mb-0">Nenhum usuário encontrado.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Ativo</th>
                                <th>Admin</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td><?php echo (int)$u['id']; ?></td>
                                    <td><?php echo sanitize($u['nome'] ?? ''); ?></td>
                                    <td><?php echo sanitize($u['email'] ?? ''); ?></td>
                                    <td>
                                        <span class="badge <?php echo !empty($u['ativo']) ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo !empty($u['ativo']) ? 'Sim' : 'Não'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo !empty($u['is_admin']) ? 'bg-primary' : 'bg-light text-dark'; ?>">
                                            <?php echo !empty($u['is_admin']) ? 'Sim' : 'Não'; ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <form method="POST" action="" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                                            <input type="hidden" name="acao" value="toggle_active">
                                            <button class="btn btn-sm btn-outline-secondary" type="submit">
                                                <?php echo !empty($u['ativo']) ? 'Bloquear' : 'Ativar'; ?>
                                            </button>
                                        </form>

                                        <form method="POST" action="" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                                            <input type="hidden" name="acao" value="toggle_admin">
                                            <button class="btn btn-sm btn-outline-primary" type="submit">
                                                <?php echo !empty($u['is_admin']) ? 'Remover admin' : 'Tornar admin'; ?>
                                            </button>
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
                        $qs = $search !== '' ? '&q=' . urlencode($search) : '';
                    ?>
                    <nav class="mt-3" aria-label="Paginação">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?php echo $pagina <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo BASE_URL . '/admin/usuarios?pagina=' . $prev . $qs; ?>">Anterior</a>
                            </li>
                            <li class="page-item disabled"><span class="page-link"><?php echo $pagina; ?> / <?php echo $totalPaginas; ?></span></li>
                            <li class="page-item <?php echo $pagina >= $totalPaginas ? 'disabled' : ''; ?>">
                                <a class="page-link" href="<?php echo BASE_URL . '/admin/usuarios?pagina=' . $next . $qs; ?>">Próxima</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
