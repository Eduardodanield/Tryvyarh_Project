<?php

/**
 * Página 500 — Erro Interno do Servidor — Trivya RH
 *
 * NÃO expõe detalhes técnicos ao usuário.
 * Erros detalhados ficam apenas nos logs.
 *
 * @autor    Equipe Trivya RH
 * @versao   1.0.0
 * @data     2025-01-01
 */

declare(strict_types=1);

http_response_code(500);

// Carrega config básica; bootstrap tem proteção contra loop (define ROOT_PATH)
if (!defined('PROTECTED_PATH')) {
    @require_once __DIR__ . '/bootstrap.php';
}

$tituloPagina    = 'Erro no servidor (500) | Trivya RH';
$descricaoPagina = 'Ocorreu um erro temporário. Tente novamente em alguns instantes.';

// Header simplificado (sem banco, sem sessão)
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($tituloPagina, ENT_QUOTES, 'UTF-8') ?></title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&family=Fraunces:wght@700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= defined('ASSETS_URL') ? htmlspecialchars(ASSETS_URL, ENT_QUOTES, 'UTF-8') : '/assets' ?>/css/style.css">
  <link rel="stylesheet" href="<?= defined('ASSETS_URL') ? htmlspecialchars(ASSETS_URL, ENT_QUOTES, 'UTF-8') : '/assets' ?>/css/components.css">
  <link rel="stylesheet" href="<?= defined('ASSETS_URL') ? htmlspecialchars(ASSETS_URL, ENT_QUOTES, 'UTF-8') : '/assets' ?>/css/pages.css">
  <link rel="stylesheet" href="<?= defined('ASSETS_URL') ? htmlspecialchars(ASSETS_URL, ENT_QUOTES, 'UTF-8') : '/assets' ?>/css/responsive.css">
</head>
<body>

<div class="erro-page">
  <div class="erro-codigo" aria-hidden="true">500</div>
  <h1 class="erro-titulo">Algo deu errado</h1>
  <p class="erro-texto">
    Ocorreu um erro temporário em nosso servidor. Nossa equipe foi notificada e está
    trabalhando para resolver. Tente novamente em alguns instantes.
  </p>
  <div style="display:flex; gap:14px; flex-wrap:wrap; justify-content:center;">
    <a href="<?= defined('SITE_URL') ? htmlspecialchars(SITE_URL . '/', ENT_QUOTES, 'UTF-8') : '/' ?>"
       class="btn-primary">← Voltar para a home</a>
    <a href="https://wa.me/5511999999999" class="btn-outline" target="_blank" rel="noopener">
      💬 Falar pelo WhatsApp
    </a>
  </div>
</div>

</body>
</html>

