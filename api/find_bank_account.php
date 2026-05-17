<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

// SỬA — thêm bank_name vào query
$bankName      = trim($_GET['bank_name'] ?? '');
$accountNumber = trim($_GET['account_number'] ?? '');

if (empty($accountNumber) || empty($bankName)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng chọn ngân hàng và nhập số tài khoản.']);
    exit();
}

$db = getDB();

$stmt = $db->prepare("
    SELECT ba.bank_name, ba.account_number, ba.account_holder,
           u.full_name, u.user_id
    FROM bank_accounts ba
    JOIN users u ON ba.user_id = u.user_id
    WHERE ba.account_number = ?
      AND ba.bank_name = ?
      AND ba.is_verified = TRUE
      AND ba.user_id != ?
    LIMIT 1
");
$stmt->execute([$accountNumber, $bankName, $_SESSION['user_id']]);
$bankAccount = $stmt->fetch();

if (!$bankAccount) {
    echo json_encode([
        'success' => false,
        'message' => 'Số tài khoản không tồn tại hoặc chưa được xác thực trong hệ thống.'
    ]);
    exit();
}

echo json_encode([
    'success'        => true,
    'account_number' => $bankAccount['account_number'],
    'account_holder' => $bankAccount['account_holder'],
    'bank_name'      => $bankAccount['bank_name'],
    'full_name'      => $bankAccount['full_name'],
    'user_id'        => $bankAccount['user_id'],
]);
exit();