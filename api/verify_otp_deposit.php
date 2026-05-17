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

if (empty($_SESSION['pending_deposit'])) {
    setFlash('error', 'Phiên nạp tiền đã hết hạn. Vui lòng thực hiện lại.');
    header('Location: /BTL-MNM/pages/deposit.php');
    exit();
}

$pending   = $_SESSION['pending_deposit'];
$amount    = $pending['amount'];
$walletId  = $pending['wallet_id'];
$otpId     = $pending['otp_id'];
$orderInfo = $pending['order_info'];

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
    header('Location: /BTL-MNM/pages/verify_otp.php');
    exit();
}

try {
    $db->beginTransaction();
    $txnRef   = generateTxnRef();
    $cashback = 0;

    // Đánh dấu OTP đã dùng
    $db->prepare("UPDATE otps SET is_used = TRUE WHERE otp_id = ?")
       ->execute([$otpId]);

    // Ghi giao dịch nạp tiền
    $db->prepare("
        INSERT INTO transactions (transaction_id, receiver_wallet_id, amount, type, status, message, reference_code)
        VALUES (?, ?, ?, 'deposit', 'success', ?, ?)
    ")->execute([$txnRef, $walletId, $amount, $orderInfo, $txnRef]);

    // Cộng tiền vào ví
    $db->prepare("UPDATE wallets SET balance = balance + ? WHERE wallet_id = ?")
       ->execute([$amount, $walletId]);

    // Thông báo nạp tiền thành công
    $db->prepare("
        INSERT INTO notifications (notification_id, user_id, title, content, type)
        VALUES (UUID(), ?, 'Nạp tiền thành công', ?, 'transaction')
    ")->execute([$_SESSION['user_id'],
        'Ví đã được cộng ' . number_format($amount, 0, ',', '.') . ' ₫. Mã GD: ' . $txnRef
    ]);

    // Hoàn tiền 5% nếu nạp >= 500.000 ₫
    if ($amount >= 500000) {
        $cashback = round($amount * 0.05);
        $cbRef    = generateTxnRef();

        // Cộng cashback vào ví
        $db->prepare("UPDATE wallets SET balance = balance + ? WHERE wallet_id = ?")
           ->execute([$cashback, $walletId]);

        // Ghi giao dịch hoàn tiền
        $db->prepare("
            INSERT INTO transactions (transaction_id, receiver_wallet_id, amount, type, status, message, reference_code)
            VALUES (?, ?, ?, 'deposit', 'success', 'Hoàn tiền 5% ưu đãi tháng này', ?)
        ")->execute([$cbRef, $walletId, $cashback, $cbRef]);

        // Thông báo hoàn tiền
        $db->prepare("
            INSERT INTO notifications (notification_id, user_id, title, content, type)
            VALUES (UUID(), ?, 'Hoàn tiền ưu đãi 5%', ?, 'promotion')
        ")->execute([$_SESSION['user_id'],
            'Bạn được hoàn ' . formatMoney($cashback) . ' (5%) vào ví vì nạp từ 500.000 ₫!'
        ]);
    }

    $db->commit();

    // Cập nhật session balance (cộng cả cashback nếu có)
    $_SESSION['balance'] = (float)$_SESSION['balance'] + $amount + $cashback;

    unset($_SESSION['pending_deposit']);

    $msg = 'Nạp ' . number_format($amount, 0, ',', '.') . ' ₫ thành công!';
    if ($cashback > 0) {
        $msg .= ' Hoàn thêm ' . formatMoney($cashback) . ' (5%) ưu đãi!';
    }

    setFlash('success', $msg);
    header('Location: /BTL-MNM/pages/dashboard.php');

} catch (Exception $e) {
    $db->rollBack();
    setFlash('error', 'Lỗi hệ thống: ' . $e->getMessage());
    header('Location: /BTL-MNM/pages/verify_otp.php');
}
exit();