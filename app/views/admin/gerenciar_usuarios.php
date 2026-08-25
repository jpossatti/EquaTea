<?php
/**
 * app/views/admin/gerenciar_usuarios.php
 * Gerenciamento de Usuários - Layout similar ao Gerenciar Alunos
 */

// Garante que $usuarios existe
$usuarios = $usuarios ?? [];
$nome_admin = $_SESSION['usuario_nome'] ?? 'Administrador';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EquaTEA - Gerenciar Usuários</title>
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

        .card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin-bottom: 20px;
        }

        .card h2 {
            margin-top: 0;
            text-align: center;
            color: var(--primary-color);
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

        /* Formulário */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .form-grid-4 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ccc;
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

        .btn-primary {
            background: #27ae60;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1rem;
            transition: background 0.2s;
        }

        .btn-primary:hover {
            background: #219a52;
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1rem;
            transition: background 0.2s;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
        }

        .btn-actions {
            text-align: center;
            margin-top: 10px;
        }

        /* Tabela */
        .table-responsive {
            overflow-x: auto;
        }

        .usuarios-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .usuarios-table th {
            background: #f8f9fa;
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            font-size: 13px;
        }

        .usuarios-table td {
            padding: 10px 16px;
            border-bottom: 1px solid #e9ecef;
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
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            color: white;
        }

        .badge-aluno { background: #007bff; }
        .badge-professor { background: #28a745; }
        .badge-admin { background: #dc3545; }

        .badge-ativo { background: #28a745; }
        .badge-inativo { background: #dc3545; }

        .btn-acao {
            display: inline-block;
            padding: 4px 8px;
            margin: 0 2px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            transition: opacity 0.2s;
        }

        .btn-acao:hover {
            opacity: 0.8;
        }

        .btn-editar {
            background: #17a2b8;
            color: white;
        }

        .btn-excluir {
            background: #dc3545;
            color: white;
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
            .form-grid { grid-template-columns: 1fr; }
            .form-grid-4 { grid-template-columns: 1fr 1fr; }
            .usuarios-table { font-size: 13px; }
            .usuarios-table th,
            .usuarios-table td { padding: 8px 10px; }
            .card { padding: 15px !important; }
        }

        @media (max-width: 480px) {
            .form-grid-4 { grid-template-columns: 1fr; }
            .usuarios-table { font-size: 12px; }
            .usuarios-table th,
            .usuarios-table td { padding: 6px 8px; }
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <header class="admin-header">
        <div class="logo">
            Equa<span>TEA</span> 
            <small>Gerenciar Usuários</small>
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
        <a href="index.php?view=admin/gerenciar" class="active">👤 Gerenciar Usuários</a>
        <a href="index.php?view=admin/equacoes">📐 Gerenciar Equações</a>
        <a href="index.php?view=logout" onclick="return confirm('Deseja realmente sair?');">🚪 Sair</a>
    </nav>

    <!-- CONTEÚDO -->
    <div class="container">

        <h1 style="text-align: center; color: #2c3e50;">👤 Gerenciar Usuários</h1>

        <!-- Mensagens de Alerta -->
        <?php if (!empty($_SESSION['admin_success'])): ?>
            <div class="alert alert-success">
                ✅ <?php echo $_SESSION['admin_success']; unset($_SESSION['admin_success']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['admin_error'])): ?>
            <div class="alert alert-error">
                ⚠️ <?php echo $_SESSION['admin_error']; unset($_SESSION['admin_error']); ?>
            </div>
        <?php endif; ?>

        <!-- Formulário de Cadastro -->
        <div class="card">
            <h2>➕ Cadastrar Novo Usuário</h2>
            
            <form method="POST" action="index.php?view=admin/criar_usuario" id="formUsuario">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nome">Nome Completo *</label>
                        <input type="text" id="nome" name="nome" placeholder="Nome completo" required>
                    </div>
                    <div class="form-group">
                        <label for="email">E-mail *</label>
                        <input type="email" id="email" name="email" placeholder="email@dominio.com" required>
                    </div>
                    <div class="form-group">
                        <label for="senha">Senha (min. 4 caracteres) *</label>
                        <input type="password" id="senha" name="senha" minlength="4" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="form-grid-4">
                    <div class="form-group">
                        <label for="tipo_perfil">Perfil *</label>
                        <select id="tipo_perfil" name="tipo_perfil" onchange="togglePerfilFields()" required>
                            <option value="aluno">👨‍🎓 Aluno</option>
                            <option value="professor">👨‍🏫 Professor</option>
                        </select>
                    </div>

                    <!-- Campos para Aluno -->
                    <div id="aluno_fields" class="perfil-fields">
                        <div class="form-group">
                            <label for="idade">Idade (14-21) *</label>
                            <input type="number" id="idade" name="idade" min="14" max="21" value="15">
                        </div>
                        <div class="form-group">
                            <label for="nivel_tea">Nível TEA *</label>
                            <select id="nivel_tea" name="nivel_tea">
                                <option value="suporte1">Suporte 1</option>
                                <option value="suporte2">Suporte 2</option>
                            </select>
                        </div>
                    </div>

                    <!-- Campos para Professor -->
                    <div id="professor_fields" class="perfil-fields" style="display:none;">
                        <div class="form-group">
                            <label for="disciplina">Disciplina</label>
                            <input type="text" id="disciplina" name="disciplina" value="Matemática">
                        </div>
                        <div class="form-group">
                            <label for="telefone">Telefone</label>
                            <input type="text" id="telefone" name="telefone" placeholder="(11) 99999-9999">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="escola">Escola</label>
                        <input type="text" id="escola" name="escola" placeholder="Nome da escola">
                    </div>

                    <div class="form-group">
                        <label for="turma">Turma</label>
                        <input type="text" id="turma" name="turma" placeholder="Ex: 1º EM A">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label>
                        <input type="checkbox" id="ativo" name="ativo" checked>
                        Usuário ativo
                    </label>
                </div>

                <div class="btn-actions">
                    <button type="submit" class="btn-primary">✔ Cadastrar Usuário</button>
                    <button type="reset" class="btn-secondary" onclick="setTimeout(function(){ togglePerfilFields(); }, 50);">🔄 Limpar</button>
                </div>
            </form>
        </div>

        <!-- Lista de Usuários -->
        <div class="card">
            <h2>📋 Lista de Usuários</h2>

            <?php if (empty($usuarios)): ?>
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <p style="font-size: 18px;">Nenhum usuário cadastrado ainda.</p>
                    <p>Utilize o formulário acima para cadastrar um novo usuário.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="usuarios-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Perfil</th>
                                <th>Status</th>
                                <th>Dados</th>
                                <th style="text-align: center;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $user): ?>
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
                                    <td><?php echo $user['id']; ?></td>
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
                                    <td style="font-size: 12px; color: #6c757d;">
                                        <?php echo htmlspecialchars($dadosPerfil); ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="index.php?view=admin/editar_usuario&id=<?php echo $user['id']; ?>" 
                                           class="btn-acao btn-editar" title="Editar">
                                            ✏️
                                        </a>
                                        <?php if ($user['id'] != $_SESSION['usuario_id'] && $user['tipo_perfil'] !== 'admin'): ?>
                                            <a href="index.php?view=admin/excluir_usuario&id=<?php echo $user['id']; ?>" 
                                               class="btn-acao btn-excluir"
                                               onclick="return confirm('Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.');"
                                               title="Excluir">
                                                🗑️
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="total-info">
                    Total: <strong><?php echo count($usuarios); ?></strong> usuário(s) cadastrado(s)
                </div>
            <?php endif; ?>
        </div>

        <div style="margin-top: 10px; text-align: center;">
            <a href="index.php?view=admin/dashboard" class="btn-secondary" style="text-decoration: none; display: inline-block;">
                ⬅ Voltar para Dashboard
            </a>
        </div>

    </div>

    <script>
        function togglePerfilFields() {
            const tipo = document.getElementById('tipo_perfil').value;
            const alunoFields = document.getElementById('aluno_fields');
            const professorFields = document.getElementById('professor_fields');
            
            if (tipo === 'aluno') {
                alunoFields.style.display = 'flex';
                professorFields.style.display = 'none';
                document.getElementById('idade').required = true;
                document.getElementById('nivel_tea').required = true;
                document.getElementById('disciplina').required = false;
                document.getElementById('telefone').required = false;
            } else {
                alunoFields.style.display = 'none';
                professorFields.style.display = 'flex';
                document.getElementById('idade').required = false;
                document.getElementById('nivel_tea').required = false;
                document.getElementById('disciplina').required = true;
                document.getElementById('telefone').required = true;
            }
        }

        // Inicializa
        document.addEventListener('DOMContentLoaded', function() {
            togglePerfilFields();
        });
    </script>

</body>
</html>