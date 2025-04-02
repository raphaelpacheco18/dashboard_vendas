<?php
/**
 * HEADER COM CONTROLE DE ACESSO E MENU CENTRALIZADO COMPLETO
 * Níveis: admin, gerente, vendedor
 */

// Configurações iniciais
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Controle de sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuração base do projeto
$base_url = '/dashboard_vendas/';

// Dados do usuário com fallback seguro
$usuario = [
    'nome' => isset($_SESSION['usuario_nome']) ? htmlspecialchars($_SESSION['usuario_nome']) : 'Visitante',
    'nivel' => $_SESSION['nivel_acesso'] ?? 'guest',
    'email' => $_SESSION['usuario_email'] ?? '',
    'foto' => isset($_SESSION['usuario_foto']) ? htmlspecialchars($_SESSION['usuario_foto']) : 'default-profile.jpg'
];

// Caminhos para fotos
$fotoPath = $base_url . 'assets/img/profiles/' . $usuario['foto'];
$fotoPadrao = $base_url . 'assets/img/profiles/default-profile.jpg';

// Identificação da página ativa
$pagina_atual = basename($_SERVER['PHP_SELF']);
$uri_segments = explode('/', $_SERVER['REQUEST_URI']);
$modulo_atual = '';
if (in_array('modules', $uri_segments)) {
    $modulo_index = array_search('modules', $uri_segments) + 1;
    $modulo_atual = $uri_segments[$modulo_index] ?? '';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Vendas</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
    :root {
        --primary-color: #4e73df;
        --secondary-color: #224abe;
        --success-color: #1cc88a;
        --info-color: #36b9cc;
        --warning-color: #f6c23e;
        --danger-color: #e74a3b;
        --light-color: #f8f9fc;
        --dark-color: #5a5c69;
        --card-radius: 0.35rem;
        --shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15);
    }
    
    body {
        padding-top: 70px;
        background-color: var(--light-color);
        font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    /* Navbar Gradient */
    .navbar {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        box-shadow: var(--shadow);
        padding: 0.5rem 1rem;
    }
    
    .navbar-brand {
        font-weight: 600;
        font-size: 1.25rem;
        display: flex;
        align-items: center;
        position: absolute;
        left: 20px;
    }
    
    .navbar-brand i {
        margin-right: 0.5rem;
    }
    
    /* Menu Centralizado */
    .navbar-nav.center-menu {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        justify-content: center;
    }
    
    /* Menu Items */
    .nav-link {
        color: white !important;
        padding: 0.5rem 1rem;
        margin: 0 0.25rem;
        border-radius: var(--card-radius);
        transition: all 0.3s;
        display: flex;
        align-items: center;
    }
    
    .nav-link i {
        margin-right: 0.5rem;
    }
    
    .nav-link:hover {
        background-color: rgba(255,255,255,0.1);
        transform: translateY(-1px);
    }
    
    .nav-link.active {
        background-color: rgba(255,255,255,0.2);
        font-weight: 500;
    }
    
    /* Dropdown Menu */
    .dropdown-menu {
        border: none;
        border-radius: var(--card-radius);
        box-shadow: var(--shadow);
        padding: 0.5rem 0;
    }
    
    .dropdown-item {
        padding: 0.5rem 1.5rem;
        transition: all 0.2s;
    }
    
    .dropdown-item:hover {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white !important;
    }
    
    .dropdown-divider {
        margin: 0.25rem 0;
    }
    
    /* User Profile */
    .profile-img {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid rgba(255,255,255,0.3);
    }
    
    /* Responsividade */
    @media (max-width: 992px) {
        .navbar-brand {
            position: static;
        }
        
        .navbar-nav.center-menu {
            position: static;
            transform: none;
            left: auto;
            display: block;
            text-align: center;
        }
        
        .nav-item {
            margin-bottom: 0.25rem;
        }
        
        .dropdown-menu {
            text-align: center;
        }
    }
    </style>
</head>
<body>
    <!-- Menu Principal -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <!-- Brand -->
            <a class="navbar-brand" href="<?= $base_url ?>index.php">
                <i class="fas fa-chart-line"></i>
                <span class="d-none d-sm-inline">Dashboard Vendas</span>
            </a>
            
            <!-- Toggle Mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Itens do Menu -->
            <div class="collapse navbar-collapse" id="mainMenu">
                <!-- Menu Centralizado -->
                <ul class="navbar-nav center-menu">
                    <!-- Dashboard (todos) -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($pagina_atual == 'index.php') ? 'active' : '' ?>" href="<?= $base_url ?>index.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="d-none d-md-inline">Geral</span>
                        </a>
                    </li>
                    
                    <!-- Vendas (todos) -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= ($modulo_atual == 'sales') ? 'active' : '' ?>" 
                           href="<?= $base_url ?>modules/sales/sales.php"
                           id="salesDropdown" 
                           role="button" 
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            <i class="fas fa-cash-register"></i>
                            <span class="d-none d-md-inline">Vendas</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item <?= (basename($_SERVER['PHP_SELF'])) == 'sales.php' ? 'active' : '' ?>" href="<?= $base_url ?>modules/sales/sales.php"><i class="fas fa-list me-2"></i>Listar Vendas</a></li>
                            <li><a class="dropdown-item <?= (basename($_SERVER['PHP_SELF'])) == 'sales_add.php' ? 'active' : '' ?>" href="<?= $base_url ?>modules/sales/sales_add.php"><i class="fas fa-plus-circle me-2"></i>Nova Venda</a></li>
                            <?php if(in_array($usuario['nivel'], ['admin', 'gerente'])): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item <?= (basename($_SERVER['PHP_SELF'])) == 'sales_report.php' ? 'active' : '' ?>" href="<?= $base_url ?>modules/sales/sales_report.php"><i class="fas fa-chart-bar me-2"></i>Relatórios</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    
                    <!-- Produtos (admin e gerente) -->
                    <?php if(in_array($usuario['nivel'], ['admin', 'gerente'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= ($modulo_atual == 'products') ? 'active' : '' ?>" 
                           href="<?= $base_url ?>modules/products/products.php"
                           id="productsDropdown" 
                           role="button" 
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            <i class="fas fa-boxes"></i>
                            <span class="d-none d-md-inline">Produtos</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item <?= (basename($_SERVER['PHP_SELF'])) == 'products.php' ? 'active' : '' ?>" 
                                   href="<?= $base_url ?>modules/products/products.php">
                                    <i class="fas fa-list me-2"></i> Listar Produtos
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?= (basename($_SERVER['PHP_SELF'])) == 'product_add.php' ? 'active' : '' ?>" 
                                   href="<?= $base_url ?>modules/products/product_add.php">
                                    <i class="fas fa-plus-circle me-2"></i> Adicionar Produto
                                </a>
                            </li>
                            <?php if($usuario['nivel'] === 'admin'): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item <?= (basename($_SERVER['PHP_SELF'])) == 'inventory.php' ? 'active' : '' ?>" 
                                   href="<?= $base_url ?>modules/products/inventory.php">
                                    <i class="fas fa-warehouse me-2"></i> Gerenciar Estoque
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Lojas (admin e gerente) -->
                    <?php if(in_array($usuario['nivel'], ['admin', 'gerente'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= ($modulo_atual == 'lojas') ? 'active' : '' ?>" 
                           href="<?= $base_url ?>modules/lojas/lojas.php"
                           id="storesDropdown" 
                           role="button" 
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            <i class="fas fa-store"></i>
                            <span class="d-none d-md-inline">Lojas</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item <?= (basename($_SERVER['PHP_SELF'])) == 'lojas.php' ? 'active' : '' ?>" href="<?= $base_url ?>modules/lojas/lojas.php"><i class="fas fa-list me-2"></i> Listar Lojas</a></li>
                            <?php if($usuario['nivel'] === 'admin'): ?>
                            <li><a class="dropdown-item <?= (basename($_SERVER['PHP_SELF'])) == 'loja_add.php' ? 'active' : '' ?>" href="<?= $base_url ?>modules/lojas/loja_add.php"><i class="fas fa-plus-circle me-2"></i> Adicionar Loja</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item <?= (basename($_SERVER['PHP_SELF'])) == 'metas_list.php' ? 'active' : '' ?>" href="<?= $base_url ?>modules/metas/metas_list.php"><i class="fas fa-bullseye me-2"></i> Metas</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Equipe (admin) -->
                    <?php if($usuario['nivel'] === 'admin'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= ($modulo_atual == 'team') ? 'active' : '' ?>" 
                           href="<?= $base_url ?>modules/team/vendedoras_list.php"
                           id="teamDropdown" 
                           role="button" 
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            <i class="fas fa-users"></i>
                            <span class="d-none d-md-inline">Equipe</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item <?= (basename($_SERVER['PHP_SELF'])) == 'vendedoras_list.php' ? 'active' : '' ?>" href="<?= $base_url ?>modules/team/vendedoras_list.php"><i class="fas fa-list me-2"></i>Vendedoras</a></li>
                            <li><a class="dropdown-item <?= (basename($_SERVER['PHP_SELF'])) == 'vendedora_add.php' ? 'active' : '' ?>" href="<?= $base_url ?>modules/team/vendedora_add.php"><i class="fas fa-user-plus me-2"></i>Adicionar Vendedora</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Relatórios (admin e gerente) -->
                    <?php if(in_array($usuario['nivel'], ['admin', 'gerente'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($modulo_atual == 'reports') ? 'active' : '' ?>" href="<?= $base_url ?>modules/reports/">
                            <i class="fas fa-chart-pie"></i>
                            <span class="d-none d-md-inline">Relatórios</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Usuários (admin) -->
                    <?php if($usuario['nivel'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($modulo_atual == 'usuarios') ? 'active' : '' ?>" href="<?= $base_url ?>modules/usuarios/usuario_list.php">
                            <i class="fas fa-user-cog"></i>
                            <span class="d-none d-md-inline">Usuários</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <!-- Menu do Usuário (direita) -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <img src="<?= $fotoPath ?>"
                                 class="profile-img me-2"
                                 alt="Foto de <?= $usuario['nome'] ?>"
                                 onerror="this.onerror=null;this.src='<?= $fotoPadrao ?>'">
                            <span class="d-none d-lg-inline"><?= $usuario['nome'] ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user-circle me-2"></i>Meu Perfil</a></li>
                            <?php if($usuario['nivel'] === 'admin'): ?>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Configurações</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= $base_url ?>auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Sair</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>