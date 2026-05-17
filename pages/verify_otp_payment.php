<?php
$pageTitle = 'Xác nhận OTP thanh toán';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (empty($_SESSION['pending_payment'])) {
    header('Location: /BTL-MNM/pages/payment.php');
    exit();
}

$pending = $_SESSION['pending_payment'];
$otpCode = flash('otp_code');
$error   = flash('error');

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="py-4">
<div class="container">
  <div class="row justify-content-center">
    <div class="col-lg-5">

      <div class="d-flex align-items-center gap-3 mb-4">
        <a href="/BTL-MNM/pages/payment.php" class="btn btn-light btn-sm">
          <i class="bi bi-arrow-left"></i>
        </a>
        <div>
          <h5 class="mb-0 fw-bold">Xác nhận OTP</h5>
          <small class="text-muted">Xác nhận thanh toán hóa đơn</small>
        </div>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger alert-auto-dismiss"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if ($otpCode): ?>
        <div class="alert alert-warning d-flex align-items-center gap-3 mb-4">
          <i class="bi bi-shield-lock fs-4"></i>
          <div>
            <div class="small fw-semibold">Mã OTP của bạn (giả lập)</div>
            <div class="fs-3 fw-bold text-danger"><?= $otpCode ?></div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Thông tin hóa đơn -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
          <h6 class="mb-0 fw-bold">
            <i class="bi bi-receipt me-2 text-primary"></i>Thông tin thanh toán
          </h6>
        </div>
        <div class="card-body p-4">
          <table class="table table-borderless small mb-0">
            <tr>
              <td class="text-muted fw-semibold" width="45%">Dịch vụ</td>
              <td class="fw-bold"><?= htmlspecialchars($pending['service_name']) ?></td>
            </tr>
            <tr>
              <td class="text-muted fw-semibold">Nhà cung cấp</td>
              <td><?= htmlspecialchars($pending['provider_name']) ?></td>
            </tr>
            <tr>
              <td class="text-muted fw-semibold">Mã khách hàng</td>
              <td class="fw-semibold"><?= htmlspecialchars($pending['customer_code']) ?></td>
            </tr>
            <tr>
              <td class="text-muted fw-semibold">Chủ hộ</td>
              <td><?= htmlspecialchars($pending['customer_name']) ?></td>
            </tr>
            <tr>
              <td class="text-muted fw-semibold">Kỳ thanh toán</td>
              <td><?= htmlspecialchars($pending['bill_period']) ?></td>
            </tr>
            <tr>
              <td class="text-muted fw-semibold">Số tiền</td>
              <td class="fw-bold text-danger fs-5">
                -<?= formatMoney($pending['amount']) ?>
              </td>
            </tr>
          </table>
        </div>
      </div>

      <!-- Form nhập OTP -->
      <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
          <p class="text-muted small text-center mb-4">
            <i class="bi bi-shield-lock me-1"></i>
            Mã OTP có hiệu lực trong <strong>5 phút</strong>
          </p>
          <form action="/BTL-MNM/api/verify_otp_payment.php" method="POST">
            <div class="mb-4">
              <label class="form-label fw-semibold">
                Nhập mã OTP <span class="text-danger">*</span>
              </label>
              <input type="text" name="otp_code"
                     class="form-control form-control-lg text-center fw-bold"
                     placeholder="_ _ _ _ _ _"
                     maxlength="6" pattern="\d{6}"
                     autocomplete="one-time-code"
                     required autofocus
                     style="font-size:1.8rem;letter-spacing:8px"/>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
              <i class="bi bi-check-circle me-2"></i>Xác nhận thanh toán
            </button>
          </form>
          <div class="text-center mt-3">
            <a href="/BTL-MNM/pages/payment.php" class="small text-muted">
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