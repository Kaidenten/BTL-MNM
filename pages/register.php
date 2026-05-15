<?php
$pageTitle = 'Đăng ký';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';
$error = flash('error');
?>
<main class="d-flex align-items-center min-vh-100 py-5">
  <div class="container">
    <div class="auth-card card shadow border-0 mx-auto" style="max-width:520px">
      <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4">
          <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:60px;height:60px">
            <i class="bi bi-person-plus fs-3"></i>
          </div>
          <h4 class="fw-bold">Tạo tài khoản</h4>
          <p class="text-muted small">Đăng ký miễn phí, ví được tạo tự động</p>
        </div>
 
        <?php if ($error): ?>
          <div class="alert alert-danger alert-auto-dismiss"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
 
        <form action="/BTL-MNM/api/auth_register.php" method="POST" novalidate>
          <div class="mb-3">
            <label class="form-label fw-semibold">Họ và tên</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person"></i></span>
              <input type="text" name="full_name" class="form-control" placeholder="Họ tên" required autofocus/>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-envelope"></i></span>
              <input type="email" name="email" class="form-control" placeholder="example@email.com" required/>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Số điện thoại</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-phone"></i></span>
              <input type="tel" name="phone_number" class="form-control" placeholder="0xxx xxx xxx" pattern="[0-9]{10,11}" required/>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Mật khẩu</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock"></i></span>
              <input type="password" name="password" id="reg-password" class="form-control" placeholder="Tối thiểu 8 ký tự" required minlength="8"/>
              <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#reg-password">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>
          <div class="mb-4">
            <label class="form-label fw-semibold">Xác nhận mật khẩu</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
              <input type="password" name="confirm_password" id="reg-confirm" class="form-control" placeholder="Nhập lại mật khẩu" required/>
              <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#reg-confirm">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>
     
          <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="agree" required/>
            <label class="form-check-label small" for="agree">
              Tôi đồng ý với <a href="#" class="text-primary">Điều khoản sử dụng</a> và <a href="#" class="text-primary">Chính sách bảo mật</a>
            </label>
          </div>
          <button type="submit" class="btn btn-success w-100 py-2 fw-semibold">
            <i class="bi bi-person-check me-2"></i>Đăng ký tài khoản
          </button>
        </form>
 
        <hr class="my-4"/>
        <p class="text-center text-muted small mb-0">
          Đã có tài khoản? <a href="/BTL-MNM/pages/login.php" class="text-primary fw-semibold">Đăng nhập</a>
        </p>
      </div>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
