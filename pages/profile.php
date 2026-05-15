
<?php
$pageTitle = 'Tài khoản';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';
$success = flash('success');
$error   = flash('error');
// Giả lập user info (thực tế lấy từ DB)
$user = ['full_name'=>$_SESSION['full_name']??'Nguyễn Văn A','email'=>'user@email.com','phone_number'=>'0912345678','status'=>'active','created_at'=>'2025-01-01'];
?>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
 
<main class="py-4">
<div class="container">
 
  <?php if ($success): ?><div class="alert alert-success alert-auto-dismiss"><?= htmlspecialchars($success) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger alert-auto-dismiss"><?= htmlspecialchars($error) ?></div><?php endif; ?>
 
  <div class="d-flex align-items-center gap-3 mb-4">
    <a href="/BTL-MNM/pages/dashboard.php" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
    <h5 class="mb-0 fw-bold">Thông tin tài khoản</h5>
  </div>
 
  <div class="row g-4">
    <!-- Avatar + menu -->
    <div class="col-lg-3">
      <div class="card border-0 shadow-sm text-center p-4">
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['full_name']) ?>&background=0d6efd&color=fff&size=100"
             class="rounded-circle mx-auto mb-3" width="90" height="90" alt="avatar"/>
        <div class="fw-bold"><?= htmlspecialchars($user['full_name']) ?></div>
        <div class="text-muted small mb-3"><?= htmlspecialchars($user['email']) ?></div>
        <span class="badge bg-success">Hoạt động</span>
        <hr/>
        <div class="list-group list-group-flush text-start">
          <a href="#info"      class="list-group-item list-group-item-action border-0 rounded"><i class="bi bi-person me-2 text-primary"></i>Thông tin cá nhân</a>
          <a href="#security"  class="list-group-item list-group-item-action border-0 rounded"><i class="bi bi-shield-lock me-2 text-warning"></i>Bảo mật</a>
          <a href="#pin"       class="list-group-item list-group-item-action border-0 rounded"><i class="bi bi-key me-2 text-info"></i>Mã PIN giao dịch</a>
        </div>
      </div>
    </div>
 
    <!-- Nội dung -->
    <div class="col-lg-9">
      <!-- Thông tin cá nhân -->
      <div class="card border-0 shadow-sm mb-4" id="info">
        <div class="card-header bg-white fw-bold py-3"><i class="bi bi-person me-2 text-primary"></i>Thông tin cá nhân</div>
        <div class="card-body p-4">
          <form action="/BTL-MNM/api/update_profile.php" method="POST">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Họ và tên</label>
                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required/>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled/>
                <div class="form-text">Email không thể thay đổi</div>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Số điện thoại</label>
                <input type="tel" name="phone_number" class="form-control" value="<?= htmlspecialchars($user['phone_number']) ?>"/>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Ngày tham gia</label>
                <input type="text" class="form-control" value="<?= $user['created_at'] ?>" disabled/>
              </div>
            </div>
            <div class="mt-3">
              <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-2"></i>Lưu thay đổi</button>
            </div>
          </form>
        </div>
      </div>
 
      <!-- Bảo mật -->
      <div class="card border-0 shadow-sm mb-4" id="security">
        <div class="card-header bg-white fw-bold py-3"><i class="bi bi-shield-lock me-2 text-warning"></i>Đổi mật khẩu</div>
        <div class="card-body p-4">
          <form action="/BTL-MNM/api/change_password.php" method="POST">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label fw-semibold">Mật khẩu hiện tại</label>
                <input type="password" name="old_password" class="form-control" required/>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Mật khẩu mới</label>
                <input type="password" name="new_password" class="form-control" minlength="8" required/>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold">Xác nhận mật khẩu</label>
                <input type="password" name="confirm_password" class="form-control" required/>
              </div>
            </div>
            <div class="mt-3">
              <button type="submit" class="btn btn-warning px-4"><i class="bi bi-lock me-2"></i>Đổi mật khẩu</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
