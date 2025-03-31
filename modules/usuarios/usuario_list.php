<?php
// Iniciar a sessão
session_start();

// Incluir o arquivo de conexão
require_once '../../config/database.php';  // Caminho atualizado para a pasta config

// Consultar todos os usuários
$stmt = $pdo->query("SELECT * FROM usuarios");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Usuários</title>
    <style>
    :root {
        --primary-color: #3498db;
        --secondary-color: #2ecc71;
        --danger-color: #e74c3c;
        --light-gray: #f4f6f7;
        --dark-gray: #2c3e50;
        --shadow-light: rgba(0, 0, 0, 0.1);
        --shadow-dark: rgba(0, 0, 0, 0.2);
        --border-radius: 8px;
        --transition-duration: 0.3s;
        --font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        background-color: var(--light-gray);
        font-family: var(--font-family);
        padding: 20px;
        margin: 0;
    }

    .main-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .page-title {
        font-size: 2rem;
        color: var(--dark-gray);
        font-weight: 600;
    }

    .btn-action-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--info-color));
        color: black;
        padding: 10px 20px;
        font-weight: 600;
        border: none;
        border-radius: var(--border-radius);
        box-shadow: 0 4px 8px var(--shadow-light);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        transition: background-color var(--transition-duration), transform var(--transition-duration);
    }

    .btn-action-primary:hover {
        background-color: var(--secondary-color);
        transform: translateY(-2px);
        box-shadow: 0 6px 10px var(--shadow-dark);
    }

    .btn-action-primary i {
        margin-right: 8px;
    }

    /* Tabela */
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table {
        width: 100%;
        margin-bottom: 20px;
        border-collapse: collapse;
        border-radius: var(--border-radius);
        box-shadow: 0 4px 10px var(--shadow-light);
        background-color: white;
    }

    .table thead {
        background-color: var(--primary-color);
        color: white;
        position: sticky;
        top: 0;
    }

    .table th, .table td {
        padding: 12px 15px;
        text-align: left;
        font-size: 1rem;
        color: var(--dark-gray);
    }

    .table th {
        font-weight: 600;
    }

    .table td {
        font-weight: 400;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(52, 152, 219, 0.1);
    }

    .badge-ativo {
        background-color: #28a745;
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
    }

    .badge-inativo {
        background-color: #e74c3c;
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
    }

    /* Responsividade */
    @media (max-width: 992px) {
        .table th, .table td {
            padding: 10px;
        }

        .btn-action-primary {
            width: 100%;
            margin-top: 10px;
        }
    }

    @media (max-width: 768px) {
        .page-title {
            font-size: 1.6rem;
        }

        .btn-action-primary {
            width: 100%;
            justify-content: center;
        }

        .table-responsive {
            overflow-x: auto;
        }
    }
    </style>
</head>
<body>

<?php
    // Incluir o cabeçalho
    include('../../templates/header.php');  // Verifique o caminho
?>

<main class="main-container">
    <div class="header-actions">
        <div class="page-title">
            <i class="fas fa-users"></i>
            Lista de Usuários
        </div>
        <a href="usuario_add.php" class="btn-action-primary">
            <i class="fas fa-plus"></i> Adicionar Novo Usuário
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Nível de Acesso</th>
                    <th>Foto</th>
                    <th>Ativo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?= $usuario['id'] ?></td>
                        <td><?= $usuario['nome'] ?></td>
                        <td><?= $usuario['email'] ?></td>
                        <td><?= ucfirst($usuario['nivel_acesso']) ?></td>
                        <td>
                            <?php 
                                // Verificar se o campo foto está vazio ou se o arquivo existe
                                $foto = $usuario['foto'] ? 'uploads/'.$usuario['foto'] : 'uploads/default.jpg';
                            ?>
                            <img src="<?= $foto ?>" alt="Foto de <?= $usuario['nome'] ?>" width="40" height="40" style="border-radius: 50%;">
                        </td>
                        <td>
                            <span class="badge <?= $usuario['ativo'] ? 'badge-ativo' : 'badge-inativo' ?>">
                                <?= $usuario['ativo'] ? 'Ativo' : 'Desativado' ?>
                            </span>
                        </td>
                        <td>
                            <a href="usuario_edit.php?id=<?= $usuario['id'] ?>" class="btn btn-primary btn-sm">Editar</a>
                            <a href="usuario_delete.php?id=<?= $usuario['id'] ?>" class="btn btn-danger btn-sm">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php
    // Incluir o rodapé
    include('../../templates/footer.php');  // Verifique o caminho
?>

</body>
</html>
