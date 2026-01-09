<?php
require_once __DIR__ . '/../config.php';

$pageTitle = 'Redefinir Senha - PetFinder';

if (isLoggedIn()) {
    redirect('/');
}

$token = $_GET['token'] ?? '';
$errors = [];
$successMessage = '';
 $infoMessage = '';

if (empty($token)) {
    $errors[] = 'Token inválido.';
}

$usuarioController = new UsuarioController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Falha na validação. Recarregue a página.';
    } else {
        $tokenPost = $_POST['token'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $confirma = $_POST['confirma_senha'] ?? '';

        if (empty($tokenPost)) {
            $errors[] = 'Token inválido.';
        } else {
            $result = $usuarioController->resetarSenha($tokenPost, $senha, $confirma);

            if (!empty($result['success'])) {
                $successMessage = 'Senha redefinida com sucesso!';
                if (!empty($result['confirmation_sent'])) {
                    $infoMessage = 'Como seu e-mail ainda não foi confirmado, enviamos automaticamente um novo link de confirmação. Verifique sua caixa de entrada (e spam).';
                } else {
                    $successMessage .= ' Você já pode fazer login.';
                }
            } elseif (!empty($result['error'])) {
                $errors[] = $result['error'];
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="logo-icon-large mb-3">🔐</div>
                        <h2 class="fw-bold mb-2">Redefinir Senha</h2>
                        <p class="text-muted">Crie uma nova senha para sua conta</p>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo sanitize($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($successMessage)): ?>
                        <div class="alert alert-success">
                            <?php echo sanitize($successMessage); ?>
                        </div>
                        <?php if (!empty($infoMessage)): ?>
                            <div class="alert alert-info">
                                <?php echo sanitize($infoMessage); ?>
                            </div>
                        <?php endif; ?>
                        <div class="d-grid">
                            <a class="btn btn-primary btn-lg" href="<?php echo BASE_URL; ?>/login/">Ir para Login</a>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="token" value="<?php echo sanitize($token); ?>">

                            <div class="mb-3">
                                <label for="senha" class="form-label">Nova senha</label>
                                <input type="password" class="form-control form-control-lg" id="senha" name="senha" required>
                                <div class="form-text">Use pelo menos 8 caracteres, com maiúsculas, minúsculas e números.</div>
                            </div>

                            <div class="mb-3">
                                <label for="confirma_senha" class="form-label">Confirmar nova senha</label>
                                <input type="password" class="form-control form-control-lg" id="confirma_senha" name="confirma_senha" required>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                Salvar nova senha
                            </button>

                            <div class="text-center mt-3">
                                <a href="<?php echo BASE_URL; ?>/login/" class="text-decoration-none">
                                    <i class="bi bi-arrow-left"></i> Voltar ao Login
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.logo-icon-large {
    font-size: 4em;
}
.card {
    border-radius: 15px;
}
.btn-lg {
    padding: 12px;
    border-radius: 8px;
    font-weight: 600;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
