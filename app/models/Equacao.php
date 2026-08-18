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
    
   
public function validarResposta($equacao_id, $passo, $resposta)
{
    $equacao = $this->getById($equacao_id);
    if (!$equacao) return false;

    // Sanitização idêntica à do Controller
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