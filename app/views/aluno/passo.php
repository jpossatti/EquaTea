<?php
/**
 * ============================================================
 * passo.php
 * Página que exibe cada passo da resolução da equação.
 * 
 * FUNCIONALIDADES:
 * - Exibe um passo por vez (1 a 4)
 * - Valida a resposta do aluno para cada passo
 * - Feedback imediato (acerto/erro)
 * - Botão de ajuda com exemplo análogo
 * - Botão de áudio para ouvir o enunciado
 * - Barra de progresso dos passos
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
// 2. CARREGAMENTO DOS MODELOS E CONTROLLERS
// ============================================================

require_once '../../models/Equacao.php';
require_once '../../models/Progresso.php';
require_once '../../models/RegistroErro.php';
require_once '../../models/AjudaExemplo.php';
require_once '../../controllers/ExercicioController.php';

// ============================================================
// 3. OBTENÇÃO DOS DADOS
// ============================================================

$aluno_id = $_SESSION['aluno_id'];
$equacao_id = isset($_GET['equacao_id']) ? (int)$_GET['equacao_id'] : 0;

if (!$equacao_id) {
    header('Location: dashboard.php');
    exit;
}

$controller = new ExercicioController();
$exercicio = $controller->getExercicio($aluno_id, $equacao_id);

if (!$exercicio || $exercicio['concluida']) {
    header('Location: dashboard.php');
    exit;
}

$equacao = $exercicio['equacao'];
$passo_atual = $exercicio['passo_atual'];
$enunciado = $exercicio['enunciado'];
$dica_passo = $exercicio['dica_passo'];

// ============================================================
// 4. DEFINIÇÃO DOS PASSOS
// ============================================================

$passos_info = [
    1 => [
        'titulo' => 'Passo 1: Identificar os termos',
        'descricao' => 'Identifique quais termos têm x e quais não têm.',
        'exemplo' => 'Na equação 3x + 5 = 14, o termo com x é 3x e os termos sem x são +5 e 14.',
        'campo_label' => 'Identifique os termos:',
        'placeholder' => 'Ex: 3x + 5 = 14'
    ],
    2 => [
        'titulo' => 'Passo 2: Isolar o termo com x',
        'descricao' => 'Use a operação inversa para isolar o termo com x de um lado da equação.',
        'exemplo' => 'Subtraia 5 dos dois lados: 3x = 14 - 5',
        'campo_label' => 'Qual a operação para isolar x?',
        'placeholder' => 'Ex: 3x = 14 - 5'
    ],
    3 => [
        'titulo' => 'Passo 3: Calcular o lado direito',
        'descricao' => 'Calcule o valor do lado direito da equação.',
        'exemplo' => '14 - 5 = 9 → 3x = 9',
        'campo_label' => 'Qual o valor do lado direito?',
        'placeholder' => 'Ex: 9'
    ],
    4 => [
        'titulo' => 'Passo 4: Isolar x',
        'descricao' => 'Divida ambos os lados pelo coeficiente de x para encontrar seu valor.',
        'exemplo' => 'Divida os dois lados por 3: x = 9 ÷ 3 = 3',
        'campo_label' => 'Qual o valor de x?',
        'placeholder' => 'Ex: 3'
    ]
];

$info_passo = $passos_info[$passo_atual];

// ============================================================
// 5. BUSCAR EXEMPLO DE AJUDA PARA O PASSO ATUAL
// ============================================================

$ajuda = new AjudaExemplo();
$exemplo_ajuda = $ajuda->getByPasso($passo_atual);

// ============================================================
// 6. VARIÁVEIS PARA A VIEW
// ============================================================

$page_title = 'Exercício - EquaTEA';
$sinal = $equacao['b'] >= 0 ? '+' : '-';
$equacao_str = "{$equacao['a']}x {$sinal} " . abs($equacao['b']) . " = {$equacao['c']}";

// ============================================================
// 7. HEADER DA PÁGINA
// ============================================================

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <meta name="description" content="Resolva equações passo a passo - EquaTEA">
    <meta name="theme-color" content="#2c3e50">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/passo.css">
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
        <a href="#passo-content" class="skip-link">Pular para o exercício</a>
        <a href="#resposta-input" class="skip-link">Pular para o campo de resposta</a>
    </div>

    <!-- ============================================================
    HEADER E MENU
    ============================================================ -->
    <?php include '../partials/header.php'; ?>
    <?php include '../partials/menu_aluno.php'; ?>

    <!-- ============================================================
    CONTEÚDO PRINCIPAL
    ============================================================ -->
    <main id="passo-content" class="container passo-container" role="main">
        
        <!-- ============================================================
        BARRA DE PROGRESSO
        ============================================================ -->
        <nav class="progresso-navegacao" aria-label="Progresso da resolução">
            <div class="passos-indicadores">
                <?php for ($i = 1; $i <= 4; $i++): ?>
                    <?php
                    $status_classe = '';
                    if ($i == $passo_atual) {
                        $status_classe = 'active';
                    } elseif ($i < $passo_atual) {
                        $status_classe = 'completed';
                    }
                    ?>
                    <div class="passo-indicador <?php echo $status_classe; ?>" aria-current="<?php echo ($i == $passo_atual) ? 'step' : 'false'; ?>">
                        <span class="passo-numero"><?php echo $i; ?></span>
                        <span class="passo-rotulo">Passo <?php echo $i; ?></span>
                        <?php if ($i < $passo_atual): ?>
                            <span class="passo-check" aria-hidden="true">✅</span>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </nav>

        <!-- ============================================================
        CARD DO EXERCÍCIO
        ============================================================ -->
        <div class="exercicio-card">
            
            <!-- ============================================================
            ENUNCIADO DA EQUAÇÃO
            ============================================================ -->
            <div class="equacao-header">
                <h2 class="equacao-titulo">Resolva a equação:</h2>
                <div class="equacao-display" aria-label="Equação: <?php echo htmlspecialchars($equacao_str); ?>">
                    <span class="equacao-texto"><?php echo htmlspecialchars($equacao_str); ?></span>
                    <button type="button" class="btn-ouvir-equacao" onclick="lerEquacao()" aria-label="Ouvir a equação">
                        <span aria-hidden="true">🔊</span>
                    </button>
                </div>
            </div>

            <!-- ============================================================
            PASSO ATUAL
            ============================================================ -->
            <div class="passo-content" id="passo-content">
                <div class="passo-header">
                    <h3 class="passo-titulo"><?php echo $info_passo['titulo']; ?></h3>
                    <p class="passo-descricao"><?php echo $info_passo['descricao']; ?></p>
                </div>

                <!-- ============================================================
                EXEMPLO ILUSTRATIVO
                ============================================================ -->
                <div class="passo-exemplo">
                    <span aria-hidden="true">💡</span>
                    <strong>Exemplo:</strong> <?php echo $info_passo['exemplo']; ?>
                </div>

                <!-- ============================================================
                FORMULÁRIO DE RESPOSTA
                ============================================================ -->
                <form id="form-resposta" class="resposta-form" method="POST">
                    <input type="hidden" name="equacao_id" value="<?php echo $equacao_id; ?>">
                    <input type="hidden" name="passo" value="<?php echo $passo_atual; ?>">
                    <input type="hidden" name="aluno_id" value="<?php echo $aluno_id; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo gerarTokenCSRF(); ?>">

                    <div class="form-group">
                        <label for="resposta" class="resposta-label">
                            <?php echo $info_passo['campo_label']; ?>
                        </label>
                        <div class="resposta-input-wrapper">
                            <input type="text" 
                                   id="resposta-input" 
                                   name="resposta" 
                                   class="resposta-input" 
                                   placeholder="<?php echo $info_passo['placeholder']; ?>" 
                                   required 
                                   autofocus
                                   aria-describedby="resposta-help resposta-error">
                            <button type="button" class="input-clear" onclick="limparResposta()" aria-label="Limpar resposta" style="display: none;">✕</button>
                        </div>
                        <small id="resposta-help" class="resposta-help">
                            <span aria-hidden="true">💡</span> 
                            Digite apenas a resposta para este passo.
                        </small>
                        <div id="resposta-error" class="resposta-error" role="alert" style="display: none;"></div>
                    </div>

                    <!-- ============================================================
                    BOTÕES DE AÇÃO
                    ============================================================ -->
                    <div class="botoes-acoes">
                        <button type="submit" class="btn-verificar" id="btn-verificar">
                            <span aria-hidden="true">✅</span> 
                            Verificar Resposta
                        </button>
                        <button type="button" class="btn-ajuda" id="btn-ajuda" onclick="mostrarAjuda()">
                            <span aria-hidden="true">❓</span> 
                            Ajuda
                        </button>
                        <button type="button" class="btn-ouvir" onclick="lerInstrucoes()">
                            <span aria-hidden="true">🔊</span> 
                            Ouvir
                        </button>
                    </div>
                </form>

                <!-- ============================================================
                ÁREA DE FEEDBACK
                ============================================================ -->
                <div id="feedback-area" class="feedback-area" style="display: none;" role="alert" aria-live="polite">
                    <div class="feedback-icon" aria-hidden="true"></div>
                    <div class="feedback-content">
                        <div class="feedback-mensagem"></div>
                        <div class="feedback-dica"></div>
                    </div>
                </div>

                <!-- ============================================================
                TENTATIVAS RESTANTES (para incentivar)
                ============================================================ -->
                <div class="tentativas-info">
                    <span aria-hidden="true">🔄</span>
                    <span id="tentativas-count">0</span> tentativas neste passo
                </div>
            </div>
        </div>

        <!-- ============================================================
        BOTÃO SAIR DO EXERCÍCIO
        ============================================================ -->
        <div class="exercicio-acoes">
            <a href="dashboard.php" class="btn-sair-exercicio">
                <span aria-hidden="true">⬅️</span> 
                Sair do Exercício
                <small>(Seu progresso será salvo)</small>
            </a>
        </div>
    </main>

    <!-- ============================================================
    MODAL DE AJUDA
    ============================================================ -->
    <div id="modal-ajuda" class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-ajuda-title">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-ajuda-title">❓ Ajuda - Passo <?php echo $passo_atual; ?></h2>
                <button class="modal-close" onclick="fecharModal()" aria-label="Fechar ajuda">✕</button>
            </div>
            <div class="modal-body">
                <div class="ajuda-content">
                    <h3>Exemplo análogo:</h3>
                    <?php if ($exemplo_ajuda): ?>
                        <div class="exemplo-card">
                            <p class="exemplo-equacao">
                                <?php 
                                $sinal_ex = $exemplo_ajuda['b'] >= 0 ? '+' : '-';
                                echo "{$exemplo_ajuda['a']}x {$sinal_ex} " . abs($exemplo_ajuda['b']) . " = {$exemplo_ajuda['c']}";
                                ?>
                            </p>
                            <p class="exemplo-descricao">
                                <?php echo htmlspecialchars($exemplo_ajuda['descricao']); ?>
                            </p>
                            <p class="exemplo-solucao">
                                <strong>Solução:</strong> x = <?php echo $exemplo_ajuda['solucao']; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <p>Não há exemplos disponíveis para este passo.</p>
                    <?php endif; ?>
                    
                    <div class="ajuda-dica">
                        <h4>📌 Dica para este passo:</h4>
                        <p><?php echo $dica_passo; ?></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-modal-fechar" onclick="fecharModal()">Entendi! 👍</button>
            </div>
        </div>
    </div>

    <!-- ============================================================
    MODAL DE PARABÉNS (para quando concluir o último passo)
    ============================================================ -->
    <div id="modal-parabens" class="modal" role="dialog" aria-modal="true" aria-labelledby="parabens-title" style="display: none;">
        <div class="modal-content modal-parabens">
            <div class="modal-body">
                <div class="parabens-content">
                    <div class="parabens-icon" aria-hidden="true">🎉</div>
                    <h2 id="parabens-title">Parabéns!</h2>
                    <p>Você concluiu a equação com sucesso!</p>
                    <div class="parabens-estatisticas">
                        <span>🏆 Equação: <strong><?php echo htmlspecialchars($equacao_str); ?></strong></span>
                        <span>⭐ Solução: <strong>x = <?php echo $equacao['solucao']; ?></strong></span>
                    </div>
                    <div class="parabens-acoes">
                        <a href="exercicio.php" class="btn-proximo-exercicio">
                            <span aria-hidden="true">🚀</span> Próximo Exercício
                        </a>
                        <a href="dashboard.php" class="btn-voltar-dashboard">
                            <span aria-hidden="true">📊</span> Ver Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================
    FOOTER
    ============================================================ -->
    <?php include '../partials/footer.php'; ?>

    <!-- ============================================================
    SCRIPTS JAVASCRIPT
    ============================================================ -->
    <script src="../../public/js/main.js"></script>
    <script src="../../public/js/passo.js"></script>
    <script src="../../public/js/speech.js"></script>
    
    <script>
        // ============================================================
        // VARIÁVEIS GLOBAIS
        // ============================================================
        
        let tentativas = 0;
        let passoAtual = <?php echo $passo_atual; ?>;
        let equacaoId = <?php echo $equacao_id; ?>;
        let alunoId = <?php echo $aluno_id; ?>;
        let audioAtivo = false;

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
            // 2. FOCAR NO CAMPO DE RESPOSTA
            // ============================================================
            
            const input = document.getElementById('resposta-input');
            if (input) {
                setTimeout(function() {
                    input.focus();
                }, 500);
            }
            
            // ============================================================
            // 3. EVENTO PARA LIMPAR CAMPO
            // ============================================================
            
            if (input) {
                input.addEventListener('input', function() {
                    const clearBtn = document.querySelector('.input-clear');
                    if (clearBtn) {
                        clearBtn.style.display = this.value ? 'block' : 'none';
                    }
                });
            }
            
            // ============================================================
            // 4. EVENTO DE SUBMISSÃO DO FORMULÁRIO
            // ============================================================
            
            const form = document.getElementById('form-resposta');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    verificarResposta();
                });
            }
            
            // ============================================================
            // 5. EVENTO DE TECLADO (ENTER)
            // ============================================================
            
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    const activeElement = document.activeElement;
                    if (activeElement && activeElement.id === 'resposta-input') {
                        verificarResposta();
                    }
                }
                if (e.key === 'Escape') {
                    const modal = document.getElementById('modal-ajuda');
                    if (modal && modal.style.display === 'block') {
                        fecharModal();
                    }
                }
            });
        });

        // ============================================================
        // FUNÇÕES PRINCIPAIS
        // ============================================================

        /**
         * Verifica a resposta do aluno
         */
        function verificarResposta() {
            const input = document.getElementById('resposta-input');
            const resposta = input.value.trim();
            
            if (!resposta) {
                mostrarErro('Por favor, digite uma resposta.');
                input.focus();
                return;
            }
            
            // Mostrar loading
            const btn = document.getElementById('btn-verificar');
            btn.disabled = true;
            btn.innerHTML = '<span aria-hidden="true">⏳</span> Verificando...';
            
            // Preparar dados
            const form = document.getElementById('form-resposta');
            const formData = new FormData(form);
            
            // Enviar requisição
            fetch('../../controllers/ExercicioController.php?action=verificar', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<span aria-hidden="true">✅</span> Verificar Resposta';
                
                if (data.status === 'avancar') {
                    // ACERTOU - Avançar para o próximo passo
                    tentativas++;
                    mostrarFeedback('success', '🎉 Correto!', 'Você acertou! Avançando para o próximo passo...');
                    
                    setTimeout(function() {
                        window.location.href = 'passo.php?equacao_id=' + equacaoId;
                    }, 1500);
                    
                } else if (data.status === 'concluido') {
                    // ACERTOU O ÚLTIMO PASSO - Concluiu a equação
                    tentativas++;
                    mostrarFeedback('success', '🎉 Parabéns!', 'Você concluiu a equação!');
                    
                    // Mostrar modal de parabéns
                    setTimeout(function() {
                        document.getElementById('modal-parabens').style.display = 'block';
                    }, 800);
                    
                } else if (data.status === 'erro') {
                    // ERROU
                    tentativas++;
                    atualizarTentativas();
                    mostrarFeedback('error', '❌ Resposta incorreta!', data.dica || 'Tente novamente!');
                    
                    // Limpar campo e focar
                    input.value = '';
                    input.focus();
                    
                } else {
                    // OUTRO ERRO
                    mostrarFeedback('error', '❌ Ops!', data.mensagem || 'Ocorreu um erro. Tente novamente.');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                btn.disabled = false;
                btn.innerHTML = '<span aria-hidden="true">✅</span> Verificar Resposta';
                mostrarFeedback('error', '❌ Erro de conexão', 'Não foi possível verificar sua resposta. Tente novamente.');
            });
        }

        /**
         * Mostra feedback para o usuário
         */
        function mostrarFeedback(tipo, titulo, mensagem) {
            const area = document.getElementById('feedback-area');
            const icon = area.querySelector('.feedback-icon');
            const msg = area.querySelector('.feedback-mensagem');
            const dica = area.querySelector('.feedback-dica');
            
            area.style.display = 'block';
            area.className = 'feedback-area feedback-' + tipo;
            
            if (tipo === 'success') {
                icon.textContent = '✅';
            } else {
                icon.textContent = '❌';
            }
            
            msg.innerHTML = '<strong>' + titulo + '</strong>';
            dica.textContent = mensagem;
            
            // Scroll para o feedback
            area.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        /**
         * Mostra erro no campo de resposta
         */
        function mostrarErro(mensagem) {
            const errorDiv = document.getElementById('resposta-error');
            errorDiv.textContent = mensagem;
            errorDiv.style.display = 'block';
            
            const input = document.getElementById('resposta-input');
            input.classList.add('invalid');
            
            setTimeout(function() {
                errorDiv.style.display = 'none';
                input.classList.remove('invalid');
            }, 5000);
        }

        /**
         * Atualiza o contador de tentativas
         */
        function atualizarTentativas() {
            const count = document.getElementById('tentativas-count');
            count.textContent = tentativas;
        }

        /**
         * Limpa o campo de resposta
         */
        function limparResposta() {
            const input = document.getElementById('resposta-input');
            input.value = '';
            input.focus();
            document.querySelector('.input-clear').style.display = 'none';
        }

        /**
         * Mostra o modal de ajuda
         */
        function mostrarAjuda() {
            const modal = document.getElementById('modal-ajuda');
            modal.style.display = 'block';
            modal.setAttribute('aria-hidden', 'false');
            
            // Anuncia a abertura para leitores de tela
            anunciarMudanca('Modal de ajuda aberto');
        }

        /**
         * Fecha o modal de ajuda
         */
        function fecharModal() {
            const modal = document.getElementById('modal-ajuda');
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            
            // Retorna o foco para o campo de resposta
            document.getElementById('resposta-input').focus();
        }

        /**
         * Lê a equação em voz alta (Web Speech API)
         */
        function lerEquacao() {
            const texto = 'Resolva a equação: ' + '<?php echo htmlspecialchars($equacao_str); ?>';
            lerTexto(texto);
        }

        /**
         * Lê as instruções do passo atual
         */
        function lerInstrucoes() {
            const texto = '<?php echo htmlspecialchars($info_passo['titulo'] . '. ' . $info_passo['descricao'] . '. ' . $info_passo['exemplo']); ?>';
            lerTexto(texto);
        }

        /**
         * Anuncia uma mensagem para leitores de tela
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
    </script>
</body>
</html>