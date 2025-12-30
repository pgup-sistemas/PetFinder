<?php
/**
 * PetFinder - Configurações Globais
 * Arquivo principal de configuração do sistema
 */

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurações de Erro (DESENVOLVIMENTO)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('America/Porto_Velho');

// ═══════════════════════════════════════════════
// BANCO DE DADOS
// ═══════════════════════════════════════════════
define('DB_HOST', 'localhost');
define('DB_NAME', 'petfinder');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ═══════════════════════════════════════════════
// CAMINHOS DO SISTEMA
// ═══════════════════════════════════════════════
define('BASE_PATH', __DIR__);
define('BASE_URL', 'http://localhost/petfinder');
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
define('DONATION_MODAL_TITLE', 'Ajude a manter o PetFinder ativo!');
define('DONATION_MODAL_TEXT', 'Seja um apoiador e ajude a manter o PetFinder ativo!');

// ═══════════════════════════════════════════════
// EMAIL
// ═══════════════════════════════════════════════
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'pageupsistemas@gmail.com');
define('SMTP_PASS', 'anxulvfmgttjhjut');
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