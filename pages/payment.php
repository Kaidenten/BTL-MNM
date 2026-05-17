<?php
$pageTitle = 'Thanh toán dịch vụ';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

$balance = $_SESSION['balance'] ?? 0;
$error   = flash('error');
$success = flash('success');

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="py-4">
<div class="container">
  <div class="row justify-content-center">
    <div class="col-lg-7">

      <div class="d-flex align-items-center gap-3 mb-4">
        <a href="/BTL-MNM/pages/dashboard.php" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
        <div>
          <h5 class="mb-0 fw-bold">Thanh toán dịch vụ</h5>
          <small class="text-muted">Điện, nước, internet, nạp điện thoại</small>
        </div>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger alert-auto-dismiss"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success alert-auto-dismiss"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <div class="card border-0 bg-primary text-white shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center py-3">
          <div>
            <div class="small opacity-75">Số dư khả dụng</div>
            <div class="fw-bold fs-5"><?= formatMoney($balance) ?></div>
          </div>
          <i class="bi bi-wallet2 fs-2 opacity-50"></i>
        </div>
      </div>

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
          <h6 class="fw-bold mb-3">Chọn dịch vụ</h6>
          <div class="row g-3" id="service-list">
            <?php
            $services = [
              ['key'=>'electricity', 'icon'=>'bi-lightning-charge-fill', 'label'=>'Tiền điện',       'color'=>'warning'],
              ['key'=>'water',       'icon'=>'bi-droplet-fill',          'label'=>'Tiền nước',        'color'=>'info'],
              ['key'=>'internet',    'icon'=>'bi-wifi',                  'label'=>'Internet / TV',    'color'=>'primary'],
              ['key'=>'phone_topup', 'icon'=>'bi-phone-fill',            'label'=>'Nạp điện thoại',  'color'=>'success'],
            ];
            foreach ($services as $svc): ?>
            <div class="col-6">
              <button type="button"
                      class="btn btn-outline-<?= $svc['color'] ?> w-100 py-3 d-flex flex-column align-items-center gap-2 btn-service"
                      data-service="<?= $svc['key'] ?>"
                      data-label="<?= $svc['label'] ?>">
                <i class="bi <?= $svc['icon'] ?> fs-3"></i>
                <span class="small fw-semibold"><?= $svc['label'] ?></span>
              </button>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div id="lookup-section" class="d-none">
        <div class="card border-0 shadow-sm mb-4">
          <div class="card-body p-4">
            <h6 class="fw-bold mb-3">
              <i id="lookup-icon" class="bi me-2"></i>
              <span id="lookup-title">Tra cứu hóa đơn</span>
            </h6>

            <div class="input-group mb-2">
              <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
              <input type="text" id="customer-code" class="form-control form-control-lg"
                     placeholder="Nhập mã khách hàng (VD: KH001)" maxlength="20"/>
              <button type="button" class="btn btn-primary" id="btn-lookup">
                <i class="bi bi-search me-1"></i>Tra cứu
              </button>
            </div>
            <div class="form-text" id="lookup-help-text">Mã khách hàng có trên hóa đơn giấy của bạn</div>
          </div>
        </div>

        <div id="lookup-result" class="d-none">

          <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-4">
              <div class="d-flex align-items-center gap-3 mb-3">
                <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center"
                     style="width:48px;height:48px;font-size:1.3rem">
                  <i class="bi bi-person-check"></i>
                </div>
                <div>
                  <div class="fw-bold" id="kh-name">---</div>
                  <div class="text-muted small">Mã KH: <span id="kh-code">---</span></div>
                </div>
                <span class="badge bg-success ms-auto">Đã xác thực</span>
              </div>

              <h6 class="fw-bold mb-3 text-muted" style="font-size:.8rem;letter-spacing:.5px;text-transform:uppercase">
                Hóa đơn chưa thanh toán
              </h6>
              <div id="bill-list"></div>
            </div>
          </div>

        </div>

        <div id="lookup-error" class="d-none">
          <div class="alert alert-danger d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span id="lookup-error-msg"></span>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>
</main>

<script>
let selectedService = '';
let selectedLabel   = '';

// Chọn dịch vụ
document.querySelectorAll('.btn-service').forEach(btn => {
  btn.addEventListener('click', function () {
    selectedService = this.dataset.service;
    selectedLabel   = this.dataset.label;

    // Đổi style active
    document.querySelectorAll('.btn-service').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    
    document.getElementById('lookup-section').classList.remove('d-none');
    document.getElementById('lookup-title').textContent = 'Tra cứu ' + selectedLabel;

    const input = document.getElementById('customer-code');
    const helpText = document.getElementById('lookup-help-text');

    // SỬA LỖI UI & LOGIC: Phân tách luồng xử lý giá trị để không bị reset mất SĐT mặc định
    if (selectedService === 'phone_topup') {
      input.placeholder   = 'Nhập số điện thoại cần nạp';
      input.value         = '<?= $_SESSION['phone_number'] ?? '' ?>'; // Đổ SĐT mặc định từ session
      input.maxLength     = 10; // Quy hoạch chuẩn 10 số tại VN
      helpText.textContent = 'Số điện thoại của bạn hoặc số cần nạp hộ';
    } else {
      input.placeholder   = 'Nhập mã khách hàng (VD: KH001)';
      input.value         = ''; // Các dịch vụ khác thì xóa trống để user nhập mới
      input.maxLength     = 20;
      helpText.textContent = 'Mã khách hàng có trên hóa đơn giấy của bạn'; // Trả lại hướng dẫn cũ
    }
    
    // Reset trạng thái các khối kết quả cũ cũ
    document.getElementById('lookup-result').classList.add('d-none');
    document.getElementById('lookup-error').classList.add('d-none');
    input.focus();
  });
});

// Tra cứu hóa đơn
document.getElementById('btn-lookup').addEventListener('click', function () {
  const code = document.getElementById('customer-code').value.trim().toUpperCase();
  if (!code) { 
    alert(selectedService === 'phone_topup' ? 'Vui lòng nhập số điện thoại.' : 'Vui lòng nhập mã khách hàng.'); 
    return; 
  }
  if (!selectedService) { alert('Vui lòng chọn loại dịch vụ.'); return; }

  const resultBox = document.getElementById('lookup-result');
  const errorBox  = document.getElementById('lookup-error');
  resultBox.classList.add('d-none');
  errorBox.classList.add('d-none');

  fetch('/BTL-MNM/api/find_bill.php?customer_code=' + encodeURIComponent(code)
      + '&service_type=' + encodeURIComponent(selectedService))
    .then(r => r.json())
    .then(data => {
      if (!data.success) {
        document.getElementById('lookup-error-msg').textContent = data.message;
        errorBox.classList.remove('d-none');
        return;
      }

      // Hiện thông tin KH
      document.getElementById('kh-name').textContent = data.customer_name;
      document.getElementById('kh-code').textContent = data.customer_code;

      // Render danh sách hóa đơn
      const billList = document.getElementById('bill-list');
      billList.innerHTML = '';

      data.bills.forEach(bill => {
  // Tính giảm giá 10.000 ₫ cho điện/nước/internet
  const discountTypes = ['electricity', 'water', 'internet'];
  const discount      = discountTypes.includes(bill.service_type) ? 10000 : 0;
  const finalAmount   = bill.amount - discount;

  const discountHtml = discount > 0 ? `
    <div class="mt-2">
      <div class="alert alert-success py-1 px-2 mb-0 d-flex justify-content-between align-items-center">
        <div>
          <i class="bi bi-gift-fill me-1 text-success"></i>
          <span style="font-size:.75rem" class="fw-semibold">Ưu đãi giảm 10.000 ₫</span>
        </div>
        <div class="text-end">
          <span style="font-size:.75rem" class="text-muted">Thực trả: </span>
          <span class="fw-bold text-success">${finalAmount.toLocaleString('vi-VN')} ₫</span>
        </div>
      </div>
    </div>
  ` : '';

  billList.innerHTML += `
    <form action="/BTL-MNM/api/payment_service.php" method="POST" class="mb-3">
      <input type="hidden" name="bill_id" value="${bill.bill_id}"/>
      ${data.is_topup ? `<input type="hidden" name="customer_code" value="${bill.customer_code}"/>` : ''}
      <div class="border rounded p-3">
        <div class="d-flex align-items-center gap-3">
          <div class="flex-grow-1">
            <div class="fw-semibold small">
              ${data.is_topup
                ? '📱 Nạp ' + bill.amount_fmt + ' — SĐT: ' + bill.customer_code
                : bill.service_name}
            </div>
            <div class="text-muted" style="font-size:.78rem">
              ${bill.provider_name} — Kỳ: ${bill.bill_period}
            </div>
          </div>
          <div class="text-end me-2">
            ${discount > 0
              ? `<div class="text-decoration-line-through text-muted small">${bill.amount_fmt}</div>
                 <div class="fw-bold text-danger">${finalAmount.toLocaleString('vi-VN')} ₫</div>`
              : `<div class="fw-bold text-danger">${bill.amount_fmt}</div>`
            }
          </div>
          <button type="submit" class="btn btn-primary btn-sm px-3">
            <i class="bi bi-shield-lock me-1"></i>Thanh toán
          </button>
        </div>
        ${discountHtml}
      </div>
    </form>
  `;
});
      resultBox.classList.remove('d-none');
    })
    .catch(() => {
      document.getElementById('lookup-error-msg').textContent = 'Lỗi kết nối. Vui lòng thử lại.';
      errorBox.classList.remove('d-none');
    });
});

// Enter để tra cứu
document.getElementById('customer-code').addEventListener('keypress', function (e) {
  if (e.key === 'Enter') document.getElementById('btn-lookup').click();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>