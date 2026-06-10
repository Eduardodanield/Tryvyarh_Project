<?php
declare(strict_types=1);
$id = (int) ($_GET['id'] ?? 0);
$tituloPaginaAdmin = $id ? 'Editar Serviço' : 'Novo Serviço';
require_once __DIR__ . '/layout/header.php';

$db   = Database::getInstance();
$erro = '';
$reg  = ['titulo' => '', 'descricao' => '', 'icone' => '🏢', 'link' => '', 'ordem' => 0, 'ativo' => 1];

if ($id) {
    $reg = $db->fetchOne("SELECT * FROM servicos WHERE id = :id", [':id' => $id]) ?? $reg;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf('admin_servicos_form', ADMIN_URL . '/servicos-form.php');

    $dados = [
        'titulo'    => sanitizeString($_POST['titulo']    ?? ''),
        'descricao' => sanitizeTexto($_POST['descricao']  ?? ''),
        'icone'     => sanitizeString($_POST['icone']     ?? '🏢', 10),
        'link'      => sanitizeUrl($_POST['link']         ?? ''),
        'ordem'     => (int) ($_POST['ordem']             ?? 0),
        'ativo'     => isset($_POST['ativo']) ? 1 : 0,
    ];

    $erroTitulo = validarObrigatorio($dados['titulo'], 'Título');
    if ($erroTitulo) {
        $erro = $erroTitulo;
        $reg  = array_merge($reg, $dados);
    } else {
        if ($id) {
            $db->execute(
                "UPDATE servicos SET titulo=:t, descricao=:d, icone=:i, link=:l, ordem=:o, ativo=:a WHERE id=:id",
                [':t'=>$dados['titulo'],':d'=>$dados['descricao'],':i'=>$dados['icone'],
                 ':l'=>$dados['link'],':o'=>$dados['ordem'],':a'=>$dados['ativo'],':id'=>$id]
            );
        } else {
            $db->execute(
                "INSERT INTO servicos (titulo,descricao,icone,link,ordem,ativo) VALUES (:t,:d,:i,:l,:o,:a)",
                [':t'=>$dados['titulo'],':d'=>$dados['descricao'],':i'=>$dados['icone'],
                 ':l'=>$dados['link'],':o'=>$dados['ordem'],':a'=>$dados['ativo']]
            );
        }
        setFlash('sucesso', $id ? 'Serviço atualizado!' : 'Serviço criado!');
        redirect(ADMIN_URL . '/servicos.php');
    }
}

$csrf = generateCsrf('admin_servicos_form');
?>

<?php if ($erro): ?><div class="adm-alert adm-alert-error">⚠️ <?= e($erro) ?></div><?php endif; ?>

<div class="adm-card" style="max-width:680px;">
  <div class="adm-card-header">
    <span class="adm-card-title"><?= $id ? '✏️ Editar' : '➕ Novo' ?> Serviço</span>
    <a href="servicos.php" class="btn btn-outline btn-sm">← Voltar</a>
  </div>

  <form method="POST" action="">
    <input type="hidden" name="<?= CSRF_FIELD_NAME ?>" value="<?= e($csrf) ?>">

    <div class="adm-form-row">
      <div class="adm-form-group">
        <label>Título *</label>
        <input type="text" name="titulo" value="<?= e($reg['titulo']) ?>" required placeholder="Ex: Varejo">
      </div>
      <div class="adm-form-group">
        <label>Ícone (emoji)</label>
        <input type="text" name="icone" value="<?= e($reg['icone']) ?>" placeholder="🛒" maxlength="10"
               style="font-size:22px;text-align:center;">
        <p class="adm-form-hint">Cole um emoji diretamente no campo</p>
      </div>
    </div>

    <div class="adm-form-group">
      <label>Descrição</label>
      <textarea name="descricao" rows="4" placeholder="Descreva o serviço..."><?= e($reg['descricao']) ?></textarea>
    </div>

    <div class="adm-form-row">
      <div class="adm-form-group">
        <label>Link (opcional)</label>
        <input type="url" name="link" value="<?= e($reg['link']) ?>" placeholder="https://...">
      </div>
      <div class="adm-form-group">
        <label>Ordem de exibição</label>
        <input type="number" name="ordem" value="<?= (int) $reg['ordem'] ?>" min="0" max="999">
        <p class="adm-form-hint">Número menor = aparece primeiro</p>
      </div>
    </div>

    <div class="adm-form-group">
      <label class="adm-form-check">
        <input type="checkbox" name="ativo" value="1" <?= $reg['ativo'] ? 'checked' : '' ?>>
        Serviço ativo (visível no site)
      </label>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:8px;">
      <a href="servicos.php" class="btn btn-outline">Cancelar</a>
      <button type="submit" class="btn btn-primary">💾 Salvar</button>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
