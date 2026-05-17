<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /BTL-MNM/pages/payment.php');
    exit();
}


$billId = trim($_POST['bill_id'] ?? '');

if (empty($billId)) {
    setFlash('error', 'Vui lòng chọn hóa đơn cần thanh toán.');
    header('Location: /BTL-MNM/pages/payment.php');
    exit();
}

// $db phải khai báo trước khi dùng
$db = getDB();

// ============================================================
// TRƯỜNG HỢP NẠP ĐIỆN THOẠI (bill_id bắt đầu bằng TOPUP_)
// ============================================================
if (str_starts_with($billId, 'TOPUP_')) {
    $amount      = (float)str_replace('TOPUP_', '', $billId);
    $phoneNumber = trim($_POST['customer_code'] ?? '');

    if (empty($phoneNumber) || !preg_match('/^[0-9]{10,11}$/', $phoneNumber)) {
        setFlash('error', 'Số điện thoại không hợp lệ.');
        header('Location: /BTL-MNM/pages/payment.php');
        exit();
    }

    if ($amount <= 0) {
        setFlash('error', 'Mệnh giá không hợp lệ.');
        header('Location: /BTL-MNM/pages/payment.php');
        exit();
    }

    // Lấy ví
    $stmt = $db->prepare("SELECT * FROM wallets WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $wallet = $stmt->fetch();

    if (!$wallet || $wallet['status'] === 'locked') {
        setFlash('error', 'Ví của bạn đang bị khóa.');
        header('Location: /BTL-MNM/pages/payment.php');
        exit();
    }

    if ($wallet['balance'] < $amount) {
        setFlash('error', 'Số dư không đủ. Hiện tại: ' . formatMoney($wallet['balance']) . ' — Cần: ' . formatMoney($amount));
        header('Location: /BTL-MNM/pages/payment.php');
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

    $_SESSION['pending_payment'] = [
        'bill_id'       => $billId,
        'customer_code' => $phoneNumber,
        'customer_name' => 'Số ĐT: ' . $phoneNumber,
        'service_type'  => 'phone_topup',
        'service_name'  => 'Nạp điện thoại',
        'provider_name' => 'Viettel / Mobi / Vina',
        'bill_period'   => date('m/Y'),
        'amount'        => $amount,
        'wallet_id'     => $wallet['wallet_id'],
        'otp_id'        => $otpId,
        'is_topup'      => true,
    ];

    setFlash('otp_code', $otpCode);
    header('Location: /BTL-MNM/pages/verify_otp_payment.php');
    exit();
}

// ============================================================
// TRƯỜNG HỢP HÓA ĐƠN THƯỜNG (điện, nước, internet)
// ============================================================

$stmt = $db->prepare("SELECT * FROM service_bills WHERE bill_id = ? AND status = 'unpaid'");
$stmt->execute([$billId]);
$bill = $stmt->fetch();

if (!$bill) {
    setFlash('error', 'Hóa đơn không tồn tại hoặc đã được thanh toán.');
    header('Location: /BTL-MNM/pages/payment.php');
    exit();
}

$stmt = $db->prepare("SELECT * FROM wallets WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$wallet = $stmt->fetch();

if (!$wallet || $wallet['status'] === 'locked') {
    setFlash('error', 'Ví của bạn đang bị khóa.');
    header('Location: /BTL-MNM/pages/payment.php');
    exit();
}

if ($wallet['balance'] < $bill['amount']) {
    setFlash('error', 'Số dư không đủ. Hiện tại: ' . formatMoney($wallet['balance']) . ' — Cần: ' . formatMoney($bill['amount']));
    header('Location: /BTL-MNM/pages/payment.php');
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

$serviceNames = [
    'electricity' => 'Tiền điện',
    'water'       => 'Tiền nước',
    'internet'    => 'Internet / TV',
    'phone_topup' => 'Nạp điện thoại',
];

$_SESSION['pending_payment'] = [
    'bill_id'       => $bill['bill_id'],
    'customer_code' => $bill['customer_code'],
    'customer_name' => $bill['customer_name'],
    'service_type'  => $bill['service_type'],
    'service_name'  => $serviceNames[$bill['service_type']] ?? $bill['service_type'],
    'provider_name' => $bill['provider_name'],
    'bill_period'   => $bill['bill_period'],
    'amount'        => $bill['amount'],
    'wallet_id'     => $wallet['wallet_id'],
    'otp_id'        => $otpId,
    'is_topup'      => false,
];

setFlash('otp_code', $otpCode);
header('Location: /BTL-MNM/pages/verify_otp_payment.php');
exit();