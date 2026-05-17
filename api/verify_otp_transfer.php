<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /BTL-MNM/pages/verify_otp_transfer.php');
    exit();
}

$otpInput = preg_replace('/\D/', '', trim($_POST['otp_code'] ?? ''));

if (empty($_SESSION['pending_transfer_bank'])) {
    setFlash('error', 'Phiên giao dịch đã hết hạn.');
    header('Location: /BTL-MNM/pages/transfer.php');
    exit();
}

$pending       = $_SESSION['pending_transfer_bank'];
$amount        = $pending['amount'];
$walletId = $pending['sender_wallet_id'] ?? null;
$otpId         = $pending['otp_id'];
$bankName      = $pending['bank_name'];
$accountNumber = $pending['account_number'];
$accountHolder = $pending['account_holder'];
$message       = $pending['message'];

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
    header('Location: /BTL-MNM/pages/verify_otp_transfer.php');
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
    $bankInfo    = "{$accountHolder} - {$bankName} ({$accountNumber})";
$userMessage = $message ?: '';

$db->prepare("
    INSERT INTO transactions (transaction_id, sender_wallet_id, amount, type, status, message, bank_info, reference_code)
    VALUES (?, ?, ?, 'transfer', 'success', ?, ?, ?)
")->execute([$txnRef, $walletId, $amount, $userMessage, $bankInfo, $txnRef]);

    // Cập nhật hạn mức ngày
    $today = date('Y-m-d');
    $db->prepare("
        INSERT INTO daily_limits_log (log_id, wallet_id, log_date, total_spent)
        VALUES (UUID(), ?, ?, ?)
        ON DUPLICATE KEY UPDATE total_spent = total_spent + ?
    ")->execute([$walletId, $today, $amount, $amount]);

    // Cập nhật session
    $_SESSION['balance'] = (float)$_SESSION['balance'] - $amount;

    // Thông báo
    $db->prepare("
        INSERT INTO notifications (notification_id, user_id, title, content, type)
        VALUES (UUID(), ?, 'Chuyển tiền thành công', ?, 'transaction')
    ")->execute([$_SESSION['user_id'],
        'Bạn đã chuyển ' . formatMoney($amount) . ' đến ' . $accountHolder . ' - ' . $bankName . '. Mã GD: ' . $txnRef
    ]);

    $db->commit();
    unset($_SESSION['pending_transfer_bank']);

    setFlash('success', 'Chuyển ' . formatMoney($amount) . ' đến ' . $accountHolder . ' thành công!');
    header('Location: /BTL-MNM/pages/dashboard.php');

} catch (Exception $e) {
    $db->rollBack();
    setFlash('error', 'Lỗi hệ thống: ' . $e->getMessage());
    header('Location: /BTL-MNM/pages/verify_otp_transfer.php');
}
exit();