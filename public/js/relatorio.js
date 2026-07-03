/**
 * ============================================================
 * relatorio.js
 * JavaScript específico para a página de relatórios do professor.
 * 
 * FUNCIONALIDADES:
 * - Filtros dinâmicos
 * - Exportação de relatórios
 * - Gráficos interativos
 * - Ordenação de tabelas
 * - Impressão de relatórios
 * - Animações de carregamento
 * 
 * @package EquaTEA
 * @subpackage Public/JS
 * @author Equipe EquaTEA
 * @version 1.0
 * ============================================================
 */

// ============================================================
// 1. INICIALIZAÇÃO
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    inicializarFiltros();
    inicializarTabela();
    inicializarGraficos();
    inicializarBotoesExportacao();
    inicializarOrdenacao();
});

// ============================================================
// 2. FILTROS
// ============================================================

/**
 * Inicializa os filtros do relatório
 */
function inicializarFiltros() {
    const form = document.querySelector('.filtros-form');
    if (!form) return;
    
    // Filtro automático ao mudar selects
    const selects = form.querySelectorAll('select');
    selects.forEach(function(select) {
        select.addEventListener('change', function() {
            aplicarFiltros();
        });
    });
    
    // Filtro com debounce para inputs de texto
    const inputs = form.querySelectorAll('input[type="text"]');
    inputs.forEach(function(input) {
        let timeout;
        input.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                aplicarFiltros();
            }, 500);
        });
    });
}

/**
 * Aplica os filtros ao relatório
 */
function aplicarFiltros() {
    const form = document.querySelector('.filtros-form');
    if (!form) return;
    
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    // Montar parâmetros
    for (const [key, value] of formData.entries()) {
        if (value && value !== '') {
            params.append(key, value);
        }
    }
    
    // Redirecionar com os filtros
    const url = window.location.pathname + '?' + params.toString();
    window.location.href = url;
}

// ============================================================
// 3. TABELA
// ============================================================

/**
 * Inicializa a tabela de relatório
 */
function inicializarTabela() {
    const tabela = document.querySelector('.tabela-relatorio');
    if (!tabela) return;
    
    // Adicionar zebra striping (alternância de cores)
    const rows = tabela.querySelectorAll('tbody tr');
    rows.forEach(function(row, index) {
        if (index % 2 === 0) {
            row.classList.add('linha-par');
        } else {
            row.classList.add('linha-impar');
        }
    });
    
    // Animação de entrada das linhas
    rows.forEach(function(row, index) {
        row.style.opacity = '0';
        row.style.transform = 'translateX(-10px)';
        setTimeout(function() {
            row.style.transition = 'all 0.3s ease';
            row.style.opacity = '1';
            row.style.transform = 'translateX(0)';
        }, 50 + (index * 30));
    });
}

/**
 * Ordena a tabela por uma coluna
 * @param {number} coluna - Índice da coluna
 * @param {HTMLElement} header - Cabeçalho da coluna
 */
function ordenarTabela(coluna, header) {
    const tabela = document.querySelector('.tabela-relatorio');
    if (!tabela) return;
    
    const tbody = tabela.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    // Verificar direção da ordenação
    const isAsc = header?.classList.contains('ordenado-asc') === false;
    
    // Remover classes de ordenação de todos os headers
    const headers = tabela.querySelectorAll('th');
    headers.forEach(function(th) {
        th.classList.remove('ordenado-asc', 'ordenado-desc');
    });
    
    if (header) {
        header.classList.add(isAsc ? 'ordenado-asc' : 'ordenado-desc');
    }
    
    // Ordenar
    rows.sort(function(a, b) {
        const valorA = a.querySelectorAll('td')[coluna]?.textContent.trim() || '';
        const valorB = b.querySelectorAll('td')[coluna]?.textContent.trim() || '';
        
        // Tentar ordenar como número
        const numA = parseFloat(valorA.replace(/[^0-9.,]/g, '').replace(',', '.'));
        const numB = parseFloat(valorB.replace(/[^0-9.,]/g, '').replace(',', '.'));
        
        if (!isNaN(numA) && !isNaN(numB)) {
            return isAsc ? numA - numB : numB - numA;
        }
        
        // Ordenar como string
        return isAsc ? valorA.localeCompare(valorB) : valorB.localeCompare(valorA);
    });
    
    // Reinserir linhas ordenadas
    rows.forEach(function(row) {
        tbody.appendChild(row);
    });
    
    // Anunciar ordenação para leitores de tela
    anunciarMudanca('Tabela ordenada por coluna ' + (coluna + 1));
}

/**
 * Inicializa a ordenação da tabela
 */
function inicializarOrdenacao() {
    const headers = document.querySelectorAll('.tabela-relatorio th');
    headers.forEach(function(header, index) {
        header.style.cursor = 'pointer';
        header.setAttribute('role', 'button');
        header.setAttribute('tabindex', '0');
        header.setAttribute('aria-label', 'Ordenar por ' + header.textContent.trim());
        
        header.addEventListener('click', function() {
            ordenarTabela(index, this);
        });
        
        header.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                ordenarTabela(index, this);
            }
        });
    });
}

// ============================================================
// 4. GRÁFICOS
// ============================================================

/**
 * Inicializa os gráficos do relatório
 */
function inicializarGraficos() {
    const graficos = document.querySelectorAll('.grafico-item');
    graficos.forEach(function(item) {
        const barra = item.querySelector('.barra');
        if (barra) {
            // Animação da barra
            const width = barra.style.width;
            barra.style.width = '0%';
            
            setTimeout(function() {
                barra.style.transition = 'width 1.2s ease';
                barra.style.width = width || '0%';
            }, 300);
        }
    });
}

// ============================================================
// 5. EXPORTAÇÃO
// ============================================================

/**
 * Inicializa os botões de exportação
 */
function inicializarBotoesExportacao() {
    // Botão Exportar CSV
    const btnCSV = document.querySelector('.btn-exportar-csv');
    if (btnCSV) {
        btnCSV.addEventListener('click', function(e) {
            e.preventDefault();
            exportarCSV();
        });
    }
    
    // Botão Exportar Excel (CSV com configuração)
    const btnExcel = document.querySelector('.btn-excel');
    if (btnExcel) {
        btnExcel.addEventListener('click', function(e) {
            e.preventDefault();
            exportarExcel();
        });
    }
    
    // Botão Imprimir
    const btnImprimir = document.querySelector('.btn-imprimir');
    if (btnImprimir) {
        btnImprimir.addEventListener('click', function(e) {
            e.preventDefault();
            window.print();
        });
    }
}

/**
 * Exporta o relatório para CSV
 */
function exportarCSV() {
    const params = new URLSearchParams(window.location.search);
    params.set('action', 'exportar');
    params.set('tipo', 'csv');
    
    // Abrir em nova aba ou fazer download
    const url = window.location.pathname + '?' + params.toString();
    window.location.href = url;
}

/**
 * Exporta o relatório para Excel (CSV com configurações específicas)
 */
function exportarExcel() {
    const params = new URLSearchParams(window.location.search);
    params.set('action', 'exportar');
    params.set('tipo', 'excel');
    
    const url = window.location.pathname + '?' + params.toString();
    window.location.href = url;
}

/**
 * Exporta dados da tabela para CSV (client-side)
 */
function exportarTabelaCSV() {
    const tabela = document.querySelector('.tabela-relatorio');
    if (!tabela) return;
    
    // Extrair dados da tabela
    const linhas = tabela.querySelectorAll('tr');
    let dados = [];
    
    linhas.forEach(function(linha) {
        const celulas = linha.querySelectorAll('th, td');
        const linhaDados = [];
        celulas.forEach(function(celula) {
            linhaDados.push(celula.textContent.trim());
        });
        dados.push(linhaDados);
    });
    
    if (dados.length === 0) return;
    
    // Converter para CSV
    let csv = '\uFEFF'; // BOM para UTF-8
    dados.forEach(function(linha) {
        csv += linha.join(';') + '\n';
    });
    
    // Download
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'relatorio_' + new Date().toISOString().slice(0, 10) + '.csv');
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

// ============================================================
// 6. FUNÇÕES AUXILIARES
// ============================================================

/**
 * Alterna a visibilidade de colunas da tabela
 * @param {number} coluna - Índice da coluna
 */
function alternarColuna(coluna) {
    const tabela = document.querySelector('.tabela-relatorio');
    if (!tabela) return;
    
    const cells = tabela.querySelectorAll('th:nth-child(' + (coluna + 1) + '), td:nth-child(' + (coluna + 1) + ')');
    const visivel = cells[0]?.style.display !== 'none';
    
    cells.forEach(function(cell) {
        cell.style.display = visivel ? 'none' : '';
    });
    
    anunciarMudanca(visivel ? 'Coluna ocultada' : 'Coluna exibida');
}

/**
 * Pesquisa na tabela (filtro rápido)
 * @param {string} termo - Termo de busca
 */
function pesquisarTabela(termo) {
    const tabela = document.querySelector('.tabela-relatorio');
    if (!tabela) return;
    
    const linhas = tabela.querySelectorAll('tbody tr');
    const termoLower = termo.toLowerCase().trim();
    
    linhas.forEach(function(linha) {
        const texto = linha.textContent.toLowerCase();
        if (texto.includes(termoLower)) {
            linha.style.display = '';
        } else {
            linha.style.display = 'none';
        }
    });
    
    const visiveis = tabela.querySelectorAll('tbody tr[style*="display: none"]');
    const totalVisiveis = linhas.length - visiveis.length;
    
    // Mostrar contagem
    const contador = document.getElementById('contador-resultados');
    if (contador) {
        contador.textContent = 'Mostrando ' + totalVisiveis + ' de ' + linhas.length + ' resultados';
    }
}

// ============================================================
// 7. EXPORTAÇÃO PARA O ESCOPO GLOBAL
// ============================================================

window.aplicarFiltros = aplicarFiltros;
window.ordenarTabela = ordenarTabela;
window.exportarTabelaCSV = exportarTabelaCSV;
window.alternarColuna = alternarColuna;
window.pesquisarTabela = pesquisarTabela;
window.exportarCSV = exportarCSV;
window.exportarExcel = exportarExcel;