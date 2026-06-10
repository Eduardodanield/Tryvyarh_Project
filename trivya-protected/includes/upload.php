<?php
/**
 * Utilitário de upload seguro de imagens — Trivya RH
 * Salva em assets/img/ com validação de tipo e tamanho.
 */
declare(strict_types=1);

/**
 * Faz upload de uma imagem, valida e salva em assets/img/.
 *
 * @param array  $arquivo  Elemento de $_FILES
 * @param string $pasta    Caminho relativo à raiz (ex: 'assets/img')
 * @param string $prefixo  Prefixo do nome do arquivo salvo
 * @return array{sucesso:bool, mensagem:string, caminho:string}
 */
function uploadImagem(array $arquivo, string $pasta = 'assets/img', string $prefixo = 'img'): array
{
    $raiz = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__);

    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
    $extsPermitidas  = ['jpg', 'jpeg', 'png', 'webp'];
    $tamanhoMax      = 5 * 1024 * 1024; // 5 MB

    // Verificar erro de upload
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        return ['sucesso' => false, 'mensagem' => 'Erro no upload. Tente novamente.', 'caminho' => ''];
    }

    // Verificar se é um upload real
    if (!is_uploaded_file($arquivo['tmp_name'])) {
        return ['sucesso' => false, 'mensagem' => 'Arquivo inválido.', 'caminho' => ''];
    }

    // Verificar tamanho
    if ($arquivo['size'] > $tamanhoMax) {
        return ['sucesso' => false, 'mensagem' => 'Arquivo muito grande. Máximo: 5 MB.', 'caminho' => ''];
    }

    // Verificar tipo MIME real (não confiar no nome do arquivo)
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $tipoReal = $finfo->file($arquivo['tmp_name']);

    if (!in_array($tipoReal, $tiposPermitidos, true)) {
        return ['sucesso' => false, 'mensagem' => 'Tipo de arquivo não permitido. Use JPG, PNG ou WebP.', 'caminho' => ''];
    }

    // Determinar extensão com base no tipo MIME real
    $ext = match ($tipoReal) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        default      => 'jpg',
    };

    // Gerar nome único
    $nomeArquivo = $prefixo . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

    $destino = $raiz . '/' . trim($pasta, '/') . '/' . $nomeArquivo;

    // Garantir que a pasta existe
    if (!is_dir(dirname($destino))) {
        mkdir(dirname($destino), 0755, true);
    }

    if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
        return ['sucesso' => false, 'mensagem' => 'Falha ao salvar o arquivo. Verifique as permissões da pasta.', 'caminho' => ''];
    }

    // Retorna o caminho relativo para salvar no banco
    $caminhoRelativo = trim($pasta, '/') . '/' . $nomeArquivo;

    return [
        'sucesso'   => true,
        'mensagem'  => 'Imagem enviada com sucesso.',
        'caminho'   => $caminhoRelativo,
        'nome'      => $nomeArquivo,
    ];
}
