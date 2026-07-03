<?php
/**
 * ============================================================
 * logout.php
 * Página de encerramento de sessão (logout) do sistema EquaTEA.
 * 
 * FUNCIONALIDADES:
 * - Encerra a sessão do usuário de forma segura
 * - Destroi todos os dados da sessão
 * - Remove cookies de sessão
 * - Redireciona para a página de login
 * - Exibe mensagem de confirmação
 * 
 * @package EquaTEA
 * @subpackage Views/Auth
 * @author Equipe EquaTEA
 * @version 1.0
 * ============================================================
 */

// ============================================================
// 1. INICIALIZAÇÃO DA SESSÃO
// ============================================================

session_start();

// ============================================================
// 2. CARREGAMENTO DE HELPERS
// ============================================================

require_once '../../helpers/session_helper.php';

// ============================================================
// 3. REGISTRO DE LOG DE LOGOUT (antes de destruir a sessão)
// ============================================================

// Registrar a ação de logout (se houver usuário logado)
if (isset($_SESSION['usuario_id'])) {
    try {
        require_once '../../config/database.php';
        $db = Database::getInstance()->getConnection();
        
        $sql = "INSERT INTO logs_sistema (usuario_id, acao, descricao, ip_address) 
                VALUES (:usuario_id, 'LOGOUT', 'Usuário realizou logout do sistema', :ip)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $_SESSION['usuario_id'],
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    } catch (Exception $e) {
        // Erro silencioso - apenas loga no error_log do PHP
        error_log("Erro ao registrar logout: " . $e->getMessage());
    }
}

// ============================================================
// 4. ENCERRAMENTO DA SESSÃO
// ============================================================

// 4.1. Limpar todas as variáveis de sessão
$_SESSION = array();

// 4.2. Destruir o cookie de sessão (se existir)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 4.3. Destruir a sessão
session_destroy();

// ============================================================
// 5. REDIRECIONAMENTO PARA A PÁGINA DE LOGIN
// ============================================================

// Redireciona para a página de login com mensagem de sucesso
$redirect_url = '../auth/login.php?msg=logout';

// Se a sessão expirou, redireciona com mensagem específica
if (isset($_GET['expirado']) && $_GET['expirado'] == '1') {
    $redirect_url = '../auth/login.php?erro=expirado';
}

header('Location: ' . $redirect_url);
exit;

// ============================================================
// 6. PÁGINA DE CONFIRMAÇÃO (caso o redirecionamento não funcione)
// ============================================================
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saindo do Sistema - EquaTEA</title>
    <meta http-equiv="refresh" content="3;url=../auth/login.php?msg=logout">
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/acessibilidade.css">
</head>
<body>
    <main class="container logout-container" role="main">
        <div class="logout-card">
            <div class="logout-icon" aria-hidden="true">👋</div>
            <h1>Saindo do Sistema...</h1>
            <p>Sua sessão foi encerrada com sucesso.</p>
            <p class="logout-timer">Você será redirecionado em <span id="contador">3</span> segundos.</p>
            <a href="../auth/login.php" class="btn-login-redirect">Ir para o Login</a>
        </div>
    </main>
    
    <script>
        // ============================================================
        // CONTADOR REGRESSIVO PARA REDIRECIONAMENTO
        // ============================================================
        
        let segundos = 3;
        const contadorElement = document.getElementById('contador');
        
        const intervalo = setInterval(function() {
            segundos--;
            if (contadorElement) {
                contadorElement.textContent = segundos;
            }
            if (segundos <= 0) {
                clearInterval(intervalo);
                window.location.href = '../auth/login.php?msg=logout';
            }
        }, 1000);
        
        // ============================================================
        // FUNÇÃO PARA REDIRECIONAR MANUALMENTE
        // ============================================================
        
        document.querySelector('.btn-login-redirect').addEventListener('click', function(e) {
            e.preventDefault();
            clearInterval(intervalo);
            window.location.href = '../auth/login.php?msg=logout';
        });
    </script>
    
    <style>
        /* ============================================================
        ESTILOS ESPECÍFICOS DA PÁGINA DE LOGOUT
        ============================================================ */
        .logout-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f5f7fa;
        }
        
        .logout-card {
            background: #ffffff;
            padding: 50px 40px 40px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            text-align: center;
            max-width: 450px;
            width: 100%;
        }
        
        .logout-icon {
            font-size: 64px;
            margin-bottom: 16px;
        }
        
        .logout-card h1 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .logout-card p {
            font-size: 18px;
            color: #555;
            margin-bottom: 8px;
        }
        
        .logout-timer {
            font-size: 16px;
            color: #888;
            margin-top: 12px;
        }
        
        .logout-timer #contador {
            font-weight: bold;
            font-size: 20px;
            color: #3498db;
        }
        
        .btn-login-redirect {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 32px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 18px;
            transition: background-color 0.2s;
        }
        
        .btn-login-redirect:hover {
            background-color: #2980b9;
        }
        
        /* Estilos para alto contraste */
        .alto-contraste .logout-card {
            background-color: #000000;
            border: 2px solid #ffffff;
        }
        
        .alto-contraste .logout-card h1,
        .alto-contraste .logout-card p {
            color: #ffffff;
        }
        
        .alto-contraste .btn-login-redirect {
            background-color: #ffff00;
            color: #000000;
        }
        
        /* Estilos para fonte dyslexic */
        .fonte-dyslexic .logout-card {
            font-family: 'Open Dyslexic', Arial, sans-serif;
        }
    </style>
</body>
</html>