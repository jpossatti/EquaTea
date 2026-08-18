<?php
/**
 * test_registro_erro.php
 * Testa as funcionalidades do modelo RegistroErro.
 * 
 * Execução: php tests/test_registro_erro.php
 */

require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/models/RegistroErro.php';
require_once __DIR__ . '/../app/models/Equacao.php';

echo "=== TESTE DO MODELO REGISTRO_ERRO ===\n\n";

$registro = new RegistroErro();
$equacao = new Equacao();

$aluno_id = 1;
$equacao_id = 1;

// ============================================================
// 1. REGISTRAR UM ERRO
// ============================================================

echo "1. Registrando erro...\n";

$resultado = $registro->registrar(
    $aluno_id,
    $equacao_id,
    2, // Passo 2
    'operacao_inversa',
    '2x = 7 + 3', // Resposta errada
    '2x = 7 - 3'  // Resposta esperada
);

if ($resultado) {
    echo "   ✅ Erro registrado!\n";
} else {
    echo "   ❌ Falha ao registrar erro!\n";
}

// ============================================================
// 2. LISTAR ERROS POR ALUNO
// ============================================================

echo "\n2. Listando erros do aluno...\n";

$erros = $registro->getByAluno($aluno_id);

if (!empty($erros)) {
    echo "   ✅ Encontrados " . count($erros) . " erros:\n";
    foreach (array_slice($erros, 0, 5) as $e) {
        echo "   - Passo {$e['passo']}: {$e['tipo_erro']} - {$e['data_erro']}\n";
    }
    if (count($erros) > 5) {
        echo "   ... e mais " . (count($erros) - 5) . " erros\n";
    }
} else {
    echo "   ❌ Nenhum erro encontrado!\n";
}

// ============================================================
// 3. OBTER ESTATÍSTICAS DE ERROS
// ============================================================

echo "\n3. Obtendo estatísticas de erros...\n";

$estatisticas = $registro->getEstatisticas($aluno_id);

if (!empty($estatisticas)) {
    echo "   ✅ Estatísticas encontradas:\n";
    foreach ($estatisticas as $e) {
        echo "   - {$e['tipo_erro']}: {$e['quantidade']} ocorrências (Passo {$e['passo']})\n";
    }
} else {
    echo "   ❌ Nenhuma estatística encontrada!\n";
}

// ============================================================
// 4. OBTER RELATÓRIO COMPLETO
// ============================================================

echo "\n4. Obtendo relatório completo...\n";

$relatorio = $registro->getRelatorioCompleto();

if (!empty($relatorio)) {
    echo "   ✅ Relatório gerado com " . count($relatorio) . " registros:\n";
    foreach (array_slice($relatorio, 0, 3) as $r) {
        echo "   - {$r['aluno']}: {$r['equacao']} - Passo {$r['passo']} - {$r['tipo_erro']}\n";
    }
    if (count($relatorio) > 3) {
        echo "   ... e mais " . (count($relatorio) - 3) . " registros\n";
    }
} else {
    echo "   ❌ Nenhum registro encontrado!\n";
}

// ============================================================
// 5. TESTAR IDENTIFICAÇÃO DE TIPO DE ERRO
// ============================================================

echo "\n5. Testando identificação de tipo de erro...\n";

$equacao_dados = $equacao->getById(1); // x + 3 = 7

$testes = [
    ['resposta' => '2x = 7 + 3', 'passo' => 2, 'esperado' => 'sinal_trocado'],
    ['resposta' => 'x = 7 + 3', 'passo' => 2, 'esperado' => 'operacao_inversa'],
    ['resposta' => 'x = 10', 'passo' => 3, 'esperado' => 'calculo_errado'],
    ['resposta' => 'x = 5', 'passo' => 4, 'esperado' => 'divisao_incorreta'],
];

foreach ($testes as $teste) {
    $tipo = $registro->identificarTipoErro($equacao_dados, $teste['passo'], $teste['resposta']);
    $status = $tipo === $teste['esperado'] ? '✅' : '❌';
    echo "   $status Resposta '{$teste['resposta']}' (Passo {$teste['passo']}) -> {$tipo}\n";
}

echo "\n✅ Teste do modelo RegistroErro concluído!\n";