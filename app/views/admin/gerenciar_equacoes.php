<?php
/**
 * app/views/admin/gerenciar_equacoes.php
 * Gerenciamento de Equações - Versão Admin
 */

$nome_admin = $_SESSION['usuario_nome'] ?? 'Administrador';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EquaTEA - Gerenciar Equações</title>
    <style>
        :root {
            --primary-color: #1a237e;
            --accent-blue: #3498db;
            --bg-light: #f4f7f6;
            --card-bg: #ffffff;
            --text-color: #333333;
            --border-color: #e0e0e0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            color: var(--text-color);
        }

        .admin-header {
            background: linear-gradient(135deg, #1a237e, #283593);
            color: white;
            padding: 15px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .admin-header .logo { font-size: 1.5rem; font-weight: bold; }
        .admin-header .logo span { color: #64b5f6; }
        .admin-header .logo small { font-size: 0.7rem; opacity: 0.8; }

        .admin-header .user-info { display: flex; align-items: center; gap: 15px; }
        .admin-header .btn-logout {
            color: white;
            text-decoration: none;
            background: #e74c3c;
            padding: 6px 14px;
            border-radius: 4px;
            font-size: 0.85rem;
            transition: background 0.3s;
        }
        .admin-header .btn-logout:hover { background: #c0392b; }

        .admin-nav {
            background: white;
            border-bottom: 1px solid var(--border-color);
            padding: 10px 24px;
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .admin-nav a {
            text-decoration: none;
            color: #555;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .admin-nav a:hover { background: #e8eaf6; color: var(--primary-color); }
        .admin-nav a.active { background: var(--primary-color); color: white; }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 15px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .page-header h1 {
            color: var(--primary-color);
            font-size: 2rem;
        }

        .page-header .subtitle {
            color: #6c757d;
            font-size: 1rem;
        }

        .card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin-bottom: 20px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .card-header h3 { 
            margin: 0; 
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .table-responsive { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        th, td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--border-color);
            text-align: left;
        }

        th {
            background: #f8f9fa;
            color: #555;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tbody tr:hover { background: #f8f9fa; }

        .equation-display {
            font-family: 'Courier New', monospace;
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-facil { background: #d4edda; color: #155724; }
        .badge-medio { background: #fff3cd; color: #856404; }
        .badge-dificil { background: #f8d7da; color: #721c24; }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 6px 14px;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.8rem;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-action:hover { transform: scale(1.02); }

        .btn-action.btn-delete { 
            background: #dc3545; 
            color: white; 
        }
        .btn-action.btn-delete:hover { background: #c82333; }

        .btn-action.btn-edit { 
            background: #17a2b8; 
            color: white; 
        }
        .btn-action.btn-edit:hover { background: #138496; }

        .btn-action.btn-add {
            background: #28a745;
            color: white;
            padding: 8px 20px;
            font-size: 0.85rem;
        }

        .btn-action.btn-add:hover { background: #218838; }

        .btn-action.btn-back { 
            background: #6c757d; 
            color: white; 
        }
        .btn-action.btn-back:hover { background: #5a6268; }

        .btn-action.btn-submit {
            background: var(--accent-blue);
            color: white;
            padding: 10px 24px;
            font-size: 0.9rem;
        }

        .btn-action.btn-submit:hover { background: #2980b9; }

        .btn-action.btn-cancel {
            background: #6c757d;
            color: white;
        }

        .btn-action.btn-cancel:hover { background: #5a6268; }

        .alert {
            padding: 12px 18px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success { 
            background: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb; 
        }
        .alert-error { 
            background: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb; 
        }

        /* Formulário */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 0.9rem;
            color: #495057;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 0.9rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 2px rgba(52,152,219,0.2);
        }

        .form-group small {
            color: #6c757d;
            font-size: 0.75rem;
        }

        .preview-box {
            background: #f8f9fa;
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }

        .preview-box .label {
            color: #64748b;
            margin-right: 10px;
        }

        .preview-box .equation {
            font-family: 'Courier New', monospace;
            font-size: 1.2rem;
            color: #1e293b;
            font-weight: 600;
        }

        .preview-box .result {
            margin-left: 15px;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: bold;
            background-color: #e2e8f0;
            color: #475569;
        }

        .preview-box .result.success {
            background-color: #d4edda;
            color: #155724;
        }

        .preview-box .result.warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .empty-state .icon {
            font-size: 3rem;
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: 1rem;
            margin: 5px 0;
        }

        .total-info {
            margin-top: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .admin-header { flex-direction: column; text-align: center; }
            .admin-nav { flex-direction: column; align-items: center; }
            .form-grid { grid-template-columns: 1fr 1fr; }
            .card-header { flex-direction: column; align-items: stretch; text-align: center; }
            table { font-size: 0.8rem; }
            th, td { padding: 6px 8px; }
            .card { padding: 15px; }
        }

        @media (max-width: 480px) {
            .admin-header .logo { font-size: 1.2rem; }
            .form-grid { grid-template-columns: 1fr; }
            .page-header h1 { font-size: 1.5rem; }
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <header class="admin-header">
        <div class="logo">
            Equa<span>TEA</span> 
            <small>Gerenciar Equações</small>
        </div>
        <div class="user-info">
            <span class="user-name">👑 <?php echo htmlspecialchars($nome_admin); ?></span>
            <a href="index.php?view=logout" class="btn-logout" onclick="return confirm('Deseja realmente sair?');">
                🚪 Sair
            </a>
        </div>
    </header>

    <!-- NAVEGAÇÃO -->
    <nav class="admin-nav">
        <a href="index.php?view=admin/dashboard">📊 Dashboard</a>
        <a href="index.php?view=admin/gerenciar">👤 Gerenciar Usuários</a>
        <a href="index.php?view=admin/equacoes" class="active">📐 Gerenciar Equações</a>
        <a href="index.php?view=logout" onclick="return confirm('Deseja realmente sair?');">🚪 Sair</a>
    </nav>

    <!-- CONTEÚDO -->
    <div class="container">

        <div class="page-header">
            <h1>📐 Gerenciar Equações</h1>
            <p class="subtitle">Cadastre, edite e remova equações do sistema</p>
        </div>

        <!-- Mensagens de Alerta -->
        <?php if (isset($_SESSION['admin_success'])): ?>
            <div class="alert alert-success">
                ✅ <?php echo $_SESSION['admin_success']; unset($_SESSION['admin_success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['admin_error'])): ?>
            <div class="alert alert-error">
                ⚠️ <?php echo $_SESSION['admin_error']; unset($_SESSION['admin_error']); ?>
            </div>
        <?php endif; ?>

        <!-- FORMULÁRIO DE CADASTRO -->
        <div class="card">
            <div class="card-header">
                <h3>➕ Cadastrar Nova Equação</h3>
            </div>
            
            <form method="POST" action="index.php?view=admin/criar_equacao" id="formEquacao">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="a">Coeficiente a *</label>
                        <input type="number" id="a" name="a" min="-20" max="20" placeholder="Ex: 2" required oninput="previewFormula()">
                        <small>Não pode ser zero</small>
                    </div>
                    <div class="form-group">
                        <label for="b">Coeficiente b *</label>
                        <input type="number" id="b" name="b" min="-20" max="20" placeholder="Ex: 5" required oninput="previewFormula()">
                    </div>
                    <div class="form-group">
                        <label for="c">Coeficiente c *</label>
                        <input type="number" id="c" name="c" min="-20" max="20" placeholder="Ex: 11" required oninput="previewFormula()">
                    </div>
                    <div class="form-group">
                        <label for="dificuldade">Dificuldade *</label>
                        <select id="dificuldade" name="dificuldade" required>
                            <option value="facil">Fácil</option>
                            <option value="medio">Médio</option>
                            <option value="dificil">Difícil</option>
                        </select>
                    </div>
                </div>

                <!-- Pré-visualização -->
                <div class="preview-box">
                    <span class="label">Pré-visualização:</span>
                    <span class="equation" id="previewEquation">ax + b = c</span>
                    <span class="result" id="previewResult">x = ?</span>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-action btn-add">
                        ✔ Cadastrar Equação
                    </button>
                    <button type="reset" onclick="setTimeout(previewFormula, 50)" class="btn-action btn-cancel">
                        🔄 Limpar
                    </button>
                </div>
            </form>
        </div>

        <!-- LISTA DE EQUAÇÕES -->
        <div class="card">
            <div class="card-header">
                <h3>📋 Lista de Equações</h3>
                <span style="color: #6c757d; font-size: 0.9rem;">
                    Total: <strong><?php echo isset($equacoes) ? count($equacoes) : 0; ?></strong>
                </span>
            </div>

            <?php if (!empty($equacoes) && is_array($equacoes)): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Equação</th>
                                <th>Dificuldade</th>
                                <th>Solução (x)</th>
                                <th style="text-align: center;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($equacoes as $eq): ?>
                                <?php
                                    $a = (int)($eq['a'] ?? 1);
                                    $b = (int)($eq['b'] ?? 0);
                                    $c = (int)($eq['c'] ?? 0);
                                    $solucao = $eq['solucao'] ?? 0;
                                    $dificuldade = $eq['dificuldade'] ?? 'facil';
                                    
                                    // Formata a equação
                                    $termoA = ($a === 1) ? 'x' : (($a === -1) ? '-x' : "{$a}x");
                                    $sinalB = ($b >= 0) ? '+ ' . $b : '- ' . abs($b);
                                    $equacao = "{$termoA} {$sinalB} = {$c}";
                                    
                                    $dificuldadeLabels = [
                                        'facil' => 'Fácil',
                                        'medio' => 'Médio',
                                        'dificil' => 'Difícil'
                                    ];
                                ?>
                                <tr>
                                    <td><?php echo $eq['id']; ?></td>
                                    <td>
                                        <span class="equation-display">
                                            <?php echo htmlspecialchars($equacao); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $dificuldade; ?>">
                                            <?php echo $dificuldadeLabels[$dificuldade] ?? $dificuldade; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong>x = <?php echo $solucao; ?></strong>
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="index.php?view=admin/editar_equacao&id=<?php echo $eq['id']; ?>" 
                                           class="btn-action btn-edit"
                                           title="Editar equação">
                                            ✏️ Editar
                                        </a>
                                        <a href="index.php?view=admin/excluir_equacao&id=<?php echo $eq['id']; ?>" 
                                           class="btn-action btn-delete"
                                           onclick="return confirm('Tem certeza que deseja excluir esta equação? Esta ação não pode ser desfeita.');"
                                           title="Excluir equação">
                                            🗑️ Excluir
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="total-info">
                    Total: <strong><?php echo count($equacoes); ?></strong> equações cadastradas
                </div>

            <?php else: ?>
                <div class="empty-state">
                    <div class="icon">📭</div>
                    <p><strong>Nenhuma equação cadastrada ainda.</strong></p>
                    <p>Utilize o formulário acima para cadastrar a primeira equação.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- BOTÃO VOLTAR -->
        <div style="margin-top: 10px; text-align: center;">
            <a href="index.php?view=admin/dashboard" class="btn-action btn-back">
                ⬅ Voltar para Dashboard
            </a>
        </div>

    </div>

    <!-- SCRIPT PARA PRÉ-VISUALIZAÇÃO -->
    <script>
        function previewFormula() {
            const a = parseFloat(document.getElementById('a').value);
            const b = parseFloat(document.getElementById('b').value);
            const c = parseFloat(document.getElementById('c').value);

            const elemEq = document.getElementById('previewEquation');
            const elemRes = document.getElementById('previewResult');

            if (isNaN(a) || isNaN(b) || isNaN(c) || a === 0) {
                elemEq.innerText = "ax + b = c";
                elemRes.innerText = "x = ?";
                elemRes.className = "result";
                return;
            }

            // Formata a equação
            let eqText = '';
            if (a === 1) {
                eqText = 'x';
            } else if (a === -1) {
                eqText = '-x';
            } else {
                eqText = `${a}x`;
            }

            if (b > 0) {
                eqText += ` + ${b}`;
            } else if (b < 0) {
                eqText += ` - ${Math.abs(b)}`;
            }

            eqText += ` = ${c}`;
            elemEq.innerText = eqText;

            // Calcula a solução
            const x = (c - b) / a;

            if (Number.isInteger(x)) {
                elemRes.innerText = `✔ x = ${x}`;
                elemRes.className = "result success";
            } else {
                elemRes.innerText = `⚠️ x = ${x.toFixed(2)} (não inteiro)`;
                elemRes.className = "result warning";
            }
        }

        // Executa a pré-visualização ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            previewFormula();
        });
    </script>

</body>
</html>