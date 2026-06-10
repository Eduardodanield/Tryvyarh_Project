/**
 * cookies.js — Trivya RH
 * Gerenciamento de consentimento de cookies (LGPD)
 * 3 categorias: essenciais (sempre), analíticos, marketing
 * Vanilla ES6+ sem dependências externas
 * @autor  Equipe Trivya RH  @versao 1.0.0  @data 2025-01-01
 */

'use strict';

// Nome do cookie que armazena as preferências
const COOKIE_CONSENTIMENTO = 'cookie_consent_trivya';

// Validade do cookie em dias (1 ano)
const COOKIE_DIAS = 365;

// ----------------------------------------------------------
// 1. UTILITÁRIOS DE COOKIE
// ----------------------------------------------------------

function setCookie(nome, valor, dias) {
  const expira = new Date();
  expira.setDate(expira.getDate() + dias);
  document.cookie = [
    `${encodeURIComponent(nome)}=${encodeURIComponent(valor)}`,
    `expires=${expira.toUTCString()}`,
    'path=/',
    'SameSite=Lax'
    // Em HTTPS: adicionar 'Secure' (via servidor via header Set-Cookie)
  ].join('; ');
}

function getCookie(nome) {
  const chave = encodeURIComponent(nome) + '=';
  const cookies = document.cookie.split(';');
  for (let c of cookies) {
    c = c.trim();
    if (c.startsWith(chave)) {
      return decodeURIComponent(c.slice(chave.length));
    }
  }
  return null;
}

// ----------------------------------------------------------
// 2. LER / SALVAR PREFERÊNCIAS
// ----------------------------------------------------------

function lerPreferencias() {
  const raw = getCookie(COOKIE_CONSENTIMENTO);
  if (!raw) return null;
  try {
    return JSON.parse(raw);
  } catch {
    return null;
  }
}

function salvarPreferencias(prefs) {
  setCookie(COOKIE_CONSENTIMENTO, JSON.stringify(prefs), COOKIE_DIAS);
}

// ----------------------------------------------------------
// 3. CONTROLE DO BANNER
// ----------------------------------------------------------

function exibirBanner() {
  const banner = document.getElementById('cookie-banner');
  banner?.classList.add('visivel');
}

function ocultarBanner() {
  const banner = document.getElementById('cookie-banner');
  banner?.classList.remove('visivel');
}

// ----------------------------------------------------------
// 4. AÇÕES DOS BOTÕES DO BANNER
// ----------------------------------------------------------

/** Aceitar todos os cookies */
window.cookieAceitarTodos = function () {
  const prefs = { essenciais: true, analiticos: true, marketing: true, data: Date.now() };
  salvarPreferencias(prefs);
  ocultarBanner();
  aplicarPreferencias(prefs);
  dispararEvento('cookies-aceitos', prefs);
};

/** Aceitar apenas cookies essenciais */
window.cookieRecusar = function () {
  const prefs = { essenciais: true, analiticos: false, marketing: false, data: Date.now() };
  salvarPreferencias(prefs);
  ocultarBanner();
  aplicarPreferencias(prefs);
  dispararEvento('cookies-aceitos', prefs);
};

/** Abrir modal de personalização */
window.cookiePersonalizar = function () {
  const modal = document.getElementById('cookie-modal');
  if (modal) {
    // Pré-marcar checkboxes conforme preferência salva
    const prefs = lerPreferencias();
    const checkAnaliticos = document.getElementById('cookie-analiticos');
    if (checkAnaliticos && prefs) {
      checkAnaliticos.checked = prefs.analiticos ?? false;
      document.getElementById('label-analiticos').textContent = checkAnaliticos.checked ? 'Ativado' : 'Desativado';
    }
    modal.style.display = 'flex';
  }
};

/** Fechar modal sem salvar */
window.cookieFecharModal = function () {
  const modal = document.getElementById('cookie-modal');
  if (modal) modal.style.display = 'none';
};

/** Salvar preferências do modal */
window.cookieSalvarPreferencias = function () {
  const analiticos = document.getElementById('cookie-analiticos')?.checked ?? false;

  const prefs = {
    essenciais: true,
    analiticos: analiticos,
    marketing:  false,
    data:       Date.now()
  };

  salvarPreferencias(prefs);
  cookieFecharModal();
  ocultarBanner();
  aplicarPreferencias(prefs);
  dispararEvento('cookies-aceitos', prefs);
};

// Fechar modal clicando fora
document.getElementById('cookie-modal')?.addEventListener('click', function (e) {
  if (e.target === this) cookieFecharModal();
});

// ----------------------------------------------------------
// 5. APLICAR PREFERÊNCIAS (carregar scripts conforme consentimento)
// ----------------------------------------------------------

function aplicarPreferencias(prefs) {
  // Google Analytics 4: carregar somente com consentimento analítico
  if (prefs.analiticos && window._ga4Id) {
    carregarGA4(window._ga4Id);
  }
}

function carregarGA4(id) {
  // Verificar se já foi carregado
  if (window.gtag) return;

  const script = document.createElement('script');
  script.src   = `https://www.googletagmanager.com/gtag/js?id=${id}`;
  script.async = true;
  document.head.appendChild(script);

  window.dataLayer = window.dataLayer || [];
  window.gtag = function () { window.dataLayer.push(arguments); };
  window.gtag('js', new Date());
  window.gtag('config', id, {
    // Anonimizar IP conforme LGPD
    anonymize_ip: true,
    // Não rastrear dados de anúncios pessoais
    allow_google_signals: false,
    allow_ad_personalization_signals: false
  });
}

// ----------------------------------------------------------
// 6. EVENTO CUSTOMIZADO
// ----------------------------------------------------------

function dispararEvento(nome, detalhe) {
  const evento = new CustomEvent(nome, { detail: detalhe, bubbles: true });
  document.dispatchEvent(evento);
}

// ----------------------------------------------------------
// 7. INICIALIZAÇÃO NA CARGA DA PÁGINA
// ----------------------------------------------------------

(function init() {
  const prefs = lerPreferencias();

  if (!prefs) {
    // Primeira visita: mostrar banner após pequeno delay (UX melhor)
    setTimeout(exibirBanner, 1200);
  } else {
    // Consentimento já registrado: aplicar diretamente
    aplicarPreferencias(prefs);
  }
})();
