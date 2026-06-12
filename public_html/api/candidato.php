<?php
/**
 * API — Formulário de Candidato (Banco de Talentos) — Trivya RH
 * Recebe POST do formulário multi-step de trabalhe-conosco.php.
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(SITE_URL . '/trabalhe-conosco');
}

// Honeypot
if (!empty($_POST['website'])) {
    redirect(SITE_URL . '/obrigado?tipo=candidato');
}

// CSRF
if (!validateCsrf('candidato', $_POST[CSRF_FIELD_NAME] ?? '')) {
    setFlash('erro', 'Token de segurança inválido. Recarregue a página e tente novamente.');
    redirect(SITE_URL . '/trabalhe-conosco');
}

// ── Sanitização ──────────────────────────────────────────────
$nome           = sanitizeString($_POST['nome']             ?? '', 100);
$telefone       = sanitizeTelefone($_POST['telefone']       ?? '');
$email          = sanitizeEmail($_POST['email']             ?? '');
$cidade_bairro  = sanitizeString($_POST['cidade_bairro']    ?? '', 200);
$area_interesse = sanitizeString($_POST['area_interesse']   ?? '', 50);
$escolaridade   = sanitizeString($_POST['escolaridade']     ?? '', 100);
$lgpd           = isset($_POST['autorizacao_lgpd']) && $_POST['autorizacao_lgpd'] === '1';

$idade_raw      = $_POST['idade'] ?? '';
$idade          = (is_numeric($idade_raw) && (int)$idade_raw >= 16 && (int)$idade_raw <= 80)
                    ? (int)$idade_raw : null;

$nascimento_raw = $_POST['data_nascimento'] ?? '';
$data_nasc      = (!empty($nascimento_raw) && strtotime($nascimento_raw) !== false
                    && strtotime($nascimento_raw) <= time())
                    ? $nascimento_raw : null;

$estuda_raw     = $_POST['esta_estudando'] ?? null;
$esta_estudando = ($estuda_raw === '1') ? 1 : (($estuda_raw === '0') ? 0 : null);

// Áreas válidas
$areasValidas = ['administrativo','recursos_humanos','atendimento','recepcao',
                 'comercial_vendas','marketing','operacional','outro'];
if (!in_array($area_interesse, $areasValidas, true)) {
    $area_interesse = 'outro';
}

// ── Validação server-side ────────────────────────────────────
$erros = [];

if (mb_strlen($nome) < 2)             $erros[] = 'Nome inválido.';
if ($idade === null)                   $erros[] = 'Idade inválida (16 a 80 anos).';
if ($data_nasc === null)               $erros[] = 'Data de nascimento inválida.';
if (mb_strlen($cidade_bairro) < 3)    $erros[] = 'Cidade/bairro obrigatório.';
if (strlen($telefone) < 10)           $erros[] = 'WhatsApp inválido.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
if (mb_strlen($escolaridade) < 3)     $erros[] = 'Escolaridade obrigatória.';
if ($esta_estudando === null)          $erros[] = 'Informe se está estudando.';
if (!$lgpd)                           $erros[] = 'Autorização LGPD obrigatória.';

// ── Upload do currículo ───────────────────────────────────────
$curriculo_path = null;
$curriculo_nome = null;

if (empty($_FILES['curriculo']['tmp_name'])) {
    $erros[] = 'Currículo obrigatório.';
} else {
    $arquivo = $_FILES['curriculo'];

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        $erros[] = 'Erro no upload do currículo.';
    } elseif ($arquivo['size'] > 10 * 1024 * 1024) {
        $erros[] = 'Currículo muito grande (máximo 10 MB).';
    } else {
        // Validar extensão + MIME
        $ext = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $extsOk = ['pdf','doc','docx'];
        $mimesOk = ['application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeReal = $finfo->file($arquivo['tmp_name']);

        if (!in_array($ext, $extsOk, true) || !in_array($mimeReal, $mimesOk, true)) {
            $erros[] = 'Formato inválido. Envie PDF, DOC ou DOCX.';
        }
    }
}

if (!empty($erros)) {
    setFlash('erro', implode(' ', $erros));
    redirect(SITE_URL . '/trabalhe-conosco');
}

// Salvar arquivo — ROOT_PATH definido em config.php como dirname(dirname(__FILE__ in config))
$pastaCV = PUBLIC_PATH . '/uploads/curriculos';
if (!is_dir($pastaCV)) {
    mkdir($pastaCV, 0755, true);
}

$nomeSeguro     = sanitizeNomeArquivo($arquivo['name']);
$caminhoFisico  = $pastaCV . '/' . $nomeSeguro;

if (!move_uploaded_file($arquivo['tmp_name'], $caminhoFisico)) {
    setFlash('erro', 'Falha ao salvar currículo. Tente novamente.');
    redirect(SITE_URL . '/trabalhe-conosco');
}

$curriculo_path = 'uploads/curriculos/' . $nomeSeguro;
$curriculo_nome = $arquivo['name'];

// ── Salvar no banco ───────────────────────────────────────────
$db = Database::getInstance();

try {
    $db->execute(
        "INSERT INTO candidatos
            (nome, idade, data_nascimento, cidade_bairro, telefone, email,
             area_interesse, escolaridade, esta_estudando,
             curriculo_path, curriculo_nome, lgpd_consentimento, ip)
         VALUES
            (:nome, :idade, :nasc, :cidade, :telefone, :email,
             :area, :escolaridade, :estudando,
             :cv_path, :cv_nome, 1, :ip)",
        [
            ':nome'         => $nome,
            ':idade'        => $idade,
            ':nasc'         => $data_nasc,
            ':cidade'       => $cidade_bairro,
            ':telefone'     => $telefone,
            ':email'        => $email,
            ':area'         => $area_interesse,
            ':escolaridade' => $escolaridade,
            ':estudando'    => $esta_estudando,
            ':cv_path'      => $curriculo_path,
            ':cv_nome'      => $curriculo_nome,
            ':ip'           => $_SERVER['REMOTE_ADDR'] ?? '',
        ]
    );
} catch (Exception $e) {
    logError('api/candidato.php', 'Erro ao salvar candidato: ' . $e->getMessage());
    setFlash('erro', 'Erro interno. Tente novamente.');
    redirect(SITE_URL . '/trabalhe-conosco');
}

// ── E-mail de notificação ─────────────────────────────────────
$emailNotif   = getConfig('email_notificacoes', SITE_EMAIL);
$estudaTexto  = $esta_estudando === 1 ? 'Sim' : 'Não';
$areasLabels  = [
    'administrativo'  => 'Administrativo',
    'recursos_humanos'=> 'Recursos Humanos',
    'atendimento'     => 'Atendimento',
    'recepcao'        => 'Recepção',
    'comercial_vendas'=> 'Comercial/Vendas',
    'marketing'       => 'Marketing',
    'operacional'     => 'Operacional',
    'outro'           => 'Outro',
];
$areaLabel = $areasLabels[$area_interesse] ?? $area_interesse;

$dataHora  = date('d/m/Y H:i:s');
$ip        = htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? '');
$hNome     = htmlspecialchars($nome);
$hCidade   = htmlspecialchars($cidade_bairro);
$hTel      = htmlspecialchars($telefone);
$hEmail    = htmlspecialchars($email);
$hArea     = htmlspecialchars($areaLabel);
$hEsc      = htmlspecialchars($escolaridade);
$hCV       = htmlspecialchars($curriculo_nome);
$hNasc     = $data_nasc ? date('d/m/Y', strtotime($data_nasc)) : '&#8212;';

$htmlEmail =
    '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Novo Candidato</title></head>'
    . '<body style="font-family:Arial,sans-serif;background:#F5F5F5;margin:0;padding:20px;">'
    . '<div style="max-width:600px;margin:0 auto;background:#FFFFFF;border-radius:8px;overflow:hidden;border:1px solid #E0E0E0;">'
    . '<div style="background:#00B4D8;padding:24px 32px;">'
    . '<h1 style="color:#FFFFFF;margin:0;font-size:20px;">Novo Candidato &mdash; Trivya RH</h1>'
    . '<p style="color:rgba(255,255,255,0.85);margin:4px 0 0;font-size:14px;">' . $dataHora . '</p>'
    . '</div>'
    . '<div style="padding:32px;">'
    . '<h2 style="color:#00B4D8;font-size:16px;border-bottom:2px solid #CAF0F8;padding-bottom:8px;margin-top:0;">Dados Pessoais</h2>'
    . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
    . '<tr><td style="padding:6px 0;color:#666;width:40%;">Nome</td><td style="padding:6px 0;font-weight:600;">' . $hNome . '</td></tr>'
    . '<tr><td style="padding:6px 0;color:#666;">Idade</td><td style="padding:6px 0;">' . (int)$idade . ' anos</td></tr>'
    . '<tr><td style="padding:6px 0;color:#666;">Nascimento</td><td style="padding:6px 0;">' . $hNasc . '</td></tr>'
    . '<tr><td style="padding:6px 0;color:#666;">Cidade/Bairro</td><td style="padding:6px 0;">' . $hCidade . '</td></tr>'
    . '<tr><td style="padding:6px 0;color:#666;">WhatsApp</td><td style="padding:6px 0;">' . $hTel . '</td></tr>'
    . '<tr><td style="padding:6px 0;color:#666;">E-mail</td><td style="padding:6px 0;">' . $hEmail . '</td></tr>'
    . '</table>'
    . '<h2 style="color:#00B4D8;font-size:16px;border-bottom:2px solid #CAF0F8;padding-bottom:8px;margin-top:24px;">Forma&ccedil;&atilde;o e Interesse</h2>'
    . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
    . '<tr><td style="padding:6px 0;color:#666;width:40%;">&Aacute;rea de Interesse</td><td style="padding:6px 0;font-weight:600;">' . $hArea . '</td></tr>'
    . '<tr><td style="padding:6px 0;color:#666;">Escolaridade</td><td style="padding:6px 0;">' . $hEsc . '</td></tr>'
    . '<tr><td style="padding:6px 0;color:#666;">Est&aacute; estudando</td><td style="padding:6px 0;">' . $estudaTexto . '</td></tr>'
    . '</table>'
    . '<h2 style="color:#00B4D8;font-size:16px;border-bottom:2px solid #CAF0F8;padding-bottom:8px;margin-top:24px;">Curr&iacute;culo</h2>'
    . '<p style="font-size:14px;color:#333;">&#128206; ' . $hCV . '</p>'
    . '<p style="font-size:12px;color:#999;margin-top:24px;border-top:1px solid #EEE;padding-top:12px;">IP: ' . $ip . ' &middot; ' . $dataHora . '</p>'
    . '</div></div></body></html>';

sendEmail(
    para:       $emailNotif,
    nomePara:   'Trivya RH',
    assunto:    "Novo candidato: {$nome} — {$areaLabel}",
    corpoHtml:  $htmlEmail,
    anexos:     [$caminhoFisico]
);

redirect(SITE_URL . '/obrigado?tipo=candidato');

