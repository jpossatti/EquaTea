<?php
/**
 * dashboard.php
 * Dashboard do professor - Versão com Debug
 */
$page_title = 'Dashboard Professor - EquaTEA';
$nome_professor = $_SESSION['usuario_nome'] ?? 'Professor';

// Estatísticas baseadas nos dados vindos do Controller
$total_alunos = count($dados_alunos ?? []);
$total_equacoes = count($dados_equacoes ?? []);
$total_erros = count($erros_comuns ?? []);

include_once __DIR__ . '/../partials/header.php';
include_once __DIR__ . '/../partials/menu_professor.php';
$page_title = 'Dashboard Professor - EquaTEA';
$nome_professor = 'Professor Carlos Silva';

// Mock de dados para validação caso não venham do controller
$dados_alunos = $dados_alunos ?? [
    ['id' => 1, 'nome' => 'Lucas Mendes', 'idade' => 15, 'nivel_tea' => 'suporte1', 'total_equacoes' => 5],
    ['id' => 2, 'nome' => 'Ana Clara Silva', 'idade' => 16, 'nivel_tea' => 'suporte2', 'total_equacoes' => 3]
];
$dados_equacoes = $dados_equacoes ?? [
    ['id' => 1, 'a' => 1, 'b' => 3, 'c' => 7, 'dificuldade' => 'facil'],
    ['id' => 2, 'a' => 2, 'b' => -4, 'c' => 10, 'dificuldade' => 'medio']
];
$dados_relatorio = $dados_relatorio ?? [
    ['quantidade' => 3], ['quantidade' => 5]
];

// Estatísticas
$total_alunos = count($dados_alunos);
$total_equacoes = count($dados_equacoes);
$total_erros = array_sum(array_column($dados_relatorio, 'quantidade') ?? [0]);

include_once __DIR__ . '/../partials/header.php';
include_once __DIR__ . '/../partials/menu_professor.php';
?>

<!-- PAINEL DE DEBUG DA VIEW PROFESSOR -->
<div style="background: #1e1e1e; color: #00ff66; padding: 15px; margin: 15px; border-radius: 8px; font-family: monospace; font-size: 13px;">
    <strong style="color: #fff; border-bottom: 1px solid #444; display: block; padding-bottom: 5px; margin-bottom: 8px;">
        🐛 [DEBUG] Dashboard Professor - Processamento de Métricas
    </strong>
    <ul>
        <li><strong>Sessão Professor:</strong> <?php echo htmlspecialchars($nome_professor); ?></li>
        <li><strong>Contagem $dados_alunos:</strong> <?php echo $total_alunos; ?> registro(s) carregado(s).</li>
        <li><strong>Contagem $dados_equacoes:</strong> <?php echo $total_equacoes; ?> registro(s) carregado(s).</li>
        <li><strong>Soma $dados_relatorio (Erros):</strong> <?php echo $total_erros; ?> ocorrência(s).</li>
    </ul>
</div>

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
              <?php if (!empty($dados_alunos) && is_array($dados_alunos)): ?>
    <?php foreach ($dados_alunos as $aluno): ?>
        <tr>
            <td><?php echo htmlspecialchars($aluno['nome'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($aluno['idade'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($aluno['nivel_tea'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($aluno['total_equacoes'] ?? 0); ?></td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="4">Nenhum aluno encontrado.</td>
    </tr>
<?php endif; ?>
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