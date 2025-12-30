<?php
/**
 * PetFinder - Modelo de Doação
 * Gerencia as operações do módulo de doações, incluindo métricas e histórico do usuário.
 */

class Doacao
{
    private $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    /**
     * Registra doação (única ou recorrente).
     */
    public function create(array $data): int
    {
        return $this->db->insert('doacoes', $data);
    }

    /**
     * Atualiza status após retorno do gateway.
     */
    public function updateStatus(int $id, string $status, array $extras = [])
    {
        $payload = array_merge($extras, ['status' => $status]);
        return $this->db->update('doacoes', $payload, 'id = ?', [$id]);
    }

    /**
     * Busca doações por usuário (histórico com filtros básicos).
     */
    public function findByUser(int $usuarioId, int $limit = 20, int $offset = 0)
    {
        return $this->db->fetchAll(
            'SELECT * FROM doacoes WHERE usuario_id = ? ORDER BY data_doacao DESC LIMIT ? OFFSET ?',
            [$usuarioId, $limit, $offset]
        );
    }

    /**
     * Obtém sumário para dashboard.
     */
    public function getDashboardSummary()
    {
        return $this->db->fetchOne(
            'SELECT 
                COUNT(*) AS total_doacoes,
                SUM(CASE WHEN status = "aprovada" THEN valor ELSE 0 END) AS total_aprovado,
                SUM(CASE WHEN tipo = "mensal" AND status = "aprovada" THEN valor ELSE 0 END) AS recorrente,
                SUM(CASE WHEN DATE_FORMAT(data_doacao, "%Y-%m") = DATE_FORMAT(CURDATE(), "%Y-%m") AND status = "aprovada" THEN valor ELSE 0 END) AS mes_atual
             FROM doacoes'
        );
    }

    /**
     * Busca mural de doadores (com permissão de exibição).
     */
    public function getMural(int $limit = 20)
    {
        return $this->db->fetchAll(
            'SELECT nome_doador, mensagem, valor, data_doacao 
             FROM doacoes 
             WHERE status = "aprovada" AND exibir_mural = 1 
             ORDER BY data_doacao DESC 
             LIMIT ?',
            [$limit]
        );
    }

    /**
     * Calcula progresso da meta financeira atual.
     */
    public function getCurrentGoalProgress()
    {
        return $this->db->fetchOne(
            'SELECT m.valor_meta, m.valor_arrecadado, m.custos_servidor, m.custos_manutencao, m.descricao
             FROM metas_financeiras m
             WHERE m.ativo = 1
             ORDER BY m.mes_referencia DESC
             LIMIT 1'
        );
    }

    /**
     * Atualiza valor arrecadado da meta ativa.
     */
    public function updateGoalProgress(float $valor)
    {
        return $this->db->query(
            'UPDATE metas_financeiras 
             SET valor_arrecadado = valor_arrecadado + ?
             WHERE ativo = 1
             ORDER BY mes_referencia DESC
             LIMIT 1',
            [$valor]
        );
    }
}

