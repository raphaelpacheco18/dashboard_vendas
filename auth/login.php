<?php
require_once '../config/auth.php';
require_once '../config/database.php';

// Inicia a sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // VALIDAÇÃO DO TOKEN CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Erro de segurança: Token CSRF inválido!");
    }

    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];

    try {
        // Busca o usuário no banco de dados
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email AND ativo = 1 LIMIT 1");
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Autenticação bem-sucedida - armazena dados na sessão
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['nivel_acesso'] = $usuario['nivel_acesso'];
            $_SESSION['usuario_foto'] = $usuario['foto'];
            $_SESSION['logado'] = true;

            // Redireciona para a página inicial
            header('Location: ../index.php');
            exit();
        } else {
            $erro = "Credenciais inválidas. Por favor, tente novamente.";
        }
    } catch (PDOException $e) {
        error_log('Erro no login: ' . $e->getMessage());
        $erro = "Erro ao processar o login. Por favor, tente novamente mais tarde.";
    }
}

// Gerar novo token CSRF para cada carregamento da página
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso ao Sistema - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-dark: #3a56d4;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --text-color: #2b2d42;
            --light-gray: #f8f9fa;
            --white: #ffffff;
            --error-color: #ef233c;
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            color: var(--text-color);
        }

        .login-wrapper {
            display: flex;
            width: 100%;
            max-width: 1000px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 0.8s ease;
        }

        .login-illustration {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: var(--white);
            text-align: center;
        }

        .login-illustration img {
            max-width: 100%;
            height: auto;
            margin-bottom: 30px;
        }

        .login-illustration h2 {
            font-size: 28px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .login-illustration p {
            opacity: 0.9;
            line-height: 1.6;
        }

        .login-form-container {
            flex: 1;
            background: var(--white);
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            margin-bottom: 40px;
            text-align: center;
        }

        .login-header h1 {
            font-size: 28px;
            color: var(--primary-color);
            margin-bottom: 10px;
            font-weight: 700;
        }

        .login-header p {
            color: #6c757d;
            font-size: 15px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 14px;
            color: var(--text-color);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: var(--transition);
            background-color: var(--light-gray);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 38px;
            color: #6c757d;
            font-size: 16px;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 10px;
        }

        .btn-login:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.25);
        }

        .error-message {
            color: var(--error-color);
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
            font-weight: 500;
        }

        .footer-links {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
        }

        .footer-links a {
            color: var(--primary-color);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
            }
            
            .login-illustration {
                display: none;
            }
            
            .login-form-container {
                padding: 40px 30px;
            }
        }

        /* Dark mode toggle */
        .dark-mode-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            color: var(--text-color);
            cursor: pointer;
            font-size: 20px;
        }

        /* Dark mode styles */
        body.dark-mode {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #f8f9fa;
        }

        body.dark-mode .login-form-container {
            background: #1e293b;
        }

        body.dark-mode .login-header h1 {
            color: var(--success-color);
        }

        body.dark-mode .form-control {
            background-color: #2d3748;
            border-color: #4a5568;
            color: #f8f9fa;
        }

        body.dark-mode .login-header p {
            color: #a0aec0;
        }

        body.dark-mode .form-group label {
            color: #e2e8f0;
        }

        body.dark-mode .input-icon {
            color: #a0aec0;
        }
    </style>
</head>
<body>
    <button class="dark-mode-toggle" id="darkModeToggle">
        <i class="fas fa-moon"></i>
    </button>

    <div class="login-wrapper">
        <div class="login-illustration">
            <img src="https://illustrations.popsy.co/amber/login.svg" alt="Login Illustration" width="300">
            <h2>Bem-vindo de volta!</h2>
            <p>Acesse seu painel de controle para gerenciar suas vendas e produtos.</p>
        </div>

        <div class="login-form-container">
            <div class="login-header">
                <h1>Acessar Sistema</h1>
                <p>Entre com suas credenciais para acessar o dashboard</p>
            </div>

            <?php if (isset($erro)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" id="email" name="email" class="form-control" placeholder="seu@email.com" required>
                </div>

                <div class="form-group">
                    <label for="senha">Senha</label>
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="••••••••" required minlength="6">
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </button>
            </form>

            <div class="footer-links">
                <a href="forgot-password.php">Esqueceu sua senha?</a>
            </div>
        </div>
    </div>

    <script>
        // Dark mode toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        const body = document.body;
        const icon = darkModeToggle.querySelector('i');
        
        // Verificar preferência do usuário
        if (localStorage.getItem('darkMode') === 'enabled') {
            body.classList.add('dark-mode');
            icon.classList.replace('fa-moon', 'fa-sun');
        }
        
        darkModeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            
            if (body.classList.contains('dark-mode')) {
                localStorage.setItem('darkMode', 'enabled');
                icon.classList.replace('fa-moon', 'fa-sun');
            } else {
                localStorage.setItem('darkMode', 'disabled');
                icon.classList.replace('fa-sun', 'fa-moon');
            }
        });
    </script>
</body>
</html>