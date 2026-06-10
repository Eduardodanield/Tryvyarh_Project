<?php
/**
 * Bootstrap — Trivya RH
 * Carrega todos os arquivos de configuração/includes.
 *
 * Estrutura (igual em local e produção): trivya-protected/ é sempre irmã
 * de public_html/.
 *   public_html/        ← este arquivo está aqui (document root)
 *   trivya-protected/    ← arquivos sensíveis (config, includes, lib, logs, .env)
 */
declare(strict_types=1);

if (defined('PROTECTED_PATH')) {
    return; // já carregado, evita dupla execução
}

define('PROTECTED_PATH',   dirname(__DIR__) . '/trivya-protected');
define('CONFIG_PATH',      PROTECTED_PATH . '/config');
define('INCLUDES_PATH',    PROTECTED_PATH . '/includes');
define('DATABASE_PATH',    PROTECTED_PATH . '/database');
define('LIB_PATH',         PROTECTED_PATH . '/lib');
define('LOGS_PATH',        PROTECTED_PATH . '/logs');
define('PUBLIC_PATH',      __DIR__); // caminho absoluto do public_html (para uploads/assets)

if (!file_exists(CONFIG_PATH . '/config.php')) {
    http_response_code(500);
    die('Erro de configuração: pasta trivya-protected/ não encontrada ao lado de public_html/.');
}

// ── Carregar configurações na ordem correta ─────────────────────────────────
require_once CONFIG_PATH . '/env.php';
require_once CONFIG_PATH . '/config.php';
require_once CONFIG_PATH . '/constants.php';
require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/session.php';
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/sanitizers.php';
require_once INCLUDES_PATH . '/validators.php';
require_once INCLUDES_PATH . '/csrf.php';
require_once INCLUDES_PATH . '/auth.php';
require_once INCLUDES_PATH . '/logger.php';
require_once INCLUDES_PATH . '/mailer.php';
