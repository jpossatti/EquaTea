<?php
/**
 * relatorio.php
 * Relatório de erros dos alunos
 */

$page_title = 'Relatório de Erros - EquaTEA';

// Filtros recebidos via GET
$filtro_aluno = $_GET['aluno'] ?? '';
$filtro_passo = $_GET['passo'] ?? '';

include_once __DIR__ . '/../partials/header.php';
include_once __DIR__ . '/../partials/menu_professor.php';
?>

<main class="container relatorio-container">
    <h1>📈 Relatório de Erros</h1>

    <!-- Filtros -->
    <div class="filtros-section">
        <form method="GET" action="" class="filtros-form">
            <input type="hidden" name="view" value="relatorio">
            <div class="filtro-grupo">
                <label for="aluno">Aluno:</label>
                <select id="aluno" name="aluno">
                    <option value="">Todos os alunos</option>
                    <?php if (!empty($dados_alunos)): ?>
                        <?php foreach ($dados_alunos as $a): ?>
                            <option value="<?php echo $a['aluno_id']; ?>" <?php echo $filtro_aluno == $a['aluno_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($a['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="filtro-grupo">
                <label for="passo">Passo:</label>
                <select id="passo" name="passo">
                    <option value="">Todos os passos</option>
                    <option value="1" <?php echo $filtro_passo == '1' ? 'selected' : ''; ?>>Passo 1</option>
                    <option value="2" <?php echo $filtro_passo == '2' ? 'selected' : ''; ?>>Passo 2</option>
                    <option value="3" <?php echo $filtro_passo == '3' ? 'selected' : ''; ?>>Passo 3</option>
                    <option value="4" <?php echo $filtro_passo == '4' ? 'selected' : ''; ?>>Passo 4</option>
                </select>
            </div>
            <button type="submit" class="btn-filtrar">🔍 Filtrar</button>
            <a href="?view=relatorio" class="btn-limpar">🔄 Limpar</a>
        </form>
    </div>

    <!-- Botões de Ação -->
    <div class="relatorio-acoes">
        <button class="btn-exportar-csv" onclick="exportarCSV()">📥 Exportar CSV</button>
        <button class="btn-excel" onclick="exportarExcel()">📊 Exportar Excel</button>
        <button class="btn-imprimir" onclick="window.print()">🖨️ Imprimir</button>
    </div>

    <!-- Tabela de Relatório -->
    <div class="tabela-wrapper">
        <table class="tabela-relatorio" id="tabela-relatorio">
            <thead>
                <tr>
                    <th>Aluno</th>
                    <th>Equação</th>
                    <th>Passo</th>
                    <th>Tipo de Erro</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($dados_relatorio)): ?>
                    <?php foreach ($dados_relatorio as $r): ?>
                        <?php 
                        // Aplica filtros
                        $show = true;
                        if ($filtro_aluno && isset($r['aluno_id']) && $r['aluno_id'] != $filtro_aluno) {
                            $show = false;
                        }
                        if ($filtro_passo && isset($r['passo']) && $r['passo'] != $filtro_passo) {
                            $show = false;
                        }
                        if (!$show) continue;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['aluno'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($r['equacao'] ?? 'N/A'); ?></td>
                            <td>Passo <?php echo $r['passo'] ?? '?'; ?></td>
                            <td>
                                <span class="badge-tipo-erro <?php echo $r['tipo_erro'] ?? 'outro'; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $r['tipo_erro'] ?? 'Outro')); ?>
                                </span>
                            </td>
                            <td><?php echo isset($r['data_erro']) ? date('d/m/Y H:i', strtotime($r['data_erro'])) : 'N/A'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 40px; color: #888;">
                            <p style="font-size: 16px;">📭 Nenhum erro registrado até o momento.</p>
                            <p style="font-size: 14px;">Os erros cometidos pelos alunos aparecerão aqui automaticamente.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Gráfico de Erros por Tipo -->
    <?php if (!empty($dados_relatorio)): ?>
        <div class="grafico-section">
            <h3>📊 Erros por Tipo</h3>
            <div class="grafico-barras">
                <?php
                $tipos = [
                    'operacao_inversa' => 'Operação Inversa',
                    'calculo_errado' => 'Cálculo Errado',
                    'sinal_trocado' => 'Sinal Trocado',
                    'divisao_incorreta' => 'Divisão Incorreta',
                    'outro' => 'Outros'
                ];
                $totais = [];
                foreach ($dados_relatorio as $r) {
                    $tipo = $r['tipo_erro'] ?? 'outro';
                    $totais[$tipo] = ($totais[$tipo] ?? 0) + 1;
                }
                $max = max($totais) ?: 1;
                $cores = [
                    'operacao_inversa' => '#e74c3c',
                    'calculo_errado' => '#f39c12',
                    'sinal_trocado' => '#3498db',
                    'divisao_incorreta' => '#2ecc71',
                    'outro' => '#95a5a6'
                ];
                ?>
                <?php foreach ($tipos as $key => $label): 
                    $count = $totais[$key] ?? 0;
                    $percent = $count > 0 ? round(($count / $max) * 100) : 0;
                    $cor = $cores[$key] ?? '#95a5a6';
                ?>
                    <div class="grafico-item">
                        <span class="label"><?php echo $label; ?></span>
                        <div class="barra-wrapper">
                            <div class="barra" style="width: <?php echo $percent; ?>%; background: <?php echo $cor; ?>;"></div>
                        </div>
                        <span class="valor"><?php echo $count; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Resumo -->
            <div class="resumo-erros">
                <span>Total de erros: <strong><?php echo count($dados_relatorio); ?></strong></span>
                <span>Alunos com erros: <strong><?php echo count(array_unique(array_column($dados_relatorio, 'aluno_id' ?? 'aluno'))); ?></strong></span>
            </div>
        </div>
    <?php endif; ?>
</main>

<script>
    function exportarCSV() {
        const tabela = document.getElementById('tabela-relatorio');
        let csv = [];
        // Cabeçalhos
        const headers = ['Aluno', 'Equação', 'Passo', 'Tipo de Erro', 'Data'];
        csv.push(headers.join(','));
        
        // Dados
        const rows = tabela.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length > 0) {
                const rowData = [];
                cells.forEach(cell => {
                    let text = cell.textContent.trim();
                    // Remove a palavra "Passo" do número
                    if (text.includes('Passo')) {
                        text = text.replace('Passo ', '');
                    }
                    // Remove quebras de linha
                    text = text.replace(/\n/g, ' ');
                    rowData.push(text);
                });
                csv.push(rowData.join(','));
            }
        });
        
        // Download
        const blob = new Blob(['\uFEFF' + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'relatorio_erros_' + new Date().toISOString().slice(0,10) + '.csv';
        link.click();
    }
    
    function exportarExcel() {
        const tabela = document.getElementById('tabela-relatorio');
        let html = '<html><head><meta charset="UTF-8"><title>Relatório de Erros</title></head><body>';
        html += '<h1>Relatório de Erros - EquaTEA</h1>';
        html += '<p>Gerado em: ' + new Date().toLocaleString() + '</p>';
        html += tabela.outerHTML;
        html += '</body></html>';
        
        const blob = new Blob(['\uFEFF' + html], { type: 'application/vnd.ms-excel;charset=utf-8' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'relatorio_erros_' + new Date().toISOString().slice(0,10) + '.xls';
        link.click();
    }
</script>

<style>
    .relatorio-container { 
        padding: 20px 0; 
    }
    
    .relatorio-container h1 {
        margin-bottom: 20px;
        color: #2c3e50;
    }
    
    .filtros-section { 
        background: #fff; 
        padding: 20px 24px; 
        border-radius: 12px; 
        box-shadow: 0 2px 8px rgba(0,0,0,0.06); 
        margin-bottom: 20px; 
    }
    
    .filtros-form { 
        display: flex; 
        flex-wrap: wrap; 
        gap: 16px; 
        align-items: flex-end; 
    }
    
    .filtro-grupo { 
        display: flex; 
        flex-direction: column; 
        gap: 4px; 
        flex: 1; 
        min-width: 150px; 
    }
    
    .filtro-grupo label { 
        font-weight: 500; 
        font-size: 14px; 
        color: #555; 
    }
    
    .filtro-grupo select { 
        padding: 10px 12px; 
        border: 2px solid #ddd; 
        border-radius: 6px; 
        font-size: 15px; 
        background: #fff;
    }
    
    .btn-filtrar { 
        padding: 10px 24px; 
        background: #2c3e50; 
        color: #fff; 
        border: none; 
        border-radius: 6px; 
        cursor: pointer; 
        font-weight: 500;
        transition: background 0.2s;
    }
    
    .btn-filtrar:hover {
        background: #1a252f;
    }
    
    .btn-limpar { 
        padding: 10px 20px; 
        background: #f8f9fa; 
        color: #555; 
        border: 2px solid #ddd; 
        border-radius: 6px; 
        cursor: pointer; 
        text-decoration: none;
        display: inline-block;
        transition: background 0.2s;
    }
    
    .btn-limpar:hover {
        background: #e9ecef;
    }
    
    .relatorio-acoes { 
        display: flex; 
        gap: 12px; 
        flex-wrap: wrap; 
        margin-bottom: 16px; 
    }
    
    .btn-exportar-csv { 
        padding: 10px 20px; 
        background: #27ae60; 
        color: #fff; 
        border: none; 
        border-radius: 6px; 
        cursor: pointer; 
        font-weight: 500;
        transition: background 0.2s;
    }
    
    .btn-exportar-csv:hover {
        background: #219a52;
    }
    
    .btn-excel { 
        padding: 10px 20px; 
        background: #1e7e34; 
        color: #fff; 
        border: none; 
        border-radius: 6px; 
        cursor: pointer; 
        font-weight: 500;
        transition: background 0.2s;
    }
    
    .btn-excel:hover {
        background: #186a2b;
    }
    
    .btn-imprimir { 
        padding: 10px 20px; 
        background: #f8f9fa; 
        color: #555; 
        border: 2px solid #ddd; 
        border-radius: 6px; 
        cursor: pointer; 
        font-weight: 500;
        transition: background 0.2s;
    }
    
    .btn-imprimir:hover {
        background: #e9ecef;
    }
    
    .tabela-wrapper { 
        overflow-x: auto; 
        background: #fff; 
        border-radius: 12px; 
        box-shadow: 0 2px 8px rgba(0,0,0,0.06); 
    }
    
    .tabela-relatorio { 
        width: 100%; 
        border-collapse: collapse; 
        font-size: 14px; 
    }
    
    .tabela-relatorio th { 
        background: #f8f9fa; 
        padding: 12px 16px; 
        text-align: left; 
        font-weight: 600; 
        color: #495057;
        border-bottom: 2px solid #dee2e6;
    }
    
    .tabela-relatorio td { 
        padding: 10px 16px; 
        border-bottom: 1px solid #f1f3f5; 
        color: #495057;
    }
    
    .tabela-relatorio tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .badge-tipo-erro { 
        display: inline-block; 
        padding: 4px 12px; 
        border-radius: 12px; 
        font-size: 12px; 
        font-weight: 500; 
    }
    
    .badge-tipo-erro.operacao_inversa { 
        background: #fde8e8; 
        color: #721c24; 
    }
    
    .badge-tipo-erro.calculo_errado { 
        background: #fff3cd; 
        color: #856404; 
    }
    
    .badge-tipo-erro.sinal_trocado { 
        background: #cce5ff; 
        color: #004085; 
    }
    
    .badge-tipo-erro.divisao_incorreta { 
        background: #d4edda; 
        color: #155724; 
    }
    
    .badge-tipo-erro.outro { 
        background: #e9ecef; 
        color: #6c757d; 
    }
    
    .grafico-section { 
        background: #fff; 
        padding: 20px 24px; 
        border-radius: 12px; 
        box-shadow: 0 2px 8px rgba(0,0,0,0.06); 
        margin-top: 20px; 
    }
    
    .grafico-section h3 {
        margin-top: 0;
        margin-bottom: 16px;
        color: #2c3e50;
    }
    
    .grafico-barras { 
        display: flex; 
        flex-direction: column; 
        gap: 12px; 
    }
    
    .grafico-item { 
        display: flex; 
        align-items: center; 
        gap: 12px; 
    }
    
    .grafico-item .label { 
        min-width: 130px; 
        font-size: 14px; 
        color: #555; 
    }
    
    .grafico-item .barra-wrapper { 
        flex: 1; 
        height: 24px; 
        background: #e9ecef; 
        border-radius: 4px; 
        overflow: hidden; 
    }
    
    .grafico-item .barra { 
        height: 100%; 
        border-radius: 4px; 
        transition: width 1s ease; 
    }
    
    .grafico-item .valor { 
        min-width: 40px; 
        font-weight: 600; 
        color: #2c3e50; 
        text-align: right;
    }
    
    .resumo-erros {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid #e9ecef;
        display: flex;
        gap: 30px;
        font-size: 14px;
        color: #555;
    }
    
    .resumo-erros strong {
        color: #2c3e50;
    }
    
    @media (max-width: 768px) {
        .filtros-form {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filtro-grupo {
            min-width: 100%;
        }
        
        .relatorio-acoes {
            flex-direction: column;
        }
        
        .relatorio-acoes button {
            width: 100%;
        }
        
        .grafico-item {
            flex-wrap: wrap;
        }
        
        .grafico-item .label {
            min-width: 100%;
        }
        
        .resumo-erros {
            flex-direction: column;
            gap: 8px;
        }
    }
    
    @media print {
        .filtros-section,
        .relatorio-acoes,
        .btn-imprimir {
            display: none !important;
        }
        
        .tabela-wrapper {
            box-shadow: none;
            border: 1px solid #ddd;
        }
        
        .grafico-section {
            box-shadow: none;
            border: 1px solid #ddd;
            page-break-inside: avoid;
        }
    }
</style>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>