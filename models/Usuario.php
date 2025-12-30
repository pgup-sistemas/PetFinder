<?php
/**
 * PetFinder - Modelo de Usuário
 * Responsável por encapsular o acesso à camada de dados da tabela `usuarios`
 * e fornecer operações coerentes com as regras de negócio.
 */

class Usuario
{
    protected $db;

    public function __construct($db = null)
    {
        $this->db = $db ?: getDB();
    }

    /**
     * Cria um novo usuário.
     * Espera receber dados previamente validados.
     */
    public function create(array $data): int
    {
        return $this->db->insert('usuarios', $data);
    }

    /**
     * Atualiza campos genéricos do usuário.
     */
    public function update(int $id, array $data)
    {
        if (empty($data)) {
            return false;
        }

        return $this->db->update('usuarios', $data, 'id = ?', [$id]);
    }

    /**
     * Atualiza senha aplicando hash bcrypt.
     */
    public function updatePassword(int $id, string $novaSenha)
    {
        return $this->update($id, ['senha' => hashPassword($novaSenha)]);
    }

    /**
     * Atualiza preferências de notificação.
     */
    public function updateNotificationPreference(int $id, bool $enabled)
    {
        return $this->update($id, ['notificacoes_email' => $enabled ? 1 : 0]);
    }

    /**
     * Atualiza foto do perfil e mantém histórico antigo para possível limpeza posterior.
     */
    public function updateProfilePhoto(int $id, string $filename)
    {
        return $this->update($id, ['foto_perfil' => $filename]);
    }

    /**
     * Obtém registro por ID.
     */
    public function findById(int $id)
    {
        return $this->db->fetchOne('SELECT * FROM usuarios WHERE id = ?', [$id]);
    }

    /**
     * Obtém registro por email.
     */
    public function findByEmail(string $email)
    {
        return $this->db->fetchOne('SELECT * FROM usuarios WHERE email = ?', [$email]);
    }

    /**
     * Obtém contador de anúncios ativos do usuário.
     */
    public function countActiveAds(int $usuarioId): int
    {
        $result = $this->db->fetchOne(
            'SELECT COUNT(*) AS total FROM anuncios WHERE usuario_id = ? AND status = ?',
            [$usuarioId, STATUS_ATIVO]
        );

        return (int)($result['total'] ?? 0);
    }

    /**
     * Obtém timestamp do último anúncio criado pelo usuário.
     */
    public function getLastAdPublishedAt(int $usuarioId)
    {
        return $this->db->fetchOne(
            'SELECT data_publicacao FROM anuncios WHERE usuario_id = ? ORDER BY data_publicacao DESC LIMIT 1',
            [$usuarioId]
        );
    }

    /**
     * Atualiza metadados de acesso após login bem-sucedido.
     */
    public function registerSuccessfulLogin(int $usuarioId)
    {
        return $this->update($usuarioId, [
            'tentativas_login' => 0,
            'bloqueado_ate' => null,
            'ultimo_acesso' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Registra tentativa malsucedida e aplica bloqueio temporário conforme regras.
     */
    public function registerFailedLogin(int $usuarioId, int $tentativasAtuais)
    {
        $novasTentativas = $tentativasAtuais + 1;
        $dados = ['tentativas_login' => $novasTentativas];

        if ($novasTentativas >= MAX_LOGIN_ATTEMPTS) {
            $dados['bloqueado_ate'] = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            $dados['tentativas_login'] = 0;
        }

        return $this->update($usuarioId, $dados);
    }

    /**
     * Confirma email através do token de confirmação.
     */
    public function confirmEmailByToken(string $token)
    {
        $usuario = $this->db->fetchOne(
            'SELECT id FROM usuarios WHERE token_confirmacao = ? AND email_confirmado = 0',
            [$token]
        );

        if (!$usuario) {
            return false;
        }

        $this->update((int)$usuario['id'], [
            'email_confirmado' => 1,
            'token_confirmacao' => null
        ]);

        return $usuario['id'];
    }

    /**
     * Armazena token de recuperação de senha.
     */
    public function startPasswordReset(int $usuarioId, string $token, string $expiraEm)
    {
        return $this->update($usuarioId, [
            'token_recuperacao' => $token,
            'token_expira' => $expiraEm
        ]);
    }

    /**
     * Busca usuário por token de recuperação válido.
     */
    public function findByValidResetToken(string $token)
    {
        return $this->db->fetchOne(
            'SELECT * FROM usuarios WHERE token_recuperacao = ? AND token_expira > NOW()',
            [$token]
        );
    }
}

