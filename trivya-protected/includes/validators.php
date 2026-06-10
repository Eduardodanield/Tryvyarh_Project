<?php

/**
 * Funções de validação de dados — Trivya RH
 *
 * Retornam strings de erro (vazia = válido) ou booleanos.
 * As funções de validação assumem que os dados JÁ foram sanitizados.
 *
 * Padrão de uso:
 *   $erro = validarEmail($email);
 *   if ($erro) { // tratar erro }
 *
 * @autor    Equipe Trivya RH
 * @versao   1.0.0
 * @data     2025-01-01
 */

declare(strict_types=1);

/**
 * Valida se um campo obrigatório não está vazio.
 *
 * @param mixed  $valor  Valor a verificar
 * @param string $rotulo Nome do campo para mensagem de erro
 * @return string Mensagem de erro ou string vazia se válido
 */
function validarObrigatorio(mixed $valor, string $rotulo = 'Campo'): string
{
    if ($valor === null || $valor === '' || $valor === false) {
        return "{$rotulo} é obrigatório.";
    }
    return '';
}

/**
 * Valida comprimento de string.
 *
 * @param string   $valor  Valor a verificar
 * @param int      $min    Comprimento mínimo
 * @param int|null $max    Comprimento máximo (null = sem limite)
 * @param string   $rotulo Nome do campo
 * @return string Mensagem de erro ou string vazia se válido
 */
function validarComprimento(string $valor, int $min = 0, ?int $max = null, string $rotulo = 'Campo'): string
{
    $len = mb_strlen($valor, 'UTF-8');

    if ($len < $min) {
        return "{$rotulo} deve ter no mínimo {$min} caractere" . ($min > 1 ? 's' : '') . ".";
    }

    if ($max !== null && $len > $max) {
        return "{$rotulo} deve ter no máximo {$max} caractere" . ($max > 1 ? 's' : '') . ".";
    }

    return '';
}

/**
 * Valida um endereço de e-mail.
 *
 * @param string $email E-mail já sanitizado
 * @return string Mensagem de erro ou string vazia se válido
 */
function validarEmail(string $email): string
{
    if (empty($email)) {
        return 'E-mail é obrigatório.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Informe um e-mail válido.';
    }

    if (mb_strlen($email, 'UTF-8') > 254) {
        return 'E-mail muito longo (máximo 254 caracteres).';
    }

    return '';
}

/**
 * Valida um número de telefone brasileiro.
 *
 * Aceita celular (11 dígitos) e fixo (10 dígitos), apenas dígitos.
 * Deve receber o valor já sanitizado por sanitizeTelefone().
 *
 * @param string $telefone Apenas dígitos (ex: "11999999999")
 * @return string Mensagem de erro ou string vazia se válido
 */
function validarTelefone(string $telefone): string
{
    if (empty($telefone)) {
        return 'Telefone é obrigatório.';
    }

    $len = strlen($telefone);

    if ($len < 10 || $len > 11) {
        return 'Telefone inválido. Informe DDD + número (10 ou 11 dígitos).';
    }

    // DDD válidos no Brasil (11-99, excluindo alguns não utilizados)
    $ddd = (int) substr($telefone, 0, 2);
    $dddsValidos = [11,12,13,14,15,16,17,18,19,21,22,24,27,28,31,32,33,34,35,37,38,41,42,43,44,45,46,47,48,49,51,53,54,55,61,62,63,64,65,66,67,68,69,71,73,74,75,77,79,81,82,83,84,85,86,87,88,89,91,92,93,94,95,96,97,98,99];

    if (!in_array($ddd, $dddsValidos, true)) {
        return 'DDD inválido.';
    }

    // Celular: deve começar com 9 após o DDD
    if ($len === 11 && substr($telefone, 2, 1) !== '9') {
        return 'Número de celular inválido (deve iniciar com 9 após o DDD).';
    }

    return '';
}

/**
 * Valida um CNPJ brasileiro.
 *
 * Implementa o algoritmo oficial de verificação dos dígitos verificadores.
 * Deve receber apenas os 14 dígitos (sem pontos, barras, hífens).
 *
 * @param string $cnpj 14 dígitos sem formatação
 * @return string Mensagem de erro ou string vazia se válido
 */
function validarCNPJ(string $cnpj): string
{
    if (empty($cnpj)) {
        return 'CNPJ é obrigatório.';
    }

    if (strlen($cnpj) !== 14 || !ctype_digit($cnpj)) {
        return 'CNPJ inválido. Informe os 14 dígitos.';
    }

    // Rejeitar sequências de dígitos iguais (ex: 00000000000000)
    if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
        return 'CNPJ inválido.';
    }

    // Calcular primeiro dígito verificador
    $pesos1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $soma1  = 0;
    for ($i = 0; $i < 12; $i++) {
        $soma1 += (int) $cnpj[$i] * $pesos1[$i];
    }
    $resto1 = $soma1 % 11;
    $dig1   = $resto1 < 2 ? 0 : 11 - $resto1;

    // Calcular segundo dígito verificador
    $pesos2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $soma2  = 0;
    for ($i = 0; $i < 13; $i++) {
        $soma2 += (int) $cnpj[$i] * $pesos2[$i];
    }
    $resto2 = $soma2 % 11;
    $dig2   = $resto2 < 2 ? 0 : 11 - $resto2;

    if ((int) $cnpj[12] !== $dig1 || (int) $cnpj[13] !== $dig2) {
        return 'CNPJ inválido (dígitos verificadores incorretos).';
    }

    return '';
}

/**
 * Valida uma URL.
 *
 * @param string $url URL já sanitizada
 * @return string Mensagem de erro ou string vazia se válido
 */
function validarUrl(string $url): string
{
    if (empty($url)) {
        return '';  // URL é geralmente opcional
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return 'URL inválida.';
    }

    // Aceitar apenas http e https (bloquear javascript:, ftp:, etc.)
    $scheme = parse_url($url, PHP_URL_SCHEME);
    if (!in_array($scheme, ['http', 'https'], true)) {
        return 'A URL deve usar http:// ou https://.';
    }

    return '';
}

/**
 * Valida um arquivo enviado via upload.
 *
 * @param array  $arquivo    Elemento de $_FILES['campo']
 * @param array  $tiposPermitidos  Tipos MIME aceitos
 * @param int    $tamanhoMax Tamanho máximo em bytes
 * @return string Mensagem de erro ou string vazia se válido
 */
function validarUpload(array $arquivo, array $tiposPermitidos = [], int $tamanhoMax = 5242880): string
{
    // Verificar erros do upload
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        return match ($arquivo['error']) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Arquivo muito grande.',
            UPLOAD_ERR_PARTIAL  => 'O upload não foi completado.',
            UPLOAD_ERR_NO_FILE  => 'Nenhum arquivo enviado.',
            default             => 'Erro no upload do arquivo.',
        };
    }

    // Verificar se é um upload real (proteção contra path traversal)
    if (!is_uploaded_file($arquivo['tmp_name'])) {
        return 'Arquivo inválido.';
    }

    // Verificar tamanho
    if ($arquivo['size'] > $tamanhoMax) {
        $mb = round($tamanhoMax / 1048576, 1);
        return "O arquivo não pode ultrapassar {$mb} MB.";
    }

    // Verificar tipo MIME real usando finfo (não confiar no tipo enviado pelo browser)
    if (!empty($tiposPermitidos)) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $tipoReal = $finfo->file($arquivo['tmp_name']);

        if (!in_array($tipoReal, $tiposPermitidos, true)) {
            return 'Tipo de arquivo não permitido.';
        }
    }

    return '';
}

/**
 * Executa múltiplas validações e retorna um array de erros.
 *
 * @param array $regras  ['campo' => ['rotulo' => 'Nome', 'obrigatorio' => true, 'email' => true, ...]]
 * @param array $dados   Dados a validar (ex: $_POST sanitizado)
 * @return array Mapa ['campo' => 'mensagem de erro'] (vazio se tudo válido)
 */
function validarCampos(array $regras, array $dados): array
{
    $erros = [];

    foreach ($regras as $campo => $opcoes) {
        $valor  = $dados[$campo] ?? '';
        $rotulo = $opcoes['rotulo'] ?? $campo;

        // Obrigatório
        if (!empty($opcoes['obrigatorio'])) {
            $erro = validarObrigatorio($valor, $rotulo);
            if ($erro) {
                $erros[$campo] = $erro;
                continue; // Não validar mais se está vazio e é obrigatório
            }
        }

        // Pular validações extras se o campo está vazio e não é obrigatório
        if ($valor === '' || $valor === null) {
            continue;
        }

        // Comprimento
        if (isset($opcoes['min']) || isset($opcoes['max'])) {
            $erro = validarComprimento(
                (string) $valor,
                $opcoes['min'] ?? 0,
                $opcoes['max'] ?? null,
                $rotulo
            );
            if ($erro) {
                $erros[$campo] = $erro;
                continue;
            }
        }

        // E-mail
        if (!empty($opcoes['email'])) {
            $erro = validarEmail((string) $valor);
            if ($erro) {
                $erros[$campo] = $erro;
                continue;
            }
        }

        // Telefone
        if (!empty($opcoes['telefone'])) {
            $erro = validarTelefone((string) $valor);
            if ($erro) {
                $erros[$campo] = $erro;
                continue;
            }
        }

        // CNPJ
        if (!empty($opcoes['cnpj'])) {
            $erro = validarCNPJ((string) $valor);
            if ($erro) {
                $erros[$campo] = $erro;
                continue;
            }
        }

        // URL
        if (!empty($opcoes['url'])) {
            $erro = validarUrl((string) $valor);
            if ($erro) {
                $erros[$campo] = $erro;
                continue;
            }
        }
    }

    return $erros;
}
