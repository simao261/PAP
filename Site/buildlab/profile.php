<?php
require_once 'includes/config.php';

// Verificar se o utilizador está logado
requireLogin();

// Buscar encomendas do utilizador
$stmt = $pdo->prepare("
    SELECT o.*, 
           GROUP_CONCAT(b.title SEPARATOR ', ') as build_titles,
           GROUP_CONCAT(oi.quantity SEPARATOR ', ') as quantities
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    LEFT JOIN builds b ON oi.build_id = b.id 
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

// Buscar pedidos de orçamento do utilizador
$stmt = $pdo->prepare("SELECT * FROM build_requests WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$budget_requests = $stmt->fetchAll();

// Buscar mensagens de suporte do utilizador
$stmt = $pdo->prepare("SELECT * FROM support_messages WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$support_messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - <?php echo SITE_NAME; ?></title>
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
            <h1 style="text-align: center; color: #333; margin-bottom: 0.5rem;">Bem-vindo, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <p style="text-align: center; color: #666;">Gerir a sua conta e acompanhar as suas encomendas</p>
        </div>
    </section>

    <!-- Messages -->
    <div class="container" style="padding: 1rem 0;">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
    </div>

    <!-- Profile Content -->
    <section class="container" style="padding: 2rem 0;">
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 3rem;">
            <!-- Profile Info -->
            <div>
                <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                    <h2 style="margin-bottom: 1.5rem; color: #333;">Informações da Conta</h2>
                    
                    <div style="margin-bottom: 1rem;">
                        <strong>Nome de Utilizador:</strong><br>
                        <span style="color: #666;"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <strong>Email:</strong><br>
                        <span style="color: #666;"><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                    </div>
                    
                    <div style="margin-bottom: 1rem;">
                        <strong>Tipo de Conta:</strong><br>
                        <span style="color: #667eea; font-weight: 600;">
                            <?php echo $_SESSION['user_role'] === 'admin' ? 'Administrador' : 'Utilizador'; ?>
                        </span>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <h3 style="margin-bottom: 1.5rem; color: #333;">Ações Rápidas</h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <a href="shop.php" class="btn">Continuar Comprando</a>
                        <a href="budget_request.php" class="btn btn-secondary">Pedir Orçamento</a>
                        <a href="support.php" class="btn btn-secondary">Contactar Suporte</a>
                    </div>
                </div>
            </div>
            
            <!-- Orders and Requests -->
            <div>
                <!-- Orders -->
                <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                    <h2 style="margin-bottom: 1.5rem; color: #333;">As Suas Encomendas</h2>
                    
                    <?php if (empty($orders)): ?>
                        <div style="text-align: center; padding: 2rem; color: #666;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">📦</div>
                            <p>Nenhuma encomenda encontrada.</p>
                            <a href="shop.php" class="btn" style="margin-top: 1rem;">Fazer Primeira Compra</a>
                        </div>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nº Encomenda</th>
                                        <th>Data</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Builds</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                            <td><?php echo formatPrice($order['total']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                                    <?php 
                                                    $status_labels = [
                                                        'pending' => 'Pendente',
                                                        'paid' => 'Pago',
                                                        'shipped' => 'Enviado',
                                                        'cancelled' => 'Cancelado'
                                                    ];
                                                    echo $status_labels[$order['status']] ?? $order['status'];
                                                    ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($order['build_titles']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Budget Requests -->
                <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                    <h2 style="margin-bottom: 1.5rem; color: #333;">Pedidos de Orçamento</h2>
                    
                    <?php if (empty($budget_requests)): ?>
                        <div style="text-align: center; padding: 2rem; color: #666;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">💰</div>
                            <p>Nenhum pedido de orçamento encontrado.</p>
                            <a href="budget_request.php" class="btn" style="margin-top: 1rem;">Fazer Primeiro Pedido</a>
                        </div>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Orçamento</th>
                                        <th>Estado</th>
                                        <th>CPU</th>
                                        <th>GPU</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($budget_requests as $request): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($request['created_at'])); ?></td>
                                            <td><?php echo $request['budget'] ? formatPrice($request['budget']) : 'Não especificado'; ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $request['status']; ?>">
                                                    <?php 
                                                    $status_labels = [
                                                        'pending' => 'Pendente',
                                                        'viewed' => 'Visualizado',
                                                        'responded' => 'Respondido',
                                                        'closed' => 'Fechado'
                                                    ];
                                                    echo $status_labels[$request['status']] ?? $request['status'];
                                                    ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($request['cpu_preference'] ?: '-'); ?></td>
                                            <td><?php echo htmlspecialchars($request['gpu_preference'] ?: '-'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Support Messages -->
                <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <h2 style="margin-bottom: 1.5rem; color: #333;">Mensagens de Suporte</h2>
                    
                    <?php if (empty($support_messages)): ?>
                        <div style="text-align: center; padding: 2rem; color: #666;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">💬</div>
                            <p>Nenhuma mensagem de suporte encontrada.</p>
                            <a href="support.php" class="btn" style="margin-top: 1rem;">Contactar Suporte</a>
                        </div>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Assunto</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($support_messages as $message): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($message['created_at'])); ?></td>
                                            <td><?php echo htmlspecialchars($message['subject'] ?: 'Sem assunto'); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $message['status']; ?>">
                                                    <?php 
                                                    $status_labels = [
                                                        'open' => 'Aberto',
                                                        'answered' => 'Respondido',
                                                        'closed' => 'Fechado'
                                                    ];
                                                    echo $status_labels[$message['status']] ?? $message['status'];
                                                    ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
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
