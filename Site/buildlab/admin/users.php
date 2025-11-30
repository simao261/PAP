<?php
require_once '../includes/config.php';

// Verificar se é administrador
requireAdmin();

// Buscar todos os utilizadores
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerir Utilizadores - <?php echo SITE_NAME; ?></title>
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
            <h1 style="margin-bottom: 2rem; color: #333;">Gerir Utilizadores</h1>
            
            <!-- Users Table -->
            <div style="background: white; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); overflow: hidden;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome de Utilizador</th>
                            <th>Email</th>
                            <th>Função</th>
                            <th>Último Login</th>
                            <th>Data de Registo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>#<?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $user['role'] == 'admin' ? 'status-responded' : 'status-pending'; ?>">
                                        <?php echo $user['role'] == 'admin' ? 'Administrador' : 'Utilizador'; ?>
                                    </span>
                                </td>
                                <td><?php echo $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Nunca'; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
