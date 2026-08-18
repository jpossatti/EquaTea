<?php
/**
 * Aluno.php
 * Model para gerenciamento de alunos.
 */
class Aluno
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtém dados completos do aluno (incluindo usuário)
     */
    public function getDadosCompletos($usuario_id)
    {
        $sql = "SELECT u.*, a.id as aluno_id, a.idade, a.nivel_tea, a.escola, a.turma 
                FROM usuarios u 
                JOIN alunos a ON u.id = a.usuario_id 
                WHERE u.id = :usuario_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':usuario_id' => $usuario_id]);
        return $stmt->fetch();
    }
    
    /**
     * Obtém todos os alunos
     */
/**
 * Obtém todos os alunos
 */
public function getAll($apenas_ativos = true)
{
    $sql = "SELECT 
                u.id as usuario_id,
                u.nome,
                u.email,
                u.tipo_perfil,
                u.ativo,
                a.id as aluno_id,
                a.idade,
                a.nivel_tea,
                a.escola,
                a.turma,
                a.data_cadastro,
                (SELECT COUNT(*) FROM progresso_aluno WHERE aluno_id = a.id) as total_equacoes,
                (SELECT SUM(concluida) FROM progresso_aluno WHERE aluno_id = a.id) as equacoes_concluidas,
                (SELECT COUNT(*) FROM registro_erros WHERE aluno_id = a.id) as total_erros
            FROM usuarios u 
            JOIN alunos a ON u.id = a.usuario_id 
            WHERE u.tipo_perfil = 'aluno'";
    
    if ($apenas_ativos) {
        $sql .= " AND u.ativo = 1";
    }
    
    $sql .= " ORDER BY u.nome ASC";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}
    
    /**
     * Obtém alunos por escola
     */
    public function getByEscola($escola)
    {
        $sql = "SELECT u.*, a.id as aluno_id, a.idade, a.nivel_tea, a.escola, a.turma
                FROM usuarios u 
                JOIN alunos a ON u.id = a.usuario_id 
                WHERE u.tipo_perfil = 'aluno' AND a.escola = :escola AND u.ativo = 1
                ORDER BY u.nome ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':escola' => $escola]);
        return $stmt->fetchAll();
    }
    
    /**
     * Cria um novo aluno
     */
    public function criar($usuario_id, $idade, $nivel_tea, $escola = null, $turma = null)
    {
        $sql = "INSERT INTO alunos (usuario_id, idade, nivel_tea, escola, turma) 
                VALUES (:usuario_id, :idade, :nivel_tea, :escola, :turma)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuario_id,
            ':idade' => $idade,
            ':nivel_tea' => $nivel_tea,
            ':escola' => $escola,
            ':turma' => $turma
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Atualiza dados do aluno
     */
    public function atualizar($aluno_id, $idade, $nivel_tea, $escola = null, $turma = null)
    {
        $sql = "UPDATE alunos SET idade = :idade, nivel_tea = :nivel_tea, 
                escola = :escola, turma = :turma WHERE id = :aluno_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':aluno_id' => $aluno_id,
            ':idade' => $idade,
            ':nivel_tea' => $nivel_tea,
            ':escola' => $escola,
            ':turma' => $turma
        ]);
    }
    
    /**
     * Obtém estatísticas do aluno
     */
    public function getEstatisticas($aluno_id)
    {
        $sql = "SELECT 
                    COUNT(DISTINCT equacao_id) as equacoes_tentadas,
                    SUM(concluida) as equacoes_concluidas,
                    SUM(tentativas) as total_tentativas,
                    AVG(tentativas) as media_tentativas
                FROM progresso_aluno 
                WHERE aluno_id = :aluno_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':aluno_id' => $aluno_id]);
        return $stmt->fetch();
    }
    
    /**
     * Obtém progresso detalhado do aluno
     */
    public function getProgressoDetalhado($aluno_id)
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
}