<?php
require_once MODELS_PATH . '/../config/database.php';

class RegistroErro {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function registrar($aluno_id, $equacao_id, $passo, $tipo_erro, $resposta_fornecida = null, $resposta_esperada = null) {
        $sql = "INSERT INTO registro_erros 
                (aluno_id, equacao_id, passo, tipo_erro, resposta_fornecida, resposta_esperada) 
                VALUES (:aluno_id, :equacao_id, :passo, :tipo_erro, :resposta_fornecida, :resposta_esperada)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':aluno_id' => $aluno_id,
            ':equacao_id' => $equacao_id,
            ':passo' => $passo,
            ':tipo_erro' => $tipo_erro,
            ':resposta_fornecida' => $resposta_fornecida,
            ':resposta_esperada' => $resposta_esperada
        ]);
    }
    
    public function getByAluno($aluno_id) {
        $sql = "SELECT * FROM registro_erros 
                WHERE aluno_id = :aluno_id 
                ORDER BY data_erro DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':aluno_id' => $aluno_id]);
        return $stmt->fetchAll();
    }
    
    public function getEstatisticas($aluno_id = null, $passo = null) {
        $sql = "SELECT 
                    tipo_erro,
                    COUNT(*) as quantidade,
                    passo
                FROM registro_erros";
        $params = [];
        $where = [];
        
        if ($aluno_id) {
            $where[] = "aluno_id = :aluno_id";
            $params[':aluno_id'] = $aluno_id;
        }
        
        if ($passo) {
            $where[] = "passo = :passo";
            $params[':passo'] = $passo;
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " GROUP BY tipo_erro, passo ORDER BY quantidade DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getErrosPorAluno() {
        $sql = "SELECT 
                    u.nome as aluno,
                    COUNT(re.id) as total_erros,
                    re.passo,
                    re.tipo_erro
                FROM registro_erros re
                JOIN alunos a ON re.aluno_id = a.id
                JOIN usuarios u ON a.usuario_id = u.id
                GROUP BY u.nome, re.passo, re.tipo_erro
                ORDER BY u.nome, total_erros DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}