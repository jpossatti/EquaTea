<?php
/**
 * Usuario.php
 * Model base para autenticação de usuários.
 */
class Usuario
{
    protected $db;
    protected $table = 'usuarios';
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Realiza login do usuário
     * @param string $email
     * @param string $senha
     * @return array|false Dados do usuário ou false
     */
    public function login($email, $senha)
    {
        // Verifica se a coluna 'ativo' existe ou busca direto pelo email
        $sql = "SELECT * FROM {$this->table} WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();
        
        // Valida se o usuário existe, a senha confere e se está ativo (caso a coluna exista)
        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            if (isset($usuario['ativo']) && $usuario['ativo'] == 0) {
                return false; // Usuário desativado
            }
            return $usuario;
        }
        return false;
    }
    
    /**
     * Busca usuário por ID
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Busca usuário por email
     */
    public function getByEmail($email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }
    
    /**
     * Cria um novo usuário (com verificação de duplicidade)
     */
    public function criar($nome, $email, $senha, $tipo_perfil = 'aluno')
    {
        $existe = $this->getByEmail($email);
        if ($existe) {
            throw new Exception("Email '{$email}' já está cadastrado.");
        }
        
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $sql = "INSERT INTO {$this->table} (nome, email, senha_hash, tipo_perfil) 
                VALUES (:nome, :email, :senha_hash, :tipo_perfil)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':senha_hash' => $senha_hash,
            ':tipo_perfil' => $tipo_perfil
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Atualiza o último acesso do usuário
     */
    public function atualizarUltimoAcesso($id)
    {
        // Tratativa caso a coluna ultimo_acesso ainda não exista no banco
        try {
            $sql = "UPDATE {$this->table} SET ultimo_acesso = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (Throwable $e) {
            return false;
        }
    }
    
    /**
     * Atualiza a senha do usuário
     */
    public function atualizarSenha($id, $nova_senha)
    {
        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        $sql = "UPDATE {$this->table} SET senha_hash = :senha_hash WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':senha_hash' => $senha_hash, ':id' => $id]);
    }
    
    /**
     * Desativa um usuário (soft delete)
     */
    public function desativar($id)
    {
        $sql = "UPDATE {$this->table} SET ativo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}