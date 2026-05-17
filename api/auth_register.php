<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /BTL-MNM/pages/register.php');
    exit();
}
 
$fullName       = trim($_POST['full_name']        ?? '');
$email          = trim($_POST['email']            ?? '');
$phoneNumber    = trim($_POST['phone_number']     ?? '');
$password       = $_POST['password']              ?? '';
$confirmPassword= $_POST['confirm_password']      ?? '';
 



// Validate
if (empty($fullName) || empty($email) || empty($phoneNumber) || empty($password)) {
    setFlash('error', 'Vui lòng điền đầy đủ thông tin.');
    header('Location: /BTL-MNM/pages/register.php');
    exit();
}
 
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('error', 'Email không hợp lệ.');
    header('Location: /BTL-MNM/pages/register.php');
    exit();
}
 

 
if (strlen($password) < 8) {
    setFlash('error', 'Mật khẩu phải có ít nhất 8 ký tự.');
    header('Location: /BTL-MNM/pages/register.php');
    exit();
}
 
if ($password !== $confirmPassword) {
    setFlash('error', 'Mật khẩu xác nhận không khớp.');
    header('Location: /BTL-MNM/pages/register.php');
    exit();
}


 
$db = getDB();
 
// Kiểm tra email / SĐT đã tồn tại
$stmt = $db->prepare("SELECT user_id FROM users WHERE email = ? OR phone_number = ?");
$stmt->execute([$email, $phoneNumber]);
if ($stmt->fetch()) {
    setFlash('error', 'Email hoặc số điện thoại đã được đăng ký.');
    header('Location: /BTL-MNM/pages/register.php');
    exit();
}
 
// Tạo user + ví trong 1 transaction DB
try {
    $db->beginTransaction();
 
    $userId   = generateUUID();
    $walletId = generateUUID();
 
    // Insert user
    $stmt = $db->prepare("
        INSERT INTO users (user_id, full_name, email, phone_number, password_hash, status, role)
        VALUES (?, ?, ?, ?, ?, 'active', 'user')
    ");
    $stmt->execute([$userId, $fullName, $email, $phoneNumber, password_hash($password, PASSWORD_BCRYPT)]);
 
    // Tạo ví cho user (số dư ban đầu = 0)
$stmt = $db->prepare("
    INSERT INTO wallets (wallet_id, user_id, balance, status)
    VALUES (?, ?, 0.00, 'active')
");
$stmt->execute([$walletId, $userId]);
 
    // Gửi thông báo chào mừng
    $stmt = $db->prepare("
        INSERT INTO notifications (notification_id, user_id, title, content, type)
        VALUES (?, ?, 'Chào mừng đến E-Wallet!', 'Tài khoản đã được kích hoạt. Bắt đầu trải nghiệm ngay!', 'system')
    ");
    $stmt->execute([generateUUID(), $userId]);
 
    $db->commit();
 
    setFlash('success', 'Đăng ký thành công! Vui lòng đăng nhập.');
    header('Location: /BTL-MNM/pages/login.php');
 
} catch (Exception $e) {
    $db->rollBack();
    setFlash('error', 'Đã có lỗi xảy ra. Vui lòng thử lại.');
    header('Location: /BTL-MNM/pages/register.php');
}
exit();
 
function generateUUID(): string {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}
