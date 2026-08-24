<?php
/**
 * equacoes.php
 * Gerenciamento de equações - Professor
 */
$page_title = 'Gerenciar Equações - EquaTEA';

include_once __DIR__ . '/../partials/header.php';
include_once __DIR__ . '/../partials/menu_professor.php';

$mensagem_sucesso = $_SESSION['admin_success'] ?? null;
$mensagem_erro = $_SESSION['admin_error'] ?? null;
unset($_SESSION['admin_success']);
unset($_SESSION['admin_error']);
?>

<main class="container gerenciar-equacoes">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 20px;">
        <h1>📝 Gerenciar Equações</h1>
        <a href="?view=editar_equacao" class="btn-primary">➕ Nova Equação</a>
    </div>

    <?php if ($mensagem_sucesso): ?>
        <div class="alert-success">✅ <?php echo htmlspecialchars($mensagem_sucesso); ?></div>
    <?php endif; ?>

    <?php if ($mensagem_erro): ?>
        <div class="alert-error">❌ <?php echo htmlspecialchars($mensagem_erro); ?></div>
    <?php endif; ?>

    <div class="tabela-wrapper">
        <table class="equacoes-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Equação</th>
                    <th>Dificuldade</th>
                    <th>Solução</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($dados_equacoes) && is_array($dados_equacoes)): ?>
                    <?php foreach ($dados_equacoes as $eq): ?>
                        <tr>
                            <td><?php echo $eq['id']; ?></td>
                            <td>
                                <code>
                                    <?php 
                                        $a = (int)($eq['a'] ?? 1);
                                        $b = (int)($eq['b'] ?? 0);
                                        $c = (int)($eq['c'] ?? 0);
                                        $termoA = ($a === 1) ? 'x' : (($a === -1) ? '-x' : "{$a}x");
                                        $sinalB = ($b >= 0) ? '+ ' . $b : '- ' . abs($b);
                                        echo htmlspecialchars("{$termoA} {$sinalB} = {$c}");
                                    ?>
                                </code>
                            </td>
                            <td>
                                <span class="badge-dificuldade <?php echo strtolower($eq['dificuldade'] ?? 'facil'); ?>">
                                    <?php echo ucfirst($eq['dificuldade'] ?? 'Fácil'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-success">x = <?php echo $eq['solucao'] ?? '?'; ?></span>
                            </td>
                            <td>
                                <div class="acoes-cell">
                                    <a href="?view=editar_equacao&id=<?php echo $eq['id']; ?>" 
                                       class="btn-edit" title="Editar">
                                        ✏️
                                    </a>
                                    <a href="?view=gerenciar_equacoes&action=deletar&id=<?php echo $eq['id']; ?>" 
                                       class="btn-delete" title="Excluir"
                                       onclick="return confirm('Deseja realmente excluir esta equação?')">
                                        🗑️
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 40px; color: #888;">
                            <div style="font-size: 48px; margin-bottom: 10px;">📭</div>
                            <p style="font-size: 16px; font-weight: 500;">Nenhuma equação cadastrada.</p>
                            <p style="font-size: 14px; margin-top: 5px;">Clique em "Nova Equação" para começar a cadastrar.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<style>
    .gerenciar-equacoes {
        padding: 20px 0;
        max-width: 900px;
        margin: 0 auto;
    }
    
    .gerenciar-equacoes h1 {
        color: #2c3e50;
        margin: 0;
    }
    
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
    
    .tabela-wrapper {
        overflow-x: auto;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    
    .equacoes-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    
    .equacoes-table th {
        background: #f8f9fa;
        padding: 12px 16px;
        text-align: left;
        font-weight: 600;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
    }
    
    .equacoes-table td {
        padding: 10px 16px;
        border-bottom: 1px solid #f1f3f5;
        color: #495057;
        vertical-align: middle;
    }
    
    .equacoes-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .equacoes-table code {
        background: #f8f9fa;
        padding: 2px 8px;
        border-radius: 4px;
        font-family: 'Courier New', monospace;
        font-size: 14px;
    }
    
    .badge-dificuldade {
        display: inline-block;
        padding: 3px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .badge-dificuldade.facil {
        background: #d4edda;
        color: #155724;
    }
    
    .badge-dificuldade.medio {
        background: #fff3cd;
        color: #856404;
    }
    
    .badge-dificuldade.dificil {
        background: #f8d7da;
        color: #721c24;
    }
    
    .badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .badge-success {
        background: #d4edda;
        color: #155724;
    }
    
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
    
    .btn-delete {
        background: #f8d7da;
        color: #721c24;
    }
    
    .btn-delete:hover {
        background: #f5c6cb;
    }
    
    @media (max-width: 768px) {
        .equacoes-table {
            font-size: 13px;
        }
        
        .equacoes-table th,
        .equacoes-table td {
            padding: 8px 10px;
        }
        
        .acoes-cell {
            flex-direction: column;
            gap: 4px;
        }
    }
</style>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>