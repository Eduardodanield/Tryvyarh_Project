<?php

/**
 * Termos de Uso — Trivya RH
 *
 * @autor    Equipe Trivya RH
 * @versao   1.0.0
 * @data     2025-01-01
 */

declare(strict_types=1);

$tituloPagina    = 'Termos de Uso | Trivya RH';
$descricaoPagina = 'Termos e condições de uso do site e dos serviços da Trivya RH — Consultoria de Recrutamento e Seleção.';

require_once __DIR__ . '/bootstrap.php';
require_once INCLUDES_PATH . '/header.php';

$emailContato    = getConfig('email_contato', SITE_EMAIL);
$dataAtualizacao = '01 de janeiro de 2025';

?>

<div class="legal-hero page-content-offset">
  <div class="section-label" style="color:rgba(255,255,255,.4)">Legal</div>
  <h1 class="section-title">Termos de Uso</h1>
  <p>Última atualização: <?= e($dataAtualizacao) ?></p>
</div>

<article class="legal-content">

  <h2>1. Aceitação dos Termos</h2>
  <p>
    Ao acessar e utilizar o site da <strong>Trivya RH</strong> ("Site"), você concorda com os presentes
    Termos de Uso. Caso não concorde com alguma condição, pedimos que não utilize o Site.
    O uso continuado após a publicação de alterações constitui aceitação das novas condições.
  </p>

  <h2>2. Descrição dos Serviços</h2>
  <p>
    A Trivya RH oferece através deste Site:
  </p>
  <ul>
    <li>Informações institucionais sobre a consultoria e seus serviços;</li>
    <li>Formulário de contato para solicitação de propostas por parte de empresas;</li>
    <li>Banco de talentos para candidatos que desejam se cadastrar em processos seletivos;</li>
    <li>Blog com conteúdo sobre mercado de trabalho e boas práticas em RH.</li>
  </ul>

  <h2>3. Uso Permitido</h2>
  <p>Você pode utilizar este Site exclusivamente para fins lícitos e conforme estes Termos. É proibido:</p>
  <ul>
    <li>Utilizar o Site de forma fraudulenta ou para fins ilegais;</li>
    <li>Enviar informações falsas, incompletas ou enganosas em qualquer formulário;</li>
    <li>Tentar acessar áreas restritas do sistema sem autorização;</li>
    <li>Realizar scraping, crawling ou extração automatizada de dados sem permissão expressa;</li>
    <li>Transmitir vírus, malware ou qualquer código malicioso;</li>
    <li>Reproduzir conteúdo do Site sem autorização prévia e por escrito.</li>
  </ul>

  <h2>4. Propriedade Intelectual</h2>
  <p>
    Todo o conteúdo disponível no Site — incluindo textos, imagens, logotipos, ícones, layout e código —
    é propriedade da Trivya RH ou de seus licenciantes e está protegido pela legislação brasileira de
    propriedade intelectual (Lei nº 9.610/1998 — Lei de Direitos Autorais).
  </p>
  <p>
    É permitido o compartilhamento de links e a reprodução parcial de conteúdo do blog para fins não
    comerciais, desde que devidamente atribuídos à Trivya RH com link para a fonte original.
  </p>

  <h2>5. Cadastro e Responsabilidade do Usuário</h2>
  <p>
    Ao preencher qualquer formulário em nosso Site, você declara que:
  </p>
  <ul>
    <li>As informações fornecidas são verdadeiras, precisas e atualizadas;</li>
    <li>Possui plenos poderes para fornecer os dados (inclusive os de terceiros, quando aplicável);</li>
    <li>É responsável por manter a confidencialidade de qualquer credencial de acesso.</li>
  </ul>
  <p>
    A Trivya RH se reserva o direito de remover cadastros que contenham informações falsas
    ou que violem estes Termos.
  </p>

  <h2>6. Limitação de Responsabilidade</h2>
  <p>
    A Trivya RH não garante que o Site estará disponível ininterruptamente ou livre de erros.
    Em nenhuma hipótese seremos responsáveis por danos indiretos, incidentais ou consequentes
    decorrentes do uso ou impossibilidade de uso do Site.
  </p>
  <p>
    Links para sites de terceiros são fornecidos apenas para conveniência. Não nos responsabilizamos
    pelo conteúdo, disponibilidade ou práticas de privacidade desses sites.
  </p>

  <h2>7. Isenção de Garantias</h2>
  <p>
    O Site é fornecido "como está", sem garantias expressas ou implícitas de qualquer natureza.
    Não garantimos que o conteúdo do Site seja sempre preciso, completo ou atualizado.
  </p>

  <h2>8. Privacidade</h2>
  <p>
    O tratamento de dados pessoais é regido pela nossa
    <a href="<?= e(SITE_URL) ?>/politica-privacidade">Política de Privacidade</a>,
    que é parte integrante destes Termos de Uso.
  </p>

  <h2>9. Modificações</h2>
  <p>
    Reservamo-nos o direito de modificar estes Termos a qualquer momento. As alterações entram em vigor
    imediatamente após a publicação no Site. Recomendamos a revisão periódica desta página.
  </p>

  <h2>10. Lei Aplicável e Foro</h2>
  <p>
    Estes Termos são regidos pelas leis da República Federativa do Brasil.
    Fica eleito o foro da Comarca de São Paulo/SP para dirimir quaisquer controvérsias
    decorrentes deste instrumento.
  </p>

  <h2>11. Contato</h2>
  <p>
    Para esclarecimentos sobre estes Termos de Uso, entre em contato:
    <a href="mailto:<?= e($emailContato) ?>"><?= e($emailContato) ?></a>
  </p>

  <p class="legal-atualizado">
    Última atualização: <?= e($dataAtualizacao) ?>
  </p>

</article>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>


