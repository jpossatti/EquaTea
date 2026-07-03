<?php
function iniciarSessao($usuario) {
    session_start();
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['tipo_perfil'] = $usuario['tipo_perfil'];
    $_SESSION['email'] = $usuario['email'];
    $_SESSION['login_time'] = time();
    
    // Se for aluno, buscar o ID do aluno
    if ($usuario['tipo_perfil'] == 'aluno') {
        require_once MODELS_PATH . '/Aluno.php';
        $aluno = new Aluno();
        $dados = $aluno->getDadosCompletos($usuario['id']);
        if ($dados) {
            $_SESSION['aluno_id'] = $dados['aluno_id'];
            $_SESSION['nivel_tea'] = $dados['nivel_tea'];
        }
    }
}

function destruirSessao() {
    session_start();
    session_unset();
    session_destroy();
    
    // Remover cookie de sessão
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
}

function estaLogado() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

function verificarSessao() {
    if (!estaLogado()) {
        header('Location: ' . BASE_URL . 'app/views/auth/login.php');
        exit;
    }
    
    // Verificar expiração (1 hora)
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 3600)) {
        destruirSessao();
        header('Location: ' . BASE_URL . 'app/views/auth/login.php?erro=expirado');
        exit;
    }
}

function isProfessor() {
    return isset($_SESSION['tipo_perfil']) && $_SESSION['tipo_perfil'] == 'professor';
}

function isAluno() {
    return isset($_SESSION['tipo_perfil']) && $_SESSION['tipo_perfil'] == 'aluno';
}