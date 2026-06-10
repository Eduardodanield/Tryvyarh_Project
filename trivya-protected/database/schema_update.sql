-- ============================================================
-- Trivya RH — Atualização do Schema para o CMS Admin
-- Execute este script UMA vez no phpMyAdmin ou MySQL CLI
-- ============================================================

SET NAMES utf8mb4;

-- ── Colunas de reset de senha na tabela admins ──────────────
ALTER TABLE `admins`
    ADD COLUMN IF NOT EXISTS `reset_token`       VARCHAR(64)  NULL AFTER `ultimo_acesso`,
    ADD COLUMN IF NOT EXISTS `reset_token_expira` DATETIME    NULL AFTER `reset_token`;

-- ── Tabela: servicos ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `servicos` (
    `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `titulo`    VARCHAR(120) NOT NULL,
    `descricao` TEXT         NULL,
    `icone`     VARCHAR(10)  NOT NULL DEFAULT '🏢' COMMENT 'Emoji ou código de ícone',
    `link`      VARCHAR(300) NULL,
    `ordem`     SMALLINT     NOT NULL DEFAULT 0,
    `ativo`     TINYINT(1)   NOT NULL DEFAULT 1,
    `criado_em` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_servicos_ativo_ordem` (`ativo`, `ordem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Tabela: nichos_marquee ──────────────────────────────────
CREATE TABLE IF NOT EXISTS `nichos_marquee` (
    `id`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nome`   VARCHAR(80)  NOT NULL,
    `ativo`  TINYINT(1)   NOT NULL DEFAULT 1,
    `ordem`  SMALLINT     NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `idx_nichos_ativo_ordem` (`ativo`, `ordem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Novas chaves na tabela configuracoes ────────────────────
INSERT IGNORE INTO `configuracoes` (`chave`, `valor`, `descricao`, `grupo`, `tipo`) VALUES
('hero_bg_image',  'assets/img/office-bg.png',            'Caminho da imagem de fundo do Hero', 'visual', 'imagem'),
('slogan',         'Recrutamento & Seleção humanizado',    'Slogan exibido no Hero',             'geral',  'texto'),
('instagram_url',  'https://www.instagram.com/trivya.consultoria/', 'URL do Instagram',          'redes_sociais', 'url'),
('linkedin_url',   '#',                                    'URL do LinkedIn',                    'redes_sociais', 'url');

-- ── Dados iniciais: Serviços ────────────────────────────────
INSERT IGNORE INTO `servicos` (`id`, `titulo`, `descricao`, `icone`, `ordem`, `ativo`) VALUES
(1, 'Varejo',           'Recrutamento ágil para redes de comércio, lojas e estabelecimentos varejistas. Da operação ao atendimento ao cliente.',  '🛒', 1, 1),
(2, 'Facilities',       'Seleção de profissionais para limpeza, segurança, manutenção e serviços gerais. Perfis alinhados à sua operação.',        '🏢', 2, 1),
(3, 'Construção Civil', 'Mão de obra qualificada para construtoras, empreiteiras e obras. Processos seletivos adaptados ao setor.',               '🏗️', 3, 1);

-- ── Dados iniciais: Nichos ──────────────────────────────────
INSERT IGNORE INTO `nichos_marquee` (`id`, `nome`, `ativo`, `ordem`) VALUES
(1, 'Comércio Varejista',       1, 1),
(2, 'Facilities',               1, 2),
(3, 'Construção Civil',         1, 3),
(4, 'Recrutamento Terceirizado',1, 4),
(5, 'Seleção Estratégica',      1, 5),
(6, 'RH Estratégico',           1, 6),
(7, 'Humanização & Performance',1, 7);
