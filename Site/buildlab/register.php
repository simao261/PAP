<?php
require_once 'includes/config.php';

// Se já estiver logado, redirecionar
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

// Processar registo
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    // Validações
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $errors[] = 'Por favor, preencha todos os campos.';
    }
    
    if (strlen($username) < 3) {
        $errors[] = 'O nome de utilizador deve ter pelo menos 3 caracteres.';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Por favor, insira um email válido.';
    }
    
    if (strlen($password) < 6) {
        $errors[] = 'A palavra-passe deve ter pelo menos 6 caracteres.';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'As palavras-passe não coincidem.';
    }
    
    // Verificar se email já existe
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Este email já está registado.';
        }
    }
    
    // Verificar se username já existe
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = 'Este nome de utilizador já está em uso.';
        }
    }
    
    // Criar conta se não houver erros
    if (empty($errors)) {
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$username, $email, $password_hash]);
            
            $_SESSION['success'] = 'Conta criada com sucesso! Pode agora fazer login.';
            header('Location: login.php');
            exit();
            
        } catch (Exception $e) {
            $errors[] = 'Erro ao criar conta. Tente novamente.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registar - <?php echo SITE_NAME; ?></title>
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

    <!-- Register Form -->
    <section class="container" style="padding: 3rem 0;">
        <div class="form-container">
            <h1 style="text-align: center; margin-bottom: 2rem; color: #333;">Criar Conta</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 1.5rem;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Nome de Utilizador</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           class="form-control" 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                           required>
                </div>
                
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
                    <small style="color: #666; font-size: 0.9rem;">Mínimo 6 caracteres</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar Palavra-passe</label>
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           class="form-control" 
                           required>
                </div>
                
                <button type="submit" class="btn" style="width: 100%; margin-bottom: 1rem;">Criar Conta</button>
            </form>
            
            <div style="text-align: center;">
                <p style="color: #666; margin-bottom: 1rem;">Já tem conta?</p>
                <a href="login.php" class="btn btn-secondary">Entrar</a>
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
