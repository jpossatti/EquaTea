<?php
/**
 * login.php
 * Página de login - Versão de teste (sem sessão)
 * 
 * Acesso: ?view=login
 */

$page_title = 'Login - EquaTEA';
$error = null;
$success = null;
$csrf_token = 'teste_token_fixo';

// Credenciais para teste
$email_preenchido = 'carlos@escola.com';

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

        <form method="POST" action="#">
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" value="<?php echo $email_preenchido; ?>" placeholder="Digite seu e-mail" required>
            </div>
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" value="professor123" placeholder="Digite sua senha" required>
            </div>
            <button type="submit" class="btn-login">🚀 Entrar no Sistema</button>
        </form>

        <div class="demo-badge">
            💡 <strong>Teste:</strong> Use as credenciais acima ou acesse as views diretamente:
        </div>

        <div class="nav-links">
            <a href="?view=aluno">👨‍🎓 Aluno</a>
            <a href="?view=professor">👨‍🏫 Professor</a>
            <a href="?view=login">🔐 Login</a>
        </div>
    </div>
</body>
</html>