<?php
declare(strict_types=1);
$tituloPaginaAdmin = 'Serviços';
require_once __DIR__ . '/layout/header.php';

$db = Database::getInstance();

// Ação de exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_acao'] ?? '') === 'excluir') {
    verificarCsrf('admin_servicos', ADMIN_URL . '/servicos.php');
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $db->execute("DELETE FROM servicos WHERE id = :id", [':id' => $id]);
        setFlash('sucesso', 'Serviço excluído.');
    }
    redirect(ADMIN_URL . '/servicos.php');
}

$servicos = $db->fetchAll("SELECT * FROM servicos ORDER BY ordem ASC, id ASC");
$csrf     = generateCsrf('admin_servicos');
?>

<?php foreach (getFlash('sucesso') as $m): ?>
  <div class="adm-alert adm-alert-success">✓ <?= e($m) ?></div>
<?php endforeach; ?>

<div class="adm-card">
  <div class="adm-card-header">
    <span class="adm-card-title">Serviços cadastrados (<?= count($servicos) ?>)</span>
    <a href="servicos-form.php" class="btn btn-primary btn-sm">+ Novo serviço</a>
  </div>

  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr>
          <th>Ícone</th><th>Título</th><th>Ordem</th><th>Status</th><th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($servicos)): ?>
          <tr><td colspan="5" style="text-align:center;color:#6B7280;padding:32px;">Nenhum serviço cadastrado.</td></tr>
        <?php else: ?>
          <?php foreach ($servicos as $s): ?>
          <tr>
            <td style="font-size:24px;"><?= e($s['icone']) ?></td>
            <td><strong><?= e($s['titulo']) ?></strong>
                <br><small style="color:#6B7280;"><?= e(mb_substr($s['descricao'] ?? '', 0, 60, 'UTF-8')) ?>...</small></td>
            <td><?= (int) $s['ordem'] ?></td>
            <td>
              <span class="badge <?= $s['ativo'] ? 'badge-ativo' : 'badge-inativo' ?>">
                <?= $s['ativo'] ? '● Ativo' : '○ Inativo' ?>
              </span>
            </td>
            <td>
              <a href="servicos-form.php?id=<?= (int) $s['id'] ?>" class="btn btn-outline btn-sm">✏️ Editar</a>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="<?= CSRF_FIELD_NAME ?>" value="<?= e($csrf) ?>">
                <input type="hidden" name="_acao" value="excluir">
                <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm"
                        data-confirm="Excluir o serviço '<?= e($s['titulo']) ?>'?">🗑️ Excluir</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
