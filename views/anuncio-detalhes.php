<?php
require_once __DIR__ . '/../config.php';

$pageTitle = 'Detalhes do Anúncio - PetFinder';

$anuncioController = new AnuncioController();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    setFlashMessage('Anúncio não encontrado.', MSG_WARNING);
    redirect('/busca.php');
}

$anuncio = $anuncioController->getDetalhes($id);

if (!$anuncio) {
    setFlashMessage('Anúncio não encontrado ou removido.', MSG_WARNING);
    redirect('/busca.php');
}

$anuncioController->registrarVisualizacao($id);

$isOwner = isLoggedIn() && getUserId() == $anuncio['usuario_id'];
$favoritoController = new FavoritoController();
$isFavorited = $favoritoController->isFavorited($anuncio['id']);

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Início</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/busca.php">Busca</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo sanitize($anuncio['nome_pet'] ?: 'Pet ' . ucfirst($anuncio['especie'])); ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-7">
            <div id="carouselFotos" class="carousel slide shadow-sm rounded" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php if (!empty($anuncio['fotos'])): ?>
                        <?php foreach ($anuncio['fotos'] as $index => $foto): ?>
                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                <img src="<?php echo BASE_URL; ?>/uploads/anuncios/<?php echo sanitize($foto['nome_arquivo']); ?>" class="d-block w-100 rounded" alt="Foto do pet">
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="carousel-item active">
                            <div class="d-flex align-items-center justify-content-center bg-light" style="height: 360px;">
                                <div class="text-center text-muted">
                                    <i class="bi bi-camera" style="font-size: 3rem;"></i>
                                    <p class="mt-3 mb-0">Este anúncio não possui fotos.</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($anuncio['fotos']) && count($anuncio['fotos']) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselFotos" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Anterior</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselFotos" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Próxima</span>
                    </button>
                <?php endif; ?>
            </div>

            <div class="card shadow-sm border-0 mt-4">
                <div class="card-body p-4">
                    <h2 class="h4 fw-bold mb-3"><?php echo sanitize($anuncio['nome_pet'] ?: 'Pet ' . ucfirst($anuncio['especie'])); ?></h2>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-<?php echo $anuncio['tipo'] === 'perdido' ? 'danger' : 'success'; ?>">
                            <?php echo $anuncio['tipo'] === 'perdido' ? '🔴 Perdido' : '🟢 Encontrado'; ?>
                        </span>
                        <span class="badge bg-light text-dark"><i class="bi bi-geo-alt me-1"></i><?php echo sanitize($anuncio['bairro']); ?> - <?php echo sanitize($anuncio['cidade']); ?></span>
                        <span class="badge bg-light text-dark"><?php echo ucfirst($anuncio['especie']); ?></span>
                        <span class="badge bg-light text-dark"><?php echo ucfirst($anuncio['tamanho']); ?></span>
                    </div>

                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                        <p class="text-muted mb-0">
                            <i class="bi bi-clock me-2"></i>Publicado <?php echo timeAgo($anuncio['data_publicacao']); ?> • Visualizações: <?php echo (int)$anuncio['visualizacoes']; ?>
                        </p>
                        <div>
                            <?php if (isLoggedIn()): ?>
                                <form method="POST" action="<?php echo BASE_URL; ?>/favorito_toggle.php" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="anuncio_id" value="<?php echo $anuncio['id']; ?>">
                                    <input type="hidden" name="return_to" value="<?php echo '/anuncio.php?id=' . $anuncio['id']; ?>">
                                    <button type="submit" class="btn btn-sm <?php echo $isFavorited ? 'btn-warning' : 'btn-outline-warning'; ?>">
                                        <i class="bi <?php echo $isFavorited ? 'bi-star-fill' : 'bi-star'; ?> me-1"></i>
                                        <?php echo $isFavorited ? 'Remover dos Favoritos' : 'Salvar nos Favoritos'; ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="<?php echo BASE_URL; ?>/login.php?redirect=/anuncio.php?id=<?php echo $anuncio['id']; ?>" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-star me-1"></i>Entre para favoritar
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h5 class="fw-bold">Descrição</h5>
                    <p class="mb-4"><?php echo nl2br(sanitize($anuncio['descricao'] ?? '')); ?></p>

                    <div class="row g-3">
                        <?php if (!empty($anuncio['raca'])): ?>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-label">Raça</span>
                                    <span class="info-value"><?php echo sanitize($anuncio['raca']); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($anuncio['cor'])): ?>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-label">Cor</span>
                                    <span class="info-value"><?php echo sanitize($anuncio['cor']); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-label">Data do ocorrido</span>
                                <span class="info-value"><?php echo formatDateBR($anuncio['data_ocorrido']); ?></span>
                            </div>
                        </div>
                        <?php if (!empty($anuncio['ponto_referencia'])): ?>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-label">Ponto de referência</span>
                                    <span class="info-value"><?php echo sanitize($anuncio['ponto_referencia']); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($anuncio['recompensa'])): ?>
                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-label">Recompensa</span>
                                    <span class="info-value text-success fw-bold"><?php echo sanitize($anuncio['recompensa']); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Localização</h5>
                    <p class="mb-2"><i class="bi bi-geo me-2"></i><?php echo sanitize($anuncio['endereco_completo']); ?></p>
                    <p class="text-muted mb-0">Bairro <?php echo sanitize($anuncio['bairro']); ?> • <?php echo sanitize($anuncio['cidade']); ?> - <?php echo sanitize($anuncio['estado']); ?></p>
                </div>
                <?php if (!empty($anuncio['latitude']) && !empty($anuncio['longitude'])): ?>
                    <div class="ratio ratio-4x3">
                        <iframe src="https://www.google.com/maps?q=<?php echo $anuncio['latitude']; ?>,<?php echo $anuncio['longitude']; ?>&output=embed" allowfullscreen loading="lazy"></iframe>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Entre em contato</h5>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0 d-flex align-items-center justify-content-between">
                            <div>
                                <strong>WhatsApp</strong>
                                <p class="mb-0 text-muted"><?php echo formatPhone($anuncio['whatsapp']); ?></p>
                            </div>
                            <a class="btn btn-success" href="https://wa.me/55<?php echo preg_replace('/[^0-9]/', '', $anuncio['whatsapp']); ?>" target="_blank"><i class="bi bi-whatsapp"></i></a>
                        </div>
                        <?php if (!empty($anuncio['telefone_contato'])): ?>
                            <div class="list-group-item px-0">
                                <strong>Telefone</strong>
                                <p class="mb-0 text-muted"><?php echo formatPhone($anuncio['telefone_contato']); ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($anuncio['email_contato'])): ?>
                            <div class="list-group-item px-0">
                                <strong>Email</strong>
                                <p class="mb-0 text-muted"><?php echo sanitize($anuncio['email_contato']); ?></p>
                                <a class="btn btn-outline-primary btn-sm mt-2" href="mailto:<?php echo sanitize($anuncio['email_contato']); ?>">Enviar email</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($isOwner): ?>
                        <hr>
                        <div class="d-grid gap-2">
                            <a href="<?php echo BASE_URL; ?>/editar-anuncio.php?id=<?php echo $anuncio['id']; ?>" class="btn btn-outline-primary"><i class="bi bi-pencil me-1"></i>Editar anúncio</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.info-box {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 12px 16px;
}

.info-label {
    display: block;
    font-size: 0.75rem;
    text-transform: uppercase;
    color: #6c757d;
    letter-spacing: 0.05em;
}

.info-value {
    font-weight: 600;
    color: #333;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
