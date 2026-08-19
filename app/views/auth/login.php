<?php
/**
 * login.php
 * Tela de login adaptada para seleção direta de acesso de teste (sem sessão)
 */

$page_title = 'Login - EquaTEA';
$error = null;
$success = null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/login.css">
    <link rel="stylesheet" href="/public/css/acessibilidade.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Dyslexic&display=swap" rel="stylesheet">
</head>
<body class="login-body">
    <div class="login-card">
        <div class="login-header">
            <h1 class="login-title">
                <span class="title-equa">Equa</span><span class="title-tea">TEA</span>
            </h1>
            <p class="login-subtitle">Aprendendo equações de 1º grau</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Formulário redireciona para a view de acordo com o perfil selecionado -->
        <form method="POST" action="index.php?view=login">
            <div class="form-group">
                <label for="tipo_perfil"><strong>Selecionar Perfil de Acesso:</strong></label>
                <select id="tipo_perfil" name="tipo_perfil" class="form-control" style="width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 6px; font-weight: bold;">
                    <option value="aluno">👨‍🎓 Aluno (Dashboard do Aluno)</option>
                    <option value="professor">👨‍🏫 Professor (Dashboard do Professor)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="email">E-mail (opcional no modo teste):</label>
                <input type="email" id="email" name="email" value="usuario@escola.com" placeholder="Digite seu e-mail">
            </div>
            
            <div class="form-group">
                <label for="senha">Senha (opcional no modo teste):</label>
                <input type="password" id="senha" name="senha" value="123456" placeholder="Digite sua senha">
            </div>

            <button type="submit" class="btn-login">🚀 Entrar no Dashboard</button>
        </form>

        <div class="demo-badge" style="margin-top: 15px; text-align: center;">
            💡 <strong>Acesso Rápido Sem Sessão:</strong>
        </div>

        <div class="nav-links" style="display: flex; justify-content: space-around; margin-top: 10px;">
            <a href="index.php?view=aluno" class="btn-demo">👨‍🎓 Ir como Aluno</a>
            <a href="index.php?view=professor" class="btn-demo">👨‍🏫 Ir como Professor</a>
        </div>
    </div>
</body>
</html>