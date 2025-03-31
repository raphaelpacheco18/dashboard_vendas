<?php
// Iniciar a sessão
session_start();

// Incluir o arquivo de conexão
require_once('../../config/database.php');  // Alterado para o caminho correto

// Verificar se o id foi passado
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Consultar o status do usuário
    $stmt = $pdo->prepare("SELECT ativo FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // Alternar o valor de 'ativo'
        $novo_status = $usuario['ativo'] == 1 ? 0 : 1;

        // Atualizar o status do usuário
        $stmt = $pdo->prepare("UPDATE usuarios SET ativo = ? WHERE id = ?");
        $stmt->execute([$novo_status, $id]);

        // Redirecionar de volta para a lista de usuários
        header("Location: usuario_list.php");
        exit();
    } else {
        echo "Usuário não encontrado.";
    }
} else {
    echo "ID do usuário não especificado.";
}
?>
