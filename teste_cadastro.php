<?php
/**
 * teste_cadastro.php - Script para testar o cadastro de aluno
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Cadastro Aluno</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; }
        .card { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; }
        .btn { display: inline-block; padding: 10px 20px; margin: 5px; background: #3498db; color: #fff; text-decoration: none; border-radius: 4px; }
        .btn-success { background: #27ae60; }
        .btn-danger { background: #e74c3c; }
        .btn-warning { background: #f39c12; }
        .info-box { background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #3498db; }
        .info-box.error { border-left-color: #e74c3c; }
        .info-box.success { border-left-color: #27ae60; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🧪 Teste de Cadastro de Aluno</h1>
        
        <div class="info-box success">
            <strong>✅ Sistema funcionando!</strong>
            <p>Esta página é um teste para verificar se o cadastro de aluno está funcionando.</p>
        </div>
        
        <h2>📌 Links para Teste</h2>
        <div style="display: flex; flex-wrap: wrap; gap: 10px; margin: 15px 0;">
            <a href="index.php?view=editar_aluno" class="btn btn-success">➕ Novo Aluno (view=editar_aluno)</a>
            <a href="index.php?view=professor/editar_aluno" class="btn">📝 Editar Aluno (view=professor/editar_aluno)</a>
            <a href="index.php?view=debug_editar_aluno" class="btn btn-warning">🐞 Debug Editar Aluno</a>
            <a href="index.php?view=gerenciar_alunos" class="btn btn-danger">⬅ Voltar Gerenciar</a>
        </div>
        
        <h2>📊 Informações do Sistema</h2>
        <div class="info-box">
            <strong>URL Atual:</strong> <?php echo $_SERVER['REQUEST_URI']; ?><br>
            <strong>Método:</strong> <?php echo $_SERVER['REQUEST_METHOD']; ?><br>
            <strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?><br>
            <strong>PHP Version:</strong> <?php echo phpversion(); ?>
        </div>
        
        <h2>👤 Sessão Atual</h2>
        <div class="info-box">
            <?php 
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            echo '<pre>' . print_r($_SESSION, true) . '</pre>'; 
            ?>
        </div>
        
        <h2>📁 Arquivos do Sistema</h2>
        <div class="info-box">
            <ul>
                <li><strong>Controller:</strong> <?php echo file_exists('app/controllers/ProfessorController.php') ? '✅ Existe' : '❌ Não existe'; ?></li>
                <li><strong>View Editar Aluno:</strong> <?php echo file_exists('app/views/professor/editar_aluno.php') ? '✅ Existe' : '❌ Não existe'; ?></li>
                <li><strong>View Gerenciar Alunos:</strong> <?php echo file_exists('app/views/professor/gerenciar_alunos.php') ? '✅ Existe' : '❌ Não existe'; ?></li>
                <li><strong>Menu Professor:</strong> <?php echo file_exists('app/views/partials/menu_professor.php') ? '✅ Existe' : '❌ Não existe'; ?></li>
            </ul>
        </div>
        
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
            <p style="color: #999; font-size: 12px;">Criado para debug do sistema EquaTEA</p>
        </div>
    </div>
</body>
</html>