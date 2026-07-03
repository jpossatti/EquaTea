<?php
session_start();
require_once '../../helpers/session_helper.php';

if (!estaLogado() || $_SESSION['tipo_perfil'] != 'professor') {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../../controllers/RelatorioController.php';

$controller = new RelatorioController();
$aluno_id = isset($_GET['aluno_id']) ? (int)$_GET['aluno_id'] : null;
$passo = isset($_GET['passo']) ? (int)$_GET['passo'] : null;

$dados = $controller->getRelatorio($aluno_id, $passo);
$progresso_alunos = $controller->getProgressoAlunos();

// Lista de alunos para o filtro
require_once '../../models/Aluno.php';
$aluno = new Aluno();
$alunos = $aluno->getAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Erros - EquaTEA</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/relatorio.css">
    <link rel="stylesheet" href="../../public/css/acessibilidade.css">
</head>
<body>
    <?php include '../partials/header.php'; ?>
    <?php include '../partials/menu_professor.php'; ?>
    
    <main class="container">
        <h1>Relatório de Erros</h1>
        
        <div class="filtros">
            <form method="GET" action="" class="filtros-form">
                <div class="form-group">
                    <label for="aluno_id">Aluno:</label>
                    <select name="aluno_id" id="aluno_id">
                        <option value="">Todos os alunos</option>
                        <?php foreach ($alunos as $a): ?>
                            <option value="<?= $a['id'] ?>" <?= $aluno_id == $a['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($a['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="passo">Passo:</label>
                    <select name="passo" id="passo">
                        <option value="">Todos os passos</option>
                        <option value="1" <?= $passo == 1 ? 'selected' : '' ?>>Passo 1</option>
                        <option value="2" <?= $passo == 2 ? 'selected' : '' ?>>Passo 2</option>
                        <option value="3" <?= $passo == 3 ? 'selected' : '' ?>>Passo 3</option>
                        <option value="4" <?= $passo == 4 ? 'selected' : '' ?>>Passo 4</option>
                    </select>
                </div>
                
                <button type="submit" class="btn-filtrar">Aplicar Filtros</button>
            </form>
        </div>
        
        <div class="relatorio-acoes">
            <button class="btn-exportar" onclick="exportarCSV()">Exportar CSV</button>
        </div>
        
        <div class="relatorio-tabela">
            <table>
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Passo</th>
                        <th>Tipo de Erro</th>
                        <th>Quantidade</th>
                        <th>Última Ocorrência</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dados)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">Nenhum erro registrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($dados as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['aluno'] ?? 'N/A') ?></td>
                                <td>Passo <?= $row['passo'] ?></td>
                                <td><?= ucfirst(str_replace('_', ' ', $row['tipo_erro'])) ?></td>
                                <td><?= $row['quantidade'] ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($row['data_erro'] ?? 'now')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="progresso-alunos">
            <h2>Progresso dos Alunos</h2>
            <table>
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Total de Equações</th>
                        <th>Concluídas</th>
                        <th>Taxa de Conclusão</th>
                        <th>Média de Tentativas</th>
                        <th>Total de Erros</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($progresso_alunos as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['aluno']) ?></td>
                            <td><?= $row['total_equacoes'] ?></td>
                            <td><?= $row['concluidas'] ?></td>
                            <td>
                                <?php 
                                $taxa = $row['total_equacoes'] > 0 ? 
                                    round(($row['concluidas'] / $row['total_equacoes']) * 100, 1) : 0;
                                echo $taxa . '%';
                                ?>
                            </td>
                            <td><?= $row['media_tentativas'] ?? 0 ?></td>
                            <td><?= $row['total_erros'] ?? 0 ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
    
    <?php include '../partials/footer.php'; ?>
    
    <script>
        function exportarCSV() {
            const params = new URLSearchParams(window.location.search);
            window.location.href = '../../controllers/RelatorioController.php?action=exportar&' + params.toString();
        }
    </script>
    <script src="../../public/js/relatorio.js"></script>
</body>
</html>