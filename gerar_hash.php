<?php
// Senha a ser hashada
$senha = 'senha123';

// Gerar o hash da senha
$hash = password_hash($senha, PASSWORD_DEFAULT);

// Exibir o hash gerado
echo "O hash da senha é: " . $hash;
?>
