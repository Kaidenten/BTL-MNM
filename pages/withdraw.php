<?php
require_once __DIR__ . '/../config/database.php';
$pageTitle = 'Rút tiền';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';
$balance  = $_SESSION['balance'] ?? 0;
$error    = flash('error');
$success  = flash('success');
$db = getDB();
$stmt = $db->prepare("SELECT * FROM bank_accounts WHERE user_id = ? ORDER BY is_default DESC");
$stmt->execute([$_SESSION['user_id']]);
$bankAccounts = $stmt->fetchAll();
?>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
 
<main class="py-4">
<div class="container">
  <div class="row justify-content-center">
    <div class="col-lg-6">
 
      <div class="d-flex align-items-center gap-3 mb-4">
        <a href="/BTL-MNM/pages/dashboard.php" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
        <div>
          <h5 class="mb-0 fw-bold">Rút tiền</h5>
          <small class="text-muted">Chuyển về tài khoản ngân hàng liên kết</small>
        </div>
      </div>
 
      <?php if ($error): ?>
        <div class="alert alert-danger alert-auto-dismiss"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
 
      <!-- Số dư -->
      <div class="card border-0 bg-danger text-white shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center py-3">
          <div><div class="small opacity-75">Số dư khả dụng</div><div class="fw-bold fs-5"><?= formatMoney($balance) ?></div></div>
          <i class="bi bi-wallet2 fs-2 opacity-50"></i>
        </div>
      </div>
 
      <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
          <form action="/BTL-MNM/api/withdraw.php" method="POST">
 
            <!-- Chọn tài khoản ngân hàng -->
            <div class="mb-3">
              <label class="form-label fw-semibold">Tài khoản nhận <span class="text-danger">*</span></label>
              <?php if (empty($bankAccounts)): ?>
                <div class="alert alert-warning small">
                  Bạn chưa liên kết tài khoản ngân hàng.
                  <a href="/BTL-MNM/pages/bank_account.php" class="alert-link">Thêm ngay</a>
                </div>
              <?php else: ?>
                <?php foreach ($bankAccounts as $ba): ?>
                <div class="form-check mb-2">
                  <input class="form-check-input" type="radio" name="bank_account_id"
                         id="ba-<?= $ba['bank_account_id'] ?>" value="<?= $ba['bank_account_id'] ?>" required/>
                  <label class="form-check-label border rounded p-3 d-flex align-items-center gap-3 w-100 cursor-pointer"
                         for="ba-<?= $ba['bank_account_id'] ?>">
                    <i class="bi bi-bank fs-4 text-primary"></i>
                    <div>
                      <div class="fw-semibold"><?= htmlspecialchars($ba['bank_name']) ?></div>
                      <div class="text-muted small"><?= htmlspecialchars($ba['account_number']) ?> — <?= htmlspecialchars($ba['account_holder']) ?></div>
                    </div>
                  </label>
                </div>
                <?php endforeach; ?>
                <a href="/BTL-MNM/pages/bank_account.php" class="small text-primary"><i class="bi bi-plus-circle me-1"></i>Thêm tài khoản khác</a>
              <?php endif; ?>
            </div>
 
            <!-- Số tiền -->
            <div class="mb-3">
              <label class="form-label fw-semibold">Số tiền rút <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-cash"></i></span>
                <input type="text" id="amount_display" class="form-control money-input form-control-lg" placeholder="0" required/>
                <span class="input-group-text">₫</span>
              </div>
              <input type="hidden" name="amount" id="amount_raw"/>
              <div class="form-text">Tối thiểu 50.000 ₫ — Phí rút: 0 ₫</div>
            </div>
 
            <button type="submit" class="btn btn-danger w-100 py-2 fw-semibold fs-5">
  <i class="bi bi-shield-lock me-2"></i>Tiếp tục xác nhận OTP
</button>
          </form>
        </div>
      </div>
 
    </div>
  </div>
</div>
</main>
 
<script>
document.querySelector('form').addEventListener('submit', function () {
  document.getElementById('amount_raw').value = document.getElementById('amount_display').value.replace(/\D/g,'');
});
</script>
 
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
