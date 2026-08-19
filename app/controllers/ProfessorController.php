<?php
/**
 * ProfessorController.php
 * Controlador atualizado sem a restrição para o coeficiente C[cite: 10]
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
    
public function gerenciarAlunos()
{
    require_once __DIR__ . '/../models/Aluno.php';
    $alunoModel = new Aluno();

    // Chama o método listarTodos que criamos acima
    $alunos = $alunoModel->listarTodos();

    $caminhoView = __DIR__ . '/../views/professor/gerenciar_alunos.php';
    if (file_exists($caminhoView)) {
        require_once $caminhoView;
    } else {
        echo "View gerenciar_alunos.php não encontrada.";
    }
}
public function cadastrarAluno()
{
    echo "<div style='background: #111; color: #00ff66; padding: 20px; font-family: monospace; line-height: 1.5; z-index: 99999; position: relative;'>";
    echo "<h2>🐛 [DEBUG CONTROLLER] Cadastrar Aluno</h2>";

    // 1. Inspecionar o que veio do formulário
    echo "<strong>1. Conteúdo de \$_POST:</strong><br>";
    echo "<pre style='color:#ffcc00;'>" . print_r($_POST, true) . "</pre>";

    try {
        // 2. Verificar dados tratados
        $nome      = $_POST['nome'] ?? '';
        $email     = $_POST['email'] ?? '';
        $senha     = $_POST['senha'] ?? '';
        $idade     = $_POST['idade'] ?? 0;
        $nivel_tea = $_POST['nivel_tea'] ?? '';
        $escola    = $_POST['escola'] ?? '';
        $turma     = $_POST['turma'] ?? '';

        echo "<strong>2. Variáveis capturadas:</strong> Nome: $nome | E-mail: $email | Idade: $idade<br>";

        // 3. Testar Model Usuario
        echo "<strong>3. Tentando carregar/instanciar Usuario...</strong><br>";
        require_once __DIR__ . '/../models/Usuario.php';
        $usuarioModel = new Usuario();
        echo "<span style='color:lime;'>✔ Model Usuario instanciado.</span><br>";

        // 4. Testar Model Aluno
        echo "<strong>4. Tentando carregar/instanciar Aluno...</strong><br>";
        require_once __DIR__ . '/../models/Aluno.php';
        $alunoModel = new Aluno();
        echo "<span style='color:lime;'>✔ Model Aluno instanciado.</span><br>";

        // 5. Execução passo a passo simulada com captura de exceção detalhada
        echo "<strong>5. Executando inserção...</strong><br>";
        
        // Crie o usuário primeiro (ajuste o método conforme seu model Usuario)
        $usuario_id = $usuarioModel->criar($nome, $email, $senha, 'aluno');
        echo "• ID do Usuário criado: " . var_export($usuario_id, true) . "<br>";

        if ($usuario_id) {
            $resultadoAluno = $alunoModel->criar($usuario_id, $idade, $nivel_tea, $escola, $turma);
            echo "• Resultado da inserção do Aluno: " . var_export($resultadoAluno, true) . "<br>";
        } else {
            echo "<span style='color:red;'>❌ O método de criação de usuário retornou falso/vazio.</span><br>";
        }

    } catch (Exception $e) {
        echo "<br><span style='color:red; font-size:1.2rem;'>❌ EXCEÇÃO CAPTURADA: " . htmlspecialchars($e->getMessage()) . "</span><br>";
        echo "<pre style='color:#ff8888;'>" . $e->getTraceAsString() . "</pre>";
    }

    echo "<br><a href='index.php?view=gerenciar_alunos' style='color:#fff; background:#27ae60; padding:10px 15px; text-decoration:none; display:inline-block; margin-top:15px; border-radius:4px;'>🔙 Voltar</a>";
    echo "</div>";
    exit; // Interrompe para visualizarmos os dados antes de redirecionar
}

public function exibirFormularioEdicao($id)
{
    require_once __DIR__ . '/../models/Aluno.php';
    $alunoModel = new Aluno();
    
    // Busca os dados do aluno específico pelo ID
    $aluno = $alunoModel->buscarPorId($id);

    // Carrega a view de edição (você precisará criar esse arquivo)
    require_once __DIR__ . '/../views/professor/editar_aluno.php';
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

        // Validação sem restrição no coeficiente C[cite: 10]
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

    public function excluirEquacao()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?view=gerenciar_equacoes');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);

        if ($this->equacao && method_exists($this->equacao, 'excluir')) {
            $resultado = $this->equacao->excluir($id);
            if ($resultado) {
                $_SESSION['admin_success'] = 'Equação excluída com sucesso!';
            } else {
                $_SESSION['admin_error'] = 'Esta equação já possui históricos associados e não pode ser removida.';
            }
        }

        header('Location: index.php?view=gerenciar_equacoes');
        exit;
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

        // Redireciona de volta para a tela de gerenciar alunos (em ambiente de teste)
        header('Location: index.php?view=gerenciar_alunos');
        exit;
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
}