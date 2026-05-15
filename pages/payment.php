
<?php
$pageTitle = 'Thanh toán dịch vụ';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';
$balance = $_SESSION['balance'] ?? 0;
$services = [
  ['key'=>'electricity','icon'=>'bi-lightning-charge-fill','label'=>'Hóa đơn điện','color'=>'warning'],
  ['key'=>'water',      'icon'=>'bi-droplet-fill',        'label'=>'Hóa đơn nước', 'color'=>'info'],
  ['key'=>'internet',   'icon'=>'bi-wifi',                'label'=>'Internet/TV',   'color'=>'primary'],
  ['key'=>'phone_topup','icon'=>'bi-phone-fill',          'label'=>'Nạp điện thoại','color'=>'success'],
];
$selected = $_GET['service'] ?? '';
?>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
 
<main class="py-4">
<div class="container">
  <div class="row justify-content-center">
    <div class="col-lg-7">
 
      <div class="d-flex align-items-center gap-3 mb-4">
        <a href="/BTL-MNM/pages/dashboard.php" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
        <div>
          <h5 class="mb-0 fw-bold">Thanh toán dịch vụ</h5>
          <small class="text-muted">Điện, nước, internet, nạp thẻ điện thoại</small>
        </div>
      </div>
 
      <!-- Số dư -->
      <div class="card border-0 bg-primary text-white shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center py-3">
          <div><div class="small opacity-75">Số dư khả dụng</div><div class="fw-bold fs-5"><?= formatMoney($balance) ?></div></div>
          <i class="bi bi-wallet2 fs-2 opacity-50"></i>
        </div>
      </div>
 
      <!-- Chọn dịch vụ -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
          <h6 class="fw-bold mb-3">Chọn dịch vụ</h6>
          <div class="row g-3">
            <?php foreach ($services as $svc): ?>
            <div class="col-6">
              <a href="?service=<?= $svc['key'] ?>"
                 class="btn btn-outline-<?= $svc['color'] ?> w-100 py-3 d-flex flex-column align-items-center gap-2
                        <?= $selected === $svc['key'] ? 'active' : '' ?>">
                <i class="bi <?= $svc['icon'] ?> fs-3"></i>
                <span class="small fw-semibold"><?= $svc['label'] ?></span>
              </a>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
 
      <!-- Form thanh toán (hiện khi chọn dịch vụ) -->
      <?php if ($selected): ?>
      <?php $svcLabel = array_column($services,'label','key')[$selected] ?? $selected; ?>
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold py-3">
          <i class="bi bi-receipt me-2 text-primary"></i>Thông tin thanh toán — <?= htmlspecialchars($svcLabel) ?>
        </div>
        <div class="card-body p-4">
          <form action="/BTL-MNM/api/payment_service.php" method="POST">
            <input type="hidden" name="service_type" value="<?= htmlspecialchars($selected) ?>"/>
 
            <?php if ($selected === 'phone_topup'): ?>
            <!-- Nạp điện thoại -->
            <div class="mb-3">
              <label class="form-label fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-phone"></i></span>
                <input type="tel" name="customer_code" class="form-control" placeholder="09xx xxx xxx" required/>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Mệnh giá</label>
              <div class="row g-2">
                <?php foreach ([10000,20000,50000,100000,200000,500000] as $amt): ?>
                <div class="col-4">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="amount" id="amt<?= $amt ?>" value="<?= $amt ?>" required/>
                    <label class="form-check-label border rounded p-2 d-block text-center small" for="amt<?= $amt ?>">
                      <?= formatMoney($amt) ?>
                    </label>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
            <?php else: ?>
            <!-- Điện / Nước / Internet -->
            <div class="mb-3">
              <label class="form-label fw-semibold">Nhà cung cấp <span class="text-danger">*</span></label>
              <select name="provider_name" class="form-select" required>
                <?php if ($selected === 'electricity'): ?>
                  <option value="">-- Chọn --</option>
                  <option>EVNHANOI - Điện lực Hà Nội</option>
                  <option>EVNHCMC - Điện lực TP.HCM</option>
                  <option>EVNCPC - Điện lực Miền Trung</option>
                <?php elseif ($selected === 'water'): ?>
                  <option value="">-- Chọn --</option>
                  <option>Công ty CP Cấp nước Hà Nội</option>
                  <option>Sawaco - Cấp nước TP.HCM</option>
                <?php elseif ($selected === 'internet'): ?>
                  <option value="">-- Chọn --</option>
                  <option>Viettel</option><option>VNPT</option>
                  <option>FPT</option><option>CMC</option>
                <?php endif; ?>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Mã khách hàng <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                <input type="text" name="customer_code" class="form-control" placeholder="Nhập mã KH trên hóa đơn" required/>
                <button type="button" class="btn btn-outline-secondary">Kiểm tra</button>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Kỳ thanh toán</label>
              <input type="text" name="bill_period" class="form-control" placeholder="VD: 04/2025"/>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Số tiền <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-cash"></i></span>
                <input type="text" id="amount_display" class="form-control money-input" placeholder="0" required/>
                <span class="input-group-text">₫</span>
              </div>
              <input type="hidden" name="amount" id="amount_raw"/>
            </div>
            <?php endif; ?>
 
           
 
            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
              <i class="bi bi-check-circle me-2"></i>Thanh toán ngay
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
const form = document.querySelector('form');
if (form) form.addEventListener('submit', function () {
  const el = document.getElementById('amount_raw');
  const di = document.getElementById('amount_display');
  if (el && di) el.value = di.value.replace(/\D/g,'');
});
</script>
 
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
