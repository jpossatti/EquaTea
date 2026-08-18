<?php
/**
 * index.php
 * Ponto de entrada para testes visuais.
 * 
 * Acesso: http://localhost:3000/
 * 
 * Parâmetros: ?view=aluno|professor|login
 */

// ============================================================
// 1. DEFINIR CAMINHO BASE
// ============================================================

$base_path = __DIR__;

// ============================================================
// 2. CARREGAR DEPENDÊNCIAS
// ============================================================

require_once $base_path . '/app/config/Database.php';
require_once $base_path . '/app/models/Aluno.php';
require_once $base_path . '/app/models/Equacao.php';
require_once $base_path . '/app/models/Progresso.php';
require_once $base_path . '/app/models/RegistroErro.php';

// ============================================================
// 3. CRIAR INSTÂNCIAS DOS MODELOS
// ============================================================

$aluno = new Aluno();
$equacao = new Equacao();
$progresso = new Progresso();
$registro = new RegistroErro();

// ============================================================
// 4. CARREGAR DADOS PARA AS VIEWS
// ============================================================

// Dados do aluno (para views do aluno)
$aluno_id = 1;
$dados_aluno = $aluno->getDadosCompletos(3); // ID do usuário do aluno
$dados_alunos = $aluno->getAll();
$dados_equacoes = $equacao->getAll();
$dados_progresso = $progresso->getByAluno($aluno_id);
$dados_erros = $registro->getEstatisticas($aluno_id);
$dados_relatorio = $registro->getRelatorioCompleto();

// ============================================================
// 5. DEFINIR A VIEW A SER EXIBIDA
// ============================================================

$view = $_GET['view'] ?? 'login';

// Mapeamento de views
$views = [
    'aluno' => 'aluno/dashboard.php',
    'exercicio' => 'aluno/exercicio.php',
    'parabens' => 'aluno/parabens.php',
    'professor' => 'professor/dashboard.php',
    'gerenciar_alunos' => 'professor/gerenciar_alunos.php',
    'gerenciar_equacoes' => 'professor/gerenciar_equacoes.php',
    'relatorio' => 'professor/relatorio.php',
    'login' => 'auth/login.php'
];

// Verificar se a view existe
if (!isset($views[$view])) {
    $view = 'login';
}

// ============================================================
// 6. CARREGAR A VIEW
// ============================================================

$view_file = __DIR__ . '/app/views/' . $views[$view];

if (file_exists($view_file)) {
    include_once $view_file;
} else {
    echo "<h1>View não encontrada: $view</h1>";
    echo "<p>Arquivo: $view_file</p>";
}