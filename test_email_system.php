<?php
require_once 'config.php';

echo "=== Teste do Sistema de Envio de Emails ===\n\n";

// Verifica configurações do SMTP
echo "1. Configurações SMTP:\n";
echo str_repeat("-", 30) . "\n";
echo "Host: " . SMTP_HOST . "\n";
echo "Port: " . SMTP_PORT . "\n";
echo "User: " . SMTP_USER . "\n";
echo "From: " . EMAIL_FROM . "\n";
echo "From Name: " . EMAIL_FROM_NAME . "\n";
echo "Password: " . (empty(SMTP_PASS) ? 'VAZIO' : 'CONFIGURADO') . "\n\n";

// Verifica se a função sendEmail existe
echo "2. Função sendEmail:\n";
echo str_repeat("-", 30) . "\n";
if (function_exists('sendEmail')) {
    echo "✓ Função sendEmail existe\n\n";
} else {
    echo "✗ Função sendEmail não encontrada\n\n";
}

// Testa envio de email
echo "3. Teste de Envio de Email:\n";
echo str_repeat("-", 30) . "\n";

$testEmail = 'pageupsistemas@gmail.com'; // Email de teste
$subject = 'Teste PetFinder - ' . date('d/m/Y H:i:s');
$message = '
<html>
<body style="font-family: Arial, sans-serif;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2196F3;">Teste de Email - PetFinder</h2>
        <p>Este é um email de teste do sistema PetFinder.</p>
        <p><strong>Data do teste:</strong> ' . date('d/m/Y H:i:s') . '</p>
        <p><strong>Servidor:</strong> ' . $_SERVER['HTTP_HOST'] ?? 'CLI' . '</p>
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #ddd;">
        <p style="color: #999; font-size: 12px;">
            Se você recebeu este email, a configuração está funcionando corretamente.
        </p>
    </div>
</body>
</html>';

try {
    echo "Enviando email para: {$testEmail}\n";
    echo "Assunto: {$subject}\n";
    
    $result = sendEmail($testEmail, $subject, $message);
    
    if ($result) {
        echo "✓ Email enviado com sucesso!\n";
        echo "  Verifique a caixa de entrada e span do email: {$testEmail}\n";
    } else {
        echo "✗ Falha ao enviar email\n";
    }
    
} catch (Exception $e) {
    echo "✗ Erro ao enviar email: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
} catch (Error $e) {
    echo "✗ Erro Fatal: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n";

// Testa funções de email específicas do sistema
echo "4. Teste de Funções de Email do Sistema:\n";
echo str_repeat("-", 30) . "\n";

try {
    require_once __DIR__ . '/includes/auth.php';
    $auth = new Auth();
    
    // Testa email de confirmação
    echo "Testando email de confirmação...\n";
    $token = bin2hex(random_bytes(16));
    $auth->sendConfirmationEmail($testEmail, 'Usuário Teste', $token);
    echo "✓ Email de confirmação enviado\n";
    
    // Testa email de recuperação de senha
    echo "Testando email de recuperação de senha...\n";
    $auth->requestPasswordReset($testEmail);
    echo "✓ Email de recuperação enviado\n";
    
} catch (Exception $e) {
    echo "✗ Erro nas funções de email: " . $e->getMessage() . "\n";
}

echo "\n";

// Verifica configurações PHP para email
echo "5. Configurações PHP:\n";
echo str_repeat("-", 30) . "\n";
echo "SMTP: " . (ini_get('SMTP') ?: 'não configurado') . "\n";
echo "smtp_port: " . (ini_get('smtp_port') ?: 'não configurado') . "\n";
echo "sendmail_path: " . (ini_get('sendmail_path') ?: 'não configurado') . "\n";
echo "mail.add_x_header: " . (ini_get('mail.add_x_header') ? 'On' : 'Off') . "\n";

echo "\n=== Resumo ===\n";
echo "✓ Configurações SMTP verificadas\n";
echo "✓ Função sendEmail testada\n";
echo "✓ Emails de sistema testados\n";
echo "\nPróximos passos:\n";
echo "1. Verifique se recebeu os emails de teste\n";
echo "2. Confirme se não foram para span\n";
echo "3. Teste envio para outros domínios\n";
?>
