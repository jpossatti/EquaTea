<?php
/**
 * RegistroErro.php
 * Model para registro de erros cometidos pelos alunos.
 */
class RegistroErro
{
    private $db;
    
    // Tipos de erro possíveis
    const TIPOS = [
        'operacao_inversa',
        'calculo_errado',
        'sinal_trocado',
        'divisao_incorreta',
        'identificacao_errada',
        'outro'
    ];
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Registra um erro
     */
    public function registrar($aluno_id, $equacao_id, $passo, $tipo_erro, $resposta_fornecida = null, $resposta_esperada = null)
    {
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
    
    /**
     * Obtém erros por aluno
     */
    public function getByAluno($aluno_id)
    {
        $sql = "SELECT r.*, 
                       CONCAT(e.a, 'x + ', e.b, ' = ', e.c) as equacao,
                       e.dificuldade
                FROM registro_erros r
                JOIN equacoes e ON r.equacao_id = e.id
                WHERE r.aluno_id = :aluno_id
                ORDER BY r.data_erro DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':aluno_id' => $aluno_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtém estatísticas de erros
     */
    public function getEstatisticas($aluno_id = null, $passo = null)
    {
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
    
    /**
     * Obtém relatório completo de erros (para professor)
     */
    public function getRelatorioCompleto($aluno_id = null, $passo = null)
    {
        $sql = "SELECT 
                    u.nome as aluno,
                    CONCAT(e.a, 'x + ', e.b, ' = ', e.c) as equacao,
                    r.passo,
                    r.tipo_erro,
                    r.resposta_fornecida,
                    r.resposta_esperada,
                    r.data_erro,
                    a.id as aluno_id
                FROM registro_erros r
                JOIN alunos a ON r.aluno_id = a.id
                JOIN usuarios u ON a.usuario_id = u.id
                JOIN equacoes e ON r.equacao_id = e.id";
        
        $params = [];
        $where = [];
        
        if ($aluno_id) {
            $where[] = "a.id = :aluno_id";
            $params[':aluno_id'] = $aluno_id;
        }
        
        if ($passo) {
            $where[] = "r.passo = :passo";
            $params[':passo'] = $passo;
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " ORDER BY r.data_erro DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Identifica o tipo de erro baseado na resposta
     */
    public function identificarTipoErro($equacao, $passo, $resposta)
    {
        $a = $equacao['a'];
        $b = $equacao['b'];
        $c = $equacao['c'];
        $resposta = trim($resposta);
        
        switch ($passo) {
            case 1:
                return 'identificacao_errada';
            case 2:
                if (strpos($resposta, '+') !== false && $b > 0) {
                    return 'sinal_trocado';
                }
                if (strpos($resposta, '-') !== false && $b < 0) {
                    return 'sinal_trocado';
                }
                return 'operacao_inversa';
            case 3:
                return 'calculo_errado';
            case 4:
                return 'divisao_incorreta';
            default:
                return 'outro';
        }
    }
}