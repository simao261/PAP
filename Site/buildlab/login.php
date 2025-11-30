<?php
require_once 'includes/config.php';

// Se já estiver logado, redirecionar
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

// Processar login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        // Buscar utilizador
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Login bem-sucedido
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // Atualizar último login
            $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            // Redirecionar
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
            header('Location: ' . $redirect);
            exit();
        } else {
            $error = 'Email ou palavra-passe incorretos.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <a href="index.php" class="logo"><?php echo SITE_NAME; ?></a>
            
            <ul class="nav-links">
                <li><a href="index.php">Início</a></li>
                <li><a href="shop.php">Loja</a></li>
                <li><a href="build_simulator.php">Simulador</a></li>
                <li><a href="support.php">Suporte</a></li>
                <li><a href="budget_request.php">Pedir Orçamento</a></li>
            </ul>
            
            <div class="user-menu">
                <a href="cart.php" class="cart-icon">
                    🛒 <span class="cart-count"><?php echo getCartCount(); ?></span>
                </a>
                
                <a href="login.php">Entrar</a>
                <a href="register.php">Registar</a>
            </div>
        </nav>
    </header>

    <!-- Login Form -->
    <section class="container" style="padding: 3rem 0;">
        <div class="form-container">
            <h1 style="text-align: center; margin-bottom: 2rem; color: #333;">Entrar na Conta</h1>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="password">Palavra-passe</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control" 
                           required>
                </div>
                
                <button type="submit" class="btn" style="width: 100%; margin-bottom: 1rem;">Entrar</button>
            </form>
            
            <div style="text-align: center;">
                <p style="color: #666; margin-bottom: 1rem;">Ainda não tem conta?</p>
                <a href="register.php" class="btn btn-secondary">Criar Conta</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 <?php echo SITE_NAME; ?>. Todos os direitos reservados.</p>
        </div>
    </footer>
</body>
</html>
