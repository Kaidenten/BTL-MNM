<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /BTL-MNM/pages/login.php');
    exit();
}
 
$identifier = trim($_POST['identifier'] ?? '');
$password   = $_POST['password'] ?? '';
 
if (empty($identifier) || empty($password)) {
    setFlash('error', 'Vui lòng nhập đầy đủ thông tin.');
    header('Location: /BTL-MNM/pages/login.php');
    exit();
}
 
$db = getDB();
 
// Tìm user theo email hoặc số điện thoại
$stmt = $db->prepare("SELECT * FROM users WHERE email = ? OR phone_number = ? LIMIT 1");
$stmt->execute([$identifier, $identifier]);
$user = $stmt->fetch();
 
if (!$user || !password_verify($password, $user['password_hash'])) {
    setFlash('error', 'Email/SĐT hoặc mật khẩu không đúng.');
    header('Location: /BTL-MNM/pages/login.php');
    exit();
}
 
if ($user['status'] === 'locked') {
    setFlash('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.');
    header('Location: /BTL-MNM/pages/login.php');
    exit();
}
 
// Lấy số dư ví
$stmtW = $db->prepare("SELECT balance FROM wallets WHERE user_id = ?");
$stmtW->execute([$user['user_id']]);
$wallet = $stmtW->fetch();
 
// Ghi session
$_SESSION['user_id']   = $user['user_id'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['email']     = $user['email'];
$_SESSION['role']      = $user['role'];
$_SESSION['balance']   = $wallet['balance'] ?? 0;
 

$db->prepare("
    INSERT INTO notifications (notification_id, user_id, title, content, type)
    VALUES (UUID(), ?, 'Đăng nhập thành công', ?, 'security')
")->execute([$user['user_id'],
    'Tài khoản vừa đăng nhập lúc ' . date('H:i d/m/Y') . ' từ IP: ' . $_SERVER['REMOTE_ADDR']
]);
// Cập nhật last login (tuỳ chọn)
// $db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?")->execute([$user['user_id']]);
 
setFlash('success', 'Chào mừng trở lại, ' . $user['full_name'] . '!');
 
if ($user['role'] === 'admin') {
    header('Location: /BTL-MNM/admin/dashboard.php');
} else {
    $redirect = $_GET['redirect'] ?? '/BTL-MNM/pages/dashboard.php';
    header('Location: ' . $redirect);
}
exit();
