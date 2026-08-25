<?php
/**
 * AdminController.php
 * Controlador para gerenciamento administrativo
 */

if (!class_exists('AdminController')) {

    if (!defined('BASE_URL')) {
        define('BASE_URL', 'index.php?view=');
    }

    if (!defined('VIEWS_PATH')) {
        define('VIEWS_PATH', __DIR__ . '/../views');
    }

    class AdminController
    {
        public function __construct()
        {
            // Verifica se é admin
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_perfil'] !== 'admin') {
                $_SESSION['login_error'] = 'Acesso restrito a administradores.';
                header('Location: index.php?view=login');
                exit;
            }
        }
        
        public function dashboard()
        {
            $viewPath = VIEWS_PATH . '/admin/dashboard.php';
            if (file_exists($viewPath)) {
                include $viewPath;
            } else {
                echo "<h1>Dashboard Admin</h1>";
                echo "<p>Bem-vindo, " . htmlspecialchars($_SESSION['usuario_nome'] ?? 'Admin') . "!</p>";
                echo "<p><a href='index.php?view=admin/gerenciar'>Gerenciar Usuários</a></p>";
                echo "<p><a href='index.php?view=admin/equacoes'>Gerenciar Equações</a></p>";
                echo "<p><a href='index.php?view=logout'>Sair</a></p>";
            }
        }
        
        public function gerenciarUsuarios()
        {
            // Carrega o modelo de usuário
            require_once __DIR__ . '/../models/Usuario.php';
            $usuarioModel = new Usuario();
            
            // Busca todos os usuários
            $usuarios = [];
            try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->query("
                    SELECT u.*, 
                           a.idade, a.nivel_tea, a.escola as escola_aluno, a.turma,
                           p.disciplina, p.escola as escola_professor, p.telefone
                    FROM usuarios u
                    LEFT JOIN alunos a ON u.id = a.usuario_id
                    LEFT JOIN professores p ON u.id = p.usuario_id
                    ORDER BY u.id DESC
                ");
                $usuarios = $stmt->fetchAll();
            } catch (Exception $e) {
                // Erro silencioso
            }
            
            $viewPath = VIEWS_PATH . '/admin/gerenciar_usuarios.php';
            if (file_exists($viewPath)) {
                include $viewPath;
            } else {
                echo "<h1>Gerenciar Usuários</h1>";
                echo "<p>Funcionalidade em desenvolvimento...</p>";
                echo "<p><a href='index.php?view=admin/dashboard'>Voltar</a></p>";
            }
        }
        
        public function gerenciarEquacoes()
        {
            // Busca todas as equações
            $equacoes = [];
            try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->query("SELECT * FROM equacoes ORDER BY dificuldade, id");
                $equacoes = $stmt->fetchAll();
            } catch (Exception $e) {
                // Erro silencioso
            }
            
            $viewPath = VIEWS_PATH . '/admin/gerenciar_equacoes.php';
            if (file_exists($viewPath)) {
                include $viewPath;
            } else {
                echo "<h1>Gerenciar Equações</h1>";
                echo "<p>Funcionalidade em desenvolvimento...</p>";
                echo "<p><a href='index.php?view=admin/dashboard'>Voltar</a></p>";
            }
        }
        
        public function criarUsuario()
        {
            // Implementação para criar usuário
            header('Location: index.php?view=admin/gerenciar');
            exit;
        }
        
        public function editarUsuario()
        {
            // Implementação para editar usuário
            header('Location: index.php?view=admin/gerenciar');
            exit;
        }
        
        public function excluirUsuario()
        {
            // Implementação para excluir usuário
            header('Location: index.php?view=admin/gerenciar');
            exit;
        }
        
        public function criarEquacao()
        {
            // Implementação para criar equação
            header('Location: index.php?view=admin/equacoes');
            exit;
        }
        
        public function excluirEquacao()
        {
            // Implementação para excluir equação
            header('Location: index.php?view=admin/equacoes');
            exit;
        }
    }
}