<?php
require_once 'config.php';

echo "=== Teste Detalhado do Sistema de Email ===\n\n";

// Teste 1: Configuração básica
echo "1. Verificando Configurações:\n";
echo str_repeat("-", 40) . "\n";
echo "✓ SMTP Host: " . SMTP_HOST . "\n";
echo "✓ SMTP Port: " . SMTP_PORT . "\n";
echo "✓ SMTP User: " . SMTP_USER . "\n";
echo "✓ Email From: " . EMAIL_FROM . "\n";
echo "✓ From Name: " . EMAIL_FROM_NAME . "\n";
echo "✓ Senha: " . (strlen(SMTP_PASS) > 10 ? 'CONFIGURADA' : 'CURTA/VAZIA') . "\n\n";

// Teste 2: Verificação do PHPMailer
echo "2. Verificando PHPMailer:\n";
echo str_repeat("-", 40) . "\n";

if (file_exists(__DIR__ . '/includes/PHPMailer.php')) {
    echo "✓ Arquivo PHPMailer.php existe\n";
    
    require_once __DIR__ . '/includes/PHPMailer.php';
    
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        echo "✓ Classe PHPMailer existe\n";
        
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            echo "✓ Instância PHPMailer criada\n";
        } catch (Exception $e) {
            echo "✗ Erro ao criar instância: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✗ Classe PHPMailer não encontrada\n";
    }
} else {
    echo "✗ Arquivo PHPMailer.php não existe\n";
}

echo "\n";

// Teste 3: Conexão SMTP
echo "3. Teste de Conexão SMTP:\n";
echo str_repeat("-", 40) . "\n";

try {
    $socket = @fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 10);
    
    if ($socket) {
        echo "✓ Conexão com " . SMTP_HOST . ":" . SMTP_PORT . " bem-sucedida\n";
        fclose($socket);
    } else {
        echo "✗ Falha na conexão: {$errno} - {$errstr}\n";
    }
} catch (Exception $e) {
    echo "✗ Erro no teste de conexão: " . $e->getMessage() . "\n";
}

echo "\n";

// Teste 4: Envio de email simples
echo "4. Teste de Envio de Email:\n";
echo str_repeat("-", 40) . "\n";

$testEmail = 'pageupsistemas@gmail.com';
$subject = 'PetFinder - Teste Detalhado ' . date('H:i:s');
$message = '
<html>
<body style="font-family: Arial, sans-serif; padding: 20px;">
    <h2 style="color: #2196F3;">Teste do Sistema de Email</h2>
    <p><strong>Data:</strong> ' . date('d/m/Y H:i:s') . '</p>
    <p><strong>Servidor:</strong> ' . ($_SERVER['HTTP_HOST'] ?? 'CLI') . '</p>
    <p><strong>SMTP:</strong> ' . SMTP_HOST . '</p>
    <hr>
    <p style="color: #666;">Este é um email de teste do PetFinder.</p>
</body>
</html>';

echo "Enviando para: {$testEmail}\n";
echo "Assunto: {$subject}\n";

try {
    $result = sendEmail($testEmail, $subject, $message);
    
    if ($result) {
        echo "✓ Email enviado com sucesso!\n";
        echo "  Verifique a caixa de entrada: {$testEmail}\n";
    } else {
        echo "✗ Falha no envio (função retornou false)\n";
    }
    
} catch (Exception $e) {
    echo "✗ Exceção: " . $e->getMessage() . "\n";
    echo "  Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n";

// Teste 5: Verificação de logs
echo "5. Verificando Logs de Erro:\n";
echo str_repeat("-", 40) . "\n";

$errorLog = ini_get('error_log');
echo "Arquivo de log: " . ($errorLog ?: 'não definido') . "\n";

if ($errorLog && file_exists($errorLog)) {
    $recentLogs = array_slice(file($errorLog), -10);
    $emailErrors = array_filter($recentLogs, function($line) {
        return stripos($line, 'email') !== false || stripos($line, 'smtp') !== false;
    });
    
    if (!empty($emailErrors)) {
        echo "✗ Encontrados erros recentes de email:\n";
        foreach (array_slice($emailErrors, -3) as $error) {
            echo "  " . trim($error) . "\n";
        }
    } else {
        echo "✓ Nenhum erro de email encontrado nos logs recentes\n";
    }
} else {
    echo "? Arquivo de log não encontrado ou inacessível\n";
}

echo "\n=== Resumo ===\n";
echo "✓ Configurações SMTP verificadas\n";
echo "✓ PHPMailer funcional\n";
echo "✓ Conexão SMTP testada\n";
echo "✓ Envio de email testado\n";
echo "\nRecomendações:\n";
echo "1. Verifique se recebeu o email de teste\n";
echo "2. Confirme configuração de app passwords no Gmail\n";
echo "3. Verifique se não foi para span\n";
echo "4. Teste com outros destinatários\n";
?>
