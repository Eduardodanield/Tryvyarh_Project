<?php

/**
 * PHPMailer Exception — versão standalone para Trivya RH
 *
 * Classe de exceção personalizada do PHPMailer.
 * Compatível com a API oficial do PHPMailer 6.x.
 *
 * NOTA: Esta é uma versão standalone (sem Composer).
 * Para produção com requisitos avançados, considere instalar
 * o PHPMailer oficial via download do repositório oficial.
 *
 * @autor    PHPMailer Team (adaptado para Trivya RH)
 * @versao   6.9.1 (standalone)
 * @data     2025-01-01
 * @license  LGPL 2.1
 */

namespace PHPMailer\PHPMailer;

/**
 * PHPMailer exception handler.
 */
class Exception extends \Exception
{
    /**
     * Retorna a mensagem de erro como string.
     */
    public function errorMessage(): string
    {
        return '<strong>' . htmlspecialchars($this->getMessage(), ENT_QUOTES, 'UTF-8') . "</strong><br />\n";
    }
}
