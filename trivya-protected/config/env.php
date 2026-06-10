<?php

/**
 * Carregador de variáveis de ambiente (.env)
 *
 * Faz o parse do arquivo .env sem dependência de bibliotecas externas.
 * Deve ser o PRIMEIRO arquivo incluído em qualquer ponto de entrada.
 *
 * @autor    Equipe Trivya RH
 * @versao   1.0.0
 * @data     2025-01-01
 */

declare(strict_types=1);

/**
 * Carrega e processa o arquivo .env, populando $_ENV e putenv().
 *
 * Regras do parser:
 *  - Linhas iniciadas com # são comentários e ignoradas
 *  - Formato esperado: CHAVE=valor
 *  - Valores podem ser envolvidos por aspas simples ou duplas
 *  - Variáveis já definidas no ambiente real não são sobrescritas
 */
function loadEnv(string $caminho): void
{
    if (!file_exists($caminho)) {
        throw new RuntimeException(
            "Arquivo .env não encontrado em: {$caminho}\n" .
            "Copie o arquivo .env.example para .env e preencha os dados."
        );
    }

    $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($linhas === false) {
        throw new RuntimeException("Não foi possível ler o arquivo .env.");
    }

    foreach ($linhas as $numeroLinha => $linha) {
        $linha = trim($linha);

        // Ignorar linhas de comentário
        if (str_starts_with($linha, '#') || $linha === '') {
            continue;
        }

        // Verificar se a linha tem o formato CHAVE=valor
        if (!str_contains($linha, '=')) {
            // Linha malformada — logar warning mas não parar execução
            error_log("Aviso: linha " . ($numeroLinha + 1) . " do .env ignorada (formato inválido): {$linha}");
            continue;
        }

        // Separar chave e valor (explode no primeiro = apenas)
        [$chave, $valor] = explode('=', $linha, 2);
        $chave = trim($chave);
        $valor = trim($valor);

        // Remover aspas duplas: "valor"
        if (preg_match('/^"(.*)"$/s', $valor, $matches)) {
            $valor = $matches[1];
            // Processar sequências de escape dentro de aspas duplas
            $valor = str_replace(['\\n', '\\r', '\\t', '\\"'], ["\n", "\r", "\t", '"'], $valor);
        }
        // Remover aspas simples: 'valor' (tudo literal, sem escapes)
        elseif (preg_match("/^'(.*)'$/s", $valor, $matches)) {
            $valor = $matches[1];
        }

        // Não sobrescrever variáveis definidas no ambiente real (ex: variáveis do servidor)
        if (!array_key_exists($chave, $_ENV) && getenv($chave) === false) {
            $_ENV[$chave] = $valor;
            putenv("{$chave}={$valor}");
        }
    }
}

/**
 * Retorna o valor de uma variável de ambiente com fallback.
 *
 * Hierarquia de busca: $_ENV → getenv() → valor padrão
 */
function env(string $chave, mixed $padrao = null): mixed
{
    if (isset($_ENV[$chave])) {
        return $_ENV[$chave];
    }

    $valor = getenv($chave);
    if ($valor !== false) {
        return $valor;
    }

    return $padrao;
}

// Determinar a raiz do projeto (um nível acima de /config/)
$raizProjeto = dirname(__DIR__);

// Carregar o .env da raiz
loadEnv($raizProjeto . DIRECTORY_SEPARATOR . '.env');
