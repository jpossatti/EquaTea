<?php
/**
 * test_aluno.php
 * Testa as funcionalidades do modelo Aluno.
 * 
 * Execução: php tests/test_aluno.php
 */

require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/models/Usuario.php';
require_once __DIR__ . '/../app/models/Aluno.php';

echo "=== TESTE DO MODELO ALUNO ===\n\n";

$aluno = new Aluno();
$usuario = new Usuario();

// ============================================================
// 1. LISTAR TODOS OS ALUNOS
// ============================================================

echo "1. Listando todos os alunos...\n";

$alunos = $aluno->getAll();

if (!empty($alunos)) {
    echo "   ✅ Encontrados " . count($alunos) . " alunos:\n";
    foreach ($alunos as $a) {
        echo "   - {$a['nome']} (ID: {$a['aluno_id']}) - {$a['nivel_tea']}\n";
    }
} else {
    echo "   ❌ Nenhum aluno encontrado!\n";
}

// ============================================================
// 2. BUSCAR ALUNO POR ESCOLA
// ============================================================

echo "\n2. Buscando alunos por escola...\n";

$alunos_escola = $aluno->getByEscola('Escola Modelo de Ensino Médio');

if (!empty($alunos_escola)) {
    echo "   ✅ Encontrados " . count($alunos_escola) . " alunos na escola:\n";
    foreach ($alunos_escola as $a) {
        echo "   - {$a['nome']} (Turma: {$a['turma']})\n";
    }
} else {
    echo "   ❌ Nenhum aluno encontrado para esta escola!\n";
}

// ============================================================
// 3. OBTER DADOS COMPLETOS DE UM ALUNO
// ============================================================

echo "\n3. Obtendo dados completos de um aluno...\n";

// Pega o primeiro aluno
$primeiro_aluno = $aluno->getAll()[0] ?? null;

if ($primeiro_aluno) {
    $dados = $aluno->getDadosCompletos($primeiro_aluno['usuario_id']);
    if ($dados) {
        echo "   ✅ Dados encontrados:\n";
        echo "   - Nome: {$dados['nome']}\n";
        echo "   - Idade: {$dados['idade']}\n";
        echo "   - Nível TEA: {$dados['nivel_tea']}\n";
        echo "   - Escola: {$dados['escola']}\n";
        echo "   - Turma: {$dados['turma']}\n";
    }
}

// ============================================================
// 4. OBTER ESTATÍSTICAS DO ALUNO
// ============================================================

echo "\n4. Obtendo estatísticas do aluno...\n";

$aluno_id = 1; // Primeiro aluno
$estatisticas = $aluno->getEstatisticas($aluno_id);

if ($estatisticas) {
    echo "   ✅ Estatísticas encontradas:\n";
    echo "   - Equações tentadas: {$estatisticas['equacoes_tentadas']}\n";
    echo "   - Equações concluídas: {$estatisticas['equacoes_concluidas']}\n";
    echo "   - Total de tentativas: {$estatisticas['total_tentativas']}\n";
    echo "   - Média de tentativas: {$estatisticas['media_tentativas']}\n";
} else {
    echo "   ❌ Estatísticas não encontradas!\n";
}

echo "\n✅ Teste do modelo Aluno concluído!\n";