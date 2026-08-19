<?php
/**
 * gerenciar_equacoes.php
 * View para gestão de equações sem restrição no coeficiente C
 */
$page_title = 'Gerenciar Equações - EquaTEA';
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
        .navbar-professor {
            background-color: #2c3e50;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .navbar-brand { color: #fff; font-size: 1.2rem; font-weight: bold; text-decoration: none; }
        .navbar-brand .tea { color: #3498db; }
        .nav-menu { display: flex; gap: 15px; list-style: none; margin: 0; padding: 0; }
        .nav-link { color: #ecf0f1; text-decoration: none; padding: 8px 14px; border-radius: 4px; font-weight: 500; }
        .nav-link:hover, .nav-link.active { background-color: #34495e; color: #fff; }
        .nav-link.btn-sair { background-color: #e74c3c; }
        .preview-box {
            background: #f8f9fa;
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body style="margin: 0; background-color: #f4f6f9; font-family: Arial, sans-serif;">

    <!-- Menu Superior do Professor -->
    <nav class="navbar-professor">
        <a href="index.php?view=professor" class="navbar-brand">
            <span>Equa</span><span class="tea">TEA</span> | Painel do Professor
        </a>
        <ul class="nav-menu">
            <li><a href="index.php?view=professor" class="nav-link">📊 Dashboard</a></li>
            <li><a href="index.php?view=gerenciar_alunos" class="nav-link">🎓 Gerenciar Alunos</a></li>
            <li><a href="index.php?view=gerenciar_equacoes" class="nav-link active">📐 Gerenciar Equações</a></li>
            <li><a href="index.php?view=relatorio" class="nav-link">📈 Relatórios</a></li>
            <li><a href="index.php?view=login" class="nav-link btn-sair">🚪 Sair</a></li>
        </ul>
    </nav>

    <div class="container" style="max-width: 950px; margin: 30px auto; padding: 0 15px;">
        
        <h1 style="text-align: center; color: #2c3e50;">📐 Gerenciar Equações</h1>

        <!-- Mensagens de Alerta -->
        <?php if (!empty($_SESSION['admin_success'])): ?>
            <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                <?php echo $_SESSION['admin_success']; unset($_SESSION['admin_success']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['admin_error'])): ?>
            <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                <?php echo $_SESSION['admin_error']; unset($_SESSION['admin_error']); ?>
            </div>
        <?php endif; ?>

        <!-- Formulário de Cadastro de Equação -->
        <div class="card" style="background: #fff; border-radius: 8px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px;">
            <h2 style="margin-top: 0; text-align: center; color: #2c3e50;">➕ Cadastrar Nova Equação</h2>
            
            <form method="POST" action="index.php?action=cadastrar_equacao" id="formEquacao">
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
                        <!-- Coeficiente C sem restrição de min/max -->
                        <label for="c"><strong>Coeficiente c *</strong></label>
                        <input type="number" id="c" name="c" placeholder="Ex: 43" required style="width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 4px;" oninput="atualizarPreview()">
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
                <div class="preview-box">
                    <span style="color: #64748b; margin-right: 10px;">Pré-visualização:</span>
                    <strong id="previewEquacao" style="font-size: 1.2rem; color: #1e293b;">ax + b = c</strong>
                    <span id="previewResultado" style="margin-left: 15px; padding: 4px 10px; border-radius: 4px; font-weight: bold; background-color: #e2e8f0; color: #475569;">
                        x = ?
                    </span>
                </div>

                <div style="text-align: center; gap: 10px; display: flex; justify-content: center;">
                    <button type="submit" style="background-color: #27ae60; color: white; border: none; padding: 10px 25px; border-radius: 5px; cursor: pointer; font-weight: bold;">
                        ✔ Cadastrar Equação
                    </button>
                    <button type="reset" onclick="setTimeout(atualizarPreview, 50)" style="background-color: #95a5a6; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">
                        🔄 Limpar
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabela com a Lista de Equações -->
        <div class="card" style="background: #fff; border-radius: 8px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <h2 style="margin-top: 0; text-align: center; color: #2c3e50;">📋 Lista de Equações</h2>

            <table class="list-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Equação</th>
            <th>Solução (x)</th>
            <th>Dificuldade</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($equacoes)): ?>
            <?php foreach ($equacoes as $eq): ?>
                <tr>
                    <td><?= $eq['id'] ?></td>
                    <td><?= $eq['a'] ?>x + <?= $eq['b'] ?> = <?= $eq['c'] ?></td>
                    <td><?= $eq['solucao'] ?></td>
                    <td>
                        <span class="badge <?= strtolower($eq['dificuldade'] ?? 'facil') ?>">
                         <?= htmlspecialchars($eq['dificuldade'] ?? 'Fácil') ?>
                        </span>
                    </td>
                   <!-- Dentro do seu loop foreach das equações -->
<td>
    <!-- Botão Editar -->
    <a href="index.php?view=editar_equacao&id=<?= $eq['id'] ?>" class="btn-acao btn-editar" title="Editar">✏️</a>
    
    <!-- Botão Excluir -->
    <a href="index.php?action=deletar_equacao&id=<?= $eq['id'] ?>" 
       onclick="return confirm('Deseja realmente excluir esta equação?');" 
       class="btn-acao btn-excluir" title="Excluir">🗑️</a>
</td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">Nenhuma equação cadastrada.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
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

            const sinalB = b >= 0 ? `+ ${b}` : `- ${Math.abs(b)}`;
            elemEq.innerText = `${a}x ${sinalB} = ${c}`;

            const x = (c - b) / a;

            if (Number.isInteger(x)) {
                elemRes.innerText = `✔ x = ${x}`;
                elemRes.style.backgroundColor = "#d1e7dd";
                elemRes.style.color = "#0f5132";
            } else {
                elemRes.innerText = `⚠️ x = ${x.toFixed(2)} (não inteiro)`;
                elemRes.style.backgroundColor = "#fff3cd";
                elemRes.style.color = "#664d03";
            }
        }
    </script>
</body>
</html>