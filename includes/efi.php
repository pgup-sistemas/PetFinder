<?php
/**
 * PetFinder - SDK EFI Simplificado
 * Implementação básica para cobranças PIX
 */

namespace EFI {
    
    class Cobranca {
        public $txid;
        public $pixCopiaECola;
        public $qrCode;
        public $status;
        public $valor;
        
        public function __construct($data = []) {
            $this->txid = $data['txid'] ?? null;
            $this->pixCopiaECola = $data['pixCopiaECola'] ?? null;
            $this->qrCode = $data['qrCode'] ?? null;
            $this->status = $data['status'] ?? 'pendente';
            $this->valor = $data['valor'] ?? 0;
        }
    }
    
    class EFI {
        private $pixKey;
        private $publicKey;
        private $accessToken;
        
        public function __construct($options = []) {
            $this->pixKey = $options['pixKey'] ?? (defined('EFI_PIX_KEY') ? EFI_PIX_KEY : 'pageupsistemas@gmail.com');
            $this->publicKey = $options['publicKey'] ?? (defined('MERCADO_PAGO_PUBLIC_KEY') ? MERCADO_PAGO_PUBLIC_KEY : 'TEST-key');
            $this->accessToken = $options['accessToken'] ?? (defined('MERCADO_PAGO_ACCESS_TOKEN') ? MERCADO_PAGO_ACCESS_TOKEN : 'TEST-token');
        }
        
        public function criarCobrancaImediata($dados) {
            // Simulação de criação de cobrança PIX
            $txid = 'PET_' . date('YmdHis') . '_' . rand(1000, 9999);
            
            // Extrair valor do array ou usar direto
            $valor = $dados;
            if (is_array($dados)) {
                $valor = $dados['valor']['original'] ?? $dados['valor'] ?? 0;
            }
            
            $valorFormatado = number_format((float)$valor, 2, '.', '');
            
            // Gera chave PIX copia e cola (simulação)
            $chavePix = $this->pixKey ?: 'pageupsistemas@gmail.com';
            
            $locId = 'LOC_' . date('YmdHis') . '_' . rand(1000, 9999);
            
            // Retornar array compatível com PagamentoController
            return [
                'txid' => $txid,
                'loc' => [
                    'id' => $locId
                ],
                'qrcode' => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==",
                'status' => 'ativa',
                'valor' => $valorFormatado
            ];
        }
        
        public function pixCreateImmediateCharge($params, $body) {
            // Método compatível com PagamentoController
            return $this->criarCobrancaImediata($body);
        }
        
        public function pixGenerateQRCode($params) {
            // Simulação de geração de QR Code
            return [
                'qrcode' => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==",
                'imagemQrcode' => "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==",
                'txid' => $params['id'] ?? 'PET_' . date('YmdHis') . '_' . rand(1000, 9999)
            ];
        }
        
        public function consultarCobranca($txid) {
            // Simulação de consulta
            return new Cobranca([
                'txid' => $txid,
                'status' => 'ativa',
                'valor' => '10.00'
            ]);
        }
    }
}

namespace {
    // Alias global para compatibilidade
    if (!class_exists('EFI')) {
        class_alias('EFI\EFI', 'EFI');
    }
}
?>
