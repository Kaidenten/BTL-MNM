<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
 
header('Content-Type: application/json');
 
$query = trim($_GET['q'] ?? '');
if (strlen($query) < 5) {
    echo json_encode(['success' => false, 'message' => 'Nhập ít nhất 5 ký tự.']);
    exit();
}
 
$db = getDB();
$stmt = $db->prepare("
    SELECT user_id, full_name, phone_number, email
    FROM users
    WHERE (phone_number = ? OR email = ?)
      AND user_id != ?
      AND status = 'active'
    LIMIT 1
");
$stmt->execute([$query, $query, $_SESSION['user_id']]);
$user = $stmt->fetch();
 
if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy người dùng.']);
    exit();
}
 
echo json_encode([
    'success'      => true,
    'user_id'      => $user['user_id'],
    'full_name'    => $user['full_name'],
    'phone_number' => $user['phone_number'],
    'avatar_url'   => 'https://ui-avatars.com/api/?name=' . urlencode($user['full_name']) . '&background=0d6efd&color=fff&size=44',
]);
exit();
