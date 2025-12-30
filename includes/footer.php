</main> <!-- Fecha main-content do header -->
    
    <!-- Footer -->
    <footer class="footer bg-dark text-white mt-5">
        <div class="container py-5">
            <div class="row">
                <!-- Sobre -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5 class="mb-3">
                        <span class="logo-icon">🐾</span> PetFinder
                    </h5>
                    <p class="text-light-gray">
                        Ajudamos a reunir pets perdidos com suas famílias desde 2025.
                        Cada contribuição mantém a plataforma gratuita e disponível para todos.
                    </p>
                    <div class="social-links mt-3">
                        <a href="#" class="text-white me-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>
                
                <!-- Links Rápidos -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="mb-3">Links Rápidos</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="<?php echo BASE_URL; ?>/" class="text-light-gray text-decoration-none">
                                Início
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo BASE_URL; ?>/busca.php" class="text-light-gray text-decoration-none">
                                Buscar Pets
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo BASE_URL; ?>/novo-anuncio.php" class="text-light-gray text-decoration-none">
                                Publicar Anúncio
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo BASE_URL; ?>/doar.php" class="text-light-gray text-decoration-none">
                                Doar
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Precisa de Ajuda? -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <h6 class="mb-3">Precisa de Ajuda?</h6>
                    <ul class="list-unstyled text-light-gray">
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i> suporte@petfinder.com</li>
                        <li class="mb-2"><i class="bi bi-whatsapp me-2"></i> (69) 99999-9999</li>
                        <li class="mb-2"><i class="bi bi-clock me-2"></i> Seg - Sex, 9h às 18h</li>
                    </ul>
                </div>

                <!-- Transparência -->
                <div class="col-lg-3 col-md-6">
                    <h6 class="mb-3">Transparência</h6>
                    <p class="text-light-gray mb-3">
                        Acompanhe nossas metas financeiras e veja como sua doação ajuda a manter o PetFinder vivo.
                    </p>
                    <a href="<?php echo BASE_URL; ?>/transparencia.php" class="btn btn-outline-light btn-sm">
                        Ver relatório financeiro
                    </a>
                </div>
            </div>
        </div>
        <div class="bg-black text-center py-3">
            <small class="text-light">&copy; <?php echo date('Y'); ?> PetFinder. Todos os direitos reservados.</small>
        </div>
    </footer>

    <!-- Modal Inteligente de Doação -->
    <div class="modal fade" id="donationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-gradient" style="background: linear-gradient(135deg, #0ba360 0%, #3cba92 100%);">
                    <h5 class="modal-title text-white fw-bold">
                        <?php echo sanitize(DONATION_MODAL_TITLE); ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 p-lg-5">
                    <div class="row g-4 align-items-center">
                        <div class="col-lg-5 text-center text-lg-start">
                            <img src="<?php echo ASSETS_URL; ?>/img/donation-heart.svg" alt="Doação PetFinder" class="img-fluid mb-3" style="max-height: 160px;">
                            <p class="text-muted mb-0">
                                <?php echo sanitize(DONATION_MODAL_TEXT); ?>
                            </p>
                        </div>
                        <div class="col-lg-7">
                            <div class="card border-0 bg-light p-3 p-lg-4 shadow-sm">
                                <h6 class="text-uppercase text-muted fw-bold mb-3">Escolha um valor rápido</h6>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <?php $sugestoes = [10, 20, 50]; ?>
                                    <?php foreach ($sugestoes as $valor): ?>
                                        <a href="<?php echo BASE_URL . '/doar.php?valor=' . $valor; ?>" class="btn btn-outline-success flex-fill">
                                            R$ <?php echo number_format($valor, 2, ',', '.'); ?>
                                        </a>
                                    <?php endforeach; ?>
                                    <a href="<?php echo BASE_URL; ?>/doar.php" class="btn btn-outline-success flex-fill">
                                        Outro valor
                                    </a>
                                </div>
                                <div class="alert alert-success d-flex align-items-center gap-2 mb-0" role="alert">
                                    <i class="bi bi-shield-check fs-4 text-success"></i>
                                    <div>
                                        Pagamento 100% seguro via nossos parceiros e ajuda a manter a plataforma gratuita.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-between flex-column flex-lg-row gap-2">
                    <div class="d-flex gap-2 order-2 order-lg-1">
                        <button type="button" class="btn btn-outline-secondary" data-action="maybe-later">
                            Talvez depois
                        </button>
                        <button type="button" class="btn btn-outline-danger" data-action="never-show">
                            Não mostrar novamente
                        </button>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/doar.php" class="btn btn-success btn-lg order-1 order-lg-2" data-action="donate-now">
                        <i class="bi bi-heart-fill me-2"></i> Doar agora
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo ASSETS_URL; ?>/js/main.js"></script>
</body>
</html>