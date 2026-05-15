<?php
$pageTitle = 'Xác nhận OTP chuyển tiền trong ví';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (empty($_SESSION['pending_transfer'])) {
    header('Location: /BTL-MNM/pages/transfer.php');
    exit();
}

$pending = $_SESSION['pending_transfer'];
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
        <a href="/BTL-MNM/pages/transfer.php" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
        <div>
          <h5 class="mb-0 fw-bold">Xác nhận OTP</h5>
          <small class="text-muted">Xác nhận chuyển tiền trong ví</small>
        </div>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger alert-auto-dismiss"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if ($otpCode): ?>
        <div class="alert alert-warning">
          <strong>Mã OTP (giả lập):</strong>
          <span class="fs-4 fw-bold text-danger ms-2"><?= $otpCode ?></span>
        </div>
      <?php endif; ?>

      <!-- Thông tin giao dịch -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
          <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>Thông tin chuyển tiền</h6>
          <table class="table table-borderless small mb-0">
            <tr>
              <td class="text-muted fw-semibold" width="45%">Người nhận</td>
              <td class="fw-bold"><?= htmlspecialchars($pending['receiver_name']) ?></td>
            </tr>
            <tr>
              <td class="text-muted fw-semibold">Số tiền</td>
              <td class="fw-bold text-danger fs-5">-<?= formatMoney($pending['amount']) ?></td>
            </tr>
            <tr>
              <td class="text-muted fw-semibold">Lời nhắn</td>
              <td><?= htmlspecialchars($pending['message'] ?: '—') ?></td>
            </tr>
          </table>
        </div>
      </div>

      <!-- Form OTP -->
      <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
          <p class="text-muted small text-center mb-4">
            <i class="bi bi-shield-lock me-1"></i>Mã OTP có hiệu lực trong <strong>5 phút</strong>
          </p>
          <form action="/BTL-MNM/api/verify_otp_wallet_transfer.php" method="POST">
            <div class="mb-4">
              <label class="form-label fw-semibold">Nhập mã OTP <span class="text-danger">*</span></label>
              <input type="text" name="otp_code"
                     class="form-control form-control-lg text-center fw-bold"
                     placeholder="_ _ _ _ _ _" maxlength="6" pattern="\d{6}"
                     autocomplete="one-time-code" required autofocus
                     style="font-size:1.8rem;letter-spacing:8px"/>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
              <i class="bi bi-check-circle me-2"></i>Xác nhận chuyển tiền
            </button>
          </form>
          <div class="text-center mt-3">
            <a href="/BTL-MNM/pages/transfer.php" class="small text-muted">
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