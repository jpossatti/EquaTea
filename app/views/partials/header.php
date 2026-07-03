<?php
/**
 * ============================================================
 * header.php
 * Cabeçalho padrão do sistema EquaTEA.
 * 
 * FUNCIONALIDADES:
 * - Exibe o logo e título do sistema
 * - Mostra informações do usuário logado
 * - Botão de logout
 * - Barra de acessibilidade rápida
 * - Controle de tema (alto contraste, fonte)
 * 
 * @package EquaTEA
 * @subpackage Views/Partials
 * @author Equipe EquaTEA
 * @version 1.0
 * ============================================================
 */

// ============================================================
// VERIFICAR SE O USUÁRIO ESTÁ LOGADO
// ============================================================

$usuario_logado = isset($_SESSION['usuario_id']);
$nome_usuario = $usuario_logado ? ($_SESSION['usuario_nome'] ?? 'Usuário') : '';
$tipo_perfil = $usuario_logado ? ($_SESSION['tipo_perfil'] ?? '') : '';

// ============================================================
// DEFINIR A PÁGINA ATUAL PARA DESTACAR O MENU
// ============================================================

$pagina_atual = basename($_SERVER['PHP_SELF']);
$caminho_atual = $_SERVER['REQUEST_URI'];

// ============================================================
// INÍCIO DO HEADER HTML
// ============================================================

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- ============================================================
    META TAGS DE SEGURANÇA E COMPATIBILIDADE
    ============================================================ -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    
    <!-- ============================================================
    META TAGS PARA ACESSIBILIDADE
    ============================================================ -->
    <meta name="theme-color" content="#2c3e50">
    <meta name="color-scheme" content="light dark">
    
    <!-- ============================================================
    CSS PRINCIPAL
    ============================================================ -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/acessibilidade.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/header.css">
    
    <!-- ============================================================
    FONTES ACESSÍVEIS
    ============================================================ -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Dyslexic&display=swap" rel="stylesheet">
    
    <!-- ============================================================
    FAVICON
    ============================================================ -->
    <link rel="icon" href="<?php echo BASE_URL; ?>public/images/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="<?php echo BASE_URL; ?>public/images/apple-touch-icon.png">
</head>
<body>
    <!-- ============================================================
    SKIP LINKS (para navegação por teclado)
    ============================================================ -->
    <div class="skip-links" role="navigation" aria-label="Pular navegação">
        <a href="#main-content" class="skip-link">Pular para o conteúdo principal</a>
        <a href="#menu-principal" class="skip-link">Pular para o menu</a>
        <a href="#footer-content" class="skip-link">Pular para o rodapé</a>
    </div>

    <!-- ============================================================
    BARRA DE ACESSIBILIDADE (fixa no topo)
    ============================================================ -->
    <div class="accessibility-bar" role="toolbar" aria-label="Ferramentas de acessibilidade">
        <div class="container accessibility-bar-content">
            <div class="accessibility-controls">
                <button class="accessibility-btn" onclick="toggleAltoContraste()" 
                        aria-label="Alternar alto contraste" title="Alto Contraste">
                    <span aria-hidden="true">🌓</span>
                    <span class="btn-label">Contraste</span>
                </button>
                <button class="accessibility-btn" onclick="toggleFonteDyslexic()" 
                        aria-label="Alternar fonte para dislexia" title="Fonte Disléxica">
                    <span aria-hidden="true">🔤</span>
                    <span class="btn-label">Fonte</span>
                </button>
                <button class="accessibility-btn" onclick="aumentarFonte()" 
                        aria-label="Aumentar tamanho da fonte" title="Aumentar fonte">
                    <span aria-hidden="true">A+</span>
                </button>
                <button class="accessibility-btn" onclick="diminuirFonte()" 
                        aria-label="Diminuir tamanho da fonte" title="Diminuir fonte">
                    <span aria-hidden="true">A-</span>
                </button>
                <button class="accessibility-btn" onclick="resetarFonte()" 
                        aria-label="Resetar tamanho da fonte" title="Resetar fonte">
                    <span aria-hidden="true">A</span>
                </button>
            </div>
            
            <div class="accessibility-status" aria-live="polite">
                <span id="status-mensagem" role="status"></span>
            </div>
        </div>
    </div>

    <!-- ============================================================
    HEADER PRINCIPAL
    ============================================================ -->
    <header class="main-header" role="banner" aria-label="Cabeçalho do sistema">
        <div class="container header-content">
            
            <!-- ============================================================
            LOGO E TÍTULO
            ============================================================ -->
            <div class="logo-container">
                <a href="<?php echo BASE_URL; ?>" class="logo-link" aria-label="Página inicial do EquaTEA">
                    <img src="<?php echo BASE_URL; ?>public/images/logo.png" 
                         alt="Logo EquaTEA" 
                         class="logo-image"
                         width="45"
                         height="45">
                    <span class="logo-text">
                        <span class="logo-equa">Equa</span><span class="logo-tea">TEA</span>
                    </span>
                </a>
                <span class="logo-subtitle">Aprendendo equações</span>
            </div>
            
            <!-- ============================================================
            INFORMAÇÕES DO USUÁRIO
            ============================================================ -->
            <div class="user-info">
                <?php if ($usuario_logado): ?>
                    <div class="user-profile">
                        <div class="user-avatar" aria-hidden="true">
                            <?php echo strtoupper(substr($nome_usuario, 0, 1)); ?>
                        </div>
                        <div class="user-details">
                            <span class="user-name"><?php echo htmlspecialchars($nome_usuario); ?></span>
                            <span class="user-role">
                                <?php if ($tipo_perfil === 'aluno'): ?>
                                    👨‍🎓 Aluno
                                <?php elseif ($tipo_perfil === 'professor'): ?>
                                    👨‍🏫 Professor
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- ============================================================
                    BOTÃO DE LOGOUT
                    ============================================================ -->
                    <a href="<?php echo BASE_URL; ?>app/controllers/AuthController.php?action=logout" 
                       class="btn-logout" 
                       aria-label="Sair do sistema">
                        <span aria-hidden="true">🚪</span>
                        <span class="btn-logout-label">Sair</span>
                    </a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>app/views/auth/login.php" class="btn-login-header">
                        <span aria-hidden="true">🔑</span>
                        Entrar
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- ============================================================
    SCRIPTS DE ACESSIBILIDADE (carregados no header para funcionamento imediato)
    ============================================================ -->
    <script>
        /**
         * ============================================================
         * FUNÇÕES DE ACESSIBILIDADE
         * ============================================================
         */
        
        /**
         * Alterna o alto contraste
         */
        function toggleAltoContraste() {
            const body = document.body;
            body.classList.toggle('alto-contraste');
            const ativo = body.classList.contains('alto-contraste');
            localStorage.setItem('alto_contraste', ativo ? 'true' : 'false');
            anunciarMudanca(ativo ? 'Alto contraste ativado' : 'Alto contraste desativado');
        }
        
        /**
         * Alterna a fonte Open Dyslexic
         */
        function toggleFonteDyslexic() {
            const body = document.body;
            body.classList.toggle('fonte-dyslexic');
            const ativo = body.classList.contains('fonte-dyslexic');
            localStorage.setItem('fonte_dyslexic', ativo ? 'true' : 'false');
            anunciarMudanca(ativo ? 'Fonte para dislexia ativada' : 'Fonte para dislexia desativada');
        }
        
        /**
         * Aumenta o tamanho da fonte
         */
        function aumentarFonte() {
            const body = document.body;
            const size = parseInt(getComputedStyle(body).fontSize) || 18;
            const newSize = Math.min(size + 2, 32);
            body.style.fontSize = newSize + 'px';
            localStorage.setItem('tamanho_fonte', newSize);
            anunciarMudanca('Fonte aumentada para ' + newSize + ' pixels');
        }
        
        /**
         * Diminui o tamanho da fonte
         */
        function diminuirFonte() {
            const body = document.body;
            const size = parseInt(getComputedStyle(body).fontSize) || 18;
            const newSize = Math.max(size - 2, 12);
            body.style.fontSize = newSize + 'px';
            localStorage.setItem('tamanho_fonte', newSize);
            anunciarMudanca('Fonte diminuída para ' + newSize + ' pixels');
        }
        
        /**
         * Reseta o tamanho da fonte para o padrão
         */
        function resetarFonte() {
            const body = document.body;
            body.style.fontSize = '18px';
            localStorage.setItem('tamanho_fonte', '18');
            anunciarMudanca('Fonte resetada para o tamanho padrão');
        }
        
        /**
         * Anuncia uma mensagem para leitores de tela
         */
        function anunciarMudanca(mensagem) {
            const status = document.getElementById('status-mensagem');
            if (status) {
                status.textContent = mensagem;
                // Limpa após 3 segundos
                setTimeout(function() {
                    status.textContent = '';
                }, 3000);
            }
        }
        
        /**
         * Restaura as preferências do usuário ao carregar a página
         */
        document.addEventListener('DOMContentLoaded', function() {
            // Alto contraste
            if (localStorage.getItem('alto_contraste') === 'true') {
                document.body.classList.add('alto-contraste');
            }
            
            // Fonte disléxica
            if (localStorage.getItem('fonte_dyslexic') === 'true') {
                document.body.classList.add('fonte-dyslexic');
            }
            
            // Tamanho da fonte
            const tamanhoFonte = localStorage.getItem('tamanho_fonte');
            if (tamanhoFonte) {
                document.body.style.fontSize = tamanhoFonte + 'px';
            }
        });
    </script>
</body>
</html>