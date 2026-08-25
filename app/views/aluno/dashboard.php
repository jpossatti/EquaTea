<?php
/**
 * app/views/aluno/dashboard.php
 * Painel do Aluno - Com controle de sessão e status das equações
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

// Atualiza o tempo da sessão
$_SESSION['login_time'] = time();
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
                $aluno_id = $aluno['id'] ?? null;
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
        error_log("Erro ao buscar progresso: " . $e->getMessage());
    }
}

// 4. BUSCA DAS EQUAÇÕES COM STATUS
$equacoes = [];
$concluidas = 0;
$pendentes = 0;
$em_andamento = 0;

try {
    if (class_exists('Equacao')) {
        $equacaoModel = new Equacao();
        
        // Verifica se o método existe
        if (method_exists($equacaoModel, 'getEquacoesComStatus')) {
            // Usa o método que já retorna com status
            $equacoes = $equacaoModel->getEquacoesComStatus($aluno_id);
        } else {
            // Fallback: busca todas e adiciona status manualmente
            $equacoesBD = $equacaoModel->buscarTodas();
            
            if (!empty($equacoesBD) && is_array($equacoesBD)) {
                foreach ($equacoesBD as $eq) {
                    // Busca o status para cada equação
                    if (method_exists($equacaoModel, 'getStatusEquacao')) {
                        $status = $equacaoModel->getStatusEquacao($aluno_id, $eq['id']);
                        $eq['status_progresso'] = $status['status_progresso'] ?? 'Pendente';
                        $eq['concluida'] = $status['concluida'] ?? false;
                        $eq['passo_atual'] = $status['passo_atual'] ?? null;
                    } else {
                        $eq['status_progresso'] = 'Pendente';
                        $eq['concluida'] = false;
                        $eq['passo_atual'] = null;
                    }
                    
                    // Gera a equação formatada
                    $a = (int)($eq['a'] ?? 1);
                    $b = (int)($eq['b'] ?? 0);
                    $c = (int)($eq['c'] ?? 0);
                    $termoA = ($a === 1) ? 'x' : (($a === -1) ? '-x' : "{$a}x");
                    $sinalB = ($b >= 0) ? '+ ' . $b : '- ' . abs($b);
                    $eq['equacao_formatada'] = "{$termoA} {$sinalB} = {$c}";
                    
                    $equacoes[] = $eq;
                }
            }
        }
        
        // Conta os status
        if (!empty($equacoes)) {
            foreach ($equacoes as $eq) {
                $status = $eq['status_progresso'] ?? 'Pendente';
                if ($status === 'Concluído' || ($eq['concluida'] ?? false)) {
                    $concluidas++;
                } elseif (($eq['passo_atual'] ?? 0) > 0) {
                    $em_andamento++;
                } else {
                    $pendentes++;
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("Erro ao buscar equações: " . $e->getMessage());
    $equacoes = [];
}

// 5. SE NÃO HOUVER EQUAÇÕES, USA LISTA DE EXEMPLO
if (empty($equacoes)) {
    $equacoes = [
        ['id' => 1, 'a' => 2, 'b' => 5, 'c' => 11, 'dificuldade' => 'facil', 'status_progresso' => 'Pendente', 'concluida' => false, 'passo_atual' => null, 'equacao_formatada' => '2x + 5 = 11'],
        ['id' => 2, 'a' => 3, 'b' => 4, 'c' => 19, 'dificuldade' => 'medio', 'status_progresso' => 'Pendente', 'concluida' => false, 'passo_atual' => null, 'equacao_formatada' => '3x + 4 = 19'],
        ['id' => 3, 'a' => 1, 'b' => 6, 'c' => 9, 'dificuldade' => 'facil', 'status_progresso' => 'Pendente', 'concluida' => false, 'passo_atual' => null, 'equacao_formatada' => 'x + 6 = 9']
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
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .stat-card.green { border-left-color: var(--accent-green); }
        .stat-card.orange { border-left-color: #f39c12; }
        .stat-card.purple { border-left-color: #9b59b6; }

        .stat-icon { font-size: 1.8rem; }

        .stat-info h4 {
            margin: 0;
            font-size: 0.8rem;
            color: #777;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-info .value {
            font-size: 1.4rem;
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

        .equation-display {
            font-family: 'Courier New', monospace;
            font-size: 0.95rem;
            color: var(--text-color);
            background: #f8f9fa;
            padding: 2px 8px;
            border-radius: 4px;
        }

        /* BADGES */
        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .badge-pendente { 
            background-color: #f8f9fa; 
            color: #6c757d; 
            border: 1px solid #dee2e6;
        }

        .badge-andamento { 
            background-color: #fff3cd; 
            color: #856404; 
            border: 1px solid #ffc107;
        }

        .badge-concluido { 
            background-color: #d4edda; 
            color: #155724; 
            border: 1px solid #28a745;
        }

        .badge-dificuldade {
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-facil { background-color: #d4edda; color: #155724; }
        .badge-medio { background-color: #fff3cd; color: #856404; }
        .badge-dificil { background-color: #f8d7da; color: #721c24; }

        /* BUTTON ACTION */
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 16px;
            background-color: var(--accent-blue);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.8rem;
            transition: background-color 0.2s, transform 0.2s;
        }

        .btn-action:hover {
            background-color: #2980b9;
            transform: scale(1.02);
        }

        .btn-action:active {
            transform: scale(0.98);
        }

        .btn-action.btn-view {
            background-color: #6c757d;
        }

        .btn-action.btn-view:hover {
            background-color: #5a6268;
        }

        .btn-action.btn-continue {
            background-color: #f39c12;
        }

        .btn-action.btn-continue:hover {
            background-color: #d68910;
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

        /* PROGRESS SUMMARY */
        .progress-summary {
            display: flex;
            gap: 20px;
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            flex-wrap: wrap;
            justify-content: center;
        }

        .summary-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 5px 20px;
        }

        .summary-number {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--text-color);
        }

        .summary-label {
            font-size: 0.7rem;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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

        .footer-info p {
            margin: 5px 0;
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
                grid-template-columns: repeat(2, 1fr);
            }

            .stat-card {
                padding: 12px 15px;
            }

            table {
                font-size: 0.8rem;
            }

            th, td {
                padding: 8px 10px;
            }

            .card-table {
                padding: 15px;
            }

            .progress-summary {
                gap: 10px;
            }

            .summary-item {
                padding: 5px 10px;
            }
        }

        @media (max-width: 480px) {
            header .logo {
                font-size: 1.1rem;
            }

            .welcome-card h2 {
                font-size: 1.2rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .stat-info .value {
                font-size: 1.1rem;
            }

            .btn-action {
                font-size: 0.7rem;
                padding: 4px 10px;
            }

            .badge {
                font-size: 0.7rem;
                padding: 3px 8px;
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
                    <div class="value" style="font-size: 1rem; margin-top: 6px;">
                        <?php echo $dados_progresso['nivel_atual'] ?? 'Nível 1 - Básico'; ?>
                    </div>
                </div>
            </div>

            <div class="stat-card purple">
                <div class="stat-icon">📚</div>
                <div class="stat-info">
                    <h4>Total de Equações</h4>
                    <div class="value"><?php echo count($equacoes); ?></div>
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
                <div class="table-responsive">
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
                                    // Pega os dados da equação
                                    $eq_id = (int)($eq['id'] ?? 0);
                                    $eq_text = $eq['equacao_formatada'] ?? '';
                                    
                                    // Se não tiver equacao_formatada, gera
                                    if (empty($eq_text)) {
                                        $a = (int)($eq['a'] ?? 1);
                                        $b = (int)($eq['b'] ?? 0);
                                        $c = (int)($eq['c'] ?? 0);
                                        $termoA = ($a === 1) ? 'x' : (($a === -1) ? '-x' : "{$a}x");
                                        $sinalB = ($b >= 0) ? '+ ' . $b : '- ' . abs($b);
                                        $eq_text = "{$termoA} {$sinalB} = {$c}";
                                    }
                                    
                                    // Determina o status
                                    $concluida = $eq['concluida'] ?? false;
                                    $passo_atual = $eq['passo_atual'] ?? null;
                                    $status = $eq['status_progresso'] ?? 'Pendente';
                                    
                                    // Define classes e ícones para cada status
                                    $statusClass = '';
                                    $statusIcon = '';
                                    $statusText = '';
                                    $actionText = '';
                                    $actionClass = '';
                                    $actionLink = '';
                                    
                                    if ($concluida || $status === 'Concluído') {
                                        $statusClass = 'badge-concluido';
                                        $statusIcon = '✅';
                                        $statusText = 'Concluído';
                                        $actionText = 'Ver';
                                        $actionClass = 'btn-view';
                                        $actionLink = 'index.php?view=parabens&id=' . $eq_id;
                                    } elseif ($passo_atual && $passo_atual > 0 && $passo_atual < 4) {
                                        $statusClass = 'badge-andamento';
                                        $statusIcon = '🔄';
                                        $statusText = 'Passo ' . $passo_atual . '/4';
                                        $actionText = 'Continuar';
                                        $actionClass = 'btn-continue';
                                        $actionLink = 'index.php?view=exercicio&id=' . $eq_id . '&passo=' . $passo_atual;
                                    } else {
                                        $statusClass = 'badge-pendente';
                                        $statusIcon = '📝';
                                        $statusText = 'Pendente';
                                        $actionText = 'Resolver';
                                        $actionClass = '';
                                        $actionLink = 'index.php?view=exercicio&id=' . $eq_id . '&passo=1';
                                    }
                                    
                                    // Dificuldade
                                    $dificuldade = $eq['dificuldade'] ?? 'facil';
                                    $dificuldadeLabel = ucfirst($dificuldade);
                                ?>
                                <tr>
                                    <td><?php echo $eq_id; ?></td>
                                    <td>
                                        <span class="equation-display">
                                            <?php echo htmlspecialchars($eq_text); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-dificuldade badge-<?php echo strtolower($dificuldade); ?>">
                                            <?php echo $dificuldadeLabel; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo $statusIcon . ' ' . $statusText; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo $actionLink; ?>" class="btn-action <?php echo $actionClass; ?>">
                                            <?php echo $actionText; ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- RESUMO DE PROGRESSO -->
                <div class="progress-summary">
                    <div class="summary-item">
                        <span class="summary-number"><?php echo count($equacoes); ?></span>
                        <span class="summary-label">Total</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-number" style="color: #28a745;"><?php echo $concluidas; ?></span>
                        <span class="summary-label">✅ Concluídas</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-number" style="color: #f39c12;"><?php echo $em_andamento; ?></span>
                        <span class="summary-label">🔄 Em andamento</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-number" style="color: #6c757d;"><?php echo $pendentes; ?></span>
                        <span class="summary-label">📝 Pendentes</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- RODAPÉ INFORMATIVO -->
        <div class="footer-info">
            <p>💡 Dica: Resolva as equações passo a passo. Cada passo correto te aproxima da solução final!</p>
            <p style="margin-top: 5px;">📚 EquaTEA - Aprendendo equações de 1º grau de forma divertida</p>
        </div>

    </div>

</body>
</html>