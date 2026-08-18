<?php
/**
 * app/controllers/AlunoController.php
 * Controller do EquaTEA sem fallback fictício.
 */

if (file_exists(__DIR__ . '/../models/Equacao.php')) {
    require_once __DIR__ . '/../models/Equacao.php';
}

class AlunoController {

    public function dashboard() {
        $aluno = ['nome' => 'Aluno Teste', 'email' => 'aluno@equatea.com'];
        $dados_progresso = ['total_resolvidas' => 0, 'taxa_acerto' => '0%', 'nivel_atual' => 'Nível 1 - Básico'];
        
        $equacoes = [];
        if (class_exists('Equacao')) {
            try {
                $equacaoModel = new Equacao();
                // Assumindo método getAll() ou listarTodas() no Model
                if (method_exists($equacaoModel, 'getAll')) {
                    $equacoes = $equacaoModel->getAll();
                }
            } catch (Throwable $e) {
                // Erro tratado na view se vazio
            }
        }

        if (file_exists(__DIR__ . '/../views/aluno/dashboard.php')) {
            require_once __DIR__ . '/../views/aluno/dashboard.php';
        } else {
            echo "<h1>Dashboard do Aluno</h1>";
        }
    }

    public function exercicio() {
        $equacaoId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 1;
        $passo     = filter_input(INPUT_GET, 'passo', FILTER_VALIDATE_INT) ?: 1;

        $equacao = $this->carregarEquacaoDoBanco($equacaoId);

        if (!$equacao) {
            $this->exibirErroBanco("A equação de ID {$equacaoId} não foi encontrada no banco de dados ou a conexão falhou.");
            return;
        }

        if (file_exists(__DIR__ . '/../views/aluno/exercicio.php')) {
            require_once __DIR__ . '/../views/aluno/exercicio.php';
        } else {
            echo "<h2>Arquivo exercicio.php não encontrado.</h2>";
        }
    }

    public function verificarResposta() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /index.php?view=exercicio');
            exit;
        }

        $equacaoId  = filter_input(INPUT_POST, 'equacao_id', FILTER_VALIDATE_INT) 
                   ?: (filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 1);

        $passoAtual = filter_input(INPUT_POST, 'passo_atual', FILTER_VALIDATE_INT) 
                   ?: (filter_input(INPUT_GET, 'passo', FILTER_VALIDATE_INT) ?: 1);

        $resposta   = trim($_POST['resposta'] ?? '');

        // Consulta obrigatória ao Banco de Dados (sem fallback)
        $equacao = $this->carregarEquacaoDoBanco($equacaoId);

        if (!$equacao) {
            $this->exibirErroBanco("Não foi possível carregar a equação ID {$equacaoId} do banco de dados para validar a resposta.");
            return;
        }

        $correto = $this->validarPasso($equacao, $passoAtual, $resposta);

        // =========================================================================
        // 🔍 PAINEL DE DEBUG EM TELA
        // =========================================================================
        $respLimpa = strtolower($resposta);
        $respLimpa = preg_replace('/[\s\x{200B}-\x{200D}\x{FEFF}]/u', '', $respLimpa);
        $respLimpa = str_replace(['–', '—', '−'], '-', $respLimpa);

        echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Debug EquaTEA</title></head>';
        echo '<body style="background:#181818; color:#00ff66; font-family:Consolas, monospace; padding:25px; line-height:1.5;">';
        echo '<h2>🔍 DIAGNÓSTICO DE VALIDAÇÃO (PASSO ' . $passoAtual . ')</h2>';
        echo '<hr style="border-color:#333">';

        echo '<h3>1. Dados Recebidos via $_POST:</h3><pre style="color:#f1fa8c; background:#222; padding:10px; border-radius:5px;">';
        print_r($_POST);
        echo '</pre>';

        echo '<h3>2. Processamento da Resposta:</h3>';
        echo '• <b>Equação ID (Banco):</b> ' . var_export($equacao['id'], true) . '<br>';
        echo '• <b>Coeficientes do Banco:</b> a=' . $equacao['a'] . ', b=' . $equacao['b'] . ', c=' . $equacao['c'] . '<br>';
        echo '• <b>Passo Atual:</b> ' . var_export($passoAtual, true) . '<br>';
        echo '• <b>Resposta (Bruta):</b> "' . htmlspecialchars($resposta) . '"<br>';
        echo '• <b>Resposta (Sanitizada):</b> "' . htmlspecialchars($respLimpa) . '"<br>';

        echo '<hr style="border-color:#333">';
        echo '<h3>3. Resultado da Análise:</h3>';

        if ($correto) {
            $proximoPasso = $passoAtual + 1;
            
            if ($passoAtual < 4) {
                $urlDestino = "/index.php?view=exercicio&id={$equacaoId}&passo={$proximoPasso}&status=correto";
                $textoBotao = "➡️ Ir para o Passo {$proximoPasso}";
            } else {
                $urlDestino = "/index.php?view=parabens&id={$equacaoId}";
                $textoBotao = "🎉 Ver Tela de Parabéns";
            }

            echo '<h1 style="color:#50fa7b; margin:10px 0;">✅ VALIDAÇÃO: CORRETO (TRUE)</h1>';
            echo '<p style="color:#8be9fd;">Sua resposta foi validada com sucesso!</p>';
            echo '<br><a href="' . $urlDestino . '" style="background:#28a745; color:#fff; padding:12px 20px; text-decoration:none; border-radius:4px; font-weight:bold; font-size:16px;">' . $textoBotao . '</a>';

        } else {
            $urlRefazer = "/index.php?view=exercicio&id={$equacaoId}&passo={$passoAtual}&status=erro";
            
            echo '<h1 style="color:#ff5555; margin:10px 0;">❌ VALIDAÇÃO: INCORRETO (FALSE)</h1>';
            echo '<p style="color:#ff79c6;">Verifique a formatação da resposta e tente novamente.</p>';
            echo '<br><a href="' . $urlRefazer . '" style="background:#dc3545; color:#fff; padding:12px 20px; text-decoration:none; border-radius:4px; font-weight:bold; font-size:16px;">🔄 Tentar Novamente o Passo ' . $passoAtual . '</a>';
        }

        echo '</body></html>';
        exit;
    }

    private function carregarEquacaoDoBanco($id) {
        if (!class_exists('Equacao')) {
            return null;
        }

        try {
            $equacaoModel = new Equacao();
            $dados = $equacaoModel->getById($id);
            return $dados ?: null;
        } catch (Throwable $e) {
            return null;
        }
    }

    private function exibirErroBanco($mensagem) {
        echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Erro de Banco de Dados</title></head>';
        echo '<body style="background:#181818; color:#ff5555; font-family:Consolas, monospace; padding:25px;">';
        echo '<h2>❌ ERRO DE COMUNICAÇÃO / BANCO DE DADOS</h2>';
        echo '<hr style="border-color:#333">';
        echo '<p style="font-size:16px; color:#fff;">' . htmlspecialchars($mensagem) . '</p>';
        echo '<br><a href="/index.php" style="background:#6c757d; color:#fff; padding:10px 15px; text-decoration:none; border-radius:4px;">⬅️ Voltar ao Início</a>';
        echo '</body></html>';
        exit;
    }

    private function validarPasso($equacao, $passo, $resposta) {
        if ($resposta === '') {
            return false;
        }

        $resp = strtolower($resposta);
        $resp = preg_replace('/[\s\x{200B}-\x{200D}\x{FEFF}]/u', '', $resp);
        $resp = str_replace(['–', '—', '−'], '-', $resp);

        $a = (int)$equacao['a'];
        $b = (int)$equacao['b'];
        $c = (int)$equacao['c'];

        $termoX = ($a === 1) ? '(1?x)' : (($a === -1) ? '(-1?x)' : "({$a}x)");

        switch ($passo) {
            case 1:
                return preg_match('/^' . $termoX . '$/i', $resp) === 1;

            case 2:
                $bAbs = abs($b);
                $opOposta = ($b >= 0) ? '-' : '\+';
                return preg_match('/^(' . $termoX . '=)?' . $c . $opOposta . $bAbs . '$/i', $resp) === 1;

            case 3:
                $resultado = $c - $b;
                return preg_match('/^(' . $termoX . '=)?' . $resultado . '$/i', $resp) === 1;

            case 4:
                if ($a === 0) return false;
                $valorX = ($c - $b) / $a;
                return preg_match('/^(x=)?' . $valorX . '$/i', $resp) === 1;

            default:
                return false;
        }
    }
}