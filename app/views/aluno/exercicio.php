<?php
/**
 * exercicio.php
 * Página de exercício passo a passo - Versão de teste
 * 
 * Acesso: ?view=exercicio
 */

$page_title = 'Exercício - EquaTEA';

// Selecionar uma equação aleatória para teste
$equacao_selecionada = $equacao->getRandom();
$enunciado = $equacao->getEnunciado($equacao_selecionada['id']);
$passo_atual = 1;

$passos_info = [
    1 => ['titulo' => 'Identificar os termos', 'descricao' => 'Identifique quais termos têm x e quais não têm.', 'exemplo' => 'Na equação 3x + 5 = 14, o termo com x é 3x e os termos sem x são +5 e 14.', 'placeholder' => 'Ex: 3x + 5 = 14'],
    2 => ['titulo' => 'Isolar o termo com x', 'descricao' => 'Use a operação inversa para isolar o termo com x.', 'exemplo' => 'Subtraia 5 dos dois lados: 3x = 14 - 5', 'placeholder' => 'Ex: 3x = 14 - 5'],
    3 => ['titulo' => 'Calcular o lado direito', 'descricao' => 'Calcule o valor do lado direito da equação.', 'exemplo' => '14 - 5 = 9 → 3x = 9', 'placeholder' => 'Ex: 9'],
    4 => ['titulo' => 'Isolar x', 'descricao' => 'Divida ambos os lados pelo coeficiente de x.', 'exemplo' => 'Divida ambos por 3: x = 9 ÷ 3 = 3', 'placeholder' => 'Ex: 3']
];

$info = $passos_info[$passo_atual];

include_once __DIR__ . '/../partials/header.php';
include_once __DIR__ . '/../partials/menu_aluno.php';
?>
<main class="container exercicio-container">
    <h1>📝 Resolva a equação:</h1>
    <div class="equacao-display"><?php echo htmlspecialchars($enunciado); ?></div>

    <div class="passos-indicadores">
        <?php for ($i = 1; $i <= 4; $i++): ?>
            <div class="passo-indicador <?php echo ($i == $passo_atual) ? 'active' : (($i < $passo_atual) ? 'completed' : ''); ?>">
                <span class="passo-numero"><?php echo $i; ?></span>
                <span class="passo-rotulo">Passo <?php echo $i; ?></span>
            </div>
        <?php endfor; ?>
    </div>

    <div class="exercicio-card">
        <h2><?php echo $info['titulo']; ?></h2>
        <p class="passo-descricao"><?php echo $info['descricao']; ?></p>
        <div class="passo-exemplo">💡 <?php echo $info['exemplo']; ?></div>

        <form method="POST" action="#">
            <div class="form-group">
                <label for="resposta">Sua resposta:</label>
                <input type="text" id="resposta" name="resposta" placeholder="<?php echo $info['placeholder']; ?>" required>
            </div>
            <div class="botoes-acoes">
                <button type="submit" class="btn-verificar">✅ Verificar</button>
                <button type="button" class="btn-ajuda">❓ Ajuda</button>
            </div>
        </form>

        <div class="feedback-area" style="display:none;">
            <div class="feedback-success">✅ Correto! Avançando para o próximo passo...</div>
        </div>
    </div>

    <div class="nav-links">
        <a href="?view=aluno">⬅️ Voltar ao Dashboard</a>
        <a href="?view=parabens">🎉 Ver tela de Parabéns</a>
    </div>
</main>
<?php include_once __DIR__ . '/../partials/footer.php'; ?>