<?php
/**
 * AlunoController.php
 * Controlador para funcionalidades do aluno.
 */
class AlunoController
{
    private $aluno;
    private $equacao;
    private $progresso;
    private $registroErro;
    
    public function __construct()
    {
        $this->aluno = new Aluno();
        $this->equacao = new Equacao();
        $this->progresso = new Progresso();
        $this->registroErro = new RegistroErro();
    }
    
    /**
     * Dashboard do aluno
     */
    public function dashboard()
    {
        $aluno_id = $_SESSION['aluno_id'];
        
        $dados = [
            'aluno' => $this->aluno->getDadosCompletos($_SESSION['usuario_id']),
            'estatisticas' => $this->aluno->getEstatisticas($aluno_id),
            'progresso' => $this->progresso->getByAluno($aluno_id),
            'taxa_conclusao' => $this->progresso->getTaxaConclusao($aluno_id),
            'erros' => $this->registroErro->getEstatisticas($aluno_id)
        ];
        
        include_once VIEWS_PATH . '/aluno/dashboard.php';
    }
    
    /**
     * Inicia um novo exercício
     */
    public function novoExercicio()
    {
        $aluno_id = $_SESSION['aluno_id'];
        
        // Busca equação aleatória
        $equacao = $this->equacao->getRandom($aluno_id);
        
        if (!$equacao) {
            $_SESSION['msg'] = 'Parabéns! Você já concluiu todas as equações disponíveis!';
            header('Location: ' . BASE_URL . 'aluno/dashboard');
            exit;
        }
        
        // Verifica se já existe progresso
        $progresso = $this->progresso->getByAlunoEquacao($aluno_id, $equacao['id']);
        
        if (!$progresso) {
            $this->progresso->iniciar($aluno_id, $equacao['id']);
            $passo_atual = 1;
        } else {
            $passo_atual = $progresso['passo_atual'];
            
            if ($progresso['concluida']) {
                $_SESSION['msg'] = 'Esta equação já foi concluída!';
                header('Location: ' . BASE_URL . 'aluno/dashboard');
                exit;
            }
        }
        
        header('Location: ' . BASE_URL . 'aluno/exercicio/' . $equacao['id']);
        exit;
    }
    
    /**
     * Exibe o exercício passo a passo
     */
    public function exercicio($equacao_id)
    {
        $aluno_id = $_SESSION['aluno_id'];
        
        $equacao = $this->equacao->getById($equacao_id);
        if (!$equacao) {
            header('Location: ' . BASE_URL . 'aluno/dashboard');
            exit;
        }
        
        $progresso = $this->progresso->getByAlunoEquacao($aluno_id, $equacao_id);
        if (!$progresso) {
            $this->progresso->iniciar($aluno_id, $equacao_id);
            $passo_atual = 1;
        } else {
            $passo_atual = $progresso['passo_atual'];
        }
        
        $dados = [
            'equacao' => $equacao,
            'passo_atual' => $passo_atual,
            'enunciado' => $this->equacao->getEnunciado($equacao_id),
            'passo_info' => $this->getPassoInfo($passo_atual)
        ];
        
        include_once VIEWS_PATH . '/aluno/exercicio.php';
    }
    
    /**
     * Verifica a resposta do aluno
     */
    public function verificarResposta()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'aluno/dashboard');
            exit;
        }
        
        $aluno_id = $_SESSION['aluno_id'];
        $equacao_id = (int)($_POST['equacao_id'] ?? 0);
        $passo = (int)($_POST['passo'] ?? 0);
        $resposta = trim($_POST['resposta'] ?? '');
        
        $equacao = $this->equacao->getById($equacao_id);
        if (!$equacao) {
            echo json_encode(['status' => 'error', 'mensagem' => 'Equação não encontrada']);
            exit;
        }
        
        $valido = $this->equacao->validarResposta($equacao_id, $passo, $resposta);
        
        if ($valido) {
            // Acertou
            $this->progresso->registrarTentativa($aluno_id, $equacao_id);
            
            if ($passo == 4) {
                $this->progresso->concluir($aluno_id, $equacao_id);
                echo json_encode(['status' => 'concluido', 'mensagem' => 'Parabéns!']);
            } else {
                $this->progresso->avancarPasso($aluno_id, $equacao_id);
                echo json_encode(['status' => 'avancar', 'passo' => $passo + 1]);
            }
        } else {
            // Errou
            $this->progresso->registrarTentativa($aluno_id, $equacao_id);
            
            $tipo_erro = $this->registroErro->identificarTipoErro($equacao, $passo, $resposta);
            $esperado = $this->equacao->getRespostaEsperada($equacao_id, $passo);
            
            $this->registroErro->registrar(
                $aluno_id,
                $equacao_id,
                $passo,
                $tipo_erro,
                $resposta,
                $esperado
            );
            
            $dica = $this->getDicaErro($tipo_erro);
            echo json_encode(['status' => 'erro', 'mensagem' => 'Resposta incorreta!', 'dica' => $dica]);
        }
        exit;
    }
    
    /**
     * Obtém informações do passo
     */
    private function getPassoInfo($passo)
    {
        $passos = [
            1 => [
                'titulo' => 'Passo 1: Identificar os termos',
                'descricao' => 'Identifique quais termos têm x e quais não têm.',
                'placeholder' => 'Ex: 3x + 5 = 14'
            ],
            2 => [
                'titulo' => 'Passo 2: Isolar o termo com x',
                'descricao' => 'Use a operação inversa para isolar o termo com x.',
                'placeholder' => 'Ex: 3x = 14 - 5'
            ],
            3 => [
                'titulo' => 'Passo 3: Calcular o lado direito',
                'descricao' => 'Calcule o valor do lado direito da equação.',
                'placeholder' => 'Ex: 9'
            ],
            4 => [
                'titulo' => 'Passo 4: Isolar x',
                'descricao' => 'Divida ambos os lados pelo coeficiente de x.',
                'placeholder' => 'Ex: 3'
            ]
        ];
        
        return $passos[$passo] ?? null;
    }
    
    /**
     * Obtém dica para o erro
     */
    private function getDicaErro($tipo_erro)
    {
        $dicas = [
            'operacao_inversa' => 'Use a operação inversa! Se está somando, subtraia. Se está subtraindo, some.',
            'calculo_errado' => 'Verifique sua conta! Refaça a operação com atenção.',
            'sinal_trocado' => 'Cuidado com o sinal! Lembre-se da regra de sinais.',
            'divisao_incorreta' => 'Verifique a divisão! Divida o número pelo coeficiente de x.',
            'identificacao_errada' => 'Identifique corretamente: termo com x e termos sem x.',
            'outro' => 'Tente novamente com atenção!'
        ];
        
        return $dicas[$tipo_erro] ?? $dicas['outro'];
    }
}