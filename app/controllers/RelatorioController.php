<?php
/**
 * RelatorioController.php
 * Controlador para relatórios do professor.
 */
class RelatorioController
{
    private $registroErro;
    private $aluno;
    private $progresso;
    
    public function __construct()
    {
        $this->registroErro = new RegistroErro();
        $this->aluno = new Aluno();
        $this->progresso = new Progresso();
    }
    
    /**
     * Exibe o relatório de erros
     */
    public function relatorio()
    {
        $aluno_id = isset($_GET['aluno_id']) ? (int)$_GET['aluno_id'] : null;
        $passo = isset($_GET['passo']) ? (int)$_GET['passo'] : null;
        
        $dados = [
            'erros' => $this->registroErro->getRelatorioCompleto($aluno_id, $passo),
            'alunos' => $this->aluno->getAll(),
            'filtro_aluno' => $aluno_id,
            'filtro_passo' => $passo,
            'estatisticas' => $this->registroErro->getEstatisticas($aluno_id, $passo)
        ];
        
        include_once VIEWS_PATH . '/professor/relatorio.php';
    }
    
    /**
     * Exporta relatório em CSV
     */
    public function exportarCSV()
    {
        $aluno_id = isset($_GET['aluno_id']) ? (int)$_GET['aluno_id'] : null;
        $passo = isset($_GET['passo']) ? (int)$_GET['passo'] : null;
        
        $dados = $this->registroErro->getRelatorioCompleto($aluno_id, $passo);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=relatorio_erros_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF"); // BOM para UTF-8
        
        // Headers
        fputcsv($output, ['Aluno', 'Equação', 'Passo', 'Tipo de Erro', 'Resposta Fornecida', 'Resposta Esperada', 'Data'], ';');
        
        // Dados
        foreach ($dados as $linha) {
            fputcsv($output, [
                $linha['aluno'],
                $linha['equacao'],
                'Passo ' . $linha['passo'],
                $this->formatarTipoErro($linha['tipo_erro']),
                $linha['resposta_fornecida'] ?? '-',
                $linha['resposta_esperada'] ?? '-',
                date('d/m/Y H:i', strtotime($linha['data_erro']))
            ], ';');
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Formata o tipo de erro
     */
    private function formatarTipoErro($tipo)
    {
        $labels = [
            'operacao_inversa' => 'Operação Inversa',
            'calculo_errado' => 'Cálculo Errado',
            'sinal_trocado' => 'Sinal Trocado',
            'divisao_incorreta' => 'Divisão Incorreta',
            'identificacao_errada' => 'Identificação Errada',
            'outro' => 'Outro'
        ];
        
        return $labels[$tipo] ?? $tipo;
    }
}