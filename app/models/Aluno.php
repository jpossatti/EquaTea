<?php
require_once MODELS_PATH . '/Usuario.php';

class Aluno {
    private $db;
    private $usuario;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->usuario = new Usuario();
    }
    
    public function getDadosCompletos($usuario_id) {
        $sql = "SELECT u.*, a.id as aluno_id, a.idade, a.nivel_tea, a.escola, a.turma 
                FROM usuarios u 
                JOIN alunos a ON u.id = a.usuario_id 
                WHERE u.id = :usuario_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuario_id]);
        return $stmt->fetch();
    }
    
    public function getEquacoesConcluidas($aluno_id) {
        $sql = "SELECT COUNT(*) as total FROM progresso_aluno 
                WHERE aluno_id = :aluno_id AND concluida = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':aluno_id' => $aluno_id]);
        return $stmt->fetch()['total'];
    }
    
    public function getEstatisticas($aluno_id) {
        $sql = "SELECT 
                    COUNT(DISTINCT equacao_id) as equacoes_tentadas,
                    SUM(concluida) as equacoes_concluidas,
                    SUM(tentativas) as total_tentativas
                FROM progresso_aluno 
                WHERE aluno_id = :aluno_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':aluno_id' => $aluno_id]);
        return $stmt->fetch();
    }
}