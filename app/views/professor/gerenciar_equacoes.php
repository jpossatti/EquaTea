<?php
/**
 * gerenciar_equacoes.php
 * View para gestão de equações - Versão Completa Corrigida
 */

// Garante que as variáveis existem
if (!isset($dados_equacoes) && isset($equacoes)) {
    $dados_equacoes = $equacoes;
}
if (!isset($dados_equacoes)) {
    $dados_equacoes = [];
}

$page_title = 'Gerenciar Equações - EquaTEA';
$view = 'gerenciar_equacoes';

// Inclui o header e menu do sistema
include_once __DIR__ . '/../partials/header.php';
include_once __DIR__ . '/../partials/menu_professor.php';

// Garante que a variável existe para a tabela
$dados_equacoes = $dados_equacoes ?? [];
?>

<div class="container" style="max-width: 950px; margin: 30px auto; padding: 0 15px;">
    
    <h1 style="text-align: center; color: #2c3e50;">📐 Gerenciar Equações</h1>

    <!-- Mensagens de Alerta -->
    <?php if (!empty($_SESSION['admin_success'])): ?>
        <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            ✅ <?php echo htmlspecialchars($_SESSION['admin_success']); unset($_SESSION['admin_success']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['admin_error'])): ?>
        <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            ⚠️ <?php echo htmlspecialchars($_SESSION['admin_error']); unset($_SESSION['admin_error']); ?>
        </div>
    <?php endif; ?>

    <!-- Formulário de Cadastro de Equação -->
    <div class="card" style="background: #fff; border-radius: 8px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px;">
        <h2 style="margin-top: 0; text-align: center; color: #2c3e50;">➕ Cadastrar Nova Equação</h2>
        
        <form method="POST" action="index.php?view=cadastrar_equacao" id="formEquacao">
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                <div>
                    <label for="a"><strong>Coeficiente a *</strong></label>
                    <input type="number" id="a" name="a" min="-20" max="20" placeholder="Ex: 7" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;" oninput="atualizarPreview()">
                    <small style="color: #6c757d;">Não pode ser zero</small>
                </div>
                <div>
                    <label for="b"><strong>Coeficiente b *</strong></label>
                    <input type="number" id="b" name="b" min="-20" max="20" placeholder="Ex: 8" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;" oninput="atualizarPreview()">
                </div>
                <div>
                    <label for="c"><strong>Coeficiente c *</strong></label>
                    <input type="number" id="c" name="c" min="-20" max="20" placeholder="Ex: 43" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;" oninput="atualizarPreview()">
                </div>
                <div>
                    <label for="dificuldade"><strong>Dificuldade *</strong></label>
                    <select id="dificuldade" name="dificuldade" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;">
                        <option value="facil">Fácil</option>
                        <option value="medio">Médio</option>
                        <option value="dificil">Difícil</option>
                    </select>
                </div>
            </div>

            <!-- Painel de Pré-visualização Dinâmica -->
            <div class="preview-box" style="background: #f8f9fa; border: 2px dashed #cbd5e1; border-radius: 8px; padding: 15px; margin-bottom: 20px; text-align: center;">
                <span style="color: #64748b; margin-right: 10px;">Pré-visualização:</span>
                <strong id="previewEquacao" style="font-size: 1.2rem; color: #1e293b;">ax + b = c</strong>
                <span id="previewResultado" style="margin-left: 15px; padding: 4px 10px; border-radius: 4px; font-weight: bold; background-color: #e2e8f0; color: #475569;">
                    x = ?
                </span>
            </div>

            <div style="text-align: center; gap: 10px; display: flex; justify-content: center; flex-wrap: wrap;">
                <button type="submit" style="background-color: #27ae60; color: white; border: none; padding: 10px 25px; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 1rem; transition: background 0.2s;">
                    ✔ Cadastrar Equação
                </button>
                <button type="reset" onclick="setTimeout(atualizarPreview, 50)" style="background-color: #95a5a6; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 1rem; transition: background 0.2s;">
                    🔄 Limpar
                </button>
            </div>
        </form>
    </div>

    <!-- Tabela com a Lista de Equações -->
    <div class="card" style="background: #fff; border-radius: 8px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <h2 style="margin-top: 0; text-align: center; color: #2c3e50;">📋 Lista de Equações</h2>

        <?php if (empty($dados_equacoes)): ?>
            <div style="text-align: center; padding: 40px; color: #6c757d;">
                <p style="font-size: 48px; margin-bottom: 10px;">📭</p>
                <p style="font-size: 18px;">Nenhuma equação cadastrada ainda.</p>
                <p>Utilize o formulário acima para cadastrar uma nova equação.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="list-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                            <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #495057;">ID</th>
                            <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #495057;">Equação</th>
                            <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #495057;">Solução (x)</th>
                            <th style="padding: 12px 16px; text-align: left; font-weight: 600; color: #495057;">Dificuldade</th>
                            <th style="padding: 12px 16px; text-align: center; font-weight: 600; color: #495057;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dados_equacoes as $eq): ?>
                            <?php
                                $a = (int)($eq['a'] ?? 1);
                                $b = (int)($eq['b'] ?? 0);
                                $c = (int)($eq['c'] ?? 0);
                                $solucao = $eq['solucao'] ?? '?';
                                
                                // Formata a equação corretamente
                                if ($a === 1) {
                                    $termoA = 'x';
                                } elseif ($a === -1) {
                                    $termoA = '-x';
                                } else {
                                    $termoA = $a . 'x';
                                }
                                
                                if ($b > 0) {
                                    $termoB = ' + ' . $b;
                                } elseif ($b < 0) {
                                    $termoB = ' - ' . abs($b);
                                } else {
                                    $termoB = '';
                                }
                                
                                $equacaoFormatada = $termoA . $termoB . ' = ' . $c;
                                
                                $dificuldade = $eq['dificuldade'] ?? 'facil';
                                $badgeColors = [
                                    'facil' => '#28a745',
                                    'medio' => '#ffc107',
                                    'dificil' => '#dc3545'
                                ];
                                $color = $badgeColors[$dificuldade] ?? '#6c757d';
                                
                                $dificuldadeLabels = [
                                    'facil' => 'Fácil',
                                    'medio' => 'Médio',
                                    'dificil' => 'Difícil'
                                ];
                            ?>
                            <tr style="border-bottom: 1px solid #e9ecef; transition: background-color 0.2s;">
                                <td style="padding: 10px 16px; color: #495057;"><?= htmlspecialchars($eq['id'] ?? '') ?></td>
                                <td style="padding: 10px 16px; color: #495057;">
                                    <strong><?= htmlspecialchars($equacaoFormatada) ?></strong>
                                </td>
                                <td style="padding: 10px 16px; color: #495057;">
                                    <strong>x = <?= htmlspecialchars($solucao) ?></strong>
                                </td>
                                <td style="padding: 10px 16px;">
                                    <span style="display: inline-block; padding: 4px 12px; border-radius: 12px; background-color: <?= $color ?>; color: white; font-size: 12px; font-weight: 500;">
                                        <?= $dificuldadeLabels[$dificuldade] ?? ucfirst($dificuldade) ?>
                                    </span>
                                </td>
                                <td style="padding: 10px 16px; text-align: center;">
                                    <a href="index.php?view=editar_equacao&id=<?= $eq['id'] ?>" 
                                       style="display: inline-block; padding: 4px 8px; margin: 0 2px; text-decoration: none; background-color: #17a2b8; color: white; border-radius: 4px; font-size: 14px; transition: opacity 0.2s;" 
                                       title="Editar"
                                       onmouseover="this.style.opacity='0.8'"
                                       onmouseout="this.style.opacity='1'">
                                        ✏️
                                    </a>
                                    <a href="index.php?view=gerenciar_equacoes&action=deletar&id=<?= $eq['id'] ?>" 
                                       onclick="return confirm('Deseja realmente excluir esta equação? Esta ação não pode ser desfeita.');" 
                                       style="display: inline-block; padding: 4px 8px; margin: 0 2px; text-decoration: none; background-color: #dc3545; color: white; border-radius: 4px; font-size: 14px; transition: opacity 0.2s;" 
                                       title="Excluir"
                                       onmouseover="this.style.opacity='0.8'"
                                       onmouseout="this.style.opacity='1'">
                                        🗑️
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 4px; text-align: center; color: #6c757d; font-size: 14px;">
                Total: <strong><?= count($dados_equacoes) ?></strong> equações cadastradas
            </div>
        <?php endif; ?>
    </div>

    <div style="text-align: center; margin-top: 10px;">
        <a href="index.php?view=professor/dashboard" style="display: inline-block; padding: 10px 20px; background-color: #6c757d; color: white; border-radius: 5px; text-decoration: none; font-weight: 500; transition: background 0.2s;" onmouseover="this.style.backgroundColor='#5a6268'" onmouseout="this.style.backgroundColor='#6c757d'">
            ⬅ Voltar para Dashboard
        </a>
    </div>

</div>

<!-- Script JS para pré-visualização e cálculo em tempo real da equação -->
<script>
    function atualizarPreview() {
        const a = parseFloat(document.getElementById('a').value);
        const b = parseFloat(document.getElementById('b').value);
        const c = parseFloat(document.getElementById('c').value);

        const elemEq = document.getElementById('previewEquacao');
        const elemRes = document.getElementById('previewResultado');

        if (isNaN(a) || isNaN(b) || isNaN(c) || a === 0) {
            elemEq.innerText = "ax + b = c";
            elemRes.innerText = "x = ?";
            elemRes.style.backgroundColor = "#e2e8f0";
            elemRes.style.color = "#475569";
            return;
        }

        // Formata a equação
        let eqText = '';
        if (a === 1) {
            eqText = 'x';
        } else if (a === -1) {
            eqText = '-x';
        } else {
            eqText = a + 'x';
        }

        if (b > 0) {
            eqText += ' + ' + b;
        } else if (b < 0) {
            eqText += ' - ' + Math.abs(b);
        }

        eqText += ' = ' + c;
        elemEq.innerText = eqText;

        // Calcula a solução
        const x = (c - b) / a;

        if (Number.isInteger(x)) {
            elemRes.innerText = '✔ x = ' + x;
            elemRes.style.backgroundColor = "#d1e7dd";
            elemRes.style.color = "#0f5132";
        } else {
            elemRes.innerText = '⚠️ x = ' + x.toFixed(2) + ' (não inteiro)';
            elemRes.style.backgroundColor = "#fff3cd";
            elemRes.style.color = "#664d03";
        }
    }

    // Executa a pré-visualização ao carregar a página
    document.addEventListener('DOMContentLoaded', function() {
        atualizarPreview();
    });
</script>

<!-- Estilos adicionais para melhor apresentação -->
<style>
    .list-table tr:hover {
        background-color: #f8f9fa;
    }
    
    .btn-acao {
        transition: transform 0.2s, opacity 0.2s;
    }
    
    .btn-acao:hover {
        transform: scale(1.1);
        opacity: 0.8;
    }
    
    .alert-success,
    .alert-danger {
        border-radius: 6px;
        padding: 12px 16px;
        margin-bottom: 20px;
        font-weight: 500;
    }
    
    @media (max-width: 768px) {
        .list-table {
            font-size: 14px;
        }
        .list-table th, 
        .list-table td {
            padding: 8px 10px;
        }
        .card {
            padding: 15px !important;
        }
    }
    
    @media (max-width: 480px) {
        .list-table {
            font-size: 12px;
        }
        .list-table th, 
        .list-table td {
            padding: 6px 8px;
        }
    }
</style>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>