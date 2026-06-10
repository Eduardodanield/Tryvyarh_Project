<?php

/**
 * Funções utilitárias globais — Trivya RH
 *
 * Funções de uso geral disponíveis em toda a aplicação:
 * redirecionamento, escape de output, acesso a configurações,
 * formatação de dados e helpers de resposta.
 *
 * @autor    Equipe Trivya RH
 * @versao   1.0.0
 * @data     2025-01-01
 */

declare(strict_types=1);

// ----------------------------------------------------------
// Navegação
// ----------------------------------------------------------

/**
 * Redireciona para uma URL e encerra a execução.
 *
 * @param string $url  URL de destino (relativa ou absoluta)
 * @param int    $code Código HTTP (302 = temporário, 301 = permanente)
 */
function redirect(string $url, int $code = 302): never
{
    // Garantir que a URL é segura (evitar header injection)
    $url = str_replace(["\r", "\n"], '', $url);

    // Se for URL relativa, prefixar com a URL base do site
    if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://') && !str_starts_with($url, '/')) {
        $url = SITE_URL . '/' . ltrim($url, '/');
    }

    http_response_code($code);
    header("Location: {$url}");
    exit;
}

/**
 * Redireciona para a página anterior ou para uma URL padrão.
 */
function redirectBack(string $padrao = '/'): never
{
    $anterior = $_SERVER['HTTP_REFERER'] ?? '';

    // Validar que o referer é do próprio site (evitar open redirect)
    if ($anterior && str_starts_with($anterior, SITE_URL)) {
        redirect($anterior);
    }

    redirect($padrao);
}

// ----------------------------------------------------------
// Segurança — Output
// ----------------------------------------------------------

/**
 * Escapa uma string para exibição segura em HTML.
 * Substitui os caracteres especiais HTML por entidades.
 * Use em TODO valor exibido no template para prevenir XSS.
 *
 * @param mixed $valor Valor a escapar (aceita null)
 * @return string String segura para HTML
 */
function e(mixed $valor): string
{
    if ($valor === null || $valor === false) {
        return '';
    }
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Imprime uma string já escapada para HTML (atalho de echo e()).
 */
function ee(mixed $valor): void
{
    echo e($valor);
}

// ----------------------------------------------------------
// Configurações do banco de dados (tabela `configuracoes`)
// ----------------------------------------------------------

/** Cache em memória das configurações para evitar múltiplas queries */
$cacheConfiguracoes = [];

/**
 * Retorna o valor de uma configuração da tabela `configuracoes`.
 *
 * @param string $chave   Nome da configuração
 * @param mixed  $padrao  Valor retornado se não encontrado
 * @return mixed Valor da configuração ou $padrao
 */
function getConfig(string $chave, mixed $padrao = null): mixed
{
    global $cacheConfiguracoes;

    // Verificar cache em memória primeiro
    if (isset($cacheConfiguracoes[$chave])) {
        return $cacheConfiguracoes[$chave];
    }

    try {
        $db = Database::getInstance();
        $row = $db->fetchOne(
            "SELECT valor FROM configuracoes WHERE chave = :chave LIMIT 1",
            [':chave' => $chave]
        );

        $valor = $row !== null ? $row['valor'] : $padrao;
        $cacheConfiguracoes[$chave] = $valor;
        return $valor;
    } catch (Exception $e) {
        error_log("[getConfig] Erro ao buscar configuração '{$chave}': " . $e->getMessage());
        return $padrao;
    }
}

/**
 * Carrega todas as configurações de uma vez na memória (otimização).
 * Ideal chamar no início da requisição.
 */
function precarregarConfiguracoes(): void
{
    global $cacheConfiguracoes;

    try {
        $db   = Database::getInstance();
        $rows = $db->fetchAll("SELECT chave, valor FROM configuracoes");

        foreach ($rows as $row) {
            $cacheConfiguracoes[$row['chave']] = $row['valor'];
        }
    } catch (Exception $e) {
        error_log("[precarregarConfiguracoes] " . $e->getMessage());
    }
}

// ----------------------------------------------------------
// Formatação
// ----------------------------------------------------------

/**
 * Formata um número de telefone brasileiro para exibição.
 *
 * Exemplos de saída:
 *   11999999999  → (11) 9 9999-9999  (celular)
 *   1133334444   → (11) 3333-4444    (fixo)
 */
function formatTelefone(string $telefone): string
{
    // Manter apenas dígitos
    $digitos = preg_replace('/\D/', '', $telefone);

    return match (strlen($digitos)) {
        11 => sprintf('(%s) %s %s-%s',
            substr($digitos, 0, 2),
            substr($digitos, 2, 1),
            substr($digitos, 3, 4),
            substr($digitos, 7)
        ),
        10 => sprintf('(%s) %s-%s',
            substr($digitos, 0, 2),
            substr($digitos, 2, 4),
            substr($digitos, 6)
        ),
        default => $telefone, // Retornar original se formato desconhecido
    };
}

/**
 * Formata uma data do banco (Y-m-d ou Y-m-d H:i:s) para o padrão brasileiro (d/m/Y).
 *
 * @param string|null $data       Data no formato MySQL
 * @param bool        $comHorario Incluir horário (H:i) no resultado
 * @return string Data formatada ou '-' se inválida
 */
function formatData(?string $data, bool $comHorario = false): string
{
    if (empty($data) || $data === '0000-00-00') {
        return '-';
    }

    try {
        $dt = new DateTimeImmutable($data);
        return $dt->format($comHorario ? 'd/m/Y H:i' : 'd/m/Y');
    } catch (Exception) {
        return '-';
    }
}

/**
 * Formata um valor monetário para o padrão brasileiro (R$ 1.500,00).
 */
function formatMoeda(float|int|string $valor): string
{
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

/**
 * Trunca um texto e adiciona reticências se ultrapassar o limite.
 *
 * @param string $texto  Texto original
 * @param int    $limite Número máximo de caracteres
 * @param string $sufixo Sufixo adicionado quando truncado (padrão: '...')
 */
function truncar(string $texto, int $limite = 150, string $sufixo = '...'): string
{
    $texto = strip_tags($texto);
    if (mb_strlen($texto, 'UTF-8') <= $limite) {
        return $texto;
    }

    // Truncar no último espaço antes do limite (evitar cortar no meio de palavra)
    $truncado = mb_substr($texto, 0, $limite, 'UTF-8');
    $ultimoEspaco = mb_strrpos($truncado, ' ', 0, 'UTF-8');

    if ($ultimoEspaco !== false) {
        $truncado = mb_substr($truncado, 0, $ultimoEspaco, 'UTF-8');
    }

    return $truncado . $sufixo;
}

/**
 * Gera um slug a partir de um texto (para URLs amigáveis).
 *
 * Exemplo: "Recrutamento e Seleção" → "recrutamento-e-selecao"
 */
function gerarSlug(string $texto): string
{
    // Mapa de transliteração de caracteres especiais
    $mapa = [
        'á'=>'a','à'=>'a','ã'=>'a','â'=>'a','ä'=>'a',
        'é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
        'í'=>'i','ì'=>'i','î'=>'i','ï'=>'i',
        'ó'=>'o','ò'=>'o','õ'=>'o','ô'=>'o','ö'=>'o',
        'ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u',
        'ç'=>'c','ñ'=>'n',
        'Á'=>'a','À'=>'a','Ã'=>'a','Â'=>'a','Ä'=>'a',
        'É'=>'e','È'=>'e','Ê'=>'e','Ë'=>'e',
        'Í'=>'i','Ì'=>'i','Î'=>'i','Ï'=>'i',
        'Ó'=>'o','Ò'=>'o','Õ'=>'o','Ô'=>'o','Ö'=>'o',
        'Ú'=>'u','Ù'=>'u','Û'=>'u','Ü'=>'u',
        'Ç'=>'c','Ñ'=>'n',
    ];

    $slug = mb_strtolower($texto, 'UTF-8');
    $slug = strtr($slug, $mapa);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', trim($slug));
    return trim($slug, '-');
}

// ----------------------------------------------------------
// Respostas JSON (para endpoints da API)
// ----------------------------------------------------------

/**
 * Envia resposta JSON de sucesso e encerra execução.
 */
function jsonSuccess(mixed $dados = null, string $mensagem = 'Operação realizada com sucesso.'): never
{
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'sucesso'  => true,
        'mensagem' => $mensagem,
        'dados'    => $dados,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Envia resposta JSON de erro e encerra execução.
 */
function jsonError(string $mensagem, int $httpCode = 400, array $erros = []): never
{
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'sucesso'  => false,
        'mensagem' => $mensagem,
        'erros'    => $erros,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ----------------------------------------------------------
// Flash messages (mensagens entre redirecionamentos)
// ----------------------------------------------------------

/**
 * Define uma mensagem flash que será exibida na próxima requisição.
 *
 * @param string $tipo    'sucesso' | 'erro' | 'aviso' | 'info'
 * @param string $mensagem Texto da mensagem
 */
function setFlash(string $tipo, string $mensagem): void
{
    $_SESSION['_flash'][$tipo][] = $mensagem;
}

/**
 * Retorna e limpa todas as mensagens flash de um tipo.
 *
 * @param string|null $tipo  Tipo específico ou null para todos
 * @return array Lista de mensagens
 */
function getFlash(?string $tipo = null): array
{
    if ($tipo !== null) {
        $mensagens = $_SESSION['_flash'][$tipo] ?? [];
        unset($_SESSION['_flash'][$tipo]);
        return $mensagens;
    }

    $todos = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $todos;
}

/**
 * Verifica se há mensagens flash pendentes.
 */
function hasFlash(string $tipo): bool
{
    return !empty($_SESSION['_flash'][$tipo]);
}
