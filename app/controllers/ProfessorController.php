<?php
/**
 * ProfessorController.php
 * Controlador para funcionalidades do professor.
 */
class ProfessorController
{
    private $aluno;
    private $equacao;
    private $registroErro;
    private $usuario;
    
    public function __construct()
    {
        $this->aluno = new Aluno();
        $this->equacao = new Equacao();
        $this->registroErro = new RegistroErro();
        $this->usuario = new Usuario();
    }
    
    /**
     * Dashboard do professor
     */
    public function dashboard()
    {
        $dados = [
            'total_alunos' => count($this->aluno->getAll()),
            'total_equacoes' => count($this->equacao->getAll()),
            'alunos_recentes' => $this->aluno->getAll(),
            'erros_comuns' => $this->registroErro->getEstatisticas()
        ];
        
        include_once VIEWS_PATH . '/professor/dashboard.php';
    }
    
    /**
     * Gerenciar alunos
     */
    public function gerenciarAlunos()
    {
        $alunos = $this->aluno->getAll(false);
        include_once VIEWS_PATH . '/professor/gerenciar_alunos.php';
    }
    
    /**
     * Cadastrar aluno
     */
    public function cadastrarAluno()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'professor/alunos');
            exit;
        }
        
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = trim($_POST['senha'] ?? '');
        $idade = (int)($_POST['idade'] ?? 0);
        $nivel_tea = $_POST['nivel_tea'] ?? 'suporte1';
        $escola = trim($_POST['escola'] ?? '');
        $turma = trim($_POST['turma'] ?? '');
        
        // Validações
        if (empty($nome) || empty($email) || empty($senha) || $idade < 14 || $idade > 21) {
            $_SESSION['admin_error'] = 'Preencha todos os campos corretamente.';
            header('Location: ' . BASE_URL . 'professor/alunos');
            exit;
        }
        
        // Verifica se email já existe
        if ($this->usuario->getByEmail($email)) {
            $_SESSION['admin_error'] = 'Este e-mail já está cadastrado.';
            header('Location: ' . BASE_URL . 'professor/alunos');
            exit;
        }
        
        // Cria usuário e aluno
        $usuario_id = $this->usuario->criar($nome, $email, $senha, 'aluno');
        if ($usuario_id) {
            $this->aluno->criar($usuario_id, $idade, $nivel_tea, $escola, $turma);
            $_SESSION['admin_success'] = 'Aluno cadastrado com sucesso!';
        } else {
            $_SESSION['admin_error'] = 'Erro ao cadastrar aluno.';
        }
        
        header('Location: ' . BASE_URL . 'professor/alunos');
        exit;
    }
    
    /**
     * Resetar senha do aluno
     */
    public function resetarSenha()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'professor/alunos');
            exit;
        }
        
        $aluno_id = (int)($_POST['aluno_id'] ?? 0);
        $nova_senha = trim($_POST['nova_senha'] ?? '');
        
        if (empty($nova_senha) || strlen($nova_senha) < 4) {
            $_SESSION['admin_error'] = 'A senha deve ter pelo menos 4 caracteres.';
            header('Location: ' . BASE_URL . 'professor/alunos');
            exit;
        }
        
        $dados = $this->aluno->getDadosCompletos($aluno_id);
        if ($dados) {
            $this->usuario->atualizarSenha($dados['usuario_id'], $nova_senha);
            $_SESSION['admin_success'] = "Senha resetada com sucesso! Nova senha: {$nova_senha}";
        } else {
            $_SESSION['admin_error'] = 'Aluno não encontrado.';
        }
        
        header('Location: ' . BASE_URL . 'professor/alunos');
        exit;
    }
    
    /**
     * Gerenciar equações
     */
    public function gerenciarEquacoes()
    {
        $dificuldade = $_GET['dificuldade'] ?? null;
        $equacoes = $this->equacao->getAll($dificuldade);
        include_once VIEWS_PATH . '/professor/gerenciar_equacoes.php';
    }
    
    /**
     * Cadastrar equação
     */
    public function cadastrarEquacao()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'professor/equacoes');
            exit;
        }
        
        $a = (int)($_POST['a'] ?? 0);
        $b = (int)($_POST['b'] ?? 0);
        $c = (int)($_POST['c'] ?? 0);
        $dificuldade = $_POST['dificuldade'] ?? 'facil';
        
        if ($a == 0 || $a < -20 || $a > 20 || $b < -20 || $b > 20 || $c < -20 || $c > 20) {
            $_SESSION['admin_error'] = 'Coeficientes inválidos. Devem estar entre -20 e 20.';
            header('Location: ' . BASE_URL . 'professor/equacoes');
            exit;
        }
        
        $resultado = $this->equacao->criar($a, $b, $c, $dificuldade);
        
        if ($resultado) {
            $_SESSION['admin_success'] = 'Equação cadastrada com sucesso!';
        } else {
            $_SESSION['admin_error'] = 'A solução deve ser um número inteiro.';
        }
        
        header('Location: ' . BASE_URL . 'professor/equacoes');
        exit;
    }
    
    /**
     * Excluir equação
     */
    public function excluirEquacao()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'professor/equacoes');
            exit;
        }
        
        $id = (int)($_POST['id'] ?? 0);
        $resultado = $this->equacao->excluir($id);
        
        if ($resultado) {
            $_SESSION['admin_success'] = 'Equação excluída com sucesso!';
        } else {
            $_SESSION['admin_error'] = 'Esta equação já foi utilizada e não pode ser excluída.';
        }
        
        header('Location: ' . BASE_URL . 'professor/equacoes');
        exit;
    }
}