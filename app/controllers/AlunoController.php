<?php
/**
 * app/controllers/AlunoController.php
 * Controlador do aluno - Completo com todos os métodos
 */

if (!defined('BASE_URL')) {
    define('BASE_URL', 'index.php?view=');
}

if (!defined('VIEWS_PATH')) {
    define('VIEWS_PATH', dirname(__DIR__) . '/views');
}

// Carrega os models
$base_dir = dirname(__DIR__);
$models = ['Aluno.php', 'Equacao.php', 'ProgressoAluno.php', 'Usuario.php'];

foreach ($models as $model_file) {
    $path = $base_dir . '/models/' . $model_file;
    if (file_exists($path)) {
        require_once $path;
    }
}

class AlunoController
{
    private $aluno;
    private $equacao;
    private $progresso;
    private $usuario;

    public function __construct()
    {
        $this->aluno    = class_exists('Aluno') ? new Aluno() : null;
        $this->equacao  = class_exists('Equacao') ? new Equacao() : null;
        $this->progresso = class_exists('ProgressoAluno') ? new ProgressoAluno() : null;
        $this->usuario  = class_exists('Usuario') ? new Usuario() : null;
    }

   /**
 * Dashboard do aluno com controle de sessão
 */
public function dashboard()
{
    // ===== CONTROLE DE SESSÃO =====
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Verifica se o usuário está logado
    if (!isset($_SESSION['usuario_id'])) {
        $_SESSION['login_error'] = 'Você precisa estar logado para acessar o dashboard.';
        header('Location: index.php?view=login');
        exit;
    }

    // Verifica se o usuário é aluno
    if (($_SESSION['tipo_perfil'] ?? '') !== 'aluno') {
        $_SESSION['login_error'] = 'Acesso restrito a alunos.';
        header('Location: index.php?view=login');
        exit;
    }

    // Verifica se a sessão expirou (30 minutos)
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 1800)) {
        session_destroy();
        $_SESSION['login_error'] = 'Sua sessão expirou. Faça login novamente.';
        header('Location: index.php?view=login');
        exit;
    }

    // Atualiza o tempo da sessão
    $_SESSION['login_time'] = time();
    // ===== FIM CONTROLE DE SESSÃO =====

    // Busca dados do aluno
    $aluno_id = $_SESSION['aluno_id'] ?? null;
    $usuario_id = $_SESSION['usuario_id'] ?? null;
    $usuario_nome = $_SESSION['usuario_nome'] ?? 'Aluno';
    $usuario_email = $_SESSION['email'] ?? 'aluno@equatea.com';

    // ===== BUSCA CORRIGIDA DO ALUNO =====
    $dados_aluno = null;

    if ($this->aluno) {
        try {
            // 1. Tenta buscar pelo ID do aluno (se existir)
            if ($aluno_id) {
                $dados_aluno = $this->aluno->getById($aluno_id);
            }

            // 2. Se não encontrou, tenta buscar pelo ID do usuário
            if (!$dados_aluno || !is_array($dados_aluno)) {
                if ($usuario_id) {
                    $dados_aluno = $this->aluno->getByUsuarioId($usuario_id);
                    if ($dados_aluno && is_array($dados_aluno)) {
                        // Atualiza o aluno_id na sessão se encontrou
                        $_SESSION['aluno_id'] = $dados_aluno['id'] ?? null;
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Erro ao buscar aluno: " . $e->getMessage());
            $dados_aluno = null;
        }
    }

    // 3. Se não encontrou ou deu erro, cria array com dados da sessão
    if (!$dados_aluno || !is_array($dados_aluno)) {
        $dados_aluno = [
            'id' => $aluno_id,
            'aluno_id' => $aluno_id,
            'nome' => $usuario_nome,
            'email' => $usuario_email,
            'idade' => null,
            'nivel_tea' => null,
            'escola' => null,
            'turma' => null
        ];
    }

    // 4. Garante que o nome está presente
    if (!isset($dados_aluno['nome']) || empty($dados_aluno['nome'])) {
        $dados_aluno['nome'] = $usuario_nome;
    }

    // 5. Garante que o email está presente
    if (!isset($dados_aluno['email']) || empty($dados_aluno['email'])) {
        $dados_aluno['email'] = $usuario_email;
    }
    // ===== FIM DA BUSCA CORRIGIDA =====

    // Busca progresso do aluno
    $dados_progresso = [
        'total_resolvidas' => 0,
        'taxa_acerto' => '0%',
        'nivel_atual' => 'Nível 1 - Básico'
    ];

    if ($this->progresso && $aluno_id) {
        try {
            $progresso = $this->progresso->getEstatisticas($aluno_id);
            if ($progresso && is_array($progresso)) {
                $dados_progresso = array_merge($dados_progresso, $progresso);
            }
        } catch (Exception $e) {
            // Mantém os dados padrão
            error_log("Erro ao buscar progresso: " . $e->getMessage());
        }
    }

    // Busca equações disponíveis
    $equacoes = [];
    if ($this->equacao) {
        try {
            $equacoes = $this->equacao->buscarTodas();
            if (!is_array($equacoes)) {
                $equacoes = [];
            }
        } catch (Exception $e) {
            $equacoes = [];
            error_log("Erro ao buscar equações: " . $e->getMessage());
        }
    }

    // Dados para a view
    $aluno = $dados_aluno;
    $dados_progresso = $dados_progresso;

    // Define a view atual para o menu
    $view = 'aluno/dashboard';
    $GLOBALS['current_view'] = 'aluno/dashboard';

    // Carrega a view
    $view_path = VIEWS_PATH . '/aluno/dashboard.php';
    if (file_exists($view_path)) {
        include_once $view_path;
    } else {
        $alt_path = __DIR__ . '/../views/aluno/dashboard.php';
        if (file_exists($alt_path)) {
            include_once $alt_path;
        } else {
            echo "<h2>Erro: View do Dashboard Aluno não encontrada.</h2>";
            echo "<p>Caminhos procurados:</p>";
            echo "<ul>";
            echo "<li>{$view_path}</li>";
            echo "<li>{$alt_path}</li>";
            echo "</ul>";
        }
    }
}

    /**
     * Página de exercício com controle de sessão
     */
    public function exercicio()
    {
        // ===== CONTROLE DE SESSÃO =====
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id']) || ($_SESSION['tipo_perfil'] ?? '') !== 'aluno') {
            $_SESSION['login_error'] = 'Acesso restrito a alunos.';
            header('Location: index.php?view=login');
            exit;
        }

        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 1800)) {
            session_destroy();
            $_SESSION['login_error'] = 'Sua sessão expirou. Faça login novamente.';
            header('Location: index.php?view=login');
            exit;
        }
        $_SESSION['login_time'] = time();
        // ===== FIM CONTROLE DE SESSÃO =====

        $equacao_id = $_GET['id'] ?? null;
        $passo = $_GET['passo'] ?? 1;
        $erro = $_GET['erro'] ?? 0;

        if (!$equacao_id) {
            // Busca uma equação aleatória
            if ($this->equacao) {
                try {
                    $equacao = $this->equacao->getRandom($_SESSION['aluno_id'] ?? null);
                    if ($equacao) {
                        $equacao_id = $equacao['id'];
                    }
                } catch (Exception $e) {
                    $equacao = null;
                }
            }
        }

        // Busca a equação
        if ($this->equacao && $equacao_id) {
            try {
                $equacao = $this->equacao->getById($equacao_id);
            } catch (Exception $e) {
                $equacao = null;
            }
        } else {
            $equacao = null;
        }

        if (!$equacao) {
            $_SESSION['erro_resposta'] = 'Equação não encontrada.';
            header('Location: index.php?view=aluno/dashboard');
            exit;
        }

        // Verifica se o aluno já concluiu esta equação
        if ($this->progresso && isset($_SESSION['aluno_id'])) {
            try {
                $concluida = $this->progresso->isConcluida($_SESSION['aluno_id'], $equacao_id);
                if ($concluida) {
                    header('Location: index.php?view=parabens&id=' . $equacao_id);
                    exit;
                }
            } catch (Exception $e) {
                // Continua se não conseguir verificar
            }
        }

        // Gera o enunciado e a solução
        $enunciado = $this->gerarEnunciado($equacao);
        $solucao = $this->gerarSolucao($equacao);
        $resposta_esperada = $this->getRespostaEsperada($equacao, $passo);

        // Mensagem de erro se houver
        $erro_resposta = $_SESSION['erro_resposta'] ?? null;
        unset($_SESSION['erro_resposta']);

        // Dados para a view
        $view = 'exercicio';
        $GLOBALS['current_view'] = 'exercicio';

        // Carrega a view
        $view_path = VIEWS_PATH . '/aluno/exercicio.php';
        if (file_exists($view_path)) {
            include_once $view_path;
        } else {
            echo "<h2>Erro: View do Exercício não encontrada.</h2>";
        }
    }

    /**
     * Verifica a resposta do aluno
     */
    public function verificarResposta()
    {
        // ===== CONTROLE DE SESSÃO =====
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id']) || ($_SESSION['tipo_perfil'] ?? '') !== 'aluno') {
            $_SESSION['login_error'] = 'Acesso restrito a alunos.';
            header('Location: index.php?view=login');
            exit;
        }
        // ===== FIM CONTROLE DE SESSÃO =====

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?view=aluno/dashboard');
            exit;
        }

        $equacao_id = $_POST['equacao_id'] ?? null;
        $passo_atual = $_POST['passo_atual'] ?? 1;
        $resposta = $_POST['resposta'] ?? '';

        if (!$equacao_id) {
            $_SESSION['erro_resposta'] = 'ID da equação não fornecido.';
            header('Location: index.php?view=aluno/dashboard');
            exit;
        }

        // Busca a equação
        if ($this->equacao) {
            try {
                $equacao = $this->equacao->getById($equacao_id);
            } catch (Exception $e) {
                $equacao = null;
            }
        } else {
            $equacao = null;
        }

        if (!$equacao) {
            $_SESSION['erro_resposta'] = 'Equação não encontrada.';
            header('Location: index.php?view=aluno/dashboard');
            exit;
        }

        // Valida a resposta
        $correto = $this->validarPasso($equacao, (int)$passo_atual, $resposta);

        // Registra o progresso
        if ($this->progresso && isset($_SESSION['aluno_id'])) {
            try {
                $this->progresso->registrarTentativa(
                    $_SESSION['aluno_id'],
                    $equacao_id,
                    (int)$passo_atual,
                    $resposta,
                    $correto
                );

                if ($correto && (int)$passo_atual == 4) {
                    // Último passo correto - marca como concluída
                    $this->progresso->marcarConcluida($_SESSION['aluno_id'], $equacao_id);
                }
            } catch (Exception $e) {
                // Registra erro mas continua
                error_log("Erro ao registrar progresso: " . $e->getMessage());
            }
        }

        // Redireciona baseado no resultado
        if ($correto) {
            $proximoPasso = (int)$passo_atual + 1;
            if ($proximoPasso <= 4) {
                header('Location: index.php?view=exercicio&id=' . $equacao_id . '&passo=' . $proximoPasso);
            } else {
                header('Location: index.php?view=parabens&id=' . $equacao_id);
            }
        } else {
            $_SESSION['erro_resposta'] = 'Resposta incorreta. Tente novamente!';
            header('Location: index.php?view=exercicio&id=' . $equacao_id . '&passo=' . $passo_atual . '&erro=1');
        }
        exit;
    }

    /**
     * Página de parabéns (equação concluída)
     */
    public function parabens()
    {
        // ===== CONTROLE DE SESSÃO =====
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id']) || ($_SESSION['tipo_perfil'] ?? '') !== 'aluno') {
            $_SESSION['login_error'] = 'Acesso restrito a alunos.';
            header('Location: index.php?view=login');
            exit;
        }

        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 1800)) {
            session_destroy();
            $_SESSION['login_error'] = 'Sua sessão expirou. Faça login novamente.';
            header('Location: index.php?view=login');
            exit;
        }
        $_SESSION['login_time'] = time();
        // ===== FIM CONTROLE DE SESSÃO =====

        $equacao_id = $_GET['id'] ?? null;
        $equacao = null;

        if ($equacao_id && $this->equacao) {
            try {
                $equacao = $this->equacao->getById($equacao_id);
            } catch (Exception $e) {
                $equacao = null;
            }
        }

        // Busca o total de equações resolvidas
        $total_resolvidas = 0;
        if ($this->progresso && isset($_SESSION['aluno_id'])) {
            try {
                $stats = $this->progresso->getEstatisticas($_SESSION['aluno_id']);
                if ($stats && is_array($stats)) {
                    $total_resolvidas = $stats['total_resolvidas'] ?? 0;
                }
            } catch (Exception $e) {
                // Mantém o valor padrão
            }
        }

        // Define a view atual para o menu
        $view = 'parabens';
        $GLOBALS['current_view'] = 'parabens';

        // Carrega a view
        $view_path = VIEWS_PATH . '/aluno/parabens.php';
        if (file_exists($view_path)) {
            include_once $view_path;
        } else {
            // Se não encontrar a view, exibe uma mensagem simples
            echo '<!DOCTYPE html>';
            echo '<html lang="pt-BR">';
            echo '<head><meta charset="UTF-8"><title>Parabéns!</title>';
            echo '<style>
                    body { font-family: Arial; text-align: center; padding: 50px; background: linear-gradient(135deg, #2b3a4a, #1a252f); color: white; min-height: 100vh; display: flex; justify-content: center; align-items: center; flex-direction: column; }
                    .card { background: #fff; padding: 40px; border-radius: 20px; color: #333; max-width: 500px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); }
                    h1 { color: #2ecc71; font-size: 3rem; margin: 0; }
                    .emoji { font-size: 4rem; }
                    .btn { display: inline-block; padding: 12px 30px; background: #3498db; color: white; text-decoration: none; border-radius: 6px; margin-top: 20px; }
                    .btn:hover { background: #2980b9; }
                  </style>';
            echo '</head>';
            echo '<body>';
            echo '<div class="card">';
            echo '<div class="emoji">🎉</div>';
            echo '<h1>Parabéns!</h1>';
            echo '<p>Você concluiu esta equação com sucesso!</p>';
            echo '<p>Total de equações resolvidas: <strong>' . $total_resolvidas . '</strong></p>';
            echo '<a href="index.php?view=aluno/dashboard" class="btn">📊 Voltar ao Dashboard</a>';
            echo '</div>';
            echo '</body></html>';
        }
    }

    /**
     * Gera o enunciado da equação
     */
    private function gerarEnunciado($equacao)
    {
        $a = (int)($equacao['a'] ?? 1);
        $b = (int)($equacao['b'] ?? 0);
        $c = (int)($equacao['c'] ?? 0);

        $termoA = ($a === 1) ? 'x' : (($a === -1) ? '-x' : "{$a}x");
        $sinalB = ($b >= 0) ? '+ ' . $b : '- ' . abs($b);

        return "{$termoA} {$sinalB} = {$c}";
    }

    /**
     * Gera a solução completa da equação
     */
    private function gerarSolucao($equacao)
    {
        $a = (int)($equacao['a'] ?? 1);
        $b = (int)($equacao['b'] ?? 0);
        $c = (int)($equacao['c'] ?? 0);

        if ($a == 0) {
            return 'Indefinido (a = 0)';
        }

        $x = ($c - $b) / $a;
        return "x = " . $x;
    }

    /**
     * Obtém a resposta esperada para um passo específico
     */
    private function getRespostaEsperada($equacao, $passo)
    {
        $a = (int)($equacao['a'] ?? 1);
        $b = (int)($equacao['b'] ?? 0);
        $c = (int)($equacao['c'] ?? 0);

        switch ((int)$passo) {
            case 1:
                $termoA = ($a === 1) ? 'x' : (($a === -1) ? '-x' : "{$a}x");
                return $termoA;
            case 2:
                $termoA = ($a === 1) ? 'x' : (($a === -1) ? '-x' : "{$a}x");
                $resultado = $c - $b;
                return "{$termoA} = {$resultado}";
            case 3:
                $resultado = $c - $b;
                return (string)$resultado;
            case 4:
                if ($a == 0) return '0';
                $valorX = ($c - $b) / $a;
                return (string)$valorX;
            default:
                return '';
        }
    }

    /**
     * Valida a resposta do aluno para um passo específico
     */
    private function validarPasso($equacao, $passo, $resposta)
    {
        if ($resposta === '') {
            return false;
        }

        $resp = strtolower(trim($resposta));
        $resp = preg_replace('/[\s\x{200B}-\x{200D}\x{FEFF}]/u', '', $resp);
        $resp = str_replace(['–', '—', '−'], '-', $resp);

        $a = (int)($equacao['a'] ?? 1);
        $b = (int)($equacao['b'] ?? 0);
        $c = (int)($equacao['c'] ?? 0);

        $termoX = ($a === 1) ? '(1?x)' : (($a === -1) ? '(-1?x)' : "({$a}x)");

        switch ((int)$passo) {
            case 1:
                // Passo 1: Identificar o termo com x
                // Ex: "3x" ou "x" ou "-x"
                return preg_match('/^' . $termoX . '$/i', $resp) === 1;

            case 2:
                // Passo 2: Isolar o termo com x
                // Ex: "3x = 7" ou "x = 7"
                $bAbs = abs($b);
                $opOposta = ($b >= 0) ? '-' : '\+';
                return preg_match('/^(' . $termoX . '=)?' . $c . $opOposta . $bAbs . '$/i', $resp) === 1;

            case 3:
                // Passo 3: Calcular o resultado do lado direito
                // Ex: "7" ou "x = 7" ou "3x = 7"
                $resultado = $c - $b;
                return preg_match('/^(' . $termoX . '=)?' . $resultado . '$/i', $resp) === 1;

            case 4:
                // Passo 4: Encontrar o valor de x
                // Ex: "3" ou "x = 3"
                if ($a === 0) return false;
                $valorX = ($c - $b) / $a;
                // Verifica se é inteiro para comparação exata
                if (is_int($valorX) || floor($valorX) == $valorX) {
                    return preg_match('/^(x=)?' . $valorX . '$/i', $resp) === 1;
                } else {
                    // Para números decimais, permite aproximação
                    $valorXStr = number_format($valorX, 2);
                    return preg_match('/^(x=)?' . $valorXStr . '$/i', $resp) === 1 || 
                           preg_match('/^(x=)?' . number_format($valorX, 1) . '$/i', $resp) === 1;
                }

            default:
                return false;
        }
    }

    /**
     * Redireciona para o dashboard se não estiver autenticado
     */
    private function redirectToLogin()
    {
        header('Location: index.php?view=login');
        exit;
    }
}