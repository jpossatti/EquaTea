<?php
/**
 * ============================================================
 * ExportarCSV.php
 * Serviço responsável pela exportação de relatórios em CSV.
 * 
 * FUNCIONALIDADES:
 * - Exportação de dados de alunos
 * - Exportação de dados de progresso
 * - Exportação de dados de erros
 * - Exportação de dados de equações
 * - Formatação para Excel (UTF-8 com BOM)
 * 
 * @package EquaTEA
 * @subpackage Services
 * @author Equipe EquaTEA
 * @version 1.0
 * ============================================================
 */

// ============================================================
// CARREGAMENTO DE DEPENDÊNCIAS
// ============================================================

require_once MODELS_PATH . '/Aluno.php';
require_once MODELS_PATH . '/Equacao.php';
require_once MODELS_PATH . '/Progresso.php';
require_once MODELS_PATH . '/RegistroErro.php';
require_once MODELS_PATH . '/Usuario.php';

/**
 * Class ExportarCSV
 * 
 * Gerencia a exportação de dados do sistema para arquivos CSV,
 * com suporte a diferentes tipos de relatórios e formatação
 * compatível com Excel.
 * 
 * @author Equipe EquaTEA
 */
class ExportarCSV
{
    /**
     * @var object Conexão com o banco de dados
     */
    private $db;
    
    /**
     * @var array Headers para cada tipo de relatório
     */
    private $headers = [];
    
    /**
     * @var array Dados a serem exportados
     */
    private $dados = [];
    
    /**
     * @var string Nome do arquivo a ser gerado
     */
    private $nome_arquivo = '';

    /**
     * Construtor da classe
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ============================================================
    // MÉTODOS PRINCIPAIS DE EXPORTAÇÃO
    // ============================================================

    /**
     * Exporta dados de alunos para CSV
     * 
     * @param int|null $aluno_id ID específico do aluno (opcional)
     * @param bool $download Se deve forçar o download
     * @return string|bool Conteúdo CSV ou true se download iniciado
     */
    public function exportarAlunos($aluno_id = null, $download = true)
    {
        // ============================================================
        // 1. BUSCAR DADOS DOS ALUNOS
        // ============================================================
        
        $sql = "SELECT 
                    u.id as usuario_id,
                    u.nome,
                    u.email,
                    u.data_cadastro,
                    u.ultimo_acesso,
                    u.ativo,
                    a.id as aluno_id,
                    a.idade,
                    a.nivel_tea,
                    a.escola,
                    a.turma,
                    (SELECT COUNT(*) FROM progresso_aluno WHERE aluno_id = a.id) as total_equacoes,
                    (SELECT SUM(concluida) FROM progresso_aluno WHERE aluno_id = a.id) as equacoes_concluidas,
                    (SELECT COUNT(*) FROM registro_erros WHERE aluno_id = a.id) as total_erros
                FROM usuarios u
                JOIN alunos a ON u.id = a.usuario_id
                WHERE u.tipo_perfil = 'aluno'";
        
        if ($aluno_id) {
            $sql .= " AND a.id = :aluno_id";
            $params = [':aluno_id' => $aluno_id];
        } else {
            $params = [];
        }
        
        $sql .= " ORDER BY u.nome ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $dados = $stmt->fetchAll();
        
        // ============================================================
        // 2. FORMATAR DADOS PARA O CSV
        // ============================================================
        
        $this->headers = [
            'ID', 'Nome', 'Email', 'Idade', 'Nível TEA', 'Escola', 'Turma',
            'Data Cadastro', 'Último Acesso', 'Status', 'Total Equações',
            'Equações Concluídas', 'Taxa Conclusão', 'Total Erros'
        ];
        
        $this->dados = [];
        foreach ($dados as $row) {
            $taxa = $row['total_equacoes'] > 0 
                ? round(($row['equacoes_concluidas'] / $row['total_equacoes']) * 100, 1) 
                : 0;
            
            $this->dados[] = [
                $row['aluno_id'],
                $row['nome'],
                $row['email'],
                $row['idade'],
                $row['nivel_tea'] == 'suporte1' ? 'Suporte 1' : 'Suporte 2',
                $row['escola'] ?? '',
                $row['turma'] ?? '',
                date('d/m/Y', strtotime($row['data_cadastro'])),
                $row['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($row['ultimo_acesso'])) : 'Nunca',
                $row['ativo'] ? 'Ativo' : 'Inativo',
                $row['total_equacoes'] ?? 0,
                $row['equacoes_concluidas'] ?? 0,
                $taxa . '%',
                $row['total_erros'] ?? 0
            ];
        }
        
        $this->nome_arquivo = 'relatorio_alunos_' . date('Y-m-d');
        
        return $this->gerarCSV($download);
    }

    /**
     * Exporta dados de progresso para CSV
     * 
     * @param int|null $aluno_id ID específico do aluno (opcional)
     * @param bool $download Se deve forçar o download
     * @return string|bool Conteúdo CSV ou true se download iniciado
     */
    public function exportarProgresso($aluno_id = null, $download = true)
    {
        // ============================================================
        // 1. BUSCAR DADOS DE PROGRESSO
        // ============================================================
        
        $sql = "SELECT 
                    u.nome as aluno,
                    CONCAT(e.a, 'x + ', e.b, ' = ', e.c) as equacao,
                    e.dificuldade,
                    p.passo_atual,
                    p.concluida,
                    p.tentativas,
                    p.data_inicio,
                    p.data_conclusao,
                    a.id as aluno_id
                FROM progresso_aluno p
                JOIN alunos a ON p.aluno_id = a.id
                JOIN usuarios u ON a.usuario_id = u.id
                JOIN equacoes e ON p.equacao_id = e.id";
        
        if ($aluno_id) {
            $sql .= " WHERE a.id = :aluno_id";
            $params = [':aluno_id' => $aluno_id];
        } else {
            $params = [];
        }
        
        $sql .= " ORDER BY u.nome, p.data_inicio DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $dados = $stmt->fetchAll();
        
        // ============================================================
        // 2. FORMATAR DADOS PARA O CSV
        // ============================================================
        
        $this->headers = [
            'Aluno', 'Equação', 'Dificuldade', 'Passo Atual', 'Concluída',
            'Tentativas', 'Data Início', 'Data Conclusão', 'Tempo Total'
        ];
        
        $this->dados = [];
        foreach ($dados as $row) {
            // Calcular tempo total
            $tempo = '-';
            if ($row['data_inicio'] && $row['data_conclusao']) {
                $inicio = new DateTime($row['data_inicio']);
                $conclusao = new DateTime($row['data_conclusao']);
                $diff = $inicio->diff($conclusao);
                $tempo = $diff->i . 'min ' . $diff->s . 's';
            }
            
            $this->dados[] = [
                $row['aluno'],
                $row['equacao'],
                ucfirst($row['dificuldade']),
                'Passo ' . $row['passo_atual'] . '/4',
                $row['concluida'] ? 'Sim' : 'Não',
                $row['tentativas'] ?? 0,
                date('d/m/Y H:i', strtotime($row['data_inicio'])),
                $row['data_conclusao'] ? date('d/m/Y H:i', strtotime($row['data_conclusao'])) : '-',
                $tempo
            ];
        }
        
        $this->nome_arquivo = 'relatorio_progresso_' . date('Y-m-d');
        
        return $this->gerarCSV($download);
    }

    /**
     * Exporta dados de erros para CSV
     * 
     * @param int|null $aluno_id ID específico do aluno (opcional)
     * @param int|null $passo Passo específico (opcional)
     * @param bool $download Se deve forçar o download
     * @return string|bool Conteúdo CSV ou true se download iniciado
     */
    public function exportarErros($aluno_id = null, $passo = null, $download = true)
    {
        // ============================================================
        // 1. BUSCAR DADOS DE ERROS
        // ============================================================
        
        $sql = "SELECT 
                    u.nome as aluno,
                    CONCAT(e.a, 'x + ', e.b, ' = ', e.c) as equacao,
                    r.passo,
                    r.tipo_erro,
                    r.resposta_fornecida,
                    r.resposta_esperada,
                    r.data_erro,
                    a.id as aluno_id
                FROM registro_erros r
                JOIN alunos a ON r.aluno_id = a.id
                JOIN usuarios u ON a.usuario_id = u.id
                JOIN equacoes e ON r.equacao_id = e.id";
        
        $where = [];
        $params = [];
        
        if ($aluno_id) {
            $where[] = "a.id = :aluno_id";
            $params[':aluno_id'] = $aluno_id;
        }
        
        if ($passo) {
            $where[] = "r.passo = :passo";
            $params[':passo'] = $passo;
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " ORDER BY r.data_erro DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $dados = $stmt->fetchAll();
        
        // ============================================================
        // 2. FORMATAR DADOS PARA O CSV
        // ============================================================
        
        $this->headers = [
            'Aluno', 'Equação', 'Passo', 'Tipo de Erro',
            'Resposta Fornecida', 'Resposta Esperada', 'Data do Erro'
        ];
        
        $this->dados = [];
        foreach ($dados as $row) {
            $this->dados[] = [
                $row['aluno'],
                $row['equacao'],
                'Passo ' . $row['passo'],
                $this->formatarTipoErro($row['tipo_erro']),
                $row['resposta_fornecida'] ?? '-',
                $row['resposta_esperada'] ?? '-',
                date('d/m/Y H:i', strtotime($row['data_erro']))
            ];
        }
        
        $this->nome_arquivo = 'relatorio_erros_' . date('Y-m-d');
        
        return $this->gerarCSV($download);
    }

    /**
     * Exporta dados de equações para CSV
     * 
     * @param string|null $dificuldade Dificuldade específica (opcional)
     * @param bool $download Se deve forçar o download
     * @return string|bool Conteúdo CSV ou true se download iniciado
     */
    public function exportarEquacoes($dificuldade = null, $download = true)
    {
        // ============================================================
        // 1. BUSCAR DADOS DE EQUAÇÕES
        // ============================================================
        
        $sql = "SELECT 
                    id,
                    a,
                    b,
                    c,
                    solucao,
                    dificuldade,
                    data_cadastro,
                    (SELECT COUNT(*) FROM progresso_aluno WHERE equacao_id = e.id) as utilizada
                FROM equacoes e";
        
        if ($dificuldade) {
            $sql .= " WHERE dificuldade = :dificuldade";
            $params = [':dificuldade' => $dificuldade];
        } else {
            $params = [];
        }
        
        $sql .= " ORDER BY FIELD(dificuldade, 'facil', 'medio', 'dificil'), a, b, c";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $dados = $stmt->fetchAll();
        
        // ============================================================
        // 2. FORMATAR DADOS PARA O CSV
        // ============================================================
        
        $this->headers = [
            'ID', 'Equação', 'Solução', 'Dificuldade', 'Data Cadastro', 'Utilizada'
        ];
        
        $this->dados = [];
        foreach ($dados as $row) {
            $sinal = $row['b'] >= 0 ? '+' : '-';
            $equacao = "{$row['a']}x {$sinal} " . abs($row['b']) . " = {$row['c']}";
            
            $this->dados[] = [
                $row['id'],
                $equacao,
                'x = ' . $row['solucao'],
                ucfirst($row['dificuldade']),
                date('d/m/Y', strtotime($row['data_cadastro'])),
                $row['utilizada'] > 0 ? 'Sim' : 'Não'
            ];
        }
        
        $this->nome_arquivo = 'relatorio_equacoes_' . date('Y-m-d');
        
        return $this->gerarCSV($download);
    }

    // ============================================================
    // MÉTODOS DE GERAÇÃO DE CSV
    // ============================================================

    /**
     * Gera o arquivo CSV
     * 
     * @param bool $download Se deve forçar o download
     * @return string|bool Conteúdo CSV ou true se download iniciado
     */
    private function gerarCSV($download = true)
    {
        // ============================================================
        // 1. ABRIR STREAM DE SAÍDA
        // ============================================================
        
        if ($download) {
            // Força o download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $this->nome_arquivo . '.csv"');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            $output = fopen('php://output', 'w');
        } else {
            // Retorna como string
            $output = fopen('php://memory', 'r+');
        }
        
        // ============================================================
        // 2. ESCREVER BOM PARA UTF-8 (compatibilidade com Excel)
        // ============================================================
        
        fwrite($output, "\xEF\xBB\xBF");
        
        // ============================================================
        // 3. ESCREVER HEADERS
        // ============================================================
        
        fputcsv($output, $this->headers, ';');
        
        // ============================================================
        // 4. ESCREVER DADOS
        // ============================================================
        
        foreach ($this->dados as $linha) {
            // Converte arrays para string, se necessário
            $linha_formatada = array_map(function($valor) {
                if (is_array($valor)) {
                    return implode('; ', $valor);
                }
                return $valor;
            }, $linha);
            
            fputcsv($output, $linha_formatada, ';');
        }
        
        // ============================================================
        // 5. RETORNAR RESULTADO
        // ============================================================
        
        if (!$download) {
            rewind($output);
            $conteudo = stream_get_contents($output);
            fclose($output);
            return $conteudo;
        }
        
        fclose($output);
        return true;
    }

    // ============================================================
    // MÉTODOS AUXILIARES
    // ============================================================

    /**
     * Formata o tipo de erro para exibição
     * 
     * @param string $tipo_erro Tipo de erro
     * @return string Tipo de erro formatado
     */
    private function formatarTipoErro($tipo_erro)
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

    /**
     * Gera um nome de arquivo com timestamp
     * 
     * @param string $prefix Prefixo do arquivo
     * @return string Nome do arquivo
     */
    public function gerarNomeArquivo($prefix = 'relatorio')
    {
        return $prefix . '_' . date('Y-m-d_H-i-s') . '.csv';
    }

    /**
     * Define os dados manualmente (para uso em outros contextos)
     * 
     * @param array $headers Headers do CSV
     * @param array $dados Dados para exportar
     * @param string $nome_arquivo Nome do arquivo
     */
    public function definirDados($headers, $dados, $nome_arquivo = null)
    {
        $this->headers = $headers;
        $this->dados = $dados;
        $this->nome_arquivo = $nome_arquivo ?? 'relatorio_' . date('Y-m-d');
    }

    /**
     * Exporta dados personalizados
     * 
     * @param bool $download Se deve forçar o download
     * @return string|bool Conteúdo CSV ou true se download iniciado
     */
    public function exportarPersonalizado($download = true)
    {
        if (empty($this->headers) || empty($this->dados)) {
            return false;
        }
        return $this->gerarCSV($download);
    }
}