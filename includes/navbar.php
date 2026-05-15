<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$currentPage = basename($_SERVER['PHP_SELF']);

// Tự load database nếu chưa có
if (!function_exists('getDB')) {
    require_once dirname(__DIR__) . '/config/database.php';
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
<div class="container">
  <a class="navbar-brand fw-bold" href="/BTL-MNM/pages/dashboard.php">
    <i class="bi bi-wallet2 me-2"></i>E-Wallet
  </a>
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navMenu">
    <ul class="navbar-nav me-auto">
      <li class="nav-item">
        <a class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>" href="/BTL-MNM/pages/dashboard.php">
          <i class="bi bi-house me-1"></i>Trang chủ
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $currentPage === 'transfer.php' ? 'active' : '' ?>" href="/BTL-MNM/pages/transfer.php">
          <i class="bi bi-send me-1"></i>Chuyển tiền
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $currentPage === 'payment.php' ? 'active' : '' ?>" href="/BTL-MNM/pages/payment.php">
          <i class="bi bi-receipt me-1"></i>Thanh toán
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $currentPage === 'history.php' ? 'active' : '' ?>" href="/BTL-MNM/pages/history.php">
          <i class="bi bi-clock-history me-1"></i>Lịch sử
        </a>
      </li>
    </ul>
    <ul class="navbar-nav ms-auto align-items-center gap-2">

      <!-- Số dư -->
      <li class="nav-item">
        <span class="badge bg-warning text-dark fs-6 px-3 py-2">
          <i class="bi bi-coin me-1"></i>
          <?= isset($_SESSION['balance']) ? number_format($_SESSION['balance'], 0, ',', '.') . ' ₫' : '---' ?>
        </span>
      </li>

      <!-- Thông báo -->
      <li class="nav-item">
        <a class="nav-link position-relative" href="/BTL-MNM/pages/notification.php">
          <i class="bi bi-bell fs-5"></i>
          <?php if (isset($_SESSION['user_id'])):
            $dbNav    = getDB();
            $stmtNav  = $dbNav->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE");
            $stmtNav->execute([$_SESSION['user_id']]);
            $navUnread = $stmtNav->fetchColumn();
            if ($navUnread > 0): ?>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.6rem">
                <?= $navUnread ?>
              </span>
            <?php endif;
          endif; ?>
        </a>
      </li>

      <!-- Avatar dropdown -->
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
          <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['full_name'] ?? 'User') ?>&background=ffffff&color=0d6efd&size=32"
               class="rounded-circle" width="32" height="32" alt="avatar"/>
          <span class="d-none d-lg-inline"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Người dùng') ?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="/BTL-MNM/pages/profile.php"><i class="bi bi-person me-2"></i>Tài khoản</a></li>
          <li><a class="dropdown-item" href="/BTL-MNM/pages/bank_account.php"><i class="bi bi-bank me-2"></i>Ngân hàng liên kết</a></li>
          <li><hr class="dropdown-divider"/></li>
          <li><a class="dropdown-item text-danger" href="/BTL-MNM/api/auth_logout.php"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a></li>
        </ul>
      </li>

    </ul>
  </div>
</div>
</nav>