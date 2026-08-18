<?php
/**
 * clean_test_data.php
 * Remove dados de teste criados pelos scripts.
 * 
 * Execução: php tests/clean_test_data.php
 */

require_once __DIR__ . '/../app/config/Database.php';

echo "=== LIMPANDO DADOS DE TESTE ===\n\n";

$db = Database::getInstance()->getConnection();

// Remove usuários criados pelos testes
$sql = "DELETE FROM usuarios WHERE email LIKE '%@cli.com'";
$stmt = $db->prepare($sql);
$stmt->execute();
$removidos = $stmt->rowCount();

echo "✅ Removidos $removidos usuários de teste (@cli.com)\n";

// Remove equações criadas pelos testes
$sql = "DELETE FROM equacoes WHERE id > 30";
$stmt = $db->prepare($sql);
$stmt->execute();
$removidos = $stmt->rowCount();

echo "✅ Removidas $removidos equações de teste (ID > 30)\n";

echo "\n✅ Limpeza concluída!\n";