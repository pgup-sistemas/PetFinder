<?php

class FavoritoController
{
    private $favoritoModel;

    public function __construct($favoritoModel = null)
    {
        $this->favoritoModel = $favoritoModel ?: new Favorito();
    }

    public function toggle(int $anuncioId)
    {
        $userId = getUserId();
        if (!$userId) {
            return ['success' => false, 'requires_login' => true];
        }

        if ($this->favoritoModel->isFavorited($userId, $anuncioId)) {
            $this->favoritoModel->remove($userId, $anuncioId);
            return ['success' => true, 'favorited' => false];
        }

        $this->favoritoModel->add($userId, $anuncioId);
        return ['success' => true, 'favorited' => true];
    }

    public function isFavorited(int $anuncioId): bool
    {
        $userId = getUserId();
        if (!$userId) {
            return false;
        }

        return $this->favoritoModel->isFavorited($userId, $anuncioId);
    }

    public function listarDoUsuario()
    {
        $userId = getUserId();
        if (!$userId) {
            requireLogin();
        }

        return $this->favoritoModel->listarPorUsuario($userId);
    }
}
