<?php
// app/config/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function verificarAutenticacao($tipoNecessario = null) {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: index.php?view=login');
        exit;
    }

    // Se a página exige um tipo específico (ex: 'professor')
    if ($tipoNecessario && ($_SESSION['tipo_usuario'] ?? '') !== $tipoNecessario) {
        header('Location: index.php?view=dashboard'); // Redireciona para área comum
        exit;
    }
}