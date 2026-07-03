/**
 * ============================================================
 * acessibilidade.js
 * Gerenciamento de recursos de acessibilidade do sistema EquaTEA.
 * 
 * FUNCIONALIDADES:
 * - Alto contraste
 * - Fonte Open Dyslexic
 * - Ajuste de tamanho de fonte
 * - Navegação por teclado
 * - Skip links
 * - Preferências salvas em localStorage
 * - Leitores de tela (ARIA)
 * 
 * @package EquaTEA
 * @subpackage Public/JS
 * @author Equipe EquaTEA
 * @version 1.0
 * ============================================================
 */

// ============================================================
// 1. INICIALIZAÇÃO
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    inicializarAcessibilidade();
    inicializarSkipLinks();
    inicializarNavegacaoTeclado();
    inicializarARIA();
});

// ============================================================
// 2. FUNÇÕES DE ACESSIBILIDADE
// ============================================================

/**
 * Inicializa os recursos de acessibilidade
 */
function inicializarAcessibilidade() {
    // ============================================================
    // 2.1. RESTAURAR PREFERÊNCIAS
    // ============================================================
    
    // Alto contraste
    if (localStorage.getItem('alto_contraste') === 'true') {
        document.body.classList.add('alto-contraste');
        atualizarBotoesAcessibilidade('contraste', true);
    }
    
    // Fonte disléxica
    if (localStorage.getItem('fonte_dyslexic') === 'true') {
        document.body.classList.add('fonte-dyslexic');
        atualizarBotoesAcessibilidade('fonte', true);
    }
    
    // Tamanho da fonte
    const tamanhoFonte = localStorage.getItem('tamanho_fonte');
    if (tamanhoFonte) {
        document.body.style.fontSize = tamanhoFonte + 'px';
    }
    
    // ============================================================
    // 2.2. EVENTOS DOS BOTÕES
    // ============================================================
    
    // Botão de alto contraste
    document.querySelectorAll('[data-acao="contraste"], .btn-contraste').forEach(function(btn) {
        btn.addEventListener('click', function() {
            toggleAltoContraste();
        });
        btn.setAttribute('aria-pressed', document.body.classList.contains('alto-contraste'));
    });
    
    // Botão de fonte disléxica
    document.querySelectorAll('[data-acao="fonte"], .btn-fonte').forEach(function(btn) {
        btn.addEventListener('click', function() {
            toggleFonteDyslexic();
        });
        btn.setAttribute('aria-pressed', document.body.classList.contains('fonte-dyslexic'));
    });
    
    // Botão de aumentar fonte
    document.querySelectorAll('[data-acao="aumentar-fonte"], .btn-aumentar-fonte').forEach(function(btn) {
        btn.addEventListener('click', function() {
            aumentarFonte();
        });
    });
    
    // Botão de diminuir fonte
    document.querySelectorAll('[data-acao="diminuir-fonte"], .btn-diminuir-fonte').forEach(function(btn) {
        btn.addEventListener('click', function() {
            diminuirFonte();
        });
    });
    
    // Botão de resetar fonte
    document.querySelectorAll('[data-acao="resetar-fonte"], .btn-resetar-fonte').forEach(function(btn) {
        btn.addEventListener('click', function() {
            resetarFonte();
        });
    });
}

/**
 * Atualiza o estado dos botões de acessibilidade
 * @param {string} tipo - Tipo do botão (contraste, fonte)
 * @param {boolean} ativo - Se está ativo
 */
function atualizarBotoesAcessibilidade(tipo, ativo) {
    const seletor = tipo === 'contraste' ? '[data-acao="contraste"]' : '[data-acao="fonte"]';
    document.querySelectorAll(seletor).forEach(function(btn) {
        btn.setAttribute('aria-pressed', ativo);
        if (ativo) {
            btn.classList.add('ativo');
        } else {
            btn.classList.remove('ativo');
        }
    });
}

// ============================================================
// 3. FUNÇÕES DE CONTROLE
// ============================================================

/**
 * Alterna o alto contraste
 */
function toggleAltoContraste() {
    const body = document.body;
    body.classList.toggle('alto-contraste');
    const ativo = body.classList.contains('alto-contraste');
    localStorage.setItem('alto_contraste', ativo ? 'true' : 'false');
    atualizarBotoesAcessibilidade('contraste', ativo);
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
    atualizarBotoesAcessibilidade('fonte', ativo);
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

// ============================================================
// 4. SKIP LINKS (navegação por teclado)
// ============================================================

/**
 * Inicializa os skip links
 */
function inicializarSkipLinks() {
    const skipLinks = document.querySelectorAll('.skip-link');
    skipLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId && targetId.startsWith('#')) {
                const target = document.querySelector(targetId);
                if (target) {
                    target.setAttribute('tabindex', '-1');
                    target.focus();
                    // Remove o tabindex após o foco
                    setTimeout(function() {
                        target.removeAttribute('tabindex');
                    }, 100);
                }
            }
        });
    });
}

// ============================================================
// 5. NAVEGAÇÃO POR TECLADO
// ============================================================

/**
 * Inicializa a navegação por teclado
 */
function inicializarNavegacaoTeclado() {
    // ============================================================
    // 5.1. FOCUS TRAP para modais
    // ============================================================
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            const modais = document.querySelectorAll('.modal, .modal-ajuda');
            modais.forEach(function(modal) {
                if (modal.style.display === 'block') {
                    trapFocus(modal, e);
                }
            });
        }
    });
    
    // ============================================================
    // 5.2. INDICADOR DE FOCO
    // ============================================================
    
    // Adicionar classe ao focar elementos
    document.addEventListener('focusin', function(e) {
        const target = e.target;
        if (target && target.matches('a, button, input, select, textarea, [tabindex]')) {
            target.classList.add('foco-ativo');
        }
    });
    
    document.addEventListener('focusout', function(e) {
        const target = e.target;
        if (target) {
            target.classList.remove('foco-ativo');
        }
    });
}

/**
 * Trap de foco para modais (mantém o foco dentro do modal)
 * @param {HTMLElement} modal - Elemento do modal
 * @param {KeyboardEvent} e - Evento de teclado
 */
function trapFocus(modal, e) {
    const focusable = modal.querySelectorAll(
        'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
    );
    
    if (focusable.length === 0) return;
    
    const firstFocusable = focusable[0];
    const lastFocusable = focusable[focusable.length - 1];
    const activeElement = document.activeElement;
    
    if (e.shiftKey) {
        if (activeElement === firstFocusable) {
            e.preventDefault();
            lastFocusable.focus();
        }
    } else {
        if (activeElement === lastFocusable) {
            e.preventDefault();
            firstFocusable.focus();
        }
    }
}

// ============================================================
// 6. ARIA (Accessible Rich Internet Applications)
// ============================================================

/**
 * Inicializa atributos ARIA para melhor acessibilidade
 */
function inicializarARIA() {
    // ============================================================
    // 6.1. TABELAS
    // ============================================================
    
    document.querySelectorAll('.tabela-relatorio, .progresso-table, .list-table').forEach(function(tabela) {
        tabela.setAttribute('role', 'table');
        tabela.querySelectorAll('thead').forEach(function(thead) {
            thead.setAttribute('role', 'rowgroup');
        });
        tabela.querySelectorAll('tbody').forEach(function(tbody) {
            tbody.setAttribute('role', 'rowgroup');
        });
        tabela.querySelectorAll('tr').forEach(function(tr) {
            tr.setAttribute('role', 'row');
        });
        tabela.querySelectorAll('th').forEach(function(th) {
            th.setAttribute('role', 'columnheader');
        });
        tabela.querySelectorAll('td').forEach(function(td) {
            td.setAttribute('role', 'cell');
        });
    });
    
    // ============================================================
    // 6.2. BOTÕES COM ÍCONES
    // ============================================================
    
    document.querySelectorAll('.btn-icone, .btn-acao, .btn-icone-simples').forEach(function(btn) {
        if (!btn.getAttribute('aria-label')) {
            const text = btn.textContent.trim();
            if (text) {
                btn.setAttribute('aria-label', text);
            }
        }
    });
    
    // ============================================================
    // 6.3. CARDS E SEÇÕES
    // ============================================================
    
    document.querySelectorAll('.stat-card, .resumo-card, .dica-card').forEach(function(card) {
        card.setAttribute('role', 'article');
    });
    
    // ============================================================
    // 6.4. BARRA DE PROGRESSO
    // ============================================================
    
    document.querySelectorAll('.progresso-barras .barra-container, .erro-bar-container').forEach(function(barra) {
        const fill = barra.querySelector('.barra-preenchimento, .erro-bar');
        if (fill) {
            const width = fill.style.width;
            barra.setAttribute('role', 'progressbar');
            barra.setAttribute('aria-valuenow', parseInt(width) || 0);
            barra.setAttribute('aria-valuemin', '0');
            barra.setAttribute('aria-valuemax', '100');
        }
    });
}

// ============================================================
// 7. FUNÇÕES DE DETECÇÃO
// ============================================================

/**
 * Detecta se o usuário está usando um leitor de tela
 * @returns {boolean} True se está usando leitor de tela
 */
function isScreenReader() {
    const ua = navigator.userAgent;
    return ua.includes('JAWS') || 
           ua.includes('NVDA') || 
           ua.includes('VoiceOver') || 
           ua.includes('TalkBack') ||
           ua.includes('ChromeVox');
}

/**
 * Detecta se o usuário prefere alto contraste (configuração do sistema)
 * @returns {boolean} True se prefere alto contraste
 */
function prefersAltoContraste() {
    return window.matchMedia && window.matchMedia('(prefers-contrast: high)').matches;
}

/**
 * Detecta se o usuário prefere redução de movimento
 * @returns {boolean} True se prefere redução de movimento
 */
function prefersReducaoMovimento() {
    return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

// ============================================================
// 8. EXPORTAÇÃO PARA O ESCOPO GLOBAL
// ============================================================

window.toggleAltoContraste = toggleAltoContraste;
window.toggleFonteDyslexic = toggleFonteDyslexic;
window.aumentarFonte = aumentarFonte;
window.diminuirFonte = diminuirFonte;
window.resetarFonte = resetarFonte;
window.isScreenReader = isScreenReader;
window.prefersAltoContraste = prefersAltoContraste;
window.prefersReducaoMovimento = prefersReducaoMovimento;