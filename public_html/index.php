<?php

/**
 * Home — Trivya RH
 *
 * Página principal com as 7 seções do wireframe:
 * Hero → Nichos → Serviços → Diferenciais → Sobre → Depoimentos → Contato
 *
 * @autor    Equipe Trivya RH
 * @versao   1.0.0
 * @data     2025-01-01
 */

declare(strict_types=1);

// Variáveis para o header (SEO e Open Graph)
$tituloPagina    = null; // usará o valor do banco via getConfig()
$descricaoPagina = null;

require_once __DIR__ . '/bootstrap.php';
require_once INCLUDES_PATH . '/header.php';

// ----------------------------------------------------------
// Buscar dados do banco para todas as seções
// Todas as queries têm try/catch: a página renderiza mesmo sem BD
// ----------------------------------------------------------

// ── Dados dinâmicos do CMS ────────────────────────────────
$db = Database::getInstance();

// Stats do hero
$anosStr     = getConfig('sobre_anos_mercado',       '5');
$clientesStr = getConfig('sobre_clientes_atendidos', '200');
$vagasStr    = getConfig('sobre_vagas_preenchidas',  '1500');

// Imagem de fundo — gerenciada pelo admin
$heroBg = getConfig('hero_bg_image', 'assets/img/office-bg.png');

// Serviços do banco
$servicos = [];
try {
    $servicos = $db->fetchAll(
        "SELECT * FROM servicos WHERE ativo = 1 ORDER BY ordem ASC LIMIT 6"
    );
} catch (Exception) {}

// Depoimentos: em produção exige autorizacao_lgpd=1; em dev mostra todos os ativos
$depoimentos = [];
try {
    $isProducao   = defined('APP_ENV') && APP_ENV === 'production';
    $whereDeptos  = $isProducao ? 'ativo = 1 AND autorizacao_lgpd = 1' : 'ativo = 1';
    $depoimentos  = $db->fetchAll(
        "SELECT * FROM depoimentos WHERE {$whereDeptos} ORDER BY destaque DESC, ordem ASC LIMIT 6"
    );
} catch (Exception) {}

// Nichos do letreiro do banco
$nichos = [];
try {
    $nichos = $db->fetchAll(
        "SELECT nome FROM nichos_marquee WHERE ativo = 1 ORDER BY ordem ASC"
    );
} catch (Exception) {}

// Contato
$wppNumero   = getConfig('whatsapp',          '5511999999999');
$wppExibicao = getConfig('telefone_exibicao', formatTelefone($wppNumero));
$emailSite   = getConfig('email_contato',     SITE_EMAIL);
$linkedinUrl = getConfig('linkedin_url',      '#');

// Token CSRF do formulário de contato
$csrfContato = generateCsrf('contato');

?>

<!-- Imagem de fundo gerenciada pelo CMS admin -->
<style>
  body {
    background-image: url('<?= e(SITE_URL . '/' . $heroBg) ?>');
  }
</style>

<!-- ======================================================
     HERO — Banner principal
     ====================================================== -->
<section id="hero">
  <!-- Box translúcido que garante legibilidade sobre a foto -->
  <div class="hero-content-box">
    <span class="hero-eyebrow">HUMANIZAR É O QUE NOS MOVE.</span>

    <h1 class="hero-title">
      Conectamos talentos às oportunidades certas.
    </h1>

    <p class="hero-sub">
      Recrutamento &amp; Seleção humanizado para impulsionar pessoas e transformar negócios.
    </p>

    <div class="hero-actions">
      <a href="#contato" class="btn-primary">Solicitar proposta →</a>
      <a href="#servicos" class="btn-outline">Conheça os serviços</a>
    </div>

    <div class="hero-stats" aria-label="Números da Trivya RH">
      <div>
        <div class="hero-stat-num" aria-label="<?= e($anosStr) ?> anos"><?= e($anosStr) ?>+</div>
        <div class="hero-stat-lbl">Anos no mercado</div>
      </div>
      <div>
        <div class="hero-stat-num" aria-label="<?= e($clientesStr) ?> clientes"><?= e($clientesStr) ?>+</div>
        <div class="hero-stat-lbl">Empresas atendidas</div>
      </div>
      <div>
        <div class="hero-stat-num" aria-label="<?= e($vagasStr) ?> vagas"><?= e($vagasStr) ?>+</div>
        <div class="hero-stat-lbl">Vagas preenchidas</div>
      </div>
    </div>
  </div>

  <!-- Visual decorativo: logo grande dentro de círculo bege -->
  <div class="hero-visual" aria-hidden="true">
    <div class="hero-visual-bg">
      <?= renderLogo('grande', 'header') ?>
    </div>

    <div class="hero-badge">
      <div class="hero-badge-dot"></div>
      <div>
        <div class="hero-badge-text">Atendimento ativo</div>
        <div class="hero-badge-sub">Resposta em até 24h</div>
      </div>
    </div>
  </div>
</section>

<!-- ======================================================
     NICHOS — Strip azul navy com segmentos atendidos
     ====================================================== -->
<?php
// Nichos: fallback se banco vazio
if (empty($nichos)) {
    $nichos = [
        ['nome'=>'Comércio Varejista'],['nome'=>'Facilities'],['nome'=>'Construção Civil'],
        ['nome'=>'Recrutamento Terceirizado'],['nome'=>'Seleção Estratégica'],['nome'=>'RH Estratégico'],
    ];
}
?>
<div class="nichos-strip" aria-label="Segmentos atendidos">
  <div class="nichos-track">
    <?php /* Conjunto original */ foreach ($nichos as $nicho): ?>
      <div class="nicho-item"><span class="nicho-dot"></span> <?= e($nicho['nome']) ?></div>
    <?php endforeach; ?>
    <?php /* Cópia para loop infinito sem salto */ foreach ($nichos as $nicho): ?>
      <div class="nicho-item" aria-hidden="true"><span class="nicho-dot"></span> <?= e($nicho['nome']) ?></div>
    <?php endforeach; ?>
  </div>
</div>

<!-- ======================================================
     SERVIÇOS — 3 cards de segmento
     ====================================================== -->
<section id="servicos">
  <div class="glass-panel">
  <div class="section-header fade-up">
    <div class="section-label">Nossos Serviços</div>
    <h2 class="section-title">
      <?= e(getConfig('servicos_subtitulo', 'Soluções para cada segmento')) ?>
    </h2>
    <p class="section-sub">
      Entendemos as particularidades de cada mercado e entregamos os profissionais certos para cada realidade.
    </p>
  </div>

  <div class="servicos-grid">
    <?php if (!empty($servicos)): ?>
      <?php foreach ($servicos as $srv): ?>
        <article class="servico-card fade-up">
          <div class="servico-icon" aria-hidden="true"><?= e($srv['icone']) ?></div>
          <h3><?= e($srv['titulo']) ?></h3>
          <p><?= e($srv['descricao']) ?></p>
          <a href="<?= e($srv['link'] ?: '#contato') ?>" class="servico-link">Saiba mais →</a>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <!-- Fallback enquanto não há serviços no banco -->
      <article class="servico-card fade-up">
        <div class="servico-icon">🛒</div>
        <h3>Varejo</h3>
        <p>Recrutamento ágil para redes de comércio, lojas e estabelecimentos varejistas.</p>
        <a href="#contato" class="servico-link">Saiba mais →</a>
      </article>
      <article class="servico-card fade-up">
        <div class="servico-icon">🏢</div>
        <h3>Facilities</h3>
        <p>Seleção de profissionais para limpeza, segurança, manutenção e serviços gerais.</p>
        <a href="#contato" class="servico-link">Saiba mais →</a>
      </article>
      <article class="servico-card fade-up">
        <div class="servico-icon">🏗️</div>
        <h3>Construção Civil</h3>
        <p>Mão de obra qualificada para construtoras, empreiteiras e obras.</p>
        <a href="#contato" class="servico-link">Saiba mais →</a>
      </article>
    <?php endif; ?>
  </div>
  </div><!-- /.glass-panel -->
</section>

<!-- ======================================================
     DIFERENCIAIS — fundo navy escuro, 4 cards numerados
     ====================================================== -->
<section id="diferenciais">
  <div class="section-header fade-up">
    <div class="section-label">Por que a Trivya RH?</div>
    <h2 class="section-title">
      <?= e(getConfig('diferenciais_titulo', 'O que nos diferencia')) ?>
    </h2>
    <p class="section-sub">
      Uma consultoria que entende o seu negócio e entrega resultado, sem burocracia.
    </p>
  </div>

  <div class="diferenciais-grid">

    <article class="diferencial-item fade-up">
      <div class="diferencial-num" aria-hidden="true">01</div>
      <h4><?= e(getConfig('diferenciais_item1_titulo', 'Processo direto e sem enrolação')) ?></h4>
      <p><?= e(getConfig('diferenciais_item1_texto',
          'Sem etapas desnecessárias. Você apresenta a necessidade, a gente apresenta os candidatos.')) ?>
      </p>
    </article>

    <article class="diferencial-item fade-up">
      <div class="diferencial-num" aria-hidden="true">02</div>
      <h4><?= e(getConfig('diferenciais_item2_titulo', 'Especialistas no seu mercado')) ?></h4>
      <p><?= e(getConfig('diferenciais_item2_texto',
          'Conhecemos a realidade do varejo, facilities e construção civil. Isso faz toda a diferença na seleção.')) ?>
      </p>
    </article>

    <article class="diferencial-item fade-up">
      <div class="diferencial-num" aria-hidden="true">03</div>
      <h4><?= e(getConfig('diferenciais_item3_titulo', 'Atendimento humano e próximo')) ?></h4>
      <p><?= e(getConfig('diferenciais_item3_texto',
          'Você fala diretamente com quem está cuidando da sua vaga. Sem intermediários, sem call center.')) ?>
      </p>
    </article>

    <article class="diferencial-item fade-up">
      <div class="diferencial-num" aria-hidden="true">04</div>
      <h4><?= e(getConfig('diferenciais_item4_titulo', 'Fit cultural como prioridade')) ?></h4>
      <p><?= e(getConfig('diferenciais_item4_texto',
          'Buscamos o profissional certo para a sua empresa — não apenas o que tem o currículo mais bonito.')) ?>
      </p>
    </article>
  </div>
</section>

<!-- ======================================================
     SOBRE — Fotos das sócias + texto + cards de sócia
     ====================================================== -->
<section id="sobre">

  <!-- Coluna esquerda: fotos das sócias -->
  <div class="sobre-fotos fade-up" aria-hidden="true">
    <div class="foto-socia-card">
      <img src="https://randomuser.me/api/portraits/women/44.jpg"
           alt="Vitória Souza — Sócia-fundadora Trivya RH"
           class="foto-socia"
           loading="lazy">
    </div>
    <div class="foto-socia-card">
      <img src="https://randomuser.me/api/portraits/women/68.jpg"
           alt="Maria Luiza Ferreira — Sócia-fundadora Trivya RH"
           class="foto-socia"
           loading="lazy">
    </div>
  </div>

  <!-- Coluna direita: texto + cards -->
  <div class="glass-panel fade-up">
    <span class="sobre-tag">🤝 Quem somos</span>

    <h2 class="section-title">
      <?= e(getConfig('sobre_titulo', 'Conexão e evolução construídas')) ?>
    </h2>

    <div class="sobre-text">
      <p>
        A Trivya RH nasceu da vontade de transformar a forma como empresas contratam. Somos uma consultoria
        autoral, especializada em recrutamento e seleção de profissionais terceirizados para o comércio
        varejista, facilities e construção civil.
      </p>
      <p>
        Acreditamos que contratar bem é a base de qualquer negócio que cresce. Por isso, nosso processo é
        direto, consultivo e focado no que realmente importa: colocar a pessoa certa no lugar certo, no
        tempo certo.
      </p>
    </div>

    <!-- Cards das sócias -->
    <div class="socias-list">
      <div class="socia-item">
        <div class="socia-avatar">
          <img src="https://randomuser.me/api/portraits/women/44.jpg"
               alt="Vitória Souza"
               loading="lazy">
        </div>
        <div>
          <div class="socia-nome">Vitória Souza</div>
          <div class="socia-cargo">Sócia-fundadora · 6 anos em Recrutamento &amp; Seleção</div>
        </div>
      </div>

      <div class="socia-item">
        <div class="socia-avatar">
          <img src="https://randomuser.me/api/portraits/women/68.jpg"
               alt="Maria Luiza Ferreira"
               loading="lazy">
        </div>
        <div>
          <div class="socia-nome">Maria Luiza Ferreira</div>
          <div class="socia-cargo">Sócia-fundadora · Especialista em RH Operacional</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ======================================================
     DEPOIMENTOS — Cards do banco (apenas com autorização LGPD)
     ====================================================== -->
<section id="depoimentos">
  <div class="glass-panel">
  <div class="section-header center fade-up">
    <div class="section-label">Clientes</div>
    <h2 class="section-title">O que dizem sobre nós</h2>
    <p class="section-sub">A confiança dos nossos clientes é o que nos move.</p>
  </div>

  <div class="depoimentos-grid">
    <?php if (!empty($depoimentos)): ?>
      <?php foreach ($depoimentos as $dep): ?>
        <article class="depoimento-card <?= !empty($dep['destaque']) ? 'depoimento-destaque' : '' ?> fade-up">

          <?php if (!empty($dep['destaque'])): ?>
            <span class="badge-destaque">⭐ Cliente em destaque</span>
          <?php endif; ?>

          <div class="depoimento-stars" aria-label="<?= (int) $dep['nota'] ?> de 5 estrelas">
            <?= str_repeat('★', (int) $dep['nota']) . str_repeat('☆', 5 - (int) $dep['nota']) ?>
          </div>

          <p class="depoimento-texto">"<?= e($dep['texto']) ?>"</p>

          <div class="depoimento-autor">
            <?php if (!empty($dep['foto_url'])): ?>
              <img src="<?= e($dep['foto_url']) ?>"
                   alt="<?= e($dep['nome']) ?>"
                   loading="lazy">
            <?php else: ?>
              <div class="depoimento-avatar" aria-hidden="true">
                <?= mb_strtoupper(mb_substr($dep['nome'], 0, 1, 'UTF-8'), 'UTF-8') ?>
              </div>
            <?php endif; ?>
            <div>
              <div class="depoimento-nome"><?= e($dep['nome']) ?></div>
              <?php if (!empty($dep['cargo'])): ?>
                <div class="depoimento-empresa"><?= e($dep['cargo']) ?></div>
              <?php endif; ?>
              <?php if (!empty($dep['cliente_desde'])): ?>
                <div class="depoimento-desde">
                  Cliente desde <?= date('Y', strtotime($dep['cliente_desde'])) ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <!-- Nenhum depoimento autorizado ainda — seção oculta visualmente -->
      <p style="grid-column:1/-1;text-align:center;color:#8A9BB0;font-size:15px;padding:32px 0;">
        Em breve os depoimentos reais dos nossos clientes aparecerão aqui.
      </p>
    <?php endif; ?>
  </div>
  </div><!-- /.glass-panel -->
</section>

<!-- ======================================================
     RECOMENDAÇÕES NO LINKEDIN — Prova social externa
     ====================================================== -->
<section id="linkedin-recomendacoes" class="section-linkedin">
  <div class="linkedin-header fade-up">
    <div class="linkedin-badge">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="#0A66C2" aria-hidden="true">
        <path d="M20.5 2h-17A1.5 1.5 0 002 3.5v17A1.5 1.5 0 003.5 22h17a1.5 1.5 0 001.5-1.5v-17A1.5 1.5 0 0020.5 2zM8 19H5v-9h3zM6.5 8.25A1.75 1.75 0 118.3 6.5a1.78 1.78 0 01-1.8 1.75zM19 19h-3v-4.74c0-1.42-.6-1.93-1.38-1.93A1.74 1.74 0 0013 14.19a.66.66 0 000 .14V19h-3v-9h2.9v1.3a3.11 3.11 0 012.7-1.4c1.55 0 3.36.86 3.36 3.66z"/>
      </svg>
      <span>LinkedIn</span>
    </div>
    <h2 class="section-title">Veja nossas recomendações</h2>
    <p class="section-sub">
      Conheça mais sobre nosso trabalho e veja recomendações reais
      de clientes e parceiros no nosso perfil profissional.
    </p>
  </div>

  <div class="linkedin-cta-card fade-up">
    <div class="linkedin-cta-content">
      <h3>Acompanhe a Trivya RH no LinkedIn</h3>
      <p>
        No nosso perfil você encontra recomendações verificadas,
        cases reais, conteúdos sobre o universo de R&amp;S e novidades
        da consultoria.
      </p>
      <a href="<?= e($linkedinUrl) ?>"
         target="_blank"
         rel="noopener noreferrer"
         class="btn-linkedin"
         aria-label="Visitar perfil da Trivya RH no LinkedIn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M20.5 2h-17A1.5 1.5 0 002 3.5v17A1.5 1.5 0 003.5 22h17a1.5 1.5 0 001.5-1.5v-17A1.5 1.5 0 0020.5 2zM8 19H5v-9h3zM6.5 8.25A1.75 1.75 0 118.3 6.5a1.78 1.78 0 01-1.8 1.75zM19 19h-3v-4.74c0-1.42-.6-1.93-1.38-1.93A1.74 1.74 0 0013 14.19a.66.66 0 000 .14V19h-3v-9h2.9v1.3a3.11 3.11 0 012.7-1.4c1.55 0 3.36.86 3.36 3.66z"/>
        </svg>
        Visitar perfil no LinkedIn
      </a>
    </div>
    <div class="linkedin-visual">
      <div class="linkedin-stats">
        <div class="linkedin-stat">
          <strong>Recomendações verificadas</strong>
          <span>de clientes reais no LinkedIn</span>
        </div>
        <div class="linkedin-stat">
          <strong>Perfis públicos</strong>
          <span>você pode confirmar a identidade</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ======================================================
     CONTATO — Info à esquerda + formulário à direita
     ====================================================== -->
<section id="contato">

  <!-- Coluna esquerda: informações de contato -->
  <div class="glass-panel fade-up">
    <div class="section-label">Contato</div>
    <h2 class="section-title">Vamos conversar?</h2>
    <p class="section-sub">
      Conte sua necessidade e a gente monta uma proposta sob medida para o seu negócio.
    </p>

    <div class="contato-opcoes">

      <a href="https://wa.me/<?= e($wppNumero) ?>?text=<?= urlencode('Olá! Vim pelo site da Trivya RH e gostaria de mais informações.') ?>"
         class="contato-opcao"
         target="_blank"
         rel="noopener noreferrer"
         aria-label="Falar pelo WhatsApp">
        <div class="contato-opcao-icon" aria-hidden="true">💬</div>
        <div>
          <div class="contato-opcao-label">WhatsApp</div>
          <div class="contato-opcao-val"><?= e($wppExibicao) ?></div>
        </div>
      </a>

      <a href="mailto:<?= e($emailSite) ?>"
         class="contato-opcao"
         aria-label="Enviar e-mail">
        <div class="contato-opcao-icon" aria-hidden="true">✉️</div>
        <div>
          <div class="contato-opcao-label">E-mail</div>
          <div class="contato-opcao-val"><?= e($emailSite) ?></div>
        </div>
      </a>

      <?php if ($linkedinUrl && $linkedinUrl !== '#'): ?>
      <a href="<?= e($linkedinUrl) ?>"
         class="contato-opcao"
         target="_blank"
         rel="noopener noreferrer"
         aria-label="Visitar LinkedIn">
        <div class="contato-opcao-icon" aria-hidden="true">💼</div>
        <div>
          <div class="contato-opcao-label">LinkedIn</div>
          <div class="contato-opcao-val">linkedin.com/company/trivyarh</div>
        </div>
      </a>
      <?php endif; ?>

    </div>
  </div>

  <!-- Coluna direita: formulário multi-step -->
  <div class="form-card form-multistep fade-up" id="form-empresa">

    <!-- Progress bar -->
    <div class="form-progress">
      <div class="progress-step active" data-step="1">
        <div class="progress-number">1</div>
        <div class="progress-label">Sua empresa</div>
      </div>
      <div class="progress-line"></div>
      <div class="progress-step" data-step="2">
        <div class="progress-number">2</div>
        <div class="progress-label">Contratação</div>
      </div>
      <div class="progress-line"></div>
      <div class="progress-step" data-step="3">
        <div class="progress-number">3</div>
        <div class="progress-label">Detalhes</div>
      </div>
    </div>

    <div class="form-header">
      <h3>Solicite uma proposta personalizada</h3>
      <p class="form-reassurance">⏱️ Leva menos de 2 minutos · Resposta em até 24h</p>
    </div>

    <?php foreach (getFlash('erro') as $m): ?>
      <div style="background:#FEE2E2;border:1px solid #FCA5A5;border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#B91C1C;font-size:13px;"><?= e($m) ?></div>
    <?php endforeach; ?>

    <form id="form-contato-empresa"
          action="<?= e(SITE_URL) ?>/api/contato.php"
          method="POST"
          novalidate>
      <?= csrfField('contato') ?>
      <input type="text" name="website" class="honeypot" tabindex="-1" autocomplete="off" aria-hidden="true">

      <!-- STEP 1: Dados da empresa -->
      <div class="form-step active" data-step="1">
        <div class="form-group">
          <label for="empresa_nome">Nome da empresa *</label>
          <input type="text" id="empresa_nome" name="empresa" required maxlength="150" autocomplete="organization" placeholder="Ex: Grupo Comercial Ltda.">
        </div>
        <div class="form-group">
          <label for="nome_resp">Nome do responsável *</label>
          <input type="text" id="nome_resp" name="nome" required maxlength="100" autocomplete="name" placeholder="Ex: Carlos Henrique">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="wpp_empresa">WhatsApp *</label>
            <input type="tel" id="wpp_empresa" name="telefone" required placeholder="(11) 99999-9999" maxlength="15" autocomplete="tel">
          </div>
          <div class="form-group">
            <label for="email_empresa">E-mail *</label>
            <input type="email" id="email_empresa" name="email" required maxlength="254" autocomplete="email" placeholder="carlos@empresa.com.br">
          </div>
        </div>
        <div class="form-step-actions form-step-actions--right">
          <button type="button" class="btn-primary btn-next" data-next="2">Continuar →</button>
        </div>
      </div>

      <!-- STEP 2: Contratação -->
      <div class="form-step" data-step="2">
        <div class="form-group">
          <label for="cidade_estado">Cidade/Estado *</label>
          <input type="text" id="cidade_estado" name="cidade_estado" required maxlength="150" placeholder="Ex: São Paulo / SP">
        </div>
        <div class="form-group">
          <label for="cargo_contratar">Cargo ou área que deseja contratar *</label>
          <input type="text" id="cargo_contratar" name="cargo_area_contratar" required maxlength="200" placeholder="Ex: Operador de loja, Auxiliar de limpeza">
        </div>
        <div class="form-group">
          <label for="segmento">Segmento da empresa</label>
          <select id="segmento" name="segmento">
            <option value="">Selecione...</option>
            <option value="varejo">Comércio Varejista</option>
            <option value="facilities">Facilities</option>
            <option value="construcao_civil">Construção Civil</option>
            <option value="outro">Outro</option>
          </select>
        </div>
        <div class="form-group">
          <label>Quantas vagas pretende preencher? *</label>
          <div class="radio-cards radio-group" data-required="qtd_vagas">
            <label class="radio-card">
              <input type="radio" name="qtd_vagas" value="1" required>
              <span class="radio-card-content"><strong>1 vaga</strong><small>Contratação pontual</small></span>
            </label>
            <label class="radio-card">
              <input type="radio" name="qtd_vagas" value="2-5">
              <span class="radio-card-content"><strong>2 a 5 vagas</strong><small>Pequena equipe</small></span>
            </label>
            <label class="radio-card">
              <input type="radio" name="qtd_vagas" value="5-10">
              <span class="radio-card-content"><strong>5 a 10 vagas</strong><small>Time completo</small></span>
            </label>
          </div>
        </div>
        <div class="form-step-actions">
          <button type="button" class="btn-outline btn-prev" data-prev="1">← Voltar</button>
          <button type="button" class="btn-primary btn-next" data-next="3">Continuar →</button>
        </div>
      </div>

      <!-- STEP 3: Detalhes -->
      <div class="form-step" data-step="3">
        <div class="form-group">
          <label for="dificuldade">Qual a maior dificuldade no recrutamento hoje?</label>
          <textarea id="dificuldade" name="dificuldade" rows="3" maxlength="1000"
                    placeholder="Conte-nos sobre os principais desafios da sua empresa..."></textarea>
          <small class="field-hint">Opcional · Ajuda nossa equipe a entender melhor sua necessidade</small>
        </div>
        <div class="form-group">
          <label>A contratação possui urgência?</label>
          <div class="radio-inline radio-group">
            <label class="radio-pill"><input type="radio" name="urgencia" value="1"><span>Sim, urgente</span></label>
            <label class="radio-pill"><input type="radio" name="urgencia" value="0"><span>Não, sem pressa</span></label>
          </div>
        </div>
        <div class="form-group">
          <label>Gostaria de receber contato via WhatsApp? *</label>
          <div class="radio-inline radio-group" data-required="aceita_whatsapp">
            <label class="radio-pill"><input type="radio" name="aceita_whatsapp" value="1" required checked><span>Sim, prefiro WhatsApp</span></label>
            <label class="radio-pill"><input type="radio" name="aceita_whatsapp" value="0"><span>Não, prefiro e-mail</span></label>
          </div>
        </div>
        <div class="form-group form-lgpd">
          <label class="checkbox-lgpd">
            <input type="checkbox" name="consentimento_lgpd" value="1" required>
            <span>Concordo com a <a href="<?= e(SITE_URL) ?>/politica-privacidade" target="_blank">Política de Privacidade</a> e autorizo o contato da equipe Trivya RH.</span>
          </label>
        </div>
        <div class="form-step-actions">
          <button type="button" class="btn-outline btn-prev" data-prev="2">← Voltar</button>
          <button type="submit" class="btn-primary btn-submit">Solicitar Proposta →</button>
        </div>
      </div>
    </form>
  </div>
</section>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>

