<?php

/**
 * Proteção contra CSRF (Cross-Site Request Forgery) — Trivya RH
 *
 * Usa tokens de uso único vinculados à sessão.
 * Cada formulário recebe um token diferente.
 * A comparação usa hash_equals() para evitar timing attacks.
 *
 * USO NOS FORMULÁRIOS:
 *   echo '<input type="hidden" name="' . CSRF_FIELD_NAME . '" value="' . generateCsrf('formulario_contato') . '">';
 *
 * USO NA VALIDAÇÃO:
 *   if (!validateCsrf('formulario_contato', $_POST[CSRF_FIELD_NAME] ?? '')) {
 *       // Token inválido — possível ataque CSRF
 *   }
 *
 * @autor    Equipe Trivya RH
 * @versao   1.0.0
 * @data     2025-01-01
 */

declare(strict_types=1);

/**
 * Gera (ou reutiliza) um token CSRF para uma ação específica.
 *
 * O token é armazenado na sessão com um timestamp de expiração.
 * Se já existir um token válido para a ação, ele é reutilizado.
 *
 * @param string $acao   Identificador da ação/formulário (ex: 'login', 'contato')
 * @return string Token hexadecimal de 64 caracteres
 */
function generateCsrf(string $acao = 'global'): string
{
    $chaveSession = '_csrf_' . $acao;

    // Reutilizar token existente e não expirado
    if (
        isset($_SESSION[$chaveSession]['token'], $_SESSION[$chaveSession]['expira']) &&
        $_SESSION[$chaveSession]['expira'] > time()
    ) {
        return $_SESSION[$chaveSession]['token'];
    }

    // Gerar novo token criptograficamente seguro (32 bytes = 64 hex chars)
    $token = bin2hex(random_bytes(32));

    $_SESSION[$chaveSession] = [
        'token'  => $token,
        'expira' => time() + CSRF_TOKEN_LIFETIME,
        'acao'   => $acao,
    ];

    return $token;
}

/**
 * Valida um token CSRF recebido de um formulário.
 *
 * Usa hash_equals() para comparação em tempo constante,
 * prevenindo timing attacks que poderiam deduzir o token correto.
 *
 * Após validação bem-sucedida, o token é invalidado (uso único)
 * e um novo token é gerado automaticamente para o próximo submit.
 *
 * @param string $acao          Identificador da ação/formulário
 * @param string $tokenRecebido Token enviado pelo formulário
 * @return bool true se válido, false se inválido ou expirado
 */
function validateCsrf(string $acao = 'global', string $tokenRecebido = ''): bool
{
    $chaveSession = '_csrf_' . $acao;

    // Verificar se existe token na sessão
    if (!isset($_SESSION[$chaveSession]['token'])) {
        error_log("[CSRF] Token não encontrado na sessão para ação: {$acao}");
        return false;
    }

    $dadosToken = $_SESSION[$chaveSession];

    // Verificar expiração
    if ($dadosToken['expira'] <= time()) {
        unset($_SESSION[$chaveSession]);
        error_log("[CSRF] Token expirado para ação: {$acao}");
        return false;
    }

    // Comparação em tempo constante (evita timing attack)
    $valido = hash_equals($dadosToken['token'], $tokenRecebido);

    if (!$valido) {
        error_log("[CSRF] Token inválido para ação: {$acao}. IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'desconhecido'));
    }

    // Invalidar token após uso (mesmo que inválido, gerar novo)
    // Isso força o formulário a sempre buscar um token fresco
    unset($_SESSION[$chaveSession]);

    return $valido;
}

/**
 * Helper: retorna o campo HTML hidden com o token CSRF.
 *
 * Uso direto no template:
 *   <?= csrfField('login') ?>
 *
 * @param string $acao Identificador da ação/formulário
 * @return string HTML do campo hidden
 */
function csrfField(string $acao = 'global'): string
{
    $token = generateCsrf($acao);
    $campo = htmlspecialchars(CSRF_FIELD_NAME, ENT_QUOTES, 'UTF-8');
    $valor = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
    return "<input type=\"hidden\" name=\"{$campo}\" value=\"{$valor}\">";
}

/**
 * Valida o CSRF de uma requisição POST e redireciona em caso de falha.
 *
 * Atalho para o padrão mais comum de uso nos controllers.
 * Chama exit() internamente via redirect().
 *
 * @param string $acao        Identificador da ação/formulário
 * @param string $urlErro     URL para redirecionar em caso de falha
 * @param string $mensagemErro Mensagem flash a exibir ao usuário
 */
function verificarCsrf(string $acao, string $urlErro = '/', string $mensagemErro = 'Token de segurança inválido. Tente novamente.'): void
{
    $token = $_POST[CSRF_FIELD_NAME] ?? '';

    if (!validateCsrf($acao, $token)) {
        setFlash('erro', $mensagemErro);
        redirect($urlErro);
    }
}
