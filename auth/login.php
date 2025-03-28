<?php
// Incluir a configuração de autenticação
require_once '../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recuperar os dados do formulário
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Chamar a função de login
    if (login($email, $senha)) {
        // Se o login for bem-sucedido, redireciona para a página inicial
        header('Location: ../index.php');
        exit();
    } else {
        // Se falhar, exibe uma mensagem de erro
        $erro = "E-mail ou senha inválidos!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dashboard de Vendas</title>
    <link rel="stylesheet" href="../assets/css/style.css"> <!-- Seu arquivo CSS -->
    <style>
        /* Reset de estilos */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Corpo da Página */
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            animation: fadeIn 1s ease-in-out;
        }

        /* Animação de Fade-in */
        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(50px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            font-size: 28px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .login-container label {
            font-weight: bold;
            margin-bottom: 8px;
            display: block;
            font-size: 14px;
            color: #444;
        }

        .login-container input[type="email"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            background-color: #f7f7f7;
            transition: border-color 0.3s;
        }

        .login-container input[type="email"]:focus,
        .login-container input[type="password"]:focus {
            border-color: #2575fc;
            outline: none;
        }

        .login-container button {
            width: 100%;
            padding: 12px;
            background-color: #2575fc;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .login-container button:hover {
            background-color: #6a11cb;
        }

        .login-container p {
            color: red;
            text-align: center;
            margin-top: 10px;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .login-container {
                width: 80%;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($erro)) { echo "<p>$erro</p>"; } ?>
        <form method="POST" action="login.php">
            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" required>

            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>

            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>
