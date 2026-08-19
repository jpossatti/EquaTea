<?php
/**
 * index.php - Roteador Central
 */

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

// Restante do código de roteamento de views...
// 2. ROTEAMENTO DE VIEWS
$view = $_GET['view'] ?? $_POST['view'] ?? 'login';

switch ($view) {

    case 'login':
        echo "<!DOCTYPE html><html lang='pt-BR'><head><meta charset='UTF-8'><title>EquaTEA - Acesso Dev</title>";
        echo "<style>
            body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
            .container { text-align: center; background: white; padding: 50px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
            h2 { color: #2b3a4a; margin-bottom: 10px; }
            p { color: #666; margin-bottom: 30px; }
            .btn-group { display: flex; gap: 20px; justify-content: center; }
            .btn { padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 1.1rem; color: white; transition: transform 0.2s; }
            .btn:hover { transform: translateY(-3px); }
            .btn-aluno { background: #3498db; }
            .btn-prof { background: #2ecc71; }
        </style></head><body>";
        
        echo "<div class='container'>";
        echo "<h2>⚙️ EquaTEA - Modo Desenvolvimento</h2>";
        echo "<p>Escolha a interface para prosseguir com a implementação:</p>";
        echo "<div class='btn-group'>";
        echo "<a href='index.php?view=dashboard' class='btn btn-aluno'>👨‍🎓 Acessar como Aluno</a>";
        echo "<a href='index.php?view=dashboard_professor' class='btn btn-prof'>👨‍🏫 Acessar como Professor</a>";
        echo "</div></div></body></html>";
        break;

    case 'editar_aluno':
        require_once __DIR__ . '/app/controllers/ProfessorController.php';
        $controller = new ProfessorController();
        // Verifica se o ID foi passado antes de chamar o método
        $id = $_GET['id'] ?? null;
        $controller->exibirFormularioEdicao($id);
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

    default:
        header("Location: index.php?view=login");
        exit;
}