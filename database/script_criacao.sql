-- ============================================================
-- BANCO DE DADOS EQUATEA - SCRIPT COMPLETO
-- ============================================================

CREATE DATABASE IF NOT EXISTS equatea_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE equatea_db;

-- Tabela: usuarios (base para autenticação)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    tipo_perfil ENUM('aluno', 'professor') NOT NULL DEFAULT 'aluno',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acesso DATETIME NULL,
    ativo BOOLEAN DEFAULT TRUE,
    INDEX idx_email (email),
    INDEX idx_tipo_perfil (tipo_perfil)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela: alunos
CREATE TABLE IF NOT EXISTS alunos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    idade INT NOT NULL CHECK (idade BETWEEN 14 AND 21),
    nivel_tea ENUM('suporte1', 'suporte2') NOT NULL,
    escola VARCHAR(100) NULL,
    turma VARCHAR(20) NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_id (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela: professores
CREATE TABLE IF NOT EXISTS professores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    disciplina VARCHAR(50) NOT NULL DEFAULT 'Matemática',
    escola VARCHAR(100) NOT NULL,
    telefone VARCHAR(20) NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela: equacoes
CREATE TABLE IF NOT EXISTS equacoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    a INT NOT NULL CHECK (a BETWEEN -20 AND 20 AND a != 0),
    b INT NOT NULL CHECK (b BETWEEN -20 AND 20),
    c INT NOT NULL CHECK (c BETWEEN -20 AND 20),
    solucao INT NOT NULL,
    dificuldade ENUM('facil', 'medio', 'dificil') DEFAULT 'facil',
    enunciado VARCHAR(255) NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_dificuldade (dificuldade)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela: progresso_aluno
CREATE TABLE IF NOT EXISTS progresso_aluno (
    id INT PRIMARY KEY AUTO_INCREMENT,
    aluno_id INT NOT NULL,
    equacao_id INT NOT NULL,
    passo_atual TINYINT DEFAULT 1 CHECK (passo_atual BETWEEN 1 AND 4),
    concluida BOOLEAN DEFAULT FALSE,
    data_inicio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_conclusao DATETIME NULL,
    tentativas TINYINT DEFAULT 0,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
    FOREIGN KEY (equacao_id) REFERENCES equacoes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_progresso (aluno_id, equacao_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela: registro_erros
CREATE TABLE IF NOT EXISTS registro_erros (
    id INT PRIMARY KEY AUTO_INCREMENT,
    aluno_id INT NOT NULL,
    equacao_id INT NOT NULL,
    passo TINYINT NOT NULL CHECK (passo BETWEEN 1 AND 4),
    tipo_erro ENUM('operacao_inversa', 'calculo_errado', 'sinal_trocado', 
                    'divisao_incorreta', 'identificacao_errada', 'outro') NOT NULL,
    resposta_fornecida VARCHAR(100) NULL,
    resposta_esperada VARCHAR(100) NULL,
    data_erro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (aluno_id) REFERENCES alunos(id) ON DELETE CASCADE,
    FOREIGN KEY (equacao_id) REFERENCES equacoes(id) ON DELETE CASCADE,
    INDEX idx_aluno_equacao (aluno_id, equacao_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela: ajuda_exemplos
CREATE TABLE IF NOT EXISTS ajuda_exemplos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    passo TINYINT NOT NULL CHECK (passo BETWEEN 1 AND 4),
    a INT NOT NULL CHECK (a BETWEEN -5 AND 5 AND a != 0),
    b INT NOT NULL CHECK (b BETWEEN -10 AND 10),
    c INT NOT NULL CHECK (c BETWEEN -10 AND 10),
    solucao INT NOT NULL,
    descricao VARCHAR(255) NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_passo (passo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- INSERÇÃO DE DADOS INICIAIS
-- ============================================================

-- Usuário Professor (senha: professor123)
INSERT INTO usuarios (nome, email, senha_hash, tipo_perfil) VALUES
('Professor Padrão', 'professor@equatea.com', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'professor');

-- Professor
INSERT INTO professores (usuario_id, disciplina, escola, telefone) VALUES
(1, 'Matemática', 'Escola Modelo', '(11) 99999-9999');

-- Usuários Alunos (senha: aluno123)
INSERT INTO usuarios (nome, email, senha_hash, tipo_perfil) VALUES
('Ana Silva', 'ana@teste.com', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'aluno'),
('Carlos Souza', 'carlos@teste.com', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'aluno'),
('Mariana Costa', 'mariana@teste.com', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'aluno');

-- Alunos
INSERT INTO alunos (usuario_id, idade, nivel_tea, escola, turma) VALUES
(2, 16, 'suporte1', 'Escola Modelo', '1º EM A'),
(3, 17, 'suporte2', 'Escola Modelo', '1º EM A'),
(4, 15, 'suporte1', 'Escola Modelo', '1º EM B');

-- Equações de exemplo
INSERT INTO equacoes (a, b, c, solucao, dificuldade) VALUES
(1, 3, 7, 4, 'facil'),
(2, 1, 9, 4, 'facil'),
(1, 5, 12, 7, 'facil'),
(3, 2, 14, 4, 'facil'),
(3, 5, 20, 5, 'medio'),
(2, 7, 19, 6, 'medio'),
(5, 3, 18, 3, 'medio'),
(6, 5, 17, 2, 'dificil'),
(7, 3, 17, 2, 'dificil'),
(8, -3, 13, 2, 'dificil');

-- Exemplos de ajuda
INSERT INTO ajuda_exemplos (passo, a, b, c, solucao, descricao) VALUES
(1, 2, 3, 7, 2, '2x + 3 = 7 → Termo com x: 2x, sem x: +3 e 7'),
(1, 1, 4, 9, 5, 'x + 4 = 9 → Termo com x: x, sem x: +4 e 9'),
(2, 2, 3, 7, 2, '2x + 3 = 7 → Subtraia 3 de ambos: 2x = 7 - 3'),
(2, 1, 4, 9, 5, 'x + 4 = 9 → Subtraia 4 de ambos: x = 9 - 4'),
(3, 2, 3, 7, 2, '2x = 7 - 3 → 7 - 3 = 4 → 2x = 4'),
(3, 1, 4, 9, 5, 'x = 9 - 4 → 9 - 4 = 5 → x = 5'),
(4, 2, 3, 7, 2, '2x = 4 → Divida ambos por 2: x = 4 ÷ 2 = 2'),
(4, 1, 4, 9, 5, 'x = 5 (já isolado) → x = 5');