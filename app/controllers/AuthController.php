<?php
/**
 * AuthController.php
 * Controlador de autenticação do sistema.
 */

// Verifica se a classe já foi declarada
if (!class_exists('AuthController')) {

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
            // Carrega o modelo Usuario apenas se não foi carregado
            if (!class_exists('Usuario')) {
                $usuarioPath = __DIR__ . '/../models/Usuario.php';
                if (file_exists($usuarioPath)) {
                    require_once $usuarioPath;
                }
            }
            
            if (class_exists('Usuario')) {
                $this->usuario = new Usuario();
            }
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
                __DIR__ . '/../views/auth/login.php',
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
                echo "<p>Caminhos procurados:</p><ul>";
                foreach ($view_paths as $path) {
                    echo "<li>" . htmlspecialchars($path) . " - " . (file_exists($path) ? '✅' : '❌') . "</li>";
                }
                echo "</ul>";
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
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['login_error'] = 'Preencha todos os campos.';
                header('Location: ' . BASE_URL . 'login');
                exit;
            }
            
            // Verifica se o método login existe
            if (!method_exists($this->usuario, 'login')) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['login_error'] = 'Erro no sistema de autenticação.';
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
                    $alunoPath = __DIR__ . '/../models/Aluno.php';
                    if (file_exists($alunoPath) && !class_exists('Aluno')) {
                        require_once $alunoPath;
                    }
                    if (class_exists('Aluno')) {
                        try {
                            $alunoModel = new Aluno();
                            $dados_aluno = $alunoModel->getByUsuarioId($dados['id']);
                            if ($dados_aluno) {
                                $_SESSION['aluno_id'] = $dados_aluno['id'];
                                $_SESSION['nivel_tea'] = $dados_aluno['nivel_tea'] ?? null;
                            }
                        } catch (Exception $e) {
                            // Silencia erro
                        }
                    }
                }
                
                // Atualiza último acesso
                if (method_exists($this->usuario, 'atualizarUltimoAcesso')) {
                    try {
                        $this->usuario->atualizarUltimoAcesso($dados['id']);
                    } catch (Exception $e) {
                        // Silencia erro
                    }
                }
                
                $this->redirectToDashboard();
                exit;
            }
            
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
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
            
            $target = '';
            switch ($perfil) {
                case 'admin':
                    $target = 'admin/dashboard';
                    break;
                case 'professor':
                    $target = 'professor/dashboard';
                    break;
                case 'aluno':
                default:
                    $target = 'aluno/dashboard';
                    break;
            }
            
            header('Location: ' . BASE_URL . $target);
            exit;
        }
    }
}