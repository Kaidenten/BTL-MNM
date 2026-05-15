<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /BTL-MNM/pages/transfer.php');
    exit();
}

$accountNumber = trim($_POST['account_number'] ?? '');
$amount        = (float)($_POST['amount']       ?? 0);
$message       = trim($_POST['message']         ?? '');

// Validate cơ bản
if (empty($accountNumber) || $amount <= 0) {
    setFlash('error', 'Vui lòng điền đầy đủ thông tin.');
    header('Location: /BTL-MNM/pages/transfer.php');
    exit();
}

if ($amount < 10000) {
    setFlash('error', 'Số tiền chuyển tối thiểu 10.000 ₫.');
    header('Location: /BTL-MNM/pages/transfer.php');
    exit();
}

$db = getDB();

// Kiểm tra số TK ngân hàng có trong DB không + phải đã được xác thực
$stmt = $db->prepare("
    SELECT ba.*, u.full_name AS owner_name, u.user_id AS owner_id
    FROM bank_accounts ba
    JOIN users u ON ba.user_id = u.user_id
    WHERE ba.account_number = ? AND ba.is_verified = TRUE
    LIMIT 1
");
$stmt->execute([$accountNumber]);
$bankAccount = $stmt->fetch();

if (!$bankAccount) {
    setFlash('error', 'Số tài khoản ngân hàng không tồn tại hoặc chưa được xác thực trong hệ thống.');
    header('Location: /BTL-MNM/pages/transfer.php');
    exit();
}

// Không tự chuyển cho chính mình
if ($bankAccount['owner_id'] === $_SESSION['user_id']) {
    setFlash('error', 'Bạn không thể chuyển tiền cho chính mình.');
    header('Location: /BTL-MNM/pages/transfer.php');
    exit();
}

// Lấy ví người gửi
$stmt = $db->prepare("SELECT * FROM wallets WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$wallet = $stmt->fetch();

if (!$wallet || $wallet['status'] === 'locked') {
    setFlash('error', 'Ví của bạn đang bị khóa.');
    header('Location: /BTL-MNM/pages/transfer.php');
    exit();
}

// Kiểm tra số dư
if ($wallet['balance'] < $amount) {
    setFlash('error', 'Số dư không đủ. Hiện tại: ' . formatMoney($wallet['balance']));
    header('Location: /BTL-MNM/pages/transfer.php');
    exit();
}

// Kiểm tra hạn mức ngày
$today = date('Y-m-d');
$stmt  = $db->prepare("SELECT total_spent FROM daily_limits_log WHERE wallet_id = ? AND log_date = ?");
$stmt->execute([$wallet['wallet_id'], $today]);
$dailyLog        = $stmt->fetch();
$totalSpentToday = ($dailyLog['total_spent'] ?? 0) + $amount;

if ($totalSpentToday > $wallet['daily_limit']) {
    setFlash('error', 'Vượt quá hạn mức giao dịch trong ngày (' . formatMoney($wallet['daily_limit']) . ').');
    header('Location: /BTL-MNM/pages/transfer.php');
    exit();
}

// Lấy ví người nhận qua bank_account
$stmtRecv = $db->prepare("SELECT * FROM wallets WHERE user_id = ?");
$stmtRecv->execute([$bankAccount['owner_id']]);
$receiverWallet = $stmtRecv->fetch();

if (!$receiverWallet || $receiverWallet['status'] === 'locked') {
    setFlash('error', 'Ví người nhận không khả dụng.');
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

// Lưu vào session
$_SESSION['pending_transfer_bank'] = [
    'bank_name'          => $bankAccount['bank_name'],
    'account_number'     => $bankAccount['account_number'],
    'account_holder'     => $bankAccount['account_holder'],
    'owner_name'         => $bankAccount['owner_name'],
    'sender_wallet_id'   => $wallet['wallet_id'],
    'receiver_wallet_id' => $receiverWallet['wallet_id'],
    'receiver_user_id'   => $bankAccount['owner_id'],
    'amount'             => $amount,
    'message'            => $message,
    'otp_id'             => $otpId,
];

setFlash('otp_code', $otpCode);
header('Location: /BTL-MNM/pages/verify_otp_transfer.php');
exit();