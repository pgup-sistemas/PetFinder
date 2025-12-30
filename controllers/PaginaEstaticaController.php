<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

class PaginaEstaticaController {
    public function mostrar($pagina) {
        // Lista de páginas estáticas permitidas
        $paginasPermitidas = [
            'politica-privacidade',
            'termos-uso',
            'politica-cookies',
            'lgpd',
            'contato-dpo'
        ];

        // Verifica se a página solicitada é válida
        if (!in_array($pagina, $paginasPermitidas)) {
            header('HTTP/1.0 404 Not Found');
            include 'views/404.php';
            exit();
        }

        // Define o título da página
        $titulo = $this->getTituloPagina($pagina);
        
        // Inclui o cabeçalho
        include 'includes/header.php';
        
        // Inclui o conteúdo da página
        include "views/paginas-estaticas/{$pagina}.php";
        
        // Inclui o rodapé
        include 'includes/footer.php';
    }
    
    private function getTituloPagina($pagina) {
        $titulos = [
            'politica-privacidade' => 'Política de Privacidade',
            'termos-uso' => 'Termos de Uso',
            'politica-cookies' => 'Política de Cookies',
            'lgpd' => 'LGPD - Lei Geral de Proteção de Dados',
            'contato-dpo' => 'Contato do Encarregado de Dados (DPO)'
        ];
        
        return $titulos[$pagina] ?? 'Página';
    }
}
?>
