<?php
/**
 * header.php
 * Cabeçalho padrão do sistema
 */
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
                <a href="/?view=login" class="logo-link">
                    <span class="logo-text">
                        <span class="logo-equa">Equa</span><span class="logo-tea">TEA</span>
                    </span>
                </a>
                <span class="logo-subtitle">Aprendendo equações</span>
            </div>
            <div class="user-info">
                <span class="user-name">🔬 Modo Teste</span>
                <a href="/?view=login" class="btn-logout-header">Sair</a>
            </div>
        </div>
    </header>