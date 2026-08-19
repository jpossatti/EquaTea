<?php
/**
 * app/views/aluno/dashboard.php
 * Painel principal do aluno com suporte a coeficientes dinâmicos (a, b, c) do banco de dados.
 */

// Garantia de fallback contra erros de variáveis indefinidas
$aluno = $aluno ?? [
    'nome'  => 'Aluno Teste',
    'email' => 'aluno@equatea.com'
];

$dados_progresso = $dados_progresso ?? [
    'total_resolvidas' => 0,
    'taxa_acerto'      => '0%',
    'nivel_atual'      => 'Nível 1 - Básico'
];

// Array de equações seguro para fallback (utilizando os coeficientes reais)
$equacoes = $equacoes ?? [
    ['id' => 1, 'a' => 1, 'b' => 3, 'c' => 7,  'dificuldade' => 'Fácil', 'status' => 'Pendente'],
    ['id' => 2, 'a' => 2, 'b' => -4, 'c' => 10, 'dificuldade' => 'Fácil', 'status' => 'Concluído'],
    ['id' => 3, 'a' => 1, 'b' => 2, 'c' => 8,  'dificuldade' => 'Fácil', 'status' => 'Pendente']
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EquaTEA - Dashboard do Aluno</title>
    <style>
        :root {
            --primary-color: #2b3a4a;
            --accent-green: #2ecc71;
            --accent-blue: #3498db;
            --bg-light: #f4f7f6;
            --card-bg: #ffffff;
            --text-color: #333333;
            --border-color: #e0e0e0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            margin: 0;
            padding: 0;
            color: var(--text-color);
        }

        /* Cabeçalho superior */
        header {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header .logo {
            font-size: 1.4rem;
            font-weight: bold;
        }

        header .logo span {
            color: var(--accent-blue);
        }

        /* Menu de Navegação */
        nav {
            background-color: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 10px 24px;
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        nav a {
            text-decoration: none;
            color: #555;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        nav a:hover, nav a.active {
            background-color: #f0f0f0;
            color: var(--primary-color);
        }

        /* Conteúdo Principal */
        .container {
            max-width: 950px;
            margin: 30px auto;
            padding: 0 15px;
        }

        .welcome-card {
            background: linear-gradient(135deg, var(--primary-color), #3a4d61);
            color: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.08);
        }

        .welcome-card h2 {
            margin: 0 0 8px 0;
            font-size: 1.6rem;
        }

        .welcome-card p {
            margin: 0;
            opacity: 0.9;
        }

        /* Cards de Estatísticas */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 15px;
            border-left: 5px solid var(--accent-blue);
        }

        .stat-card.green {
            border-left-color: var(--accent-green);
        }

        .stat-card.orange {
            border-left-color: #f39c12;
        }

        .stat-icon {
            font-size: 2rem;
        }

        .stat-info h4 {
            margin: 0;
            font-size: 0.85rem;
            color: #777;
            text-transform: uppercase;
        }

        .stat-info .value {
            font-size: 1.5rem;
            font-weight: bold;
            margin-top: 4px;
        }

        /* Tabela de Equações */
        .card-table {
            background-color: var(--card-bg);
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .card-table h3 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 1.2rem;
            color: var(--primary-color);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
        }

        th {
            background-color: #f8f9fa;
            color: #555;
            font-weight: 600;
        }

        /* Badges de Status */
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        /* Botão de Ação */
        .btn-action {
            display: inline-block;
            padding: 6px 14px;
            background-color: var(--accent-blue);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: background-color 0.2s;
        }

        .btn-action:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

    <header>
        <div class="logo">Equa<span>TEA</span> <small style="font-size:0.7rem; font-weight:normal; opacity:0.8;">Aprendendo equações</small></div>
        <div>
            <span style="font-size:0.85rem; margin-right:10px;">🕹️ Modo Teste</span>
            <a href="index.php?view=login" style="color:white; text-decoration:none; background:#e74c3c; padding:4px 12px; border-radius:4px; font-size:0.85rem;">Sair</a>
        </div>
    </header>

    <nav>
        <a href="index.php?view=dashboard" class="active">📊 Dashboard</a>
        <a href="index.php?view=exercicio">📝 Novo Exercício</a>
        <a href="index.php?view=parabens">🎉 Concluído</a>
        <a href="index.php?view=login">🚪 Sair</a>
    </nav>

    <div class="container">
        
        <!-- Cartão de Boas-Vindas -->
        <div class="welcome-card">
            <h2>Olá, <?php echo htmlspecialchars($aluno['nome']); ?>! 👋</h2>
            <p>Seja bem-vindo ao seu painel. Escolha uma atividade abaixo para começar a resolver as equações passo a passo.</p>
        </div>

        <!-- Estatísticas do Aluno -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🎯</div>
                <div class="stat-info">
                    <h4>Exercícios Resolvidos</h4>
                    <div class="value"><?php echo is_array($dados_progresso) ? ($dados_progresso['total_resolvidas'] ?? 0) : 0; ?></div>
                </div>
            </div>

            <div class="stat-card green">
                <div class="stat-icon">📈</div>
                <div class="stat-info">
                    <h4>Taxa de Acertos</h4>
                    <div class="value"><?php echo is_array($dados_progresso) ? ($dados_progresso['taxa_acerto'] ?? '0%') : '0%'; ?></div>
                </div>
            </div>

            <div class="stat-card orange">
                <div class="stat-icon">⭐</div>
                <div class="stat-info">
                    <h4>Nível Atual</h4>
                    <div class="value" style="font-size: 1.1rem; margin-top:8px;">
                        <?php echo is_array($dados_progresso) ? ($dados_progresso['nivel_atual'] ?? 'Nível 1') : 'Nível 1'; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela com Exercícios Disponíveis -->
        <div class="card-table">
            <h3>📘 Equações Disponíveis</h3>

            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Equação</th>
                        <th>Dificuldade</th>
                        <th>Status</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($equacoes) && is_iterable($equacoes)): ?>
                        <?php foreach ($equacoes as $eq): ?>
                            <?php
                                // Se o banco devolver o texto pronto na chave 'expressao', ele é mantido.
                                // Caso contrário, ele é construído dinamicamente via coeficientes 'a', 'b' e 'c'.
                                if (!empty($eq['expressao'])) {
                                    $textoEquacao = $eq['expressao'];
                                } else {
                                    $a = (int)($eq['a'] ?? 1);
                                    $b = (int)($eq['b'] ?? 0);
                                    $c = (int)($eq['c'] ?? 0);

                                    $termoA = ($a === 1) ? '1x' : (($a === -1) ? '-1x' : "{$a}x");
                                    $sinalB = ($b >= 0) ? '+ ' . $b : '- ' . abs($b);
                                    $textoEquacao = "{$termoA} {$sinalB} = {$c}";
                                }
                            ?>
                            <tr>
                                <td><?php echo (int)($eq['id'] ?? 1); ?></td>
                                <td><strong><?php echo htmlspecialchars($textoEquacao); ?></strong></td>
                                <td style="text-transform: capitalize;"><?php echo htmlspecialchars($eq['dificuldade'] ?? 'Fácil'); ?></td>
                                <td>
                                    <?php if (($eq['status'] ?? '') === 'Concluído'): ?>
                                        <span class="badge badge-success">Concluído</span>
                                    <?php else: ?>
                                        <span class="badge badge-pending">Pendente</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="index.php?view=exercicio&id=<?php echo (int)($eq['id'] ?? 1); ?>&passo=1" class="btn-action">
                                        ▶️ Resolver
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center; color:#888;">Nenhuma equação encontrada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>