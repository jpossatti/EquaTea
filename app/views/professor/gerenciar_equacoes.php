<?php
/**
 * gerenciar_equacoes.php
 * Gerenciamento de equações - Versão de teste
 * 
 * Acesso: ?view=gerenciar_equacoes
 */

$page_title = 'Gerenciar Equações - EquaTEA';

// ============================================================
// 1. CARREGAR DEPENDÊNCIAS
// ============================================================

$base_path = dirname(__DIR__, 2);

// Tentar diferentes localizações do Database.php
$database_locations = [
    $base_path . '/app/config/Database.php',
    $base_path . '/config/Database.php',
];

$found = false;
foreach ($database_locations as $location) {
    if (file_exists($location)) {
        require_once $location;
        $found = true;
        break;
    }
}

if (!$found) {
    die('❌ Database.php não encontrado.');
}

// Tentar diferentes localizações do Equacao.php
$model_locations = [
    $base_path . '/app/models/Equacao.php',
    $base_path . '/models/Equacao.php',
];

$found = false;
foreach ($model_locations as $location) {
    if (file_exists($location)) {
        require_once $location;
        $found = true;
        break;
    }
}

if (!$found) {
    die('❌ Equacao.php não encontrado.');
}

// ============================================================
// 2. BUSCAR DADOS
// ============================================================

$equacao = new Equacao();
$dados_equacoes = $equacao->getAll();

// Contagens por dificuldade
$totais = [
    'facil' => 0,
    'medio' => 0,
    'dificil' => 0
];
foreach ($dados_equacoes as $e) {
    if (isset($totais[$e['dificuldade']])) {
        $totais[$e['dificuldade']]++;
    }
}

// ============================================================
// 3. CARREGAR PARTIALS
// ============================================================

include_once dirname(__DIR__) . '/partials/header.php';
include_once dirname(__DIR__) . '/partials/menu_professor.php';
?>

<main class="container gerenciar-container">
    <div class="page-header">
        <h1>📝 Gerenciar Equações</h1>
        <p class="subtitle">Cadastre, edite e remova equações do banco de dados</p>
    </div>

    <section class="form-section">
        <h2 class="section-title">➕ Cadastrar Nova Equação</h2>
        <form method="POST" action="#" class="form-cadastro">
            <div class="form-grid equacao-grid">
                <div class="form-group">
                    <label for="a">Coeficiente a *</label>
                    <input type="number" id="a" name="a" placeholder="-20 a 20" min="-20" max="20" value="3">
                    <small class="form-help">Não pode ser zero</small>
                </div>
                <div class="form-group">
                    <label for="b">Coeficiente b *</label>
                    <input type="number" id="b" name="b" placeholder="-20 a 20" min="-20" max="20" value="5">
                </div>
                <div class="form-group">
                    <label for="c">Coeficiente c *</label>
                    <input type="number" id="c" name="c" placeholder="-20 a 20" min="-20" max="20" value="14">
                </div>
                <div class="form-group">
                    <label for="dificuldade">Dificuldade *</label>
                    <select id="dificuldade" name="dificuldade">
                        <option value="facil">Fácil</option>
                        <option value="medio" selected>Médio</option>
                        <option value="dificil">Difícil</option>
                    </select>
                </div>
            </div>

            <div class="equacao-preview">
                <span class="preview-label">Pré-visualização:</span>
                <span class="preview-equacao" id="preview">3x + 5 = 14</span>
                <span class="preview-solucao" id="preview-solucao">x = 3</span>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">✅ Cadastrar Equação</button>
                <button type="reset" class="btn-secondary">🔄 Limpar</button>
            </div>
        </form>
    </section>

    <section class="list-section">
        <div class="list-header">
            <h2 class="section-title">📋 Lista de Equações</h2>
            <div class="resumo-equacoes">
                <span class="resumo-item">📚 Total: <strong><?php echo count($dados_equacoes); ?></strong></span>
                <span class="resumo-item facil">🟢 Fácil: <strong><?php echo $totais['facil']; ?></strong></span>
                <span class="resumo-item medio">🟡 Médio: <strong><?php echo $totais['medio']; ?></strong></span>
                <span class="resumo-item dificil">🔴 Difícil: <strong><?php echo $totais['dificil']; ?></strong></span>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="list-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Equação</th>
                        <th>Solução</th>
                        <th>Dificuldade</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($dados_equacoes)): ?>
                        <?php foreach ($dados_equacoes as $e): ?>
                        <?php 
                            $sinal = $e['b'] >= 0 ? '+' : '-';
                            $equacao_str = "{$e['a']}x {$sinal} " . abs($e['b']) . " = {$e['c']}";
                            $dificuldade_class = $e['dificuldade'];
                            $dificuldade_label = ucfirst($e['dificuldade']);
                        ?>
                        <tr>
                            <td data-label="ID">#<?php echo $e['id']; ?></td>
                            <td data-label="Equação"><strong><?php echo $equacao_str; ?></strong></td>
                            <td data-label="Solução">x = <?php echo $e['solucao']; ?></td>
                            <td data-label="Dificuldade">
                                <span class="badge <?php echo $dificuldade_class; ?>"><?php echo $dificuldade_label; ?></span>
                            </td>
                            <td data-label="Ações">
                                <button class="btn-acao btn-editar" title="Editar">✏️</button>
                                <button class="btn-acao btn-excluir" title="Excluir">🗑️</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding: 30px; color: #888;">
                                Nenhuma equação cadastrada.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <div class="nav-links">
        <a href="?view=professor">⬅️ Voltar ao Dashboard</a>
        <a href="?view=relatorio">📈 Ver Relatórios</a>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const a = document.getElementById('a');
        const b = document.getElementById('b');
        const c = document.getElementById('c');
        const preview = document.getElementById('preview');
        const previewSolucao = document.getElementById('preview-solucao');

        function atualizarPreview() {
            const valA = parseInt(a.value) || 0;
            const valB = parseInt(b.value) || 0;
            const valC = parseInt(c.value) || 0;
            const sinal = valB >= 0 ? '+' : '-';
            const bAbs = Math.abs(valB);
            preview.textContent = `${valA}x ${sinal} ${bAbs} = ${valC}`;

            if (valA !== 0) {
                const solucao = (valC - valB) / valA;
                if (Number.isInteger(solucao)) {
                    previewSolucao.textContent = `✅ x = ${solucao}`;
                    previewSolucao.style.color = '#27ae60';
                    previewSolucao.style.background = '#d4edda';
                } else {
                    previewSolucao.textContent = `⚠️ x = ${solucao.toFixed(2)} (não inteiro)`;
                    previewSolucao.style.color = '#e74c3c';
                    previewSolucao.style.background = '#fde8e8';
                }
            } else {
                previewSolucao.textContent = '❌ a não pode ser zero';
                previewSolucao.style.color = '#e74c3c';
                previewSolucao.style.background = '#fde8e8';
            }
        }

        if (a && b && c) {
            a.addEventListener('input', atualizarPreview);
            b.addEventListener('input', atualizarPreview);
            c.addEventListener('input', atualizarPreview);
            atualizarPreview();
        }
    });
</script>

<?php include_once dirname(__DIR__) . '/partials/footer.php'; ?>