/**
 * ============================================================
 * speech.js
 * Serviço de síntese de voz (Web Speech API) para o sistema EquaTEA.
 * 
 * FUNCIONALIDADES:
 * - Leitura de textos em voz alta
 * - Suporte a múltiplos idiomas
 * - Controle de velocidade e tom
 * - Feedback visual durante a leitura
 * - Fallback para navegadores sem suporte
 * 
 * @package EquaTEA
 * @subpackage Public/JS
 * @author Equipe EquaTEA
 * @version 1.0
 * ============================================================
 */

// ============================================================
// 1. VERIFICAÇÃO DE SUPORTE
// ============================================================

/**
 * Verifica se o navegador suporta a Web Speech API
 * @returns {boolean} True se suporta
 */
function suportaSpeech() {
    return 'speechSynthesis' in window;
}

// ============================================================
// 2. FUNÇÃO PRINCIPAL DE LEITURA
// ============================================================

/**
 * Lê um texto em voz alta
 * @param {string} texto - Texto a ser lido
 * @param {string} idioma - Código do idioma (padrão: 'pt-BR')
 * @param {number} velocidade - Velocidade da fala (0.1 a 2.0)
 * @param {number} tom - Tom da voz (0.1 a 2.0)
 * @param {Function} callback - Função a ser executada após a leitura
 */
function lerTexto(texto, idioma = 'pt-BR', velocidade = 0.9, tom = 1, callback = null) {
    // ============================================================
    // 1. VERIFICAR SUPORTE
    // ============================================================
    
    if (!suportaSpeech()) {
        console.warn('Web Speech API não suportada neste navegador.');
        if (callback) callback();
        return;
    }
    
    // ============================================================
    // 2. PARAR LEITURA ANTERIOR
    // ============================================================
    
    pararLeitura();
    
    // ============================================================
    // 3. PREPARAR O TEXTO
    // ============================================================
    
    // Limpar texto (remover emojis, caracteres especiais)
    texto = texto.replace(/[^\w\s,.!?;:]/g, ' ').trim();
    texto = texto.replace(/\s+/g, ' ');
    
    if (!texto || texto.length === 0) {
        console.warn('Texto vazio para leitura.');
        if (callback) callback();
        return;
    }
    
    // ============================================================
    // 4. CRIAR A UTTERANCE
    // ============================================================
    
    const utterance = new SpeechSynthesisUtterance(texto);
    utterance.lang = idioma;
    utterance.rate = velocidade;
    utterance.pitch = tom;
    utterance.volume = 1;
    
    // ============================================================
    // 5. CONFIGURAR VOZ (buscar voz em português)
    // ============================================================
    
    const vozes = window.speechSynthesis.getVoices();
    const vozPt = vozes.find(function(v) {
        return v.lang.startsWith('pt') || v.lang === 'pt-BR';
    });
    
    if (vozPt) {
        utterance.voice = vozPt;
    }
    
    // ============================================================
    // 6. EVENTOS DA LEITURA
    // ============================================================
    
    // Início da leitura
    utterance.onstart = function() {
        const btn = document.querySelector('.btn-ouvir, .btn-ouvir-equacao, .ajuda-btn-ouvir');
        if (btn) {
            btn.classList.add('ouvindo');
            btn.innerHTML = '<span aria-hidden="true">🔊</span> Ouvindo...';
            btn.disabled = true;
        }
        anunciarMudanca('Leitura em voz alta iniciada');
    };
    
    // Pausa na leitura
    utterance.onpause = function() {
        console.log('Leitura pausada');
    };
    
    // Retomada da leitura
    utterance.onresume = function() {
        console.log('Leitura retomada');
    };
    
    // Fim da leitura
    utterance.onend = function() {
        const btn = document.querySelector('.btn-ouvir, .btn-ouvir-equacao, .ajuda-btn-ouvir');
        if (btn) {
            btn.classList.remove('ouvindo');
            btn.innerHTML = '<span aria-hidden="true">🔊</span> Ouvir';
            btn.disabled = false;
        }
        anunciarMudanca('Leitura em voz alta finalizada');
        if (callback) callback();
    };
    
    // Erro na leitura
    utterance.onerror = function(event) {
        console.error('Erro na síntese de voz:', event);
        const btn = document.querySelector('.btn-ouvir, .btn-ouvir-equacao, .ajuda-btn-ouvir');
        if (btn) {
            btn.classList.remove('ouvindo');
            btn.innerHTML = '<span aria-hidden="true">🔊</span> Ouvir';
            btn.disabled = false;
        }
        if (callback) callback();
    };
    
    // ============================================================
    // 7. EXECUTAR LEITURA
    // ============================================================
    
    window.speechSynthesis.speak(utterance);
}

// ============================================================
// 3. CONTROLE DE LEITURA
// ============================================================

/**
 * Para a leitura em andamento
 */
function pararLeitura() {
    if (suportaSpeech()) {
        window.speechSynthesis.cancel();
        
        // Restaurar botões
        const botoes = document.querySelectorAll('.btn-ouvir, .btn-ouvir-equacao, .ajuda-btn-ouvir');
        botoes.forEach(function(btn) {
            btn.classList.remove('ouvindo');
            btn.innerHTML = '<span aria-hidden="true">🔊</span> Ouvir';
            btn.disabled = false;
        });
    }
}

/**
 * Pausa a leitura em andamento
 */
function pausarLeitura() {
    if (suportaSpeech() && window.speechSynthesis.speaking) {
        window.speechSynthesis.pause();
    }
}

/**
 * Retoma a leitura pausada
 */
function retomarLeitura() {
    if (suportaSpeech() && window.speechSynthesis.paused) {
        window.speechSynthesis.resume();
    }
}

/**
 * Alterna entre pausar e retomar
 */
function alternarPausa() {
    if (!suportaSpeech()) return;
    
    if (window.speechSynthesis.paused) {
        retomarLeitura();
    } else if (window.speechSynthesis.speaking) {
        pausarLeitura();
    }
}

// ============================================================
// 4. FUNÇÕES ESPECÍFICAS DO SISTEMA
// ============================================================

/**
 * Lê o enunciado de uma equação
 * @param {string} enunciado - Enunciado da equação
 */
function lerEnunciadoEquacao(enunciado) {
    if (!enunciado) return;
    lerTexto('Resolva a equação: ' + enunciado);
}

/**
 * Lê as instruções de um passo
 * @param {string} titulo - Título do passo
 * @param {string} descricao - Descrição do passo
 * @param {string} exemplo - Exemplo do passo
 */
function lerInstrucoesPasso(titulo, descricao, exemplo) {
    let texto = titulo + '. ';
    if (descricao) texto += descricao + '. ';
    if (exemplo) texto += 'Exemplo: ' + exemplo;
    lerTexto(texto);
}

/**
 * Lê a dica de um erro
 * @param {string} dica - Dica a ser lida
 */
function lerDica(dica) {
    if (dica) {
        lerTexto('Dica: ' + dica);
    }
}

// ============================================================
// 5. LISTA DE VOZES DISPONÍVEIS
// ============================================================

/**
 * Obtém a lista de vozes disponíveis no navegador
 * @param {string} idioma - Filtro por idioma (opcional)
 * @returns {Array} Lista de vozes
 */
function obterVozes(idioma = null) {
    if (!suportaSpeech()) return [];
    
    const vozes = window.speechSynthesis.getVoices();
    if (idioma) {
        return vozes.filter(function(v) {
            return v.lang.startsWith(idioma);
        });
    }
    return vozes;
}

/**
 * Obtém a voz em português disponível
 * @param {string} idioma - Código do idioma
 * @returns {SpeechSynthesisVoice|null} Objeto de voz ou null
 */
function obterVozPt(idioma = 'pt-BR') {
    const vozes = obterVozes('pt');
    if (vozes.length > 0) {
        // Tenta encontrar a voz específica do idioma
        const vozEspecifica = vozes.find(function(v) {
            return v.lang === idioma;
        });
        return vozEspecifica || vozes[0];
    }
    return null;
}

// ============================================================
// 6. CARREGAMENTO DE VOZES
// ============================================================

// Carregar vozes quando disponíveis
if (suportaSpeech()) {
    // A primeira chamada pode não ter vozes carregadas
    window.speechSynthesis.getVoices();
    
    // Evento quando as vozes são carregadas
    window.speechSynthesis.onvoiceschanged = function() {
        // As vozes estão disponíveis agora
        console.log('Vozes carregadas:', window.speechSynthesis.getVoices().length);
    };
}

// ============================================================
// 7. EXPORTAÇÃO PARA O ESCOPO GLOBAL
// ============================================================

window.suportaSpeech = suportaSpeech;
window.lerTexto = lerTexto;
window.pararLeitura = pararLeitura;
window.pausarLeitura = pausarLeitura;
window.retomarLeitura = retomarLeitura;
window.alternarPausa = alternarPausa;
window.lerEnunciadoEquacao = lerEnunciadoEquacao;
window.lerInstrucoesPasso = lerInstrucoesPasso;
window.lerDica = lerDica;
window.obterVozes = obterVozes;
window.obterVozPt = obterVozPt;