'use strict';

// ============================================================
// 1. MÁSCARAS
// ============================================================

function mascaraTelefone(input) {
  let v = input.value.replace(/\D/g, '').slice(0, 11);
  if (v.length > 6) {
    v = v.replace(/^(\d{2})(\d{4,5})(\d{0,4})/, (_, a, b, c) =>
      c ? `(${a}) ${b}-${c}` : `(${a}) ${b}`
    );
  } else if (v.length > 2) {
    v = `(${v.slice(0,2)}) ${v.slice(2)}`;
  } else if (v.length > 0) {
    v = `(${v}`;
  }
  input.value = v;
}

// ============================================================
// 2. VALIDAÇÃO DE CAMPO INDIVIDUAL
// ============================================================

function exibirErro(grupo, msg) {
  grupo.classList.add('tem-erro');
  let span = grupo.querySelector('.form-erro-msg');
  if (!span) {
    span = document.createElement('span');
    span.className = 'form-erro-msg';
    span.style.cssText = 'color:#E53E3E;font-size:12px;display:block;margin-top:4px;';
    grupo.appendChild(span);
  }
  span.textContent = msg;
  const campo = grupo.querySelector('input:not([type=radio]):not([type=checkbox]), select, textarea');
  campo?.classList.add('erro');
  campo?.setAttribute('aria-invalid', 'true');
}

function limparErro(grupo) {
  grupo.classList.remove('tem-erro');
  grupo.querySelector('.form-erro-msg')?.remove();
  const campo = grupo.querySelector('input, select, textarea');
  campo?.classList.remove('erro');
  campo?.removeAttribute('aria-invalid');
}

function validarCampo(campo) {
  const grupo = campo.closest('.form-group');
  if (!grupo) return true;

  const val   = campo.value.trim();
  const label = grupo.querySelector('label')?.textContent.replace(/[*]/g, '').trim() || 'Campo';
  const obrig = campo.required;

  if (obrig && !val) {
    exibirErro(grupo, `${label} é obrigatório.`);
    return false;
  }
  if (!val && !obrig) { limparErro(grupo); return true; }

  if (campo.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(val)) {
    exibirErro(grupo, 'Informe um e-mail válido.'); return false;
  }
  if (campo.type === 'tel') {
    const d = val.replace(/\D/g, '');
    if (d.length < 10) { exibirErro(grupo, 'Telefone inválido (DDD + número).'); return false; }
  }
  if (campo.type === 'number') {
    const n = Number(val);
    if (campo.min && n < Number(campo.min)) { exibirErro(grupo, `Valor mínimo: ${campo.min}.`); return false; }
    if (campo.max && n > Number(campo.max)) { exibirErro(grupo, `Valor máximo: ${campo.max}.`); return false; }
  }

  limparErro(grupo); return true;
}

// ============================================================
// 3. FORMULÁRIO MULTI-STEP
// ============================================================

function iniciarMultiStep(container) {
  const form          = container.querySelector('form');
  if (!form) return;

  const steps         = container.querySelectorAll('.form-step');
  const progSteps     = container.querySelectorAll('.progress-step');
  const progLines     = container.querySelectorAll('.progress-line');
  const btnsNext      = container.querySelectorAll('.btn-next');
  const btnsPrev      = container.querySelectorAll('.btn-prev');
  let   currentStep   = 1;

  function mostrarStep(num) {
    steps.forEach(s => s.classList.remove('active'));
    container.querySelector(`.form-step[data-step="${num}"]`)?.classList.add('active');

    progSteps.forEach((p, i) => {
      const n = i + 1;
      p.classList.toggle('active', n === num);
      p.classList.toggle('completed', n < num);
    });
    progLines.forEach((l, i) => l.classList.toggle('completed', i + 1 < num));

    currentStep = num;
    if (window.innerWidth < 768) {
      container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  function validarStep(num) {
    const stepEl = container.querySelector(`.form-step[data-step="${num}"]`);
    if (!stepEl) return false;
    let ok = true;

    // Campos de texto/select/textarea/number/date/file
    stepEl.querySelectorAll('input:not([type=radio]):not([type=checkbox]):not([name=website]), select, textarea').forEach(f => {
      if (!validarCampo(f)) ok = false;
    });

    // Radio groups obrigatórios — detectar pelo primeiro radio required do grupo
    const radiosRequired = new Set();
    stepEl.querySelectorAll('input[type=radio][required]').forEach(r => radiosRequired.add(r.name));
    // Também detectar grupos marcados com data-required
    stepEl.querySelectorAll('.radio-group[data-required]').forEach(g => radiosRequired.add(g.dataset.required));

    radiosRequired.forEach(name => {
      const checked = stepEl.querySelector(`input[name="${name}"]:checked`);
      if (!checked) {
        ok = false;
        const grupo = stepEl.querySelector(`input[name="${name}"]`)?.closest('.form-group');
        if (grupo) exibirErro(grupo, 'Selecione uma opção.');
      }
    });

    // Checkbox LGPD
    stepEl.querySelectorAll('input[type=checkbox][required]').forEach(cb => {
      if (!cb.checked) {
        ok = false;
        const grupo = cb.closest('.form-group');
        if (grupo) exibirErro(grupo, 'Você precisa aceitar os termos.');
      }
    });

    if (!ok) {
      const first = stepEl.querySelector('.tem-erro, .form-erro-msg');
      first?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    return ok;
  }

  btnsNext.forEach(btn => {
    btn.addEventListener('click', () => {
      if (validarStep(currentStep)) mostrarStep(parseInt(btn.dataset.next));
    });
  });

  btnsPrev.forEach(btn => {
    btn.addEventListener('click', () => mostrarStep(parseInt(btn.dataset.prev)));
  });

  form.addEventListener('submit', e => {
    if (!validarStep(currentStep)) { e.preventDefault(); return; }

    // Honeypot
    if (form.querySelector('[name="website"]')?.value) { e.preventDefault(); return; }

    const btn = form.querySelector('.btn-submit');
    if (btn) { btn.disabled = true; btn.textContent = '⏳ Enviando...'; }
  });

  // Limpeza de erro em tempo real
  form.querySelectorAll('input, select, textarea').forEach(f => {
    f.addEventListener('blur', () => validarCampo(f));
    f.addEventListener('input', () => {
      const g = f.closest('.form-group');
      if (g?.classList.contains('tem-erro')) limparErro(g);
    });
    if (f.type === 'tel') f.addEventListener('input', () => mascaraTelefone(f));
  });
}

// ============================================================
// 4. UPLOAD VISUAL (área de drag-and-drop)
// ============================================================

function iniciarUpload(area) {
  const input = area.querySelector('input[type="file"]');
  const info  = area.querySelector('.file-info');
  if (!input) return;

  input.addEventListener('change', () => atualizarUpload());

  ['dragover','dragenter'].forEach(ev => {
    area.addEventListener(ev, e => { e.preventDefault(); area.classList.add('drag-over'); });
  });
  ['dragleave','drop'].forEach(ev => {
    area.addEventListener(ev, e => {
      e.preventDefault(); area.classList.remove('drag-over');
    });
  });
  area.addEventListener('drop', e => {
    e.preventDefault();
    if (e.dataTransfer.files[0]) {
      input.files = e.dataTransfer.files;
      atualizarUpload();
    }
  });

  function atualizarUpload() {
    const f = input.files[0];
    if (!f) return;
    const mb = (f.size / 1048576).toFixed(1);
    const maxMb = 10;
    if (f.size > maxMb * 1024 * 1024) {
      const grupo = input.closest('.form-group');
      if (grupo) exibirErro(grupo, `Arquivo muito grande. Máximo ${maxMb} MB.`);
      input.value = '';
      area.classList.remove('has-file');
      return;
    }
    area.classList.add('has-file');
    if (info) info.textContent = `✅ ${f.name} (${mb} MB)`;
  }
}

// ============================================================
// 5. FORMULÁRIO SIMPLES (legado — sem multi-step)
// ============================================================

function configurarFormulario(form) {
  if (form.closest('.form-multistep')) return; // já tratado pelo multi-step

  let enviando = false;
  const btn    = form.querySelector('.btn-form');

  form.querySelectorAll('input, select, textarea').forEach(f => {
    if (f.type === 'tel') f.addEventListener('input', () => mascaraTelefone(f));
    f.addEventListener('blur', () => validarCampo(f));
    f.addEventListener('input', () => {
      const g = f.closest('.form-group');
      if (g?.classList.contains('tem-erro')) limparErro(g);
    });
  });

  form.addEventListener('submit', function(e) {
    if (enviando) { e.preventDefault(); return; }
    if (form.querySelector('[name="website"]')?.value) { e.preventDefault(); return; }

    let ok = true;
    form.querySelectorAll('input:not([name=website]):not([type=hidden]), select, textarea').forEach(f => {
      if (!validarCampo(f)) ok = false;
    });

    const lgpd = form.querySelector('[name="lgpd_consentimento"]');
    if (lgpd && !lgpd.checked) {
      const g = lgpd.closest('.form-group');
      if (g) exibirErro(g, 'Você precisa aceitar a Política de Privacidade.');
      ok = false;
    }

    if (!ok) {
      e.preventDefault();
      form.querySelector('.tem-erro')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }

    enviando = true;
    if (btn) { btn.disabled = true; btn.textContent = 'Enviando...'; }
    setTimeout(() => { enviando = false; if (btn) { btn.disabled = false; } }, 30000);
  });
}

// ============================================================
// 6. INICIALIZAÇÃO
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.form-multistep').forEach(iniciarMultiStep);
  document.querySelectorAll('form[data-js="formulario"]').forEach(configurarFormulario);
  document.querySelectorAll('.file-upload-area').forEach(iniciarUpload);
});
