<?php
require_once __DIR__ . '/config.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    setFlashMessage('Token de confirmação inválido.', MSG_ERROR);
    redirect('/login.php');
}

$usuarioModel = new Usuario();
$userId = $usuarioModel->confirmEmailByToken($token);

if ($userId) {
    setFlashMessage('E-mail confirmado com sucesso! Você já pode fazer login.', MSG_SUCCESS);
    redirect('/login.php');
} else {
    setFlashMessage('Token inválido ou já utilizado. Tente solicitar um novo email de confirmação.', MSG_ERROR);
    redirect('/login.php');
}
