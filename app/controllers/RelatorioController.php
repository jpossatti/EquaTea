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
    private $equacao;
    private $db;
    
    public function __construct()
    {
        // Carrega os models necessários
        $base_dir = dirname(__DIR__);
        
        $models = ['RegistroErro.php', 'Aluno.php', 'Progresso.php', 'Equacao.php'];
        foreach ($models as $model_file) {
            $path = $base_dir . '/models/' . $model_file;
            if (file_exists($path)) {
                require_once $path;
            }
        }
        
        try {
            $this->db = Database::getInstance()->getConnection();
        } catch (Exception $e) {
            $this->db = null;
        }
        
        $this->registroErro = class_exists('RegistroErro') ? new RegistroErro() : null;
        $this->aluno = class_exists('Aluno') ? new Aluno() : null;
        $this->progresso = class_exists('Progresso') ? new Progresso() : null;
        $this->equacao = class_exists('Equacao') ? new Equacao() : null;
    }
    
    /**
     * Exibe o relatório de erros
     */
    public function relatorio()
    {
        // ===== CONTROLE DE SESSÃO =====
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id']) || ($_SESSION['tipo_perfil'] ?? '') !== 'professor') {
            $_SESSION['login_error'] = 'Acesso restrito a professores.';
            header('Location: index.php?view=login');
            exit;
        }
        // ===== FIM CONTROLE DE SESSÃO =====
        
        $aluno_id = isset($_GET['aluno']) ? (int)$_GET['aluno'] : null;
        $passo = isset($_GET['passo']) ? (int)$_GET['passo'] : null;
        
        // Busca todos os alunos para o filtro
        $dados_alunos = [];
        if ($this->aluno) {
            try {
                $dados_alunos = $this->aluno->listarTodos();
                if (!is_array($dados_alunos)) {
                    $dados_alunos = [];
                }
            } catch (Exception $e) {
                $dados_alunos = [];
            }
        }
        
        // Busca o relatório completo de erros
        $dados_relatorio = [];
        if ($this->registroErro) {
            try {
                $dados_relatorio = $this->registroErro->getRelatorioCompleto($aluno_id, $passo);
                if (!is_array($dados_relatorio)) {
                    $dados_relatorio = [];
                }
            } catch (Exception $e) {
                $dados_relatorio = [];
            }
        }
        
        // Busca erros comuns (para estatísticas)
        $erros_comuns = [];
        if ($this->registroErro) {
            try {
                $erros_comuns = $this->registroErro->getEstatisticas($aluno_id, $passo);
                if (!is_array($erros_comuns)) {
                    $erros_comuns = [];
                }
            } catch (Exception $e) {
                $erros_comuns = [];
            }
        }
        
        // Busca equações para estatísticas
        $dados_equacoes = [];
        if ($this->equacao) {
            try {
                $dados_equacoes = $this->equacao->buscarTodas();
                if (!is_array($dados_equacoes)) {
                    $dados_equacoes = [];
                }
            } catch (Exception $e) {
                $dados_equacoes = [];
            }
        }
        
        // Prepara os dados para a view
        $dados = [
            'dados_relatorio' => $dados_relatorio,
            'dados_alunos' => $dados_alunos,
            'erros_comuns' => $erros_comuns,
            'dados_equacoes' => $dados_equacoes,
            'filtro_aluno' => $aluno_id,
            'filtro_passo' => $passo
        ];
        
        // Define constantes para a view
        if (!defined('VIEWS_PATH')) {
            define('VIEWS_PATH', dirname(__DIR__) . '/views');
        }
        
        // Carrega a view
        $view_path = VIEWS_PATH . '/professor/relatorio.php';
        if (file_exists($view_path)) {
            extract($dados);
            include_once $view_path;
        } else {
            $alt_path = __DIR__ . '/../views/professor/relatorio.php';
            if (file_exists($alt_path)) {
                extract($dados);
                include_once $alt_path;
            } else {
                echo "<h2>Erro: View de Relatório não encontrada.</h2>";
            }
        }
    }
    
    /**
     * Exporta relatório em CSV
     */
    public function exportarCSV()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id']) || ($_SESSION['tipo_perfil'] ?? '') !== 'professor') {
            header('Location: index.php?view=login');
            exit;
        }
        
        $aluno_id = isset($_GET['aluno']) ? (int)$_GET['aluno'] : null;
        $passo = isset($_GET['passo']) ? (int)$_GET['passo'] : null;
        
        $dados = [];
        if ($this->registroErro) {
            try {
                $dados = $this->registroErro->getRelatorioCompleto($aluno_id, $passo);
            } catch (Exception $e) {
                $dados = [];
            }
        }
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=relatorio_erros_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");
        
        fputcsv($output, ['Aluno', 'Equação', 'Passo', 'Tipo de Erro', 'Resposta Fornecida', 'Resposta Esperada', 'Data'], ';');
        
        foreach ($dados as $linha) {
            fputcsv($output, [
                $linha['aluno'] ?? 'N/A',
                $linha['equacao'] ?? 'N/A',
                'Passo ' . ($linha['passo'] ?? '?'),
                $this->formatarTipoErro($linha['tipo_erro'] ?? 'outro'),
                $linha['resposta_fornecida'] ?? '-',
                $linha['resposta_esperada'] ?? '-',
                isset($linha['data_erro']) ? date('d/m/Y H:i', strtotime($linha['data_erro'])) : 'N/A'
            ], ';');
        }
        
        fclose($output);
        exit;
    }
    
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