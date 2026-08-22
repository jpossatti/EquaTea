<?php
/**
 * app/views/aluno/dashboard.php
 * Painel do Aluno - Com controle de sessão
 */

// ===== CONTROLE DE SESSÃO =====
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?view=login');
    exit;
}

// Verifica se é aluno
if (($_SESSION['tipo_perfil'] ?? '') !== 'aluno') {
    header('Location: index.php?view=login');
    exit;
}

// Verifica expiração da sessão (30 minutos)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 1800)) {
    session_destroy();
    header('Location: index.php?view=login');
    exit;
}
// ===== FIM CONTROLE DE SESSÃO =====

// 1. CARREGAMENTO DAS DEPENDÊNCIAS
$root_path = dirname(__DIR__, 3);
require_once $root_path . '/app/config/Database.php';
require_once $root_path . '/app/models/Equacao.php';
require_once $root_path . '/app/models/Aluno.php';

// Verifica se o arquivo ProgressoAluno existe
$progresso_path = $root_path . '/app/models/ProgressoAluno.php';
if (file_exists($progresso_path)) {
    require_once $progresso_path;
    $progresso_disponivel = true;
} else {
    $progresso_disponivel = false;
}

// 2. DADOS DO ALUNO
$aluno_id = $_SESSION['aluno_id'] ?? null;
$usuario_id = $_SESSION['usuario_id'] ?? null;
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Aluno';
$usuario_email = $_SESSION['email'] ?? 'aluno@equatea.com';

// Busca dados do aluno de forma segura
$aluno = null;

if ($aluno_id && class_exists('Aluno')) {
    try {
        $alunoModel = new Aluno();
        // Tenta buscar pelo ID do aluno
        $aluno = $alunoModel->getById($aluno_id);
        
        // Se não encontrou, tenta buscar pelo ID do usuário
        if (!$aluno || !is_array($aluno)) {
            $aluno = $alunoModel->getByUsuarioId($usuario_id);
            if ($aluno && is_array($aluno)) {
                // Atualiza o aluno_id na sessão
                $_SESSION['aluno_id'] = $aluno['id'] ?? null;
            }
        }
    } catch (Exception $e) {
        $aluno = null;
    }
}

// Se não encontrou ou deu erro, cria array com dados da sessão
if (!$aluno || !is_array($aluno)) {
    $aluno = [
        'id' => $aluno_id,
        'aluno_id' => $aluno_id,
        'nome' => $usuario_nome,
        'email' => $usuario_email,
        'idade' => null,
        'nivel_tea' => null,
        'escola' => null,
        'turma' => null
    ];
}

// Garante que o nome está presente
if (!isset($aluno['nome']) || empty($aluno['nome'])) {
    $aluno['nome'] = $usuario_nome;
}

// Garante que o email está presente
if (!isset($aluno['email']) || empty($aluno['email'])) {
    $aluno['email'] = $usuario_email;
}

// 3. BUSCA DO PROGRESSO
$dados_progresso = [
    'total_resolvidas' => 0,
    'taxa_acerto' => '0%',
    'nivel_atual' => 'Nível 1 - Básico'
];

if ($aluno_id && $progresso_disponivel && class_exists('ProgressoAluno')) {
    try {
        $progressoModel = new ProgressoAluno();
        $progresso = $progressoModel->getEstatisticas($aluno_id);
        if ($progresso && is_array($progresso)) {
            $dados_progresso = array_merge($dados_progresso, $progresso);
        }
    } catch (Exception $e) {
        // Mantém os dados padrão
    }
}

// 4. BUSCA DAS EQUAÇÕES
$equacoes = [];
try {
    if (class_exists('Equacao')) {
        $equacaoModel = new Equacao();
        $equacoesBD = $equacaoModel->buscarTodas();
        
        if (!empty($equacoesBD) && is_array($equacoesBD)) {
            $equacoes = $equacoesBD;
        }
    }
} catch (Exception $e) {
    // Mantém $equacoes vazio
}

// 5. SE NÃO HOUVER EQUAÇÕES, USA LISTA DE EXEMPLO
if (empty($equacoes)) {
    $equacoes = [
        ['id' => 1, 'a' => 1, 'b' => 3, 'c' => 7, 'dificuldade' => 'Fácil', 'status' => 'Pendente'],
        ['id' => 2, 'a' => 2, 'b' => -4, 'c' => 10, 'dificuldade' => 'Fácil', 'status' => 'Pendente'],
        ['id' => 3, 'a' => 1, 'b' => 2, 'c' => 8, 'dificuldade' => 'Fácil', 'status' => 'Pendente']
    ];
}

// 6. DEFINE O NOME DO ALUNO PARA EXIBIÇÃO
$nome_aluno = $aluno['nome'] ?? $usuario_nome;

// 7. Define a view atual para o menu
$view = 'aluno/dashboard';
$GLOBALS['current_view'] = 'aluno/dashboard';
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
        header {
            background-color: var(--primary-color);
            color: white;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        header .logo {
            font-size: 1.4rem;
            font-weight: bold;
        }

        header .logo span {
            color: var(--accent-blue);
        }

        header .logo small {
            font-size: 0.7rem;
            font-weight: normal;
            opacity: 0.8;
        }

        header .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        header .user-name {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        header .btn-logout {
            color: white;
            text-decoration: none;
            background: #e74c3c;
            padding: 6px 14px;
            border-radius: 4px;
            font-size: 0.85rem;
            transition: background 0.3s;
        }

        header .btn-logout:hover {
            background: #c0392b;
        }

        /* NAVEGAÇÃO */
        nav {
            background-color: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 10px 24px;
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
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

        /* CONTAINER */
        .container {
            max-width: 950px;
            margin: 30px auto;
            padding: 0 15px;
        }

        /* WELCOME CARD */
        .welcome-card {
            background: linear-gradient(135deg, var(--primary-color), #3a4d61);
            color: white;
            padding: 25px 30px;
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

        /* STATS GRID */
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
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .stat-card.green { border-left-color: var(--accent-green); }
        .stat-card.orange { border-left-color: #f39c12; }

        .stat-icon { font-size: 2rem; }

        .stat-info h4 {
            margin: 0;
            font-size: 0.85rem;
            color: #777;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-info .value {
            font-size: 1.5rem;
            font-weight: bold;
            margin-top: 4px;
            color: var(--primary-color);
        }

        /* CARD TABLE */
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

        /* TABLE */
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

        tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* BADGES */
        .badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-pending { 
            background-color: #fff3cd; 
            color: #856404; 
        }

        .badge-success { 
            background-color: #d4edda; 
            color: #155724; 
        }

        .badge-dificuldade {
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-facil { background-color: #d4edda; color: #155724; }
        .badge-medio { background-color: #fff3cd; color: #856404; }
        .badge-dificil { background-color: #f8d7da; color: #721c24; }

        /* BUTTON ACTION */
        .btn-action {
            display: inline-block;
            padding: 6px 14px;
            background-color: var(--accent-blue);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: background-color 0.2s, transform 0.2s;
        }

        .btn-action:hover {
            background-color: #2980b9;
            transform: scale(1.02);
        }

        .btn-action:active {
            transform: scale(0.98);
        }

        /* ALERTS */
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #888;
        }

        .empty-state .icon {
            font-size: 3rem;
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: 1rem;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }

            nav {
                flex-direction: column;
                align-items: center;
                gap: 8px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .stat-card {
                padding: 15px;
            }

            table {
                font-size: 14px;
            }

            th, td {
                padding: 8px 10px;
            }

            .card-table {
                padding: 15px;
                overflow-x: auto;
            }
        }

        @media (max-width: 480px) {
            header .logo {
                font-size: 1.1rem;
            }

            .welcome-card h2 {
                font-size: 1.2rem;
            }

            .stat-info .value {
                font-size: 1.2rem;
            }

            .btn-action {
                font-size: 0.75rem;
                padding: 4px 10px;
            }
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <header>
        <div class="logo">
            Equa<span>TEA</span> 
            <small>Aprendendo equações</small>
        </div>
        <div class="user-info">
            <span class="user-name">👤 <?php echo htmlspecialchars($nome_aluno); ?></span>
            <a href="index.php?view=logout" class="btn-logout" onclick="return confirm('Deseja realmente sair?');">
                🚪 Sair
            </a>
        </div>
    </header>

    <!-- NAVEGAÇÃO -->
    <nav>
        <a href="index.php?view=aluno/dashboard" class="active">📊 Dashboard</a>
        <a href="index.php?view=exercicio">📝 Novo Exercício</a>
        <a href="index.php?view=parabens">🎉 Concluído</a>
        <a href="index.php?view=logout" onclick="return confirm('Deseja realmente sair?');">🚪 Sair</a>
    </nav>

    <!-- CONTEÚDO -->
    <div class="container">

        <?php if (isset($erroBanco)): ?>
            <div class="alert-error">
                ⚠️ <?php echo htmlspecialchars($erroBanco); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['admin_success'])): ?>
            <div class="alert-success">
                ✅ <?php echo $_SESSION['admin_success']; unset($_SESSION['admin_success']); ?>
            </div>
        <?php endif; ?>

        <!-- WELCOME -->
        <div class="welcome-card">
            <h2>Olá, <?php echo htmlspecialchars($nome_aluno); ?>! 👋</h2>
            <p>Seja bem-vindo ao seu painel. Escolha uma atividade abaixo para começar a resolver as equações passo a passo.</p>
        </div>

        <!-- ESTATÍSTICAS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🎯</div>
                <div class="stat-info">
                    <h4>Exercícios Resolvidos</h4>
                    <div class="value"><?php echo $dados_progresso['total_resolvidas'] ?? 0; ?></div>
                </div>
            </div>

            <div class="stat-card green">
                <div class="stat-icon">📈</div>
                <div class="stat-info">
                    <h4>Taxa de Acertos</h4>
                    <div class="value"><?php echo $dados_progresso['taxa_acerto'] ?? '0%'; ?></div>
                </div>
            </div>

            <div class="stat-card orange">
                <div class="stat-icon">⭐</div>
                <div class="stat-info">
                    <h4>Nível Atual</h4>
                    <div class="value" style="font-size: 1.1rem; margin-top:8px;">
                        <?php echo $dados_progresso['nivel_atual'] ?? 'Nível 1 - Básico'; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- EQUAÇÕES DISPONÍVEIS -->
        <div class="card-table">
            <h3>📘 Equações Disponíveis</h3>

            <?php if (empty($equacoes) || !is_array($equacoes)): ?>
                <div class="empty-state">
                    <div class="icon">📭</div>
                    <p>Nenhuma equação disponível no momento.</p>
                    <p style="font-size: 0.9rem; color: #aaa;">Volte mais tarde ou entre em contato com seu professor.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
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
                            <?php foreach ($equacoes as $eq): ?>
                                <?php
                                    // Gera o texto da equação
                                    if (!empty($eq['expressao'])) {
                                        $textoEquacao = $eq['expressao'];
                                    } else {
                                        $a = (int)($eq['a'] ?? 1);
                                        $b = (int)($eq['b'] ?? 0);
                                        $c = (int)($eq['c'] ?? 0);

                                        $termoA = ($a === 1) ? 'x' : (($a === -1) ? '-x' : "{$a}x");
                                        $sinalB = ($b >= 0) ? '+ ' . $b : '- ' . abs($b);
                                        $textoEquacao = "{$termoA} {$sinalB} = {$c}";
                                    }
                                    
                                    // Determina o status
                                    $status = $eq['status'] ?? 'Pendente';
                                    $statusClass = ($status === 'Concluído') ? 'badge-success' : 'badge-pending';
                                    
                                    // Determina a dificuldade
                                    $dificuldade = $eq['dificuldade'] ?? 'Fácil';
                                    $dificuldadeClass = 'badge-' . strtolower($dificuldade);
                                ?>
                                <tr>
                                    <td><?php echo (int)($eq['id'] ?? 0); ?></td>
                                    <td><strong><?php echo htmlspecialchars($textoEquacao); ?></strong></td>
                                    <td>
                                        <span class="badge-dificuldade <?php echo $dificuldadeClass; ?>">
                                            <?php echo htmlspecialchars($dificuldade); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo $status; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="index.php?view=exercicio&id=<?php echo (int)($eq['id'] ?? 0); ?>&passo=1" class="btn-action">
                                            ▶️ Resolver
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 4px; text-align: center; color: #6c757d; font-size: 14px;">
                    Total: <strong><?php echo count($equacoes); ?></strong> equação(ões) disponível(eis)
                </div>
            <?php endif; ?>
        </div>

        <!-- RODAPÉ INFORMATIVO -->
        <div style="margin-top: 30px; padding: 15px; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); text-align: center; color: #888; font-size: 0.85rem;">
            <p>💡 Dica: Resolva as equações passo a passo. Cada passo correto te aproxima da solução final!</p>
            <p style="margin-top: 5px;">📚 EquaTEA - Aprendendo equações de 1º grau de forma divertida</p>
        </div>

    </div>

</body>
</html>