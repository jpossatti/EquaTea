<?php
session_start();
require_once '../../helpers/session_helper.php';

if (!estaLogado() || $_SESSION['tipo_perfil'] != 'aluno') {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../../models/Equacao.php';
require_once '../../models/Progresso.php';
require_once '../../controllers/ExercicioController.php';

$aluno_id = $_SESSION['aluno_id'];
$equacao_id = isset($_GET['equacao_id']) ? (int)$_GET['equacao_id'] : 0;

if (!$equacao_id) {
    header('Location: dashboard.php');
    exit;
}

$controller = new ExercicioController();
$exercicio = $controller->getExercicio($aluno_id, $equacao_id);

if (!$exercicio || $exercicio['concluida']) {
    header('Location: dashboard.php');
    exit;
}

$equacao = $exercicio['equacao'];
$passo_atual = $exercicio['passo_atual'];
$enunciado = $exercicio['enunciado'];
$dica_passo = $exercicio['dica_passo'];

// Passos da resolução
$passos = [
    1 => 'Identificar termos com x e sem x',
    2 => 'Isolar o termo com x (operação inversa da constante)',
    3 => 'Calcular o lado direito',
    4 => 'Isolar x (dividir pelo coeficiente)'
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exercício - EquaTEA</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <link rel="stylesheet" href="../../public/css/acessibilidade.css">
</head>
<body>
    <?php include '../partials/header.php'; ?>
    <?php include '../partials/menu_aluno.php'; ?>
    
    <main class="container exercicio-container">
        <div class="progress-bar">
            <div class="step-indicators">
                <?php for ($i = 1; $i <= 4; $i++): ?>
                    <div class="step <?= $i == $passo_atual ? 'active' : ($i < $passo_atual ? 'completed' : '') ?>">
                        <span class="step-number"><?= $i ?></span>
                        <span class="step-label">Passo <?= $i ?></span>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
        
        <div class="exercicio-card">
            <div class="equacao-header">
                <h2>Resolva a equação:</h2>
                <div class="equacao-display">
                    <span class="equacao-text"><?= htmlspecialchars($enunciado) ?></span>
                </div>
            </div>
            
            <div class="passo-container">
                <h3>Passo <?= $passo_atual ?>: <?= $passos[$passo_atual] ?></h3>
                <p class="passo-dica"><?= htmlspecialchars($dica_passo) ?></p>
                
                <div class="resposta-area">
                    <form id="form-resposta" method="POST" action="../../controllers/ExercicioController.php?action=verificar">
                        <input type="hidden" name="equacao_id" value="<?= $equacao_id ?>">
                        <input type="hidden" name="passo" value="<?= $passo_atual ?>">
                        <input type="hidden" name="aluno_id" value="<?= $aluno_id ?>">
                        
                        <div class="form-group">
                            <label for="resposta">Sua resposta:</label>
                            <input type="text" id="resposta" name="resposta" 
                                   placeholder="Digite sua resposta para este passo" 
                                   required autofocus>
                        </div>
                        
                        <div class="botoes-acoes">
                            <button type="submit" class="btn-verificar">Verificar</button>
                            <button type="button" class="btn-ajuda" id="btn-ajuda">Ajuda</button>
                            <button type="button" class="btn-ouvir" id="btn-ouvir">Ouvir</button>
                        </div>
                    </form>
                </div>
                
                <div id="feedback" class="feedback-area" style="display: none;">
                    <div class="feedback-message"></div>
                    <div class="feedback-dica"></div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Modal de Ajuda -->
    <?php include '../partials/modal_ajuda.php'; ?>
    
    <?php include '../partials/footer.php'; ?>
    
    <script src="../../public/js/exercicio.js"></script>
    <script src="../../public/js/speech.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('form-resposta');
            const feedback = document.getElementById('feedback');
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                fetch('../../controllers/ExercicioController.php?action=verificar', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    feedback.style.display = 'block';
                    const messageDiv = feedback.querySelector('.feedback-message');
                    const dicaDiv = feedback.querySelector('.feedback-dica');
                    
                    if (data.status === 'avancar' || data.status === 'concluido') {
                        messageDiv.className = 'feedback-success';
                        messageDiv.textContent = data.mensagem;
                        dicaDiv.textContent = '';
                        
                        if (data.status === 'concluido') {
                            setTimeout(() => {
                                window.location.href = 'parabens.php?equacao_id=<?= $equacao_id ?>';
                            }, 2000);
                        } else {
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        }
                    } else if (data.status === 'erro') {
                        messageDiv.className = 'feedback-error';
                        messageDiv.textContent = data.mensagem;
                        dicaDiv.textContent = '💡 Dica: ' + data.dica;
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                });
            });
            
            // Botão Ajuda
            document.getElementById('btn-ajuda').addEventListener('click', function() {
                document.getElementById('modal-ajuda').style.display = 'block';
            });
            
            // Botão Ouvir
            document.getElementById('btn-ouvir').addEventListener('click', function() {
                const texto = '<?= htmlspecialchars($enunciado) ?>';
                lerTexto(texto);
            });
        });
    </script>
</body>
</html>