<?php
/**
 * Redefinir Senha — Trivya Admin
 * Valida o token e permite definir nova senha.
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

if (isLoggedIn()) redirect(ADMIN_URL . '/dashboard.php');

$token   = sanitizeString($_GET['token'] ?? '');
$erro    = '';
$sucesso = false;
$admin   = null;

if (empty($token)) {
    redirect(ADMIN_URL . '/login.php');
}

try {
    $db    = Database::getInstance();
    $admin = $db->fetchOne(
        "SELECT id, nome FROM admins
         WHERE reset_token = :t AND reset_token_expira > NOW() AND ativo = 1
         LIMIT 1",
        [':t' => $token]
    );
} catch (Exception) {}

if (!$admin) {
    $erro = 'Este link de redefinição é inválido ou expirou. Solicite um novo.';
}

if ($admin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf('redefinir_senha', ADMIN_URL . '/redefinir-senha.php?token=' . urlencode($token));

    $novaSenha   = $_POST['senha'] ?? '';
    $confirmacao = $_POST['confirmacao'] ?? '';

    if (strlen($novaSenha) < 8) {
        $erro = 'A senha deve ter no mínimo 8 caracteres.';
    } elseif ($novaSenha !== $confirmacao) {
        $erro = 'As senhas não coincidem.';
    } else {
        $hash = password_hash($novaSenha, PASSWORD_ALGO, ['cost' => BCRYPT_COST]);
        $db->execute(
            "UPDATE admins SET senha_hash = :h, reset_token = NULL, reset_token_expira = NULL WHERE id = :id",
            [':h' => $hash, ':id' => $admin['id']]
        );

        setFlash('sucesso', 'Senha redefinida com sucesso! Faça login.');
        redirect(ADMIN_URL . '/login.php');
    }
}

$csrfToken = generateCsrf('redefinir_senha');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Nova Senha — Trivya Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(ASSETS_URL) ?>/css/admin.css">
</head>
<body>
<div class="adm-login-page">
  <div class="adm-login-box">

    <div class="adm-login-logo">
      <div class="adm-login-brand">TRIVYA</div>
      <div class="adm-login-brand-sub">Nova Senha</div>
    </div>

    <?php if ($erro && !$admin): ?>
      <div class="adm-alert adm-alert-error"><?= e($erro) ?></div>
      <p class="adm-login-divider" style="margin-top:16px;">
        <a href="<?= e(ADMIN_URL) ?>/esqueci-senha.php">Solicitar novo link</a>
      </p>

    <?php else: ?>
      <div class="adm-login-title">Criar nova senha</div>
      <div class="adm-login-sub">Olá, <?= e(explode(' ', $admin['nome'])[0]) ?>! Defina sua nova senha de acesso.</div>

      <?php if ($erro): ?>
        <div class="adm-alert adm-alert-error" style="margin:16px 0;">⚠️ <?= e($erro) ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <input type="hidden" name="<?= CSRF_FIELD_NAME ?>" value="<?= e($csrfToken) ?>">

        <div class="adm-form-group">
          <label for="senha">Nova senha <small style="color:#6B7280;">(mín. 8 caracteres)</small></label>
          <input type="password" id="senha" name="senha" required autocomplete="new-password" autofocus>
        </div>

        <div class="adm-form-group">
          <label for="confirmacao">Confirmar nova senha</label>
          <input type="password" id="confirmacao" name="confirmacao" required autocomplete="new-password">
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:14px;">
          Salvar nova senha
        </button>
      </form>
    <?php endif; ?>

  </div>
</div>
</body>
</html>

