<?php
/**
 * parabens.php
 * Tela de parabéns ao concluir uma equação
 * 
 * Acesso: ?view=parabens
 */

$page_title = 'Parabéns! - EquaTEA';
$nome_aluno = $dados_aluno['nome'] ?? 'Aluno Teste';
$equacao_concluida = $equacao->getRandom();
$enunciado = $equacao->getEnunciado($equacao_concluida['id']);

include_once __DIR__ . '/../partials/header.php';
include_once __DIR__ . '/../partials/menu_aluno.php';
?>
<main class="container parabens-container">
    <div class="parabens-card">
        <div class="parabens-icon">🎉</div>
        <h1>Parabéns, <?php echo htmlspecialchars($nome_aluno); ?>!</h1>
        <p>Você concluiu a equação com sucesso!</p>

        <div class="parabens-detalhes">
            <div class="detalhe-item">
                <span>📝 Equação</span>
                <strong><?php echo htmlspecialchars($enunciado); ?></strong>
            </div>
            <div class="detalhe-item">
                <span>🎯 Solução</span>
                <strong>x = <?php echo $equacao_concluida['solucao']; ?></strong>
            </div>
            <div class="detalhe-item">
                <span>📊 Dificuldade</span>
                <strong><?php echo ucfirst($equacao_concluida['dificuldade']); ?></strong>
            </div>
        </div>

        <div class="parabens-acoes">
            <a href="?view=exercicio" class="btn-proximo">🚀 Próximo Exercício</a>
            <a href="?view=aluno" class="btn-voltar">📊 Dashboard</a>
        </div>
    </div>
</main>
<?php include_once __DIR__ . '/../partials/footer.php'; ?>