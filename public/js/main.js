/**
 * ============================================================
 * main.js
 * Arquivo principal de JavaScript do sistema EquaTEA.
 * 
 * FUNCIONALIDADES:
 * - Inicialização geral do sistema
 * - Gerenciamento de preferências do usuário
 * - Funções utilitárias globais
 * - Controle de navegação
 * - Gerenciamento de modais
 * - Feedback de ações do usuário
 * 
 * @package EquaTEA
 * @subpackage Public/JS
 * @author Equipe EquaTEA
 * @version 1.0
 * ============================================================
 */

// ============================================================
// 1. INICIALIZAÇÃO DO DOM
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    // ============================================================
    // 1.1. RESTAURAR PREFERÊNCIAS DO USUÁRIO
    // ============================================================
    
    restaurarPreferencias();
    
    // ============================================================
    // 1.2. INICIALIZAR COMPONENTES
    // ============================================================
    
    inicializarModais();
    inicializarAlertas();
    inicializarNavegacao();
    inicializarBotoesAcao();
    
    // ============================================================
    // 1.3. DETECTAR NAVEGADOR PARA COMPATIBILIDADE
    // ============================================================
    
    detectarNavegador();
    
    // ============================================================
    // 1.4. VERIFICAR CONEXÃO COM O SERVIDOR
    // ============================================================
    
    verificarConexao();
});

// ============================================================
// 2. GERENCIAMENTO DE PREFERÊNCIAS
// ============================================================

/**
 * Restaura as preferências salvas do usuário
 */
function restaurarPreferencias() {
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
}

/**
 * Salva as preferências do usuário
 * @param {string} chave - Nome da preferência
 * @param {*} valor - Valor da preferência
 */
function salvarPreferencia(chave, valor) {
    localStorage.setItem(chave, valor);
}

// ============================================================
// 3. INICIALIZAÇÃO DE COMPONENTES
// ============================================================

/**
 * Inicializa os modais do sistema
 */
function inicializarModais() {
    // Fechar modais com a tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modais = document.querySelectorAll('.modal, .modal-ajuda');
            modais.forEach(function(modal) {
                if (modal.style.display === 'block') {
                    modal.style.display = 'none';
                    modal.setAttribute('aria-hidden', 'true');
                }
            });
        }
    });
    
    // Fechar modais clicando no overlay
    document.addEventListener('click', function(e) {
        const modalOverlays = document.querySelectorAll('.modal, .modal-ajuda');
        modalOverlays.forEach(function(modal) {
            if (e.target === modal && modal.style.display === 'block') {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
            }
        });
    });
}

/**
 * Inicializa os alertas do sistema
 */
function inicializarAlertas() {
    // Auto-fechamento de alertas após 5 segundos
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if (alert) {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            }
        }, 5000);
    });
    
    // Botões de fechar alertas
    document.querySelectorAll('.alert-close').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const alert = this.closest('.alert');
            if (alert) {
                alert.style.transition = 'opacity 0.3s ease';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 300);
            }
        });
    });
}

/**
 * Inicializa a navegação (destaque do menu ativo)
 */
function inicializarNavegacao() {
    const currentPath = window.location.pathname;
    const menuLinks = document.querySelectorAll('.menu-aluno-link, .menu-professor-link');
    
    menuLinks.forEach(function(link) {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href)) {
            link.closest('li')?.classList.add('active');
        }
    });
}

/**
 * Inicializa botões de ação (como "Voltar ao topo")
 */
function inicializarBotoesAcao() {
    // Botão de voltar ao topo
    const btnTopo = document.getElementById('btn-topo');
    if (btnTopo) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                btnTopo.style.display = 'flex';
            } else {
                btnTopo.style.display = 'none';
            }
        });
        
        btnTopo.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
}

// ============================================================
// 4. FUNÇÕES UTILITÁRIAS
// ============================================================

/**
 * Detecta o navegador para compatibilidade
 */
function detectarNavegador() {
    const ua = navigator.userAgent;
    const navegadores = {
        chrome: /chrome/i.test(ua) && !/edge/i.test(ua),
        firefox: /firefox/i.test(ua),
        safari: /safari/i.test(ua) && !/chrome/i.test(ua),
        edge: /edge/i.test(ua),
        opera: /opr/i.test(ua)
    };
    
    // Adiciona classe ao body para estilos específicos
    for (const [nome, ativo] of Object.entries(navegadores)) {
        if (ativo) {
            document.body.classList.add('navegador-' + nome);
            break;
        }
    }
}

/**
 * Verifica a conexão com o servidor
 */
function verificarConexao() {
    const statusDot = document.querySelector('.status-indicator-dot');
    const statusText = document.querySelector('.status-text');
    
    if (!statusDot || !statusText) return;
    
    statusDot.className = 'status-indicator-dot checking';
    statusText.textContent = 'Verificando conexão...';
    
    fetch(BASE_URL + 'index.php', {
        method: 'HEAD',
        cache: 'no-cache',
        headers: {
            'Cache-Control': 'no-cache, no-store, must-revalidate'
        }
    })
    .then(function(response) {
        if (response.ok) {
            statusDot.className = 'status-indicator-dot online';
            statusText.textContent = '✓ Conectado ao servidor';
        } else {
            statusDot.className = 'status-indicator-dot warning';
            statusText.textContent = '⚠️ Servidor com problemas';
        }
    })
    .catch(function() {
        statusDot.className = 'status-indicator-dot offline';
        statusText.textContent = '⚠️ Servidor indisponível';
    });
}

/**
 * Anuncia uma mensagem para leitores de tela
 * @param {string} mensagem - Mensagem a ser anunciada
 */
function anunciarMudanca(mensagem) {
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
 * Mostra um feedback flutuante
 * @param {string} mensagem - Mensagem a ser exibida
 * @param {string} tipo - Tipo de feedback (success, error, info)
 * @param {number} duracao - Duração em milissegundos
 */
function mostrarFeedbackFlutuante(mensagem, tipo = 'success', duracao = 3000) {
    const feedback = document.createElement('div');
    feedback.className = 'feedback-flutuante feedback-' + tipo;
    feedback.textContent = mensagem;
    feedback.style.position = 'fixed';
    feedback.style.bottom = '20px';
    feedback.style.left = '50%';
    feedback.style.transform = 'translateX(-50%)';
    feedback.style.padding = '16px 24px';
    feedback.style.borderRadius = '8px';
    feedback.style.fontSize = '18px';
    feedback.style.zIndex = '9999';
    feedback.style.boxShadow = '0 4px 12px rgba(0,0,0,0.2)';
    feedback.style.animation = 'fadeInUp 0.3s ease';
    
    // Cores baseadas no tipo
    const cores = {
        success: { bg: '#28a745', color: '#fff' },
        error: { bg: '#dc3545', color: '#fff' },
        info: { bg: '#3498db', color: '#fff' },
        warning: { bg: '#f39c12', color: '#fff' }
    };
    
    const cor = cores[tipo] || cores.info;
    feedback.style.backgroundColor = cor.bg;
    feedback.style.color = cor.color;
    
    document.body.appendChild(feedback);
    
    setTimeout(function() {
        feedback.style.opacity = '0';
        feedback.style.transition = 'opacity 0.5s ease';
        setTimeout(function() {
            document.body.removeChild(feedback);
        }, 500);
    }, duracao);
}

/**
 * Formata um valor para exibição como moeda
 * @param {number} valor - Valor a ser formatado
 * @returns {string} Valor formatado
 */
function formatarMoeda(valor) {
    return 'R$ ' + valor.toFixed(2).replace('.', ',');
}

/**
 * Formata uma data para exibição
 * @param {string} data - Data no formato ISO
 * @returns {string} Data formatada
 */
function formatarData(data) {
    if (!data) return '-';
    const d = new Date(data);
    return d.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Trunca um texto para um tamanho máximo
 * @param {string} texto - Texto a ser truncado
 * @param {number} tamanho - Tamanho máximo
 * @param {string} sufixo - Sufixo para texto truncado
 * @returns {string} Texto truncado
 */
function truncarTexto(texto, tamanho = 100, sufixo = '...') {
    if (!texto) return '';
    if (texto.length <= tamanho) return texto;
    return texto.substring(0, tamanho) + sufixo;
}

/**
 * Gera um ID único para elementos
 * @param {string} prefix - Prefixo opcional
 * @returns {string} ID único
 */
function gerarIdUnico(prefix = 'id') {
    return prefix + '_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
}

// ============================================================
// 5. EXPORTAÇÃO PARA O ESCOPO GLOBAL
// ============================================================

// Torna as funções globais acessíveis em outros scripts
window.restaurarPreferencias = restaurarPreferencias;
window.salvarPreferencia = salvarPreferencia;
window.anunciarMudanca = anunciarMudanca;
window.mostrarFeedbackFlutuante = mostrarFeedbackFlutuante;
window.formatarData = formatarData;
window.truncarTexto = truncarTexto;
window.gerarIdUnico = gerarIdUnico;