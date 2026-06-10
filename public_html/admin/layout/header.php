<?php
/**
 * Layout Header — Painel Admin Trivya RH
 * Sidebar + Topbar. Inclua este arquivo no TOPO de cada página admin.
 * @autor Equipe Trivya RH  @versao 1.0.0
 */

if (!defined('PROTECTED_PATH')) {
    require_once dirname(dirname(__DIR__)) . '/bootstrap.php';
}

precarregarConfiguracoes();

// Proteger página — redireciona para login se não autenticado
requireAuth();

$adminAtual  = getCurrentAdmin();
$paginaAtual = basename($_SERVER['PHP_SELF'], '.php');

// Links do menu lateral
$menuLinks = [
    ['href' => 'dashboard',    'icon' => '📊', 'label' => 'Dashboard'],
    ['href' => 'leads',        'icon' => '🏢', 'label' => 'Leads (Empresas)'],
    ['href' => 'candidatos',   'icon' => '👤', 'label' => 'Candidatos'],
    ['href' => 'configuracoes','icon' => '⚙️',  'label' => 'Configurações'],
    ['href' => 'servicos',     'icon' => '🛒', 'label' => 'Serviços'],
    ['href' => 'depoimentos',  'icon' => '💬', 'label' => 'Depoimentos'],
    ['href' => 'nichos',       'icon' => '🏷️',  'label' => 'Nichos'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($tituloPaginaAdmin ?? 'Painel') ?> — Trivya Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(ASSETS_URL) ?>/css/admin.css">
  <meta name="color-scheme" content="light">
</head>
<body>
<div class="adm-wrapper">

  <!-- ── SIDEBAR ── -->
  <aside class="adm-sidebar" id="adm-sidebar">

    <div class="adm-sidebar-header">
      <div class="adm-sidebar-brand">
        TRIVYA
        <span>Painel Administrativo</span>
      </div>
    </div>

    <nav class="adm-nav">
      <div class="adm-nav-section">Menu</div>
      <?php foreach ($menuLinks as $link): ?>
        <a href="<?= e(ADMIN_URL) ?>/<?= e($link['href']) ?>.php"
           class="adm-nav-link <?= $paginaAtual === $link['href'] ? 'ativo' : '' ?>">
          <span class="icon"><?= $link['icon'] ?></span>
          <?= e($link['label']) ?>
        </a>
      <?php endforeach; ?>

      <div class="adm-nav-section" style="margin-top:20px;">Site</div>
      <a href="<?= e(SITE_URL) ?>/public/" target="_blank" class="adm-nav-link">
        <span class="icon">🌐</span> Ver Site
      </a>
    </nav>

    <div class="adm-sidebar-footer">
      <div class="adm-user-info">
        <div class="adm-user-avatar">
          <?= e(mb_strtoupper(mb_substr($adminAtual['nome'], 0, 1, 'UTF-8'), 'UTF-8')) ?>
        </div>
        <div>
          <div class="adm-user-name"><?= e(explode(' ', $adminAtual['nome'])[0]) ?></div>
          <div class="adm-user-role"><?= e(ADMIN_ROLES[$adminAtual['role']] ?? $adminAtual['role']) ?></div>
        </div>
      </div>
      <a href="<?= e(ADMIN_URL) ?>/logout.php" class="adm-btn-logout">
        ⎋ Sair
      </a>
    </div>
  </aside>

  <!-- ── CONTEÚDO PRINCIPAL ── -->
  <div class="adm-main">

    <!-- Topbar -->
    <header class="adm-topbar">
      <div style="display:flex;align-items:center;gap:14px;">
        <!-- Botão hamburguer mobile -->
        <button onclick="toggleSidebar()" style="display:none;background:none;border:none;cursor:pointer;font-size:22px;" id="adm-menu-btn">☰</button>
        <span class="adm-topbar-title"><?= e($tituloPaginaAdmin ?? 'Dashboard') ?></span>
      </div>
      <div class="adm-topbar-actions">
        <span style="font-size:13px;color:#6B7280;">Olá, <?= e(explode(' ', $adminAtual['nome'])[0]) ?> 👋</span>
      </div>
    </header>

    <!-- Conteúdo da página começa aqui -->
    <main class="adm-content">
