<?php
/**
 * professor/editar_aluno.php
 * Formulário de edição/criação de aluno - Layout do sistema
 */

// Controle de Sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado e se o perfil é de professor
if (!isset($_SESSION['usuario_id']) || ($_SESSION['tipo_perfil'] ?? '') !== 'professor') {
    $_SESSION['admin_error'] = 'Acesso negado. Faça login como professor.';
    header('Location: index.php?view=login');
    exit;
}

$page_title = isset($is_edit) && $is_edit ? 'Editar Aluno - EquaTEA' : 'Novo Aluno - EquaTEA';
$view = 'editar_aluno';

// Inclui o header e menu do sistema
include_once __DIR__ . '/../partials/header.php';
include_once __DIR__ . '/../partials/menu_professor.php';

// Dados do aluno (vindo do controller)
$aluno = $aluno ?? null;
$is_edit = isset($is_edit) ? $is_edit : !empty($aluno);

// Detecta o caminho base automaticamente
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$script_name = $_SERVER['SCRIPT_NAME'];
// Remove o nome do arquivo para pegar o caminho base
$base_path = dirname($script_name);
$base_url = $protocol . $host . $base_path;
?>

<div class="container" style="max-width: 700px; margin: 30px auto; padding: 0 15px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 20px;">
        <h1 style="color: #2c3e50; margin: 0;"><?= $is_edit ? '✏️ Editar Aluno' : '➕ Novo Aluno' ?></h1>
        <a href="index.php?view=gerenciar_alunos" style="display: inline-block; padding: 10px 20px; background: #6c757d; color: #fff; border-radius: 6px; text-decoration: none; font-weight: 500; transition: background 0.2s;">
            ⬅ Voltar
        </a>
    </div>

    <?php if (!empty($_SESSION['admin_error'])): ?>
        <div style="background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            <?php 
                echo $_SESSION['admin_error']; 
                unset($_SESSION['admin_error']);
            ?>
        </div>
    <?php endif; ?>

    <div style="background: #fff; border-radius: 8px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        
        <!-- CORRIGIDO: action aponta para o index.php na raiz do projeto usando caminho absoluto -->
        <form method="POST" action="../../../index.php?action=salvar_edicao">
            <?php if ($is_edit && $aluno): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($aluno['id'] ?? '') ?>">
            <?php endif; ?>
            
            <!-- Nome e E-mail -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label for="nome" style="display: block; font-weight: 600; margin-bottom: 5px; color: #555;"><strong>📛 Nome:</strong></label>
                    <input type="text" id="nome" name="nome" value="<?= $is_edit && $aluno ? htmlspecialchars($aluno['nome'] ?? '') : '' ?>" placeholder="Nome completo" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
                </div>
                <div>
                    <label for="email" style="display: block; font-weight: 600; margin-bottom: 5px; color: #555;"><strong>📧 E-mail:</strong></label>
                    <input type="email" id="email" name="email" value="<?= $is_edit && $aluno ? htmlspecialchars($aluno['email'] ?? '') : '' ?>" placeholder="email@escola.com" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
                </div>
            </div>

            <!-- Senha (apenas para novo cadastro) -->
            <?php if (!$is_edit): ?>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label for="senha" style="display: block; font-weight: 600; margin-bottom: 5px; color: #555;"><strong>🔒 Senha (min. 4 caracteres):</strong></label>
                        <input type="password" id="senha" name="senha" minlength="4" placeholder="••••••••" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
                    </div>
                    <div>
                        <label for="confirmar_senha" style="display: block; font-weight: 600; margin-bottom: 5px; color: #555;"><strong>🔒 Confirmar Senha:</strong></label>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" minlength="4" placeholder="••••••••" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
                    </div>
                </div>
            <?php endif; ?>

            <!-- Idade, Nível TEA, Escola, Turma -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div>
                    <label for="idade" style="display: block; font-weight: 600; margin-bottom: 5px; color: #555;"><strong>📅 Idade (14-21):</strong></label>
                    <input type="number" id="idade" name="idade" min="14" max="21" value="<?= $is_edit && $aluno ? ($aluno['idade'] ?? 15) : 15 ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
                </div>
                <div>
                    <label for="nivel_tea" style="display: block; font-weight: 600; margin-bottom: 5px; color: #555;"><strong>🧩 Nível TEA:</strong></label>
                    <select id="nivel_tea" name="nivel_tea" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box; background: #fff;">
                        <option value="suporte1" <?= ($is_edit && $aluno && ($aluno['nivel_tea'] ?? '') === 'suporte1') ? 'selected' : '' ?>>Suporte 1</option>
                        <option value="suporte2" <?= ($is_edit && $aluno && ($aluno['nivel_tea'] ?? '') === 'suporte2') ? 'selected' : '' ?>>Suporte 2</option>
                        <option value="suporte3" <?= ($is_edit && $aluno && ($aluno['nivel_tea'] ?? '') === 'suporte3') ? 'selected' : '' ?>>Suporte 3</option>
                    </select>
                </div>
                <div>
                    <label for="escola" style="display: block; font-weight: 600; margin-bottom: 5px; color: #555;"><strong>🏫 Escola:</strong></label>
                    <input type="text" id="escola" name="escola" value="<?= $is_edit && $aluno ? htmlspecialchars($aluno['escola'] ?? '') : '' ?>" placeholder="Nome da Escola" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
                </div>
                <div>
                    <label for="turma" style="display: block; font-weight: 600; margin-bottom: 5px; color: #555;"><strong>📚 Turma:</strong></label>
                    <input type="text" id="turma" name="turma" value="<?= $is_edit && $aluno ? htmlspecialchars($aluno['turma'] ?? '') : '' ?>" placeholder="Ex: 1º EM A" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;">
                </div>
            </div>

            <!-- Botões -->
            <div style="text-align: center; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                <button type="submit" style="background-color: #27ae60; color: white; border: none; padding: 10px 25px; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 1rem; transition: background 0.2s;">
                    <?= $is_edit ? '💾 Salvar Alterações' : '✔ Cadastrar Aluno' ?>
                </button>
                <button type="reset" style="background-color: #95a5a6; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 1rem; transition: background 0.2s;">
                    🔄 Limpar
                </button>
                <a href="index.php?view=gerenciar_alunos" style="display: inline-block; padding: 10px 20px; background: #6c757d; color: white; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 1rem; transition: background 0.2s;">
                    ❌ Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>