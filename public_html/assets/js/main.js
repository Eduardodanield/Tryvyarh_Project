/**
 * main.js — Trivya RH
 * Comportamentos globais: scroll suave, fade-up, menu mobile, header shrink
 * Vanilla ES6+ sem dependências externas
 * @autor  Equipe Trivya RH  @versao 1.0.0  @data 2025-01-01
 */

'use strict';

// ----------------------------------------------------------
// 1. ANIMAÇÃO DE ENTRADA (opcional, progressiva)
//    Elementos com .fade-up são SEMPRE visíveis por padrão (CSS).
//    Quando JS carrega, adiciona .animar para ativar a transição
//    suave nos que ainda não entraram no viewport.
// ----------------------------------------------------------

const fadeObserver = new IntersectionObserver(
  (entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        fadeObserver.unobserve(entry.target);
      }
    });
  },
  { threshold: 0.05 }
);

// Adicionar .animar SOMENTE em elementos abaixo do fold inicial
document.querySelectorAll('.fade-up').forEach(function(el) {
  var rect = el.getBoundingClientRect();
  // Se o elemento estiver abaixo do viewport, marcar para animar
  if (rect.top > window.innerHeight) {
    el.classList.add('animar');
    fadeObserver.observe(el);
  }
  // Elementos já visíveis ficam com opacity:1 sem animação
});

// ----------------------------------------------------------
// 2. HEADER — classe scrolled ao rolar
// ----------------------------------------------------------

const siteHeader = document.getElementById('site-header');

const verificarScroll = () => {
  siteHeader?.classList.toggle('scrolled', window.scrollY > 20);
};

window.addEventListener('scroll', verificarScroll, { passive: true });
verificarScroll();

// ----------------------------------------------------------
// 3. MENU MOBILE
// ----------------------------------------------------------

const mobileToggle = document.getElementById('nav-mobile-toggle');
const mobileMenu   = document.getElementById('nav-mobile-menu');

function fecharMenu() {
  mobileMenu?.classList.remove('open');
  mobileToggle?.setAttribute('aria-expanded', 'false');
  document.body.style.overflow = '';
}

mobileToggle?.addEventListener('click', () => {
  const aberto = mobileMenu?.classList.toggle('open');
  mobileToggle.setAttribute('aria-expanded', aberto ? 'true' : 'false');
  document.body.style.overflow = aberto ? 'hidden' : '';
});

mobileMenu?.querySelectorAll('a').forEach(a => a.addEventListener('click', fecharMenu));

document.addEventListener('keydown', e => {
  if (e.key === 'Escape' && mobileMenu?.classList.contains('open')) {
    fecharMenu();
    mobileToggle?.focus();
  }
});

// ----------------------------------------------------------
// 4. SMOOTH SCROLL PARA ÂNCORAS
// ----------------------------------------------------------

const headerH = () => siteHeader?.offsetHeight ?? 72;

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    const href = this.getAttribute('href');
    const alvo = href === '#' ? null : document.querySelector(href);
    if (alvo) {
      e.preventDefault();
      const topo = alvo.getBoundingClientRect().top + window.scrollY - headerH() - 16;
      window.scrollTo({ top: topo, behavior: 'smooth' });
      history.pushState(null, '', href);
    }
  });
});

// ----------------------------------------------------------
// 5. ACTIVE LINK NO NAV AO ROLAR
// ----------------------------------------------------------

const secoes   = document.querySelectorAll('section[id]');
const navLinks = document.querySelectorAll('.nav-links a');

const ativarLink = () => {
  const scrollPos = window.scrollY + headerH() + 40;
  secoes.forEach(secao => {
    const topo  = secao.offsetTop;
    const fundo = topo + secao.offsetHeight;
    if (scrollPos >= topo && scrollPos < fundo) {
      navLinks.forEach(link => {
        link.classList.remove('ativo');
        if (link.getAttribute('href') === `#${secao.id}`) link.classList.add('ativo');
      });
    }
  });
};

window.addEventListener('scroll', ativarLink, { passive: true });

// ----------------------------------------------------------
// 6. MÁSCARA DE TELEFONE (sobrescrita pelo form.js se carregado)
// ----------------------------------------------------------

// Delegação de evento: aplica a qualquer input[type="tel"] da página
document.addEventListener('input', function (e) {
  if (e.target.matches('input[type="tel"]')) {
    aplicarMascaraTelefone(e.target);
  }
});

function aplicarMascaraTelefone(input) {
  let v = input.value.replace(/\D/g, '').slice(0, 11);
  if (v.length <= 10) {
    v = v.replace(/^(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
  } else {
    v = v.replace(/^(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
  }
  input.value = v;
}

// ----------------------------------------------------------
// 7. LAZY LOADING DE IMAGENS (para browsers sem suporte nativo)
// ----------------------------------------------------------

if ('loading' in HTMLImageElement.prototype) {
  // Browser suporta lazy loading nativo — nada a fazer
} else {
  // Polyfill simplificado com IntersectionObserver
  const imgObserver = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target;
        img.src = img.dataset.src;
        imgObserver.unobserve(img);
      }
    });
  });

  document.querySelectorAll('img[data-src]').forEach(img => imgObserver.observe(img));
}
