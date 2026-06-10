-- ============================================================
-- Trivya RH — Schema do Banco de Dados
-- Versão: 1.0.0  |  Data: 2025-01-01
--
-- Engine:    InnoDB (suporte a transações e foreign keys)
-- Charset:   utf8mb4 (suporte completo a emojis e unicode)
-- Collation: utf8mb4_unicode_ci (ordenação correta para PT-BR)
--
-- INSTRUÇÕES:
--   1. Criar o banco: CREATE DATABASE trivya_rh CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
--   2. Selecionar: USE trivya_rh;
--   3. Executar este arquivo no phpMyAdmin ou MySQL CLI
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ============================================================
-- 1. TABELA: admins
--    Usuários com acesso ao painel administrativo
-- ============================================================
CREATE TABLE IF NOT EXISTS `admins` (
    `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `nome`           VARCHAR(100)    NOT NULL COMMENT 'Nome completo do administrador',
    `email`          VARCHAR(254)    NOT NULL COMMENT 'E-mail único, usado como login',
    `senha_hash`     VARCHAR(255)    NOT NULL COMMENT 'Hash bcrypt da senha (cost 12)',
    `role`           ENUM('super_admin', 'admin', 'editor') NOT NULL DEFAULT 'editor'
                                     COMMENT 'Nível de permissão',
    `ativo`          TINYINT(1)      NOT NULL DEFAULT 1 COMMENT '1 = ativo, 0 = bloqueado',
    `avatar_url`     VARCHAR(500)    NULL COMMENT 'URL do avatar (opcional)',
    `ultimo_acesso`  DATETIME        NULL COMMENT 'Timestamp do último login bem-sucedido',
    `criado_em`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_admins_email` (`email`),
    KEY `idx_admins_role` (`role`),
    KEY `idx_admins_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Usuários administradores do painel';

-- ============================================================
-- 2. TABELA: login_attempts
--    Registro de tentativas de login (proteção contra força bruta)
-- ============================================================
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id`          BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `ip`          VARCHAR(45)      NOT NULL COMMENT 'IPv4 ou IPv6 do cliente',
    `email`       VARCHAR(254)     NOT NULL COMMENT 'E-mail tentado (pode não existir)',
    `sucesso`     TINYINT(1)       NOT NULL DEFAULT 0 COMMENT '1 = login OK, 0 = falha',
    `motivo`      VARCHAR(100)     NULL COMMENT 'Motivo da falha (para auditoria)',
    `tentado_em`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_login_ip_data` (`ip`, `tentado_em`),
    KEY `idx_login_email` (`email`),
    KEY `idx_login_sucesso` (`sucesso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Histórico de tentativas de login para bloqueio por IP';

-- ============================================================
-- 3. TABELA: conteudo
--    Conteúdo editável das seções do site institucional
--    (hero, sobre, serviços, diferenciais, CTA, footer)
-- ============================================================
CREATE TABLE IF NOT EXISTS `conteudo` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `secao`         VARCHAR(50)     NOT NULL COMMENT 'Identificador da seção (ex: hero, sobre)',
    `campo`         VARCHAR(100)    NOT NULL COMMENT 'Nome do campo dentro da seção',
    `valor`         TEXT            NULL COMMENT 'Conteúdo do campo (texto, HTML, URL)',
    `tipo`          ENUM('texto', 'html', 'url', 'imagem', 'numero', 'booleano')
                                    NOT NULL DEFAULT 'texto',
    `ordem`         SMALLINT        NOT NULL DEFAULT 0 COMMENT 'Ordem de exibição',
    `admin_id`      INT UNSIGNED    NULL COMMENT 'Último admin que editou',
    `criado_em`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_conteudo_secao_campo` (`secao`, `campo`),
    KEY `idx_conteudo_secao` (`secao`),
    KEY `fk_conteudo_admin` (`admin_id`),
    CONSTRAINT `fk_conteudo_admin` FOREIGN KEY (`admin_id`)
        REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Conteúdo editável das seções do site via painel admin';

-- ============================================================
-- 4. TABELA: depoimentos
--    Depoimentos de clientes exibidos no site
-- ============================================================
CREATE TABLE IF NOT EXISTS `depoimentos` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `nome`          VARCHAR(100)    NOT NULL COMMENT 'Nome do cliente/candidato',
    `cargo`         VARCHAR(100)    NULL COMMENT 'Cargo ou empresa',
    `texto`         TEXT            NOT NULL COMMENT 'Texto do depoimento',
    `foto_url`      VARCHAR(500)    NULL COMMENT 'URL da foto (opcional)',
    `nota`          TINYINT         NOT NULL DEFAULT 5 COMMENT 'Avaliação de 1 a 5',
    `ativo`         TINYINT(1)      NOT NULL DEFAULT 1 COMMENT '1 = visível no site',
    `ordem`         SMALLINT        NOT NULL DEFAULT 0,
    `criado_em`     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_depoimentos_ativo_ordem` (`ativo`, `ordem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Depoimentos de clientes para exibição no site';

-- ============================================================
-- 5. TABELA: leads
--    Contatos gerados pelo formulário "Fale Conosco" (empresas)
-- ============================================================
CREATE TABLE IF NOT EXISTS `leads` (
    `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `nome`            VARCHAR(100)    NOT NULL COMMENT 'Nome do contato na empresa',
    `empresa`         VARCHAR(150)    NULL COMMENT 'Nome da empresa',
    `cnpj`            VARCHAR(18)     NULL COMMENT 'CNPJ da empresa (formatado)',
    `email`           VARCHAR(254)    NOT NULL,
    `telefone`        VARCHAR(20)     NULL COMMENT 'Telefone formatado',
    `cargo`           VARCHAR(100)    NULL COMMENT 'Cargo do contato',
    `mensagem`        TEXT            NULL COMMENT 'Mensagem do formulário de contato',
    `servico_interesse` VARCHAR(100)  NULL COMMENT 'Serviço de interesse indicado no formulário',
    `status`          ENUM('novo', 'em_contato', 'qualificado', 'convertido', 'descartado')
                                      NOT NULL DEFAULT 'novo',
    `origem`          VARCHAR(50)     NOT NULL DEFAULT 'site' COMMENT 'Canal de origem (site, whatsapp, indicacao)',
    `utm_source`      VARCHAR(100)    NULL,
    `utm_medium`      VARCHAR(100)    NULL,
    `utm_campaign`    VARCHAR(100)    NULL,
    `ip`              VARCHAR(45)     NULL COMMENT 'IP do visitante',
    `observacoes`     TEXT            NULL COMMENT 'Anotações internas do time de vendas',
    `admin_id`        INT UNSIGNED    NULL COMMENT 'Admin responsável pelo lead',
    `consentimento_lgpd` TINYINT(1)  NOT NULL DEFAULT 0 COMMENT '1 = consentiu com LGPD',
    `criado_em`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_leads_status` (`status`),
    KEY `idx_leads_email` (`email`),
    KEY `idx_leads_criado_em` (`criado_em`),
    KEY `fk_leads_admin` (`admin_id`),
    CONSTRAINT `fk_leads_admin` FOREIGN KEY (`admin_id`)
        REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Leads de empresas captados pelo formulário de contato';

-- ============================================================
-- 6. TABELA: candidatos
--    Candidatos que enviaram currículo pelo site
-- ============================================================
CREATE TABLE IF NOT EXISTS `candidatos` (
    `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `nome`            VARCHAR(100)    NOT NULL,
    `email`           VARCHAR(254)    NOT NULL,
    `telefone`        VARCHAR(20)     NULL,
    `cidade`          VARCHAR(100)    NULL,
    `estado`          CHAR(2)         NULL COMMENT 'UF (ex: SP, RJ)',
    `area_interesse`  VARCHAR(150)    NULL COMMENT 'Área de atuação desejada',
    `nivel`           ENUM('estagio', 'junior', 'pleno', 'senior', 'gerencia', 'diretoria', 'nao_informado')
                                      NOT NULL DEFAULT 'nao_informado',
    `pretensao_salarial` VARCHAR(50)  NULL,
    `linkedin_url`    VARCHAR(500)    NULL,
    `curriculo_nome`  VARCHAR(255)    NULL COMMENT 'Nome do arquivo do currículo no servidor',
    `curriculo_tipo`  VARCHAR(10)     NULL COMMENT 'Extensão do arquivo: pdf, doc, docx',
    `curriculo_tamanho` INT UNSIGNED  NULL COMMENT 'Tamanho do arquivo em bytes',
    `mensagem`        TEXT            NULL COMMENT 'Mensagem complementar do candidato',
    `status`          ENUM('recebido', 'em_analise', 'entrevista', 'aprovado', 'reprovado', 'contratado')
                                      NOT NULL DEFAULT 'recebido',
    `ip`              VARCHAR(45)     NULL,
    `observacoes`     TEXT            NULL COMMENT 'Notas internas do recrutador',
    `admin_id`        INT UNSIGNED    NULL COMMENT 'Recrutador responsável',
    `consentimento_lgpd` TINYINT(1)  NOT NULL DEFAULT 0,
    `criado_em`       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_candidatos_status` (`status`),
    KEY `idx_candidatos_email` (`email`),
    KEY `idx_candidatos_area` (`area_interesse`),
    KEY `idx_candidatos_criado_em` (`criado_em`),
    KEY `fk_candidatos_admin` (`admin_id`),
    CONSTRAINT `fk_candidatos_admin` FOREIGN KEY (`admin_id`)
        REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Candidatos que enviaram currículo pelo site';

-- ============================================================
-- 7. TABELA: posts
--    Blog / artigos da Trivya RH
-- ============================================================
CREATE TABLE IF NOT EXISTS `posts` (
    `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `titulo`         VARCHAR(200)    NOT NULL,
    `slug`           VARCHAR(220)    NOT NULL COMMENT 'URL amigável única (gerada do título)',
    `resumo`         VARCHAR(500)    NULL COMMENT 'Resumo para listagem e meta description',
    `conteudo`       LONGTEXT        NOT NULL COMMENT 'Corpo do artigo em HTML',
    `imagem_destaque` VARCHAR(500)   NULL COMMENT 'URL da imagem principal',
    `status`         ENUM('rascunho', 'publicado', 'arquivado') NOT NULL DEFAULT 'rascunho',
    `categoria`      VARCHAR(100)    NULL,
    `tags`           VARCHAR(500)    NULL COMMENT 'Tags separadas por vírgula',
    `meta_title`     VARCHAR(70)     NULL COMMENT 'Título SEO (até 60 chars recomendado)',
    `meta_description` VARCHAR(170)  NULL COMMENT 'Meta description SEO (até 160 chars)',
    `visualizacoes`  INT UNSIGNED    NOT NULL DEFAULT 0,
    `admin_id`       INT UNSIGNED    NULL COMMENT 'Autor do post',
    `publicado_em`   DATETIME        NULL COMMENT 'Data de publicação (permite agendamento)',
    `criado_em`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `atualizado_em`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_posts_slug` (`slug`),
    KEY `idx_posts_status_publicado` (`status`, `publicado_em`),
    KEY `idx_posts_categoria` (`categoria`),
    KEY `fk_posts_admin` (`admin_id`),
    CONSTRAINT `fk_posts_admin` FOREIGN KEY (`admin_id`)
        REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Blog e artigos publicados pela Trivya RH';

-- ============================================================
-- 8. TABELA: configuracoes
--    Configurações globais editáveis via painel admin
--    (chave-valor: whatsapp, e-mail, redes sociais, textos, etc.)
-- ============================================================
CREATE TABLE IF NOT EXISTS `configuracoes` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `chave`         VARCHAR(100)    NOT NULL COMMENT 'Identificador único da configuração',
    `valor`         TEXT            NULL COMMENT 'Valor da configuração',
    `descricao`     VARCHAR(300)    NULL COMMENT 'Descrição para o painel admin',
    `grupo`         VARCHAR(50)     NOT NULL DEFAULT 'geral' COMMENT 'Agrupamento: geral, contato, redes_sociais, seo',
    `tipo`          ENUM('texto', 'html', 'url', 'email', 'telefone', 'numero', 'booleano', 'imagem')
                                    NOT NULL DEFAULT 'texto',
    `admin_id`      INT UNSIGNED    NULL COMMENT 'Último admin que alterou',
    `atualizado_em` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_configuracoes_chave` (`chave`),
    KEY `idx_configuracoes_grupo` (`grupo`),
    KEY `fk_configuracoes_admin` (`admin_id`),
    CONSTRAINT `fk_configuracoes_admin` FOREIGN KEY (`admin_id`)
        REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Configurações globais do site editáveis via painel';

-- ============================================================
-- 9. TABELA: logs
--    Registro de eventos da aplicação (segurança, auditoria)
-- ============================================================
CREATE TABLE IF NOT EXISTS `logs` (
    `id`         BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `nivel`      ENUM('info', 'warning', 'error', 'critical') NOT NULL DEFAULT 'info',
    `modulo`     VARCHAR(50)      NOT NULL DEFAULT 'app' COMMENT 'Módulo gerador: auth, mailer, lead, etc.',
    `mensagem`   VARCHAR(1000)    NOT NULL,
    `contexto`   JSON             NULL COMMENT 'Dados adicionais em JSON',
    `ip`         VARCHAR(45)      NULL,
    `url`        VARCHAR(500)     NULL COMMENT 'URL da requisição',
    `admin_id`   INT UNSIGNED     NULL COMMENT 'Admin autenticado no momento do evento',
    `criado_em`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_logs_nivel_data` (`nivel`, `criado_em`),
    KEY `idx_logs_modulo` (`modulo`),
    KEY `idx_logs_admin` (`admin_id`),
    KEY `fk_logs_admin` (`admin_id`),
    CONSTRAINT `fk_logs_admin` FOREIGN KEY (`admin_id`)
        REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Logs de eventos da aplicação: segurança, erros e auditoria';

-- ============================================================
-- 10. TABELA: consentimentos_lgpd
--     Registro de consentimentos LGPD (Art. 7º, inciso I)
--     Mantém evidência auditável de cada consentimento coletado
-- ============================================================
CREATE TABLE IF NOT EXISTS `consentimentos_lgpd` (
    `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `tipo_titular`  ENUM('lead', 'candidato') NOT NULL COMMENT 'Quem consentiu',
    `titular_id`    INT UNSIGNED     NOT NULL COMMENT 'ID na tabela correspondente',
    `email`         VARCHAR(254)     NOT NULL COMMENT 'E-mail do titular (redundância para rastreabilidade)',
    `finalidade`    VARCHAR(300)     NOT NULL COMMENT 'Para qual finalidade o dado será usado',
    `versao_politica` VARCHAR(20)    NOT NULL DEFAULT '1.0'
                                     COMMENT 'Versão da Política de Privacidade aceita',
    `ip`            VARCHAR(45)      NOT NULL COMMENT 'IP do dispositivo no momento do consentimento',
    `user_agent`    VARCHAR(500)     NULL COMMENT 'Navegador/dispositivo no momento do consentimento',
    `url_origem`    VARCHAR(500)     NULL COMMENT 'URL do formulário onde o consentimento foi coletado',
    `consentido_em` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `revogado_em`   DATETIME         NULL COMMENT 'Preenchido se o titular revogar o consentimento',
    `motivo_revogacao` VARCHAR(300)  NULL,
    PRIMARY KEY (`id`),
    KEY `idx_consentimentos_titular` (`tipo_titular`, `titular_id`),
    KEY `idx_consentimentos_email` (`email`),
    KEY `idx_consentimentos_data` (`consentido_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Registros de consentimento LGPD - Art. 7 Lei 13.709/2018';

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Índices adicionais para performance em consultas comuns
-- ============================================================
-- Otimiza o bloqueio por IP (consulta mais frequente de segurança)
ALTER TABLE `login_attempts`
    ADD INDEX `idx_ip_sucesso_data` (`ip`, `sucesso`, `tentado_em`);

-- Limpar logs antigos (manter apenas 90 dias via EVENT ou cron)
-- DELIMITER $$
-- CREATE EVENT IF NOT EXISTS `limpar_logs_antigos`
--   ON SCHEDULE EVERY 1 DAY
--   DO DELETE FROM `logs` WHERE `criado_em` < DATE_SUB(NOW(), INTERVAL 90 DAY);
-- $$
-- DELIMITER ;
