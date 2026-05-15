<?php
$pageTitle = 'Ngân hàng liên kết';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

$db = getDB();

$success   = flash('success');
$error     = flash('error');
$justAdded = isset($_GET['added']) && $_GET['added'] == 1;

$stmt = $db->prepare("SELECT * FROM bank_accounts WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$bankAccounts = $stmt->fetchAll();

$bankList = ['Vietcombank','Vietinbank','BIDV','Agribank','Techcombank','MB Bank','ACB','Sacombank','VPBank','TPBank','SHB','HDBank','OCB'];

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="py-4">
<div class="container">

  <div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
      <a href="/BTL-MNM/pages/dashboard.php" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
      <div>
        <h5 class="mb-0 fw-bold">Ngân hàng liên kết</h5>
        <small class="text-muted">Quản lý tài khoản ngân hàng để rút tiền</small>
      </div>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBankModal">
      <i class="bi bi-plus-circle me-1"></i>Thêm tài khoản
    </button>
  </div>

  <?php if ($justAdded): ?>
    <div class="alert alert-info alert-auto-dismiss d-flex align-items-center gap-2 mb-4">
      <i class="bi bi-hourglass-split fs-5"></i>
      <div>
        <div class="fw-bold">Yêu cầu đã được gửi!</div>
        <div class="small">Tài khoản ngân hàng đang chờ admin xác nhận. Bạn sẽ nhận thông báo khi được duyệt.</div>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="alert alert-success alert-auto-dismiss"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-danger alert-auto-dismiss"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <!-- Danh sách tài khoản -->
  <div class="row g-3">
    <?php foreach ($bankAccounts as $ba): ?>
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100 <?= $ba['is_default'] && $ba['is_verified'] ? 'border-primary border' : '' ?>">
        <div class="card-body p-4">
          <div class="d-flex align-items-start gap-3">

            <!-- Icon ngân hàng -->
            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center"
                 style="width:50px;height:50px;font-size:1.4rem">
              <i class="bi bi-bank"></i>
            </div>

            <div class="flex-grow-1">
              <!-- Tên ngân hàng + badge -->
              <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="fw-bold"><?= htmlspecialchars($ba['bank_name']) ?></div>
                <div class="d-flex gap-1 flex-wrap justify-content-end">
                  <?php if (!$ba['is_verified']): ?>
                    <span class="badge bg-warning text-dark">
                      <i class="bi bi-hourglass-split me-1"></i>Chờ duyệt
                    </span>
                  <?php else: ?>
                    <span class="badge bg-success">
                      <i class="bi bi-check me-1"></i>Đã xác thực
                    </span>
                    <?php if ($ba['is_default']): ?>
                      <span class="badge bg-primary">Mặc định</span>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>
              </div>

              <div class="text-muted small"><?= htmlspecialchars($ba['account_number']) ?></div>
              <div class="text-muted small"><?= htmlspecialchars($ba['account_holder']) ?></div>

              <?php if (!$ba['is_verified']): ?>
                <div class="text-warning small mt-2">
                  <i class="bi bi-info-circle me-1"></i>Đang chờ admin xác nhận, chưa thể sử dụng
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Nút thao tác -->
          <?php if ($ba['is_verified']): ?>
          <div class="d-flex gap-2 mt-3">
            <?php if (!$ba['is_default']): ?>
            <form action="/BTL-MNM/api/bank_set_default.php" method="POST" class="d-inline">
              <input type="hidden" name="bank_account_id" value="<?= $ba['bank_account_id'] ?>"/>
              <button type="submit" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-star me-1"></i>Đặt mặc định
              </button>
            </form>
            <?php endif; ?>
            <form action="/BTL-MNM/api/bank_delete.php" method="POST" class="d-inline"
                  onsubmit="return confirm('Xóa tài khoản này?')">
              <input type="hidden" name="bank_account_id" value="<?= $ba['bank_account_id'] ?>"/>
              <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-trash me-1"></i>Xóa
              </button>
            </form>
          </div>
          <?php else: ?>
          <!-- TK chưa duyệt chỉ có nút hủy yêu cầu -->
          <div class="d-flex gap-2 mt-3">
            <form action="/BTL-MNM/api/bank_delete.php" method="POST" class="d-inline"
                  onsubmit="return confirm('Hủy yêu cầu liên kết này?')">
              <input type="hidden" name="bank_account_id" value="<?= $ba['bank_account_id'] ?>"/>
              <button type="submit" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-x-circle me-1"></i>Hủy yêu cầu
              </button>
            </form>
          </div>
          <?php endif; ?>

        </div>
      </div>
    </div>
    <?php endforeach; ?>

    <!-- Card thêm mới -->
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100 d-flex align-items-center justify-content-center"
           style="border:2px dashed #dee2e6!important;min-height:160px;cursor:pointer"
           data-bs-toggle="modal" data-bs-target="#addBankModal">
        <div class="text-center text-muted">
          <i class="bi bi-plus-circle fs-2 d-block mb-2"></i>
          <span class="small fw-semibold">Thêm tài khoản ngân hàng</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Ghi chú -->
  <div class="alert alert-light border mt-4 small">
    <i class="bi bi-info-circle text-primary me-2"></i>
    Tài khoản ngân hàng sau khi thêm sẽ cần được admin xác nhận trước khi sử dụng.
    Thời gian xét duyệt thường trong vòng <strong>24 giờ</strong>.
  </div>

</div>
</main>

<!-- Modal thêm tài khoản -->
<div class="modal fade" id="addBankModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0">
        <h6 class="modal-title fw-bold"><i class="bi bi-bank me-2"></i>Thêm tài khoản ngân hàng</h6>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="alert alert-warning small mb-3">
          <i class="bi bi-exclamation-triangle me-1"></i>
          Tài khoản sẽ cần admin xác nhận trước khi sử dụng được.
        </div>
        <form action="/BTL-MNM/api/bank_add.php" method="POST">
          <div class="mb-3">
            <label class="form-label fw-semibold">Ngân hàng <span class="text-danger">*</span></label>
            <select name="bank_name" class="form-select" required>
              <option value="">-- Chọn ngân hàng --</option>
              <?php foreach ($bankList as $b): ?>
                <option><?= $b ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Số tài khoản <span class="text-danger">*</span></label>
            <input type="text" name="account_number" class="form-control"
                   placeholder="Nhập số tài khoản" required/>
          </div>
          <div class="mb-4">
            <label class="form-label fw-semibold">Tên chủ tài khoản <span class="text-danger">*</span></label>
            <input type="text" name="account_holder" class="form-control"
                   placeholder="NGUYEN VAN A (chữ in hoa)" required/>
          </div>
          <button type="submit" class="btn btn-primary w-100 fw-semibold">
            <i class="bi bi-send me-2"></i>Gửi yêu cầu liên kết
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>