<?php
require_once MODELS_PATH . '/../config/database.php';

class Progresso {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getByAlunoEquacao($aluno_id, $equacao_id) {
        $sql = "SELECT * FROM progresso_aluno 
                WHERE aluno_id = :aluno_id AND equacao_id = :equacao_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':aluno_id' => $aluno_id,
            ':equacao_id' => $equacao_id
        ]);
        return $stmt->fetch();
    }
    
    public function iniciar($aluno_id, $equacao_id) {
        $sql = "INSERT INTO progresso_aluno (aluno_id, equacao_id, passo_atual, data_inicio) 
                VALUES (:aluno_id, :equacao_id, 1, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':aluno_id' => $aluno_id,
            ':equacao_id' => $equacao_id
        ]);
    }
    
    public function avancarPasso($aluno_id, $equacao_id, $passo_atual) {
        $sql = "UPDATE progresso_aluno 
                SET passo_atual = :passo_atual 
                WHERE aluno_id = :aluno_id AND equacao_id = :equacao_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':passo_atual' => $passo_atual + 1,
            ':aluno_id' => $aluno_id,
            ':equacao_id' => $equacao_id
        ]);
    }
    
    public function concluir($aluno_id, $equacao_id) {
        $sql = "UPDATE progresso_aluno 
                SET concluida = 1, data_conclusao = NOW() 
                WHERE aluno_id = :aluno_id AND equacao_id = :equacao_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':aluno_id' => $aluno_id,
            ':equacao_id' => $equacao_id
        ]);
    }
    
    public function getByAluno($aluno_id) {
        $sql = "SELECT p.*, e.a, e.b, e.c, e.dificuldade 
                FROM progresso_aluno p 
                JOIN equacoes e ON p.equacao_id = e.id 
                WHERE p.aluno_id = :aluno_id 
                ORDER BY p.data_inicio DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':aluno_id' => $aluno_id]);
        return $stmt->fetchAll();
    }
    
    public function getTaxaConclusao($aluno_id) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(concluida) as concluidas
                FROM progresso_aluno 
                WHERE aluno_id = :aluno_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':aluno_id' => $aluno_id]);
        $result = $stmt->fetch();
        
        if ($result['total'] > 0) {
            return ($result['concluidas'] / $result['total']) * 100;
        }
        return 0;
    }
}