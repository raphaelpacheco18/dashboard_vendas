<?php
// Verificar se a sessão já foi iniciada antes de iniciar
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Principal - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="templates/templates.css">
</head>
<body>
    <?php include 'templates/header.php'; ?>
    
    <div class="container mt-4 text-center">
        <h2 class="fw-bold">Página Principal - Dashboard</h2>
        <p class="lead">Bem-vindo, <strong><?= isset($_SESSION['usuario_nome']) ? $_SESSION['usuario_nome'] : 'Usuário'; ?></strong>!</p>
        <p>Você tem acesso a todas as funcionalidades conforme seu nível de permissão.</p>
    </div>
    
    <footer class="bg-dark text-white text-center py-3 mt-5">
        &copy; 2025 Dashboard de Vendas. Todos os direitos reservados.
    </footer>
</body>
</html>
