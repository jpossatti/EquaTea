<?php
/**
 * ============================================================
 * menu_aluno.php
 * Menu de navegação lateral/superior para alunos.
 * 
 * FUNCIONALIDADES:
 * - Links para dashboard, exercícios e progresso
 * - Indicador visual da página atual
 * - Atalhos rápidos para funcionalidades principais
 * 
 * @package EquaTEA
 * @subpackage Views/Partials
 * @author Equipe EquaTEA
 * @version 1.0
 * ============================================================
 */

// ============================================================
// VERIFICAR SE O USUÁRIO ESTÁ LOGADO E É ALUNO
// ============================================================

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_perfil'] !== 'aluno') {
    // Se não for aluno, não exibe o menu
    return;
}

// ============================================================
// DETERMINAR A PÁGINA ATIVA
// ============================================================

$pagina_atual = basename($_SERVER['PHP_SELF']);
$secao_atual = '';

// Determina qual seção está ativa baseado na página
if (in_array($pagina_atual, ['dashboard.php'])) {
    $secao_atual = 'dashboard';
} elseif (in_array($pagina_atual, ['exercicio.php', 'passo.php', 'parabens.php'])) {
    $secao_atual = 'exercicio';
} elseif (in_array($pagina_atual, ['progresso.php'])) {
    $secao_atual = 'progresso';
}

// ============================================================
// DADOS DO ALUNO
// ============================================================

$nome_aluno = $_SESSION['usuario_nome'] ?? 'Aluno';
$nivel_tea = $_SESSION['nivel_tea'] ?? 'suporte1';

?>
<!-- ============================================================
    MENU DE NAVEGAÇÃO DO ALUNO
    ============================================================ -->
<nav class="menu-aluno" id="menu-principal" role="navigation" aria-label="Menu do aluno">
    <div class="container menu-aluno-content">
        
        <!-- ============================================================
        SAUDAÇÃO E PERFIL
        ============================================================ -->
        <div class="menu-aluno-profile">
            <div class="menu-aluno-avatar" aria-hidden="true">
                <?php echo strtoupper(substr($nome_aluno, 0, 1)); ?>
            </div>
            <div class="menu-aluno-info">
                <span class="menu-aluno-nome">Olá, <?php echo htmlspecialchars($nome_aluno); ?></span>
                <span class="menu-aluno-nivel">
                    <?php if ($nivel_tea === 'suporte1'): ?>
                        🌱 Suporte 1
                    <?php else: ?>
                        🌿 Suporte 2
                    <?php endif; ?>
                </span>
            </div>
        </div>
        
        <!-- ============================================================
        LINKS DO MENU
        ============================================================ -->
        <ul class="menu-aluno-links">
            <li class="menu-aluno-item <?php echo ($secao_atual === 'dashboard') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>app/views/aluno/dashboard.php" 
                   class="menu-aluno-link"
                   aria-current="<?php echo ($secao_atual === 'dashboard') ? 'page' : 'false'; ?>">
                    <span class="menu-icon" aria-hidden="true">📊</span>
                    <span class="menu-label">Dashboard</span>
                </a>
            </li>
            
            <li class="menu-aluno-item <?php echo ($secao_atual === 'exercicio') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>app/views/aluno/exercicio.php" 
                   class="menu-aluno-link"
                   aria-current="<?php echo ($secao_atual === 'exercicio') ? 'page' : 'false'; ?>">
                    <span class="menu-icon" aria-hidden="true">📝</span>
                    <span class="menu-label">Novo Exercício</span>
                    <span class="menu-badge">+</span>
                </a>
            </li>
            
            <li class="menu-aluno-item <?php echo ($secao_atual === 'progresso') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>app/views/aluno/progresso.php" 
                   class="menu-aluno-link"
                   aria-current="<?php echo ($secao_atual === 'progresso') ? 'page' : 'false'; ?>">
                    <span class="menu-icon" aria-hidden="true">📈</span>
                    <span class="menu-label">Meu Progresso</span>
                </a>
            </li>
            
            <li class="menu-aluno-item">
                <a href="<?php echo BASE_URL; ?>app/views/auth/logout.php" 
                   class="menu-aluno-link menu-aluno-logout">
                    <span class="menu-icon" aria-hidden="true">🚪</span>
                    <span class="menu-label">Sair</span>
                </a>
            </li>
        </ul>
        
        <!-- ============================================================
        ATALHOS RÁPIDOS (apenas para desktop)
        ============================================================ -->
        <div class="menu-aluno-atalhos">
            <button class="atalho-btn" onclick="window.location.href='<?php echo BASE_URL; ?>app/views/aluno/exercicio.php'" 
                    aria-label="Iniciar exercício" title="Iniciar exercício">
                <span aria-hidden="true">🚀</span>
                <span class="atalho-label">Iniciar</span>
            </button>
            <button class="atalho-btn" onclick="window.location.href='<?php echo BASE_URL; ?>app/views/aluno/dashboard.php#dica'" 
                    aria-label="Ver dica do dia" title="Dica do dia">
                <span aria-hidden="true">💡</span>
                <span class="atalho-label">Dica</span>
            </button>
            <button class="atalho-btn" onclick="window.location.href='<?php echo BASE_URL; ?>app/views/aluno/dashboard.php#progresso'" 
                    aria-label="Ver progresso" title="Ver progresso">
                <span aria-hidden="true">📊</span>
                <span class="atalho-label">Progresso</span>
            </button>
        </div>
    </div>
</nav>

<!-- ============================================================
    CSS DO MENU DO ALUNO
    ============================================================ -->
<style>
    /* ============================================================
     * ESTILOS DO MENU DO ALUNO
     * ============================================================ */
    
    .menu-aluno {
        background-color: #f8f9fa;
        border-bottom: 2px solid #e9ecef;
        padding: 8px 0;
        position: sticky;
        top: 0;
        z-index: 100;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .menu-aluno-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    
    .menu-aluno-profile {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-right: 16px;
        border-right: 1px solid #e9ecef;
    }
    
    .menu-aluno-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3498db, #2c3e50);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 18px;
        text-transform: uppercase;
    }
    
    .menu-aluno-info {
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }
    
    .menu-aluno-nome {
        font-weight: 600;
        font-size: 15px;
        color: #2c3e50;
    }
    
    .menu-aluno-nivel {
        font-size: 12px;
        color: #888;
    }
    
    .menu-aluno-links {
        display: flex;
        list-style: none;
        margin: 0;
        padding: 0;
        gap: 4px;
        flex-wrap: wrap;
    }
    
    .menu-aluno-item {
        margin: 0;
    }
    
    .menu-aluno-link {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 6px;
        color: #555;
        text-decoration: none;
        font-size: 15px;
        font-weight: 500;
        transition: all 0.2s;
        position: relative;
    }
    
    .menu-aluno-link:hover {
        background-color: #e9ecef;
        color: #2c3e50;
    }
    
    .menu-aluno-link:focus {
        outline: 3px solid #3498db;
        outline-offset: 2px;
    }
    
    .menu-aluno-item.active .menu-aluno-link {
        background-color: #3498db;
        color: white;
        box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
    }
    
    .menu-aluno-item.active .menu-aluno-link:hover {
        background-color: #2980b9;
    }
    
    .menu-icon {
        font-size: 18px;
    }
    
    .menu-badge {
        background-color: #e74c3c;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
    }
    
    .menu-aluno-logout {
        color: #e74c3c;
    }
    
    .menu-aluno-logout:hover {
        background-color: #fde8e8;
        color: #c0392b;
    }
    
    .menu-aluno-atalhos {
        display: flex;
        gap: 6px;
        align-items: center;
    }
    
    .atalho-btn {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 6px 12px;
        border: none;
        border-radius: 6px;
        background: #e9ecef;
        color: #2c3e50;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .atalho-btn:hover {
        background: #d1d5db;
        transform: translateY(-1px);
    }
    
    .atalho-btn:focus {
        outline: 3px solid #3498db;
        outline-offset: 2px;
    }
    
    .atalho-label {
        font-weight: 500;
    }
    
    /* ============================================================
     * RESPONSIVIDADE
     * ============================================================ */
    
    @media (max-width: 768px) {
        .menu-aluno-content {
            flex-direction: column;
            align-items: stretch;
            gap: 8px;
        }
        
        .menu-aluno-profile {
            border-right: none;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 8px;
            padding-right: 0;
            justify-content: center;
        }
        
        .menu-aluno-links {
            justify-content: center;
        }
        
        .menu-aluno-atalhos {
            justify-content: center;
        }
        
        .menu-aluno-link {
            font-size: 13px;
            padding: 6px 10px;
        }
        
        .menu-label {
            display: none;
        }
        
        .menu-aluno-item.active .menu-label {
            display: inline;
        }
    }
    
    @media (max-width: 480px) {
        .menu-aluno-link {
            padding: 6px 8px;
            font-size: 12px;
        }
        
        .menu-icon {
            font-size: 16px;
        }
        
        .atalho-btn {
            font-size: 12px;
            padding: 4px 10px;
        }
        
        .atalho-label {
            display: none;
        }
    }
    
    /* ============================================================
     * ALTO CONTRASTE
     * ============================================================ */
    .alto-contraste .menu-aluno {
        background-color: #000000;
        border-bottom-color: #ffffff;
    }
    
    .alto-contraste .menu-aluno-link {
        color: #ffffff;
    }
    
    .alto-contraste .menu-aluno-link:hover {
        background-color: #222222;
        color: #ffff00;
    }
    
    .alto-contraste .menu-aluno-item.active .menu-aluno-link {
        background-color: #ffff00;
        color: #000000;
    }
    
    .alto-contraste .menu-aluno-nome {
        color: #ffffff;
    }
    
    .alto-contraste .menu-aluno-nivel {
        color: #dddddd;
    }
    
    .alto-contraste .atalho-btn {
        background: #222222;
        color: #ffffff;
        border: 1px solid #ffffff;
    }
    
    .alto-contraste .atalho-btn:hover {
        background: #444444;
    }
</style>