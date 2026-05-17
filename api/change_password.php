<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /BTL-MNM/pages/profile.php');
    exit();
}
 
$oldPassword     = $_POST['old_password']     ?? '';
$newPassword     = $_POST['new_password']     ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
 
if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
    setFlash('error', 'Vui lòng điền đầy đủ thông tin.');
    header('Location: /BTL-MNM/pages/profile.php');
    exit();
}
 
if (strlen($newPassword) < 8) {
    setFlash('error', 'Mật khẩu mới phải có ít nhất 8 ký tự.');
    header('Location: /BTL-MNM/pages/profile.php');
    exit();
}
 
if ($newPassword !== $confirmPassword) {
    setFlash('error', 'Mật khẩu xác nhận không khớp.');
    header('Location: /BTL-MNM/pages/profile.php');
    exit();
}
 
$db = getDB();
$stmt = $db->prepare("SELECT password_hash FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
 
if (!$user || !password_verify($oldPassword, $user['password_hash'])) {
    setFlash('error', 'Mật khẩu hiện tại không đúng.');
    header('Location: /BTL-MNM/pages/profile.php');
    exit();
}
 
$db->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?")
   ->execute([password_hash($newPassword, PASSWORD_BCRYPT), $_SESSION['user_id']]);
 
$db->prepare("
    INSERT INTO notifications (notification_id, user_id, title, content, type)
    VALUES (UUID(), ?, 'Đổi mật khẩu thành công', ?, 'security')
")->execute([$_SESSION['user_id'],
    'Mật khẩu tài khoản của bạn vừa được thay đổi lúc ' . date('H:i d/m/Y') . '. Nếu không phải bạn thực hiện, hãy liên hệ hỗ trợ ngay!'
]);

setFlash('success', 'Đổi mật khẩu thành công!');
header('Location: /BTL-MNM/pages/profile.php');
exit();
