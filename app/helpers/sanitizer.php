<?php
/**
 * ============================================================
 * sanitizer.php
 * Funções de sanitização de dados para o sistema EquaTEA.
 * 
 * FUNCIONALIDADES:
 * - Sanitização de entrada de dados
 * - Sanitização de saída de dados
 * - Limpeza de strings
 * - Sanitização de arrays
 * - Prevenção de XSS e SQL Injection
 * - Sanitização de dados para relatórios
 * 
 * @package EquaTEA
 * @subpackage Helpers
 * @author Equipe EquaTEA
 * @version 1.0
 * ============================================================
 */

// ============================================================
// 1. SANITIZAÇÃO DE DADOS DE ENTRADA
// ============================================================

/**
 * Sanitiza uma string removendo tags HTML e espaços extras
 * 
 * @param string $input Texto a ser sanitizado
 * @param bool $strip_tags Se deve remover tags HTML
 * @return string Texto sanitizado
 */
function sanitizarString($input, $strip_tags = true)
{
    if ($input === null) {
        return '';
    }
    
    // Remove espaços extras
    $input = trim($input);
    
    // Remove múltiplos espaços
    $input = preg_replace('/\s+/', ' ', $input);
    
    if ($strip_tags) {
        // Remove tags HTML
        $input = strip_tags($input);
    }
    
    // Remove caracteres de controle
    $input = preg_replace('/[\x00-\x1F\x7F]/', '', $input);
    
    return $input;
}

/**
 * Sanitiza um e-mail
 * 
 * @param string $email E-mail a ser sanitizado
 * @return string E-mail sanitizado
 */
function sanitizarEmail($email)
{
    $email = sanitizarString($email);
    return filter_var($email, FILTER_SANITIZE_EMAIL);
}

/**
 * Sanitiza uma URL
 * 
 * @param string $url URL a ser sanitizada
 * @return string URL sanitizada
 */
function sanitizarURL($url)
{
    $url = sanitizarString($url);
    return filter_var($url, FILTER_SANITIZE_URL);
}

/**
 * Sanitiza um número inteiro
 * 
 * @param mixed $input Valor a ser sanitizado
 * @param int $default Valor padrão se não for válido
 * @return int Número sanitizado
 */
function sanitizarInteiro($input, $default = 0)
{
    $input = filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    if ($input === false || $input === '') {
        return $default;
    }
    return (int)$input;
}

/**
 * Sanitiza um número float
 * 
 * @param mixed $input Valor a ser sanitizado
 * @param float $default Valor padrão se não for válido
 * @return float Número sanitizado
 */
function sanitizarFloat($input, $default = 0.0)
{
    $input = filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    if ($input === false || $input === '') {
        return $default;
    }
    return (float)$input;
}

/**
 * Sanitiza um booleano
 * 
 * @param mixed $input Valor a ser sanitizado
 * @return bool Valor booleano
 */
function sanitizarBooleano($input)
{
    return filter_var($input, FILTER_VALIDATE_BOOLEAN);
}

// ============================================================
// 2. SANITIZAÇÃO DE ARRAYS
// ============================================================

/**
 * Sanitiza todos os valores de um array
 * 
 * @param array $array Array a ser sanitizado
 * @param bool $recursive Se deve sanitizar recursivamente
 * @return array Array sanitizado
 */
function sanitizarArray($array, $recursive = true)
{
    if (!is_array($array)) {
        return sanitizarString($array);
    }
    
    $result = [];
    foreach ($array as $key => $value) {
        $key = sanitizarString($key);
        if ($recursive && is_array($value)) {
            $result[$key] = sanitizarArray($value, true);
        } else {
            $result[$key] = sanitizarString($value);
        }
    }
    return $result;
}

/**
 * Sanitiza um array de entrada (POST/GET)
 * 
 * @param array $data Dados a serem sanitizados
 * @param array $allowed_fields Campos permitidos
 * @return array Dados sanitizados
 */
function sanitizarEntrada($data, $allowed_fields = null)
{
    if (!is_array($data)) {
        return [];
    }
    
    $result = [];
    foreach ($data as $key => $value) {
        // Se campos permitidos estão definidos, verifica se o campo está na lista
        if ($allowed_fields !== null && !in_array($key, $allowed_fields)) {
            continue;
        }
        
        // Sanitiza baseado no tipo
        if (is_array($value)) {
            $result[$key] = sanitizarArray($value);
        } elseif (is_numeric($value)) {
            $result[$key] = sanitizarInteiro($value);
        } elseif (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $result[$key] = sanitizarEmail($value);
        } else {
            $result[$key] = sanitizarString($value);
        }
    }
    return $result;
}

/**
 * Sanitiza dados de um formulário de cadastro de equação
 * 
 * @param array $data Dados do formulário
 * @return array Dados sanitizados
 */
function sanitizarFormularioEquacao($data)
{
    $sanitized = [];
    
    $sanitized['a'] = sanitizarInteiro($data['a'] ?? 0);
    $sanitized['b'] = sanitizarInteiro($data['b'] ?? 0);
    $sanitized['c'] = sanitizarInteiro($data['c'] ?? 0);
    $sanitized['dificuldade'] = sanitizarString($data['dificuldade'] ?? 'facil');
    
    return $sanitized;
}

/**
 * Sanitiza dados de um formulário de cadastro de aluno
 * 
 * @param array $data Dados do formulário
 * @return array Dados sanitizados
 */
function sanitizarFormularioAluno($data)
{
    $sanitized = [];
    
    $sanitized['nome'] = sanitizarString($data['nome'] ?? '');
    $sanitized['email'] = sanitizarEmail($data['email'] ?? '');
    $sanitized['senha'] = sanitizarString($data['senha'] ?? '');
    $sanitized['idade'] = sanitizarInteiro($data['idade'] ?? 0);
    $sanitized['nivel_tea'] = sanitizarString($data['nivel_tea'] ?? 'suporte1');
    $sanitized['escola'] = sanitizarString($data['escola'] ?? '');
    $sanitized['turma'] = sanitizarString($data['turma'] ?? '');
    
    return $sanitized;
}

/**
 * Sanitiza dados de um formulário de cadastro de professor
 * 
 * @param array $data Dados do formulário
 * @return array Dados sanitizados
 */
function sanitizarFormularioProfessor($data)
{
    $sanitized = [];
    
    $sanitized['nome'] = sanitizarString($data['nome'] ?? '');
    $sanitized['email'] = sanitizarEmail($data['email'] ?? '');
    $sanitized['senha'] = sanitizarString($data['senha'] ?? '');
    $sanitized['disciplina'] = sanitizarString($data['disciplina'] ?? 'Matemática');
    $sanitized['escola'] = sanitizarString($data['escola'] ?? '');
    $sanitized['telefone'] = sanitizarString($data['telefone'] ?? '');
    
    return $sanitized;
}

// ============================================================
// 3. SANITIZAÇÃO PARA EXIBIÇÃO (ESCAPE)
// ============================================================

/**
 * Escapa um texto para exibição em HTML
 * 
 * @param string $text Texto a ser escapado
 * @param bool $double_encode Se deve codificar novamente caracteres já codificados
 * @return string Texto escapado
 */
function escaparHTML($text, $double_encode = true)
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8', $double_encode);
}

/**
 * Escapa um texto para uso em atributo HTML
 * 
 * @param string $text Texto a ser escapado
 * @return string Texto escapado para atributo
 */
function escaparAtributo($text)
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8', true);
}

/**
 * Escapa um texto para uso em JavaScript
 * 
 * @param string $text Texto a ser escapado
 * @return string Texto escapado para JavaScript
 */
function escaparJS($text)
{
    return json_encode($text, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

/**
 * Escapa um texto para uso em URL
 * 
 * @param string $text Texto a ser escapado
 * @return string Texto escapado para URL
 */
function escaparURL($text)
{
    return urlencode($text);
}

/**
 * Escapa um texto para uso em SQL (usando prepared statements é melhor)
 * 
 * @param string $text Texto a ser escapado
 * @return string Texto escapado para SQL
 * @deprecated Use prepared statements em vez de escapar manualmente
 */
function escaparSQL($text)
{
    // Esta função é mantida para compatibilidade, mas prepared statements são recomendados
    if (function_exists('mysqli_real_escape_string')) {
        // Se tiver conexão mysqli, usa
        global $conn;
        if (isset($conn) && $conn instanceof mysqli) {
            return $conn->real_escape_string($text);
        }
    }
    // Fallback básico
    return addslashes($text);
}

// ============================================================
// 4. SANITIZAÇÃO DE SAÍDA DE DADOS ESPECÍFICOS
// ============================================================

/**
 * Sanitiza uma equação para exibição
 * 
 * @param int $a Coeficiente a
 * @param int $b Coeficiente b
 * @param int $c Coeficiente c
 * @param bool $escape Se deve escapar para HTML
 * @return string Equação formatada
 */
function sanitizarEquacao($a, $b, $c, $escape = true)
{
    $sinal = $b >= 0 ? '+' : '-';
    $equacao = "{$a}x {$sinal} " . abs($b) . " = {$c}";
    
    return $escape ? escaparHTML($equacao) : $equacao;
}

/**
 * Sanitiza uma solução para exibição
 * 
 * @param int $solucao Solução da equação
 * @param bool $escape Se deve escapar para HTML
 * @return string Solução formatada
 */
function sanitizarSolucao($solucao, $escape = true)
{
    $texto = "x = {$solucao}";
    return $escape ? escaparHTML($texto) : $texto;
}

/**
 * Sanitiza o nome de um aluno para exibição
 * 
 * @param string $nome Nome do aluno
 * @param bool $escape Se deve escapar para HTML
 * @return string Nome sanitizado
 */
function sanitizarNomeAluno($nome, $escape = true)
{
    $nome = sanitizarString($nome);
    // Capitaliza o nome
    $nome = ucwords(strtolower($nome));
    return $escape ? escaparHTML($nome) : $nome;
}

/**
 * Sanitiza o nível de TEA para exibição
 * 
 * @param string $nivel Nível de TEA (suporte1, suporte2)
 * @param bool $escape Se deve escapar para HTML
 * @return string Nível formatado
 */
function sanitizarNivelTEA($nivel, $escape = true)
{
    $labels = [
        'suporte1' => 'Suporte 1',
        'suporte2' => 'Suporte 2'
    ];
    $texto = $labels[$nivel] ?? $nivel;
    return $escape ? escaparHTML($texto) : $texto;
}

/**
 * Sanitiza um tipo de erro para exibição
 * 
 * @param string $tipo_erro Tipo de erro
 * @param bool $escape Se deve escapar para HTML
 * @return string Tipo de erro formatado
 */
function sanitizarTipoErro($tipo_erro, $escape = true)
{
    $labels = [
        'operacao_inversa' => 'Operação Inversa',
        'calculo_errado' => 'Cálculo Errado',
        'sinal_trocado' => 'Sinal Trocado',
        'divisao_incorreta' => 'Divisão Incorreta',
        'identificacao_errada' => 'Identificação Errada',
        'outro' => 'Outro'
    ];
    $texto = $labels[$tipo_erro] ?? ucfirst(str_replace('_', ' ', $tipo_erro));
    return $escape ? escaparHTML($texto) : $texto;
}

// ============================================================
// 5. SANITIZAÇÃO DE DADOS PARA RELATÓRIOS
// ============================================================

/**
 * Sanitiza dados de um relatório para exibição em CSV
 * 
 * @param array $data Dados do relatório
 * @return array Dados sanitizados para CSV
 */
function sanitizarParaCSV($data)
{
    $sanitized = [];
    foreach ($data as $row) {
        $sanitized_row = [];
        foreach ($row as $key => $value) {
            // Remove caracteres que podem quebrar o CSV
            if (is_string($value)) {
                $value = str_replace([',', ';', '"', "\n", "\r"], ' ', $value);
                $value = sanitizarString($value);
            }
            $sanitized_row[$key] = $value;
        }
        $sanitized[] = $sanitized_row;
    }
    return $sanitized;
}

/**
 * Sanitiza dados de um relatório para exibição em JSON
 * 
 * @param array $data Dados do relatório
 * @return array Dados sanitizados para JSON
 */
function sanitizarParaJSON($data)
{
    return sanitizarArray($data);
}

// ============================================================
// 6. FUNÇÕES AUXILIARES DE SANITIZAÇÃO
// ============================================================

/**
 * Remove caracteres especiais de uma string
 * 
 * @param string $input Texto a ser sanitizado
 * @param bool $allow_accented Se deve permitir acentos
 * @return string Texto sem caracteres especiais
 */
function removerCaracteresEspeciais($input, $allow_accented = false)
{
    $input = sanitizarString($input);
    
    if (!$allow_accented) {
        // Remove acentos
        $input = preg_replace('/[áàãâä]/', 'a', $input);
        $input = preg_replace('/[ÁÀÃÂÄ]/', 'A', $input);
        $input = preg_replace('/[éèêë]/', 'e', $input);
        $input = preg_replace('/[ÉÈÊË]/', 'E', $input);
        $input = preg_replace('/[íìîï]/', 'i', $input);
        $input = preg_replace('/[ÍÌÎÏ]/', 'I', $input);
        $input = preg_replace('/[óòôõö]/', 'o', $input);
        $input = preg_replace('/[ÓÒÔÕÖ]/', 'O', $input);
        $input = preg_replace('/[úùûü]/', 'u', $input);
        $input = preg_replace('/[ÚÙÛÜ]/', 'U', $input);
        $input = preg_replace('/[ç]/', 'c', $input);
        $input = preg_replace('/[Ç]/', 'C', $input);
    }
    
    // Remove caracteres especiais
    $input = preg_replace('/[^a-zA-Z0-9\s\-_\.]/', '', $input);
    
    return $input;
}

/**
 * Gera um slug a partir de uma string
 * 
 * @param string $input Texto para gerar o slug
 * @param string $separator Separador para o slug
 * @return string Slug gerado
 */
function gerarSlug($input, $separator = '-')
{
    $input = removerCaracteresEspeciais($input, false);
    $input = strtolower($input);
    $input = preg_replace('/\s+/', $separator, $input);
    $input = preg_replace('/[' . preg_quote($separator) . ']+/', $separator, $input);
    return trim($input, $separator);
}

/**
 * Limpa um nome de arquivo para upload
 * 
 * @param string $filename Nome do arquivo
 * @return string Nome do arquivo limpo
 */
function limparNomeArquivo($filename)
{
    $filename = sanitizarString($filename);
    $filename = removerCaracteresEspeciais($filename, false);
    $filename = strtolower($filename);
    $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '-', $filename);
    $filename = preg_replace('/\-+/', '-', $filename);
    return trim($filename, '-');
}

/**
 * Trunca um texto para um tamanho máximo
 * 
 * @param string $text Texto a ser truncado
 * @param int $length Tamanho máximo
 * @param string $suffix Sufixo para texto truncado
 * @param bool $escape Se deve escapar para HTML
 * @return string Texto truncado
 */
function truncarTexto($text, $length = 100, $suffix = '...', $escape = true)
{
    $text = sanitizarString($text);
    
    if (mb_strlen($text) <= $length) {
        return $escape ? escaparHTML($text) : $text;
    }
    
    $truncado = mb_substr($text, 0, $length) . $suffix;
    return $escape ? escaparHTML($truncado) : $truncado;
}

// ============================================================
// 7. SANITIZAÇÃO DE FILTROS E PARÂMETROS
// ============================================================

/**
 * Sanitiza parâmetros de filtro para relatórios
 * 
 * @param array $params Parâmetros do filtro
 * @return array Parâmetros sanitizados
 */
function sanitizarFiltros($params)
{
    $sanitized = [];
    
    // Aluno ID
    if (isset($params['aluno_id'])) {
        $sanitized['aluno_id'] = sanitizarInteiro($params['aluno_id'], null);
        if ($sanitized['aluno_id'] <= 0) {
            unset($sanitized['aluno_id']);
        }
    }
    
    // Passo
    if (isset($params['passo'])) {
        $passo = sanitizarInteiro($params['passo']);
        if ($passo >= 1 && $passo <= 4) {
            $sanitized['passo'] = $passo;
        }
    }
    
    // Dificuldade
    if (isset($params['dificuldade'])) {
        $dificuldade = sanitizarString($params['dificuldade']);
        if (in_array($dificuldade, ['facil', 'medio', 'dificil'])) {
            $sanitized['dificuldade'] = $dificuldade;
        }
    }
    
    // Data inicial
    if (isset($params['data_inicio']) && !empty($params['data_inicio'])) {
        $data = sanitizarString($params['data_inicio']);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            $sanitized['data_inicio'] = $data;
        }
    }
    
    // Data final
    if (isset($params['data_fim']) && !empty($params['data_fim'])) {
        $data = sanitizarString($params['data_fim']);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            $sanitized['data_fim'] = $data;
        }
    }
    
    return $sanitized;
}

// ============================================================
// 8. FUNÇÃO DE SANITIZAÇÃO DE RESPOSTA DE API
// ============================================================

/**
 * Sanitiza a resposta de uma API para garantir que seja segura
 * 
 * @param mixed $data Dados a serem sanitizados
 * @param bool $encode Se deve codificar como JSON
 * @return mixed Dados sanitizados
 */
function sanitizarRespostaAPI($data, $encode = true)
{
    $sanitized = sanitizarArray($data);
    if ($encode) {
        return json_encode($sanitized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    return $sanitized;
}