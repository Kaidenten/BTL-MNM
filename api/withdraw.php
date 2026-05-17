<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /BTL-MNM/pages/withdraw.php');
    exit();
}

$bankAccountId = trim($_POST['bank_account_id'] ?? '');
$amount        = (float)($_POST['amount'] ?? 0);

if (empty($bankAccountId) || $amount <= 0) {
    setFlash('error', 'Vui lòng điền đầy đủ thông tin.');
    header('Location: /BTL-MNM/pages/withdraw.php');
    exit();
}

if ($amount < 50000) {
    setFlash('error', 'Số tiền rút tối thiểu là 50.000 ₫.');
    header('Location: /BTL-MNM/pages/withdraw.php');
    exit();
}

// $db PHẢI Ở ĐÂY — trước tất cả query
$db = getDB();

// Lấy ví
$stmt = $db->prepare("SELECT * FROM wallets WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$wallet = $stmt->fetch();

if (!$wallet || $wallet['status'] === 'locked') {
    setFlash('error', 'Ví của bạn đang bị khóa.');
    header('Location: /BTL-MNM/pages/withdraw.php');
    exit();
}

if ($wallet['balance'] < $amount) {
    setFlash('error', 'Số dư không đủ. Hiện tại: ' . formatMoney($wallet['balance']));
    header('Location: /BTL-MNM/pages/withdraw.php');
    exit();
}

// Kiểm tra tài khoản ngân hàng
$stmt = $db->prepare("SELECT * FROM bank_accounts WHERE bank_account_id = ? AND user_id = ?");
$stmt->execute([$bankAccountId, $_SESSION['user_id']]);
$bankAccount = $stmt->fetch();

if (!$bankAccount) {
    setFlash('error', 'Tài khoản ngân hàng không hợp lệ.');
    header('Location: /BTL-MNM/pages/withdraw.php');
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

$_SESSION['pending_withdraw'] = [
    'amount'          => $amount,
    'wallet_id'       => $wallet['wallet_id'],
    'bank_account_id' => $bankAccountId,
    'bank_name'       => $bankAccount['bank_name'],
    'account_number'  => $bankAccount['account_number'],
    'account_holder'  => $bankAccount['account_holder'],
    'otp_id'          => $otpId,
];

setFlash('otp_code', $otpCode);
header('Location: /BTL-MNM/pages/verify_otp_withdraw.php');
exit();