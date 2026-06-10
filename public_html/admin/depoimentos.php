<?php
declare(strict_types=1);
$tituloPaginaAdmin = 'Depoimentos';
require_once __DIR__ . '/layout/header.php';

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf('admin_depoimentos', ADMIN_URL . '/depoimentos.php');
    $acao = $_POST['_acao'] ?? '';

    if ($acao === 'excluir') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $db->execute("DELETE FROM depoimentos WHERE id = :id", [':id' => $id]);
            setFlash('sucesso', 'Depoimento excluído.');
        }

    } elseif ($acao === 'mover') {
        $id      = (int) ($_POST['id'] ?? 0);
        $direcao = $_POST['direcao'] ?? '';

        if ($id > 0 && in_array($direcao, ['cima', 'baixo'], true)) {
            $atual = $db->fetchOne(
                "SELECT id, ordem FROM depoimentos WHERE id = :id",
                [':id' => $id]
            );
            if ($atual) {
                if ($direcao === 'cima') {
                    $vizinho = $db->fetchOne(
                        "SELECT id, ordem FROM depoimentos WHERE ordem < :o ORDER BY ordem DESC LIMIT 1",
                        [':o' => $atual['ordem']]
                    );
                } else {
                    $vizinho = $db->fetchOne(
                        "SELECT id, ordem FROM depoimentos WHERE ordem > :o ORDER BY ordem ASC LIMIT 1",
                        [':o' => $atual['ordem']]
                    );
                }
                if ($vizinho) {
                    $db->execute(
                        "UPDATE depoimentos SET ordem = :o WHERE id = :id",
                        [':o' => $vizinho['ordem'], ':id' => $atual['id']]
                    );
                    $db->execute(
                        "UPDATE depoimentos SET ordem = :o WHERE id = :id",
                        [':o' => $atual['ordem'], ':id' => $vizinho['id']]
                    );
                }
            }
        }
    }
    redirect(ADMIN_URL . '/depoimentos.php');
}

$depoimentos = $db->fetchAll(
    "SELECT * FROM depoimentos ORDER BY ordem ASC, id DESC"
);
$csrf = generateCsrf('admin_depoimentos');

// Mapeamento de origem para label e cor
$origemInfo = [
    'whatsapp'     => ['label' => 'WhatsApp',     'cor' => '#25D366', 'texto' => '#fff'],
    'email'        => ['label' => 'E-mail',        'cor' => '#2563EB', 'texto' => '#fff'],
    'linkedin'     => ['label' => 'LinkedIn',      'cor' => '#7C3AED', 'texto' => '#fff'],
    'pessoalmente' => ['label' => 'Presencial',    'cor' => '#EA580C', 'texto' => '#fff'],
    'outro'        => ['label' => 'Outro',         'cor' => '#6B7280', 'texto' => '#fff'],
];
?>

<?php foreach (getFlash('sucesso') as $m): ?>
  <div class="adm-alert adm-alert-success">✓ <?= e($m) ?></div>
<?php endforeach; ?>

<?php
$semLgpd = $db->fetchOne("SELECT COUNT(*) AS total FROM depoimentos WHERE ativo = 1 AND autorizacao_lgpd = 0");
if ($semLgpd && (int)$semLgpd['total'] > 0):
?>
<div style="background:#FEF5F5;border-left:4px solid #E24B4A;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;color:#791F1F;">
  ⚠️ <strong><?= (int)$semLgpd['total'] ?> depoimento(s)</strong> ativo(s) sem autorização LGPD.
  Em produção (APP_ENV=production) esses depoimentos <strong>NÃO aparecerão no site</strong>.
  Antes de publicar, marque a autorização ou desative-os.
</div>
<?php endif; ?>

<!-- Aviso LGPD -->
<div class="aviso-lgpd">
  <strong>⚠️ Atenção LGPD:</strong> Todo depoimento deve ser um texto <em>real</em>
  enviado pelo cliente com <em>autorização formal</em> de uso. Marque "Autorização LGPD"
  apenas quando tiver a permissão escrita (WhatsApp, e-mail ou pessoalmente).
  Editar aqui significa <strong>ajustar formatação</strong>, <u>não criar depoimentos falsos</u>.
  Os depoimentos sem autorização LGPD <strong>não aparecem no site público</strong>.
</div>

<div class="adm-card">
  <div class="adm-card-header">
    <span class="adm-card-title">Depoimentos (<?= count($depoimentos) ?>)</span>
    <a href="depoimentos-form.php" class="btn btn-primary btn-sm">+ Novo depoimento</a>
  </div>

  <div class="adm-table-wrap">
    <table class="adm-table">
      <thead>
        <tr>
          <th title="Destaque">★</th>
          <th>Ordem</th>
          <th>Cliente</th>
          <th>Cargo / Empresa</th>
          <th>Origem</th>
          <th>LGPD</th>
          <th>Status</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($depoimentos)): ?>
          <tr>
            <td colspan="8" style="text-align:center;color:#6B7280;padding:32px;">
              Nenhum depoimento cadastrado.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($depoimentos as $d): ?>
          <tr>
            <!-- Destaque -->
            <td style="text-align:center;font-size:18px;">
              <?= $d['destaque'] ? '<span title="Em destaque" style="color:#F59E0B;">★</span>' : '<span style="color:#D1D5DB;">☆</span>' ?>
            </td>

            <!-- Ordem + setas -->
            <td style="text-align:center;">
              <div style="display:flex;align-items:center;justify-content:center;gap:4px;">
                <form method="POST" style="margin:0;">
                  <input type="hidden" name="<?= CSRF_FIELD_NAME ?>" value="<?= e($csrf) ?>">
                  <input type="hidden" name="_acao"   value="mover">
                  <input type="hidden" name="id"      value="<?= (int) $d['id'] ?>">
                  <input type="hidden" name="direcao" value="cima">
                  <button type="submit" class="btn-seta" title="Mover para cima">↑</button>
                </form>
                <span style="font-size:13px;color:#6B7280;min-width:20px;text-align:center;">
                  <?= (int) $d['ordem'] ?>
                </span>
                <form method="POST" style="margin:0;">
                  <input type="hidden" name="<?= CSRF_FIELD_NAME ?>" value="<?= e($csrf) ?>">
                  <input type="hidden" name="_acao"   value="mover">
                  <input type="hidden" name="id"      value="<?= (int) $d['id'] ?>">
                  <input type="hidden" name="direcao" value="baixo">
                  <button type="submit" class="btn-seta" title="Mover para baixo">↓</button>
                </form>
              </div>
            </td>

            <!-- Cliente -->
            <td>
              <strong><?= e($d['nome']) ?></strong>
              <br>
              <small style="color:#6B7280;">"<?= e(mb_substr($d['texto'] ?? '', 0, 48, 'UTF-8')) ?>…"</small>
            </td>

            <!-- Cargo / Empresa -->
            <td><?= e($d['cargo'] ?? '—') ?></td>

            <!-- Origem -->
            <td>
              <?php if (!empty($d['origem']) && isset($origemInfo[$d['origem']])): ?>
                <?php $o = $origemInfo[$d['origem']]; ?>
                <span class="badge-origem" style="background:<?= $o['cor'] ?>;color:<?= $o['texto'] ?>;">
                  <?= $o['label'] ?>
                </span>
              <?php else: ?>
                <span style="color:#D1D5DB;font-size:13px;">—</span>
              <?php endif; ?>
            </td>

            <!-- LGPD -->
            <td style="text-align:center;font-size:16px;">
              <?= $d['autorizacao_lgpd']
                    ? '<span title="Autorizado" style="color:#22C55E;">✅</span>'
                    : '<span title="Sem autorização" style="color:#F59E0B;">⚠️</span>' ?>
            </td>

            <!-- Status -->
            <td>
              <span class="badge <?= $d['ativo'] ? 'badge-ativo' : 'badge-inativo' ?>">
                <?= $d['ativo'] ? '● Ativo' : '○ Inativo' ?>
              </span>
            </td>

            <!-- Ações -->
            <td style="white-space:nowrap;">
              <a href="depoimentos-form.php?id=<?= (int) $d['id'] ?>"
                 class="btn btn-outline btn-sm"
                 title="Ajustar formatação do texto">
                ✏️ Ajustar formatação
              </a>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="<?= CSRF_FIELD_NAME ?>" value="<?= e($csrf) ?>">
                <input type="hidden" name="_acao" value="excluir">
                <input type="hidden" name="id"    value="<?= (int) $d['id'] ?>">
                <button type="submit"
                        class="btn btn-danger btn-sm"
                        data-confirm="Excluir o depoimento de '<?= e($d['nome']) ?>'?">
                  🗑️
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<style>
/* Aviso LGPD */
.aviso-lgpd {
  background: #FFFBEB;
  border-left: 4px solid #F59E0B;
  border-radius: 6px;
  padding: 14px 18px;
  font-size: 13.5px;
  color: #78350F;
  line-height: 1.6;
  margin-bottom: 20px;
}

/* Badge de origem */
.badge-origem {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.3px;
  white-space: nowrap;
}

/* Botões de seta para reordenação */
.btn-seta {
  background: none;
  border: 1px solid #D1D5DB;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  color: #6B7280;
  width: 22px;
  height: 22px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  transition: background 0.15s, color 0.15s;
  line-height: 1;
}
.btn-seta:hover {
  background: #001233;
  color: #fff;
  border-color: #001233;
}
</style>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
