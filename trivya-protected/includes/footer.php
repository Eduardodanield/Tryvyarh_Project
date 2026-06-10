<?php

/**
 * Footer global — Trivya RH
 *
 * Fecha o <main>, renderiza o footer com 4 colunas,
 * inclui o banner de cookies LGPD
 * e fecha o </body></html>.
 *
 * Scripts JS incluídos com defer para não bloquear renderização.
 *
 * @autor    Equipe Trivya RH
 * @versao   1.0.0
 * @data     2025-01-01
 */

declare(strict_types=1);

// Dados dinâmicos do banco
$emailContato = getConfig('email_contato', SITE_EMAIL);
$wppNumero    = getConfig('whatsapp', '5511999999999');
$wppExibicao  = getConfig('telefone_exibicao', formatTelefone($wppNumero));
$instagramUrl = 'https://www.instagram.com/trivya.consultoria/';
$linkedinUrl  = getConfig('linkedin_url', '#');
$anoAtual     = date('Y');
$urlPolitica  = SITE_URL . '/politica-privacidade';
$urlTermos    = SITE_URL . '/termos-uso';

?>
</main><!-- /#conteudo-principal -->

<!-- ======================================================
     FOOTER
     ====================================================== -->
<footer>
  <div class="footer-grid">

    <!-- Coluna 1: Nome + descrição + redes sociais -->
    <div>
      <p class="footer-logo-name" style="font-size:20px; font-weight:700; letter-spacing:2px; color:#FFFFFF; margin-bottom:10px;">
        TRIVYA
      </p>

      <p class="footer-brand-desc">
        Recrutamento &amp; Seleção humanizado para impulsionar pessoas e transformar negócios.
      </p>

      <!-- Redes sociais com cores originais de cada plataforma -->
      <div class="footer-social" style="margin-top:20px; display:flex; gap:12px;">

        <!-- Instagram -->
        <a href="<?= e($instagramUrl) ?>" class="social-btn social-btn--ig"
           target="_blank" rel="noopener noreferrer" aria-label="Instagram Trivya">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill="#FFFFFF" d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
          </svg>
        </a>

        <!-- WhatsApp -->
        <a href="https://wa.me/<?= e($wppNumero) ?>" class="social-btn social-btn--wa"
           target="_blank" rel="noopener noreferrer" aria-label="WhatsApp Trivya">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="#FFFFFF" xmlns="http://www.w3.org/2000/svg">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
          </svg>
        </a>

        <?php if (!empty($linkedinUrl) && $linkedinUrl !== '#'): ?>
        <!-- LinkedIn -->
        <a href="<?= e($linkedinUrl) ?>" class="social-btn social-btn--li"
           target="_blank" rel="noopener noreferrer" aria-label="LinkedIn Trivya">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="#FFFFFF" xmlns="http://www.w3.org/2000/svg">
            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
          </svg>
        </a>
        <?php endif; ?>

      </div>
    </div>

    <!-- Coluna 2: Serviços -->
    <div class="footer-col">
      <h4>Serviços</h4>
      <ul>
        <li><a href="<?= e(SITE_URL) ?>/#servicos">Varejo</a></li>
        <li><a href="<?= e(SITE_URL) ?>/#servicos">Facilities</a></li>
        <li><a href="<?= e(SITE_URL) ?>/#servicos">Construção Civil</a></li>
        <li><a href="<?= e(SITE_URL) ?>/trabalhe-conosco">Banco de Talentos</a></li>
      </ul>
    </div>

    <!-- Coluna 3: Empresa -->
    <div class="footer-col">
      <h4>Empresa</h4>
      <ul>
        <li><a href="<?= e(SITE_URL) ?>/#sobre">Quem Somos</a></li>
        <li><a href="<?= e(SITE_URL) ?>/#diferenciais">Diferenciais</a></li>
        <li><a href="<?= e(SITE_URL) ?>/#depoimentos">Clientes</a></li>
        <li><a href="<?= e(SITE_URL) ?>/trabalhe-conosco">Trabalhe Conosco</a></li>
      </ul>
    </div>

    <!-- Coluna 4: Contato -->
    <div class="footer-col">
      <h4>Contato</h4>
      <ul>
        <li><a href="<?= e(SITE_URL) ?>/#contato">Solicitar proposta</a></li>
        <li><a href="mailto:<?= e($emailContato) ?>"><?= e($emailContato) ?></a></li>
        <li><a href="<?= e(SITE_URL) ?>/politica-privacidade">Política de Privacidade</a></li>
        <li><a href="<?= e(SITE_URL) ?>/termos-uso">Termos de Uso</a></li>
      </ul>
    </div>
  </div>

  <!-- Rodapé inferior: copyright + localização -->
  <div class="footer-bottom">
    <span>&copy; <?= $anoAtual ?> <?= e(SITE_NAME) ?>. Todos os direitos reservados.</span>
    <span>
      <a href="<?= e($urlPolitica) ?>">Privacidade</a>
      &nbsp;·&nbsp;
      <a href="<?= e($urlTermos) ?>">Termos</a>
      &nbsp;·&nbsp;
      São Paulo — SP
    </span>
  </div>
</footer>

<?php
// Componentes auxiliares
require_once dirname(__DIR__) . '/includes/cookie-banner.php';
?>

<!-- Botão flutuante WhatsApp -->
<a href="https://wa.me/<?= e($wppNumero) ?>?text=<?= urlencode('Olá! Vim pelo site da Trivya e gostaria de mais informações.') ?>"
   class="whatsapp-float"
   target="_blank"
   rel="noopener noreferrer"
   aria-label="Fale com a Trivya pelo WhatsApp">
  <svg width="28" height="28" viewBox="0 0 24 24" fill="#fff" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
  </svg>
</a>

<!-- ======================================================
     SCRIPTS — carregados com defer (não bloqueiam renderização)
     ====================================================== -->
<?php $v = '20260530h'; ?>
<script src="<?= e(ASSETS_URL) ?>/js/main.js?v=<?= $v ?>" defer></script>
<script src="<?= e(ASSETS_URL) ?>/js/form.js?v=<?= $v ?>" defer></script>
<script src="<?= e(ASSETS_URL) ?>/js/cookies.js?v=<?= $v ?>" defer></script>

</body>
</html>

