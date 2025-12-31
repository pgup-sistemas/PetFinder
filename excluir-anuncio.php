<?php
require_once __DIR__ . '/config.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit();
}

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    setFlashMessage('Erro de validação. Recarregue a página e tente novamente.', MSG_ERROR);
    redirect('/meus-anuncios.php');
}

$anuncioId = isset($_POST['anuncio_id']) ? (int)$_POST['anuncio_id'] : 0;
if ($anuncioId <= 0) {
    setFlashMessage('Anúncio inválido.', MSG_ERROR);
    redirect('/meus-anuncios.php');
}

$controller = new AnuncioController();
$result = $controller->excluir($anuncioId);

if (!empty($result['success'])) {
    setFlashMessage('Anúncio removido com sucesso.', MSG_SUCCESS);
} else {
    setFlashMessage($result['error'] ?? 'Não foi possível excluir o anúncio.', MSG_ERROR);
}

redirect('/meus-anuncios.php');
