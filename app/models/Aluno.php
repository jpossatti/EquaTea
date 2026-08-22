<?php
/**
 * Aluno.php
 * Model para gerenciamento de alunos.
 */
class Aluno
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Busca aluno por ID do aluno
     */
    public function getById($id)
    {
        try {
            $sql = "SELECT a.*, u.nome, u.email 
                    FROM alunos a 
                    JOIN usuarios u ON a.usuario_id = u.id 
                    WHERE a.id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar aluno por ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Busca aluno por ID do usuário
     */
    public function getByUsuarioId($usuario_id)
    {
        try {
            $sql = "SELECT a.*, u.nome, u.email 
                    FROM alunos a 
                    JOIN usuarios u ON a.usuario_id = u.id 
                    WHERE a.usuario_id = :usuario_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':usuario_id' => $usuario_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar aluno por usuário ID: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtém dados completos do aluno (incluindo usuário)
     */
    public function getDadosCompletos($usuario_id)
    {
        $sql = "SELECT u.*, a.id as aluno_id, a.idade, a.nivel_tea, a.escola, a.turma 
                FROM usuarios u 
                JOIN alunos a ON u.id = a.usuario_id 
                WHERE u.id = :usuario_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuario_id]);
        return $stmt->fetch();
    }
    
    /**
     * Obtém todos os alunos
     */
    public function getAll($apenas_ativos = true)
    {
        $sql = "SELECT 
                    u.id as usuario_id,
                    u.nome,
                    u.email,
                    u.tipo_perfil,
                    u.ativo,
                    a.id as aluno_id,
                    a.idade,
                    a.nivel_tea,
                    a.escola,
                    a.turma,
                    a.data_cadastro
                FROM usuarios u 
                JOIN alunos a ON u.id = a.usuario_id 
                WHERE u.tipo_perfil = 'aluno'";
        
        if ($apenas_ativos) {
            $sql .= " AND u.ativo = 1";
        }
        
        $sql .= " ORDER BY u.nome ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Cria um novo aluno
     */
    public function criar($usuario_id, $idade, $nivel_tea, $escola = null, $turma = null)
    {
        $sql = "INSERT INTO alunos (usuario_id, idade, nivel_tea, escola, turma) 
                VALUES (:usuario_id, :idade, :nivel_tea, :escola, :turma)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuario_id,
            ':idade' => $idade,
            ':nivel_tea' => $nivel_tea,
            ':escola' => $escola,
            ':turma' => $turma
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Busca aluno por ID (alias)
     */
    public function buscarPorId($id)
    {
        return $this->getById($id);
    }
    
    /**
     * Atualiza dados do aluno
     */
    public function atualizar($id, $nome, $email, $nivelTea, $turma)
    {
        $db = Database::getInstance()->getConnection();
        
        $sql = "UPDATE alunos a 
                JOIN usuarios u ON a.usuario_id = u.id 
                SET u.nome = :nome, u.email = :email, a.nivel_tea = :nivel_tea, a.turma = :turma 
                WHERE a.id = :id";
                
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'nome' => $nome,
            'email' => $email,
            'nivel_tea' => $nivelTea,
            'turma' => $turma
        ]);
    }
    
    /**
     * Deleta um aluno
     */
    public function deletar($id)
    {
        $db = Database::getInstance()->getConnection();
        $sql = "DELETE FROM alunos WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Lista todos os alunos
     */
    public function listarTodos()
    {
        try {
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT a.*, u.nome, u.email 
                    FROM alunos a 
                    JOIN usuarios u ON a.usuario_id = u.id";
            $stmt = $db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}