<?php
/**
 * header.php
 * Cabeçalho padrão do sistema
 */

// Verifica se o usuário está logado para mostrar informações corretas
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario_nome = $_SESSION['usuario_nome'] ?? 'Visitante';
$tipo_perfil = $_SESSION['tipo_perfil'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'EquaTEA'; ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/acessibilidade.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Dyslexic&display=swap" rel="stylesheet">
</head>
<body>
    <header class="main-header">
        <div class="container header-content">
            <div class="logo-container">
                <a href="index.php?view=login" class="logo-link">
                    <span class="logo-text">
                        <span class="logo-equa">Equa</span><span class="logo-tea">TEA</span>
                    </span>
                </a>
                <span class="logo-subtitle">Aprendendo equações</span>
            </div>
            <div class="user-info">
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <span class="user-name">👤 <?php echo htmlspecialchars($usuario_nome); ?></span>
                    <span class="user-type">(<?php echo ucfirst($tipo_perfil); ?>)</span>
                    <a href="index.php?view=logout" class="btn-logout-header" onclick="return confirm('Deseja realmente sair?');">🚪 Sair</a>
                <?php else: ?>
                    <span class="user-name">🔬 Modo Teste</span>
                    <a href="index.php?view=login" class="btn-logout-header">Entrar</a>
                <?php endif; ?>
            </div>
        </div>
    </header>