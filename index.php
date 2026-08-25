<?php
/**
 * index.php
 * Ponto de entrada principal do sistema
 */

// Define o caminho base
define('BASE_PATH', __DIR__);

// Carrega configurações
if (file_exists(BASE_PATH . '/app/config/config.php')) {
    require_once BASE_PATH . '/app/config/config.php';
}

// Define constantes
if (!defined('BASE_URL')) {
    define('BASE_URL', 'index.php?view=');
}

if (!defined('VIEWS_PATH')) {
    define('VIEWS_PATH', BASE_PATH . '/app/views');
}

// Carrega a configuração do banco
if (!class_exists('Database')) {
    require_once BASE_PATH . '/app/config/Database.php';
}

// Inicia a sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determina a view
$view = $_GET['view'] ?? 'login';

// Remove parâmetros extras
if (strpos($view, '&') !== false) {
    $view = substr($view, 0, strpos($view, '&'));
}

// Mapeamento de rotas
$routes = [
    // Auth
    'login' => ['controller' => 'AuthController', 'method' => 'showLogin'],
    'fazer_login' => ['controller' => 'AuthController', 'method' => 'login'],
    'logout' => ['controller' => 'AuthController', 'method' => 'logout'],
    
    // Aluno
    'aluno/dashboard' => ['controller' => 'AlunoController', 'method' => 'dashboard'],
    'aluno/exercicio' => ['controller' => 'AlunoController', 'method' => 'exercicio'],
    'aluno/parabens' => ['controller' => 'AlunoController', 'method' => 'parabens'],
    
    // Professor
    'professor/dashboard' => ['controller' => 'ProfessorController', 'method' => 'dashboard'],
    'professor/alunos' => ['controller' => 'ProfessorController', 'method' => 'alunos'],
    'professor/equacoes' => ['controller' => 'ProfessorController', 'method' => 'equacoes'],
    'professor/relatorios' => ['controller' => 'ProfessorController', 'method' => 'relatorios'],
    
    // Admin
    'admin/dashboard' => ['controller' => 'AdminController', 'method' => 'dashboard'],
    'admin/gerenciar' => ['controller' => 'AdminController', 'method' => 'gerenciarUsuarios'],
    'admin/equacoes' => ['controller' => 'AdminController', 'method' => 'gerenciarEquacoes'],
    'admin/criar_usuario' => ['controller' => 'AdminController', 'method' => 'criarUsuario'],
    'admin/editar_usuario' => ['controller' => 'AdminController', 'method' => 'editarUsuario'],
    'admin/excluir_usuario' => ['controller' => 'AdminController', 'method' => 'excluirUsuario'],
    'admin/criar_equacao' => ['controller' => 'AdminController', 'method' => 'criarEquacao'],
    'admin/excluir_equacao' => ['controller' => 'AdminController', 'method' => 'excluirEquacao'],
];

// Processa a rota
if (isset($routes[$view])) {
    $route = $routes[$view];
    $controllerFile = BASE_PATH . '/app/controllers/' . $route['controller'] . '.php';
    
    if (file_exists($controllerFile)) {
        // Usa require_once para evitar duplicação
        require_once $controllerFile;
        
        if (class_exists($route['controller'])) {
            $controller = new $route['controller']();
            
            if (method_exists($controller, $route['method'])) {
                $controller->{$route['method']}();
            } else {
                die("Erro: Método {$route['method']} não encontrado.");
            }
        } else {
            die("Erro: Classe {$route['controller']} não encontrada.");
        }
    } else {
        die("Erro: Controller não encontrado em " . $controllerFile);
    }
} else {
    // Fallback: tenta carregar a view diretamente
    $viewFile = VIEWS_PATH . '/' . $view . '.php';
    if (file_exists($viewFile)) {
        include $viewFile;
    } else {
        echo "<h2>Erro: Página não encontrada.</h2>";
        echo "<p>View solicitada: " . htmlspecialchars($view) . "</p>";
        echo "<p><a href='index.php?view=login'>Voltar ao login</a></p>";
    }
}