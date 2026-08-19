<?php
/**
 * index.php
 * Roteador com tratamento das ações POST de formulários
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuração de conexões e Models
if (file_exists(__DIR__ . '/app/config/Database.php')) {
    require_once __DIR__ . '/app/config/Database.php';
}

$models = ['Aluno.php', 'Equacao.php', 'RegistroErro.php', 'Usuario.php'];
foreach ($models as $modelFile) {
    $path = __DIR__ . '/app/models/' . $modelFile;
    if (file_exists($path)) {
        require_once $path;
    }
}

// Controllers
if (file_exists(__DIR__ . '/app/controllers/AlunoController.php')) {
    require_once __DIR__ . '/app/controllers/AlunoController.php';
}
if (file_exists(__DIR__ . '/app/controllers/ProfessorController.php')) {
    require_once __DIR__ . '/app/controllers/ProfessorController.php';
}

$action = $_GET['action'] ?? null;
$view   = $_GET['view']   ?? 'login';

// Processa Ações do Professor
if (class_exists('ProfessorController')) {
    $professorController = new ProfessorController();

    switch ($action) {
        case 'cadastrar_aluno':
            $professorController->cadastrarAluno();
            exit;
        case 'resetar_senha':
            $professorController->resetarSenha();
            exit;
        case 'cadastrar_equacao':
            $professorController->cadastrarEquacao();
            exit;
        case 'excluir_equacao':
            $professorController->excluirEquacao();
            exit;
    }
}

// Exibição de Views
switch ($view) {
    case 'login':
        require_once __DIR__ . '/app/views/auth/login.php';
        break;

    case 'aluno':
    case 'dashboard':
        if (class_exists('AlunoController')) {
            (new AlunoController())->dashboard();
        }
        break;

    case 'professor':
    case 'dashboard_professor':
        if (class_exists('ProfessorController')) {
            (new ProfessorController())->dashboard();
        }
        break;

    case 'gerenciar_alunos':
        if (class_exists('ProfessorController')) {
            (new ProfessorController())->gerenciarAlunos();
        }
        break;

    case 'gerenciar_equacoes':
        if (class_exists('ProfessorController')) {
            (new ProfessorController())->gerenciarEquacoes();
        }
        break;

    default:
        header('Location: index.php?view=login');
        exit;
}