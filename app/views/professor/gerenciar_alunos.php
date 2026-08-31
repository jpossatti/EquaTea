<?php
/**
 * professor/gerenciar_alunos.php
 * Gerenciamento de alunos - Professor
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

$page_title = 'Gerenciar Alunos - EquaTEA';
$view = 'gerenciar_alunos';

// Inclui o header e menu do sistema
include_once __DIR__ . '/../partials/header.php';
include_once __DIR__ . '/../partials/menu_professor.php';

// Garante que $dados_alunos existe
$dados_alunos = $dados_alunos ?? [];
?>

<div class="container" style="max-width: 950px; margin: 30px auto; padding: 0 15px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 20px;">
        <h1 style="color: #2c3e50;">🎓 Gerenciar Alunos</h1>
        <a href="index.php?view=editar_aluno" class="btn-primary" style="display: inline-block; padding: 10px 24px; background: #2c3e50; color: #fff; border-radius: 6px; text-decoration: none; font-weight: 500; transition: background 0.2s;">
            ➕ Novo Aluno
        </a>
    </div>

    <!-- Mensagens de Alerta -->
    <?php if (!empty($_SESSION['admin_success'])): ?>
        <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            <?php 
                echo $_SESSION['admin_success']; 
                unset($_SESSION['admin_success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['admin_error'])): ?>
        <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            <?php 
                echo $_SESSION['admin_error']; 
                unset($_SESSION['admin_error']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Tabela de Alunos -->
    <div class="card" style="background: #fff; border-radius: 8px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h2 style="margin-top: 0; text-align: center; color: #2c3e50;">📋 Lista de Alunos</h2>

        <?php if (empty($dados_alunos)): ?>
            <div style="text-align: center; padding: 40px; color: #6c757d;">
                <p style="font-size: 18px;">Nenhum aluno cadastrado ainda.</p>
                <p>Clique em "Novo Aluno" para cadastrar.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="alunos-table" style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                    <thead>
                        <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                            <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #495057;">ID</th>
                            <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #495057;">Nome</th>
                            <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #495057;">E-mail</th>
                            <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #495057;">Idade</th>
                            <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #495057;">Nível TEA</th>
                            <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #495057;">Turma</th>
                            <th style="padding: 12px 16px; text-align: center; font-weight: 600; color: #495057;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dados_alunos as $aluno): ?>
                            <tr style="border-bottom: 1px solid #e9ecef; transition: background-color 0.2s;">
                                <td style="padding: 10px 16px; color: #495057;"><?= htmlspecialchars($aluno['id'] ?? $aluno['aluno_id'] ?? '-') ?></td>
                                <td style="padding: 10px 16px; color: #495057;">
                                    <strong><?= htmlspecialchars($aluno['nome'] ?? '') ?></strong>
                                </td>
                                <td style="padding: 10px 16px; color: #495057;"><?= htmlspecialchars($aluno['email'] ?? '') ?></td>
                                <td style="padding: 10px 16px; color: #495057;"><?= htmlspecialchars($aluno['idade'] ?? '-') ?></td>
                                <td style="padding: 10px 16px;">
                                    <?php 
                                    $nivel = $aluno['nivel_tea'] ?? 'suporte1';
                                    $nivelColors = [
                                        'suporte1' => '#28a745',
                                        'suporte2' => '#ffc107',
                                        'suporte3' => '#dc3545'
                                    ];
                                    $color = $nivelColors[$nivel] ?? '#6c757d';
                                    $nivelLabels = [
                                        'suporte1' => 'Suporte 1',
                                        'suporte2' => 'Suporte 2',
                                        'suporte3' => 'Suporte 3'
                                    ];
                                    ?>
                                    <span style="display: inline-block; padding: 4px 12px; border-radius: 12px; background-color: <?= $color ?>; color: white; font-size: 12px; font-weight: 500;">
                                        <?= htmlspecialchars($nivelLabels[$nivel] ?? $nivel) ?>
                                    </span>
                                </td>
                                <td style="padding: 10px 16px; color: #495057;"><?= htmlspecialchars($aluno['turma'] ?? 'N/A') ?></td>
                                <td style="padding: 10px 16px; text-align: center;">
                                    <div style="display: flex; gap: 4px; justify-content: center; flex-wrap: wrap;">
                                        <a href="index.php?view=editar_aluno&id=<?= $aluno['id'] ?? $aluno['aluno_id'] ?? '' ?>" 
                                           style="display: inline-block; padding: 4px 8px; text-decoration: none; background-color: #17a2b8; color: white; border-radius: 4px; font-size: 14px;" 
                                           title="Editar">
                                            ✏️
                                        </a>
                                        <a href="index.php?view=gerenciar_alunos&action=resetar_senha&id=<?= $aluno['id'] ?? $aluno['aluno_id'] ?? '' ?>" 
                                           onclick="return confirm('Deseja resetar a senha deste aluno? A nova senha será 123456.');" 
                                           style="display: inline-block; padding: 4px 8px; text-decoration: none; background-color: #f39c12; color: white; border-radius: 4px; font-size: 14px;" 
                                           title="Resetar Senha">
                                            🔑
                                        </a>
                                        <a href="index.php?view=gerenciar_alunos&action=deletar&id=<?= $aluno['id'] ?? $aluno['aluno_id'] ?? '' ?>" 
                                           onclick="return confirm('Deseja realmente excluir este aluno? Esta ação não pode ser desfeita.');" 
                                           style="display: inline-block; padding: 4px 8px; text-decoration: none; background-color: #dc3545; color: white; border-radius: 4px; font-size: 14px;" 
                                           title="Excluir">
                                            🗑️
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 4px; text-align: center; color: #6c757d; font-size: 14px;">
                Total: <strong><?= count($dados_alunos) ?></strong> aluno(s) cadastrado(s)
            </div>
        <?php endif; ?>
    </div>

</div>

<style>
    .alunos-table tr:hover {
        background-color: #f8f9fa;
    }
    
    .btn-primary:hover {
        background: #1a252f !important;
    }
    
    @media (max-width: 768px) {
        .alunos-table {
            font-size: 14px;
        }
        .alunos-table th, 
        .alunos-table td {
            padding: 8px 10px;
        }
        .card {
            padding: 15px !important;
        }
    }
</style>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>