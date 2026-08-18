<?php
/**
 * test_progresso.php
 * Testa as funcionalidades do modelo Progresso.
 * 
 * Execução: php tests/test_progresso.php
 */

require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/models/Progresso.php';
require_once __DIR__ . '/../app/models/Equacao.php';

echo "=== TESTE DO MODELO PROGRESSO ===\n\n";

$progresso = new Progresso();
$equacao = new Equacao();

$aluno_id = 1;

// ============================================================
// 1. BUSCAR EQUAÇÃO SEM PROGRESSO
// ============================================================

echo "1. Buscando equação sem progresso...\n";

$sql = "SELECT e.id FROM equacoes e 
        LEFT JOIN progresso_aluno p ON e.id = p.equacao_id AND p.aluno_id = :aluno_id
        WHERE p.equacao_id IS NULL LIMIT 1";
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare($sql);
$stmt->execute([':aluno_id' => $aluno_id]);
$result = $stmt->fetch();

if ($result) {
    $equacao_id = $result['id'];
    echo "   📝 Usando equação ID: $equacao_id\n";
} else {
    echo "   ⚠️ Todas as equações já têm progresso. Usando fallback.\n";
    $equacao_id = 1;
}

// ============================================================
// 2. INICIAR PROGRESSO
// ============================================================

echo "\n2. Iniciando progresso...\n";

// Remove progresso existente (se houver) para teste
$sql = "DELETE FROM progresso_aluno WHERE aluno_id = :aluno_id AND equacao_id = :equacao_id";
$stmt = $db->prepare($sql);
$stmt->execute([
    ':aluno_id' => $aluno_id,
    ':equacao_id' => $equacao_id
]);

$resultado = $progresso->iniciar($aluno_id, $equacao_id);

if ($resultado) {
    echo "   ✅ Progresso iniciado!\n";
} else {
    echo "   ❌ Falha ao iniciar progresso!\n";
}

// ============================================================
// 3. SIMULAR RESOLUÇÃO PASSO A PASSO
// ============================================================

echo "\n3. Simulando resolução passo a passo...\n";

for ($passo = 1; $passo <= 4; $passo++) {
    // Registrar tentativa
    $progresso->registrarTentativa($aluno_id, $equacao_id);
    echo "   - Passo $passo: tentativa registrada\n";
    
    // Avançar (exceto no último passo)
    if ($passo < 4) {
        $progresso->avancarPasso($aluno_id, $equacao_id);
        echo "   - Passo $passo: avançou para " . ($passo + 1) . "\n";
    }
}

// ============================================================
// 4. CONCLUIR EQUAÇÃO
// ============================================================

echo "\n4. Concluindo equação...\n";

$resultado = $progresso->concluir($aluno_id, $equacao_id);

if ($resultado) {
    echo "   ✅ Equação concluída!\n";
} else {
    echo "   ❌ Falha ao concluir equação!\n";
}

// ============================================================
// 5. VERIFICAR RESULTADO
// ============================================================

echo "\n5. Verificando resultado...\n";

$dados = $progresso->getByAlunoEquacao($aluno_id, $equacao_id);

if ($dados) {
    echo "   ✅ Progresso final:\n";
    echo "   - Passo atual: {$dados['passo_atual']}\n";
    echo "   - Concluída: " . ($dados['concluida'] ? 'SIM ✅' : 'NÃO ❌') . "\n";
    echo "   - Tentativas: {$dados['tentativas']}\n";
    if ($dados['data_conclusao']) {
        echo "   - Data conclusão: {$dados['data_conclusao']}\n";
    }
} else {
    echo "   ❌ Progresso não encontrado!\n";
}

// ============================================================
// 6. TAXA DE CONCLUSÃO
// ============================================================

echo "\n6. Taxa de conclusão do aluno...\n";

$taxa = $progresso->getTaxaConclusao($aluno_id);
echo "   ✅ Taxa de conclusão: {$taxa}%\n";

echo "\n✅ Teste do modelo Progresso concluído!\n";