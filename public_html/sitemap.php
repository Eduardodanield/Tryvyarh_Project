<?php

/**
 * Sitemap XML — Trivya RH
 *
 * Retorna o sitemap em XML com todas as URLs públicas.
 * Acessível via /sitemap.xml graças à reescrita no .htaccess.
 *
 * @autor    Equipe Trivya RH
 * @versao   1.0.0
 * @data     2025-01-01
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

// Enviar cabeçalho XML correto
header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex'); // Não indexar o arquivo PHP diretamente

// Coletar posts publicados dinamicamente
$postsPublicados = [];
try {
    $db = Database::getInstance();
    $postsPublicados = $db->fetchAll(
        "SELECT slug, atualizado_em FROM posts WHERE status = 'publicado' ORDER BY publicado_em DESC"
    );
} catch (Exception) {
    // Silencioso — sitemap continua sem os posts se DB falhar
}

$siteUrl  = rtrim(SITE_URL, '/');
$dataHoje = date('Y-m-d');

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
          http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">

  <!-- Home -->
  <url>
    <loc><?= e($siteUrl) ?>/</loc>
    <lastmod><?= $dataHoje ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>1.0</priority>
  </url>

  <!-- Trabalhe Conosco -->
  <url>
    <loc><?= e($siteUrl) ?>/trabalhe-conosco</loc>
    <lastmod><?= $dataHoje ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.8</priority>
  </url>

  <!-- Política de Privacidade -->
  <url>
    <loc><?= e($siteUrl) ?>/politica-privacidade</loc>
    <lastmod><?= $dataHoje ?></lastmod>
    <changefreq>yearly</changefreq>
    <priority>0.5</priority>
  </url>

  <!-- Termos de Uso -->
  <url>
    <loc><?= e($siteUrl) ?>/termos-uso</loc>
    <lastmod><?= $dataHoje ?></lastmod>
    <changefreq>yearly</changefreq>
    <priority>0.5</priority>
  </url>

  <?php foreach ($postsPublicados as $post): ?>
  <!-- Post: <?= e($post['slug']) ?> -->
  <url>
    <loc><?= e($siteUrl) ?>/blog/<?= e($post['slug']) ?></loc>
    <lastmod><?= e(date('Y-m-d', strtotime($post['atualizado_em']))) ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.7</priority>
  </url>
  <?php endforeach; ?>

</urlset>

