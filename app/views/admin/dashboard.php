<?php
/**
 * app/views/admin/dashboard.php
 * Dashboard do Administrador
 */

// Dados do Admin
$nome_admin = $_SESSION['usuario_nome'] ?? 'Administrador';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EquaTEA - Painel Administrativo</title>
    <style>
        :root {
            --primary-color: #1a237e;
            --accent-green: #2ecc71;
            --accent-blue: #3498db;
            --accent-orange: #f39c12;
            --accent-red: #e74c3c;
            --bg-light: #f4f7f6;
            --card-bg: #ffffff;
            --text-color: #333333;
            --border-color: #e0e0e0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            margin: 0;
            padding: 0;
            color: var(--text-color);
        }

        /* HEADER */
        .admin-header {
            background: linear-gradient(135deg, #1a237e, #283593);
            color: white;
            padding: 15px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            flex-wrap: wrap;
            gap: 10px;
        }

        .admin-header .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .admin-header .logo span {
            color: #64b5f6;
        }

        .admin-header .logo small {
            font-size: 0.7rem;
            font-weight: normal;
            opacity: 0.8;
        }

        .admin-header .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admin-header .user-name {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .admin-header .btn-logout {
            color: white;
            text-decoration: none;
            background: #e74c3c;
            padding: 6px 14px;
            border-radius: 4px;
            font-size: 0.85rem;
            transition: background 0.3s;
        }

        .admin-header .btn-logout:hover {
            background: #c0392b;
        }

        /* NAVEGAÇÃO */
        .admin-nav {
            background-color: #ffffff;
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
            transition: background-color 0.2s, color 0.2s;
        }

        .admin-nav a:hover,
        .admin-nav a.active {
            background-color: #e8eaf6;
            color: var(--primary-color);
        }

        .admin-nav a.active {
            background-color: var(--primary-color);
            color: white;
        }

        /* CONTAINER */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 15px;
        }

        /* WELCOME */
        .welcome-card {
            background: linear-gradient(135deg, #1a237e, #283593);
            color: white;
            padding: 25px 30px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .welcome-card h2 {
            margin: 0 0 8px 0;
            font-size: 1.6rem;
        }

        .welcome-card p {
            margin: 0;
            opacity: 0.9;
        }

        /* STATS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: var(--card-bg);
            padding: 18px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 15px;
            border-left: 5px solid var(--accent-blue);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .stat-card.green { border-left-color: var(--accent-green); }
        .stat-card.orange { border-left-color: var(--accent-orange); }
        .stat-card.red { border-left-color: var(--accent-red); }
        .stat-card.purple { border-left-color: #9b59b6; }

        .stat-icon { font-size: 2rem; }

        .stat-info h4 {
            margin: 0;
            font-size: 0.8rem;
            color: #777;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-info .value {
            font-size: 1.6rem;
            font-weight: bold;
            margin-top: 2px;
            color: var(--primary-color);
        }

        /* CARD */
        .card {
            background-color: var(--card-bg);
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin-bottom: 20px;
        }

        .card h3 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 1.2rem;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* TABLE */
        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.9rem;
        }

        th, td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background-color: #f8f9fa;
            color: #555;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* BADGES */
        .badge {
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-secondary { background: #e9ecef; color: #6c757d; }

        .badge-aluno { background: #cce5ff; color: #004085; }
        .badge-professor { background: #d4edda; color: #155724; }
        .badge-admin { background: #f8d7da; color: #721c24; }

        .badge-ativo { background: #d4edda; color: #155724; }
        .badge-inativo { background: #f8d7da; color: #721c24; }

        /* BUTTONS */
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
            transition: background-color 0.2s, transform 0.1s;
        }

        .btn-action:hover {
            transform: scale(1.02);
        }

        .btn-action.btn-edit {
            background: #3498db;
            color: white;
        }

        .btn-action.btn-edit:hover {
            background: #2980b9;
        }

        .btn-action.btn-delete {
            background: #e74c3c;
            color: white;
        }

        .btn-action.btn-delete:hover {
            background: #c0392b;
        }

        .btn-action.btn-add {
            background: #28a745;
            color: white;
            padding: 8px 20px;
            font-size: 0.85rem;
        }

        .btn-action.btn-add:hover {
            background: #218838;
        }

        .btn-action.btn-view {
            background: #6c757d;
            color: white;
        }

        .btn-action.btn-view:hover {
            background: #5a6268;
        }

        .btn-action.btn-back {
            background: #6c757d;
            color: white;
        }

        .btn-action.btn-back:hover {
            background: #5a6268;
        }

        /* ALERTAS */
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

        /* MODAL SIMPLES */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            background: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 600px;
            width: 95%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }

        .modal h3 {
            margin-top: 0;
            color: var(--primary-color);
        }

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
            grid-template-columns: 1fr 1fr;
            gap: 15px;
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

        .modal .btn-submit:hover {
            background: #2980b9;
        }

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

        .modal .btn-cancel:hover {
            background: #5a6268;
        }

        /* AÇÕES RÁPIDAS */
        .acoes-rapidas {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .acao-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
            color: var(--text-color);
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            font-weight: 500;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 2px solid transparent;
        }

        .acao-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            border-color: var(--accent-blue);
        }

        .acao-icon {
            font-size: 2.2rem;
            display: block;
            margin-bottom: 8px;
        }

        /* FOOTER */
        .footer-info {
            margin-top: 30px;
            padding: 15px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            text-align: center;
            color: #888;
            font-size: 0.85rem;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                text-align: center;
            }

            .admin-nav {
                flex-direction: column;
                align-items: center;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .modal .form-row {
                grid-template-columns: 1fr;
            }

            .stat-card {
                padding: 12px 15px;
            }

            .stat-info .value {
                font-size: 1.2rem;
            }

            table {
                font-size: 0.8rem;
            }

            th, td {
                padding: 6px 8px;
            }

            .card {
                padding: 15px;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .admin-header .logo {
                font-size: 1.2rem;
            }

            .welcome-card h2 {
                font-size: 1.2rem;
            }

            .modal {
                padding: 20px;
                margin: 10px;
            }
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <header class="admin-header">
        <div class="logo">
            Equa<span>TEA</span> 
            <small>Painel Administrativo</small>
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
        <a href="index.php?view=admin/dashboard" class="active">📊 Dashboard</a>
        <a href="index.php?view=admin/gerenciar">👤 Gerenciar Usuários</a>
        <a href="index.php?view=admin/equacoes">📐 Gerenciar Equações</a>
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

        <!-- WELCOME -->
        <div class="welcome-card">
            <h2>👑 Bem-vindo, <?php echo htmlspecialchars($nome_admin); ?>!</h2>
            <p>Gerencie todos os aspectos do sistema EquaTEA de forma centralizada.</p>
        </div>

        <!-- ESTATÍSTICAS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👤</div>
                <div class="stat-info">
                    <h4>Total de Usuários</h4>
                    <div class="value"><?php echo $total_usuarios ?? 0; ?></div>
                </div>
            </div>

            <div class="stat-card green">
                <div class="stat-icon">👨‍🎓</div>
                <div class="stat-info">
                    <h4>Alunos</h4>
                    <div class="value"><?php echo $total_alunos ?? 0; ?></div>
                </div>
            </div>

            <div class="stat-card orange">
                <div class="stat-icon">👨‍🏫</div>
                <div class="stat-info">
                    <h4>Professores</h4>
                    <div class="value"><?php echo $total_professores ?? 0; ?></div>
                </div>
            </div>

            <div class="stat-card purple">
                <div class="stat-icon">📐</div>
                <div class="stat-info">
                    <h4>Equações</h4>
                    <div class="value"><?php echo $total_equacoes ?? 0; ?></div>
                </div>
            </div>
        </div>

        <!-- AÇÕES RÁPIDAS -->
        <div class="acoes-rapidas">
            <a href="index.php?view=admin/gerenciar" class="acao-card">
                <span class="acao-icon">👤</span>
                Gerenciar Usuários
            </a>
            <a href="index.php?view=admin/equacoes" class="acao-card">
                <span class="acao-icon">📐</span>
                Gerenciar Equações
            </a>
        </div>

        <!-- ÚLTIMOS USUÁRIOS -->
        <div class="card">
            <h3>📋 Últimos Usuários Cadastrados</h3>

            <?php if (!empty($usuarios) && is_array($usuarios)): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Perfil</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($usuarios, 0, 10) as $user): ?>
                                <?php
                                    $perfilLabels = [
                                        'aluno' => 'Aluno',
                                        'professor' => 'Professor',
                                        'admin' => 'Administrador'
                                    ];
                                    $statusClass = $user['ativo'] ? 'badge-ativo' : 'badge-inativo';
                                    $statusText = $user['ativo'] ? 'Ativo' : 'Inativo';
                                    $perfilClass = 'badge-' . $user['tipo_perfil'];
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($user['nome'] ?? 'N/A'); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge <?php echo $perfilClass; ?>">
                                            <?php echo $perfilLabels[$user['tipo_perfil']] ?? $user['tipo_perfil']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo $statusText; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="index.php?view=admin/gerenciar" class="btn-action btn-edit">
                                            ✏️ Editar
                                        </a>
                                        <?php if ($user['id'] != $_SESSION['usuario_id']): ?>
                                            <a href="index.php?view=admin/excluir_usuario&id=<?php echo $user['id']; ?>" 
                                               class="btn-action btn-delete"
                                               onclick="return confirm('Tem certeza que deseja excluir este usuário?');">
                                                🗑️
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (count($usuarios) > 10): ?>
                    <div style="text-align: center; margin-top: 15px;">
                        <a href="index.php?view=admin/gerenciar" class="btn-action btn-view">
                            Ver todos os <?php echo count($usuarios); ?> usuários →
                        </a>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div style="text-align: center; padding: 20px; color: #6c757d;">
                    <p style="font-size: 16px;">📭 Nenhum usuário cadastrado ainda.</p>
                    <p>Clique em "Gerenciar Usuários" para criar o primeiro.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- RODAPÉ -->
        <div class="footer-info">
            <p>🔒 Painel Administrativo - EquaTEA</p>
            <p style="margin-top: 5px; font-size: 0.8rem;">Gerencie usuários, equações e acompanhe o progresso do sistema.</p>
        </div>

    </div>

</body>
</html>