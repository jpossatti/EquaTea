<?php
/**
 * dashboard.php
 * Dashboard do professor - COM ESTATÍSTICAS COMPLETAS
 */
$page_title = 'Dashboard Professor - EquaTEA';
$nome_professor = $_SESSION['usuario_nome'] ?? 'Professor';

// Estatísticas baseadas nos dados vindos do Controller
$total_alunos = count($dados_alunos ?? []);
$total_equacoes = count($dados_equacoes ?? []);
$total_erros = $total_erros ?? 0;
$total_equacoes_resolvidas = $total_equacoes_resolvidas ?? 0;
$total_tentativas = $total_tentativas ?? 0;

include_once __DIR__ . '/../partials/header.php';
include_once __DIR__ . '/../partials/menu_professor.php';
?>

<main class="container professor-dashboard">
    <h1>👋 Olá, Prof. <?php echo htmlspecialchars($nome_professor); ?>!</h1>
    <p class="subtitle">Acompanhe o progresso dos seus alunos</p>

    <!-- Cards de Estatísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👨‍🎓</div>
            <div>
                <span class="stat-value"><?php echo $total_alunos; ?></span>
                <span class="stat-label">Alunos</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📐</div>
            <div>
                <span class="stat-value"><?php echo $total_equacoes; ?></span>
                <span class="stat-label">Equações</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div>
                <span class="stat-value"><?php echo $total_equacoes_resolvidas; ?></span>
                <span class="stat-label">Equações Resolvidas</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🔄</div>
            <div>
                <span class="stat-value"><?php echo $total_tentativas; ?></span>
                <span class="stat-label">Tentativas</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">❌</div>
            <div>
                <span class="stat-value"><?php echo $total_erros; ?></span>
                <span class="stat-label">Erros</span>
            </div>
        </div>
    </div>

    <!-- Ações Rápidas -->
<div class="acoes-rapidas">
    <a href="index.php?view=gerenciar_alunos" class="acao-card">
        <span class="acao-icon">👨‍🎓</span>
        Gerenciar Alunos
    </a>
    <a href="index.php?view=gerenciar_equacoes" class="acao-card">
        <span class="acao-icon">📐</span>
        Gerenciar Equações
    </a>
    <a href="index.php?view=relatorio" class="acao-card">
        <span class="acao-icon">📈</span>
        Relatórios
    </a>
</div>

    <!-- Tabela de Alunos -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 30px; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
        <h2 style="margin: 0;">👨‍🎓 Alunos</h2>
        <a href="index.php?view=gerenciar_alunos" class="btn-ver-todos">Ver todos →</a>
    </div>

    <?php if (!empty($dados_alunos) && is_array($dados_alunos)): ?>
        <table class="alunos-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Idade</th>
                    <th>Nível TEA</th>
                    <th>Resolvidas</th>
                    <th>Tentativas</th>
                    <th>Taxa de Acerto</th>
                    <th>Nível</th>
                    <th>Última Atividade</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dados_alunos as $aluno): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($aluno['nome'] ?? 'N/A'); ?></strong>
                        </td>
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
                        <td>
                            <span class="badge badge-success">
                                <?php echo $aluno['total_equacoes'] ?? 0; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-info">
                                <?php echo $aluno['total_tentativas'] ?? 0; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?php 
                                $taxa = (int)str_replace('%', '', $aluno['taxa_acerto'] ?? '0');
                                echo $taxa >= 70 ? 'badge-success' : ($taxa >= 40 ? 'badge-warning' : 'badge-danger');
                            ?>">
                                <?php echo $aluno['taxa_acerto'] ?? '0%'; ?>
                            </span>
                        </td>
                        <td>
                            <span class="nivel-badge <?php echo strtolower(str_replace(' ', '-', $aluno['nivel'] ?? 'básico')); ?>">
                                <?php echo htmlspecialchars($aluno['nivel'] ?? 'Básico'); ?>
                            </span>
                        </td>
                        <td style="font-size: 12px; color: #888;">
                            <?php echo $aluno['ultima_atividade'] ?? '-'; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 10px; text-align: right; font-size: 14px; color: #888;">
            Total: <strong><?php echo count($dados_alunos); ?></strong> alunos
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 30px; background: #fff; border-radius: 8px; color: #6c757d; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
            <p style="font-size: 16px;">📭 Nenhum aluno encontrado.</p>
            <p>Clique em "Gerenciar Alunos" para cadastrar novos alunos.</p>
        </div>
    <?php endif; ?>
</main>

<style>
    .professor-dashboard { 
        padding: 20px 0; 
    }
    
    .subtitle { 
        color: #7f8c8d; 
        font-size: 18px; 
        margin-top: -8px; 
    }
    
    /* Cards de Estatísticas */
    .stats-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); 
        gap: 16px; 
        margin: 20px 0; 
    }
    
    .stat-card { 
        background: #fff; 
        padding: 16px 20px; 
        border-radius: 8px; 
        box-shadow: 0 2px 8px rgba(0,0,0,0.06); 
        display: flex;
        align-items: center;
        gap: 15px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .stat-icon {
        font-size: 28px;
    }
    
    .stat-card > div {
        display: flex;
        flex-direction: column;
    }
    
    .stat-value { 
        font-size: 28px; 
        font-weight: 700; 
        color: #2c3e50; 
        line-height: 1.2;
    }
    
    .stat-label { 
        font-size: 13px; 
        color: #888; 
        margin-top: 2px;
    }
    
    /* Ações Rápidas */
    .acoes-rapidas { 
        display: grid; 
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
        gap: 12px; 
        margin: 20px 0 30px; 
    }
    
    .acao-card { 
        background: #fff; 
        padding: 20px; 
        border-radius: 8px; 
        text-align: center; 
        text-decoration: none; 
        color: #2c3e50; 
        box-shadow: 0 2px 8px rgba(0,0,0,0.06); 
        font-weight: 500; 
        transition: transform 0.2s, box-shadow 0.2s;
        border: 2px solid transparent;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }
    
    .acao-card:hover { 
        transform: translateY(-3px); 
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        border-color: #3498db;
    }
    
    .acao-icon {
        font-size: 32px;
    }
    
    .btn-ver-todos {
        display: inline-block;
        padding: 6px 16px;
        background: #f8f9fa;
        color: #555;
        border-radius: 4px;
        text-decoration: none;
        font-size: 14px;
        transition: background 0.2s;
    }
    
    .btn-ver-todos:hover {
        background: #e9ecef;
    }
    
    /* Tabela de Alunos */
    .alunos-table { 
        width: 100%; 
        border-collapse: collapse; 
        background: #fff; 
        border-radius: 8px; 
        overflow: hidden; 
        box-shadow: 0 2px 8px rgba(0,0,0,0.06); 
        margin-top: 10px;
    }
    
    .alunos-table th { 
        background: #f8f9fa; 
        padding: 12px 16px; 
        text-align: left; 
        font-weight: 600; 
        color: #495057;
        border-bottom: 2px solid #dee2e6;
        font-size: 13px;
        white-space: nowrap;
    }
    
    .alunos-table td { 
        padding: 10px 16px; 
        border-bottom: 1px solid #f1f3f5;
        color: #495057;
        font-size: 14px;
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
    
    .badge-success {
        background: #d4edda;
        color: #155724;
    }
    
    .badge-info {
        background: #cce5ff;
        color: #004085;
    }
    
    .badge-warning {
        background: #fff3cd;
        color: #856404;
    }
    
    .badge-danger {
        background: #f8d7da;
        color: #721c24;
    }
    
    /* Nível Badge */
    .nivel-badge {
        display: inline-block;
        padding: 3px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .nivel-badge.básico {
        background: #e9ecef;
        color: #6c757d;
    }
    
    .nivel-badge.iniciante-avançado {
        background: #d4edda;
        color: #155724;
    }
    
    .nivel-badge.intermediário {
        background: #cce5ff;
        color: #004085;
    }
    
    .nivel-badge.intermediário-avançado {
        background: #fff3cd;
        color: #856404;
    }
    
    .nivel-badge.avançado {
        background: #f8d7da;
        color: #721c24;
    }
    
    /* Responsivo */
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .acoes-rapidas {
            grid-template-columns: 1fr;
        }
        
        .alunos-table {
            font-size: 13px;
        }
        
        .alunos-table th,
        .alunos-table td {
            padding: 8px 10px;
        }
        
        .alunos-table th {
            font-size: 11px;
        }
        
        .stat-value {
            font-size: 22px;
        }
    }
    
    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .alunos-table {
            font-size: 12px;
        }
        
        .alunos-table th,
        .alunos-table td {
            padding: 6px 8px;
        }
        
        .badge {
            font-size: 10px;
            padding: 2px 8px;
        }
    }
</style>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>