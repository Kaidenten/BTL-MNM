<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /BTL-MNM/pages/verify_otp_wallet_transfer.php');
    exit();
}

$otpInput = preg_replace('/\D/', '', trim($_POST['otp_code'] ?? ''));

if (empty($_SESSION['pending_transfer'])) {
    setFlash('error', 'Phiên giao dịch đã hết hạn.');
    header('Location: /BTL-MNM/pages/transfer.php');
    exit();
}

$pending          = $_SESSION['pending_transfer'];
$amount           = $pending['amount'];
$senderWalletId   = $pending['sender_wallet_id'];
$receiverWalletId = $pending['receiver_wallet_id'];
$receiverName     = $pending['receiver_name'];
$message          = $pending['message'];
$otpId            = $pending['otp_id'];

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
    header('Location: /BTL-MNM/pages/verify_otp_wallet_transfer.php');
    exit();
}

try {
    $db->beginTransaction();
    $txnRef = generateTxnRef();

    // Đánh dấu OTP đã dùng
    $db->prepare("UPDATE otps SET is_used = TRUE WHERE otp_id = ?")
       ->execute([$otpId]);

    // Trừ ví người gửi
    $db->prepare("UPDATE wallets SET balance = balance - ? WHERE wallet_id = ?")
       ->execute([$amount, $senderWalletId]);

    // Cộng ví người nhận
    $db->prepare("UPDATE wallets SET balance = balance + ? WHERE wallet_id = ?")
       ->execute([$amount, $receiverWalletId]);

    // Ghi giao dịch
    $db->prepare("
        INSERT INTO transactions
            (transaction_id, sender_wallet_id, receiver_wallet_id, amount, type, status, message, reference_code)
        VALUES (?, ?, ?, ?, 'transfer', 'success', ?, ?)
    ")->execute([$txnRef, $senderWalletId, $receiverWalletId, $amount, $message, $txnRef]);

    // Cập nhật hạn mức ngày
    $today = date('Y-m-d');
    $db->prepare("
        INSERT INTO daily_limits_log (log_id, wallet_id, log_date, total_spent)
        VALUES (UUID(), ?, ?, ?)
        ON DUPLICATE KEY UPDATE total_spent = total_spent + ?
    ")->execute([$senderWalletId, $today, $amount, $amount]);

    // Cập nhật session balance
    $_SESSION['balance'] = (float)$_SESSION['balance'] - $amount;

    // Thông báo người gửi
    $db->prepare("
        INSERT INTO notifications (notification_id, user_id, title, content, type)
        VALUES (UUID(), ?, 'Chuyển tiền thành công', ?, 'transaction')
    ")->execute([$_SESSION['user_id'],
        'Bạn đã chuyển ' . formatMoney($amount) . ' đến ' . $receiverName . '. Mã GD: ' . $txnRef
    ]);

    // Thông báo người nhận
    $stmtRecv = $db->prepare("SELECT user_id FROM wallets WHERE wallet_id = ?");
    $stmtRecv->execute([$receiverWalletId]);
    $recvUser = $stmtRecv->fetch();

    if ($recvUser) {
        $db->prepare("
            INSERT INTO notifications (notification_id, user_id, title, content, type)
            VALUES (UUID(), ?, 'Nhận tiền thành công', ?, 'transaction')
        ")->execute([$recvUser['user_id'],
            'Bạn nhận được ' . formatMoney($amount) . ' từ ' . $_SESSION['full_name'] .
            ($message ? '. Lời nhắn: ' . $message : '') . '. Mã GD: ' . $txnRef
        ]);
    }

    $db->commit();
    unset($_SESSION['pending_transfer']);

    setFlash('success', 'Chuyển ' . formatMoney($amount) . ' đến ' . $receiverName . ' thành công!');
    header('Location: /BTL-MNM/pages/dashboard.php');

} catch (Exception $e) {
    $db->rollBack();
    setFlash('error', 'Lỗi hệ thống: ' . $e->getMessage());
    header('Location: /BTL-MNM/pages/verify_otp_wallet_transfer.php');
}
exit();