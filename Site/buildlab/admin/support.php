<?php
require_once '../includes/config.php';

// Verificar se é administrador
requireAdmin();

// Processar atualização de estado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message_id']) && isset($_POST['status'])) {
    $message_id = (int)$_POST['message_id'];
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE support_messages SET status = ? WHERE id = ?");
        $stmt->execute([$status, $message_id]);
        $_SESSION['success'] = 'Estado da mensagem atualizado!';
    } catch (Exception $e) {
        $_SESSION['error'] = 'Erro ao atualizar estado.';
    }
    
    header('Location: support.php');
    exit();
}

// Buscar todas as mensagens de suporte
$stmt = $pdo->prepare("
    SELECT sm.*, u.username, u.email 
    FROM support_messages sm 
    JOIN users u ON sm.user_id = u.id 
    ORDER BY sm.created_at DESC
");
$stmt->execute();
$messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerir Suporte - <?php echo SITE_NAME; ?></title>
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
            <h1 style="margin-bottom: 2rem; color: #333;">Gerir Suporte</h1>
            
            <!-- Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <!-- Support Messages Table -->
            <div style="background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); overflow: hidden;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Utilizador</th>
                            <th>Email</th>
                            <th>Assunto</th>
                            <th>Mensagem</th>
                            <th>Estado</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $message): ?>
                            <tr>
                                <td>#<?php echo $message['id']; ?></td>
                                <td><?php echo htmlspecialchars($message['username']); ?></td>
                                <td><?php echo htmlspecialchars($message['email']); ?></td>
                                <td><?php echo htmlspecialchars($message['subject'] ?: 'Sem assunto'); ?></td>
                                <td><?php echo htmlspecialchars(substr($message['message'], 0, 50)) . (strlen($message['message']) > 50 ? '...' : ''); ?></td>
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
                                <td><?php echo date('d/m/Y H:i', strtotime($message['created_at'])); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                        <select name="status" onchange="this.form.submit()" style="padding: 0.3rem; border: 1px solid #ddd; border-radius: 3px;">
                                            <option value="open" <?php echo $message['status'] == 'open' ? 'selected' : ''; ?>>Aberto</option>
                                            <option value="answered" <?php echo $message['status'] == 'answered' ? 'selected' : ''; ?>>Respondido</option>
                                            <option value="closed" <?php echo $message['status'] == 'closed' ? 'selected' : ''; ?>>Fechado</option>
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
