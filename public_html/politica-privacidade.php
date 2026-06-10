<?php

/**
 * Política de Privacidade — Trivya RH
 * Conforme Lei nº 13.709/2018 (LGPD)
 *
 * @autor    Equipe Trivya RH
 * @versao   1.0.0
 * @data     2025-01-01
 */

declare(strict_types=1);

$tituloPagina    = 'Política de Privacidade | ' . 'Trivya RH';
$descricaoPagina = 'Saiba como a Trivya RH coleta, usa e protege seus dados pessoais, em conformidade com a Lei Geral de Proteção de Dados (LGPD).';

require_once __DIR__ . '/bootstrap.php';
require_once INCLUDES_PATH . '/header.php';

$versaoPolitica   = getConfig('versao_politica_privacidade', '1.0');
$emailContato     = getConfig('email_contato', SITE_EMAIL);
$cnpjEmpresa      = getConfig('footer_cnpj', 'CNPJ: XX.XXX.XXX/0001-XX');
$dataAtualizacao  = '01 de janeiro de 2025';

?>

<!-- Hero da página legal -->
<div class="legal-hero page-content-offset">
  <div class="section-label" style="color:rgba(255,255,255,.4)">Legal</div>
  <h1 class="section-title">Política de Privacidade</h1>
  <p>Versão <?= e($versaoPolitica) ?> · Última atualização: <?= e($dataAtualizacao) ?></p>
</div>

<!-- Conteúdo da política -->
<article class="legal-content">

  <h2>1. Quem somos</h2>
  <p>
    <strong>Trivya RH Consultoria Ltda.</strong> ("Trivya RH", "nós" ou "nosso") é uma consultoria de
    recrutamento e seleção com sede em São Paulo/SP, <?= e($cnpjEmpresa) ?>.
    Este documento explica como coletamos, usamos, compartilhamos e protegemos seus dados pessoais quando
    você utiliza nosso site ou nossos serviços.
  </p>
  <p>
    Em caso de dúvidas, entre em contato com nosso responsável pelo tratamento de dados pelo e-mail:
    <a href="mailto:<?= e($emailContato) ?>"><?= e($emailContato) ?></a>.
  </p>

  <h2>2. Quais dados coletamos</h2>

  <h3>2.1 Dados fornecidos diretamente por você</h3>
  <ul>
    <li><strong>Formulário de contato (empresas):</strong> nome, empresa, cargo, e-mail, telefone, mensagem, CNPJ.</li>
    <li><strong>Banco de talentos (candidatos):</strong> nome, e-mail, telefone, cidade, estado, área de interesse,
        nível profissional, pretensão salarial, LinkedIn e currículo (PDF/DOC/DOCX).</li>
    <li><strong>Comunicação direta:</strong> dados fornecidos por e-mail, WhatsApp ou telefone.</li>
  </ul>

  <h3>2.2 Dados coletados automaticamente</h3>
  <ul>
    <li><strong>Dados de acesso:</strong> endereço IP, tipo de navegador, sistema operacional, páginas visitadas e horário de acesso.</li>
    <li><strong>Cookies essenciais:</strong> necessários para manter sua sessão ativa e proteger formulários (CSRF).</li>
    <li><strong>Cookies analíticos (com consentimento):</strong> Google Analytics 4 — informações agregadas sobre o comportamento de navegação. O IP é anonimizado.</li>
  </ul>

  <h2>3. Para que usamos seus dados</h2>
  <ul>
    <li>Responder às suas solicitações de proposta ou dúvidas;</li>
    <li>Encaminhar seu currículo para processos seletivos compatíveis com seu perfil;</li>
    <li>Entrar em contato para apresentar oportunidades de trabalho relevantes;</li>
    <li>Cumprir obrigações legais e regulatórias;</li>
    <li>Melhorar nosso site e a experiência do usuário (dados analíticos anonimizados);</li>
    <li>Proteger nossos sistemas contra fraudes e acessos não autorizados.</li>
  </ul>

  <h2>4. Base legal para o tratamento (LGPD, Art. 7º)</h2>
  <ul>
    <li><strong>Consentimento (inciso I):</strong> dados de candidatos e leads que marcaram a caixa de consentimento no formulário.</li>
    <li><strong>Execução de contrato (inciso V):</strong> dados necessários para prestar nossos serviços de R&amp;S.</li>
    <li><strong>Legítimo interesse (inciso IX):</strong> segurança da plataforma, prevenção de fraudes e melhoria dos serviços.</li>
    <li><strong>Cumprimento de obrigação legal (inciso II):</strong> quando a lei nos exige manter registros.</li>
  </ul>

  <h2>5. Por quanto tempo armazenamos seus dados</h2>
  <ul>
    <li><strong>Leads (empresas):</strong> até 2 anos após o último contato, salvo exigência legal.</li>
    <li><strong>Candidatos:</strong> até 2 anos após o envio do currículo ou enquanto houver consentimento ativo.</li>
    <li><strong>Logs de acesso:</strong> 90 dias (conforme Marco Civil da Internet — Lei 12.965/2014).</li>
    <li><strong>Consentimentos LGPD:</strong> mantidos por 5 anos para fins de comprovação.</li>
  </ul>

  <h2>6. Compartilhamento de dados</h2>
  <p>
    <strong>A Trivya RH não vende, aluga ou compartilha seus dados pessoais com terceiros para fins comerciais.</strong>
  </p>
  <p>Podemos compartilhar dados apenas nas seguintes situações:</p>
  <ul>
    <li>Com a empresa contratante, caso você (candidato) avance no processo seletivo — sempre com sua ciência;</li>
    <li>Com provedores de serviço que nos auxiliam (hospedagem, e-mail), vinculados por contrato de confidencialidade;</li>
    <li>Por exigência judicial ou de autoridade competente.</li>
  </ul>

  <h2>7. Seus direitos como titular</h2>
  <p>Conforme o Art. 18 da LGPD, você tem direito a:</p>
  <ul>
    <li><strong>Confirmação e acesso:</strong> saber se tratamos seus dados e obter uma cópia;</li>
    <li><strong>Correção:</strong> solicitar a atualização de dados incompletos ou incorretos;</li>
    <li><strong>Anonimização ou bloqueio:</strong> dos dados desnecessários ou excessivos;</li>
    <li><strong>Eliminação:</strong> dos dados tratados com base no consentimento (quando não houver outra base legal);</li>
    <li><strong>Portabilidade:</strong> receber seus dados em formato estruturado;</li>
    <li><strong>Revogação do consentimento:</strong> a qualquer momento, sem prejudicar tratamentos anteriores;</li>
    <li><strong>Informação:</strong> sobre com quem compartilhamos seus dados.</li>
  </ul>
  <p>
    Para exercer qualquer direito, envie um e-mail para
    <a href="mailto:<?= e($emailContato) ?>"><?= e($emailContato) ?></a>
    com o assunto "LGPD — [seu direito]". Responderemos em até 15 dias.
  </p>

  <h2>8. Cookies utilizados</h2>
  <ul>
    <li><strong>TRIVYA_SID</strong> — sessão do usuário (essencial, HTTP-only, expiração: 8h);</li>
    <li><strong>cookie_consent_trivya</strong> — armazena sua preferência de cookies (essencial, 1 ano);</li>
    <li><strong>_ga, _ga_*</strong> — Google Analytics 4 (analítico, com consentimento, 2 anos).</li>
  </ul>
  <p>
    Você pode gerenciar suas preferências de cookies clicando em "Personalizar" no banner de cookies
    ou acessando as configurações do seu navegador.
  </p>

  <h2>9. Segurança dos dados</h2>
  <p>
    Adotamos medidas técnicas e administrativas para proteger seus dados: conexão HTTPS, senhas criptografadas
    com bcrypt, proteção CSRF em formulários, acesso restrito ao painel administrativo e logs de auditoria.
  </p>

  <h2>10. Transferência internacional de dados</h2>
  <p>
    O Google Analytics pode processar dados em servidores fora do Brasil. O IP é anonimizado antes do envio.
    O Google adere ao GDPR (legislação europeia equivalente à LGPD) e às cláusulas contratuais padrão da UE.
  </p>

  <h2>11. Alterações nesta política</h2>
  <p>
    Podemos atualizar esta Política de Privacidade periodicamente. A versão atual e a data de atualização
    estão sempre indicadas no topo desta página. Em caso de alterações relevantes, comunicaremos por e-mail
    os titulares cujos dados estão em nossa base.
  </p>

  <h2>12. Foro</h2>
  <p>
    Esta Política é regida pela legislação brasileira, em especial a Lei nº 13.709/2018 (LGPD).
    Fica eleito o foro da Comarca de São Paulo/SP para dirimir quaisquer controvérsias.
  </p>

  <p class="legal-atualizado">
    Última atualização: <?= e($dataAtualizacao) ?> · Versão <?= e($versaoPolitica) ?>
  </p>

</article>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>


