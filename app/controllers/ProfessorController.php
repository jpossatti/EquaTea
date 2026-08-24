<?php
/**
 * ProfessorController.php
 * Controlador para as funcionalidades do professor.
 */
class ProfessorController
{
    private $aluno;
    private $professor;
    private $equacao;
    private $progresso;
    private $registroErro;
    private $db;
    
    public function __construct()
    {
        $base_dir = dirname(__DIR__);
        
        $models = ['Aluno.php', 'Professor.php', 'Equacao.php', 'Progresso.php', 'ProgressoAluno.php', 'RegistroErro.php'];
        foreach ($models as $model_file) {
            $path = $base_dir . '/models/' . $model_file;
            if (file_exists($path)) {
                require_once $path;
            }
        }
        
        try {
            $this->db = Database::getInstance()->getConnection();
        } catch (Exception $e) {
            $this->db = null;
        }
        
        $this->aluno = class_exists('Aluno') ? new Aluno() : null;
        $this->professor = class_exists('Professor') ? new Professor() : null;
        $this->equacao = class_exists('Equacao') ? new Equacao() : null;
        $this->progresso = class_exists('Progresso') ? new Progresso() : null;
        $this->progressoAluno = class_exists('ProgressoAluno') ? new ProgressoAluno() : null;
        $this->registroErro = class_exists('RegistroErro') ? new RegistroErro() : null;
    }
    
    /**
     * Dashboard do professor com estatísticas completas
     */
    public function dashboard()
    {
        // ===== CONTROLE DE SESSÃO =====
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id']) || ($_SESSION['tipo_perfil'] ?? '') !== 'professor') {
            $_SESSION['login_error'] = 'Acesso restrito a professores.';
            header('Location: index.php?view=login');
            exit;
        }
        // ===== FIM CONTROLE DE SESSÃO =====
        
        // ===== 1. BUSCAR DADOS DOS ALUNOS COM ESTATÍSTICAS =====
        $dados_alunos = [];
        $total_alunos = 0;
        $total_equacoes_resolvidas = 0;
        $total_tentativas = 0;
        
        if ($this->aluno) {
            try {
                // Busca todos os alunos
                $alunos = $this->aluno->listarTodos();
                
                if (!empty($alunos) && is_array($alunos)) {
                    foreach ($alunos as &$aluno) {
                        $aluno_id = isset($aluno['id']) ? $aluno['id'] : (isset($aluno['aluno_id']) ? $aluno['aluno_id'] : null);
                        
                        if ($aluno_id) {
                            // Busca estatísticas do aluno
                            $estatisticas = $this->getEstatisticasAluno($aluno_id);
                            
                            $aluno['total_equacoes'] = $estatisticas['total_resolvidas'] ?? 0;
                            $aluno['total_tentativas'] = $estatisticas['total_tentativas'] ?? 0;
                            $aluno['taxa_acerto'] = $estatisticas['taxa_acerto'] ?? '0%';
                            $aluno['nivel'] = $estatisticas['nivel'] ?? 'Básico';
                            $aluno['ultima_atividade'] = $estatisticas['ultima_atividade'] ?? '-';
                            
                            // Acumula totais
                            $total_equacoes_resolvidas += ($aluno['total_equacoes'] ?? 0);
                            $total_tentativas += ($aluno['total_tentativas'] ?? 0);
                        } else {
                            $aluno['total_equacoes'] = 0;
                            $aluno['total_tentativas'] = 0;
                            $aluno['taxa_acerto'] = '0%';
                            $aluno['nivel'] = 'Básico';
                            $aluno['ultima_atividade'] = '-';
                        }
                    }
                    
                    $dados_alunos = $alunos;
                    $total_alunos = count($dados_alunos);
                }
            } catch (Exception $e) {
                error_log("Erro ao buscar alunos: " . $e->getMessage());
                $dados_alunos = [];
            }
        }
        
        // ===== 2. BUSCAR EQUAÇÕES =====
        $dados_equacoes = [];
        $total_equacoes = 0;
        
        if ($this->equacao) {
            try {
                $dados_equacoes = $this->equacao->buscarTodas();
                if (!is_array($dados_equacoes)) {
                    $dados_equacoes = [];
                }
                $total_equacoes = count($dados_equacoes);
            } catch (Exception $e) {
                error_log("Erro ao buscar equações: " . $e->getMessage());
                $dados_equacoes = [];
            }
        }
        
        // ===== 3. BUSCAR ERROS =====
        $total_erros = 0;
        if ($this->registroErro) {
            try {
                $erros = $this->registroErro->getEstatisticas();
                if (!empty($erros) && is_array($erros)) {
                    foreach ($erros as $e) {
                        $total_erros += ($e['quantidade'] ?? 0);
                    }
                }
            } catch (Exception $e) {
                error_log("Erro ao buscar erros: " . $e->getMessage());
            }
        }
        
        // ===== 4. BUSCAR ERROS COMUNS =====
        $erros_comuns = [];
        if ($this->registroErro) {
            try {
                $erros_comuns = $this->registroErro->getEstatisticas();
                if (!is_array($erros_comuns)) {
                    $erros_comuns = [];
                }
            } catch (Exception $e) {
                $erros_comuns = [];
            }
        }
        
        // ===== 5. DADOS PARA VIEW =====
        $dados = [
            'dados_alunos' => $dados_alunos,
            'dados_equacoes' => $dados_equacoes,
            'erros_comuns' => $erros_comuns,
            'total_alunos' => $total_alunos,
            'total_equacoes' => $total_equacoes,
            'total_equacoes_resolvidas' => $total_equacoes_resolvidas,
            'total_tentativas' => $total_tentativas,
            'total_erros' => $total_erros
        ];
        
        // Define constantes para a view
        if (!defined('VIEWS_PATH')) {
            define('VIEWS_PATH', dirname(__DIR__) . '/views');
        }
        
        // Carrega a view
        $view_path = VIEWS_PATH . '/professor/dashboard.php';
        if (file_exists($view_path)) {
            extract($dados);
            include_once $view_path;
        } else {
            $alt_path = __DIR__ . '/../views/professor/dashboard.php';
            if (file_exists($alt_path)) {
                extract($dados);
                include_once $alt_path;
            } else {
                echo "<h2>Erro: View do Dashboard Professor não encontrada.</h2>";
                echo "<p>Caminhos procurados:</p>";
                echo "<ul>";
                echo "<li>{$view_path}</li>";
                echo "<li>{$alt_path}</li>";
                echo "</ul>";
            }
        }
    }
    
    /**
     * Obtém estatísticas completas de um aluno
     */
    private function getEstatisticasAluno($aluno_id)
    {
        $estatisticas = [
            'total_resolvidas' => 0,
            'total_tentativas' => 0,
            'taxa_acerto' => '0%',
            'nivel' => 'Básico',
            'ultima_atividade' => '-'
        ];
        
        try {
            if ($this->db) {
                // 1. Total de equações resolvidas (concluídas)
                $sql = "SELECT COUNT(*) as total FROM progresso_aluno 
                        WHERE aluno_id = :aluno_id AND concluida = 1";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':aluno_id' => $aluno_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $estatisticas['total_resolvidas'] = (int)($result['total'] ?? 0);
                
                // 2. Total de tentativas
                $sql = "SELECT COUNT(*) as total FROM progresso_tentativas 
                        WHERE aluno_id = :aluno_id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':aluno_id' => $aluno_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $estatisticas['total_tentativas'] = (int)($result['total'] ?? 0);
                
                // 3. Taxa de acertos
                $sql = "SELECT 
                            COUNT(*) as total_tentativas,
                            SUM(correto) as total_acertos 
                        FROM progresso_tentativas 
                        WHERE aluno_id = :aluno_id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':aluno_id' => $aluno_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $total_tentativas = (int)($result['total_tentativas'] ?? 0);
                $total_acertos = (int)($result['total_acertos'] ?? 0);
                
                if ($total_tentativas > 0) {
                    $estatisticas['taxa_acerto'] = round(($total_acertos / $total_tentativas) * 100) . '%';
                } else {
                    $estatisticas['taxa_acerto'] = '0%';
                }
                
                // 4. Nível baseado no total de equações resolvidas
                $total_resolvidas = $estatisticas['total_resolvidas'];
                if ($total_resolvidas >= 30) {
                    $estatisticas['nivel'] = 'Avançado';
                } elseif ($total_resolvidas >= 20) {
                    $estatisticas['nivel'] = 'Intermediário Avançado';
                } elseif ($total_resolvidas >= 10) {
                    $estatisticas['nivel'] = 'Intermediário';
                } elseif ($total_resolvidas >= 5) {
                    $estatisticas['nivel'] = 'Iniciante Avançado';
                } else {
                    $estatisticas['nivel'] = 'Básico';
                }
                
                // 5. Última atividade
                $sql = "SELECT MAX(data_tentativa) as ultima FROM progresso_tentativas 
                        WHERE aluno_id = :aluno_id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':aluno_id' => $aluno_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result && $result['ultima']) {
                    $estatisticas['ultima_atividade'] = date('d/m/Y H:i', strtotime($result['ultima']));
                }
            }
        } catch (Exception $e) {
            error_log("Erro ao buscar estatísticas do aluno $aluno_id: " . $e->getMessage());
        }
        
        return $estatisticas;
    }
    
    /**
     * Lista todas as equações para gerenciamento
     */
    public function listarEquacoes()
    {
        // ===== CONTROLE DE SESSÃO =====
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id']) || ($_SESSION['tipo_perfil'] ?? '') !== 'professor') {
            header('Location: index.php?view=login');
            exit;
        }
        // ===== FIM CONTROLE DE SESSÃO =====
        
        $dados_equacoes = [];
        if ($this->equacao) {
            try {
                $dados_equacoes = $this->equacao->buscarTodas();
                if (!is_array($dados_equacoes)) {
                    $dados_equacoes = [];
                }
            } catch (Exception $e) {
                $dados_equacoes = [];
            }
        }
        
        if (!defined('VIEWS_PATH')) {
            define('VIEWS_PATH', dirname(__DIR__) . '/views');
        }
        
        $view_path = VIEWS_PATH . '/professor/equacoes.php';
        if (file_exists($view_path)) {
            include_once $view_path;
        } else {
            echo "<h2>Erro: View de Equações não encontrada.</h2>";
        }
    }
    
    /**
 * Gerenciar alunos
 */
public function gerenciarAlunos()
{
    // ===== CONTROLE DE SESSÃO =====
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['usuario_id']) || ($_SESSION['tipo_perfil'] ?? '') !== 'professor') {
        header('Location: index.php?view=login');
        exit;
    }
    // ===== FIM CONTROLE DE SESSÃO =====
    
    // Processa ações
    $action = $_GET['action'] ?? null;
    
    if ($action === 'deletar') {
        $this->deletarAluno();
        return;
    }
    
    if ($action === 'resetar_senha') {
        $this->resetarSenha();
        return;
    }
    
    if ($action === 'cadastrar') {
        $this->exibirFormularioCadastro();
        return;
    }
    
    // Busca alunos com estatísticas
    $dados_alunos = [];
    if ($this->aluno) {
        try {
            $alunos = $this->aluno->listarTodos();
            
            if (!empty($alunos) && is_array($alunos)) {
                foreach ($alunos as &$aluno) {
                    $aluno_id = isset($aluno['id']) ? $aluno['id'] : (isset($aluno['aluno_id']) ? $aluno['aluno_id'] : null);
                    
                    if ($aluno_id) {
                        // Busca total de equações resolvidas
                        $estatisticas = $this->getEstatisticasAluno($aluno_id);
                        $aluno['total_equacoes'] = $estatisticas['total_resolvidas'] ?? 0;
                    } else {
                        $aluno['total_equacoes'] = 0;
                    }
                }
                $dados_alunos = $alunos;
            }
        } catch (Exception $e) {
            error_log("Erro ao buscar alunos: " . $e->getMessage());
            $dados_alunos = [];
        }
    }
    
    // Define constantes para a view
    if (!defined('VIEWS_PATH')) {
        define('VIEWS_PATH', dirname(__DIR__) . '/views');
    }
    
    // Carrega a view
    $view_path = VIEWS_PATH . '/professor/alunos.php';
    if (file_exists($view_path)) {
        include_once $view_path;
    } else {
        // Fallback: exibe mensagem de erro com debug
        echo "<h2>Erro: View de Alunos não encontrada.</h2>";
        echo "<p>Caminho procurado: <code>{$view_path}</code></p>";
        echo "<p>Dados disponíveis: " . count($dados_alunos) . " alunos</p>";
        echo "<h3>Dados:</h3>";
        echo "<pre>" . print_r($dados_alunos, true) . "</pre>";
    }
}

/**
 * Exibe formulário de cadastro de aluno
 */
private function exibirFormularioCadastro()
{
    // Redireciona para o formulário de cadastro
    // Pode ser implementado como uma view separada
    header('Location: ?view=editar_aluno');
    exit;
}
    
    /**
     * Exibe relatório de erros
     */
    public function relatorio()
    {
        // ===== CONTROLE DE SESSÃO =====
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id']) || ($_SESSION['tipo_perfil'] ?? '') !== 'professor') {
            header('Location: index.php?view=login');
            exit;
        }
        // ===== FIM CONTROLE DE SESSÃO =====
        
        // Redireciona para o RelatorioController
        require_once __DIR__ . '/RelatorioController.php';
        $controller = new RelatorioController();
        $controller->relatorio();
    }
    
    // ===== MÉTODOS PARA CRUD DE ALUNOS E EQUAÇÕES =====
    // (mantenha os métodos existentes: cadastrarAluno, atualizar, deletarAluno, resetarSenha,
    // cadastrarEquacao, atualizarEquacao, deletarEquacao, exibirFormularioEdicao, 
    // exibirFormularioEdicaoEquacao)
}