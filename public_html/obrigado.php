<?php

/**
 * Obrigado — Confirmação de envio de formulário — Trivya RH
 *
 * Exibida após o envio bem-sucedido de formulário de contato ou candidatura.
 * O tipo de mensagem é detectado pelo parâmetro GET 'tipo' (lead|candidato).
 *
 * @autor    Equipe Trivya RH
 * @versao   1.0.0
 * @data     2025-01-01
 */

declare(strict_types=1);

$tituloPagina    = 'Obrigado pelo contato! | Trivya RH';
$descricaoPagina = 'Sua mensagem foi recebida. Em breve entraremos em contato.';

// Não indexar página de agradecimento
$robotsMeta = 'noindex, nofollow';

require_once __DIR__ . '/bootstrap.php';
require_once INCLUDES_PATH . '/header.php';

// Tipo de formulário para personalizar a mensagem
$tipo = sanitizeString($_GET['tipo'] ?? 'lead');
$tipo = in_array($tipo, ['lead', 'candidato'], true) ? $tipo : 'lead';

// Mensagens dinâmicas por tipo
$conteudo = match ($tipo) {
  'candidato' => [
    'icone'   => '📄',
    'titulo'  => 'Currículo recebido com sucesso!',
    'texto'   => 'Seu currículo foi cadastrado em nosso banco de talentos. Analisaremos seu perfil e entraremos em contato caso surja uma oportunidade alinhada à sua experiência.',
    'cta_wpp' => 'Quero agilizar o processo pelo WhatsApp',
  ],
  default => [ // lead / empresa
    'icone'   => '✅',
    'titulo'  => 'Solicitação recebida!',
    'texto'   => 'Sua solicitação de proposta foi recebida com sucesso. Nossa equipe analisará suas necessidades e entrará em contato em até 24 horas úteis.',
    'cta_wpp' => 'Falar agora pelo WhatsApp',
  ],
};

$wppNumero  = getConfig('whatsapp', '5511999999999');
$wppMsg     = urlencode($tipo === 'candidato'
  ? 'Olá! Acabei de enviar meu currículo pelo site da Trivya RH e gostaria de mais informações.'
  : 'Olá! Acabei de enviar uma solicitação de proposta pelo site da Trivya RH.'
);

?>

<div class="obrigado-wrap page-content-offset">
  <div class="obrigado-card fade-up">

    <div class="obrigado-icone" aria-hidden="true"><?= $conteudo['icone'] ?></div>

    <h1 class="obrigado-titulo"><?= e($conteudo['titulo']) ?></h1>

    <p class="obrigado-texto"><?= e($conteudo['texto']) ?></p>

    <div class="obrigado-acoes">
      <a href="https://wa.me/<?= e($wppNumero) ?>?text=<?= $wppMsg ?>"
         class="btn-primary"
         target="_blank"
         rel="noopener noreferrer">
        💬 <?= e($conteudo['cta_wpp']) ?>
      </a>

      <a href="<?= e(SITE_URL) ?>/" class="btn-outline">
        ← Voltar para a home
      </a>
    </div>

  </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>

