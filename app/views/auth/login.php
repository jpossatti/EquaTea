<?php
/**
 * ============================================================
 * login.php
 * Página de autenticação do sistema EquaTEA.
 * 
 * FUNCIONALIDADES:
 * - Formulário de login com campos de e-mail e senha
 * - Validação de campos em tempo real (JS)
 * - Feedback visual para erros de autenticação
 * - Interface acessível para usuários com TEA
 * - Opção de "Ouvir" o enunciado (Web Speech API)
 * 
 * @package EquaTEA
 * @subpackage Views/Auth
 * @author Equipe EquaTEA
 * @version 1.0
 * ============================================================
 */

// ============================================================
// 1. INICIALIZAÇÃO E VERIFICAÇÃO DE SESSÃO
// ============================================================

session_start();

// Se o usuário já está logado, redireciona para o dashboard apropriado
if (isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id'])) {
    if ($_SESSION['tipo_perfil'] == 'aluno') {
        header('Location: ../aluno/dashboard.php');
    } else {
        header('Location: ../professor/dashboard.php');
    }
    exit;
}

// ============================================================
// 2. CARREGAMENTO DE HELPERS E VARIÁVEIS
// ============================================================

// Define o título da página para o header
$page_title = 'Login - EquaTEA';

// Mensagens de erro e sucesso (capturadas da sessão)
$error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : null;
$success = isset($_SESSION['login_success']) ? $_SESSION['login_success'] : null;

// Limpa as mensagens da sessão após carregar
unset($_SESSION['login_error']);
unset($_SESSION['login_success']);

// ============================================================
// 3. PREENCHIMENTO AUTOMÁTICO DE E-MAIL (para facilitar testes)
// ============================================================

$email_preenchido = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';
$mensagem_logout = isset($_GET['msg']) && $_GET['msg'] == 'logout' ? true : false;
$mensagem_expirado = isset($_GET['erro']) && $_GET['erro'] == 'expirado' ? true : false;

// ============================================================
// 4. HEADER DA PÁGINA (com DOCTYPE e metadados)
// ============================================================

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- ============================================================
    META TAGS PARA ACESSIBILIDADE E COMPATIBILIDADE
    ============================================================ -->
    <meta name="description" content="Sistema EquaTEA - Aprendendo equações de forma simples e acessível">
    <meta name="theme-color" content="#2c3e50">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- ============================================================
    CSS - FOLHAS DE ESTILO
    ============================================================ -->
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/login.css">
    <link rel="stylesheet" href="../../public/css/acessibilidade.css">
    
    <!-- ============================================================
    FAVICON
    ============================================================ -->
    <link rel="icon" href="../../public/images/favicon.ico" type="image/x-icon">
    
    <!-- ============================================================
    FONTES ACESSÍVEIS (Google Fonts - Open Dyslexic)
    ============================================================ -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Dyslexic&display=swap" rel="stylesheet">
</head>

<body>
    <!-- ============================================================
    BARRA DE ACESSIBILIDADE RÁPIDA (Skip Links)
    ============================================================ -->
    <div class="skip-links" role="navigation" aria-label="Pular navegação">
        <a href="#main-content" class="skip-link">Pular para o conteúdo principal</a>
        <a href="#login-form" class="skip-link">Pular para o formulário de login</a>
    </div>

    <!-- ============================================================
    CONTEÚDO PRINCIPAL DA PÁGINA
    ============================================================ -->
    <main id="main-content" class="login-container" role="main">
        
        <!-- ============================================================
        CARD DE LOGIN
        ============================================================ -->
        <div class="login-card" role="region" aria-labelledby="login-title">
            
            <!-- ============================================================
            LOGO E TÍTULO
            ============================================================ -->
            <div class="login-header">
                <div class="logo-container">
                    <img src="../../public/images/logo.png" 
                         alt="EquaTEA - Logo do sistema" 
                         class="logo-image"
                         width="80"
                         height="80">
                </div>
                <h1 id="login-title" class="login-title">
                    <span class="title-equa">Equa</span><span class="title-tea">TEA</span>
                </h1>
                <p class="login-subtitle">Aprendendo equações de 1º grau de forma simples e acessível</p>
            </div>
            
            <!-- ============================================================
            MENSAGENS DE FEEDBACK
            ============================================================ -->
            
            <?php if ($error): ?>
                <div class="alert alert-error" role="alert" aria-live="polite">
                    <span class="alert-icon" aria-hidden="true">⚠️</span>
                    <strong>Erro:</strong> <?php echo htmlspecialchars($error); ?>
                    <button class="alert-close" onclick="this.parentElement.style.display='none'" 
                            aria-label="Fechar mensagem de erro">✕</button>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" role="alert" aria-live="polite">
                    <span class="alert-icon" aria-hidden="true">✅</span>
                    <?php echo htmlspecialchars($success); ?>
                    <button class="alert-close" onclick="this.parentElement.style.display='none'" 
                            aria-label="Fechar mensagem de sucesso">✕</button>
                </div>
            <?php endif; ?>
            
            <?php if ($mensagem_logout): ?>
                <div class="alert alert-success" role="alert" aria-live="polite">
                    <span class="alert-icon" aria-hidden="true">👋</span>
                    Você foi desconectado com sucesso.
                    <button class="alert-close" onclick="this.parentElement.style.display='none'" 
                            aria-label="Fechar mensagem">✕</button>
                </div>
            <?php endif; ?>
            
            <?php if ($mensagem_expirado): ?>
                <div class="alert alert-warning" role="alert" aria-live="polite">
                    <span class="alert-icon" aria-hidden="true">⏰</span>
                    <strong>Sessão expirada!</strong> Por favor, faça login novamente para continuar.
                    <button class="alert-close" onclick="this.parentElement.style.display='none'" 
                            aria-label="Fechar mensagem">✕</button>
                </div>
            <?php endif; ?>
            
            <!-- ============================================================
            FORMULÁRIO DE LOGIN
            ============================================================ -->
            <form id="login-form" 
                  action="../../controllers/AuthController.php?action=login" 
                  method="POST" 
                  class="login-form"
                  autocomplete="on"
                  novalidate>
                
                <!-- ============================================================
                CAMPO: E-MAIL
                ============================================================ -->
                <div class="form-group">
                    <label for="email" class="form-label">
                        <span class="label-icon" aria-hidden="true">📧</span>
                        E-mail:
                    </label>
                    <div class="input-wrapper">
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-input" 
                               placeholder="Digite seu e-mail (ex: aluno@escola.com)" 
                               value="<?php echo $email_preenchido; ?>"
                               required 
                               autofocus
                               aria-describedby="email-help"
                               autocomplete="email">
                        <button type="button" 
                                class="input-clear" 
                                onclick="document.getElementById('email').value=''; this.style.display='none';"
                                aria-label="Limpar campo de e-mail"
                                style="display: none;">
                            ✕
                        </button>
                    </div>
                    <small id="email-help" class="form-help">
                        <span aria-hidden="true">💡</span> 
                        Digite o e-mail cadastrado pelo professor.
                    </small>
                    <div id="email-error" class="form-error" role="alert" style="display: none;">
                        <span aria-hidden="true">❌</span> 
                        Por favor, digite um e-mail válido.
                    </div>
                </div>
                
                <!-- ============================================================
                CAMPO: SENHA
                ============================================================ -->
                <div class="form-group">
                    <label for="senha" class="form-label">
                        <span class="label-icon" aria-hidden="true">🔒</span>
                        Senha:
                    </label>
                    <div class="input-wrapper">
                        <input type="password" 
                               id="senha" 
                               name="senha" 
                               class="form-input" 
                               placeholder="Digite sua senha" 
                               required
                               aria-describedby="senha-help"
                               autocomplete="current-password">
                        <button type="button" 
                                class="password-toggle" 
                                onclick="toggleSenha()"
                                aria-label="Mostrar ou ocultar senha">
                            <span aria-hidden="true" id="toggle-icon">👁️</span>
                        </button>
                    </div>
                    <small id="senha-help" class="form-help">
                        <span aria-hidden="true">💡</span> 
                        A senha tem pelo menos 4 caracteres.
                    </small>
                    <div id="senha-error" class="form-error" role="alert" style="display: none;">
                        <span aria-hidden="true">❌</span> 
                        A senha deve ter pelo menos 4 caracteres.
                    </div>
                </div>
                
                <!-- ============================================================
                OPÇÕES ADICIONAIS (Lembrar-me)
                ============================================================ -->
                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="lembrar" id="lembrar" value="1">
                        <span class="checkbox-custom"></span>
                        <span class="checkbox-text">Lembrar-me</span>
                    </label>
                    
                    <a href="#" class="forgot-password" onclick="showRecuperacao()">
                        Esqueceu sua senha?
                    </a>
                </div>
                
                <!-- ============================================================
                BOTÃO DE ENVIO
                ============================================================ -->
                <div class="form-actions">
                    <button type="submit" class="btn-login" id="btn-login">
                        <span aria-hidden="true">🚀</span> 
                        Entrar no Sistema
                    </button>
                </div>
                
                <!-- ============================================================
                CSRF TOKEN (Segurança)
                ============================================================ -->
                <input type="hidden" name="csrf_token" value="<?php echo gerarTokenCSRF(); ?>">
                
                <!-- ============================================================
                BOTÃO OUVIR (Web Speech API)
                ============================================================ -->
                <button type="button" class="btn-ouvir" onclick="lerTextoEnunciado()" aria-label="Ouvir instruções">
                    <span aria-hidden="true">🔊</span> Ouvir instruções
                </button>
                
            </form>
            
            <!-- ============================================================
            RODAPÉ DO CARD DE LOGIN
            ============================================================ -->
            <div class="login-footer">
                <p class="footer-text">
                    <span aria-hidden="true">📚</span> 
                    Sistema desenvolvido para o ensino de equações para jovens com TEA.
                </p>
                <p class="footer-help">
                    <span aria-hidden="true">❓</span> 
                    Dúvidas? Entre em contato com seu professor.
                </p>
                
                <!-- ============================================================
                INDICADOR DE CONEXÃO
                ============================================================ -->
                <div class="status-indicator" aria-live="polite">
                    <span class="status-dot" id="status-dot"></span>
                    <span id="status-text">Verificando conexão...</span>
                </div>
            </div>
        </div>
        
        <!-- ============================================================
        RECURSOS ADICIONAIS (Tema, Fonte, Contraste)
        ============================================================ -->
        <div class="acessibilidade-tools" role="toolbar" aria-label="Ferramentas de acessibilidade">
            <button class="tool-btn" onclick="toggleFonte()" aria-label="Alternar fonte Open Dyslexic">
                <span aria-hidden="true">🔤</span> Fonte
            </button>
            <button class="tool-btn" onclick="toggleContraste()" aria-label="Alternar alto contraste">
                <span aria-hidden="true">🌓</span> Contraste
            </button>
            <button class="tool-btn" onclick="aumentarFonte()" aria-label="Aumentar tamanho da fonte">
                <span aria-hidden="true">A+</span>
            </button>
            <button class="tool-btn" onclick="diminuirFonte()" aria-label="Diminuir tamanho da fonte">
                <span aria-hidden="true">A-</span>
            </button>
        </div>
    </main>
    
    <!-- ============================================================
    MODAL DE RECUPERAÇÃO DE SENHA
    ============================================================ -->
    <div id="modal-recuperacao" class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">🔑 Recuperar Senha</h2>
                <button class="modal-close" onclick="fecharModal('modal-recuperacao')" aria-label="Fechar modal">✕</button>
            </div>
            <div class="modal-body">
                <p>Para recuperar sua senha, entre em contato com seu professor.</p>
                <p>O professor pode redefinir sua senha diretamente no sistema.</p>
                <div class="modal-info">
                    <span aria-hidden="true">📞</span>
                    <strong>Contato:</strong> professor@equatea.com
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal-close" onclick="fecharModal('modal-recuperacao')">Entendi</button>
            </div>
        </div>
    </div>
    
    <!-- ============================================================
    SCRIPTS JAVASCRIPT
    ============================================================ -->
    <script src="../../public/js/main.js"></script>
    <script src="../../public/js/login.js"></script>
    <script src="../../public/js/speech.js"></script>
    
    <script>
        /**
         * ============================================================
         * INICIALIZAÇÃO DA PÁGINA
         * ============================================================
         */
        document.addEventListener('DOMContentLoaded', function() {
            // ============================================================
            // 1. VERIFICAR CONEXÃO COM O SERVIDOR
            // ============================================================
            verificarConexao();
            
            // ============================================================
            // 2. VALIDAÇÃO EM TEMPO REAL DO FORMULÁRIO
            // ============================================================
            const form = document.getElementById('login-form');
            const emailInput = document.getElementById('email');
            const senhaInput = document.getElementById('senha');
            
            // Validação do e-mail em tempo real
            emailInput.addEventListener('blur', function() {
                validarEmail(this);
            });
            
            emailInput.addEventListener('input', function() {
                const errorDiv = document.getElementById('email-error');
                if (this.value && this.checkValidity()) {
                    errorDiv.style.display = 'none';
                    this.classList.remove('invalid');
                    this.classList.add('valid');
                } else {
                    this.classList.remove('valid');
                }
            });
            
            // Validação da senha em tempo real
            senhaInput.addEventListener('input', function() {
                const errorDiv = document.getElementById('senha-error');
                if (this.value.length >= 4) {
                    errorDiv.style.display = 'none';
                    this.classList.remove('invalid');
                    this.classList.add('valid');
                } else if (this.value.length > 0) {
                    errorDiv.style.display = 'block';
                    this.classList.add('invalid');
                    this.classList.remove('valid');
                } else {
                    this.classList.remove('valid', 'invalid');
                }
            });
            
            // ============================================================
            // 3. SUBMISSÃO DO FORMULÁRIO
            // ============================================================
            form.addEventListener('submit', function(e) {
                // Validação final antes do envio
                let isValid = true;
                
                // Validar e-mail
                if (!emailInput.value || !emailInput.checkValidity()) {
                    document.getElementById('email-error').style.display = 'block';
                    emailInput.classList.add('invalid');
                    isValid = false;
                }
                
                // Validar senha
                if (!senhaInput.value || senhaInput.value.length < 4) {
                    document.getElementById('senha-error').style.display = 'block';
                    senhaInput.classList.add('invalid');
                    isValid = false;
                }
                
                if (!isValid) {
                    e.preventDefault();
                    // Foco no primeiro campo com erro
                    if (emailInput.classList.contains('invalid')) {
                        emailInput.focus();
                    } else if (senhaInput.classList.contains('invalid')) {
                        senhaInput.focus();
                    }
                }
            });
            
            // ============================================================
            // 4. EVENTO DE TECLADO PARA SUBMISSÃO (ENTER)
            // ============================================================
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    const activeElement = document.activeElement;
                    if (activeElement && activeElement.tagName === 'INPUT') {
                        const form = activeElement.closest('form');
                        if (form) {
                            form.dispatchEvent(new Event('submit'));
                        }
                    }
                }
            });
            
            // ============================================================
            // 5. RECURSOS DE ACESSIBILIDADE
            // ============================================================
            
            // Detectar se o usuário usa leitor de tela
            if (window.navigator && window.navigator.userAgent) {
                const ua = window.navigator.userAgent;
                if (ua.includes('JAWS') || ua.includes('NVDA') || ua.includes('VoiceOver')) {
                    document.body.classList.add('screen-reader');
                }
            }
            
            // Preferência de contraste (salva em localStorage)
            if (localStorage.getItem('alto_contraste') === 'true') {
                document.body.classList.add('alto-contraste');
            }
            
            // Preferência de fonte (salva em localStorage)
            if (localStorage.getItem('fonte_dyslexic') === 'true') {
                document.body.classList.add('fonte-dyslexic');
            }
            
            // Tamanho da fonte (salvo em localStorage)
            const tamanhoFonte = localStorage.getItem('tamanho_fonte');
            if (tamanhoFonte) {
                document.body.style.fontSize = tamanhoFonte + 'px';
            }
            
            // ============================================================
            // 6. OUVIR INSTRUÇÕES (Web Speech API)
            // ============================================================
            
            window.lerTextoEnunciado = function() {
                const texto = 'Bem-vindo ao EquaTEA! Faça login com seu e-mail e senha. ' +
                              'Se você tem dificuldade de leitura, use o botão de alto contraste ou a fonte especial. ' +
                              'Caso precise de ajuda, entre em contato com seu professor.';
                lerTexto(texto);
            };
        });
        
        // ============================================================
        // FUNÇÕES AUXILIARES
        // ============================================================
        
        /**
         * Alterna a visibilidade da senha
         */
        function toggleSenha() {
            const senhaInput = document.getElementById('senha');
            const toggleIcon = document.getElementById('toggle-icon');
            
            if (senhaInput.type === 'password') {
                senhaInput.type = 'text';
                toggleIcon.textContent = '🙈';
                document.querySelector('.password-toggle').setAttribute('aria-label', 'Ocultar senha');
            } else {
                senhaInput.type = 'password';
                toggleIcon.textContent = '👁️';
                document.querySelector('.password-toggle').setAttribute('aria-label', 'Mostrar senha');
            }
        }
        
        /**
         * Valida o campo de e-mail
         */
        function validarEmail(input) {
            const errorDiv = document.getElementById('email-error');
            if (!input.value) {
                errorDiv.style.display = 'block';
                errorDiv.innerHTML = '<span aria-hidden="true">❌</span> Por favor, digite seu e-mail.';
                input.classList.add('invalid');
                return false;
            }
            
            if (!input.checkValidity()) {
                errorDiv.style.display = 'block';
                errorDiv.innerHTML = '<span aria-hidden="true">❌</span> Por favor, digite um e-mail válido.';
                input.classList.add('invalid');
                return false;
            }
            
            errorDiv.style.display = 'none';
            input.classList.remove('invalid');
            input.classList.add('valid');
            return true;
        }
        
        /**
         * Abre o modal de recuperação de senha
         */
        function showRecuperacao() {
            document.getElementById('modal-recuperacao').style.display = 'block';
            document.getElementById('modal-recuperacao').setAttribute('aria-hidden', 'false');
            document.getElementById('login-form').setAttribute('aria-hidden', 'true');
        }
        
        /**
         * Fecha um modal
         */
        function fecharModal(id) {
            const modal = document.getElementById(id);
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            document.getElementById('login-form').setAttribute('aria-hidden', 'false');
            // Volta o foco para o último elemento focado
            document.getElementById('email').focus();
        }
        
        /**
         * Alterna a fonte para Open Dyslexic
         */
        function toggleFonte() {
            const body = document.body;
            body.classList.toggle('fonte-dyslexic');
            localStorage.setItem('fonte_dyslexic', body.classList.contains('fonte-dyslexic'));
        }
        
        /**
         * Alterna o alto contraste
         */
        function toggleContraste() {
            const body = document.body;
            body.classList.toggle('alto-contraste');
            localStorage.setItem('alto_contraste', body.classList.contains('alto-contraste'));
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
        }
        
        /**
         * Verifica a conexão com o servidor
         */
        function verificarConexao() {
            const dot = document.getElementById('status-dot');
            const text = document.getElementById('status-text');
            
            dot.className = 'status-dot checking';
            text.textContent = 'Verificando conexão...';
            
            fetch('../../index.php', { method: 'HEAD', cache: 'no-cache' })
                .then(function(response) {
                    if (response.ok) {
                        dot.className = 'status-dot online';
                        text.textContent = '✓ Conectado ao servidor';
                    } else {
                        dot.className = 'status-dot offline';
                        text.textContent = '⚠️ Servidor com problemas de resposta';
                    }
                })
                .catch(function() {
                    dot.className = 'status-dot offline';
                    text.textContent = '⚠️ Servidor indisponível. Verifique sua conexão.';
                });
        }
    </script>
</body>
</html>