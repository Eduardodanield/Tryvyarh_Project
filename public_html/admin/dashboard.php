<?php
declare(strict_types=1);
$tituloPaginaAdmin = 'Dashboard';
require_once __DIR__ . '/layout/header.php';

$db = Database::getInstance();

// Contadores para os stat cards
$totalServicos   = (int) ($db->fetchOne("SELECT COUNT(*) AS n FROM servicos WHERE ativo=1")['n'] ?? 0);
$totalDepoimentos= (int) ($db->fetchOne("SELECT COUNT(*) AS n FROM depoimentos WHERE ativo=1")['n'] ?? 0);
$totalNichos     = (int) ($db->fetchOne("SELECT COUNT(*) AS n FROM nichos_marquee WHERE ativo=1")['n'] ?? 0);
$totalLeads      = (int) ($db->fetchOne("SELECT COUNT(*) AS n FROM leads")['n'] ?? 0);
?>

<div class="adm-stats-grid">
  <div class="adm-stat">
    <div class="adm-stat-icon">🏢</div>
    <div>
      <div class="adm-stat-num"><?= $totalServicos ?></div>
      <div class="adm-stat-lbl">Serviços ativos</div>
    </div>
  </div>
  <div class="adm-stat">
    <div class="adm-stat-icon">💬</div>
    <div>
      <div class="adm-stat-num"><?= $totalDepoimentos ?></div>
      <div class="adm-stat-lbl">Depoimentos ativos</div>
    </div>
  </div>
  <div class="adm-stat">
    <div class="adm-stat-icon">🏷️</div>
    <div>
      <div class="adm-stat-num"><?= $totalNichos ?></div>
      <div class="adm-stat-lbl">Nichos no letreiro</div>
    </div>
  </div>
  <div class="adm-stat">
    <div class="adm-stat-icon">📩</div>
    <div>
      <div class="adm-stat-num"><?= $totalLeads ?></div>
      <div class="adm-stat-lbl">Leads recebidos</div>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

  <!-- Acesso rápido -->
  <div class="adm-card">
    <div class="adm-card-header">
      <span class="adm-card-title">Acesso Rápido</span>
    </div>
    <div style="display:flex;flex-direction:column;gap:10px;">
      <a href="configuracoes.php" class="btn btn-outline" style="justify-content:flex-start;">⚙️ &nbsp;Editar Configurações Gerais</a>
      <a href="servicos.php"      class="btn btn-outline" style="justify-content:flex-start;">🏢 &nbsp;Gerenciar Serviços</a>
      <a href="depoimentos.php"   class="btn btn-outline" style="justify-content:flex-start;">💬 &nbsp;Gerenciar Depoimentos</a>
      <a href="nichos.php"        class="btn btn-outline" style="justify-content:flex-start;">🏷️ &nbsp;Editar Letreiro de Nichos</a>
    </div>
  </div>

  <!-- Informações do painel -->
  <div class="adm-card">
    <div class="adm-card-header">
      <span class="adm-card-title">Sobre o Painel</span>
    </div>
    <p style="font-size:14px;color:#6B7280;line-height:1.7;margin-bottom:16px;">
      Bem-vinda ao painel administrativo da <strong>Trivya RH</strong>. Aqui você pode editar todos os conteúdos do site sem precisar alterar código.
    </p>
    <div style="font-size:13px;color:#8A9BB0;border-top:1px solid #E9ECEF;padding-top:14px;">
      <div>🌐 <a href="<?= e(SITE_URL) ?>/" target="_blank" style="color:#0ECAD4;">Ver o site ao vivo</a></div>
      <div style="margin-top:6px;">🔒 Sessão segura · <?= e($adminAtual['email']) ?></div>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
