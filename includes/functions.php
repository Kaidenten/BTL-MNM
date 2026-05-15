<?php
function formatMoney(float $amount): string {
    return number_format($amount, 0, ',', '.') . ' ₫';
}
 
function generateTxnRef(): string {
    return date('YmdHis') . rand(1000, 9999);
}
 
function getClientIp(): string {
    foreach (['HTTP_X_FORWARDED_FOR','HTTP_CLIENT_IP','REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) return trim(explode(',', $_SERVER[$key])[0]);
    }
    return '127.0.0.1';
}
 
function txnTypeBadge(string $type): string {
    $map = [
        'deposit'  => ['bg-success', 'bi-arrow-down-circle', 'Nạp tiền'],
        'withdraw' => ['bg-danger',  'bi-arrow-up-circle',   'Rút tiền'],
        'transfer' => ['bg-primary', 'bi-send',              'Chuyển tiền'],
        'payment'  => ['bg-warning text-dark', 'bi-receipt', 'Thanh toán'],
    ];
    [$cls, $icon, $label] = $map[$type] ?? ['bg-secondary', 'bi-question', $type];
    return "<span class='badge $cls'><i class='bi $icon me-1'></i>$label</span>";
}
 
function statusBadge(string $status): string {
    $map = [
        'success'   => ['bg-success',  'Thành công'],
        'pending'   => ['bg-warning text-dark', 'Đang xử lý'],
        'failed'    => ['bg-danger',   'Thất bại'],
        'cancelled' => ['bg-secondary','Đã hủy'],
    ];
    [$cls, $label] = $map[$status] ?? ['bg-secondary', $status];
    return "<span class='badge $cls'>$label</span>";
}
 
function flash(string $key): ?string {
    if (!empty($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}
 
function setFlash(string $key, string $msg): void {
    $_SESSION['flash'][$key] = $msg;
}
