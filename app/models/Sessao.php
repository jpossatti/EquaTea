<?php
/**
 * ============================================================
 * Sessao.php
 * Modelo que gerencia as sessões de usuários no sistema.
 * 
 * FUNCIONALIDADES:
 * - Criar e gerenciar sessões de usuários
 * - Validar sessões ativas
 * - Limpeza automática de sessões expiradas
 * - Histórico de acessos
 * 
 * @package EquaTEA
 * @subpackage Models
 * @author Equipe EquaTEA
 * @version 1.0
 * ============================================================
 */

require_once MODELS_PATH . '/../config/database.php';

/**
 * Class Sessao
 * 
 * Gerencia o ciclo de vida das sessões de usuários,
 * incluindo criação, validação, renovação e encerramento.
 * 
 * @author Equipe EquaTEA
 */
class Sessao
{
    /**
     * @var PDO Conexão com o banco de dados
     */
    private $db;
    
    /**
     * @var int Tempo de expiração da sessão em segundos (1 hora = 3600)
     */
    const TEMPO_EXPIRACAO = 3600;
    
    /**
     * @var int Tempo de renovação da sessão em segundos (10 minutos)
     */
    const TEMPO_RENOVACAO = 600;

    /**
     * Construtor da classe Sessao.
     * Inicializa a conexão com o banco de dados e
     * executa a limpeza automática de sessões expiradas.
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->limparExpiradas();
    }

    // ============================================================
    // MÉTODOS DE CRIAÇÃO E GERENCIAMENTO DE SESSÃO
    // ============================================================

    /**
     * Cria uma nova sessão para um usuário.
     * 
     * @param int    $usuario_id ID do usuário
     * @param string $ip_address Endereço IP do usuário
     * @param string $user_agent User-Agent do navegador
     * @return string|false Session ID gerado ou false em caso de erro
     */
    public function criar($usuario_id, $ip_address = null, $user_agent = null)
    {
        try {
            // ============================================================
            // 1. GERAR UM ID DE SESSÃO ÚNICO
            // ============================================================
            
            $session_id = $this->gerarSessionId();
            
            // ============================================================
            // 2. DEFINIR DATAS DE EXPIRAÇÃO
            // ============================================================
            
            $data_criacao = date('Y-m-d H:i:s');
            $data_expiracao = date('Y-m-d H:i:s', time() + self::TEMPO_EXPIRACAO);
            
            // ============================================================
            // 3. INSERIR A SESSÃO NO BANCO DE DADOS
            // ============================================================
            
            $sql = "INSERT INTO sessao 
                    (usuario_id, session_id, ip_address, user_agent, data_criacao, ultima_atividade, expiracao, ativa) 
                    VALUES 
                    (:usuario_id, :session_id, :ip_address, :user_agent, :data_criacao, :ultima_atividade, :expiracao, 1)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':usuario_id' => $usuario_id,
                ':session_id' => $session_id,
                ':ip_address' => $ip_address,
                ':user_agent' => $user_agent,
                ':data_criacao' => $data_criacao,
                ':ultima_atividade' => $data_criacao,
                ':expiracao' => $data_expiracao
            ]);
            
            if (!$result) {
                throw new Exception('Falha ao criar sessão.');
            }
            
            // ============================================================
            // 4. REGISTRAR LOG DE ACESSO
            // ============================================================
            
            $this->registrarAcesso($usuario_id);
            
            return $session_id;
            
        } catch (Exception $e) {
            error_log("Erro ao criar sessão: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gera um ID de sessão único e aleatório.
     * 
     * @return string Session ID de 64 caracteres
     */
    private function gerarSessionId()
    {
        // ============================================================
        // COMBINA VÁRIAS FONTES DE RANDOMICIDADE
        // ============================================================
        
        $entropy = uniqid() . 
                   microtime() . 
                   bin2hex(random_bytes(32)) . 
                   uniqid() . 
                   mt_rand() . 
                   $_SERVER['REMOTE_ADDR'] ?? 'localhost';
        
        $session_id = hash('sha256', $entropy);
        
        // Verifica se o ID já existe no banco
        $sql = "SELECT COUNT(*) FROM sessao WHERE session_id = :session_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':session_id' => $session_id]);
        
        if ($stmt->fetch()['COUNT(*)'] > 0) {
            // Recursivo: gera um novo ID se houver colisão
            return $this->gerarSessionId();
        }
        
        return $session_id;
    }

    /**
     * Valida uma sessão existente.
     * 
     * @param string $session_id ID da sessão
     * @return array|false Dados da sessão ou false se inválida
     */
    public function validar($session_id)
    {
        try {
            // ============================================================
            // 1. BUSCAR A SESSÃO NO BANCO
            // ============================================================
            
            $sql = "SELECT 
                        s.*,
                        u.nome as usuario_nome,
                        u.email as usuario_email,
                        u.tipo_perfil
                    FROM sessao s
                    JOIN usuarios u ON s.usuario_id = u.id
                    WHERE s.session_id = :session_id 
                    AND s.ativa = 1 
                    AND s.expiracao > NOW()";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':session_id' => $session_id]);
            $sessao = $stmt->fetch();
            
            // ============================================================
            // 2. VERIFICAR SE A SESSÃO EXISTE
            // ============================================================
            
            if (!$sessao) {
                return false;
            }
            
            // ============================================================
            // 3. RENOVAR A SESSÃO (SE PRÓXIMO DA EXPIRAÇÃO)
            // ============================================================
            
            $tempo_restante = strtotime($sessao['expiracao']) - time();
            if ($tempo_restante < self::TEMPO_RENOVACAO) {
                $this->renovar($session_id);
                $sessao['renovada'] = true;
            }
            
            // ============================================================
            // 4. ATUALIZAR ÚLTIMA ATIVIDADE
            // ============================================================
            
            $this->atualizarAtividade($session_id);
            
            return $sessao;
            
        } catch (PDOException $e) {
            error_log("Erro ao validar sessão: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Renova uma sessão, estendendo seu tempo de expiração.
     * 
     * @param string $session_id ID da sessão
     * @return bool True se renovada com sucesso
     */
    public function renovar($session_id)
    {
        try {
            $nova_expiracao = date('Y-m-d H:i:s', time() + self::TEMPO_EXPIRACAO);
            
            $sql = "UPDATE sessao 
                    SET expiracao = :expiracao 
                    WHERE session_id = :session_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':expiracao' => $nova_expiracao,
                ':session_id' => $session_id
            ]);
            
        } catch (PDOException $e) {
            error_log("Erro ao renovar sessão: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza o timestamp da última atividade da sessão.
     * 
     * @param string $session_id ID da sessão
     * @return bool True se atualizado com sucesso
     */
    private function atualizarAtividade($session_id)
    {
        try {
            $sql = "UPDATE sessao 
                    SET ultima_atividade = NOW() 
                    WHERE session_id = :session_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':session_id' => $session_id]);
            
        } catch (PDOException $e) {
            error_log("Erro ao atualizar atividade: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Encerra uma sessão (logout).
     * 
     * @param string $session_id ID da sessão
     * @return bool True se encerrada com sucesso
     */
    public function encerrar($session_id)
    {
        try {
            // ============================================================
            // 1. MARCAR A SESSÃO COMO INATIVA
            // ============================================================
            
            $sql = "UPDATE sessao 
                    SET ativa = 0 
                    WHERE session_id = :session_id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([':session_id' => $session_id]);
            
            if ($result) {
                // ============================================================
                // 2. REGISTRAR LOG DE LOGOUT
                // ============================================================
                
                $this->registrarLogout($session_id);
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Erro ao encerrar sessão: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Encerra todas as sessões ativas de um usuário.
     * Útil para forçar logout em todas as sessões (ex: mudança de senha).
     * 
     * @param int $usuario_id ID do usuário
     * @return int Número de sessões encerradas
     */
    public function encerrarTodasSessoes($usuario_id)
    {
        try {
            // ============================================================
            // 1. BUSCAR AS SESSÕES ATIVAS DO USUÁRIO
            // ============================================================
            
            $sql = "SELECT session_id FROM sessao 
                    WHERE usuario_id = :usuario_id AND ativa = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':usuario_id' => $usuario_id]);
            $sessoes = $stmt->fetchAll();
            
            $total = 0;
            
            // ============================================================
            // 2. ENCERRAR CADA SESSÃO
            // ============================================================
            
            foreach ($sessoes as $sessao) {
                if ($this->encerrar($sessao['session_id'])) {
                    $total++;
                }
            }
            
            return $total;
            
        } catch (PDOException $e) {
            error_log("Erro ao encerrar todas as sessões: " . $e->getMessage());
            return 0;
        }
    }

    // ============================================================
    // MÉTODOS DE LIMPEZA E MANUTENÇÃO
    // ============================================================

    /**
     * Limpa todas as sessões expiradas do banco de dados.
     * Este método é chamado automaticamente no construtor.
     * 
     * @return int Número de sessões removidas
     */
    public function limparExpiradas()
    {
        try {
            $sql = "DELETE FROM sessao 
                    WHERE expiracao < NOW() OR ativa = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->rowCount();
            
        } catch (PDOException $e) {
            error_log("Erro ao limpar sessões expiradas: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtém estatísticas sobre as sessões ativas.
     * 
     * @return array Estatísticas de sessões
     */
    public function getEstatisticas()
    {
        try {
            $stats = [];
            
            // ============================================================
            // 1. TOTAL DE SESSÕES ATIVAS
            // ============================================================
            
            $sql = "SELECT COUNT(*) as total FROM sessao WHERE ativa = 1 AND expiracao > NOW()";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['total_ativas'] = $stmt->fetch()['total'];
            
            // ============================================================
            // 2. TOTAL DE SESSÕES EXPIRADAS
            // ============================================================
            
            $sql = "SELECT COUNT(*) as total FROM sessao WHERE expiracao < NOW()";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['total_expiradas'] = $stmt->fetch()['total'];
            
            // ============================================================
            // 3. MÉDIA DE DURAÇÃO DAS SESSÕES
            // ============================================================
            
            $sql = "SELECT AVG(TIMESTAMPDIFF(SECOND, data_criacao, ultima_atividade)) as media 
                    FROM sessao 
                    WHERE data_criacao IS NOT NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['media_duracao_segundos'] = $result['media'] ?? 0;
            $stats['media_duracao_minutos'] = round($stats['media_duracao_segundos'] / 60, 1);
            
            // ============================================================
            // 4. SESSÕES POR PERFIL DE USUÁRIO
            // ============================================================
            
            $sql = "SELECT 
                        u.tipo_perfil,
                        COUNT(*) as total
                    FROM sessao s
                    JOIN usuarios u ON s.usuario_id = u.id
                    WHERE s.ativa = 1
                    GROUP BY u.tipo_perfil";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['por_perfil'] = $stmt->fetchAll();
            
            return $stats;
            
        } catch (PDOException $e) {
            error_log("Erro ao obter estatísticas de sessões: " . $e->getMessage());
            return [];
        }
    }

    // ============================================================
    // MÉTODOS DE LOGGING E AUDITORIA
    // ============================================================

    /**
     * Registra um acesso (login) de usuário.
     * 
     * @param int $usuario_id ID do usuário
     */
    private function registrarAcesso($usuario_id)
    {
        try {
            $sql = "INSERT INTO logs_sistema (usuario_id, acao, descricao, ip_address) 
                    VALUES (:usuario_id, 'LOGIN', 'Usuário realizou login no sistema', :ip)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':usuario_id' => $usuario_id,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? null
            ]);
            
        } catch (PDOException $e) {
            // Erro silencioso
        }
    }

    /**
     * Registra um logout de usuário.
     * 
     * @param string $session_id ID da sessão
     */
    private function registrarLogout($session_id)
    {
        try {
            // Buscar o usuário da sessão
            $sql = "SELECT usuario_id FROM sessao WHERE session_id = :session_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':session_id' => $session_id]);
            $result = $stmt->fetch();
            
            if ($result) {
                $sql = "INSERT INTO logs_sistema (usuario_id, acao, descricao, ip_address) 
                        VALUES (:usuario_id, 'LOGOUT', 'Usuário realizou logout', :ip)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':usuario_id' => $result['usuario_id'],
                    ':ip' => $_SERVER['REMOTE_ADDR'] ?? null
                ]);
            }
            
        } catch (PDOException $e) {
            // Erro silencioso
        }
    }

    // ============================================================
    // MÉTODOS DE CONSULTA E BUSCA
    // ============================================================

    /**
     * Busca uma sessão pelo Session ID.
     * 
     * @param string $session_id ID da sessão
     * @return array|false Dados da sessão ou false
     */
    public function buscarPorId($session_id)
    {
        try {
            $sql = "SELECT * FROM sessao WHERE session_id = :session_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':session_id' => $session_id]);
            return $stmt->fetch();
            
        } catch (PDOException $e) {
            error_log("Erro ao buscar sessão: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca todas as sessões de um usuário.
     * 
     * @param int  $usuario_id ID do usuário
     * @param bool $apenas_ativas Se true, retorna apenas sessões ativas
     * @return array Lista de sessões do usuário
     */
    public function buscarPorUsuario($usuario_id, $apenas_ativas = true)
    {
        try {
            $sql = "SELECT * FROM sessao WHERE usuario_id = :usuario_id";
            
            if ($apenas_ativas) {
                $sql .= " AND ativa = 1 AND expiracao > NOW()";
            }
            
            $sql .= " ORDER BY data_criacao DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':usuario_id' => $usuario_id]);
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Erro ao buscar sessões do usuário: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica se uma sessão específica está ativa.
     * 
     * @param string $session_id ID da sessão
     * @return bool True se a sessão está ativa
     */
    public function isAtiva($session_id)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM sessao 
                    WHERE session_id = :session_id 
                    AND ativa = 1 
                    AND expiracao > NOW()";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':session_id' => $session_id]);
            return $stmt->fetch()['total'] > 0;
            
        } catch (PDOException $e) {
            error_log("Erro ao verificar sessão ativa: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtém o ID do usuário associado a uma sessão.
     * 
     * @param string $session_id ID da sessão
     * @return int|false ID do usuário ou false
     */
    public function getUsuarioId($session_id)
    {
        try {
            $sql = "SELECT usuario_id FROM sessao WHERE session_id = :session_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':session_id' => $session_id]);
            $result = $stmt->fetch();
            return $result ? $result['usuario_id'] : false;
            
        } catch (PDOException $e) {
            error_log("Erro ao obter usuário da sessão: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Calcula o tempo restante de uma sessão em minutos.
     * 
     * @param string $session_id ID da sessão
     * @return int Tempo restante em minutos
     */
    public function getTempoRestante($session_id)
    {
        try {
            $sql = "SELECT expiracao FROM sessao WHERE session_id = :session_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':session_id' => $session_id]);
            $result = $stmt->fetch();
            
            if (!$result) {
                return 0;
            }
            
            $restante = strtotime($result['expiracao']) - time();
            return max(0, round($restante / 60, 0));
            
        } catch (PDOException $e) {
            error_log("Erro ao calcular tempo restante: " . $e->getMessage());
            return 0;
        }
    }

    // ============================================================
    // MÉTODOS DE GESTÃO DE SEGURANÇA
    // ============================================================

    /**
     * Verifica se o IP e User-Agent correspondem aos registrados.
     * Previne sequestro de sessão (session hijacking).
     * 
     * @param string $session_id   ID da sessão
     * @param string $ip_address   IP atual do usuário
     * @param string $user_agent   User-Agent atual
     * @return bool True se os dados coincidem
     */
    public function verificarConsistencia($session_id, $ip_address, $user_agent)
    {
        try {
            $sql = "SELECT ip_address, user_agent FROM sessao WHERE session_id = :session_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':session_id' => $session_id]);
            $sessao = $stmt->fetch();
            
            if (!$sessao) {
                return false;
            }
            
            // ============================================================
            // PERMITE IP DINÂMICO PARA PROVEDORES MÓVEIS
            // Verifica apenas o prefixo do IP (primeiros 2 octetos)
            // ============================================================
            
            $ip_sessao = explode('.', $sessao['ip_address']);
            $ip_atual = explode('.', $ip_address);
            
            if (count($ip_sessao) >= 2 && count($ip_atual) >= 2) {
                $ip_valido = ($ip_sessao[0] == $ip_atual[0]) && ($ip_sessao[1] == $ip_atual[1]);
            } else {
                $ip_valido = ($sessao['ip_address'] == $ip_address);
            }
            
            // ============================================================
            // VERIFICA O USER-AGENT (apenas os primeiros 50 caracteres)
            // ============================================================
            
            $ua_sessao = substr($sessao['user_agent'], 0, 50);
            $ua_atual = substr($user_agent, 0, 50);
            $ua_valido = ($ua_sessao == $ua_atual);
            
            return $ip_valido && $ua_valido;
            
        } catch (PDOException $e) {
            error_log("Erro ao verificar consistência: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Bloqueia todas as sessões de um usuário, exceto a atual.
     * Útil quando o usuário muda a senha.
     * 
     * @param int    $usuario_id        ID do usuário
     * @param string $session_atual     ID da sessão atual (a ser mantida)
     * @return int Número de sessões bloqueadas
     */
    public function bloquearOutrasSessoes($usuario_id, $session_atual)
    {
        try {
            $sql = "UPDATE sessao 
                    SET ativa = 0 
                    WHERE usuario_id = :usuario_id 
                    AND session_id != :session_atual 
                    AND ativa = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':usuario_id' => $usuario_id,
                ':session_atual' => $session_atual
            ]);
            return $stmt->rowCount();
            
        } catch (PDOException $e) {
            error_log("Erro ao bloquear outras sessões: " . $e->getMessage());
            return 0;
        }
    }
}