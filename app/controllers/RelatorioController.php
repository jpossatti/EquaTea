<?php
require_once CONTROLLERS_PATH . '/../models/RegistroErro.php';
require_once CONTROLLERS_PATH . '/../models/Progresso.php';
require_once CONTROLLERS_PATH . '/../models/Aluno.php';

class RelatorioController {
    private $registroErro;
    private $progresso;
    private $aluno;
    
    public function __construct() {
        $this->registroErro = new RegistroErro();
        $this->progresso = new Progresso();
        $this->aluno = new Aluno();
    }
    
    public function getRelatorio($aluno_id = null, $passo = null) {
        return $this->registroErro->getEstatisticas($aluno_id, $passo);
    }
    
    public function getProgressoAlunos() {
        // SQL para relatório completo
        $sql = "SELECT 
                    u.nome as aluno,
                    COUNT(DISTINCT p.equacao_id) as total_equacoes,
                    SUM(p.concluida) as concluidas,
                    ROUND(AVG(p.tentativas), 2) as media_tentativas,
                    (SELECT COUNT(*) FROM registro_erros re WHERE re.aluno_id = a.id) as total_erros
                FROM alunos a
                JOIN usuarios u ON a.usuario_id = u.id
                LEFT JOIN progresso_aluno p ON a.id = p.aluno_id
                GROUP BY a.id, u.nome
                ORDER BY u.nome";
        
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function exportarCSV($dados) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=relatorio_erros_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Aluno', 'Passo', 'Tipo de Erro', 'Quantidade', 'Data']);
        
        foreach ($dados as $linha) {
            fputcsv($output, $linha);
        }
        
        fclose($output);
        exit;
    }
}