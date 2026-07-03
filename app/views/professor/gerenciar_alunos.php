<?php
/**
 * ============================================================
 * gerenciar_alunos.php
 * Página para gerenciar alunos do sistema.
 * 
 * FUNCIONALIDADES:
 * - Listar todos os alunos
 * - Cadastrar novos alunos
 * - Editar dados de alunos
 * - Resetar senha de alunos
 * - Excluir (desativar) alunos
 * - Visualizar progresso individual
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

require_once '../../models/Aluno.php';
require_once '../../models/Usuario.php';

// ============================================================
// 3. PROCESSAMENTO DE AÇÕES DO FORMULÁRIO
// ============================================================

$aluno = new Aluno();
$usuario = new Usuario();
$mensagem = '';
$tipo_mensagem = '';
$dados_aluno = null;

// ============================================================
// 3.1. CADASTRAR ALUNO
// ============================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'cadastrar') {
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = trim($_POST['senha'] ?? '');
        $idade = (int)($_POST['idade'] ?? 0);
        $nivel_tea = $_POST['nivel_tea'] ?? 'suporte1';
        $escola = trim($_POST['escola'] ?? '');
        $turma = trim($_POST['turma'] ?? '');
        
        // Validar dados
        if (empty($nome) || empty($email) || empty($senha) || $idade < 14 || $idade > 21) {
            $mensagem = 'Preencha todos os campos corretamente.';
            $tipo_mensagem = 'erro';
        } else {
            // Verificar se email já existe
            if ($usuario->getByEmail($email)) {
                $mensagem = 'Este e-mail já está cadastrado no sistema.';
                $tipo_mensagem = 'erro';
            } else {
                // Cadastrar aluno
                $resultado = $usuario->criar($nome, $email, $senha, 'aluno');
                if ($resultado) {
                    $usuario_id = $resultado;
                    
                    // Criar registro na tabela alunos
                    $sql = "INSERT INTO alunos (usuario_id, idade, nivel_tea, escola, turma) 
                            VALUES (:usuario_id, :idade, :nivel_tea, :escola, :turma)";
                    $db = Database::getInstance()->getConnection();
                    $stmt = $db->prepare($sql);
                    $stmt->execute([
                        ':usuario_id' => $usuario_id,
                        ':idade' => $idade,
                        ':nivel_tea' => $nivel_tea,
                        ':escola' => $escola,
                        ':turma' => $turma
                    ]);
                    
                    $mensagem = 'Aluno cadastrado com sucesso!';
                    $tipo_mensagem = 'sucesso';
                } else {
                    $mensagem = 'Erro ao cadastrar aluno. Tente novamente.';
                    $tipo_mensagem = 'erro';
                }
            }
        }
    }
    
    // ============================================================
    // 3.2. EDITAR ALUNO
    // ============================================================
    
    if ($action === 'editar') {
        $aluno_id = (int)$_POST['aluno_id'];
        $nome = trim($_POST['nome'] ?? '');
        $idade = (int)($_POST['idade'] ?? 0);
        $nivel_tea = $_POST['nivel_tea'] ?? 'suporte1';
        $escola = trim($_POST['escola'] ?? '');
        $turma = trim($_POST['turma'] ?? '');
        
        if (empty($nome) || $idade < 14 || $idade > 21) {
            $mensagem = 'Preencha todos os campos corretamente.';
            $tipo_mensagem = 'erro';
        } else {
            // Buscar dados atuais do aluno
            $dados = $aluno->getDadosCompletos($aluno_id);
            if ($dados) {
                // Atualizar nome na tabela usuarios
                $sql = "UPDATE usuarios SET nome = :nome WHERE id = :usuario_id";
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':nome' => $nome,
                    ':usuario_id' => $dados['usuario_id']
                ]);
                
                // Atualizar dados na tabela alunos
                $sql = "UPDATE alunos SET idade = :idade, nivel_tea = :nivel_tea, escola = :escola, turma = :turma 
                        WHERE id = :aluno_id";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':idade' => $idade,
                    ':nivel_tea' => $nivel_tea,
                    ':escola' => $escola,
                    ':turma' => $turma,
                    ':aluno_id' => $aluno_id
                ]);
                
                $mensagem = 'Dados do aluno atualizados com sucesso!';
                $tipo_mensagem = 'sucesso';
            } else {
                $mensagem = 'Aluno não encontrado.';
                $tipo_mensagem = 'erro';
            }
        }
    }
    
    // ============================================================
    // 3.3. RESETAR SENHA
    // ============================================================
    
    if ($action === 'resetar_senha') {
        $aluno_id = (int)$_POST['aluno_id'];
        $nova_senha = $_POST['nova_senha'] ?? '';
        
        if (empty($nova_senha) || strlen($nova_senha) < 4) {
            $mensagem = 'A senha deve ter pelo menos 4 caracteres.';
            $tipo_mensagem = 'erro';
        } else {
            $dados = $aluno->getDadosCompletos($aluno_id);
            if ($dados) {
                $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET senha_hash = :senha_hash WHERE id = :usuario_id";
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':senha_hash' => $senha_hash,
                    ':usuario_id' => $dados['usuario_id']
                ]);
                
                $mensagem = "Senha resetada com sucesso! Nova senha: <strong>{$nova_senha}</strong>";
                $tipo_mensagem = 'sucesso';
            } else {
                $mensagem = 'Aluno não encontrado.';
                $tipo_mensagem = 'erro';
            }
        }
    }
    
    // ============================================================
    // 3.4. EXCLUIR (DESATIVAR) ALUNO
    // ============================================================
    
    if ($action === 'excluir') {
        $aluno_id = (int)$_POST['aluno_id'];
        $dados = $aluno->getDadosCompletos($aluno_id);
        if ($dados) {
            $sql = "UPDATE usuarios SET ativo = 0 WHERE id = :usuario_id";
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare($sql);
            $stmt->execute([':usuario_id' => $dados['usuario_id']]);
            
            $mensagem = 'Aluno desativado com sucesso!';
            $tipo_mensagem = 'sucesso';
        } else {
            $mensagem = 'Aluno não encontrado.';
            $tipo_mensagem = 'erro';
        }
    }
}

// ============================================================
// 4. LISTAR ALUNOS
// ============================================================

$sql = "SELECT 
            u.id as usuario_id,
            u.nome,
            u.email,
            u.data_cadastro,
            u.ultimo_acesso,
            u.ativo,
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
        WHERE u.tipo_perfil = 'aluno'
        ORDER BY u.nome ASC";
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare($sql);
$stmt->execute();
$alunos = $stmt->fetchAll();

// ============================================================
// 5. VARIÁVEIS PARA A VIEW
// ============================================================

$page_title = 'Gerenciar Alunos - EquaTEA';

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
    
    <meta name="description" content="Gerenciar alunos - EquaTEA">
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
                <span aria-hidden="true">👨‍🎓</span> 
                Gerenciar Alunos
            </h1>
            <p class="page-subtitle">Cadastre, edite e acompanhe seus alunos</p>
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
        FORMULÁRIO DE CADASTRO
        ============================================================ -->
        <section class="form-section" aria-labelledby="cadastro-title">
            <h2 id="cadastro-title" class="section-title">
                <span aria-hidden="true">➕</span> 
                Cadastrar Novo Aluno
            </h2>
            
            <form id="form-cadastro" method="POST" action="" class="form-cadastro">
                <input type="hidden" name="action" value="cadastrar">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nome">Nome completo *</label>
                        <input type="text" id="nome" name="nome" required 
                               placeholder="Ex: João Silva" minlength="3">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">E-mail *</label>
                        <input type="email" id="email" name="email" required 
                               placeholder="Ex: joao@escola.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="senha">Senha *</label>
                        <input type="text" id="senha" name="senha" required 
                               placeholder="Mínimo 4 caracteres" minlength="4">
                        <small class="form-help">A senha será enviada ao aluno.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="idade">Idade *</label>
                        <input type="number" id="idade" name="idade" required 
                               placeholder="14-21" min="14" max="21">
                    </div>
                    
                    <div class="form-group">
                        <label for="nivel_tea">Nível de Suporte TEA *</label>
                        <select id="nivel_tea" name="nivel_tea" required>
                            <option value="suporte1">Suporte 1</option>
                            <option value="suporte2">Suporte 2</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="escola">Escola</label>
                        <input type="text" id="escola" name="escola" 
                               placeholder="Ex: Escola Modelo">
                    </div>
                    
                    <div class="form-group">
                        <label for="turma">Turma</label>
                        <input type="text" id="turma" name="turma" 
                               placeholder="Ex: 1º EM A">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <span aria-hidden="true">✅</span> Cadastrar Aluno
                    </button>
                    <button type="reset" class="btn-secondary">
                        <span aria-hidden="true">🔄</span> Limpar
                    </button>
                </div>
            </form>
        </section>

        <!-- ============================================================
        LISTA DE ALUNOS
        ============================================================ -->
        <section class="list-section" aria-labelledby="lista-title">
            <h2 id="lista-title" class="section-title">
                <span aria-hidden="true">📋</span> 
                Lista de Alunos
                <span class="badge-count"><?php echo count($alunos); ?></span>
            </h2>
            
            <?php if (empty($alunos)): ?>
                <div class="empty-state">
                    <span aria-hidden="true">📝</span>
                    <p>Nenhum aluno cadastrado ainda.</p>
                    <p class="empty-hint">Use o formulário acima para adicionar alunos.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="list-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Idade</th>
                                <th>Nível TEA</th>
                                <th>Equações</th>
                                <th>Concluídas</th>
                                <th>Erros</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alunos as $a): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($a['nome']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($a['email']); ?></td>
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
                                        <?php if ($a['ativo']): ?>
                                            <span class="status-ativo">✅ Ativo</span>
                                        <?php else: ?>
                                            <span class="status-inativo">❌ Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="acoes-botoes">
                                            <button class="btn-acao btn-editar" onclick="editarAluno(<?php echo $a['aluno_id']; ?>)" 
                                                    aria-label="Editar aluno">
                                                ✏️
                                            </button>
                                            <button class="btn-acao btn-senha" onclick="resetarSenha(<?php echo $a['aluno_id']; ?>, '<?php echo htmlspecialchars($a['nome']); ?>')" 
                                                    aria-label="Resetar senha">
                                                🔑
                                            </button>
                                            <?php if ($a['ativo']): ?>
                                                <button class="btn-acao btn-excluir" onclick="excluirAluno(<?php echo $a['aluno_id']; ?>, '<?php echo htmlspecialchars($a['nome']); ?>')" 
                                                        aria-label="Desativar aluno">
                                                    🗑️
                                                </button>
                                            <?php endif; ?>
                                            <a href="relatorio.php?aluno_id=<?php echo $a['aluno_id']; ?>" class="btn-acao btn-relatorio" 
                                               aria-label="Ver relatório">
                                                📊
                                            </a>
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
    MODAL DE EDIÇÃO
    ============================================================ -->
    <div id="modal-editar" class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-editar-title" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-editar-title">✏️ Editar Aluno</h2>
                <button class="modal-close" onclick="fecharModal('modal-editar')" aria-label="Fechar">✕</button>
            </div>
            <div class="modal-body">
                <form id="form-editar" method="POST" action="">
                    <input type="hidden" name="action" value="editar">
                    <input type="hidden" name="aluno_id" id="edit_aluno_id">
                    
                    <div class="form-group">
                        <label for="edit_nome">Nome completo *</label>
                        <input type="text" id="edit_nome" name="nome" required minlength="3">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_idade">Idade *</label>
                        <input type="number" id="edit_idade" name="idade" required min="14" max="21">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_nivel_tea">Nível de Suporte TEA *</label>
                        <select id="edit_nivel_tea" name="nivel_tea" required>
                            <option value="suporte1">Suporte 1</option>
                            <option value="suporte2">Suporte 2</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_escola">Escola</label>
                        <input type="text" id="edit_escola" name="escola">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_turma">Turma</label>
                        <input type="text" id="edit_turma" name="turma">
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
    MODAL DE RESET DE SENHA
    ============================================================ -->
    <div id="modal-senha" class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-senha-title" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-senha-title">🔑 Resetar Senha</h2>
                <button class="modal-close" onclick="fecharModal('modal-senha')" aria-label="Fechar">✕</button>
            </div>
            <div class="modal-body">
                <p>Resetar a senha do aluno: <strong id="senha_aluno_nome"></strong></p>
                <form id="form-senha" method="POST" action="">
                    <input type="hidden" name="action" value="resetar_senha">
                    <input type="hidden" name="aluno_id" id="senha_aluno_id">
                    
                    <div class="form-group">
                        <label for="nova_senha">Nova senha *</label>
                        <input type="text" id="nova_senha" name="nova_senha" required minlength="4" 
                               placeholder="Mínimo 4 caracteres">
                        <small class="form-help">A nova senha será exibida após a confirmação.</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">🔑 Resetar Senha</button>
                        <button type="button" class="btn-secondary" onclick="fecharModal('modal-senha')">Cancelar</button>
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
                <p>Tem certeza que deseja desativar o aluno <strong id="excluir_aluno_nome"></strong>?</p>
                <p style="color:#888;font-size:14px;">O aluno poderá ser reativado posteriormente.</p>
                <form id="form-excluir" method="POST" action="">
                    <input type="hidden" name="action" value="excluir">
                    <input type="hidden" name="aluno_id" id="excluir_aluno_id">
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-danger">🗑️ Desativar Aluno</button>
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
        });

        // ============================================================
        // FUNÇÕES DOS MODAIS
        // ============================================================
        
        function editarAluno(aluno_id) {
            // Buscar dados do aluno via AJAX
            fetch('../../controllers/AdminController.php?action=get_aluno&id=' + aluno_id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('edit_aluno_id').value = data.aluno.id;
                        document.getElementById('edit_nome').value = data.aluno.nome;
                        document.getElementById('edit_idade').value = data.aluno.idade;
                        document.getElementById('edit_nivel_tea').value = data.aluno.nivel_tea;
                        document.getElementById('edit_escola').value = data.aluno.escola || '';
                        document.getElementById('edit_turma').value = data.aluno.turma || '';
                        
                        document.getElementById('modal-editar').style.display = 'block';
                    } else {
                        alert('Erro ao carregar dados do aluno.');
                    }
                })
                .catch(() => {
                    alert('Erro de conexão. Tente novamente.');
                });
        }
        
        function resetarSenha(aluno_id, nome) {
            document.getElementById('senha_aluno_id').value = aluno_id;
            document.getElementById('senha_aluno_nome').textContent = nome;
            document.getElementById('nova_senha').value = '';
            document.getElementById('modal-senha').style.display = 'block';
        }
        
        function excluirAluno(aluno_id, nome) {
            document.getElementById('excluir_aluno_id').value = aluno_id;
            document.getElementById('excluir_aluno_nome').textContent = nome;
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