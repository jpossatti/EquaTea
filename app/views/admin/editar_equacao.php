<?php
/**
 * app/views/admin/editar_equacao.php
 * Formulário de edição de equação para o Admin
 */

// Verifica se a equação foi passada
if (!isset($equacao) || empty($equacao)) {
    // Tenta buscar do banco se não veio
    if (isset($_GET['id'])) {
        try {
            require_once __DIR__ . '/../../config/Database.php';
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM equacoes WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $equacao = $stmt->fetch();
        } catch (Exception $e) {
            $equacao = null;
        }
    }
}

// Se não encontrou, redireciona
if (!$equacao) {
    $_SESSION['admin_error'] = 'Equação não encontrada.';
    header('Location: index.php?view=admin/equacoes');
    exit;
}

$nome_admin = $_SESSION['usuario_nome'] ?? 'Administrador';
$page_title = 'Editar Equação - EquaTEA Admin';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <style>
        :root {
            --primary-color: #1a237e;
            --accent-blue: #3498db;
            --bg-light: #f4f7f6;
            --card-bg: #ffffff;
            --text-color: #333333;
            --border-color: #e0e0e0;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
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
            max-width: 800px;
            margin: 30px auto;
            padding: 0 15px;
        }

        .page-header {
            margin-bottom: 25px;
        }

        .page-header h1 {
            color: var(--primary-color);
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-header .subtitle {
            color: #6c757d;
            font-size: 0.95rem;
            margin-top: 5px;
        }

        .card {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin-bottom: 20px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
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

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 0.9rem;
            color: #495057;
        }

        .form-group label .required {
            color: var(--danger-color);
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 0.95rem;
            transition: border-color 0.3s, box-shadow 0.3s;
            background: #fafafa;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15);
            background: white;
        }

        .form-group input:hover,
        .form-group select:hover {
            border-color: #adb5bd;
        }

        .form-group small {
            display: block;
            color: #6c757d;
            font-size: 0.8rem;
            margin-top: 4px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-grid .full-width {
            grid-column: 1 / -1;
        }

        .preview-box {
            background: #f8f9fa;
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0 25px;
            text-align: center;
        }

        .preview-box .label {
            color: #64748b;
            font-weight: 500;
            margin-right: 10px;
        }

        .preview-box .equation {
            font-family: 'Courier New', monospace;
            font-size: 1.3rem;
            color: var(--primary-color);
            font-weight: 700;
        }

        .preview-box .result {
            display: inline-block;
            margin-left: 15px;
            padding: 4px 14px;
            border-radius: 4px;
            font-weight: bold;
            background-color: #e2e8f0;
            color: #475569;
            font-size: 0.95rem;
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
            gap: 12px;
            justify-content: flex-end;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn:hover { transform: translateY(-1px); }
        .btn:active { transform: translateY(0); }

        .btn-primary {
            background: var(--accent-blue);
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
            box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
        }

        .btn-success {
            background: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background: #218838;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
        }

        .btn-warning {
            background: var(--warning-color);
            color: #212529;
        }

        .btn-warning:hover {
            background: #e0a800;
            box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
        }

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

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-facil { background: #d4edda; color: #155724; }
        .badge-medio { background: #fff3cd; color: #856404; }
        .badge-dificil { background: #f8d7da; color: #721c24; }

        .current-info {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .current-info .label {
            color: #6c757d;
            font-weight: 500;
        }

        .current-info .value {
            font-weight: 600;
            color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .admin-header { flex-direction: column; text-align: center; }
            .admin-nav { flex-direction: column; align-items: center; }
            .form-grid { grid-template-columns: 1fr; }
            .form-actions { justify-content: center; }
            .card { padding: 20px; }
            .page-header h1 { font-size: 1.4rem; }
            .preview-box .equation { font-size: 1rem; }
            .current-info { flex-direction: column; text-align: center; }
        }

        @media (max-width: 480px) {
            .admin-header .logo { font-size: 1.2rem; }
            .btn { width: 100%; justify-content: center; }
            .form-actions { flex-direction: column; }
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <header class="admin-header">
        <div class="logo">
            Equa<span>TEA</span> 
            <small>Editar Equação</small>
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

        <div class="page-header">
            <h1>✏️ Editar Equação</h1>
            <p class="subtitle">Atualize os coeficientes e o nível de dificuldade da equação</p>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>📐 Dados da Equação</h3>
                <span>
                    <span class="badge badge-<?php echo $equacao['dificuldade'] ?? 'facil'; ?>">
                        ID: #<?php echo $equacao['id'] ?? '?'; ?>
                    </span>
                </span>
            </div>

            <!-- Informações atuais -->
            <div class="current-info">
                <span>
                    <span class="label">Equação atual:</span>
                    <span class="value">
                        <?php 
                            $a = (int)($equacao['a'] ?? 1);
                            $b = (int)($equacao['b'] ?? 0);
                            $c = (int)($equacao['c'] ?? 0);
                            $termoA = ($a === 1) ? 'x' : (($a === -1) ? '-x' : "{$a}x");
                            $sinalB = ($b >= 0) ? '+ ' . $b : '- ' . abs($b);
                            echo htmlspecialchars("{$termoA} {$sinalB} = {$c}");
                        ?>
                    </span>
                </span>
                <span>
                    <span class="label">Solução:</span>
                    <span class="value">x = <?php echo $equacao['solucao'] ?? '?'; ?></span>
                </span>
                <span>
                    <span class="label">Dificuldade:</span>
                    <span class="value">
                        <span class="badge badge-<?php echo $equacao['dificuldade'] ?? 'facil'; ?>">
                            <?php 
                                $labels = ['facil' => 'Fácil', 'medio' => 'Médio', 'dificil' => 'Difícil'];
                                echo $labels[$equacao['dificuldade'] ?? 'facil'] ?? $equacao['dificuldade'];
                            ?>
                        </span>
                    </span>
                </span>
            </div>

            <form method="POST" action="index.php?view=admin/editar_equacao_salvar" id="formEditarEquacao">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($equacao['id'] ?? ''); ?>">

                <div class="form-grid">
                    <div class="form-group">
                        <label for="a">Coeficiente a <span class="required">*</span></label>
                        <input type="number" id="a" name="a" step="any" 
                               value="<?php echo htmlspecialchars($equacao['a'] ?? 1); ?>" 
                               required oninput="previewFormula()">
                        <small>Não pode ser zero</small>
                    </div>

                    <div class="form-group">
                        <label for="b">Coeficiente b <span class="required">*</span></label>
                        <input type="number" id="b" name="b" step="any" 
                               value="<?php echo htmlspecialchars($equacao['b'] ?? 0); ?>" 
                               required oninput="previewFormula()">
                    </div>

                    <div class="form-group">
                        <label for="c">Coeficiente c <span class="required">*</span></label>
                        <input type="number" id="c" name="c" step="any" 
                               value="<?php echo htmlspecialchars($equacao['c'] ?? 0); ?>" 
                               required oninput="previewFormula()">
                    </div>

                    <div class="form-group">
                        <label for="dificuldade">Dificuldade <span class="required">*</span></label>
                        <select id="dificuldade" name="dificuldade" required>
                            <option value="facil" <?php echo ($equacao['dificuldade'] ?? '') === 'facil' ? 'selected' : ''; ?>>Fácil</option>
                            <option value="medio" <?php echo ($equacao['dificuldade'] ?? '') === 'medio' ? 'selected' : ''; ?>>Médio</option>
                            <option value="dificil" <?php echo ($equacao['dificuldade'] ?? '') === 'dificil' ? 'selected' : ''; ?>>Difícil</option>
                        </select>
                    </div>
                </div>

                <!-- Pré-visualização -->
                <div class="preview-box">
                    <span class="label">🔍 Pré-visualização:</span>
                    <span class="equation" id="previewEquation">
                        <?php 
                            $a = (int)($equacao['a'] ?? 1);
                            $b = (int)($equacao['b'] ?? 0);
                            $c = (int)($equacao['c'] ?? 0);
                            $termoA = ($a === 1) ? 'x' : (($a === -1) ? '-x' : "{$a}x");
                            $sinalB = ($b >= 0) ? '+ ' . $b : '- ' . abs($b);
                            echo htmlspecialchars("{$termoA} {$sinalB} = {$c}");
                        ?>
                    </span>
                    <span class="result" id="previewResult">
                        <?php 
                            $solucao = $equacao['solucao'] ?? 0;
                            if (is_int($solucao)) {
                                echo "✔ x = {$solucao}";
                            } else {
                                echo "⚠️ x = " . number_format($solucao, 2);
                            }
                        ?>
                    </span>
                </div>

                <div class="form-actions">
                    <a href="index.php?view=admin/equacoes" class="btn btn-secondary">
                        ⬅ Cancelar
                    </a>
                    <button type="submit" class="btn btn-success">
                        💾 Salvar Alterações
                    </button>
                </div>
            </form>
        </div>

        <div style="text-align: center; margin-top: 10px;">
            <a href="index.php?view=admin/equacoes" class="btn btn-secondary">
                ⬅ Voltar para Gerenciar Equações
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