<?php
/**
 * test_fluxo_completo.php
 * Testa o fluxo completo de um aluno resolvendo uma equação.
 * 
 * Execução: php tests/test_fluxo_completo.php
 */

require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/models/Usuario.php';
require_once __DIR__ . '/../app/models/Aluno.php';
require_once __DIR__ . '/../app/models/Equacao.php';
require_once __DIR__ . '/../app/models/Progresso.php';
require_once __DIR__ . '/../app/models/RegistroErro.php';

echo "=== TESTE DE FLUXO COMPLETO ===\n\n";

$usuario = new Usuario();
$aluno = new Aluno();
$equacao = new Equacao();
$progresso = new Progresso();
$registro = new RegistroErro();

// ============================================================
// 1. SELECIONAR ALUNO E EQUAÇÃO
// ============================================================

echo "1. Selecionando aluno e equação...\n";

// Buscar primeiro aluno
$alunos = $aluno->getAll();
if (empty($alunos)) {
    die("❌ Nenhum aluno encontrado!\n");
}

$aluno_id = $alunos[0]['aluno_id'];
$usuario_id = $alunos[0]['usuario_id'];
$nome_aluno = $alunos[0]['nome'];

echo "   ✅ Aluno selecionado: $nome_aluno (ID: $aluno_id)\n";

// Buscar equação aleatória não concluída
$equacao_dados = $equacao->getRandom($aluno_id);

if (!$equacao_dados) {
    die("❌ Nenhuma equação disponível!\n");
}

$equacao_id = $equacao_dados['id'];
$sinal = $equacao_dados['b'] >= 0 ? '+' : '-';
$enunciado = "{$equacao_dados['a']}x {$sinal} " . abs($equacao_dados['b']) . " = {$equacao_dados['c']}";

echo "   ✅ Equação selecionada: $enunciado (x = {$equacao_dados['solucao']})\n";

// ============================================================
// 2. INICIAR PROGRESSO
// ============================================================

echo "\n2. Iniciando progresso...\n";

$progresso_existente = $progresso->getByAlunoEquacao($aluno_id, $equacao_id);

if (!$progresso_existente) {
    $progresso->iniciar($aluno_id, $equacao_id);
    echo "   ✅ Progresso iniciado!\n";
} else {
    echo "   ⚠️ Progresso já existente (Passo {$progresso_existente['passo_atual']})\n";
}

// ============================================================
// 3. SIMULAR RESOLUÇÃO PASSO A PASSO
// ============================================================

echo "\n3. Simulando resolução...\n";

$passos = [
    1 => 'x + 3 = 7',
    2 => 'x = 7 - 3',
    3 => '4',
    4 => '4'
];

$passo_atual = 1;
$erros = 0;

while ($passo_atual <= 4) {
    echo "   Passo $passo_atual: ";
    
    $resposta = $passos[$passo_atual] ?? '';
    $valido = $equacao->validarResposta($equacao_id, $passo_atual, $resposta);
    
    if ($valido) {
        echo "✅ Correto!\n";
        $progresso->registrarTentativa($aluno_id, $equacao_id);
        
        if ($passo_atual < 4) {
            $progresso->avancarPasso($aluno_id, $equacao_id);
        }
        $passo_atual++;
    } else {
        echo "❌ Errado!\n";
        $erros++;
        
        // Identificar tipo de erro
        $tipo_erro = $registro->identificarTipoErro($equacao_dados, $passo_atual, $resposta);
        $esperado = $equacao->getRespostaEsperada($equacao_id, $passo_atual);
        
        $registro->registrar(
            $aluno_id,
            $equacao_id,
            $passo_atual,
            $tipo_erro,
            $resposta,
            $esperado
        );
        
        $progresso->registrarTentativa($aluno_id, $equacao_id);
        echo "      Dica: " . getDica($tipo_erro) . "\n";
        break;
    }
}

// ============================================================
// 4. CONCLUIR EXERCÍCIO
// ============================================================

echo "\n4. Concluindo exercício...\n";

if ($passo_atual > 4) {
    $progresso->concluir($aluno_id, $equacao_id);
    echo "   ✅ Exercício concluído com sucesso!\n";
    echo "   - Total de erros: $erros\n";
} else {
    echo "   ⚠️ Exercício não concluído (parou no passo $passo_atual)\n";
}

// ============================================================
// 5. RESUMO FINAL
// ============================================================

echo "\n5. Resumo final:\n";

$progresso_dados = $progresso->getByAluno($aluno_id);
echo "   - Total de equações tentadas: " . count($progresso_dados) . "\n";

$estatisticas = $aluno->getEstatisticas($aluno_id);
echo "   - Equações concluídas: {$estatisticas['equacoes_concluidas']}\n";
echo "   - Taxa de conclusão: " . $progresso->getTaxaConclusao($aluno_id) . "%\n";

$erros_totais = $registro->getEstatisticas($aluno_id);
$total_erros = array_sum(array_column($erros_totais, 'quantidade'));
echo "   - Total de erros: $total_erros\n";

echo "\n✅ Teste de fluxo completo concluído!\n";

// ============================================================
// FUNÇÃO AUXILIAR
// ============================================================

function getDica($tipo_erro)
{
    $dicas = [
        'operacao_inversa' => 'Use a operação inversa! Se está somando, subtraia.',
        'calculo_errado' => 'Verifique sua conta! Refaça a operação.',
        'sinal_trocado' => 'Cuidado com o sinal! Lembre-se da regra de sinais.',
        'divisao_incorreta' => 'Verifique a divisão!',
        'identificacao_errada' => 'Identifique corretamente: termo com x e termos sem x.',
        'outro' => 'Tente novamente com atenção!'
    ];
    return $dicas[$tipo_erro] ?? $dicas['outro'];
}