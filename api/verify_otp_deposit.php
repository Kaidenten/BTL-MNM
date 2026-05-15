<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /BTL-MNM/pages/verify_otp.php');
    exit();
}

$otpInput = preg_replace('/\D/', '', trim($_POST['otp_code'] ?? ''));

// Kiểm tra session pending
if (empty($_SESSION['pending_deposit'])) {
    setFlash('error', 'Phiên nạp tiền đã hết hạn. Vui lòng thực hiện lại.');
    header('Location: /BTL-MNM/pages/deposit.php');
    exit();
}

$pending  = $_SESSION['pending_deposit'];
$amount   = $pending['amount'];
$walletId = $pending['wallet_id'];
$otpId    = $pending['otp_id'];
$orderInfo= $pending['order_info'];




$db = getDB();
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
    header('Location: /BTL-MNM/pages/verify_otp.php');
    exit();
}




// OTP đúng → thực hiện nạp tiền
try {
    $db->beginTransaction();
    $txnRef = generateTxnRef();

    // Đánh dấu OTP đã dùng
    $db->prepare("UPDATE otps SET is_used = TRUE WHERE otp_id = ?")
       ->execute([$otpId]);

    // Ghi giao dịch
    $db->prepare("
        INSERT INTO transactions (transaction_id, receiver_wallet_id, amount, type, status, message, reference_code)
        VALUES (?, ?, ?, 'deposit', 'success', ?, ?)
    ")->execute([$txnRef, $walletId, $amount, $orderInfo, $txnRef]);

    // Cộng tiền vào ví
    $db->prepare("UPDATE wallets SET balance = balance + ? WHERE wallet_id = ?")
       ->execute([$amount, $walletId]);

    // Cập nhật session balance
    $_SESSION['balance'] = (float)$_SESSION['balance'] + $amount;

    // Gửi thông báo
    $db->prepare("
        INSERT INTO notifications (notification_id, user_id, title, content, type)
        VALUES (UUID(), ?, 'Nạp tiền thành công', ?, 'transaction')
    ")->execute([$_SESSION['user_id'],
        'Ví đã được cộng ' . number_format($amount, 0, ',', '.') . ' ₫. Mã GD: ' . $txnRef
    ]);

    $db->commit();

    // Xóa session pending
    unset($_SESSION['pending_deposit']);

    setFlash('success', 'Nạp ' . number_format($amount, 0, ',', '.') . ' ₫ thành công!');
    header('Location: /BTL-MNM/pages/dashboard.php');

} catch (Exception $e) {
    $db->rollBack();
    setFlash('error', 'Lỗi hệ thống: ' . $e->getMessage());
    header('Location: /BTL-MNM/pages/verify_otp.php');
}
exit();