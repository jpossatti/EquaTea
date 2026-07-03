<?php
/**
 * ============================================================
 * menu_professor.php
 * Menu de navegação lateral/superior para professores.
 * 
 * FUNCIONALIDADES:
 * - Links para dashboard, gerenciamento de alunos e equações
 * - Link para relatórios
 * - Atalhos rápidos para funcionalidades principais
 * 
 * @package EquaTEA
 * @subpackage Views/Partials
 * @author Equipe EquaTEA
 * @version 1.0
 * ============================================================
 */

// ============================================================
// VERIFICAR SE O USUÁRIO ESTÁ LOGADO E É PROFESSOR
// ============================================================

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_perfil'] !== 'professor') {
    // Se não for professor, não exibe o menu
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
} elseif (in_array($pagina_atual, ['gerenciar_alunos.php'])) {
    $secao_atual = 'alunos';
} elseif (in_array($pagina_atual, ['gerenciar_equacoes.php'])) {
    $secao_atual = 'equacoes';
} elseif (in_array($pagina_atual, ['relatorio.php'])) {
    $secao_atual = 'relatorios';
}

// ============================================================
// DADOS DO PROFESSOR
// ============================================================

$nome_professor = $_SESSION['usuario_nome'] ?? 'Professor';

?>
<!-- ============================================================
    MENU DE NAVEGAÇÃO DO PROFESSOR
    ============================================================ -->
<nav class="menu-professor" id="menu-principal" role="navigation" aria-label="Menu do professor">
    <div class="container menu-professor-content">
        
        <!-- ============================================================
        SAUDAÇÃO E PERFIL
        ============================================================ -->
        <div class="menu-professor-profile">
            <div class="menu-professor-avatar" aria-hidden="true">
                <?php echo strtoupper(substr($nome_professor, 0, 1)); ?>
            </div>
            <div class="menu-professor-info">
                <span class="menu-professor-nome">Olá, <?php echo htmlspecialchars($nome_professor); ?></span>
                <span class="menu-professor-role">👨‍🏫 Professor</span>
            </div>
        </div>
        
        <!-- ============================================================
        LINKS DO MENU
        ============================================================ -->
        <ul class="menu-professor-links">
            <li class="menu-professor-item <?php echo ($secao_atual === 'dashboard') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>app/views/professor/dashboard.php" 
                   class="menu-professor-link"
                   aria-current="<?php echo ($secao_atual === 'dashboard') ? 'page' : 'false'; ?>">
                    <span class="menu-icon" aria-hidden="true">📊</span>
                    <span class="menu-label">Dashboard</span>
                </a>
            </li>
            
            <li class="menu-professor-item <?php echo ($secao_atual === 'alunos') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>app/views/professor/gerenciar_alunos.php" 
                   class="menu-professor-link"
                   aria-current="<?php echo ($secao_atual === 'alunos') ? 'page' : 'false'; ?>">
                    <span class="menu-icon" aria-hidden="true">👨‍🎓</span>
                    <span class="menu-label">Alunos</span>
                    <span class="menu-badge">Gerenciar</span>
                </a>
            </li>
            
            <li class="menu-professor-item <?php echo ($secao_atual === 'equacoes') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>app/views/professor/gerenciar_equacoes.php" 
                   class="menu-professor-link"
                   aria-current="<?php echo ($secao_atual === 'equacoes') ? 'page' : 'false'; ?>">
                    <span class="menu-icon" aria-hidden="true">📝</span>
                    <span class="menu-label">Equações</span>
                    <span class="menu-badge">Gerenciar</span>
                </a>
            </li>
            
            <li class="menu-professor-item <?php echo ($secao_atual === 'relatorios') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>app/views/professor/relatorio.php" 
                   class="menu-professor-link"
                   aria-current="<?php echo ($secao_atual === 'relatorios') ? 'page' : 'false'; ?>">
                    <span class="menu-icon" aria-hidden="true">📈</span>
                    <span class="menu-label">Relatórios</span>
                </a>
            </li>
            
            <li class="menu-professor-item">
                <a href="<?php echo BASE_URL; ?>app/views/auth/logout.php" 
                   class="menu-professor-link menu-professor-logout">
                    <span class="menu-icon" aria-hidden="true">🚪</span>
                    <span class="menu-label">Sair</span>
                </a>
            </li>
        </ul>
        
        <!-- ============================================================
        ATALHOS RÁPIDOS
        ============================================================ -->
        <div class="menu-professor-atalhos">
            <button class="atalho-btn" onclick="window.location.href='<?php echo BASE_URL; ?>app/views/professor/gerenciar_alunos.php'" 
                    aria-label="Cadastrar aluno" title="Cadastrar aluno">
                <span aria-hidden="true">➕</span>
                <span class="atalho-label">Aluno</span>
            </button>
            <button class="atalho-btn" onclick="window.location.href='<?php echo BASE_URL; ?>app/views/professor/gerenciar_equacoes.php'" 
                    aria-label="Cadastrar equação" title="Cadastrar equação">
                <span aria-hidden="true">📝</span>
                <span class="atalho-label">Equação</span>
            </button>
            <button class="atalho-btn" onclick="window.location.href='<?php echo BASE_URL; ?>app/views/professor/relatorio.php'" 
                    aria-label="Ver relatórios" title="Ver relatórios">
                <span aria-hidden="true">📊</span>
                <span class="atalho-label">Relatório</span>
            </button>
        </div>
    </div>
</nav>

<!-- ============================================================
    CSS DO MENU DO PROFESSOR
    ============================================================ -->
<style>
    /* ============================================================
     * ESTILOS DO MENU DO PROFESSOR
     * ============================================================ */
    
    .menu-professor {
        background-color: #f8f9fa;
        border-bottom: 2px solid #3498db;
        padding: 8px 0;
        position: sticky;
        top: 0;
        z-index: 100;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .menu-professor-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    
    .menu-professor-profile {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-right: 16px;
        border-right: 1px solid #e9ecef;
    }
    
    .menu-professor-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #e74c3c, #c0392b);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 18px;
        text-transform: uppercase;
    }
    
    .menu-professor-info {
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }
    
    .menu-professor-nome {
        font-weight: 600;
        font-size: 15px;
        color: #2c3e50;
    }
    
    .menu-professor-role {
        font-size: 12px;
        color: #888;
    }
    
    .menu-professor-links {
        display: flex;
        list-style: none;
        margin: 0;
        padding: 0;
        gap: 4px;
        flex-wrap: wrap;
    }
    
    .menu-professor-item {
        margin: 0;
    }
    
    .menu-professor-link {
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
    
    .menu-professor-link:hover {
        background-color: #e9ecef;
        color: #2c3e50;
    }
    
    .menu-professor-link:focus {
        outline: 3px solid #3498db;
        outline-offset: 2px;
    }
    
    .menu-professor-item.active .menu-professor-link {
        background-color: #2c3e50;
        color: white;
        box-shadow: 0 2px 8px rgba(44, 62, 80, 0.3);
    }
    
    .menu-professor-item.active .menu-professor-link:hover {
        background-color: #1a252f;
    }
    
    .menu-icon {
        font-size: 18px;
    }
    
    .menu-badge {
        background-color: #e74c3c;
        color: white;
        border-radius: 4px;
        padding: 2px 8px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .menu-professor-logout {
        color: #e74c3c;
    }
    
    .menu-professor-logout:hover {
        background-color: #fde8e8;
        color: #c0392b;
    }
    
    .menu-professor-atalhos {
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
        .menu-professor-content {
            flex-direction: column;
            align-items: stretch;
            gap: 8px;
        }
        
        .menu-professor-profile {
            border-right: none;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 8px;
            padding-right: 0;
            justify-content: center;
        }
        
        .menu-professor-links {
            justify-content: center;
        }
        
        .menu-professor-atalhos {
            justify-content: center;
        }
        
        .menu-professor-link {
            font-size: 13px;
            padding: 6px 10px;
        }
        
        .menu-label {
            display: none;
        }
        
        .menu-professor-item.active .menu-label {
            display: inline;
        }
    }
    
    @media (max-width: 480px) {
        .menu-professor-link {
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
    .alto-contraste .menu-professor {
        background-color: #000000;
        border-bottom-color: #ffff00;
    }
    
    .alto-contraste .menu-professor-link {
        color: #ffffff;
    }
    
    .alto-contraste .menu-professor-link:hover {
        background-color: #222222;
        color: #ffff00;
    }
    
    .alto-contraste .menu-professor-item.active .menu-professor-link {
        background-color: #ffff00;
        color: #000000;
    }
    
    .alto-contraste .menu-professor-nome {
        color: #ffffff;
    }
    
    .alto-contraste .menu-professor-role {
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