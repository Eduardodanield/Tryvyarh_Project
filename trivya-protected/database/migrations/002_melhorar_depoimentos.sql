-- ============================================================
-- Migration 002: Melhorias no sistema de depoimentos — Trivya RH
-- Execute UMA VEZ no phpMyAdmin → Importar → selecionar este arquivo
-- ============================================================

SET NAMES utf8mb4;

ALTER TABLE `depoimentos`
    ADD COLUMN IF NOT EXISTS `destaque` TINYINT(1) NOT NULL DEFAULT 0
        COMMENT '1 = depoimento em destaque (badge no site)'
        AFTER `ativo`,
    ADD COLUMN IF NOT EXISTS `autorizacao_lgpd` TINYINT(1) NOT NULL DEFAULT 0
        COMMENT 'Cliente autorizou formalmente o uso do depoimento'
        AFTER `destaque`,
    ADD COLUMN IF NOT EXISTS `origem` ENUM('whatsapp','email','linkedin','pessoalmente','outro')
        NULL DEFAULT NULL
        COMMENT 'Canal de origem do depoimento (rastreabilidade)'
        AFTER `autorizacao_lgpd`,
    ADD COLUMN IF NOT EXISTS `cliente_desde` DATE NULL
        COMMENT 'Data aproximada do início da parceria'
        AFTER `origem`;

-- Índice para ordenação eficiente no site público
ALTER TABLE `depoimentos`
    ADD INDEX IF NOT EXISTS `idx_destaque_ordem` (`ativo`, `destaque`, `ordem`);

-- Marcar os 3 depoimentos fictícios de seed como não autorizados
UPDATE `depoimentos`
SET `autorizacao_lgpd` = 0,
    `origem`           = NULL
WHERE id IN (1, 2, 3);
