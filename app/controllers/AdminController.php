<?php
/**
 * ============================================================
 * AdminController.php
 * Controlador responsável pelas funcionalidades administrativas
 * do sistema EquaTEA.
 * 
 * FUNCIONALIDADES:
 * - Gerenciar alunos (listar, cadastrar, editar, excluir)
 * - Gerenciar equações (listar, cadastrar, editar, excluir)
 * - Resetar senha de alunos
 * - Visualizar estatísticas gerais do sistema
 * 
 * @package EquaTEA
 * @subpackage Controllers
 * @author Equipe EquaTEA
 * @version 1.0
 * ============================================================
 */

// ============================================================
// IMPORTAÇÃO DOS MODELOS NECESSÁRIOS
// ============================================================

require_once CONTROLLERS_PATH . '/../models/Usuario.php';
require_once CONTROLLERS_PATH . '/../models/Aluno.php';
require_once CONTROLLERS_PATH . '/../models/Professor.php';
require_once CONTROLLERS_PATH . '/../models/Equacao.php';
require_once CONTROLLERS_PATH . '/../models/Progresso.php';
require_once CONTROLLERS_PATH . '/../models/RegistroErro.php';
require_once CONTROLLERS_PATH . '/../models/Sessao.php';
require_once CONTROLLERS_PATH . '/../helpers/session_helper.php';

/**
 * Class AdminController
 * 
 * Gerencia todas as operações administrativas do sistema,
 * incluindo CRUD de alunos e equações, reset de senhas
 * e visualização de estatísticas.
 * 
 * @author Equipe EquaTEA
 */
class AdminController
{
    /**
     * @var object Instância do modelo Usuario
     */
    private $usuario;
    
    /**
     * @var object Instância do modelo Aluno
     */
    private $aluno;
    
    /**
     * @var object Instância do modelo Professor
     */
    private $professor;
    
    /**
     * @var object Instância do modelo Equacao
     */
    private $equacao;
    
    /**
     * @var object Instância do modelo Progresso
     */
    private $progresso;
    
    /**
     * @var object Instância do modelo RegistroErro
     */
    private $registroErro;
    
    /**
     * @var object Instância do modelo Sessao
     */
    private $sessao;
    
    /**
     * @var object Conexão com o banco de dados
     */
    private $db;

    /**
     * Construtor da classe AdminController.
     * Inicializa todos os modelos necessários e verifica
     * se o usuário está autenticado e possui perfil de professor.
     */
    public function __construct()
    {
        // ============================================================
        // 1. VERIFICAÇÃO DE AUTENTICAÇÃO E PERFIL
        // ============================================================
        
        // Verifica se o usuário está logado
        verificarSessao();
        
        // Verifica se o usuário é um professor (apenas professores têm acesso administrativo)
        if (!isProfessor()) {
            // Se não for professor, redireciona para a página inicial do aluno
            header('Location: ' . BASE_URL . 'app/views/aluno/dashboard.php');
            exit;
        }
        
        // ============================================================
        // 2. INICIALIZAÇÃO DOS MODELOS
        // ============================================================
        
        $this->usuario = new Usuario();
        $this->aluno = new Aluno();
        $this->professor = new Professor();
        $this->equacao = new Equacao();
        $this->progresso = new Progresso();
        $this->registroErro = new RegistroErro();
        $this->sessao = new Sessao();
        $this->db = Database::getInstance()->getConnection();
        
        // ============================================================
        // 3. REGISTRO DE LOG DA AÇÃO ADMINISTRATIVA
        // ============================================================
        
        $this->logAcao('ACESSO_ADMIN', 'Professor acessou o painel administrativo');
    }

    // ============================================================
    // MÉTODOS DE GERENCIAMENTO DE ALUNOS
    // ============================================================

    /**
     * Lista todos os alunos cadastrados no sistema.
     * Retorna um array com os dados completos de cada aluno,
     * incluindo informações de progresso e estatísticas.
     * 
     * @return array Lista de alunos com dados completos
     */
    public function listarAlunos()
    {
        // ============================================================
        // 1. BUSCA BÁSICA DOS ALUNOS
        // ============================================================
        
        $sql = "SELECT 
                    u.id as usuario_id,
                    u.nome,
                    u.email,
                    u.data_cadastro,
                    u.ultimo_acesso,
                    u.ativo,
                    a.id as aluno_id,
                    a.idade,
                    a.nivel_tea,
                    a.escola,
                    a.turma,
                    (SELECT COUNT(*) FROM progresso_aluno WHERE aluno_id = a.id) as total_equacoes,
                    (SELECT SUM(concluida) FROM progresso_aluno WHERE aluno_id = a.id) as equacoes_concluidas,
                    (SELECT COUNT(*) FROM registro_erros WHERE aluno_id = a.id) as total_erros
                FROM usuarios u
                INNER JOIN alunos a ON u.id = a.usuario_id
                WHERE u.tipo_perfil = 'aluno'
                ORDER BY u.nome ASC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // ============================================================
            // 2. CÁLCULO DE ESTATÍSTICAS ADICIONAIS
            // ============================================================
            
            foreach ($alunos as &$aluno) {
                // Calcula a taxa de conclusão
                if ($aluno['total_equacoes'] > 0) {
                    $aluno['taxa_conclusao'] = round(
                        ($aluno['equacoes_concluidas'] / $aluno['total_equacoes']) * 100,
                        1
                    );
                } else {
                    $aluno['taxa_conclusao'] = 0;
                }
                
                // Determina o status do aluno baseado na última atividade
                if ($aluno['ultimo_acesso']) {
                    $dias_sem_acesso = (time() - strtotime($aluno['ultimo_acesso'])) / (60 * 60 * 24);
                    if ($dias_sem_acesso > 7) {
                        $aluno['status'] = 'inativo';
                    } elseif ($dias_sem_acesso > 3) {
                        $aluno['status'] = 'ausente';
                    } else {
                        $aluno['status'] = 'ativo';
                    }
                } else {
                    $aluno['status'] = 'nunca_acessou';
                }
            }
            
            $this->logAcao('LISTAR_ALUNOS', 'Professor listou todos os alunos');
            return $alunos;
            
        } catch (PDOException $e) {
            error_log("Erro ao listar alunos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cadastra um novo aluno no sistema.
     * Cria o registro na tabela usuarios e na tabela alunos.
     * 
     * @param string $nome      Nome completo do aluno
     * @param string $email     E-mail do aluno (usado para login)
     * @param string $senha     Senha do aluno (será hasheada)
     * @param int    $idade     Idade do aluno (14-21 anos)
     * @param string $nivel_tea Nível de suporte TEA (suporte1 ou suporte2)
     * @param string $escola    Nome da escola
     * @param string $turma     Turma do aluno
     * @return bool True se cadastrado com sucesso, False em caso de erro
     */
    public function cadastrarAluno($nome, $email, $senha, $idade, $nivel_tea, $escola, $turma)
    {
        // ============================================================
        // 1. VALIDAÇÃO DOS DADOS DE ENTRADA
        // ============================================================
        
        // Validação do nome
        if (empty($nome) || strlen($nome) < 3) {
            $this->setError('Nome deve ter pelo menos 3 caracteres.');
            return false;
        }
        
        // Validação do email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setError('E-mail inválido.');
            return false;
        }
        
        // Validação da senha
        if (empty($senha) || strlen($senha) < 4) {
            $this->setError('Senha deve ter pelo menos 4 caracteres.');
            return false;
        }
        
        // Validação da idade
        if ($idade < 14 || $idade > 21) {
            $this->setError('Idade deve estar entre 14 e 21 anos.');
            return false;
        }
        
        // Validação do nível TEA
        if (!in_array($nivel_tea, ['suporte1', 'suporte2'])) {
            $this->setError('Nível de suporte TEA inválido.');
            return false;
        }
        
        // ============================================================
        // 2. VERIFICAÇÃO DE DUPLICIDADE DE E-MAIL
        // ============================================================
        
        if ($this->usuario->getByEmail($email)) {
            $this->setError('Este e-mail já está cadastrado no sistema.');
            return false;
        }
        
        // ============================================================
        // 3. TRANSAÇÃO - CRIAÇÃO DO USUÁRIO E ALUNO
        // ============================================================
        
        try {
            // Inicia transação
            $this->db->beginTransaction();
            
            // 3.1. Criar o usuário na tabela usuarios
            $usuario_id = $this->usuario->criar($nome, $email, $senha, 'aluno');
            
            if (!$usuario_id) {
                throw new Exception('Falha ao criar usuário.');
            }
            
            // 3.2. Criar o aluno na tabela alunos
            $sql = "INSERT INTO alunos (usuario_id, idade, nivel_tea, escola, turma) 
                    VALUES (:usuario_id, :idade, :nivel_tea, :escola, :turma)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':usuario_id' => $usuario_id,
                ':idade' => $idade,
                ':nivel_tea' => $nivel_tea,
                ':escola' => $escola,
                ':turma' => $turma
            ]);
            
            if (!$result) {
                throw new Exception('Falha ao criar registro de aluno.');
            }
            
            // 3.3. Commit da transação
            $this->db->commit();
            
            // 3.4. Log da ação
            $this->logAcao('CADASTRAR_ALUNO', "Professor cadastrou aluno: $nome ($email)");
            
            $this->setSuccess('Aluno cadastrado com sucesso!');
            return true;
            
        } catch (Exception $e) {
            // Em caso de erro, rollback da transação
            $this->db->rollBack();
            error_log("Erro ao cadastrar aluno: " . $e->getMessage());
            $this->setError('Erro ao cadastrar aluno: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Edita os dados de um aluno existente.
     * Permite atualizar nome, idade, nível TEA, escola e turma.
     * 
     * @param int    $aluno_id  ID do aluno na tabela alunos
     * @param string $nome      Novo nome do aluno
     * @param int    $idade     Nova idade do aluno
     * @param string $nivel_tea Novo nível de suporte TEA
     * @param string $escola    Nova escola do aluno
     * @param string $turma     Nova turma do aluno
     * @return bool True se editado com sucesso
     */
    public function editarAluno($aluno_id, $nome, $idade, $nivel_tea, $escola, $turma)
    {
        // ============================================================
        // 1. VALIDAÇÃO DOS DADOS DE ENTRADA
        // ============================================================
        
        if (empty($nome) || strlen($nome) < 3) {
            $this->setError('Nome deve ter pelo menos 3 caracteres.');
            return false;
        }
        
        if ($idade < 14 || $idade > 21) {
            $this->setError('Idade deve estar entre 14 e 21 anos.');
            return false;
        }
        
        if (!in_array($nivel_tea, ['suporte1', 'suporte2'])) {
            $this->setError('Nível de suporte TEA inválido.');
            return false;
        }
        
        // ============================================================
        // 2. ATUALIZAÇÃO DOS DADOS
        // ============================================================
        
        try {
            // 2.1. Buscar o aluno para obter o usuario_id
            $aluno_atual = $this->aluno->getDadosCompletos($aluno_id);
            if (!$aluno_atual) {
                $this->setError('Aluno não encontrado.');
                return false;
            }
            
            $this->db->beginTransaction();
            
            // 2.2. Atualizar o nome na tabela usuarios
            $sql_usuario = "UPDATE usuarios SET nome = :nome WHERE id = :usuario_id";
            $stmt_usuario = $this->db->prepare($sql_usuario);
            $stmt_usuario->execute([
                ':nome' => $nome,
                ':usuario_id' => $aluno_atual['usuario_id']
            ]);
            
            // 2.3. Atualizar os dados específicos do aluno
            $sql_aluno = "UPDATE alunos 
                          SET idade = :idade, 
                              nivel_tea = :nivel_tea, 
                              escola = :escola, 
                              turma = :turma 
                          WHERE id = :aluno_id";
            $stmt_aluno = $this->db->prepare($sql_aluno);
            $stmt_aluno->execute([
                ':idade' => $idade,
                ':nivel_tea' => $nivel_tea,
                ':escola' => $escola,
                ':turma' => $turma,
                ':aluno_id' => $aluno_id
            ]);
            
            $this->db->commit();
            
            $this->logAcao('EDITAR_ALUNO', "Professor editou dados do aluno ID: $aluno_id");
            $this->setSuccess('Dados do aluno atualizados com sucesso!');
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erro ao editar aluno: " . $e->getMessage());
            $this->setError('Erro ao editar aluno: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui um aluno do sistema (soft delete ou hard delete).
     * Por padrão, utiliza soft delete (desativa o usuário).
     * 
     * @param int  $aluno_id   ID do aluno na tabela alunos
     * @param bool $hard_delete Se true, exclui permanentemente
     * @return bool True se excluído com sucesso
     */
    public function excluirAluno($aluno_id, $hard_delete = false)
    {
        try {
            $aluno = $this->aluno->getDadosCompletos($aluno_id);
            if (!$aluno) {
                $this->setError('Aluno não encontrado.');
                return false;
            }
            
            if ($hard_delete) {
                // ============================================================
                // HARD DELETE - Exclusão permanente (cascade)
                // ============================================================
                
                // O ON DELETE CASCADE nas foreign keys cuidará das tabelas relacionadas
                $sql = "DELETE FROM usuarios WHERE id = :usuario_id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':usuario_id' => $aluno['usuario_id']]);
                
                $this->logAcao('EXCLUIR_ALUNO_PERMANENTE', 
                    "Professor excluiu permanentemente o aluno ID: $aluno_id");
            } else {
                // ============================================================
                // SOFT DELETE - Apenas desativa o usuário
                // ============================================================
                
                $sql = "UPDATE usuarios SET ativo = 0 WHERE id = :usuario_id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':usuario_id' => $aluno['usuario_id']]);
                
                $this->logAcao('EXCLUIR_ALUNO_SOFT', 
                    "Professor desativou o aluno ID: $aluno_id");
            }
            
            $this->setSuccess('Aluno excluído com sucesso!');
            return true;
            
        } catch (Exception $e) {
            error_log("Erro ao excluir aluno: " . $e->getMessage());
            $this->setError('Erro ao excluir aluno: ' . $e->getMessage());
            return false;
        }
    }

    // ============================================================
    // MÉTODOS DE GERENCIAMENTO DE SENHAS
    // ============================================================

    /**
     * Reseta a senha de um aluno.
     * Gera uma nova senha aleatória, hasheia e atualiza no banco.
     * 
     * @param int    $aluno_id   ID do aluno na tabela alunos
     * @param string $nova_senha Nova senha (se não informada, gera aleatória)
     * @return array|bool Retorna a nova senha gerada ou false em caso de erro
     */
    public function resetarSenhaAluno($aluno_id, $nova_senha = null)
    {
        // ============================================================
        // 1. BUSCAR O ALUNO
        // ============================================================
        
        $aluno = $this->aluno->getDadosCompletos($aluno_id);
        if (!$aluno) {
            $this->setError('Aluno não encontrado.');
            return false;
        }
        
        // ============================================================
        // 2. GERAR NOVA SENHA (se não fornecida)
        // ============================================================
        
        if (empty($nova_senha)) {
            // Gera uma senha aleatória com 6 caracteres
            $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $nova_senha = '';
            for ($i = 0; $i < 6; $i++) {
                $nova_senha .= $caracteres[rand(0, strlen($caracteres) - 1)];
            }
        }
        
        // ============================================================
        // 3. ATUALIZAR A SENHA NO BANCO DE DADOS
        // ============================================================
        
        try {
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            
            $sql = "UPDATE usuarios SET senha_hash = :senha_hash WHERE id = :usuario_id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':senha_hash' => $senha_hash,
                ':usuario_id' => $aluno['usuario_id']
            ]);
            
            if (!$result) {
                throw new Exception('Falha ao atualizar a senha.');
            }
            
            // ============================================================
            // 4. REGISTRAR A AÇÃO
            // ============================================================
            
            $this->logAcao('RESETAR_SENHA', 
                "Professor resetou a senha do aluno: {$aluno['nome']} (ID: $aluno_id)");
            
            // ============================================================
            // 5. RETORNAR A NOVA SENHA PARA O PROFESSOR
            // ============================================================
            
            $this->setSuccess('Senha resetada com sucesso!');
            return [
                'senha' => $nova_senha,
                'aluno' => $aluno['nome']
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao resetar senha: " . $e->getMessage());
            $this->setError('Erro ao resetar senha: ' . $e->getMessage());
            return false;
        }
    }

    // ============================================================
    // MÉTODOS DE GERENCIAMENTO DE EQUAÇÕES
    // ============================================================

    /**
     * Cadastra uma nova equação no banco de dados.
     * Valida os coeficientes e calcula a solução automaticamente.
     * 
     * @param int    $a          Coeficiente de x (-20 a 20, não pode ser 0)
     * @param int    $b          Constante do lado esquerdo (-20 a 20)
     * @param int    $c          Constante do lado direito (-20 a 20)
     * @param string $dificuldade Dificuldade da equação (facil, medio, dificil)
     * @return bool True se cadastrada com sucesso
     */
    public function cadastrarEquacao($a, $b, $c, $dificuldade = 'facil')
    {
        // ============================================================
        // 1. VALIDAÇÃO DOS COEFICIENTES
        // ============================================================
        
        // Verifica se a está dentro do intervalo e não é zero
        if ($a == 0 || $a < -20 || $a > 20) {
            $this->setError('O coeficiente a deve ser diferente de zero e estar entre -20 e 20.');
            return false;
        }
        
        // Verifica se b está dentro do intervalo
        if ($b < -20 || $b > 20) {
            $this->setError('O coeficiente b deve estar entre -20 e 20.');
            return false;
        }
        
        // Verifica se c está dentro do intervalo
        if ($c < -20 || $c > 20) {
            $this->setError('O coeficiente c deve estar entre -20 e 20.');
            return false;
        }
        
        // ============================================================
        // 2. CÁLCULO DA SOLUÇÃO
        // ============================================================
        
        $solucao = ($c - $b) / $a;
        
        // Verifica se a solução é inteira (evita frações)
        if (fmod($solucao, 1) != 0) {
            $this->setError('A solução deve ser um número inteiro. (c - b) / a = ' . $solucao);
            return false;
        }
        
        // ============================================================
        // 3. INSERÇÃO NO BANCO DE DADOS
        // ============================================================
        
        try {
            $sql = "INSERT INTO equacoes (a, b, c, solucao, dificuldade) 
                    VALUES (:a, :b, :c, :solucao, :dificuldade)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':a' => $a,
                ':b' => $b,
                ':c' => $c,
                ':solucao' => $solucao,
                ':dificuldade' => $dificuldade
            ]);
            
            if (!$result) {
                throw new Exception('Falha ao inserir equação.');
            }
            
            // Gera o enunciado para exibição
            $sinal = $b >= 0 ? '+' : '-';
            $enunciado = "{$a}x {$sinal} " . abs($b) . " = {$c}";
            
            $this->logAcao('CADASTRAR_EQUACAO', 
                "Professor cadastrou equação: $enunciado (dificuldade: $dificuldade)");
            
            $this->setSuccess('Equação cadastrada com sucesso!');
            return true;
            
        } catch (Exception $e) {
            error_log("Erro ao cadastrar equação: " . $e->getMessage());
            $this->setError('Erro ao cadastrar equação: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Edita uma equação existente.
     * 
     * @param int    $id          ID da equação
     * @param int    $a           Novo coeficiente a
     * @param int    $b           Novo coeficiente b
     * @param int    $c           Novo coeficiente c
     * @param string $dificuldade Nova dificuldade
     * @return bool True se editada com sucesso
     */
    public function editarEquacao($id, $a, $b, $c, $dificuldade)
    {
        // ============================================================
        // 1. VALIDAÇÃO DOS COEFICIENTES
        // ============================================================
        
        if ($a == 0 || $a < -20 || $a > 20) {
            $this->setError('Coeficiente a inválido.');
            return false;
        }
        
        if ($b < -20 || $b > 20) {
            $this->setError('Coeficiente b inválido.');
            return false;
        }
        
        if ($c < -20 || $c > 20) {
            $this->setError('Coeficiente c inválido.');
            return false;
        }
        
        // ============================================================
        // 2. CÁLCULO DA SOLUÇÃO
        // ============================================================
        
        $solucao = ($c - $b) / $a;
        if (fmod($solucao, 1) != 0) {
            $this->setError('A solução deve ser um número inteiro.');
            return false;
        }
        
        // ============================================================
        // 3. ATUALIZAÇÃO NO BANCO DE DADOS
        // ============================================================
        
        try {
            $sql = "UPDATE equacoes 
                    SET a = :a, b = :b, c = :c, solucao = :solucao, dificuldade = :dificuldade 
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':a' => $a,
                ':b' => $b,
                ':c' => $c,
                ':solucao' => $solucao,
                ':dificuldade' => $dificuldade,
                ':id' => $id
            ]);
            
            if (!$result) {
                throw new Exception('Falha ao atualizar equação.');
            }
            
            $this->logAcao('EDITAR_EQUACAO', "Professor editou equação ID: $id");
            $this->setSuccess('Equação atualizada com sucesso!');
            return true;
            
        } catch (Exception $e) {
            error_log("Erro ao editar equação: " . $e->getMessage());
            $this->setError('Erro ao editar equação: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui uma equação do banco de dados.
     * Verifica se a equação já foi utilizada por algum aluno antes de excluir.
     * 
     * @param int $id ID da equação
     * @return bool True se excluída com sucesso
     */
    public function excluirEquacao($id)
    {
        try {
            // ============================================================
            // 1. VERIFICA SE A EQUAÇÃO FOI UTILIZADA
            // ============================================================
            
            $sql_verifica = "SELECT COUNT(*) as total FROM progresso_aluno WHERE equacao_id = :id";
            $stmt_verifica = $this->db->prepare($sql_verifica);
            $stmt_verifica->execute([':id' => $id]);
            $result = $stmt_verifica->fetch();
            
            if ($result['total'] > 0) {
                $this->setError('Esta equação já foi utilizada por alunos e não pode ser excluída.');
                return false;
            }
            
            // ============================================================
            // 2. EXCLUSÃO DA EQUAÇÃO
            // ============================================================
            
            $sql = "DELETE FROM equacoes WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([':id' => $id]);
            
            if (!$result) {
                throw new Exception('Falha ao excluir equação.');
            }
            
            $this->logAcao('EXCLUIR_EQUACAO', "Professor excluiu equação ID: $id");
            $this->setSuccess('Equação excluída com sucesso!');
            return true;
            
        } catch (Exception $e) {
            error_log("Erro ao excluir equação: " . $e->getMessage());
            $this->setError('Erro ao excluir equação: ' . $e->getMessage());
            return false;
        }
    }

    // ============================================================
    // MÉTODOS DE ESTATÍSTICAS E RELATÓRIOS
    // ============================================================

    /**
     * Obtém estatísticas gerais do sistema para o dashboard administrativo.
     * 
     * @return array Estatísticas do sistema
     */
    public function getEstatisticasGerais()
    {
        try {
            $stats = [];
            
            // ============================================================
            // 1. TOTAL DE ALUNOS ATIVOS
            // ============================================================
            
            $sql = "SELECT COUNT(*) as total FROM usuarios WHERE tipo_perfil = 'aluno' AND ativo = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['total_alunos'] = $stmt->fetch()['total'];
            
            // ============================================================
            // 2. TOTAL DE EQUAÇÕES CADASTRADAS
            // ============================================================
            
            $sql = "SELECT COUNT(*) as total FROM equacoes";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['total_equacoes'] = $stmt->fetch()['total'];
            
            // ============================================================
            // 3. TOTAL DE EQUAÇÕES CONCLUÍDAS
            // ============================================================
            
            $sql = "SELECT COUNT(*) as total FROM progresso_aluno WHERE concluida = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['total_concluidas'] = $stmt->fetch()['total'];
            
            // ============================================================
            // 4. TOTAL DE ERROS REGISTRADOS
            // ============================================================
            
            $sql = "SELECT COUNT(*) as total FROM registro_erros";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['total_erros'] = $stmt->fetch()['total'];
            
            // ============================================================
            // 5. ERRO MAIS COMUM
            // ============================================================
            
            $sql = "SELECT tipo_erro, COUNT(*) as total 
                    FROM registro_erros 
                    GROUP BY tipo_erro 
                    ORDER BY total DESC 
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['erro_mais_comum'] = $result ? $result['tipo_erro'] : 'Nenhum erro registrado';
            
            // ============================================================
            // 6. DIFICULDADE MAIS UTILIZADA
            // ============================================================
            
            $sql = "SELECT dificuldade, COUNT(*) as total 
                    FROM equacoes 
                    GROUP BY dificuldade 
                    ORDER BY total DESC 
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['dificuldade_mais_comum'] = $result ? $result['dificuldade'] : 'Nenhuma';
            
            // ============================================================
            // 7. MÉDIA DE TENTATIVAS POR EQUAÇÃO
            // ============================================================
            
            $sql = "SELECT AVG(tentativas) as media FROM progresso_aluno";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['media_tentativas'] = round($stmt->fetch()['media'] ?? 0, 1);
            
            // ============================================================
            // 8. TAXA DE CONCLUSÃO GERAL
            // ============================================================
            
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(concluida) as concluidas
                    FROM progresso_aluno";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['taxa_conclusao_geral'] = $result['total'] > 0 
                ? round(($result['concluidas'] / $result['total']) * 100, 1) 
                : 0;
            
            $this->logAcao('VER_ESTATISTICAS', 'Professor visualizou estatísticas gerais');
            return $stats;
            
        } catch (Exception $e) {
            error_log("Erro ao obter estatísticas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Gera um relatório completo do sistema em formato CSV.
     * Inclui dados de alunos, progresso e erros.
     * 
     * @param string $tipo Tipo de relatório (alunos, progresso, erros)
     */
    public function exportarRelatorioCSV($tipo = 'alunos')
    {
        try {
            $data = [];
            $headers = [];
            
            switch ($tipo) {
                case 'alunos':
                    $headers = ['ID', 'Nome', 'Email', 'Idade', 'Nível TEA', 'Escola', 'Turma', 'Status'];
                    $alunos = $this->listarAlunos();
                    foreach ($alunos as $aluno) {
                        $data[] = [
                            $aluno['aluno_id'],
                            $aluno['nome'],
                            $aluno['email'],
                            $aluno['idade'],
                            $aluno['nivel_tea'],
                            $aluno['escola'] ?? '',
                            $aluno['turma'] ?? '',
                            $aluno['status']
                        ];
                    }
                    break;
                    
                case 'progresso':
                    $headers = ['Aluno', 'Equação', 'Dificuldade', 'Passo Atual', 'Concluída', 'Tentativas'];
                    $sql = "SELECT 
                                u.nome as aluno,
                                CONCAT(e.a, 'x + ', e.b, ' = ', e.c) as equacao,
                                e.dificuldade,
                                p.passo_atual,
                                p.concluida,
                                p.tentativas
                            FROM progresso_aluno p
                            JOIN alunos a ON p.aluno_id = a.id
                            JOIN usuarios u ON a.usuario_id = u.id
                            JOIN equacoes e ON p.equacao_id = e.id";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute();
                    $progressos = $stmt->fetchAll();
                    foreach ($progressos as $p) {
                        $data[] = [
                            $p['aluno'],
                            $p['equacao'],
                            $p['dificuldade'],
                            'Passo ' . $p['passo_atual'] . '/4',
                            $p['concluida'] ? 'Sim' : 'Não',
                            $p['tentativas']
                        ];
                    }
                    break;
                    
                case 'erros':
                    $headers = ['Aluno', 'Equação', 'Passo', 'Tipo de Erro', 'Data'];
                    $sql = "SELECT 
                                u.nome as aluno,
                                CONCAT(e.a, 'x + ', e.b, ' = ', e.c) as equacao,
                                r.passo,
                                r.tipo_erro,
                                r.data_erro
                            FROM registro_erros r
                            JOIN alunos a ON r.aluno_id = a.id
                            JOIN usuarios u ON a.usuario_id = u.id
                            JOIN equacoes e ON r.equacao_id = e.id
                            ORDER BY r.data_erro DESC";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute();
                    $erros = $stmt->fetchAll();
                    foreach ($erros as $erro) {
                        $data[] = [
                            $erro['aluno'],
                            $erro['equacao'],
                            'Passo ' . $erro['passo'],
                            ucfirst(str_replace('_', ' ', $erro['tipo_erro'])),
                            date('d/m/Y H:i', strtotime($erro['data_erro']))
                        ];
                    }
                    break;
                    
                default:
                    $this->setError('Tipo de relatório inválido.');
                    return false;
            }
            
            // ============================================================
            // GERAR O ARQUIVO CSV
            // ============================================================
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=relatorio_' . $tipo . '_' . date('Y-m-d') . '.csv');
            
            $output = fopen('php://output', 'w');
            
            // Escreve o cabeçalho com BOM para suporte a UTF-8 no Excel
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($output, $headers, ';');
            
            // Escreve os dados
            foreach ($data as $row) {
                fputcsv($output, $row, ';');
            }
            
            fclose($output);
            
            $this->logAcao('EXPORTAR_CSV', "Professor exportou relatório: $tipo");
            exit;
            
        } catch (Exception $e) {
            error_log("Erro ao exportar CSV: " . $e->getMessage());
            $this->setError('Erro ao exportar relatório.');
            return false;
        }
    }

    // ============================================================
    // MÉTODOS DE GERENCIAMENTO DE SESSÕES
    // ============================================================

    /**
     * Lista todas as sessões ativas do sistema.
     * Útil para monitoramento de usuários logados.
     * 
     * @return array Lista de sessões ativas
     */
    public function listarSessoesAtivas()
    {
        try {
            $sql = "SELECT 
                        s.*,
                        u.nome as usuario_nome,
                        u.email as usuario_email,
                        u.tipo_perfil
                    FROM sessao s
                    JOIN usuarios u ON s.usuario_id = u.id
                    WHERE s.ativa = 1 AND s.expiracao > NOW()
                    ORDER BY s.ultima_atividade DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $sessoes = $stmt->fetchAll();
            
            foreach ($sessoes as &$sessao) {
                // Calcula o tempo de inatividade
                $sessao['tempo_inativo'] = round((time() - strtotime($sessao['ultima_atividade'])) / 60, 0) . ' min';
            }
            
            return $sessoes;
            
        } catch (Exception $e) {
            error_log("Erro ao listar sessões: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Encerra uma sessão específica (força logout de um usuário).
     * 
     * @param int $sessao_id ID da sessão
     * @return bool True se encerrada com sucesso
     */
    public function encerrarSessao($sessao_id)
    {
        try {
            $sql = "UPDATE sessao SET ativa = 0 WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([':id' => $sessao_id]);
            
            if ($result) {
                $this->logAcao('ENCERRAR_SESSAO', "Professor encerrou sessão ID: $sessao_id");
                $this->setSuccess('Sessão encerrada com sucesso!');
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao encerrar sessão: " . $e->getMessage());
            $this->setError('Erro ao encerrar sessão.');
            return false;
        }
    }

    // ============================================================
    // MÉTODOS AUXILIARES
    // ============================================================

    /**
     * Registra uma ação no log do sistema.
     * 
     * @param string $acao       Nome da ação
     * @param string $descricao  Descrição detalhada da ação
     */
    private function logAcao($acao, $descricao)
    {
        try {
            // ============================================================
            // 1. REGISTRO EM TABELA DE LOGS
            // ============================================================
            
            $usuario_id = $_SESSION['usuario_id'] ?? null;
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            
            $sql = "INSERT INTO logs_sistema (usuario_id, acao, descricao, ip_address) 
                    VALUES (:usuario_id, :acao, :descricao, :ip_address)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':usuario_id' => $usuario_id,
                ':acao' => $acao,
                ':descricao' => $descricao,
                ':ip_address' => $ip
            ]);
            
        } catch (Exception $e) {
            // Erro silencioso - apenas loga no error_log do PHP
            error_log("Erro ao registrar log: " . $e->getMessage());
        }
    }

    /**
     * Define uma mensagem de erro para ser exibida na view.
     * 
     * @param string $mensagem Mensagem de erro
     */
    private function setError($mensagem)
    {
        $_SESSION['admin_error'] = $mensagem;
    }

    /**
     * Define uma mensagem de sucesso para ser exibida na view.
     * 
     * @param string $mensagem Mensagem de sucesso
     */
    private function setSuccess($mensagem)
    {
        $_SESSION['admin_success'] = $mensagem;
    }

    /**
     * Obtém e limpa a mensagem de erro armazenada na sessão.
     * 
     * @return string|null Mensagem de erro ou null se não houver
     */
    public function getError()
    {
        if (isset($_SESSION['admin_error'])) {
            $msg = $_SESSION['admin_error'];
            unset($_SESSION['admin_error']);
            return $msg;
        }
        return null;
    }

    /**
     * Obtém e limpa a mensagem de sucesso armazenada na sessão.
     * 
     * @return string|null Mensagem de sucesso ou null se não houver
     */
    public function getSuccess()
    {
        if (isset($_SESSION['admin_success'])) {
            $msg = $_SESSION['admin_success'];
            unset($_SESSION['admin_success']);
            return $msg;
        }
        return null;
    }
}