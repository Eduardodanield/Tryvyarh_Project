<?php

/**
 * Funções de sanitização de entrada — Trivya RH
 *
 * Sanitizar ≠ Validar.
 * Sanitização transforma o valor para uma forma segura/padronizada.
 * Validação verifica se o valor está dentro do esperado.
 *
 * Sempre sanitize ANTES de validar e ANTES de usar o valor.
 *
 * @autor    Equipe Trivya RH
 * @versao   1.0.0
 * @data     2025-01-01
 */

declare(strict_types=1);

/**
 * Sanitiza uma string de uso geral.
 *
 * Remove espaços no início/fim, tags HTML, caracteres de controle
 * e normaliza múltiplos espaços em um único espaço.
 *
 * @param mixed  $valor   Valor a sanitizar
 * @param int    $maxLen  Comprimento máximo (0 = sem limite)
 * @return string String sanitizada
 */
function sanitizeString(mixed $valor, int $maxLen = 0): string
{
    if ($valor === null || $valor === false) {
        return '';
    }

    $str = (string) $valor;

    // Remover tags HTML/PHP
    $str = strip_tags($str);

    // Remover caracteres de controle (exceto \t, \n, \r)
    $str = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $str);

    // Normalizar múltiplos espaços/tabs em um único espaço
    $str = preg_replace('/\s+/', ' ', $str);

    // Remover espaços no início e fim
    $str = trim($str);

    // Aplicar limite de comprimento
    if ($maxLen > 0 && mb_strlen($str, 'UTF-8') > $maxLen) {
        $str = mb_substr($str, 0, $maxLen, 'UTF-8');
    }

    return $str;
}

/**
 * Sanitiza um texto longo (como observações, mensagens, bios).
 *
 * Diferente de sanitizeString(), preserva quebras de linha
 * pois textos longos geralmente precisam de formatação.
 *
 * @param mixed $valor   Valor a sanitizar
 * @param int   $maxLen  Comprimento máximo (0 = sem limite)
 * @return string Texto sanitizado
 */
function sanitizeTexto(mixed $valor, int $maxLen = 5000): string
{
    if ($valor === null || $valor === false) {
        return '';
    }

    $str = (string) $valor;

    // Remover tags HTML (mas manter quebras de linha)
    $str = strip_tags($str);

    // Remover caracteres de controle exceto \n, \r, \t
    $str = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $str);

    // Normalizar quebras de linha (Windows \r\n → Unix \n)
    $str = str_replace("\r\n", "\n", $str);
    $str = str_replace("\r", "\n", $str);

    // Limitar quebras de linha consecutivas (máximo 2)
    $str = preg_replace('/\n{3,}/', "\n\n", $str);

    $str = trim($str);

    if ($maxLen > 0 && mb_strlen($str, 'UTF-8') > $maxLen) {
        $str = mb_substr($str, 0, $maxLen, 'UTF-8');
    }

    return $str;
}

/**
 * Sanitiza um endereço de e-mail.
 *
 * Remove espaços, converte para minúsculas e aplica o filtro
 * nativo do PHP para e-mails.
 *
 * @param mixed $valor E-mail a sanitizar
 * @return string E-mail sanitizado (pode ser inválido — use validarEmail() depois)
 */
function sanitizeEmail(mixed $valor): string
{
    if ($valor === null) {
        return '';
    }

    $email = trim(strtolower((string) $valor));
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    return $email !== false ? $email : '';
}

/**
 * Sanitiza um número de telefone, mantendo apenas dígitos.
 *
 * @param mixed $valor Telefone a sanitizar
 * @return string Apenas dígitos (ex: "11999999999")
 */
function sanitizeTelefone(mixed $valor): string
{
    if ($valor === null) {
        return '';
    }

    // Remover tudo que não for dígito
    return preg_replace('/\D/', '', (string) $valor);
}

/**
 * Sanitiza um CNPJ, mantendo apenas dígitos.
 *
 * @param mixed $valor CNPJ a sanitizar
 * @return string 14 dígitos sem formatação
 */
function sanitizeCNPJ(mixed $valor): string
{
    return preg_replace('/\D/', '', (string) $valor);
}

/**
 * Sanitiza uma URL.
 *
 * Remove caracteres inválidos e garante que começa com http/https.
 *
 * @param mixed $valor URL a sanitizar
 * @return string URL sanitizada ou string vazia
 */
function sanitizeUrl(mixed $valor): string
{
    if ($valor === null || $valor === '') {
        return '';
    }

    $url = trim((string) $valor);
    $url = filter_var($url, FILTER_SANITIZE_URL);

    if ($url === false) {
        return '';
    }

    // Garantir que começa com http:// ou https://
    if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
        $url = 'https://' . $url;
    }

    return $url;
}

/**
 * Sanitiza um inteiro.
 *
 * @param mixed    $valor   Valor a sanitizar
 * @param int|null $min     Valor mínimo aceito
 * @param int|null $max     Valor máximo aceito
 * @return int|null Inteiro sanitizado ou null se inválido
 */
function sanitizeInt(mixed $valor, ?int $min = null, ?int $max = null): ?int
{
    if ($valor === null || $valor === '') {
        return null;
    }

    $int = filter_var($valor, FILTER_VALIDATE_INT);

    if ($int === false) {
        return null;
    }

    if ($min !== null && $int < $min) {
        return $min;
    }

    if ($max !== null && $int > $max) {
        return $max;
    }

    return $int;
}

/**
 * Sanitiza um array de entrada (ex: $_POST, $_GET).
 *
 * @param array  $dados  Array de entrada
 * @param array  $campos Mapa ['campo' => 'tipo'] onde tipo pode ser:
 *                        'string', 'email', 'telefone', 'url', 'int', 'texto', 'cnpj'
 * @return array Array com valores sanitizados
 */
function sanitizeArray(array $dados, array $campos): array
{
    $sanitizado = [];

    foreach ($campos as $campo => $tipo) {
        $valor = $dados[$campo] ?? null;

        $sanitizado[$campo] = match ($tipo) {
            'email'    => sanitizeEmail($valor),
            'telefone' => sanitizeTelefone($valor),
            'url'      => sanitizeUrl($valor),
            'int'      => sanitizeInt($valor),
            'texto'    => sanitizeTexto($valor),
            'cnpj'     => sanitizeCNPJ($valor),
            default    => sanitizeString($valor), // 'string' e qualquer outro
        };
    }

    return $sanitizado;
}

/**
 * Sanitiza um nome de arquivo para upload.
 *
 * Remove caracteres especiais e garante que é seguro para o sistema de arquivos.
 *
 * @param string $nome Nome original do arquivo
 * @return string Nome sanitizado
 */
function sanitizeNomeArquivo(string $nome): string
{
    // Separar extensão
    $ext  = strtolower(pathinfo($nome, PATHINFO_EXTENSION));
    $base = pathinfo($nome, PATHINFO_FILENAME);

    // Substituir caracteres especiais por hífen
    $base = preg_replace('/[^a-zA-Z0-9_-]/', '-', $base);
    $base = preg_replace('/-+/', '-', $base);
    $base = trim($base, '-');

    // Limitar comprimento do nome (sem extensão)
    $base = mb_substr($base, 0, 100);

    // Adicionar timestamp para evitar colisões
    $timestamp = date('YmdHis');

    return "{$base}_{$timestamp}.{$ext}";
}
