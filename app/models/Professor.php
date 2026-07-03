<?php
/**
 * ============================================================
 * Professor.php
 * Modelo que representa a tabela "professores" no banco de dados.
 * 
 * FUNCIONALIDADES:
 * - CRUD de professores
 * - Relacionamento com a tabela usuarios
 * - Métodos específicos para professores
 * 
 * @package EquaTEA
 * @subpackage Models
 * @author Equipe EquaTEA
 * @version 1.0
 * ============================================================
 */

require_once MODELS_PATH . '/../config/database.php';

/**
 * Class Professor
 * 
 * Gerencia todas as operações relacionadas aos professores
 * no sistema EquaTEA.
 * 
 * @author Equipe EquaTEA
 */
class Professor
{
    /**
     * @var PDO Conexão com o banco de dados
     */
    private $db;
    
    /**
     * @var array Dados do professor atualmente carregados
     */
    private $dados;

    /**
     * Construtor da classe Professor.
     * Inicializa a conexão com o banco de dados.
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ============================================================
    // MÉTODOS DE CRUD
    // ============================================================

    /**
     * Obtém todos os dados de um professor pelo ID do usuário.
     * 
     * @param int $usuario_id ID do usuário na tabela usuarios
     * @return array|false Dados do professor ou false se não encontrado
     */
    public function getByUsuarioId($usuario_id)
    {
        try {
            $sql = "SELECT 
                        p.*,
                        u.nome,
                        u.email,
                        u.data_cadastro,
                        u.ultimo_acesso,
                        u.ativo
                    FROM professores p
                    JOIN usuarios u ON p.usuario_id = u.id
                    WHERE p.usuario_id = :usuario_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':usuario_id' => $usuario_id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Erro ao buscar professor: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtém todos os dados de um professor pelo ID do professor.
     * 
     * @param int $id ID do professor na tabela professores
     * @return array|false Dados do professor ou false se não encontrado
     */
    public function getById($id)
    {
        try {
            $sql = "SELECT 
                        p.*,
                        u.nome,
                        u.email,
                        u.data_cadastro,
                        u.ultimo_acesso,
                        u.ativo
                    FROM professores p
                    JOIN usuarios u ON p.usuario_id = u.id
                    WHERE p.id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Erro ao buscar professor: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lista todos os professores cadastrados no sistema.
     * 
     * @param bool $apenas_ativos Se true, retorna apenas professores ativos
     * @return array Lista de professores
     */
    public function getAll($apenas_ativos = true)
    {
        try {
            $sql = "SELECT 
                        p.*,
                        u.nome,
                        u.email,
                        u.data_cadastro,
                        u.ultimo_acesso,
                        u.ativo,
                        (SELECT COUNT(*) FROM alunos a WHERE a.escola = p.escola) as total_alunos
                    FROM professores p
                    JOIN usuarios u ON p.usuario_id = u.id";
            
            if ($apenas_ativos) {
                $sql .= " WHERE u.ativo = 1";
            }
            
            $sql .= " ORDER BY u.nome ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erro ao listar professores: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cria um novo professor no sistema.
     * Cria o registro na tabela usuarios e na tabela professores.
     * 
     * @param string $nome       Nome completo do professor
     * @param string $email      E-mail do professor
     * @param string $senha      Senha do professor (será hasheada)
     * @param string $disciplina Disciplina que leciona
     * @param string $escola     Escola onde trabalha
     * @param string $telefone   Telefone de contato
     * @return int|false ID do professor criado ou false em caso de erro
     */
    public function criar($nome, $email, $senha, $disciplina, $escola, $telefone = null)
    {
        // ============================================================
        // 1. VALIDAÇÃO DOS DADOS DE ENTRADA
        // ============================================================
        
        if (empty($nome) || strlen($nome) < 3) {
            $this->setError('Nome deve ter pelo menos 3 caracteres.');
            return false;
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setError('E-mail inválido.');
            return false;
        }
        
        if (empty($senha) || strlen($senha) < 4) {
            $this->setError('Senha deve ter pelo menos 4 caracteres.');
            return false;
        }
        
        if (empty($escola)) {
            $this->setError('Escola é obrigatória.');
            return false;
        }
        
        // ============================================================
        // 2. VERIFICAÇÃO DE DUPLICIDADE
        // ============================================================
        
        $usuario = new Usuario();
        if ($usuario->getByEmail($email)) {
            $this->setError('Este e-mail já está cadastrado no sistema.');
            return false;
        }
        
        // ============================================================
        // 3. TRANSAÇÃO - CRIAÇÃO DO USUÁRIO E PROFESSOR
        // ============================================================
        
        try {
            $this->db->beginTransaction();
            
            // 3.1. Criar o usuário
            $usuario_id = $usuario->criar($nome, $email, $senha, 'professor');
            
            if (!$usuario_id) {
                throw new Exception('Falha ao criar usuário.');
            }
            
            // 3.2. Criar o professor
            $sql = "INSERT INTO professores (usuario_id, disciplina, escola, telefone) 
                    VALUES (:usuario_id, :disciplina, :escola, :telefone)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':usuario_id' => $usuario_id,
                ':disciplina' => $disciplina ?: 'Matemática',
                ':escola' => $escola,
                ':telefone' => $telefone
            ]);
            
            if (!$result) {
                throw new Exception('Falha ao criar registro de professor.');
            }
            
            // 3.3. Obter o ID do professor criado
            $professor_id = $this->db->lastInsertId();
            
            $this->db->commit();
            
            $this->setSuccess('Professor cadastrado com sucesso!');
            return $professor_id;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao criar professor: " . $e->getMessage());
            $this->setError('Erro ao criar professor: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza os dados de um professor existente.
     * 
     * @param int    $professor_id ID do professor na tabela professores
     * @param string $nome         Novo nome
     * @param string $disciplina   Nova disciplina
     * @param string $escola       Nova escola
     * @param string $telefone     Novo telefone
     * @return bool True se atualizado com sucesso
     */
    public function atualizar($professor_id, $nome, $disciplina, $escola, $telefone = null)
    {
        // ============================================================
        // 1. VALIDAÇÃO DOS DADOS DE ENTRADA
        // ============================================================
        
        if (empty($nome) || strlen($nome) < 3) {
            $this->setError('Nome deve ter pelo menos 3 caracteres.');
            return false;
        }
        
        if (empty($escola)) {
            $this->setError('Escola é obrigatória.');
            return false;
        }
        
        // ============================================================
        // 2. BUSCAR O PROFESSOR ATUAL
        // ============================================================
        
        $professor_atual = $this->getById($professor_id);
        if (!$professor_atual) {
            $this->setError('Professor não encontrado.');
            return false;
        }
        
        // ============================================================
        // 3. ATUALIZAÇÃO DOS DADOS
        // ============================================================
        
        try {
            $this->db->beginTransaction();
            
            // 3.1. Atualizar o nome na tabela usuarios
            $sql_usuario = "UPDATE usuarios SET nome = :nome WHERE id = :usuario_id";
            $stmt_usuario = $this->db->prepare($sql_usuario);
            $stmt_usuario->execute([
                ':nome' => $nome,
                ':usuario_id' => $professor_atual['usuario_id']
            ]);
            
            // 3.2. Atualizar os dados específicos do professor
            $sql_professor = "UPDATE professores 
                              SET disciplina = :disciplina, 
                                  escola = :escola, 
                                  telefone = :telefone 
                              WHERE id = :professor_id";
            $stmt_professor = $this->db->prepare($sql_professor);
            $stmt_professor->execute([
                ':disciplina' => $disciplina ?: 'Matemática',
                ':escola' => $escola,
                ':telefone' => $telefone,
                ':professor_id' => $professor_id
            ]);
            
            $this->db->commit();
            
            $this->setSuccess('Dados do professor atualizados com sucesso!');
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao atualizar professor: " . $e->getMessage());
            $this->setError('Erro ao atualizar professor: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui um professor do sistema (soft delete).
     * Apenas desativa o usuário, mantendo os dados para histórico.
     * 
     * @param int $professor_id ID do professor na tabela professores
     * @return bool True se excluído com sucesso
     */
    public function excluir($professor_id)
    {
        try {
            $professor = $this->getById($professor_id);
            if (!$professor) {
                $this->setError('Professor não encontrado.');
                return false;
            }
            
            // ============================================================
            // SOFT DELETE - Desativa o usuário
            // ============================================================
            
            $sql = "UPDATE usuarios SET ativo = 0 WHERE id = :usuario_id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([':usuario_id' => $professor['usuario_id']]);
            
            if ($result) {
                $this->setSuccess('Professor excluído com sucesso!');
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao excluir professor: " . $e->getMessage());
            $this->setError('Erro ao excluir professor: ' . $e->getMessage());
            return false;
        }
    }

    // ============================================================
    // MÉTODOS ESPECÍFICOS PARA PROFESSORES
    // ============================================================

    /**
     * Obtém a lista de alunos associados à escola do professor.
     * 
     * @param int $professor_id ID do professor
     * @return array Lista de alunos da mesma escola
     */
    public function getAlunosPorEscola($professor_id)
    {
        try {
            $professor = $this->getById($professor_id);
            if (!$professor) {
                return [];
            }
            
            $sql = "SELECT 
                        a.*,
                        u.nome,
                        u.email,
                        u.ultimo_acesso,
                        (SELECT COUNT(*) FROM progresso_aluno WHERE aluno_id = a.id) as total_equacoes,
                        (SELECT SUM(concluida) FROM progresso_aluno WHERE aluno_id = a.id) as equacoes_concluidas
                    FROM alunos a
                    JOIN usuarios u ON a.usuario_id = u.id
                    WHERE a.escola = :escola AND u.ativo = 1
                    ORDER BY u.nome ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':escola' => $professor['escola']]);
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Erro ao buscar alunos por escola: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtém estatísticas específicas do professor.
     * 
     * @param int $professor_id ID do professor
     * @return array Estatísticas do professor
     */
    public function getEstatisticas($professor_id)
    {
        try {
            $professor = $this->getById($professor_id);
            if (!$professor) {
                return [];
            }
            
            $stats = [];
            
            // Total de alunos na escola
            $sql = "SELECT COUNT(*) as total FROM alunos WHERE escola = :escola";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':escola' => $professor['escola']]);
            $stats['total_alunos_escola'] = $stmt->fetch()['total'];
            
            // Média de desempenho dos alunos
            $sql = "SELECT 
                        AVG(taxa) as media_conclusao
                    FROM (
                        SELECT 
                            a.id,
                            (SELECT SUM(concluida) FROM progresso_aluno WHERE aluno_id = a.id) as concluidas,
                            (SELECT COUNT(*) FROM progresso_aluno WHERE aluno_id = a.id) as total
                        FROM alunos a
                        WHERE a.escola = :escola
                    ) AS sub
                    WHERE total > 0";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':escola' => $professor['escola']]);
            $result = $stmt->fetch();
            $stats['media_conclusao'] = $result ? round($result['media_conclusao'] * 100, 1) . '%' : 'N/A';
            
            // Erro mais comum na escola
            $sql = "SELECT 
                        r.tipo_erro,
                        COUNT(*) as total
                    FROM registro_erros r
                    JOIN alunos a ON r.aluno_id = a.id
                    WHERE a.escola = :escola
                    GROUP BY r.tipo_erro
                    ORDER BY total DESC
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':escola' => $professor['escola']]);
            $result = $stmt->fetch();
            $stats['erro_mais_comum'] = $result ? ucfirst(str_replace('_', ' ', $result['tipo_erro'])) : 'Nenhum';
            
            return $stats;
            
        } catch (PDOException $e) {
            error_log("Erro ao buscar estatísticas do professor: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica se um professor existe no sistema.
     * 
     * @param int $professor_id ID do professor
     * @return bool True se o professor existe
     */
    public function existe($professor_id)
    {
        $sql = "SELECT COUNT(*) as total FROM professores WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $professor_id]);
        return $stmt->fetch()['total'] > 0;
    }

    // ============================================================
    // MÉTODOS AUXILIARES DE MENSAGENS
    // ============================================================

    /**
     * @var string Mensagem de erro
     */
    private $error = null;
    
    /**
     * @var string Mensagem de sucesso
     */
    private $success = null;

    /**
     * Define uma mensagem de erro.
     * 
     * @param string $mensagem Mensagem de erro
     */
    private function setError($mensagem)
    {
        $this->error = $mensagem;
        $_SESSION['professor_error'] = $mensagem;
    }

    /**
     * Define uma mensagem de sucesso.
     * 
     * @param string $mensagem Mensagem de sucesso
     */
    private function setSuccess($mensagem)
    {
        $this->success = $mensagem;
        $_SESSION['professor_success'] = $mensagem;
    }

    /**
     * Obtém a mensagem de erro.
     * 
     * @return string|null Mensagem de erro ou null
     */
    public function getError()
    {
        if (isset($_SESSION['professor_error'])) {
            $msg = $_SESSION['professor_error'];
            unset($_SESSION['professor_error']);
            return $msg;
        }
        return $this->error;
    }

    /**
     * Obtém a mensagem de sucesso.
     * 
     * @return string|null Mensagem de sucesso ou null
     */
    public function getSuccess()
    {
        if (isset($_SESSION['professor_success'])) {
            $msg = $_SESSION['professor_success'];
            unset($_SESSION['professor_success']);
            return $msg;
        }
        return $this->success;
    }
}