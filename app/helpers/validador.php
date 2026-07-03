<?php
/**
 * ============================================================
 * validador.php
 * Funções de validação específicas para o sistema EquaTEA.
 * 
 * FUNCIONALIDADES:
 * - Validação de equações (coeficientes, solução)
 * - Validação de passos da resolução
 * - Validação de dados de alunos e professores
 * - Validação de formulários
 * - Validação de dados para relatórios
 * 
 * @package EquaTEA
 * @subpackage Helpers
 * @author Equipe EquaTEA
 * @version 1.0
 * ============================================================
 */

// ============================================================
// 1. VALIDAÇÃO DE EQUAÇÕES
// ============================================================

/**
 * Valida os coeficientes de uma equação de 1º grau
 * 
 * @param int $a Coeficiente de x (não pode ser zero)
 * @param int $b Constante do lado esquerdo
 * @param int $c Constante do lado direito
 * @return array Resultado da validação com erros
 */
function validarCoeficientes($a, $b, $c)
{
    $erros = [];
    
    // Validar a (não pode ser zero)
    if ($a == 0) {
        $erros[] = 'O coeficiente a não pode ser zero.';
    }
    if ($a < -20 || $a > 20) {
        $erros[] = 'O coeficiente a deve estar entre -20 e 20.';
    }
    
    // Validar b
    if ($b < -20 || $b > 20) {
        $erros[] = 'O coeficiente b deve estar entre -20 e 20.';
    }
    
    // Validar c
    if ($c < -20 || $c > 20) {
        $erros[] = 'O coeficiente c deve estar entre -20 e 20.';
    }
    
    return $erros;
}

/**
 * Valida se a solução de uma equação é um número inteiro
 * 
 * @param int $a Coeficiente de x
 * @param int $b Constante do lado esquerdo
 * @param int $c Constante do lado direito
 * @return array Resultado da validação com solução e se é inteira
 */
function validarSolucaoInteira($a, $b, $c)
{
    if ($a == 0) {
        return ['valido' => false, 'solucao' => null, 'erro' => 'a não pode ser zero'];
    }
    
    $solucao = ($c - $b) / $a;
    $inteira = fmod($solucao, 1) == 0;
    
    return [
        'valido' => $inteira,
        'solucao' => $solucao,
        'erro' => $inteira ? null : 'A solução deve ser um número inteiro. Valor calculado: ' . $solucao
    ];
}

/**
 * Valida se uma equação é válida (coeficientes dentro do intervalo e solução inteira)
 * 
 * @param int $a Coeficiente de x
 * @param int $b Constante do lado esquerdo
 * @param int $c Constante do lado direito
 * @return array Resultado completo da validação
 */
function validarEquacaoCompleta($a, $b, $c)
{
    // Validar coeficientes
    $erros_coeficientes = validarCoeficientes($a, $b, $c);
    if (!empty($erros_coeficientes)) {
        return [
            'valido' => false,
            'erros' => $erros_coeficientes,
            'solucao' => null
        ];
    }
    
    // Validar solução inteira
    $resultado_solucao = validarSolucaoInteira($a, $b, $c);
    if (!$resultado_solucao['valido']) {
        return [
            'valido' => false,
            'erros' => [$resultado_solucao['erro']],
            'solucao' => $resultado_solucao['solucao']
        ];
    }
    
    return [
        'valido' => true,
        'erros' => [],
        'solucao' => (int)$resultado_solucao['solucao']
    ];
}

// ============================================================
// 2. VALIDAÇÃO DE PASSOS DA RESOLUÇÃO
// ============================================================

/**
 * Valida a resposta de um passo específico da resolução
 * 
 * @param array $equacao Dados da equação (a, b, c, solucao)
 * @param int $passo Passo atual (1-4)
 * @param string $resposta Resposta do aluno
 * @return array Resultado da validação
 */
function validarPasso($equacao, $passo, $resposta)
{
    $a = $equacao['a'];
    $b = $equacao['b'];
    $c = $equacao['c'];
    $resposta = trim($resposta);
    
    switch ($passo) {
        case 1: // Identificar termos
            return validarPasso1($a, $b, $c, $resposta);
        case 2: // Isolar termo com x
            return validarPasso2($a, $b, $c, $resposta);
        case 3: // Calcular lado direito
            return validarPasso3($a, $b, $c, $resposta);
        case 4: // Isolar x
            return validarPasso4($a, $b, $c, $resposta);
        default:
            return [
                'valido' => false,
                'erro' => 'Passo inválido',
                'esperado' => null
            ];
    }
}

/**
 * Valida o Passo 1: Identificar termos
 */
function validarPasso1($a, $b, $c, $resposta)
{
    // Formato esperado: "3x + 5 = 14" ou similar
    $sinal = $b >= 0 ? '+' : '-';
    $esperado = "{$a}x {$sinal} " . abs($b) . " = {$c}";
    
    // Normaliza a resposta (remove espaços extras, converte para minúsculas)
    $resposta_normalizada = strtolower(preg_replace('/\s+/', ' ', trim($resposta)));
    $esperado_normalizado = strtolower(preg_replace('/\s+/', ' ', trim($esperado)));
    
    $valido = $resposta_normalizada === $esperado_normalizado;
    
    return [
        'valido' => $valido,
        'esperado' => $esperado,
        'erro' => $valido ? null : 'Os termos não foram identificados corretamente.'
    ];
}

/**
 * Valida o Passo 2: Isolar termo com x
 */
function validarPasso2($a, $b, $c, $resposta)
{
    $resultado = $c - $b;
    $esperado = "{$a}x = {$resultado}";
    
    // Permite variações como "2x = 14 - 5" ou "2x = 9"
    $resposta_normalizada = strtolower(preg_replace('/\s+/', '', trim($resposta)));
    $esperado_normalizado = strtolower(preg_replace('/\s+/', '', trim($esperado)));
    
    // Também permite a resposta com a operação explícita
    $esperado_com_operacao = "{$a}x = {$c} - " . abs($b);
    $esperado_com_operacao_normalizado = strtolower(preg_replace('/\s+/', '', trim($esperado_com_operacao)));
    
    $valido = $resposta_normalizada === $esperado_normalizado || 
              $resposta_normalizada === $esperado_com_operacao_normalizado;
    
    return [
        'valido' => $valido,
        'esperado' => $esperado,
        'erro' => $valido ? null : 'A operação inversa não foi aplicada corretamente.'
    ];
}

/**
 * Valida o Passo 3: Calcular lado direito
 */
function validarPasso3($a, $b, $c, $resposta)
{
    $esperado = $c - $b;
    
    // Remove espaços e converte para inteiro
    $resposta_normalizada = trim($resposta);
    $valido = (int)$resposta_normalizada === $esperado;
    
    return [
        'valido' => $valido,
        'esperado' => $esperado,
        'erro' => $valido ? null : 'O cálculo do lado direito está incorreto.'
    ];
}

/**
 * Valida o Passo 4: Isolar x */
function validarPasso4($a, $b, $c, $resposta)
{
    $esperado = ($c - $b) / $a;
    
    // Remove espaços e converte para número
    $resposta_normalizada = trim($resposta);
    $valido = (float)$resposta_normalizada === (float)$esperado;
    
    return [
        'valido' => $valido,
        'esperado' => $esperado,
        'erro' => $valido ? null : 'O valor de x está incorreto.'
    ];
}

/**
 * Identifica o tipo de erro baseado na resposta do aluno
 * 
 * @param array $equacao Dados da equação
 * @param int $passo Passo atual
 * @param string $resposta Resposta do aluno
 * @return string Tipo de erro identificado
 */
function identificarTipoErro($equacao, $passo, $resposta)
{
    $a = $equacao['a'];
    $b = $equacao['b'];
    $c = $equacao['c'];
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

// ============================================================
// 3. VALIDAÇÃO DE DADOS DE ALUNOS
// ============================================================

/**
 * Valida os dados de um aluno
 * 
 * @param string $nome Nome do aluno
 * @param string $email E-mail do aluno
 * @param int $idade Idade do aluno
 * @param string $nivel_tea Nível de suporte TEA
 * @param string $escola Escola do aluno
 * @param string $turma Turma do aluno
 * @return array Resultado da validação
 */
function validarDadosAluno($nome, $email, $idade, $nivel_tea, $escola, $turma)
{
    $erros = [];
    
    // Validar nome
    if (empty($nome) || strlen($nome) < 3) {
        $erros[] = 'O nome deve ter pelo menos 3 caracteres.';
    }
    if (strlen($nome) > 100) {
        $erros[] = 'O nome deve ter no máximo 100 caracteres.';
    }
    
    // Validar e-mail
    if (empty($email) || !validarEmail($email)) {
        $erros[] = 'E-mail inválido.';
    }
    
    // Validar idade
    if ($idade < 14 || $idade > 21) {
        $erros[] = 'A idade deve estar entre 14 e 21 anos.';
    }
    
    // Validar nível TEA
    if (!in_array($nivel_tea, ['suporte1', 'suporte2'])) {
        $erros[] = 'Nível de suporte TEA inválido.';
    }
    
    // Validar escola (opcional, mas deve ter no máximo 100 caracteres)
    if (strlen($escola) > 100) {
        $erros[] = 'A escola deve ter no máximo 100 caracteres.';
    }
    
    // Validar turma (opcional, mas deve ter no máximo 20 caracteres)
    if (strlen($turma) > 20) {
        $erros[] = 'A turma deve ter no máximo 20 caracteres.';
    }
    
    return [
        'valido' => empty($erros),
        'erros' => $erros
    ];
}

/**
 * Valida os dados de um professor
 * 
 * @param string $nome Nome do professor
 * @param string $email E-mail do professor
 * @param string $disciplina Disciplina
 * @param string $escola Escola
 * @param string $telefone Telefone
 * @return array Resultado da validação
 */
function validarDadosProfessor($nome, $email, $disciplina, $escola, $telefone)
{
    $erros = [];
    
    // Validar nome
    if (empty($nome) || strlen($nome) < 3) {
        $erros[] = 'O nome deve ter pelo menos 3 caracteres.';
    }
    if (strlen($nome) > 100) {
        $erros[] = 'O nome deve ter no máximo 100 caracteres.';
    }
    
    // Validar e-mail
    if (empty($email) || !validarEmail($email)) {
        $erros[] = 'E-mail inválido.';
    }
    
    // Validar disciplina
    if (empty($disciplina) || strlen($disciplina) > 50) {
        $erros[] = 'A disciplina deve ter no máximo 50 caracteres.';
    }
    
    // Validar escola
    if (empty($escola) || strlen($escola) > 100) {
        $erros[] = 'A escola deve ter no máximo 100 caracteres.';
    }
    
    // Validar telefone (opcional)
    if (!empty($telefone) && strlen($telefone) > 20) {
        $erros[] = 'O telefone deve ter no máximo 20 caracteres.';
    }
    
    return [
        'valido' => empty($erros),
        'erros' => $erros
    ];
}

// ============================================================
// 4. VALIDAÇÃO DE FORMULÁRIOS
// ============================================================

/**
 * Valida os dados de um formulário de cadastro de equação
 * 
 * @param array $dados Dados do formulário
 * @return array Resultado da validação
 */
function validarFormularioEquacao($dados)
{
    $erros = [];
    
    // Campos obrigatórios
    $campos = ['a', 'b', 'c', 'dificuldade'];
    foreach ($campos as $campo) {
        if (!isset($dados[$campo]) || $dados[$campo] === '') {
            $erros[] = "O campo '{$campo}' é obrigatório.";
        }
    }
    
    if (!empty($erros)) {
        return ['valido' => false, 'erros' => $erros];
    }
    
    // Validar coeficientes
    $a = (int)$dados['a'];
    $b = (int)$dados['b'];
    $c = (int)$dados['c'];
    $dificuldade = $dados['dificuldade'];
    
    // Validar coeficientes
    $erros_coeficientes = validarCoeficientes($a, $b, $c);
    if (!empty($erros_coeficientes)) {
        $erros = array_merge($erros, $erros_coeficientes);
    }
    
    // Validar solução inteira
    $resultado_solucao = validarSolucaoInteira($a, $b, $c);
    if (!$resultado_solucao['valido']) {
        $erros[] = $resultado_solucao['erro'];
    }
    
    // Validar dificuldade
    if (!in_array($dificuldade, ['facil', 'medio', 'dificil'])) {
        $erros[] = 'Dificuldade inválida.';
    }
    
    return [
        'valido' => empty($erros),
        'erros' => $erros
    ];
}

/**
 * Valida os dados de um formulário de cadastro de aluno
 * 
 * @param array $dados Dados do formulário
 * @return array Resultado da validação
 */
function validarFormularioAluno($dados)
{
    $erros = [];
    
    // Campos obrigatórios
    $campos = ['nome', 'email', 'senha', 'idade', 'nivel_tea'];
    foreach ($campos as $campo) {
        if (!isset($dados[$campo]) || $dados[$campo] === '') {
            $erros[] = "O campo '{$campo}' é obrigatório.";
        }
    }
    
    if (!empty($erros)) {
        return ['valido' => false, 'erros' => $erros];
    }
    
    // Validar dados do aluno
    $nome = $dados['nome'];
    $email = $dados['email'];
    $idade = (int)$dados['idade'];
    $nivel_tea = $dados['nivel_tea'];
    $escola = $dados['escola'] ?? '';
    $turma = $dados['turma'] ?? '';
    $senha = $dados['senha'];
    
    // Validar nome
    if (empty($nome) || strlen($nome) < 3) {
        $erros[] = 'O nome deve ter pelo menos 3 caracteres.';
    }
    
    // Validar e-mail
    if (empty($email) || !validarEmail($email)) {
        $erros[] = 'E-mail inválido.';
    }
    
    // Validar senha
    if (empty($senha) || strlen($senha) < 4) {
        $erros[] = 'A senha deve ter pelo menos 4 caracteres.';
    }
    
    // Validar idade
    if ($idade < 14 || $idade > 21) {
        $erros[] = 'A idade deve estar entre 14 e 21 anos.';
    }
    
    // Validar nível TEA
    if (!in_array($nivel_tea, ['suporte1', 'suporte2'])) {
        $erros[] = 'Nível de suporte TEA inválido.';
    }
    
    return [
        'valido' => empty($erros),
        'erros' => $erros
    ];
}

// ============================================================
// 5. VALIDAÇÃO DE RELATÓRIOS
// ============================================================

/**
 * Valida os parâmetros de um relatório
 * 
 * @param int|null $aluno_id ID do aluno
 * @param int|null $passo Passo (1-4)
 * @param string|null $tipo Tipo de relatório
 * @return array Resultado da validação
 */
function validarParametrosRelatorio($aluno_id, $passo, $tipo)
{
    $erros = [];
    
    // Validar ID do aluno (se fornecido)
    if ($aluno_id !== null) {
        if (!is_numeric($aluno_id) || $aluno_id <= 0) {
            $erros[] = 'ID do aluno inválido.';
        }
    }
    
    // Validar passo (se fornecido)
    if ($passo !== null) {
        $passo = (int)$passo;
        if ($passo < 1 || $passo > 4) {
            $erros[] = 'Passo inválido. Deve ser entre 1 e 4.';
        }
    }
    
    // Validar tipo de relatório
    if ($tipo !== null) {
        if (!in_array($tipo, ['alunos', 'progresso', 'erros'])) {
            $erros[] = 'Tipo de relatório inválido.';
        }
    }
    
    return [
        'valido' => empty($erros),
        'erros' => $erros
    ];
}

// ============================================================
// 6. FUNÇÕES DE VALIDAÇÃO DE SESSÃO
// ============================================================

/**
 * Verifica a consistência da sessão (IP e User-Agent)
 * 
 * @return bool True se a sessão é consistente
 */
function validarConsistenciaSessao()
{
    if (!estaLogado()) {
        return false;
    }
    
    $ip_atual = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua_atual = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Verifica IP (permite IP dinâmico - verifica apenas os primeiros 2 octetos)
    if (isset($_SESSION['ip_address']) && !empty($ip_atual)) {
        $ip_sessao = explode('.', $_SESSION['ip_address']);
        $ip_atual_arr = explode('.', $ip_atual);
        if (count($ip_sessao) >= 2 && count($ip_atual_arr) >= 2) {
            if ($ip_sessao[0] !== $ip_atual_arr[0] || $ip_sessao[1] !== $ip_atual_arr[1]) {
                return false;
            }
        }
    }
    
    // Verifica User-Agent (apenas os primeiros 50 caracteres)
    if (isset($_SESSION['user_agent']) && !empty($ua_atual)) {
        $ua_sessao = substr($_SESSION['user_agent'], 0, 50);
        $ua_atual_arr = substr($ua_atual, 0, 50);
        if ($ua_sessao !== $ua_atual_arr) {
            return false;
        }
    }
    
    return true;
}

/**
 * Obtém o tempo restante da sessão em minutos
 * 
 * @return int Tempo restante em minutos
 */
function tempoRestanteSessao()
{
    if (!isset($_SESSION['login_time'])) {
        return 0;
    }
    
    $tempo_passado = time() - $_SESSION['login_time'];
    $tempo_restante = 3600 - $tempo_passado; // 1 hora de sessão
    return max(0, round($tempo_restante / 60));
}

// ============================================================
// 7. FUNÇÕES DE VALIDAÇÃO DE DADOS GERAIS
// ============================================================

/**
 * Valida se uma string não está vazia
 * 
 * @param string $str String a ser validada
 * @param int $min_len Tamanho mínimo
 * @param int $max_len Tamanho máximo
 * @return bool True se a string é válida
 */
function validarString($str, $min_len = 1, $max_len = 255)
{
    $str = trim($str);
    return strlen($str) >= $min_len && strlen($str) <= $max_len;
}

/**
 * Valida se um valor está em uma lista de opções permitidas
 * 
 * @param mixed $valor Valor a ser validado
 * @param array $opcoes Lista de opções permitidas
 * @return bool True se o valor está na lista
 */
function validarOpcao($valor, $opcoes)
{
    return in_array($valor, $opcoes, true);
}

/**
 * Valida se um número é positivo
 * 
 * @param int $number Número a ser validado
 * @return bool True se o número é positivo
 */
function validarPositivo($number)
{
    return is_numeric($number) && $number > 0;
}

/**
 * Valida se um número é não-negativo
 * 
 * @param int $number Número a ser validado
 * @return bool True se o número é não-negativo
 */
function validarNaoNegativo($number)
{
    return is_numeric($number) && $number >= 0;
}