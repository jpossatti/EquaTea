<?php
/**
 * ============================================================
 * dashboard.php
 * Página inicial do aluno após o login.
 * 
 * FUNCIONALIDADES:
 * - Visão geral do progresso do aluno
 * - Estatísticas de desempenho
 * - Botão para iniciar novo exercício
 * - Histórico de equações concluídas
 * - Lista de equações pendentes
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

require_once '../../models/Aluno.php';
require_once '../../models/Progresso.php';
require_once '../../models/RegistroErro.php';
require_once '../../models/Equacao.php';

// ============================================================
// 3. OBTENÇÃO DOS DADOS DO ALUNO
// ============================================================

$aluno = new Aluno();
$progresso = new Progresso();
$registroErro = new RegistroErro();
$equacao = new Equacao();

$aluno_id = $_SESSION['aluno_id'];
$dados_aluno = $aluno->getDadosCompletos($_SESSION['usuario_id']);

// ============================================================
// 4. ESTATÍSTICAS DO ALUNO
// ============================================================

// Estatísticas gerais
$estatisticas = $aluno->getEstatisticas($aluno_id);

// Taxa de conclusão
$taxa_conclusao = $progresso->getTaxaConclusao($aluno_id);

// Progresso recente
$progresso_recente = $progresso->getByAluno($aluno_id);

// Erros por tipo
$erros_por_tipo = $registroErro->getEstatisticas($aluno_id);

// Equação atual (se houver)
$equacao_atual = $equacao->getRandom($aluno_id);

// ============================================================
// 5. VARIÁVEIS PARA A VIEW
// ============================================================

$page_title = 'Dashboard - EquaTEA';
$nome_aluno = $_SESSION['usuario_nome'] ?? 'Aluno';

// Calcula o nível baseado nas equações concluídas
$total_concluidas = $estatisticas['equacoes_concluidas'] ?? 0;
if ($total_concluidas >= 20) {
    $nivel = 'Avançado 🌟';
    $nivel_cor = 'nivel-avancado';
} elseif ($total_concluidas >= 10) {
    $nivel = 'Intermediário ⭐';
    $nivel_cor = 'nivel-intermediario';
} elseif ($total_concluidas >= 5) {
    $nivel = 'Iniciante 🌱';
    $nivel_cor = 'nivel-iniciante';
} else {
    $nivel = 'Começando 🚀';
    $nivel_cor = 'nivel-comecando';
}

// ============================================================
// 6. HEADER DA PÁGINA
// ============================================================

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <meta name="description" content="Dashboard do aluno - EquaTEA">
    <meta name="theme-color" content="#2c3e50">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/dashboard.css">
    <link rel="stylesheet" href="../../public/css/acessibilidade.css">
    
    <!-- Favicon -->
    <link rel="icon" href="../../public/images/favicon.ico" type="image/x-icon">
    
    <!-- Fontes acessíveis -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Dyslexic&display=swap" rel="stylesheet">
</head>
<body>
    <!-- ============================================================
    SKIP LINKS (para leitores de tela)
    ============================================================ -->
    <div class="skip-links" role="navigation" aria-label="Pular navegação">
        <a href="#main-content" class="skip-link">Pular para o conteúdo principal</a>
        <a href="#btn-novo-exercicio" class="skip-link">Pular para novo exercício</a>
    </div>

    <!-- ============================================================
    HEADER
    ============================================================ -->
    <?php include '../partials/header.php'; ?>
    <?php include '../partials/menu_aluno.php'; ?>

    <!-- ============================================================
    CONTEÚDO PRINCIPAL
    ============================================================ -->
    <main id="main-content" class="container dashboard-container" role="main">
        
        <!-- ============================================================
        SAUDAÇÃO E RESUMO
        ============================================================ -->
        <div class="dashboard-header">
            <div class="saudacao">
                <h1 class="page-title">
                    <span aria-hidden="true">👋</span> 
                    Olá, <strong><?php echo htmlspecialchars($nome_aluno); ?></strong>!
                </h1>
                <p class="page-subtitle">Vamos praticar equações de 1º grau hoje?</p>
            </div>
            
            <div class="nivel-container">
                <span class="nivel-badge <?php echo $nivel_cor; ?>">
                    <span aria-hidden="true">🏆</span>
                    <?php echo $nivel; ?>
                </span>
                <span class="equacoes-count">
                    <?php echo $total_concluidas; ?> equações concluídas
                </span>
            </div>
        </div>

        <!-- ============================================================
        CARDS DE ESTATÍSTICAS
        ============================================================ -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" aria-hidden="true">📚</div>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $estatisticas['equacoes_tentadas'] ?? 0; ?></span>
                    <span class="stat-label">Equações tentadas</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" aria-hidden="true">✅</div>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $estatisticas['equacoes_concluidas'] ?? 0; ?></span>
                    <span class="stat-label">Equações concluídas</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" aria-hidden="true">📊</div>
                <div class="stat-content">
                    <span class="stat-value"><?php echo round($taxa_conclusao, 0); ?>%</span>
                    <span class="stat-label">Taxa de conclusão</span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" aria-hidden="true">🔄</div>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $estatisticas['total_tentativas'] ?? 0; ?></span>
                    <span class="stat-label">Total de tentativas</span>
                </div>
            </div>
        </div>

        <!-- ============================================================
        BOTÃO NOVO EXERCÍCIO
        ============================================================ -->
        <div class="action-area">
            <a href="exercicio.php" id="btn-novo-exercicio" class="btn-novo-exercicio">
                <span aria-hidden="true">🚀</span> 
                Iniciar Novo Exercício
                <span class="btn-subtext">Resolva uma equação passo a passo</span>
            </a>
            
            <?php if ($equacao_atual): ?>
                <div class="continuar-exercicio">
                    <p>Você tem uma equação em andamento!</p>
                    <a href="exercicio.php?equacao_id=<?php echo $equacao_atual['id']; ?>" class="btn-continuar">
                        <span aria-hidden="true">▶️</span> Continuar exercício
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- ============================================================
        SEÇÃO DE PROGRESSO RECENTE
        ============================================================ -->
        <section class="progresso-section" aria-labelledby="progresso-title">
            <h2 id="progresso-title" class="section-title">
                <span aria-hidden="true">📈</span> 
                Meu Progresso Recente
            </h2>
            
            <?php if (empty($progresso_recente)): ?>
                <div class="empty-state">
                    <span aria-hidden="true">📝</span>
                    <p>Você ainda não começou nenhum exercício.</p>
                    <p class="empty-hint">Clique em "Iniciar Novo Exercício" para começar!</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="progresso-table">
                        <thead>
                            <tr>
                                <th>Equação</th>
                                <th>Dificuldade</th>
                                <th>Passo Atual</th>
                                <th>Status</th>
                                <th>Tentativas</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($progresso_recente, 0, 10) as $p): ?>
                                <?php
                                $sinal = $p['b'] >= 0 ? '+' : '-';
                                $equacao_str = "{$p['a']}x {$sinal} " . abs($p['b']) . " = {$p['c']}";
                                $status = $p['concluida'] ? '✅ Concluída' : '⏳ Passo ' . $p['passo_atual'] . '/4';
                                $status_class = $p['concluida'] ? 'status-concluida' : 'status-andamento';
                                ?>
                                <tr>
                                    <td><strong><?php echo $equacao_str; ?></strong></td>
                                    <td>
                                        <span class="dificuldade-badge <?php echo $p['dificuldade']; ?>">
                                            <?php echo ucfirst($p['dificuldade']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $p['passo_atual']; ?> / 4</td>
                                    <td class="<?php echo $status_class; ?>"><?php echo $status; ?></td>
                                    <td><?php echo $p['tentativas'] ?? 0; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($p['data_inicio'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <!-- ============================================================
        SEÇÃO DE ERROS MAIS COMUNS
        ============================================================ -->
        <section class="erros-section" aria-labelledby="erros-title">
            <h2 id="erros-title" class="section-title">
                <span aria-hidden="true">🔍</span> 
                Meus Erros Mais Comuns
            </h2>
            
            <?php if (empty($erros_por_tipo)): ?>
                <div class="empty-state">
                    <span aria-hidden="true">🎉</span>
                    <p>Sem erros registrados! Continue assim!</p>
                </div>
            <?php else: ?>
                <div class="erros-grid">
                    <?php 
                    $cores_erro = [
                        'operacao_inversa' => '#e74c3c',
                        'calculo_errado' => '#f39c12',
                        'sinal_trocado' => '#3498db',
                        'divisao_incorreta' => '#9b59b6',
                        'identificacao_errada' => '#1abc9c',
                        'outro' => '#95a5a6'
                    ];
                    $labels_erro = [
                        'operacao_inversa' => 'Operação Inversa',
                        'calculo_errado' => 'Cálculo Errado',
                        'sinal_trocado' => 'Sinal Trocado',
                        'divisao_incorreta' => 'Divisão Incorreta',
                        'identificacao_errada' => 'Identificação Errada',
                        'outro' => 'Outro'
                    ];
                    foreach ($erros_por_tipo as $erro): 
                        $total = $erro['quantidade'];
                        $percentual = ($estatisticas['total_tentativas'] ?? 1) > 0 
                            ? round(($total / ($estatisticas['total_tentativas'] ?? 1)) * 100, 1) 
                            : 0;
                        $cor = $cores_erro[$erro['tipo_erro']] ?? '#95a5a6';
                    ?>
                        <div class="erro-item">
                            <div class="erro-info">
                                <span class="erro-label"><?php echo $labels_erro[$erro['tipo_erro']] ?? $erro['tipo_erro']; ?></span>
                                <span class="erro-count"><?php echo $total; ?>x</span>
                            </div>
                            <div class="erro-bar-container">
                                <div class="erro-bar" style="width: <?php echo min($percentual * 2, 100); ?>%; background-color: <?php echo $cor; ?>;"></div>
                            </div>
                            <span class="erro-percentual"><?php echo $percentual; ?>%</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- ============================================================
        DICA DO DIA
        ============================================================ -->
        <section class="dica-section" aria-labelledby="dica-title">
            <h2 id="dica-title" class="section-title">
                <span aria-hidden="true">💡</span> 
                Dica do Dia
            </h2>
            <div class="dica-card">
                <p>
                    <?php
                    $dicas = [
                        'Para resolver uma equação, lembre-se: o objetivo é isolar o x!',
                        'Sempre faça a operação inversa: se está somando, subtraia; se está subtraindo, some.',
                        'Cuidado com os sinais! Um erro de sinal pode mudar toda a resposta.',
                        'Divida o problema em passos menores. Cada passo te aproxima da solução!',
                        'Revise sempre seus cálculos. Pequenos erros podem ser corrigidos com atenção.',
                        'Use o botão "Ajuda" se ficar travado. Não tenha vergonha de pedir ajuda!',
                        'A prática leva à perfeição. Quanto mais exercícios, mais fácil fica!'
                    ];
                    echo $dicas[array_rand($dicas)];
                    ?>
                </p>
                <div class="dica-emoji" aria-hidden="true">📖</div>
            </div>
        </section>
    </main>

    <!-- ============================================================
    FOOTER
    ============================================================ -->
    <?php include '../partials/footer.php'; ?>

    <!-- ============================================================
    SCRIPTS JAVASCRIPT
    ============================================================ -->
    <script src="../../public/js/main.js"></script>
    <script src="../../public/js/dashboard.js"></script>
    
    <script>
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
            // 2. ANIMAÇÃO DOS CARDS (apenas para efeito visual)
            // ============================================================
            
            const cards = document.querySelectorAll('.stat-card');
            cards.forEach(function(card, index) {
                setTimeout(function() {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100 * index);
            });
        });
    </script>
</body>
</html>