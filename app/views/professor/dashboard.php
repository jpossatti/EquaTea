<?php
/**
 * ============================================================
 * dashboard.php
 * Página inicial do professor após o login.
 * 
 * FUNCIONALIDADES:
 * - Visão geral das estatísticas do sistema
 * - Lista de alunos com seu progresso
 * - Gráficos simples de desempenho
 * - Acesso rápido às funcionalidades de gerenciamento
 * - Alertas e notificações importantes
 * 
 * @package EquaTEA
 * @subpackage Views/Professor
 * @author Equipe EquaTEA
 * @version 1.0
 * ============================================================
 */

// ============================================================
// 1. INICIALIZAÇÃO E VERIFICAÇÃO DE SESSÃO
// ============================================================

session_start();
require_once '../../helpers/session_helper.php';

// Verifica se o usuário está logado e é um professor
verificarSessao();
if (!isProfessor()) {
    header('Location: ../auth/login.php');
    exit;
}

// ============================================================
// 2. CARREGAMENTO DOS MODELOS
// ============================================================

require_once '../../models/Professor.php';
require_once '../../models/Aluno.php';
require_once '../../models/Equacao.php';
require_once '../../models/Progresso.php';
require_once '../../models/RegistroErro.php';

// ============================================================
// 3. OBTENÇÃO DOS DADOS
// ============================================================

$professor = new Professor();
$aluno = new Aluno();
$equacao = new Equacao();
$progresso = new Progresso();
$registroErro = new RegistroErro();

$professor_id = $_SESSION['usuario_id'];
$dados_professor = $professor->getByUsuarioId($professor_id);

// ============================================================
// 4. ESTATÍSTICAS DO SISTEMA
// ============================================================

// Estatísticas gerais
$db = Database::getInstance()->getConnection();

// Total de alunos ativos
$sql = "SELECT COUNT(*) as total FROM usuarios WHERE tipo_perfil = 'aluno' AND ativo = 1";
$stmt = $db->prepare($sql);
$stmt->execute();
$total_alunos = $stmt->fetch()['total'];

// Total de equações
$sql = "SELECT COUNT(*) as total FROM equacoes";
$stmt = $db->prepare($sql);
$stmt->execute();
$total_equacoes = $stmt->fetch()['total'];

// Total de equações concluídas
$sql = "SELECT COUNT(*) as total FROM progresso_aluno WHERE concluida = 1";
$stmt = $db->prepare($sql);
$stmt->execute();
$total_concluidas = $stmt->fetch()['total'];

// Total de erros
$sql = "SELECT COUNT(*) as total FROM registro_erros";
$stmt = $db->prepare($sql);
$stmt->execute();
$total_erros = $stmt->fetch()['total'];

// Taxa de conclusão geral
$sql = "SELECT 
            COUNT(*) as total,
            SUM(concluida) as concluidas
        FROM progresso_aluno";
$stmt = $db->prepare($sql);
$stmt->execute();
$result = $stmt->fetch();
$taxa_conclusao = $result['total'] > 0 
    ? round(($result['concluidas'] / $result['total']) * 100, 1) 
    : 0;

// ============================================================
// 5. LISTA DE ALUNOS COM PROGRESSO
// ============================================================

$sql = "SELECT 
            u.id as usuario_id,
            u.nome,
            u.email,
            u.ultimo_acesso,
            a.id as aluno_id,
            a.idade,
            a.nivel_tea,
            a.escola,
            a.turma,
            (SELECT COUNT(*) FROM progresso_aluno WHERE aluno_id = a.id) as total_equacoes,
            (SELECT SUM(concluida) FROM progresso_aluno WHERE aluno_id = a.id) as equacoes_concluidas,
            (SELECT COUNT(*) FROM registro_erros WHERE aluno_id = a.id) as total_erros
        FROM usuarios u
        JOIN alunos a ON u.id = a.usuario_id
        WHERE u.tipo_perfil = 'aluno' AND u.ativo = 1
        ORDER BY u.nome ASC
        LIMIT 10";
$stmt = $db->prepare($sql);
$stmt->execute();
$alunos_recentes = $stmt->fetchAll();

// ============================================================
// 6. ERROS MAIS COMUNS (para o professor)
// ============================================================

$sql = "SELECT 
            tipo_erro,
            COUNT(*) as total
        FROM registro_erros
        GROUP BY tipo_erro
        ORDER BY total DESC
        LIMIT 5";
$stmt = $db->prepare($sql);
$stmt->execute();
$erros_comuns = $stmt->fetchAll();

// Mapeamento de nomes dos erros
$labels_erro = [
    'operacao_inversa' => 'Operação Inversa',
    'calculo_errado' => 'Cálculo Errado',
    'sinal_trocado' => 'Sinal Trocado',
    'divisao_incorreta' => 'Divisão Incorreta',
    'identificacao_errada' => 'Identificação Errada',
    'outro' => 'Outro'
];

// ============================================================
// 7. ALUNOS COM MAIOR NÚMERO DE ERROS
// ============================================================

$sql = "SELECT 
            u.nome,
            COUNT(r.id) as total_erros
        FROM registro_erros r
        JOIN alunos a ON r.aluno_id = a.id
        JOIN usuarios u ON a.usuario_id = u.id
        GROUP BY u.nome
        ORDER BY total_erros DESC
        LIMIT 5";
$stmt = $db->prepare($sql);
$stmt->execute();
$alunos_mais_erros = $stmt->fetchAll();

// ============================================================
// 8. VARIÁVEIS PARA A VIEW
// ============================================================

$page_title = 'Dashboard Professor - EquaTEA';
$nome_professor = $_SESSION['usuario_nome'] ?? 'Professor';

// ============================================================
// 9. HEADER DA PÁGINA
// ============================================================

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <meta name="description" content="Dashboard do professor - EquaTEA">
    <meta name="theme-color" content="#2c3e50">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/dashboard-professor.css">
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
        <a href="#btn-novo-exercicio" class="skip-link">Pular para novo exercício</a>
    </div>

    <!-- ============================================================
    HEADER E MENU
    ============================================================ -->
    <?php include '../partials/header.php'; ?>
    <?php include '../partials/menu_professor.php'; ?>

    <!-- ============================================================
    CONTEÚDO PRINCIPAL
    ============================================================ -->
    <main id="main-content" class="container dashboard-professor-container" role="main">
        
        <!-- ============================================================
        SAUDAÇÃO E RESUMO
        ============================================================ -->
        <div class="dashboard-header">
            <div class="saudacao">
                <h1 class="page-title">
                    <span aria-hidden="true">👋</span> 
                    Olá, Prof. <strong><?php echo htmlspecialchars($nome_professor); ?></strong>!
                </h1>
                <p class="page-subtitle">Acompanhe o progresso dos seus alunos</p>
            </div>
            
            <div class="data-hora">
                <span aria-hidden="true">📅</span>
                <?php echo date('d/m/Y H:i'); ?>
            </div>
        </div>

        <!-- ============================================================
        CARDS DE ESTATÍSTICAS
        ============================================================ -->
        <div class="stats-grid">
            <div class="stat-card stat-card-alunos">
                <div class="stat-icon" aria-hidden="true">👨‍🎓</div>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $total_alunos; ?></span>
                    <span class="stat-label">Alunos Ativos</span>
                </div>
            </div>
            
            <div class="stat-card stat-card-equacoes">
                <div class="stat-icon" aria-hidden="true">📚</div>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $total_equacoes; ?></span>
                    <span class="stat-label">Equações Disponíveis</span>
                </div>
            </div>
            
            <div class="stat-card stat-card-concluidas">
                <div class="stat-icon" aria-hidden="true">✅</div>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $total_concluidas; ?></span>
                    <span class="stat-label">Equações Concluídas</span>
                </div>
            </div>
            
            <div class="stat-card stat-card-taxa">
                <div class="stat-icon" aria-hidden="true">📊</div>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $taxa_conclusao; ?>%</span>
                    <span class="stat-label">Taxa de Conclusão</span>
                </div>
            </div>
            
            <div class="stat-card stat-card-erros">
                <div class="stat-icon" aria-hidden="true">🔍</div>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $total_erros; ?></span>
                    <span class="stat-label">Total de Erros</span>
                </div>
            </div>
        </div>

        <!-- ============================================================
        AÇÕES RÁPIDAS
        ============================================================ -->
        <div class="acoes-rapidas">
            <h2 class="section-title">
                <span aria-hidden="true">⚡</span> 
                Ações Rápidas
            </h2>
            <div class="acoes-grid">
                <a href="gerenciar_alunos.php" class="acao-card">
                    <span aria-hidden="true">👨‍🎓</span>
                    <span class="acao-titulo">Gerenciar Alunos</span>
                    <span class="acao-descricao">Cadastrar, editar e visualizar alunos</span>
                </a>
                <a href="gerenciar_equacoes.php" class="acao-card">
                    <span aria-hidden="true">📝</span>
                    <span class="acao-titulo">Gerenciar Equações</span>
                    <span class="acao-descricao">Cadastrar, editar e remover equações</span>
                </a>
                <a href="relatorio.php" class="acao-card">
                    <span aria-hidden="true">📊</span>
                    <span class="acao-titulo">Relatório de Erros</span>
                    <span class="acao-descricao">Visualizar erros por aluno e passo</span>
                </a>
                <a href="#" class="acao-card" onclick="exportarDados()">
                    <span aria-hidden="true">📥</span>
                    <span class="acao-titulo">Exportar Dados</span>
                    <span class="acao-descricao">Baixar relatórios em CSV</span>
                </a>
            </div>
        </div>

        <!-- ============================================================
        ALUNOS RECENTES
        ============================================================ -->
        <section class="alunos-section" aria-labelledby="alunos-title">
            <h2 id="alunos-title" class="section-title">
                <span aria-hidden="true">👨‍🎓</span> 
                Alunos Recentes
            </h2>
            
            <?php if (empty($alunos_recentes)): ?>
                <div class="empty-state">
                    <span aria-hidden="true">📝</span>
                    <p>Nenhum aluno cadastrado ainda.</p>
                    <p class="empty-hint">Clique em "Gerenciar Alunos" para adicionar alunos.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="alunos-table">
                        <thead>
                            <tr>
                                <th>Aluno</th>
                                <th>Idade</th>
                                <th>Nível TEA</th>
                                <th>Equações</th>
                                <th>Concluídas</th>
                                <th>Erros</th>
                                <th>Último Acesso</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alunos_recentes as $a): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($a['nome']); ?></strong></td>
                                    <td><?php echo $a['idade']; ?> anos</td>
                                    <td>
                                        <span class="nivel-badge <?php echo $a['nivel_tea']; ?>">
                                            <?php echo $a['nivel_tea'] == 'suporte1' ? 'Suporte 1' : 'Suporte 2'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $a['total_equacoes'] ?? 0; ?></td>
                                    <td><?php echo $a['equacoes_concluidas'] ?? 0; ?></td>
                                    <td><?php echo $a['total_erros'] ?? 0; ?></td>
                                    <td>
                                        <?php if ($a['ultimo_acesso']): ?>
                                            <?php echo date('d/m/Y', strtotime($a['ultimo_acesso'])); ?>
                                        <?php else: ?>
                                            <span class="status-nunca">Nunca</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="gerenciar_alunos.php?editar=<?php echo $a['aluno_id']; ?>" class="btn-icone" aria-label="Editar aluno">
                                            ✏️
                                        </a>
                                        <a href="relatorio.php?aluno_id=<?php echo $a['aluno_id']; ?>" class="btn-icone" aria-label="Ver relatório">
                                            📊
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="ver-todos">
                    <a href="gerenciar_alunos.php" class="btn-ver-todos">Ver todos os alunos →</a>
                </div>
            <?php endif; ?>
        </section>

        <!-- ============================================================
        ERROS MAIS COMUNS E ALUNOS COM MAIS ERROS
        ============================================================ -->
        <div class="analises-grid">
            
            <!-- ============================================================
            ERROS MAIS COMUNS
            ============================================================ -->
            <section class="erros-comuns-section" aria-labelledby="erros-title">
                <h2 id="erros-title" class="section-title">
                    <span aria-hidden="true">🔍</span> 
                    Erros Mais Comuns
                </h2>
                
                <?php if (empty($erros_comuns)): ?>
                    <div class="empty-state small">
                        <span aria-hidden="true">🎉</span>
                        <p>Nenhum erro registrado ainda!</p>
                    </div>
                <?php else: ?>
                    <div class="erros-list">
                        <?php foreach ($erros_comuns as $erro): 
                            $total = $erro['total'];
                            $percentual = $total_erros > 0 ? round(($total / $total_erros) * 100, 1) : 0;
                            $label = $labels_erro[$erro['tipo_erro']] ?? $erro['tipo_erro'];
                        ?>
                            <div class="erro-item">
                                <div class="erro-info">
                                    <span class="erro-label"><?php echo $label; ?></span>
                                    <span class="erro-count"><?php echo $total; ?>x (<?php echo $percentual; ?>%)</span>
                                </div>
                                <div class="erro-bar-container">
                                    <div class="erro-bar" style="width: <?php echo $percentual; ?>%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <!-- ============================================================
            ALUNOS COM MAIS ERROS
            ============================================================ -->
            <section class="alunos-erros-section" aria-labelledby="alunos-erros-title">
                <h2 id="alunos-erros-title" class="section-title">
                    <span aria-hidden="true">⚠️</span> 
                    Alunos com Mais Erros
                </h2>
                
                <?php if (empty($alunos_mais_erros)): ?>
                    <div class="empty-state small">
                        <span aria-hidden="true">🎉</span>
                        <p>Nenhum erro registrado ainda!</p>
                    </div>
                <?php else: ?>
                    <div class="alunos-erros-list">
                        <?php foreach ($alunos_mais_erros as $a): ?>
                            <div class="aluno-erro-item">
                                <span class="aluno-erro-nome"><?php echo htmlspecialchars($a['nome']); ?></span>
                                <span class="aluno-erro-count"><?php echo $a['total_erros']; ?> erros</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>

        <!-- ============================================================
        DICA DO DIA (para o professor)
        ============================================================ -->
        <section class="dica-section" aria-labelledby="dica-title">
            <h2 id="dica-title" class="section-title">
                <span aria-hidden="true">💡</span> 
                Dica para o Professor
            </h2>
            <div class="dica-card">
                <p>
                    <?php
                    $dicas = [
                        'Alunos com TEA se beneficiam de instruções claras e passo a passo. Reforce a importância de cada passo!',
                        'Use os relatórios de erros para identificar padrões e planejar intervenções específicas.',
                        'Incentive seus alunos a usar o botão "Ajuda" sempre que precisarem.',
                        'Celebre cada pequena conquista! O reforço positivo é muito importante para alunos com TEA.',
                        'Mantenha a rotina: sessões regulares ajudam na previsibilidade e reduzem a ansiedade.',
                        'Adapte o ritmo: alguns alunos podem precisar de mais tempo em passos específicos.',
                        'Use exemplos concretos e visuais para ajudar na compreensão dos conceitos abstratos.'
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
    <script src="../../public/js/dashboard-professor.js"></script>
    
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
            // 2. ANIMAÇÃO DOS CARDS
            // ============================================================
            
            const cards = document.querySelectorAll('.stat-card');
            cards.forEach(function(card, index) {
                setTimeout(function() {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100 * index);
            });
        });

        // ============================================================
        // FUNÇÃO PARA EXPORTAR DADOS
        // ============================================================
        
        function exportarDados() {
            const tipos = [
                { id: 'alunos', label: 'Alunos' },
                { id: 'progresso', label: 'Progresso' },
                { id: 'erros', label: 'Erros' }
            ];
            
            // Criar um menu simples para escolher o tipo de relatório
            let menuHTML = '<div class="exportar-menu" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:30px;border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,0.2);z-index:9999;min-width:300px;">';
            menuHTML += '<h3 style="margin-top:0;">📥 Exportar Relatório</h3>';
            menuHTML += '<p style="color:#666;">Selecione o tipo de relatório:</p>';
            menuHTML += '<div style="display:flex;flex-direction:column;gap:10px;margin:16px 0;">';
            
            tipos.forEach(function(tipo) {
                menuHTML += '<a href="../../controllers/RelatorioController.php?action=exportar&tipo=' + tipo.id + '" ';
                menuHTML += 'style="padding:12px 20px;background:#f8f9fa;border-radius:8px;text-decoration:none;color:#2c3e50;text-align:center;font-size:16px;border:2px solid #e9ecef;">';
                menuHTML += tipo.label;
                menuHTML += '</a>';
            });
            
            menuHTML += '</div>';
            menuHTML += '<button onclick="this.parentElement.remove()" style="padding:10px 20px;background:#e74c3c;color:white;border:none;border-radius:6px;font-size:16px;cursor:pointer;width:100%;">Cancelar</button>';
            menuHTML += '</div>';
            
            // Remover menu anterior se existir
            const oldMenu = document.querySelector('.exportar-menu');
            if (oldMenu) oldMenu.remove();
            
            // Adicionar menu ao body
            document.body.insertAdjacentHTML('beforeend', menuHTML);
            
            // Fechar ao clicar fora
            document.querySelector('.exportar-menu').addEventListener('click', function(e) {
                if (e.target === this) {
                    this.remove();
                }
            });
        }
    </script>
</body>
</html>