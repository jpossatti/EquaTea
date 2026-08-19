<?php
/**
 * app/views/aluno/exercicio.php
 * Interface do passo a passo para resolução de equações no EquaTEA.
 */

// 1. Captura e validação dos parâmetros via URL
$equacaoId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 1;
$passo     = filter_input(INPUT_GET, 'passo', FILTER_VALIDATE_INT) ?: 1;

// SE O PASSO FOR MAIOR QUE 4, REDIRECIONA DIRETO PARA A TELA DE PARABÉNS
if ($passo > 4) {
    header("Location: index.php?view=parabens&id={$equacaoId}");
    exit;
}

// 2. Tenta carregar a equação ativa do Model se fornecida pelo Controller
$equacaoDados = null;
if (isset($equacao) && is_object($equacao) && method_exists($equacao, 'getById')) {
    $equacaoDados = $equacao->getById($equacaoId);
} elseif (class_exists('Equacao')) {
    try {
        $model = new Equacao();
        $equacaoDados = $model->getById($equacaoId);
    } catch (Throwable $e) {
        $equacaoDados = null;
    }
}

// Fallback de segurança para testes
if (!$equacaoDados) {
    $equacaoDados = [
        'id'          => $equacaoId,
        'a'           => 1,
        'b'           => 3,
        'c'           => 7,
        'dificuldade' => 'fácil'
    ];
}

// Coeficientes da equação ATUAL do exercício
$a = (int)($equacaoDados['a'] ?? 1);
$b = (int)($equacaoDados['b'] ?? 0);
$c = (int)($equacaoDados['c'] ?? 0);

$termoA = ($a === 1) ? '1x' : (($a === -1) ? '-1x' : "{$a}x");
$sinalB = ($b >= 0) ? '+ ' . $b : '- ' . abs($b);
$bInvertido = -$b;
$sinalBInvertido = ($bInvertido >= 0) ? '+ ' . $bInvertido : '- ' . abs($bInvertido);
$ladoDireitoResolvido = $c - $b;
$solucaoFinal = ($a !== 0) ? ($ladoDireitoResolvido / $a) : 0;

// 3. EVOLUÇÃO VISUAL DA EQUAÇÃO (Destaque do topo)
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

$exemploTermoA = ($exemploA === 1) ? '1x' : "{$exemploA}x";
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
        $proximoAction = "index.php?view=exercicio&id={$equacaoId}&passo=2";
        $textoBotao    = "Validar Passo 1 ➔";
        break;

    case 2:
        $tituloPasso   = "Passo 2: Isolar o termo com X";
        $descricao     = "Passe o termo independente para o outro lado mudando o sinal.";
        $textoExemplo  = "Se a equação fosse <b>{$exemploTermoA} {$exemploSinalB} = {$exemploC}</b>, a resposta seria <b>{$exemploTermoA} = {$exemploC} {$exemploSinalInvertido}</b>";
        $placeholder   = "Ex: {$exemploTermoA} = {$exemploC} " . ($exemploBInvertido >= 0 ? "+ {$exemploBInvertido}" : "- " . abs($exemploBInvertido));
        $proximoAction = "index.php?view=exercicio&id={$equacaoId}&passo=3";
        $textoBotao    = "Validar Passo 2 ➔";
        break;

    case 3:
        $tituloPasso   = "Passo 3: Resolver a operação do lado direito";
        $descricao     = "Calcule o resultado numérico do lado direito do sinal de igual.";
        $textoExemplo  = "Se a equação fosse <b>{$exemploTermoA} = {$exemploC} {$exemploSinalInvertido}</b>, a resposta seria <b>{$exemploTermoA} = {$exemploLadoDireito}</b>";
        $placeholder   = "Ex: {$exemploTermoA} = {$exemploLadoDireito}";
        $proximoAction = "index.php?view=exercicio&id={$equacaoId}&passo=4";
        $textoBotao    = "Validar Passo 3 ➔";
        break;

    case 4:
        $tituloPasso   = "Passo 4: Encontrar o valor de X";
        $descricao     = "Divida o valor do lado direito pelo coeficiente de X.";
        $textoExemplo  = "Se a equação fosse <b>{$exemploTermoA} = {$exemploLadoDireito}</b>, a resposta seria <b>x = {$exemploSolucao}</b>";
        $placeholder   = "Ex: x = {$exemploSolucao}";
        // No passo 4, o próximo destino é a TELA DE PARABÉNS
        $proximoAction = "index.php?view=parabens&id={$equacaoId}";
        $textoBotao    = "Concluir Exercício 🎉";
        break;

    default:
        $proximoAction = "index.php?view=parabens&id={$equacaoId}";
        $textoBotao    = "Concluir Exercício 🎉";
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
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .exercise-card {
            background-color: var(--card-dark);
            border-radius: 12px;
            width: 100%;
            max-width: 550px;
            padding: 30px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.5);
            border: 1px solid var(--border-color);
        }

        .header-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
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
            border: 1px solid var(--primary-green);
            border-radius: 6px;
            padding: 12px 15px;
            color: var(--text-light);
            font-size: 1rem;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.2s;
        }

        .input-response:focus {
            border-color: var(--primary-cyan);
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
            transition: background-color 0.2s;
        }

        .btn-submit:hover {
            background-color: #219150;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #3498db;
            text-decoration: none;
            font-size: 0.85rem;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="exercise-card">
        
        <!-- Cabeçalho -->
        <div class="header-title">
            <h2>EquaTEA - Resolução Passos</h2>
            <span class="badge-id">Equação #<?php echo htmlspecialchars((string)$equacaoId); ?></span>
        </div>

        <!-- Display da Equação Ativa -->
        <div class="equation-box">
            <small>EQUAÇÃO ATUAL (PASSO <?php echo (int)$passo; ?>):</small>
            <div class="expression"><?php echo htmlspecialchars($expressaoAtual); ?></div>
        </div>

        <!-- Instrução do Passo -->
        <h3 class="step-title"><?php echo htmlspecialchars($tituloPasso); ?></h3>
        <p class="step-desc"><?php echo htmlspecialchars($descricao); ?></p>

        <!-- Bloco do Exemplo Didático Neutro -->
        <div class="exemplo-box">
            <span class="exemplo-tag">Exemplo:</span>
            <span><?php echo $textoExemplo; ?></span>
        </div>

        <!-- Formulário de Envio com Action Dinâmico -->
        <form action="<?php echo htmlspecialchars($proximoAction); ?>" method="POST">
            <div class="form-group">
                <label for="resposta">Sua Resposta:</label>
                <input type="text" 
                       id="resposta" 
                       name="resposta" 
                       class="input-response" 
                       placeholder="<?php echo htmlspecialchars($placeholder); ?>" 
                       required 
                       autocomplete="off"
                       autofocus>
            </div>

            <button type="submit" class="btn-submit">
                <?php echo htmlspecialchars($textoBotao); ?>
            </button>
        </form>

        <a href="index.php?view=dashboard" class="back-link">⬅ Voltar ao Dashboard</a>

    </div>

</body>
</html>