<?php
/**
 * Functions.php
 * Funções auxiliares globais.
 */

/**
 * Sanitiza uma string para HTML
 */
function e($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Gera token CSRF
 */
function gerarTokenCSRF()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida token CSRF
 */
function validarTokenCSRF($token)
{
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Formata data
 */
function formatarData($data, $formato = 'd/m/Y H:i')
{
    if (empty($data)) return '-';
    try {
        $dt = new DateTime($data);
        return $dt->format($formato);
    } catch (Exception $e) {
        return $data;
    }
}

/**
 * Redireciona com mensagem
 */
function redirectComMensagem($url, $mensagem, $tipo = 'success')
{
    $_SESSION['flash_' . $tipo] = $mensagem;
    header('Location: ' . $url);
    exit;
}

/**
 * Obtém mensagem flash
 */
function getFlash($tipo)
{
    $key = 'flash_' . $tipo;
    if (isset($_SESSION[$key])) {
        $mensagem = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $mensagem;
    }
    return null;
}