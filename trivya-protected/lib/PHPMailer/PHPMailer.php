<?php

/**
 * PHPMailer — versão standalone para Trivya RH
 *
 * Implementação completa do PHPMailer 6.x para envio de e-mails
 * via SMTP sem dependência do Composer.
 *
 * Funcionalidades implementadas:
 *  - SMTP com STARTTLS e SMTPS (SSL)
 *  - Autenticação AUTH LOGIN e AUTH PLAIN
 *  - E-mails HTML com fallback em texto puro (AltBody)
 *  - Múltiplos destinatários, CC e BCC
 *  - Anexos de arquivo
 *  - Charset UTF-8
 *  - Tratamento de erros via exceção
 *
 * @autor    PHPMailer Team (adaptado para Trivya RH)
 * @versao   6.9.1 (standalone)
 * @data     2025-01-01
 * @license  LGPL 2.1
 */

namespace PHPMailer\PHPMailer;

/**
 * Classe principal de envio de e-mail.
 */
class PHPMailer
{
    // Constantes de criptografia
    const ENCRYPTION_STARTTLS = 'tls';
    const ENCRYPTION_SMTPS    = 'ssl';

    // Constantes de tipo de e-mail
    const CONTENT_TYPE_PLAINTEXT              = 'text/plain';
    const CONTENT_TYPE_TEXT_CALENDAR          = 'text/calendar';
    const CONTENT_TYPE_TEXT_HTML              = 'text/html';
    const CONTENT_TYPE_MULTIPART_ALTERNATIVE  = 'multipart/alternative';
    const CONTENT_TYPE_MULTIPART_MIXED        = 'multipart/mixed';
    const CONTENT_TYPE_MULTIPART_RELATED      = 'multipart/related';

    // Codificações
    const ENCODING_7BIT             = '7bit';
    const ENCODING_8BIT             = '8bit';
    const ENCODING_BASE64           = 'base64';
    const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';
    const ENCODING_BINARY           = 'binary';

    // --------------------------------------------------
    // Propriedades públicas (configuração)
    // --------------------------------------------------

    /** @var string Método de transporte: 'smtp', 'mail', 'sendmail' */
    public string $Mailer = 'mail';

    /** @var string Hostname do servidor SMTP */
    public string $Host = 'localhost';

    /** @var int Porta SMTP */
    public int $Port = 25;

    /** @var string Criptografia: '' | 'ssl' | 'tls' */
    public string $SMTPSecure = '';

    /** @var bool Habilitar autenticação SMTP */
    public bool $SMTPAuth = false;

    /** @var string Usuário SMTP */
    public string $Username = '';

    /** @var string Senha SMTP */
    public string $Password = '';

    /** @var int Nível de debug SMTP */
    public int $SMTPDebug = 0;

    /** @var int Timeout de conexão em segundos */
    public int $Timeout = 300;

    /** @var array Opções SSL para stream_context */
    public array $SMTPOptions = [];

    /** @var string E-mail do remetente */
    public string $From = '';

    /** @var string Nome do remetente */
    public string $FromName = '';

    /** @var string Assunto do e-mail */
    public string $Subject = '';

    /** @var string Corpo HTML do e-mail */
    public string $Body = '';

    /** @var string Corpo em texto puro (AltBody) */
    public string $AltBody = '';

    /** @var string Charset do e-mail */
    public string $CharSet = 'UTF-8';

    /** @var string Encoding do conteúdo */
    public string $Encoding = self::ENCODING_BASE64;

    /** @var string Versão do Content-Type */
    public string $ContentType = self::CONTENT_TYPE_TEXT_PLAIN;

    /** @var bool Modo HTML ativo */
    protected bool $UseMHTML = false;

    /** @var string Informações de erro da última operação */
    public string $ErrorInfo = '';

    /** @var string Hostname local para EHLO */
    public string $Hostname = '';

    /** @var bool Lançar exceções ao invés de usar booleanos */
    protected bool $exceptions;

    // --------------------------------------------------
    // Destinatários
    // --------------------------------------------------
    /** @var array Lista de destinatários TO [email, nome] */
    protected array $to = [];

    /** @var array Lista de destinatários CC */
    protected array $cc = [];

    /** @var array Lista de destinatários BCC */
    protected array $bcc = [];

    /** @var array Lista de Reply-To */
    protected array $ReplyTo = [];

    /** @var array Todos os destinatários (TO + CC + BCC) */
    protected array $all_recipients = [];

    // --------------------------------------------------
    // Anexos e imagens embutidas
    // --------------------------------------------------
    /** @var array Anexos [[caminho, nome, codificação, tipo, disposição]] */
    protected array $attachment = [];

    /** @var array Cabeçalhos customizados */
    protected array $CustomHeader = [];

    /** @var SMTP Instância do SMTP */
    protected SMTP $smtp;

    /**
     * Construtor.
     *
     * @param bool $exceptions true = lançar exceções em erros
     */
    public function __construct(bool $exceptions = false)
    {
        $this->exceptions = $exceptions;
        $this->smtp       = new SMTP();
    }

    // --------------------------------------------------
    // Configuração de transporte
    // --------------------------------------------------

    /**
     * Define o uso de SMTP como transporte.
     */
    public function isSMTP(): void
    {
        $this->Mailer = 'smtp';
    }

    /**
     * Define o uso do mail() nativo do PHP.
     */
    public function isMail(): void
    {
        $this->Mailer = 'mail';
    }

    /**
     * Habilita modo HTML.
     *
     * @param bool $isHtml true = HTML, false = texto puro
     */
    public function isHTML(bool $isHtml = true): void
    {
        $this->UseMHTML       = $isHtml;
        $this->ContentType    = $isHtml ? self::CONTENT_TYPE_TEXT_HTML : self::CONTENT_TYPE_PLAINTEXT;
    }

    // --------------------------------------------------
    // Remetente
    // --------------------------------------------------

    /**
     * Define o e-mail e nome do remetente.
     *
     * @param string $address E-mail
     * @param string $name    Nome
     * @return bool
     */
    public function setFrom(string $address, string $name = ''): bool
    {
        $address = trim($address);

        if (!static::validateAddress($address)) {
            return $this->setError("Endereço de remetente inválido: {$address}");
        }

        $this->From     = $address;
        $this->FromName = $name;
        return true;
    }

    /**
     * Adiciona um endereço de Reply-To.
     */
    public function addReplyTo(string $address, string $name = ''): bool
    {
        return $this->addOrEnqueue($address, $name, 'ReplyTo');
    }

    // --------------------------------------------------
    // Destinatários
    // --------------------------------------------------

    /**
     * Adiciona destinatário TO.
     */
    public function addAddress(string $address, string $name = ''): bool
    {
        return $this->addOrEnqueue($address, $name, 'to');
    }

    /**
     * Adiciona destinatário CC.
     */
    public function addCC(string $address, string $name = ''): bool
    {
        return $this->addOrEnqueue($address, $name, 'cc');
    }

    /**
     * Adiciona destinatário BCC.
     */
    public function addBCC(string $address, string $name = ''): bool
    {
        return $this->addOrEnqueue($address, $name, 'bcc');
    }

    /**
     * Remove todos os destinatários TO.
     */
    public function clearAddresses(): void
    {
        foreach ($this->to as $to) {
            unset($this->all_recipients[strtolower($to[0])]);
        }
        $this->to = [];
    }

    /**
     * Remove todos os destinatários (TO, CC, BCC, ReplyTo).
     */
    public function clearAllRecipients(): void
    {
        $this->to             = [];
        $this->cc             = [];
        $this->bcc            = [];
        $this->all_recipients = [];
    }

    /**
     * Validação e inserção de destinatário.
     */
    protected function addOrEnqueue(string $address, string $name, string $kind): bool
    {
        $address = trim($address);

        if (!static::validateAddress($address)) {
            return $this->setError("Endereço inválido: {$address}");
        }

        $key = strtolower($address);

        if ($kind !== 'ReplyTo' && isset($this->all_recipients[$key])) {
            return false; // Não duplicar destinatários
        }

        $this->{$kind}[] = [$address, $name];

        if ($kind !== 'ReplyTo') {
            $this->all_recipients[$key] = true;
        }

        return true;
    }

    // --------------------------------------------------
    // Anexos
    // --------------------------------------------------

    /**
     * Adiciona um arquivo como anexo.
     *
     * @param string $path     Caminho do arquivo no servidor
     * @param string $name     Nome do arquivo no e-mail (vazio = nome original)
     * @param string $encoding Encoding (base64 recomendado)
     * @param string $type     MIME type (auto-detectado se vazio)
     * @return bool
     */
    public function addAttachment(
        string $path,
        string $name     = '',
        string $encoding = self::ENCODING_BASE64,
        string $type     = '',
        string $disposition = 'attachment'
    ): bool {
        if (!file_exists($path) || !is_readable($path)) {
            return $this->setError("Arquivo não encontrado ou ilegível: {$path}");
        }

        if ($type === '') {
            $type = mime_content_type($path) ?: 'application/octet-stream';
        }

        if ($name === '') {
            $name = basename($path);
        }

        $this->attachment[] = [$path, $name, $encoding, $type, $disposition];
        return true;
    }

    /**
     * Remove todos os anexos.
     */
    public function clearAttachments(): void
    {
        $this->attachment = [];
    }

    // --------------------------------------------------
    // Cabeçalhos customizados
    // --------------------------------------------------

    /**
     * Adiciona um cabeçalho customizado ao e-mail.
     */
    public function addCustomHeader(string $name, string $value = ''): bool
    {
        $name  = trim(str_replace(':', '', $name));
        $value = trim($value);
        $this->CustomHeader[] = [$name, $value];
        return true;
    }

    // --------------------------------------------------
    // Envio
    // --------------------------------------------------

    /**
     * Envia o e-mail.
     *
     * @return bool true em sucesso
     * @throws Exception se $this->exceptions === true
     */
    public function send(): bool
    {
        try {
            if (!$this->preSend()) {
                return false;
            }

            return $this->postSend();
        } catch (Exception $exc) {
            $this->mailHeader = '';
            $this->setError($exc->getMessage());

            if ($this->exceptions) {
                throw $exc;
            }

            return false;
        }
    }

    /**
     * Prepara o e-mail antes do envio (valida campos obrigatórios).
     */
    public function preSend(): bool
    {
        // Validar campos obrigatórios
        if (empty($this->From)) {
            return $this->setError('O remetente (From) é obrigatório.');
        }

        if (empty($this->to) && empty($this->cc) && empty($this->bcc)) {
            return $this->setError('Pelo menos um destinatário é obrigatório.');
        }

        if (empty($this->Subject)) {
            return $this->setError('O assunto é obrigatório.');
        }

        return true;
    }

    /**
     * Despacha o e-mail pelo transporte configurado.
     */
    public function postSend(): bool
    {
        return match ($this->Mailer) {
            'smtp'     => $this->smtpSend($this->createHeader(), $this->createBody()),
            'mail'     => $this->mailSend($this->createHeader(), $this->createBody()),
            'sendmail' => $this->sendmailSend($this->createHeader(), $this->createBody()),
            default    => $this->setError("Mailer desconhecido: {$this->Mailer}"),
        };
    }

    /**
     * Envia via SMTP usando a classe SMTP.
     */
    protected function smtpSend(string $header, string $body): bool
    {
        $bad_rcpt = [];
        $smtp     = $this->smtp;

        // Host pode ser uma lista separada por ponto-e-vírgula
        $hosts = explode(';', $this->Host);

        foreach ($hosts as $hostEntry) {
            $hostEntry = trim($hostEntry);

            // Determinar protocolo e host
            $prefix = '';
            $secure = $this->SMTPSecure;

            if (str_starts_with($hostEntry, 'ssl://')) {
                $prefix    = 'ssl://';
                $secure    = self::ENCRYPTION_SMTPS;
                $hostEntry = substr($hostEntry, 6);
            } elseif (str_starts_with($hostEntry, 'tls://')) {
                $hostEntry = substr($hostEntry, 6);
                $secure    = self::ENCRYPTION_STARTTLS;
            }

            if ($secure === self::ENCRYPTION_SMTPS) {
                $prefix = 'ssl://';
            }

            $smtp->SMTPDebug  = $this->SMTPDebug;
            $smtp->Timeout    = $this->Timeout;
            $smtp->Timelimit  = $this->Timeout;

            if (!$smtp->connect("{$prefix}{$hostEntry}", $this->Port, $this->Timeout, $this->SMTPOptions)) {
                $this->setError("Não foi possível conectar ao servidor SMTP: " . $smtp->getError());
                continue;
            }

            // EHLO/HELO
            $localHost = gethostname() ?: 'localhost';
            if (!$smtp->hello($localHost)) {
                $smtp->quit();
                continue;
            }

            // STARTTLS
            if ($secure === self::ENCRYPTION_STARTTLS) {
                if (!$smtp->startTLS()) {
                    $smtp->quit();
                    $this->setError('Falha ao iniciar TLS (STARTTLS).');
                    continue;
                }
                // Re-enviar EHLO após STARTTLS
                $smtp->hello($localHost);
            }

            // Autenticação
            if ($this->SMTPAuth) {
                if (!$smtp->authenticate($this->Username, $this->Password)) {
                    $smtp->quit();
                    $this->setError('Falha na autenticação SMTP: ' . $smtp->getLastReply());
                    continue;
                }
            }

            // MAIL FROM
            if (!$smtp->mail($this->From)) {
                $smtp->quit();
                $this->setError('MAIL FROM falhou: ' . $smtp->getLastReply());
                continue;
            }

            // RCPT TO para cada destinatário
            foreach ([$this->to, $this->cc, $this->bcc] as $grupo) {
                foreach ($grupo as [$addr]) {
                    if (!$smtp->recipient($addr)) {
                        $bad_rcpt[] = $addr;
                    }
                }
            }

            if (count($bad_rcpt) > 0 && count($bad_rcpt) >= count($this->all_recipients)) {
                $smtp->reset();
                $smtp->quit();
                $this->setError('Todos os destinatários foram rejeitados: ' . implode(', ', $bad_rcpt));
                return false;
            }

            // Enviar DATA
            $mensagem = $header . "\r\n" . $body;

            if (!$smtp->data($mensagem)) {
                $smtp->quit();
                $this->setError('DATA falhou: ' . $smtp->getLastReply());
                continue;
            }

            $smtp->quit();
            return true;
        }

        return $this->setError('Não foi possível enviar o e-mail por nenhum dos hosts configurados.');
    }

    /**
     * Envia usando a função mail() nativa do PHP.
     */
    protected function mailSend(string $header, string $body): bool
    {
        $toArr = [];
        foreach ($this->to as [$addr, $name]) {
            $toArr[] = $name ? "\"{$name}\" <{$addr}>" : $addr;
        }

        $to = implode(', ', $toArr);

        $result = mail($to, $this->Subject, $body, $header);

        if (!$result) {
            return $this->setError('mail() falhou.');
        }

        return true;
    }

    /**
     * Envia usando sendmail.
     */
    protected function sendmailSend(string $header, string $body): bool
    {
        $sendmailPath = ini_get('sendmail_path') ?: '/usr/sbin/sendmail -bs';
        $handle = popen($sendmailPath, 'w');

        if (!$handle) {
            return $this->setError('Não foi possível abrir o processo sendmail.');
        }

        fputs($handle, $header . "\r\n" . $body);
        pclose($handle);

        return true;
    }

    // --------------------------------------------------
    // Construção do e-mail (cabeçalhos e corpo)
    // --------------------------------------------------

    /**
     * Gera os cabeçalhos MIME do e-mail.
     */
    protected function createHeader(): string
    {
        $result = '';

        // From
        $result .= $this->headerLine('From', $this->formatAddress($this->From, $this->FromName));

        // Reply-To
        if (!empty($this->ReplyTo)) {
            $replyTo = [];
            foreach ($this->ReplyTo as [$addr, $name]) {
                $replyTo[] = $this->formatAddress($addr, $name);
            }
            $result .= $this->headerLine('Reply-To', implode(', ', $replyTo));
        }

        // To
        $to = [];
        foreach ($this->to as [$addr, $name]) {
            $to[] = $this->formatAddress($addr, $name);
        }
        $result .= $this->headerLine('To', implode(', ', $to));

        // CC
        if (!empty($this->cc)) {
            $cc = [];
            foreach ($this->cc as [$addr, $name]) {
                $cc[] = $this->formatAddress($addr, $name);
            }
            $result .= $this->headerLine('Cc', implode(', ', $cc));
        }

        // Subject
        $result .= $this->headerLine('Subject', $this->encodeHeader($this->Subject));

        // Date
        $result .= $this->headerLine('Date', date('r'));

        // Message-ID
        $result .= $this->headerLine('Message-ID', $this->generateMessageId());

        // MIME-Version
        $result .= $this->headerLine('MIME-Version', '1.0');

        // X-Mailer
        $result .= $this->headerLine('X-Mailer', 'PHPMailer 6.9.1 (Trivya RH Standalone)');

        // Content-Type
        $boundary = $this->generateBoundary();

        if (!empty($this->attachment)) {
            $result .= $this->headerLine(
                'Content-Type',
                self::CONTENT_TYPE_MULTIPART_MIXED . "; boundary=\"{$boundary}\""
            );
        } elseif (!empty($this->AltBody)) {
            $result .= $this->headerLine(
                'Content-Type',
                self::CONTENT_TYPE_MULTIPART_ALTERNATIVE . "; boundary=\"{$boundary}\""
            );
        } else {
            $result .= $this->headerLine('Content-Type', $this->ContentType . "; charset={$this->CharSet}");
            $result .= $this->headerLine('Content-Transfer-Encoding', $this->Encoding);
        }

        // Cabeçalhos customizados
        foreach ($this->CustomHeader as [$name, $value]) {
            $result .= $this->headerLine($name, $value);
        }

        return $result;
    }

    /**
     * Gera o corpo MIME do e-mail.
     */
    protected function createBody(): string
    {
        $boundary = $this->generateBoundary();
        $body     = '';

        if (!empty($this->attachment)) {
            // Multipart/mixed para e-mails com anexo
            if (!empty($this->AltBody)) {
                $altBoundary = $this->generateBoundary('alt');
                $body .= "--{$boundary}\r\n";
                $body .= "Content-Type: " . self::CONTENT_TYPE_MULTIPART_ALTERNATIVE . "; boundary=\"{$altBoundary}\"\r\n\r\n";
                $body .= "--{$altBoundary}\r\n";
                $body .= "Content-Type: " . self::CONTENT_TYPE_PLAINTEXT . "; charset={$this->CharSet}\r\n";
                $body .= "Content-Transfer-Encoding: {$this->Encoding}\r\n\r\n";
                $body .= $this->encodeString($this->AltBody) . "\r\n";
                $body .= "--{$altBoundary}\r\n";
                $body .= "Content-Type: " . self::CONTENT_TYPE_TEXT_HTML . "; charset={$this->CharSet}\r\n";
                $body .= "Content-Transfer-Encoding: {$this->Encoding}\r\n\r\n";
                $body .= $this->encodeString($this->Body) . "\r\n";
                $body .= "--{$altBoundary}--\r\n";
            } else {
                $body .= "--{$boundary}\r\n";
                $body .= "Content-Type: {$this->ContentType}; charset={$this->CharSet}\r\n";
                $body .= "Content-Transfer-Encoding: {$this->Encoding}\r\n\r\n";
                $body .= $this->encodeString($this->Body) . "\r\n";
            }

            // Anexos
            foreach ($this->attachment as [$path, $name, $encoding, $type]) {
                $fileContent = file_get_contents($path);
                if ($fileContent === false) {
                    continue;
                }

                $body .= "--{$boundary}\r\n";
                $body .= "Content-Type: {$type}; name=\"" . $this->encodeHeader($name) . "\"\r\n";
                $body .= "Content-Transfer-Encoding: {$encoding}\r\n";
                $body .= "Content-Disposition: attachment; filename=\"" . $this->encodeHeader($name) . "\"\r\n\r\n";
                $body .= chunk_split(base64_encode($fileContent), 76, "\r\n");
            }

            $body .= "--{$boundary}--\r\n";

        } elseif (!empty($this->AltBody)) {
            // Multipart/alternative (HTML + texto puro)
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Type: " . self::CONTENT_TYPE_PLAINTEXT . "; charset={$this->CharSet}\r\n";
            $body .= "Content-Transfer-Encoding: {$this->Encoding}\r\n\r\n";
            $body .= $this->encodeString($this->AltBody) . "\r\n";

            $body .= "--{$boundary}\r\n";
            $body .= "Content-Type: " . self::CONTENT_TYPE_TEXT_HTML . "; charset={$this->CharSet}\r\n";
            $body .= "Content-Transfer-Encoding: {$this->Encoding}\r\n\r\n";
            $body .= $this->encodeString($this->Body) . "\r\n";

            $body .= "--{$boundary}--\r\n";
        } else {
            // E-mail simples (sem multipart)
            $body = $this->encodeString($this->Body);
        }

        return $body;
    }

    // --------------------------------------------------
    // Utilitários internos
    // --------------------------------------------------

    /**
     * Formata um endereço de e-mail com nome para cabeçalho.
     */
    protected function formatAddress(string $address, string $name = ''): string
    {
        if ($name === '') {
            return $address;
        }

        if (preg_match('/[^a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~.-]/', $name)) {
            return '"' . $this->encodeHeader($name) . "\" <{$address}>";
        }

        return "\"{$name}\" <{$address}>";
    }

    /**
     * Codifica um cabeçalho com caracteres não-ASCII em Base64 (RFC 2047).
     */
    protected function encodeHeader(string $str): string
    {
        if (!preg_match('/[^\x20-\x7E]/', $str)) {
            return $str; // Apenas ASCII — não precisa codificar
        }

        return '=?' . $this->CharSet . '?B?' . base64_encode($str) . '?=';
    }

    /**
     * Codifica o conteúdo do e-mail (Base64 ou QP).
     */
    protected function encodeString(string $str): string
    {
        return match ($this->Encoding) {
            self::ENCODING_BASE64           => chunk_split(base64_encode($str), 76, "\r\n"),
            self::ENCODING_QUOTED_PRINTABLE => quoted_printable_encode($str),
            default                         => $str,
        };
    }

    /**
     * Formata uma linha de cabeçalho.
     */
    protected function headerLine(string $name, string $value): string
    {
        return "{$name}: {$value}\r\n";
    }

    /**
     * Gera um boundary MIME único.
     */
    protected function generateBoundary(string $suffix = ''): string
    {
        static $boundaries = [];

        $key = $suffix ?: 'main';

        if (!isset($boundaries[$key])) {
            $boundaries[$key] = 'b1_' . bin2hex(random_bytes(16)) . ($suffix ? "_{$suffix}" : '');
        }

        return $boundaries[$key];
    }

    /**
     * Gera um Message-ID único.
     */
    protected function generateMessageId(): string
    {
        $dominio = parse_url($this->From, PHP_URL_HOST) ?: gethostname() ?: 'localhost';
        return '<' . bin2hex(random_bytes(16)) . '@' . $dominio . '>';
    }

    /**
     * Define mensagem de erro e retorna false.
     */
    protected function setError(string $msg): bool
    {
        $this->ErrorInfo = $msg;

        if ($this->exceptions) {
            throw new Exception($msg);
        }

        return false;
    }

    /**
     * Valida um endereço de e-mail.
     *
     * @param string $address E-mail a validar
     * @return bool
     */
    public static function validateAddress(string $address): bool
    {
        return (bool) filter_var($address, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Retorna a versão do PHPMailer.
     */
    public static function version(): string
    {
        return '6.9.1';
    }
}
