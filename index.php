<?php
/**
 * index.php - Roteador Central (Ambiente Dev - Sem Sessão)
 */

// Captura a view atual via GET ou POST (Padrão: 'login')
$view = $_GET['view'] ?? $_POST['view'] ?? 'login';

// Roteamento Direto por Parâmetro
switch ($view) {

    case 'login':
        // Tela de Seleção Inicial do Perfil (Sem Persistência de Sessão)
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

    case 'dashboard':
        $caminhoView = __DIR__ . '/app/views/aluno/dashboard.php';
        if (file_exists($caminhoView)) {
            require_once $caminhoView;
        } else {
            echo "<h3>View 'app/views/aluno/dashboard.php' não encontrada.</h3>";
        }
        break;

    case 'exercicio':
        $caminhoView = __DIR__ . '/app/views/aluno/exercicio.php';
        if (file_exists($caminhoView)) {
            require_once $caminhoView;
        } else {
            echo "<h3>View 'app/views/aluno/exercicio.php' não encontrada.</h3>";
        }
        break;

    case 'parabens':
        $caminhoView = __DIR__ . '/app/views/aluno/parabens.php';
        if (file_exists($caminhoView)) {
            require_once $caminhoView;
        } else {
            echo "<div style='text-align:center; padding:50px; font-family:sans-serif;'>";
            echo "<h1>🎉 Exercício Concluído!</h1>";
            echo "<p><a href='index.php?view=dashboard'>Voltar ao Dashboard</a></p>";
            echo "</div>";
        }
        break;

    case 'dashboard_professor':
        $caminhoView = __DIR__ . '/app/views/professor/dashboard.php';
        if (file_exists($caminhoView)) {
            require_once $caminhoView;
        } else {
            echo "<div style='text-align:center; padding:50px; font-family:sans-serif;'>";
            echo "<h2>👨‍🏫 Painel do Professor (Em Construção)</h2>";
            echo "<p><a href='index.php?view=login'>Voltar ao Início</a></p>";
            echo "</div>";
        }
        break;

    default:
        header("Location: index.php?view=login");
        exit;
}