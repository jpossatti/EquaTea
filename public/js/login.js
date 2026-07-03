/**
 * ============================================================
 * login.js
 * JavaScript específico para a página de login do EquaTEA
 * 
 * @package EquaTEA
 * @subpackage Public/JS
 * @version 1.0
 * ============================================================
 */

/**
 * Mostra ou oculta a senha no campo de senha
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
 * @param {HTMLInputElement} input - O campo de entrada
 * @returns {boolean} - true se válido, false caso contrário
 */
function validarEmail(input) {
    const errorDiv = document.getElementById('email-error');
    const email = input.value.trim();
    
    if (!email) {
        errorDiv.style.display = 'block';
        errorDiv.innerHTML = '<span aria-hidden="true">❌</span> Por favor, digite seu e-mail.';
        input.classList.add('invalid');
        input.classList.remove('valid');
        return false;
    }
    
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailRegex.test(email)) {
        errorDiv.style.display = 'block';
        errorDiv.innerHTML = '<span aria-hidden="true">❌</span> Por favor, digite um e-mail válido (ex: nome@dominio.com).';
        input.classList.add('invalid');
        input.classList.remove('valid');
        return false;
    }
    
    errorDiv.style.display = 'none';
    input.classList.remove('invalid');
    input.classList.add('valid');
    return true;
}

/**
 * Valida o campo de senha
 * @param {HTMLInputElement} input - O campo de entrada
 * @returns {boolean} - true se válido, false caso contrário
 */
function validarSenha(input) {
    const errorDiv = document.getElementById('senha-error');
    const senha = input.value;
    
    if (!senha) {
        errorDiv.style.display = 'block';
        errorDiv.innerHTML = '<span aria-hidden="true">❌</span> Por favor, digite sua senha.';
        input.classList.add('invalid');
        input.classList.remove('valid');
        return false;
    }
    
    if (senha.length < 4) {
        errorDiv.style.display = 'block';
        errorDiv.innerHTML = '<span aria-hidden="true">❌</span> A senha deve ter pelo menos 4 caracteres.';
        input.classList.add('invalid');
        input.classList.remove('valid');
        return false;
    }
    
    errorDiv.style.display = 'none';
    input.classList.remove('invalid');
    input.classList.add('valid');
    return true;
}

/**
 * Mostra o modal de recuperação de senha
 */
function showRecuperacao() {
    const modal = document.getElementById('modal-recuperacao');
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');
    document.getElementById('login-form').setAttribute('aria-hidden', 'true');
    
    // Foco no primeiro elemento interativo do modal
    const closeButton = modal.querySelector('.modal-close');
    if (closeButton) {
        setTimeout(function() {
            closeButton.focus();
        }, 100);
    }
}

/**
 * Fecha um modal
 * @param {string} id - ID do modal a ser fechado
 */
function fecharModal(id) {
    const modal = document.getElementById(id);
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    document.getElementById('login-form').setAttribute('aria-hidden', 'false');
    
    // Retorna o foco para o campo de e-mail
    document.getElementById('email').focus();
}

/**
 * Alterna a fonte para Open Dyslexic
 */
function toggleFonte() {
    const body = document.body;
    body.classList.toggle('fonte-dyslexic');
    localStorage.setItem('fonte_dyslexic', body.classList.contains('fonte-dyslexic'));
    
    // Anuncia a mudança para leitores de tela
    const status = body.classList.contains('fonte-dyslexic') ? 'ativada' : 'desativada';
    const mensagem = 'Fonte para dislexia ' + status;
    anunciarMudanca(mensagem);
}

/**
 * Alterna o alto contraste
 */
function toggleContraste() {
    const body = document.body;
    body.classList.toggle('alto-contraste');
    localStorage.setItem('alto_contraste', body.classList.contains('alto-contraste'));
    
    // Anuncia a mudança para leitores de tela
    const status = body.classList.contains('alto-contraste') ? 'ativado' : 'desativado';
    const mensagem = 'Alto contraste ' + status;
    anunciarMudanca(mensagem);
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
 * Anuncia uma mudança para leitores de tela
 * @param {string} mensagem - Mensagem a ser anunciada
 */
function anunciarMudanca(mensagem) {
    // Cria um elemento de anúncio ARIA se não existir
    let announcer = document.getElementById('anunciador');
    if (!announcer) {
        announcer = document.createElement('div');
        announcer.id = 'anunciador';
        announcer.setAttribute('aria-live', 'polite');
        announcer.setAttribute('aria-atomic', 'true');
        announcer.style.position = 'absolute';
        announcer.style.width = '1px';
        announcer.style.height = '1px';
        announcer.style.padding = '0';
        announcer.style.margin = '-1px';
        announcer.style.overflow = 'hidden';
        announcer.style.clip = 'rect(0, 0, 0, 0)';
        announcer.style.border = '0';
        document.body.appendChild(announcer);
    }
    
    announcer.textContent = mensagem;
}

/**
 * Verifica a conexão com o servidor
 */
function verificarConexao() {
    const dot = document.getElementById('status-dot');
    const text = document.getElementById('status-text');
    
    if (!dot || !text) return;
    
    dot.className = 'status-dot checking';
    text.textContent = 'Verificando conexão...';
    
    fetch('../../index.php', { 
        method: 'HEAD', 
        cache: 'no-cache',
        headers: {
            'Cache-Control': 'no-cache, no-store, must-revalidate'
        }
    })
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

/**
 * Inicializa a página quando o DOM estiver carregado
 */
document.addEventListener('DOMContentLoaded', function() {
    // ============================================================
    // 1. VERIFICAR CONEXÃO COM O SERVIDOR
    // ============================================================
    verificarConexao();
    
    // ============================================================
    // 2. RESTAURAR PREFERÊNCIAS DO USUÁRIO
    // ============================================================
    
    // Preferência de contraste
    if (localStorage.getItem('alto_contraste') === 'true') {
        document.body.classList.add('alto-contraste');
    }
    
    // Preferência de fonte
    if (localStorage.getItem('fonte_dyslexic') === 'true') {
        document.body.classList.add('fonte-dyslexic');
    }
    
    // Tamanho da fonte
    const tamanhoFonte = localStorage.getItem('tamanho_fonte');
    if (tamanhoFonte) {
        document.body.style.fontSize = tamanhoFonte + 'px';
    }
    
    // ============================================================
    // 3. VALIDAÇÃO EM TEMPO REAL
    // ============================================================
    
    const emailInput = document.getElementById('email');
    const senhaInput = document.getElementById('senha');
    
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            validarEmail(this);
        });
        
        emailInput.addEventListener('input', function() {
            if (this.value.trim() && this.checkValidity()) {
                const errorDiv = document.getElementById('email-error');
                errorDiv.style.display = 'none';
                this.classList.remove('invalid');
                this.classList.add('valid');
            } else if (!this.value.trim()) {
                this.classList.remove('valid', 'invalid');
            }
        });
        
        // Limpar campo se tiver conteúdo
        emailInput.addEventListener('input', function() {
            const clearBtn = document.querySelector('.input-clear');
            if (clearBtn) {
                clearBtn.style.display = this.value ? 'block' : 'none';
            }
        });
    }
    
    if (senhaInput) {
        senhaInput.addEventListener('input', function() {
            if (this.value.length >= 4) {
                const errorDiv = document.getElementById('senha-error');
                errorDiv.style.display = 'none';
                this.classList.remove('invalid');
                this.classList.add('valid');
            } else if (this.value.length > 0) {
                const errorDiv = document.getElementById('senha-error');
                errorDiv.style.display = 'block';
                errorDiv.innerHTML = '<span aria-hidden="true">❌</span> A senha deve ter pelo menos 4 caracteres.';
                this.classList.add('invalid');
                this.classList.remove('valid');
            } else {
                this.classList.remove('valid', 'invalid');
            }
        });
    }
    
    // ============================================================
    // 4. SUBMISSÃO DO FORMULÁRIO
    // ============================================================
    
    const form = document.getElementById('login-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validar e-mail
            if (emailInput) {
                if (!validarEmail(emailInput)) {
                    isValid = false;
                }
            }
            
            // Validar senha
            if (senhaInput) {
                if (!validarSenha(senhaInput)) {
                    isValid = false;
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                // Foco no primeiro campo com erro
                if (emailInput && emailInput.classList.contains('invalid')) {
                    emailInput.focus();
                } else if (senhaInput && senhaInput.classList.contains('invalid')) {
                    senhaInput.focus();
                }
            }
        });
    }
    
    // ============================================================
    // 5. EVENTO DE TECLADO PARA FECHAR MODAL (ESC)
    // ============================================================
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('modal-recuperacao');
            if (modal && modal.style.display === 'block') {
                fecharModal('modal-recuperacao');
            }
        }
    });
    
    // ============================================================
    // 6. FECHAR MODAL CLICANDO FORA DO CONTEÚDO
    // ============================================================
    
    const modal = document.getElementById('modal-recuperacao');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModal('modal-recuperacao');
            }
        });
    }
    
    // ============================================================
    // 7. VERIFICAR SE O NAVEGADOR SUPORTA WEB SPEECH API
    // ============================================================
    
    if (!('speechSynthesis' in window)) {
        const btnOuvir = document.querySelector('.btn-ouvir');
        if (btnOuvir) {
            btnOuvir.style.opacity = '0.5';
            btnOuvir.title = 'Seu navegador não suporta a funcionalidade de áudio';
            btnOuvir.disabled = true;
        }
    }
});