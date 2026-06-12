<?php

/**
 * Header global — Trivya RH
 *
 * Bootstrap da aplicação + <head> completo + navegação fixa.
 * Deve ser o PRIMEIRO include de qualquer página pública.
 *
 * Variáveis opcionais que as páginas podem definir ANTES do include:
 *   $tituloPagina    — título do <title> e OG:title
 *   $descricaoPagina — meta description e OG:description
 *   $tipoOg          — og:type (padrão: 'website')
 *   $imagemOg        — URL da imagem OG (padrão: logo)
 *   $canonicalUrl    — URL canônica (padrão: URL atual)
 *
 * @autor    Equipe Trivya RH
 * @versao   1.0.0
 * @data     2025-01-01
 */

declare(strict_types=1);

// ----------------------------------------------------------
// Bootstrap: carregar configurações e dependências
// ----------------------------------------------------------

// Usa PROTECTED_PATH se definido pelo bootstrap (deploy) ou calcula localmente (XAMPP)
if (!defined('PROTECTED_PATH')) {
    $raiz = dirname(__DIR__);
    require_once $raiz . '/config/env.php';
    require_once $raiz . '/config/config.php';
    require_once $raiz . '/config/constants.php';
    require_once $raiz . '/config/database.php';
    require_once $raiz . '/config/session.php';
} else {
    $raiz = PROTECTED_PATH;
}

require_once $raiz . '/includes/functions.php';
require_once $raiz . '/includes/sanitizers.php';
require_once $raiz . '/includes/validators.php';
require_once $raiz . '/includes/csrf.php';
require_once $raiz . '/includes/auth.php';
require_once $raiz . '/includes/logger.php';
require_once $raiz . '/includes/logo.php';

// Pré-carregar todas as configurações na memória (evita N queries)
precarregarConfiguracoes();

// ----------------------------------------------------------
// Dados da página (com fallbacks das configurações do banco)
// ----------------------------------------------------------

$tituloPagina    = $tituloPagina    ?? getConfig('meta_title_home', 'TRIVYA | Recrutamento &amp; Seleção');
$descricaoPagina = $descricaoPagina ?? getConfig('meta_description_home', 'Recrutamento &amp; Seleção humanizado para impulsionar pessoas e transformar negócios. Conectamos talentos às oportunidades certas em São Paulo.');
$tipoOg          = $tipoOg          ?? 'website';
$imagemOg        = $imagemOg        ?? ASSETS_URL . '/img/og-image.jpg';

// URL canônica: remover parâmetros de query string para SEO
$protocolo    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host         = $_SERVER['HTTP_HOST'] ?? 'localhost';
$uri          = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$canonicalUrl = $canonicalUrl ?? "{$protocolo}://{$host}{$uri}";

// Dados dinâmicos do banco para o nav e meta tags
$wppNumero    = getConfig('whatsapp', '5511999999999');
$instagramUrl = getConfig('instagram_url', '#');
$linkedinUrl  = getConfig('linkedin_url', '#');
$ga4Id        = getConfig('ga4_measurement_id', GA4_MEASUREMENT_ID);

?><!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <!-- Forçar modo CLARO — impede Chrome Forced Dark Mode de inverter cores -->
  <meta name="color-scheme" content="light">
  <meta name="supported-color-schemes" content="light">

  <!-- Link para pular para conteúdo (acessibilidade) -->
  <title><?= e($tituloPagina) ?></title>

  <!-- SEO Básico -->
  <meta name="description"  content="<?= e($descricaoPagina) ?>">
  <meta name="keywords"     content="<?= e(SITE_KEYWORDS) ?>">
  <meta name="author"       content="<?= e(SITE_AUTHOR) ?>">
  <meta name="robots"       content="index, follow">
  <link rel="canonical"     href="<?= e($canonicalUrl) ?>">

  <!-- Open Graph (Facebook, WhatsApp, LinkedIn) -->
  <meta property="og:type"        content="<?= e($tipoOg) ?>">
  <meta property="og:title"       content="<?= e($tituloPagina) ?>">
  <meta property="og:description" content="<?= e($descricaoPagina) ?>">
  <meta property="og:url"         content="<?= e($canonicalUrl) ?>">
  <meta property="og:image"       content="<?= e($imagemOg) ?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:site_name"   content="<?= e(SITE_NAME) ?>">
  <meta property="og:locale"      content="pt_BR">

  <!-- Twitter Card -->
  <meta name="twitter:card"        content="summary_large_image">
  <meta name="twitter:title"       content="<?= e($tituloPagina) ?>">
  <meta name="twitter:description" content="<?= e($descricaoPagina) ?>">
  <meta name="twitter:image"       content="<?= e($imagemOg) ?>">

  <!-- PWA -->
  <link rel="manifest" href="<?= e(SITE_URL) ?>/manifest.json">
  <meta name="theme-color" content="#C85A2A">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="Trivya RH">

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="<?= e(ASSETS_URL) ?>/img/logoheader.png">

  <!-- Google Fonts: preconnect + link -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Fraunces:ital,wght@0,700;1,400&display=swap" rel="stylesheet">

  <!-- CSS da aplicação — ?v= força o browser a buscar a versão mais recente -->
  <?php $v = '20260611b'; /* atualizar ao fazer deploy */ ?>
  <link rel="stylesheet" href="<?= e(ASSETS_URL) ?>/css/style.css?v=<?= $v ?>">
  <link rel="stylesheet" href="<?= e(ASSETS_URL) ?>/css/components.css?v=<?= $v ?>">
  <link rel="stylesheet" href="<?= e(ASSETS_URL) ?>/css/pages.css?v=<?= $v ?>">
  <link rel="stylesheet" href="<?= e(ASSETS_URL) ?>/css/responsive.css?v=<?= $v ?>">

  <!-- Schema.org JSON-LD: Organization (SEO estruturado) -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "<?= e(SITE_NAME) ?>",
    "url": "<?= e(SITE_URL) ?>",
    "logo": "<?= e(ASSETS_URL) ?>/img/logocentral.png",
    "description": "<?= e(SITE_DESC) ?>",
    "address": {
      "@type": "PostalAddress",
      "addressLocality": "São Paulo",
      "addressRegion": "SP",
      "addressCountry": "BR"
    },
    "contactPoint": {
      "@type": "ContactPoint",
      "contactType": "customer service",
      "telephone": "+55-<?= e(substr($wppNumero, 2)) ?>",
      "email": "<?= e(SITE_EMAIL) ?>",
      "availableLanguage": "Portuguese"
    },
    "sameAs": [
      "<?= e($instagramUrl) ?>",
      "<?= e($linkedinUrl) ?>"
    ]
  }
  </script>

<?php if (!empty($ga4Id)): ?>
  <!-- Google Analytics 4 (carregado apenas se consentimento analítico ativo) -->
  <script>
    window._ga4Id = '<?= e($ga4Id) ?>';
  </script>
<?php endif; ?>

  <!-- Service Worker (PWA) — desativado em desenvolvimento para evitar cache de versões quebradas.
       Reativar apenas em produção (Hostinger) descomentando o bloco abaixo.
  <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('<?= e(SITE_URL) ?>/service-worker.js')
          .catch(() => {});
      });
    }
  </script>
  -->
  <!-- Limpar TODO cache do Service Worker e de Caches API -->
  <script>
    (function() {
      // 1. Desregistrar todos os service workers
      if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistrations().then(function(regs) {
          regs.forEach(function(r) { r.unregister(); });
        });
      }
      // 2. Apagar todos os caches da Cache API
      if ('caches' in window) {
        caches.keys().then(function(nomes) {
          nomes.forEach(function(nome) { caches.delete(nome); });
        });
      }
    })();
  </script>
</head>
<body>

<!-- ======================================================
     HEADER — Nav centralizada com max-width 1200px
     ====================================================== -->
<header class="site-header" id="site-header">
  <nav class="site-nav" role="navigation" aria-label="Navegação principal">
    <div class="nav-inner">

      <!-- Logo (esquerda) -->
      <a href="<?= e(SITE_URL) ?>/" class="nav-logo" aria-label="Trivya RH — Início">
        <?= renderLogo('header') ?>
        <div class="nav-logo-text">
          <span class="nav-logo-name">TRIVYA</span>
          <span class="nav-logo-sub">Consultoria de RH</span>
        </div>
      </a>

      <!-- Links centrais -->
      <ul class="nav-links" id="nav-links">
        <li><a href="#servicos">Serviços</a></li>
        <li><a href="#sobre">Quem Somos</a></li>
        <li><a href="#diferenciais">Diferenciais</a></li>
        <li><a href="#depoimentos">Clientes</a></li>
        <li><a href="<?= e(SITE_URL) ?>/trabalhe-conosco">Trabalhe Conosco</a></li>
        <li><a href="#contato">Contato</a></li>
      </ul>

      <!-- Ações (direita) -->
      <div class="nav-actions">
        <div class="nav-social">
          <a href="<?= e($instagramUrl) ?>" target="_blank" rel="noopener noreferrer"
             class="nav-social-btn" aria-label="Instagram da Trivya RH">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true">
              <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
            </svg>
          </a>
          <a href="https://wa.me/<?= e($wppNumero) ?>" target="_blank" rel="noopener noreferrer"
             class="nav-social-btn nav-social-btn--wa" aria-label="WhatsApp da Trivya RH">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
          </a>
          <a href="<?= e($linkedinUrl) ?>" target="_blank" rel="noopener noreferrer"
             class="nav-social-btn nav-social-btn--li" aria-label="LinkedIn da Trivya RH">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true">
              <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
            </svg>
          </a>
        </div>

        <a href="#contato" class="nav-cta">Fale Conosco</a>

        <button class="nav-mobile-toggle" id="nav-mobile-toggle"
                aria-label="Abrir menu de navegação" aria-expanded="false"
                aria-controls="nav-mobile-menu">
          <span></span>
          <span></span>
          <span></span>
        </button>
      </div>
    </div>
  </nav>
</header>

<!-- Menu mobile -->
<div class="nav-mobile-menu" id="nav-mobile-menu" role="dialog" aria-label="Menu de navegação">
  <ul>
    <li><a href="#servicos">Serviços</a></li>
    <li><a href="#sobre">Quem Somos</a></li>
    <li><a href="#diferenciais">Diferenciais</a></li>
    <li><a href="#depoimentos">Clientes</a></li>
    <li><a href="<?= e(SITE_URL) ?>/trabalhe-conosco">Trabalhe Conosco</a></li>
    <li><a href="#contato">Contato</a></li>
  </ul>
  <a href="#contato" class="nav-cta">Fale Conosco</a>
</div>

<main id="conteudo-principal">

