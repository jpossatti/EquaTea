<?php
/**
 * menu_aluno.php
 * Menu de navegação do aluno - CORRIGIDO
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
$isDashboard = ($view == 'aluno' || $view == 'aluno/dashboard');
$isExercicio = ($view == 'exercicio' || $view == 'aluno/exercicio');
$isParabens = ($view == 'parabens' || $view == 'aluno/parabens');
?>

<nav class="menu-aluno">
    <div class="container menu-aluno-content">
        <ul class="menu-aluno-links">
            <li class="menu-aluno-item <?php echo $isDashboard ? 'active' : ''; ?>">
                <a href="index.php?view=aluno/dashboard">📊 Dashboard</a>
            </li>
            <li class="menu-aluno-item <?php echo $isExercicio ? 'active' : ''; ?>">
                <a href="index.php?view=exercicio">📝 Novo Exercício</a>
            </li>
            <li class="menu-aluno-item <?php echo $isParabens ? 'active' : ''; ?>">
                <a href="index.php?view=parabens">🎉 Concluído</a>
            </li>
            <li class="menu-aluno-item">
                <a href="index.php?view=logout" onclick="return confirm('Deseja realmente sair?');">🚪 Sair</a>
            </li>
        </ul>
    </div>
</nav>

<style>
    .menu-aluno {
        background: #ffffff;
        border-bottom: 1px solid #e0e0e0;
        padding: 10px 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .menu-aluno-content {
        max-width: 950px;
        margin: 0 auto;
        padding: 0 15px;
    }
    
    .menu-aluno-links {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        gap: 5px;
        flex-wrap: wrap;
    }
    
    .menu-aluno-item {
        margin: 0;
    }
    
    .menu-aluno-item a {
        display: block;
        padding: 8px 18px;
        color: #555;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.95rem;
        border-radius: 6px;
        transition: all 0.2s;
    }
    
    .menu-aluno-item a:hover {
        background: #f0f0f0;
        color: #2b3a4a;
    }
    
    .menu-aluno-item.active a {
        background: #2b3a4a;
        color: white;
    }
    
    .menu-aluno-item.active a:hover {
        background: #3a4d61;
        color: white;
    }
    
    @media (max-width: 768px) {
        .menu-aluno-links {
            flex-direction: column;
            align-items: center;
        }
        
        .menu-aluno-item {
            width: 100%;
            text-align: center;
        }
        
        .menu-aluno-item a {
            padding: 10px 20px;
        }
    }
</style>