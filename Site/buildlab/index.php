<?php
require_once 'includes/config.php';
require_once 'includes/recommendation.php';
require_once 'includes/notifications.php';

// Buscar builds em destaque
$stmt = $pdo->prepare("SELECT * FROM builds ORDER BY created_at DESC LIMIT 4");
$stmt->execute();
$featured_builds = $stmt->fetchAll();

// Processar recomendação se especificada
$recommendations = [];
if (isset($_GET['usage'])) {
    $recommendations = getBuildRecommendation($_GET['usage'], $_GET['budget'] ?? null);
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Loja Online de PCs Pré-Montados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
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
                    <a href="notifications.php" class="position-relative">
                        🔔 
                        <?php 
                        $unread_count = getUnreadNotificationCount($_SESSION['user_id']);
                        if ($unread_count > 0): 
                        ?>
                            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill notification-badge">
                                <?php echo $unread_count; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <!-- verifica se é admin -->
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Bem-vindo ao <?php echo SITE_NAME; ?></h1>
            <p>Descubra as melhores builds de computadores pré-montados para gaming, trabalho e entretenimento</p>
            <a href="shop.php" class="btn">Ver Loja</a>
            <a href="build_simulator.php" class="btn btn-secondary">Simulador de Build</a>
            <a href="budget_request.php" class="btn btn-secondary">Pedir Orçamento</a>
        </div>
    </section>

    <!-- Sistema de Recomendação -->
    <section class="container py-5">
        <div class="row">
            <div class="col-12">
                <h2 class="text-center mb-4">Encontre a Build Ideal para Si</h2>
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="usage" class="form-label">Uso Principal</label>
                                <select class="form-select" id="usage" name="usage">
                                    <option value="">Selecione o uso</option>
                                    <option value="gaming" <?php echo (isset($_GET['usage']) && $_GET['usage'] === 'gaming') ? 'selected' : ''; ?>>Gaming</option>
                                    <option value="work" <?php echo (isset($_GET['usage']) && $_GET['usage'] === 'work') ? 'selected' : ''; ?>>Trabalho</option>
                                    <option value="study" <?php echo (isset($_GET['usage']) && $_GET['usage'] === 'study') ? 'selected' : ''; ?>>Estudo</option>
                                    <option value="professional" <?php echo (isset($_GET['usage']) && $_GET['usage'] === 'professional') ? 'selected' : ''; ?>>Profissional</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="budget" class="form-label">Orçamento (€)</label>
                                <input type="number" class="form-control" id="budget" name="budget" 
                                       value="<?php echo $_GET['budget'] ?? ''; ?>" placeholder="Ex: 1000">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Recomendar Builds</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Recomendações -->
    <?php if (!empty($recommendations)): ?>
        <section class="container py-4">
            <h3 class="text-center mb-4">Builds Recomendadas para Si</h3>
            <div class="row">
                <?php foreach ($recommendations as $build): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card build-card h-100">
                            <img src="<?php echo $build['image_path'] ?: 'images/placeholder.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($build['title']); ?>" 
                                 class="card-img-top build-image">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($build['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($build['description']); ?></p>
                                <div class="build-price"><?php echo formatPrice($build['price']); ?></div>
                                <div class="mt-3">
                                    <a href="build.php?id=<?php echo $build['id']; ?>" class="btn btn-primary">Ver Detalhes</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Featured Builds -->
    <section class="container">
        <h2 style="text-align: center; margin-bottom: 2rem; color: #333;">Builds em Destaque</h2>
        
        <div class="builds-grid">
            <?php foreach ($featured_builds as $build): ?>
                <div class="build-card">
                    <img src="<?php echo $build['image_path'] ?: 'images/placeholder.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($build['title']); ?>" 
                         class="build-image">
                    
                    <div class="build-content">
                        <h3 class="build-title"><?php echo htmlspecialchars($build['title']); ?></h3>
                        <p class="build-description"><?php echo htmlspecialchars($build['description']); ?></p>
                        <div class="build-price"><?php echo formatPrice($build['price']); ?></div>
                        
                        <div class="build-actions">
                            <a href="build.php?id=<?php echo $build['id']; ?>" class="btn">Ver Mais</a>
                            <?php if (isLoggedIn()): ?>
                                <a href="cart.php?action=add&id=<?php echo $build['id']; ?>" class="btn btn-success">Adicionar ao Carrinho</a>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-secondary">Login para Comprar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- About Section -->
    <section style="background: white; padding: 3rem 0; margin: 3rem 0;">
        <div class="container">
            <div style="text-align: center; max-width: 800px; margin: 0 auto;">
                <h2 style="margin-bottom: 1rem; color: #333;">Sobre o <?php echo SITE_NAME; ?></h2>
                <p style="font-size: 1.1rem; line-height: 1.8; color: #666; margin-bottom: 2rem;">
                    Somos especialistas em computadores pré-montados, oferecendo as melhores configurações 
                    para gaming, trabalho e entretenimento. Todas as nossas builds são testadas e otimizadas 
                    para garantir o melhor desempenho.
                </p>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin-top: 3rem;">
                    <div style="text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 1rem;">🎮</div>
                        <h3 style="margin-bottom: 0.5rem;">Gaming</h3>
                        <p style="color: #666;">Builds otimizadas para jogos</p>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 1rem;">💼</div>
                        <h3 style="margin-bottom: 0.5rem;">Trabalho</h3>
                        <p style="color: #666;">Configurações profissionais</p>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 1rem;">🎨</div>
                        <h3 style="margin-bottom: 0.5rem;">Criação</h3>
                        <p style="color: #666;">Para designers e criadores</p>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 1rem;">💰</div>
                        <h3 style="margin-bottom: 0.5rem;">Orçamento</h3>
                        <p style="color: #666;">Pedidos personalizados</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 <?php echo SITE_NAME; ?>. Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
