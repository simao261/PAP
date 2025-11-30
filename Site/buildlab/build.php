<?php
require_once 'includes/config.php';

// Verificar se foi fornecido um ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: shop.php');
    exit();
}

$build_id = (int)$_GET['id'];

// Buscar dados da build
$stmt = $pdo->prepare("SELECT * FROM builds WHERE id = ?");
$stmt->execute([$build_id]);
$build = $stmt->fetch();

if (!$build) {
    header('Location: shop.php');
    exit();
}

// Buscar componentes da build
$stmt = $pdo->prepare("SELECT * FROM components WHERE build_id = ? ORDER BY category, model");
$stmt->execute([$build_id]);
$components = $stmt->fetchAll();

// Agrupar componentes por categoria
$components_by_category = [];
foreach ($components as $component) {
    $components_by_category[$component['category']][] = $component;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($build['title']); ?> - <?php echo SITE_NAME; ?></title>
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

    <!-- Build Details -->
    <section class="container" style="padding: 3rem 0;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin-bottom: 3rem;">
            <!-- Build Image -->
            <div>
                <img src="<?php echo $build['image_path'] ?: 'images/placeholder.jpg'; ?>" 
                     alt="<?php echo htmlspecialchars($build['title']); ?>" 
                     style="width: 100%; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            </div>
            
            <!-- Build Info -->
            <div>
                <h1 style="font-size: 2.5rem; margin-bottom: 1rem; color: #333;"><?php echo htmlspecialchars($build['title']); ?></h1>
                <p style="font-size: 1.2rem; color: #666; margin-bottom: 2rem; line-height: 1.6;"><?php echo htmlspecialchars($build['description']); ?></p>
                
                <div style="background: #f8f9fa; padding: 2rem; border-radius: 10px; margin-bottom: 2rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <span style="font-size: 1.5rem; font-weight: 600; color: #667eea;"><?php echo formatPrice($build['price']); ?></span>
                        <span style="color: #28a745; font-weight: 600;">Stock: <?php echo $build['stock']; ?></span>
                    </div>
                    
                    <div style="display: flex; gap: 1rem;">
                        <?php if (isLoggedIn() && $build['stock'] > 0): ?>
                            <a href="cart.php?action=add&id=<?php echo $build['id']; ?>" class="btn btn-success" style="flex: 1; text-align: center;">Adicionar ao Carrinho</a>
                            <a href="checkout.php?build_id=<?php echo $build['id']; ?>" class="btn" style="flex: 1; text-align: center;">Comprar Agora</a>
                        <?php elseif (!isLoggedIn()): ?>
                            <a href="login.php" class="btn btn-secondary" style="flex: 1; text-align: center;">Login para Comprar</a>
                        <?php else: ?>
                            <span class="btn btn-secondary" style="flex: 1; text-align: center; opacity: 0.6; cursor: not-allowed;">Sem Stock</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Components -->
        <div>
            <h2 style="margin-bottom: 2rem; color: #333;">Componentes Incluídos</h2>
            
            <?php if (empty($components_by_category)): ?>
                <div style="text-align: center; padding: 2rem; background: #f8f9fa; border-radius: 10px;">
                    <p>Nenhum componente listado para esta build.</p>
                </div>
            <?php else: ?>
                <?php foreach ($components_by_category as $category => $category_components): ?>
                    <div style="margin-bottom: 2rem;">
                        <h3 style="color: #667eea; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e9ecef;"><?php echo htmlspecialchars($category); ?></h3>
                        
                        <div style="display: grid; gap: 1rem;">
                            <?php foreach ($category_components as $component): ?>
                                <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                                    <div style="display: flex; justify-content: space-between; align-items: start;">
                                        <div style="flex: 1;">
                                            <h4 style="margin-bottom: 0.5rem; color: #333;"><?php echo htmlspecialchars($component['model']); ?></h4>
                                            <?php if ($component['specs']): ?>
                                                <p style="color: #666; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($component['specs']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($component['price'] > 0): ?>
                                            <div style="color: #667eea; font-weight: 600; font-size: 1.1rem;">
                                                <?php echo formatPrice($component['price']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Related Builds -->
    <section style="background: #f8f9fa; padding: 3rem 0;">
        <div class="container">
            <h2 style="text-align: center; margin-bottom: 2rem; color: #333;">Outras Builds</h2>
            
            <?php
            // Buscar outras builds
            $stmt = $pdo->prepare("SELECT * FROM builds WHERE id != ? ORDER BY RAND() LIMIT 3");
            $stmt->execute([$build_id]);
            $related_builds = $stmt->fetchAll();
            ?>
            
            <?php if (!empty($related_builds)): ?>
                <div class="builds-grid">
                    <?php foreach ($related_builds as $related_build): ?>
                        <div class="build-card">
                            <img src="<?php echo $related_build['image_path'] ?: 'images/placeholder.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($related_build['title']); ?>" 
                                 class="build-image">
                            
                            <div class="build-content">
                                <h3 class="build-title"><?php echo htmlspecialchars($related_build['title']); ?></h3>
                                <p class="build-description"><?php echo htmlspecialchars($related_build['description']); ?></p>
                                <div class="build-price"><?php echo formatPrice($related_build['price']); ?></div>
                                
                                <div class="build-actions">
                                    <a href="build.php?id=<?php echo $related_build['id']; ?>" class="btn">Ver Mais</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
