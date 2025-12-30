<?php
namespace PHPMailer\PHPMailer;

/**
 * PHPMailer - Versão simplificada para envio de emails via SMTP
 * Implementação básica para substituir a biblioteca completa
 */

class PHPMailer
{
    public const ENCRYPTION_STARTTLS = 'tls';

    public $Host;
    public $Port;
    public $Username;
    public $Password;
    public $From;
    public $FromName;
    public $to = [];
    public $Subject;
    public $Body;
    public $isHTML = false;
    public $SMTPAuth = false;
    public $SMTPSecure = '';
    public $CharSet = 'UTF-8';
    public $ContentType = 'text/plain';
    
    public function __construct($exceptions = true)
    {
        // Inicializa configurações
    }
    
    public function isSMTP()
    {
        // Flag para indicar que usará SMTP
        return $this;
    }
    
    public function Host($host)
    {
        $this->Host = $host;
        return $this;
    }
    
    public function Port($port)
    {
        $this->Port = $port;
        return $this;
    }
    
    public function SMTPAuth($auth = true)
    {
        $this->SMTPAuth = $auth;
        return $this;
    }
    
    public function Username($username)
    {
        $this->Username = $username;
        return $this;
    }
    
    public function Password($password)
    {
        $this->Password = $password;
        return $this;
    }
    
    public function SMTPSecure($secure = 'tls')
    {
        $this->SMTPSecure = $secure;
        return $this;
    }
    
    public function setFrom($address, $name = '')
    {
        $this->From = $address;
        $this->FromName = $name;
        return $this;
    }
    
    public function addAddress($address, $name = '')
    {
        $this->to[] = ['address' => $address, 'name' => $name];
        return $this;
    }
    
    public function isHTML($isHTML = true)
    {
        $this->isHTML = $isHTML;
        $this->ContentType = $isHTML ? 'text/html' : 'text/plain';
        return $this;
    }
    
    public function Subject($subject)
    {
        $this->Subject = $subject;
        return $this;
    }
    
    public function Body($body)
    {
        $this->Body = $body;
        return $this;
    }
    
    public function send()
    {
        // Constrói headers
        $headers = [];
        $headers[] = 'From: ' . $this->FromName . ' <' . $this->From . '>';
        $headers[] = 'Reply-To: ' . $this->From;
        $headers[] = 'X-Mailer: PHP/' . phpversion();
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: ' . $this->ContentType . '; charset=' . $this->CharSet;
        
        // Se for SMTP, tenta usar stream_socket_client
        if ($this->Host && $this->Port) {
            return $this->sendSMTP();
        }
        
        // Fallback para mail()
        $to = $this->to[0]['address'] ?? '';
        $headersStr = implode("\r\n", $headers);
        
        return mail($to, $this->Subject, $this->Body, $headersStr);
    }
    
    private function sendSMTP()
    {
        try {
            $socket = @stream_socket_client(
                'tcp://' . $this->Host . ':' . $this->Port,
                $errno,
                $errstr,
                30,
                STREAM_CLIENT_CONNECT
            );

            if (!$socket) {
                error_log("Conexão SMTP falhou: $errno - $errstr");
                return false;
            }

            stream_set_timeout($socket, 30);

            $this->smtpExpect($socket, [220]);
            $this->smtpCommand($socket, 'EHLO ' . gethostname(), [250]);

            if ($this->SMTPSecure === self::ENCRYPTION_STARTTLS || $this->SMTPSecure === 'tls') {
                $this->smtpCommand($socket, 'STARTTLS', [220]);
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new \Exception('Falha ao iniciar STARTTLS');
                }
                $this->smtpCommand($socket, 'EHLO ' . gethostname(), [250]);
            }

            if ($this->SMTPAuth) {
                $this->smtpCommand($socket, 'AUTH LOGIN', [334]);
                $this->smtpCommand($socket, base64_encode($this->Username), [334]);
                $this->smtpCommand($socket, base64_encode($this->Password), [235]);
            }

            $this->smtpCommand($socket, 'MAIL FROM:<' . $this->From . '>', [250]);
            $this->smtpCommand($socket, 'RCPT TO:<' . ($this->to[0]['address'] ?? '') . '>', [250, 251]);
            $this->smtpCommand($socket, 'DATA', [354]);

            $data = $this->buildHeaders() . "\r\n\r\n" . $this->Body;
            $data = str_replace(["\r\n.\r\n", "\n.\n"], ["\r\n..\r\n", "\n..\n"], $data);
            fwrite($socket, $data . "\r\n.\r\n");
            $this->smtpExpect($socket, [250]);

            $this->smtpCommand($socket, 'QUIT', [221, 250]);
            fclose($socket);

            return true;
        } catch (\Exception $e) {
            error_log("Erro SMTP: " . $e->getMessage());
            return false;
        }
    }

    private function smtpCommand($socket, string $command, array $expectedCodes)
    {
        fwrite($socket, $command . "\r\n");
        $this->smtpExpect($socket, $expectedCodes);
    }

    private function smtpExpect($socket, array $expectedCodes)
    {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            // Respostas multi-line: continua enquanto o 4o caractere for '-'
            if (strlen($line) >= 4 && $line[3] !== '-') {
                break;
            }
        }

        if (!preg_match('/^(\d{3})/', $response, $m)) {
            throw new \Exception('Resposta SMTP inválida: ' . trim($response));
        }

        $code = (int)$m[1];
        if (!in_array($code, $expectedCodes, true)) {
            throw new \Exception('SMTP retornou ' . $code . ': ' . trim($response));
        }
    }
    
    private function buildHeaders()
    {
        $headers = "From: {$this->FromName} <{$this->From}>\r\n";
        $headers .= "To: <{$this->to[0]['address']}>\r\n";
        $headers .= "Subject: {$this->Subject}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: {$this->ContentType}; charset={$this->CharSet}\r\n";
        return $headers;
    }
}
