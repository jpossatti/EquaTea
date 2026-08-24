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
        try {
            $sql = "INSERT INTO registro_erros 
                    (aluno_id, equacao_id, passo, tipo_erro, resposta_fornecida, resposta_esperada, data_erro) 
                    VALUES (:aluno_id, :equacao_id, :passo, :tipo_erro, :resposta_fornecida, :resposta_esperada, NOW())";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':aluno_id' => $aluno_id,
                ':equacao_id' => $equacao_id,
                ':passo' => $passo,
                ':tipo_erro' => $tipo_erro,
                ':resposta_fornecida' => $resposta_fornecida,
                ':resposta_esperada' => $resposta_esperada
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao registrar erro: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtém erros por aluno
     */
    public function getByAluno($aluno_id)
    {
        try {
            $sql = "SELECT r.*, 
                           CONCAT(
                               CASE WHEN e.a = 1 THEN 'x' 
                                    WHEN e.a = -1 THEN '-x' 
                                    ELSE CONCAT(e.a, 'x') END,
                               CASE WHEN e.b > 0 THEN CONCAT(' + ', e.b)
                                    WHEN e.b < 0 THEN CONCAT(' - ', ABS(e.b))
                                    ELSE '' END,
                               ' = ', e.c
                           ) AS equacao,
                           e.dificuldade
                    FROM registro_erros r
                    JOIN equacoes e ON r.equacao_id = e.id
                    WHERE r.aluno_id = :aluno_id
                    ORDER BY r.data_erro DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':aluno_id' => $aluno_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar erros por aluno: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtém estatísticas de erros
     */
    public function getEstatisticas($aluno_id = null, $passo = null)
    {
        try {
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
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar estatísticas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtém relatório completo de erros (para professor)
     * CORRIGIDO: Agora retorna todos os dados necessários
     */
    public function getRelatorioCompleto($aluno_id = null, $passo = null)
    {
        try {
            $sql = "SELECT 
                        u.nome AS aluno,
                        a.id AS aluno_id,
                        CONCAT(
                            CASE WHEN e.a = 1 THEN 'x' 
                                 WHEN e.a = -1 THEN '-x' 
                                 ELSE CONCAT(e.a, 'x') END,
                            CASE WHEN e.b > 0 THEN CONCAT(' + ', e.b)
                                 WHEN e.b < 0 THEN CONCAT(' - ', ABS(e.b))
                                 ELSE '' END,
                            ' = ', e.c
                        ) AS equacao,
                        e.id AS equacao_id,
                        r.passo,
                        r.tipo_erro,
                        r.resposta_fornecida,
                        r.resposta_esperada,
                        r.data_erro,
                        a.id AS aluno_id
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
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Relatório completo: " . count($result) . " registros encontrados");
            return $result;
        } catch (PDOException $e) {
            error_log("Erro ao buscar relatório completo: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtém relatório de erros agrupado por aluno
     */
    public function getRelatorioPorAluno()
    {
        try {
            $sql = "SELECT 
                        u.nome AS aluno,
                        a.id AS aluno_id,
                        COUNT(r.id) AS total_erros,
                        COUNT(DISTINCT r.equacao_id) AS equacoes_com_erro,
                        GROUP_CONCAT(DISTINCT r.tipo_erro SEPARATOR ', ') AS tipos_erro,
                        MAX(r.data_erro) AS ultimo_erro
                    FROM registro_erros r
                    JOIN alunos a ON r.aluno_id = a.id
                    JOIN usuarios u ON a.usuario_id = u.id
                    GROUP BY a.id, u.nome
                    ORDER BY total_erros DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar relatório por aluno: " . $e->getMessage());
            return [];
        }
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