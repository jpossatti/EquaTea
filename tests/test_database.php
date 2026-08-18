<?php
/**
 * test_database.php
 * Testa a conexão com o banco de dados e exibe as tabelas.
 * 
 * Execução: php tests/test_database.php
 */

require_once __DIR__ . '/../app/config/Database.php';

echo "=== TESTE DE CONEXÃO COM O BANCO ===\n\n";

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Listar tabelas
    $stmt = $conn->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📊 Tabelas encontradas:\n";
    foreach ($tabelas as $tabela) {
        echo "  - $tabela\n";
    }
    
    // Contar registros em cada tabela
    echo "\n📈 Total de registros:\n";
    foreach ($tabelas as $tabela) {
        $stmt = $conn->query("SELECT COUNT(*) as total FROM $tabela");
        $total = $stmt->fetch()['total'];
        echo "  - $tabela: $total registros\n";
    }
    
    echo "\n✅ Teste de conexão concluído com sucesso!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}