<?php
/**
 * app/views/aluno/exercicio.php
 * Interface do passo a passo para resolução de equações no EquaTEA.
 * Com integração ao sistema de progresso do aluno.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Captura e validação dos parâmetros via URL
$equacaoId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 1;
$passo     = filter_input(INPUT_GET, 'passo', FILTER_VALIDATE_INT) ?: 1;
$erro      = filter_input(INPUT_GET, 'erro', FILTER_VALIDATE_INT) ?: 0;
$debug     = filter_input(INPUT_GET, 'debug', FILTER_VALIDATE_INT) ?: 0;

// SE O PASSO FOR MAIOR QUE 4, REDIRECIONA DIRETO PARA A TELA DE PARABÉNS
if ($passo > 4) {
    header("Location: index.php?view=parabens&id={$equacaoId}");
    exit;
}

// 2. Tenta carregar a equação ativa do Model se fornecida pelo Controller
$equacaoDados = null;
if (isset($equacao) && is_array($equacao) && !empty($equacao)) {
    $equacaoDados = $equacao;
} elseif (class_exists('Equacao')) {
    try {
        $model = new Equacao();
        $equacaoDados = $model->getById($equacaoId);
    } catch (Throwable $e) {
        $equacaoDados = null;
    }
}

// Fallback de segurança para testes
if (!$equacaoDados || !is_array($equacaoDados)) {
    $equacaoDados = [
        'id'          => $equacaoId,
        'a'           => 2,
        'b'           => 5,
        'c'           => 11,
        'dificuldade' => 'facil'
    ];
}

// Coeficientes da equação ATUAL do exercício
$a = (int)($equacaoDados['a'] ?? 2);
$b = (int)($equacaoDados['b'] ?? 5);
$c = (int)($equacaoDados['c'] ?? 11);

// Formatação da equação
$termoA = ($a === 1) ? 'x' : (($a === -1) ? '-x' : "{$a}x");
$sinalB = ($b >= 0) ? '+ ' . $b : '- ' . abs($b);
$bInvertido = -$b;
$sinalBInvertido = ($bInvertido >= 0) ? '+ ' . $bInvertido : '- ' . abs($bInvertido);
$ladoDireitoResolvido = $c - $b;
$solucaoFinal = ($a !== 0) ? ($ladoDireitoResolvido / $a) : 0;

// 3. EVOLUÇÃO VISUAL DA EQUAÇÃO
switch ($passo) {
    case 1:
        $expressaoAtual = "{$termoA} {$sinalB} = {$c}";
        break;
    case 2:
        $expressaoAtual = "{$termoA} {$sinalB} = {$c}";
        break;
    case 3:
        $expressaoAtual = "{$termoA} = {$c} {$sinalBInvertido}";
        break;
    case 4:
        $expressaoAtual = "{$termoA} = {$ladoDireitoResolvido}";
        break;
    default:
        $expressaoAtual = "{$termoA} {$sinalB} = {$c}";
        break;
}

// 4. Lógica para gerar o EXEMPLO PARALELO
$exemploA = ($a === 3) ? 2 : 3;
$exemploB = ($b === 5) ? 4 : 5;
$exemploC = ($c === 20) ? 14 : 20;

$exemploTermoA = ($exemploA === 1) ? 'x' : "{$exemploA}x";
$exemploSinalB = ($exemploB >= 0) ? '+ ' . $exemploB : '- ' . abs($exemploB);
$exemploBInvertido = -$exemploB;
$exemploSinalInvertido = ($exemploBInvertido >= 0) ? '+ ' . $exemploBInvertido : '- ' . abs($exemploBInvertido);
$exemploLadoDireito = $exemploC - $exemploB;
$exemploSolucao = ($exemploA !== 0) ? ($exemploLadoDireito / $exemploA) : 0;

// Configuração de instruções, exemplos e DESTINO DO FORMULÁRIO por PASSO
switch ($passo) {
    case 1:
        $tituloPasso   = "Passo 1: Identifique o termo com X";
        $descricao     = "Escreva qual é o termo que contém a variável X.";
        $textoExemplo  = "Se a equação fosse <b>{$exemploTermoA} {$exemploSinalB} = {$exemploC}</b>, a resposta seria <b>{$exemploTermoA}</b>";
        $placeholder   = "Ex: {$exemploTermoA}";
        $textoBotao    = "Validar Passo 1 ➔";
        break;

    case 2:
        $tituloPasso   = "Passo 2: Isolar o termo com X";
        $descricao     = "Passe o termo independente para o outro lado mudando o sinal.";
        $textoExemplo  = "Se a equação fosse <b>{$exemploTermoA} {$exemploSinalB} = {$exemploC}</b>, a resposta seria <b>{$exemploTermoA} = {$exemploC} {$exemploSinalInvertido}</b>";
        $placeholder   = "Ex: {$exemploTermoA} = {$exemploC} " . ($exemploBInvertido >= 0 ? "+ {$exemploBInvertido}" : "- " . abs($exemploBInvertido));
        $textoBotao    = "Validar Passo 2 ➔";
        break;

    case 3:
        $tituloPasso   = "Passo 3: Resolver a operação do lado direito";
        $descricao     = "Calcule o resultado numérico do lado direito do sinal de igual.";
        $textoExemplo  = "Se a equação fosse <b>{$exemploTermoA} = {$exemploC} {$exemploSinalInvertido}</b>, a resposta seria <b>{$exemploTermoA} = {$exemploLadoDireito}</b>";
        $placeholder   = "Ex: {$exemploTermoA} = {$exemploLadoDireito}";
        $textoBotao    = "Validar Passo 3 ➔";
        break;

    case 4:
        $tituloPasso   = "Passo 4: Encontrar o valor de X";
        $descricao     = "Divida o valor do lado direito pelo coeficiente de X.";
        $textoExemplo  = "Se a equação fosse <b>{$exemploTermoA} = {$exemploLadoDireito}</b>, a resposta seria <b>x = {$exemploSolucao}</b>";
        $placeholder   = "Ex: x = {$exemploSolucao}";
        $textoBotao    = "Concluir Exercício 🎉";
        break;

    default:
        $textoBotao    = "Concluir Exercício 🎉";
        break;
}

// 5. Busca o progresso atual do aluno para esta equação
$progresso_atual = null;
$tentativas_realizadas = 0;
$aluno_id = $_SESSION['aluno_id'] ?? null;

if ($aluno_id && class_exists('ProgressoAluno')) {
    try {
        $progressoModel = new ProgressoAluno();
        $progresso_atual = $progressoModel->getByAlunoEquacao($aluno_id, $equacaoId);
        if ($progresso_atual && is_array($progresso_atual)) {
            $tentativas_realizadas = $progresso_atual['tentativas'] ?? 0;
        }
    } catch (Exception $e) {
        // Mantém valores padrão
    }
}

// 6. Mensagem de erro ou sucesso
$mensagem_erro = $_SESSION['erro_resposta'] ?? null;
unset($_SESSION['erro_resposta']);

$mensagem_sucesso = $_SESSION['sucesso_resposta'] ?? null;
unset($_SESSION['sucesso_resposta']);

// 7. Verifica se há tentativas registradas para este passo
$tentativas_passo = 0;
if ($aluno_id && class_exists('ProgressoAluno')) {
    try {
        $progressoModel = new ProgressoAluno();
        if (method_exists($progressoModel, 'getTentativasPasso')) {
            $tentativas_passo = $progressoModel->getTentativasPasso($aluno_id, $equacaoId, $passo);
        }
    } catch (Exception $e) {
        // Mantém valor padrão
        error_log("Erro ao buscar tentativas do passo: " . $e->getMessage());
    }
}

// 8. Resposta esperada para debug
$respostaEsperada = '';
$termoX = ($a === 1) ? 'x' : (($a === -1) ? '-x' : "{$a}x");
switch ($passo) {
    case 1:
        $respostaEsperada = $termoX;
        break;
    case 2:
        $respostaEsperada = $termoX . ' = ' . ($c - $b);
        break;
    case 3:
        $respostaEsperada = $termoX . ' = ' . ($c - $b);
        break;
    case 4:
        $respostaEsperada = 'x = ' . (($a !== 0) ? (($c - $b) / $a) : 0);
        break;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EquaTEA - Resolução Passos</title>
    <style>
        :root {
            --bg-dark: #121212;
            --card-dark: #1e1e1e;
            --card-inner: #292929;
            --primary-green: #2ecc71;
            --primary-cyan: #00d2d3;
            --text-light: #ffffff;
            --text-muted: #aaaaaa;
            --border-color: #333333;
            --error-color: #e74c3c;
            --success-color: #2ecc71;
            --debug-bg: #1a1a2e;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .exercise-card {
            background-color: var(--card-dark);
            border-radius: 12px;
            width: 100%;
            max-width: 600px;
            padding: 30px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.5);
            border: 1px solid var(--border-color);
        }

        .header-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .header-title h2 {
            margin: 0;
            color: var(--primary-green);
            font-size: 1.25rem;
        }

        .badge-id {
            background-color: var(--card-inner);
            color: var(--text-muted);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.8rem;
        }

        .badge-progresso {
            background-color: var(--card-inner);
            color: var(--primary-cyan);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.8rem;
            border: 1px solid var(--primary-cyan);
        }

        .badge-tentativas {
            background-color: var(--card-inner);
            color: #f39c12;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.8rem;
            border: 1px solid #f39c12;
        }

        .equation-box {
            background-color: var(--card-inner);
            border-left: 4px solid var(--primary-green);
            border-radius: 6px;
            padding: 20px;
            text-align: center;
            margin-bottom: 25px;
        }

        .equation-box small {
            color: var(--text-muted);
            letter-spacing: 1px;
            font-size: 0.75rem;
            display: block;
            margin-bottom: 8px;
        }

        .equation-box .expression {
            font-size: 2.2rem;
            font-weight: bold;
            letter-spacing: 1px;
            color: var(--text-light);
        }

        .step-title {
            color: var(--primary-cyan);
            font-size: 1.1rem;
            margin: 0 0 6px 0;
        }

        .step-desc {
            color: #cccccc;
            font-size: 0.9rem;
            margin: 0 0 12px 0;
        }

        .exemplo-box {
            background-color: #242424;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 0.85rem;
            color: #dddddd;
            margin-bottom: 20px;
            border: 1px solid #333;
        }

        .exemplo-tag {
            background-color: #333;
            color: #f1c40f;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
            margin-right: 6px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .input-response {
            width: 100%;
            background-color: var(--card-inner);
            border: 2px solid var(--primary-green);
            border-radius: 6px;
            padding: 12px 15px;
            color: var(--text-light);
            font-size: 1rem;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .input-response:focus {
            border-color: var(--primary-cyan);
            box-shadow: 0 0 0 3px rgba(0, 210, 211, 0.2);
        }

        .input-response.error {
            border-color: var(--error-color);
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.2);
        }

        .input-response.success {
            border-color: var(--success-color);
            box-shadow: 0 0 0 3px rgba(46, 204, 113, 0.2);
        }

        .btn-submit {
            width: 100%;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
        }

        .btn-submit:hover {
            background-color: #219150;
        }

        .btn-submit:active {
            transform: scale(0.98);
        }

        .btn-submit:disabled {
            background-color: #555;
            cursor: not-allowed;
        }

        .mensagem-erro {
            background-color: rgba(231, 76, 60, 0.15);
            border: 1px solid var(--error-color);
            color: #ff6b6b;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        .mensagem-sucesso {
            background-color: rgba(46, 204, 113, 0.15);
            border: 1px solid var(--success-color);
            color: #55efc4;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        .info-tentativas {
            color: var(--text-muted);
            font-size: 0.8rem;
            text-align: right;
            margin-top: 5px;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #3498db;
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: #5dade2;
            text-decoration: underline;
        }

        .nav-links {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            gap: 10px;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: #3498db;
            text-decoration: none;
            font-size: 0.85rem;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .nav-links a:hover {
            background-color: rgba(52, 152, 219, 0.1);
            text-decoration: underline;
        }

        /* ===== DEBUG STYLES ===== */
        .debug-panel {
            background: var(--debug-bg);
            padding: 15px;
            margin: 15px 0;
            border-radius: 6px;
            font-size: 12px;
            font-family: 'Courier New', monospace;
            max-height: 300px;
            overflow: auto;
            color: #aaa;
            border: 1px solid #2a2a4a;
        }

        .debug-panel strong {
            color: #f1c40f;
        }

        .debug-panel .debug-success {
            color: var(--success-color);
        }

        .debug-panel .debug-error {
            color: var(--error-color);
        }

        .debug-panel .debug-info {
            color: var(--primary-cyan);
        }

        .debug-toggle {
            background: transparent;
            border: 1px solid #444;
            color: #888;
            padding: 4px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.75rem;
            transition: all 0.2s;
        }

        .debug-toggle:hover {
            border-color: var(--primary-cyan);
            color: var(--primary-cyan);
        }

        .debug-toggle.active {
            border-color: var(--primary-cyan);
            color: var(--primary-cyan);
            background: rgba(0, 210, 211, 0.1);
        }

        @media (max-width: 480px) {
            .exercise-card {
                padding: 20px;
            }

            .equation-box .expression {
                font-size: 1.6rem;
            }

            .header-title {
                flex-direction: column;
                align-items: flex-start;
            }

            .nav-links {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>

    <div class="exercise-card">
        <div class="header-title">
            <h2>📐 EquaTEA - Resolução</h2>
            <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                <span class="badge-id">Equação #<?php echo htmlspecialchars((string)$equacaoId); ?></span>
                <span class="badge-progresso">Passo <?php echo (int)$passo; ?>/4</span>
                <?php if ($tentativas_realizadas > 0): ?>
                    <span class="badge-tentativas">
                        🔄 <?php echo $tentativas_realizadas; ?> tentativa(s)
                    </span>
                <?php endif; ?>
                <button class="debug-toggle <?php echo $debug ? 'active' : ''; ?>" 
                        onclick="toggleDebug()" 
                        title="Ativar/Desativar modo debug">
                    🐞 Debug
                </button>
            </div>
        </div>

        <div class="equation-box">
            <small>EQUAÇÃO ATUAL (PASSO <?php echo (int)$passo; ?>):</small>
            <div class="expression"><?php echo htmlspecialchars($expressaoAtual); ?></div>
        </div>

        <h3 class="step-title"><?php echo htmlspecialchars($tituloPasso); ?></h3>
        <p class="step-desc"><?php echo htmlspecialchars($descricao); ?></p>

        <div class="exemplo-box">
            <span class="exemplo-tag">💡 Exemplo:</span>
            <span><?php echo $textoExemplo; ?></span>
        </div>

        <?php if ($mensagem_erro): ?>
            <div class="mensagem-erro">
                ❌ <?php echo htmlspecialchars($mensagem_erro); ?>
                <?php if ($tentativas_passo > 1): ?>
                    <br><small>Você já tentou <?php echo $tentativas_passo; ?> vez(es) este passo. Continue tentando!</small>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($mensagem_sucesso): ?>
            <div class="mensagem-sucesso">
                ✅ <?php echo htmlspecialchars($mensagem_sucesso); ?>
            </div>
        <?php endif; ?>

        <?php if ($tentativas_passo > 3): ?>
            <div style="background-color: rgba(241, 196, 15, 0.1); border: 1px solid #f1c40f; color: #f1c40f; padding: 10px 14px; border-radius: 6px; margin-bottom: 15px; font-size: 0.85rem;">
                💡 Dica: Reveja o exemplo acima e tente novamente. Preste atenção nos sinais e operações!
            </div>
        <?php endif; ?>

        <!-- Formulário com Action apontando para o método verificarResposta -->
        <form action="index.php?action=verificar_resposta" method="POST">
            <input type="hidden" name="equacao_id" value="<?php echo (int)$equacaoId; ?>">
            <input type="hidden" name="passo_atual" value="<?php echo (int)$passo; ?>">
            
            <div class="form-group">
                <label for="resposta">Sua Resposta:</label>
                <input type="text" 
                       id="resposta" 
                       name="resposta" 
                       class="input-response <?php echo $mensagem_erro ? 'error' : ''; ?>" 
                       placeholder="<?php echo htmlspecialchars($placeholder); ?>" 
                       required 
                       autocomplete="off"
                       autofocus>
            </div>

            <button type="submit" class="btn-submit">
                <?php echo htmlspecialchars($textoBotao); ?>
            </button>
        </form>

        <div class="nav-links">
            <a href="index.php?view=aluno/dashboard">⬅ Voltar ao Dashboard</a>
            <?php if ($passo > 1): ?>
                <a href="index.php?view=exercicio&id=<?php echo $equacaoId; ?>&passo=<?php echo $passo - 1; ?>">⬅ Passo anterior</a>
            <?php endif; ?>
            <?php if ($passo < 4): ?>
                <a href="index.php?view=exercicio&id=<?php echo $equacaoId; ?>&passo=<?php echo $passo + 1; ?>">Próximo passo ➔</a>
            <?php endif; ?>
        </div>

        <!-- Informações de progresso -->
        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <div style="color: var(--text-muted); font-size: 0.75rem;">
                🔹 Equação: <?php echo htmlspecialchars($termoA . ' ' . $sinalB . ' = ' . $c); ?>
            </div>
            <div style="color: var(--text-muted); font-size: 0.75rem;">
                🔹 Dificuldade: <span style="text-transform: capitalize;"><?php echo htmlspecialchars($equacaoDados['dificuldade'] ?? 'Fácil'); ?></span>
            </div>
            <?php if ($tentativas_realizadas > 0): ?>
                <div style="color: var(--text-muted); font-size: 0.75rem;">
                    🔹 Tentativas: <?php echo $tentativas_realizadas; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ===== PANEL DE DEBUG ===== -->
        <div id="debugPanel" class="debug-panel" style="display: <?php echo $debug ? 'block' : 'none'; ?>;">
            <strong>🐞 DEBUG INFO</strong><br><br>
            
            <span class="debug-info">📌 DADOS DA EQUAÇÃO</span><br>
            ID: <?php echo $equacaoId; ?><br>
            a = <?php echo $a; ?>, b = <?php echo $b; ?>, c = <?php echo $c; ?><br>
            Termo X: <?php echo $termoX; ?><br>
            Solução final: x = <?php echo $solucaoFinal; ?><br>
            <br>
            
            <span class="debug-info">📌 DADOS DO PASSO ATUAL</span><br>
            Passo: <?php echo $passo; ?><br>
            Resposta esperada: <span class="debug-success"><?php echo htmlspecialchars($respostaEsperada); ?></span><br>
            Expressão atual: <?php echo htmlspecialchars($expressaoAtual); ?><br>
            <br>
            
            <span class="debug-info">👤 DADOS DO ALUNO</span><br>
            Aluno ID: <?php echo $aluno_id ?: 'NÃO LOGADO'; ?><br>
            Tentativas neste passo: <?php echo $tentativas_passo; ?><br>
            Tentativas totais: <?php echo $tentativas_realizadas; ?><br>
            Progresso: <?php echo $progresso_atual ? 'Encontrado' : 'Não encontrado'; ?><br>
            <?php if ($progresso_atual): ?>
                Passo atual no banco: <?php echo $progresso_atual['passo_atual'] ?? 'N/A'; ?><br>
                Concluída: <?php echo ($progresso_atual['concluida'] ?? 0) ? '✅ Sim' : '❌ Não'; ?><br>
            <?php endif; ?>
            <br>
            
            <span class="debug-info">📝 SESSÃO</span><br>
            <?php 
            $sessionInfo = [
                'usuario_id' => $_SESSION['usuario_id'] ?? 'N/A',
                'aluno_id' => $_SESSION['aluno_id'] ?? 'N/A',
                'usuario_nome' => $_SESSION['usuario_nome'] ?? 'N/A',
                'tipo_perfil' => $_SESSION['tipo_perfil'] ?? 'N/A'
            ];
            foreach ($sessionInfo as $key => $value) {
                echo $key . ': ' . htmlspecialchars((string)$value) . '<br>';
            }
            ?>
            <br>
            
            <span class="debug-info">💡 VALIDAÇÃO ESPERADA</span><br>
            <?php if ($passo == 1): ?>
                Deve ser exatamente: <span class="debug-success"><?php echo $termoX; ?></span>
            <?php elseif ($passo == 2): ?>
                Aceita: <span class="debug-success"><?php echo $termoX . ' = ' . ($c - $b); ?></span>, 
                <span class="debug-success"><?php echo $c . ' - ' . $b; ?></span> ou 
                <span class="debug-success"><?php echo ($c - $b); ?></span>
            <?php elseif ($passo == 3): ?>
                Deve ser: <span class="debug-success"><?php echo $termoX . ' = ' . ($c - $b); ?></span>
            <?php elseif ($passo == 4): ?>
                Aceita: <span class="debug-success">x = <?php echo $solucaoFinal; ?></span> ou 
                <span class="debug-success"><?php echo $solucaoFinal; ?></span>
            <?php endif; ?>
        </div>
        <!-- ===== FIM PANEL DE DEBUG ===== -->

    </div>

    <script>
        function toggleDebug() {
            const panel = document.getElementById('debugPanel');
            const btn = document.querySelector('.debug-toggle');
            if (panel.style.display === 'none' || panel.style.display === '') {
                panel.style.display = 'block';
                btn.classList.add('active');
                // Adiciona parâmetro debug na URL sem recarregar
                const url = new URL(window.location.href);
                url.searchParams.set('debug', '1');
                window.history.replaceState({}, '', url);
            } else {
                panel.style.display = 'none';
                btn.classList.remove('active');
                const url = new URL(window.location.href);
                url.searchParams.delete('debug');
                window.history.replaceState({}, '', url);
            }
        }

        // Se o debug estiver ativo via URL, abre o painel
        <?php if ($debug): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('debugPanel').style.display = 'block';
            document.querySelector('.debug-toggle').classList.add('active');
        });
        <?php endif; ?>

        // Foco automático no campo de resposta
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('resposta');
            if (input) {
                input.focus();
                // Seleciona todo o texto se já tiver conteúdo
                if (input.value) {
                    input.select();
                }
            }
        });
    </script>

</body>
</html>