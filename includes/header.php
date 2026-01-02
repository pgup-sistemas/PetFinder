<?php
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/config.php';
}

$pageTitle = $pageTitle ?? 'PetFinder';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitize($pageTitle); ?></title>

    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Ctext y='50' font-size='48'%3E%F0%9F%90%BE%3C/text%3E%3C/svg%3E">

    <?php if (!empty($metaOgTitle) || !empty($metaOgDescription) || !empty($metaOgImage) || !empty($metaOgUrl)): ?>
        <meta property="og:type" content="website">
        <?php if (!empty($metaOgTitle)): ?><meta property="og:title" content="<?php echo sanitize($metaOgTitle); ?>"><?php endif; ?>
        <?php if (!empty($metaOgDescription)): ?><meta property="og:description" content="<?php echo sanitize($metaOgDescription); ?>"><?php endif; ?>
        <?php if (!empty($metaOgUrl)): ?><meta property="og:url" content="<?php echo sanitize($metaOgUrl); ?>"><?php endif; ?>
        <?php if (!empty($metaOgImage)): ?><meta property="og:image" content="<?php echo sanitize($metaOgImage); ?>"><?php endif; ?>
        <meta name="twitter:card" content="summary_large_image">
        <?php if (!empty($metaOgTitle)): ?><meta name="twitter:title" content="<?php echo sanitize($metaOgTitle); ?>"><?php endif; ?>
        <?php if (!empty($metaOgDescription)): ?><meta name="twitter:description" content="<?php echo sanitize($metaOgDescription); ?>"><?php endif; ?>
        <?php if (!empty($metaOgImage)): ?><meta name="twitter:image" content="<?php echo sanitize($metaOgImage); ?>"><?php endif; ?>
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/css/style.css">
</head>
<body>
    <header class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?php echo BASE_URL; ?>">
                <span class="logo-icon">🐾</span> <span class="logo-text">PetFinder</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <nav class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/">Início</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/busca">Buscar Pets</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/novo-anuncio">Publicar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/doar">Doar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/parceiros">Parceiros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/ajuda">Ajuda</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <?php
                                    $userFullName = (string)($_SESSION['user_name'] ?? 'Usuário');
                                    $userFirstName = trim(strtok($userFullName, ' '));
                                    if ($userFirstName === '') {
                                        $userFirstName = 'Usuário';
                                    }
                                ?>
                                <i class="bi bi-person-circle"></i>
                                <span class="d-lg-none">Conta</span>
                                <span class="d-none d-lg-inline">Olá, <?php echo sanitize($userFirstName); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php if (isAdmin()): ?>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin">Painel Admin</a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/parceiros">Admin Parceiros</a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/usuarios">Admin Usuários</a></li>
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/financeiro">Admin Financeiro</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/parceiros">Ver Parceiros</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/parceiro/painel">Painel do Parceiro</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/parceiros/inscricao">Inscrição de Parceiro</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/perfil">Meu Perfil</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/alertas">Meus Alertas</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/meus-anuncios">Meus Anúncios</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/favoritos">Favoritos</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/logout">Sair</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn btn-outline-primary ms-lg-3" href="<?php echo BASE_URL; ?>/login">Entrar</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-success ms-lg-2 mt-2 mt-lg-0" href="<?php echo BASE_URL; ?>/cadastro">Criar Conta</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container mt-3">
            <?php displayFlashMessage(); ?>
        </div>

