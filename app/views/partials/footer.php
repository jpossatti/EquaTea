<?php
/**
 * ============================================================
 * footer.php
 * Rodapé padrão do sistema EquaTEA.
 * 
 * FUNCIONALIDADES:
 * - Informações de copyright
 * - Links úteis (Sobre, Ajuda, Contato)
 * - Versão do sistema
 * - Indicador de acessibilidade
 * 
 * @package EquaTEA
 * @subpackage Views/Partials
 * @author Equipe EquaTEA
 * @version 1.0
 * ============================================================
 */

// ============================================================
// VARIÁVEIS DO RODAPÉ
// ============================================================

$ano_atual = date('Y');
$versao_sistema = '1.0.0';

// ============================================================
// INÍCIO DO RODAPÉ
// ============================================================

?>
<footer id="footer-content" class="main-footer" role="contentinfo" aria-label="Rodapé do sistema">
    <div class="container footer-content">
        
        <!-- ============================================================
        COLUNA 1: LOGO E DESCRIÇÃO
        ============================================================ -->
        <div class="footer-column footer-about">
            <div class="footer-logo">
                <span class="footer-logo-equa">Equa</span><span class="footer-logo-tea">TEA</span>
            </div>
            <p class="footer-description">
                Sistema web para ensino de equações de 1º grau 
                para jovens com TEA no ensino médio.
            </p>
            <div class="footer-odss">
                <span class="ods-badge" title="ODS 4 - Educação de Qualidade">
                    <img src="<?php echo BASE_URL; ?>public/images/ods4.png" 
                         alt="ODS 4" 
                         width="32" 
                         height="32"
                         loading="lazy">
                </span>
                <span class="ods-badge" title="ODS 10 - Redução das Desigualdades">
                    <img src="<?php echo BASE_URL; ?>public/images/ods10.png" 
                         alt="ODS 10" 
                         width="32" 
                         height="32"
                         loading="lazy">
                </span>
            </div>
        </div>
        
        <!-- ============================================================
        COLUNA 2: LINKS RÁPIDOS
        ============================================================ -->
        <div class="footer-column footer-links">
            <h3 class="footer-title">Links Rápidos</h3>
            <ul class="footer-list">
                <li><a href="<?php echo BASE_URL; ?>app/views/aluno/dashboard.php">Dashboard</a></li>
                <li><a href="<?php echo BASE_URL; ?>app/views/aluno/exercicio.php">Novo Exercício</a></li>
                <li><a href="<?php echo BASE_URL; ?>app/views/professor/relatorio.php">Relatórios</a></li>
                <li><a href="<?php echo BASE_URL; ?>app/views/auth/login.php">Login</a></li>
            </ul>
        </div>
        
        <!-- ============================================================
        COLUNA 3: AJUDA E SUPORTE
        ============================================================ -->
        <div class="footer-column footer-support">
            <h3 class="footer-title">Ajuda e Suporte</h3>
            <ul class="footer-list">
                <li><a href="#" onclick="mostrarAjuda()">❓ Como usar</a></li>
                <li><a href="#" onclick="mostrarAcessibilidade()">♿ Acessibilidade</a></li>
                <li><a href="#" onclick="mostrarSobre()">📖 Sobre o projeto</a></li>
                <li><a href="mailto:suporte@equatea.com">📧 suporte@equatea.com</a></li>
            </ul>
        </div>
        
        <!-- ============================================================
        COLUNA 4: STATUS E VERSÃO
        ============================================================ -->
        <div class="footer-column footer-status">
            <h3 class="footer-title">Status do Sistema</h3>
            <div class="status-info">
                <span class="status-indicator-dot"></span>
                <span class="status-text">Sistema operacional</span>
            </div>
            <div class="version-info">
                <span class="version-label">Versão:</span>
                <span class="version-number">v<?php echo $versao_sistema; ?></span>
            </div>
            <div class="php-version">
                <span class="version-label">PHP:</span>
                <span class="version-number"><?php echo phpversion(); ?></span>
            </div>
            <div class="footer-copyright">
                &copy; <?php echo $ano_atual; ?> EquaTEA - Todos os direitos reservados.
            </div>
        </div>
    </div>
    
    <!-- ============================================================
    RODAPÉ INFERIOR
    ============================================================ -->
    <div class="footer-bottom">
        <div class="container footer-bottom-content">
            <span class="footer-license">
                Este sistema é um software livre sob a licença <a href="#" onclick="mostrarLicenca()">MIT</a>.
            </span>
            <span class="footer-credits">
                Desenvolvido com ❤️ para a inclusão educacional.
            </span>
        </div>
    </div>
</footer>

<!-- ============================================================
    SCRIPTS DO RODAPÉ
    ============================================================ -->
<script>
    /**
     * ============================================================
     * FUNÇÕES DO RODAPÉ
     * ============================================================
     */
    
    /**
     * Mostra informações de ajuda
     */
    function mostrarAjuda() {
        alert(
            '📖 Como usar o EquaTEA:\n\n' +
            '1. Faça login com seu e-mail e senha\n' +
            '2. Clique em "Novo Exercício" para começar\n' +
            '3. Resolva a equação passo a passo\n' +
            '4. Use o botão "Ajuda" se precisar de exemplos\n' +
            '5. Conclua todos os 4 passos para finalizar\n' +
            '6. Acompanhe seu progresso no Dashboard\n\n' +
            '💡 Dica: Use os botões de acessibilidade no topo!'
        );
    }
    
    /**
     * Mostra informações de acessibilidade
     */
    function mostrarAcessibilidade() {
        alert(
            '♿ Recursos de Acessibilidade:\n\n' +
            '✅ Alto Contraste - Melhora a visualização\n' +
            '✅ Fonte Disléxica - Facilita a leitura\n' +
            '✅ Ajuste de tamanho da fonte (A+ / A-)\n' +
            '✅ Navegação por teclado (Tab, Enter, ESC)\n' +
            '✅ Skip Links para pular navegação\n' +
            '✅ Botão "Ouvir" com leitura em voz alta\n' +
            '✅ Interface sem animações ou distrações\n' +
            '✅ Feedback visual claro e objetivo'
        );
    }
    
    /**
     * Mostra informações sobre o projeto
     */
    function mostrarSobre() {
        alert(
            '📖 Sobre o EquaTEA:\n\n' +
            'O EquaTEA é um sistema web desenvolvido para o\n' +
            'ensino de equações de 1º grau para jovens com TEA.\n\n' +
            '🎯 Objetivo: Tornar o aprendizado de álgebra\n' +
            'acessível, previsível e menos ansioso.\n\n' +
            '📚 Metodologia:\n' +
            '• 4 passos decompostos\n' +
            '• Feedback imediato\n' +
            '• Interface sem distrações\n\n' +
            '🌍 ODS 4 e 10 - Educação Inclusiva e\n' +
            'Redução das Desigualdades'
        );
    }
    
    /**
     * Mostra informações da licença
     */
    function mostrarLicenca() {
        alert(
            '📄 Licença MIT\n\n' +
            'Copyright (c) <?php echo $ano_atual; ?> EquaTEA\n\n' +
            'Permissão é concedida, gratuitamente, a qualquer pessoa\n' +
            'que obtenha uma cópia deste software e arquivos de\n' +
            'documentação associados, para lidar com o software\n' +
            'sem restrição, incluindo direitos de uso, cópia,\n' +
            'modificação, fusão, publicação, distribuição,\n' +
            'sublicenciamento e/ou venda de cópias do software.'
        );
    }
    
    /**
     * Atualiza o indicador de status do sistema
     */
    document.addEventListener('DOMContentLoaded', function() {
        const dot = document.querySelector('.status-indicator-dot');
        const text = document.querySelector('.status-text');
        
        if (dot && text) {
            // Verifica a conexão com o servidor
            fetch('<?php echo BASE_URL; ?>index.php', { 
                method: 'HEAD', 
                cache: 'no-cache' 
            })
            .then(function(response) {
                if (response.ok) {
                    dot.className = 'status-indicator-dot online';
                    text.textContent = '✓ Sistema online';
                } else {
                    dot.className = 'status-indicator-dot warning';
                    text.textContent = '⚠️ Sistema com problemas';
                }
            })
            .catch(function() {
                dot.className = 'status-indicator-dot offline';
                text.textContent = '✗ Sistema offline';
            });
        }
    });
</script>

<!-- ============================================================
    CSS DO RODAPÉ
    ============================================================ -->
<style>
    /* ============================================================
     * ESTILOS DO RODAPÉ
     * ============================================================ */
    
    .main-footer {
        background-color: #2c3e50;
        color: #ecf0f1;
        padding: 40px 0 0 0;
        margin-top: 40px;
        border-top: 4px solid #3498db;
    }
    
    .footer-content {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1.2fr;
        gap: 30px;
        padding-bottom: 30px;
        border-bottom: 1px solid #445566;
    }
    
    .footer-column {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .footer-logo {
        font-size: 24px;
        font-weight: 700;
    }
    
    .footer-logo-equa {
        color: #ecf0f1;
    }
    
    .footer-logo-tea {
        color: #e74c3c;
    }
    
    .footer-description {
        font-size: 14px;
        color: #bdc3c7;
        line-height: 1.6;
        margin: 0;
    }
    
    .footer-odss {
        display: flex;
        gap: 8px;
        margin-top: 4px;
    }
    
    .ods-badge {
        display: inline-block;
        border-radius: 4px;
        overflow: hidden;
        opacity: 0.8;
        transition: opacity 0.2s;
    }
    
    .ods-badge:hover {
        opacity: 1;
    }
    
    .footer-title {
        font-size: 16px;
        font-weight: 600;
        color: #ecf0f1;
        margin: 0 0 4px 0;
    }
    
    .footer-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    
    .footer-list li {
        margin: 0;
    }
    
    .footer-list a {
        color: #bdc3c7;
        text-decoration: none;
        font-size: 14px;
        transition: color 0.2s;
        padding: 4px 0;
        display: inline-block;
    }
    
    .footer-list a:hover {
        color: #3498db;
        text-decoration: underline;
    }
    
    .footer-list a:focus {
        outline: 3px solid #3498db;
        outline-offset: 2px;
    }
    
    .status-info {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #bdc3c7;
    }
    
    .status-indicator-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
    }
    
    .status-indicator-dot.online {
        background-color: #2ecc71;
        box-shadow: 0 0 8px rgba(46, 204, 113, 0.4);
    }
    
    .status-indicator-dot.warning {
        background-color: #f39c12;
        box-shadow: 0 0 8px rgba(243, 156, 18, 0.4);
    }
    
    .status-indicator-dot.offline {
        background-color: #e74c3c;
        box-shadow: 0 0 8px rgba(231, 76, 60, 0.4);
    }
    
    .version-info,
    .php-version {
        font-size: 14px;
        color: #bdc3c7;
    }
    
    .version-label {
        color: #95a5a6;
    }
    
    .version-number {
        color: #ecf0f1;
        font-weight: 500;
    }
    
    .footer-copyright {
        font-size: 13px;
        color: #95a5a6;
        margin-top: 8px;
    }
    
    .footer-bottom {
        background-color: #1a252f;
        padding: 12px 0;
    }
    
    .footer-bottom-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        font-size: 13px;
        color: #95a5a6;
    }
    
    .footer-license a {
        color: #3498db;
        text-decoration: none;
    }
    
    .footer-license a:hover {
        text-decoration: underline;
    }
    
    /* ============================================================
     * RESPONSIVIDADE DO RODAPÉ
     * ============================================================ */
    
    @media (max-width: 992px) {
        .footer-content {
            grid-template-columns: 1fr 1fr;
        }
    }
    
    @media (max-width: 576px) {
        .footer-content {
            grid-template-columns: 1fr;
            gap: 24px;
        }
        
        .footer-bottom-content {
            flex-direction: column;
            text-align: center;
        }
    }
    
    /* ============================================================
     * ALTO CONTRASTE
     * ============================================================ */
    .alto-contraste .main-footer {
        background-color: #000000;
        border-top-color: #ffff00;
    }
    
    .alto-contraste .footer-bottom {
        background-color: #000000;
        border-top: 1px solid #ffffff;
    }
    
    .alto-contraste .footer-logo-equa {
        color: #ffffff;
    }
    
    .alto-contraste .footer-title {
        color: #ffffff;
    }
    
    .alto-contraste .footer-list a {
        color: #ffff00;
    }
    
    .alto-contraste .footer-list a:hover {
        color: #ffffff;
    }
    
    .alto-contraste .footer-description,
    .alto-contraste .status-info,
    .alto-contraste .version-info,
    .alto-contraste .php-version,
    .alto-contraste .footer-copyright {
        color: #dddddd;
    }
</style>