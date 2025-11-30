<?php
// Sistema de Notificações

function createNotification($user_id, $title, $message, $type = 'info') {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $title, $message, $type]);
        return true;
    } catch (PDOException $e) {
        error_log("Erro ao criar notificação: " . $e->getMessage());
        return false;
    }
}

function getUserNotifications($user_id, $limit = 10) {
    global $pdo;
    
    // Validar o limite para evitar SQL injection
    $limit = (int)$limit;
    if ($limit <= 0) $limit = 10;
    if ($limit > 100) $limit = 100; // Máximo de 100 notificações
    
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT " . $limit);
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function getUnreadNotificationCount($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

function markNotificationAsRead($notification_id, $user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?");
    return $stmt->execute([$notification_id, $user_id]);
}

function markAllNotificationsAsRead($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?");
    return $stmt->execute([$user_id]);
}

function deleteNotification($notification_id, $user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    return $stmt->execute([$notification_id, $user_id]);
}

// Notificações automáticas
function notifySupportResponse($user_id, $subject) {
    $title = "Resposta ao Suporte";
    $message = "Recebeu uma resposta para o seu pedido de suporte: " . $subject;
    return createNotification($user_id, $title, $message, 'success');
}

function notifyBudgetResponse($user_id) {
    $title = "Resposta ao Orçamento";
    $message = "Recebeu uma resposta para o seu pedido de orçamento personalizado.";
    return createNotification($user_id, $title, $message, 'success');
}

function notifyOrderUpdate($user_id, $order_id, $status) {
    $status_messages = [
        'paid' => 'A sua encomenda foi paga e está a ser processada.',
        'shipped' => 'A sua encomenda foi enviada!',
        'cancelled' => 'A sua encomenda foi cancelada.'
    ];
    
    $title = "Atualização da Encomenda #" . $order_id;
    $message = $status_messages[$status] ?? "O estado da sua encomenda foi atualizado.";
    $type = $status === 'cancelled' ? 'warning' : 'success';
    
    return createNotification($user_id, $title, $message, $type);
}

function notifyNewBuild($user_id, $build_title) {
    $title = "Nova Build Disponível";
    $message = "Uma nova build foi adicionada à loja: " . $build_title;
    return createNotification($user_id, $title, $message, 'info');
}
?>
