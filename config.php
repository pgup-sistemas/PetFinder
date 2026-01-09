<?php
/**
 * PetFinder - Configurações Globais
 * Arquivo principal de configuração do sistema
 */

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurações de Erro (PRODUÇÃO)
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 0);

// Timezone
date_default_timezone_set('America/Porto_Velho');

// ═══════════════════════════════════════════════
// BANCO DE DADOS
// ═══════════════════════════════════════════════
define('DB_HOST', 'petfinder.mysql.dbaas.com.br'); // Ex: mysql.locaweb.com.br
define('DB_NAME', 'petfinder');
define('DB_USER', 'petfinder');
define('DB_PASS', 'Petfinder#2026');
define('DB_CHARSET', 'utf8mb4');

// ═══════════════════════════════════════════════
// CAMINHOS DO SISTEMA
// ═══════════════════════════════════════════════
define('BASE_PATH', __DIR__);
define('BASE_URL', 'https://petfinder.pageup.net.br'); // Subdomínio correto
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('ASSETS_URL', BASE_URL . '/assets');

// ═══════════════════════════════════════════════
// UPLOAD DE ARQUIVOS
// ═══════════════════════════════════════════════
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);
define('MAX_PHOTOS_PER_AD', 2);

// ═══════════════════════════════════════════════
// LIMITES DO SISTEMA
// ═══════════════════════════════════════════════
define('MAX_ACTIVE_ADS_PER_USER', 10);
define('MIN_PUBLISH_INTERVAL', 300); // 5 minutos em segundos
define('AD_EXPIRATION_DAYS', 180); // 6 meses
define('MAX_LOGIN_ATTEMPTS', 3);
define('MAX_ALERTS_PER_USER', 5);
define('RESULTS_PER_PAGE', 20);
define('ALERT_MIN_INTERVAL_SECONDS', 3600); // 1 hora entre disparos do mesmo alerta
define('ALERT_EMAIL_MAX_RESULTS', 5);

// ═══════════════════════════════════════════════
// DOAÇÕES
// ═══════════════════════════════════════════════
define('MIN_DONATION_AMOUNT', 2.00);
define('MERCADO_PAGO_PUBLIC_KEY', 'TEST-your-public-key');
define('MERCADO_PAGO_ACCESS_TOKEN', 'TEST-your-access-token');

// Configurações EFIbank
define('EFI_PIX_KEY', 'new.normando@gmail.com'); // Chave PIX para cobranças
define('EFI_CLIENT_ID', 'Client_Id_eb634fb28bc3cf46747e4188072a77f40be0ec45'); // ID do cliente EFI
define('EFI_CLIENT_SECRET', 'Client_Secret_10e743b7c9992ee387bdbdf32e38d7bb641684e4'); // Secret do cliente EFI
define('EFI_CERTIFICATE_PATH', __DIR__ . '/certs/production.pem'); // Caminho do certificado
define('EFI_PIX_DESCRIPTION', 'Doação para PetFinder'); // Descrição das cobranças
define('EFI_PIX_NOTIFICATION_URL', 'https://petfinder.pageup.net.br/api/efi-billing-notification.php'); // URL de webhook
define('EFI_BASE_URL', 'https://api.efipay.com.br/api/'); // Base URL da API
define('EFI_WEBHOOK_TOKEN', 'e239441a10244d1b9c5bb4b14bab7e83'); // Token de segurança do webhook
define('EFI_SANDBOX', false); // Modo sandbox (true = teste, false = produção)
define('DONATION_MODAL_TITLE', 'Ajude a manter o PetFinder ativo!');
define('DONATION_MODAL_TEXT', 'Seja um apoiador e ajude a manter o PetFinder ativo!');

// ═══════════════════════════════════════════════
// EMAIL
// ═══════════════════════════════════════════════
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'pageupsistemas@gmail.com');
define('SMTP_PASS', 'rsyh bqnh hboh ycgw');
define('EMAIL_FROM', 'pageupsistemas@gmail.com');
define('EMAIL_FROM_NAME', 'PetFinder');

// ═══════════════════════════════════════════════
// GOOGLE MAPS API
// ═══════════════════════════════════════════════
define('GOOGLE_MAPS_API_KEY', 'your-google-maps-api-key');

// ═══════════════════════════════════════════════
// CACHE
// ═══════════════════════════════════════════════
define('CACHE_ENABLED', true);
define('CACHE_TIME_HOME', 300); // 5 minutos
define('CACHE_TIME_SEARCH', 600); // 10 minutos

// ═══════════════════════════════════════════════
// SEGURANÇA
// ═══════════════════════════════════════════════
define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_TIMEOUT', 86400); // 24 horas
define('CSRF_TOKEN_NAME', 'csrf_token');

// ═══════════════════════════════════════════════
// AUTOLOAD DE CLASSES
// ═══════════════════════════════════════════════
spl_autoload_register(function ($class) {
    $paths = [
        BASE_PATH . '/models/' . $class . '.php',
        BASE_PATH . '/controllers/' . $class . '.php',
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// ═══════════════════════════════════════════════
// FUNÇÕES AUXILIARES
// ═══════════════════════════════════════════════
require_once BASE_PATH . '/includes/functions.php';
require_once BASE_PATH . '/includes/db.php';

// ═══════════════════════════════════════════════
// CONSTANTES DE STATUS
// ═══════════════════════════════════════════════
define('STATUS_ATIVO', 'ativo');
define('STATUS_RESOLVIDO', 'resolvido');
define('STATUS_INATIVO', 'inativo');
define('STATUS_BLOQUEADO', 'bloqueado');
define('STATUS_EXPIRADO', 'expirado');

define('TIPO_PERDIDO', 'perdido');
define('TIPO_ENCONTRADO', 'encontrado');
define('TIPO_DOACAO', 'doacao');

define('ESPECIE_CACHORRO', 'cachorro');
define('ESPECIE_GATO', 'gato');
define('ESPECIE_AVE', 'ave');
define('ESPECIE_OUTRO', 'outro');

define('TAMANHO_PEQUENO', 'pequeno');
define('TAMANHO_MEDIO', 'medio');
define('TAMANHO_GRANDE', 'grande');

// ═══════════════════════════════════════════════
// MENSAGENS DO SISTEMA
// ═══════════════════════════════════════════════
define('MSG_SUCCESS', 'success');
define('MSG_ERROR', 'error');
define('MSG_WARNING', 'warning');
define('MSG_INFO', 'info');

// ═══════════════════════════════════════════════
// VERIFICAÇÃO DE AMBIENTE
// ═══════════════════════════════════════════════
if (!is_writable(UPLOAD_PATH)) {
    die('ERRO: O diretório de uploads não tem permissão de escrita!');
}

// ═══════════════════════════════════════════════
// HEADER SECURITY
// ═══════════════════════════════════════════════
if (php_sapi_name() !== 'cli') {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
}

?>