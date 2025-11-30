<?php
require_once 'includes/config.php';

// Inicializar carrinho se não existir
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Processar ações do carrinho
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $build_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    switch ($action) {
        case 'add':
            if ($build_id > 0) {
                // Verificar se a build existe e tem stock
                $stmt = $pdo->prepare("SELECT * FROM builds WHERE id = ? AND stock > 0");
                $stmt->execute([$build_id]);
                $build = $stmt->fetch();
                
                if ($build) {
                    if (isset($_SESSION['cart'][$build_id])) {
                        $_SESSION['cart'][$build_id]['quantity']++;
                    } else {
                        $_SESSION['cart'][$build_id] = [
                            'id' => $build['id'],
                            'title' => $build['title'],
                            'price' => $build['price'],
                            'image' => $build['image_path'],
                            'quantity' => 1
                        ];
                    }
                    $_SESSION['success'] = 'Build adicionada ao carrinho!';
                } else {
                    $_SESSION['error'] = 'Build não disponível ou sem stock.';
                }
            }
            header('Location: cart.php');
            exit();
            
        case 'remove':
            if ($build_id > 0 && isset($_SESSION['cart'][$build_id])) {
                unset($_SESSION['cart'][$build_id]);
                $_SESSION['success'] = 'Build removida do carrinho!';
            }
            header('Location: cart.php');
            exit();
            
        case 'update':
            if ($build_id > 0 && isset($_POST['quantity'])) {
                $quantity = (int)$_POST['quantity'];
                if ($quantity > 0) {
                    $_SESSION['cart'][$build_id]['quantity'] = $quantity;
                } else {
                    unset($_SESSION['cart'][$build_id]);
                }
                $_SESSION['success'] = 'Carrinho atualizado!';
            }
            header('Location: cart.php');
            exit();
            
        case 'clear':
            $_SESSION['cart'] = [];
            $_SESSION['success'] = 'Carrinho limpo!';
            header('Location: cart.php');
            exit();
    }
}

// Calcular total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho - <?php echo SITE_NAME; ?></title>
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

    <!-- Page Header -->
    <section style="background: #f8f9fa; padding: 2rem 0;">
        <div class="container">
            <h1 style="text-align: center; color: #333; margin-bottom: 0.5rem;">Carrinho de Compras</h1>
            <p style="text-align: center; color: #666;">Revise os seus itens antes de finalizar a compra</p>
        </div>
    </section>

    <!-- Messages -->
    <div class="container" style="padding: 1rem 0;">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
    </div>

    <!-- Cart Content -->
    <section class="container" style="padding: 2rem 0;">
        <?php if (empty($_SESSION['cart'])): ?>
            <div style="text-align: center; padding: 3rem;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">🛒</div>
                <h3 style="margin-bottom: 1rem; color: #333;">Carrinho Vazio</h3>
                <p style="margin-bottom: 2rem; color: #666;">Adicione algumas builds ao seu carrinho para começar!</p>
                <a href="shop.php" class="btn">Continuar Comprando</a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 3rem;">
                <!-- Cart Items -->
                <div>
                    <h2 style="margin-bottom: 2rem; color: #333;">Itens no Carrinho</h2>
                    
                    <?php foreach ($_SESSION['cart'] as $build_id => $item): ?>
                        <div class="cart-item">
                            <img src="<?php echo $item['image'] ?: 'images/placeholder.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                 class="cart-item-image">
                            
                            <div class="cart-item-details">
                                <h4 class="cart-item-title"><?php echo htmlspecialchars($item['title']); ?></h4>
                                <div class="cart-item-price"><?php echo formatPrice($item['price']); ?> cada</div>
                            </div>
                            
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <form method="POST" action="cart.php?action=update&id=<?php echo $build_id; ?>" style="display: flex; align-items: center; gap: 0.5rem;">
                                    <label for="quantity_<?php echo $build_id; ?>">Qtd:</label>
                                    <input type="number" 
                                           id="quantity_<?php echo $build_id; ?>" 
                                           name="quantity" 
                                           value="<?php echo $item['quantity']; ?>" 
                                           min="1" 
                                           max="10" 
                                           style="width: 60px; padding: 0.3rem; border: 1px solid #ddd; border-radius: 3px;">
                                    <button type="submit" class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.9rem;">Atualizar</button>
                                </form>
                                
                                <div style="text-align: right;">
                                    <div style="font-weight: 600; color: #333; margin-bottom: 0.5rem;">
                                        Total: <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                                    </div>
                                    <a href="cart.php?action=remove&id=<?php echo $build_id; ?>" 
                                       class="btn btn-danger" 
                                       style="padding: 0.3rem 0.8rem; font-size: 0.9rem;"
                                       onclick="return confirm('Tem certeza que deseja remover este item?')">Remover</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div style="margin-top: 2rem;">
                        <a href="cart.php?action=clear" 
                           class="btn btn-danger" 
                           onclick="return confirm('Tem certeza que deseja limpar o carrinho?')">Limpar Carrinho</a>
                    </div>
                </div>
                
                <!-- Cart Summary -->
                <div>
                    <div class="cart-total">
                        <h3>Resumo do Pedido</h3>
                        
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Subtotal:</span>
                            <span><?php echo formatPrice($total); ?></span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Envio:</span>
                            <span>Grátis</span>
                        </div>
                        
                        <hr style="margin: 1rem 0;">
                        
                        <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 600; color: #333;">
                            <span>Total:</span>
                            <span><?php echo formatPrice($total); ?></span>
                        </div>
                        
                        <div style="margin-top: 2rem;">
                            <?php if (isLoggedIn()): ?>
                                <a href="checkout.php" class="btn" style="width: 100%; text-align: center; margin-bottom: 1rem;">Finalizar Compra</a>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-secondary" style="width: 100%; text-align: center; margin-bottom: 1rem;">Login para Comprar</a>
                            <?php endif; ?>
                            
                            <a href="shop.php" class="btn btn-secondary" style="width: 100%; text-align: center;">Continuar Comprando</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 <?php echo SITE_NAME; ?>. Todos os direitos reservados.</p>
        </div>
    </footer>
</body>
</html>
