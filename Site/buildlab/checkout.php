<?php
require_once 'includes/config.php';

// Verificar se o utilizador está logado
requireLogin();

// Verificar se há itens no carrinho
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

// Processar checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Calcular total
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        // Criar encomenda
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$_SESSION['user_id'], $total]);
        $order_id = $pdo->lastInsertId();
        
        // Adicionar itens da encomenda
        foreach ($_SESSION['cart'] as $build_id => $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, build_id, quantity, price_each) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $build_id, $item['quantity'], $item['price']]);
            
            // Atualizar stock
            $stmt = $pdo->prepare("UPDATE builds SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $build_id]);
        }
        
        $pdo->commit();
        
        // Limpar carrinho
        $_SESSION['cart'] = [];
        $_SESSION['success'] = 'Encomenda realizada com sucesso! Número da encomenda: #' . $order_id;
        
        header('Location: profile.php');
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Erro ao processar a encomenda. Tente novamente.';
    }
}

// Calcular total do carrinho
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
    <title>Checkout - <?php echo SITE_NAME; ?></title>
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
                
                <a href="profile.php">Perfil</a>
                <?php if (isAdmin()): ?>
                    <a href="admin/">Admin</a>
                <?php endif; ?>
                <a href="logout.php">Sair</a>
            </div>
        </nav>
    </header>

    <!-- Page Header -->
    <section style="background: #f8f9fa; padding: 2rem 0;">
        <div class="container">
            <h1 style="text-align: center; color: #333; margin-bottom: 0.5rem;">Finalizar Compra</h1>
            <p style="text-align: center; color: #666;">Confirme os detalhes da sua encomenda</p>
        </div>
    </section>

    <!-- Messages -->
    <div class="container" style="padding: 1rem 0;">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
    </div>

    <!-- Checkout Content -->
    <section class="container" style="padding: 2rem 0;">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 3rem;">
            <!-- Order Form -->
            <div>
                <h2 style="margin-bottom: 2rem; color: #333;">Detalhes da Encomenda</h2>
                
                <form method="POST" class="form-container" style="max-width: none; margin: 0; box-shadow: none; padding: 0;">
                    <div class="form-group">
                        <label>Nome Completo</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Endereço de Entrega</label>
                        <textarea class="form-control" name="address" placeholder="Rua, Número, Código Postal, Cidade" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Telefone</label>
                        <input type="tel" class="form-control" name="phone" placeholder="+351 123 456 789" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Observações (Opcional)</label>
                        <textarea class="form-control" name="notes" placeholder="Instruções especiais para a entrega..."></textarea>
                    </div>
                    
                    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin: 2rem 0;">
                        <h3 style="margin-bottom: 1rem; color: #333;">Método de Pagamento</h3>
                        <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="radio" name="payment_method" value="transfer" checked>
                                <span>Transferência Bancária</span>
                            </label>
                        </div>
                        <div style="display: flex; gap: 1rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="radio" name="payment_method" value="mbway">
                                <span>MB Way</span>
                            </label>
                        </div>
                        <p style="color: #666; font-size: 0.9rem; margin-top: 1rem;">
                            Após a confirmação da encomenda, enviaremos os dados para pagamento.
                        </p>
                    </div>
                    
                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <a href="cart.php" class="btn btn-secondary">Voltar ao Carrinho</a>
                        <button type="submit" class="btn" style="flex: 1;">Confirmar Encomenda</button>
                    </div>
                </form>
            </div>
            
            <!-- Order Summary -->
            <div>
                <h2 style="margin-bottom: 2rem; color: #333;">Resumo da Encomenda</h2>
                
                <div style="background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); padding: 2rem;">
                    <h3 style="margin-bottom: 1.5rem; color: #333;">Itens</h3>
                    
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; border-bottom: 1px solid #e9ecef;">
                            <div>
                                <div style="font-weight: 600; margin-bottom: 0.3rem;"><?php echo htmlspecialchars($item['title']); ?></div>
                                <div style="color: #666; font-size: 0.9rem;">Qtd: <?php echo $item['quantity']; ?></div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 600;"><?php echo formatPrice($item['price'] * $item['quantity']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid #e9ecef;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Subtotal:</span>
                            <span><?php echo formatPrice($total); ?></span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Envio:</span>
                            <span>Grátis</span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 600; color: #333; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e9ecef;">
                            <span>Total:</span>
                            <span><?php echo formatPrice($total); ?></span>
                        </div>
                    </div>
                </div>
                
                <div style="background: #d1ecf1; padding: 1.5rem; border-radius: 10px; margin-top: 2rem;">
                    <h4 style="color: #0c5460; margin-bottom: 1rem;">ℹ️ Informações Importantes</h4>
                    <ul style="color: #0c5460; font-size: 0.9rem; line-height: 1.6;">
                        <li>O prazo de entrega é de 3-5 dias úteis</li>
                        <li>Enviaremos confirmação por email</li>
                        <li>Pode acompanhar o estado no seu perfil</li>
                        <li>Suporte disponível 24/7</li>
                    </ul>
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
</body>
</html>
