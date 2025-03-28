<?php
// Definir as credenciais do banco de dados
$host = 'localhost'; // Host do banco de dados
$user = 'root'; // Usuário do banco de dados
$password = ''; // Senha do banco de dados
$dbname = 'dashboard_vendas'; // Nome do banco de dados

// Criar a conexão com o banco de dados usando PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Define o modo de erro para exceções
} catch (PDOException $e) {
    // Caso haja erro na conexão, exibe a mensagem de erro
    die("Erro de conexão: " . $e->getMessage());
}
?>
