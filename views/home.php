<?php
require_once __DIR__ . '/../config.php';

// Buscar últimos anúncios
$db = getDB();
$anunciosRecentes = $db->fetchAll("
    SELECT a.*, u.nome as autor_nome,
           (SELECT nome_arquivo FROM fotos_anuncios WHERE anuncio_id = a.id ORDER BY ordem LIMIT 1) as foto
    FROM anuncios a
    JOIN usuarios u ON a.usuario_id = u.id
    WHERE a.status = 'ativo'
    ORDER BY a.data_publicacao DESC
    LIMIT 8
");

// Estatísticas
$stats = $db->fetchOne("SELECT * FROM view_estatisticas");

include __DIR__ . '/../includes/header.php';
?>

<div class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="display-4 fw-bold mb-4">
                    🐾 Encontre ou Publique um Pet
                </h1>
                <p class="lead mb-4">
                    Ajudamos a reunir animais perdidos com suas famílias. 
                    Juntos já reunimos <strong><?php echo number_format($stats['casos_resolvidos'] ?? 0); ?> pets</strong>!
                </p>
                
                <!-- Busca Rápida -->
                <div class="search-box mb-4">
                    <form action="/petfinder/busca" method="GET" class="d-flex gap-2">
                        <input type="text" 
                               name="q" 
                               class="form-control form-control-lg" 
                               placeholder="O que você procura? Ex: labrador preto"
                               id="quick-search">
                        <button type="submit" class="btn btn-primary">
                            🔍
                        </button>
                    </form>
                </div>
                
                <!-- Filtros Rápidos -->
                <div class="quick-filters d-flex flex-wrap gap-2">
                    <a href="/petfinder/busca?tipo=perdido" class="btn btn-outline-danger">
                        🔴 Perdidos
                    </a>
                    <a href="/petfinder/busca?tipo=encontrado" class="btn btn-outline-success">
                        🟢 Encontrados
                    </a>
                    <a href="/petfinder/busca?especie=cachorro" class="btn btn-outline-secondary">
                        🐕 Cachorros
                    </a>
                    <a href="/petfinder/busca?especie=gato" class="btn btn-outline-secondary">
                        🐈 Gatos
                    </a>
                    <button onclick="buscarProximos()" class="btn btn-outline-primary">
                        📍 Perto de Mim
                    </button>
                </div>
            </div>
            
            <div class="col-lg-5 text-center">
                <div class="cta-buttons">
                    <a href="/petfinder/novo-anuncio.php" class="btn btn-success btn-lg mb-3 w-100">
                        📢 PUBLICAR ANÚNCIO
                    </a>
                    <p class="text-muted small">
                        É rápido, fácil e 100% gratuito!
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estatísticas -->
<div class="stats-section py-4 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-card">
                    <div class="stat-number text-primary">
                        <?php echo number_format($stats['usuarios_ativos'] ?? 0); ?>
                    </div>
                    <div class="stat-label">Usuários Ativos</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-card">
                    <div class="stat-number text-danger">
                        <?php echo number_format($stats['perdidos_ativos'] ?? 0); ?>
                    </div>
                    <div class="stat-label">Pets Perdidos</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-card">
                    <div class="stat-number text-success">
                        <?php echo number_format($stats['encontrados_ativos'] ?? 0); ?>
                    </div>
                    <div class="stat-label">Pets Encontrados</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stat-card">
                    <div class="stat-number text-info">
                        <?php echo number_format($stats['casos_resolvidos'] ?? 0); ?>
                    </div>
                    <div class="stat-label">Casos Resolvidos</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Anúncios Recentes -->
<div class="anuncios-section py-5">
    <div class="container">
        <div class="section-header mb-4">
            <h2 class="h3 fw-bold">⚡ Publicados Hoje</h2>
            <p class="text-muted">Anúncios mais recentes na sua região</p>
        </div>
        
        <div class="row g-4">
            <?php foreach ($anunciosRecentes as $anuncio): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="anuncio-card" onclick="window.location='/petfinder/anuncio.php?id=<?php echo $anuncio['id']; ?>'">
                        <div class="anuncio-image">
                            <?php if ($anuncio['foto']): ?>
                                <img src="/uploads/anuncios/<?php echo $anuncio['foto']; ?>" 
                                     alt="<?php echo sanitize($anuncio['nome_pet']); ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <span>📷</span>
                                    <p>Sem foto</p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="anuncio-badge badge-<?php echo $anuncio['tipo']; ?>">
                                <?php echo $anuncio['tipo'] == 'perdido' ? '🔴 Perdido' : '🟢 Encontrado'; ?>
                            </div>
                        </div>
                        
                        <div class="anuncio-body">
                            <h5 class="anuncio-title">
                                <?php echo sanitize($anuncio['nome_pet'] ?: 'Pet ' . ucfirst($anuncio['especie'])); ?>
                            </h5>
                            
                            <div class="anuncio-info">
                                <span class="badge bg-secondary me-1">
                                    <?php echo ucfirst($anuncio['especie']); ?>
                                </span>
                                <span class="badge bg-light text-dark">
                                    <?php echo ucfirst($anuncio['tamanho']); ?>
                                </span>
                            </div>
                            
                            <p class="anuncio-location text-muted small mb-2">
                                📍 <?php echo sanitize($anuncio['bairro']); ?>, 
                                <?php echo sanitize($anuncio['cidade']); ?>
                            </p>
                            
                            <p class="anuncio-time text-muted small">
                                🕒 <?php echo timeAgo($anuncio['data_publicacao']); ?>
                            </p>
                        </div>
                        
                        <div class="anuncio-footer">
                            <button class="btn btn-sm btn-outline-primary w-100">
                                Ver Detalhes
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="/petfinder/busca" class="btn btn-primary btn-lg">
                Ver Todos os Anúncios →
            </a>
        </div>
    </div>
</div>

<!-- Como Funciona -->
<div class="how-it-works-section py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="h3 fw-bold">Como Funciona?</h2>
            <p class="text-muted">É simples e rápido!</p>
        </div>
        
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="step-card">
                    <div class="step-icon">📝</div>
                    <h4>1. Publique</h4>
                    <p>Cadastre o pet perdido ou encontrado com foto e localização</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="step-card">
                    <div class="step-icon">🔍</div>
                    <h4>2. Busque</h4>
                    <p>Pessoas procuram por pets na sua região</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="step-card">
                    <div class="step-icon">❤️</div>
                    <h4>3. Reúna</h4>
                    <p>Conecte pets com suas famílias novamente!</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Doações CTA -->
<div class="donation-cta py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3 class="fw-bold mb-3">💚 Ajude a Manter o PetFinder Gratuito</h3>
                <p class="mb-0">
                    Com sua doação, mantemos o sistema funcionando e ajudamos 
                    mais pets a reencontrar suas famílias. Qualquer valor ajuda!
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="/petfinder/doar.php" class="btn btn-success btn-lg">
                    💚 Doar Agora
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.hero-section {
    padding: 80px 0;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    color: #212529;
}

.search-box .form-control {
    border-radius: 12px;
    padding: 14px 20px;
    border: 2px solid #dee2e6;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    font-size: 1rem;
}

.search-box .form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.search-box .btn {
    border-radius: 12px;
    padding: 14px 20px;
    font-weight: 600;
    font-size: 1.2rem;
    min-width: 56px;
}

.quick-filters .btn {
    border-radius: 50px;
    padding: 8px 16px;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
}

.quick-filters .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.cta-buttons .btn-success {
    border-radius: 50px;
    padding: 16px 32px;
    font-weight: 700;
    font-size: 1.1rem;
    box-shadow: 0 4px 16px rgba(25, 135, 84, 0.3);
    transition: all 0.3s ease;
}

.cta-buttons .btn-success:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(25, 135, 84, 0.4);
}

@media (max-width: 768px) {
    .hero-section {
        padding: 60px 0 40px;
    }
    .search-box .form-control,
    .search-box .btn {
        border-radius: 8px;
        margin-bottom: 8px;
    }
    .search-box {
        flex-direction: column;
    }
    .quick-filters {
        justify-content: center;
    }
}

.search-box {
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    border-radius: 10px;
    overflow: hidden;
}

.anuncio-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s;
    cursor: pointer;
}

.anuncio-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.anuncio-image {
    height: 200px;
    background: #f0f0f0;
    position: relative;
    overflow: hidden;
}

.anuncio-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-image {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #999;
}

.no-image span {
    font-size: 3em;
}

.anuncio-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: bold;
}

.badge-perdido {
    background: #ff4444;
    color: white;
}

.badge-encontrado {
    background: #00CC66;
    color: white;
}

.anuncio-body {
    padding: 15px;
}

.anuncio-title {
    font-size: 1.1em;
    font-weight: bold;
    margin-bottom: 10px;
}

.anuncio-footer {
    padding: 0 15px 15px;
}

.stat-card {
    padding: 20px;
}

.stat-number {
    font-size: 2.5em;
    font-weight: bold;
}

.stat-label {
    color: #666;
    font-size: 0.9em;
}

.step-card {
    padding: 30px 20px;
}

.step-icon {
    font-size: 4em;
    margin-bottom: 20px;
}

.donation-cta {
    background: linear-gradient(135deg, #00CC66 0%, #00994D 100%);
    color: white;
}
</style>

<script>
function buscarProximos() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            window.location.href = `/petfinder/busca?lat=${lat}&lng=${lng}&raio=10`;
        }, function() {
            alert('Não foi possível obter sua localização. Verifique as permissões do navegador.');
        });
    } else {
        alert('Seu navegador não suporta geolocalização.');
    }
}

// Busca com sugestões
const searchInput = document.getElementById('quick-search');
if (searchInput) {
    let timeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            // Aqui você pode adicionar sugestões automáticas via AJAX
            console.log('Buscando:', this.value);
        }, 300);
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>