<?php
/**
 * Script para executar migration de cancelamentos
 * Execute: php database/migrate_cancelamentos.php
 */

require_once __DIR__ . '/../config.php';

echo "=== Migration de Cancelamentos ===\n";

try {
    $db = getDB();
    
    // Criar tabela de logs
    echo "Criando tabela cancelamentos_log...\n";
    $sql = "
    CREATE TABLE IF NOT EXISTS `cancelamentos_log` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `usuario_id` int(11) DEFAULT NULL,
      `doacao_id` int(11) DEFAULT NULL,
      `tipo` enum('assinatura_parceiro','doacao_recorrente') NOT NULL,
      `motivo` text DEFAULT NULL,
      `gateway_response` varchar(50) DEFAULT NULL,
      `responsavel` enum('usuario','admin','sistema') NOT NULL DEFAULT 'usuario',
      `ip_address` varchar(45) DEFAULT NULL,
      `user_agent` text DEFAULT NULL,
      `data_cancelamento` datetime NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `idx_usuario_id` (`usuario_id`),
      KEY `idx_doacao_id` (`doacao_id`),
      KEY `idx_tipo` (`tipo`),
      KEY `idx_data_cancelamento` (`data_cancelamento`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $db->query($sql);
    echo "✓ Tabela cancelamentos_log criada com sucesso!\n";
    
    // Adicionar colunas na tabela parceiro_assinaturas
    echo "\nAdicionando colunas na tabela parceiro_assinaturas...\n";
    
    $colunas = [
        "cancelada_em" => "datetime DEFAULT NULL",
        "motivo_cancelamento" => "text DEFAULT NULL",
        "cancelamento_gateway" => "varchar(20) DEFAULT NULL COMMENT 'sucesso, manual, falha'"
    ];
    
    foreach ($colunas as $coluna => $definicao) {
        try {
            $sql = "ALTER TABLE `parceiro_assinaturas` ADD COLUMN `$coluna` $definicao";
            $db->query($sql);
            echo "✓ Coluna $coluna adicionada\n";
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate column name')) {
                echo "⚠ Coluna $coluna já existe\n";
            } else {
                throw $e;
            }
        }
    }
    
    // Adicionar colunas na tabela doacoes
    echo "\nAdicionando colunas na tabela doacoes...\n";
    
    foreach ($colunas as $coluna => $definicao) {
        try {
            $sql = "ALTER TABLE `doacoes` ADD COLUMN `$coluna` $definicao";
            $db->query($sql);
            echo "✓ Coluna $coluna adicionada\n";
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate column name')) {
                echo "⚠ Coluna $coluna já existe\n";
            } else {
                throw $e;
            }
        }
    }
    
    echo "\n=== Migration concluída com sucesso! ===\n";
    echo "\nResumo:\n";
    echo "- Tabela cancelamentos_log criada\n";
    echo "- Colunas de cancelamento adicionadas em parceiro_assinaturas\n";
    echo "- Colunas de cancelamento adicionadas em doacoes\n";
    echo "\nO sistema de cancelamentos está pronto para uso!\n";
    
} catch (Exception $e) {
    echo "\n❌ Erro durante migration: " . $e->getMessage() . "\n";
    echo "Verifique suas permissões e configurações do banco de dados.\n";
    exit(1);
}
