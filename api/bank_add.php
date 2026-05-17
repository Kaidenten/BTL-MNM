<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /BTL-MNM/pages/bank_account.php');
    exit();
}

$bankName      = trim($_POST['bank_name']      ?? '');
$accountNumber = trim($_POST['account_number'] ?? '');
$accountHolder = strtoupper(trim($_POST['account_holder'] ?? ''));

if (empty($bankName) || empty($accountNumber) || empty($accountHolder)) {
    setFlash('error', 'Vui lòng điền đầy đủ thông tin.');
    header('Location: /BTL-MNM/pages/bank_account.php');
    exit();
}

$db = getDB();

// Kiểm tra số tài khoản đã liên kết chưa
$stmt = $db->prepare("SELECT bank_account_id FROM bank_accounts WHERE user_id = ? AND account_number = ?");
$stmt->execute([$_SESSION['user_id'], $accountNumber]);
if ($stmt->fetch()) {
    setFlash('error', 'Số tài khoản này đã được liên kết.');
    header('Location: /BTL-MNM/pages/bank_account.php');
    exit();
}

// Kiểm tra đây có phải TK đầu tiên không (đặt mặc định)
$stmt = $db->prepare("SELECT COUNT(*) FROM bank_accounts WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$isFirst = $stmt->fetchColumn() == 0;

// Thêm TK với is_verified = FALSE — chờ admin duyệt
$db->prepare("
    INSERT INTO bank_accounts (bank_account_id, user_id, bank_name, account_number, account_holder, is_verified, is_default)
    VALUES (UUID(), ?, ?, ?, ?, FALSE, ?)
")->execute([$_SESSION['user_id'], $bankName, $accountNumber, $accountHolder, $isFirst ? 1 : 0]);

// Thông báo cho user
$db->prepare("
    INSERT INTO notifications (notification_id, user_id, title, content, type)
    VALUES (UUID(), ?, 'Yêu cầu liên kết ngân hàng đang chờ duyệt', ?, 'system')
")->execute([$_SESSION['user_id'],
    'Tài khoản ' . $bankName . ' - ' . $accountNumber . ' đang chờ admin xác nhận. Bạn sẽ nhận thông báo khi được duyệt.'
]);

header('Location: /BTL-MNM/pages/bank_account.php?added=1');
exit();