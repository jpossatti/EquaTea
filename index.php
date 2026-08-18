<?php
/**
 * index.php
 * Roteador principal com gerenciamento de sessão e rotas
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Importa a conexão de Banco de Dados se existir
if (file_exists(__DIR__ . '/app/config/Database.php')) {
    require_once __DIR__ . '/app/config/Database.php';
}

// Caminho do controller
$controllerPath = __DIR__ . '/app/controllers/AlunoController.php';

if (file_exists($controllerPath)) {
    require_once $controllerPath;
}

// Captura parâmetros de ação e navegação
$action = $_GET['action'] ?? null;
$view   = $_GET['view']   ?? 'dashboard';

if (class_exists('AlunoController')) {
    $alunoController = new AlunoController();

    // Processa o envio do formulário de verificação
    if ($action === 'verificar_resposta') {
        $alunoController->verificarResposta();
        exit;
    }

    // Direciona a exibição das views
    switch ($view) {
        case 'dashboard':
        case 'aluno':
            $alunoController->dashboard();
            break;

        case 'exercicio':
            $alunoController->exercicio();
            break;

        case 'parabens':
            if (file_exists(__DIR__ . '/app/views/aluno/parabens.php')) {
                require_once __DIR__ . '/app/views/aluno/parabens.php';
            } else {
                echo "<h1>🎉 Parabéns! Você concluiu a equação!</h1><p><a href='index.php?view=exercicio'>Voltar ao exercício</a></p>";
            }
            break;

        case 'login':
            if (file_exists(__DIR__ . '/app/views/auth/login.php')) {
                require_once __DIR__ . '/app/views/auth/login.php';
            } else {
                echo "<h1>Página de Login</h1>";
            }
            break;

        default:
            $alunoController->dashboard();
            break;
    }
} else {
    echo "<h2>Erro: O arquivo AlunoController.php não foi localizado em /app/controllers/.</h2>";
}