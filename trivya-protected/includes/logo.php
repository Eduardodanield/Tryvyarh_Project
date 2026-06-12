<?php

/**
 * Componente de logo — Trivya RH
 *
 * Retorna a tag <img> do logotipo de acordo com o contexto de uso.
 *
 * @autor    Equipe Trivya RH
 * @versao   2.0.0
 * @data     2026-06-11
 */

declare(strict_types=1);

/**
 * Gera a tag <img> do logotipo Trivya RH.
 *
 * @param string $contexto 'header' (símbolo) | 'hero' | 'footer' (logo completo)
 * @return string Tag <img> completa
 */
function renderLogo(string $contexto = 'header'): string
{
    $arquivo = ($contexto === 'header') ? 'logoheader.png' : 'logocentral.png';

    $alt = match ($contexto) {
        'hero' => 'Trivya RH — Consultoria de Recrutamento e Seleção',
        default => '',
    };

    $loading = ($contexto === 'hero') ? 'lazy' : 'eager';

    return '<img src="' . e(SITE_URL . '/assets/img/' . $arquivo) . '"'
        . ' alt="' . e($alt) . '"'
        . ' class="logo-img logo-' . e($contexto) . '"'
        . ' loading="' . $loading . '">';
}
