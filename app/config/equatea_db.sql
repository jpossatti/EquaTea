-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 25/08/2026 às 00:31
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `equatea_db`
--

DELIMITER $$
--
-- Procedimentos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_limpar_sessoes_expiradas` ()   BEGIN
    DELETE FROM sessao 
    WHERE expiracao < NOW() OR ativa = FALSE;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ajuda_exemplos`
--

CREATE TABLE `ajuda_exemplos` (
  `id` int(11) NOT NULL,
  `passo` tinyint(4) NOT NULL CHECK (`passo` between 1 and 4),
  `a` int(11) NOT NULL CHECK (`a` between -5 and 5 and `a` <> 0),
  `b` int(11) NOT NULL CHECK (`b` between -10 and 10),
  `c` int(11) NOT NULL CHECK (`c` between -10 and 10),
  `solucao` int(11) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `ajuda_exemplos`
--

INSERT INTO `ajuda_exemplos` (`id`, `passo`, `a`, `b`, `c`, `solucao`, `descricao`, `data_cadastro`) VALUES
(1, 1, 2, 3, 7, 2, '2x + 3 = 7 → Termo com x: 2x, sem x: +3 e 7', '2026-08-18 13:54:12'),
(2, 1, 1, 4, 9, 5, 'x + 4 = 9 → Termo com x: x, sem x: +4 e 9', '2026-08-18 13:54:12'),
(3, 1, 3, 1, 10, 3, '3x + 1 = 10 → Termo com x: 3x, sem x: +1 e 10', '2026-08-18 13:54:12'),
(4, 2, 2, 3, 7, 2, '2x + 3 = 7 → Subtraia 3 de ambos: 2x = 7 - 3', '2026-08-18 13:54:12'),
(5, 2, 1, 4, 9, 5, 'x + 4 = 9 → Subtraia 4 de ambos: x = 9 - 4', '2026-08-18 13:54:12'),
(6, 2, 3, 1, 10, 3, '3x + 1 = 10 → Subtraia 1 de ambos: 3x = 10 - 1', '2026-08-18 13:54:12'),
(7, 3, 2, 3, 7, 2, '2x = 7 - 3 → 7 - 3 = 4 → 2x = 4', '2026-08-18 13:54:12'),
(8, 3, 1, 4, 9, 5, 'x = 9 - 4 → 9 - 4 = 5 → x = 5', '2026-08-18 13:54:12'),
(9, 3, 3, 1, 10, 3, '3x = 10 - 1 → 10 - 1 = 9 → 3x = 9', '2026-08-18 13:54:12'),
(10, 4, 2, 3, 7, 2, '2x = 4 → Divida ambos por 2: x = 4 ÷ 2 = 2', '2026-08-18 13:54:12'),
(11, 4, 1, 4, 9, 5, 'x = 5 (já isolado) → x = 5', '2026-08-18 13:54:12'),
(12, 4, 3, 1, 10, 3, '3x = 9 → Divida ambos por 3: x = 9 ÷ 3 = 3', '2026-08-18 13:54:12');

-- --------------------------------------------------------

--
-- Estrutura para tabela `alunos`
--

CREATE TABLE `alunos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `idade` int(11) NOT NULL CHECK (`idade` between 14 and 21),
  `nivel_tea` enum('suporte1','suporte2') NOT NULL,
  `escola` varchar(100) DEFAULT NULL,
  `turma` varchar(20) DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `alunos`
--

INSERT INTO `alunos` (`id`, `usuario_id`, `idade`, `nivel_tea`, `escola`, `turma`, `data_cadastro`) VALUES
(1, 3, 16, 'suporte1', 'Escola Modelo de Ensino Médio', '1º EM A', '2026-08-18 13:54:11'),
(2, 4, 17, 'suporte2', 'Escola Modelo de Ensino Médio', '1º EM A', '2026-08-18 13:54:11'),
(3, 5, 15, 'suporte1', 'Escola Modelo de Ensino Médio', '1º EM B', '2026-08-18 13:54:11'),
(4, 6, 18, 'suporte2', 'Escola Modelo de Ensino Médio', '2º EM A', '2026-08-18 13:54:11'),
(5, 7, 16, 'suporte1', 'Escola Modelo de Ensino Médio', '2º EM A', '2026-08-18 13:54:11');

-- --------------------------------------------------------

--
-- Estrutura para tabela `equacoes`
--

CREATE TABLE `equacoes` (
  `id` int(11) NOT NULL,
  `a` int(11) NOT NULL CHECK (`a` between -20 and 20 and `a` <> 0),
  `b` int(11) NOT NULL CHECK (`b` between -20 and 20),
  `c` int(11) NOT NULL CHECK (`c` between -20 and 20),
  `solucao` int(11) NOT NULL,
  `dificuldade` enum('facil','medio','dificil') DEFAULT 'facil',
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `equacoes`
--

INSERT INTO `equacoes` (`id`, `a`, `b`, `c`, `solucao`, `dificuldade`, `data_cadastro`) VALUES
(1, 1, 3, 7, 4, 'facil', '2026-08-18 13:54:11'),
(2, 2, 1, 9, 4, 'facil', '2026-08-18 13:54:11'),
(3, 1, 5, 12, 7, 'facil', '2026-08-18 13:54:11'),
(4, 3, 2, 14, 4, 'facil', '2026-08-18 13:54:11'),
(5, 1, 8, 15, 7, 'facil', '2026-08-18 13:54:11'),
(6, 2, 4, 10, 3, 'facil', '2026-08-18 13:54:11'),
(7, 1, 2, 8, 6, 'facil', '2026-08-18 13:54:11'),
(8, 4, 1, 17, 4, 'facil', '2026-08-18 13:54:11'),
(9, 1, 6, 9, 3, 'facil', '2026-08-18 13:54:11'),
(10, 2, 5, 11, 3, 'facil', '2026-08-18 13:54:11'),
(11, 3, 5, 20, 5, 'medio', '2026-08-18 13:54:12'),
(12, 2, 7, 19, 6, 'medio', '2026-08-18 13:54:12'),
(13, 5, 3, 18, 3, 'medio', '2026-08-18 13:54:12'),
(14, 4, -2, 14, 4, 'medio', '2026-08-18 13:54:12'),
(15, 3, -4, 14, 6, 'medio', '2026-08-18 13:54:12'),
(16, 6, 2, 20, 3, 'medio', '2026-08-18 13:54:12'),
(17, 5, -3, 17, 4, 'medio', '2026-08-18 13:54:12'),
(18, 7, -11, 10, 3, 'medio', '2026-08-18 13:54:12'),
(19, 4, -11, 9, 5, 'medio', '2026-08-18 13:54:12'),
(20, 8, -17, 7, 3, 'medio', '2026-08-18 13:54:12'),
(21, 6, 5, 17, 2, 'dificil', '2026-08-18 13:54:12'),
(22, 7, 3, 17, 2, 'dificil', '2026-08-18 13:54:12'),
(23, 8, -3, 13, 2, 'dificil', '2026-08-18 13:54:12'),
(24, -3, 5, -4, 3, 'dificil', '2026-08-18 13:54:12'),
(25, 7, -5, 16, 3, 'dificil', '2026-08-18 13:54:12'),
(26, 9, -5, 13, 2, 'dificil', '2026-08-18 13:54:12'),
(27, 11, -2, 20, 2, 'dificil', '2026-08-18 13:54:12'),
(28, -4, -5, -17, 3, 'dificil', '2026-08-18 13:54:12'),
(29, 5, -7, 13, 4, 'dificil', '2026-08-18 13:54:12'),
(30, 6, -11, 7, 3, 'dificil', '2026-08-18 13:54:12'),
(34, 3, 4, 19, 5, 'medio', '2026-08-18 14:34:05');

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs_sistema`
--

CREATE TABLE `logs_sistema` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `acao` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `data_log` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `logs_sistema`
--

INSERT INTO `logs_sistema` (`id`, `usuario_id`, `acao`, `descricao`, `ip_address`, `data_log`) VALUES
(1, 1, 'LOGIN', 'Professor Carlos Silva realizou login', '192.168.1.100', '2026-08-18 13:54:12'),
(2, 1, 'ACESSO_ADMIN', 'Professor acessou o painel administrativo', '192.168.1.100', '2026-08-18 13:54:12'),
(3, 3, 'LOGIN', 'Aluna Ana Silva realizou login', '192.168.1.102', '2026-08-18 13:54:12'),
(4, 3, 'EXERCICIO_INICIO', 'Aluna Ana iniciou exercício equação_id=1', '192.168.1.102', '2026-08-18 13:54:12'),
(5, 3, 'EXERCICIO_CONCLUIDO', 'Aluna Ana concluiu exercício equação_id=1', '192.168.1.102', '2026-08-18 13:54:12'),
(6, 1, 'EXERCICIO_CONCLUIDO', 'Aluno concluiu equação ID 10 - 2x + 5 = 11', '::1', '2026-08-24 21:20:54'),
(7, 1, 'EXERCICIO_CONCLUIDO', 'Aluno concluiu equação ID 2 - 2x + 1 = 9', '::1', '2026-08-24 21:34:01');

-- --------------------------------------------------------

--
-- Estrutura para tabela `professores`
--

CREATE TABLE `professores` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `disciplina` varchar(50) NOT NULL DEFAULT 'Matemática',
  `escola` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `professores`
--

INSERT INTO `professores` (`id`, `usuario_id`, `disciplina`, `escola`, `telefone`, `data_cadastro`) VALUES
(1, 1, 'Matemática', 'Escola Modelo de Ensino Médio', '(11) 9999-8888', '2026-08-18 13:54:11'),
(2, 2, 'Matemática', 'Escola Modelo de Ensino Médio', '(11) 9999-7777', '2026-08-18 13:54:11');

-- --------------------------------------------------------

--
-- Estrutura para tabela `progresso_aluno`
--

CREATE TABLE `progresso_aluno` (
  `id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `equacao_id` int(11) NOT NULL,
  `passo_atual` tinyint(4) DEFAULT 1 CHECK (`passo_atual` between 1 and 4),
  `concluida` tinyint(1) DEFAULT 0,
  `data_inicio` datetime NOT NULL DEFAULT current_timestamp(),
  `data_conclusao` datetime DEFAULT NULL,
  `tentativas` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `progresso_aluno`
--

INSERT INTO `progresso_aluno` (`id`, `aluno_id`, `equacao_id`, `passo_atual`, `concluida`, `data_inicio`, `data_conclusao`, `tentativas`) VALUES
(1, 1, 1, 4, 1, '2026-08-16 10:54:12', '2026-08-17 10:54:12', 2),
(2, 1, 2, 4, 1, '2026-08-17 10:54:12', '2026-08-24 18:34:01', 4),
(3, 2, 3, 4, 1, '2026-08-15 10:54:12', '2026-08-16 10:54:12', 3),
(4, 3, 1, 2, 0, '2026-08-17 10:54:12', NULL, 1),
(5, 4, 4, 4, 1, '2026-08-16 10:54:12', '2026-08-17 10:54:12', 2),
(6, 1, 5, 4, 0, '2026-08-18 11:10:20', NULL, 1),
(8, 1, 3, 4, 0, '2026-08-18 11:30:49', NULL, 3),
(9, 1, 4, 4, 1, '2026-08-18 11:34:05', '2026-08-18 11:34:05', 4),
(10, 1, 10, 4, 1, '2026-08-22 21:48:51', '2026-08-24 18:20:54', 4);

-- --------------------------------------------------------

--
-- Estrutura para tabela `progresso_tentativas`
--

CREATE TABLE `progresso_tentativas` (
  `id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `equacao_id` int(11) NOT NULL,
  `passo` tinyint(4) NOT NULL,
  `resposta` varchar(100) DEFAULT NULL,
  `correto` tinyint(1) DEFAULT 0,
  `data_tentativa` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `progresso_tentativas`
--

INSERT INTO `progresso_tentativas` (`id`, `aluno_id`, `equacao_id`, `passo`, `resposta`, `correto`, `data_tentativa`) VALUES
(1, 1, 10, 1, '2x', 1, '2026-08-24 18:20:19'),
(2, 1, 10, 2, '2x = 11 - 5', 1, '2026-08-24 18:20:32'),
(3, 1, 10, 3, '2x = 6', 1, '2026-08-24 18:20:41'),
(4, 1, 10, 4, 'x = 3', 1, '2026-08-24 18:20:54'),
(5, 1, 2, 3, '2x = 8', 1, '2026-08-24 18:33:54'),
(6, 1, 2, 4, 'x = 4', 1, '2026-08-24 18:34:01'),
(7, 1, 3, 1, '3x', 0, '2026-08-24 18:34:58');

-- --------------------------------------------------------

--
-- Estrutura para tabela `registro_erros`
--

CREATE TABLE `registro_erros` (
  `id` int(11) NOT NULL,
  `aluno_id` int(11) NOT NULL,
  `equacao_id` int(11) NOT NULL,
  `passo` tinyint(4) NOT NULL CHECK (`passo` between 1 and 4),
  `tipo_erro` enum('operacao_inversa','calculo_errado','sinal_trocado','divisao_incorreta','identificacao_errada','outro') NOT NULL,
  `resposta_fornecida` varchar(100) DEFAULT NULL,
  `resposta_esperada` varchar(100) DEFAULT NULL,
  `data_erro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `registro_erros`
--

INSERT INTO `registro_erros` (`id`, `aluno_id`, `equacao_id`, `passo`, `tipo_erro`, `resposta_fornecida`, `resposta_esperada`, `data_erro`) VALUES
(1, 1, 1, 2, 'operacao_inversa', '2x = 7 + 3', '2x = 7 - 3', '2026-08-18 13:54:12'),
(2, 1, 1, 3, 'calculo_errado', '2x = 9', '2x = 4', '2026-08-18 13:54:12'),
(3, 2, 3, 2, 'sinal_trocado', '3x = 14 - 2', '3x = 14 - 5', '2026-08-18 13:54:12'),
(4, 2, 3, 4, 'divisao_incorreta', 'x = 16 ÷ 3 = 5', 'x = 9 ÷ 3 = 3', '2026-08-18 13:54:12'),
(5, 4, 4, 2, 'operacao_inversa', '5x = 22 - 6', '5x = 22 + 6', '2026-08-18 13:54:12'),
(6, 1, 1, 2, 'operacao_inversa', '2x = 7 + 3', '2x = 7 - 3', '2026-08-18 14:34:05'),
(7, 1, 2, 1, 'identificacao_errada', 'x + 3 = 7', '2x + 1 = 9', '2026-08-18 14:34:05'),
(8, 1, 1, 2, 'operacao_inversa', '2x = 7 + 3', '2x = 7 - 3', '2026-08-24 21:46:14'),
(9, 1, 1, 3, 'calculo_errado', '2x = 9', '2x = 4', '2026-08-24 21:46:14'),
(10, 1, 2, 2, 'sinal_trocado', 'x = 9 - 1', 'x = 9 + 1', '2026-08-24 21:46:14'),
(11, 1, 3, 4, 'divisao_incorreta', 'x = 12 ÷ 3 = 4', 'x = 9 ÷ 3 = 3', '2026-08-24 21:46:14'),
(12, 1, 4, 1, 'identificacao_errada', '2x', '3x', '2026-08-24 21:46:14'),
(13, 2, 1, 2, 'operacao_inversa', '2x = 7 + 3', '2x = 7 - 3', '2026-08-24 21:46:14'),
(14, 2, 2, 3, 'calculo_errado', 'x = 9 - 1 = 8', 'x = 9 - 1 = 8', '2026-08-24 21:46:14'),
(15, 1, 1, 2, 'operacao_inversa', '2x = 7 + 3', '2x = 7 - 3', '2026-08-24 21:56:54'),
(16, 1, 1, 3, 'calculo_errado', '2x = 9', '2x = 4', '2026-08-24 21:56:54'),
(17, 1, 2, 2, 'sinal_trocado', 'x = 9 - 1', 'x = 9 + 1', '2026-08-24 21:56:54'),
(18, 1, 3, 4, 'divisao_incorreta', 'x = 12 ÷ 3 = 4', 'x = 9 ÷ 3 = 3', '2026-08-24 21:56:54'),
(19, 1, 4, 1, 'identificacao_errada', '2x', '3x', '2026-08-24 21:56:54'),
(20, 3, 1, 2, 'operacao_inversa', '2x = 7 + 3', '2x = 7 - 3', '2026-08-24 21:56:54'),
(21, 5, 1, 2, 'operacao_inversa', '2x = 7 + 3', '2x = 7 - 3', '2026-08-24 21:56:54'),
(22, 4, 1, 2, 'operacao_inversa', '2x = 7 + 3', '2x = 7 - 3', '2026-08-24 21:56:54');

-- --------------------------------------------------------

--
-- Estrutura para tabela `sessao`
--

CREATE TABLE `sessao` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `data_criacao` datetime NOT NULL DEFAULT current_timestamp(),
  `ultima_atividade` datetime NOT NULL DEFAULT current_timestamp(),
  `expiracao` datetime NOT NULL,
  `ativa` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `tipo_perfil` enum('aluno','professor') NOT NULL DEFAULT 'aluno',
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_acesso` datetime DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha_hash`, `tipo_perfil`, `data_cadastro`, `ultimo_acesso`, `ativo`) VALUES
(1, 'Professor Carlos Silva', 'carlos@escola.com', '$2y$10$F6R2/iZlQhYUSbgfQOD4t.rAEupnxAeAYqt2mlPXAbJQi7Dox7hUW', 'professor', '2026-08-18 13:54:10', '2026-08-24 18:57:34', 1),
(2, 'Professora Ana Santos', 'ana@escola.com', '$2y$10$Z6xCBLNZL0adiZXkXjSNQ.BP6Z3Oe5vQCwV9dLp0jEcPxqWZer7Ym', 'professor', '2026-08-18 13:54:10', NULL, 1),
(3, 'Ana Silva', 'ana.silva@escola.com', '$2y$10$AVCMSObP.BBdQcpIsSan5eFXKCQCoLFP.cwb/MfvyMQndbp5zbSBa', 'aluno', '2026-08-18 13:54:11', '2026-08-24 18:20:10', 1),
(4, 'Carlos Souza', 'carlos.souza@escola.com', '$2y$10$7gjF55ba6nYJaxtjBX0uX.ymkEBMjn.dIBjZhI3uDs7YVlf9t/3rO', 'aluno', '2026-08-18 13:54:11', NULL, 1),
(5, 'Mariana Costa', 'mariana.costa@escola.com', '$2y$10$Hj1GXHfIWSFoYNGQpF6lCOOU2HbOFMB9TklKroCFa/CJnvyMecJoG', 'aluno', '2026-08-18 13:54:11', NULL, 1),
(6, 'João Pereira', 'joao.pereira@escola.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'aluno', '2026-08-18 13:54:11', NULL, 1),
(7, 'Beatriz Lima', 'beatriz.lima@escola.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'aluno', '2026-08-18 13:54:11', NULL, 1),
(15, 'Administrador', 'admin@equatea.com', '$2y$10$jKX1SNaYN4XvAHi4b2nqYepf7/qPZNKbONODPVXJ15wq6Xha1nd2G', 'professor', '2026-08-22 22:16:56', NULL, 1);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_acertos_aluno`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_acertos_aluno` (
`aluno` varchar(100)
,`equacao` varchar(40)
,`passo` tinyint(4)
,`resposta` varchar(100)
,`data_tentativa` datetime
,`aluno_id` int(11)
,`equacao_id` int(11)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_progresso_aluno`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_progresso_aluno` (
`aluno` varchar(100)
,`aluno_id` int(11)
,`equacao_id` int(11)
,`equacao` varchar(40)
,`dificuldade` enum('facil','medio','dificil')
,`passo_atual` tinyint(4)
,`concluida` tinyint(1)
,`data_inicio` datetime
,`data_conclusao` datetime
,`tentativas` tinyint(4)
,`status_progresso` varchar(12)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_relatorio_erros`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_relatorio_erros` (
`aluno` varchar(100)
,`equacao` varchar(40)
,`passo` tinyint(4)
,`tipo_erro` enum('operacao_inversa','calculo_errado','sinal_trocado','divisao_incorreta','identificacao_errada','outro')
,`resposta_fornecida` varchar(100)
,`resposta_esperada` varchar(100)
,`data_erro` timestamp
,`aluno_id` int(11)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_relatorio_erros_completo`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_relatorio_erros_completo` (
`aluno` varchar(100)
,`equacao` varchar(40)
,`passo` tinyint(4)
,`tipo_erro` enum('operacao_inversa','calculo_errado','sinal_trocado','divisao_incorreta','identificacao_errada','outro')
,`resposta_fornecida` varchar(100)
,`resposta_esperada` varchar(100)
,`data_erro` timestamp
,`aluno_id` int(11)
,`equacao_id` int(11)
);

-- --------------------------------------------------------

--
-- Estrutura para view `vw_acertos_aluno`
--
DROP TABLE IF EXISTS `vw_acertos_aluno`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_acertos_aluno`  AS SELECT `u`.`nome` AS `aluno`, concat(`e`.`a`,'x + ',`e`.`b`,' = ',`e`.`c`) AS `equacao`, `t`.`passo` AS `passo`, `t`.`resposta` AS `resposta`, `t`.`data_tentativa` AS `data_tentativa`, `a`.`id` AS `aluno_id`, `e`.`id` AS `equacao_id` FROM (((`progresso_tentativas` `t` join `alunos` `a` on(`t`.`aluno_id` = `a`.`id`)) join `usuarios` `u` on(`a`.`usuario_id` = `u`.`id`)) join `equacoes` `e` on(`t`.`equacao_id` = `e`.`id`)) WHERE `t`.`correto` = 1 ORDER BY `t`.`data_tentativa` DESC ;

-- --------------------------------------------------------

--
-- Estrutura para view `vw_progresso_aluno`
--
DROP TABLE IF EXISTS `vw_progresso_aluno`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_progresso_aluno`  AS SELECT `u`.`nome` AS `aluno`, `a`.`id` AS `aluno_id`, `e`.`id` AS `equacao_id`, concat(case when `e`.`a` = 1 then 'x' when `e`.`a` = -1 then '-x' else concat(`e`.`a`,'x') end,case when `e`.`b` > 0 then concat(' + ',`e`.`b`) when `e`.`b` < 0 then concat(' - ',abs(`e`.`b`)) else '' end,' = ',`e`.`c`) AS `equacao`, `e`.`dificuldade` AS `dificuldade`, `p`.`passo_atual` AS `passo_atual`, `p`.`concluida` AS `concluida`, `p`.`data_inicio` AS `data_inicio`, `p`.`data_conclusao` AS `data_conclusao`, `p`.`tentativas` AS `tentativas`, CASE WHEN `p`.`concluida` = 1 THEN 'Concluído' WHEN `p`.`passo_atual` is not null AND `p`.`passo_atual` > 0 THEN concat('Passo ',`p`.`passo_atual`,'/4') ELSE 'Pendente' END AS `status_progresso` FROM (((`equacoes` `e` left join `progresso_aluno` `p` on(`e`.`id` = `p`.`equacao_id`)) left join `alunos` `a` on(`p`.`aluno_id` = `a`.`id`)) left join `usuarios` `u` on(`a`.`usuario_id` = `u`.`id`)) ORDER BY `e`.`dificuldade` ASC, `e`.`id` ASC ;

-- --------------------------------------------------------

--
-- Estrutura para view `vw_relatorio_erros`
--
DROP TABLE IF EXISTS `vw_relatorio_erros`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_relatorio_erros`  AS SELECT `u`.`nome` AS `aluno`, concat(`e`.`a`,'x + ',`e`.`b`,' = ',`e`.`c`) AS `equacao`, `r`.`passo` AS `passo`, `r`.`tipo_erro` AS `tipo_erro`, `r`.`resposta_fornecida` AS `resposta_fornecida`, `r`.`resposta_esperada` AS `resposta_esperada`, `r`.`data_erro` AS `data_erro`, `a`.`id` AS `aluno_id` FROM (((`registro_erros` `r` join `alunos` `a` on(`r`.`aluno_id` = `a`.`id`)) join `usuarios` `u` on(`a`.`usuario_id` = `u`.`id`)) join `equacoes` `e` on(`r`.`equacao_id` = `e`.`id`)) ORDER BY `r`.`data_erro` DESC ;

-- --------------------------------------------------------

--
-- Estrutura para view `vw_relatorio_erros_completo`
--
DROP TABLE IF EXISTS `vw_relatorio_erros_completo`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_relatorio_erros_completo`  AS SELECT `u`.`nome` AS `aluno`, concat(`e`.`a`,'x + ',`e`.`b`,' = ',`e`.`c`) AS `equacao`, `r`.`passo` AS `passo`, `r`.`tipo_erro` AS `tipo_erro`, `r`.`resposta_fornecida` AS `resposta_fornecida`, `r`.`resposta_esperada` AS `resposta_esperada`, `r`.`data_erro` AS `data_erro`, `a`.`id` AS `aluno_id`, `e`.`id` AS `equacao_id` FROM (((`registro_erros` `r` join `alunos` `a` on(`r`.`aluno_id` = `a`.`id`)) join `usuarios` `u` on(`a`.`usuario_id` = `u`.`id`)) join `equacoes` `e` on(`r`.`equacao_id` = `e`.`id`)) ORDER BY `r`.`data_erro` DESC ;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `ajuda_exemplos`
--
ALTER TABLE `ajuda_exemplos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_passo` (`passo`);

--
-- Índices de tabela `alunos`
--
ALTER TABLE `alunos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_id` (`usuario_id`),
  ADD KEY `idx_nivel_tea` (`nivel_tea`),
  ADD KEY `idx_escola` (`escola`);

--
-- Índices de tabela `equacoes`
--
ALTER TABLE `equacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dificuldade` (`dificuldade`),
  ADD KEY `idx_solucao` (`solucao`);

--
-- Índices de tabela `logs_sistema`
--
ALTER TABLE `logs_sistema`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_id` (`usuario_id`),
  ADD KEY `idx_acao` (`acao`),
  ADD KEY `idx_data_log` (`data_log`);

--
-- Índices de tabela `professores`
--
ALTER TABLE `professores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_id` (`usuario_id`),
  ADD KEY `idx_escola` (`escola`);

--
-- Índices de tabela `progresso_aluno`
--
ALTER TABLE `progresso_aluno`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_progresso` (`aluno_id`,`equacao_id`),
  ADD KEY `idx_aluno_id` (`aluno_id`),
  ADD KEY `idx_equacao_id` (`equacao_id`),
  ADD KEY `idx_concluida` (`concluida`),
  ADD KEY `idx_passo_atual` (`passo_atual`);

--
-- Índices de tabela `progresso_tentativas`
--
ALTER TABLE `progresso_tentativas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_aluno_equacao_passo` (`aluno_id`,`equacao_id`,`passo`),
  ADD KEY `progresso_tentativas_ibfk_2` (`equacao_id`),
  ADD KEY `idx_data_tentativa` (`data_tentativa`);

--
-- Índices de tabela `registro_erros`
--
ALTER TABLE `registro_erros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_aluno_id` (`aluno_id`),
  ADD KEY `idx_equacao_id` (`equacao_id`),
  ADD KEY `idx_passo` (`passo`),
  ADD KEY `idx_tipo_erro` (`tipo_erro`),
  ADD KEY `idx_data_erro` (`data_erro`);

--
-- Índices de tabela `sessao`
--
ALTER TABLE `sessao`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_id` (`session_id`),
  ADD KEY `idx_usuario_id` (`usuario_id`),
  ADD KEY `idx_session_id` (`session_id`),
  ADD KEY `idx_ativa` (`ativa`),
  ADD KEY `idx_expiracao` (`expiracao`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_tipo_perfil` (`tipo_perfil`),
  ADD KEY `idx_ativo` (`ativo`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `ajuda_exemplos`
--
ALTER TABLE `ajuda_exemplos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `alunos`
--
ALTER TABLE `alunos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `equacoes`
--
ALTER TABLE `equacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de tabela `logs_sistema`
--
ALTER TABLE `logs_sistema`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `professores`
--
ALTER TABLE `professores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `progresso_aluno`
--
ALTER TABLE `progresso_aluno`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `progresso_tentativas`
--
ALTER TABLE `progresso_tentativas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `registro_erros`
--
ALTER TABLE `registro_erros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de tabela `sessao`
--
ALTER TABLE `sessao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `alunos`
--
ALTER TABLE `alunos`
  ADD CONSTRAINT `alunos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `logs_sistema`
--
ALTER TABLE `logs_sistema`
  ADD CONSTRAINT `logs_sistema_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `professores`
--
ALTER TABLE `professores`
  ADD CONSTRAINT `professores_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `progresso_aluno`
--
ALTER TABLE `progresso_aluno`
  ADD CONSTRAINT `progresso_aluno_ibfk_1` FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `progresso_aluno_ibfk_2` FOREIGN KEY (`equacao_id`) REFERENCES `equacoes` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `progresso_tentativas`
--
ALTER TABLE `progresso_tentativas`
  ADD CONSTRAINT `progresso_tentativas_ibfk_1` FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `progresso_tentativas_ibfk_2` FOREIGN KEY (`equacao_id`) REFERENCES `equacoes` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `registro_erros`
--
ALTER TABLE `registro_erros`
  ADD CONSTRAINT `registro_erros_ibfk_1` FOREIGN KEY (`aluno_id`) REFERENCES `alunos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `registro_erros_ibfk_2` FOREIGN KEY (`equacao_id`) REFERENCES `equacoes` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `sessao`
--
ALTER TABLE `sessao`
  ADD CONSTRAINT `sessao_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
