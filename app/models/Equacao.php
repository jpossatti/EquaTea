<?php
/**
 * Equacao.php
 * Model para gerenciamento de equações.
 */
class Equacao
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtém equação por ID
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM equacoes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca todas as equações ordenadas por dificuldade
     */
    public function buscarTodas()
    {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "SELECT * FROM equacoes 
                    ORDER BY CASE dificuldade 
                        WHEN 'Fácil' THEN 1 
                        WHEN 'Médio' THEN 2 
                        WHEN 'Difícil' THEN 3 
                        ELSE 4 END ASC, id DESC";
            
            $stmt = $db->query($sql);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $result ?: [];
            
        } catch (PDOException $e) {
            error_log("Erro ao buscar todas as equações: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtém todas as equações com filtro de dificuldade
     */
    public function getAll($dificuldade = null)
    {
        $sql = "SELECT * FROM equacoes";
        $params = [];
        
        if ($dificuldade) {
            $sql .= " WHERE dificuldade = :dificuldade";
            $params[':dificuldade'] = $dificuldade;
        }
        
        $sql .= " ORDER BY FIELD(dificuldade, 'facil', 'medio', 'dificil'), a, b, c";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtém equação aleatória não concluída pelo aluno
     */
    public function getRandom($aluno_id = null)
    {
        if ($aluno_id) {
            $sql = "SELECT e.* FROM equacoes e 
                    LEFT JOIN progresso_aluno p ON e.id = p.equacao_id AND p.aluno_id = :aluno_id
                    WHERE p.equacao_id IS NULL OR p.concluida = 0
                    ORDER BY RAND() LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':aluno_id' => $aluno_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $result;
            }
        }
        
        // Se não houver equações não concluídas, retorna qualquer uma
        $sql = "SELECT * FROM equacoes ORDER BY RAND() LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtém o enunciado da equação
     */
    public function getEnunciado($id)
    {
        $equacao = $this->getById($id);
        if ($equacao) {
            $sinal = $equacao['b'] >= 0 ? '+' : '-';
            return "{$equacao['a']}x {$sinal} " . abs($equacao['b']) . " = {$equacao['c']}";
        }
        return '';
    }
    
    /**
     * Cria uma nova equação validando a solução inteira
     */
    public function criar($a, $b, $c, $dificuldade = 'facil')
    {
        if ((int)$a === 0) {
            return false;
        }

        // Verifica se a solução x = (c - b) / a é um número inteiro
        $x = ($c - $b) / $a;
        if (!is_int($x) && floor($x) != $x) {
            return false;
        }

        $sql = "INSERT INTO equacoes (a, b, c, solucao, dificuldade, data_cadastro) 
                VALUES (:a, :b, :c, :solucao, :dificuldade, NOW())";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':a'           => (int)$a,
                ':b'           => (int)$b,
                ':c'           => (int)$c,
                ':solucao'     => (int)$x,
                ':dificuldade' => $dificuldade
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao inserir equação: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Atualiza uma equação
     */
    public function atualizar($id, $a, $b, $c, $solucao, $dificuldade)
    {
        $db = Database::getInstance()->getConnection();
        
        $sql = "UPDATE equacoes SET a = :a, b = :b, c = :c, solucao = :solucao, dificuldade = :dificuldade WHERE id = :id";
        
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'a' => $a,
            'b' => $b,
            'c' => $c,
            'solucao' => $solucao,
            'dificuldade' => $dificuldade
        ]);
    }
    
    /**
     * Deleta uma equação
     */
    public function deletar($id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM equacoes WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Busca equação por ID (alias para getById)
     */
    public function buscarPorId($id)
    {
        return $this->getById($id);
    }
    
    /**
     * Exclui uma equação (apenas se não utilizada)
     */
    public function excluir($id)
    {
        // Verifica se foi utilizada
        $sql = "SELECT COUNT(*) as total FROM progresso_aluno WHERE equacao_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && $result['total'] > 0) {
            return false;
        }
        
        $sql = "DELETE FROM equacoes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    /**
     * Valida a resposta digitada pelo aluno
     */
    public function validarResposta($equacao_id, $passo, $resposta)
    {
        $equacao = $this->getById($equacao_id);
        if (!$equacao) return false;

        // Sanitização
        $resp = strtolower($resposta);
        $resp = preg_replace('/[\s\x{200B}-\x{200D}\x{FEFF}]/u', '', $resp);
        $resp = str_replace(['–', '—', '−'], '-', $resp);

        $a = (int)$equacao['a'];
        $b = (int)$equacao['b'];
        $c = (int)$equacao['c'];

        $termoX = ($a === 1) ? '(1?x)' : (($a === -1) ? '(-1?x)' : "({$a}x)");

        switch ($passo) {
            case 1:
                return preg_match('/^' . $termoX . '$/i', $resp) === 1;

            case 2:
                $bAbs = abs($b);
                $opOposta = ($b >= 0) ? '-' : '\+';
                return preg_match('/^(' . $termoX . '=)?' . $c . $opOposta . $bAbs . '$/i', $resp) === 1;

            case 3:
                $resultado = $c - $b;
                return preg_match('/^(' . $termoX . '=)?' . $resultado . '$/i', $resp) === 1;

            case 4:
                if ($a === 0) return false;
                $valorX = ($c - $b) / $a;
                return preg_match('/^(x=)?' . $valorX . '$/i', $resp) === 1;

            default:
                return false;
        }
    }

    /**
     * Obtém a resposta esperada para um passo
     */
    public function getRespostaEsperada($equacao_id, $passo)
    {
        $equacao = $this->getById($equacao_id);
        if (!$equacao) return null;
        
        $a = (int)$equacao['a'];
        $b = (int)$equacao['b'];
        $c = (int)$equacao['c'];
        
        switch ($passo) {
            case 1:
                $sinal = $b >= 0 ? '+' : '-';
                return "{$a}x {$sinal} " . abs($b) . " = {$c}";
            case 2:
                return "{$a}x = " . ($c - $b);
            case 3:
                return (string)($c - $b);
            case 4:
                return (string)(($c - $b) / $a);
            default:
                return null;
        }
    }
}