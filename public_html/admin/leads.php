<?php
declare(strict_types=1);
$tituloPaginaAdmin = 'Leads de Empresas';
require_once __DIR__ . '/layout/header.php';

$db = Database::getInstance();

// Excluir lead
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_acao'] ?? '') === 'excluir') {
    verificarCsrf('admin_leads', ADMIN_URL . '/leads.php');
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $db->execute("DELETE FROM leads WHERE id = :id", [':id' => $id]);
        setFlash('sucesso', 'Lead excluído.');
    }
    redirect(ADMIN_URL . '/leads.php');
}

// Filtros
$filtroUrgencia  = $_GET['urgencia']  ?? '';
$filtroSegmento  = $_GET['segmento']  ?? '';
$filtroQtd       = $_GET['qtd_vagas'] ?? '';

$where  = '1=1';
$params = [];

if ($filtroUrgencia !== '') {
    $where .= ' AND urgencia = :urgencia';
    $params[':urgencia'] = (int) $filtroUrgencia;
}
if ($filtroSegmento !== '') {
    $where .= ' AND segmento = :segmento';
    $params[':segmento'] = $filtroSegmento;
}
if ($filtroQtd !== '') {
    $where .= ' AND qtd_vagas = :qtd';
    $params[':qtd'] = $filtroQtd;
}

$leads = $db->fetchAll(
    "SELECT * FROM leads WHERE {$where} ORDER BY criado_em DESC",
    $params
);
$csrf = generateCsrf('admin_leads');

$segmentosLabel = [
    'varejo'          => 'Varejo',
    'facilities'      => 'Facilities',
    'construcao_civil'=> 'Construção Civil',
    'construcao'      => 'Construção Civil',
    'outro'           => 'Outro',
];
?>

<?php foreach (getFlash('sucesso') as $m): ?>
  <div class="adm-alert adm-alert-success">✓ <?= e($m) ?></div>
<?php endforeach; ?>

<!-- Filtros -->
<div class="adm-card" style="margin-bottom:16px;">
  <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;padding:4px 0;">
    <div>
      <label style="font-size:12px;font-weight:600;color:#6B7280;display:block;margin-bottom:4px;">Urgência</label>
      <select name="urgencia" style="padding:8px 12px;border:1.5px solid #E9ECEF;border-radius:6px;font-size:13px;">
        <option value="">Todas</option>
        <option value="1" <?= $filtroUrgencia === '1' ? 'selected' : '' ?>>Urgente ⚡</option>
        <option value="0" <?= $filtroUrgencia === '0' ? 'selected' : '' ?>>Sem urgência</option>
      </select>
    </div>
    <div>
      <label style="font-size:12px;font-weight:600;color:#6B7280;display:block;margin-bottom:4px;">Segmento</label>
      <select name="segmento" style="padding:8px 12px;border:1.5px solid #E9ECEF;border-radius:6px;font-size:13px;">
        <option value="">Todos</option>
        <option value="varejo" <?= $filtroSegmento === 'varejo' ? 'selected' : '' ?>>Varejo</option>
        <option value="facilities" <?= $filtroSegmento === 'facilities' ? 'selected' : '' ?>>Facilities</option>
        <option value="construcao_civil" <?= $filtroSegmento === 'construcao_civil' ? 'selected' : '' ?>>Construção Civil</option>
        <option value="outro" <?= $filtroSegmento === 'outro' ? 'selected' : '' ?>>Outro</option>
      </select>
    </div>
    <div>
      <label style="font-size:12px;font-weight:600;color:#6B7280;display:block;margin-bottom:4px;">Qtd. Vagas</label>
      <select name="qtd_vagas" style="padding:8px 12px;border:1.5px solid #E9ECEF;border-radius:6px;font-size:13px;">
        <option value="">Todas</option>
        <option value="1" <?= $filtroQtd === '1' ? 'selected' : '' ?>>1 vaga</option>
        <option value="2-5" <?= $filtroQtd === '2-5' ? 'selected' : '' ?>>2 a 5 vagas</option>
        <option value="5-10" <?= $filtroQtd === '5-10' ? 'selected' : '' ?>>5 a 10 vagas</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
    <?php if ($filtroUrgencia !== '' || $filtroSegmento !== '' || $filtroQtd !== ''): ?>
      <a href="leads.php" class="btn btn-outline btn-sm">Limpar</a>
    <?php endif; ?>
  </form>
</div>

<div class="adm-card">
  <div class="adm-card-header">
    <span class="adm-card-title">Leads de Empresas (<?= count($leads) ?>)</span>
  </div>
  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr>
          <th>Empresa / Responsável</th>
          <th>Contato</th>
          <th>Cidade/Estado</th>
          <th>Cargo/Área</th>
          <th>Segmento</th>
          <th>Vagas</th>
          <th>Urgência</th>
          <th>Data</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($leads)): ?>
          <tr><td colspan="9" style="text-align:center;color:#6B7280;padding:32px;">Nenhum lead encontrado.</td></tr>
        <?php else: ?>
          <?php foreach ($leads as $lead): ?>
          <tr>
            <td>
              <strong><?= e($lead['empresa'] ?? '—') ?></strong>
              <br><small style="color:#6B7280;"><?= e($lead['nome'] ?? '') ?></small>
            </td>
            <td>
              <div><?= e($lead['telefone'] ?? '—') ?></div>
              <small style="color:#6B7280;"><?= e($lead['email'] ?? '') ?></small>
            </td>
            <td><?= e($lead['cidade_estado'] ?? '—') ?></td>
            <td><?= e(mb_substr($lead['cargo_area_contratar'] ?? '—', 0, 40, 'UTF-8')) ?></td>
            <td>
              <?php $seg = $lead['segmento'] ?? ''; ?>
              <?php if ($seg): ?>
                <span class="badge badge-ativo" style="background:#E8FAFB;color:#0ECAD4;">
                  <?= e($segmentosLabel[$seg] ?? $seg) ?>
                </span>
              <?php else: ?>
                <span style="color:#D1D5DB;">—</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!empty($lead['qtd_vagas'])): ?>
                <span class="badge badge-ativo"><?= e($lead['qtd_vagas']) ?></span>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td style="text-align:center;">
              <?php if ($lead['urgencia'] === null || $lead['urgencia'] === ''): ?>
                <span style="color:#D1D5DB;">—</span>
              <?php elseif ($lead['urgencia'] == 1): ?>
                <span title="Urgente" style="color:#F59E0B;font-weight:700;">⚡ Sim</span>
              <?php else: ?>
                <span style="color:#6B7280;font-size:12px;">Não</span>
              <?php endif; ?>
            </td>
            <td style="white-space:nowrap;">
              <small><?= date('d/m/Y', strtotime($lead['criado_em'])) ?></small>
              <br><small style="color:#6B7280;"><?= date('H:i', strtotime($lead['criado_em'])) ?></small>
            </td>
            <td>
              <button type="button"
                      class="btn btn-outline btn-sm"
                      onclick="document.getElementById('lead-detail-<?= (int)$lead['id'] ?>').classList.toggle('hidden')"
                      style="margin-bottom:4px;">
                👁️ Ver detalhes
              </button>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="<?= CSRF_FIELD_NAME ?>" value="<?= e($csrf) ?>">
                <input type="hidden" name="_acao" value="excluir">
                <input type="hidden" name="id"    value="<?= (int) $lead['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm"
                        data-confirm="Excluir lead de '<?= e($lead['empresa'] ?? '') ?>'?">🗑️</button>
              </form>
              <!-- Painel de detalhe expandível -->
              <div id="lead-detail-<?= (int)$lead['id'] ?>" class="hidden"
                   style="margin-top:12px;background:#F8F9FA;border-radius:8px;padding:12px;font-size:13px;border:1px solid #E9ECEF;">
                <strong>WhatsApp aceito:</strong> <?= $lead['aceita_contato_whatsapp'] ? 'Sim ✅' : 'Não' ?><br>
                <?php if (!empty($lead['mensagem'])): ?>
                  <strong>Dificuldade:</strong><br>
                  <p style="margin:4px 0 8px;color:#4A5568;"><?= e($lead['mensagem']) ?></p>
                <?php endif; ?>
                <strong>IP:</strong> <?= e($lead['ip'] ?? '—') ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<style>
.hidden { display: none !important; }
</style>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
