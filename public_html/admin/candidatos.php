<?php
declare(strict_types=1);
$tituloPaginaAdmin = 'Candidatos';
require_once __DIR__ . '/layout/header.php';

$db = Database::getInstance();

// Excluir candidato
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_acao'] ?? '') === 'excluir') {
    verificarCsrf('admin_candidatos', ADMIN_URL . '/candidatos.php');
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $db->execute("DELETE FROM candidatos WHERE id = :id", [':id' => $id]);
        setFlash('sucesso', 'Candidato excluído.');
    }
    redirect(ADMIN_URL . '/candidatos.php');
}

// Filtros
$filtroArea  = $_GET['area']         ?? '';
$filtroEsc   = $_GET['escolaridade'] ?? '';

$where  = '1=1';
$params = [];

if ($filtroArea !== '') {
    $where .= ' AND area_interesse = :area';
    $params[':area'] = $filtroArea;
}
if ($filtroEsc !== '') {
    $where .= ' AND escolaridade = :esc';
    $params[':esc'] = $filtroEsc;
}

$candidatos = $db->fetchAll(
    "SELECT * FROM candidatos WHERE {$where} ORDER BY criado_em DESC",
    $params
);
$csrf = generateCsrf('admin_candidatos');

$areasLabels = [
    'administrativo'   => 'Administrativo',
    'recursos_humanos' => 'RH',
    'atendimento'      => 'Atendimento',
    'recepcao'         => 'Recepção',
    'comercial_vendas' => 'Comercial',
    'marketing'        => 'Marketing',
    'operacional'      => 'Operacional',
    'outro'            => 'Outro',
];
?>

<?php foreach (getFlash('sucesso') as $m): ?>
  <div class="adm-alert adm-alert-success">✓ <?= e($m) ?></div>
<?php endforeach; ?>

<!-- Filtros -->
<div class="adm-card" style="margin-bottom:16px;">
  <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;padding:4px 0;">
    <div>
      <label style="font-size:12px;font-weight:600;color:#6B7280;display:block;margin-bottom:4px;">Área</label>
      <select name="area" style="padding:8px 12px;border:1.5px solid #E9ECEF;border-radius:6px;font-size:13px;">
        <option value="">Todas</option>
        <?php foreach ($areasLabels as $val => $label): ?>
          <option value="<?= e($val) ?>" <?= $filtroArea === $val ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label style="font-size:12px;font-weight:600;color:#6B7280;display:block;margin-bottom:4px;">Escolaridade</label>
      <select name="escolaridade" style="padding:8px 12px;border:1.5px solid #E9ECEF;border-radius:6px;font-size:13px;">
        <option value="">Todas</option>
        <option value="Ensino Médio Completo"      <?= $filtroEsc === 'Ensino Médio Completo'      ? 'selected' : '' ?>>Ensino Médio Completo</option>
        <option value="Ensino Técnico"             <?= $filtroEsc === 'Ensino Técnico'             ? 'selected' : '' ?>>Ensino Técnico</option>
        <option value="Ensino Superior Incompleto" <?= $filtroEsc === 'Ensino Superior Incompleto' ? 'selected' : '' ?>>Superior Incompleto</option>
        <option value="Ensino Superior Completo"   <?= $filtroEsc === 'Ensino Superior Completo'   ? 'selected' : '' ?>>Superior Completo</option>
        <option value="Pós-graduação"              <?= $filtroEsc === 'Pós-graduação'              ? 'selected' : '' ?>>Pós-graduação</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
    <?php if ($filtroArea !== '' || $filtroEsc !== ''): ?>
      <a href="candidatos.php" class="btn btn-outline btn-sm">Limpar</a>
    <?php endif; ?>
  </form>
</div>

<div class="adm-card">
  <div class="adm-card-header">
    <span class="adm-card-title">Candidatos (<?= count($candidatos) ?>)</span>
  </div>
  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr>
          <th>Nome</th>
          <th>Contato</th>
          <th>Idade</th>
          <th>Cidade/Bairro</th>
          <th>Área</th>
          <th>Escolaridade</th>
          <th>Currículo</th>
          <th>Data</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($candidatos)): ?>
          <tr><td colspan="9" style="text-align:center;color:#6B7280;padding:32px;">Nenhum candidato encontrado.</td></tr>
        <?php else: ?>
          <?php foreach ($candidatos as $c): ?>
          <tr>
            <td>
              <strong><?= e($c['nome']) ?></strong>
            </td>
            <td>
              <div><?= e($c['telefone'] ?? '—') ?></div>
              <small style="color:#6B7280;"><?= e($c['email'] ?? '') ?></small>
            </td>
            <td style="text-align:center;">
              <?= !empty($c['idade']) ? e((string)$c['idade']) : '—' ?>
            </td>
            <td><?= e(mb_substr($c['cidade_bairro'] ?? '—', 0, 35, 'UTF-8')) ?></td>
            <td>
              <?php $area = $c['area_interesse'] ?? ''; ?>
              <?php if ($area && isset($areasLabels[$area])): ?>
                <span class="badge badge-ativo" style="background:#E8FAFB;color:#0ECAD4;">
                  <?= e($areasLabels[$area]) ?>
                </span>
              <?php else: ?>
                <span style="color:#D1D5DB;">—</span>
              <?php endif; ?>
            </td>
            <td style="font-size:12px;"><?= e(mb_substr($c['escolaridade'] ?? '—', 0, 30, 'UTF-8')) ?></td>
            <td>
              <?php if (!empty($c['curriculo_path'])): ?>
                <?php
                $cvUrl = SITE_URL . '/' . ltrim(e($c['curriculo_path']), '/');
                ?>
                <a href="<?= $cvUrl ?>" target="_blank" class="btn btn-outline btn-sm"
                   title="<?= e($c['curriculo_nome'] ?? '') ?>">
                  📎 Baixar
                </a>
              <?php else: ?>
                <span style="color:#D1D5DB;">—</span>
              <?php endif; ?>
            </td>
            <td style="white-space:nowrap;">
              <small><?= date('d/m/Y', strtotime($c['criado_em'])) ?></small>
              <br><small style="color:#6B7280;"><?= date('H:i', strtotime($c['criado_em'])) ?></small>
            </td>
            <td>
              <button type="button"
                      class="btn btn-outline btn-sm"
                      onclick="document.getElementById('cand-detail-<?= (int)$c['id'] ?>').classList.toggle('hidden')"
                      style="margin-bottom:4px;">
                👁️ Ver
              </button>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="<?= CSRF_FIELD_NAME ?>" value="<?= e($csrf) ?>">
                <input type="hidden" name="_acao" value="excluir">
                <input type="hidden" name="id"    value="<?= (int) $c['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm"
                        data-confirm="Excluir candidato '<?= e($c['nome']) ?>'?">🗑️</button>
              </form>
              <!-- Detalhe expandível -->
              <div id="cand-detail-<?= (int)$c['id'] ?>" class="hidden"
                   style="margin-top:12px;background:#F8F9FA;border-radius:8px;padding:12px;font-size:13px;border:1px solid #E9ECEF;">
                <?php if (!empty($c['data_nascimento'])): ?>
                  <strong>Nascimento:</strong> <?= date('d/m/Y', strtotime($c['data_nascimento'])) ?><br>
                <?php endif; ?>
                <strong>Está estudando:</strong>
                <?= $c['esta_estudando'] === null ? '—' : ($c['esta_estudando'] ? 'Sim' : 'Não') ?><br>
                <strong>IP:</strong> <?= e($c['ip'] ?? '—') ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<style>.hidden { display: none !important; }</style>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
