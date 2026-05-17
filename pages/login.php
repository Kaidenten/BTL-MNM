
<?php
$pageTitle = 'Đăng nhập';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';
$error   = flash('error');
$success = flash('success');
?>
<main class="d-flex align-items-center min-vh-100">
  <div class="container">
    <div class="auth-card card shadow border-0 mx-auto">
      <div class="card-body p-4 p-md-5">
        <!-- Logo -->
        <div class="text-center mb-4">
          <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:60px;height:60px">
            <i class="bi bi-wallet2 fs-3"></i>
          </div>
          <h4 class="fw-bold">E-Wallet</h4>
          <p class="text-muted small">Đăng nhập vào tài khoản của bạn</p>
        </div>
 
        <?php if ($error): ?>
          <div class="alert alert-danger alert-auto-dismiss d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill"></i><?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>
        <?php if ($success): ?>
          <div class="alert alert-success alert-auto-dismiss"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
 
        <form action="/BTL-MNM/api/auth_login.php" method="POST" novalidate>
          <div class="mb-3">
            <label class="form-label fw-semibold">Email / Số điện thoại</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-person"></i></span>
              <input type="text" name="identifier" class="form-control" placeholder="Email hoặc SĐT" required autofocus/>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Mật khẩu</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock"></i></span>
              <input type="password" name="password" id="password" class="form-control" placeholder="Nhập mật khẩu" required/>
              <button type="button" class="btn btn-outline-secondary toggle-password" data-target="#password">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="remember" id="remember"/>
              <label class="form-check-label small" for="remember">Ghi nhớ đăng nhập</label>
            </div>
            <a href="#" class="small text-primary">Quên mật khẩu?</a>
          </div>
          <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
            <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
          </button>
        </form>
 
        <hr class="my-4"/>
        <p class="text-center text-muted small mb-0">
          Chưa có tài khoản?
          <a href="/BTL-MNM/pages/register.php" class="text-primary fw-semibold">Đăng ký ngay</a>
        </p>
      </div>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
