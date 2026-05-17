<?php
$pageTitle = 'Chuyển tiền';
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
    <div class="col-lg-6">

      <div class="d-flex align-items-center gap-3 mb-4">
        <a href="/BTL-MNM/pages/dashboard.php" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
        <div>
          <h5 class="mb-0 fw-bold">Chuyển tiền</h5>
          <small class="text-muted">Chuyển trong ví hoặc qua ngân hàng</small>
        </div>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger alert-auto-dismiss"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success alert-auto-dismiss"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <!-- Số dư -->
      <div class="card border-0 bg-primary text-white shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center py-3">
          <div>
            <div class="small opacity-75">Số dư khả dụng</div>
            <div class="fw-bold fs-5"><?= formatMoney($balance) ?></div>
          </div>
          <i class="bi bi-wallet2 fs-2 opacity-50"></i>
        </div>
      </div>

      <!-- 2 Tab -->
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white p-0">
          <ul class="nav nav-tabs border-0" id="transferTab">
            <li class="nav-item flex-fill text-center">
              <a class="nav-link active py-3 fw-semibold" data-bs-toggle="tab" href="#wallet-tab">
                <i class="bi bi-wallet2 me-2"></i>Chuyển trong ví
              </a>
            </li>
            <li class="nav-item flex-fill text-center">
              <a class="nav-link py-3 fw-semibold" data-bs-toggle="tab" href="#bank-tab">
                <i class="bi bi-bank me-2"></i>Chuyển qua ngân hàng
              </a>
            </li>
          </ul>
        </div>
        <div class="card-body p-4">
          <div class="tab-content">

            <!-- TAB 1: Chuyển trong ví -->
            <div class="tab-pane fade show active" id="wallet-tab">
              <form action="/BTL-MNM/api/transfer.php" method="POST" id="wallet-form">

                <div class="mb-3">
                  <label class="form-label fw-semibold">Số điện thoại / Email người nhận <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="receiver" id="receiver" class="form-control"
                           placeholder="Nhập SĐT hoặc email" required/>
                    <button type="button" class="btn btn-outline-secondary" id="btn-find">Tìm</button>
                  </div>
                </div>

                <!-- Thông tin người nhận -->
                <div id="receiver-info" class="d-none mb-3">
                  <div class="border rounded p-3 d-flex align-items-center gap-3 bg-light">
                    <img id="recv-avatar" src="" class="rounded-circle" width="44" height="44" alt="avatar"/>
                    <div>
                      <div class="fw-semibold" id="recv-name">---</div>
                      <div class="text-muted small" id="recv-phone">---</div>
                    </div>
                    <i class="bi bi-check-circle-fill text-success ms-auto fs-5"></i>
                  </div>
                  <input type="hidden" name="receiver_id" id="receiver_id"/>
                </div>

                <div class="mb-3">
                  <label class="form-label fw-semibold">Số tiền <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-cash"></i></span>
                    <input type="text" id="wallet_amount_display" class="form-control money-input form-control-lg" placeholder="0" required/>
                    <span class="input-group-text">₫</span>
                  </div>
                  <input type="hidden" name="amount" id="wallet_amount_raw"/>
                </div>

                <div class="mb-4">
                  <label class="form-label fw-semibold">Lời nhắn</label>
                  <input type="text" name="message" class="form-control" placeholder="Nhập lời nhắn (tùy chọn)" maxlength="255"/>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold fs-5" id="btn-submit" disabled>
                  <i class="bi bi-shield-lock me-2"></i>Tiếp tục xác nhận OTP
                </button>
              </form>
            </div>

            <!-- TAB 2: Chuyển qua ngân hàng -->
            <!-- TAB 2: Chuyển qua ngân hàng -->

        

<div class="tab-pane fade" id="bank-tab">
  <form action="/BTL-MNM/api/transfer_bank.php" method="POST" id="bank-form">

  <div class="mb-3">
  <label class="form-label fw-semibold">Ngân hàng <span class="text-danger">*</span></label>
  <select name="bank_name" id="bank_name" class="form-select" required>
    <option value="">-- Chọn ngân hàng --</option>
    <?php foreach (['Vietcombank','Vietinbank','BIDV','Agribank','Techcombank','MB Bank','ACB','Sacombank','VPBank','TPBank','SHB','HDBank','OCB'] as $b): ?>
      <option><?= $b ?></option>
    <?php endforeach; ?>
    </select>
  </div>
        
    <div class="mb-3">
      <label class="form-label fw-semibold">Số tài khoản ngân hàng <span class="text-danger">*</span></label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
        <input type="text" name="account_number" id="bank_account_number"
               class="form-control" placeholder="Nhập số tài khoản" required/>
        <button type="button" class="btn btn-outline-secondary" id="btn-find-bank">Tra cứu</button>
      </div>
      <div class="form-text">Số tài khoản phải đã được đăng ký trong hệ thống</div>
    </div>

    <!-- Thông tin TK sau khi tra cứu -->
    <div id="bank-account-info" class="d-none mb-3">
      <div class="border rounded p-3 bg-light d-flex align-items-center gap-3">
        <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center"
             style="width:44px;height:44px;font-size:1.2rem">
          <i class="bi bi-bank"></i>
        </div>
        <div>
          <div class="fw-semibold" id="bank-recv-holder">---</div>
          <div class="text-muted small" id="bank-recv-bank">---</div>
          <div class="text-muted small" id="bank-recv-account">---</div>
        </div>
        <i class="bi bi-check-circle-fill text-success ms-auto fs-5"></i>
      </div>
    </div>

    <!-- Lỗi không tìm thấy -->
    <div id="bank-account-error" class="d-none mb-3">
      <div class="alert alert-danger small mb-0">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <span id="bank-error-msg"></span>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label fw-semibold">Số tiền <span class="text-danger">*</span></label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-cash"></i></span>
        <input type="text" id="bank_amount_display" class="form-control money-input form-control-lg" placeholder="0" required/>
        <span class="input-group-text">₫</span>
      </div>
      <input type="hidden" name="amount" id="bank_amount_raw"/>
    </div>

    <div class="mb-4">
      <label class="form-label fw-semibold">Nội dung chuyển tiền</label>
      <input type="text" name="message" class="form-control" placeholder="Nhập nội dung (tùy chọn)" maxlength="255"/>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold fs-5"
            id="btn-submit-bank" disabled>
      <i class="bi bi-shield-lock me-2"></i>Tiếp tục xác nhận OTP
    </button>
  </form>
</div>

          </div>
        </div>
      </div>

    </div>
  </div>
</div>
</main>

<script>
// Tìm người dùng (gọi API thật)
document.getElementById('btn-find').addEventListener('click', function () {
  const val = document.getElementById('receiver').value.trim();
  if (!val) return;

  fetch('/BTL-MNM/api/find_user.php?q=' + encodeURIComponent(val))
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        document.getElementById('recv-name').textContent  = data.full_name;
        document.getElementById('recv-phone').textContent = data.phone_number;
        document.getElementById('recv-avatar').src        = data.avatar_url;
        document.getElementById('receiver_id').value      = data.user_id;
        document.getElementById('receiver-info').classList.remove('d-none');
        document.getElementById('btn-submit').disabled    = false;
      } else {
        alert(data.message || 'Không tìm thấy người dùng.');
      }
    });
});

// Gán amount raw — form chuyển trong ví
document.getElementById('wallet-form').addEventListener('submit', function () {
  document.getElementById('wallet_amount_raw').value =
    document.getElementById('wallet_amount_display').value.replace(/\D/g, '');
});

// Tra cứu số TK ngân hàng
document.getElementById('btn-find-bank').addEventListener('click', function () {
  const accountNumber = document.getElementById('bank_account_number').value.trim();
  const bankName      = document.getElementById('bank_name').value.trim();
 if (!accountNumber || !bankName) {
    alert('Vui lòng chọn ngân hàng và nhập số tài khoản.');
    return;
  }

  const infoBox   = document.getElementById('bank-account-info');
  const errorBox  = document.getElementById('bank-account-error');
  const btnSubmit = document.getElementById('btn-submit-bank');

  // Reset
  infoBox.classList.add('d-none');
  errorBox.classList.add('d-none');
  btnSubmit.disabled = true;

  fetch('/BTL-MNM/api/find_bank_account.php?account_number=' + encodeURIComponent(accountNumber) + '&bank_name=' + encodeURIComponent(bankName))

    .then(r => r.json())
    .then(data => {
      if (data.success) {
        document.getElementById('bank-recv-holder').textContent  = data.account_holder;
        document.getElementById('bank-recv-bank').textContent    = data.bank_name;
        document.getElementById('bank-recv-account').textContent = data.account_number;
        infoBox.classList.remove('d-none');
        btnSubmit.disabled = false;
      } else {
        document.getElementById('bank-error-msg').textContent = data.message;
        errorBox.classList.remove('d-none');
      }
    });
});

// Gán amount raw — form chuyển ngân hàng
document.getElementById('bank-form').addEventListener('submit', function () {
  document.getElementById('bank_amount_raw').value =
    document.getElementById('bank_amount_display').value.replace(/\D/g, '');
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>