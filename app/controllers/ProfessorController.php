<?php
/**
 * ProfessorController.php
 * Controlador para as funcionalidades do professor - Versão Completa
 */

if (!class_exists('ProfessorController')) {

    class ProfessorController
    {
        private $db;
        
        public function __construct()
        {
            // Carrega Database
            if (!class_exists('Database')) {
                require_once __DIR__ . '/../config/Database.php';
            }
            $this->db = Database::getInstance()->getConnection();
            
            // Verifica se é professor
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            if (!isset($_SESSION['usuario_id']) || ($_SESSION['tipo_perfil'] ?? '') !== 'professor') {
                $_SESSION['login_error'] = 'Acesso restrito a professores.';
                header('Location: index.php?view=login');
                exit;
            }
        }
        
        /**
         * Dashboard do professor com estatísticas completas
         */
        public function dashboard()
        {
            // Busca dados dos alunos com estatísticas
            $dados_alunos = $this->getDadosAlunosComEstatisticas();
            $dados_equacoes = $this->getDadosEquacoes();
            
            $total_alunos = count($dados_alunos);
            $total_equacoes = count($dados_equacoes);
            $total_equacoes_resolvidas = $this->getTotalEquacoesResolvidas();
            $total_tentativas = $this->getTotalTentativas();
            $total_erros = $this->getTotalErros();
            $erros_comuns = $this->getErrosComuns();
            
            // Define constantes para a view
            if (!defined('VIEWS_PATH')) {
                define('VIEWS_PATH', dirname(__DIR__) . '/views');
            }
            
            // Carrega a view
            $view_path = VIEWS_PATH . '/professor/dashboard.php';
            if (file_exists($view_path)) {
                include_once $view_path;
            } else {
                echo "<h2>Erro: View do Dashboard Professor não encontrada.</h2>";
                echo "<p>Caminho: {$view_path}</p>";
            }
        }
        
        /**
         * Gerenciar alunos
         */
        public function gerenciarAlunos()
        {
            // Processa ações via GET
            $action = $_GET['action'] ?? null;
            
            if ($action === 'deletar') {
                $this->deletarAluno();
                return;
            }
            
            if ($action === 'resetar_senha') {
                $this->resetarSenha();
                return;
            }
            
            // Busca alunos
            $alunos = $this->getDadosAlunosComEstatisticas();
            
            // Define constantes para a view
            if (!defined('VIEWS_PATH')) {
                define('VIEWS_PATH', dirname(__DIR__) . '/views');
            }
            
            // Carrega a view
            $view_path = VIEWS_PATH . '/professor/alunos.php';
            if (file_exists($view_path)) {
                // Passa os dados para a view
                $dados_alunos = $alunos;
                include_once $view_path;
            } else {
                echo "<h2>Erro: View de Alunos não encontrada.</h2>";
                echo "<p>Caminho: {$view_path}</p>";
            }
        }
        
        /**
         * Gerenciar equações - CORRIGIDO
         */
        public function gerenciarEquacoes()
        {
            // Processa ações via GET
            $action = $_GET['action'] ?? null;
            
            if ($action === 'deletar') {
                $this->deletarEquacao();
                return;
            }
            
            // Busca equações
            $dados_equacoes = $this->getDadosEquacoes();
            
            // Define constantes para a view
            if (!defined('VIEWS_PATH')) {
                define('VIEWS_PATH', dirname(__DIR__) . '/views');
            }
            
            // Carrega a view
            $view_path = VIEWS_PATH . '/professor/gerenciar_equacoes.php';
            
            // Se não encontrar, tenta o caminho alternativo
            if (!file_exists($view_path)) {
                $view_path = VIEWS_PATH . '/gerenciar_equacoes.php';
            }
            
            if (file_exists($view_path)) {
                // Extrai as variáveis para a view
                extract(['dados_equacoes' => $dados_equacoes]);
                include_once $view_path;
            } else {
                echo "<h2>Erro: View de Equações não encontrada.</h2>";
                echo "<p>Caminhos procurados:</p>";
                echo "<ul>";
                echo "<li>" . VIEWS_PATH . "/professor/gerenciar_equacoes.php</li>";
                echo "<li>" . VIEWS_PATH . "/gerenciar_equacoes.php</li>";
                echo "</ul>";
            }
        }
        
        /**
         * Relatório de erros
         */
        public function relatorio()
        {
            // Busca dados para o relatório
            $dados_relatorio = $this->getDadosRelatorio();
            $dados_alunos = $this->getDadosAlunosComEstatisticas();
            
            // Define constantes para a view
            if (!defined('VIEWS_PATH')) {
                define('VIEWS_PATH', dirname(__DIR__) . '/views');
            }
            
            // Carrega a view
            $view_path = VIEWS_PATH . '/professor/relatorio.php';
            if (file_exists($view_path)) {
                include_once $view_path;
            } else {
                echo "<h2>Erro: View de Relatório não encontrada.</h2>";
                echo "<p>Caminho: {$view_path}</p>";
            }
        }
        
        /**
         * Editar aluno - Exibe formulário
         */
        public function editarAluno()
        {
            $id = $_GET['id'] ?? 0;
            
            if ($id <= 0) {
                $_SESSION['admin_error'] = 'ID de aluno inválido.';
                header('Location: index.php?view=gerenciar_alunos');
                exit;
            }
            
            $aluno = $this->getAlunoById($id);
            
            if (!$aluno) {
                $_SESSION['admin_error'] = 'Aluno não encontrado.';
                header('Location: index.php?view=gerenciar_alunos');
                exit;
            }
            
            // Define constantes para a view
            if (!defined('VIEWS_PATH')) {
                define('VIEWS_PATH', dirname(__DIR__) . '/views');
            }
            
            // Carrega a view
            $view_path = VIEWS_PATH . '/professor/editar_aluno.php';
            if (file_exists($view_path)) {
                include_once $view_path;
            } else {
                echo "<h2>Erro: View de Edição de Aluno não encontrada.</h2>";
                echo "<p>Caminho: {$view_path}</p>";
            }
        }
        
        /**
         * Salvar edição de aluno
         */
        public function salvarEdicao()
        {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: index.php?view=gerenciar_alunos');
                exit;
            }
            
            $id = (int)($_POST['id'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $nivel_tea = $_POST['nivel_tea'] ?? '';
            $turma = $_POST['turma'] ?? '';
            
            if ($id <= 0 || empty($nome) || empty($email)) {
                $_SESSION['admin_error'] = 'Dados inválidos.';
                header('Location: index.php?view=gerenciar_alunos');
                exit;
            }
            
            try {
                // Atualiza usuário
                $stmt = $this->db->prepare("UPDATE usuarios SET nome = ?, email = ? WHERE id = ?");
                $stmt->execute([$nome, $email, $id]);
                
                // Atualiza aluno
                $stmt = $this->db->prepare("UPDATE alunos SET nivel_tea = ?, turma = ? WHERE usuario_id = ?");
                $stmt->execute([$nivel_tea, $turma, $id]);
                
                $_SESSION['admin_success'] = 'Aluno atualizado com sucesso!';
            } catch (Exception $e) {
                $_SESSION['admin_error'] = 'Erro ao atualizar aluno: ' . $e->getMessage();
            }
            
            header('Location: index.php?view=gerenciar_alunos');
            exit;
        }
        
        /**
         * Deletar aluno
         */
        public function deletarAluno()
        {
            $id = $_GET['id'] ?? 0;
            
            if ($id <= 0) {
                $_SESSION['admin_error'] = 'ID de aluno inválido.';
                header('Location: index.php?view=gerenciar_alunos');
                exit;
            }
            
            try {
                $this->db->beginTransaction();
                
                // Remove aluno
                $stmt = $this->db->prepare("DELETE FROM alunos WHERE usuario_id = ?");
                $stmt->execute([$id]);
                
                // Remove usuário
                $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ? AND tipo_perfil = 'aluno'");
                $stmt->execute([$id]);
                
                $this->db->commit();
                $_SESSION['admin_success'] = 'Aluno excluído com sucesso!';
            } catch (Exception $e) {
                $this->db->rollback();
                $_SESSION['admin_error'] = 'Erro ao excluir aluno: ' . $e->getMessage();
            }
            
            header('Location: index.php?view=gerenciar_alunos');
            exit;
        }
        
        /**
         * Resetar senha do aluno
         */
        public function resetarSenha()
        {
            $id = $_GET['id'] ?? 0;
            
            if ($id <= 0) {
                $_SESSION['admin_error'] = 'ID de aluno inválido.';
                header('Location: index.php?view=gerenciar_alunos');
                exit;
            }
            
            try {
                $nova_senha = '123456';
                $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                
                $stmt = $this->db->prepare("UPDATE usuarios SET senha_hash = ? WHERE id = ? AND tipo_perfil = 'aluno'");
                $stmt->execute([$senha_hash, $id]);
                
                $_SESSION['admin_success'] = "Senha resetada para '$nova_senha' com sucesso!";
            } catch (Exception $e) {
                $_SESSION['admin_error'] = 'Erro ao resetar senha: ' . $e->getMessage();
            }
            
            header('Location: index.php?view=gerenciar_alunos');
            exit;
        }
        
        /**
         * Cadastrar aluno via POST
         */
        public function cadastrarAluno()
        {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: index.php?view=gerenciar_alunos');
                exit;
            }
            
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $senha = $_POST['senha'] ?? '';
            $idade = (int)($_POST['idade'] ?? 0);
            $nivel_tea = $_POST['nivel_tea'] ?? 'suporte1';
            $escola = $_POST['escola'] ?? '';
            $turma = $_POST['turma'] ?? '';
            
            if (empty($nome) || empty($email) || empty($senha) || $idade < 14 || $idade > 21) {
                $_SESSION['admin_error'] = 'Preencha todos os campos corretamente.';
                header('Location: index.php?view=gerenciar_alunos');
                exit;
            }
            
            try {
                // Verifica se email já existe
                $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $_SESSION['admin_error'] = 'Este e-mail já está cadastrado.';
                    header('Location: index.php?view=gerenciar_alunos');
                    exit;
                }
                
                $this->db->beginTransaction();
                
                // Cria usuário
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("
                    INSERT INTO usuarios (nome, email, senha_hash, tipo_perfil, ativo)
                    VALUES (?, ?, ?, 'aluno', 1)
                ");
                $stmt->execute([$nome, $email, $senha_hash]);
                $usuario_id = $this->db->lastInsertId();
                
                // Cria aluno
                $stmt = $this->db->prepare("
                    INSERT INTO alunos (usuario_id, idade, nivel_tea, escola, turma)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$usuario_id, $idade, $nivel_tea, $escola, $turma]);
                
                $this->db->commit();
                $_SESSION['admin_success'] = "Aluno $nome cadastrado com sucesso!";
            } catch (Exception $e) {
                $this->db->rollback();
                $_SESSION['admin_error'] = 'Erro ao cadastrar aluno: ' . $e->getMessage();
            }
            
            header('Location: index.php?view=gerenciar_alunos');
            exit;
        }
        
        /**
         * Editar equação - Exibe formulário
         */
        public function editarEquacao()
        {
            $id = $_GET['id'] ?? 0;
            
            if ($id <= 0) {
                $_SESSION['admin_error'] = 'ID de equação inválido.';
                header('Location: index.php?view=gerenciar_equacoes');
                exit;
            }
            
            $equacao = $this->getEquacaoById($id);
            
            if (!$equacao) {
                $_SESSION['admin_error'] = 'Equação não encontrada.';
                header('Location: index.php?view=gerenciar_equacoes');
                exit;
            }
            
            // Define constantes para a view
            if (!defined('VIEWS_PATH')) {
                define('VIEWS_PATH', dirname(__DIR__) . '/views');
            }
            
            // Carrega a view
            $view_path = VIEWS_PATH . '/professor/editar_equacao.php';
            if (file_exists($view_path)) {
                include_once $view_path;
            } else {
                echo "<h2>Erro: View de Edição de Equação não encontrada.</h2>";
                echo "<p>Caminho: {$view_path}</p>";
            }
        }
        
        /**
         * Salvar edição de equação - CORRIGIDO
         */
        public function salvarEdicaoEquacao()
        {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: index.php?view=gerenciar_equacoes');
                exit;
            }
            
            $id = (int)($_POST['id'] ?? 0);
            $a = (float)($_POST['coef_a'] ?? 0);
            $b = (float)($_POST['coef_b'] ?? 0);
            $c = (float)($_POST['coef_c'] ?? 0);
            $dificuldade = $_POST['dificuldade'] ?? 'facil';
            
            // Converte dificuldade para o formato do banco
            $dificuldadeMap = [
                'Fácil' => 'facil',
                'Médio' => 'medio',
                'Difícil' => 'dificil'
            ];
            $dificuldade = $dificuldadeMap[$dificuldade] ?? 'facil';
            
            if ($id <= 0 || $a == 0) {
                $_SESSION['admin_error'] = 'Dados inválidos. O coeficiente "a" não pode ser zero.';
                header('Location: index.php?view=gerenciar_equacoes');
                exit;
            }
            
            try {
                $solucao = ($c - $b) / $a;
                
                $stmt = $this->db->prepare("
                    UPDATE equacoes 
                    SET a = ?, b = ?, c = ?, solucao = ?, dificuldade = ?
                    WHERE id = ?
                ");
                $stmt->execute([$a, $b, $c, $solucao, $dificuldade, $id]);
                
                $_SESSION['admin_success'] = 'Equação atualizada com sucesso!';
            } catch (Exception $e) {
                $_SESSION['admin_error'] = 'Erro ao atualizar equação: ' . $e->getMessage();
            }
            
            header('Location: index.php?view=gerenciar_equacoes');
            exit;
        }
        
        /**
         * Deletar equação
         */
        public function deletarEquacao()
        {
            $id = $_GET['id'] ?? 0;
            
            if ($id <= 0) {
                $_SESSION['admin_error'] = 'ID de equação inválido.';
                header('Location: index.php?view=gerenciar_equacoes');
                exit;
            }
            
            try {
                $stmt = $this->db->prepare("DELETE FROM equacoes WHERE id = ?");
                $stmt->execute([$id]);
                
                $_SESSION['admin_success'] = 'Equação excluída com sucesso!';
            } catch (Exception $e) {
                $_SESSION['admin_error'] = 'Erro ao excluir equação: ' . $e->getMessage();
            }
            
            header('Location: index.php?view=gerenciar_equacoes');
            exit;
        }
        
        /**
         * Cadastrar equação via POST
         */
        public function cadastrarEquacao()
        {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: index.php?view=gerenciar_equacoes');
                exit;
            }
            
            $a = (float)($_POST['a'] ?? 0);
            $b = (float)($_POST['b'] ?? 0);
            $c = (float)($_POST['c'] ?? 0);
            $dificuldade = $_POST['dificuldade'] ?? 'facil';
            
            if ($a == 0) {
                $_SESSION['admin_error'] = 'O coeficiente "a" não pode ser zero.';
                header('Location: index.php?view=gerenciar_equacoes');
                exit;
            }
            
            try {
                $solucao = ($c - $b) / $a;
                
                $stmt = $this->db->prepare("
                    INSERT INTO equacoes (a, b, c, solucao, dificuldade)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$a, $b, $c, $solucao, $dificuldade]);
                
                $_SESSION['admin_success'] = "Equação {$a}x + {$b} = {$c} criada com sucesso!";
            } catch (Exception $e) {
                $_SESSION['admin_error'] = 'Erro ao criar equação: ' . $e->getMessage();
            }
            
            header('Location: index.php?view=gerenciar_equacoes');
            exit;
        }
        
        // ===== MÉTODOS DE CONSULTA AO BANCO =====
        
        /**
         * Busca todos os alunos com estatísticas
         */
        private function getDadosAlunosComEstatisticas()
        {
            try {
                $stmt = $this->db->query("
                    SELECT u.*, a.idade, a.nivel_tea, a.escola, a.turma,
                           (SELECT COUNT(*) FROM progresso_aluno WHERE aluno_id = a.id AND concluida = 1) as total_equacoes,
                           (SELECT COUNT(*) FROM progresso_tentativas WHERE aluno_id = a.id) as total_tentativas,
                           (SELECT MAX(data_tentativa) FROM progresso_tentativas WHERE aluno_id = a.id) as ultima_atividade
                    FROM usuarios u
                    INNER JOIN alunos a ON u.id = a.usuario_id
                    WHERE u.tipo_perfil = 'aluno' AND u.ativo = 1
                    ORDER BY u.nome
                ");
                $alunos = $stmt->fetchAll();
                
                // Calcula taxa de acerto e nível para cada aluno
                foreach ($alunos as &$aluno) {
                    $aluno_id = $aluno['id'] ?? null;
                    if ($aluno_id) {
                        // Taxa de acerto
                        $stmt = $this->db->prepare("
                            SELECT 
                                COUNT(*) as total,
                                SUM(correto) as acertos
                            FROM progresso_tentativas
                            WHERE aluno_id = (SELECT id FROM alunos WHERE usuario_id = ?)
                        ");
                        $stmt->execute([$aluno_id]);
                        $result = $stmt->fetch();
                        
                        $total = (int)($result['total'] ?? 0);
                        $acertos = (int)($result['acertos'] ?? 0);
                        
                        if ($total > 0) {
                            $aluno['taxa_acerto'] = round(($acertos / $total) * 100) . '%';
                        } else {
                            $aluno['taxa_acerto'] = '0%';
                        }
                        
                        // Nível
                        $total_resolvidas = (int)($aluno['total_equacoes'] ?? 0);
                        if ($total_resolvidas >= 30) {
                            $aluno['nivel'] = 'Avançado';
                        } elseif ($total_resolvidas >= 20) {
                            $aluno['nivel'] = 'Intermediário Avançado';
                        } elseif ($total_resolvidas >= 10) {
                            $aluno['nivel'] = 'Intermediário';
                        } elseif ($total_resolvidas >= 5) {
                            $aluno['nivel'] = 'Iniciante Avançado';
                        } else {
                            $aluno['nivel'] = 'Básico';
                        }
                        
                        // Última atividade
                        if (!empty($aluno['ultima_atividade'])) {
                            $aluno['ultima_atividade'] = date('d/m/Y H:i', strtotime($aluno['ultima_atividade']));
                        } else {
                            $aluno['ultima_atividade'] = '-';
                        }
                    }
                }
                
                return $alunos;
            } catch (Exception $e) {
                error_log("Erro ao buscar alunos: " . $e->getMessage());
                return [];
            }
        }
        
        /**
         * Busca um aluno por ID
         */
        private function getAlunoById($id)
        {
            try {
                $stmt = $this->db->prepare("
                    SELECT u.*, a.idade, a.nivel_tea, a.escola, a.turma
                    FROM usuarios u
                    INNER JOIN alunos a ON u.id = a.usuario_id
                    WHERE u.id = ? AND u.tipo_perfil = 'aluno'
                ");
                $stmt->execute([$id]);
                return $stmt->fetch();
            } catch (Exception $e) {
                error_log("Erro ao buscar aluno: " . $e->getMessage());
                return null;
            }
        }
        
        /**
         * Busca todas as equações
         */
        private function getDadosEquacoes()
        {
            try {
                $stmt = $this->db->query("
                    SELECT * FROM equacoes 
                    ORDER BY 
                        FIELD(dificuldade, 'facil', 'medio', 'dificil'),
                        id
                ");
                return $stmt->fetchAll();
            } catch (Exception $e) {
                error_log("Erro ao buscar equações: " . $e->getMessage());
                return [];
            }
        }
        
        /**
         * Busca uma equação por ID
         */
        private function getEquacaoById($id)
        {
            try {
                $stmt = $this->db->prepare("SELECT * FROM equacoes WHERE id = ?");
                $stmt->execute([$id]);
                return $stmt->fetch();
            } catch (Exception $e) {
                error_log("Erro ao buscar equação: " . $e->getMessage());
                return null;
            }
        }
        
        /**
         * Busca dados para o relatório de erros
         */
        private function getDadosRelatorio()
        {
            try {
                $stmt = $this->db->query("
                    SELECT 
                        r.*,
                        u.nome as aluno,
                        CONCAT(
                            CASE WHEN e.a = 1 THEN 'x' 
                                 WHEN e.a = -1 THEN '-x' 
                                 ELSE CONCAT(e.a, 'x') 
                            END,
                            CASE WHEN e.b > 0 THEN CONCAT(' + ', e.b)
                                 WHEN e.b < 0 THEN CONCAT(' - ', ABS(e.b))
                                 ELSE '' 
                            END,
                            ' = ', e.c
                        ) as equacao,
                        e.id as equacao_id,
                        a.id as aluno_id
                    FROM registro_erros r
                    INNER JOIN alunos a ON r.aluno_id = a.id
                    INNER JOIN usuarios u ON a.usuario_id = u.id
                    INNER JOIN equacoes e ON r.equacao_id = e.id
                    ORDER BY r.data_erro DESC
                    LIMIT 100
                ");
                return $stmt->fetchAll();
            } catch (Exception $e) {
                error_log("Erro ao buscar relatório: " . $e->getMessage());
                return [];
            }
        }
        
        /**
         * Total de equações resolvidas
         */
        private function getTotalEquacoesResolvidas()
        {
            try {
                $stmt = $this->db->query("SELECT COUNT(*) as total FROM progresso_aluno WHERE concluida = 1");
                $result = $stmt->fetch();
                return (int)($result['total'] ?? 0);
            } catch (Exception $e) {
                return 0;
            }
        }
        
        /**
         * Total de tentativas
         */
        private function getTotalTentativas()
        {
            try {
                $stmt = $this->db->query("SELECT COUNT(*) as total FROM progresso_tentativas");
                $result = $stmt->fetch();
                return (int)($result['total'] ?? 0);
            } catch (Exception $e) {
                return 0;
            }
        }
        
        /**
         * Total de erros
         */
        private function getTotalErros()
        {
            try {
                $stmt = $this->db->query("SELECT COUNT(*) as total FROM registro_erros");
                $result = $stmt->fetch();
                return (int)($result['total'] ?? 0);
            } catch (Exception $e) {
                return 0;
            }
        }
        
        /**
         * Erros mais comuns
         */
        private function getErrosComuns()
        {
            try {
                $stmt = $this->db->query("
                    SELECT 
                        tipo_erro,
                        COUNT(*) as quantidade,
                        CONCAT(
                            CASE WHEN e.a = 1 THEN 'x' 
                                 WHEN e.a = -1 THEN '-x' 
                                 ELSE CONCAT(e.a, 'x') 
                            END,
                            CASE WHEN e.b > 0 THEN CONCAT(' + ', e.b)
                                 WHEN e.b < 0 THEN CONCAT(' - ', ABS(e.b))
                                 ELSE '' 
                            END,
                            ' = ', e.c
                        ) as equacao
                    FROM registro_erros r
                    INNER JOIN equacoes e ON r.equacao_id = e.id
                    GROUP BY tipo_erro
                    ORDER BY quantidade DESC
                    LIMIT 5
                ");
                return $stmt->fetchAll();
            } catch (Exception $e) {
                return [];
            }
        }
    }
}