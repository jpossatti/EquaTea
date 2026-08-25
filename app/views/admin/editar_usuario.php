<?php
/**
 * app/views/admin/editar_usuario.php
 * Editar Usuário - Layout similar ao Editar Aluno
 */

// Verifica se o usuário foi passado
if (!isset($usuario) || empty($usuario)) {
    if (isset($_GET['id'])) {
        try {
            require_once __DIR__ . '/../../config/Database.php';
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT u.*, 
                       a.idade, a.nivel_tea, a.escola as escola_aluno, a.turma,
                       p.disciplina, p.escola as escola_professor, p.telefone
                FROM usuarios u
                LEFT JOIN alunos a ON u.id = a.usuario_id
                LEFT JOIN professores p ON u.id = p.usuario_id
                WHERE u.id = ?
            ");
            $stmt->execute([$_GET['id']]);
            $usuario = $stmt->fetch();
        } catch (Exception $e) {
            $usuario = null;
        }
    }
}

// Se não encontrou, redireciona
if (!$usuario) {
    $_SESSION['admin_error'] = 'Usuário não encontrado.';
    header('Location: index.php?view=admin/gerenciar');
    exit;
}

// Não permite editar o próprio admin
if ($usuario['id'] == $_SESSION['usuario_id']) {
    $_SESSION['admin_error'] = 'Você não pode editar seu próprio usuário pelo formulário.';
    header('Location: index.php?view=admin/gerenciar');
    exit;
}

$nome_admin = $_SESSION['usuario_nome'] ?? 'Administrador';
$page_title = 'Editar Usuário - EquaTEA Admin';
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

        .card {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin-bottom: 20px;
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

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-grid .full-width {
            grid-column: 1 / -1;
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
            color: #dc3545;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 0.95rem;
            transition: border-color 0.3s;
            background: #fafafa;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15);
            background: white;
        }

        .form-group small {
            display: block;
            color: #6c757d;
            font-size: 0.8rem;
            margin-top: 4px;
        }

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
            background: #27ae60;
            color: white;
        }

        .btn-primary:hover {
            background: #219a52;
            box-shadow: 0 2px 8px rgba(39, 174, 96, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
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

        .perfil-fields {
            display: none;
        }

        .perfil-fields.active {
            display: block;
        }

        @media (max-width: 768px) {
            .admin-header { flex-direction: column; text-align: center; }
            .admin-nav { flex-direction: column; align-items: center; }
            .form-grid { grid-template-columns: 1fr; }
            .form-actions { justify-content: center; }
            .card { padding: 20px; }
            .page-header h1 { font-size: 1.4rem; }
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
            <small>Editar Usuário</small>
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

        <div class="page-header">
            <h1>✏️ Editar Usuário</h1>
            <p class="subtitle">Atualize os dados cadastrais do usuário</p>
        </div>

        <div class="card">
            <!-- Informações atuais -->
            <div class="current-info">
                <span>
                    <span class="label">Nome:</span>
                    <span class="value"><?php echo htmlspecialchars($usuario['nome'] ?? 'N/A'); ?></span>
                </span>
                <span>
                    <span class="label">Perfil:</span>
                    <span class="value">
                        <span class="badge badge-<?php echo $usuario['tipo_perfil']; ?>">
                            <?php 
                                $perfilLabels = [
                                    'aluno' => 'Aluno',
                                    'professor' => 'Professor',
                                    'admin' => 'Administrador'
                                ];
                                echo $perfilLabels[$usuario['tipo_perfil']] ?? $usuario['tipo_perfil'];
                            ?>
                        </span>
                    </span>
                </span>
                <span>
                    <span class="label">Status:</span>
                    <span class="value">
                        <span class="badge <?php echo $usuario['ativo'] ? 'badge-ativo' : 'badge-inativo'; ?>">
                            <?php echo $usuario['ativo'] ? 'Ativo' : 'Inativo'; ?>
                        </span>
                    </span>
                </span>
            </div>

            <form method="POST" action="index.php?view=admin/editar_usuario_salvar">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($usuario['id']); ?>">

                <div class="form-grid">
                    <div class="form-group">
                        <label for="nome">Nome Completo <span class="required">*</span></label>
                        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">E-mail <span class="required">*</span></label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="senha">Nova Senha <span style="color: #6c757d; font-weight: normal;">(deixe em branco para manter)</span></label>
                        <input type="password" id="senha" name="senha" minlength="4" placeholder="••••••••">
                        <small>Mínimo 4 caracteres</small>
                    </div>

                    <div class="form-group">
                        <label for="tipo_perfil">Perfil <span class="required">*</span></label>
                        <select id="tipo_perfil" name="tipo_perfil" onchange="togglePerfilFields()" required>
                            <option value="aluno" <?php echo ($usuario['tipo_perfil'] ?? '') === 'aluno' ? 'selected' : ''; ?>>👨‍🎓 Aluno</option>
                            <option value="professor" <?php echo ($usuario['tipo_perfil'] ?? '') === 'professor' ? 'selected' : ''; ?>>👨‍🏫 Professor</option>
                        </select>
                    </div>
                </div>

                <!-- Campos para Aluno -->
                <div id="aluno_fields" class="perfil-fields <?php echo ($usuario['tipo_perfil'] ?? '') === 'aluno' ? 'active' : ''; ?>">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="idade">Idade (14-21) <span class="required">*</span></label>
                            <input type="number" id="idade" name="idade" min="14" max="21" value="<?php echo htmlspecialchars($usuario['idade'] ?? 15); ?>">
                        </div>
                        <div class="form-group">
                            <label for="nivel_tea">Nível TEA <span class="required">*</span></label>
                            <select id="nivel_tea" name="nivel_tea">
                                <option value="suporte1" <?php echo ($usuario['nivel_tea'] ?? '') === 'suporte1' ? 'selected' : ''; ?>>Suporte 1</option>
                                <option value="suporte2" <?php echo ($usuario['nivel_tea'] ?? '') === 'suporte2' ? 'selected' : ''; ?>>Suporte 2</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="escola">Escola</label>
                            <input type="text" id="escola" name="escola" value="<?php echo htmlspecialchars($usuario['escola_aluno'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="turma">Turma</label>
                            <input type="text" id="turma" name="turma" value="<?php echo htmlspecialchars($usuario['turma'] ?? ''); ?>" placeholder="Ex: 1º EM A">
                        </div>
                    </div>
                </div>

                <!-- Campos para Professor -->
                <div id="professor_fields" class="perfil-fields <?php echo ($usuario['tipo_perfil'] ?? '') === 'professor' ? 'active' : ''; ?>">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="disciplina">Disciplina</label>
                            <input type="text" id="disciplina" name="disciplina" value="<?php echo htmlspecialchars($usuario['disciplina'] ?? 'Matemática'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="escola_professor">Escola</label>
                            <input type="text" id="escola_professor" name="escola" value="<?php echo htmlspecialchars($usuario['escola_professor'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="telefone">Telefone</label>
                            <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($usuario['telefone'] ?? ''); ?>" placeholder="(11) 99999-9999">
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label>
                        <input type="checkbox" id="ativo" name="ativo" <?php echo ($usuario['ativo'] ?? 1) ? 'checked' : ''; ?>>
                        Usuário ativo
                    </label>
                </div>

                <div class="form-actions">
                    <a href="index.php?view=admin/gerenciar" class="btn btn-secondary">⬅ Cancelar</a>
                    <button type="submit" class="btn btn-primary">💾 Salvar Alterações</button>
                </div>
            </form>
        </div>

        <div style="text-align: center;">
            <a href="index.php?view=admin/gerenciar" class="btn btn-secondary" style="text-decoration: none; display: inline-block;">
                ⬅ Voltar para Gerenciar Usuários
            </a>
        </div>

    </div>

    <script>
        function togglePerfilFields() {
            const tipo = document.getElementById('tipo_perfil').value;
            const alunoFields = document.getElementById('aluno_fields');
            const professorFields = document.getElementById('professor_fields');
            
            if (tipo === 'aluno') {
                alunoFields.classList.add('active');
                professorFields.classList.remove('active');
                document.getElementById('idade').required = true;
                document.getElementById('nivel_tea').required = true;
            } else {
                alunoFields.classList.remove('active');
                professorFields.classList.add('active');
                document.getElementById('idade').required = false;
                document.getElementById('nivel_tea').required = false;
            }
        }

        // Inicializa
        document.addEventListener('DOMContentLoaded', function() {
            togglePerfilFields();
        });
    </script>

</body>
</html>