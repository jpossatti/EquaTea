<?php
/**
 * ============================================================
 * ValidadorPasso.php
 * Serviço responsável pela validação dos passos da resolução
 * de equações de 1º grau.
 * 
 * FUNCIONALIDADES:
 * - Validação de cada passo (1 a 4)
 * - Identificação do tipo de erro cometido
 * - Geração de dicas para o aluno
 * - Validação flexível com aceitação de diferentes formatos
 * 
 * @package EquaTEA
 * @subpackage Services
 * @author Equipe EquaTEA
 * @version 1.0
 * ============================================================
 */

/**
 * Class ValidadorPasso
 * 
 * Gerencia a validação dos 4 passos da resolução de equações
 * de 1º grau no formato ax + b = c.
 * 
 * @author Equipe EquaTEA
 */
class ValidadorPasso
{
    /**
     * @var array Mapeamento de tipos de erro para dicas
     */
    private $dicas = [
        'operacao_inversa' => 'Use a operação inversa! Se está somando, subtraia. Se está subtraindo, some.',
        'calculo_errado' => 'Verifique sua conta! Refaça a operação com atenção.',
        'sinal_trocado' => 'Cuidado com o sinal! Lembre-se da regra de sinais.',
        'divisao_incorreta' => 'Verifique a divisão! Divida o número pelo coeficiente de x.',
        'identificacao_errada' => 'Identifique corretamente: termo com x e termos sem x.',
        'outro' => 'Tente novamente com atenção!'
    ];
    
    /**
     * @var array Mapeamento de passos para dicas específicas
     */
    private $dicas_passo = [
        1 => 'Identifique qual parte tem x e qual não tem. O termo com x é aquele que contém a variável.',
        2 => 'Use a operação inversa da constante para isolar o termo com x de um lado da equação.',
        3 => 'Calcule o lado direito da equação. Faça a subtração com cuidado.',
        4 => 'Divida ambos os lados pelo coeficiente de x para encontrar seu valor.'
    ];
    
    /**
     * @var array Opções de dificuldade para validação flexível
     */
    private $validacao_flexivel = true;
    
    /**
     * Construtor da classe
     * 
     * @param bool $validacao_flexivel Se deve aceitar variações na resposta
     */
    public function __construct($validacao_flexivel = true)
    {
        $this->validacao_flexivel = $validacao_flexivel;
    }

    // ============================================================
    // MÉTODO PRINCIPAL DE VALIDAÇÃO
    // ============================================================

    /**
     * Valida a resposta de um passo específico
     * 
     * @param array $equacao Dados da equação (a, b, c, solucao)
     * @param int $passo Passo atual (1-4)
     * @param string $resposta Resposta do aluno
     * @return array Resultado da validação
     */
    public function validar($equacao, $passo, $resposta)
    {
        // ============================================================
        // 1. SANITIZAÇÃO DA RESPOSTA
        // ============================================================
        
        $resposta = trim($resposta);
        $resposta = preg_replace('/\s+/', ' ', $resposta);
        
        // ============================================================
        // 2. VALIDAÇÃO BASEADA NO PASSO
        // ============================================================
        
        $a = (int)$equacao['a'];
        $b = (int)$equacao['b'];
        $c = (int)$equacao['c'];
        $solucao = (int)$equacao['solucao'];
        
        switch ($passo) {
            case 1:
                return $this->validarPasso1($a, $b, $c, $resposta);
            case 2:
                return $this->validarPasso2($a, $b, $c, $resposta);
            case 3:
                return $this->validarPasso3($a, $b, $c, $resposta);
            case 4:
                return $this->validarPasso4($a, $b, $c, $solucao, $resposta);
            default:
                return $this->respostaErro('Passo inválido. Deve ser entre 1 e 4.');
        }
    }

    // ============================================================
    // VALIDAÇÃO DO PASSO 1: IDENTIFICAR TERMOS
    // ============================================================

    /**
     * Valida o Passo 1: Identificar os termos da equação
     * 
     * @param int $a Coeficiente a
     * @param int $b Coeficiente b
     * @param int $c Coeficiente c
     * @param string $resposta Resposta do aluno
     * @return array Resultado da validação
     */
    private function validarPasso1($a, $b, $c, $resposta)
    {
        // ============================================================
        // 1. GERAR RESPOSTA ESPERADA
        // ============================================================
        
        $sinal = $b >= 0 ? '+' : '-';
        $b_abs = abs($b);
        $esperado = "{$a}x {$sinal} {$b_abs} = {$c}";
        
        // Variações aceitas
        $variacoes = [
            $esperado,
            "{$a}x + {$b} = {$c}",
            "{$a}x - {$b_abs} = {$c}" . ($b < 0 ? '' : ''),
            "{$a}x = {$c} - {$b}",
            "{$a}x = {$c} - {$b_abs}"
        ];
        
        // ============================================================
        // 2. NORMALIZAR RESPOSTA E ESPERADO
        // ============================================================
        
        $resposta_norm = $this->normalizarResposta($resposta);
        $esperado_norm = $this->normalizarResposta($esperado);
        
        // ============================================================
        // 3. VERIFICAR SE A RESPOSTA É VÁLIDA
        // ============================================================
        
        $valido = $resposta_norm === $esperado_norm;
        
        // Verificar variações permitidas
        if (!$valido && $this->validacao_flexivel) {
            foreach ($variacoes as $variacao) {
                $variacao_norm = $this->normalizarResposta($variacao);
                if ($resposta_norm === $variacao_norm) {
                    $valido = true;
                    break;
                }
            }
        }
        
        // Verificar se o aluno identificou corretamente os termos (mais flexível)
        if (!$valido && $this->validacao_flexivel) {
            // Verifica se mencionou o termo com x
            $tem_x = stripos($resposta, "{$a}x") !== false || 
                     stripos($resposta, "x") !== false;
            // Verifica se mencionou os termos sem x
            $tem_b = stripos($resposta, (string)$b) !== false || 
                     stripos($resposta, (string)$b_abs) !== false;
            $tem_c = stripos($resposta, (string)$c) !== false;
            
            // Se identificou os principais elementos, considera válido
            if ($tem_x && $tem_b && $tem_c) {
                $valido = true;
            }
        }
        
        // ============================================================
        // 4. RETORNAR RESULTADO
        // ============================================================
        
        if ($valido) {
            return $this->respostaSucesso('Termos identificados corretamente!');
        }
        
        return $this->respostaErro(
            'Os termos não foram identificados corretamente.',
            'identificacao_errada',
            $esperado
        );
    }

    // ============================================================
    // VALIDAÇÃO DO PASSO 2: ISOLAR TERMO COM X
    // ============================================================

    /**
     * Valida o Passo 2: Isolar o termo com x
     * 
     * @param int $a Coeficiente a
     * @param int $b Coeficiente b
     * @param int $c Coeficiente c
     * @param string $resposta Resposta do aluno
     * @return array Resultado da validação
     */
    private function validarPasso2($a, $b, $c, $resposta)
    {
        // ============================================================
        // 1. CALCULAR O RESULTADO DA OPERAÇÃO
        // ============================================================
        
        $resultado = $c - $b;
        $b_abs = abs($b);
        $sinal = $b >= 0 ? '-' : '+';
        
        // Formas esperadas
        $esperados = [
            "{$a}x = {$resultado}",
            "{$a}x = {$c} - {$b_abs}",
            "{$a}x = {$c} {$sinal} {$b_abs}",
            "{$a}x = " . ($resultado)
        ];
        
        // Se b é negativo, a operação é soma
        if ($b < 0) {
            $esperados[] = "{$a}x = {$c} + {$b_abs}";
        }
        
        // ============================================================
        // 2. NORMALIZAR RESPOSTA E ESPERADOS
        // ============================================================
        
        $resposta_norm = $this->normalizarResposta($resposta);
        
        // ============================================================
        // 3. VERIFICAR SE A RESPOSTA É VÁLIDA
        // ============================================================
        
        $valido = false;
        $tipo_erro = 'operacao_inversa';
        $esperado_correto = "{$a}x = {$resultado}";
        
        foreach ($esperados as $esperado) {
            $esperado_norm = $this->normalizarResposta($esperado);
            if ($resposta_norm === $esperado_norm) {
                $valido = true;
                break;
            }
        }
        
        // Verificar se o aluno usou a operação correta (mais flexível)
        if (!$valido && $this->validacao_flexivel) {
            // Extrair o número após o "=" na resposta
            if (preg_match('/=\s*([\d\-]+)/', $resposta_norm, $matches)) {
                $resposta_valor = (int)$matches[1];
                if ($resposta_valor === $resultado) {
                    $valido = true;
                } else {
                    // Verifica se o erro é de sinal
                    if ($resposta_valor === ($c + $b) || $resposta_valor === ($c + abs($b))) {
                        $tipo_erro = 'sinal_trocado';
                    } else {
                        $tipo_erro = 'calculo_errado';
                    }
                }
            }
        }
        
        // ============================================================
        // 4. RETORNAR RESULTADO
        // ============================================================
        
        if ($valido) {
            return $this->respostaSucesso('Termo com x isolado corretamente!');
        }
        
        return $this->respostaErro(
            'A operação inversa não foi aplicada corretamente.',
            $tipo_erro,
            $esperado_correto
        );
    }

    // ============================================================
    // VALIDAÇÃO DO PASSO 3: CALCULAR LADO DIREITO
    // ============================================================

    /**
     * Valida o Passo 3: Calcular o lado direito da equação
     * 
     * @param int $a Coeficiente a
     * @param int $b Coeficiente b
     * @param int $c Coeficiente c
     * @param string $resposta Resposta do aluno
     * @return array Resultado da validação
     */
    private function validarPasso3($a, $b, $c, $resposta)
    {
        // ============================================================
        // 1. CALCULAR O RESULTADO ESPERADO
        // ============================================================
        
        $resultado = $c - $b;
        $esperado = (string)$resultado;
        
        // ============================================================
        // 2. NORMALIZAR RESPOSTA
        // ============================================================
        
        $resposta_norm = $this->normalizarResposta($resposta);
        
        // ============================================================
        // 3. VERIFICAR SE A RESPOSTA É VÁLIDA
        // ============================================================
        
        $valido = false;
        $tipo_erro = 'calculo_errado';
        
        // Verifica se a resposta é um número
        if (is_numeric($resposta_norm)) {
            $resposta_num = (int)$resposta_norm;
            if ($resposta_num === $resultado) {
                $valido = true;
            }
        }
        
        // Verificar se a resposta é uma expressão que resulta no valor correto
        if (!$valido && $this->validacao_flexivel) {
            // Tenta avaliar a expressão (com segurança)
            $resposta_sem_espacos = str_replace(' ', '', $resposta_norm);
            // Verifica se contém apenas números e operadores básicos
            if (preg_match('/^[\d\+\-\*\/\(\)]+$/', $resposta_sem_espacos)) {
                try {
                    // Avalia a expressão (apenas para verificação)
                    $resultado_calculado = eval("return {$resposta_sem_espacos};");
                    if (is_numeric($resultado_calculado) && (int)$resultado_calculado === $resultado) {
                        $valido = true;
                    }
                } catch (ParseError $e) {
                    // Ignora erro de parsing
                }
            }
        }
        
        // ============================================================
        // 4. RETORNAR RESULTADO
        // ============================================================
        
        if ($valido) {
            return $this->respostaSucesso('Lado direito calculado corretamente!');
        }
        
        return $this->respostaErro(
            'O cálculo do lado direito está incorreto.',
            $tipo_erro,
            $esperado
        );
    }

    // ============================================================
    // VALIDAÇÃO DO PASSO 4: ISOLAR X
    // ============================================================

    /**
     * Valida o Passo 4: Isolar x (dividir pelo coeficiente)
     * 
     * @param int $a Coeficiente a
     * @param int $b Coeficiente b
     * @param int $c Coeficiente c
     * @param int $solucao Solução da equação
     * @param string $resposta Resposta do aluno
     * @return array Resultado da validação
     */
    private function validarPasso4($a, $b, $c, $solucao, $resposta)
    {
        // ============================================================
        // 1. PREPARAR RESPOSTA ESPERADA
        // ============================================================
        
        $esperado = (string)$solucao;
        $resultado = ($c - $b) / $a;
        
        // ============================================================
        // 2. NORMALIZAR RESPOSTA
        // ============================================================
        
        $resposta_norm = $this->normalizarResposta($resposta);
        
        // ============================================================
        // 3. VERIFICAR SE A RESPOSTA É VÁLIDA
        // ============================================================
        
        $valido = false;
        $tipo_erro = 'divisao_incorreta';
        
        // Verifica se a resposta é um número
        if (is_numeric($resposta_norm)) {
            $resposta_num = (float)$resposta_norm;
            if (abs($resposta_num - $resultado) < 0.001) {
                $valido = true;
            }
        }
        
        // Verificar respostas com fração (ex: "9/3")
        if (!$valido && $this->validacao_flexivel) {
            if (preg_match('/^(\d+)\/(\d+)$/', $resposta_norm, $matches)) {
                $numerador = (int)$matches[1];
                $denominador = (int)$matches[2];
                if ($denominador != 0 && $numerador / $denominador == $resultado) {
                    $valido = true;
                }
            }
            
            // Verificar resposta com expressão que resulta no valor correto
            $resposta_sem_espacos = str_replace(' ', '', $resposta_norm);
            if (preg_match('/^[\d\+\-\*\/\(\)]+$/', $resposta_sem_espacos)) {
                try {
                    $resultado_calculado = eval("return {$resposta_sem_espacos};");
                    if (is_numeric($resultado_calculado) && abs($resultado_calculado - $resultado) < 0.001) {
                        $valido = true;
                    }
                } catch (ParseError $e) {
                    // Ignora erro de parsing
                }
            }
        }
        
        // ============================================================
        // 4. RETORNAR RESULTADO
        // ============================================================
        
        if ($valido) {
            return $this->respostaSucesso('Valor de x encontrado corretamente!');
        }
        
        return $this->respostaErro(
            'O valor de x está incorreto. Verifique a divisão.',
            $tipo_erro,
            $esperado
        );
    }

    // ============================================================
    // MÉTODOS AUXILIARES
    // ============================================================

    /**
     * Normaliza uma resposta para comparação
     * 
     * @param string $resposta Resposta a ser normalizada
     * @return string Resposta normalizada
     */
    private function normalizarResposta($resposta)
    {
        // Remove espaços extras
        $resposta = trim($resposta);
        $resposta = preg_replace('/\s+/', ' ', $resposta);
        
        // Remove caracteres especiais (mantém números, letras e operadores básicos)
        $resposta = preg_replace('/[^a-zA-Z0-9\s\+\-\*\/\=\(\)]/', '', $resposta);
        
        // Converte para minúsculas
        $resposta = strtolower($resposta);
        
        // Remove espaços em torno de operadores
        $resposta = preg_replace('/\s*([\+\-\*\/\=])\s*/', '$1', $resposta);
        
        return $resposta;
    }

    /**
     * Gera uma resposta de sucesso
     * 
     * @param string $mensagem Mensagem de sucesso
     * @return array Resposta formatada
     */
    private function respostaSucesso($mensagem)
    {
        return [
            'status' => 'success',
            'mensagem' => $mensagem,
            'valido' => true,
            'tipo_erro' => null,
            'esperado' => null
        ];
    }

    /**
     * Gera uma resposta de erro
     * 
     * @param string $mensagem Mensagem de erro
     * @param string $tipo_erro Tipo de erro identificado
     * @param string $esperado Resposta esperada
     * @return array Resposta formatada
     */
    private function respostaErro($mensagem, $tipo_erro = 'outro', $esperado = null)
    {
        return [
            'status' => 'error',
            'mensagem' => $mensagem,
            'valido' => false,
            'tipo_erro' => $tipo_erro,
            'esperado' => $esperado,
            'dica' => $this->getDica($tipo_erro)
        ];
    }

    /**
     * Obtém a resposta esperada para um passo
     * 
     * @param array $equacao Dados da equação
     * @param int $passo Passo atual
     * @return string|null Resposta esperada ou null
     */
    public function getRespostaEsperada($equacao, $passo)
    {
        $a = (int)$equacao['a'];
        $b = (int)$equacao['b'];
        $c = (int)$equacao['c'];
        $solucao = (int)$equacao['solucao'];
        
        switch ($passo) {
            case 1:
                $sinal = $b >= 0 ? '+' : '-';
                return "{$a}x {$sinal} " . abs($b) . " = {$c}";
            case 2:
                return "{$a}x = " . ($c - $b);
            case 3:
                return (string)($c - $b);
            case 4:
                return (string)$solucao;
            default:
                return null;
        }
    }

    /**
     * Identifica o tipo de erro baseado na resposta do aluno
     * 
     * @param array $equacao Dados da equação
     * @param int $passo Passo atual
     * @param string $resposta Resposta do aluno
     * @return string Tipo de erro identificado
     */
    public function identificarErro($equacao, $passo, $resposta)
    {
        $a = (int)$equacao['a'];
        $b = (int)$equacao['b'];
        $c = (int)$equacao['c'];
        $resposta = trim($resposta);
        
        switch ($passo) {
            case 1:
                return 'identificacao_errada';
            case 2:
                // Verifica se o sinal foi trocado
                if (strpos($resposta, '+') !== false && $b > 0) {
                    return 'sinal_trocado';
                }
                if (strpos($resposta, '-') !== false && $b < 0) {
                    return 'sinal_trocado';
                }
                return 'operacao_inversa';
            case 3:
                return 'calculo_errado';
            case 4:
                return 'divisao_incorreta';
            default:
                return 'outro';
        }
    }

    /**
     * Obtém uma dica para um tipo de erro específico
     * 
     * @param string $tipo_erro Tipo de erro
     * @return string Dica para o erro
     */
    public function getDica($tipo_erro)
    {
        return $this->dicas[$tipo_erro] ?? $this->dicas['outro'];
    }

    /**
     * Obtém a dica para um passo específico
     * 
     * @param int $passo Passo atual
     * @return string Dica para o passo
     */
    public function getDicaPasso($passo)
    {
        return $this->dicas_passo[$passo] ?? 'Siga os passos com atenção.';
    }

    /**
     * Verifica se uma resposta é válida para um passo
     * 
     * @param array $equacao Dados da equação
     * @param int $passo Passo atual
     * @param string $resposta Resposta do aluno
     * @return bool True se a resposta é válida
     */
    public function isValid($equacao, $passo, $resposta)
    {
        $resultado = $this->validar($equacao, $passo, $resposta);
        return $resultado['valido'];
    }

    /**
     * Define se a validação flexível está ativada
     * 
     * @param bool $ativado Se deve ativar a validação flexível
     */
    public function setValidacaoFlexivel($ativado)
    {
        $this->validacao_flexivel = (bool)$ativado;
    }
}