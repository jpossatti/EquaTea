<?php
require_once CONTROLLERS_PATH . '/../models/Usuario.php';
require_once CONTROLLERS_PATH . '/../helpers/session_helper.php';

class AuthController {
    private $usuario;
    
    public function __construct() {
        $this->usuario = new Usuario();
    }
    
    public function login($email, $senha) {
        $dados = $this->usuario->login($email, $senha);
        
        if ($dados) {
            iniciarSessao($dados);
            $this->usuario->atualizarUltimoAcesso($dados['id']);
            
            // Redirecionar baseado no perfil
            if ($dados['tipo_perfil'] == 'aluno') {
                header('Location: ../views/aluno/dashboard.php');
            } else {
                header('Location: ../views/professor/dashboard.php');
            }
            exit;
        }
        
        return false;
    }
    
    public function logout() {
        destruirSessao();
        header('Location: ../views/auth/login.php');
        exit;
    }
    
    public function verificarAutenticacao() {
        if (!estaLogado()) {
            header('Location: ../views/auth/login.php');
            exit;
        }
        return true;
    }
    
    public function verificarPerfil($perfil) {
        if ($_SESSION['tipo_perfil'] != $perfil) {
            header('Location: ../views/auth/login.php');
            exit;
        }
        return true;
    }
}