<?php

/**
 * Página 404 — Não Encontrado — Trivya RH
 *
 * @autor    Equipe Trivya RH
 * @versao   1.0.0
 * @data     2025-01-01
 */

declare(strict_types=1);

// Enviar o código HTTP correto
http_response_code(404);

$tituloPagina    = 'Página não encontrada (404) | Trivya RH';
$descricaoPagina = 'A página que você está procurando não foi encontrada.';

require_once __DIR__ . '/bootstrap.php';
require_once INCLUDES_PATH . '/header.php';

?>

<div class="erro-page page-content-offset">
  <div class="erro-codigo" aria-hidden="true">404</div>
  <h1 class="erro-titulo">Página não encontrada</h1>
  <p class="erro-texto">
    A página que você está procurando pode ter sido movida, renomeada ou simplesmente não existe.
  </p>
  <div style="display:flex; gap:14px; flex-wrap:wrap; justify-content:center;">
    <a href="<?= e(SITE_URL) ?>/" class="btn-primary">← Voltar para a home</a>
    <a href="<?= e(SITE_URL) ?>/#contato" class="btn-outline">Falar conosco</a>
  </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>

