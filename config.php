<?php
/**
 * PetFinder - Configurações Globais
 * Arquivo principal de configuração do sistema
 */

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$PETFINDER_ENV = [];

if (file_exists(__DIR__ . '/.env')) {
    $lines = @file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES);
    if (is_array($lines)) {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                [$k, $v] = array_map('trim', explode('=', $line, 2));
                if ($k !== '') {
                    $PETFINDER_ENV[$k] = $v;
                }
                continue;
            }

            if (str_starts_with($line, 'Client_Id_')) {
                $PETFINDER_ENV['EFI_CLIENT_ID'] = $line;
                continue;
            }

            if (str_starts_with($line, 'Client_Secret_')) {
                $PETFINDER_ENV['EFI_CLIENT_SECRET'] = $line;
                continue;
            }
        }
    }
}

function envValue(string $key, $default = null)
{
    global $PETFINDER_ENV;

    $value = getenv($key);
    if ($value !== false && $value !== '') {
        return $value;
    }

    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }

    if (isset($PETFINDER_ENV[$key]) && $PETFINDER_ENV[$key] !== '') {
        return $PETFINDER_ENV[$key];
    }

    return $default;
}

// Configurações de Erro (DESENVOLVIMENTO)
define('APP_ENV', (string)envValue('APP_ENV', 'development'));
$petfinderIsProd = APP_ENV === 'production';
error_reporting(E_ALL);
ini_set('display_errors', $petfinderIsProd ? '0' : '1');

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

$petfinderBaseUrl = (string)envValue('BASE_URL', '');
if ($petfinderBaseUrl === '') {
    if (php_sapi_name() !== 'cli' && isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== '') {
        $isHttps = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (string)$_SERVER['SERVER_PORT'] === '443')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        $scheme = $isHttps ? 'https' : 'http';

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = '';
        if ($scriptName !== '') {
            $dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
            if ($dir !== '' && $dir !== '.') {
                // Quando usamos mod_rewrite apontando para /views ou /api, o SCRIPT_NAME pode conter
                // essas subpastas internas. Para montar BASE_URL corretamente, removemos o sufixo.
                $normalizedDir = $dir;
                $internalRoots = ['/views', '/api'];
                foreach ($internalRoots as $root) {
                    $pos = strpos($normalizedDir, $root);
                    if ($pos !== false) {
                        $normalizedDir = substr($normalizedDir, 0, $pos);
                        break;
                    }
                }

                $normalizedDir = rtrim($normalizedDir, '/');
                if ($normalizedDir !== '' && $normalizedDir !== '.') {
                    $basePath = $normalizedDir;
                }
            }
        }

        $petfinderBaseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'] . $basePath;
    } else {
        $petfinderBaseUrl = 'http://localhost/petfinder';
    }
}

define('BASE_URL', rtrim($petfinderBaseUrl, '/'));
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

$petfinderDefaultEfiCert = '';
$petfinderCertCandidates = array_merge(
    glob(__DIR__ . '/*.p12') ?: [],
    glob(__DIR__ . '/*.pem') ?: []
);
if (!empty($petfinderCertCandidates)) {
    $petfinderDefaultEfiCert = $petfinderCertCandidates[0];
}

define('EFI_CLIENT_ID', (string)envValue('EFI_CLIENT_ID', ''));
define('EFI_CLIENT_SECRET', (string)envValue('EFI_CLIENT_SECRET', ''));
define('EFI_SANDBOX', filter_var((string)envValue('EFI_SANDBOX', 'false'), FILTER_VALIDATE_BOOLEAN));
define('EFI_CERTIFICATE_PATH', (string)envValue('EFI_CERTIFICATE_PATH', $petfinderDefaultEfiCert));
define('EFI_CERTIFICATE_PASSWORD', (string)envValue('EFI_CERTIFICATE_PASSWORD', ''));
define('EFI_PIX_KEY', (string)envValue('EFI_PIX_KEY', ''));
define('EFI_WEBHOOK_TOKEN', (string)envValue('EFI_WEBHOOK_TOKEN', ''));

// ═══════════════════════════════════════════════
// EMAIL
// ═══════════════════════════════════════════════
define('SMTP_HOST', (string)envValue('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_PORT', (int)envValue('SMTP_PORT', 587));
define('SMTP_USER', (string)envValue('SMTP_USER', ''));
define('SMTP_PASS', (string)envValue('SMTP_PASS', ''));
define('EMAIL_FROM', (string)envValue('EMAIL_FROM', ''));
define('EMAIL_FROM_NAME', (string)envValue('EMAIL_FROM_NAME', 'PetFinder'));

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

$petfinderComposerAutoload = BASE_PATH . '/vendor/autoload.php';
if (file_exists($petfinderComposerAutoload)) {
    require_once $petfinderComposerAutoload;
}
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

function ensureDefaultAdmin(): void
{
    static $ran = false;
    if ($ran) {
        return;
    }
    $ran = true;

    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO(
            $dsn,
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        $email = (string)envValue('DEFAULT_ADMIN_EMAIL', 'admin@petfinder.com');
        $nome = (string)envValue('DEFAULT_ADMIN_NAME', 'Administrador');
        $senhaPlain = (string)envValue('DEFAULT_ADMIN_PASSWORD', 'Admin@123');
        $telefone = (string)envValue('DEFAULT_ADMIN_PHONE', '00000000000');
        $cidade = envValue('DEFAULT_ADMIN_CITY', null);
        $estado = envValue('DEFAULT_ADMIN_STATE', null);

        $force = filter_var((string)envValue('DEFAULT_ADMIN_FORCE', 'false'), FILTER_VALIDATE_BOOLEAN);
        $unlock = filter_var((string)envValue('DEFAULT_ADMIN_UNLOCK', 'false'), FILTER_VALIDATE_BOOLEAN);
        $resetPassword = filter_var((string)envValue('DEFAULT_ADMIN_RESET_PASSWORD', 'false'), FILTER_VALIDATE_BOOLEAN);
        $manageExisting = $force || $unlock || $resetPassword;

        $stmtHasAdmin = $pdo->query('SELECT id FROM usuarios WHERE is_admin = 1 LIMIT 1');
        $hasAdmin = $stmtHasAdmin->fetch();
        if (!empty($hasAdmin) && !$manageExisting) {
            return;
        }

        $telefone = preg_replace('/[^0-9]/', '', $telefone);
        if ($telefone === null || $telefone === '') {
            $telefone = '00000000000';
        }

        $stmtExisting = $pdo->prepare('SELECT id, is_admin FROM usuarios WHERE email = ? LIMIT 1');
        $stmtExisting->execute([$email]);
        $existing = $stmtExisting->fetch();
        if (!empty($existing['id'])) {
            $fields = [];
            $params = [];
            
            if ($force || empty($hasAdmin)) {
                $fields[] = 'is_admin = 1';
            }

            if ($force || $unlock || $resetPassword || empty($hasAdmin)) {
                $fields[] = 'ativo = 1';
                $fields[] = 'email_confirmado = 1';
            }

            if ($unlock) {
                $fields[] = 'tentativas_login = 0';
                $fields[] = 'bloqueado_ate = NULL';
            }

            if ($resetPassword) {
                $fields[] = 'senha = ?';
                $params[] = hashPassword($senhaPlain);
            }

            if (!empty($fields)) {
                $sql = 'UPDATE usuarios SET ' . implode(', ', $fields) . ' WHERE id = ?';
                $params[] = (int)$existing['id'];
                $stmtUpdate = $pdo->prepare($sql);
                $stmtUpdate->execute($params);
            }
            return;
        }

        if (!empty($hasAdmin) && !$force) {
            return;
        }

        $stmtInsert = $pdo->prepare(
            'INSERT INTO usuarios (nome, email, telefone, senha, cidade, estado, notificacoes_email, email_confirmado, token_confirmacao, tentativas_login, bloqueado_ate, data_cadastro, ativo, is_admin)
             VALUES (?, ?, ?, ?, ?, ?, 1, 1, NULL, 0, NULL, ?, 1, 1)'
        );
        $stmtInsert->execute([
            $nome,
            $email,
            $telefone,
            hashPassword($senhaPlain),
            $cidade,
            $estado,
            date('Y-m-d H:i:s'),
        ]);
    } catch (Throwable $e) {
        error_log('[PetFinder] ensureDefaultAdmin: ' . $e->getMessage());
    }
}

ensureDefaultAdmin();

?>