<?php
/**
 * login.php
 * Tela de login adaptada para produção (autenticação real via AuthController)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page_title = 'Login - EquaTEA';
$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']); // Limpa o erro após exibir
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
            <div class="alert alert-error" style="background: #ff5555; color: white; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; font-weight: bold;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Formulário apontando para a ação de login do AuthController -->
        <form method="POST" action="/index.php?view=fazer_login">
            <div class="form-group">
                <label for="email"><strong>E-mail de Acesso:</strong></label>
                <input type="email" id="email" name="email" required placeholder="Digite seu e-mail cadastrado" class="form-control" style="width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 6px;">
            </div>
            
            <div class="form-group">
                <label for="senha"><strong>Senha:</strong></label>
                <input type="password" id="senha" name="senha" required placeholder="Digite sua senha" class="form-control" style="width: 100%; padding: 10px; margin-bottom: 20px; border-radius: 6px;">
            </div>

            <button type="submit" class="btn-login" style="width: 100%; padding: 12px; background: #28a745; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">🚀 Entrar no Sistema</button>
        </form>
    </div>
</body>
</html>