<?php
/**
 * Esqueci minha senha — Trivya Admin
 * Envia link de redefinição para o e-mail cadastrado.
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

if (isLoggedIn()) redirect(ADMIN_URL . '/dashboard.php');

$mensagem = '';
$tipo     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf('esqueci_senha', ADMIN_URL . '/esqueci-senha.php');

    $email = sanitizeEmail($_POST['email'] ?? '');

    if (empty($email)) {
        $mensagem = 'Informe seu e-mail.';
        $tipo     = 'erro';
    } else {
        $db    = Database::getInstance();
        $admin = $db->fetchOne(
            "SELECT id, nome FROM admins WHERE email = :e AND ativo = 1 LIMIT 1",
            [':e' => $email]
        );

        // Sempre mostrar a mesma mensagem (evitar enumeração de usuários)
        $mensagem = 'Se esse e-mail estiver cadastrado, você receberá as instruções em instantes.';
        $tipo     = 'sucesso';

        if ($admin) {
            $token   = bin2hex(random_bytes(32));
            $expira  = date('Y-m-d H:i:s', time() + 3600); // 1 hora

            $db->execute(
                "UPDATE admins SET reset_token = :t, reset_token_expira = :e WHERE id = :id",
                [':t' => $token, ':e' => $expira, ':id' => $admin['id']]
            );

            $link = ADMIN_URL . '/redefinir-senha.php?token=' . urlencode($token);
            $corpo = templateEmail(
                'Redefinição de Senha',
                "<p>Olá, <strong>" . e($admin['nome']) . "</strong>!</p>
                 <p>Recebemos uma solicitação para redefinir a senha do seu acesso ao painel da Trivya RH.</p>
                 <p><a href='{$link}' class='btn'>Redefinir minha senha</a></p>
                 <p style='font-size:13px;color:#666;'>Este link é válido por <strong>1 hora</strong>. Se você não fez essa solicitação, ignore este e-mail.</p>"
            );

            sendEmail($email, $admin['nome'], '[Trivya RH] Redefinição de senha', $corpo);
            Logger::info('Solicitação de reset de senha', ['email' => $email], 'auth');
        }
    }
}

$csrfToken = generateCsrf('esqueci_senha');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Esqueci minha senha — Trivya Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(ASSETS_URL) ?>/css/admin.css">
</head>
<body>
<div class="adm-login-page">
  <div class="adm-login-box">

    <div class="adm-login-logo">
      <div class="adm-login-brand">TRIVYA</div>
      <div class="adm-login-brand-sub">Recuperação de Acesso</div>
    </div>

    <div class="adm-login-title">Esqueceu a senha?</div>
    <div class="adm-login-sub">Informe seu e-mail e enviaremos um link de redefinição.</div>

    <?php if ($mensagem): ?>
      <div class="adm-alert adm-alert-<?= $tipo === 'sucesso' ? 'success' : 'error' ?>" style="margin:16px 0;">
        <?= $tipo === 'sucesso' ? '✓' : '⚠️' ?> <?= e($mensagem) ?>
      </div>
    <?php endif; ?>

    <?php if ($tipo !== 'sucesso'): ?>
    <form method="POST" action="">
      <input type="hidden" name="<?= CSRF_FIELD_NAME ?>" value="<?= e($csrfToken) ?>">

      <div class="adm-form-group">
        <label for="email">Seu e-mail</label>
        <input type="email" id="email" name="email"
               placeholder="seuemail@gmail.com"
               required autocomplete="email" autofocus>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:14px;">
        Enviar link de redefinição
      </button>
    </form>
    <?php endif; ?>

    <p class="adm-login-divider" style="margin-top:20px;">
      <a href="<?= e(ADMIN_URL) ?>/login.php">← Voltar ao login</a>
    </p>

  </div>
</div>
</body>
</html>

