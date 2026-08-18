<?php
/**
 * test_usuario.php
 * Testa as funcionalidades do modelo Usuario.
 * 
 * Execução: php tests/test_usuario.php
 */

require_once __DIR__ . '/../app/config/Database.php';
require_once __DIR__ . '/../app/models/Usuario.php';

echo "=== TESTE DO MODELO USUARIO ===\n\n";

$usuario = new Usuario();

// ============================================================
// 1. TESTAR LOGIN
// ============================================================

echo "1. Testando login...\n";

$email = 'carlos@escola.com';
$senha = 'professor123';

$resultado = $usuario->login($email, $senha);

if ($resultado) {
    echo "   ✅ Login bem-sucedido!\n";
    echo "   - ID: {$resultado['id']}\n";
    echo "   - Nome: {$resultado['nome']}\n";
    echo "   - Email: {$resultado['email']}\n";
    echo "   - Perfil: {$resultado['tipo_perfil']}\n";
} else {
    echo "   ❌ Login falhou!\n";
}

// ============================================================
// 2. TESTAR LOGIN COM SENHA ERRADA
// ============================================================

echo "\n2. Testando login com senha errada...\n";

$resultado = $usuario->login($email, 'senha_errada');

if (!$resultado) {
    echo "   ✅ Login com senha errada foi corretamente rejeitado!\n";
} else {
    echo "   ❌ O sistema aceitou senha errada!\n";
}

// ============================================================
// 3. TESTAR BUSCA POR ID
// ============================================================

echo "\n3. Testando busca por ID...\n";

$usuario_id = 1;
$dados = $usuario->getById($usuario_id);

if ($dados) {
    echo "   ✅ Usuário encontrado!\n";
    echo "   - Nome: {$dados['nome']}\n";
    echo "   - Email: {$dados['email']}\n";
} else {
    echo "   ❌ Usuário não encontrado!\n";
}

// ============================================================
// 4. TESTAR BUSCA POR EMAIL
// ============================================================

echo "\n4. Testando busca por email...\n";

$dados = $usuario->getByEmail('ana.silva@escola.com');

if ($dados) {
    echo "   ✅ Usuário encontrado por email!\n";
    echo "   - ID: {$dados['id']}\n";
    echo "   - Nome: {$dados['nome']}\n";
} else {
    echo "   ❌ Usuário não encontrado!\n";
}

// ============================================================
// 5. TESTAR CRIAÇÃO DE USUÁRIO (COM EMAIL ÚNICO)
// ============================================================

echo "\n5. Testando criação de usuário...\n";

// Gera um email único baseado no timestamp
$email_unico = 'teste_' . time() . '@cli.com';
echo "   📧 Email gerado: $email_unico\n";

$novo_id = $usuario->criar(
    'Usuario Teste CLI',
    $email_unico,
    'senha123',
    'aluno'
);

if ($novo_id) {
    echo "   ✅ Usuário criado com sucesso! ID: $novo_id\n";
    
    // Verificar se foi criado
    $dados = $usuario->getById($novo_id);
    if ($dados) {
        echo "   - Nome: {$dados['nome']}\n";
        echo "   - Email: {$dados['email']}\n";
        echo "   - Perfil: {$dados['tipo_perfil']}\n";
    }
} else {
    echo "   ❌ Falha ao criar usuário!\n";
}

// ============================================================
// 6. TESTAR CRIAÇÃO COM EMAIL DUPLICADO (DEVE FALHAR)
// ============================================================

echo "\n6. Testando criação com email duplicado (deve falhar)...\n";

try {
    $id_duplicado = $usuario->criar(
        'Usuario Duplicado',
        'carlos@escola.com', // Email já existente
        'senha123',
        'aluno'
    );
    
    if ($id_duplicado) {
        echo "   ❌ O sistema permitiu criar usuário com email duplicado!\n";
        $usuario->desativar($id_duplicado);
    }
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'já está cadastrado') !== false) {
        echo "   ✅ Email duplicado foi corretamente rejeitado!\n";
        echo "   📝 Mensagem: " . $e->getMessage() . "\n";
    } else {
        echo "   ⚠️ Outro erro: " . $e->getMessage() . "\n";
    }
}

// ============================================================
// 7. LIMPAR DADOS DE TESTE
// ============================================================

echo "\n7. Limpando dados de teste...\n";

try {
    // Remove o usuário criado no teste
    $sql = "DELETE FROM usuarios WHERE email LIKE '%@cli.com'";
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $removidos = $stmt->rowCount();
    echo "   ✅ Removidos $removidos usuários de teste\n";
} catch (Exception $e) {
    echo "   ⚠️ Erro ao limpar: " . $e->getMessage() . "\n";
}

echo "\n✅ Teste do modelo Usuario concluído!\n";