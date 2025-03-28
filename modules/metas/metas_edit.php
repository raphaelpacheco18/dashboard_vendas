<?php
require_once '../../config/auth.php';
require_once '../../config/database.php';

// Verificar se o usuário está logado
if (!usuarioLogado()) {
    header('Location: ../../auth/login.php');
    exit();
}

// Verificar se o ID da meta foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "ID da meta não fornecido!";
    exit();
}

$id = $_GET['id'];

// Consultar a meta pelo ID
$sql = "SELECT * FROM metas WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$meta = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar se a meta foi encontrada
if (!$meta) {
    echo "Meta não encontrada!";
    exit();
}

// Consultar lojas e vendedoras para preencher os selects
$lojas = $pdo->query("SELECT * FROM lojas")->fetchAll(PDO::FETCH_ASSOC);
$vendedoras = $pdo->query("SELECT * FROM vendedoras")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loja_id = $_POST['loja_id'];
    $vendedora_id = $_POST['vendedora_id'];
    $periodo = $_POST['periodo'];
    $meta_valor = $_POST['meta_valor'];
    $meta_quantidade = $_POST['meta_quantidade'];
    $bonificacao = $_POST['bonificacao'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];

    // Atualizar a meta no banco de dados
    $sql = "UPDATE metas SET loja_id = :loja_id, vendedora_id = :vendedora_id, periodo = :periodo, meta_valor = :meta_valor, 
            meta_quantidade = :meta_quantidade, bonificacao = :bonificacao, data_inicio = :data_inicio, data_fim = :data_fim 
            WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':loja_id', $loja_id);
    $stmt->bindParam(':vendedora_id', $vendedora_id);
    $stmt->bindParam(':periodo', $periodo);
    $stmt->bindParam(':meta_valor', $meta_valor);
    $stmt->bindParam(':meta_quantidade', $meta_quantidade);
    $stmt->bindParam(':bonificacao', $bonificacao);
    $stmt->bindParam(':data_inicio', $data_inicio);
    $stmt->bindParam(':data_fim', $data_fim);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        // Redirecionar para a lista de metas após a atualização
        header('Location: metas_list.php');
        exit();
    } else {
        echo "Erro ao atualizar a meta.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Meta</title>
</head>
<body>
<?php include('../../templates/header.php'); ?>
<h1>Editar Meta de Vendas</h1>

<form action="metas_edit.php?id=<?php echo $meta['id']; ?>" method="post">
    <label for="loja_id">Loja:</label>
    <select name="loja_id" required>
        <option value="">Selecione uma Loja</option>
        <?php foreach ($lojas as $loja): ?>
            <option value="<?php echo $loja['id']; ?>" <?php echo ($loja['id'] == $meta['loja_id']) ? 'selected' : ''; ?>>
                <?php echo $loja['nome']; ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="vendedora_id">Vendedora:</label>
    <select name="vendedora_id" required>
        <option value="">Selecione uma Vendedora</option>
        <?php foreach ($vendedoras as $vendedora): ?>
            <option value="<?php echo $vendedora['id']; ?>" <?php echo ($vendedora['id'] == $meta['vendedora_id']) ? 'selected' : ''; ?>>
                <?php echo $vendedora['nome']; ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="periodo">Período:</label>
    <select name="periodo" required>
        <option value="diario" <?php echo ($meta['periodo'] == 'diario') ? 'selected' : ''; ?>>Diário</option>
        <option value="mensal" <?php echo ($meta['periodo'] == 'mensal') ? 'selected' : ''; ?>>Mensal</option>
        <option value="trimestral" <?php echo ($meta['periodo'] == 'trimestral') ? 'selected' : ''; ?>>Trimestral</option>
    </select>

    <label for="meta_valor">Valor da Meta:</label>
    <input type="number" name="meta_valor" step="0.01" value="<?php echo $meta['meta_valor']; ?>" required>

    <label for="meta_quantidade">Quantidade da Meta:</label>
    <input type="number" name="meta_quantidade" value="<?php echo $meta['meta_quantidade']; ?>" required>

    <label for="bonificacao">Bonificação:</label>
    <input type="number" name="bonificacao" step="0.01" value="<?php echo $meta['bonificacao']; ?>" required>

    <label for="data_inicio">Data de Início:</label>
    <input type="date" name="data_inicio" value="<?php echo $meta['data_inicio']; ?>" required>

    <label for="data_fim">Data de Fim:</label>
    <input type="date" name="data_fim" value="<?php echo $meta['data_fim']; ?>" required>

    <button type="submit">Salvar Alterações</button>
</form>

</body>
</html>
