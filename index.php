<?php
/**
 * index.php - Roteador Central
 */
require_once __DIR__ . '/app/config/auth.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Carrega a conexão com o banco de dados e os models essenciais globalmente
$caminhoDb = __DIR__ . '/app/config/Database.php';
if (file_exists($caminhoDb)) {
    require_once $caminhoDb;
}

// 1. PROCESSAMENTO DAS AÇÕES (POST/GET)
$action = $_GET['action'] ?? $_POST['action'] ?? null;

// Adição da nova ação de salvar edição
if ($action === 'salvar_edicao') {
    require_once __DIR__ . '/app/controllers/ProfessorController.php';
    $controller = new ProfessorController();
    $controller->atualizar(); // Certifique-se que o método 'atualizar' exista no seu ProfessorController
    exit;
}

if ($action === 'cadastrar_aluno') {
    require_once __DIR__ . '/app/controllers/ProfessorController.php';
    $controller = new ProfessorController();
    $controller->cadastrarAluno();
    exit;
}

if ($action === 'resetar_senha') {
    require_once __DIR__ . '/app/controllers/ProfessorController.php';
    $controller = new ProfessorController();
    $controller->resetarSenha();
    exit;
}
if ($action === 'deletar_aluno') {
    require_once __DIR__ . '/app/controllers/ProfessorController.php';
    $controller = new ProfessorController();
    $controller->deletarAluno();
    exit;
}
if ($action === 'cadastrar_equacao') {
    require_once __DIR__ . '/app/controllers/ProfessorController.php';
    $controller = new ProfessorController();
    $controller->cadastrarEquacao();
    exit;
}
if ($action === 'deletar_equacao') {
    require_once __DIR__ . '/app/controllers/ProfessorController.php';
    $controller = new ProfessorController();
    $controller->deletarEquacao($_GET['id']);
    exit;
}
if ($action === 'salvar_edicao_equacao') {
    require_once __DIR__ . '/app/controllers/ProfessorController.php';
    $controller = new ProfessorController();
    $controller->atualizarEquacao();
    exit;
}
// Restante do código de roteamento de views...
// 2. ROTEAMENTO DE VIEWS
$view = $_GET['view'] ?? $_POST['view'] ?? 'login';

switch ($view) {

case 'login':
        require_once __DIR__ . '/app/controllers/AuthController.php'; // <-- Adicione aqui
        $auth = new AuthController();
        $auth->showLogin();
        break;

        case 'dashboard_professor':
    case 'gerenciar_alunos':

    case 'gerenciar_equacoes':

        verificarAutenticacao('professor'); // Bloqueia quem não é professor
        require_once __DIR__ . '/app/controllers/ProfessorController.php';
        break;


 case 'fazer_login':
        require_once __DIR__ . '/app/models/Usuario.php';   // <-- Inclua o Model primeiro
        require_once __DIR__ . '/app/controllers/AuthController.php';
        $auth = new AuthController();
        $auth->login();
        break;

    case 'logout':
        require_once __DIR__ . '/app/controllers/AuthController.php';
        $auth = new AuthController();
        $auth->logout();
        break;
    case 'dashboard':
        verificarAutenticacao(); // Bloqueia qualquer um não logado
        // ... carregar view de aluno
        break;

    case 'editar_equacao':
        require_once __DIR__ . '/app/controllers/ProfessorController.php';
        $controller = new ProfessorController();
        $controller->exibirFormularioEdicaoEquacao($_GET['id']);
    break;

    case 'editar_aluno':
        require_once __DIR__ . '/app/controllers/ProfessorController.php';
        $controller = new ProfessorController();
        // Verifica se o ID foi passado antes de chamar o método
        $id = $_GET['id'] ?? null;
        $controller->exibirFormularioEdicao($id);
        break;
    
    case 'gerenciar_equacoes':
        require_once __DIR__ . '/app/controllers/ProfessorController.php';
        $controller = new ProfessorController();
        $controller->listarEquacoes(); // Criaremos este método abaixo
        break;

    case 'dashboard':
        $caminhoView = __DIR__ . '/app/views/aluno/dashboard.php';
        file_exists($caminhoView) ? require_once $caminhoView : print("View não encontrada.");
        break;

    case 'exercicio':
        $caminhoView = __DIR__ . '/app/views/aluno/exercicio.php';
        file_exists($caminhoView) ? require_once $caminhoView : print("View não encontrada.");
        break;

    case 'parabens':
        $caminhoView = __DIR__ . '/app/views/aluno/parabens.php';
        file_exists($caminhoView) ? require_once $caminhoView : print("View não encontrada.");
        break;

    case 'dashboard_professor':
    case 'professor':
        $caminhoView = __DIR__ . '/app/views/professor/dashboard.php';
        file_exists($caminhoView) ? require_once $caminhoView : print("View não encontrada.");
        break;

    case 'gerenciar_alunos':
        require_once __DIR__ . '/app/controllers/ProfessorController.php';
        $controller = new ProfessorController();
        $controller->gerenciarAlunos();
        break;

    case 'gerenciar_equacoes':
        $caminhoView = __DIR__ . '/app/views/professor/gerenciar_equacoes.php';
        file_exists($caminhoView) ? require_once $caminhoView : print("View não encontrada.");
        break;

    case 'relatorio':
        $caminhoView = __DIR__ . '/app/views/professor/relatorio.php';
        file_exists($caminhoView) ? require_once $caminhoView : print("View não encontrada.");
        break;

    case 'editar_aluno':
        require_once __DIR__ . '/app/controllers/ProfessorController.php';
        $controller = new ProfessorController();
        $controller->exibirFormularioEdicao($_GET['id']);
        break;
case 'professor/dashboard':
        // Certifique-se de incluir o Model Professor e o Controller correspondente
        require_once __DIR__ . '/app/models/Professor.php';
        require_once __DIR__ . '/app/controllers/ProfessorController.php'; // ou o nome do seu controller
        $controller = new ProfessorController();
        $controller->dashboard();
        break;
    default:
        header("Location: index.php?view=login");
        exit;
}