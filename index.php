<<?php
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

// Ações do Professor
if ($action === 'salvar_edicao') {
    verificarAutenticacao('professor');
    require_once __DIR__ . '/app/controllers/ProfessorController.php';
    $controller = new ProfessorController();
    $controller->atualizar();
    exit;
}

if ($action === 'cadastrar_aluno') {
    verificarAutenticacao('professor');
    require_once __DIR__ . '/app/controllers/ProfessorController.php';
    $controller = new ProfessorController();
    $controller->cadastrarAluno();
    exit;
}

if ($action === 'resetar_senha') {
    verificarAutenticacao('professor');
    require_once __DIR__ . '/app/controllers/ProfessorController.php';
    $controller = new ProfessorController();
    $controller->resetarSenha();
    exit;
}

if ($action === 'deletar_aluno') {
    verificarAutenticacao('professor');
    require_once __DIR__ . '/app/controllers/ProfessorController.php';
    $controller = new ProfessorController();
    $controller->deletarAluno();
    exit;
}

if ($action === 'cadastrar_equacao') {
    verificarAutenticacao('professor');
    require_once __DIR__ . '/app/controllers/ProfessorController.php';
    $controller = new ProfessorController();
    $controller->cadastrarEquacao();
    exit;
}

if ($action === 'deletar_equacao') {
    verificarAutenticacao('professor');
    require_once __DIR__ . '/app/controllers/ProfessorController.php';
    $controller = new ProfessorController();
    $controller->deletarEquacao($_GET['id']);
    exit;
}

if ($action === 'salvar_edicao_equacao') {
    verificarAutenticacao('professor');
    require_once __DIR__ . '/app/controllers/ProfessorController.php';
    $controller = new ProfessorController();
    $controller->atualizarEquacao();
    exit;
}

// Ações do Aluno
if ($action === 'verificar_resposta') {
    verificarAutenticacao('aluno');
    require_once __DIR__ . '/app/controllers/AlunoController.php';
    $controller = new AlunoController();
    $controller->verificarResposta();
    exit;
}

// 2. ROTEAMENTO DE VIEWS
$view = $_GET['view'] ?? $_POST['view'] ?? 'login';

// Define a variável global para o menu
$GLOBALS['current_view'] = $view;

switch ($view) {
    // ===== LOGIN / AUTENTICAÇÃO =====
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

    // ===== ROTAS DO ALUNO =====
    case 'aluno/dashboard':
    case 'dashboard':
        verificarAutenticacao('aluno');
        require_once __DIR__ . '/app/controllers/AlunoController.php';
        $controller = new AlunoController();
        $controller->dashboard();
        break;

    case 'exercicio':
        verificarAutenticacao('aluno');
        require_once __DIR__ . '/app/controllers/AlunoController.php';
        $controller = new AlunoController();
        $controller->exercicio();
        break;

    case 'parabens':
        verificarAutenticacao('aluno');
        require_once __DIR__ . '/app/controllers/AlunoController.php';
        $controller = new AlunoController();
        $controller->parabens();
        break;

    // ===== ROTAS DO PROFESSOR =====
    case 'editar_equacao':
        verificarAutenticacao('professor');
        require_once __DIR__ . '/app/controllers/ProfessorController.php';
        $controller = new ProfessorController();
        $controller->exibirFormularioEdicaoEquacao($_GET['id'] ?? null);
        break;

    case 'editar_aluno':
        verificarAutenticacao('professor');
        require_once __DIR__ . '/app/controllers/ProfessorController.php';
        $controller = new ProfessorController();
        $controller->exibirFormularioEdicao($_GET['id'] ?? null);
        break;

    case 'gerenciar_alunos':
        verificarAutenticacao('professor');
        require_once __DIR__ . '/app/controllers/ProfessorController.php';
        $controller = new ProfessorController();
        $controller->gerenciarAlunos();
        break;

    case 'gerenciar_equacoes':
        verificarAutenticacao('professor');
        require_once __DIR__ . '/app/controllers/ProfessorController.php';
        $controller = new ProfessorController();
        $controller->listarEquacoes();
        break;

    case 'relatorio':
        verificarAutenticacao('professor');
        require_once __DIR__ . '/app/controllers/ProfessorController.php';
        $controller = new ProfessorController();
        $controller->relatorio();
        break;

    case 'professor/dashboard':
    case 'dashboard_professor':
    case 'professor':
        verificarAutenticacao('professor');
        require_once __DIR__ . '/app/models/Professor.php';
        require_once __DIR__ . '/app/controllers/ProfessorController.php';
        $controller = new ProfessorController();
        $controller->dashboard();
        break;

    default:
        header("Location: index.php?view=login");
        exit;
}