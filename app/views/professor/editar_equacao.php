<?php
/**
 * editar_equacao.php
 * Formulário de edição de equação seguindo o padrão de layout do sistema
 */
$page_title = 'Editar Equação - EquaTEA';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/menu_professor.php';
?>

<div class="gerenciar-container">
    <div class="page-header">
        <h1>Editar Equação</h1>
        <p class="subtitle">Atualize os coeficientes e o nível de dificuldade</p>
    </div>

    <div class="form-section">
        <form action="index.php?action=salvar_edicao_equacao" method="POST">
            <input type="hidden" name="id" value="<?= htmlspecialchars($equacao['id'] ?? '') ?>">
            
            <div class="form-grid equacao-grid">
                <div class="form-group">
                    <label for="coef_a">Coeficiente a *</label>
                    <input type="number" step="any" id="coef_a" name="coef_a" value="<?= htmlspecialchars($equacao['a'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="coef_b">Coeficiente b *</label>
                    <input type="number" step="any" id="coef_b" name="coef_b" value="<?= htmlspecialchars($equacao['b'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="coef_c">Coeficiente c *</label>
                    <input type="number" step="any" id="coef_c" name="coef_c" value="<?= htmlspecialchars($equacao['c'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="dificuldade">Dificuldade *</label>
                    <select id="dificuldade" name="dificuldade" required>
                        <option value="Fácil" <?= (isset($equacao['dificuldade']) && $equacao['dificuldade'] === 'Fácil') ? 'selected' : '' ?>>Fácil</option>
                        <option value="Médio" <?= (isset($equacao['dificuldade']) && $equacao['dificuldade'] === 'Médio') ? 'selected' : '' ?>>Médio</option>
                        <option value="Difícil" <?= (isset($equacao['dificuldade']) && $equacao['dificuldade'] === 'Difícil') ? 'selected' : '' ?>>Difícil</option>
                    </select>
                </div>
            </div>

            <div class="form-actions" style="margin-top: 20px;">
                <button type="submit" class="btn-primary">Salvar Alterações</button>
                <a href="index.php?view=gerenciar_equacoes" class="btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>