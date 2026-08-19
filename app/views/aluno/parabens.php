<?php
/**
 * app/views/aluno/parabens.php
 * Tela de comemoração após concluir os 4 passos da equação no EquaTEA.
 */

// 1. Captura o ID retornado pela URL (ex: ?view=parabens&id=16)
$equacaoId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 1;

// 2. Tenta carregar a equação específica do banco
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

// 3. Fallback seguro caso a busca falhe
if (!$equacaoDados) {
    $equacaoDados = [
        'id'          => $equacaoId,
        'a'           => 1,
        'b'           => 2,
        'c'           => 8,
        'dificuldade' => 'fácil'
    ];
}

// 4. Cálculos e formatação dos dados para exibição
$a = (int)($equacaoDados['a'] ?? 1);
$b = (int)($equacaoDados['b'] ?? 0);
$c = (int)($equacaoDados['c'] ?? 0);

$termoA = ($a === 1) ? '1x' : (($a === -1) ? '-1x' : "{$a}x");
$sinalB = ($b >= 0) ? '+ ' . $b : '- ' . abs($b);
$enunciado = "{$termoA} {$sinalB} = {$c}";

// Solução final da equação: x = (c - b) / a
$solucaoX = ($a !== 0) ? (($c - $b) / $a) : 0;

$page_title = 'Parabéns! - EquaTEA';
$nome_aluno = $dados_aluno['nome'] ?? ($aluno['nome'] ?? 'Aluno Teste');

// Inclui partials se existirem
if (file_exists(__DIR__ . '/../partials/header.php')) {
    include_once __DIR__ . '/../partials/header.php';
}
if (file_exists(__DIR__ . '/../partials/menu_aluno.php')) {
    include_once __DIR__ . '/../partials/menu_aluno.php';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <style>
        .parabens-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 80vh;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .parabens-card {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: 2px solid #2ecc71;
        }

        .parabens-icon {
            font-size: 4rem;
            margin-bottom: 15px;
            animation: bounce 1s infinite alternate;
        }

        .parabens-card h1 {
            color: #2c3e50;
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .parabens-card p {
            color: #7f8c8d;
            font-size: 1.05rem;
            margin-bottom: 25px;
        }

        .parabens-detalhes {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            border: 1px solid #e9ecef;
        }

        .detalhe-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.95rem;
            color: #495057;
        }

        .detalhe-item strong {
            color: #2ecc71;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .parabens-acoes {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn-proximo, .btn-voltar {
            padding: 12px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.95rem;
            transition: transform 0.1s, opacity 0.2s;
        }

        .btn-proximo {
            background-color: #2ecc71;
            color: #ffffff;
        }

        .btn-voltar {
            background-color: #3498db;
            color: #ffffff;
        }

        .btn-proximo:hover, .btn-voltar:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        @keyframes bounce {
            from { transform: translateY(0); }
            to { transform: translateY(-10px); }
        }
    </style>
</head>
<body>

<main class="container parabens-container">
    <div class="parabens-card">
        <div class="parabens-icon">🎉</div>
        <h1>Parabéns, <?php echo htmlspecialchars($nome_aluno); ?>!</h1>
        <p>Você concluiu a equação com sucesso!</p>

        <div class="parabens-detalhes">
            <div class="detalhe-item">
                <span>📝 Equação Concluída</span>
                <strong><?php echo htmlspecialchars($enunciado); ?></strong>
            </div>
            <div class="detalhe-item">
                <span>🎯 Solução Encontrada</span>
                <strong>x = <?php echo htmlspecialchars((string)$solucaoX); ?></strong>
            </div>
            <div class="detalhe-item">
                <span>📊 Dificuldade</span>
                <strong><?php echo ucfirst(htmlspecialchars($equacaoDados['dificuldade'] ?? 'Fácil')); ?></strong>
            </div>
        </div>

        <div class="parabens-acoes">
            <a href="index.php?view=dashboard" class="btn-proximo">🚀 Escolher Outro Exercício</a>
            <a href="index.php?view=dashboard" class="btn-voltar">📊 Dashboard</a>
        </div>
    </div>
</main>

<?php 
if (file_exists(__DIR__ . '/../partials/footer.php')) {
    include_once __DIR__ . '/../partials/footer.php';
}
?>
</body>
</html>