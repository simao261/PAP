<?php
require_once '../includes/config.php';

// Verificar se é administrador
requireAdmin();

// Estatísticas gerais
$stats = [];

// Total de utilizadores
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$stats['users'] = $stmt->fetch()['total'];

// Total de builds
$stmt = $pdo->query("SELECT COUNT(*) as total FROM builds");
$stats['builds'] = $stmt->fetch()['total'];

// Total de encomendas
$stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
$stats['orders'] = $stmt->fetch()['total'];

// Total de pedidos de orçamento
$stmt = $pdo->query("SELECT COUNT(*) as total FROM build_requests");
$stats['budget_requests'] = $stmt->fetch()['total'];

// Total de mensagens de suporte
$stmt = $pdo->query("SELECT COUNT(*) as total FROM support_messages");
$stats['support'] = $stmt->fetch()['total'];

// Receita total
$stmt = $pdo->query("SELECT SUM(total) as total FROM orders WHERE status = 'paid'");
$stats['revenue'] = $stmt->fetch()['total'] ?: 0;

// Encomendas recentes
$stmt = $pdo->prepare("
    SELECT o.*, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$stmt->execute();
$recent_orders = $stmt->fetchAll();

// Pedidos de orçamento recentes
$stmt = $pdo->prepare("
    SELECT br.*, u.username 
    FROM build_requests br 
    JOIN users u ON br.user_id = u.id 
    ORDER BY br.created_at DESC 
    LIMIT 5
");
$stmt->execute();
$recent_budget_requests = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Administração - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div style="display: flex; min-height: 100vh;">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div style="padding: 2rem; text-align: center; border-bottom: 1px solid #495057;">
                <h2 style="color: white; margin-bottom: 0.5rem;"><?php echo SITE_NAME; ?></h2>
                <p style="color: #adb5bd; font-size: 0.9rem;">Painel de Administração</p>
            </div>
            
            <ul>
                <li><a href="index.php">📊 Dashboard</a></li>
                <li><a href="builds.php">🖥️ Builds</a></li>
                <li><a href="orders.php">📦 Encomendas</a></li>
                <li><a href="budget_requests.php">💰 Pedidos de Orçamento</a></li>
                <li><a href="support.php">💬 Suporte</a></li>
                <li><a href="users.php">👥 Utilizadores</a></li>
                <li><a href="../index.php">🏠 Voltar ao Site</a></li>
                <li><a href="../logout.php">🚪 Sair</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="admin-content">
            <h1 style="margin-bottom: 2rem; color: #333;">Dashboard</h1>
            
            <!-- Stats Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
                <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center;">
                    <div style="font-size: 2.5rem; color: #667eea; margin-bottom: 0.5rem;">👥</div>
                    <h3 style="color: #333; margin-bottom: 0.5rem;"><?php echo $stats['users']; ?></h3>
                    <p style="color: #666;">Utilizadores</p>
                </div>
                
                <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center;">
                    <div style="font-size: 2.5rem; color: #28a745; margin-bottom: 0.5rem;">🖥️</div>
                    <h3 style="color: #333; margin-bottom: 0.5rem;"><?php echo $stats['builds']; ?></h3>
                    <p style="color: #666;">Builds</p>
                </div>
                
                <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center;">
                    <div style="font-size: 2.5rem; color: #ffc107; margin-bottom: 0.5rem;">📦</div>
                    <h3 style="color: #333; margin-bottom: 0.5rem;"><?php echo $stats['orders']; ?></h3>
                    <p style="color: #666;">Encomendas</p>
                </div>
                
                <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center;">
                    <div style="font-size: 2.5rem; color: #17a2b8; margin-bottom: 0.5rem;">💰</div>
                    <h3 style="color: #333; margin-bottom: 0.5rem;"><?php echo $stats['budget_requests']; ?></h3>
                    <p style="color: #666;">Pedidos de Orçamento</p>
                </div>
                
                <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center;">
                    <div style="font-size: 2.5rem; color: #dc3545; margin-bottom: 0.5rem;">💬</div>
                    <h3 style="color: #333; margin-bottom: 0.5rem;"><?php echo $stats['support']; ?></h3>
                    <p style="color: #666;">Mensagens de Suporte</p>
                </div>
                
                <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center;">
                    <div style="font-size: 2.5rem; color: #6f42c1; margin-bottom: 0.5rem;">💵</div>
                    <h3 style="color: #333; margin-bottom: 0.5rem;"><?php echo formatPrice($stats['revenue']); ?></h3>
                    <p style="color: #666;">Receita Total</p>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;">
                <!-- Recent Orders -->
                <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <h3 style="margin-bottom: 1.5rem; color: #333;">Encomendas Recentes</h3>
                    
                    <?php if (empty($recent_orders)): ?>
                        <p style="color: #666; text-align: center; padding: 2rem;">Nenhuma encomenda encontrada.</p>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Utilizador</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['username']); ?></td>
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
                                            <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Recent Budget Requests -->
                <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <h3 style="margin-bottom: 1.5rem; color: #333;">Pedidos de Orçamento Recentes</h3>
                    
                    <?php if (empty($recent_budget_requests)): ?>
                        <p style="color: #666; text-align: center; padding: 2rem;">Nenhum pedido encontrado.</p>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Utilizador</th>
                                        <th>Orçamento</th>
                                        <th>Estado</th>
                                        <th>Data</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_budget_requests as $request): ?>
                                        <tr>
                                            <td>#<?php echo $request['id']; ?></td>
                                            <td><?php echo htmlspecialchars($request['username']); ?></td>
                                            <td><?php echo $request['budget'] ? formatPrice($request['budget']) : '-'; ?></td>
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
                                            <td><?php echo date('d/m/Y', strtotime($request['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
