<?php
/**
 * Professor.php
 * Model para gerenciamento de professores.
 */
class Professor
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtém dados do professor por ID do usuário
     */
    public function getByUsuarioId($usuario_id)
    {
        $sql = "SELECT p.*, u.nome, u.email 
                FROM professores p 
                JOIN usuarios u ON p.usuario_id = u.id 
                WHERE p.usuario_id = :usuario_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuario_id]);
        return $stmt->fetch();
    }
    
    /**
     * Obtém todos os professores
     */
    public function getAll()
    {
        $sql = "SELECT p.*, u.nome, u.email 
                FROM professores p 
                JOIN usuarios u ON p.usuario_id = u.id 
                WHERE u.ativo = 1
                ORDER BY u.nome ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Cria um novo professor
     */
    public function criar($usuario_id, $disciplina = 'Matemática', $escola, $telefone = null)
    {
        $sql = "INSERT INTO professores (usuario_id, disciplina, escola, telefone) 
                VALUES (:usuario_id, :disciplina, :escola, :telefone)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuario_id,
            ':disciplina' => $disciplina,
            ':escola' => $escola,
            ':telefone' => $telefone
        ]);
        return $this->db->lastInsertId();
    }
}