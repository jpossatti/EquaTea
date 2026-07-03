<?php
// Ponto de entrada da aplicação
// Redireciona para a página inicial baseado no perfil do usuário

session_start();
require_once 'config/config.php';
require_once 'app/helpers/functions.php';

// Verifica se usuário está logado
if (isset($_SESSION['usuario_id'])) {
    $tipo = $_SESSION['tipo_perfil'];
    if ($tipo == 'aluno') {
        header('Location: app/views/aluno/dashboard.php');
    } else {
        header('Location: app/views/professor/dashboard.php');
    }
    exit;
}

// Se não estiver logado, redireciona para login
header('Location: app/views/auth/login.php');
exit;