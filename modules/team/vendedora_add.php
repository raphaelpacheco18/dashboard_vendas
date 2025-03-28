<?php
// Incluir o arquivo de autenticação
require_once '../../config/auth.php';
require_once '../../config/database.php';  // Incluir a conexão com o banco de dados

// Verificar se o usuário está logado
if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receber dados do formulário
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $loja_id = $_POST['loja_id']; // ID da loja vinculada

    // Validar dados (exemplo simples)
    if (!empty($nome) && !empty($email) && !empty($loja_id)) {
        // Preparar a consulta para inserir a vendedora
        $sql = "INSERT INTO vendedoras (nome, email, loja_id) VALUES (:nome, :email, :loja_id)";
        $stmt = $pdo->prepare($sql);

        // Vincular parâmetros
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':loja_id', $loja_id);

        // Executar a consulta
        if ($stmt->execute()) {
            // Se o cadastro for bem-sucedido, redirecionar para a lista de vendedoras
            header('Location: vendedoras_list.php');
            exit();
        } else {
            echo "Erro ao cadastrar vendedora.";
        }
    } else {
        echo "Por favor, preencha todos os campos.";
    }
}

// Consultar lojas para preencher o campo de seleção
$sql = "SELECT * FROM lojas";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$lojas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Vendedora</title>
    <link rel="stylesheet" href="../../assets/css/style.css"> <!-- Ajuste se necessário -->
</head>
<body>
<?php include('../../templates/header.php'); ?>
<h1>Cadastrar Vendedora</h1>

<form action="vendedora_add.php" method="post">
    <label for="nome">Nome:</label>
    <input type="text" name="nome" required>

    <label for="email">E-mail:</label>
    <input type="email" name="email" required>

    <label for="telefone">Telefone:</label>
    <input type="text" name="telefone" required>

    <label for="loja_id">Loja:</label>
    <select name="loja_id" required>
        <?php foreach ($lojas as $loja): ?>
            <option value="<?php echo $loja['id']; ?>"><?php echo $loja['nome']; ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Cadastrar</button>
</form>

</body>
</html>
