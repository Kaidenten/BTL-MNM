<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /BTL-MNM/pages/notification.php'); exit();
}
 
$db = getDB();
$db->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?")
   ->execute([$_SESSION['user_id']]);
 
setFlash('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
header('Location: /BTL-MNM/pages/notification.php');
exit();
