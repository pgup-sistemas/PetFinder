<?php
require_once __DIR__ . '/../config.php';

$pageTitle = 'Ajuda - PetFinder';

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <h1 class="h3 fw-bold mb-3">Ajuda e Como Usar</h1>
            <p class="text-muted mb-4">
                Aqui você encontra orientações rápidas para usar o PetFinder: publicar anúncios, buscar pets, favoritos, alertas e status.
            </p>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3">1) Tipos de anúncio</h2>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded">
                                <div class="fw-semibold">🔴 Perdido</div>
                                <div class="text-muted small">Quando você perdeu seu pet e precisa de ajuda para encontrar.</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded">
                                <div class="fw-semibold">🟢 Encontrado</div>
                                <div class="text-muted small">Quando você encontrou um pet e quer localizar o tutor.</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded">
                                <div class="fw-semibold">💙 Adoção</div>
                                <div class="text-muted small">Quando você quer disponibilizar um pet para adoção responsável.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3">2) Publicar anúncio</h2>
                    <div class="text-muted">
                        <div class="mb-2"><strong>Passo 1:</strong> escolha o tipo (Perdido/Encontrado/Adoção).</div>
                        <div class="mb-2"><strong>Passo 2:</strong> adicione fotos e informações do pet.</div>
                        <div class="mb-2"><strong>Passo 3:</strong> informe local e contatos.</div>
                        <div class="mt-3">Dica: fotos nítidas ajudam muito a identificação.</div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3">3) Buscar pets</h2>
                    <div class="text-muted">
                        Use a tela de busca para filtrar por tipo, espécie, cidade/bairro e ordenar resultados.
                        Se preferir, use a aba <strong>Mapa</strong> para ver anúncios com localização.
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3">4) Favoritos</h2>
                    <div class="text-muted">
                        Em um anúncio, use o botão de favoritos para salvar e acompanhar depois.
                        Você pode gerenciar tudo em <strong>Favoritos</strong>.
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3">5) Alertas (e-mails)</h2>
                    <div class="text-muted">
                        Em <strong>Meus Alertas</strong> você pode criar alertas com filtros (tipo, espécie, cidade/UF e raio).
                        Quando anúncios corresponderem aos critérios, o sistema envia um resumo por e-mail.
                        Você pode <strong>pausar/reativar</strong> ou <strong>excluir</strong> alertas a qualquer momento.
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3">6) Status do anúncio (Ativo / Resolvido / Expirado / Inativo)</h2>
                    <div class="text-muted">
                        <div class="mb-2"><strong>Ativo:</strong> aparece nas buscas normalmente.</div>
                        <div class="mb-2"><strong>Resolvido:</strong> indica que o caso foi concluído (pet encontrado/reunido). Isso alimenta a contagem de <strong>Casos Resolvidos</strong>.</div>
                        <div class="mb-2"><strong>Expirado:</strong> quando o anúncio passa do prazo. Ele deixa de aparecer nas buscas públicas e fica no seu histórico para conferência.</div>
                        <div class="mb-2"><strong>Inativo:</strong> usado para remoção/arquivamento (soft delete). O anúncio deixa de aparecer nas buscas.</div>
                        <div class="mt-3">Por padrão, as buscas públicas exibem apenas anúncios <strong>Ativos</strong>. Você pode ver seus anúncios <strong>Resolvidos</strong> e <strong>Expirados</strong> em <strong>Meus Anúncios</strong>.</div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3">7) Excluir anúncio (Arquivar)</h2>
                    <div class="text-muted">
                        Ao excluir um anúncio, o sistema faz um <strong>arquivamento (soft delete)</strong>.
                        Isso significa que:
                        <div class="mt-3">
                            <div class="mb-2"><strong>-</strong> O anúncio muda para o status <strong>Inativo</strong> e deixa de aparecer nas buscas públicas.</div>
                            <div class="mb-2"><strong>-</strong> As <strong>imagens não são apagadas</strong> do disco automaticamente (mais seguro e simples).</div>
                            <div class="mb-2"><strong>-</strong> Somente o <strong>dono</strong> do anúncio (ou um <strong>admin</strong>) pode excluir.</div>
                        </div>
                        <div class="mt-3">Dica: se o pet foi encontrado, prefira usar <strong>Marcar como resolvido</strong> em vez de excluir.</div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3">8) Adoção (campos extras)</h2>
                    <div class="text-muted">
                        No tipo <strong>💙 Adoção</strong>, existem campos adicionais para ajudar a encontrar um lar responsável:
                        <div class="mt-3">
                            <div class="mb-2"><strong>Idade:</strong> idade aproximada do pet (em anos).</div>
                            <div class="mb-2"><strong>Castrado:</strong> informe se o pet é castrado (sim/não/não informado).</div>
                            <div class="mb-2"><strong>Vacinas / Observações:</strong> descreva vacinas, vermífugo e cuidados especiais.</div>
                            <div class="mb-2"><strong>Termo de responsabilidade:</strong> se marcado, indica que a adoção deve ser formalizada com termo.</div>
                        </div>
                        <div class="mt-3">Dica: quanto mais completo o anúncio, maior a chance de uma adoção rápida e segura.</div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-outline-primary" href="<?php echo BASE_URL; ?>/busca">Ir para Busca</a>
                <?php if (isLoggedIn()): ?>
                    <a class="btn btn-outline-primary" href="<?php echo BASE_URL; ?>/meus-anuncios">Meus Anúncios</a>
                    <a class="btn btn-outline-primary" href="<?php echo BASE_URL; ?>/alertas">Meus Alertas</a>
                <?php else: ?>
                    <a class="btn btn-outline-primary" href="<?php echo BASE_URL; ?>/login">Entrar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
