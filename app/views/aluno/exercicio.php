<?php
/**
 * app/views/aluno/exercicio.php
 * Interface do Exercício Guiado com Evolução Dinâmica de Passos (EquaTEA)
 */

if (!isset($equacao) || empty($equacao)) {
    echo "<div style='color:red; padding:20px;'>Erro: Nenhuma equação válida foi carregada para visualização.</div>";
    return;
}

// Parâmetros de contexto
$passoAtual = filter_input(INPUT_GET, 'passo', FILTER_VALIDATE_INT) ?: 1;
$status     = filter_input(INPUT_GET, 'status', FILTER_DEFAULT);

// Coeficientes do banco de dados
$a = (int)$equacao['a'];
$b = (int)$equacao['b'];
$c = (int)$equacao['c'];

$termoA   = ($a === 1) ? '1x' : (($a === -1) ? '-1x' : "{$a}x");
$sinalB   = ($b >= 0) ? '+' : '-';
$absB     = abs($b);
$sinalInverso = ($b >= 0) ? '-' : '+';
$resultadoDireito = $c - $b;
$valorFinalX      = ($a !== 0) ? ($resultadoDireito / $a) : 0;

// Construção da "Equação Atual" baseada no passo
switch ($passoAtual) {
    case 1:
    case 2:
        $expressaoAtual = "{$termoA} {$sinalB} {$absB} = {$c}";
        break;
    case 3:
        $expressaoAtual = "{$termoA} = {$c} {$sinalInverso} {$absB}";
        break;
    case 4:
        $expressaoAtual = "{$termoA} = {$resultadoDireito}";
        break;
    default:
        $expressaoAtual = "{$termoA} {$sinalB} {$absB} = {$c}";
}

// Configuração das instruções e dicas por passo
$instrucoesPassos = [
    1 => [
        'titulo'    => 'Passo 1: Identifique o termo com X',
        'descricao' => 'Escreva qual é o termo que contém a variável X.',
        'exemplo'   => "Exemplo: {$termoA}",
        'ph'        => "Ex: {$termoA}"
    ],
    2 => [
        'titulo'    => 'Passo 2: Isole o termo com X',
        'descricao' => "Passe o número para o outro lado mudando o sinal.",
        'exemplo'   => "Exemplo: {$termoA} = {$c} {$sinalInverso} {$absB}",
        'ph'        => "Ex: {$termoA} = {$c} {$sinalInverso} {$absB}"
    ],
    3 => [
        'titulo'    => 'Passo 3: Simplifique a operação',
        'descricao' => "Calcule o resultado da conta do lado direito.",
        'exemplo'   => "Exemplo: {$termoA} = {$resultadoDireito}",
        'ph'        => "Ex: {$termoA} = {$resultadoDireito}"
    ],
    4 => [
        'titulo'    => 'Passo 4: Encontre o valor final de X',
        'descricao' => "Descubra o valor final da incógnita X.",
        'exemplo'   => "Exemplo: x = {$valorFinalX}",
        'ph'        => "Ex: x = {$valorFinalX}"
    ]
];

$infoPasso = $instrucoesPassos[$passoAtual] ?? $instrucoesPassos[1];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EquaTEA - Resolução Passos</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #121212;
            color: #e0e0e0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .card-container {
            background-color: #1e1e1e;
            border: 1px solid #333;
            border-radius: 12px;
            width: 100%;
            max-width: 600px;
            padding: 30px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.5);
        }

        .header {
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 1.2rem;
            color: #00ff66;
        }

        .badge-id {
            background-color: #2a2a2a;
            color: #aaa;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        .equacao-display {
            background-color: #262626;
            border-left: 5px solid #00ff66;
            padding: 18px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }

        .equacao-display span {
            display: block;
            font-size: 0.85rem;
            color: #aaa;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .equacao-display h2 {
            font-size: 2.2rem;
            color: #ffffff;
            letter-spacing: 2px;
        }

        /* Histórico de Evolução dos Passos */
        .historico-passos {
            background-color: #181818;
            border: 1px dashed #444;
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .historico-passos h4 {
            color: #888;
            margin-bottom: 8px;
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        .historico-item {
            color: #00ff66;
            margin-bottom: 4px;
            font-family: monospace;
        }

        .passo-box {
            margin-bottom: 20px;
        }

        .passo-box h3 {
            color: #00d9ff;
            font-size: 1.15rem;
            margin-bottom: 8px;
        }

        .passo-box p {
            color: #ccc;
            font-size: 0.95rem;
            margin-bottom: 10px;
        }

        .exemplo-dica {
            background-color: #262626;
            color: #f1fa8c;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-family: monospace;
            display: inline-block;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            background-color: #2a2a2a;
            border: 1px solid #00ff66;
            border-radius: 6px;
            color: #fff;
            font-size: 1.1rem;
            outline: none;
        }

        .alert-erro {
            background-color: #421313;
            color: #ff79c6;
            border: 1px solid #ff5555;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .btn-enviar {
            width: 100%;
            padding: 14px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-enviar:hover {
            background-color: #218838;
        }

        .footer-nav {
            margin-top: 20px;
            text-align: center;
        }

        .footer-nav a {
            color: #888;
            text-decoration: none;
            font-size: 0.85rem;
        }

        .footer-nav a:hover {
            color: #fff;
        }
    </style>
</head>
<body>

<div class="card-container">
    <div class="header">
        <h1>EquaTEA - Resolução Passos</h1>
        <span class="badge-id">Equação #<?php echo (int)$equacao['id']; ?></span>
    </div>

    <!-- Bloco Dinâmico da Equação Atual conforme o Passo -->
    <div class="equacao-display">
        <span>Equação Atual (Passo <?php echo (int)$passoAtual; ?>):</span>
        <h2><?php echo htmlspecialchars($expressaoAtual); ?></h2>
    </div>

    <!-- Histórico Visual dos Passos Anteriores -->
    <?php if ($passoAtual > 1): ?>
        <div class="historico-passos">
            <h4>Evolução da Equação:</h4>
            <div class="historico-item">✓ Equação Inicial: <?php echo "{$termoA} {$sinalB} {$absB} = {$c}"; ?></div>
            <?php if ($passoAtual >= 3): ?>
                <div class="historico-item">✓ Isolar o termo: <?php echo "{$termoA} = {$c} {$sinalInverso} {$absB}"; ?></div>
            <?php endif; ?>
            <?php if ($passoAtual >= 4): ?>
                <div class="historico-item">✓ Simplificado: <?php echo "{$termoA} = {$resultadoDireito}"; ?></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($status === 'erro'): ?>
        <div class="alert-erro">
            ⚠️ Resposta incorreta para este passo. Verifique os cálculos e a formatação e tente novamente!
        </div>
    <?php endif; ?>

    <div class="passo-box">
        <h3><?php echo htmlspecialchars($infoPasso['titulo']); ?></h3>
        <p><?php echo htmlspecialchars($infoPasso['descricao']); ?></p>
        <div class="exemplo-dica"><?php echo htmlspecialchars($infoPasso['exemplo']); ?></div>
    </div>

    <form action="/index.php?action=verificar_resposta" method="POST">
        <input type="hidden" name="equacao_id" value="<?php echo (int)$equacao['id']; ?>">
        <input type="hidden" name="passo_atual" value="<?php echo (int)$passoAtual; ?>">

        <div class="form-group">
            <label for="resposta">Sua Resposta:</label>
            <input 
                type="text" 
                id="resposta" 
                name="resposta" 
                class="form-control" 
                placeholder="<?php echo htmlspecialchars($infoPasso['ph']); ?>" 
                required 
                autocomplete="off"
                autofocus
            >
        </div>

        <button type="submit" class="btn-enviar">Validar Passo <?php echo (int)$passoAtual; ?> ➔</button>
    </form>

    <div class="footer-nav">
        <a href="/index.php?view=dashboard">⬅️ Voltar ao Dashboard</a>
    </div>
</div>

</body>
</html>