<?php
/**
 * ============================================================
 * gerenciar_equacoes.php
 * Página para gerenciar equações do sistema.
 * 
 * FUNCIONALIDADES:
 * - Listar todas as equações
 * - Cadastrar novas equações
 * - Editar equações existentes
 * - Excluir equações (apenas se não utilizadas)
 * - Filtrar por dificuldade
 * - Validação automática de soluções inteiras
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

require_once '../../models/Equacao.php';

// ============================================================
// 3. PROCESSAMENTO DE AÇÕES DO FORMULÁRIO
// ============================================================

$equacao = new Equacao();
$mensagem = '';
$tipo_mensagem = '';
$filtro_dificuldade = isset($_GET['dificuldade']) ? $_GET['dificuldade'] : '';

// ============================================================
// 3.1. CADASTRAR EQUAÇÃO
// ============================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'cadastrar') {
        $a = (int)$_POST['a'];
        $b = (int)$_POST['b'];
        $c = (int)$_POST['c'];
        $dificuldade = $_POST['dificuldade'] ?? 'facil';
        
        // Validar coeficientes
        if ($a == 0 || $a < -20 || $a > 20) {
            $mensagem = 'O coeficiente a deve ser diferente de zero e estar entre -20 e 20.';
            $tipo_mensagem = 'erro';
        } elseif ($b < -20 || $b > 20) {
            $mensagem = 'O coeficiente b deve estar entre -20 e 20.';
            $tipo_mensagem = 'erro';
        } elseif ($c < -20 || $c > 20) {
            $mensagem = 'O coeficiente c deve estar entre -20 e 20.';
            $tipo_mensagem = 'erro';
        } else {
            // Calcular solução
            $solucao = ($c - $b) / $a;
            
            // Verificar se solução é inteira
            if (fmod($solucao, 1) != 0) {
                $mensagem = "A solução deve ser um número inteiro. Valor calculado: {$solucao}";
                $tipo_mensagem = 'erro';
            } else {
                // Inserir equação
                $db = Database::getInstance()->getConnection();
                $sql = "INSERT INTO equacoes (a, b, c, solucao, dificuldade) 
                        VALUES (:a, :b, :c, :solucao, :dificuldade)";
                $stmt = $db->prepare($sql);
                $result = $stmt->execute([
                    ':a' => $a,
                    ':b' => $b,
                    ':c' => $c,
                    ':solucao' => (int)$solucao,
                    ':dificuldade' => $dificuldade
                ]);
                
                if ($result) {
                    $mensagem = 'Equação cadastrada com sucesso!';
                    $tipo_mensagem = 'sucesso';
                } else {
                    $mensagem = 'Erro ao cadastrar equação. Tente novamente.';
                    $tipo_mensagem = 'erro';
                }
            }
        }
    }
    
    // ============================================================
    // 3.2. EDITAR EQUAÇÃO
    // ============================================================
    
    if ($action === 'editar') {
        $id = (int)$_POST['id'];
        $a = (int)$_POST['a'];
        $b = (int)$_POST['b'];
        $c = (int)$_POST['c'];
        $dificuldade = $_POST['dificuldade'] ?? 'facil';
        
        // Validar coeficientes
        if ($a == 0 || $a < -20 || $a > 20) {
            $mensagem = 'O coeficiente a deve ser diferente de zero e estar entre -20 e 20.';
            $tipo_mensagem = 'erro';
        } elseif ($b < -20 || $b > 20) {
            $mensagem = 'O coeficiente b deve estar entre -20 e 20.';
            $tipo_mensagem = 'erro';
        } elseif ($c < -20 || $c > 20) {
            $mensagem = 'O coeficiente c deve estar entre -20 e 20.';
            $tipo_mensagem = 'erro';
        } else {
            // Calcular solução
            $solucao = ($c - $b) / $a;
            
            // Verificar se solução é inteira
            if (fmod($solucao, 1) != 0) {
                $mensagem = "A solução deve ser um número inteiro. Valor calculado: {$solucao}";
                $tipo_mensagem = 'erro';
            } else {
                // Atualizar equação
                $db = Database::getInstance()->getConnection();
                $sql = "UPDATE equacoes 
                        SET a = :a, b = :b, c = :c, solucao = :solucao, dificuldade = :dificuldade 
                        WHERE id = :id";
                $stmt = $db->prepare($sql);
                $result = $stmt->execute([
                    ':a' => $a,
                    ':b' => $b,
                    ':c' => $c,
                    ':solucao' => (int)$solucao,
                    ':dificuldade' => $dificuldade,
                    ':id' => $id
                ]);
                
                if ($result) {
                    $mensagem = 'Equação atualizada com sucesso!';
                    $tipo_mensagem = 'sucesso';
                } else {
                    $mensagem = 'Erro ao atualizar equação. Tente novamente.';
                    $tipo_mensagem = 'erro';
                }
            }
        }
    }
    
    // ============================================================
    // 3.3. EXCLUIR EQUAÇÃO
    // ============================================================
    
    if ($action === 'excluir') {
        $id = (int)$_POST['id'];
        
        // Verificar se a equação foi utilizada
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT COUNT(*) as total FROM progresso_aluno WHERE equacao_id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        
        if ($result['total'] > 0) {
            $mensagem = 'Esta equação já foi utilizada por alunos e não pode ser excluída.';
            $tipo_mensagem = 'erro';
        } else {
            $sql = "DELETE FROM equacoes WHERE id = :id";
            $stmt = $db->prepare($sql);
            $result = $stmt->execute([':id' => $id]);
            
            if ($result) {
                $mensagem = 'Equação excluída com sucesso!';
                $tipo_mensagem = 'sucesso';
            } else {
                $mensagem = 'Erro ao excluir equação. Tente novamente.';
                $tipo_mensagem = 'erro';
            }
        }
    }
}

// ============================================================
// 4. LISTAR EQUAÇÕES
// ============================================================

$sql = "SELECT * FROM equacoes";
$params = [];

if ($filtro_dificuldade) {
    $sql .= " WHERE dificuldade = :dificuldade";
    $params[':dificuldade'] = $filtro_dificuldade;
}

$sql .= " ORDER BY FIELD(dificuldade, 'facil', 'medio', 'dificil'), a, b, c";

$db = Database::getInstance()->getConnection();
$stmt = $db->prepare($sql);
$stmt->execute($params);
$equacoes = $stmt->fetchAll();

// ============================================================
// 5. CONTAGEM POR DIFICULDADE
// ============================================================

$sql = "SELECT dificuldade, COUNT(*) as total FROM equacoes GROUP BY dificuldade";
$stmt = $db->prepare($sql);
$stmt->execute();
$contagens = [];
foreach ($stmt->fetchAll() as $row) {
    $contagens[$row['dificuldade']] = $row['total'];
}

// ============================================================
// 6. VARIÁVEIS PARA A VIEW
// ============================================================

$page_title = 'Gerenciar Equações - EquaTEA';

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
    
    <meta name="description" content="Gerenciar equações - EquaTEA">
    <meta name="theme-color" content="#2c3e50">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/gerenciar.css">
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
        <a href="#form-cadastro" class="skip-link">Pular para formulário de cadastro</a>
    </div>

    <!-- ============================================================
    HEADER E MENU
    ============================================================ -->
    <?php include '../partials/header.php'; ?>
    <?php include '../partials/menu_professor.php'; ?>

    <!-- ============================================================
    CONTEÚDO PRINCIPAL
    ============================================================ -->
    <main id="main-content" class="container gerenciar-container" role="main">
        
        <div class="page-header">
            <h1 class="page-title">
                <span aria-hidden="true">📝</span> 
                Gerenciar Equações
            </h1>
            <p class="page-subtitle">Cadastre, edite e remova equações do banco</p>
        </div>

        <!-- ============================================================
        MENSAGENS DE FEEDBACK
        ============================================================ -->
        <?php if ($mensagem): ?>
            <div class="alert alert-<?php echo $tipo_mensagem; ?>" role="alert" aria-live="polite">
                <span class="alert-icon" aria-hidden="true">
                    <?php echo $tipo_mensagem === 'sucesso' ? '✅' : '⚠️'; ?>
                </span>
                <?php echo $mensagem; ?>
                <button class="alert-close" onclick="this.parentElement.style.display='none'" 
                        aria-label="Fechar mensagem">✕</button>
            </div>
        <?php endif; ?>

        <!-- ============================================================
        RESUMO DE EQUAÇÕES
        ============================================================ -->
        <div class="resumo-equacoes">
            <span class="resumo-item">
                <span class="resumo-icone" aria-hidden="true">📚</span>
                Total: <strong><?php echo count($equacoes); ?></strong> equações
            </span>
            <span class="resumo-item">
                <span class="resumo-icone" aria-hidden="true">🟢</span>
                Fácil: <strong><?php echo $contagens['facil'] ?? 0; ?></strong>
            </span>
            <span class="resumo-item">
                <span class="resumo-icone" aria-hidden="true">🟡</span>
                Médio: <strong><?php echo $contagens['medio'] ?? 0; ?></strong>
            </span>
            <span class="resumo-item">
                <span class="resumo-icone" aria-hidden="true">🔴</span>
                Difícil: <strong><?php echo $contagens['dificil'] ?? 0; ?></strong>
            </span>
        </div>

        <!-- ============================================================
        FORMULÁRIO DE CADASTRO
        ============================================================ -->
        <section class="form-section" aria-labelledby="cadastro-title">
            <h2 id="cadastro-title" class="section-title">
                <span aria-hidden="true">➕</span> 
                Cadastrar Nova Equação
            </h2>
            
            <form id="form-cadastro" method="POST" action="" class="form-cadastro equacao-form">
                <input type="hidden" name="action" value="cadastrar">
                
                <div class="equacao-preview">
                    <span class="preview-label">Pré-visualização:</span>
                    <span class="preview-equacao" id="preview-equacao">ax + b = c</span>
                </div>
                
                <div class="form-grid equacao-grid">
                    <div class="form-group">
                        <label for="a">Coeficiente a *</label>
                        <input type="number" id="a" name="a" required 
                               placeholder="-20 a 20" min="-20" max="20" 
                               oninput="atualizarPreview()">
                        <small class="form-help">Não pode ser zero.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="b">Coeficiente b *</label>
                        <input type="number" id="b" name="b" required 
                               placeholder="-20 a 20" min="-20" max="20"
                               oninput="atualizarPreview()">
                    </div>
                    
                    <div class="form-group">
                        <label for="c">Coeficiente c *</label>
                        <input type="number" id="c" name="c" required 
                               placeholder="-20 a 20" min="-20" max="20"
                               oninput="atualizarPreview()">
                    </div>
                    
                    <div class="form-group">
                        <label for="dificuldade">Dificuldade *</label>
                        <select id="dificuldade" name="dificuldade" required>
                            <option value="facil">Fácil</option>
                            <option value="medio">Médio</option>
                            <option value="dificil">Difícil</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <span aria-hidden="true">✅</span> Cadastrar Equação
                    </button>
                    <button type="reset" class="btn-secondary" onclick="atualizarPreview()">
                        <span aria-hidden="true">🔄</span> Limpar
                    </button>
                </div>
            </form>
        </section>

        <!-- ============================================================
        FILTROS E LISTA DE EQUAÇÕES
        ============================================================ -->
        <section class="list-section" aria-labelledby="lista-title">
            <div class="list-header">
                <h2 id="lista-title" class="section-title">
                    <span aria-hidden="true">📋</span> 
                    Lista de Equações
                    <span class="badge-count"><?php echo count($equacoes); ?></span>
                </h2>
                
                <div class="filtros">
                    <form method="GET" action="" class="filtro-form">
                        <label for="filtro_dificuldade">Filtrar por dificuldade:</label>
                        <select id="filtro_dificuldade" name="dificuldade" onchange="this.form.submit()">
                            <option value="">Todas</option>
                            <option value="facil" <?php echo $filtro_dificuldade === 'facil' ? 'selected' : ''; ?>>Fácil</option>
                            <option value="medio" <?php echo $filtro_dificuldade === 'medio' ? 'selected' : ''; ?>>Médio</option>
                            <option value="dificil" <?php echo $filtro_dificuldade === 'dificil' ? 'selected' : ''; ?>>Difícil</option>
                        </select>
                        <?php if ($filtro_dificuldade): ?>
                            <a href="gerenciar_equacoes.php" class="btn-limpar-filtro">✕ Limpar filtro</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <?php if (empty($equacoes)): ?>
                <div class="empty-state">
                    <span aria-hidden="true">📝</span>
                    <p>Nenhuma equação cadastrada <?php echo $filtro_dificuldade ? "com dificuldade '{$filtro_dificuldade}'" : ''; ?>.</p>
                    <p class="empty-hint">Use o formulário acima para adicionar equações.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="list-table equacoes-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Equação</th>
                                <th>Solução (x)</th>
                                <th>Dificuldade</th>
                                <th>Utilizada</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($equacoes as $e): 
                                $sinal = $e['b'] >= 0 ? '+' : '-';
                                $equacao_str = "{$e['a']}x {$sinal} " . abs($e['b']) . " = {$e['c']}";
                                
                                // Verificar se foi utilizada
                                $sql = "SELECT COUNT(*) as total FROM progresso_aluno WHERE equacao_id = :id";
                                $stmt = $db->prepare($sql);
                                $stmt->execute([':id' => $e['id']]);
                                $utilizada = $stmt->fetch()['total'] > 0;
                            ?>
                                <tr>
                                    <td>#<?php echo $e['id']; ?></td>
                                    <td><strong><?php echo $equacao_str; ?></strong></td>
                                    <td><span class="solucao-destaque">x = <?php echo $e['solucao']; ?></span></td>
                                    <td>
                                        <span class="dificuldade-badge <?php echo $e['dificuldade']; ?>">
                                            <?php echo ucfirst($e['dificuldade']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($utilizada): ?>
                                            <span class="status-utilizada">✅ Sim</span>
                                        <?php else: ?>
                                            <span class="status-nao-utilizada">❌ Não</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="acoes-botoes">
                                            <button class="btn-acao btn-editar" onclick="editarEquacao(<?php echo $e['id']; ?>)" 
                                                    aria-label="Editar equação">
                                                ✏️
                                            </button>
                                            <?php if (!$utilizada): ?>
                                                <button class="btn-acao btn-excluir" onclick="excluirEquacao(<?php echo $e['id']; ?>, '<?php echo addslashes($equacao_str); ?>')" 
                                                        aria-label="Excluir equação">
                                                    🗑️
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- ============================================================
    MODAL DE EDIÇÃO DE EQUAÇÃO
    ============================================================ -->
    <div id="modal-editar" class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-editar-title" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-editar-title">✏️ Editar Equação</h2>
                <button class="modal-close" onclick="fecharModal('modal-editar')" aria-label="Fechar">✕</button>
            </div>
            <div class="modal-body">
                <form id="form-editar" method="POST" action="">
                    <input type="hidden" name="action" value="editar">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="equacao-preview">
                        <span class="preview-label">Pré-visualização:</span>
                        <span class="preview-equacao" id="edit-preview-equacao">ax + b = c</span>
                    </div>
                    
                    <div class="form-grid equacao-grid">
                        <div class="form-group">
                            <label for="edit_a">Coeficiente a *</label>
                            <input type="number" id="edit_a" name="a" required 
                                   min="-20" max="20" oninput="atualizarEditPreview()">
                            <small class="form-help">Não pode ser zero.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_b">Coeficiente b *</label>
                            <input type="number" id="edit_b" name="b" required 
                                   min="-20" max="20" oninput="atualizarEditPreview()">
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_c">Coeficiente c *</label>
                            <input type="number" id="edit_c" name="c" required 
                                   min="-20" max="20" oninput="atualizarEditPreview()">
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_dificuldade">Dificuldade *</label>
                            <select id="edit_dificuldade" name="dificuldade" required>
                                <option value="facil">Fácil</option>
                                <option value="medio">Médio</option>
                                <option value="dificil">Difícil</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">💾 Salvar Alterações</button>
                        <button type="button" class="btn-secondary" onclick="fecharModal('modal-editar')">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================================================
    MODAL DE CONFIRMAÇÃO DE EXCLUSÃO
    ============================================================ -->
    <div id="modal-excluir" class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-excluir-title" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-excluir-title">⚠️ Confirmar Exclusão</h2>
                <button class="modal-close" onclick="fecharModal('modal-excluir')" aria-label="Fechar">✕</button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir a equação <strong id="excluir_equacao_str"></strong>?</p>
                <p style="color:#888;font-size:14px;">Esta ação não pode ser desfeita.</p>
                <form id="form-excluir" method="POST" action="">
                    <input type="hidden" name="action" value="excluir">
                    <input type="hidden" name="id" id="excluir_id">
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-danger">🗑️ Excluir Equação</button>
                        <button type="button" class="btn-secondary" onclick="fecharModal('modal-excluir')">Cancelar</button>
                    </div>
                </form>
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
    <script src="../../public/js/gerenciar.js"></script>
    
    <script>
        // ============================================================
        // INICIALIZAÇÃO
        // ============================================================
        
        document.addEventListener('DOMContentLoaded', function() {
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
            
            // Inicializar preview do cadastro
            atualizarPreview();
        });

        // ============================================================
        // FUNÇÕES DE PREVIEW
        // ============================================================
        
        function atualizarPreview() {
            const a = document.getElementById('a').value || 'a';
            const b = document.getElementById('b').value || 'b';
            const c = document.getElementById('c').value || 'c';
            const sinal = parseInt(b) >= 0 ? '+' : '-';
            const preview = document.getElementById('preview-equacao');
            preview.textContent = `${a}x ${sinal} ${Math.abs(parseInt(b) || 0)} = ${c}`;
        }
        
        function atualizarEditPreview() {
            const a = document.getElementById('edit_a').value || 'a';
            const b = document.getElementById('edit_b').value || 'b';
            const c = document.getElementById('edit_c').value || 'c';
            const sinal = parseInt(b) >= 0 ? '+' : '-';
            const preview = document.getElementById('edit-preview-equacao');
            preview.textContent = `${a}x ${sinal} ${Math.abs(parseInt(b) || 0)} = ${c}`;
        }

        // ============================================================
        // FUNÇÕES DOS MODAIS
        // ============================================================
        
        function editarEquacao(id) {
            // Buscar dados da equação via AJAX
            fetch('../../controllers/AdminController.php?action=get_equacao&id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('edit_id').value = data.equacao.id;
                        document.getElementById('edit_a').value = data.equacao.a;
                        document.getElementById('edit_b').value = data.equacao.b;
                        document.getElementById('edit_c').value = data.equacao.c;
                        document.getElementById('edit_dificuldade').value = data.equacao.dificuldade;
                        
                        atualizarEditPreview();
                        document.getElementById('modal-editar').style.display = 'block';
                    } else {
                        alert('Erro ao carregar dados da equação.');
                    }
                })
                .catch(() => {
                    alert('Erro de conexão. Tente novamente.');
                });
        }
        
        function excluirEquacao(id, equacao_str) {
            document.getElementById('excluir_id').value = id;
            document.getElementById('excluir_equacao_str').textContent = equacao_str;
            document.getElementById('modal-excluir').style.display = 'block';
        }
        
        function fecharModal(id) {
            document.getElementById(id).style.display = 'none';
        }
        
        // Fechar modal clicando fora
        document.querySelectorAll('.modal').forEach(function(modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                }
            });
        });
        
        // Fechar modal com ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal').forEach(function(modal) {
                    if (modal.style.display === 'block') {
                        modal.style.display = 'none';
                    }
                });
            }
        });
    </script>
</body>
</html>