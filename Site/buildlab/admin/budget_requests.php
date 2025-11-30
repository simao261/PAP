<?php
require_once '../includes/config.php';

// Verificar se é administrador
requireAdmin();

// Processar atualização de estado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id']) && isset($_POST['status'])) {
    $request_id = (int)$_POST['request_id'];
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE build_requests SET status = ? WHERE id = ?");
        $stmt->execute([$status, $request_id]);
        $_SESSION['success'] = 'Estado do pedido atualizado!';
    } catch (Exception $e) {
        $_SESSION['error'] = 'Erro ao atualizar estado.';
    }
    
    header('Location: budget_requests.php');
    exit();
}

// Buscar todos os pedidos de orçamento
$stmt = $pdo->prepare("
    SELECT br.*, u.username, u.email 
    FROM build_requests br 
    JOIN users u ON br.user_id = u.id 
    ORDER BY br.created_at DESC
");
$stmt->execute();
$requests = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerir Pedidos de Orçamento - <?php echo SITE_NAME; ?></title>
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
            <h1 style="margin-bottom: 2rem; color: #333;">Gerir Pedidos de Orçamento</h1>
            
            <!-- Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <!-- Requests Table -->
            <div style="background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); overflow: hidden;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Utilizador</th>
                            <th>Email</th>
                            <th>CPU</th>
                            <th>GPU</th>
                            <th>RAM</th>
                            <th>Armazenamento</th>
                            <th>Orçamento</th>
                            <th>Estado</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td>#<?php echo $request['id']; ?></td>
                                <td><?php echo htmlspecialchars($request['username']); ?></td>
                                <td><?php echo htmlspecialchars($request['email']); ?></td>
                                <td><?php echo htmlspecialchars($request['cpu_preference'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($request['gpu_preference'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($request['ram_preference'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($request['storage_preference'] ?: '-'); ?></td>
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
                                <td><?php echo date('d/m/Y H:i', strtotime($request['created_at'])); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                        <select name="status" onchange="this.form.submit()" style="padding: 0.3rem; border: 1px solid #ddd; border-radius: 3px;">
                                            <option value="pending" <?php echo $request['status'] == 'pending' ? 'selected' : ''; ?>>Pendente</option>
                                            <option value="viewed" <?php echo $request['status'] == 'viewed' ? 'selected' : ''; ?>>Visualizado</option>
                                            <option value="responded" <?php echo $request['status'] == 'responded' ? 'selected' : ''; ?>>Respondido</option>
                                            <option value="closed" <?php echo $request['status'] == 'closed' ? 'selected' : ''; ?>>Fechado</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Request Details Modal -->
            <div id="detailsModal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
                <div style="background: white; margin: 5% auto; padding: 2rem; border-radius: 10px; width: 80%; max-width: 600px; max-height: 80vh; overflow-y: auto;">
                    <h2>Detalhes do Pedido</h2>
                    <div id="requestDetails"></div>
                    <button onclick="closeDetailsModal()" class="btn btn-secondary" style="margin-top: 1rem;">Fechar</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function showDetails(requestId) {
            // This would typically fetch details via AJAX
            // For now, we'll show a placeholder
            document.getElementById('requestDetails').innerHTML = '<p>Carregando detalhes do pedido #' + requestId + '...</p>';
            document.getElementById('detailsModal').style.display = 'block';
        }
        
        function closeDetailsModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('detailsModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
