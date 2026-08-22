<?php
// app/config/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica se o usuário está autenticado
 */
function estaAutenticado() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Verifica se o usuário é aluno
 */
function isAluno() {
    return isset($_SESSION['tipo_perfil']) && $_SESSION['tipo_perfil'] === 'aluno';
}

/**
 * Verifica se o usuário é professor
 */
function isProfessor() {
    return isset($_SESSION['tipo_perfil']) && $_SESSION['tipo_perfil'] === 'professor';
}

/**
 * Verifica se a sessão expirou (30 minutos)
 */
function sessaoExpirada() {
    if (!isset($_SESSION['login_time'])) {
        return true;
    }
    return (time() - $_SESSION['login_time'] > 1800);
}

/**
 * Verifica autenticação e redireciona se não estiver logado
 */
function verificarAutenticacao($tipoNecessario = null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Verifica se está logado
    if (!isset($_SESSION['usuario_id'])) {
        $_SESSION['login_error'] = 'Você precisa estar logado para acessar esta página.';
        header('Location: index.php?view=login');
        exit;
    }
    
    // Verifica expiração da sessão
    if (sessaoExpirada()) {
        session_destroy();
        $_SESSION['login_error'] = 'Sua sessão expirou. Faça login novamente.';
        header('Location: index.php?view=login');
        exit;
    }
    
    // Atualiza o tempo da sessão
    $_SESSION['login_time'] = time();
    
    // Verifica o tipo de usuário se necessário
    if ($tipoNecessario) {
        $tipoAtual = $_SESSION['tipo_perfil'] ?? '';
        
        if ($tipoNecessario === 'aluno' && $tipoAtual !== 'aluno') {
            $_SESSION['login_error'] = 'Acesso restrito a alunos.';
            header('Location: index.php?view=login');
            exit;
        }
        
        if ($tipoNecessario === 'professor' && $tipoAtual !== 'professor') {
            $_SESSION['login_error'] = 'Acesso restrito a professores.';
            header('Location: index.php?view=login');
            exit;
        }
    }
}