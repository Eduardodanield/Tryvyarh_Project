<?php

/**
 * Serviço de envio de e-mail — Trivya RH
 *
 * Usa PHPMailer via SMTP com as configurações do .env.
 * Suporta e-mails em HTML com fallback para texto puro.
 * Registra todos os envios (sucesso e falha) no Logger.
 *
 * @autor    Equipe Trivya RH
 * @versao   1.0.0
 * @data     2025-01-01
 */

declare(strict_types=1);

// Carregar PHPMailer (sem Composer)
require_once LIB_PATH . '/PHPMailer/Exception.php';
require_once LIB_PATH . '/PHPMailer/PHPMailer.php';
require_once LIB_PATH . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Envia um e-mail via SMTP.
 *
 * @param string       $para           E-mail do destinatário
 * @param string       $nomePara       Nome do destinatário
 * @param string       $assunto        Assunto do e-mail
 * @param string       $corpoHtml      Corpo em HTML
 * @param string       $corpoTexto     Corpo em texto puro (fallback automático se vazio)
 * @param array        $anexos         Lista de caminhos de arquivo para anexar
 * @param array        $destinatarioCc Lista de ['email' => '...', 'nome' => '...'] em CC
 * @return array{sucesso: bool, mensagem: string}
 */
function sendEmail(
    string $para,
    string $nomePara,
    string $assunto,
    string $corpoHtml,
    string $corpoTexto = '',
    array  $anexos = [],
    array  $destinatarioCc = []
): array {
    $mail = new PHPMailer(true); // true = lançar exceções

    try {
        // --------------------------------------------------
        // Configuração do servidor SMTP
        // --------------------------------------------------
        $mail->isSMTP();
        $mail->Host        = SMTP_HOST;
        $mail->Port        = SMTP_PORT;
        $mail->SMTPAuth    = true;
        $mail->Username    = SMTP_USER;
        $mail->Password    = SMTP_PASS;

        // Segurança da conexão SMTP
        // Porta 465 = SSL, porta 587/25 = STARTTLS (TLS automático)
        $mail->SMTPSecure  = (SMTP_PORT === 465)
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;

        // Charset UTF-8 para suporte a acentos e emojis
        $mail->CharSet     = 'UTF-8';
        $mail->Encoding    = 'base64';

        // Debug desativado em produção
        $mail->SMTPDebug   = APP_DEBUG ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;

        // Timeout de 10 segundos (servidores compartilhados podem ser lentos)
        $mail->Timeout     = 10;

        // --------------------------------------------------
        // Remetente
        // --------------------------------------------------
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);

        // --------------------------------------------------
        // Destinatários
        // --------------------------------------------------
        $mail->addAddress($para, $nomePara);

        foreach ($destinatarioCc as $cc) {
            $mail->addCC($cc['email'], $cc['nome'] ?? '');
        }

        // --------------------------------------------------
        // Conteúdo
        // --------------------------------------------------
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $corpoHtml;

        // Gerar texto puro automaticamente se não fornecido
        $mail->AltBody = $corpoTexto ?: strip_tags(
            str_replace(['<br>', '<br/>', '<br />', '</p>', '</h1>', '</h2>', '</h3>'], "\n", $corpoHtml)
        );

        // --------------------------------------------------
        // Anexos
        // --------------------------------------------------
        foreach ($anexos as $caminhoAnexo) {
            if (file_exists($caminhoAnexo)) {
                $mail->addAttachment($caminhoAnexo);
            }
        }

        // --------------------------------------------------
        // Enviar
        // --------------------------------------------------
        $mail->send();

        Logger::info("E-mail enviado com sucesso", [
            'para'    => $para,
            'assunto' => $assunto,
        ], 'mailer');

        return ['sucesso' => true, 'mensagem' => 'E-mail enviado com sucesso.'];

    } catch (PHPMailerException $e) {
        $erro = $mail->ErrorInfo;

        Logger::error("Falha ao enviar e-mail", [
            'para'    => $para,
            'assunto' => $assunto,
            'erro'    => $erro,
        ], 'mailer');

        if (APP_DEBUG) {
            return ['sucesso' => false, 'mensagem' => "Falha ao enviar e-mail: {$erro}"];
        }

        return ['sucesso' => false, 'mensagem' => 'Não foi possível enviar o e-mail. Tente novamente mais tarde.'];
    }
}

/**
 * Envia notificação interna para o time da Trivya RH.
 *
 * Atalho para sendEmail() usando o e-mail da configuração do site.
 *
 * @param string $assunto  Assunto do e-mail
 * @param string $conteudo Conteúdo HTML
 * @return array{sucesso: bool, mensagem: string}
 */
function notificarEquipe(string $assunto, string $conteudo): array
{
    $emailEquipe = getConfig('email_notificacoes', SMTP_FROM_EMAIL);
    $nomeEquipe  = 'Equipe ' . SITE_NAME;

    return sendEmail($emailEquipe, $nomeEquipe, "[{SITE_NAME}] {$assunto}", $conteudo);
}

/**
 * Gera o HTML base para e-mails transacionais da Trivya RH.
 *
 * Garante consistência visual em todos os e-mails enviados.
 *
 * @param string $titulo   Título exibido no cabeçalho do e-mail
 * @param string $corpo    Conteúdo HTML principal (entre header e footer)
 * @return string HTML completo do e-mail
 */
function templateEmail(string $titulo, string $corpo): string
{
    $siteName = e(SITE_NAME);
    $siteUrl  = e(SITE_URL);
    $ano      = date('Y');

    return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$titulo}</title>
    <style>
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; }
        .header { background-color: #1a365d; padding: 30px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; }
        .content { padding: 30px; color: #333333; line-height: 1.6; }
        .footer { background-color: #f0f0f0; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .btn { display: inline-block; background-color: #1a365d; color: #ffffff !important;
               padding: 12px 24px; border-radius: 4px; text-decoration: none; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{$siteName}</h1>
        </div>
        <div class="content">
            <h2>{$titulo}</h2>
            {$corpo}
        </div>
        <div class="footer">
            <p>&copy; {$ano} {$siteName} | <a href="{$siteUrl}">{$siteUrl}</a></p>
            <p>Este é um e-mail automático, por favor não responda diretamente.</p>
        </div>
    </div>
</body>
</html>
HTML;
}
