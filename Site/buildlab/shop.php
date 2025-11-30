<?php
require_once 'includes/config.php';

// Buscar todas as builds
$stmt = $pdo->prepare("SELECT * FROM builds ORDER BY created_at DESC");
$stmt->execute();
$builds = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loja - <?php echo SITE_NAME; ?></title>
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
                
                <?php if (isLoggedIn()): ?>
                    <a href="profile.php">Perfil</a>
                    <?php if (isAdmin()): ?>
                        <a href="admin/">Admin</a>
                    <?php endif; ?>
                    <a href="logout.php">Sair</a>
                <?php else: ?>
                    <a href="login.php">Entrar</a>
                    <a href="register.php">Registar</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <!-- Page Header -->
    <section style="background: #f8f9fa; padding: 2rem 0;">
        <div class="container">
            <h1 style="text-align: center; color: #333; margin-bottom: 0.5rem;">Loja de Builds</h1>
            <p style="text-align: center; color: #666;">Descubra todas as nossas configurações de computadores</p>
        </div>
    </section>

    <!-- Builds Grid -->
    <section class="container" style="padding: 3rem 0;">
        <?php if (empty($builds)): ?>
            <div style="text-align: center; padding: 3rem;">
                <h3>Nenhuma build disponível</h3>
                <p>Volte em breve para ver as nossas ofertas!</p>
            </div>
        <?php else: ?>
            <div class="builds-grid">
                <?php foreach ($builds as $build): ?>
                    <div class="build-card">
                        <img src="<?php echo $build['image_path'] ?: 'images/placeholder.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($build['title']); ?>" 
                             class="build-image">
                        
                        <div class="build-content">
                            <h3 class="build-title"><?php echo htmlspecialchars($build['title']); ?></h3>
                            <p class="build-description"><?php echo htmlspecialchars($build['description']); ?></p>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <div class="build-price"><?php echo formatPrice($build['price']); ?></div>
                                <div style="color: #28a745; font-weight: 600;">
                                    Stock: <?php echo $build['stock']; ?>
                                </div>
                            </div>
                            
                            <div class="build-actions">
                                <a href="build.php?id=<?php echo $build['id']; ?>" class="btn">Ver Detalhes</a>
                                <?php if (isLoggedIn() && $build['stock'] > 0): ?>
                                    <a href="cart.php?action=add&id=<?php echo $build['id']; ?>" class="btn btn-success">Adicionar ao Carrinho</a>
                                <?php elseif (!isLoggedIn()): ?>
                                    <a href="login.php" class="btn btn-secondary">Login para Comprar</a>
                                <?php else: ?>
                                    <span class="btn btn-secondary" style="opacity: 0.6; cursor: not-allowed;">Sem Stock</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- Call to Action -->
    <section style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 3rem 0; text-align: center;">
        <div class="container">
            <h2 style="margin-bottom: 1rem;">Não encontrou o que procura?</h2>
            <p style="margin-bottom: 2rem; font-size: 1.1rem;">Peça um orçamento personalizado e criamos a build perfeita para si!</p>
            <a href="budget_request.php" class="btn" style="background: white; color: #667eea;">Pedir Orçamento</a>
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
