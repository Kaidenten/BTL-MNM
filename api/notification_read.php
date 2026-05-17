
<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
 
header('Content-Type: application/json');
 
$notificationId = trim($_POST['notification_id'] ?? '');
if (empty($notificationId)) {
    echo json_encode(['success' => false]); exit();
}
 
$db = getDB();
$db->prepare("UPDATE notifications SET is_read = TRUE WHERE notification_id = ? AND user_id = ?")
   ->execute([$notificationId, $_SESSION['user_id']]);
 
echo json_encode(['success' => true]);
exit();
