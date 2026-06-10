<?php

/**
 * Configuração segura de sessões PHP — Trivya RH
 *
 * Aplica as melhores práticas de segurança para sessões:
 *  - Cookie HttpOnly (JavaScript não acessa o cookie de sessão)
 *  - Cookie Secure em HTTPS (não transmitido em HTTP puro)
 *  - SameSite Lax (proteção contra CSRF via cookie)
 *  - Regeneração de ID ao fazer login (prevenção de session fixation)
 *  - Tempo de vida controlado pela aplicação
 *
 * @autor    Equipe Trivya RH
 * @versao   1.0.0
 * @data     2025-01-01
 */

declare(strict_types=1);

/**
 * Configura e inicia a sessão com parâmetros de segurança.
 * Deve ser chamada antes de qualquer output.
 */
function iniciarSessao(): void
{
    // Não reiniciar sessão já ativa
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    // Detectar se a requisição chegou via HTTPS
    $isHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443) ||
        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    );

    // Configurar parâmetros do cookie ANTES de session_start()
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'domain'   => '',           // Domínio atual automaticamente
        'secure'   => $isHttps,     // Apenas HTTPS em produção
        'httponly' => true,         // Bloquear acesso via JavaScript
        'samesite' => 'Lax',        // Lax: permite GET cross-site (ex: links externos), bloqueia POST
    ]);

    // Nome de sessão personalizado (ofusca o padrão PHPSESSID)
    session_name(SESSION_NAME);

    // Usar apenas cookies para sessão (nunca URL — segurança e privacidade)
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');

    // Garantir que o garbage collector limpa sessões expiradas
    ini_set('session.gc_maxlifetime', (string) SESSION_LIFETIME);

    // Usar hash SHA-256 para o ID de sessão (mais seguro que MD5/SHA1)
    ini_set('session.sid_length', '64');
    ini_set('session.sid_bits_per_character', '6');

    session_start();

    // Validar se a sessão existente é legítima
    validarSessaoAtiva();
}

/**
 * Valida a sessão ativa verificando tempo de vida e IP.
 *
 * NOTA SOBRE O USER-AGENT: a verificação de UA foi removida intencionalmente.
 * Browsers modernos (Chrome, Edge) variam levemente o UA entre sub-requisições
 * (pre-fetch, service worker, background fetch), causando falsos positivos que
 * destruíam a sessão legítima em cada visita. A checagem de IP + tempo de vida
 * é suficiente para uso público neste projeto.
 */
function validarSessaoAtiva(): void
{
    $ip  = $_SERVER['REMOTE_ADDR'] ?? '';
    $now = time();

    // Primeira vez que essa sessão é validada — registrar impressão digital
    if (!isset($_SESSION['_ip'])) {
        $_SESSION['_ip']         = $ip;
        $_SESSION['_criado_em']  = $now;
        $_SESSION['_ultimo_uso'] = $now;
        $_SESSION['_regen_em']   = $now;
        return;
    }

    // Verificar expiração por inatividade
    if (($now - $_SESSION['_ultimo_uso']) > SESSION_LIFETIME) {
        encerrarSessao();
        return;
    }

    // Detectar mudança de IP (indica sessão sendo usada de outra rede)
    if ($_SESSION['_ip'] !== $ip) {
        error_log("[Sessão] Mudança de IP detectada. Esperado: {$_SESSION['_ip']}, recebido: {$ip}");
        encerrarSessao();
        return;
    }

    // Atualizar timestamp de último uso
    $_SESSION['_ultimo_uso'] = $now;

    // Regenerar ID periodicamente para mitigar session fixation (mesmo logado)
    if (($now - $_SESSION['_regen_em']) > SESSION_REGEN_INTERVAL) {
        session_regenerate_id(true); // true = apagar sessão antiga
        $_SESSION['_regen_em'] = $now;
    }
}

/**
 * Encerra a sessão atual de forma completa e segura.
 * Remove dados, invalida o cookie e destrói o arquivo de sessão.
 */
function encerrarSessao(): void
{
    // Limpar todos os dados da sessão
    $_SESSION = [];

    // Remover o cookie de sessão do navegador do cliente
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            [
                'expires'  => time() - 42000,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => 'Lax',
            ]
        );
    }

    // Destruir o arquivo/registro de sessão no servidor
    session_destroy();
}

// Iniciar a sessão automaticamente ao incluir este arquivo
iniciarSessao();
