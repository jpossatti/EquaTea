<?php
/**
 * gerenciar_alunos.php
 * Gerenciamento de alunos - Versão de teste
 * 
 * Acesso: ?view=gerenciar_alunos
 */

$page_title = 'Gerenciar Alunos - EquaTEA';

include_once __DIR__ . '/../partials/header.php';
include_once __DIR__ . '/../partials/menu_professor.php';
?>
<main class="container gerenciar-container">
    <h1>👨‍🎓 Gerenciar Alunos</h1>

    <div class="form-section">
        <h2>➕ Cadastrar Novo Aluno</h2>
        <form method="POST" action="#">
            <div class="form-grid">
                <div><label>Nome:</label><input type="text" placeholder="Nome completo"></div>
                <div><label>E-mail:</label><input type="email" placeholder="aluno@escola.com"></div>
                <div><label>Senha:</label><input type="text" placeholder="Mínimo 4 caracteres"></div>
                <div><label>Idade:</label><input type="number" placeholder="14-21"></div>
                <div><label>Nível TEA:</label>
                    <select><option value="suporte1">Suporte 1</option><option value="suporte2">Suporte 2</option></select>
                </div>
                <div><label>Escola:</label><input type="text" placeholder="Escola Modelo"></div>
                <div><label>Turma:</label><input type="text" placeholder="1º EM A"></div>
            </div>
            <button type="submit" class="btn-primary">✅ Cadastrar Aluno</button>
        </form>
    </div>

    <div class="list-section">
        <h2>📋 Lista de Alunos</h2>
        <table class="list-table">
            <thead><tr><th>Nome</th><th>Email</th><th>Idade</th><th>Nível</th><th>Ações</th></tr></thead>
            <tbody>
                <?php foreach ($dados_alunos as $a): ?>
                <tr>
                    <td><?php echo htmlspecialchars($a['nome']); ?></td>
                    <td><?php echo htmlspecialchars($a['email']); ?></td>
                    <td><?php echo $a['idade']; ?></td>
                    <td><?php echo $a['nivel_tea'] == 'suporte1' ? 'Suporte 1' : 'Suporte 2'; ?></td>
                    <td>
                        <button class="btn-acao btn-editar">✏️</button>
                        <button class="btn-acao btn-senha">🔑</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include_once __DIR__ . '/../partials/footer.php'; ?>