<?php
/**
 * Sidebar do Sistema
 */
$pagina_atual = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h4><i class="fas fa-chart-line"></i> Dashboard</h4>
    </div>
    
    <div class="quick-access">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= ($pagina_atual == 'index.php') ? 'active' : '' ?>" href="../index.php">
                    <i class="fas fa-tachometer-alt"></i> Visão Geral
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($pagina_atual, 'sales') !== false ? 'active' : '' ?>" href="sales/sales.php">
                    <i class="fas fa-cash-register"></i> Vendas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($pagina_atual, 'products') !== false ? 'active' : '' ?>" href="products/products.php">
                    <i class="fas fa-boxes"></i> Produtos
                </a>
            </li>
            <!-- Adicione outros itens conforme necessário -->
        </ul>
    </div>
    
    <div class="sidebar-footer mt-auto">
        <div class="user-info">
            <p class="mb-1"><i class="fas fa-user"></i> <?= $_SESSION['usuario_nome'] ?? 'Usuário' ?></p>
            <small class="text-muted"><?= ucfirst($_SESSION['nivel_acesso'] ?? 'guest') ?></small>
        </div>
    </div>
</div>

<style>
.sidebar {
    background: linear-gradient(135deg, #2c3e50, #34495e);
    color: white;
    padding: 20px;
    height: 100vh;
    position: sticky;
    top: 0;
}

.sidebar-header {
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    margin-bottom: 20px;
}

.nav-link {
    color: #ecf0f1;
    border-radius: 5px;
    margin-bottom: 5px;
    transition: all 0.3s;
}

.nav-link:hover, .nav-link.active {
    background-color: rgba(255,255,255,0.1);
    color: white;
}

.sidebar-footer {
    padding-top: 15px;
    border-top: 1px solid rgba(255,255,255,0.1);
}
</style>