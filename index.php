<?php
/**
 * index.php - Roteador Central
 */
require_once __DIR__ . '/app/config/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Carrega a conexão com o banco de dados
$caminhoDb = __DIR__ . '/app/config/Database.php';
if (file_exists($caminhoDb)) {
    require_once $caminhoDb;
}

// 1. PROCESSAMENTO DAS AÇÕES (POST/GET)
$action = $_GET['action'] ?? $_POST['action'] ?? null;

if ($action === 'salvar_edicao') {
    require_once __DIR__ . '/app/controllers/ProfessorController.php';
    $controller = new ProfessorController();
    $controller->atualizar();
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

// 2. ROTEAMENTO DE VIEWS
$view = $_GET['view'] ?? $_POST['view'] ?? 'login';

// Define a variável global para o menu
$GLOBALS['current_view'] = $view;

switch ($view) {
    case 'login':
        require_once __DIR__ . '/app/controllers/AuthController.php';
        $auth = new AuthController();
        $auth->showLogin();
        break;

    case 'fazer_login':
        require_once __DIR__ . '/app/models/Usuario.php';
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
        verificarAutenticacao();
        $caminhoView = __DIR__ . '/app/views/aluno/dashboard.php';
        file_exists($caminhoView) ? require_once $caminhoView : print("View não encontrada.");
        break;

    case 'editar_equacao':
        require_once __DIR__ . '/app/controllers/ProfessorController.php';
        $controller = new ProfessorController();
        $controller->exibirFormularioEdicaoEquacao($_GET['id'] ?? null);
        break;

    case 'editar_aluno':
        require_once __DIR__ . '/app/controllers/ProfessorController.php';
        $controller = new ProfessorController();
        $controller->exibirFormularioEdicao($_GET['id'] ?? null);
        break;

    case 'exercicio':
        $caminhoView = __DIR__ . '/app/views/aluno/exercicio.php';
        file_exists($caminhoView) ? require_once $caminhoView : print("View não encontrada.");
        break;

    case 'parabens':
        $caminhoView = __DIR__ . '/app/views/aluno/parabens.php';
        file_exists($caminhoView) ? require_once $caminhoView : print("View não encontrada.");
        break;

    case 'gerenciar_alunos':
        require_once __DIR__ . '/app/controllers/ProfessorController.php';
        $controller = new ProfessorController();
        $controller->gerenciarAlunos();
        break;

    case 'gerenciar_equacoes':
        require_once __DIR__ . '/app/controllers/ProfessorController.php';
        $controller = new ProfessorController();
        $controller->listarEquacoes();
        break;

    case 'relatorio':
        $caminhoView = __DIR__ . '/app/views/professor/relatorio.php';
        file_exists($caminhoView) ? require_once $caminhoView : print("View não encontrada.");
        break;

    case 'professor/dashboard':
    case 'dashboard_professor':
    case 'professor':
        require_once __DIR__ . '/app/models/Professor.php';
        require_once __DIR__ . '/app/controllers/ProfessorController.php';
        $controller = new ProfessorController();
        $controller->dashboard();
        break;

    default:
        header("Location: index.php?view=login");
        exit;
}