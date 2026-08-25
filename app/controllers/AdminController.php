<?php
/**
 * AdminController.php
 * Controlador para gerenciamento administrativo
 */

if (!class_exists('AdminController')) {

    if (!defined('BASE_URL')) {
        define('BASE_URL', 'index.php?view=');
    }

    if (!defined('VIEWS_PATH')) {
        define('VIEWS_PATH', __DIR__ . '/../views');
    }

    class AdminController
    {
        private $db;
        
        public function __construct()
        {
            // Carrega Database se necessário
            if (!class_exists('Database')) {
                require_once __DIR__ . '/../config/Database.php';
            }
            $this->db = Database::getInstance()->getConnection();
            
            // Verifica se é admin
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_perfil'] !== 'admin') {
                $_SESSION['login_error'] = 'Acesso restrito a administradores.';
                header('Location: index.php?view=login');
                exit;
            }
        }
        
        public function dashboard()
        {
            $viewPath = VIEWS_PATH . '/admin/dashboard.php';
            if (file_exists($viewPath)) {
                include $viewPath;
            } else {
                echo "<h1>Dashboard Admin</h1>";
                echo "<p>Bem-vindo, " . htmlspecialchars($_SESSION['usuario_nome'] ?? 'Admin') . "!</p>";
                echo "<p><a href='index.php?view=admin/gerenciar'>Gerenciar Usuários</a></p>";
                echo "<p><a href='index.php?view=admin/equacoes'>Gerenciar Equações</a></p>";
                echo "<p><a href='index.php?view=logout'>Sair</a></p>";
            }
        }
        
        public function gerenciarUsuarios()
        {
            // Busca todos os usuários
            $usuarios = [];
            try {
                $stmt = $this->db->query("
                    SELECT u.*, 
                           a.idade, a.nivel_tea, a.escola as escola_aluno, a.turma,
                           p.disciplina, p.escola as escola_professor, p.telefone
                    FROM usuarios u
                    LEFT JOIN alunos a ON u.id = a.usuario_id
                    LEFT JOIN professores p ON u.id = p.usuario_id
                    ORDER BY u.id DESC
                ");
                $usuarios = $stmt->fetchAll();
            } catch (Exception $e) {
                error_log("Erro ao buscar usuários: " . $e->getMessage());
            }
            
            // Verifica ambos os nomes de arquivo
            $viewPaths = [
                VIEWS_PATH . '/admin/gerenciar_usuarios.php',
                VIEWS_PATH . '/admin/gerenciar_usuario.php'
            ];
            
            $loaded = false;
            foreach ($viewPaths as $viewPath) {
                if (file_exists($viewPath)) {
                    include $viewPath;
                    $loaded = true;
                    break;
                }
            }
            
            if (!$loaded) {
                echo "<h1>Gerenciar Usuários</h1>";
                echo "<p>Arquivo de visualização não encontrado.</p>";
                echo "<p><a href='index.php?view=admin/dashboard'>Voltar ao Dashboard</a></p>";
            }
        }
        
        public function gerenciarEquacoes()
        {
            // Busca todas as equações
            $equacoes = [];
            try {
                $stmt = $this->db->query("SELECT * FROM equacoes ORDER BY dificuldade, id");
                $equacoes = $stmt->fetchAll();
            } catch (Exception $e) {
                error_log("Erro ao buscar equações: " . $e->getMessage());
            }
            
            // Define a view
            $viewPath = VIEWS_PATH . '/admin/gerenciar_equacoes.php';
            if (file_exists($viewPath)) {
                include $viewPath;
            } else {
                echo "<h1>Gerenciar Equações</h1>";
                echo "<p>Arquivo de visualização não encontrado.</p>";
                echo "<p><a href='index.php?view=admin/dashboard'>Voltar ao Dashboard</a></p>";
            }
        }
        
        public function criarUsuario()
        {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: index.php?view=admin/gerenciar');
                exit;
            }
            
            $nome = $_POST['nome'] ?? '';
            $email = $_POST['email'] ?? '';
            $senha = $_POST['senha'] ?? '';
            $tipo_perfil = $_POST['tipo_perfil'] ?? 'aluno';
            
            if (empty($nome) || empty($email) || empty($senha)) {
                $_SESSION['admin_error'] = 'Preencha todos os campos obrigatórios.';
                header('Location: index.php?view=admin/gerenciar');
                exit;
            }
            
            try {
                // Verifica se email já existe
                $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $_SESSION['admin_error'] = 'Este e-mail já está cadastrado.';
                    header('Location: index.php?view=admin/gerenciar');
                    exit;
                }
                
                $this->db->beginTransaction();
                
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("
                    INSERT INTO usuarios (nome, email, senha_hash, tipo_perfil, ativo)
                    VALUES (?, ?, ?, ?, 1)
                ");
                $stmt->execute([$nome, $email, $senha_hash, $tipo_perfil]);
                $usuario_id = $this->db->lastInsertId();
                
                if ($tipo_perfil === 'aluno') {
                    $idade = $_POST['idade'] ?? null;
                    $nivel_tea = $_POST['nivel_tea'] ?? null;
                    $escola = $_POST['escola'] ?? '';
                    $turma = $_POST['turma'] ?? '';
                    
                    if (empty($idade) || empty($nivel_tea)) {
                        throw new Exception('Idade e nível TEA são obrigatórios para alunos.');
                    }
                    
                    $stmt = $this->db->prepare("
                        INSERT INTO alunos (usuario_id, idade, nivel_tea, escola, turma)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$usuario_id, $idade, $nivel_tea, $escola, $turma]);
                    
                } elseif ($tipo_perfil === 'professor') {
                    $disciplina = $_POST['disciplina'] ?? 'Matemática';
                    $escola = $_POST['escola_professor'] ?? '';
                    $telefone = $_POST['telefone'] ?? '';
                    
                    $stmt = $this->db->prepare("
                        INSERT INTO professores (usuario_id, disciplina, escola, telefone)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$usuario_id, $disciplina, $escola, $telefone]);
                }
                
                $this->db->commit();
                $_SESSION['admin_success'] = "Usuário {$nome} criado com sucesso!";
                
            } catch (Exception $e) {
                $this->db->rollback();
                $_SESSION['admin_error'] = 'Erro ao criar usuário: ' . $e->getMessage();
            }
            
            header('Location: index.php?view=admin/gerenciar');
            exit;
        }
        
       /**
 * Exibe formulário de edição de usuário
 */
public function editarUsuario()
{
    $id = $_GET['id'] ?? 0;
    
    if ($id <= 0) {
        $_SESSION['admin_error'] = 'ID de usuário inválido.';
        header('Location: index.php?view=admin/gerenciar');
        exit;
    }
    
    // Não permite editar o próprio admin
    if ($id == $_SESSION['usuario_id']) {
        $_SESSION['admin_error'] = 'Você não pode editar seu próprio usuário.';
        header('Location: index.php?view=admin/gerenciar');
        exit;
    }
    
    try {
        $stmt = $this->db->prepare("
            SELECT u.*, 
                   a.idade, a.nivel_tea, a.escola as escola_aluno, a.turma,
                   p.disciplina, p.escola as escola_professor, p.telefone
            FROM usuarios u
            LEFT JOIN alunos a ON u.id = a.usuario_id
            LEFT JOIN professores p ON u.id = p.usuario_id
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        $usuario = $stmt->fetch();
        
        if (!$usuario) {
            $_SESSION['admin_error'] = 'Usuário não encontrado.';
            header('Location: index.php?view=admin/gerenciar');
            exit;
        }
        
        $viewPath = VIEWS_PATH . '/admin/editar_usuario.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            echo "<h1>Editar Usuário</h1>";
            echo "<pre>";
            print_r($usuario);
            echo "</pre>";
            echo "<p><a href='index.php?view=admin/gerenciar'>Voltar</a></p>";
        }
        
    } catch (Exception $e) {
        $_SESSION['admin_error'] = 'Erro ao buscar usuário: ' . $e->getMessage();
        header('Location: index.php?view=admin/gerenciar');
        exit;
    }
}
        
        public function excluirUsuario()
        {
            $usuario_id = $_GET['id'] ?? 0;
            
            if ($usuario_id <= 0) {
                $_SESSION['admin_error'] = 'ID de usuário inválido.';
                header('Location: index.php?view=admin/gerenciar');
                exit;
            }
            
            if ($usuario_id == $_SESSION['usuario_id']) {
                $_SESSION['admin_error'] = 'Você não pode excluir seu próprio usuário.';
                header('Location: index.php?view=admin/gerenciar');
                exit;
            }
            
            try {
                $this->db->beginTransaction();
                
                $stmt = $this->db->prepare("SELECT tipo_perfil FROM usuarios WHERE id = ?");
                $stmt->execute([$usuario_id]);
                $usuario = $stmt->fetch();
                
                if ($usuario) {
                    if ($usuario['tipo_perfil'] === 'aluno') {
                        $stmt = $this->db->prepare("DELETE FROM alunos WHERE usuario_id = ?");
                        $stmt->execute([$usuario_id]);
                    } elseif ($usuario['tipo_perfil'] === 'professor') {
                        $stmt = $this->db->prepare("DELETE FROM professores WHERE usuario_id = ?");
                        $stmt->execute([$usuario_id]);
                    }
                }
                
                $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt->execute([$usuario_id]);
                
                $this->db->commit();
                $_SESSION['admin_success'] = "Usuário excluído com sucesso!";
                
            } catch (Exception $e) {
                $this->db->rollback();
                $_SESSION['admin_error'] = 'Erro ao excluir usuário: ' . $e->getMessage();
            }
            
            header('Location: index.php?view=admin/gerenciar');
            exit;
        }
        
        public function criarEquacao()
        {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: index.php?view=admin/equacoes');
                exit;
            }
            
            $a = (int)($_POST['a'] ?? 0);
            $b = (int)($_POST['b'] ?? 0);
            $c = (int)($_POST['c'] ?? 0);
            $dificuldade = $_POST['dificuldade'] ?? 'facil';
            
            if ($a === 0) {
                $_SESSION['admin_error'] = 'O coeficiente "a" não pode ser zero.';
                header('Location: index.php?view=admin/equacoes');
                exit;
            }
            
            // Calcula solução: ax + b = c => x = (c - b) / a
            $solucao = ($c - $b) / $a;
            
            try {
                $stmt = $this->db->prepare("
                    INSERT INTO equacoes (a, b, c, solucao, dificuldade)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$a, $b, $c, $solucao, $dificuldade]);
                
                $_SESSION['admin_success'] = "Equação {$a}x + {$b} = {$c} criada com sucesso!";
                
            } catch (Exception $e) {
                $_SESSION['admin_error'] = 'Erro ao criar equação: ' . $e->getMessage();
            }
            
            header('Location: index.php?view=admin/equacoes');
            exit;
        }
        
        public function excluirEquacao()
        {
            $equacao_id = $_GET['id'] ?? 0;
            
            if ($equacao_id <= 0) {
                $_SESSION['admin_error'] = 'ID de equação inválido.';
                header('Location: index.php?view=admin/equacoes');
                exit;
            }
            
            try {
                $stmt = $this->db->prepare("DELETE FROM equacoes WHERE id = ?");
                $stmt->execute([$equacao_id]);
                
                $_SESSION['admin_success'] = "Equação excluída com sucesso!";
                
            } catch (Exception $e) {
                $_SESSION['admin_error'] = 'Erro ao excluir equação: ' . $e->getMessage();
            }
            
            header('Location: index.php?view=admin/equacoes');
            exit;
        }
        
       /**
 * Exibe formulário de edição de equação
 */
public function editarEquacao()
{
    $id = $_GET['id'] ?? 0;
    
    if ($id <= 0) {
        $_SESSION['admin_error'] = 'ID de equação inválido.';
        header('Location: index.php?view=admin/equacoes');
        exit;
    }
    
    try {
        $stmt = $this->db->prepare("SELECT * FROM equacoes WHERE id = ?");
        $stmt->execute([$id]);
        $equacao = $stmt->fetch();
        
        if (!$equacao) {
            $_SESSION['admin_error'] = 'Equação não encontrada.';
            header('Location: index.php?view=admin/equacoes');
            exit;
        }
        
        // Carrega a view de edição
        $viewPath = VIEWS_PATH . '/admin/editar_equacao.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            echo "<h1>Editar Equação</h1>";
            echo "<pre>";
            print_r($equacao);
            echo "</pre>";
            echo "<p><a href='index.php?view=admin/equacoes'>Voltar</a></p>";
        }
        
    } catch (Exception $e) {
        $_SESSION['admin_error'] = 'Erro ao buscar equação: ' . $e->getMessage();
        header('Location: index.php?view=admin/equacoes');
        exit;
    }
}

/**
 * Salva a edição da equação
 */
public function editarEquacaoSalvar()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?view=admin/equacoes');
        exit;
    }
    
    $id = (int)($_POST['id'] ?? 0);
    $a = (float)($_POST['a'] ?? 0);
    $b = (float)($_POST['b'] ?? 0);
    $c = (float)($_POST['c'] ?? 0);
    $dificuldade = $_POST['dificuldade'] ?? 'facil';
    
    if ($id <= 0 || $a == 0) {
        $_SESSION['admin_error'] = 'Dados inválidos. O coeficiente "a" não pode ser zero.';
        header('Location: index.php?view=admin/equacoes');
        exit;
    }
    
    // Calcula a solução: ax + b = c => x = (c - b) / a
    $solucao = ($c - $b) / $a;
    
    try {
        $stmt = $this->db->prepare("
            UPDATE equacoes 
            SET a = ?, b = ?, c = ?, solucao = ?, dificuldade = ?
            WHERE id = ?
        ");
        $stmt->execute([$a, $b, $c, $solucao, $dificuldade, $id]);
        
        $_SESSION['admin_success'] = "Equação atualizada com sucesso!";
        
    } catch (Exception $e) {
        $_SESSION['admin_error'] = 'Erro ao atualizar equação: ' . $e->getMessage();
    }
    
    header('Location: index.php?view=admin/equacoes');
    exit;
}

/**
 * Salva a edição do usuário
 */
public function editarUsuarioSalvar()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?view=admin/gerenciar');
        exit;
    }
    
    $id = (int)($_POST['id'] ?? 0);
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $tipo_perfil = $_POST['tipo_perfil'] ?? 'aluno';
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    
    if ($id <= 0 || empty($nome) || empty($email)) {
        $_SESSION['admin_error'] = 'Dados inválidos para edição.';
        header('Location: index.php?view=admin/gerenciar');
        exit;
    }
    
    // Não permite editar o próprio admin
    if ($id == $_SESSION['usuario_id']) {
        $_SESSION['admin_error'] = 'Você não pode editar seu próprio usuário.';
        header('Location: index.php?view=admin/gerenciar');
        exit;
    }
    
    try {
        $this->db->beginTransaction();
        
        // Verifica se email já existe (exceto o próprio)
        $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            $_SESSION['admin_error'] = 'Este e-mail já está em uso por outro usuário.';
            header('Location: index.php?view=admin/gerenciar');
            exit;
        }
        
        // Atualiza usuário
        $sql = "UPDATE usuarios SET nome = ?, email = ?, tipo_perfil = ?, ativo = ?";
        $params = [$nome, $email, $tipo_perfil, $ativo];
        
        if (!empty($senha)) {
            $sql .= ", senha_hash = ?";
            $params[] = password_hash($senha, PASSWORD_DEFAULT);
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        // Atualiza perfil específico
        if ($tipo_perfil === 'aluno') {
            $idade = (int)($_POST['idade'] ?? 0);
            $nivel_tea = $_POST['nivel_tea'] ?? 'suporte1';
            $escola = $_POST['escola'] ?? '';
            $turma = $_POST['turma'] ?? '';
            
            // Verifica se já existe registro
            $stmt = $this->db->prepare("SELECT id FROM alunos WHERE usuario_id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->fetch()) {
                $stmt = $this->db->prepare("
                    UPDATE alunos SET idade = ?, nivel_tea = ?, escola = ?, turma = ?
                    WHERE usuario_id = ?
                ");
                $stmt->execute([$idade, $nivel_tea, $escola, $turma, $id]);
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO alunos (usuario_id, idade, nivel_tea, escola, turma)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$id, $idade, $nivel_tea, $escola, $turma]);
            }
        }
        
        if ($tipo_perfil === 'professor') {
            $disciplina = $_POST['disciplina'] ?? 'Matemática';
            $escola = $_POST['escola'] ?? '';
            $telefone = $_POST['telefone'] ?? '';
            
            // Verifica se já existe registro
            $stmt = $this->db->prepare("SELECT id FROM professores WHERE usuario_id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->fetch()) {
                $stmt = $this->db->prepare("
                    UPDATE professores SET disciplina = ?, escola = ?, telefone = ?
                    WHERE usuario_id = ?
                ");
                $stmt->execute([$disciplina, $escola, $telefone, $id]);
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO professores (usuario_id, disciplina, escola, telefone)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$id, $disciplina, $escola, $telefone]);
            }
        }
        
        $this->db->commit();
        $_SESSION['admin_success'] = "Usuário atualizado com sucesso!";
        
    } catch (Exception $e) {
        $this->db->rollback();
        $_SESSION['admin_error'] = 'Erro ao atualizar usuário: ' . $e->getMessage();
    }
    
    header('Location: index.php?view=admin/gerenciar');
    exit;
}
    }
}