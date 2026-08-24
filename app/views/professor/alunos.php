<?php
/**
 * alunos.php
 * Gerenciamento de alunos - Professor
 */
$page_title = 'Gerenciar Alunos - EquaTEA';

include_once __DIR__ . '/../partials/header.php';
include_once __DIR__ . '/../partials/menu_professor.php';

// Mensagens de feedback
$mensagem_sucesso = $_SESSION['admin_success'] ?? null;
$mensagem_erro = $_SESSION['admin_error'] ?? null;
unset($_SESSION['admin_success']);
unset($_SESSION['admin_error']);
?>

<main class="container gerenciar-alunos">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 20px;">
        <h1>👨‍🎓 Gerenciar Alunos</h1>
        <a href="?view=gerenciar_alunos&action=cadastrar" class="btn-primary">
            ➕ Novo Aluno
        </a>
    </div>

    <?php if ($mensagem_sucesso): ?>
        <div class="alert-success">✅ <?php echo htmlspecialchars($mensagem_sucesso); ?></div>
    <?php endif; ?>

    <?php if ($mensagem_erro): ?>
        <div class="alert-error">❌ <?php echo htmlspecialchars($mensagem_erro); ?></div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="filtros-section">
        <form method="GET" action="" class="filtros-form">
            <input type="hidden" name="view" value="gerenciar_alunos">
            <div class="filtro-grupo">
                <label for="buscar">🔍 Buscar:</label>
                <input type="text" id="buscar" name="buscar" placeholder="Nome do aluno..." value="<?php echo htmlspecialchars($_GET['buscar'] ?? ''); ?>">
            </div>
            <button type="submit" class="btn-filtrar">Buscar</button>
            <a href="?view=gerenciar_alunos" class="btn-limpar">Limpar</a>
        </form>
    </div>

    <!-- Tabela de Alunos -->
    <div class="tabela-wrapper">
        <table class="alunos-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Idade</th>
                    <th>Nível TEA</th>
                    <th>Escola</th>
                    <th>Turma</th>
                    <th>Equações</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($dados_alunos) && is_array($dados_alunos)): ?>
                    <?php 
                    $contador = 1;
                    $buscar = $_GET['buscar'] ?? '';
                    foreach ($dados_alunos as $aluno):
                        // Aplica filtro de busca se existir
                        if (!empty($buscar)) {
                            $nome = strtolower($aluno['nome'] ?? '');
                            $email = strtolower($aluno['email'] ?? '');
                            $buscarLower = strtolower($buscar);
                            if (strpos($nome, $buscarLower) === false && strpos($email, $buscarLower) === false) {
                                continue;
                            }
                        }
                    ?>
                        <tr>
                            <td><?php echo $contador++; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($aluno['nome'] ?? 'N/A'); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($aluno['email'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($aluno['idade'] ?? '-'); ?></td>
                            <td>
                                <?php 
                                    $nivel_tea = $aluno['nivel_tea'] ?? '';
                                    $nivelLabels = [
                                        'suporte1' => 'Suporte 1',
                                        'suporte2' => 'Suporte 2',
                                        'suporte3' => 'Suporte 3'
                                    ];
                                    echo htmlspecialchars($nivelLabels[$nivel_tea] ?? $nivel_tea);
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($aluno['escola'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($aluno['turma'] ?? '-'); ?></td>
                            <td>
                                <span class="badge badge-info">
                                    <?php echo $aluno['total_equacoes'] ?? 0; ?>
                                </span>
                            </td>
                            <td>
                                <div class="acoes-cell">
                                    <a href="?view=editar_aluno&id=<?php echo $aluno['id'] ?? $aluno['aluno_id'] ?? 0; ?>" 
                                       class="btn-edit" title="Editar">
                                        ✏️
                                    </a>
                                    <a href="?view=gerenciar_alunos&action=resetar_senha&id=<?php echo $aluno['id'] ?? $aluno['aluno_id'] ?? 0; ?>" 
                                       class="btn-reset" title="Resetar Senha"
                                       onclick="return confirm('Deseja resetar a senha deste aluno?')">
                                        🔑
                                    </a>
                                    <a href="?view=gerenciar_alunos&action=deletar&id=<?php echo $aluno['id'] ?? $aluno['aluno_id'] ?? 0; ?>" 
                                       class="btn-delete" title="Excluir"
                                       onclick="return confirm('Deseja realmente excluir este aluno? Esta ação não pode ser desfeita.')">
                                        🗑️
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align:center; padding: 40px; color: #888;">
                            <div style="font-size: 48px; margin-bottom: 10px;">📭</div>
                            <p style="font-size: 16px; font-weight: 500;">Nenhum aluno cadastrado.</p>
                            <p style="font-size: 14px; margin-top: 5px;">Clique em "Novo Aluno" para começar a cadastrar.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Estatísticas -->
    <?php if (!empty($dados_alunos) && is_array($dados_alunos)): 
        $total_alunos = count($dados_alunos);
        $total_equacoes = 0;
        foreach ($dados_alunos as $a) {
            $total_equacoes += ($a['total_equacoes'] ?? 0);
        }
    ?>
        <div class="resumo-alunos">
            <div class="resumo-item">
                <span class="resumo-numero"><?php echo $total_alunos; ?></span>
                <span class="resumo-label">Total de Alunos</span>
            </div>
            <div class="resumo-item">
                <span class="resumo-numero" style="color: #3498db;"><?php echo $total_equacoes; ?></span>
                <span class="resumo-label">Equações Resolvidas</span>
            </div>
            <div class="resumo-item">
                <span class="resumo-numero" style="color: #2ecc71;"><?php echo number_format($total_alunos > 0 ? $total_equacoes / $total_alunos : 0, 1); ?></span>
                <span class="resumo-label">Média por Aluno</span>
            </div>
        </div>
    <?php endif; ?>
</main>

<style>
    .gerenciar-alunos {
        padding: 20px 0;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .gerenciar-alunos h1 {
        color: #2c3e50;
        margin: 0;
    }
    
    /* Botão Principal */
    .btn-primary {
        display: inline-block;
        padding: 10px 24px;
        background: #2c3e50;
        color: #fff;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 500;
        transition: background 0.2s;
    }
    
    .btn-primary:hover {
        background: #1a252f;
    }
    
    /* Alertas */
    .alert-success {
        background: #d4edda;
        color: #155724;
        padding: 12px 16px;
        border-radius: 6px;
        margin-bottom: 20px;
        border: 1px solid #c3e6cb;
    }
    
    .alert-error {
        background: #f8d7da;
        color: #721c24;
        padding: 12px 16px;
        border-radius: 6px;
        margin-bottom: 20px;
        border: 1px solid #f5c6cb;
    }
    
    /* Filtros */
    .filtros-section {
        background: #fff;
        padding: 16px 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        margin-bottom: 20px;
    }
    
    .filtros-form {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: flex-end;
    }
    
    .filtro-grupo {
        display: flex;
        flex-direction: column;
        gap: 4px;
        flex: 1;
        min-width: 150px;
    }
    
    .filtro-grupo label {
        font-weight: 500;
        font-size: 13px;
        color: #555;
    }
    
    .filtro-grupo input {
        padding: 10px 12px;
        border: 2px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        background: #fff;
    }
    
    .filtro-grupo input:focus {
        border-color: #3498db;
        outline: none;
    }
    
    .btn-filtrar {
        padding: 10px 24px;
        background: #3498db;
        color: #fff;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        transition: background 0.2s;
    }
    
    .btn-filtrar:hover {
        background: #2980b9;
    }
    
    .btn-limpar {
        padding: 10px 20px;
        background: #f8f9fa;
        color: #555;
        border: 2px solid #ddd;
        border-radius: 6px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        transition: background 0.2s;
    }
    
    .btn-limpar:hover {
        background: #e9ecef;
    }
    
    /* Tabela */
    .tabela-wrapper {
        overflow-x: auto;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    
    .alunos-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    
    .alunos-table th {
        background: #f8f9fa;
        padding: 12px 16px;
        text-align: left;
        font-weight: 600;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
    }
    
    .alunos-table td {
        padding: 10px 16px;
        border-bottom: 1px solid #f1f3f5;
        color: #495057;
        vertical-align: middle;
    }
    
    .alunos-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    /* Badges */
    .badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .badge-info {
        background: #cce5ff;
        color: #004085;
    }
    
    /* Ações */
    .acoes-cell {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }
    
    .acoes-cell a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 16px;
        transition: background 0.2s;
    }
    
    .btn-edit {
        background: #cce5ff;
        color: #004085;
    }
    
    .btn-edit:hover {
        background: #b3d9ff;
    }
    
    .btn-reset {
        background: #fff3cd;
        color: #856404;
    }
    
    .btn-reset:hover {
        background: #ffecb5;
    }
    
    .btn-delete {
        background: #f8d7da;
        color: #721c24;
    }
    
    .btn-delete:hover {
        background: #f5c6cb;
    }
    
    /* Resumo */
    .resumo-alunos {
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
        margin-top: 20px;
        padding: 15px 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    
    .resumo-item {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .resumo-numero {
        font-size: 24px;
        font-weight: 700;
        color: #2c3e50;
    }
    
    .resumo-label {
        font-size: 13px;
        color: #888;
        margin-top: 2px;
    }
    
    /* Responsivo */
    @media (max-width: 768px) {
        .filtros-form {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filtro-grupo {
            min-width: 100%;
        }
        
        .alunos-table {
            font-size: 13px;
        }
        
        .alunos-table th,
        .alunos-table td {
            padding: 8px 10px;
        }
        
        .acoes-cell {
            flex-direction: column;
            gap: 4px;
        }
        
        .resumo-alunos {
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
    }
</style>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>