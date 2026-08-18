<?php
/**
 * relatorio.php
 * Relatório de erros - Versão de teste
 * 
 * Acesso: ?view=relatorio
 */

$page_title = 'Relatório de Erros - EquaTEA';

include_once __DIR__ . '/../partials/header.php';
include_once __DIR__ . '/../partials/menu_professor.php';
?>
<main class="container relatorio-container">
    <h1>📈 Relatório de Erros</h1>

    <div class="filtros-section">
        <form method="GET" action="#" class="filtros-form">
            <div class="filtro-grupo">
                <label for="aluno">Aluno:</label>
                <select id="aluno">
                    <option value="">Todos os alunos</option>
                    <?php foreach ($dados_alunos as $a): ?>
                    <option value="<?php echo $a['aluno_id']; ?>"><?php echo htmlspecialchars($a['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filtro-grupo">
                <label for="passo">Passo:</label>
                <select id="passo">
                    <option value="">Todos os passos</option>
                    <option value="1">Passo 1</option>
                    <option value="2">Passo 2</option>
                    <option value="3">Passo 3</option>
                    <option value="4">Passo 4</option>
                </select>
            </div>
            <button type="submit" class="btn-filtrar">🔍 Filtrar</button>
            <button type="reset" class="btn-limpar">🔄 Limpar</button>
        </form>
    </div>

    <div class="relatorio-acoes">
        <button class="btn-exportar-csv">📥 Exportar CSV</button>
        <button class="btn-excel">📊 Exportar Excel</button>
        <button class="btn-imprimir">🖨️ Imprimir</button>
    </div>

    <div class="tabela-wrapper">
        <table class="tabela-relatorio">
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
                    <?php foreach (array_slice($dados_relatorio, 0, 20) as $r): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($r['aluno'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($r['equacao'] ?? 'N/A'); ?></td>
                        <td>Passo <?php echo $r['passo']; ?></td>
                        <td><span class="badge-tipo-erro <?php echo $r['tipo_erro']; ?>"><?php echo ucfirst(str_replace('_', ' ', $r['tipo_erro'] ?? 'outro')); ?></span></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($r['data_erro'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center; padding: 30px; color: #888;">Nenhum erro registrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="grafico-section">
        <h3>📊 Erros por Tipo</h3>
        <div class="grafico-barras">
            <?php
            $tipos = ['operacao_inversa' => 'Operação Inversa', 'calculo_errado' => 'Cálculo Errado', 'sinal_trocado' => 'Sinal Trocado', 'divisao_incorreta' => 'Divisão Incorreta'];
            $totais = [];
            foreach ($dados_relatorio as $r) {
                $tipo = $r['tipo_erro'] ?? 'outro';
                $totais[$tipo] = ($totais[$tipo] ?? 0) + 1;
            }
            $max = max($totais) ?: 1;
            foreach ($tipos as $key => $label):
                $count = $totais[$key] ?? 0;
                $percent = round(($count / $max) * 100);
            ?>
            <div class="grafico-item">
                <span class="label"><?php echo $label; ?></span>
                <div class="barra-wrapper">
                    <div class="barra" style="width: <?php echo $percent; ?>%;"></div>
                </div>
                <span class="valor"><?php echo $count; ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>
<style>
    .relatorio-container { padding: 20px 0; }
    .filtros-section { background: #fff; padding: 20px 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 20px; }
    .filtros-form { display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end; }
    .filtro-grupo { display: flex; flex-direction: column; gap: 4px; flex: 1; min-width: 150px; }
    .filtro-grupo label { font-weight: 500; font-size: 14px; color: #555; }
    .filtro-grupo select { padding: 10px 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 15px; }
    .btn-filtrar { padding: 10px 24px; background: #2c3e50; color: #fff; border: none; border-radius: 6px; cursor: pointer; }
    .btn-limpar { padding: 10px 20px; background: #f8f9fa; color: #555; border: 2px solid #ddd; border-radius: 6px; cursor: pointer; }
    .relatorio-acoes { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
    .btn-exportar-csv { padding: 10px 20px; background: #27ae60; color: #fff; border: none; border-radius: 6px; cursor: pointer; }
    .btn-excel { padding: 10px 20px; background: #1e7e34; color: #fff; border: none; border-radius: 6px; cursor: pointer; }
    .btn-imprimir { padding: 10px 20px; background: #f8f9fa; color: #555; border: 2px solid #ddd; border-radius: 6px; cursor: pointer; }
    .tabela-wrapper { overflow-x: auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    .tabela-relatorio { width: 100%; border-collapse: collapse; font-size: 14px; }
    .tabela-relatorio th { background: #f8f9fa; padding: 12px 16px; text-align: left; font-weight: 600; }
    .tabela-relatorio td { padding: 10px 16px; border-bottom: 1px solid #f1f3f5; }
    .badge-tipo-erro { display: inline-block; padding: 2px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; }
    .badge-tipo-erro.operacao_inversa { background: #fde8e8; color: #721c24; }
    .badge-tipo-erro.calculo_errado { background: #fff3cd; color: #856404; }
    .badge-tipo-erro.sinal_trocado { background: #cce5ff; color: #004085; }
    .badge-tipo-erro.divisao_incorreta { background: #d4edda; color: #155724; }
    .grafico-section { background: #fff; padding: 20px 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-top: 20px; }
    .grafico-barras { display: flex; flex-direction: column; gap: 12px; }
    .grafico-item { display: flex; align-items: center; gap: 12px; }
    .grafico-item .label { min-width: 130px; font-size: 14px; color: #555; }
    .grafico-item .barra-wrapper { flex: 1; height: 24px; background: #e9ecef; border-radius: 4px; overflow: hidden; }
    .grafico-item .barra { height: 100%; background: linear-gradient(90deg, #3498db, #2c3e50); border-radius: 4px; transition: width 1s ease; }
    .grafico-item .valor { min-width: 40px; font-weight: 600; color: #2c3e50; }
</style>
<?php include_once __DIR__ . '/../partials/footer.php'; ?>