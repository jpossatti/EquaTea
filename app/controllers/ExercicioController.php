<?php
require_once CONTROLLERS_PATH . '/../models/Equacao.php';
require_once CONTROLLERS_PATH . '/../models/Progresso.php';
require_once CONTROLLERS_PATH . '/../models/RegistroErro.php';
require_once CONTROLLERS_PATH . '/../services/ValidadorPasso.php';

class ExercicioController {
    private $equacao;
    private $progresso;
    private $registroErro;
    private $validador;
    
    public function __construct() {
        $this->equacao = new Equacao();
        $this->progresso = new Progresso();
        $this->registroErro = new RegistroErro();
        $this->validador = new ValidadorPasso();
    }
    
    public function iniciar($aluno_id) {
        // Busca uma equação aleatória
        $equacao = $this->equacao->getRandom($aluno_id);
        
        if (!$equacao) {
            header('Location: dashboard.php?msg=sem_equacoes');
            exit;
        }
        
        // Verifica se já existe progresso
        $progresso = $this->progresso->getByAlunoEquacao($aluno_id, $equacao['id']);
        
        if (!$progresso) {
            // Inicia novo progresso
            $this->progresso->iniciar($aluno_id, $equacao['id']);
            $passo_atual = 1;
        } else {
            $passo_atual = $progresso['passo_atual'];
        }
        
        // Redireciona para o exercício
        header("Location: ../views/aluno/exercicio.php?equacao_id={$equacao['id']}");
        exit;
    }
    
    public function verificar($aluno_id, $equacao_id, $passo, $resposta) {
        $equacao = $this->equacao->getById($equacao_id);
        
        if (!$equacao) {
            return ['erro' => 'Equação não encontrada'];
        }
        
        $valido = $this->validador->validar($equacao, $passo, $resposta);
        
        if ($valido) {
            // Acertou
            if ($passo == 4) {
                $this->progresso->concluir($aluno_id, $equacao_id);
                return ['status' => 'concluido', 'mensagem' => 'Parabéns! Você concluiu a equação!'];
            } else {
                $this->progresso->avancarPasso($aluno_id, $equacao_id, $passo);
                return ['status' => 'avancar', 'passo' => $passo + 1, 'mensagem' => 'Correto! Avance para o próximo passo.'];
            }
        } else {
            // Errou
            $tipo_erro = $this->validador->identificarErro($equacao, $passo, $resposta);
            $this->registroErro->registrar(
                $aluno_id,
                $equacao_id,
                $passo,
                $tipo_erro,
                $resposta,
                $this->validador->getRespostaEsperada($equacao, $passo)
            );
            
            $dica = $this->validador->getDica($tipo_erro);
            return ['status' => 'erro', 'mensagem' => 'Resposta incorreta!', 'dica' => $dica];
        }
    }
    
    public function getExercicio($aluno_id, $equacao_id) {
        $equacao = $this->equacao->getById($equacao_id);
        $progresso = $this->progresso->getByAlunoEquacao($aluno_id, $equacao_id);
        
        if (!$equacao || !$progresso) {
            return null;
        }
        
        return [
            'equacao' => $equacao,
            'passo_atual' => $progresso['passo_atual'],
            'concluida' => $progresso['concluida'],
            'enunciado' => $this->equacao->getEnunciado($equacao_id),
            'dica_passo' => $this->validador->getDicaPasso($progresso['passo_atual'])
        ];
    }
}