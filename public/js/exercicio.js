/**
 * ============================================================
 * exercicio.js
 * JavaScript específico para a página de exercícios do aluno.
 * 
 * FUNCIONALIDADES:
 * - Validação de resposta em tempo real
 * - Submissão assíncrona (AJAX) da resposta
 * - Feedback visual imediato
 * - Controle de passos
 * - Gerenciamento de tentativas
 * - Integração com o serviço de áudio
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
    
    // ============================================================
    // 1.1. FOCAR NO CAMPO DE RESPOSTA
    // ============================================================
    
    const inputResposta = document.getElementById('resposta-input');
    if (inputResposta) {
        setTimeout(function() {
            inputResposta.focus();
        }, 300);
    }
    
    // ============================================================
    // 1.2. INICIALIZAR COMPONENTES
    // ============================================================
    
    inicializarFormulario();
    inicializarBotoes();
    inicializarFeedback();
    inicializarTeclasAtalho();
});

// ============================================================
// 2. VARIÁVEIS GLOBAIS
// ============================================================

let tentativas = 0;
let passoAtual = parseInt(document.querySelector('[data-passo]')?.getAttribute('data-passo')) || 1;
let equacaoId = parseInt(document.querySelector('[data-equacao-id]')?.getAttribute('data-equacao-id')) || 0;
let alunoId = parseInt(document.querySelector('[data-aluno-id]')?.getAttribute('data-aluno-id')) || 0;

// ============================================================
// 3. INICIALIZAÇÃO DO FORMULÁRIO
// ============================================================

/**
 * Inicializa o formulário de resposta
 */
function inicializarFormulario() {
    const form = document.getElementById('form-resposta');
    if (!form) return;
    
    const input = document.getElementById('resposta-input');
    if (input) {
        // Validação em tempo real
        input.addEventListener('input', function() {
            const errorDiv = document.getElementById('resposta-error');
            const clearBtn = document.querySelector('.input-clear');
            
            if (this.value.trim()) {
                this.classList.remove('invalid');
                if (errorDiv) errorDiv.style.display = 'none';
                if (clearBtn) clearBtn.style.display = 'block';
            } else {
                this.classList.remove('invalid');
                this.classList.remove('valid');
                if (clearBtn) clearBtn.style.display = 'none';
            }
        });
        
        // Validação ao perder o foco
        input.addEventListener('blur', function() {
            if (this.value.trim() && this.value.trim().length > 0) {
                this.classList.add('valid');
            }
        });
        
        // Tecla Enter para submeter
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                verificarResposta();
            }
        });
    }
    
    // Submissão do formulário
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        verificarResposta();
    });
}

/**
 * Inicializa os botões da página
 */
function inicializarBotoes() {
    // Botão de limpar resposta
    const clearBtn = document.querySelector('.input-clear');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            const input = document.getElementById('resposta-input');
            if (input) {
                input.value = '';
                input.focus();
                input.classList.remove('valid');
                this.style.display = 'none';
            }
        });
    }
    
    // Botão de ajuda
    const btnAjuda = document.getElementById('btn-ajuda');
    if (btnAjuda) {
        btnAjuda.addEventListener('click', function() {
            const modal = document.getElementById('modal-ajuda');
            if (modal) {
                modal.style.display = 'block';
                modal.setAttribute('aria-hidden', 'false');
                anunciarMudanca('Modal de ajuda aberto');
            }
        });
    }
    
    // Botão de ouvir
    const btnOuvir = document.querySelector('.btn-ouvir');
    if (btnOuvir) {
        btnOuvir.addEventListener('click', function() {
            lerInstrucoes();
        });
    }
    
    // Botão de ouvir equação
    const btnOuvirEquacao = document.querySelector('.btn-ouvir-equacao');
    if (btnOuvirEquacao) {
        btnOuvirEquacao.addEventListener('click', function() {
            lerEquacao();
        });
    }
}

/**
 * Inicializa a área de feedback
 */
function inicializarFeedback() {
    const feedbackArea = document.getElementById('feedback-area');
    if (feedbackArea) {
        // Esconde o feedback após 5 segundos (se for de sucesso)
        const observer = new MutationObserver(function() {
            if (feedbackArea.style.display !== 'none') {
                const isSuccess = feedbackArea.classList.contains('feedback-success');
                if (isSuccess) {
                    setTimeout(function() {
                        if (feedbackArea.style.display !== 'none') {
                            feedbackArea.style.opacity = '0';
                            feedbackArea.style.transition = 'opacity 0.5s ease';
                            setTimeout(function() {
                                feedbackArea.style.display = 'none';
                                feedbackArea.style.opacity = '1';
                            }, 500);
                        }
                    }, 3000);
                }
            }
        });
        observer.observe(feedbackArea, { attributes: true, attributeFilter: ['style'] });
    }
}

/**
 * Inicializa as teclas de atalho
 */
function inicializarTeclasAtalho() {
    document.addEventListener('keydown', function(e) {
        // Ctrl + Enter: enviar resposta
        if (e.ctrlKey && e.key === 'Enter') {
            e.preventDefault();
            verificarResposta();
        }
        
        // ESC: fechar modal de ajuda
        if (e.key === 'Escape') {
            const modal = document.getElementById('modal-ajuda');
            if (modal && modal.style.display === 'block') {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
                document.getElementById('resposta-input')?.focus();
            }
        }
    });
}

// ============================================================
// 4. FUNÇÃO PRINCIPAL: VERIFICAR RESPOSTA
// ============================================================

/**
 * Verifica a resposta do aluno via AJAX
 */
function verificarResposta() {
    const input = document.getElementById('resposta-input');
    const resposta = input?.value.trim();
    
    if (!resposta) {
        mostrarErro('Por favor, digite sua resposta.');
        input?.focus();
        return;
    }
    
    // ============================================================
    // 4.1. PREPARAR A REQUISIÇÃO
    // ============================================================
    
    const btn = document.getElementById('btn-verificar');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span aria-hidden="true">⏳</span> Verificando...';
    }
    
    const form = document.getElementById('form-resposta');
    const formData = new FormData(form);
    formData.append('ajax', '1');
    
    // ============================================================
    // 4.2. ENVIAR REQUISIÇÃO
    // ============================================================
    
    fetch(BASE_URL + 'app/controllers/ExercicioController.php?action=verificar', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(function(response) {
        if (!response.ok) {
            throw new Error('Erro na comunicação com o servidor.');
        }
        return response.json();
    })
    .then(function(data) {
        // Restaurar botão
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<span aria-hidden="true">✅</span> Verificar Resposta';
        }
        
        // ============================================================
        // 4.3. PROCESSAR RESPOSTA DO SERVIDOR
        // ============================================================
        
        if (data.status === 'success' || data.valido === true) {
            // ACERTOU
            tentativas++;
            atualizarTentativas();
            mostrarFeedback('success', '🎉 Correto!', data.mensagem || 'Parabéns!');
            
            // Animação de celebração (sutil)
            input?.classList.add('feedback-acerto');
            setTimeout(function() {
                input?.classList.remove('feedback-acerto');
            }, 1000);
            
            // Verifica se é o último passo
            if (data.concluido || data.status === 'concluido') {
                // Concluiu a equação
                setTimeout(function() {
                    window.location.href = 'parabens.php?equacao_id=' + equacaoId;
                }, 1500);
            } else {
                // Avançar para o próximo passo
                setTimeout(function() {
                    window.location.reload();
                }, 1200);
            }
            
        } else if (data.status === 'error' || data.valido === false) {
            // ERROU
            tentativas++;
            atualizarTentativas();
            
            const dica = data.dica || 'Tente novamente com atenção!';
            mostrarFeedback('error', '❌ Resposta incorreta!', dica);
            
            input?.classList.add('feedback-erro');
            setTimeout(function() {
                input?.classList.remove('feedback-erro');
            }, 1000);
            
            // Limpar campo e focar
            if (input) {
                input.value = '';
                input.focus();
            }
        } else {
            // ERRO DESCONHECIDO
            mostrarFeedback('error', '❌ Ops!', data.mensagem || 'Ocorreu um erro inesperado.');
        }
    })
    .catch(function(error) {
        console.error('Erro:', error);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<span aria-hidden="true">✅</span> Verificar Resposta';
        }
        mostrarFeedback('error', '❌ Erro de conexão', 'Não foi possível verificar sua resposta. Tente novamente.');
    });
}

// ============================================================
// 5. FUNÇÕES DE FEEDBACK E INTERFACE
// ============================================================

/**
 * Mostra feedback para o usuário
 * @param {string} tipo - Tipo de feedback (success, error)
 * @param {string} titulo - Título do feedback
 * @param {string} mensagem - Mensagem do feedback
 */
function mostrarFeedback(tipo, titulo, mensagem) {
    const area = document.getElementById('feedback-area');
    if (!area) return;
    
    const icon = area.querySelector('.feedback-icon');
    const msg = area.querySelector('.feedback-mensagem');
    const dica = area.querySelector('.feedback-dica');
    
    area.style.display = 'flex';
    area.className = 'feedback-area feedback-' + tipo;
    
    if (icon) {
        icon.textContent = tipo === 'success' ? '✅' : '❌';
    }
    
    if (msg) {
        msg.innerHTML = '<strong>' + titulo + '</strong>';
    }
    
    if (dica) {
        dica.textContent = mensagem;
    }
    
    // Scroll para o feedback
    area.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    // Anunciar para leitores de tela
    anunciarMudanca(titulo + '. ' + mensagem);
}

/**
 * Mostra erro no campo de resposta
 * @param {string} mensagem - Mensagem de erro
 */
function mostrarErro(mensagem) {
    const errorDiv = document.getElementById('resposta-error');
    if (!errorDiv) return;
    
    errorDiv.textContent = mensagem;
    errorDiv.style.display = 'block';
    
    const input = document.getElementById('resposta-input');
    if (input) {
        input.classList.add('invalid');
        setTimeout(function() {
            input.classList.remove('invalid');
        }, 3000);
    }
    
    setTimeout(function() {
        errorDiv.style.display = 'none';
    }, 4000);
}

/**
 * Atualiza o contador de tentativas
 */
function atualizarTentativas() {
    const count = document.getElementById('tentativas-count');
    if (count) {
        count.textContent = tentativas;
    }
}

/**
 * Limpa o campo de resposta
 */
function limparResposta() {
    const input = document.getElementById('resposta-input');
    if (input) {
        input.value = '';
        input.focus();
        input.classList.remove('valid');
        const clearBtn = document.querySelector('.input-clear');
        if (clearBtn) {
            clearBtn.style.display = 'none';
        }
    }
}

// ============================================================
// 6. FUNÇÕES DE ÁUDIO (integração com speech.js)
// ============================================================

/**
 * Lê a equação em voz alta
 */
function lerEquacao() {
    const texto = document.querySelector('.equacao-texto')?.textContent;
    if (texto && typeof lerTexto === 'function') {
        lerTexto('Resolva a equação: ' + texto);
    }
}

/**
 * Lê as instruções do passo atual
 */
function lerInstrucoes() {
    const titulo = document.querySelector('.passo-titulo')?.textContent;
    const descricao = document.querySelector('.passo-descricao')?.textContent;
    const exemplo = document.querySelector('.passo-exemplo')?.textContent;
    
    let texto = '';
    if (titulo) texto += titulo + '. ';
    if (descricao) texto += descricao + '. ';
    if (exemplo) texto += 'Exemplo: ' + exemplo;
    
    if (texto && typeof lerTexto === 'function') {
        lerTexto(texto);
    }
}

// ============================================================
// 7. EXPORTAÇÃO PARA O ESCOPO GLOBAL
// ============================================================

window.verificarResposta = verificarResposta;
window.limparResposta = limparResposta;
window.mostrarFeedback = mostrarFeedback;
window.mostrarErro = mostrarErro;
window.atualizarTentativas = atualizarTentativas;
window.lerEquacao = lerEquacao;
window.lerInstrucoes = lerInstrucoes;