<?php
/**
 * Progresso.php
 * Model para gerenciamento do progresso do aluno.
 */
class Progresso
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
        $sql = "SELECT * FROM progresso_aluno 
                WHERE aluno_id = :aluno_id AND equacao_id = :equacao_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':aluno_id' => $aluno_id,
            ':equacao_id' => $equacao_id
        ]);
        return $stmt->fetch();
    }
    
    /**
     * Inicia um novo progresso
     */
    public function iniciar($aluno_id, $equacao_id)
    {
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
    }
    
    /**
     * Avança para o próximo passo
     */
    public function avancarPasso($aluno_id, $equacao_id)
    {
        // Verifica o passo atual
        $progresso = $this->getByAlunoEquacao($aluno_id, $equacao_id);
        if (!$progresso) {
            return false;
        }
        
        // Se já está no passo 4, não avança
        if ($progresso['passo_atual'] >= 4) {
            return false;
        }
        
        $sql = "UPDATE progresso_aluno 
                SET passo_atual = passo_atual + 1 
                WHERE aluno_id = :aluno_id AND equacao_id = :equacao_id 
                AND passo_atual < 4";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':aluno_id' => $aluno_id,
            ':equacao_id' => $equacao_id
        ]);
    }
    
    /**
     * Conclui a equação
     */
    public function concluir($aluno_id, $equacao_id)
    {
        $progresso = $this->getByAlunoEquacao($aluno_id, $equacao_id);
        if (!$progresso || $progresso['concluida']) {
            return false;
        }
        
        // Avança até o passo 4 se necessário
        while ($progresso['passo_atual'] < 4) {
            $this->avancarPasso($aluno_id, $equacao_id);
            $progresso = $this->getByAlunoEquacao($aluno_id, $equacao_id);
        }
        
        $sql = "UPDATE progresso_aluno 
                SET concluida = 1, data_conclusao = NOW() 
                WHERE aluno_id = :aluno_id AND equacao_id = :equacao_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':aluno_id' => $aluno_id,
            ':equacao_id' => $equacao_id
        ]);
    }
    
    /**
     * Registra uma tentativa
     */
    public function registrarTentativa($aluno_id, $equacao_id)
    {
        // Verifica se o progresso existe
        $progresso = $this->getByAlunoEquacao($aluno_id, $equacao_id);
        if (!$progresso || $progresso['concluida']) {
            return false;
        }
        
        $sql = "UPDATE progresso_aluno 
                SET tentativas = tentativas + 1 
                WHERE aluno_id = :aluno_id AND equacao_id = :equacao_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':aluno_id' => $aluno_id,
            ':equacao_id' => $equacao_id
        ]);
    }
    
    /**
     * Obtém o progresso de um aluno
     */
    public function getByAluno($aluno_id)
    {
        $sql = "SELECT p.*, 
                       CONCAT(e.a, 'x + ', e.b, ' = ', e.c) as equacao,
                       e.dificuldade
                FROM progresso_aluno p
                JOIN equacoes e ON p.equacao_id = e.id
                WHERE p.aluno_id = :aluno_id
                ORDER BY p.data_inicio DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':aluno_id' => $aluno_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Calcula a taxa de conclusão do aluno
     */
    public function getTaxaConclusao($aluno_id)
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(concluida) as concluidas
                FROM progresso_aluno 
                WHERE aluno_id = :aluno_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':aluno_id' => $aluno_id]);
        $result = $stmt->fetch();
        
        if ($result['total'] > 0) {
            return round(($result['concluidas'] / $result['total']) * 100, 1);
        }
        return 0;
    }
}