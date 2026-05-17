<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

$customerCode = strtoupper(trim($_GET['customer_code'] ?? ''));
$serviceType  = trim($_GET['service_type'] ?? '');

if (empty($customerCode)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã khách hàng hoặc số điện thoại.']);
    exit();
}

// TỐI ƯU 1: Định dạng lại Regex chuẩn 10 số của VN (Bắt đầu bằng số 0, theo sau là 9 chữ số)
$phoneRegex = '/^0[0-9]{9}$/';

// TỐI ƯU 2: Tự động nhận diện nếu client gửi lên là SĐT thì ép loại dịch vụ thành 'phone_topup'
// Tránh lỗi client quên chọn loại dịch vụ mà nhập SĐT bị hệ thống nhảy xuống quét DB báo "Mã KH không tồn tại"
if (preg_match($phoneRegex, $customerCode)) {
    $serviceType = 'phone_topup';
}

$validTypes = ['electricity', 'water', 'internet', 'phone_topup'];
if (!empty($serviceType) && !in_array($serviceType, $validTypes)) {
    echo json_encode(['success' => false, 'message' => 'Loại dịch vụ không hợp lệ.']);
    exit();
}

// Xử lý nạp tiền điện thoại
if ($serviceType === 'phone_topup') {
    // Kiểm tra lại định dạng SĐT (Phòng trường hợp client truyền bừa mã chữ nhưng cố tình chọn service_type là phone_topup)
    if (!preg_match($phoneRegex, $customerCode)) {
        echo json_encode(['success' => false, 'message' => 'Số điện thoại không hợp lệ (Phải gồm 10 chữ số và bắt đầu bằng số 0).']);
        exit();
    }

    // Trả về danh sách mệnh giá nạp thẻ cố định
    $denominations = [10000, 20000, 50000, 100000, 200000, 500000];
    $bills = [];
    foreach ($denominations as $amt) {
        $bills[] = [
            'bill_id'       => 'TOPUP_' . $amt,  // ID giả cho mệnh giá
            'customer_code' => $customerCode,
            'customer_name' => 'Số ĐT: ' . $customerCode,
            'service_type'  => 'phone_topup',
            'service_name'  => 'Nạp điện thoại',
            'provider_name' => 'Viettel / Mobi / Vina',
            'bill_period'   => date('m/Y'),
            'amount'        => $amt,
            'amount_fmt'    => formatMoney($amt),
        ];
    }

    echo json_encode([
        'success'       => true,
        'customer_name' => 'Số ĐT: ' . $customerCode,
        'customer_code' => $customerCode,
        'bills'         => $bills,
        'total'         => count($bills),
        'is_topup'      => true,
    ]);
    exit();
}

$db = getDB();

// TỐI ƯU 3: Sử dụng hàm UPPER(customer_code) trong SQL để đồng bộ tuyệt đối hoa/thường 
// Giúp code chạy mượt mà trên cả MySQL, PostgreSQL hay SQL Server mà không lo lệch chữ hoa chữ thường
$where  = "WHERE UPPER(customer_code) = ? AND status = 'unpaid'";
$params = [$customerCode];

if (!empty($serviceType)) {
    $where   .= " AND service_type = ?";
    $params[] = $serviceType;
}

$stmt = $db->prepare("
    SELECT * FROM service_bills
    $where
    ORDER BY service_type ASC, bill_period DESC
");
$stmt->execute($params);
$bills = $stmt->fetchAll();

if (empty($bills)) {
    // Kiểm tra mã KH có tồn tại không (Cũng áp dụng UPPER để đồng bộ)
    $checkStmt = $db->prepare("SELECT customer_name FROM service_bills WHERE UPPER(customer_code) = ? LIMIT 1");
    $checkStmt->execute([$customerCode]);
    $check = $checkStmt->fetch();

    if (!$check) {
        echo json_encode(['success' => false, 'message' => 'Mã khách hàng ' . $customerCode . ' không tồn tại trong hệ thống.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không có hóa đơn chưa thanh toán cho mã KH ' . $customerCode . '.']);
    }
    exit();
}

// Map tên dịch vụ
$serviceNames = [
    'electricity' => 'Tiền điện',
    'water'       => 'Tiền nước',
    'internet'    => 'Internet / TV',
    'phone_topup' => 'Nạp điện thoại',
];

$result = [];
foreach ($bills as $bill) {
    $result[] = [
        'bill_id'       => $bill['bill_id'],
        'customer_code' => $bill['customer_code'],
        'customer_name' => $bill['customer_name'],
        'service_type'  => $bill['service_type'],
        'service_name'  => $serviceNames[$bill['service_type']] ?? $bill['service_type'],
        'provider_name' => $bill['provider_name'],
        'bill_period'   => $bill['bill_period'],
        'amount'        => $bill['amount'],
        'amount_fmt'    => formatMoney($bill['amount']),
    ];
}

echo json_encode([
    'success'       => true,
    'customer_name' => $bills[0]['customer_name'],
    'customer_code' => $customerCode,
    'bills'         => $result,
    'total'         => count($result),
]);
exit();