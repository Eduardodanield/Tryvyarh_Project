<?php

/**
 * Autenticação de administradores — Trivya RH
 *
 * Gerencia login/logout, verificação de sessão autenticada,
 * controle de acesso por papel (role) e proteção contra
 * força bruta por IP.
 *
 * @autor    Equipe Trivya RH
 * @versao   1.0.0
 * @data     2025-01-01
 */

declare(strict_types=1);

/**
 * Tenta autenticar um administrador com e-mail e senha.
 *
 * Registra toda tentativa (sucesso ou falha) na tabela login_attempts.
 *
 * @param string $email  E-mail informado
 * @param string $senha  Senha em texto puro informada
 * @return array{sucesso: bool, mensagem: string, admin?: array}
 */
function login(string $email, string $senha): array
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $db = Database::getInstance();

    // Verificar bloqueio de IP antes de qualquer consulta
    if (isIpBlocked($ip)) {
        return [
            'sucesso'  => false,
            'mensagem' => 'Muitas tentativas incorretas. Tente novamente em 1 hora.',
        ];
    }

    // Buscar admin pelo e-mail (apenas ativos)
    $admin = $db->fetchOne(
        "SELECT id, nome, email, senha_hash, role, ativo
         FROM admins
         WHERE email = :email
         LIMIT 1",
        [':email' => $email]
    );

    // Admin não encontrado ou inativo
    if ($admin === null || !$admin['ativo']) {
        recordLoginAttempt($ip, $email, false, 'admin_nao_encontrado');
        return [
            'sucesso'  => false,
            'mensagem' => 'E-mail ou senha incorretos.',
        ];
    }

    // Verificar senha com bcrypt (hash_equals internamente via password_verify)
    if (!password_verify($senha, $admin['senha_hash'])) {
        recordLoginAttempt($ip, $email, false, 'senha_incorreta');
        return [
            'sucesso'  => false,
            'mensagem' => 'E-mail ou senha incorretos.',
        ];
    }

    // Verificar se o hash precisa ser atualizado (custo bcrypt mudou)
    if (password_needs_rehash($admin['senha_hash'], PASSWORD_ALGO, ['cost' => BCRYPT_COST])) {
        $novoHash = password_hash($senha, PASSWORD_ALGO, ['cost' => BCRYPT_COST]);
        $db->execute(
            "UPDATE admins SET senha_hash = :hash WHERE id = :id",
            [':hash' => $novoHash, ':id' => $admin['id']]
        );
    }

    // Login bem-sucedido — regenerar ID de sessão (previne session fixation)
    session_regenerate_id(true);

    // Armazenar dados do admin na sessão
    $_SESSION['admin_id']    = $admin['id'];
    $_SESSION['admin_nome']  = $admin['nome'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_role']  = $admin['role'];
    $_SESSION['login_em']    = time();
    $_SESSION['_regen_em']   = time();

    // Registrar tentativa bem-sucedida
    recordLoginAttempt($ip, $email, true, 'sucesso');

    // Atualizar último acesso do admin
    $db->execute(
        "UPDATE admins SET ultimo_acesso = NOW() WHERE id = :id",
        [':id' => $admin['id']]
    );

    return [
        'sucesso'  => true,
        'mensagem' => 'Login realizado com sucesso.',
        'admin'    => $admin,
    ];
}

/**
 * Encerra a sessão do administrador atual.
 */
function logout(): void
{
    // Registrar logout no log
    if (isLoggedIn()) {
        error_log("[Auth] Admin ID " . $_SESSION['admin_id'] . " fez logout. IP: " . ($_SERVER['REMOTE_ADDR'] ?? '-'));
    }

    encerrarSessao(); // Definida em config/session.php
}

/**
 * Verifica se há um administrador autenticado na sessão atual.
 *
 * @return bool true se autenticado e sessão válida
 */
function isLoggedIn(): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return false;
    }

    if (!isset($_SESSION['admin_id'], $_SESSION['login_em'])) {
        return false;
    }

    // Verificar se a sessão não expirou
    if ((time() - $_SESSION['login_em']) > SESSION_LIFETIME) {
        encerrarSessao();
        return false;
    }

    return true;
}

/**
 * Exige autenticação. Redireciona para o login se não autenticado.
 *
 * @param string|null $roleMinimo Role mínimo exigido (null = qualquer admin)
 */
function requireAuth(?string $roleMinimo = null): void
{
    if (!isLoggedIn()) {
        setFlash('aviso', 'Você precisa fazer login para acessar esta área.');
        redirect(ADMIN_URL . '/login.php');
    }

    // Verificar hierarquia de roles
    if ($roleMinimo !== null && !temPermissao($roleMinimo)) {
        setFlash('erro', 'Você não tem permissão para acessar esta página.');
        redirect(ADMIN_URL . '/dashboard.php');
    }
}

/**
 * Verifica se o admin atual tem o role mínimo exigido.
 *
 * Hierarquia: super_admin > admin > editor
 *
 * @param string $roleMinimo Role mínimo necessário
 * @return bool
 */
function temPermissao(string $roleMinimo): bool
{
    if (!isLoggedIn()) {
        return false;
    }

    $hierarquia = ['editor' => 1, 'admin' => 2, 'super_admin' => 3];

    $rolAtual   = $hierarquia[$_SESSION['admin_role']] ?? 0;
    $rolMinimo  = $hierarquia[$roleMinimo] ?? 999;

    return $rolAtual >= $rolMinimo;
}

/**
 * Retorna o array com os dados do administrador logado.
 *
 * @return array|null null se não autenticado
 */
function getCurrentAdmin(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'id'    => $_SESSION['admin_id'],
        'nome'  => $_SESSION['admin_nome'],
        'email' => $_SESSION['admin_email'],
        'role'  => $_SESSION['admin_role'],
    ];
}

/**
 * Registra uma tentativa de login na tabela login_attempts.
 *
 * @param string $ip       Endereço IP do cliente
 * @param string $email    E-mail tentado
 * @param bool   $sucesso  true = login bem-sucedido
 * @param string $motivo   Motivo da falha (para auditoria)
 */
function recordLoginAttempt(string $ip, string $email, bool $sucesso, string $motivo = ''): void
{
    try {
        $db = Database::getInstance();
        $db->execute(
            "INSERT INTO login_attempts (ip, email, sucesso, motivo, tentado_em)
             VALUES (:ip, :email, :sucesso, :motivo, NOW())",
            [
                ':ip'      => $ip,
                ':email'   => $email,
                ':sucesso' => $sucesso ? 1 : 0,
                ':motivo'  => $motivo,
            ]
        );
    } catch (Exception $e) {
        // Não deixar falha no registro bloquear o fluxo de login
        error_log("[Auth] Falha ao registrar tentativa de login: " . $e->getMessage());
    }
}

/**
 * Verifica se um IP está bloqueado por excesso de tentativas falhas.
 *
 * Um IP é bloqueado quando ultrapassa LOGIN_MAX_ATTEMPTS tentativas
 * falhas na janela de tempo LOGIN_WINDOW_SECONDS.
 *
 * @param string $ip Endereço IP a verificar
 * @return bool true se bloqueado
 */
function isIpBlocked(string $ip): bool
{
    try {
        $db = Database::getInstance();

        $resultado = $db->fetchOne(
            "SELECT COUNT(*) AS tentativas
             FROM login_attempts
             WHERE ip = :ip
               AND sucesso = 0
               AND tentado_em >= DATE_SUB(NOW(), INTERVAL :janela SECOND)",
            [
                ':ip'     => $ip,
                ':janela' => LOGIN_WINDOW_SECONDS,
            ]
        );

        return ($resultado['tentativas'] ?? 0) >= LOGIN_MAX_ATTEMPTS;
    } catch (Exception $e) {
        error_log("[Auth] Falha ao verificar bloqueio de IP: " . $e->getMessage());
        // Em caso de erro, não bloquear (fail open) para evitar DoS
        return false;
    }
}

/**
 * Retorna o número de tentativas restantes antes do bloqueio.
 *
 * @param string $ip Endereço IP
 * @return int Tentativas restantes (0 = bloqueado)
 */
function tentativasRestantes(string $ip): int
{
    try {
        $db = Database::getInstance();

        $resultado = $db->fetchOne(
            "SELECT COUNT(*) AS tentativas
             FROM login_attempts
             WHERE ip = :ip
               AND sucesso = 0
               AND tentado_em >= DATE_SUB(NOW(), INTERVAL :janela SECOND)",
            [
                ':ip'     => $ip,
                ':janela' => LOGIN_WINDOW_SECONDS,
            ]
        );

        $usadas = (int) ($resultado['tentativas'] ?? 0);
        return max(0, LOGIN_MAX_ATTEMPTS - $usadas);
    } catch (Exception) {
        return LOGIN_MAX_ATTEMPTS;
    }
}

/**
 * Altera a senha do administrador logado.
 *
 * @param string $senhaAtual  Senha atual (para confirmação)
 * @param string $novaSenha   Nova senha
 * @return array{sucesso: bool, mensagem: string}
 */
function alterarSenha(string $senhaAtual, string $novaSenha): array
{
    if (!isLoggedIn()) {
        return ['sucesso' => false, 'mensagem' => 'Não autenticado.'];
    }

    $db    = Database::getInstance();
    $admin = $db->fetchOne(
        "SELECT senha_hash FROM admins WHERE id = :id",
        [':id' => $_SESSION['admin_id']]
    );

    if (!$admin || !password_verify($senhaAtual, $admin['senha_hash'])) {
        return ['sucesso' => false, 'mensagem' => 'Senha atual incorreta.'];
    }

    if (mb_strlen($novaSenha, 'UTF-8') < 8) {
        return ['sucesso' => false, 'mensagem' => 'A nova senha deve ter no mínimo 8 caracteres.'];
    }

    $novoHash = password_hash($novaSenha, PASSWORD_ALGO, ['cost' => BCRYPT_COST]);
    $db->execute(
        "UPDATE admins SET senha_hash = :hash WHERE id = :id",
        [':hash' => $novoHash, ':id' => $_SESSION['admin_id']]
    );

    return ['sucesso' => true, 'mensagem' => 'Senha alterada com sucesso.'];
}
