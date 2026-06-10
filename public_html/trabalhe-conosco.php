<?php
/**
 * Trabalhe Conosco — Banco de Talentos — Trivya RH
 * Formulário multi-step em 3 etapas conforme especificação oficial.
 */
declare(strict_types=1);

$tituloPagina    = 'Banco de Talentos — Envie seu Currículo | Trivya RH';
$descricaoPagina = 'Cadastre-se no banco de talentos da Trivya RH e seja encontrado pelas melhores empresas de varejo, facilities e construção civil em São Paulo.';

require_once __DIR__ . '/bootstrap.php';
require_once INCLUDES_PATH . '/header.php';

$csrfCandidato = generateCsrf('candidato');
$flashErro     = getFlash('erro');
$flashSucesso  = getFlash('sucesso');
?>

<!-- Hero curto da página -->
<div class="page-form-container">
  <div class="page-form-hero">
    <span class="section-label">Banco de Talentos</span>
    <h1>Faça parte do nosso time</h1>
    <p>
      Cadastre seu currículo e seja considerado para as melhores
      oportunidades em varejo, facilities e construção civil.
    </p>
  </div>

  <?php foreach ($flashErro as $m): ?>
    <div style="background:#FEE2E2;border:1px solid #FCA5A5;border-radius:8px;padding:14px 16px;margin-bottom:20px;color:#B91C1C;font-size:14px;"><?= e($m) ?></div>
  <?php endforeach; ?>
  <?php foreach ($flashSucesso as $m): ?>
    <div style="background:#D1FAE5;border:1px solid #6EE7B7;border-radius:8px;padding:14px 16px;margin-bottom:20px;color:#065F46;font-size:14px;"><?= e($m) ?></div>
  <?php endforeach; ?>

  <div class="form-card form-multistep" id="form-candidato">

    <!-- Progress bar -->
    <div class="form-progress">
      <div class="progress-step active" data-step="1">
        <div class="progress-number">1</div>
        <div class="progress-label">Dados pessoais</div>
      </div>
      <div class="progress-line"></div>
      <div class="progress-step" data-step="2">
        <div class="progress-number">2</div>
        <div class="progress-label">Formação</div>
      </div>
      <div class="progress-line"></div>
      <div class="progress-step" data-step="3">
        <div class="progress-number">3</div>
        <div class="progress-label">Currículo</div>
      </div>
    </div>

    <div class="form-header">
      <h3>Cadastre seus dados profissionais</h3>
      <p class="form-reassurance">🔒 Dados protegidos pela LGPD · Usados só para R&amp;S</p>
    </div>

    <form action="<?= e(SITE_URL) ?>/api/candidato.php"
          method="POST"
          enctype="multipart/form-data"
          novalidate>

      <input type="hidden" name="<?= CSRF_FIELD_NAME ?>" value="<?= e($csrfCandidato) ?>">
      <input type="text" name="website" class="honeypot" tabindex="-1" autocomplete="off" aria-hidden="true">

      <!-- STEP 1: Dados pessoais -->
      <div class="form-step active" data-step="1">
        <div class="form-group">
          <label for="nome">Nome completo *</label>
          <input type="text" id="nome" name="nome" required maxlength="100" autocomplete="name" placeholder="Maria da Silva">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="idade">Idade *</label>
            <input type="number" id="idade" name="idade" required min="16" max="80" placeholder="Ex: 25">
          </div>
          <div class="form-group">
            <label for="data_nascimento">Data de nascimento *</label>
            <input type="date" id="data_nascimento" name="data_nascimento" required
                   max="<?= date('Y-m-d') ?>">
          </div>
        </div>
        <div class="form-group">
          <label for="cidade_bairro">Cidade e bairro onde mora *</label>
          <input type="text" id="cidade_bairro" name="cidade_bairro" required maxlength="200" placeholder="Ex: São Paulo — Mooca">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="telefone">WhatsApp *</label>
            <input type="tel" id="telefone" name="telefone" required placeholder="(11) 99999-9999" maxlength="15" autocomplete="tel">
          </div>
          <div class="form-group">
            <label for="email">E-mail *</label>
            <input type="email" id="email" name="email" required maxlength="254" autocomplete="email" placeholder="maria@email.com">
          </div>
        </div>
        <div class="form-step-actions form-step-actions--right">
          <button type="button" class="btn-primary btn-next" data-next="2">Continuar →</button>
        </div>
      </div>

      <!-- STEP 2: Formação e interesse -->
      <div class="form-step" data-step="2">
        <div class="form-group">
          <label>Em qual área você tem interesse? *</label>
          <div class="radio-cards radio-cards--wrap radio-group" data-required="area_interesse">
            <?php
            $areas = [
              'administrativo'   => 'Administrativo',
              'recursos_humanos' => 'Recursos Humanos',
              'atendimento'      => 'Atendimento',
              'recepcao'         => 'Recepção',
              'comercial_vendas' => 'Comercial/Vendas',
              'marketing'        => 'Marketing',
              'operacional'      => 'Operacional',
              'outro'            => 'Outro',
            ];
            foreach ($areas as $val => $label):
            ?>
              <label class="radio-card">
                <input type="radio" name="area_interesse" value="<?= e($val) ?>" required>
                <span class="radio-card-content"><strong><?= e($label) ?></strong></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="form-group">
          <label for="escolaridade">Escolaridade *</label>
          <select id="escolaridade" name="escolaridade" required>
            <option value="">Selecione...</option>
            <option value="Ensino Fundamental Incompleto">Ensino Fundamental Incompleto</option>
            <option value="Ensino Fundamental Completo">Ensino Fundamental Completo</option>
            <option value="Ensino Médio Incompleto">Ensino Médio Incompleto</option>
            <option value="Ensino Médio Completo">Ensino Médio Completo</option>
            <option value="Ensino Técnico">Ensino Técnico</option>
            <option value="Ensino Superior Incompleto">Ensino Superior Incompleto</option>
            <option value="Ensino Superior Completo">Ensino Superior Completo</option>
            <option value="Pós-graduação">Pós-graduação</option>
          </select>
        </div>

        <div class="form-group">
          <label>Está estudando atualmente? *</label>
          <div class="radio-inline radio-group" data-required="esta_estudando">
            <label class="radio-pill"><input type="radio" name="esta_estudando" value="1" required><span>Sim</span></label>
            <label class="radio-pill"><input type="radio" name="esta_estudando" value="0"><span>Não</span></label>
          </div>
        </div>

        <div class="form-step-actions">
          <button type="button" class="btn-outline btn-prev" data-prev="1">← Voltar</button>
          <button type="button" class="btn-primary btn-next" data-next="3">Continuar →</button>
        </div>
      </div>

      <!-- STEP 3: Currículo e autorização -->
      <div class="form-step" data-step="3">
        <div class="form-group">
          <label>Currículo *</label>
          <div class="file-upload-area" id="upload-area-cv" role="button" tabindex="0"
               aria-label="Clique para selecionar o currículo">
            <input type="file" id="curriculo" name="curriculo" accept=".pdf,.doc,.docx" required>
            <div class="file-upload-icon" aria-hidden="true">📄</div>
            <div class="file-upload-text">Clique aqui ou arraste o arquivo</div>
            <div class="file-upload-hint">PDF, DOC ou DOCX · máximo 10 MB</div>
            <div class="file-info" id="cv-info"></div>
          </div>
        </div>

        <div class="form-group">
          <label>Autoriza o armazenamento dos seus dados para futuras oportunidades? *</label>
          <div class="radio-inline radio-group" data-required="autorizacao_lgpd">
            <label class="radio-pill"><input type="radio" name="autorizacao_lgpd" value="1" required><span>Sim, autorizo</span></label>
            <label class="radio-pill"><input type="radio" name="autorizacao_lgpd" value="0"><span>Não, somente esta vaga</span></label>
          </div>
        </div>

        <div class="form-group form-lgpd">
          <label class="checkbox-lgpd">
            <input type="checkbox" name="lgpd_consentimento" value="1" required>
            <span>Li e aceito a <a href="<?= e(SITE_URL) ?>/politica-privacidade" target="_blank">Política de Privacidade</a> e autorizo o tratamento dos meus dados para recrutamento e seleção.</span>
          </label>
        </div>

        <div class="form-step-actions">
          <button type="button" class="btn-outline btn-prev" data-prev="2">← Voltar</button>
          <button type="submit" class="btn-primary btn-submit">Enviar Cadastro →</button>
        </div>
      </div>

    </form>
  </div>

  <p style="text-align:center;margin-top:20px;font-size:13px;color:#8A9BB0;line-height:1.6;">
    🔒 Seus dados são protegidos conforme a LGPD. Utilizamos seus dados exclusivamente para fins de recrutamento e seleção.
  </p>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>


