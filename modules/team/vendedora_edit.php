<?php
// Incluir o arquivo de autenticação
require_once '../../config/auth.php';
require_once '../../config/database.php';

// Verificar se o usuário está logado
if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Verificar se o ID da vendedora foi passado via GET
if (!isset($_GET['id'])) {
    echo "Vendedora não encontrada!";
    exit();
}

$id = $_GET['id'];

// Consultar os dados da vendedora
$sql = "SELECT * FROM vendedoras WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();
$vendedora = $stmt->fetch(PDO::FETCH_ASSOC);

// Se a vendedora não for encontrada
if (!$vendedora) {
    echo "Vendedora não encontrada!";
    exit();
}

// Consultar lojas para preencher o campo de seleção
$sql = "SELECT * FROM lojas";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$lojas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receber dados do formulário
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $loja_id = $_POST['loja_id'];

    // Validar dados
    if (!empty($nome) && !empty($email) && !empty($telefone) && !empty($loja_id)) {
        // Preparar a consulta para atualizar os dados da vendedora
        $sql = "UPDATE vendedoras SET nome = :nome, email = :email, telefone = :telefone, loja_id = :loja_id WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        // Vincular parâmetros
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':loja_id', $loja_id);
        $stmt->bindParam(':id', $id);

        // Executar a consulta
        if ($stmt->execute()) {
            // Se a atualização for bem-sucedida, redirecionar para a lista de vendedoras
            header('Location: vendedoras_list.php');
            exit();
        } else {
            echo "Erro ao atualizar vendedora.";
        }
    } else {
        echo "Por favor, preencha todos os campos.";
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Vendedora</title>
    <link rel="stylesheet" href="../../assets/css/style.css"> <!-- Ajuste se necessário -->
</head>
<body>
<?php include('../../templates/header.php'); ?>
<h1>Editar Vendedora</h1>

<form action="vendedora_edit.php?id=<?php echo $id; ?>" method="post">
    <label for="nome">Nome:</label>
    <input type="text" name="nome" value="<?php echo $vendedora['nome']; ?>" required>

    <label for="email">E-mail:</label>
    <input type="email" name="email" value="<?php echo $vendedora['email']; ?>" required>

    <label for="telefone">Telefone:</label>
    <input type="text" name="telefone" value="<?php echo $vendedora['telefone']; ?>" required>

    <label for="loja_id">Loja:</label>
    <select name="loja_id" required>
        <?php foreach ($lojas as $loja): ?>
            <option value="<?php echo $loja['id']; ?>" <?php echo ($vendedora['loja_id'] == $loja['id']) ? 'selected' : ''; ?>><?php echo $loja['nome']; ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Salvar</button>
</form>

</body>
</html>
