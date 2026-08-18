<?php
/**
 * dashboard.php
 * Dashboard do aluno - Versão de teste (sem sessão)
 * 
 * Acesso: ?view=aluno
 */

$page_title = 'Dashboard - EquaTEA';
$nome_aluno = $dados_aluno['nome'] ?? 'Aluno Teste';
$nivel_tea = $dados_aluno['nivel_tea'] ?? 'suporte1';

// Dados de exemplo
$total_equacoes = count($dados_progresso);
$concluidas = array_sum(array_column($dados_progresso, 'concluida'));
$taxa = $total_equacoes > 0 ? round(($concluidas / $total_equacoes) * 100, 1) : 0;

include_once __DIR__ . '/../partials/header.php';
include_once __DIR__ . '/../partials/menu_aluno.php';
?>
<main class="container dashboard-container">
    <h1>👋 Olá, <?php echo htmlspecialchars($nome_aluno); ?>!</h1>
    <p class="subtitle">Vamos praticar equações de 1º grau hoje?</p>

    <div class="stats-grid">
        <div class="stat-card"><span class="stat-value"><?php echo $total_equacoes; ?></span><span class="stat-label">Equações tentadas</span></div>
        <div class="stat-card"><span class="stat-value"><?php echo $concluidas; ?></span><span class="stat-label">Equações concluídas</span></div>
        <div class="stat-card"><span class="stat-value"><?php echo $taxa; ?>%</span><span class="stat-label">Taxa de conclusão</span></div>
    </div>

    <a href="?view=exercicio" class="btn-novo-exercicio">🚀 Iniciar Novo Exercício</a>

    <?php if (!empty($dados_progresso)): ?>
        <h2>📈 Meu Progresso Recente</h2>
        <table class="progresso-table">
            <thead><tr><th>Equação</th><th>Dificuldade</th><th>Status</th><th>Tentativas</th></tr></thead>
            <tbody>
                <?php foreach (array_slice($dados_progresso, 0, 5) as $p): ?>
                <tr>
                    <td><?php echo htmlspecialchars($p['equacao']); ?></td>
                    <td><span class="badge <?php echo $p['dificuldade']; ?>"><?php echo ucfirst($p['dificuldade']); ?></span></td>
                    <td><?php echo $p['concluida'] ? '✅ Concluída' : '⏳ Passo ' . $p['passo_atual'] . '/4'; ?></td>
                    <td><?php echo $p['tentativas']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
<?php include_once __DIR__ . '/../partials/footer.php'; ?>