<?php
/**
 * Login do Painel Admin — Trivya RH
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

// Já logado → ir para o dashboard
if (isLoggedIn()) {
    redirect(ADMIN_URL . '/dashboard.php');
}

$erro  = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf('admin_login', ADMIN_URL . '/login.php');

    $email = sanitizeEmail($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $erro = 'Preencha e-mail e senha.';
    } else {
        $resultado = login($email, $senha);
        if ($resultado['sucesso']) {
            redirect(ADMIN_URL . '/dashboard.php');
        } else {
            $erro = $resultado['mensagem'];
        }
    }
}

$csrfToken = generateCsrf('admin_login');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Login — Trivya Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(ASSETS_URL) ?>/css/admin.css">
  <meta name="color-scheme" content="light">
</head>
<body>
<div class="adm-login-page">
  <div class="adm-login-box">

    <div class="adm-login-logo">
      <div class="adm-login-brand">TRIVYA</div>
      <div class="adm-login-brand-sub">Painel Administrativo</div>
    </div>

    <div class="adm-login-title">Bem-vinda de volta</div>
    <div class="adm-login-sub">Acesse com seu e-mail corporativo</div>

    <?php if ($erro): ?>
      <div class="adm-alert adm-alert-error" style="margin-bottom:20px;">
        ⚠️ <?= e($erro) ?>
      </div>
    <?php endif; ?>

    <?php foreach (getFlash('sucesso') as $msg): ?>
      <div class="adm-alert adm-alert-success" style="margin-bottom:20px;">
        ✓ <?= e($msg) ?>
      </div>
    <?php endforeach; ?>

    <?php foreach (getFlash('erro') as $msg): ?>
      <div class="adm-alert adm-alert-error" style="margin-bottom:20px;">
        ⚠️ <?= e($msg) ?>
      </div>
    <?php endforeach; ?>

    <form method="POST" action="">
      <input type="hidden" name="<?= CSRF_FIELD_NAME ?>" value="<?= e($csrfToken) ?>">

      <div class="adm-form-group">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email"
               value="<?= e($email) ?>"
               placeholder="seuemail@gmail.com"
               required autocomplete="email" autofocus>
      </div>

      <div class="adm-form-group">
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha"
               placeholder="••••••••"
               required autocomplete="current-password">
        <a href="<?= e(ADMIN_URL) ?>/esqueci-senha.php" class="adm-login-link">
          Esqueci minha senha
        </a>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:14px;">
        Entrar →
      </button>
    </form>

    <p class="adm-login-divider" style="margin-top:24px;">
      <a href="<?= e(SITE_URL) ?>/public/">← Voltar ao site</a>
    </p>

  </div>
</div>
</body>
</html>

