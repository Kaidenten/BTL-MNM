<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /BTL-MNM/pages/profile.php');
    exit();
}
 
$fullName    = trim($_POST['full_name']    ?? '');
$phoneNumber = trim($_POST['phone_number'] ?? '');
 
if (empty($fullName)) {
    setFlash('error', 'Họ tên không được để trống.');
    header('Location: /BTL-MNM/pages/profile.php');
    exit();
}
 
if (!preg_match('/^[0-9]{10,11}$/', $phoneNumber)) {
    setFlash('error', 'Số điện thoại không hợp lệ.');
    header('Location: /BTL-MNM/pages/profile.php');
    exit();
}
 
$db = getDB();
 
// Kiểm tra SĐT đã dùng bởi user khác chưa
$stmt = $db->prepare("SELECT user_id FROM users WHERE phone_number = ? AND user_id != ?");
$stmt->execute([$phoneNumber, $_SESSION['user_id']]);
if ($stmt->fetch()) {
    setFlash('error', 'Số điện thoại này đã được sử dụng.');
    header('Location: /BTL-MNM/pages/profile.php');
    exit();
}
 
$db->prepare("UPDATE users SET full_name = ?, phone_number = ? WHERE user_id = ?")
   ->execute([$fullName, $phoneNumber, $_SESSION['user_id']]);
 
$_SESSION['full_name'] = $fullName;
 
setFlash('success', 'Cập nhật thông tin thành công!');
header('Location: /BTL-MNM/pages/profile.php');
exit();
