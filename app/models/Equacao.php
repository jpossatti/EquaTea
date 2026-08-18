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
        return $stmt->fetch();
    }
    
    /**
     * Obtém todas as equações
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
        return $stmt->fetchAll();
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
     * Cria uma nova equação
     */
    public function criar($a, $b, $c, $dificuldade = 'facil')
    {
        $solucao = ($c - $b) / $a;
        
        // Verifica se a solução é inteira
        if (fmod($solucao, 1) != 0) {
            return false;
        }
        
        $sql = "INSERT INTO equacoes (a, b, c, solucao, dificuldade) 
                VALUES (:a, :b, :c, :solucao, :dificuldade)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':a' => $a,
            ':b' => $b,
            ':c' => $c,
            ':solucao' => (int)$solucao,
            ':dificuldade' => $dificuldade
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Atualiza uma equação
     */
    public function atualizar($id, $a, $b, $c, $dificuldade)
    {
        $solucao = ($c - $b) / $a;
        
        if (fmod($solucao, 1) != 0) {
            return false;
        }
        
        $sql = "UPDATE equacoes SET a = :a, b = :b, c = :c, solucao = :solucao, 
                dificuldade = :dificuldade WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':a' => $a,
            ':b' => $b,
            ':c' => $c,
            ':solucao' => (int)$solucao,
            ':dificuldade' => $dificuldade,
            ':id' => $id
        ]);
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
        $result = $stmt->fetch();
        
        if ($result['total'] > 0) {
            return false;
        }
        
        $sql = "DELETE FROM equacoes WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
   /**
 * Valida a resposta de um passo
 */
public function validarResposta($equacao_id, $passo, $resposta)
{
    $equacao = $this->getById($equacao_id);
    if (!$equacao) return false;
    
    $a = $equacao['a'];
    $b = $equacao['b'];
    $c = $equacao['c'];
    $resposta = trim($resposta);
    
    // Normaliza a resposta: remove espaços extras
    $resposta = preg_replace('/\s+/', ' ', trim($resposta));
    $resposta = strtolower($resposta);
    
    switch ($passo) {
        case 1: // Identificar termos
            $sinal = $b >= 0 ? '+' : '-';
            $esperado1 = "{$a}x {$sinal} " . abs($b) . " = {$c}";
            $esperado1 = strtolower(preg_replace('/\s+/', ' ', trim($esperado1)));
            
            if ($a == 1) {
                $esperado2 = "x {$sinal} " . abs($b) . " = {$c}";
                $esperado2 = strtolower(preg_replace('/\s+/', ' ', trim($esperado2)));
                return $resposta === $esperado1 || $resposta === $esperado2;
            }
            
            return $resposta === $esperado1;
            
        case 2: // Isolar termo com x
            $resultado = $c - $b;
            $esperado1 = "{$a}x = {$resultado}";
            $esperado1 = strtolower(preg_replace('/\s+/', ' ', trim($esperado1)));
            
            // Permite "x = 7 - 3" em vez de "x = 4"
            $esperado2 = "{$a}x = {$c} - " . abs($b);
            $esperado2 = strtolower(preg_replace('/\s+/', ' ', trim($esperado2)));
            
            // Permite "x = 7 - 3" em vez de "x = 4" (para a=1)
            if ($a == 1) {
                $esperado3 = "x = {$resultado}";
                $esperado3 = strtolower(preg_replace('/\s+/', ' ', trim($esperado3)));
                $esperado4 = "x = {$c} - " . abs($b);
                $esperado4 = strtolower(preg_replace('/\s+/', ' ', trim($esperado4)));
                return $resposta === $esperado1 || $resposta === $esperado2 || 
                       $resposta === $esperado3 || $resposta === $esperado4;
            }
            
            return $resposta === $esperado1 || $resposta === $esperado2;
            
        case 3: // Calcular lado direito
            $esperado = $c - $b;
            return (int)$resposta === $esperado;
            
        case 4: // Isolar x
            $esperado = ($c - $b) / $a;
            return (float)$resposta === (float)$esperado;
            
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
        
        $a = $equacao['a'];
        $b = $equacao['b'];
        $c = $equacao['c'];
        
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