<?php

/**
 * Componente de logo SVG inline — Trivya RH
 *
 * Retorna o logotipo como SVG inline (sem imagem externa).
 * O logo é um triângulo com 3 nós terracota interconectados,
 * representando a rede de conexões entre pessoas e empresas.
 *
 * @autor    Equipe Trivya RH
 * @versao   1.0.0
 * @data     2025-01-01
 */

declare(strict_types=1);

/**
 * Gera o SVG do logotipo Trivya RH.
 *
 * @param string $tamanho  'pequeno' (32px) | 'medio' (40px) | 'grande' (270px)
 * @param string $contexto 'header' | 'hero' | 'footer'
 * @return string SVG inline completo
 */
function renderLogo(string $tamanho = 'medio', string $contexto = 'header'): string
{
    // Dimensões em pixels de acordo com o tamanho solicitado
    $dimensoes = match ($tamanho) {
        'pequeno' => 32,
        'grande'  => 270,
        default   => 40, // 'medio'
    };

    // Cores conforme o contexto — nova paleta ciano/navy
    if ($contexto === 'footer') {
        // Fundo escuro: linhas e nós brancos semitransparentes
        $corLinhas         = 'rgba(255,255,255,0.5)';
        $corLinhasInternas = 'rgba(255,255,255,0.3)';
        $corNosBaixo       = 'rgba(255,255,255,0.25)';
        $opacidadeNoBaixo  = '0.8';
    } else {
        // Fundo claro (header / hero): linhas navy, nós ciano
        $corLinhas         = '#001233';
        $corLinhasInternas = '#001233';
        $corNosBaixo       = '#0ECAD4';
        $opacidadeNoBaixo  = '1';
    }

    // Nó do topo: ciano vibrante (cor de destaque da nova identidade)
    $corNoTopo = '#0ECAD4';

    return <<<SVG
<svg width="{$dimensoes}" height="{$dimensoes}" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <!-- Linhas externas do triângulo -->
  <line x1="60" y1="16" x2="20" y2="92" stroke="{$corLinhas}" stroke-width="2.5" stroke-linecap="round"/>
  <line x1="60" y1="16" x2="100" y2="92" stroke="{$corLinhas}" stroke-width="2.5" stroke-linecap="round"/>
  <line x1="20" y1="92" x2="100" y2="92" stroke="{$corLinhas}" stroke-width="2.5" stroke-linecap="round"/>
  <!-- Linhas internas de conexão (medianas) -->
  <line x1="60" y1="16" x2="60" y2="92" stroke="{$corLinhasInternas}" stroke-width="1.8" stroke-linecap="round" opacity=".5"/>
  <line x1="20" y1="92" x2="80" y2="54" stroke="{$corLinhasInternas}" stroke-width="1.8" stroke-linecap="round" opacity=".5"/>
  <line x1="100" y1="92" x2="40" y2="54" stroke="{$corLinhasInternas}" stroke-width="1.8" stroke-linecap="round" opacity=".5"/>
  <!-- Nó superior: terracota (cor de destaque da marca) -->
  <circle cx="60" cy="16" r="9.5" fill="{$corNoTopo}"/>
  <circle cx="60" cy="12.5" r="3.2" fill="white"/>
  <ellipse cx="60" cy="21" rx="5" ry="3.2" fill="white" opacity=".9"/>
  <!-- Nó inferior esquerdo -->
  <circle cx="20" cy="92" r="9.5" fill="{$corNosBaixo}"/>
  <circle cx="20" cy="88.5" r="3.2" fill="white" opacity="{$opacidadeNoBaixo}"/>
  <ellipse cx="20" cy="97" rx="5" ry="3.2" fill="white" opacity=".9"/>
  <!-- Nó inferior direito -->
  <circle cx="100" cy="92" r="9.5" fill="{$corNosBaixo}"/>
  <circle cx="100" cy="88.5" r="3.2" fill="white" opacity="{$opacidadeNoBaixo}"/>
  <ellipse cx="100" cy="97" rx="5" ry="3.2" fill="white" opacity=".9"/>
</svg>
SVG;
}
