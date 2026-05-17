<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /BTL-MNM/pages/verify_otp_payment.php');
    exit();
}

$otpInput = preg_replace('/\D/', '', trim($_POST['otp_code'] ?? ''));

if (empty($_SESSION['pending_payment'])) {
    setFlash('error', 'Phiên thanh toán đã hết hạn. Vui lòng thực hiện lại.');
    header('Location: /BTL-MNM/pages/payment.php');
    exit();
}

$pending  = $_SESSION['pending_payment'];
$billId   = $pending['bill_id'];
$amount   = $pending['amount'];
$walletId = $pending['wallet_id'];
$otpId    = $pending['otp_id'];
$isTopup  = $pending['is_topup'] ?? false; // Lấy cờ nhận diện nạp điện thoại

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
    header('Location: /BTL-MNM/pages/verify_otp_payment.php');
    exit();
}

// BƯỚC CHẶN LỖI: CHỈ KIỂM TRA DATABASE NẾU LÀ HÓA ĐƠN THẬT
if (!$isTopup) {
    $stmt = $db->prepare("SELECT * FROM service_bills WHERE bill_id = ? AND status = 'unpaid'");
    $stmt->execute([$billId]);
    $bill = $stmt->fetch();

    if (!$bill) {
        setFlash('error', 'Hóa đơn này đã được thanh toán hoặc không tồn tại.');
        header('Location: /BTL-MNM/pages/payment.php');
        exit();
    }
}

// ... (Các phần trên giữ nguyên)

try {
    $db->beginTransaction();
    $txnRef = generateTxnRef();

    // 1. Đánh dấu OTP đã dùng
    $db->prepare("UPDATE otps SET is_used = TRUE WHERE otp_id = ?")
       ->execute([$otpId]);

    // 2. CHỐT CHẶN BẢO MẬT: Khóa ví và kiểm tra số dư thực tế lần cuối trước khi trừ
    $stmtWallet = $db->prepare("SELECT balance FROM wallets WHERE wallet_id = ? FOR UPDATE");
    $stmtWallet->execute([$walletId]);
    $currentWallet = $stmtWallet->fetch();

    // Nếu không thấy ví hoặc số dư thực tế trong DB đang nhỏ hơn số tiền cần thanh toán
    if (!$currentWallet || $currentWallet['balance'] < $amount) {
        throw new Exception("Số dư thực tế trong ví không đủ để thực hiện giao dịch này.");
    }

    // Trừ tiền ví
$db->prepare("UPDATE wallets SET balance = balance - ? WHERE wallet_id = ?")
   ->execute([$amount, $walletId]);



$discount = 0;

// THÊM — Giảm 10.000 ₫ nếu thanh toán hóa đơn điện/nước/internet
$discountTypes = ['electricity', 'water', 'internet'];
if (in_array($pending['service_type'], $discountTypes)) {
    $discount = 10000;
    $db->prepare("UPDATE wallets SET balance = balance + ? WHERE wallet_id = ?")
       ->execute([$discount, $walletId]);

    // Ghi giao dịch hoàn giảm giá
    $dcRef = generateTxnRef();
    $db->prepare("
        INSERT INTO transactions (transaction_id, receiver_wallet_id, amount, type, status, message, reference_code)
        VALUES (?, ?, ?, 'deposit', 'success', 'Giảm 10.000 ₫ ưu đãi thanh toán hóa đơn', ?)
    ")->execute([$dcRef, $walletId, $discount, $dcRef]);

    // Thông báo
    $db->prepare("
        INSERT INTO notifications (notification_id, user_id, title, content, type)
        VALUES (UUID(), ?, 'Ưu đãi giảm 10.000 ₫', ?, 'promotion')
    ")->execute([$_SESSION['user_id'],
        'Bạn được giảm 10.000 ₫ khi thanh toán hóa đơn ' . $pending['service_name'] . '!'
    ]);

}

    // 4. Ghi giao dịch 
    if ($isTopup) {
        $msg = 'Nạp tiền điện thoại cho số ' . $pending['customer_code'];
    } else {
        $msg = 'Thanh toán ' . $pending['service_name'] . ' - ' . $pending['provider_name'] . ' kỳ ' . $pending['bill_period'];
    }

    $db->prepare("
        INSERT INTO transactions (transaction_id, sender_wallet_id, amount, type, status, message, reference_code)
        VALUES (?, ?, ?, 'payment', 'success', ?, ?)
    ")->execute([$txnRef, $walletId, $amount, $msg, $txnRef]);

    // 5. Cập nhật trạng thái hóa đơn (CHỈ CHẠY VỚI HÓA ĐƠN THẬT)
    if (!$isTopup) {
        $db->prepare("
            UPDATE service_bills
            SET status = 'paid', paid_at = NOW(), transaction_id = ?
            WHERE bill_id = ?
        ")->execute([$txnRef, $billId]);
    }

    // 6. CẬP NHẬT LẠI SESSION TỪ SỐ LIỆU DATABASE CHUẨN (Khắc phục triệt để lỗi hiện -10.000đ)
    $_SESSION['balance'] = (float)$currentWallet['balance'] - $amount;

    // 7. Gửi thông báo
    if ($isTopup) {
        $notiContent = 'Bạn đã nạp thành công ' . formatMoney($amount) . ' cho số điện thoại ' . $pending['customer_code'] . '. Mã GD: ' . $txnRef;
    } else {
        $notiContent = 'Bạn đã thanh toán ' . $pending['service_name'] . ' (' . $pending['provider_name'] . ') kỳ ' . $pending['bill_period'] . ' số tiền ' . formatMoney($amount) . '. Mã GD: ' . $txnRef;
    }

    $db->prepare("
        INSERT INTO notifications (notification_id, user_id, title, content, type)
        VALUES (UUID(), ?, 'Thanh toán thành công', ?, 'transaction')
    ")->execute([$_SESSION['user_id'], $notiContent]);

    $db->commit();
    unset($_SESSION['pending_payment']); 

    setFlash('success', 'Giao dịch ' . formatMoney($amount) . ' thành công! Mã GD: ' . $txnRef);
    header('Location: /BTL-MNM/pages/dashboard.php');

} catch (Exception $e) {
    $db->rollBack();
    setFlash('error', 'Giao dịch thất bại: ' . $e->getMessage());
    header('Location: /BTL-MNM/pages/verify_otp_payment.php');
}
exit();