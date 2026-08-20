<?php
/**
 * AuthController.php
 * Controlador de autenticação do sistema.
 */

if (!defined('BASE_URL')) {
    define('BASE_URL', 'index.php?view=');
}

if (!defined('VIEWS_PATH')) {
    define('VIEWS_PATH', __DIR__ . '/../views');
}

class AuthController
{
    private $usuario;
    private $aluno;
    private $professor;
    
    public function __construct()
    {
        $this->usuario   = class_exists('Usuario') ? new Usuario() : null;
        $this->aluno     = class_exists('Aluno') ? new Aluno() : null;
        $this->professor = class_exists('Professor') ? new Professor() : null;
    }
    
    /**
     * Exibe a página de login
     */
    public function showLogin()
    {
        if (isset($_SESSION['usuario_id'])) {
            $this->redirectToDashboard();
            return;
        }
        
        $view_path = VIEWS_PATH . '/auth/login.php';
        if (file_exists($view_path)) {
            include_once $view_path;
        } else {
            include_once __DIR__ . '/../views/auth/login.php';
        }
    }
    
    /**
     * Processa o login (Com Debug)
     */
   /**
     * Processa o login
     */
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        
        $email = $_POST['email'] ?? '';
        $senha = $_POST['senha'] ?? '';
        
        // Validação CSRF (Opcional caso a função helper exista)
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (function_exists('validarTokenCSRF') && !empty($csrf_token)) {
            if (!validarTokenCSRF($csrf_token)) {
                $_SESSION['login_error'] = 'Erro de segurança. Tente novamente.';
                header('Location: ' . BASE_URL . 'login');
                exit;
            }
        }
        
        $dados = $this->usuario ? $this->usuario->login($email, $senha) : false;
        
        if ($dados) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            $_SESSION['usuario_id']   = $dados['id'];
            $_SESSION['usuario_nome'] = $dados['nome'];
            $_SESSION['email']        = $dados['email'];
            $_SESSION['tipo_perfil']  = $dados['tipo_perfil'] ?? 'aluno';
            $_SESSION['login_time']   = time();
            
            // Busca dados específicos de acordo com o perfil
            if ($_SESSION['tipo_perfil'] === 'aluno' && $this->aluno) {
                $dados_aluno = method_exists($this->aluno, 'getDadosCompletos') ? $this->aluno->getDadosCompletos($dados['id']) : null;
                if ($dados_aluno) {
                    $_SESSION['aluno_id']  = $dados_aluno['aluno_id'] ?? null;
                    $_SESSION['nivel_tea'] = $dados_aluno['nivel_tea'] ?? null;
                }
            } else if ($this->professor && method_exists($this->professor, 'getByUsuarioId')) {
                $dados_professor = $this->professor->getByUsuarioId($dados['id']);
                if ($dados_professor) {
                    $_SESSION['professor_id'] = $dados_professor['id'] ?? null;
                }
            }
            
            if ($this->usuario && method_exists($this->usuario, 'atualizarUltimoAcesso')) {
                $this->usuario->atualizarUltimoAcesso($dados['id']);
            }
            
            $this->redirectToDashboard();
        }
        
        $_SESSION['login_error'] = 'E-mail ou senha inválidos.';
        header('Location: ' . BASE_URL . 'login');
        exit;
    }
    
    /**
     * Processa o logout
     */
    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = array();
        session_destroy();
        header('Location: ' . BASE_URL . 'login&msg=logout');
        exit;
    }
    
    /**
     * Redireciona para o dashboard apropriado
     */
    private function redirectToDashboard()
    {
        $perfil = $_SESSION['tipo_perfil'] ?? 'aluno';
        
        if ($perfil === 'aluno') {
            header('Location: ' . BASE_URL . 'aluno/dashboard');
        } else {
            header('Location: ' . BASE_URL . 'professor/dashboard');
        }
        exit;
    }
}