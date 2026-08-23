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
     * Obtém progresso por aluno e equação
     */
    public function getByAlunoEquacao($aluno_id, $equacao_id)
    {
        try {
            $sql = "SELECT * FROM progresso_aluno 
                    WHERE aluno_id = :aluno_id AND equacao_id = :equacao_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':aluno_id' => $aluno_id,
                ':equacao_id' => $equacao_id
            ]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar progresso: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Inicia um novo progresso para uma equação
     */
    public function iniciar($aluno_id, $equacao_id)
    {
        try {
            // Verifica se já existe
            $existe = $this->getByAlunoEquacao($aluno_id, $equacao_id);
            if ($existe) {
                return false;
            }
            
            $sql = "INSERT INTO progresso_aluno (aluno_id, equacao_id, passo_atual, data_inicio) 
                    VALUES (:aluno_id, :equacao_id, 1, NOW())";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':aluno_id' => $aluno_id,
                ':equacao_id' => $equacao_id
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao iniciar progresso: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Atualiza o passo atual
     */
    public function atualizarPasso($aluno_id, $equacao_id, $passo)
    {
        try {
            // Limita o passo entre 1 e 4
            $passo = max(1, min(4, (int)$passo));
            
            $sql = "UPDATE progresso_aluno 
                    SET passo_atual = :passo 
                    WHERE aluno_id = :aluno_id AND equacao_id = :equacao_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':aluno_id' => $aluno_id,
                ':equacao_id' => $equacao_id,
                ':passo' => $passo
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar passo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Registra uma tentativa do aluno
     */
    public function registrarTentativa($aluno_id, $equacao_id, $passo, $resposta, $correto)
    {
        try {
            // Verifica se já existe registro de tentativa para este passo
            $sql = "SELECT id FROM progresso_tentativas 
                    WHERE aluno_id = :aluno_id 
                    AND equacao_id = :equacao_id 
                    AND passo = :passo";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':aluno_id' => $aluno_id,
                ':equacao_id' => $equacao_id,
                ':passo' => $passo
            ]);
            $existe = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existe) {
                // Atualiza a tentativa existente
                $sql = "UPDATE progresso_tentativas 
                        SET resposta = :resposta, 
                            correto = :correto, 
                            data_tentativa = NOW() 
                        WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([
                    ':resposta' => $resposta,
                    ':correto' => $correto ? 1 : 0,
                    ':id' => $existe['id']
                ]);
            } else {
                // Insere nova tentativa
                $sql = "INSERT INTO progresso_tentativas 
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
            }
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
                    AND equacao_id = :equacao_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':aluno_id' => $aluno_id,
                ':equacao_id' => $equacao_id
            ]);
            
            $existe = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existe) {
                // Atualiza como concluída
                $sql = "UPDATE progresso_aluno 
                        SET concluida = 1, 
                            passo_atual = 4, 
                            data_conclusao = NOW() 
                        WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([':id' => $existe['id']]);
            } else {
                // Insere como concluída
                $sql = "INSERT INTO progresso_aluno 
                        (aluno_id, equacao_id, passo_atual, concluida, data_inicio, data_conclusao) 
                        VALUES 
                        (:aluno_id, :equacao_id, 4, 1, NOW(), NOW())";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([
                    ':aluno_id' => $aluno_id,
                    ':equacao_id' => $equacao_id
                ]);
            }
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
                    FROM progresso_tentativas 
                    WHERE aluno_id = :aluno_id";
            
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
    
    /**
     * Obtém o número de tentativas de um aluno para um passo específico
     */
    public function getTentativasPasso($aluno_id, $equacao_id, $passo)
    {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM progresso_tentativas 
                    WHERE aluno_id = :aluno_id 
                    AND equacao_id = :equacao_id 
                    AND passo = :passo";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':aluno_id' => $aluno_id,
                ':equacao_id' => $equacao_id,
                ':passo' => $passo
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['total'] : 0;
        } catch (PDOException $e) {
            error_log("Erro ao buscar tentativas do passo: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtém o histórico completo de tentativas do aluno para uma equação
     */
    public function getHistoricoTentativas($aluno_id, $equacao_id)
    {
        try {
            $sql = "SELECT * FROM progresso_tentativas 
                    WHERE aluno_id = :aluno_id 
                    AND equacao_id = :equacao_id 
                    ORDER BY passo ASC, data_tentativa DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':aluno_id' => $aluno_id,
                ':equacao_id' => $equacao_id
            ]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar histórico de tentativas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Verifica se o aluno já tentou este passo
     */
    public function jaTentouPasso($aluno_id, $equacao_id, $passo)
    {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM progresso_tentativas 
                    WHERE aluno_id = :aluno_id 
                    AND equacao_id = :equacao_id 
                    AND passo = :passo 
                    AND correto = 0";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':aluno_id' => $aluno_id,
                ':equacao_id' => $equacao_id,
                ':passo' => $passo
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result && (int)$result['total'] > 0;
        } catch (PDOException $e) {
            error_log("Erro ao verificar tentativa do passo: " . $e->getMessage());
            return false;
        }
    }
}