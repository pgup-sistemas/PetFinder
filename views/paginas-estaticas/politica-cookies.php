<?php
$pageTitle = 'Política de Cookies';
include __DIR__ . '/../../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <h1 class="mb-4">Política de Cookies</h1>
            
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <p class="text-muted">Última atualização: <?= date('d/m/Y') ?></p>
                    
                    <h3 class="h5 mb-3">1. O que são Cookies?</h3>
                    <p>Cookies são pequenos arquivos de texto armazenados no seu dispositivo quando você visita um site. Eles são amplamente utilizados para fazer os sites funcionarem de forma mais eficiente, além de fornecer informações aos proprietários do site.</p>
                    
                    <h3 class="h5 mb-3 mt-4">2. Como Usamos os Cookies</h3>
                    <p>Utilizamos cookies para:</p>
                    <ul>
                        <li>Lembrar suas preferências e configurações</li>
                        <li>Autenticar usuários e prevenir fraudes</li>
                        <li>Analisar o uso do site para melhorar nossos serviços</li>
                        <li>Personalizar sua experiência de navegação</li>
                        <li>Medir a eficácia de campanhas publicitárias</li>
                    </ul>
                    
                    <h3 class="h5 mb-3 mt-4">3. Tipos de Cookies que Utilizamos</h3>
                    
                    <h4 class="h6 mt-3">3.1 Cookies Essenciais</h4>
                    <p>Essenciais para o funcionamento do site, permitindo recursos como login e segurança.</p>
                    
                    <h4 class="h6 mt-3">3.2 Cookies de Preferências</h4>
                    <p>Lembram suas escolhas para fornecer uma experiência mais personalizada.</p>
                    
                    <h4 class="h6 mt-3">3.3 Cookies Estatísticos</h4>
                    <p>Nos ajudam a entender como os visitantes interagem com o site, coletando informações anônimas.</p>
                    
                    <h4 class="h6 mt-3">3.4 Cookies de Marketing</h4>
                    <p>Utilizados para rastrear visitantes em sites com o objetivo de exibir anúncios mais relevantes.</p>
                    
                    <h3 class="h5 mb-3 mt-4">4. Gerenciamento de Cookies</h3>
                    <p>Você pode controlar e/ou excluir cookies conforme desejar. Você pode excluir todos os cookies que já estão no seu computador e configurar a maioria dos navegadores para impedir que eles sejam colocados. No entanto, se fizer isso, pode ter que ajustar manualmente algumas preferências sempre que visitar um site e alguns serviços e funcionalidades podem não funcionar.</p>
                    
                    <h4 class="h6 mt-3">4.1 Como Gerenciar Cookies nos Navegadores</h4>
                    <p>Você pode gerenciar os cookies através das configurações do seu navegador. Abaixo estão os links para as páginas de ajuda dos principais navegadores:</p>
                    <ul>
                        <li><a href="https://support.google.com/chrome/answer/95647" target="_blank">Google Chrome</a></li>
                        <li><a href="https://support.mozilla.org/pt-BR/kb/gerencie-configuracoes-de-armazenamento-local-de-s" target="_blank">Mozilla Firefox</a></li>
                        <li><a href="https://support.apple.com/pt-br/guide/safari/sfri11471/mac" target="_blank">Safari</a></li>
                        <li><a href="https://support.microsoft.com/pt-br/microsoft-edge/excluir-cookies-no-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank">Microsoft Edge</a></li>
                        <li><a href="https://help.opera.com/en/latest/web-preferences/#cookies" target="_blank">Opera</a></li>
                    </ul>
                    
                    <h3 class="h5 mb-3 mt-4">5. Alterações na Política de Cookies</h3>
                    <p>Podemos atualizar nossa Política de Cookies periodicamente. Recomendamos que você revise esta página regularmente para se manter informado sobre como estamos usando cookies.</p>
                    
                    <h3 class="h5 mb-3 mt-4">6. Contato</h3>
                    <p>Se tiver dúvidas sobre nossa Política de Cookies, entre em contato conosco através da nossa <a href="<?php echo BASE_URL; ?>/contato-dpo">página de contato do DPO</a>.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<!-- Banner de Consentimento de Cookies -->
<div id="cookie-consent-banner" class="fixed-bottom bg-dark text-white p-3 d-none">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <p class="mb-0">Nós utilizamos cookies para melhorar sua experiência em nosso site. Ao continuar navegando, você concorda com a nossa <a href="/politica-cookies" class="text-light font-weight-bold">Política de Cookies</a>.</p>
            </div>
            <div class="col-md-4 text-right">
                <button id="btn-accept-cookies" class="btn btn-primary btn-sm mr-2">Aceitar</button>
                <a href="/politica-cookies" class="btn btn-outline-light btn-sm">Saber mais</a>
            </div>
        </div>
    </div>
</div>

<script>
// Verifica se o usuário já aceitou os cookies
document.addEventListener('DOMContentLoaded', function() {
    if (!localStorage.getItem('cookieConsent')) {
        document.getElementById('cookie-consent-banner').classList.remove('d-none');
    }
    
    // Ao clicar em Aceitar
    document.getElementById('btn-accept-cookies').addEventListener('click', function() {
        localStorage.setItem('cookieConsent', 'accepted');
        document.getElementById('cookie-consent-banner').classList.add('d-none');
    });
});
</script>
