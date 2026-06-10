<?php

/**
 * Constantes globais da aplicação Trivya RH
 *
 * Define as constantes derivadas das variáveis de ambiente e valores
 * fixos do sistema. Deve ser carregado após config/env.php.
 *
 * @autor    Equipe Trivya RH
 * @versao   1.0.0
 * @data     2025-01-01
 */

declare(strict_types=1);

// Garantir que env.php foi carregado antes
if (!function_exists('env')) {
    require_once __DIR__ . '/env.php';
}

// ----------------------------------------------------------
// Identidade do site
// ----------------------------------------------------------
define('SITE_NAME',         'Trivya RH');
define('SITE_SLOGAN',       'Conectando talentos às melhores oportunidades');
define('SITE_DESC',         'Consultoria de recrutamento e seleção em São Paulo. Soluções personalizadas para empresas e profissionais.');
define('SITE_KEYWORDS',     'recrutamento, seleção, RH, recursos humanos, São Paulo, vagas, emprego, consultoria');
define('SITE_AUTHOR',       'Trivya RH');
define('SITE_EMAIL',        env('SMTP_FROM_EMAIL', 'contato@trivyarh.com.br'));
define('SITE_WHATSAPP',     '5511999999999'); // Formato internacional sem +

// ----------------------------------------------------------
// URLs e caminhos
// ----------------------------------------------------------
define('SITE_URL',          rtrim((string) env('APP_URL', 'http://localhost/trivya-rh'), '/'));
define('ASSETS_URL',        SITE_URL . '/assets');
define('ADMIN_URL',         SITE_URL . '/admin');
define('API_URL',           SITE_URL . '/api');

// CONFIG_PATH, INCLUDES_PATH, LIB_PATH, LOGS_PATH e PUBLIC_PATH já são
// definidos pelo bootstrap.php. assets/ e uploads/ ficam dentro de public_html/.
define('ASSETS_PATH',       PUBLIC_PATH . '/assets');
define('UPLOAD_PATH',       PUBLIC_PATH . '/uploads/curriculos');

// ----------------------------------------------------------
// Ambiente
// ----------------------------------------------------------
define('APP_ENV',           env('APP_ENV', 'production'));
define('APP_DEBUG',         filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN));
define('IS_PRODUCTION',     APP_ENV === 'production');
define('IS_DEVELOPMENT',    APP_ENV === 'development');

// ----------------------------------------------------------
// Banco de dados
// ----------------------------------------------------------
define('DB_HOST',           env('DB_HOST', 'localhost'));
define('DB_NAME',           env('DB_NAME', 'trivya_rh'));
define('DB_USER',           env('DB_USER', 'root'));
define('DB_PASS',           env('DB_PASS', ''));
define('DB_CHARSET',        env('DB_CHARSET', 'utf8mb4'));

// ----------------------------------------------------------
// SMTP
// ----------------------------------------------------------
define('SMTP_HOST',         env('SMTP_HOST', ''));
define('SMTP_PORT',         (int) env('SMTP_PORT', 587));
define('SMTP_USER',         env('SMTP_USER', ''));
define('SMTP_PASS',         env('SMTP_PASS', ''));
define('SMTP_FROM_NAME',    env('SMTP_FROM_NAME', SITE_NAME));
define('SMTP_FROM_EMAIL',   env('SMTP_FROM_EMAIL', ''));

// ----------------------------------------------------------
// Integrações externas
// ----------------------------------------------------------
define('RECAPTCHA_SITE_KEY',    env('RECAPTCHA_SITE_KEY', ''));
define('RECAPTCHA_SECRET_KEY',  env('RECAPTCHA_SECRET_KEY', ''));
define('GA4_MEASUREMENT_ID',    env('GA4_MEASUREMENT_ID', ''));

// ----------------------------------------------------------
// Configurações de upload
// ----------------------------------------------------------
define('UPLOAD_MAX_SIZE',       5 * 1024 * 1024); // 5 MB em bytes
define('UPLOAD_ALLOWED_TYPES',  ['application/pdf', 'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
define('UPLOAD_ALLOWED_EXT',    ['pdf', 'doc', 'docx']);

// Ativar exibição de erros apenas em desenvolvimento
if (APP_DEBUG && IS_DEVELOPMENT) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// Timezone padrão do Brasil (São Paulo)
date_default_timezone_set('America/Sao_Paulo');
