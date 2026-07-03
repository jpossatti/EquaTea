<?php
/**
 * ============================================================
 * parabens.php
 * Página exibida quando o aluno conclui uma equação com sucesso.
 * 
 * FUNCIONALIDADES:
 * - Exibe mensagem de parabéns personalizada
 * - Mostra estatísticas da equação concluída
 * - Botões para próximo exercício ou voltar ao dashboard
 * - Efeito visual de comemoração
 * - Compartilhamento do resultado (opcional)
 * 
 * @package EquaTEA
 * @subpackage Views/Aluno
 * @author Equipe EquaTEA
 * @version 1.0
 * ============================================================
 */

// ============================================================
// 1. INICIALIZAÇÃO E VERIFICAÇÃO DE SESSÃO
// ============================================================

session_start();
require_once '../../helpers/session_helper.php';

// Verifica se o usuário está logado e é um aluno
verificarSessao();
if (!isAluno()) {
    header('Location: ../auth/login.php');
    exit;
}

// ============================================================
// 2. CARREGAMENTO DOS MODELOS
// ============================================================

require_once '../../models/Equacao.php';
require_once '../../models/Progresso.php';

// ============================================================
// 3. OBTENÇÃO DOS DADOS
// ============================================================

$aluno_id = $_SESSION['aluno_id'];
$equacao_id = isset($_GET['equacao_id']) ? (int)$_GET['equacao_id'] : 0;

// Se não veio equacao_id, tenta buscar a última concluída
if (!$equacao_id) {
    $progresso = new Progresso();
    $ultimos = $progresso->getByAluno($aluno_id);
    foreach ($ultimos as $p) {
        if ($p['concluida']) {
            $equacao_id = $p['equacao_id'];
            break;
        }
    }
}

if (!$equacao_id) {
    header('Location: dashboard.php');
    exit;
}

// Buscar dados da equação
$equacao = new Equacao();
$dados_equacao = $equacao->getById($equacao_id);

if (!$dados_equacao) {
    header('Location: dashboard.php');
    exit;
}

// Buscar progresso
$progresso = new Progresso();
$dados_progresso = $progresso->getByAlunoEquacao($aluno_id, $equacao_id);

if (!$dados_progresso || !$dados_progresso['concluida']) {
    header('Location: dashboard.php');
    exit;
}

// ============================================================
// 4. VARIÁVEIS PARA A VIEW
// ============================================================

$page_title = 'Parabéns! - EquaTEA';
$nome_aluno = $_SESSION['usuario_nome'] ?? 'Aluno';

$sinal = $dados_equacao['b'] >= 0 ? '+' : '-';
$equacao_str = "{$dados_equacao['a']}x {$sinal} " . abs($dados_equacao['b']) . " = {$dados_equacao['c']}";
$solucao = $dados_equacao['solucao'];

// Calcular tempo total (se disponível)
$tempo_total = null;
if ($dados_progresso['data_inicio'] && $dados_progresso['data_conclusao']) {
    $inicio = new DateTime($dados_progresso['data_inicio']);
    $conclusao = new DateTime($dados_progresso['data_conclusao']);
    $diff = $inicio->diff($conclusao);
    $tempo_total = $diff->i . 'min ' . $diff->s . 's';
}

// Mensagens motivacionais aleatórias
$mensagens_motivacionais = [
    'Você é incrível! Continue assim! 🌟',
    'Parabéns pelo esforço! Cada passo te leva mais longe! 🚀',
    'Excelente trabalho! Você está aprendendo muito! 📚',
    'Que conquista! Continue praticando! 💪',
    'Você é um campeão! Nunca desista! 🏆',
    'Muito bem! Você está cada vez melhor! ✨',
    'Parabéns pela dedicação! O conhecimento é o maior tesouro! 🎯',
    'Você conseguiu! Acredite sempre no seu potencial! 🌈'
];
$mensagem_motivacional = $mensagens_motivacionais[array_rand($mensagens_motivacionais)];

// ============================================================
// 5. HEADER DA PÁGINA
// ============================================================

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <meta name="description" content="Parabéns! Você concluiu uma equação no EquaTEA">
    <meta name="theme-color" content="#2c3e50">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/parabens.css">
    <link rel="stylesheet" href="../../public/css/acessibilidade.css">
    
    <!-- Favicon -->
    <link rel="icon" href="../../public/images/favicon.ico" type="image/x-icon">
    
    <!-- Fontes acessíveis -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Dyslexic&display=swap" rel="stylesheet">
</head>
<body>
    <!-- ============================================================
    SKIP LINKS
    ============================================================ -->
    <div class="skip-links" role="navigation" aria-label="Pular navegação">
        <a href="#main-content" class="skip-link">Pular para o conteúdo principal</a>
        <a href="#btn-proximo" class="skip-link">Pular para próximo exercício</a>
    </div>

    <!-- ============================================================
    HEADER E MENU
    ============================================================ -->
    <?php include '../partials/header.php'; ?>
    <?php include '../partials/menu_aluno.php'; ?>

    <!-- ============================================================
    CONTEÚDO PRINCIPAL
    ============================================================ -->
    <main id="main-content" class="container parabens-container" role="main">
        
        <!-- ============================================================
        CONFETES E EFEITO VISUAL (apenas decorativo, sem animação para TEA)
        ============================================================ -->
        <div class="confetes-bg" aria-hidden="true">
            <span>🎉</span><span>⭐</span><span>🌟</span><span>✨</span>
            <span>🎊</span><span>🏆</span><span>💪</span><span>🌈</span>
            <span>🎉</span><span>⭐</span><span>🌟</span><span>✨</span>
        </div>

        <!-- ============================================================
        CARD PRINCIPAL
        ============================================================ -->
        <div class="parabens-card">
            
            <!-- ============================================================
            ÍCONE E TÍTULO
            ============================================================ -->
            <div class="parabens-header">
                <div class="parabens-icon" aria-hidden="true">🎉</div>
                <h1 class="parabens-title">Parabéns, <?php echo htmlspecialchars($nome_aluno); ?>!</h1>
                <p class="parabens-subtitle"><?php echo $mensagem_motivacional; ?></p>
            </div>

            <!-- ============================================================
            DETALHES DA EQUAÇÃO CONCLUÍDA
            ============================================================ -->
            <div class="parabens-detalhes">
                <div class="detalhe-item">
                    <span class="detalhe-label" aria-hidden="true">📝</span>
                    <div class="detalhe-content">
                        <span class="detalhe-titulo">Equação</span>
                        <span class="detalhe-valor equacao-destaque"><?php echo htmlspecialchars($equacao_str); ?></span>
                    </div>
                </div>
                
                <div class="detalhe-item">
                    <span class="detalhe-label" aria-hidden="true">🎯</span>
                    <div class="detalhe-content">
                        <span class="detalhe-titulo">Solução</span>
                        <span class="detalhe-valor solucao-destaque">x = <?php echo $solucao; ?></span>
                    </div>
                </div>
                
                <div class="detalhe-item">
                    <span class="detalhe-label" aria-hidden="true">📊</span>
                    <div class="detalhe-content">
                        <span class="detalhe-titulo">Dificuldade</span>
                        <span class="detalhe-valor dificuldade-badge <?php echo $dados_equacao['dificuldade']; ?>">
                            <?php echo ucfirst($dados_equacao['dificuldade']); ?>
                        </span>
                    </div>
                </div>
                
                <?php if ($tempo_total): ?>
                    <div class="detalhe-item">
                        <span class="detalhe-label" aria-hidden="true">⏱️</span>
                        <div class="detalhe-content">
                            <span class="detalhe-titulo">Tempo total</span>
                            <span class="detalhe-valor"><?php echo $tempo_total; ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="detalhe-item">
                    <span class="detalhe-label" aria-hidden="true">🔄</span>
                    <div class="detalhe-content">
                        <span class="detalhe-titulo">Tentativas</span>
                        <span class="detalhe-valor"><?php echo $dados_progresso['tentativas'] ?? 0; ?></span>
                    </div>
                </div>
            </div>

            <!-- ============================================================
            ESTATÍSTICAS ADICIONAIS (progresso geral)
            ============================================================ -->
            <div class="parabens-progresso">
                <div class="progresso-info">
                    <span aria-hidden="true">📈</span>
                    <span>Seu progresso</span>
                </div>
                <div class="progresso-barras">
                    <?php
                    // Buscar estatísticas gerais do aluno
                    $aluno = new Aluno();
                    $estatisticas = $aluno->getEstatisticas($aluno_id);
                    $total = $estatisticas['equacoes_tentadas'] ?? 0;
                    $concluidas = $estatisticas['equacoes_concluidas'] ?? 0;
                    $percentual = $total > 0 ? round(($concluidas / $total) * 100, 0) : 0;
                    ?>
                    <div class="barra-item">
                        <span class="barra-label">Taxa de conclusão</span>
                        <div class="barra-container">
                            <div class="barra-preenchimento" style="width: <?php echo $percentual; ?>%; background-color: #28a745;"></div>
                            <span class="barra-texto"><?php echo $percentual; ?>%</span>
                        </div>
                    </div>
                    <div class="barra-item">
                        <span class="barra-label">Equações concluídas</span>
                        <div class="barra-container">
                            <div class="barra-preenchimento" style="width: <?php echo min($concluidas * 2, 100); ?>%; background-color: #3498db;"></div>
                            <span class="barra-texto"><?php echo $concluidas; ?> / <?php echo $total; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================
            BOTÕES DE AÇÃO
            ============================================================ -->
            <div class="parabens-acoes">
                <a href="exercicio.php" id="btn-proximo" class="btn-proximo-exercicio">
                    <span aria-hidden="true">🚀</span> 
                    Próximo Exercício
                    <small>Continue praticando!</small>
                </a>
                <a href="dashboard.php" class="btn-voltar-dashboard">
                    <span aria-hidden="true">📊</span> 
                    Ver Dashboard
                    <small>Veja seu progresso completo</small>
                </a>
            </div>

            <!-- ============================================================
            COMPARTILHAR (opcional)
            ============================================================ -->
            <div class="parabens-compartilhar">
                <span class="compartilhar-label">Compartilhe sua conquista:</span>
                <div class="compartilhar-botoes">
                    <button onclick="compartilhar('whatsapp')" class="btn-compartilhar btn-whatsapp" aria-label="Compartilhar no WhatsApp">
                        <span aria-hidden="true">💬</span> WhatsApp
                    </button>
                    <button onclick="copiarResultado()" class="btn-compartilhar btn-copiar" aria-label="Copiar resultado">
                        <span aria-hidden="true">📋</span> Copiar
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- ============================================================
    FOOTER
    ============================================================ -->
    <?php include '../partials/footer.php'; ?>

    <!-- ============================================================
    SCRIPTS JAVASCRIPT
    ============================================================ -->
    <script src="../../public/js/main.js"></script>
    <script src="../../public/js/parabens.js"></script>
    
    <script>
        // ============================================================
        // INICIALIZAÇÃO
        // ============================================================
        
        document.addEventListener('DOMContentLoaded', function() {
            // ============================================================
            // 1. RESTAURAR PREFERÊNCIAS DO USUÁRIO
            // ============================================================
            
            if (localStorage.getItem('alto_contraste') === 'true') {
                document.body.classList.add('alto-contraste');
            }
            
            if (localStorage.getItem('fonte_dyslexic') === 'true') {
                document.body.classList.add('fonte-dyslexic');
            }
            
            const tamanhoFonte = localStorage.getItem('tamanho_fonte');
            if (tamanhoFonte) {
                document.body.style.fontSize = tamanhoFonte + 'px';
            }
            
            // ============================================================
            // 2. ANIMAÇÃO DE ENTRADA (suave)
            // ============================================================
            
            const card = document.querySelector('.parabens-card');
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(function() {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 300);
            }
        });

        // ============================================================
        // FUNÇÕES DE COMPARTILHAMENTO
        // ============================================================

        /**
         * Compartilha o resultado em redes sociais
         */
        function compartilhar(rede) {
            const texto = '🎉 Acabei de resolver uma equação no EquaTEA!\n\n' +
                          'Equação: <?php echo addslashes($equacao_str); ?>\n' +
                          'Solução: x = <?php echo $solucao; ?>\n' +
                          'Dificuldade: <?php echo ucfirst($dados_equacao['dificuldade']); ?>\n\n' +
                          '📚 Aprendendo matemática de forma acessível!';
            
            const url = encodeURIComponent('<?php echo BASE_URL; ?>');
            
            switch(rede) {
                case 'whatsapp':
                    window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(texto), '_blank');
                    break;
                default:
                    copiarResultado();
            }
        }

        /**
         * Copia o resultado para a área de transferência
         */
        function copiarResultado() {
            const texto = '🎉 Equação concluída no EquaTEA!\n' +
                          'Equação: <?php echo htmlspecialchars($equacao_str); ?>\n' +
                          'Solução: x = <?php echo $solucao; ?>\n' +
                          'Dificuldade: <?php echo ucfirst($dados_equacao['dificuldade']); ?>\n' +
                          'Tentativas: <?php echo $dados_progresso['tentativas'] ?? 0; ?>\n\n' +
                          '🚀 Continue praticando!';
            
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(texto).then(function() {
                    mostrarFeedback('✅ Resultado copiado para a área de transferência!');
                }).catch(function() {
                    copiarTextoFallback(texto);
                });
            } else {
                copiarTextoFallback(texto);
            }
        }

        /**
         * Fallback para copiar texto (método antigo)
         */
        function copiarTextoFallback(texto) {
            const textarea = document.createElement('textarea');
            textarea.value = texto;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            textarea.style.left = '-9999px';
            document.body.appendChild(textarea);
            textarea.select();
            
            try {
                document.execCommand('copy');
                mostrarFeedback('✅ Resultado copiado para a área de transferência!');
            } catch (err) {
                mostrarFeedback('❌ Não foi possível copiar. Tente manualmente.');
            }
            
            document.body.removeChild(textarea);
        }

        /**
         * Mostra feedback para o usuário
         */
        function mostrarFeedback(mensagem) {
            // Criar elemento de feedback temporário
            const feedback = document.createElement('div');
            feedback.className = 'feedback-flutuante';
            feedback.textContent = mensagem;
            feedback.style.position = 'fixed';
            feedback.style.bottom = '20px';
            feedback.style.left = '50%';
            feedback.style.transform = 'translateX(-50%)';
            feedback.style.padding = '16px 24px';
            feedback.style.backgroundColor = '#28a745';
            feedback.style.color = '#fff';
            feedback.style.borderRadius = '8px';
            feedback.style.fontSize = '18px';
            feedback.style.zIndex = '9999';
            feedback.style.boxShadow = '0 4px 12px rgba(0,0,0,0.2)';
            feedback.style.animation = 'fadeInUp 0.3s ease';
            
            document.body.appendChild(feedback);
            
            setTimeout(function() {
                feedback.style.opacity = '0';
                feedback.style.transition = 'opacity 0.5s ease';
                setTimeout(function() {
                    document.body.removeChild(feedback);
                }, 500);
            }, 3000);
        }
    </script>
    
    <style>
        /* ============================================================
        ESTILOS PARA O FEEDBACK FLUTUANTE
        ============================================================ */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }
        
        .feedback-flutuante {
            animation: fadeInUp 0.3s ease;
        }
    </style>
</body>
</html>