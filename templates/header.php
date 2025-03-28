<?php
/**
 * HEADER SIMPLIFICADO - TODOS OS ITENS NO MENU PRINCIPAL
 * Versão corrigida - Erro de sintaxe resolvido
 */

// Configurações iniciais
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Controle de sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Dados do usuário
$usuario = [
    'nome' => isset($_SESSION['usuario_nome']) ? htmlspecialchars($_SESSION['usuario_nome']) : 'Visitante',
    'nivel' => $_SESSION['nivel_acesso'] ?? 'guest'
];

// Identifica a página atual
$pagina_atual = basename($_SERVER['PHP_SELF']);
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
    <link href="templates.css" rel="stylesheet">
    
    <style>
        body {
            padding-top: 70px;
            background-color: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .nav-link {
            transition: all 0.2s ease;
            margin: 0 5px;
            border-radius: 4px;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.15);
        }
        
        .nav-link.active {
            background: rgba(255,255,255,0.25);
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Menu Principal -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <!-- Brand -->
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-chart-line me-2"></i>
                <?php echo $usuario['nome']; ?>
            </a>
            
            <!-- Toggle Mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Itens do Menu -->
            <div class="collapse navbar-collapse" id="mainMenu">
                <ul class="navbar-nav me-auto">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($pagina_atual == 'index.php') ? 'active' : ''; ?>" href="index.php">
                            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                        </a>
                    </li>
                    
                    <!-- Vendas -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo (strpos($pagina_atual, 'sales') !== false) ? 'active' : ''; ?>" href="modules/sales/">
                            <i class="fas fa-cash-register me-1"></i> Vendas
                        </a>
                    </li>
                    
                    <!-- Produtos -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo (strpos($pagina_atual, 'products') !== false) ? 'active' : ''; ?>" href="modules/products/">
                            <i class="fas fa-boxes me-1"></i> Produtos
                        </a>
                    </li>
                    
                    <!-- Administração (apenas para admin) -->
                    <?php if ($usuario['nivel'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (strpos($pagina_atual, 'users') !== false) ? 'active' : ''; ?>" href="modules/users/">
                            <i class="fas fa-users-cog me-1"></i> Usuários
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (strpos($pagina_atual, 'stores') !== false) ? 'active' : ''; ?>" href="modules/stores/">
                            <i class="fas fa-store me-1"></i> Lojas
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <!-- Relatórios -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo (strpos($pagina_atual, 'reports') !== false) ? 'active' : ''; ?>" href="modules/reports/">
                            <i class="fas fa-chart-pie me-1"></i> Relatórios
                        </a>
                    </li>
                </ul>
                
                <!-- Botão Sair -->
                <div class="d-flex">
                    <a href="auth/logout.php" class="btn btn-outline-light">
                        <i class="fas fa-sign-out-alt me-1"></i> Sair
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Container Principal -->
    <main class="container-fluid mt-4">