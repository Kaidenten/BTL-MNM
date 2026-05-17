<?php
$pageTitle = 'Nạp tiền';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

$db = getDB();
$stmt = $db->prepare("SELECT * FROM bank_accounts WHERE user_id = ? AND is_verified = TRUE LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$hasBankAccount = $stmt->fetch();
$balance = $_SESSION['balance'] ?? 0;

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="py-4">
<div class="container">
  <div class="row justify-content-center">
    <div class="col-lg-6">

      <!-- Header -->
      <div class="d-flex align-items-center gap-3 mb-4">
        <a href="/BTL-MNM/pages/dashboard.php" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
        <div>
          <h5 class="mb-0 fw-bold">Nạp tiền vào ví</h5>
          <small class="text-muted">Xác nhận qua mã OTP</small>
        </div>
      </div>

      <!-- Số dư hiện tại -->
      <div class="card border-0 bg-primary text-white shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center py-3">
          <div>
            <div class="small opacity-75">Số dư hiện tại</div>
            <div class="fw-bold fs-5"><?= formatMoney($balance) ?></div>
          </div>
          <i class="bi bi-wallet2 fs-2 opacity-50"></i>
        </div>
      </div>

      <?php if (!$hasBankAccount): ?>
      <!-- Chưa liên kết ngân hàng -->
      <div class="alert alert-warning d-flex align-items-center gap-3">
        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
        <div>
          <div class="fw-bold">Chưa liên kết ngân hàng</div>
          <div class="small">Bạn cần liên kết tài khoản ngân hàng trước khi nạp tiền.</div>
        </div>
        <a href="/BTL-MNM/pages/bank_account.php" class="btn btn-warning btn-sm ms-auto">
          Liên kết ngay
        </a>
      </div>

      <?php else: ?>
      <!-- Form nạp tiền -->
      <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
          <form action="/BTL-MNM/vnpay/create_payment.php" method="POST">

            <!-- Chọn số tiền nhanh -->
            <div class="mb-4">
              <label class="form-label fw-semibold">Chọn mệnh giá nhanh</label>
              <div class="row g-2" id="quick-amounts">
                <?php foreach ([50000,100000,200000,500000,1000000,2000000] as $amt): ?>
                <div class="col-4">
                  <button type="button" class="btn btn-outline-primary w-100 btn-quick-amount"
                          data-amount="<?= $amt ?>">
                    <?= formatMoney($amt) ?>
                  </button>
                </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Nhập thủ công -->
            <div class="mb-3">
              <label class="form-label fw-semibold">Hoặc nhập số tiền <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-cash-coin"></i></span>
                <input type="text" name="amount_display" id="amount_display"
                       class="form-control money-input form-control-lg"
                       placeholder="Tối thiểu 10.000 ₫" required/>
                <span class="input-group-text">₫</span>
              </div>
              <input type="hidden" name="amount" id="amount_raw"/>
              <div class="form-text">Tối thiểu 10.000 ₫ — Tối đa 50.000.000 ₫/lần</div>
            </div>
              
            <!-- Thêm sau input số tiền -->
<div id="promo-info" class="d-none mt-2">
  <div class="alert alert-success py-2 mb-0 d-flex justify-content-between align-items-center">
    <div>
      <i class="bi bi-gift-fill me-2 text-success"></i>
      <span class="small fw-semibold">Ưu đãi hoàn tiền 5%</span>
      <span class="badge bg-success ms-1" id="promo-cashback"></span>
    </div>
    <div class="text-end">
      <div class="small text-muted">Thực nhận vào ví</div>
      <div class="fw-bold text-success" id="promo-total"></div>
    </div>
  </div>
</div>
            <!-- Nội dung -->
            <div class="mb-4">
              <label class="form-label fw-semibold">Nội dung giao dịch</label>
              <input type="text" name="order_info" class="form-control"
                     value="Nap tien vi dien tu" maxlength="255"/>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold fs-5">
              <i class="bi bi-shield-lock me-2"></i>Tiếp tục xác nhận OTP
            </button>

          </form>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>
</main>

<script>
// SỬA lại đoạn JS btn-quick-amount
document.querySelectorAll('.btn-quick-amount').forEach(btn => {
  btn.addEventListener('click', function () {
    const raw = this.dataset.amount;
    const amount = Number(raw);

    // Tính cashback nếu >= 500.000
    const cashback = amount >= 500000 ? Math.round(amount * 0.05) : 0;
    const total    = amount + cashback;

    document.getElementById('amount_raw').value     = raw;
    document.getElementById('amount_display').value = amount.toLocaleString('vi-VN');

    // Hiện thông tin ưu đãi
    const promoBox = document.getElementById('promo-info');
    if (cashback > 0) {
      document.getElementById('promo-cashback').textContent  = '+' + cashback.toLocaleString('vi-VN') + ' ₫';
      document.getElementById('promo-total').textContent     = total.toLocaleString('vi-VN') + ' ₫';
      promoBox.classList.remove('d-none');
    } else {
      promoBox.classList.add('d-none');
    }

    document.querySelectorAll('.btn-quick-amount').forEach(b => {
      b.classList.remove('active', 'btn-primary');
      b.classList.add('btn-outline-primary');
    });
    this.classList.add('active', 'btn-primary');
    this.classList.remove('btn-outline-primary');
  });
});

document.querySelector('form')?.addEventListener('submit', function () {
  const raw = document.getElementById('amount_display').value.replace(/\D/g,'');
  document.getElementById('amount_raw').value = raw;
  document.getElementById('amount_display').value = raw;
});

// Thêm vào cuối JS
document.getElementById('amount_display').addEventListener('input', function () {
  const raw      = this.value.replace(/\D/g, '');
  const amount   = Number(raw);
  const cashback = amount >= 500000 ? Math.round(amount * 0.05) : 0;
  const total    = amount + cashback;
  const promoBox = document.getElementById('promo-info');

  if (cashback > 0) {
    document.getElementById('promo-cashback').textContent = '+' + cashback.toLocaleString('vi-VN') + ' ₫';
    document.getElementById('promo-total').textContent    = total.toLocaleString('vi-VN') + ' ₫';
    promoBox.classList.remove('d-none');
  } else {
    promoBox.classList.add('d-none');
  }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>