<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /BTL-MNM/pages/verify_otp_withdraw.php');
    exit();
}

$otpInput = preg_replace('/\D/', '', trim($_POST['otp_code'] ?? ''));

if (empty($_SESSION['pending_withdraw'])) {
    setFlash('error', 'Phiên giao dịch đã hết hạn.');
    header('Location: /BTL-MNM/pages/withdraw.php');
    exit();
}

$pending  = $_SESSION['pending_withdraw'];
$amount   = $pending['amount'];
$walletId = $pending['wallet_id'];
$otpId    = $pending['otp_id'];

$db  = getDB();
$now = date('Y-m-d H:i:s');

// Kiểm tra OTP
$stmt = $db->prepare("
    SELECT * FROM otps
    WHERE otp_id = ? AND user_id = ? AND type = 'transaction'
    AND is_used = FALSE AND expired_at > ?
");
$stmt->execute([$otpId, $_SESSION['user_id'], $now]);
$otp = $stmt->fetch();

if (!$otp || trim($otp['code']) !== trim($otpInput)) {
    setFlash('error', 'Mã OTP không đúng hoặc đã hết hạn.');
    header('Location: /BTL-MNM/pages/verify_otp_withdraw.php');
    exit();
}

try {
    $db->beginTransaction();
    $txnRef = generateTxnRef();

    // Đánh dấu OTP đã dùng
    $db->prepare("UPDATE otps SET is_used = TRUE WHERE otp_id = ?")
       ->execute([$otpId]);

    // Trừ tiền ví
    $db->prepare("UPDATE wallets SET balance = balance - ? WHERE wallet_id = ?")
       ->execute([$amount, $walletId]);

    // Ghi giao dịch
    $msg = 'Rút về ' . $pending['bank_name'] . ' - ' . $pending['account_number'];
    $db->prepare("
        INSERT INTO transactions (transaction_id, sender_wallet_id, amount, type, status, message, reference_code)
        VALUES (?, ?, ?, 'withdraw', 'success', ?, ?)
    ")->execute([$txnRef, $walletId, $amount, $msg, $txnRef]);

    // Cập nhật session
    $_SESSION['balance'] = (float)$_SESSION['balance'] - $amount;

    // Thông báo
    $db->prepare("
        INSERT INTO notifications (notification_id, user_id, title, content, type)
        VALUES (UUID(), ?, 'Rút tiền thành công', ?, 'transaction')
    ")->execute([$_SESSION['user_id'],
        'Bạn đã rút ' . formatMoney($amount) . ' về ' . $pending['bank_name'] . '. Mã GD: ' . $txnRef
    ]);

    $db->commit();
    unset($_SESSION['pending_withdraw']);

    setFlash('success', 'Rút ' . formatMoney($amount) . ' thành công!');
    header('Location: /BTL-MNM/pages/dashboard.php');

} catch (Exception $e) {
    $db->rollBack();
    setFlash('error', 'Lỗi hệ thống: ' . $e->getMessage());
    header('Location: /BTL-MNM/pages/verify_otp_withdraw.php');
}
exit();