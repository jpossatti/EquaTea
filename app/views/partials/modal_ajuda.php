<?php
/**
 * ============================================================
 * modal_ajuda.php
 * Modal de ajuda reutilizável para exibir exemplos e dicas.
 * 
 * FUNCIONALIDADES:
 * - Exibe um exemplo análogo para o passo atual
 * - Mostra dicas específicas para o passo
 * - Interface acessível com foco controlado
 * - Pode ser reutilizado em diferentes páginas
 * 
 * @package EquaTEA
 * @subpackage Views/Partials
 * @author Equipe EquaTEA
 * @version 1.0
 * ============================================================
 */

// ============================================================
// VARIÁVEIS DO MODAL
// ============================================================

// Estas variáveis devem ser definidas na página que inclui este partial
$passo_atual = isset($passo_atual) ? $passo_atual : 1;
$exemplo_ajuda = isset($exemplo_ajuda) ? $exemplo_ajuda : null;
$dica_passo = isset($dica_passo) ? $dica_passo : 'Siga os passos com atenção.';

// ============================================================
// INÍCIO DO MODAL
// ============================================================

?>
<!-- ============================================================
    MODAL DE AJUDA
    ============================================================ -->
<div id="modal-ajuda" class="modal-ajuda" role="dialog" 
     aria-modal="true" 
     aria-labelledby="modal-ajuda-title"
     aria-describedby="modal-ajuda-desc"
     style="display: none;">
    
    <div class="modal-ajuda-overlay" onclick="fecharModalAjuda()"></div>
    
    <div class="modal-ajuda-content">
        
        <!-- ============================================================
        CABEÇALHO DO MODAL
        ============================================================ -->
        <div class="modal-ajuda-header">
            <h2 id="modal-ajuda-title" class="modal-ajuda-title">
                <span aria-hidden="true">❓</span> 
                Ajuda - Passo <?php echo $passo_atual; ?>
            </h2>
            <button class="modal-ajuda-close" onclick="fecharModalAjuda()" 
                    aria-label="Fechar ajuda">
                <span aria-hidden="true">✕</span>
            </button>
        </div>
        
        <!-- ============================================================
        CORPO DO MODAL
        ============================================================ -->
        <div class="modal-ajuda-body" id="modal-ajuda-desc">
            
            <!-- ============================================================
            DICA DO PASSO
            ============================================================ -->
            <div class="ajuda-dica-passo">
                <h3 class="ajuda-subtitulo">📌 Dica para este passo:</h3>
                <p class="ajuda-dica-texto"><?php echo htmlspecialchars($dica_passo); ?></p>
            </div>
            
            <!-- ============================================================
            EXEMPLO ANÁLOGO
            ============================================================ -->
            <div class="ajuda-exemplo">
                <h3 class="ajuda-subtitulo">📝 Exemplo análogo:</h3>
                
                <?php if ($exemplo_ajuda): ?>
                    <div class="exemplo-card">
                        <div class="exemplo-equacao">
                            <?php 
                            $sinal_ex = $exemplo_ajuda['b'] >= 0 ? '+' : '-';
                            $equacao_ex = "{$exemplo_ajuda['a']}x {$sinal_ex} " . abs($exemplo_ajuda['b']) . " = {$exemplo_ajuda['c']}";
                            ?>
                            <span class="exemplo-equacao-texto"><?php echo $equacao_ex; ?></span>
                            <span class="exemplo-solucao-badge">x = <?php echo $exemplo_ajuda['solucao']; ?></span>
                        </div>
                        
                        <div class="exemplo-descricao">
                            <span class="exemplo-descricao-texto">
                                <?php echo htmlspecialchars($exemplo_ajuda['descricao']); ?>
                            </span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="exemplo-indisponivel">
                        <span aria-hidden="true">ℹ️</span>
                        <p>Não há exemplos disponíveis para este passo.</p>
                        <p class="exemplo-hint">Tente resolver com base na dica acima.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- ============================================================
            PASSOS ADICIONAIS (se houver)
            ============================================================ -->
            <div class="ajuda-passos">
                <h3 class="ajuda-subtitulo">📖 Lembre-se dos passos:</h3>
                <ol class="ajuda-passos-lista">
                    <li><strong>Passo 1:</strong> Identificar os termos com x e sem x</li>
                    <li><strong>Passo 2:</strong> Isolar o termo com x (operação inversa)</li>
                    <li><strong>Passo 3:</strong> Calcular o lado direito</li>
                    <li><strong>Passo 4:</strong> Isolar x (dividir pelo coeficiente)</li>
                </ol>
            </div>
            
            <!-- ============================================================
            BOTÃO DE ÁUDIO
            ============================================================ -->
            <button class="ajuda-btn-ouvir" onclick="lerAjuda()" aria-label="Ouvir ajuda em voz alta">
                <span aria-hidden="true">🔊</span> 
                Ouvir ajuda em voz alta
            </button>
        </div>
        
        <!-- ============================================================
        RODAPÉ DO MODAL
        ============================================================ -->
        <div class="modal-ajuda-footer">
            <button class="modal-ajuda-btn-fechar" onclick="fecharModalAjuda()">
                <span aria-hidden="true">👍</span> 
                Entendi!
            </button>
        </div>
    </div>
</div>

<!-- ============================================================
    SCRIPTS DO MODAL DE AJUDA
    ============================================================ -->
<script>
    /**
     * ============================================================
     * FUNÇÕES DO MODAL DE AJUDA
     * ============================================================
     */
    
    /**
     * Abre o modal de ajuda
     */
    function abrirModalAjuda() {
        const modal = document.getElementById('modal-ajuda');
        if (modal) {
            modal.style.display = 'block';
            modal.setAttribute('aria-hidden', 'false');
            
            // Armazena o elemento que tinha foco para retornar depois
            modal._focoAnterior = document.activeElement;
            
            // Foco no primeiro elemento interativo do modal
            const primeiroElemento = modal.querySelector('button, a, input, select, textarea');
            if (primeiroElemento) {
                setTimeout(function() {
                    primeiroElemento.focus();
                }, 100);
            }
            
            // Desabilita scroll do body
            document.body.style.overflow = 'hidden';
        }
    }
    
    /**
     * Fecha o modal de ajuda
     */
    function fecharModalAjuda() {
        const modal = document.getElementById('modal-ajuda');
        if (modal) {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            
            // Restaura o scroll do body
            document.body.style.overflow = '';
            
            // Retorna o foco para o elemento anterior
            if (modal._focoAnterior) {
                modal._focoAnterior.focus();
            }
        }
    }
    
    /**
     * Fecha o modal com a tecla ESC
     */
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('modal-ajuda');
            if (modal && modal.style.display === 'block') {
                fecharModalAjuda();
            }
        }
    });
    
    /**
     * Fecha o modal clicando fora do conteúdo
     */
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('modal-ajuda');
        if (modal && modal.style.display === 'block') {
            if (e.target.closest('.modal-ajuda-content') === null && 
                e.target.closest('.modal-ajuda-overlay') === null) {
                // Não faz nada - apenas clicar no overlay fecha
            }
        }
    });
    
    /**
     * Lê o conteúdo da ajuda em voz alta
     */
    function lerAjuda() {
        const modal = document.getElementById('modal-ajuda');
        if (!modal) return;
        
        // Extrai o texto do modal
        const dicaTexto = modal.querySelector('.ajuda-dica-texto')?.textContent || '';
        const exemploTexto = modal.querySelector('.exemplo-descricao-texto')?.textContent || '';
        const equacaoTexto = modal.querySelector('.exemplo-equacao-texto')?.textContent || '';
        
        let texto = 'Dica: ' + dicaTexto + '. ';
        if (exemploTexto) {
            texto += 'Exemplo: ' + exemploTexto + '. ';
        }
        if (equacaoTexto) {
            texto += 'Equação do exemplo: ' + equacaoTexto + '. ';
        }
        texto += 'Lembre-se dos passos: identificar termos, isolar x, calcular e dividir.';
        
        // Verifica se a Web Speech API está disponível
        if ('speechSynthesis' in window) {
            const utterance = new SpeechSynthesisUtterance(texto);
            utterance.lang = 'pt-BR';
            utterance.rate = 0.9;
            utterance.pitch = 1;
            window.speechSynthesis.speak(utterance);
        } else {
            alert('Seu navegador não suporta leitura em voz alta.');
        }
    }
    
    /**
     * Inicializa o modal quando a página é carregada
     */
    document.addEventListener('DOMContentLoaded', function() {
        // Adiciona evento para fechar modal com ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('modal-ajuda');
                if (modal && modal.style.display === 'block') {
                    fecharModalAjuda();
                }
            }
        });
        
        // Adiciona evento para fechar modal clicando no overlay
        const overlay = document.querySelector('.modal-ajuda-overlay');
        if (overlay) {
            overlay.addEventListener('click', function() {
                fecharModalAjuda();
            });
        }
    });
</script>

<!-- ============================================================
    CSS DO MODAL DE AJUDA
    ============================================================ -->
<style>
    /* ============================================================
     * ESTILOS DO MODAL DE AJUDA
     * ============================================================ */
    
    .modal-ajuda {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
        display: none;
        animation: modalFadeIn 0.3s ease;
    }
    
    .modal-ajuda-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
    }
    
    .modal-ajuda-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #ffffff;
        border-radius: 16px;
        max-width: 600px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: modalContentSlide 0.3s ease;
    }
    
    @keyframes modalFadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes modalContentSlide {
        from {
            opacity: 0;
            transform: translate(-50%, -50%) scale(0.95) translateY(20px);
        }
        to {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1) translateY(0);
        }
    }
    
    .modal-ajuda-header {
        padding: 20px 24px;
        border-bottom: 2px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8f9fa;
        border-radius: 16px 16px 0 0;
    }
    
    .modal-ajuda-title {
        margin: 0;
        font-size: 22px;
        color: #2c3e50;
        font-weight: 600;
    }
    
    .modal-ajuda-close {
        background: none;
        border: none;
        font-size: 28px;
        cursor: pointer;
        color: #999;
        padding: 4px 12px;
        border-radius: 6px;
        transition: all 0.2s;
        line-height: 1;
    }
    
    .modal-ajuda-close:hover {
        color: #333;
        background: #e9ecef;
    }
    
    .modal-ajuda-close:focus {
        outline: 3px solid #3498db;
        outline-offset: 2px;
    }
    
    .modal-ajuda-body {
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .ajuda-subtitulo {
        font-size: 17px;
        font-weight: 600;
        color: #2c3e50;
        margin: 0 0 8px 0;
    }
    
    .ajuda-dica-passo {
        background: #e8f4fd;
        border-left: 4px solid #3498db;
        padding: 14px 18px;
        border-radius: 4px;
    }
    
    .ajuda-dica-texto {
        font-size: 16px;
        color: #2c3e50;
        margin: 0;
        line-height: 1.6;
    }
    
    .exemplo-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 16px 20px;
        border: 1px solid #e9ecef;
    }
    
    .exemplo-equacao {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 8px;
    }
    
    .exemplo-equacao-texto {
        font-size: 22px;
        font-weight: 700;
        color: #2c3e50;
    }
    
    .exemplo-solucao-badge {
        background: #27ae60;
        color: white;
        padding: 4px 14px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
    }
    
    .exemplo-descricao-texto {
        font-size: 15px;
        color: #555;
        line-height: 1.5;
    }
    
    .exemplo-indisponivel {
        background: #fff3cd;
        border-radius: 8px;
        padding: 16px 20px;
        text-align: center;
    }
    
    .exemplo-indisponivel span {
        font-size: 32px;
        display: block;
        margin-bottom: 4px;
    }
    
    .exemplo-indisponivel p {
        margin: 0;
        color: #856404;
        font-size: 15px;
    }
    
    .exemplo-hint {
        font-size: 13px !important;
        color: #999 !important;
        margin-top: 4px !important;
    }
    
    .ajuda-passos {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 16px 20px;
    }
    
    .ajuda-passos-lista {
        margin: 8px 0 0 0;
        padding-left: 20px;
        font-size: 15px;
        color: #333;
        line-height: 1.8;
    }
    
    .ajuda-btn-ouvir {
        padding: 10px 20px;
        background: #3498db;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        align-self: center;
    }
    
    .ajuda-btn-ouvir:hover {
        background: #2980b9;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
    }
    
    .ajuda-btn-ouvir:focus {
        outline: 3px solid #3498db;
        outline-offset: 2px;
    }
    
    .modal-ajuda-footer {
        padding: 16px 24px;
        border-top: 2px solid #e9ecef;
        text-align: right;
        background: #f8f9fa;
        border-radius: 0 0 16px 16px;
    }
    
    .modal-ajuda-btn-fechar {
        padding: 10px 28px;
        background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .modal-ajuda-btn-fechar:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(46, 204, 113, 0.3);
    }
    
    .modal-ajuda-btn-fechar:focus {
        outline: 3px solid #3498db;
        outline-offset: 2px;
    }
    
    /* ============================================================
     * SCROLLBAR PERSONALIZADA
     * ============================================================ */
    .modal-ajuda-content::-webkit-scrollbar {
        width: 8px;
    }
    
    .modal-ajuda-content::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .modal-ajuda-content::-webkit-scrollbar-thumb {
        background: #c1c7cd;
        border-radius: 4px;
    }
    
    .modal-ajuda-content::-webkit-scrollbar-thumb:hover {
        background: #a0a7ad;
    }
    
    /* ============================================================
     * ALTO CONTRASTE
     * ============================================================ */
    .alto-contraste .modal-ajuda-content {
        background: #000000;
        border: 2px solid #ffffff;
    }
    
    .alto-contraste .modal-ajuda-header {
        background: #222222;
        border-bottom-color: #ffffff;
    }
    
    .alto-contraste .modal-ajuda-title {
        color: #ffffff;
    }
    
    .alto-contraste .ajuda-subtitulo {
        color: #ffffff;
    }
    
    .alto-contraste .ajuda-dica-passo {
        background: #222222;
        border-left-color: #ffff00;
    }
    
    .alto-contraste .ajuda-dica-texto {
        color: #ffffff;
    }
    
    .alto-contraste .exemplo-card {
        background: #222222;
        border-color: #666666;
    }
    
    .alto-contraste .exemplo-equacao-texto {
        color: #ffffff;
    }
    
    .alto-contraste .exemplo-descricao-texto {
        color: #dddddd;
    }
    
    .alto-contraste .ajuda-passos {
        background: #222222;
    }
    
    .alto-contraste .ajuda-passos-lista {
        color: #ffffff;
    }
    
    .alto-contraste .modal-ajuda-footer {
        background: #222222;
        border-top-color: #ffffff;
    }
    
    /* ============================================================
     * RESPONSIVIDADE
     * ============================================================ */
    @media (max-width: 576px) {
        .modal-ajuda-content {
            width: 95%;
            max-height: 95vh;
        }
        
        .modal-ajuda-title {
            font-size: 18px;
        }
        
        .exemplo-equacao-texto {
            font-size: 18px;
        }
        
        .ajuda-dica-texto {
            font-size: 14px;
        }
        
        .ajuda-passos-lista {
            font-size: 14px;
        }
        
        .modal-ajuda-header {
            padding: 14px 16px;
        }
        
        .modal-ajuda-body {
            padding: 16px;
        }
        
        .modal-ajuda-footer {
            padding: 12px 16px;
        }
    }
</style>