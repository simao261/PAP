<?php
require_once '../includes/config.php';

// Verificar se é administrador
requireAdmin();

// Processar atualização de estado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        $_SESSION['success'] = 'Estado da encomenda atualizado!';
    } catch (Exception $e) {
        $_SESSION['error'] = 'Erro ao atualizar estado.';
    }
    
    header('Location: orders.php');
    exit();
}

// Buscar todas as encomendas
$stmt = $pdo->prepare("
    SELECT o.*, u.username, u.email,
           GROUP_CONCAT(b.title SEPARATOR ', ') as build_titles
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    LEFT JOIN builds b ON oi.build_id = b.id 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
");
$stmt->execute();
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerir Encomendas - <?php echo SITE_NAME; ?></title>
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
            <h1 style="margin-bottom: 2rem; color: #333;">Gerir Encomendas</h1>
            
            <!-- Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <!-- Orders Table -->
            <div style="background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); overflow: hidden;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Utilizador</th>
                            <th>Email</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Builds</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['username']); ?></td>
                                <td><?php echo htmlspecialchars($order['email']); ?></td>
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
                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="status" onchange="this.form.submit()" style="padding: 0.3rem; border: 1px solid #ddd; border-radius: 3px;">
                                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pendente</option>
                                            <option value="paid" <?php echo $order['status'] == 'paid' ? 'selected' : ''; ?>>Pago</option>
                                            <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Enviado</option>
                                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
