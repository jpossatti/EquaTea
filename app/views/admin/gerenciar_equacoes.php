<?php
/**
 * app/views/admin/gerenciar_equacoes.php
 * Gerenciamento de Equações
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
        /* Mesmo estilo do dashboard */
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

        .card-header h3 { margin: 0; color: var(--primary-color); }

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
        }

        tbody tr:hover { background: #f8f9fa; }

        .equation-display {
            font-family: 'Courier New', monospace;
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .badge {
            padding: 3px 10px;
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
            padding: 4px 12px;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.75rem;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-action:hover { transform: scale(1.02); }

        .btn-action.btn-delete { background: #e74c3c; color: white; }
        .btn-action.btn-delete:hover { background: #c0392b; }

        .btn-action.btn-add {
            background: #28a745;
            color: white;
            padding: 8px 20px;
            font-size: 0.85rem;
        }

        .btn-action.btn-add:hover { background: #218838; }
        .btn-action.btn-back { background: #6c757d; color: white; }
        .btn-action.btn-back:hover { background: #5a6268; }

        .alert {
            padding: 12px 18px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* MODAL */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.active { display: flex; }

        .modal {
            background: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 500px;
            width: 95%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }

        .modal h3 { margin-top: 0; color: var(--primary-color); }

        .modal .form-group {
            margin-bottom: 15px;
        }

        .modal .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .modal .form-group input,
        .modal .form-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .modal .form-group input:focus,
        .modal .form-group select:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 2px rgba(52,152,219,0.2);
        }

        .modal .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
        }

        .modal .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: flex-end;
        }

        .modal .btn-submit {
            padding: 10px 24px;
            background: var(--accent-blue);
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .modal .btn-submit:hover { background: #2980b9; }

        .modal .btn-cancel {
            padding: 10px 24px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .modal .btn-cancel:hover { background: #5a6268; }

        .modal .formula-preview {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
            font-family: 'Courier New', monospace;
            font-size: 1.2rem;
            color: var(--primary-color);
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .admin-header { flex-direction: column; text-align: center; }
            .admin-nav { flex-direction: column; align-items: center; }
            .modal .form-row { grid-template-columns: 1fr; }
            .card-header { flex-direction: column; align-items: stretch; }
        }

        @media (max-width: 480px) {
            .admin-header .logo { font-size: 1.2rem; }
            .modal { padding: 20px; margin: 10px; }
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

        <!-- LISTA DE EQUAÇÕES -->
        <div class="card">
            <div class="card-header">
                <h3>📐 Lista de Equações</h3>
                <button class="btn-action btn-add" onclick="abrirModal()">
                    ➕ Nova Equação
                </button>
            </div>

            <?php if (!empty($equacoes) && is_array($equacoes)): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Equação</th>
                                <th>Dificuldade</th>
                                <th>Solução</th>
                                <th>Ação</th>
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
                                    <td>
                                        <a href="index.php?view=admin/excluir_equacao&id=<?php echo $eq['id']; ?>" 
                                           class="btn-action btn-delete"
                                           onclick="return confirm('Tem certeza que deseja excluir esta equação?');">
                                            🗑️
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="margin-top: 10px; text-align: right; font-size: 14px; color: #888;">
                    Total: <strong><?php echo count($equacoes); ?></strong> equações
                </div>

            <?php else: ?>
                <div style="text-align: center; padding: 30px; color: #6c757d;">
                    <p style="font-size: 18px;">📭 Nenhuma equação cadastrada.</p>
                    <p>Clique em "Nova Equação" para criar a primeira.</p>
                </div>
            <?php endif; ?>
        </div>

        <div style="margin-top: 10px;">
            <a href="index.php?view=admin/dashboard" class="btn-action btn-back">
                ⬅ Voltar para Dashboard
            </a>
        </div>

    </div>

    <!-- MODAL DE CRIAÇÃO -->
    <div class="modal-overlay" id="modalEquacao">
        <div class="modal">
            <h3>➕ Nova Equação</h3>
            
            <form method="POST" action="index.php?view=admin/criar_equacao">
                <div class="form-row">
                    <div class="form-group">
                        <label for="a">a (coeficiente)</label>
                        <input type="number" id="a" name="a" value="1" required onchange="previewFormula()">
                    </div>
                    <div class="form-group">
                        <label for="b">b</label>
                        <input type="number" id="b" name="b" value="0" required onchange="previewFormula()">
                    </div>
                    <div class="form-group">
                        <label for="c">c</label>
                        <input type="number" id="c" name="c" value="1" required onchange="previewFormula()">
                    </div>
                </div>

                <div class="formula-preview" id="formulaPreview">
                    1x + 0 = 1
                </div>

                <div class="form-group">
                    <label for="dificuldade">Dificuldade</label>
                    <select id="dificuldade" name="dificuldade">
                        <option value="facil">Fácil</option>
                        <option value="medio">Médio</option>
                        <option value="dificil">Difícil</option>
                    </select>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="btn-submit">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModal() {
            document.getElementById('modalEquacao').classList.add('active');
            previewFormula();
        }

        function fecharModal() {
            document.getElementById('modalEquacao').classList.remove('active');
        }

        function previewFormula() {
            const a = document.getElementById('a').value || 1;
            const b = document.getElementById('b').value || 0;
            const c = document.getElementById('c').value || 0;
            
            const termoA = a == 1 ? 'x' : (a == -1 ? '-x' : a + 'x');
            const sinalB = b >= 0 ? '+ ' + b : '- ' + Math.abs(b);
            const preview = termoA + ' ' + sinalB + ' = ' + c;
            
            document.getElementById('formulaPreview').textContent = preview;
        }

        // Fechar modal ao clicar fora
        document.getElementById('modalEquacao').addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModal();
            }
        });

        // Inicializa preview
        previewFormula();
    </script>

</body>
</html>