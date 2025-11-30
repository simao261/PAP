<?php
require_once '../includes/config.php';
require_once '../includes/notifications.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'count':
        $count = getUnreadNotificationCount($_SESSION['user_id']);
        echo json_encode(['count' => $count]);
        break;
        
    case 'list':
        $notifications = getUserNotifications($_SESSION['user_id'], 10);
        echo json_encode($notifications);
        break;
        
    case 'mark_read':
        $notification_id = $_POST['notification_id'] ?? 0;
        if (markNotificationAsRead($notification_id, $_SESSION['user_id'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Erro ao marcar como lida']);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Ação inválida']);
}
?>

