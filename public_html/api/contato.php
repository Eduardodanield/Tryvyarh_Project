<?php
/**
 * API — Formulário de Contato (Empresa) — Trivya RH
 * Recebe POST do formulário multi-step da homepage.
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(SITE_URL . '/#contato');
}

// Honeypot anti-bot
if (!empty($_POST['website'])) {
    redirect(SITE_URL . '/obrigado?tipo=lead');
}

// CSRF
if (!validateCsrf('contato', $_POST[CSRF_FIELD_NAME] ?? '')) {
    setFlash('erro', 'Token de segurança inválido. Recarregue a página e tente novamente.');
    redirect(SITE_URL . '/#contato');
}

// ── Sanitização ──────────────────────────────────────────────
$nome               = sanitizeString($_POST['nome']                ?? '', 100);
$empresa            = sanitizeString($_POST['empresa']             ?? '', 150);
$telefone           = sanitizeTelefone($_POST['telefone']          ?? '');
$email              = sanitizeEmail($_POST['email']                ?? '');
$cidade_estado      = sanitizeString($_POST['cidade_estado']       ?? '', 150);
$cargo_area         = sanitizeString($_POST['cargo_area_contratar'] ?? '', 200);
$segmento           = sanitizeString($_POST['segmento']            ?? '', 50);
$qtd_vagas          = sanitizeString($_POST['qtd_vagas']           ?? '', 10);
$dificuldade        = sanitizeTexto($_POST['dificuldade']          ?? '', 1000);
$urgencia_raw       = $_POST['urgencia'] ?? null;
$aceita_wpp_raw     = $_POST['aceita_whatsapp'] ?? '1';
$lgpd               = isset($_POST['consentimento_lgpd']) && $_POST['consentimento_lgpd'] === '1';

// ── Validação server-side ────────────────────────────────────
$erros = [];

if (mb_strlen($nome) < 2)            $erros[] = 'Nome do responsável inválido.';
if (mb_strlen($empresa) < 2)         $erros[] = 'Nome da empresa inválido.';
if (strlen($telefone) < 10)          $erros[] = 'Telefone inválido (mínimo 10 dígitos com DDD).';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
if (mb_strlen($cidade_estado) < 2)   $erros[] = 'Cidade/Estado obrigatório.';
if (mb_strlen($cargo_area) < 2)      $erros[] = 'Cargo/área a contratar obrigatório.';
if (!in_array($qtd_vagas, ['1','2-5','5-10'], true)) $erros[] = 'Quantidade de vagas inválida.';
if (!$lgpd)                          $erros[] = 'Autorização LGPD obrigatória.';

// Segmento — lista fechada
$segmentosValidos = ['varejo','facilities','construcao_civil','outro',''];
if (!in_array($segmento, $segmentosValidos, true)) $segmento = '';

// Urgência — pode ser null (não respondeu)
$urgencia = null;
if ($urgencia_raw === '1') $urgencia = 1;
if ($urgencia_raw === '0') $urgencia = 0;

// Aceita WhatsApp
$aceita_wpp = ($aceita_wpp_raw === '1') ? 1 : 0;

if (!empty($erros)) {
    setFlash('erro', implode(' ', $erros));
    redirect(SITE_URL . '/#contato');
}

// ── Salvar no banco ───────────────────────────────────────────
$db = Database::getInstance();

try {
    $db->execute(
        "INSERT INTO leads
            (nome, empresa, telefone, email, cargo, cidade_estado,
             cargo_area_contratar, qtd_vagas, segmento, mensagem,
             urgencia, aceita_contato_whatsapp, ip, origem)
         VALUES
            (:nome, :empresa, :telefone, :email, :cargo, :cidade_estado,
             :cargo_area, :qtd_vagas, :segmento, :mensagem,
             :urgencia, :aceita_wpp, :ip, 'site')",
        [
            ':nome'          => $nome,
            ':empresa'       => $empresa,
            ':telefone'      => $telefone,
            ':email'         => $email,
            ':cargo'         => '',
            ':cidade_estado' => $cidade_estado,
            ':cargo_area'    => $cargo_area,
            ':qtd_vagas'     => $qtd_vagas,
            ':segmento'      => $segmento,
            ':mensagem'      => $dificuldade,
            ':urgencia'      => $urgencia,
            ':aceita_wpp'    => $aceita_wpp,
            ':ip'            => $_SERVER['REMOTE_ADDR'] ?? '',
        ]
    );
} catch (Exception $e) {
    logError('api/contato.php', 'Erro ao salvar lead: ' . $e->getMessage());
    setFlash('erro', 'Erro interno. Tente novamente em instantes.');
    redirect(SITE_URL . '/#contato');
}

// ── E-mail de notificação ─────────────────────────────────────
$emailNotif    = getConfig('email_notificacoes', SITE_EMAIL);
$urgenciaTexto = $urgencia === 1 ? 'Sim ⚡' : ($urgencia === 0 ? 'Não' : 'Não informado');
$wppTexto      = $aceita_wpp ? 'Sim ✅' : 'Não';
$dataHora      = date('d/m/Y H:i:s');
$ip            = htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? '');

$hEmpresa   = htmlspecialchars($empresa);
$hNome      = htmlspecialchars($nome);
$hTelefone  = htmlspecialchars($telefone);
$hEmail     = htmlspecialchars($email);
$hCidade    = htmlspecialchars($cidade_estado);
$hCargo     = htmlspecialchars($cargo_area);
$hSegmento  = htmlspecialchars($segmento ?: '—');
$hQtd       = htmlspecialchars($qtd_vagas);

$blocoIfDificuldade = $dificuldade
    ? '<h2 style="color:#C85A2A;font-size:16px;border-bottom:2px solid #F4E8E0;padding-bottom:8px;margin-top:24px;">Maior Dificuldade</h2>'
      . '<p style="font-size:14px;color:#333;background:#F9F5F2;padding:12px;border-radius:6px;border-left:3px solid #C85A2A;">'
      . nl2br(htmlspecialchars($dificuldade)) . '</p>'
    : '';

$htmlEmail =
    '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Novo Lead — Trivya RH</title></head>'
    . '<body style="font-family:Arial,sans-serif;background:#F5F5F5;margin:0;padding:20px;">'
    . '<div style="max-width:600px;margin:0 auto;background:#FFFFFF;border-radius:8px;overflow:hidden;border:1px solid #E0E0E0;">'
    . '<div style="background:#C85A2A;padding:24px 32px;">'
    . '<h1 style="color:#FFFFFF;margin:0;font-size:20px;">&#127970; Novo Lead &mdash; Trivya RH</h1>'
    . '<p style="color:rgba(255,255,255,0.8);margin:4px 0 0;font-size:14px;">' . $dataHora . '</p>'
    . '</div>'
    . '<div style="padding:32px;">'
    . '<h2 style="color:#C85A2A;font-size:16px;border-bottom:2px solid #F4E8E0;padding-bottom:8px;margin-top:0;">Dados da Empresa</h2>'
    . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
    . '<tr><td style="padding:6px 0;color:#666;width:40%;">Empresa</td><td style="padding:6px 0;font-weight:600;">' . $hEmpresa . '</td></tr>'
    . '<tr><td style="padding:6px 0;color:#666;">Respons&aacute;vel</td><td style="padding:6px 0;font-weight:600;">' . $hNome . '</td></tr>'
    . '<tr><td style="padding:6px 0;color:#666;">WhatsApp</td><td style="padding:6px 0;">' . $hTelefone . '</td></tr>'
    . '<tr><td style="padding:6px 0;color:#666;">E-mail</td><td style="padding:6px 0;">' . $hEmail . '</td></tr>'
    . '<tr><td style="padding:6px 0;color:#666;">Cidade/Estado</td><td style="padding:6px 0;">' . $hCidade . '</td></tr>'
    . '</table>'
    . '<h2 style="color:#C85A2A;font-size:16px;border-bottom:2px solid #F4E8E0;padding-bottom:8px;margin-top:24px;">Sobre a Contrata&ccedil;&atilde;o</h2>'
    . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
    . '<tr><td style="padding:6px 0;color:#666;width:40%;">Cargo/&Aacute;rea</td><td style="padding:6px 0;font-weight:600;">' . $hCargo . '</td></tr>'
    . '<tr><td style="padding:6px 0;color:#666;">Segmento</td><td style="padding:6px 0;">' . $hSegmento . '</td></tr>'
    . '<tr><td style="padding:6px 0;color:#666;">Qtd. Vagas</td><td style="padding:6px 0;"><strong>' . $hQtd . '</strong></td></tr>'
    . '<tr><td style="padding:6px 0;color:#666;">Urg&ecirc;ncia</td><td style="padding:6px 0;">' . $urgenciaTexto . '</td></tr>'
    . '<tr><td style="padding:6px 0;color:#666;">Aceita WhatsApp</td><td style="padding:6px 0;">' . $wppTexto . '</td></tr>'
    . '</table>'
    . $blocoIfDificuldade
    . '<p style="font-size:12px;color:#999;margin-top:24px;border-top:1px solid #EEE;padding-top:12px;">IP: ' . $ip . ' &middot; ' . $dataHora . '</p>'
    . '</div></div></body></html>';

sendEmail(
    para:       $emailNotif,
    nomePara:   'Trivya RH',
    assunto:    "Novo lead: {$empresa} — {$qtd_vagas} vaga(s)" . ($urgencia === 1 ? ' URGENTE' : ''),
    corpoHtml:  $htmlEmail
);

redirect(SITE_URL . '/obrigado?tipo=lead');

