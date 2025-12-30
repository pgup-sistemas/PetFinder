<?php
require_once __DIR__ . '/../config.php';

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
                <div>
                    <h1 class="h3 fw-bold mb-1">📊 Transparência</h1>
                    <p class="text-muted mb-0">Relatório público de custos e metas do PetFinder.</p>
                </div>
                <a href="<?php echo BASE_URL; ?>/doar.php" class="btn btn-success">
                    💚 Fazer uma doação
                </a>
            </div>

            <div class="alert alert-info">
                Esta página é um <strong>placeholder</strong>. A integração com pagamento (Efí Bank) e o painel de relatório financeiro ainda serão implementados.
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h2 class="h5 fw-bold">✅ O que já temos</h2>
                    <ul class="mb-0">
                        <li>Plataforma web funcionando (cadastro, login, anúncios, busca, favoritos).</li>
                        <li>Envio de e-mails via SMTP (PHPMailer).</li>
                        <li>Upload de fotos e exibição de anúncios.</li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <h2 class="h5 fw-bold">🗺️ Próximos passos</h2>
                    <ul class="mb-0">
                        <li>Integração de geolocalização/mapas para melhorar busca por proximidade.</li>
                        <li>Integração de pagamento via <strong>Efí Bank</strong> para doações (PIX/cartão).</li>
                        <li>Webhook para confirmar pagamentos e registrar doações.</li>
                        <li>Relatório mensal (custos e entradas) com histórico.</li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h2 class="h5 fw-bold">📌 Como você pode ajudar</h2>
                    <p class="mb-0 text-muted">
                        Você pode contribuir com melhorias, sugestões ou apoiando financeiramente. Toda ajuda mantém o PetFinder disponível e gratuito para mais pessoas.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
