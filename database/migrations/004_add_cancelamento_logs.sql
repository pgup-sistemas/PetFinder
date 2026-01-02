-- Tabela de auditoria de cancelamentos
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

-- Adicionar colunas nas tabelas existentes para registrar cancelamento
ALTER TABLE `parceiro_assinaturas` 
ADD COLUMN IF NOT EXISTS `cancelada_em` datetime DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `motivo_cancelamento` text DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `cancelamento_gateway` varchar(20) DEFAULT NULL COMMENT 'sucesso, manual, falha';

ALTER TABLE `doacoes` 
ADD COLUMN IF NOT EXISTS `cancelada_em` datetime DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `motivo_cancelamento` text DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `cancelamento_gateway` varchar(20) DEFAULT NULL COMMENT 'sucesso, manual, falha';
