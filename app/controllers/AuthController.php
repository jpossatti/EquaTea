<?php
/**
 * AuthController.php
 * Controlador de autenticação do sistema.
 */
class AuthController
{
    private $usuario;
    private $aluno;
    private $professor;
    
    public function __construct()
    {
        $this->usuario = new Usuario();
        $this->aluno = new Aluno();
        $this->professor = new Professor();
    }
    
    /**
     * Exibe a página de login
     */
    public function showLogin()
    {
        // Se já está logado, redireciona
        if (isset($_SESSION['usuario_id'])) {
            $this->redirectToDashboard();
        }
        
        include_once VIEWS_PATH . '/auth/login.php';
    }
    
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
        
        // Validação CSRF
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!validarTokenCSRF($csrf_token)) {
            $_SESSION['login_error'] = 'Erro de segurança. Tente novamente.';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        
        $dados = $this->usuario->login($email, $senha);
        
        if ($dados) {
            // Inicia sessão
            $_SESSION['usuario_id'] = $dados['id'];
            $_SESSION['usuario_nome'] = $dados['nome'];
            $_SESSION['email'] = $dados['email'];
            $_SESSION['tipo_perfil'] = $dados['tipo_perfil'];
            $_SESSION['login_time'] = time();
            
            // Busca dados específicos
            if ($dados['tipo_perfil'] === 'aluno') {
                $dados_aluno = $this->aluno->getDadosCompletos($dados['id']);
                if ($dados_aluno) {
                    $_SESSION['aluno_id'] = $dados_aluno['aluno_id'];
                    $_SESSION['nivel_tea'] = $dados_aluno['nivel_tea'];
                }
            } else {
                $dados_professor = $this->professor->getByUsuarioId($dados['id']);
                if ($dados_professor) {
                    $_SESSION['professor_id'] = $dados_professor['id'];
                }
            }
            
            $this->usuario->atualizarUltimoAcesso($dados['id']);
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
        session_destroy();
        $_SESSION = array();
        header('Location: ' . BASE_URL . 'login?msg=logout');
        exit;
    }
    
    /**
     * Redireciona para o dashboard apropriado
     */
    private function redirectToDashboard()
    {
        if ($_SESSION['tipo_perfil'] === 'aluno') {
            header('Location: ' . BASE_URL . 'aluno/dashboard');
        } else {
            header('Location: ' . BASE_URL . 'professor/dashboard');
        }
        exit;
    }
}