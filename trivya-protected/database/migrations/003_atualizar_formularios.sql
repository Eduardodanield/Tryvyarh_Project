-- ============================================================
-- Migration 003: Atualização dos campos dos formulários
-- Empresa (leads) e Candidato conforme especificação oficial
-- Execute UMA VEZ no phpMyAdmin → Importar → selecionar este arquivo
-- ============================================================

SET NAMES utf8mb4;

-- ============================================================
-- TABELA LEADS (empresas) — Novos campos
-- ============================================================
ALTER TABLE `leads`
    ADD COLUMN IF NOT EXISTS `cidade_estado` VARCHAR(150) NULL
        COMMENT 'Cidade/Estado da empresa'
        AFTER `cargo`,
    ADD COLUMN IF NOT EXISTS `cargo_area_contratar` VARCHAR(200) NULL
        COMMENT 'Cargo ou área que deseja contratar'
        AFTER `cidade_estado`,
    ADD COLUMN IF NOT EXISTS `qtd_vagas` ENUM('1','2-5','5-10') NULL
        COMMENT 'Quantidade de vagas pretendidas'
        AFTER `cargo_area_contratar`,
    ADD COLUMN IF NOT EXISTS `urgencia` TINYINT(1) NULL DEFAULT NULL
        COMMENT '1 = Sim, 0 = Não, NULL = não informado'
        AFTER `qtd_vagas`,
    ADD COLUMN IF NOT EXISTS `aceita_contato_whatsapp` TINYINT(1) NOT NULL DEFAULT 1
        COMMENT 'Cliente autorizou contato via WhatsApp'
        AFTER `urgencia`;

ALTER TABLE `leads`
    ADD INDEX IF NOT EXISTS `idx_qtd_vagas` (`qtd_vagas`),
    ADD INDEX IF NOT EXISTS `idx_urgencia` (`urgencia`);

-- ============================================================
-- TABELA CANDIDATOS — Novos campos
-- ============================================================
ALTER TABLE `candidatos`
    ADD COLUMN IF NOT EXISTS `idade` TINYINT UNSIGNED NULL
        COMMENT 'Idade declarada pelo candidato'
        AFTER `nome`,
    ADD COLUMN IF NOT EXISTS `data_nascimento` DATE NULL
        COMMENT 'Data de nascimento'
        AFTER `idade`,
    ADD COLUMN IF NOT EXISTS `cidade_bairro` VARCHAR(200) NULL
        COMMENT 'Cidade e bairro onde mora'
        AFTER `data_nascimento`,
    ADD COLUMN IF NOT EXISTS `escolaridade` VARCHAR(100) NULL
        COMMENT 'Nível de escolaridade'
        AFTER `area_interesse`,
    ADD COLUMN IF NOT EXISTS `esta_estudando` TINYINT(1) NULL
        COMMENT '1 = Sim, 0 = Não'
        AFTER `escolaridade`;

-- Atualizar ENUM de area_interesse para as opções oficiais
ALTER TABLE `candidatos`
    MODIFY COLUMN `area_interesse` ENUM(
        'administrativo',
        'recursos_humanos',
        'atendimento',
        'recepcao',
        'comercial_vendas',
        'marketing',
        'operacional',
        'outro'
    ) NULL COMMENT 'Área de interesse oficial da Trivya';

ALTER TABLE `candidatos`
    ADD INDEX IF NOT EXISTS `idx_area_estudando` (`area_interesse`, `esta_estudando`);
