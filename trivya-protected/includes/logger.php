<?php

/**
 * Classe Logger — Trivya RH
 *
 * Registra eventos da aplicação em dois destinos:
 *   1. Tabela `logs` no banco de dados (consultável no painel admin)
 *   2. Arquivo em logs/ (disponível mesmo com banco fora do ar)
 *
 * Níveis de severidade (em ordem crescente):
 *   info → warning → error → critical
 *
 * USO:
 *   Logger::info('Lead cadastrado', ['lead_id' => 42]);
 *   Logger::error('Falha no envio de e-mail', ['destinatario' => $email]);
 *
 * @autor    Equipe Trivya RH
 * @versao   1.0.0
 * @data     2025-01-01
 */

declare(strict_types=1);

class Logger
{
    // Níveis de log ordenados por severidade
    private const NIVEIS = ['info', 'warning', 'error', 'critical'];

    // Mapeamento de arquivo por nível
    private const ARQUIVOS = [
        'info'     => LOG_FILE_ACCESS,
        'warning'  => LOG_FILE_ACCESS,
        'error'    => LOG_FILE_ERROR,
        'critical' => LOG_FILE_ERROR,
    ];

    /**
     * Registra evento de nível INFO.
     * Use para ações normais de usuários e sistema (leads, candidaturas, acessos).
     *
     * @param string $mensagem Descrição do evento
     * @param array  $contexto Dados adicionais (serão codificados em JSON)
     * @param string $modulo   Módulo da aplicação responsável pelo evento
     */
    public static function info(string $mensagem, array $contexto = [], string $modulo = 'app'): void
    {
        self::registrar('info', $mensagem, $contexto, $modulo);
    }

    /**
     * Registra evento de nível WARNING.
     * Use para situações inesperadas mas não críticas (tentativa de CSRF, validação falhou).
     */
    public static function warning(string $mensagem, array $contexto = [], string $modulo = 'app'): void
    {
        self::registrar('warning', $mensagem, $contexto, $modulo);
    }

    /**
     * Registra evento de nível ERROR.
     * Use para falhas que impedem uma operação (e-mail não enviado, banco retornou erro).
     */
    public static function error(string $mensagem, array $contexto = [], string $modulo = 'app'): void
    {
        self::registrar('error', $mensagem, $contexto, $modulo);
    }

    /**
     * Registra evento de nível CRITICAL.
     * Use para falhas graves que comprometem o sistema (conexão com banco perdida, hack detectado).
     */
    public static function critical(string $mensagem, array $contexto = [], string $modulo = 'app'): void
    {
        self::registrar('critical', $mensagem, $contexto, $modulo);
    }

    /**
     * Registra o evento nos dois destinos (banco + arquivo).
     *
     * @param string $nivel    Nível de severidade
     * @param string $mensagem Descrição do evento
     * @param array  $contexto Dados adicionais
     * @param string $modulo   Módulo responsável
     */
    private static function registrar(string $nivel, string $mensagem, array $contexto, string $modulo): void
    {
        // Coletar informações do contexto da requisição
        $ip      = $_SERVER['REMOTE_ADDR'] ?? null;
        $url     = ($_SERVER['REQUEST_URI'] ?? null);
        $agente  = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $adminId = isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null;

        // Tentar salvar no banco primeiro
        self::salvarNoBanco($nivel, $mensagem, $contexto, $modulo, $ip, $url, $adminId);

        // Sempre salvar em arquivo (backup confiável)
        self::salvarEmArquivo($nivel, $mensagem, $contexto, $modulo, $ip, $url, $agente);
    }

    /**
     * Persiste o log na tabela `logs` do banco de dados.
     */
    private static function salvarNoBanco(
        string $nivel,
        string $mensagem,
        array  $contexto,
        string $modulo,
        ?string $ip,
        ?string $url,
        ?int    $adminId
    ): void {
        try {
            $db = Database::getInstance();
            $db->execute(
                "INSERT INTO logs (nivel, modulo, mensagem, contexto, ip, url, admin_id, criado_em)
                 VALUES (:nivel, :modulo, :mensagem, :contexto, :ip, :url, :admin_id, NOW())",
                [
                    ':nivel'    => $nivel,
                    ':modulo'   => $modulo,
                    ':mensagem' => $mensagem,
                    ':contexto' => empty($contexto) ? null : json_encode($contexto, JSON_UNESCAPED_UNICODE),
                    ':ip'       => $ip,
                    ':url'      => $url ? mb_substr($url, 0, 500, 'UTF-8') : null,
                    ':admin_id' => $adminId,
                ]
            );
        } catch (Exception $e) {
            // Falha no banco não deve impedir o log em arquivo
            error_log("[Logger] Falha ao salvar log no banco: " . $e->getMessage());
        }
    }

    /**
     * Salva o log em arquivo texto.
     *
     * Formato: [2025-01-01 14:30:00] [CRITICAL] [modulo] Mensagem {"contexto":"valor"}
     */
    private static function salvarEmArquivo(
        string $nivel,
        string $mensagem,
        array  $contexto,
        string $modulo,
        ?string $ip,
        ?string $url,
        ?string $agente
    ): void {
        try {
            $arquivoDestino = self::ARQUIVOS[$nivel] ?? LOG_FILE_ACCESS;

            // Verificar tamanho e rotacionar se necessário
            self::rotacionarSeNecessario($arquivoDestino);

            $timestamp  = date('Y-m-d H:i:s');
            $nivelUpper = strtoupper($nivel);
            $contextoJson = empty($contexto) ? '' : ' ' . json_encode($contexto, JSON_UNESCAPED_UNICODE);

            $linhaLog = "[{$timestamp}] [{$nivelUpper}] [{$modulo}] {$mensagem}{$contextoJson}" .
                        " | IP:{$ip}" .
                        " | URL:{$url}" .
                        PHP_EOL;

            // FILE_APPEND com LOCK_EX para evitar corrupção em escrita concorrente
            file_put_contents($arquivoDestino, $linhaLog, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            // Último recurso: error_log nativo do PHP
            error_log("[Logger] Falha ao salvar em arquivo: " . $e->getMessage() . " | Log original: {$mensagem}");
        }
    }

    /**
     * Rotaciona o arquivo de log se ultrapassar LOG_MAX_LINES linhas.
     * Renomeia o arquivo atual para .bak e cria um novo vazio.
     */
    private static function rotacionarSeNecessario(string $caminho): void
    {
        if (!file_exists($caminho)) {
            return;
        }

        // Contar linhas sem carregar o arquivo inteiro na memória
        $linhas = 0;
        $handle = fopen($caminho, 'r');
        if ($handle === false) {
            return;
        }

        while (!feof($handle)) {
            fgets($handle);
            $linhas++;
            if ($linhas > LOG_MAX_LINES) {
                break;
            }
        }
        fclose($handle);

        if ($linhas > LOG_MAX_LINES) {
            $backup = $caminho . '.' . date('YmdHis') . '.bak';
            rename($caminho, $backup);
        }
    }

    /**
     * Retorna os últimos N registros de log do banco (para o painel admin).
     *
     * @param int         $limite  Número de registros
     * @param string|null $nivel   Filtrar por nível (null = todos)
     * @param string|null $modulo  Filtrar por módulo (null = todos)
     * @return array Lista de logs
     */
    public static function buscar(int $limite = 100, ?string $nivel = null, ?string $modulo = null): array
    {
        try {
            $db     = Database::getInstance();
            $where  = [];
            $params = [];

            if ($nivel !== null) {
                $where[]         = 'nivel = :nivel';
                $params[':nivel'] = $nivel;
            }

            if ($modulo !== null) {
                $where[]          = 'modulo = :modulo';
                $params[':modulo'] = $modulo;
            }

            $clausulaWhere = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            return $db->fetchAll(
                "SELECT * FROM logs {$clausulaWhere} ORDER BY criado_em DESC LIMIT :limite",
                array_merge($params, [':limite' => $limite])
            );
        } catch (Exception $e) {
            error_log("[Logger] Falha ao buscar logs: " . $e->getMessage());
            return [];
        }
    }
}
