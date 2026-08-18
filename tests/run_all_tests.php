<?php
/**
 * run_all_tests_web.php
 * Versão web para visualizar resultados dos testes.
 * 
 * Acesso: http://localhost:3000/tests/run_all_tests_web.php
 */

echo "<!DOCTYPE html>";
echo "<html><head><title>EquaTEA - Testes</title>";
echo "<style>
    body { font-family: Arial; background: #f0f2f5; padding: 20px; }
    .container { max-width: 900px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
    h1 { color: #2c3e50; }
    .test { background: #f8f9fa; padding: 10px 16px; margin: 8px 0; border-radius: 6px; border-left: 4px solid #3498db; }
    .test.pass { border-left-color: #28a745; }
    .test.fail { border-left-color: #e74c3c; }
    pre { background: #1e1e1e; color: #d4d4d4; padding: 16px; border-radius: 6px; overflow-x: auto; font-size: 13px; }
</style></head><body>";

echo "<div class='container'>";
echo "<h1>🧪 EquaTEA - Testes de Funcionalidades</h1>";

$tests = [
    'test_database.php' => 'Conexão com o Banco',
    'test_usuario.php' => 'Modelo Usuário',
    'test_aluno.php' => 'Modelo Aluno',
    'test_equacao.php' => 'Modelo Equação',
    'test_progresso.php' => 'Modelo Progresso',
    'test_registro_erro.php' => 'Modelo Registro de Erros',
    'test_fluxo_completo.php' => 'Fluxo Completo'
];

foreach ($tests as $arquivo => $descricao) {
    echo "<div class='test'>";
    echo "<strong>" . $descricao . "</strong>";
    
    $caminho = __DIR__ . '/' . $arquivo;
    if (file_exists($caminho)) {
        echo " <span style='color:#28a745;'>✅</span>";
        echo "<pre>";
        // Executa e captura a saída
        ob_start();
        include $caminho;
        $output = ob_get_clean();
        echo htmlspecialchars($output);
        echo "</pre>";
    } else {
        echo " <span style='color:#e74c3c;'>❌ Arquivo não encontrado</span>";
    }
    echo "</div>";
}

echo "</div></body></html>";