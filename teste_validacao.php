<?php
/**
 * teste_validacao.php
 * Teste de validação de passos - Versão Final Corrigida
 */

echo "<h1>Teste de Validação de Passos - Versão Final Corrigida</h1>";

function validarPassoTeste($equacao, $passo, $resposta) {
    if ($resposta === '') {
        return false;
    }

    $resp = trim($resposta);
    $resp = preg_replace('/\s+/', ' ', $resp);
    $resp = str_replace(['–', '—', '−'], '-', $resp);
    $respClean = preg_replace('/\s+/', '', $resp);

    $a = (int)($equacao['a'] ?? 1);
    $b = (int)($equacao['b'] ?? 0);
    $c = (int)($equacao['c'] ?? 0);

    $termoX = ($a === 1) ? 'x' : (($a === -1) ? '-x' : "{$a}x");

    switch ((int)$passo) {
        case 1:
            return ($respClean === $termoX);

        case 2:
            $resultadoEsperado = $c - $b;
            
            if (strpos($respClean, $termoX) === 0 && strpos($respClean, '=') !== false) {
                $partes = explode('=', $respClean);
                $ladoDireito = $partes[1] ?? '';
                
                if (preg_match('/^(\d+)([-+])(\d+)$/', $ladoDireito, $matches)) {
                    $num1 = (int)$matches[1];
                    $operador = $matches[2];
                    $num2 = (int)$matches[3];
                    $resultadoCalculado = ($operador === '-') ? ($num1 - $num2) : ($num1 + $num2);
                    if ($resultadoCalculado === $resultadoEsperado) {
                        return true;
                    }
                }
                
                if (is_numeric($ladoDireito) && (int)$ladoDireito === $resultadoEsperado) {
                    return true;
                }
            }
            return false;

        case 3:
            // Passo 3: DEVE mostrar "2x = 6" (com o termo X)
            // NÃO pode ser apenas "6"
            $resultadoEsperado = $c - $b;
            
            // Verifica se contém "=" e o termo X
            if (strpos($respClean, '=') !== false && strpos($respClean, $termoX) !== false) {
                $partes = explode('=', $respClean);
                $ladoEsquerdo = $partes[0] ?? '';
                $ladoDireito = $partes[1] ?? '';
                
                // Lado esquerdo deve ser exatamente o termo X
                if ($ladoEsquerdo === $termoX) {
                    if (is_numeric($ladoDireito) && (int)$ladoDireito === $resultadoEsperado) {
                        return true;
                    }
                }
            }
            
            // Verifica se é exatamente "2x = 6" (com espaços)
            if (preg_match('/^' . preg_quote($termoX, '/') . '\s*=\s*' . $resultadoEsperado . '$/i', $resp)) {
                return true;
            }
            
            return false;

        case 4:
            if ($a === 0) return false;
            $valorX = ($c - $b) / $a;
            
            if (is_numeric($respClean) && (float)$respClean == $valorX) {
                return true;
            }
            
            if (strpos($respClean, '=') !== false) {
                $partes = explode('=', $respClean);
                $ladoEsquerdo = $partes[0] ?? '';
                $ladoDireito = $partes[1] ?? '';
                
                if (preg_match('/^x$/i', $ladoEsquerdo) && is_numeric($ladoDireito) && (float)$ladoDireito == $valorX) {
                    return true;
                }
            }
            
            if (preg_match('/^x\s*=\s*' . $valorX . '$/i', $resp)) {
                return true;
            }
            
            return false;

        default:
            return false;
    }
}

// Simula uma equação
$equacao = ['a' => 2, 'b' => 5, 'c' => 11];

$testes = [
    // Passo 1
    ['passo' => 1, 'resposta' => '2x', 'esperado' => true, 'nota' => 'Correto'],
    ['passo' => 1, 'resposta' => '2x = 11', 'esperado' => false, 'nota' => 'Contém "="'],
    ['passo' => 1, 'resposta' => 'x', 'esperado' => false, 'nota' => 'Falta coeficiente'],
    
    // Passo 2
    ['passo' => 2, 'resposta' => '2x = 11 - 5', 'esperado' => true, 'nota' => 'Expressão com espaços'],
    ['passo' => 2, 'resposta' => '2x = 11-5', 'esperado' => true, 'nota' => 'Expressão sem espaços'],
    ['passo' => 2, 'resposta' => '2x = 6', 'esperado' => true, 'nota' => 'Resultado direto'],
    
    // Passo 3 - CORRIGIDO: DEVE ter o termo X
    ['passo' => 3, 'resposta' => '2x = 6', 'esperado' => true, 'nota' => 'Com termo X - VÁLIDO'],
    ['passo' => 3, 'resposta' => '2x=6', 'esperado' => true, 'nota' => 'Sem espaços - VÁLIDO'],
    ['passo' => 3, 'resposta' => '6', 'esperado' => false, 'nota' => 'Apenas número - INVÁLIDO'],
    ['passo' => 3, 'resposta' => 'x = 6', 'esperado' => false, 'nota' => 'Falta coeficiente - INVÁLIDO'],
    
    // Passo 4
    ['passo' => 4, 'resposta' => '3', 'esperado' => true, 'nota' => 'Apenas o número'],
    ['passo' => 4, 'resposta' => 'x = 3', 'esperado' => true, 'nota' => 'Com "x ="'],
    ['passo' => 4, 'resposta' => 'x=3', 'esperado' => true, 'nota' => 'Sem espaços'],
];

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Status</th><th>Passo</th><th>Resposta</th><th>Nota</th><th>Esperado</th><th>Resultado</th></tr>";

$todosPassaram = true;
foreach ($testes as $teste) {
    $resultado = validarPassoTeste($equacao, $teste['passo'], $teste['resposta']);
    $passou = ($resultado === $teste['esperado']);
    $status = $passou ? '✅' : '❌';
    if (!$passou) $todosPassaram = false;
    
    echo "<tr>";
    echo "<td style='text-align:center;font-size:20px;'>$status</td>";
    echo "<td>Passo {$teste['passo']}</td>";
    echo "<td>'{$teste['resposta']}'</td>";
    echo "<td style='color:#666;font-size:12px;'>{$teste['nota']}</td>";
    echo "<td>" . ($teste['esperado'] ? 'VÁLIDO' : 'INVÁLIDO') . "</td>";
    echo "<td style='color:" . ($resultado ? 'green' : 'red') . ";font-weight:bold;'>" . ($resultado ? 'VÁLIDO' : 'INVÁLIDO') . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<br><strong>Equação usada:</strong> 2x + 5 = 11 (a=2, b=5, c=11)";
echo "<br><br>";

if ($todosPassaram) {
    echo "<div style='background:#d4edda;padding:15px;border-radius:5px;border:1px solid #c3e6cb;'>";
    echo "✅ <strong>TODOS OS TESTES PASSARAM!</strong><br>";
    echo "🔹 Passo 1: Aceita exatamente '2x'<br>";
    echo "🔹 Passo 2: Aceita '2x = 11 - 5', '2x = 11-5', '2x = 6'<br>";
    echo "🔹 Passo 3: Aceita '2x = 6' (com o termo X). Rejeita apenas '6'<br>";
    echo "🔹 Passo 4: Aceita '3', 'x = 3', 'x=3'<br>";
    echo "</div>";
} else {
    echo "<div style='background:#f8d7da;padding:15px;border-radius:5px;border:1px solid #f5c6cb;'>";
    echo "❌ <strong>ALGUNS TESTES FALHARAM!</strong> Verifique os resultados acima.";
    echo "</div>";
}