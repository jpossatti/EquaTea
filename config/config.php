<?php
// Configurações gerais da aplicação

// Configurações de ambiente
define('ENVIRONMENT', 'development'); // development | production

// Configurações de sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.gc_maxlifetime', 3600); // 1 hora

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de debug
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Caminhos da aplicação
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('VIEWS_PATH', APP_PATH . '/views');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('CONTROLLERS_PATH', APP_PATH . '/controllers');
define('MODELS_PATH', APP_PATH . '/models');
define('HELPERS_PATH', APP_PATH . '/helpers');
define('SERVICES_PATH', APP_PATH . '/services');

// URL base
define('BASE_URL', 'http://localhost/equatea/');