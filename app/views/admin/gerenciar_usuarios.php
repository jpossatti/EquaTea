<?php
/**
 * app/views/admin/gerenciar_usuarios.php
 * Gerenciamento de Usuários
 */

$nome_admin = $_SESSION['usuario_nome'] ?? 'Administrador';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EquaTEA - Gerenciar Usuários</title>
    <link rel="stylesheet" href="/public/css/style.css">
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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

        .admin-header .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }

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

        .card-header h3 {
            margin: 0;
            color: var(--primary-color);
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
        }

        tbody tr:hover { background: #f8f9fa; }

        .badge {
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }

        .badge-aluno { background: #cce5ff; color: #004085; }
        .badge-professor { background: #d4edda; color: #155724; }
        .badge-admin { background: #f8d7da; color: #721c24; }

        .badge-ativo { background: #d4edda; color: #155724; }
        .badge-inativo { background: #f8d7da; color: #721c24; }

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

        .btn-action.btn-edit { background: #3498db; color: white; }
        .btn-action.btn-edit:hover { background: #2980b9; }

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
            max-width: 600px;
            width: 95%;
            max-height: 90vh;
            overflow-y: auto;
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

        .perfil-fields {
            display: none;
        }

        .perfil-fields.active {
            display: block;
        }

        @media (max-width: 768px) {
            .admin-header { flex-direction: column; text-align: center; }
            .admin-nav { flex-direction: column; align-items: center; }
            .modal .form-row { grid-template-columns: 1fr; }
            .card-header { flex-direction: column; align-items: stretch; }
            table { font-size: 0.8rem; }
            th, td { padding: 6px 8px; }
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

        <!-- LISTA DE USUÁRIOS -->
        <div class="card">
            <div class="card-header">
                <h3>👤 Lista de Usuários</h3>
                <button class="btn-action btn-add" onclick="abrirModal()">
                    ➕ Novo Usuário
                </button>
            </div>

            <?php if (!empty($usuarios) && is_array($usuarios)): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Perfil</th>
                                <th>Status</th>
                                <th>Dados</th>
                                <th>Ações</th>
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
                                    
                                    // Dados específicos do perfil
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
                                    <td style="font-size: 0.75rem; color: #6c757d;">
                                        <?php echo htmlspecialchars($dadosPerfil); ?>
                                    </td>
                                    <td>
                                        <button class="btn-action btn-edit" onclick="editarUsuario(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                            ✏️
                                        </button>
                                        <?php if ($user['id'] != $_SESSION['usuario_id'] && $user['tipo_perfil'] !== 'admin'): ?>
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
                
                <div style="margin-top: 10px; text-align: right; font-size: 14px; color: #888;">
                    Total: <strong><?php echo count($usuarios); ?></strong> usuários
                </div>

            <?php else: ?>
                <div style="text-align: center; padding: 30px; color: #6c757d;">
                    <p style="font-size: 18px;">📭 Nenhum usuário cadastrado.</p>
                    <p>Clique em "Novo Usuário" para criar o primeiro.</p>
                </div>
            <?php endif; ?>
        </div>

        <div style="margin-top: 10px;">
            <a href="index.php?view=admin/dashboard" class="btn-action btn-back">
                ⬅ Voltar para Dashboard
            </a>
        </div>

    </div>

    <!-- MODAL DE CRIAÇÃO/EDIÇÃO -->
    <div class="modal-overlay" id="modalUsuario">
        <div class="modal">
            <h3 id="modalTitle">➕ Novo Usuário</h3>
            
            <form method="POST" action="index.php?view=admin/criar_usuario" id="formUsuario">
                <input type="hidden" name="usuario_id" id="usuario_id" value="0">
                
                <div class="form-group">
                    <label for="nome">Nome Completo *</label>
                    <input type="text" id="nome" name="nome" required>
                </div>

                <div class="form-group">
                    <label for="email">E-mail *</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="senha">Senha <?php echo isset($user) && $user ? '(deixe em branco para manter)' : '*'; ?></label>
                    <input type="password" id="senha" name="senha" <?php echo !isset($user) ? 'required' : ''; ?>>
                </div>

                <div class="form-group">
                    <label for="tipo_perfil">Perfil *</label>
                    <select id="tipo_perfil" name="tipo_perfil" onchange="togglePerfilFields()" required>
                        <option value="aluno">👨‍🎓 Aluno</option>
                        <option value="professor">👨‍🏫 Professor</option>
                    </select>
                </div>

                <!-- Campos para Aluno -->
                <div id="aluno_fields" class="perfil-fields active">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="idade">Idade *</label>
                            <input type="number" id="idade" name="idade" min="14" max="21">
                        </div>
                        <div class="form-group">
                            <label for="nivel_tea">Nível TEA *</label>
                            <select id="nivel_tea" name="nivel_tea">
                                <option value="suporte1">Suporte 1</option>
                                <option value="suporte2">Suporte 2</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="escola">Escola</label>
                            <input type="text" id="escola" name="escola">
                        </div>
                        <div class="form-group">
                            <label for="turma">Turma</label>
                            <input type="text" id="turma" name="turma">
                        </div>
                    </div>
                </div>

                <!-- Campos para Professor -->
                <div id="professor_fields" class="perfil-fields">
                    <div class="form-group">
                        <label for="disciplina">Disciplina</label>
                        <input type="text" id="disciplina" name="disciplina" value="Matemática">
                    </div>
                    <div class="form-group">
                        <label for="escola_professor">Escola</label>
                        <input type="text" id="escola_professor" name="escola">
                    </div>
                    <div class="form-group">
                        <label for="telefone">Telefone</label>
                        <input type="text" id="telefone" name="telefone">
                    </div>
                </div>

                <div class="form-group">
                    <label for="ativo">
                        <input type="checkbox" id="ativo" name="ativo" checked>
                        Usuário ativo
                    </label>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" class="btn-submit" id="btnSubmit">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModal(usuario = null) {
            const modal = document.getElementById('modalUsuario');
            const form = document.getElementById('formUsuario');
            const title = document.getElementById('modalTitle');
            const btnSubmit = document.getElementById('btnSubmit');
            
            if (usuario) {
                title.textContent = '✏️ Editar Usuário';
                btnSubmit.textContent = 'Atualizar';
                form.action = 'index.php?view=admin/editar_usuario';
                
                document.getElementById('usuario_id').value = usuario.id;
                document.getElementById('nome').value = usuario.nome;
                document.getElementById('email').value = usuario.email;
                document.getElementById('tipo_perfil').value = usuario.tipo_perfil;
                document.getElementById('ativo').checked = usuario.ativo == 1;
                document.getElementById('senha').required = false;
                
                // Preenche dados específicos
                if (usuario.tipo_perfil === 'aluno') {
                    document.getElementById('idade').value = usuario.idade || '';
                    document.getElementById('nivel_tea').value = usuario.nivel_tea || 'suporte1';
                    document.getElementById('escola').value = usuario.escola_aluno || '';
                    document.getElementById('turma').value = usuario.turma || '';
                } else if (usuario.tipo_perfil === 'professor') {
                    document.getElementById('disciplina').value = usuario.disciplina || 'Matemática';
                    document.getElementById('escola_professor').value = usuario.escola_professor || '';
                    document.getElementById('telefone').value = usuario.telefone || '';
                }
            } else {
                title.textContent = '➕ Novo Usuário';
                btnSubmit.textContent = 'Salvar';
                form.action = 'index.php?view=admin/criar_usuario';
                form.reset();
                document.getElementById('usuario_id').value = 0;
                document.getElementById('senha').required = true;
            }
            
            togglePerfilFields();
            modal.classList.add('active');
        }

        function editarUsuario(usuario) {
            abrirModal(usuario);
        }

        function fecharModal() {
            document.getElementById('modalUsuario').classList.remove('active');
        }

        function togglePerfilFields() {
            const tipo = document.getElementById('tipo_perfil').value;
            const alunoFields = document.getElementById('aluno_fields');
            const professorFields = document.getElementById('professor_fields');
            
            if (tipo === 'aluno') {
                alunoFields.classList.add('active');
                professorFields.classList.remove('active');
            } else {
                alunoFields.classList.remove('active');
                professorFields.classList.add('active');
            }
        }

        // Fechar modal ao clicar fora
        document.getElementById('modalUsuario').addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModal();
            }
        });

        // Inicializa
        togglePerfilFields();
    </script>

</body>
</html>