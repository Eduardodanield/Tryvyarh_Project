<?php
declare(strict_types=1);
$tituloPaginaAdmin = 'Nichos do Letreiro';
require_once __DIR__ . '/layout/header.php';

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf('admin_nichos', ADMIN_URL . '/nichos.php');
    $acao = $_POST['_acao'] ?? '';

    if ($acao === 'excluir') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) { $db->execute("DELETE FROM nichos_marquee WHERE id=:id", [':id'=>$id]); setFlash('sucesso','Nicho excluído.'); }
    } elseif ($acao === 'toggle') {
        $id   = (int) ($_POST['id'] ?? 0);
        $ativo = (int) ($_POST['ativo'] ?? 0);
        if ($id > 0) { $db->execute("UPDATE nichos_marquee SET ativo=:a WHERE id=:id", [':a'=>$ativo,':id'=>$id]); setFlash('sucesso','Status atualizado.'); }
    } elseif ($acao === 'novo') {
        $nome = sanitizeString($_POST['nome'] ?? '');
        if ($nome) {
            $ordem = (int) $db->fetchOne("SELECT COALESCE(MAX(ordem),0)+1 AS n FROM nichos_marquee")['n'];
            $db->execute("INSERT INTO nichos_marquee (nome,ativo,ordem) VALUES (:n,1,:o)", [':n'=>$nome,':o'=>$ordem]);
            setFlash('sucesso','Nicho adicionado!');
        }
    }
    redirect(ADMIN_URL . '/nichos.php');
}

$nichos = $db->fetchAll("SELECT * FROM nichos_marquee ORDER BY ordem ASC, id ASC");
$csrf   = generateCsrf('admin_nichos');
?>

<?php foreach (getFlash('sucesso') as $m): ?>
  <div class="adm-alert adm-alert-success">✓ <?= e($m) ?></div>
<?php endforeach; ?>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;">

  <!-- Lista de nichos -->
  <div class="adm-card">
    <div class="adm-card-header">
      <span class="adm-card-title">Nichos do letreiro (<?= count($nichos) ?>)</span>
    </div>
    <div class="adm-table-wrap">
      <table class="adm-table">
        <thead><tr><th>Nome</th><th>Ordem</th><th>Status</th><th>Ações</th></tr></thead>
        <tbody>
          <?php if (empty($nichos)): ?>
            <tr><td colspan="4" style="text-align:center;color:#6B7280;padding:32px;">Nenhum nicho cadastrado.</td></tr>
          <?php else: ?>
            <?php foreach ($nichos as $n): ?>
            <tr>
              <td><?= e($n['nome']) ?></td>
              <td><?= (int) $n['ordem'] ?></td>
              <td>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="<?= CSRF_FIELD_NAME ?>" value="<?= e($csrf) ?>">
                  <input type="hidden" name="_acao" value="toggle">
                  <input type="hidden" name="id" value="<?= (int) $n['id'] ?>">
                  <input type="hidden" name="ativo" value="<?= $n['ativo'] ? 0 : 1 ?>">
                  <button type="submit" class="badge <?= $n['ativo'] ? 'badge-ativo' : 'badge-inativo' ?>"
                          style="cursor:pointer;border:none;font-family:inherit;">
                    <?= $n['ativo'] ? '● Ativo' : '○ Inativo' ?>
                  </button>
                </form>
              </td>
              <td>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="<?= CSRF_FIELD_NAME ?>" value="<?= e($csrf) ?>">
                  <input type="hidden" name="_acao" value="excluir">
                  <input type="hidden" name="id" value="<?= (int) $n['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm" data-confirm="Excluir '<?= e($n['nome']) ?>'?">🗑️</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Adicionar novo -->
  <div class="adm-card">
    <div class="adm-card-header"><span class="adm-card-title">+ Adicionar nicho</span></div>
    <form method="POST" action="">
      <input type="hidden" name="<?= CSRF_FIELD_NAME ?>" value="<?= e($csrf) ?>">
      <input type="hidden" name="_acao" value="novo">
      <div class="adm-form-group">
        <label>Nome do nicho</label>
        <input type="text" name="nome" placeholder="Ex: Logística" required maxlength="80">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Adicionar</button>
    </form>

    <div style="margin-top:16px;padding-top:16px;border-top:1px solid #E9ECEF;">
      <p style="font-size:13px;color:#6B7280;">
        💡 Os nichos ativos aparecem no letreiro animado logo abaixo do hero. Clique no status para ativar/desativar.
      </p>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
