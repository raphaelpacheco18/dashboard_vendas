<?php
/**
 * HEADER COM CONTROLE DE ACESSO
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
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- CSS Local -->
    <link href="<?= $base_url ?>assets/css/templates.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --hover-color: rgba(255,255,255,0.15);
            --active-color: rgba(255,255,255,0.25);
            --text-light: #f8f9fa;
            --shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        body {
            padding-top: 70px;
            background-color: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: var(--shadow);
        }
        
        .nav-link {
            transition: all 0.2s ease;
            margin: 0 5px;
            border-radius: 4px;
            padding: 8px 12px;
            color: var(--text-light);
            display: flex;
            align-items: center;
        }
        
        .nav-link:hover {
            background: var(--hover-color);
            transform: translateY(-2px);
        }
        
        .nav-link.active {
            background: var(--active-color);
            font-weight: 500;
            box-shadow: var(--shadow);
        }
        
        .nav-link i {
            margin-right: 8px;
            font-size: 0.9em;
        }
        
        .dropdown-menu {
            border-radius: 8px;
            border: none;
            box-shadow: var(--shadow);
        }
        
        .dropdown-item {
            transition: all 0.2s;
            padding: 8px 16px;
        }
        
        .dropdown-item:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
        }
        
        .profile-img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,0.3);
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 10px;
            padding: 3px 6px;
        }
        
        @media (max-width: 992px) {
            .navbar-collapse {
                padding-top: 15px;
            }
            
            .dropdown-menu {
                margin-top: 5px;
                margin-bottom: 10px;
            }
        }
        .navbar {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        box-shadow: var(--shadow);
        padding: 0.5rem 1rem; /* Adicione padding para melhor espaçamento */
    }

    .navbar-brand {
        margin-right: auto; /* Empurra a logo para a esquerda */
    }

    .navbar-nav.me-auto {
        margin: 0 auto; /* Centraliza os itens do menu principal */
        display: flex;
        justify-content: center;
        flex-grow: 1;
    }

    .navbar-nav:last-child {
        margin-left: auto; /* Empurra o menu do usuário para a direita */
    }

    /* Ajuste para mobile */
    @media (max-width: 992px) {
        .navbar-collapse {
            padding-top: 15px;
        }
        
        .navbar-nav.me-auto {
            margin: 0;
            flex-direction: column;
        }
        
        .navbar-nav:last-child {
            margin-left: 0;
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
                <i class="fas fa-chart-line me-2"></i>
                Dashboard Vendas
            </a>
            
            <!-- Toggle Mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Itens do Menu -->
            <div class="collapse navbar-collapse" id="mainMenu">
                <ul class="navbar-nav me-auto">
                    <!-- Dashboard (todos) -->
                    <li class="nav-item">
                        <a class="nav-link <?= ($pagina_atual == 'index.php') ? 'active' : '' ?>" href="<?= $base_url ?>index.php">
                            <i class="fas fa-tachometer-alt"></i> Geral
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
                            <i class="fas fa-cash-register"></i> Vendas
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item <?= (basename($_SERVER['PHP_SELF']) == 'sales.php' ? 'active' : '') ?>" href="<?= $base_url ?>modules/sales/sales.php"><i class="fas fa-list me-2"></i>Listar Vendas</a></li>
                            <li><a class="dropdown-item <?= (basename($_SERVER['PHP_SELF']) == 'sales_add.php' ? 'active' : '') ?>" href="<?= $base_url ?>modules/sales/sales_add.php"><i class="fas fa-plus-circle me-2"></i>Nova Venda</a></li>
                            <?php if(in_array($usuario['nivel'], ['admin', 'gerente'])): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item <?= (basename($_SERVER['PHP_SELF']) == 'sales_report.php' ? 'active' : '') ?>" href="<?= $base_url ?>modules/sales/sales_report.php"><i class="fas fa-chart-bar me-2"></i>Relatórios</a></li>
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
        <i class="fas fa-boxes"></i> Produtos
    </a>
    <ul class="dropdown-menu">
        <li>
            <a class="dropdown-item <?= (basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '') ?>" 
               href="<?= $base_url ?>modules/products/products.php">
                <i class="fas fa-list me-2"></i> Listar Produtos
            </a>
        </li>
        <li>
            <a class="dropdown-item <?= (basename($_SERVER['PHP_SELF']) == 'product_add.php' ? 'active' : '') ?>" 
               href="<?= $base_url ?>modules/products/product_add.php">
                <i class="fas fa-plus-circle me-2"></i> Adicionar Produto
            </a>
        </li>
        <?php if($usuario['nivel'] === 'admin'): ?>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item <?= (basename($_SERVER['PHP_SELF']) == 'inventory.php' ? 'active' : '') ?>" 
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
        <i class="fas fa-store"></i> Lojas
    </a>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item <?= (basename($_SERVER['PHP_SELF']) == 'lojas.php' ? 'active' : '') ?>" href="<?= $base_url ?>modules/lojas/lojas.php"><i class="fas fa-list me-2"></i> Listar Lojas</a></li>
        <?php if($usuario['nivel'] === 'admin'): ?>
        <li><a class="dropdown-item <?= (basename($_SERVER['PHP_SELF']) == 'loja_add.php' ? 'active' : '') ?>" href="<?= $base_url ?>modules/lojas/loja_add.php"><i class="fas fa-plus-circle me-2"></i> Adicionar Loja</a>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item <?= (basename($_SERVER['PHP_SELF']) == 'lojas.php' ? 'active' : '') ?>" href="<?= $base_url ?>modules/metas/metas_list.php"><i class="fas fa-list me-2"></i> Metas</a></li>
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
        <i class="fas fa-users"></i> Equipe
    </a>
    <ul class="dropdown-menu">
        <li><a class="dropdown-item <?= (basename($_SERVER['PHP_SELF']) == 'vendedoras_list.php' ? 'active' : '') ?>" href="<?= $base_url ?>modules/team/vendedoras_list.php"><i class="fas fa-list me-2"></i>Vendedoras</a></li>
        <li><a class="dropdown-item <?= (basename($_SERVER['PHP_SELF']) == 'vendedoras_add.php' ? 'active' : '') ?>" href="<?= $base_url ?>modules/team/vendedora_add.php"><i class="fas fa-user-plus me-2"></i>Adicionar Vendedora</a></li>
    </ul>
</li>
<?php endif; ?>

                    <!-- Relatórios (admin e gerente) -->
                    <?php if(in_array($usuario['nivel'], ['admin', 'gerente'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($modulo_atual == 'reports') ? 'active' : '' ?>" href="<?= $base_url ?>modules/reports/">
                            <i class="fas fa-chart-pie"></i> Relatórios
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Item de Usuários separado -->
<?php if($usuario['nivel'] === 'admin'): ?>
<li class="nav-item">
    <a class="nav-link <?= ($modulo_atual == 'usuarios') ? 'active' : '' ?>" href="<?= $base_url ?>modules/usuarios/usuario_list.php">
        <i class="fas fa-user-cog"></i> Usuários
    </a>
</li>
<?php endif; ?>>
                </ul>
                
                <!-- Menu do Usuário -->
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <img src="<?= $fotoPath ?>"
                                 class="profile-img me-2"
                                 alt="Foto de <?= $usuario['nome'] ?>"
                                 onerror="this.onerror=null;this.src='<?= $fotoPadrao ?>'">
                            <?= $usuario['nome'] ?>
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
    
    <!-- Container Principal -->
    <main class="container-fluid mt-4">
        <!-- Seu conteúdo aqui -->
    </main>
 
