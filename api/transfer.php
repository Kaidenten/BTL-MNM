<?php


require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /BTL-MNM/pages/transfer.php');
    exit();
}

$receiverId = trim($_POST['receiver_id'] ?? '');
$amount     = (float)($_POST['amount']   ?? 0);
$message    = trim($_POST['message']     ?? '');

if (empty($receiverId) || $amount <= 0) {
    setFlash('error', 'Vui lòng điền đầy đủ thông tin.');
    header('Location: /BTL-MNM/pages/transfer.php');
    exit();
}

if ($amount < 1000) {
    setFlash('error', 'Số tiền chuyển tối thiểu là 1.000 ₫.');
    header('Location: /BTL-MNM/pages/transfer.php');
    exit();
}

// $db PHẢI Ở ĐÂY — trước tất cả query
$db = getDB();

// Lấy ví người gửi
$stmt = $db->prepare("SELECT * FROM wallets WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$senderWallet = $stmt->fetch();

if (!$senderWallet || $senderWallet['status'] === 'locked') {
    setFlash('error', 'Ví của bạn đang bị khóa.');
    header('Location: /BTL-MNM/pages/transfer.php');
    exit();
}

// Không tự chuyển cho mình
if ($receiverId === $_SESSION['user_id']) {
    setFlash('error', 'Bạn không thể chuyển tiền cho chính mình.');
    header('Location: /BTL-MNM/pages/transfer.php');
    exit();
}

// Kiểm tra số dư
if ($senderWallet['balance'] < $amount) {
    setFlash('error', 'Số dư không đủ. Hiện tại: ' . formatMoney($senderWallet['balance']));
    header('Location: /BTL-MNM/pages/transfer.php');
    exit();
}

// Kiểm tra hạn mức ngày
$today = date('Y-m-d');
$stmt  = $db->prepare("SELECT total_spent FROM daily_limits_log WHERE wallet_id = ? AND log_date = ?");
$stmt->execute([$senderWallet['wallet_id'], $today]);
$dailyLog        = $stmt->fetch();
$totalSpentToday = ($dailyLog['total_spent'] ?? 0) + $amount;

if ($totalSpentToday > $senderWallet['daily_limit']) {
    setFlash('error', 'Vượt quá hạn mức giao dịch trong ngày (' . formatMoney($senderWallet['daily_limit']) . ').');
    header('Location: /BTL-MNM/pages/transfer.php');
    exit();
}

// Lấy ví người nhận
$stmt = $db->prepare("
    SELECT w.*, u.full_name
    FROM wallets w
    JOIN users u ON w.user_id = u.user_id
    WHERE w.user_id = ?
");
$stmt->execute([$receiverId]);
$receiverWallet = $stmt->fetch();

if (!$receiverWallet || $receiverWallet['status'] === 'locked') {
    setFlash('error', 'Không tìm thấy ví người nhận hoặc ví đã bị khóa.');
    header('Location: /BTL-MNM/pages/transfer.php');
    exit();
}

// Tạo OTP
$otpCode  = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
$otpId    = generateTxnRef();
$expireAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

$db->prepare("
    INSERT INTO otps (otp_id, user_id, code, type, is_used, expired_at)
    VALUES (?, ?, ?, 'transaction', FALSE, ?)
")->execute([$otpId, $_SESSION['user_id'], $otpCode, $expireAt]);

$_SESSION['pending_transfer'] = [
    'sender_wallet_id'   => $senderWallet['wallet_id'],
    'receiver_wallet_id' => $receiverWallet['wallet_id'],
    'receiver_name'      => $receiverWallet['full_name'],
    'amount'             => $amount,
    'message'            => $message,
    'otp_id'             => $otpId,
];

setFlash('otp_code', $otpCode);
header('Location: /BTL-MNM/pages/verify_otp_wallet_transfer.php');
exit();