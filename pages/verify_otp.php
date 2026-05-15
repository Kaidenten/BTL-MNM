<?php
$pageTitle = 'Xác nhận OTP';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';

// Nếu không có pending_deposit thì redirect về deposit
if (empty($_SESSION['pending_deposit'])) {
    header('Location: /BTL-MNM/pages/deposit.php');
    exit();
}

$amount  = $_SESSION['pending_deposit']['amount'];
$otpCode = flash('otp_code'); // Chỉ hiện khi test
$error   = flash('error');
?>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<main class="py-4">
<div class="container">
  <div class="row justify-content-center">
    <div class="col-lg-5">

      <div class="d-flex align-items-center gap-3 mb-4">
        <a href="/BTL-MNM/pages/deposit.php" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
        <div>
          <h5 class="mb-0 fw-bold">Xác nhận OTP</h5>
          <small class="text-muted">Nhập mã OTP để xác nhận nạp tiền</small>
        </div>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger alert-auto-dismiss"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <!-- Giả lập hiển thị OTP để test -->
      <?php if ($otpCode): ?>
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
          <i class="bi bi-info-circle-fill"></i>
          <div>
            <strong>Mã OTP của bạn (giả lập):</strong>
            <span class="fs-4 fw-bold text-danger ms-2 letter-spacing-2"><?= $otpCode ?></span>
          </div>
        </div>
      <?php endif; ?>

      <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

          <!-- Thông tin nạp tiền -->
          <div class="bg-light rounded p-3 mb-4 d-flex justify-content-between align-items-center">
            <span class="text-muted">Số tiền nạp</span>
            <span class="fw-bold fs-5 text-success">+<?= number_format($amount, 0, ',', '.') ?> ₫</span>
          </div>

          <p class="text-muted small text-center mb-4">
            <i class="bi bi-shield-lock me-1"></i>
            Mã OTP có hiệu lực trong <strong>5 phút</strong>
          </p>

          <form action="/BTL-MNM/api/verify_otp_deposit.php" method="POST">
            <div class="mb-4">
              <label class="form-label fw-semibold">Nhập mã OTP <span class="text-danger">*</span></label>
              <input type="text" name="otp_code" class="form-control form-control-lg text-center fw-bold"
                     placeholder="_ _ _ _ _ _" maxlength="6" pattern="\d{6}"
                     autocomplete="one-time-code" required autofocus
                     style="font-size:1.8rem;letter-spacing:8px"/>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
              <i class="bi bi-check-circle me-2"></i>Xác nhận nạp tiền
            </button>
          </form>

          <div class="text-center mt-3">
            <a href="/BTL-MNM/vnpay/create_payment.php" class="small text-muted">
              <i class="bi bi-arrow-repeat me-1"></i>Gửi lại OTP
            </a>
          </div>

        </div>
      </div>

    </div>
  </div>
</div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>