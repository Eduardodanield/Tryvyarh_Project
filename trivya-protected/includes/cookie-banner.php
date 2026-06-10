<?php

/**
 * Banner de consentimento de cookies (LGPD) — Trivya RH
 *
 * Exibe o banner na primeira visita do usuário.
 * O JavaScript em cookies.js controla a lógica de exibição
 * e armazenamento da preferência via cookie.
 *
 * Categorias de cookies:
 *  - Essenciais: sessão, CSRF (sempre ativos, sem consentimento)
 *  - Analíticos: Google Analytics 4
 *  - Marketing: (não utilizado por ora)
 *
 * @autor    Equipe Trivya RH
 * @versao   1.0.0
 * @data     2025-01-01
 */

declare(strict_types=1);

$urlPolitica = e(SITE_URL . '/politica-privacidade');

?>
<!-- Banner de Cookies LGPD — oculto até cookies.js verificar a preferência -->
<div class="cookie-banner" id="cookie-banner" role="alertdialog" aria-live="polite" aria-label="Aviso sobre cookies">
  <p class="cookie-banner-texto">
    🍪 Usamos <strong>cookies essenciais</strong> para o funcionamento do site e, com sua autorização,
    cookies analíticos para melhorar sua experiência. Seus dados são protegidos conforme a
    <a href="<?= $urlPolitica ?>" target="_blank" rel="noopener">Política de Privacidade</a>
    (Lei nº 13.709/2018 — LGPD).
  </p>
  <div class="cookie-banner-acoes">
    <button class="cookie-btn cookie-btn-recusar"
            onclick="cookieRecusar()"
            aria-label="Aceitar apenas cookies essenciais">
      Só essenciais
    </button>
    <button class="cookie-btn cookie-btn-personalizar"
            onclick="cookiePersonalizar()"
            aria-label="Personalizar preferências de cookies">
      Personalizar
    </button>
    <button class="cookie-btn cookie-btn-aceitar"
            onclick="cookieAceitarTodos()"
            aria-label="Aceitar todos os cookies">
      Aceitar todos
    </button>
  </div>
</div>

<!-- Modal de personalização (oculto por padrão) -->
<div id="cookie-modal"
     style="display:none; position:fixed; inset:0; z-index:9001; background:rgba(0,0,0,.6);
            align-items:center; justify-content:center; padding:20px;"
     role="dialog" aria-modal="true" aria-labelledby="cookie-modal-titulo">
  <div style="background:#fff; border-radius:16px; padding:32px; max-width:480px; width:100%;">
    <h3 id="cookie-modal-titulo"
        style="font-family:'Fraunces',serif; font-size:20px; font-weight:700;
               color:#1A1A1A; margin-bottom:20px;">
      Preferências de Cookies
    </h3>

    <!-- Essenciais (sempre ON) -->
    <div style="padding:14px 0; border-bottom:1px solid #E9ECEF;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
        <strong style="font-size:15px; color:#1A1A1A;">Cookies Essenciais</strong>
        <span style="font-size:12px; font-weight:600; color:#8A8A8A; background:#F8F9FA;
                     padding:3px 10px; border-radius:20px;">Sempre ativos</span>
      </div>
      <p style="font-size:13px; color:#4A4A4A; margin:0;">
        Necessários para o funcionamento básico do site (sessão, segurança CSRF).
        Não podem ser desativados.
      </p>
    </div>

    <!-- Analíticos -->
    <div style="padding:14px 0; border-bottom:1px solid #E9ECEF;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
        <strong style="font-size:15px; color:#1A1A1A;">Cookies Analíticos</strong>
        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
          <span style="font-size:13px; color:#8A8A8A;" id="label-analiticos">Desativado</span>
          <input type="checkbox" id="cookie-analiticos"
                 style="accent-color:#0ECAD4; width:18px; height:18px;"
                 onchange="document.getElementById('label-analiticos').textContent=this.checked?'Ativado':'Desativado'">
        </label>
      </div>
      <p style="font-size:13px; color:#4A4A4A; margin:0;">
        Google Analytics 4 — nos ajuda a entender como os visitantes usam o site (páginas vistas,
        tempo de permanência). Não identifica você pessoalmente.
      </p>
    </div>

    <!-- Ações do modal -->
    <div style="display:flex; gap:10px; margin-top:20px; justify-content:flex-end;">
      <button onclick="cookieFecharModal()"
              style="padding:10px 20px; border:1.5px solid #E9ECEF; border-radius:6px;
                     background:transparent; font-size:14px; font-weight:600; cursor:pointer;
                     color:#4A4A4A; font-family:inherit;">
        Cancelar
      </button>
      <button onclick="cookieSalvarPreferencias()"
              style="padding:10px 20px; border:none; border-radius:6px; background:#0ECAD4;
                     color:#fff; font-size:14px; font-weight:600; cursor:pointer; font-family:inherit;">
        Salvar preferências
      </button>
    </div>
  </div>
</div>

