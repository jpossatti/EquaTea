-- ============================================================
-- SCRIPT DE SEED - EQUATEA
-- Dados iniciais para teste e demonstração do sistema
-- 
-- ATENÇÃO: Este script deve ser executado APÓS o script de criação
-- do banco de dados (script_criacao.sql)
-- 
-- @package EquaTEA
-- @subpackage Database
-- @version 1.0
-- ============================================================

-- ============================================================
-- 1. SELECIONAR O BANCO DE DADOS
-- ============================================================

USE equatea_db;

-- ============================================================
-- 2. LIMPAR DADOS EXISTENTES (opcional - comentar se não quiser)
-- ============================================================

-- ATENÇÃO: Descomente as linhas abaixo para limpar todos os dados
-- antes de inserir os novos. Use com cuidado!

-- SET FOREIGN_KEY_CHECKS = 0;
-- TRUNCATE TABLE registro_erros;
-- TRUNCATE TABLE progresso_aluno;
-- TRUNCATE TABLE ajuda_exemplos;
-- TRUNCATE TABLE equacoes;
-- TRUNCATE TABLE alunos;
-- TRUNCATE TABLE professores;
-- TRUNCATE TABLE usuarios;
-- TRUNCATE TABLE logs_sistema;
-- TRUNCATE TABLE sessao;
-- SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 3. INSERIR USUÁRIOS
-- ============================================================

-- Senhas padrão (hash gerado com password_hash):
-- - professor123 -> $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- - aluno123     -> $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

-- ============================================================
-- 3.1. PROFESSORES (2 usuários)
-- ============================================================

-- Professor Padrão (acesso principal)
INSERT INTO usuarios (nome, email, senha_hash, tipo_perfil, data_cadastro, ultimo_acesso, ativo) VALUES
('Professor Carlos Silva', 'carlos@escola.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'professor', NOW(), NOW(), 1);

-- Professora Ana Santos (segunda professora)
INSERT INTO usuarios (nome, email, senha_hash, tipo_perfil, data_cadastro, ultimo_acesso, ativo) VALUES
('Professora Ana Santos', 'ana@escola.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'professor', NOW(), NOW(), 1);

-- ============================================================
-- 3.2. ALUNOS (10 alunos)
-- ============================================================

-- Aluno 1: Ana Silva
INSERT INTO usuarios (nome, email, senha_hash, tipo_perfil, data_cadastro, ultimo_acesso, ativo) VALUES
('Ana Silva', 'ana.silva@escola.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'aluno', NOW(), NOW(), 1);

-- Aluno 2: Carlos Souza
INSERT INTO usuarios (nome, email, senha_hash, tipo_perfil, data_cadastro, ultimo_acesso, ativo) VALUES
('Carlos Souza', 'carlos.souza@escola.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'aluno', NOW(), NOW(), 1);

-- Aluno 3: Mariana Costa
INSERT INTO usuarios (nome, email, senha_hash, tipo_perfil, data_cadastro, ultimo_acesso, ativo) VALUES
('Mariana Costa', 'mariana.costa@escola.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'aluno', NOW(), NOW(), 1);

-- Aluno 4: João Pereira
INSERT INTO usuarios (nome, email, senha_hash, tipo_perfil, data_cadastro, ultimo_acesso, ativo) VALUES
('João Pereira', 'joao.pereira@escola.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'aluno', NOW(), NOW(), 1);

-- Aluno 5: Beatriz Lima
INSERT INTO usuarios (nome, email, senha_hash, tipo_perfil, data_cadastro, ultimo_acesso, ativo) VALUES
('Beatriz Lima', 'beatriz.lima@escola.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'aluno', NOW(), NOW(), 1);

-- Aluno 6: Rafael Santos
INSERT INTO usuarios (nome, email, senha_hash, tipo_perfil, data_cadastro, ultimo_acesso, ativo) VALUES
('Rafael Santos', 'rafael.santos@escola.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'aluno', NOW(), NOW(), 1);

-- Aluno 7: Fernanda Oliveira
INSERT INTO usuarios (nome, email, senha_hash, tipo_perfil, data_cadastro, ultimo_acesso, ativo) VALUES
('Fernanda Oliveira', 'fernanda.oliveira@escola.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'aluno', NOW(), NOW(), 1);

-- Aluno 8: Lucas Mendes
INSERT INTO usuarios (nome, email, senha_hash, tipo_perfil, data_cadastro, ultimo_acesso, ativo) VALUES
('Lucas Mendes', 'lucas.mendes@escola.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'aluno', NOW(), NOW(), 1);

-- Aluno 9: Júlia Rocha
INSERT INTO usuarios (nome, email, senha_hash, tipo_perfil, data_cadastro, ultimo_acesso, ativo) VALUES
('Júlia Rocha', 'julia.rocha@escola.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'aluno', NOW(), NOW(), 1);

-- Aluno 10: Pedro Alves
INSERT INTO usuarios (nome, email, senha_hash, tipo_perfil, data_cadastro, ultimo_acesso, ativo) VALUES
('Pedro Alves', 'pedro.alves@escola.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'aluno', NOW(), NOW(), 1);

-- ============================================================
-- 4. INSERIR PROFESSORES (detalhes)
-- ============================================================

INSERT INTO professores (usuario_id, disciplina, escola, telefone, data_cadastro) VALUES
(1, 'Matemática', 'Escola Modelo de Ensino Médio', '(11) 9999-8888', NOW()),
(2, 'Matemática', 'Escola Modelo de Ensino Médio', '(11) 9999-7777', NOW());

-- ============================================================
-- 5. INSERIR ALUNOS (detalhes)
-- ============================================================

INSERT INTO alunos (usuario_id, idade, nivel_tea, escola, turma, data_cadastro) VALUES
(3, 16, 'suporte1', 'Escola Modelo de Ensino Médio', '1º EM A', NOW()),
(4, 17, 'suporte2', 'Escola Modelo de Ensino Médio', '1º EM A', NOW()),
(5, 15, 'suporte1', 'Escola Modelo de Ensino Médio', '1º EM B', NOW()),
(6, 18, 'suporte2', 'Escola Modelo de Ensino Médio', '2º EM A', NOW()),
(7, 16, 'suporte1', 'Escola Modelo de Ensino Médio', '2º EM A', NOW()),
(8, 17, 'suporte1', 'Escola Modelo de Ensino Médio', '1º EM A', NOW()),
(9, 15, 'suporte2', 'Escola Modelo de Ensino Médio', '1º EM B', NOW()),
(10, 18, 'suporte1', 'Escola Modelo de Ensino Médio', '2º EM A', NOW()),
(11, 16, 'suporte2', 'Escola Modelo de Ensino Médio', '2º EM A', NOW()),
(12, 17, 'suporte1', 'Escola Modelo de Ensino Médio', '1º EM A', NOW());

-- ============================================================
-- 6. INSERIR EQUAÇÕES (30 equações - 10 por dificuldade)
-- ============================================================

-- ============================================================
-- 6.1. EQUAÇÕES FÁCEIS (10)
-- ============================================================

INSERT INTO equacoes (a, b, c, solucao, dificuldade, data_cadastro) VALUES
(1, 3, 7, 4, 'facil', NOW()),    -- x + 3 = 7 → x = 4
(2, 1, 9, 4, 'facil', NOW()),    -- 2x + 1 = 9 → x = 4
(1, 5, 12, 7, 'facil', NOW()),   -- x + 5 = 12 → x = 7
(3, 2, 14, 4, 'facil', NOW()),   -- 3x + 2 = 14 → x = 4
(1, 8, 15, 7, 'facil', NOW()),   -- x + 8 = 15 → x = 7
(2, 4, 10, 3, 'facil', NOW()),   -- 2x + 4 = 10 → x = 3
(1, 2, 8, 6, 'facil', NOW()),    -- x + 2 = 8 → x = 6
(4, 1, 17, 4, 'facil', NOW()),   -- 4x + 1 = 17 → x = 4
(1, 6, 9, 3, 'facil', NOW()),    -- x + 6 = 9 → x = 3
(2, 5, 11, 3, 'facil', NOW());   -- 2x + 5 = 11 → x = 3

-- ============================================================
-- 6.2. EQUAÇÕES MÉDIAS (10)
-- ============================================================

INSERT INTO equacoes (a, b, c, solucao, dificuldade, data_cadastro) VALUES
(3, 5, 20, 5, 'medio', NOW()),   -- 3x + 5 = 20 → x = 5
(2, 7, 19, 6, 'medio', NOW()),   -- 2x + 7 = 19 → x = 6
(5, 3, 18, 3, 'medio', NOW()),   -- 5x + 3 = 18 → x = 3
(4, -2, 14, 4, 'medio', NOW()),  -- 4x - 2 = 14 → x = 4
(3, -4, 14, 6, 'medio', NOW()),  -- 3x - 4 = 14 → x = 6
(6, 2, 20, 3, 'medio', NOW()),   -- 6x + 2 = 20 → x = 3
(5, -3, 17, 4, 'medio', NOW()),  -- 5x - 3 = 17 → x = 4
(7, -11, 10, 3, 'medio', NOW()), -- 7x - 11 = 10 → x = 3
(4, -11, 9, 5, 'medio', NOW()),  -- 4x - 11 = 9 → x = 5
(8, -17, 7, 3, 'medio', NOW());  -- 8x - 17 = 7 → x = 3

-- ============================================================
-- 6.3. EQUAÇÕES DIFÍCEIS (10)
-- ============================================================

INSERT INTO equacoes (a, b, c, solucao, dificuldade, data_cadastro) VALUES
(6, 5, 17, 2, 'dificil', NOW()),   -- 6x + 5 = 17 → x = 2
(7, 3, 17, 2, 'dificil', NOW()),   -- 7x + 3 = 17 → x = 2
(8, -3, 13, 2, 'dificil', NOW()),  -- 8x - 3 = 13 → x = 2
(-3, 5, -4, 3, 'dificil', NOW()),  -- -3x + 5 = -4 → x = 3
(7, -5, 16, 3, 'dificil', NOW()),  -- 7x - 5 = 16 → x = 3
(9, -5, 13, 2, 'dificil', NOW()),  -- 9x - 5 = 13 → x = 2
(11, -2, 20, 2, 'dificil', NOW()), -- 11x - 2 = 20 → x = 2
(-4, -5, -17, 3, 'dificil', NOW()),-- -4x - 5 = -17 → x = 3
(5, -7, 13, 4, 'dificil', NOW()),  -- 5x - 7 = 13 → x = 4
(6, -11, 7, 3, 'dificil', NOW());  -- 6x - 11 = 7 → x = 3

-- ============================================================
-- 7. INSERIR EXEMPLOS DE AJUDA (20 exemplos - 5 por passo)
-- ============================================================

-- ============================================================
-- 7.1. PASSO 1: IDENTIFICAR TERMOS
-- ============================================================

INSERT INTO ajuda_exemplos (passo, a, b, c, solucao, descricao, data_cadastro) VALUES
(1, 2, 3, 7, 2, '2x + 3 = 7 → Termo com x: 2x, sem x: +3 e 7', NOW()),
(1, 1, 4, 9, 5, 'x + 4 = 9 → Termo com x: x, sem x: +4 e 9', NOW()),
(1, 3, 1, 10, 3, '3x + 1 = 10 → Termo com x: 3x, sem x: +1 e 10', NOW()),
(1, 2, 5, 11, 3, '2x + 5 = 11 → Termo com x: 2x, sem x: +5 e 11', NOW()),
(1, 4, 2, 14, 3, '4x + 2 = 14 → Termo com x: 4x, sem x: +2 e 14', NOW());

-- ============================================================
-- 7.2. PASSO 2: ISOLAR TERMO COM X
-- ============================================================

INSERT INTO ajuda_exemplos (passo, a, b, c, solucao, descricao, data_cadastro) VALUES
(2, 2, 3, 7, 2, '2x + 3 = 7 → Subtraia 3 de ambos: 2x = 7 - 3', NOW()),
(2, 1, 4, 9, 5, 'x + 4 = 9 → Subtraia 4 de ambos: x = 9 - 4', NOW()),
(2, 3, 1, 10, 3, '3x + 1 = 10 → Subtraia 1 de ambos: 3x = 10 - 1', NOW()),
(2, 2, 5, 11, 3, '2x + 5 = 11 → Subtraia 5 de ambos: 2x = 11 - 5', NOW()),
(2, 4, -2, 10, 3, '4x - 2 = 10 → Some 2 de ambos: 4x = 10 + 2', NOW());

-- ============================================================
-- 7.3. PASSO 3: CALCULAR LADO DIREITO
-- ============================================================

INSERT INTO ajuda_exemplos (passo, a, b, c, solucao, descricao, data_cadastro) VALUES
(3, 2, 3, 7, 2, '2x = 7 - 3 → 7 - 3 = 4 → 2x = 4', NOW()),
(3, 1, 4, 9, 5, 'x = 9 - 4 → 9 - 4 = 5 → x = 5', NOW()),
(3, 3, 1, 10, 3, '3x = 10 - 1 → 10 - 1 = 9 → 3x = 9', NOW()),
(3, 2, 5, 11, 3, '2x = 11 - 5 → 11 - 5 = 6 → 2x = 6', NOW()),
(3, 4, -2, 10, 3, '4x = 10 + 2 → 10 + 2 = 12 → 4x = 12', NOW());

-- ============================================================
-- 7.4. PASSO 4: ISOLAR X
-- ============================================================

INSERT INTO ajuda_exemplos (passo, a, b, c, solucao, descricao, data_cadastro) VALUES
(4, 2, 3, 7, 2, '2x = 4 → Divida ambos por 2: x = 4 ÷ 2 = 2', NOW()),
(4, 1, 4, 9, 5, 'x = 5 (já isolado) → x = 5', NOW()),
(4, 3, 1, 10, 3, '3x = 9 → Divida ambos por 3: x = 9 ÷ 3 = 3', NOW()),
(4, 2, 5, 11, 3, '2x = 6 → Divida ambos por 2: x = 6 ÷ 2 = 3', NOW()),
(4, 4, -2, 10, 3, '4x = 12 → Divida ambos por 4: x = 12 ÷ 4 = 3', NOW());

-- ============================================================
-- 8. INSERIR DADOS DE PROGRESSO (para testes)
-- ============================================================

-- Progresso de vários alunos em diferentes equações

-- Aluno Ana (id: 3) - completou algumas equações
INSERT INTO progresso_aluno (aluno_id, equacao_id, passo_atual, concluida, data_inicio, data_conclusao, tentativas) VALUES
(3, 1, 4, 1, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY), 2),
(3, 3, 4, 1, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY), 3),
(3, 5, 2, 0, DATE_SUB(NOW(), INTERVAL 1 DAY), NULL, 1),
(3, 7, 1, 0, DATE_SUB(NOW(), INTERVAL 5 HOUR), NULL, 0);

-- Aluno Carlos (id: 4) - iniciou algumas, com erros
INSERT INTO progresso_aluno (aluno_id, equacao_id, passo_atual, concluida, data_inicio, data_conclusao, tentativas) VALUES
(4, 2, 4, 1, DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY), 4),
(4, 4, 2, 0, DATE_SUB(NOW(), INTERVAL 2 DAY), NULL, 2),
(4, 6, 1, 0, DATE_SUB(NOW(), INTERVAL 1 DAY), NULL, 0);

-- Aluno Mariana (id: 5) - várias concluídas, boa performance
INSERT INTO progresso_aluno (aluno_id, equacao_id, passo_atual, concluida, data_inicio, data_conclusao, tentativas) VALUES
(5, 2, 4, 1, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY), 1),
(5, 4, 4, 1, DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY), 2),
(5, 6, 4, 1, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY), 1),
(5, 8, 4, 1, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY), 2),
(5, 10, 3, 0, DATE_SUB(NOW(), INTERVAL 1 DAY), NULL, 1);

-- Aluno João (id: 6) - dificuldade, vários erros
INSERT INTO progresso_aluno (aluno_id, equacao_id, passo_atual, concluida, data_inicio, data_conclusao, tentativas) VALUES
(6, 1, 4, 1, DATE_SUB(NOW(), INTERVAL 6 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY), 5),
(6, 2, 3, 0, DATE_SUB(NOW(), INTERVAL 4 DAY), NULL, 3),
(6, 3, 2, 0, DATE_SUB(NOW(), INTERVAL 2 DAY), NULL, 2);

-- Aluno Beatriz (id: 7) - iniciante
INSERT INTO progresso_aluno (aluno_id, equacao_id, passo_atual, concluida, data_inicio, data_conclusao, tentativas) VALUES
(7, 1, 4, 1, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY), 2),
(7, 2, 1, 0, DATE_SUB(NOW(), INTERVAL 1 DAY), NULL, 0);

-- Aluno Rafael (id: 8) - várias concluídas
INSERT INTO progresso_aluno (aluno_id, equacao_id, passo_atual, concluida, data_inicio, data_conclusao, tentativas) VALUES
(8, 1, 4, 1, DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY), 2),
(8, 3, 4, 1, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY), 1),
(8, 5, 4, 1, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY), 2),
(8, 7, 2, 0, DATE_SUB(NOW(), INTERVAL 1 DAY), NULL, 1);

-- Aluno Fernanda (id: 9) - algumas concluídas, outras em andamento
INSERT INTO progresso_aluno (aluno_id, equacao_id, passo_atual, concluida, data_inicio, data_conclusao, tentativas) VALUES
(9, 2, 4, 1, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY), 3),
(9, 4, 4, 1, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY), 2),
(9, 6, 3, 0, DATE_SUB(NOW(), INTERVAL 2 DAY), NULL, 1);

-- Aluno Lucas (id: 10) - poucas atividades
INSERT INTO progresso_aluno (aluno_id, equacao_id, passo_atual, concluida, data_inicio, data_conclusao, tentativas) VALUES
(10, 1, 4, 1, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY), 3);

-- Aluno Júlia (id: 11) - em andamento
INSERT INTO progresso_aluno (aluno_id, equacao_id, passo_atual, concluida, data_inicio, data_conclusao, tentativas) VALUES
(11, 3, 2, 0, DATE_SUB(NOW(), INTERVAL 1 DAY), NULL, 1);

-- Aluno Pedro (id: 12) - iniciou recentemente
INSERT INTO progresso_aluno (aluno_id, equacao_id, passo_atual, concluida, data_inicio, data_conclusao, tentativas) VALUES
(12, 1, 1, 0, DATE_SUB(NOW(), INTERVAL 2 HOUR), NULL, 0);

-- ============================================================
-- 9. INSERIR REGISTRO DE ERROS (para testes de relatórios)
-- ============================================================

-- Erros do aluno Ana (id: 3)
INSERT INTO registro_erros (aluno_id, equacao_id, passo, tipo_erro, resposta_fornecida, resposta_esperada, data_erro) VALUES
(3, 1, 2, 'operacao_inversa', '2x = 7 + 3', '2x = 7 - 3', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, 1, 3, 'calculo_errado', '2x = 9', '2x = 4', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, 3, 2, 'sinal_trocado', '3x = 14 - 2', '3x = 14 - 5', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(3, 3, 4, 'divisao_incorreta', 'x = 16 ÷ 3 = 5', 'x = 9 ÷ 3 = 3', DATE_SUB(NOW(), INTERVAL 3 DAY));

-- Erros do aluno Carlos (id: 4)
INSERT INTO registro_erros (aluno_id, equacao_id, passo, tipo_erro, resposta_fornecida, resposta_esperada, data_erro) VALUES
(4, 2, 1, 'identificacao_errada', 'Termo com x: 5', 'Termo com x: 2x', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(4, 2, 2, 'operacao_inversa', '2x = 9 + 1', '2x = 9 - 1', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(4, 2, 3, 'calculo_errado', '2x = 10', '2x = 8', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(4, 4, 2, 'sinal_trocado', '4x = 14 + 2', '4x = 14 - (-2)', DATE_SUB(NOW(), INTERVAL 2 DAY));

-- Erros do aluno Mariana (id: 5) - poucos erros
INSERT INTO registro_erros (aluno_id, equacao_id, passo, tipo_erro, resposta_fornecida, resposta_esperada, data_erro) VALUES
(5, 4, 2, 'operacao_inversa', '4x = 14 + 2', '4x = 14 - (-2)', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(5, 4, 4, 'divisao_incorreta', 'x = 16 ÷ 4 = 5', 'x = 16 ÷ 4 = 4', DATE_SUB(NOW(), INTERVAL 4 DAY));

-- Erros do aluno João (id: 6) - muitos erros
INSERT INTO registro_erros (aluno_id, equacao_id, passo, tipo_erro, resposta_fornecida, resposta_esperada, data_erro) VALUES
(6, 1, 1, 'identificacao_errada', 'Termo com x: 5', 'Termo com x: x', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(6, 1, 2, 'operacao_inversa', 'x = 7 + 3', 'x = 7 - 3', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(6, 1, 2, 'operacao_inversa', 'x = 7 + 3', 'x = 7 - 3', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(6, 1, 3, 'calculo_errado', 'x = 10', 'x = 4', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(6, 1, 4, 'divisao_incorreta', 'x = 4', 'x = 4', DATE_SUB(NOW(), INTERVAL 5 DAY)), -- Acertou na 5ª tentativa
(6, 2, 1, 'identificacao_errada', 'Termo com x: x', 'Termo com x: 2x', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(6, 2, 2, 'sinal_trocado', '2x = 9 + 1', '2x = 9 - 1', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(6, 2, 2, 'operacao_inversa', '2x = 9 - 1', '2x = 9 - 1', DATE_SUB(NOW(), INTERVAL 4 DAY)), -- Acertou na 3ª tentativa
(6, 3, 1, 'identificacao_errada', 'Termo com x: 5', 'Termo com x: 3x', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(6, 3, 2, 'sinal_trocado', '3x = 26 + 8', '3x = 26 - 8', DATE_SUB(NOW(), INTERVAL 2 DAY));

-- Erros do aluno Beatriz (id: 7)
INSERT INTO registro_erros (aluno_id, equacao_id, passo, tipo_erro, resposta_fornecida, resposta_esperada, data_erro) VALUES
(7, 1, 2, 'operacao_inversa', 'x = 7 + 3', 'x = 7 - 3', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(7, 1, 3, 'calculo_errado', 'x = 11', 'x = 4', DATE_SUB(NOW(), INTERVAL 3 DAY));

-- Erros do aluno Rafael (id: 8)
INSERT INTO registro_erros (aluno_id, equacao_id, passo, tipo_erro, resposta_fornecida, resposta_esperada, data_erro) VALUES
(8, 1, 1, 'identificacao_errada', 'Termo com x: 5', 'Termo com x: x', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(8, 1, 2, 'sinal_trocado', 'x = 7 + 3', 'x = 7 - 3', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(8, 5, 2, 'operacao_inversa', '5x = 12 - 3', '5x = 15 - 8', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(8, 5, 3, 'calculo_errado', '5x = 9', '5x = 7', DATE_SUB(NOW(), INTERVAL 2 DAY));

-- Erros do aluno Fernanda (id: 9)
INSERT INTO registro_erros (aluno_id, equacao_id, passo, tipo_erro, resposta_fornecida, resposta_esperada, data_erro) VALUES
(9, 2, 1, 'identificacao_errada', 'Termo com x: x', 'Termo com x: 2x', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(9, 2, 2, 'operacao_inversa', '2x = 9 + 1', '2x = 9 - 1', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(9, 2, 4, 'divisao_incorreta', 'x = 8 ÷ 2 = 5', 'x = 8 ÷ 2 = 4', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(9, 4, 2, 'sinal_trocado', '4x = 14 + 2', '4x = 14 - (-2)', DATE_SUB(NOW(), INTERVAL 3 DAY));

-- Erros do aluno Lucas (id: 10)
INSERT INTO registro_erros (aluno_id, equacao_id, passo, tipo_erro, resposta_fornecida, resposta_esperada, data_erro) VALUES
(10, 1, 1, 'identificacao_errada', 'Termo com x: 3', 'Termo com x: x', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(10, 1, 2, 'operacao_inversa', 'x = 7 + 3', 'x = 7 - 3', DATE_SUB(NOW(), INTERVAL 2 DAY));

-- Erros do aluno Júlia (id: 11)
INSERT INTO registro_erros (aluno_id, equacao_id, passo, tipo_erro, resposta_fornecida, resposta_esperada, data_erro) VALUES
(11, 3, 1, 'identificacao_errada', 'Termo com x: 10', 'Termo com x: 3x', DATE_SUB(NOW(), INTERVAL 1 DAY));

-- ============================================================
-- 10. INSERIR LOGS DO SISTEMA (para auditoria)
-- ============================================================

INSERT INTO logs_sistema (usuario_id, acao, descricao, ip_address, data_log) VALUES
(1, 'LOGIN', 'Professor Carlos Silva realizou login', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(1, 'ACESSO_ADMIN', 'Professor acessou o painel administrativo', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(2, 'LOGIN', 'Professora Ana Santos realizou login', '192.168.1.101', DATE_SUB(NOW(), INTERVAL 4 HOUR)),
(3, 'LOGIN', 'Aluna Ana Silva realizou login', '192.168.1.102', DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(3, 'EXERCICIO_INICIO', 'Aluna Ana iniciou exercício equação_id=1', '192.168.1.102', DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(3, 'EXERCICIO_CONCLUIDO', 'Aluna Ana concluiu exercício equação_id=1', '192.168.1.102', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(4, 'LOGIN', 'Aluno Carlos Souza realizou login', '192.168.1.103', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(4, 'EXERCICIO_INICIO', 'Aluno Carlos iniciou exercício equação_id=3', '192.168.1.103', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(1, 'RELATORIO_VISUALIZADO', 'Professor visualizou relatório de erros', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(5, 'LOGIN', 'Aluna Mariana Costa realizou login', '192.168.1.104', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(5, 'EXERCICIO_CONCLUIDO', 'Aluna Mariana concluiu exercício equação_id=5', '192.168.1.104', DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(1, 'EXPORTAR_CSV', 'Professor exportou relatório de alunos', '192.168.1.100', DATE_SUB(NOW(), INTERVAL 15 MINUTE));

-- ============================================================
-- 11. CONSULTAS DE VERIFICAÇÃO
-- ============================================================

-- Exibe resumo dos dados inseridos
SELECT '=== RESULTADO DO SEED ===' AS '';

SELECT 'Total de usuários:' AS 'Descrição', COUNT(*) AS 'Quantidade' FROM usuarios
UNION ALL
SELECT 'Total de professores:', COUNT(*) FROM professores
UNION ALL
SELECT 'Total de alunos:', COUNT(*) FROM alunos
UNION ALL
SELECT 'Total de equações:', COUNT(*) FROM equacoes
UNION ALL
SELECT '  - Fáceis:', COUNT(*) FROM equacoes WHERE dificuldade = 'facil'
UNION ALL
SELECT '  - Médias:', COUNT(*) FROM equacoes WHERE dificuldade = 'medio'
UNION ALL
SELECT '  - Difíceis:', COUNT(*) FROM equacoes WHERE dificuldade = 'dificil'
UNION ALL
SELECT 'Total de exemplos de ajuda:', COUNT(*) FROM ajuda_exemplos
UNION ALL
SELECT '  - Passo 1:', COUNT(*) FROM ajuda_exemplos WHERE passo = 1
UNION ALL
SELECT '  - Passo 2:', COUNT(*) FROM ajuda_exemplos WHERE passo = 2
UNION ALL
SELECT '  - Passo 3:', COUNT(*) FROM ajuda_exemplos WHERE passo = 3
UNION ALL
SELECT '  - Passo 4:', COUNT(*) FROM ajuda_exemplos WHERE passo = 4
UNION ALL
SELECT 'Total de progressos:', COUNT(*) FROM progresso_aluno
UNION ALL
SELECT 'Total de erros registrados:', COUNT(*) FROM registro_erros
UNION ALL
SELECT 'Total de logs do sistema:', COUNT(*) FROM logs_sistema;

-- ============================================================
-- 12. EXIBIR DADOS PARA CONSULTA RÁPIDA
-- ============================================================

-- Listar todos os usuários com seus perfis
SELECT 
    '=== USUÁRIOS ===' AS '',
    id,
    nome,
    email,
    tipo_perfil,
    CASE WHEN ativo = 1 THEN 'Ativo' ELSE 'Inativo' END AS status
FROM usuarios
ORDER BY tipo_perfil, nome;

-- Listar todas as equações
SELECT 
    '=== EQUAÇÕES ===' AS '',
    id,
    CONCAT(a, 'x + ', b, ' = ', c) AS equacao,
    CONCAT('x = ', solucao) AS solucao,
    dificuldade
FROM equacoes
ORDER BY FIELD(dificuldade, 'facil', 'medio', 'dificil'), id;

-- Listar exemplos de ajuda por passo
SELECT 
    '=== EXEMPLOS DE AJUDA ===' AS '',
    passo,
    CONCAT(a, 'x + ', b, ' = ', c) AS equacao,
    CONCAT('x = ', solucao) AS solucao,
    descricao
FROM ajuda_exemplos
ORDER BY passo, id;

-- ============================================================
-- 13. CREDENCIAIS DE ACESSO PARA TESTE
-- ============================================================

SELECT '=== CREDENCIAIS DE ACESSO ===' AS '';
SELECT 'Professor:' AS 'Perfil', 'carlos@escola.com' AS 'Email', 'professor123' AS 'Senha'
UNION ALL
SELECT 'Professor:', 'ana@escola.com', 'professor123'
UNION ALL
SELECT 'Aluno:', 'ana.silva@escola.com', 'aluno123'
UNION ALL
SELECT 'Aluno:', 'carlos.souza@escola.com', 'aluno123'
UNION ALL
SELECT 'Aluno:', 'mariana.costa@escola.com', 'aluno123'
UNION ALL
SELECT 'Aluno:', 'joao.pereira@escola.com', 'aluno123'
UNION ALL
SELECT 'Aluno:', 'beatriz.lima@escola.com', 'aluno123';

-- ============================================================
-- FIM DO SCRIPT DE SEED
-- ============================================================