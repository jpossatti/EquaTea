<?php
/**
 * menu_professor.php
 * Menu de navegação do professor - CORRIGIDO
 */

// Usa a variável global
$view = $GLOBALS['current_view'] ?? $_GET['view'] ?? '';

// Se a view estiver vazia, usa a view da URL
if (empty($view) && isset($_GET['view'])) {
    $view = $_GET['view'];
}

// Limpa parâmetros extras da view
if (strpos($view, '&') !== false) {
    $view = substr($view, 0, strpos($view, '&'));
}

// Define a página ativa
$isDashboard = ($view == 'professor' || $view == 'professor/dashboard');
$isAlunos = ($view == 'gerenciar_alunos' || $view == 'editar_aluno' || $view == 'salvar_edicao');
$isEquacoes = ($view == 'gerenciar_equacoes' || $view == 'editar_equacao' || $view == 'salvar_edicao_equacao');
$isRelatorio = ($view == 'relatorio');
?>

<nav class="menu-professor">
    <div class="container menu-professor-content">
        <ul class="menu-professor-links">
            <li class="menu-professor-item <?php echo $isDashboard ? 'active' : ''; ?>">
                <a href="index.php?view=professor/dashboard">📊 Dashboard</a>
            </li>
            <li class="menu-professor-item <?php echo $isAlunos ? 'active' : ''; ?>">
                <a href="index.php?view=gerenciar_alunos">👨‍🎓 Alunos</a>
            </li>
            <li class="menu-professor-item <?php echo $isEquacoes ? 'active' : ''; ?>">
                <a href="index.php?view=gerenciar_equacoes">📐 Equações</a>
            </li>
            <li class="menu-professor-item <?php echo $isRelatorio ? 'active' : ''; ?>">
                <a href="index.php?view=relatorio">📈 Relatórios</a>
            </li>
            <li class="menu-professor-item">
                <a href="index.php?view=logout" onclick="return confirm('Deseja realmente sair?');">🚪 Sair</a>
            </li>
        </ul>
    </div>
</nav>

<style>
    .menu-professor {
        background: #ffffff;
        border-bottom: 1px solid #e0e0e0;
        padding: 10px 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .menu-professor-content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
    }
    
    .menu-professor-links {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        gap: 5px;
        flex-wrap: wrap;
    }
    
    .menu-professor-item {
        margin: 0;
    }
    
    .menu-professor-item a {
        display: block;
        padding: 8px 18px;
        color: #555;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.95rem;
        border-radius: 6px;
        transition: all 0.2s;
    }
    
    .menu-professor-item a:hover {
        background: #f0f0f0;
        color: #1a237e;
    }
    
    .menu-professor-item.active a {
        background: #1a237e;
        color: white;
    }
    
    .menu-professor-item.active a:hover {
        background: #283593;
        color: white;
    }
    
    @media (max-width: 768px) {
        .menu-professor-links {
            flex-direction: column;
            align-items: center;
        }
        
        .menu-professor-item {
            width: 100%;
            text-align: center;
        }
        
        .menu-professor-item a {
            padding: 10px 20px;
        }
    }
</style>