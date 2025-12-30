<?php
require_once __DIR__ . '/../config.php';

$pageTitle = 'Meus Anúncios - PetFinder';

requireLogin();

$anuncioModel = new Anuncio();
$usuarioId = (int)getUserId();

$anuncios = $anuncioModel->findByUser($usuarioId, 100, 0);

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 fw-bold mb-0">Meus Anúncios</h1>
        <a class="btn btn-primary" href="<?php echo BASE_URL; ?>/novo-anuncio.php">
            <i class="bi bi-plus-lg"></i> Publicar
        </a>
    </div>

    <?php if (empty($anuncios)): ?>
        <div class="alert alert-info">
            Você ainda não publicou nenhum anúncio.
            <a href="<?php echo BASE_URL; ?>/novo-anuncio.php" class="alert-link">Publicar agora</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($anuncios as $anuncio): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0">
                        <?php if (!empty($anuncio['foto'])): ?>
                            <img src="<?php echo BASE_URL; ?>/uploads/anuncios/<?php echo sanitize($anuncio['foto']); ?>" class="card-img-top" alt="Foto" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center bg-light" style="height: 200px;">
                                <div class="text-center text-muted">
                                    <i class="bi bi-camera" style="font-size: 2rem;"></i>
                                    <div class="small">Sem foto</div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="badge bg-<?php echo $anuncio['tipo'] === 'perdido' ? 'danger' : 'success'; ?> mb-2">
                                        <?php echo $anuncio['tipo'] === 'perdido' ? 'Perdido' : 'Encontrado'; ?>
                                    </div>
                                    <h5 class="card-title mb-1">
                                        <?php echo sanitize($anuncio['nome_pet'] ?: ('Pet ' . ucfirst($anuncio['especie']))); ?>
                                    </h5>
                                    <div class="text-muted small">
                                        <?php echo sanitize($anuncio['cidade']); ?> - <?php echo sanitize($anuncio['estado']); ?>
                                    </div>
                                </div>
                                <span class="badge bg-secondary">
                                    <?php echo sanitize($anuncio['status']); ?>
                                </span>
                            </div>

                            <div class="mt-3 d-flex gap-2">
                                <a class="btn btn-outline-primary btn-sm flex-grow-1" href="<?php echo BASE_URL; ?>/anuncio.php?id=<?php echo (int)$anuncio['id']; ?>">
                                    Ver
                                </a>
                                <a class="btn btn-primary btn-sm flex-grow-1" href="<?php echo BASE_URL; ?>/editar-anuncio.php?id=<?php echo (int)$anuncio['id']; ?>">
                                    Editar
                                </a>
                            </div>
                        </div>

                        <div class="card-footer bg-white border-0 pt-0 pb-3">
                            <div class="text-muted small">
                                Publicado em <?php echo formatDateTimeBR($anuncio['data_publicacao'] ?? ''); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
