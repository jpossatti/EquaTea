<?php
require_once MODELS_PATH . '/../config/database.php';

class Equacao {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM equacoes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    public function getAll() {
        $sql = "SELECT * FROM equacoes ORDER BY dificuldade, id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getRandom($aluno_id = null) {
        if ($aluno_id) {
            // Buscar equações não concluídas pelo aluno
            $sql = "SELECT e.* FROM equacoes e 
                    LEFT JOIN progresso_aluno p ON e.id = p.equacao_id AND p.aluno_id = :aluno_id
                    WHERE p.equacao_id IS NULL OR p.concluida = 0
                    ORDER BY RAND() LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':aluno_id' => $aluno_id]);
            $result = $stmt->fetch();
            
            if ($result) {
                return $result;
            }
        }
        
        // Se não houver equações não concluídas, retorna qualquer uma
        $sql = "SELECT * FROM equacoes ORDER BY RAND() LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function getByDificuldade($dificuldade) {
        $sql = "SELECT * FROM equacoes WHERE dificuldade = :dificuldade";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':dificuldade' => $dificuldade]);
        return $stmt->fetchAll();
    }
    
    public function getEnunciado($id) {
        $equacao = $this->getById($id);
        if ($equacao) {
            $b = $equacao['b'] >= 0 ? "+ {$equacao['b']}" : "- " . abs($equacao['b']);
            return "{$equacao['a']}x {$b} = {$equacao['c']}";
        }
        return '';
    }
    
    public function getSolucaoPasso($equacao_id, $passo) {
        $equacao = $this->getById($equacao_id);
        if (!$equacao) return null;
        
        $a = $equacao['a'];
        $b = $equacao['b'];
        $c = $equacao['c'];
        
        switch ($passo) {
            case 1:
                return [
                    'termo_x' => "{$a}x",
                    'termos_sem_x' => ($b >= 0 ? "+ $b" : "- " . abs($b)) . " e $c"
                ];
            case 2:
                $operacao = $b >= 0 ? 'subtraia' : 'some';
                $valor = abs($b);
                return "{$operacao} $valor de ambos os lados";
            case 3:
                return $c - $b;
            case 4:
                return ($c - $b) / $a;
            default:
                return null;
        }
    }
    
    public function validarResposta($equacao_id, $passo, $resposta) {
        $equacao = $this->getById($equacao_id);
        if (!$equacao) return false;
        
        $a = $equacao['a'];
        $b = $equacao['b'];
        $c = $equacao['c'];
        
        switch ($passo) {
            case 1:
                $esperado = "{$a}x" . ($b >= 0 ? " + $b" : " - " . abs($b)) . " = $c";
                return trim($resposta) == trim($esperado);
            case 2:
                $esperado = "{$a}x = " . ($c - $b);
                return trim($resposta) == trim($esperado);
            case 3:
                return trim($resposta) == ($c - $b);
            case 4:
                return trim($resposta) == (($c - $b) / $a);
            default:
                return false;
        }
    }
}