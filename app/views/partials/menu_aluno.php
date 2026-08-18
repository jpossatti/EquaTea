<?php
/**
 * menu_aluno.php
 * Menu de navegação do aluno
 */
?>
<nav class="menu-aluno">
    <div class="container menu-aluno-content">
        <ul class="menu-aluno-links">
            <li class="menu-aluno-item <?php echo ($view == 'aluno') ? 'active' : ''; ?>">
                <a href="?view=aluno">📊 Dashboard</a>
            </li>
            <li class="menu-aluno-item <?php echo ($view == 'exercicio') ? 'active' : ''; ?>">
                <a href="?view=exercicio">📝 Novo Exercício</a>
            </li>
            <li class="menu-aluno-item <?php echo ($view == 'parabens') ? 'active' : ''; ?>">
                <a href="?view=parabens">🎉 Concluído</a>
            </li>
            <li class="menu-aluno-item">
                <a href="?view=login">🚪 Sair</a>
            </li>
        </ul>
    </div>
</nav>