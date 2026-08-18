<?php
/**
 * dashboard.php
 * Dashboard do professor - Versão de teste
 * 
 * Acesso: ?view=professor
 */

$page_title = 'Dashboard Professor - EquaTEA';
$nome_professor = 'Professor Carlos Silva';

// Estatísticas
$total_alunos = count($dados_alunos);
$total_equacoes = count($dados_equacoes);
$total_erros = array_sum(array_column($dados_relatorio, 'quantidade') ?? [0]);

include_once __DIR__ . '/../partials/header.php';
include_once __DIR__ . '/../partials/menu_professor.php';
?>
<main class="container professor-dashboard">
    <h1>👋 Olá, Prof. <?php echo htmlspecialchars($nome_professor); ?>!</h1>
    <p class="subtitle">Acompanhe o progresso dos seus alunos</p>

    <div class="stats-grid">
        <div class="stat-card"><span class="stat-value"><?php echo $total_alunos; ?></span><span class="stat-label">Alunos</span></div>
        <div class="stat-card"><span class="stat-value"><?php echo $total_equacoes; ?></span><span class="stat-label">Equações</span></div>
        <div class="stat-card"><span class="stat-value"><?php echo $total_erros; ?></span><span class="stat-label">Erros</span></div>
    </div>

    <div class="acoes-rapidas">
        <a href="?view=gerenciar_alunos" class="acao-card">👨‍🎓 Gerenciar Alunos</a>
        <a href="?view=gerenciar_equacoes" class="acao-card">📝 Gerenciar Equações</a>
        <a href="?view=relatorio" class="acao-card">📈 Relatórios</a>
    </div>

    <?php if (!empty($dados_alunos)): ?>
        <h2>👨‍🎓 Alunos</h2>
        <table class="alunos-table">
            <thead><tr><th>Nome</th><th>Idade</th><th>Nível TEA</th><th>Equações</th></tr></thead>
            <tbody>
                <?php foreach (array_slice($dados_alunos, 0, 5) as $a): ?>
                <tr>
                    <td><?php echo htmlspecialchars($a['nome']); ?></td>
                    <td><?php echo $a['idade']; ?></td>
                    <td><?php echo $a['nivel_tea'] == 'suporte1' ? 'Suporte 1' : 'Suporte 2'; ?></td>
                    <td><?php echo $a['total_equacoes'] ?? 0; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
<style>
    .professor-dashboard { padding: 20px 0; }
    .subtitle { color: #7f8c8d; font-size: 18px; margin-top: -8px; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px; margin: 20px 0; }
    .stat-card { background: #fff; padding: 16px 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); text-align: center; }
    .stat-value { font-size: 28px; font-weight: 700; color: #2c3e50; display: block; }
    .stat-label { font-size: 14px; color: #888; }
    .acoes-rapidas { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin: 20px 0; }
    .acao-card { background: #fff; padding: 16px 20px; border-radius: 8px; text-align: center; text-decoration: none; color: #2c3e50; box-shadow: 0 2px 8px rgba(0,0,0,0.06); font-weight: 500; }
    .acao-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
    .alunos-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    .alunos-table th { background: #f8f9fa; padding: 12px 16px; text-align: left; font-weight: 600; }
    .alunos-table td { padding: 10px 16px; border-bottom: 1px solid #f1f3f5; }
</style>
<?php include_once __DIR__ . '/../partials/footer.php'; ?>