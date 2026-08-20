<?php
// --- CONTROLE DE SESSÃO ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado e se é professor
if (!isset($_SESSION['usuario_id']) || ($_SESSION['tipo_perfil'] ?? '') !== 'professor') {
    $_SESSION['admin_error'] = 'Acesso negado. Faça login como professor.';
    header('Location: index.php?view=login');
    exit;
}
// ---------------------------

$page_title = 'Editar Aluno - EquaTEA';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/menu_professor.php';
?>

<div class="gerenciar-container">
    <div class="page-header">
        <h1>Editar Aluno</h1>
        <p class="subtitle">Atualize os dados cadastrais do estudante</p>
    </div>

    <div class="form-section">
        <form action="index.php?action=salvar_edicao" method="POST">
            <input type="hidden" name="id" value="<?= htmlspecialchars($aluno['id']) ?>">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="nome">Nome Completo</label>
                    <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($aluno['nome']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($aluno['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="nivel_tea">Nível TEA</label>
                    <input type="text" id="nivel_tea" name="nivel_tea" value="<?= htmlspecialchars($aluno['nivel_tea'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="turma">Turma</label>
                    <input type="text" id="turma" name="turma" value="<?= htmlspecialchars($aluno['turma'] ?? '') ?>">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Salvar Alterações</button>
                <a href="index.php?view=gerenciar_alunos" class="btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>


