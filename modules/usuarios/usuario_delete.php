/* usuario_delete.php - Exclusão de Usuário */
<?php
require_once '../../config/database.php';
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM usuarios WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    header('Location: usuario_list.php'); exit();
}
?>