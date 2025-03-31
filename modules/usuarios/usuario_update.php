<?php
// Iniciar a sessão
session_start();

// Incluir a conexão com o banco de dados
require_once('../../config/database.php');

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pegar os dados do formulário
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $nivel_acesso = $_POST['nivel_acesso'];
    $ativo = isset($_POST['ativo']) ? 1 : 0; // Se o checkbox ativo for marcado, será 1, caso contrário 0
    $senha = $_POST['senha'] ? password_hash($_POST['senha'], PASSWORD_DEFAULT) : null; // Se a senha for alterada, fazer o hash, caso contrário não alterar
    
    // Atualizar os dados do usuário no banco de dados
    try {
        $sql = "UPDATE usuarios SET nome = :nome, email = :email, nivel_acesso = :nivel_acesso, ativo = :ativo";
        
        if ($senha) {
            $sql .= ", senha = :senha"; // Se a senha for fornecida, incluí-la na query
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        
        // Bind dos parâmetros
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':nivel_acesso', $nivel_acesso);
        $stmt->bindParam(':ativo', $ativo);
        
        if ($senha) {
            $stmt->bindParam(':senha', $senha);
        }
        
        $stmt->bindParam(':id', $id);
        
        // Executar a query
        $stmt->execute();
        
        // Redirecionar para a página de listagem de usuários
        header('Location: usuario_list.php');
        exit();
        
    } catch (PDOException $e) {
        // Caso ocorra um erro na execução da query
        echo "Erro: " . $e->getMessage();
    }
} else {
    // Se não for uma requisição POST
    echo "Ação inválida!";
}
?>
