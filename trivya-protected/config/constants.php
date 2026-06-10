<?php

/**
 * Constantes internas da aplicação Trivya RH
 *
 * Define limites, custos, timeouts e outros valores imutáveis
 * usados em toda a aplicação. Separado de config.php para
 * facilitar auditoria de segurança.
 *
 * @autor    Equipe Trivya RH
 * @versao   1.0.0
 * @data     2025-01-01
 */

declare(strict_types=1);

// ----------------------------------------------------------
// Segurança — Senhas
// ----------------------------------------------------------
/** Custo do bcrypt. Valor 12 é seguro para servidores compartilhados. */
define('BCRYPT_COST',               12);

/** Algoritmo de hash de senha */
define('PASSWORD_ALGO',             PASSWORD_BCRYPT);

// ----------------------------------------------------------
// Segurança — Sessões
// ----------------------------------------------------------
/** Tempo de vida da sessão em segundos (8 horas) */
define('SESSION_LIFETIME',          8 * 60 * 60);

/** Intervalo de regeneração do ID de sessão em segundos (30 minutos) */
define('SESSION_REGEN_INTERVAL',    30 * 60);

/** Nome do cookie de sessão (ofuscar o padrão PHPSESSID) */
define('SESSION_NAME',              'TRIVYA_SID');

// ----------------------------------------------------------
// Segurança — Proteção contra força bruta
// ----------------------------------------------------------
/** Tentativas de login antes do bloqueio de IP */
define('LOGIN_MAX_ATTEMPTS',        5);

/** Janela de tempo para contar tentativas (em segundos — 15 minutos) */
define('LOGIN_WINDOW_SECONDS',      15 * 60);

/** Duração do bloqueio de IP após exceder tentativas (1 hora) */
define('LOGIN_BLOCK_DURATION',      60 * 60);

// ----------------------------------------------------------
// Segurança — CSRF
// ----------------------------------------------------------
/** Tempo de validade do token CSRF em segundos (2 horas) */
define('CSRF_TOKEN_LIFETIME',       2 * 60 * 60);

/** Nome do campo CSRF nos formulários */
define('CSRF_FIELD_NAME',           '_csrf_token');

// ----------------------------------------------------------
// Segurança — reCAPTCHA
// ----------------------------------------------------------
/** Pontuação mínima aceita do reCAPTCHA v3 (0.0 a 1.0) */
define('RECAPTCHA_MIN_SCORE',       0.5);

// ----------------------------------------------------------
// Paginação
// ----------------------------------------------------------
define('PAGINAS_ADMIN_POR_PAGINA', 20);
define('PAGINAS_BLOG_POR_PAGINA',  9);

// ----------------------------------------------------------
// Logs
// ----------------------------------------------------------
/** Caminho do arquivo de log de acesso */
define('LOG_FILE_ACCESS',           LOGS_PATH . '/access.log');

/** Caminho do arquivo de log de erros da aplicação */
define('LOG_FILE_ERROR',            LOGS_PATH . '/error.log');

/** Caminho do arquivo de log de e-mails */
define('LOG_FILE_MAIL',             LOGS_PATH . '/mail.log');

/** Máximo de linhas antes de rotacionar o arquivo de log */
define('LOG_MAX_LINES',             10000);

// ----------------------------------------------------------
// Leads e Candidatos
// ----------------------------------------------------------
/** Status disponíveis para leads */
define('LEAD_STATUS', [
    'novo'         => 'Novo',
    'em_contato'   => 'Em Contato',
    'qualificado'  => 'Qualificado',
    'convertido'   => 'Convertido',
    'descartado'   => 'Descartado',
]);

/** Status disponíveis para candidatos */
define('CANDIDATO_STATUS', [
    'recebido'      => 'Recebido',
    'em_analise'    => 'Em Análise',
    'entrevista'    => 'Entrevista',
    'aprovado'      => 'Aprovado',
    'reprovado'     => 'Reprovado',
    'contratado'    => 'Contratado',
]);

/** Status disponíveis para posts do blog */
define('POST_STATUS', [
    'rascunho'    => 'Rascunho',
    'publicado'   => 'Publicado',
    'arquivado'   => 'Arquivado',
]);

// ----------------------------------------------------------
// Níveis de permissão dos administradores
// ----------------------------------------------------------
define('ADMIN_ROLES', [
    'super_admin' => 'Super Administrador',
    'admin'       => 'Administrador',
    'editor'      => 'Editor',
]);

// ----------------------------------------------------------
// Tipos de conteúdo editável (tabela `conteudo`)
// ----------------------------------------------------------
define('CONTEUDO_SECOES', [
    'hero',
    'sobre',
    'servicos',
    'diferenciais',
    'cta',
    'footer',
]);
