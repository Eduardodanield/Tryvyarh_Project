<?php
declare(strict_types=1);
$tituloPaginaAdmin = 'Configurações Gerais';
require_once __DIR__ . '/layout/header.php';
require_once INCLUDES_PATH . '/upload.php';

$db   = Database::getInstance();
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf('admin_config', ADMIN_URL . '/configuracoes.php');

    $campos = [
        'slogan'        => sanitizeString($_POST['slogan']       ?? ''),
        'whatsapp'      => sanitizeTelefone($_POST['whatsapp']   ?? ''),
        'email_contato' => sanitizeEmail($_POST['email_contato'] ?? ''),
        'instagram_url' => sanitizeUrl($_POST['instagram_url']   ?? ''),
        'linkedin_url'  => sanitizeUrl($_POST['linkedin_url']    ?? ''),
        'endereco'      => sanitizeString($_POST['endereco']     ?? ''),
    ];

    // Upload de imagem de fundo do Hero
    if (!empty($_FILES['hero_bg']['name'])) {
        $resultadoUpload = uploadImagem($_FILES['hero_bg'], 'assets/img', 'office-bg');
        if ($resultadoUpload['sucesso']) {
            $campos['hero_bg_image'] = $resultadoUpload['caminho'];
        } else {
            $erro = $resultadoUpload['mensagem'];
        }
    }

    if (empty($erro)) {
        foreach ($campos as $chave => $valor) {
            $db->execute(
                "INSERT INTO configuracoes (chave, valor, grupo, tipo) VALUES (:k, :v, 'geral', 'texto')
                 ON DUPLICATE KEY UPDATE valor = :v2",
                [':k' => $chave, ':v' => $valor, ':v2' => $valor]
            );
        }
        Logger::info('Configurações atualizadas', [], 'admin');
        setFlash('sucesso', 'Configurações salvas com sucesso!');
        redirect(ADMIN_URL . '/configuracoes.php');
    }
}

// Carregar valores atuais
$cfg = [];
$rows = $db->fetchAll("SELECT chave, valor FROM configuracoes");
foreach ($rows as $r) $cfg[$r['chave']] = $r['valor'];

$csrfToken = generateCsrf('admin_config');
?>

<?php foreach (getFlash('sucesso') as $msg): ?>
  <div class="adm-alert adm-alert-success">✓ <?= e($msg) ?></div>
<?php endforeach; ?>
<?php if ($erro): ?>
  <div class="adm-alert adm-alert-error">⚠️ <?= e($erro) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" action="">
  <input type="hidden" name="<?= CSRF_FIELD_NAME ?>" value="<?= e($csrfToken) ?>">

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

    <!-- Identidade -->
    <div class="adm-card">
      <div class="adm-card-header"><span class="adm-card-title">🏷️ Identidade</span></div>

      <div class="adm-form-group">
        <label>Slogan / subtítulo do Hero</label>
        <input type="text" name="slogan" value="<?= e($cfg['slogan'] ?? '') ?>" placeholder="Recrutamento & Seleção humanizado...">
      </div>
      <div class="adm-form-group">
        <label>Endereço / cidade</label>
        <input type="text" name="endereco" value="<?= e($cfg['endereco'] ?? 'São Paulo, SP') ?>">
      </div>
    </div>

    <!-- Contato -->
    <div class="adm-card">
      <div class="adm-card-header"><span class="adm-card-title">📞 Contato</span></div>

      <div class="adm-form-group">
        <label>WhatsApp <small style="color:#6B7280;">(apenas dígitos, ex: 5511999999999)</small></label>
        <input type="text" name="whatsapp" value="<?= e($cfg['whatsapp'] ?? '') ?>" placeholder="5511999999999">
      </div>
      <div class="adm-form-group">
        <label>E-mail de contato</label>
        <input type="email" name="email_contato" value="<?= e($cfg['email_contato'] ?? '') ?>" placeholder="contato@trivyarh.com.br">
      </div>
    </div>

    <!-- Redes Sociais -->
    <div class="adm-card">
      <div class="adm-card-header"><span class="adm-card-title">📱 Redes Sociais</span></div>

      <div class="adm-form-group">
        <label>Instagram URL</label>
        <input type="url" name="instagram_url" value="<?= e($cfg['instagram_url'] ?? '') ?>" placeholder="https://instagram.com/...">
      </div>
      <div class="adm-form-group">
        <label>LinkedIn URL</label>
        <input type="url" name="linkedin_url" value="<?= e($cfg['linkedin_url'] ?? '') ?>" placeholder="https://linkedin.com/...">
      </div>
    </div>

    <!-- Imagem de Fundo -->
    <div class="adm-card">
      <div class="adm-card-header"><span class="adm-card-title">🖼️ Imagem de Fundo do Hero</span></div>

      <?php $imgAtual = $cfg['hero_bg_image'] ?? 'assets/img/office-bg.png'; ?>
      <div style="margin-bottom:14px;">
        <img src="<?= e(SITE_URL . '/' . $imgAtual) ?>" alt="Imagem atual"
             style="width:100%;height:140px;object-fit:cover;border-radius:8px;border:1px solid #E9ECEF;">
        <p class="adm-form-hint">Imagem atual: <?= e(basename($imgAtual)) ?></p>
      </div>
      <div class="adm-form-group">
        <label>Substituir imagem <small style="color:#6B7280;">(JPG/PNG, máx. 5MB)</small></label>
        <input type="file" name="hero_bg" accept="image/jpeg,image/png,image/webp">
      </div>
    </div>

  </div>

  <div style="margin-top:20px;display:flex;justify-content:flex-end;">
    <button type="submit" class="btn btn-primary">💾 Salvar configurações</button>
  </div>
</form>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
