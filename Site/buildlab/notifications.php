<?php
require_once 'includes/config.php';
require_once 'includes/notifications.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$notifications = getUserNotifications($user_id, 20);
$unread_count = getUnreadNotificationCount($user_id);

// Marcar notificação como lida
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    markNotificationAsRead($_GET['mark_read'], $user_id);
    header('Location: notifications.php');
    exit();
}

// Marcar todas como lidas
if (isset($_POST['mark_all_read'])) {
    markAllNotificationsAsRead($user_id);
    header('Location: notifications.php');
    exit();
}

// Eliminar notificação
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    deleteNotification($_GET['delete'], $user_id);
    header('Location: notifications.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificações - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .notification-item {
            border-left: 4px solid #dee2e6;
            transition: all 0.3s ease;
        }
        .notification-item.unread {
            border-left-color: #007bff;
            background-color: #f8f9fa;
        }
        .notification-item:hover {
            background-color: #e9ecef;
        }
        .notification-type {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .type-info { background-color: #17a2b8; }
        .type-success { background-color: #28a745; }
        .type-warning { background-color: #ffc107; }
        .type-error { background-color: #dc3545; }
    </style>
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
            </ul>
            
            <div class="user-menu">
                <a href="cart.php" class="cart-icon">
                    🛒 <span class="cart-count"><?php echo getCartCount(); ?></span>
                </a>
                <a href="notifications.php" class="position-relative">
                    🔔 
                    <?php if ($unread_count > 0): ?>
                        <span class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill">
                            <?php echo $unread_count; ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="profile.php">Perfil</a>
                <a href="logout.php">Sair</a>
            </div>
        </nav>
    </header>

    <!-- Notificações -->
    <section class="container py-5">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Notificações</h1>
                    <?php if ($unread_count > 0): ?>
                        <form method="POST" class="d-inline">
                            <button type="submit" name="mark_all_read" class="btn btn-outline-primary">
                                Marcar todas como lidas
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
                
                <?php if (empty($notifications)): ?>
                    <div class="text-center py-5">
                        <h3 class="text-muted">Nenhuma notificação</h3>
                        <p class="text-muted">Quando tiver notificações, elas aparecerão aqui.</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($notifications as $notification): ?>
                            <div class="col-12 mb-3">
                                <div class="card notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-2">
                                                    <span class="notification-type type-<?php echo $notification['type']; ?>"></span>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                                    <?php if (!$notification['is_read']): ?>
                                                        <span class="badge bg-primary ms-2">Nova</span>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="text-muted mb-2"><?php echo htmlspecialchars($notification['message']); ?></p>
                                                <small class="text-muted">
                                                    <?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?>
                                                </small>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    ⋮
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <?php if (!$notification['is_read']): ?>
                                                        <li><a class="dropdown-item" href="?mark_read=<?php echo $notification['id']; ?>">Marcar como lida</a></li>
                                                    <?php endif; ?>
                                                    <li><a class="dropdown-item text-danger" href="?delete=<?php echo $notification['id']; ?>" onclick="return confirm('Tem certeza?')">Eliminar</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 <?php echo SITE_NAME; ?>. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

