<?php
/**
 * gerenciar_alunos.php
 * View para listagem e cadastro de alunos com barra de navegação do professor
 */
$page_title = 'Gerenciar Alunos - EquaTEA';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/professor.css">
    <style>
        /* Estilização do Menu de Navegação Superior */
        .navbar-professor {
            background-color: #2c3e50;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            color: #fff;
            font-size: 1.2rem;
            font-weight: bold;
            text-decoration: none;
        }
        .navbar-brand .tea { color: #3498db; }
        .nav-menu {
            display: flex;
            gap: 15px;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .nav-link {
            color: #ecf0f1;
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 4px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .nav-link:hover, .nav-link.active {
            background-color: #34495e;
            color: #fff;
        }
        .nav-link.btn-sair {
            background-color: #e74c3c;
        }
        .nav-link.btn-sair:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body style="margin: 0; background-color: #f4f6f9; font-family: Arial, sans-serif;">

    <!-- Barra de Navegação do Professor -->
    <nav class="navbar-professor">
        <a href="index.php?view=professor" class="navbar-brand">
            <span>Equa</span><span class="tea">TEA</span> | Painel do Professor
        </a>
        <ul class="nav-menu">
            <li><a href="index.php?view=professor" class="nav-link">📊 Dashboard</a></li>
            <li><a href="index.php?view=gerenciar_alunos" class="nav-link active">🎓 Gerenciar Alunos</a></li>
            <li><a href="index.php?view=gerenciar_equacoes" class="nav-link">📐 Gerenciar Equações</a></li>
            <li><a href="index.php?view=relatorio" class="nav-link">📈 Relatórios</a></li>
            <li><a href="index.php?view=login" class="nav-link btn-sair">🚪 Sair</a></li>
        </ul>
    </nav>

    <div class="container" style="max-width: 950px; margin: 30px auto; padding: 0 15px;">
        
        <h1 style="text-align: center; color: #2c3e50;">🎓 Gerenciar Alunos</h1>

        <!-- Mensagens de Alerta -->
        <?php if (!empty($_SESSION['admin_success'])): ?>
            <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                <?php 
                    echo $_SESSION['admin_success']; 
                    unset($_SESSION['admin_success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['admin_error'])): ?>
            <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                <?php 
                    echo $_SESSION['admin_error']; 
                    unset($_SESSION['admin_error']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Form de Cadastro -->
        <div class="card" style="background: #fff; border-radius: 8px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px;">
            <h2 style="margin-top: 0; text-align: center; color: #2c3e50;">➕ Cadastrar Novo Aluno</h2>
            
            <form method="POST" action="index.php?action=cadastrar_aluno">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label for="nome"><strong>Nome:</strong></label>
                        <input type="text" id="nome" name="nome" placeholder="Nome completo" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div>
                        <label for="email"><strong>E-mail:</strong></label>
                        <input type="email" id="email" name="email" placeholder="email@escola.com" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div>
                        <label for="senha"><strong>Senha (min. 4 caracteres):</strong></label>
                        <input type="password" id="senha" name="senha" minlength="4" placeholder="••••••••" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <div>
                        <label for="idade"><strong>Idade (14-21):</strong></label>
                        <input type="number" id="idade" name="idade" min="14" max="21" value="15" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div>
                        <label for="nivel_tea"><strong>Nível TEA:</strong></label>
                        <select id="nivel_tea" name="nivel_tea" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;">
                            <option value="suporte1">Suporte 1</option>
                            <option value="suporte2">Suporte 2</option>
                            <option value="suporte3">Suporte 3</option>
                        </select>
                    </div>
                    <div>
                        <label for="escola"><strong>Escola:</strong></label>
                        <input type="text" id="escola" name="escola" placeholder="Nome da Escola" style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div>
                        <label for="turma"><strong>Turma:</strong></label>
                        <input type="text" id="turma" name="turma" placeholder="Ex: 1º EM A" style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                </div>

                <div style="text-align: center;">
                    <button type="submit" style="background-color: #27ae60; color: white; border: none; padding: 10px 25px; border-radius: 5px; cursor: pointer; font-weight: bold; font-size: 1rem;">
                        ✔ Cadastrar Aluno
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabela de Alunos -->
        <div class="card" style="background: #fff; border-radius: 8px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <h2 style="margin-top: 0; text-align: center; color: #2c3e50;">📋 Lista de Alunos</h2>

            <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                <thead>
                    <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; text-align: left;">
                        <th style="padding: 10px;">ID</th>
                        <th style="padding: 10px;">Nome</th>
                        <th style="padding: 10px;">E-mail</th>
                        <th style="padding: 10px;">Nível TEA</th>
                        <th style="padding: 10px;">Turma</th>
                        <th style="padding: 10px; text-align: center;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                  <?php if (!empty($alunos)): ?>
   <?php if (!empty($alunos)): ?>
    <?php foreach ($alunos as $aluno): ?>
        <tr>
            <td><?= htmlspecialchars($aluno['id'] ?? $aluno['aluno_id']) ?></td>
            <td><?= htmlspecialchars($aluno['nome']) ?></td>
            <td><?= htmlspecialchars($aluno['email']) ?></td>
            <td><span class="badge"><?= htmlspecialchars($aluno['nivel_tea']) ?></span></td>
            <td><?= htmlspecialchars($aluno['turma']) ?></td>
            <td class="acoes-col" style="display: flex; gap: 8px;">
                <!-- Botão Editar -->
                <a href="index.php?view=editar_aluno&id=<?= $aluno['id'] ?? $aluno['aluno_id'] ?>" class="btn-acao btn-editar" style="background: #3498db; color: #fff; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 0.85rem;">
                    ✏️ Editar
                </a>
                
                <!-- Botão Excluir -->
                <a href="index.php?action=deletar_aluno&id=<?= $aluno['id'] ?? $aluno['aluno_id'] ?>" onclick="return confirm('Deseja realmente excluir este aluno?');" class="btn-acao btn-excluir" style="background: #e74c3c; color: #fff; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 0.85rem;">Excluir</a>
                    
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="6" style="text-align: center;">Nenhum aluno cadastrado.</td>
    </tr>
<?php endif; ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 15px; color: #6c757d;">Nenhum aluno cadastrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>