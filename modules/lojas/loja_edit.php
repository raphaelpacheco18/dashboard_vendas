<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

// Verificar se o usuário está logado e se ele tem o nível de acesso adequado (administrador)
if (!usuarioLogado() || $_SESSION['nivel_acesso'] != 'admin') {
    header('Location: ../index.php'); // Redirecionar para a página inicial se o usuário não for admin
    exit();
}

// Verificar se o ID da loja foi passado
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Consultar a loja pelo ID
    $sql = "SELECT * FROM lojas WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $loja = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $endereco = $_POST['endereco'];
    $telefone = $_POST['telefone'];

    // Atualizar as informações da loja
    $sql = "UPDATE lojas SET nome = :nome, endereco = :endereco, telefone = :telefone WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':endereco', $endereco);
    $stmt->bindParam(':telefone', $telefone);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        echo "<script>
            alert('Loja atualizada com sucesso!');
            window.location.href = 'lojas.php';
        </script>";
    } else {
        echo "<script>
            alert('Erro ao atualizar a loja. Tente novamente.');
            window.history.back();
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Loja</title>
    <style>
        /* CSS UNIFICADO PARA PÁGINAS DO DASHBOARD */
        :root {
            --primary-color: #3498db;
            --success-color: #28a745;
            --hover-success: #218838;
            --danger-color: #dc3545;
            --warning-color: #fd7e14;
            --info-color: #17a2b8;
            --sidebar-gradient-start: #2c3e50;
            --sidebar-gradient-end: #34495e;
            --btn-gradient-start: #27ae60;
            --btn-gradient-end: #2ecc71;
            --btn-gradient-hover-start: #219653;
            --btn-gradient-hover-end: #27ae60;
            --text-dark: #2c3e50;
            --text-muted: #6c757d;
            --border-color: #dee2e6;
            --card-shadow: 0 5px 15px rgba(0,0,0,0.05);
            --filter-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        /* ESTRUTURA BASE */
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
        }

        /* FORMULÁRIO */
        .form-edit {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: var(--card-shadow);
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-dark);
        }

        .form-group input[type="text"] {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 16px;
        }

        .form-group input[type="text"]:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        /* BOTÕES */
        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            font-size: 16px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        /* TÍTULO */
        .page-title {
            color: var(--text-dark);
            font-size: 24px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include('../../templates/header.php'); ?>
    
    <div class="container">
        <h1 class="page-title">Editar Loja</h1>
        
        <div class="form-edit">
            <form action="loja_edit.php?id=<?php echo $loja['id']; ?>" method="post">
                <div class="form-group">
                    <label for="nome">Nome:</label>
                    <input type="text" name="nome" value="<?php echo htmlspecialchars($loja['nome']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="endereco">Endereço:</label>
                    <input type="text" name="endereco" value="<?php echo htmlspecialchars($loja['endereco']); ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Atualizar</button>
            </form>
        </div>
    </div>
</body>
</html>