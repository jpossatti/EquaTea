<?php
/**
 * menu_professor.php
 * Menu de navegação do professor
 */
?>
<nav class="menu-professor">
    <div class="container menu-professor-content">
        <ul class="menu-professor-links">
            <li class="menu-professor-item <?php echo ($view == 'professor') ? 'active' : ''; ?>">
                <a href="?view=professor">📊 Dashboard</a>
            </li>
            <li class="menu-professor-item <?php echo ($view == 'gerenciar_alunos') ? 'active' : ''; ?>">
                <a href="?view=gerenciar_alunos">👨‍🎓 Alunos</a>
            </li>
            <li class="menu-professor-item <?php echo ($view == 'gerenciar_equacoes') ? 'active' : ''; ?>">
                <a href="?view=gerenciar_equacoes">📝 Equações</a>
            </li>
            <li class="menu-professor-item <?php echo ($view == 'relatorio') ? 'active' : ''; ?>">
                <a href="?view=relatorio">📈 Relatórios</a>
            </li>
            <li class="menu-professor-item">
                <a href="?view=login">🚪 Sair</a>
            </li>
        </ul>
    </div>
</nav>