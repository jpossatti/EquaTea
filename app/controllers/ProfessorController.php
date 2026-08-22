<?php
/**
 * ProfessorController.php
 * Controlador atualizado sem a restrição para o coeficiente C
 */

if (!defined('BASE_URL')) {
    define('BASE_URL', 'index.php?view=');
}

if (!defined('VIEWS_PATH')) {
    define('VIEWS_PATH', dirname(__DIR__) . '/views');
}

$base_dir = dirname(__DIR__);
$models = ['Aluno.php', 'Equacao.php', 'RegistroErro.php', 'Usuario.php'];

foreach ($models as $model_file) {
    $path = $base_dir . '/models/' . $model_file;
    if (file_exists($path)) {
        require_once $path;
    }
}

class ProfessorController
{
    private $aluno;
    private $equacao;
    private $registroErro;
    private $usuario;

    public function __construct()
    {
        $this->aluno        = class_exists('Aluno') ? new Aluno() : null;
        $this->equacao      = class_exists('Equacao') ? new Equacao() : null;
        $this->registroErro = class_exists('RegistroErro') ? new RegistroErro() : null;
        $this->usuario      = class_exists('Usuario') ? new Usuario() : null;
    }

    public function dashboard()
    {
        $dados_alunos = ($this->aluno && method_exists($this->aluno, 'getAll')) ? $this->aluno->getAll() : [];
        $dados_equacoes = ($this->equacao && method_exists($this->equacao, 'getAll')) ? $this->equacao->getAll() : [];
        $erros_comuns = ($this->registroErro && method_exists($this->registroErro, 'getEstatisticas')) ? $this->registroErro->getEstatisticas() : [];

        $dados = [
            'total_alunos'    => count($dados_alunos),
            'total_equacoes'  => count($dados_equacoes),
            'dados_alunos'    => $dados_alunos,
            'dados_equacoes'  => $dados_equacoes,
            'erros_comuns'    => $erros_comuns
        ];

        extract($dados);

        $view_path = VIEWS_PATH . '/professor/dashboard.php';
        if (file_exists($view_path)) {
            include_once $view_path;
        } else {
            echo "<h2>Erro: View do Dashboard Professor não encontrada em: {$view_path}</h2>";
        }
    }
    
    public function gerenciarAlunos() {
        // Busca os alunos do banco de dados
        $alunos = $this->aluno->getAll(); 
        
        // Inclui a view passando a variável $alunos
        require_once VIEWS_PATH . '/professor/gerenciar_alunos.php';
    }

    public function cadastrarAluno()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?view=gerenciar_alunos');
            exit;
        }

        $nome      = $_POST['nome'] ?? '';
        $email     = $_POST['email'] ?? '';
        $senha     = $_POST['senha'] ?? '';
        $idade     = $_POST['idade'] ?? 0;
        $nivel_tea = $_POST['nivel_tea'] ?? '';
        $escola    = $_POST['escola'] ?? '';
        $turma     = $_POST['turma'] ?? '';

        if (!empty($nome) && !empty($email) && !empty($senha)) {
            require_once __DIR__ . '/../models/Usuario.php';
            require_once __DIR__ . '/../models/Aluno.php';
            
            $usuarioModel = new Usuario();
            $alunoModel = new Aluno();

            $usuario_id = $usuarioModel->criar($nome, $email, $senha, 'aluno');

            if ($usuario_id) {
                $alunoModel->criar($usuario_id, $idade, $nivel_tea, $escola, $turma);
                $_SESSION['admin_success'] = 'Aluno cadastrado com sucesso!';
            } else {
                $_SESSION['admin_error'] = 'Erro ao criar usuário para o aluno.';
            }
        }

        header('Location: index.php?view=gerenciar_alunos');
        exit;
    }

    public function exibirFormularioEdicao($id)
    {
        require_once __DIR__ . '/../models/Aluno.php';
        $alunoModel = new Aluno();
        
        // Busca os dados do aluno específico pelo ID
        $aluno = $alunoModel->buscarPorId($id);

        // Carrega a view de edição
        require_once __DIR__ . '/../views/professor/editar_aluno.php';
    }

    public function deletarEquacao($id) {
        require_once __DIR__ . '/../models/Equacao.php';
        $model = new Equacao();
        $model->deletar($id);
        header('Location: index.php?view=gerenciar_equacoes');
        exit;
    }

    public function exibirFormularioEdicaoEquacao($id) {
        require_once __DIR__ . '/../models/Equacao.php';
        $model = new Equacao();
        $equacao = $model->buscarPorId($id);
        // Carrega a view 'editar_equacao.php'
        require_once __DIR__ . '/../views/professor/editar_equacao.php';
    }

    public function resetarSenha()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?view=gerenciar_alunos');
            exit;
        }

        $aluno_id   = (int)($_POST['aluno_id'] ?? 0);
        $nova_senha = trim($_POST['nova_senha'] ?? '');

        if (empty($nova_senha) || strlen($nova_senha) < 4) {
            $_SESSION['admin_error'] = 'A senha deve ter pelo menos 4 caracteres.';
            header('Location: index.php?view=gerenciar_alunos');
            exit;
        }

        if ($this->aluno && $this->usuario) {
            $dados = $this->aluno->getDadosCompletos($aluno_id);
            if ($dados) {
                $this->usuario->atualizarSenha($dados['usuario_id'], $nova_senha);
                $_SESSION['admin_success'] = "Senha resetada com sucesso! Nova senha: {$nova_senha}";
            } else {
                $_SESSION['admin_error'] = 'Aluno não encontrado.';
            }
        }

        header('Location: index.php?view=gerenciar_alunos');
        exit;
    }

    public function gerenciarEquacoes()
    {
        $dificuldade = $_GET['dificuldade'] ?? null;
        $dados_equacoes = ($this->equacao && method_exists($this->equacao, 'getAll')) ? $this->equacao->getAll($dificuldade) : [];

        $view_path = VIEWS_PATH . '/professor/gerenciar_equacoes.php';
        if (file_exists($view_path)) {
            include_once $view_path;
        } else {
            echo "<h2>Erro: View 'gerenciar_equacoes.php' não encontrada.</h2>";
        }
    }

    public function cadastrarEquacao()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?view=gerenciar_equacoes');
            exit;
        }

        $a           = (int)($_POST['a'] ?? 0);
        $b           = (int)($_POST['b'] ?? 0);
        $c           = (int)($_POST['c'] ?? 0);
        $dificuldade = $_POST['dificuldade'] ?? 'facil';

        // Validação sem restrição no coeficiente C
        if ($a == 0 || $a < -20 || $a > 20 || $b < -20 || $b > 20) {
            $_SESSION['admin_error'] = 'Coeficientes "a" e "b" inválidos. "a" não pode ser zero e ambos devem estar entre -20 e 20.';
            header('Location: index.php?view=gerenciar_equacoes');
            exit;
        }

        if ($this->equacao && method_exists($this->equacao, 'criar')) {
            $resultado = $this->equacao->criar($a, $b, $c, $dificuldade);
            if ($resultado) {
                $_SESSION['admin_success'] = 'Equação cadastrada com sucesso!';
            } else {
                $_SESSION['admin_error'] = 'A solução da equação precisa ser um número inteiro.';
            }
        }

        header('Location: index.php?view=gerenciar_equacoes');
        exit;
    }

    public function atualizarEquacao() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $a = $_POST['coef_a'] ?? 0;
            $b = $_POST['coef_b'] ?? 0;
            $c = $_POST['coef_c'] ?? 0;
            $dificuldade = $_POST['dificuldade'] ?? 'Fácil';

            if ($id && $a != 0) {
                $solucao = ($c - $b) / $a;

                require_once __DIR__ . '/../models/Equacao.php';
                $model = new Equacao();
                
                // Executa a atualização no banco de dados
                $model->atualizar($id, $a, $b, $c, $solucao, $dificuldade);
            }

            header('Location: index.php?view=gerenciar_equacoes');
            exit;
        }
    }

    public function atualizar()
    {
        // Verifica se os dados vieram via POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $nome = $_POST['nome'] ?? '';
            $email = $_POST['email'] ?? '';
            $nivelTea = $_POST['nivel_tea'] ?? '';
            $turma = $_POST['turma'] ?? '';

            require_once __DIR__ . '/../models/Aluno.php';
            $alunoModel = new Aluno();
            
            // Executa a atualização no banco
            $alunoModel->atualizar($id, $nome, $email, $nivelTea, $turma);

            // Redireciona de volta para a tela de gerenciar alunos
            header('Location: index.php?view=gerenciar_alunos');
            exit;
        }
    }

    public function listarEquacoes() {
        // Define as constantes se não estiverem definidas
        if (!defined('VIEWS_PATH')) {
            define('VIEWS_PATH', dirname(__DIR__) . '/views');
        }
        
        // Carrega o modelo
        require_once __DIR__ . '/../models/Equacao.php';
        $equacaoModel = new Equacao();
        $equacoes = $equacaoModel->buscarTodas();
        
        // Garante que a variável existe
        $dados_equacoes = $equacoes ?: [];
        
        // Define a view atual para o menu
        $view = 'gerenciar_equacoes';
        $GLOBALS['current_view'] = 'gerenciar_equacoes';
        
        // Caminho da view
        $view_path = VIEWS_PATH . '/professor/gerenciar_equacoes.php';
        
        if (file_exists($view_path)) {
            include_once $view_path;
        } else {
            // Tenta carregar com caminho alternativo
            $alt_path = __DIR__ . '/../views/professor/gerenciar_equacoes.php';
            if (file_exists($alt_path)) {
                include_once $alt_path;
            } else {
                echo "<h2>Erro: View não encontrada.</h2>";
            }
        }
    }

    public function deletarAluno()
    {
        $id = $_GET['id'] ?? null;
        if ($id) {
            require_once __DIR__ . '/../models/Aluno.php';
            $alunoModel = new Aluno();
            $alunoModel->deletar($id);
        }
        
        // Redireciona de volta para a lista de alunos
        header('Location: index.php?view=gerenciar_alunos');
        exit;
    }

    /**
     * Relatório de erros do professor
     */
    public function relatorio()
    {
        // Verifica se o professor está logado
        if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_perfil'] !== 'professor') {
            header('Location: index.php?view=login');
            exit;
        }

        // Filtros
        $filtro_aluno = $_GET['aluno'] ?? '';
        $filtro_passo = $_GET['passo'] ?? '';

        // Busca alunos para o filtro
        $dados_alunos = ($this->aluno && method_exists($this->aluno, 'getAll')) ? $this->aluno->getAll() : [];

        // Busca os erros do banco
        if ($this->registroErro && method_exists($this->registroErro, 'getAll')) {
            $dados_relatorio = $this->registroErro->getAll($filtro_aluno, $filtro_passo);
        } else {
            $dados_relatorio = [];
        }

        // Define a view atual para o menu
        $view = 'relatorio';
        $GLOBALS['current_view'] = 'relatorio';

        // Caminho da view
        $view_path = VIEWS_PATH . '/professor/relatorio.php';
        
        if (file_exists($view_path)) {
            include_once $view_path;
        } else {
            // Tenta carregar com caminho alternativo
            $alt_path = __DIR__ . '/../views/professor/relatorio.php';
            if (file_exists($alt_path)) {
                include_once $alt_path;
            } else {
                echo "<h2>Erro: View do Relatório não encontrada.</h2>";
            }
        }
    }
}