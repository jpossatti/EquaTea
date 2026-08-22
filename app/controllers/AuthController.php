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
    
    public function __construct()
    {
        require_once __DIR__ . '/../models/Usuario.php';
        $this->usuario = new Usuario();
    }
    
 /**
 * Exibe a página de login
 */
public function showLogin()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['usuario_id'])) {
        $this->redirectToDashboard();
        return;
    }
    
    // Tenta carregar de diferentes locais
    $view_paths = [
        VIEWS_PATH . '/auth/login.php',
        VIEWS_PATH . '/login.php',
        __DIR__ . '/../views/login.php'
    ];
    
    $loaded = false;
    foreach ($view_paths as $path) {
        if (file_exists($path)) {
            include_once $path;
            $loaded = true;
            break;
        }
    }
    
    if (!$loaded) {
        echo "<h2>Erro: Página de login não encontrada.</h2>";
    }
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
        
        if (empty($email) || empty($senha)) {
            $_SESSION['login_error'] = 'Preencha todos os campos.';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
        
        $dados = $this->usuario->login($email, $senha);
        
        if ($dados) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            $_SESSION['usuario_id']   = $dados['id'];
            $_SESSION['usuario_nome'] = $dados['nome'];
            $_SESSION['email']        = $dados['email'];
            $_SESSION['tipo_perfil']  = $dados['tipo_perfil'] ?? 'aluno';
            $_SESSION['login_time']   = time();
            
            // Busca dados específicos do aluno se for aluno
            if ($_SESSION['tipo_perfil'] === 'aluno') {
                require_once __DIR__ . '/../models/Aluno.php';
                $alunoModel = new Aluno();
                $dados_aluno = $alunoModel->getByUsuarioId($dados['id']);
                if ($dados_aluno) {
                    $_SESSION['aluno_id'] = $dados_aluno['id'];
                    $_SESSION['nivel_tea'] = $dados_aluno['nivel_tea'] ?? null;
                }
            }
            
            // Atualiza último acesso
            if (method_exists($this->usuario, 'atualizarUltimoAcesso')) {
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
    
    // Limpa todas as variáveis de sessão
    $_SESSION = array();
    
    // Se houver cookie de sessão, remove
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), 
            '', 
            time() - 42000,
            $params["path"], 
            $params["domain"],
            $params["secure"], 
            $params["httponly"]
        );
    }
    
    // Destroi a sessão
    session_destroy();
    
    // Redireciona para o login com mensagem
    header('Location: index.php?view=login&msg=logout');
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