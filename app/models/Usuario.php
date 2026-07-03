<?php
require_once MODELS_PATH . '/../config/database.php';

class Usuario {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function login($email, $senha) {
        $sql = "SELECT * FROM usuarios WHERE email = :email AND ativo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();
        
        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            return $usuario;
        }
        return false;
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM usuarios WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    public function getByEmail($email) {
        $sql = "SELECT * FROM usuarios WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }
    
    public function criar($nome, $email, $senha, $tipo_perfil = 'aluno') {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (nome, email, senha_hash, tipo_perfil) 
                VALUES (:nome, :email, :senha_hash, :tipo_perfil)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':senha_hash' => $senha_hash,
            ':tipo_perfil' => $tipo_perfil
        ]);
    }
    
    public function atualizarUltimoAcesso($id) {
        $sql = "UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}