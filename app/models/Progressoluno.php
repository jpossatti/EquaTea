<?php
/**
 * ProgressoAluno.php
 * Model para gerenciar o progresso dos alunos
 */
class ProgressoAluno
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Registra uma tentativa do aluno
     */
    public function registrarTentativa($aluno_id, $equacao_id, $passo, $resposta, $correto)
    {
        try {
            $sql = "INSERT INTO progresso_aluno 
                    (aluno_id, equacao_id, passo, resposta, correto, data_tentativa) 
                    VALUES 
                    (:aluno_id, :equacao_id, :passo, :resposta, :correto, NOW())";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':aluno_id' => $aluno_id,
                ':equacao_id' => $equacao_id,
                ':passo' => $passo,
                ':resposta' => $resposta,
                ':correto' => $correto ? 1 : 0
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao registrar tentativa: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica se uma equação já foi concluída pelo aluno
     */
    public function isConcluida($aluno_id, $equacao_id)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM progresso_aluno 
                    WHERE aluno_id = :aluno_id 
                    AND equacao_id = :equacao_id 
                    AND concluida = 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':aluno_id' => $aluno_id,
                ':equacao_id' => $equacao_id
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return ($result && $result['total'] > 0);
        } catch (PDOException $e) {
            error_log("Erro ao verificar conclusão: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Marca uma equação como concluída
     */
    public function marcarConcluida($aluno_id, $equacao_id)
    {
        try {
            // Verifica se já existe um registro
            $sql = "SELECT id FROM progresso_aluno 
                    WHERE aluno_id = :aluno_id 
                    AND equacao_id = :equacao_id 
                    AND concluida = 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':aluno_id' => $aluno_id,
                ':equacao_id' => $equacao_id
            ]);
            
            $existe = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existe) {
                return true;
            }
            
            // Insere como concluída
            $sql = "INSERT INTO progresso_aluno 
                    (aluno_id, equacao_id, passo, concluida, data_tentativa) 
                    VALUES 
                    (:aluno_id, :equacao_id, 4, 1, NOW())";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':aluno_id' => $aluno_id,
                ':equacao_id' => $equacao_id
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao marcar concluída: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtém estatísticas do aluno
     */
    public function getEstatisticas($aluno_id)
    {
        try {
            // Total de equações resolvidas
            $sql = "SELECT COUNT(DISTINCT equacao_id) as total 
                    FROM progresso_aluno 
                    WHERE aluno_id = :aluno_id 
                    AND concluida = 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':aluno_id' => $aluno_id]);
            $total = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Taxa de acertos
            $sql = "SELECT 
                        COUNT(*) as total_tentativas,
                        SUM(correto) as total_acertos 
                    FROM progresso_aluno 
                    WHERE aluno_id = :aluno_id 
                    AND passo <= 4";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':aluno_id' => $aluno_id]);
            $acertos = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $total_tentativas = $acertos['total_tentativas'] ?? 0;
            $total_acertos = $acertos['total_acertos'] ?? 0;
            $taxa_acerto = ($total_tentativas > 0) ? round(($total_acertos / $total_tentativas) * 100) . '%' : '0%';
            
            // Nível baseado no total de equações resolvidas
            $total_resolvidas = $total['total'] ?? 0;
            $nivel = $this->calcularNivel($total_resolvidas);
            
            return [
                'total_resolvidas' => $total_resolvidas,
                'taxa_acerto' => $taxa_acerto,
                'nivel_atual' => $nivel
            ];
        } catch (PDOException $e) {
            error_log("Erro ao obter estatísticas: " . $e->getMessage());
            return [
                'total_resolvidas' => 0,
                'taxa_acerto' => '0%',
                'nivel_atual' => 'Nível 1 - Básico'
            ];
        }
    }
    
    /**
     * Calcula o nível do aluno baseado no total de equações resolvidas
     */
    private function calcularNivel($total)
    {
        if ($total >= 30) {
            return 'Nível 5 - Avançado';
        } elseif ($total >= 20) {
            return 'Nível 4 - Intermediário Avançado';
        } elseif ($total >= 10) {
            return 'Nível 3 - Intermediário';
        } elseif ($total >= 5) {
            return 'Nível 2 - Iniciante Avançado';
        } else {
            return 'Nível 1 - Básico';
        }
    }
}