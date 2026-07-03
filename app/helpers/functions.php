<?php
/**
 * ============================================================
 * functions.php
 * Funções auxiliares globais do sistema EquaTEA.
 * 
 * FUNCIONALIDADES:
 * - Gerenciamento de sessão e autenticação
 * - Geração de tokens CSRF
 * - Formatação de dados
 * - Funções de data e hora
 * - Funções de debug
 * - Funções de URL
 * - Funções de validação básica
 * 
 * @package EquaTEA
 * @subpackage Helpers
 * @author Equipe EquaTEA
 * @version 1.0
 * ============================================================
 */

// ============================================================
// CONFIGURAÇÕES INICIAIS
// ============================================================

// Garante que a sessão esteja iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================
// 1. FUNÇÕES DE SESSÃO E AUTENTICAÇÃO
// ============================================================

/**
 * Verifica se o usuário está logado
 * 
 * @return bool True se o usuário está logado
 */
function estaLogado()
{
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Verifica se o usuário é um aluno
 * 
 * @return bool True se o usuário é um aluno
 */
function isAluno()
{
    return isset($_SESSION['tipo_perfil']) && $_SESSION['tipo_perfil'] === 'aluno';
}

/**
 * Verifica se o usuário é um professor
 * 
 * @return bool True se o usuário é um professor
 */
function isProfessor()
{
    return isset($_SESSION['tipo_perfil']) && $_SESSION['tipo_perfil'] === 'professor';
}

/**
 * Verifica se o usuário está logado e redireciona para login se não estiver
 * 
 * @param string $redirect_url URL para redirecionar (opcional)
 */
function verificarSessao($redirect_url = null)
{
    if (!estaLogado()) {
        $url = $redirect_url ?? BASE_URL . 'app/views/auth/login.php';
        header('Location: ' . $url);
        exit;
    }
    
    // Verifica expiração da sessão (1 hora)
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 3600)) {
        destruirSessao();
        header('Location: ' . BASE_URL . 'app/views/auth/login.php?erro=expirado');
        exit;
    }
}

/**
 * Inicia uma sessão para o usuário
 * 
 * @param array $usuario Dados do usuário
 */
function iniciarSessao($usuario)
{
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['email'] = $usuario['email'];
    $_SESSION['tipo_perfil'] = $usuario['tipo_perfil'];
    $_SESSION['login_time'] = time();
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    // Se for aluno, buscar o ID do aluno
    if ($usuario['tipo_perfil'] === 'aluno') {
        try {
            require_once MODELS_PATH . '/Aluno.php';
            $aluno = new Aluno();
            $dados = $aluno->getDadosCompletos($usuario['id']);
            if ($dados) {
                $_SESSION['aluno_id'] = $dados['aluno_id'];
                $_SESSION['nivel_tea'] = $dados['nivel_tea'];
                $_SESSION['escola'] = $dados['escola'];
                $_SESSION['turma'] = $dados['turma'];
            }
        } catch (Exception $e) {
            error_log("Erro ao buscar dados do aluno: " . $e->getMessage());
        }
    }
    
    // Se for professor, buscar o ID do professor
    if ($usuario['tipo_perfil'] === 'professor') {
        try {
            require_once MODELS_PATH . '/Professor.php';
            $professor = new Professor();
            $dados = $professor->getByUsuarioId($usuario['id']);
            if ($dados) {
                $_SESSION['professor_id'] = $dados['id'];
                $_SESSION['escola_professor'] = $dados['escola'];
            }
        } catch (Exception $e) {
            error_log("Erro ao buscar dados do professor: " . $e->getMessage());
        }
    }
}

/**
 * Destroi a sessão do usuário
 */
function destruirSessao()
{
    // Limpar todas as variáveis de sessão
    $_SESSION = array();
    
    // Destruir o cookie de sessão
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    // Destruir a sessão
    session_destroy();
}

/**
 * Obtém o ID do usuário logado
 * 
 * @return int|null ID do usuário ou null se não logado
 */
function getUsuarioId()
{
    return $_SESSION['usuario_id'] ?? null;
}

/**
 * Obtém o nome do usuário logado
 * 
 * @return string Nome do usuário ou 'Usuário'
 */
function getUsuarioNome()
{
    return $_SESSION['usuario_nome'] ?? 'Usuário';
}

/**
 * Obtém o perfil do usuário logado
 * 
 * @return string Perfil do usuário (aluno/professor) ou null
 */
function getUsuarioPerfil()
{
    return $_SESSION['tipo_perfil'] ?? null;
}

// ============================================================
// 2. FUNÇÕES DE SEGURANÇA (CSRF)
// ============================================================

/**
 * Gera um token CSRF para proteção de formulários
 * 
 * @return string Token CSRF
 */
function gerarTokenCSRF()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica se o token CSRF é válido
 * 
 * @param string $token Token a ser verificado
 * @return bool True se o token é válido
 */
function validarTokenCSRF($token)
{
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Regenera o token CSRF
 */
function regenerarTokenCSRF()
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ============================================================
// 3. FUNÇÕES DE SANITIZAÇÃO E VALIDAÇÃO
// ============================================================

/**
 * Sanitiza uma string para exibição em HTML
 * 
 * @param string $text Texto a ser sanitizado
 * @return string Texto sanitizado
 */
function e($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitiza uma string para uso em URL
 * 
 * @param string $text Texto a ser sanitizado
 * @return string Texto sanitizado para URL
 */
function urlEncode($text)
{
    return urlencode($text);
}

/**
 * Valida um endereço de e-mail
 * 
 * @param string $email E-mail a ser validado
 * @return bool True se o e-mail é válido
 */
function validarEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida um número inteiro dentro de um intervalo
 * 
 * @param int $number Número a ser validado
 * @param int $min Valor mínimo
 * @param int $max Valor máximo
 * @return bool True se o número está no intervalo
 */
function validarNumero($number, $min, $max)
{
    return is_numeric($number) && $number >= $min && $number <= $max;
}

/**
 * Valida um CPF (apenas formatação básica)
 * 
 * @param string $cpf CPF a ser validado
 * @return bool True se o CPF tem formato válido
 */
function validarCPF($cpf)
{
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    return strlen($cpf) === 11;
}

/**
 * Valida uma senha (mínimo 4 caracteres)
 * 
 * @param string $senha Senha a ser validada
 * @param int $min_length Tamanho mínimo
 * @return bool True se a senha é válida
 */
function validarSenha($senha, $min_length = 4)
{
    return strlen($senha) >= $min_length;
}

// ============================================================
// 4. FUNÇÕES DE FORMATAÇÃO
// ============================================================

/**
 * Formata uma data para exibição
 * 
 * @param string $data Data no formato YYYY-MM-DD HH:MM:SS
 * @param string $formato Formato de saída
 * @return string Data formatada
 */
function formatarData($data, $formato = 'd/m/Y H:i')
{
    if (empty($data)) {
        return '-';
    }
    try {
        $dt = new DateTime($data);
        return $dt->format($formato);
    } catch (Exception $e) {
        return $data;
    }
}

/**
 * Formata uma data apenas com a parte da data
 * 
 * @param string $data Data no formato YYYY-MM-DD HH:MM:SS
 * @return string Data formatada (d/m/Y)
 */
function formatarDataSimples($data)
{
    return formatarData($data, 'd/m/Y');
}

/**
 * Formata uma data para exibição em português
 * 
 * @param string $data Data no formato YYYY-MM-DD HH:MM:SS
 * @return string Data em português
 */
function formatarDataPortugues($data)
{
    if (empty($data)) {
        return '-';
    }
    
    $meses = [
        'January' => 'Janeiro', 'February' => 'Fevereiro',
        'March' => 'Março', 'April' => 'Abril',
        'May' => 'Maio', 'June' => 'Junho',
        'July' => 'Julho', 'August' => 'Agosto',
        'September' => 'Setembro', 'October' => 'Outubro',
        'November' => 'Novembro', 'December' => 'Dezembro'
    ];
    
    $data_formatada = date('d F Y H:i', strtotime($data));
    return str_replace(array_keys($meses), array_values($meses), $data_formatada);
}

/**
 * Calcula o tempo decorrido desde uma data
 * 
 * @param string $data Data no formato YYYY-MM-DD HH:MM:SS
 * @return string Tempo decorrido (ex: "há 5 minutos")
 */
function tempoDecorrido($data)
{
    if (empty($data)) {
        return '-';
    }
    
    $agora = new DateTime();
    $data_obj = new DateTime($data);
    $diff = $agora->diff($data_obj);
    
    if ($diff->y > 0) {
        return "há " . $diff->y . " ano" . ($diff->y > 1 ? "s" : "");
    } elseif ($diff->m > 0) {
        return "há " . $diff->m . " mês" . ($diff->m > 1 ? "es" : "");
    } elseif ($diff->d > 0) {
        return "há " . $diff->d . " dia" . ($diff->d > 1 ? "s" : "");
    } elseif ($diff->h > 0) {
        return "há " . $diff->h . " hora" . ($diff->h > 1 ? "s" : "");
    } elseif ($diff->i > 0) {
        return "há " . $diff->i . " minuto" . ($diff->i > 1 ? "s" : "");
    } else {
        return "agora mesmo";
    }
}

/**
 * Formata um valor de dificuldade para exibição
 * 
 * @param string $dificuldade Dificuldade (facil, medio, dificil)
 * @return string HTML com badge
 */
function formatarDificuldade($dificuldade)
{
    $classes = [
        'facil' => 'badge-success',
        'medio' => 'badge-warning',
        'dificil' => 'badge-danger'
    ];
    
    $labels = [
        'facil' => 'Fácil',
        'medio' => 'Médio',
        'dificil' => 'Difícil'
    ];
    
    $classe = $classes[$dificuldade] ?? 'badge-secondary';
    $label = $labels[$dificuldade] ?? $dificuldade;
    
    return '<span class="badge ' . $classe . '">' . $label . '</span>';
}

/**
 * Formata um tipo de erro para exibição
 * 
 * @param string $tipo_erro Tipo de erro
 * @return string Erro formatado
 */
function formatarTipoErro($tipo_erro)
{
    $labels = [
        'operacao_inversa' => 'Operação Inversa',
        'calculo_errado' => 'Cálculo Errado',
        'sinal_trocado' => 'Sinal Trocado',
        'divisao_incorreta' => 'Divisão Incorreta',
        'identificacao_errada' => 'Identificação Errada',
        'outro' => 'Outro'
    ];
    
    return $labels[$tipo_erro] ?? ucfirst(str_replace('_', ' ', $tipo_erro));
}

// ============================================================
// 5. FUNÇÕES DE FORMULÁRIO
// ============================================================

/**
 * Mantém o valor de um campo de formulário após submissão
 * 
 * @param string $nome Nome do campo
 * @param string $padrao Valor padrão
 * @return string Valor do campo
 */
function manterValor($nome, $padrao = '')
{
    if (isset($_POST[$nome])) {
        return e($_POST[$nome]);
    }
    if (isset($_GET[$nome])) {
        return e($_GET[$nome]);
    }
    return $padrao;
}

/**
 * Verifica se um campo está selecionado (para selects e radios)
 * 
 * @param string $valor Valor a ser verificado
 * @param string $selecionado Valor selecionado
 * @param bool $multiple Se é múltipla seleção
 * @return string 'selected' ou 'checked'
 */
function estaSelecionado($valor, $selecionado, $multiple = false)
{
    if ($multiple && is_array($selecionado)) {
        return in_array($valor, $selecionado) ? 'selected' : '';
    }
    return $valor == $selecionado ? 'selected' : '';
}

/**
 * Gera um campo de formulário com token CSRF
 * 
 * @return string HTML do campo
 */
function campoCSRF()
{
    return '<input type="hidden" name="csrf_token" value="' . gerarTokenCSRF() . '">';
}

// ============================================================
// 6. FUNÇÕES DE URL
// ============================================================

/**
 * Gera uma URL amigável para o sistema
 * 
 * @param string $path Caminho dentro do sistema
 * @return string URL completa
 */
function url($path = '')
{
    $base = rtrim(BASE_URL, '/');
    $path = ltrim($path, '/');
    return $base . '/' . $path;
}

/**
 * Redireciona para uma URL
 * 
 * @param string $url URL para redirecionar
 */
function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

/**
 * Retorna a URL da página atual
 * 
 * @return string URL da página atual
 */
function urlAtual()
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

// ============================================================
// 7. FUNÇÕES DE ARRAY E OBJETO
// ============================================================

/**
 * Obtém um valor de um array com fallback
 * 
 * @param array $array Array de origem
 * @param string $key Chave a ser buscada
 * @param mixed $default Valor padrão
 * @return mixed Valor encontrado ou padrão
 */
function array_get($array, $key, $default = null)
{
    return isset($array[$key]) ? $array[$key] : $default;
}

/**
 * Agrupa um array por uma chave
 * 
 * @param array $array Array a ser agrupado
 * @param string $key Chave para agrupamento
 * @return array Array agrupado
 */
function array_group_by($array, $key)
{
    $result = [];
    foreach ($array as $item) {
        $group_key = $item[$key] ?? 'default';
        $result[$group_key][] = $item;
    }
    return $result;
}

/**
 * Converte um objeto para array
 * 
 * @param object $obj Objeto a ser convertido
 * @return array Array resultante
 */
function object_to_array($obj)
{
    if (is_object($obj)) {
        return get_object_vars($obj);
    }
    return $obj;
}

// ============================================================
// 8. FUNÇÕES DE DEBUG
// ============================================================

/**
 * Função de debug para exibir variáveis de forma legível
 * 
 * @param mixed $var Variável a ser debugada
 * @param bool $die Se deve parar a execução
 */
function dd($var, $die = true)
{
    echo '<pre style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #ddd; font-family: monospace; font-size: 14px; margin: 20px; overflow-x: auto;">';
    print_r($var);
    echo '</pre>';
    if ($die) {
        die();
    }
}

/**
 * Log de debug (escreve no arquivo de log)
 * 
 * @param mixed $data Dados a serem logados
 * @param string $label Label para identificação
 */
function debug_log($data, $label = 'DEBUG')
{
    $log_file = LOG_PATH . '/debug.log';
    $message = '[' . date('Y-m-d H:i:s') . "] $label: " . print_r($data, true) . "\n";
    error_log($message, 3, $log_file);
}

// ============================================================
// 9. FUNÇÕES DE NOTIFICAÇÃO
// ============================================================

/**
 * Define uma mensagem de sucesso na sessão
 * 
 * @param string $mensagem Mensagem de sucesso
 */
function setSuccess($mensagem)
{
    $_SESSION['flash_success'] = $mensagem;
}

/**
 * Define uma mensagem de erro na sessão
 * 
 * @param string $mensagem Mensagem de erro
 */
function setError($mensagem)
{
    $_SESSION['flash_error'] = $mensagem;
}

/**
 * Define uma mensagem de info na sessão
 * 
 * @param string $mensagem Mensagem de info
 */
function setInfo($mensagem)
{
    $_SESSION['flash_info'] = $mensagem;
}

/**
 * Obtém e limpa a mensagem flash
 * 
 * @param string $tipo Tipo de mensagem (success, error, info)
 * @return string|null Mensagem ou null
 */
function getFlash($tipo)
{
    $key = 'flash_' . $tipo;
    if (isset($_SESSION[$key])) {
        $mensagem = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $mensagem;
    }
    return null;
}

/**
 * Exibe todas as mensagens flash
 * 
 * @return string HTML das mensagens
 */
function exibirFlash()
{
    $html = '';
    $tipos = ['success' => 'sucesso', 'error' => 'erro', 'info' => 'info'];
    
    foreach ($tipos as $tipo => $classe) {
        $mensagem = getFlash($tipo);
        if ($mensagem) {
            $html .= '<div class="alert alert-' . $classe . '" role="alert">';
            $html .= '<span class="alert-icon" aria-hidden="true">' . ($tipo === 'success' ? '✅' : ($tipo === 'error' ? '⚠️' : 'ℹ️')) . '</span>';
            $html .= e($mensagem);
            $html .= '<button class="alert-close" onclick="this.parentElement.style.display=\'none\'" aria-label="Fechar mensagem">✕</button>';
            $html .= '</div>';
        }
    }
    
    return $html;
}

// ============================================================
// 10. FUNÇÕES DE ARQUIVO
// ============================================================

/**
 * Verifica se um arquivo existe e é legível
 * 
 * @param string $path Caminho do arquivo
 * @return bool True se o arquivo existe e é legível
 */
function arquivoExiste($path)
{
    return file_exists($path) && is_readable($path);
}

/**
 * Obtém a extensão de um arquivo
 * 
 * @param string $filename Nome do arquivo
 * @return string Extensão do arquivo
 */
function getExtensao($filename)
{
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Gera um nome único para arquivo
 * 
 * @param string $prefix Prefixo opcional
 * @return string Nome único
 */
function gerarNomeUnico($prefix = '')
{
    return $prefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(8));
}

// ============================================================
// 11. FUNÇÕES DE DATA/HORA ADICIONAIS
// ============================================================

/**
 * Verifica se uma data é válida
 * 
 * @param string $date Data no formato YYYY-MM-DD
 * @param string $format Formato da data
 * @return bool True se a data é válida
 */
function validarData($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Obtém a diferença em dias entre duas datas
 * 
 * @param string $data1 Primeira data
 * @param string $data2 Segunda data
 * @return int Diferença em dias
 */
function diferencaDias($data1, $data2 = null)
{
    if ($data2 === null) {
        $data2 = date('Y-m-d');
    }
    $dt1 = new DateTime($data1);
    $dt2 = new DateTime($data2);
    $diff = $dt1->diff($dt2);
    return $diff->days;
}

// ============================================================
// 12. FUNÇÃO DE LOG DO SISTEMA
// ============================================================

/**
 * Registra uma ação no log do sistema
 * 
 * @param string $acao Nome da ação
 * @param string $descricao Descrição da ação
 * @param int|null $usuario_id ID do usuário (opcional)
 */
function logSistema($acao, $descricao, $usuario_id = null)
{
    try {
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT INTO logs_sistema (usuario_id, acao, descricao, ip_address) 
                VALUES (:usuario_id, :acao, :descricao, :ip_address)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuario_id ?? getUsuarioId(),
            ':acao' => $acao,
            ':descricao' => $descricao,
            ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    } catch (Exception $e) {
        // Erro silencioso - apenas loga no error_log do PHP
        error_log("Erro ao registrar log: " . $e->getMessage());
    }
}

// ============================================================
// 13. FUNÇÕES DE RESPONSIVIDADE
// ============================================================

/**
 * Verifica se o dispositivo é mobile
 * 
 * @return bool True se é mobile
 */
function isMobile()
{
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $mobileAgents = ['Mobile', 'Android', 'iPhone', 'iPad', 'iPod', 'BlackBerry', 'Windows Phone'];
    foreach ($mobileAgents as $agent) {
        if (stripos($userAgent, $agent) !== false) {
            return true;
        }
    }
    return false;
}

/**
 * Verifica se o dispositivo é tablet
 * 
 * @return bool True se é tablet
 */
function isTablet()
{
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return stripos($userAgent, 'iPad') !== false || 
           (stripos($userAgent, 'Android') !== false && stripos($userAgent, 'Mobile') === false);
}

// ============================================================
// 14. FUNÇÃO DE REDIRECIONAMENTO COM MENSAGEM
// ============================================================

/**
 * Redireciona com uma mensagem flash
 * 
 * @param string $url URL para redirecionar
 * @param string $mensagem Mensagem a ser exibida
 * @param string $tipo Tipo da mensagem (success, error, info)
 */
function redirectComMensagem($url, $mensagem, $tipo = 'success')
{
    $setter = 'set' . ucfirst($tipo);
    if (function_exists($setter)) {
        $setter($mensagem);
    }
    redirect($url);
}