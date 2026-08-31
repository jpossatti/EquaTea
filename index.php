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
    // ===== AUTH =====
    'login' => ['controller' => 'AuthController', 'method' => 'showLogin'],
    'fazer_login' => ['controller' => 'AuthController', 'method' => 'login'],
    'logout' => ['controller' => 'AuthController', 'method' => 'logout'],
    
    // ===== ALUNO =====
    // Dashboard
    'aluno/dashboard' => ['controller' => 'AlunoController', 'method' => 'dashboard'],
    'aluno' => ['controller' => 'AlunoController', 'method' => 'dashboard'],
    
    // Exercício
    'aluno/exercicio' => ['controller' => 'AlunoController', 'method' => 'exercicio'],
    'exercicio' => ['controller' => 'AlunoController', 'method' => 'exercicio'],
    
    // Parabéns
    'aluno/parabens' => ['controller' => 'AlunoController', 'method' => 'parabens'],
    'parabens' => ['controller' => 'AlunoController', 'method' => 'parabens'],
    
    // Verificar Resposta (POST)
    'verificar_resposta' => ['controller' => 'AlunoController', 'method' => 'verificarResposta'],
    
    // ===== PROFESSOR =====
    // Dashboard
    'professor/dashboard' => ['controller' => 'ProfessorController', 'method' => 'dashboard'],
    
    // Gerenciar Alunos
    'gerenciar_alunos' => ['controller' => 'ProfessorController', 'method' => 'gerenciarAlunos'],
    'professor/editar_aluno' => ['controller' => 'ProfessorController', 'method' => 'editarAluno'],
    'editar_aluno' => ['controller' => 'ProfessorController', 'method' => 'editarAluno'],
    'salvar_edicao' => ['controller' => 'ProfessorController', 'method' => 'salvarEdicao'],
    'deletar_aluno' => ['controller' => 'ProfessorController', 'method' => 'deletarAluno'],
    'resetar_senha' => ['controller' => 'ProfessorController', 'method' => 'resetarSenha'],
    'cadastrar_aluno' => ['controller' => 'ProfessorController', 'method' => 'cadastrarAluno'],
    // Adicione no array $routes do index.php:

// ===== DEBUG =====
'debug_editar_aluno' => ['controller' => 'ProfessorController', 'method' => 'debugEditarAluno'],
    
    // Gerenciar Equações
    'gerenciar_equacoes' => ['controller' => 'ProfessorController', 'method' => 'gerenciarEquacoes'],
    'professor/editar_equacao' => ['controller' => 'ProfessorController', 'method' => 'editarEquacao'],
    'editar_equacao' => ['controller' => 'ProfessorController', 'method' => 'editarEquacao'],
    'salvar_edicao_equacao' => ['controller' => 'ProfessorController', 'method' => 'salvarEdicaoEquacao'],
    'deletar_equacao' => ['controller' => 'ProfessorController', 'method' => 'deletarEquacao'],
    'cadastrar_equacao' => ['controller' => 'ProfessorController', 'method' => 'cadastrarEquacao'],
    
    // Relatórios
    'relatorio' => ['controller' => 'ProfessorController', 'method' => 'relatorio'],
    
    // ===== ADMIN =====
    // Dashboard
    'admin/dashboard' => ['controller' => 'AdminController', 'method' => 'dashboard'],
    
    // Gerenciar Usuários
    'admin/gerenciar' => ['controller' => 'AdminController', 'method' => 'gerenciarUsuarios'],
    'admin/criar_usuario' => ['controller' => 'AdminController', 'method' => 'criarUsuario'],
    'admin/editar_usuario' => ['controller' => 'AdminController', 'method' => 'editarUsuario'],
    'admin/editar_usuario_salvar' => ['controller' => 'AdminController', 'method' => 'editarUsuarioSalvar'],
    'admin/excluir_usuario' => ['controller' => 'AdminController', 'method' => 'excluirUsuario'],
    
    // Gerenciar Equações (Admin)
    'admin/equacoes' => ['controller' => 'AdminController', 'method' => 'gerenciarEquacoes'],
    'admin/criar_equacao' => ['controller' => 'AdminController', 'method' => 'criarEquacao'],
    'admin/editar_equacao' => ['controller' => 'AdminController', 'method' => 'editarEquacao'],
    'admin/editar_equacao_salvar' => ['controller' => 'AdminController', 'method' => 'editarEquacaoSalvar'],
    'admin/excluir_equacao' => ['controller' => 'AdminController', 'method' => 'excluirEquacao'],
];

// Verifica se a view é uma ação POST (sem view na URL)
$action = $_GET['action'] ?? null;
if ($action && isset($routes[$action])) {
    $route = $routes[$action];
} elseif (isset($routes[$view])) {
    $route = $routes[$view];
} else {
    $route = null;
}

// Processa a rota
if ($route) {
    $controllerFile = BASE_PATH . '/app/controllers/' . $route['controller'] . '.php';
    
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        
        if (class_exists($route['controller'])) {
            $controller = new $route['controller']();
            
            if (method_exists($controller, $route['method'])) {
                $controller->{$route['method']}();
            } else {
                die("Erro: Método {$route['method']} não encontrado no controller " . $route['controller']);
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