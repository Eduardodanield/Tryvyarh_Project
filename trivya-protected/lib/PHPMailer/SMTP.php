<?php

/**
 * PHPMailer SMTP — versão standalone para Trivya RH
 *
 * Implementa o protocolo SMTP com suporte a STARTTLS e SMTPS.
 * Compatível com a API oficial do PHPMailer 6.x.
 *
 * @autor    PHPMailer Team (adaptado para Trivya RH)
 * @versao   6.9.1 (standalone)
 * @data     2025-01-01
 * @license  LGPL 2.1
 */

namespace PHPMailer\PHPMailer;

/**
 * Classe de comunicação SMTP.
 */
class SMTP
{
    // Constantes de modo de debug
    const DEBUG_OFF    = 0;
    const DEBUG_CLIENT = 1;
    const DEBUG_SERVER = 2;
    const DEBUG_CONNECTION = 3;
    const DEBUG_LOWLEVEL = 4;

    // Versão
    const VERSION = '6.9.1';

    // Término de linha SMTP (RFC 5321)
    const LE = "\r\n";

    // Número máximo de bytes por linha SMTP (RFC 5321 §4.5.3.1.6)
    const MAX_LINE_LENGTH = 998;

    // Número máximo de destinatários por mensagem
    const MAX_REPLY_LENGTH = 512;

    /** @var int Nível de debug */
    public int $do_debug = self::DEBUG_OFF;

    /** @var callable|null Callback para output de debug */
    public $Debugoutput = 'echo';

    /** @var bool Verificar certificado SSL do servidor */
    public bool $SMTPOptions_verify_peer = true;

    /** @var resource|null Socket de conexão */
    protected $smtp_conn = null;

    /** @var string Último código de resposta SMTP */
    protected string $last_reply = '';

    /** @var string Erro da última operação */
    protected string $error = '';

    /** @var array Extensões SMTP anunciadas pelo servidor (EHLO) */
    protected array $server_caps = [];

    /** @var string Versão do servidor SMTP */
    protected string $helo_rply = '';

    /** @var int Timeout de conexão em segundos */
    public int $Timeout = 300;

    /** @var int Tempo limite de operação SMTP em segundos */
    public int $Timelimit = 300;

    /**
     * Conecta ao servidor SMTP.
     *
     * @param string   $host    Hostname do servidor
     * @param int      $port    Porta (25, 465, 587)
     * @param int      $timeout Timeout em segundos
     * @param array    $options Opções de contexto SSL
     * @return bool
     */
    public function connect(string $host, int $port = 25, int $timeout = 30, array $options = []): bool
    {
        $this->setError('');
        $this->server_caps = [];

        // Construir contexto SSL
        $streamContext = stream_context_create(['ssl' => array_merge([
            'verify_peer'       => true,
            'verify_peer_name'  => true,
            'allow_self_signed' => false,
        ], $options['ssl'] ?? [])]);

        $errno  = 0;
        $errstr = '';

        // Abrir socket
        $this->smtp_conn = @stream_socket_client(
            $host . ':' . $port,
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $streamContext
        );

        if (!is_resource($this->smtp_conn)) {
            $this->setError("Falha ao conectar ao SMTP: {$errstr} ({$errno})");
            return false;
        }

        // Definir timeout de leitura/escrita
        stream_set_timeout($this->smtp_conn, $timeout);

        // Ler banner de boas-vindas do servidor
        $anuncio = $this->getLines();
        $this->debugOutput("CONECTADO: {$anuncio}", self::DEBUG_CONNECTION);

        return true;
    }

    /**
     * Envia o comando EHLO/HELO e processa as extensões suportadas.
     *
     * @param string $host Nome do host local (FQDN ou IP)
     * @return bool
     */
    public function hello(string $host = ''): bool
    {
        return $this->sendHello('EHLO', $host) || $this->sendHello('HELO', $host);
    }

    /**
     * Envia EHLO ou HELO.
     */
    protected function sendHello(string $hello, string $host): bool
    {
        $noExt = ($hello === 'HELO');

        $this->sendCommand($hello, "{$hello} {$host}", 250);

        $this->helo_rply   = $this->last_reply;
        $this->server_caps = [];

        if (!$noExt) {
            foreach (explode("\n", $this->helo_rply) as $linha) {
                if (preg_match('/^250[ -](\w+)(.*)?$/', trim($linha), $m)) {
                    $this->server_caps[strtolower($m[1])] = trim($m[2] ?? '');
                }
            }
        }

        return $this->getLastReplyCode() === 250;
    }

    /**
     * Inicia o upgrade TLS na conexão existente (STARTTLS).
     *
     * @param array $params Parâmetros de contexto SSL
     * @return bool
     */
    public function startTLS(array $params = []): bool
    {
        if (!$this->sendCommand('STARTTLS', 'STARTTLS', 220)) {
            return false;
        }

        $crypto = STREAM_CRYPTO_METHOD_TLS_CLIENT;

        // Tentar TLS 1.2 ou superior preferencialmente
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
            $crypto = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT | STREAM_CRYPTO_METHOD_TLS_CLIENT;
        }

        set_error_handler(static function () {});
        $resultado = stream_socket_enable_crypto($this->smtp_conn, true, $crypto);
        restore_error_handler();

        return (bool) $resultado;
    }

    /**
     * Autentica no servidor SMTP usando AUTH LOGIN ou AUTH PLAIN.
     *
     * @param string $username Usuário
     * @param string $password Senha
     * @param string $authtype Método: 'LOGIN', 'PLAIN', 'XOAUTH2'
     * @return bool
     */
    public function authenticate(string $username, string $password, string $authtype = 'LOGIN'): bool
    {
        switch (strtoupper($authtype)) {
            case 'PLAIN':
                $this->sendCommand('AUTH', 'AUTH PLAIN', 334);
                return $this->sendCommand(
                    'User & Password',
                    base64_encode("\0{$username}\0{$password}"),
                    235
                );

            case 'LOGIN':
            default:
                $this->sendCommand('AUTH LOGIN', 'AUTH LOGIN', 334);
                $this->sendCommand('Username', base64_encode($username), 334);
                return $this->sendCommand('Password', base64_encode($password), 235);
        }
    }

    /**
     * Envia o envelope de remetente (MAIL FROM).
     *
     * @param string $from E-mail do remetente
     * @return bool
     */
    public function mail(string $from): bool
    {
        return $this->sendCommand('MAIL FROM', "MAIL FROM:<{$from}>", 250);
    }

    /**
     * Envia o envelope de destinatário (RCPT TO).
     *
     * @param string $to E-mail do destinatário
     * @return bool
     */
    public function recipient(string $to): bool
    {
        return $this->sendCommand('RCPT TO', "RCPT TO:<{$to}>", [250, 251]);
    }

    /**
     * Inicia o bloco DATA e envia o conteúdo do e-mail.
     *
     * @param string $msg Mensagem completa (cabeçalhos + corpo)
     * @return bool
     */
    public function data(string $msg): bool
    {
        if (!$this->sendCommand('DATA', 'DATA', 354)) {
            return false;
        }

        // Transparência de ponto: linhas que começam com . precisam de ..
        $msg = str_replace("\n.", "\n..", "\r\n" . ltrim($msg, "\r\n"));
        $this->client_send($msg . self::LE . '.' . self::LE);

        $this->last_reply = $this->getLines();
        return $this->getLastReplyCode() === 250;
    }

    /**
     * Envia RESET para cancelar a transação atual sem desconectar.
     */
    public function reset(): bool
    {
        return $this->sendCommand('RSET', 'RSET', 250);
    }

    /**
     * Envia QUIT e fecha a conexão.
     */
    public function quit(bool $close = true): bool
    {
        $this->sendCommand('QUIT', 'QUIT', 221);
        if ($close) {
            $this->close();
        }
        return true;
    }

    /**
     * Fecha o socket de conexão.
     */
    public function close(): void
    {
        if (is_resource($this->smtp_conn)) {
            fclose($this->smtp_conn);
            $this->smtp_conn = null;
        }
    }

    /**
     * Verifica se há conexão ativa.
     */
    public function connected(): bool
    {
        return is_resource($this->smtp_conn);
    }

    /**
     * Retorna o código de resposta da última operação.
     */
    public function getLastReplyCode(): int
    {
        return (int) substr(trim($this->last_reply), 0, 3);
    }

    /**
     * Retorna o texto da última resposta do servidor.
     */
    public function getLastReply(): string
    {
        return $this->last_reply;
    }

    /**
     * Retorna a mensagem de erro da última falha.
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * Retorna as extensões SMTP suportadas pelo servidor.
     */
    public function getServerExtList(): array
    {
        return $this->server_caps;
    }

    /**
     * Envia um comando SMTP e aguarda resposta com o código esperado.
     *
     * @param string    $command     Nome do comando (para debug)
     * @param string    $commandstring Comando SMTP completo
     * @param int|array $expect      Código(s) de resposta esperado(s)
     * @return bool
     */
    protected function sendCommand(string $command, string $commandstring, int|array $expect): bool
    {
        if (!$this->connected()) {
            $this->setError("Comando enviado sem conexão: {$command}");
            return false;
        }

        $this->client_send($commandstring . self::LE);

        $this->last_reply = $this->getLines();
        $responseCode = $this->getLastReplyCode();

        $expect = (array) $expect;

        if (!in_array($responseCode, $expect, true)) {
            $this->setError(
                "{$command}: falha [{$responseCode}]: {$this->last_reply}"
            );
            return false;
        }

        $this->debugOutput("CMD: {$commandstring}\nRES: {$this->last_reply}", self::DEBUG_SERVER);
        return true;
    }

    /**
     * Envia dados pelo socket.
     */
    protected function client_send(string $data): int|bool
    {
        return fwrite($this->smtp_conn, $data);
    }

    /**
     * Lê linhas de resposta do servidor SMTP.
     */
    protected function getLines(): string
    {
        if (!is_resource($this->smtp_conn)) {
            return '';
        }

        $data     = '';
        $endTime  = time() + $this->Timelimit;

        while (is_resource($this->smtp_conn) && !feof($this->smtp_conn)) {
            $str = @fgets($this->smtp_conn, 515);

            if ($str === false) {
                break;
            }

            $data .= $str;

            // Resposta termina quando a linha tem formato "NNN " (sem hífen)
            if (preg_match('/^\d{3}[^-]/m', $str)) {
                break;
            }

            if (time() > $endTime) {
                $this->setError('Timeout ao aguardar resposta SMTP');
                break;
            }
        }

        return $data;
    }

    /**
     * Define a mensagem de erro interna.
     */
    protected function setError(string $msg): void
    {
        $this->error = $msg;
        $this->debugOutput("ERRO: {$msg}", self::DEBUG_CLIENT);
    }

    /**
     * Imprime mensagem de debug se o nível for adequado.
     */
    protected function debugOutput(string $str, int $level): void
    {
        if ($level > $this->do_debug) {
            return;
        }

        if (is_callable($this->Debugoutput)) {
            call_user_func($this->Debugoutput, $str, $level);
        } else {
            echo gmdate('Y-m-d H:i:s') . "\t" . htmlspecialchars($str, ENT_QUOTES) . "\n";
        }
    }
}
