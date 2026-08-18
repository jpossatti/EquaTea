<?php
/**
 * test_equacao.php
 * Testa as funcionalidades do modelo Equacao.
 * 
 * Execução: php tests/test_equacao.php
 */

require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/models/Equacao.php';

echo "=== TESTE DO MODELO EQUACAO ===\n\n";

$equacao = new Equacao();

// ============================================================
// 1. LISTAR TODAS AS EQUAÇÕES
// ============================================================

echo "1. Listando todas as equações...\n";

$equacoes = $equacao->getAll();

if (!empty($equacoes)) {
    echo "   ✅ Encontradas " . count($equacoes) . " equações:\n";
    foreach (array_slice($equacoes, 0, 5) as $e) {
        $sinal = $e['b'] >= 0 ? '+' : '-';
        echo "   - {$e['a']}x {$sinal} " . abs($e['b']) . " = {$e['c']} (x = {$e['solucao']}) - {$e['dificuldade']}\n";
    }
    if (count($equacoes) > 5) {
        echo "   ... e mais " . (count($equacoes) - 5) . " equações\n";
    }
} else {
    echo "   ❌ Nenhuma equação encontrada!\n";
}

// ============================================================
// 2. LISTAR POR DIFICULDADE
// ============================================================

echo "\n2. Listando equações por dificuldade...\n";

foreach (['facil', 'medio', 'dificil'] as $dificuldade) {
    $lista = $equacao->getAll($dificuldade);
    echo "   - $dificuldade: " . count($lista) . " equações\n";
}

// ============================================================
// 3. BUSCAR EQUAÇÃO ALEATÓRIA
// ============================================================

echo "\n3. Buscando equação aleatória...\n";

$aleatoria = $equacao->getRandom();

if ($aleatoria) {
    $sinal = $aleatoria['b'] >= 0 ? '+' : '-';
    echo "   ✅ Equação aleatória:\n";
    echo "   - {$aleatoria['a']}x {$sinal} " . abs($aleatoria['b']) . " = {$aleatoria['c']}\n";
    echo "   - Solução: x = {$aleatoria['solucao']}\n";
    echo "   - Dificuldade: {$aleatoria['dificuldade']}\n";
} else {
    echo "   ❌ Nenhuma equação encontrada!\n";
}

// ============================================================
// 4. TESTAR VALIDAÇÃO DE RESPOSTA
// ============================================================

echo "\n4. Testando validação de resposta...\n";

$equacao_id = 1; // Primeira equação (x + 3 = 7)
$passos = [
    1 => ['resposta' => 'x + 3 = 7', 'esperado' => true],
    2 => ['resposta' => 'x = 7 - 3', 'esperado' => true],
    3 => ['resposta' => '4', 'esperado' => true],
    4 => ['resposta' => '4', 'esperado' => true],
];

foreach ($passos as $passo => $dados) {
    $valido = $equacao->validarResposta($equacao_id, $passo, $dados['resposta']);
    $status = $valido === $dados['esperado'] ? '✅' : '❌';
    echo "   $status Passo $passo: '{$dados['resposta']}' -> " . ($valido ? 'CORRETO' : 'ERRADO') . "\n";
}

// ============================================================
// 5. TESTAR CRIAÇÃO DE EQUAÇÃO
// ============================================================

echo "\n5. Testando criação de equação...\n";

$novo_id = $equacao->criar(3, 4, 19, 'medio'); // 3x + 4 = 19 → x = 5

if ($novo_id) {
    echo "   ✅ Equação criada com sucesso! ID: $novo_id\n";
    
    $dados = $equacao->getById($novo_id);
    $sinal = $dados['b'] >= 0 ? '+' : '-';
    echo "   - {$dados['a']}x {$sinal} " . abs($dados['b']) . " = {$dados['c']}\n";
    echo "   - Solução: x = {$dados['solucao']}\n";
} else {
    echo "   ❌ Falha ao criar equação (verifique se a solução é inteira)!\n";
}

echo "\n✅ Teste do modelo Equacao concluído!\n";