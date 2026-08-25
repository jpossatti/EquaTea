<?php
/**
 * app/views/admin/dashboard.php
 * Dashboard do Administrador - Layout similar ao do Professor
 */

// Dados do Admin
$nome_admin = $_SESSION['usuario_nome'] ?? 'Administrador';

// Estatísticas (vindas do controller)
$total_usuarios = $total_usuarios ?? 0;
$total_alunos = $total_alunos ?? 0;
$total_professores = $total_professores ?? 0;
$total_equacoes = $total_equacoes ?? 0;
$usuarios = $usuarios ?? [];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EquaTEA - Painel Administrativo</title>
    <link rel="stylesheet" href="/public/css/style.css">
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
            border-color: var(--accent-blue);
        }

        .acao-icon {
            font-size: 32px;
        }

        /* Tabela */
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

        .usuarios-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin-top: 10px;
        }

        .usuarios-table th {
            background: #f8f9fa;
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            font-size: 13px;
            white-space: nowrap;
        }

        .usuarios-table td {
            padding: 10px 16px;
            border-bottom: 1px solid #f1f3f5;
            color: #495057;
            font-size: 14px;
            vertical-align: middle;
        }

        .usuarios-table tbody tr:hover {
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

        .badge-success { background: #d4edda; color: #155724; }
        .badge-info { background: #cce5ff; color: #004085; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }

        .badge-aluno { background: #cce5ff; color: #004085; }
        .badge-professor { background: #d4edda; color: #155724; }
        .badge-admin { background: #f8d7da; color: #721c24; }

        .badge-ativo { background: #d4edda; color: #155724; }
        .badge-inativo { background: #f8d7da; color: #721c24; }

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

        @media (max-width: 768px) {
            .admin-header { flex-direction: column; text-align: center; }
            .admin-nav { flex-direction: column; align-items: center; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .acoes-rapidas { grid-template-columns: 1fr; }
            .usuarios-table { font-size: 13px; }
            .usuarios-table th,
            .usuarios-table td { padding: 8px 10px; }
            .usuarios-table th { font-size: 11px; }
            .stat-value { font-size: 22px; }
        }

        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
            .usuarios-table { font-size: 12px; }
            .usuarios-table th,
            .usuarios-table td { padding: 6px 8px; }
            .badge { font-size: 10px; padding: 2px 8px; }
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

        <h1>👑 Olá, <?php echo htmlspecialchars($nome_admin); ?>!</h1>
        <p class="subtitle">Gerencie todos os aspectos do sistema EquaTEA de forma centralizada</p>

        <!-- Cards de Estatísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👤</div>
                <div>
                    <span class="stat-value"><?php echo $total_usuarios; ?></span>
                    <span class="stat-label">Usuários</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👨‍🎓</div>
                <div>
                    <span class="stat-value"><?php echo $total_alunos; ?></span>
                    <span class="stat-label">Alunos</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👨‍🏫</div>
                <div>
                    <span class="stat-value"><?php echo $total_professores; ?></span>
                    <span class="stat-label">Professores</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📐</div>
                <div>
                    <span class="stat-value"><?php echo $total_equacoes; ?></span>
                    <span class="stat-label">Equações</span>
                </div>
            </div>
        </div>

        <!-- Ações Rápidas -->
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

        <!-- Tabela de Usuários -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 30px; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
            <h2 style="margin: 0;">👤 Últimos Usuários</h2>
            <a href="index.php?view=admin/gerenciar" class="btn-ver-todos">Ver todos →</a>
        </div>

        <?php if (!empty($usuarios) && is_array($usuarios)): ?>
            <table class="usuarios-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Perfil</th>
                        <th>Status</th>
                        <th>Dados</th>
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
                            
                            $dadosPerfil = '';
                            if ($user['tipo_perfil'] === 'aluno') {
                                $dadosPerfil = "Idade: {$user['idade']} | Nível: {$user['nivel_tea']}";
                            } elseif ($user['tipo_perfil'] === 'professor') {
                                $dadosPerfil = "Disciplina: {$user['disciplina']}";
                            }
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
                            <td style="font-size: 12px; color: #888;">
                                <?php echo htmlspecialchars($dadosPerfil); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div style="margin-top: 10px; text-align: right; font-size: 14px; color: #888;">
                Total: <strong><?php echo count($usuarios); ?></strong> usuários
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 30px; background: #fff; border-radius: 8px; color: #6c757d; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                <p style="font-size: 16px;">📭 Nenhum usuário encontrado.</p>
                <p>Clique em "Gerenciar Usuários" para cadastrar novos usuários.</p>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>