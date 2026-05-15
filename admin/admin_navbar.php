<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
if (session_status() === PHP_SESSION_NONE) session_start();
$currentPage = basename($_SERVER['PHP_SELF']);

if (!function_exists('getDB')) {
    require_once dirname(__DIR__) . '/config/database.php';
}

// Đếm TK ngân hàng chờ duyệt
$pendingBankCount = 0;
if (isset($_SESSION['user_id'])) {
    $stmtPending = getDB()->prepare("SELECT COUNT(*) FROM bank_accounts WHERE is_verified = FALSE");
    $stmtPending->execute();
    $pendingBankCount = $stmtPending->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?= $pageTitle ?? 'Admin' ?> — E-Wallet Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"/>
  <link href="/BTL-MNM/assets/css/style.css" rel="stylesheet"/>
  <style>
    .admin-sidebar {
      background: linear-gradient(180deg, #1e3a5f 0%, #0f2027 100%);
      box-shadow: 4px 0 15px rgba(0,0,0,.2);
    }
    .admin-sidebar .nav-link {
      color: rgba(255,255,255,.7);
      border-radius: .5rem;
      transition: all .2s;
    }
    .admin-sidebar .nav-link:hover {
      background: rgba(255,255,255,.1);
      color: #fff;
      transform: translateX(4px);
    }
    .admin-sidebar .nav-link.active {
      background: rgba(59,130,246,.3);
      color: #60a5fa;
      border-left: 3px solid #3b82f6;
    }
  </style>
</head>
<body class="bg-light">

<div class="d-flex" style="min-height:100vh">

  <!-- Sidebar -->
  <div class="admin-sidebar d-flex flex-column p-3" style="width:240px;min-width:240px">

    <!-- Logo -->
    <div class="text-white fw-bold fs-5 mb-4 px-2 d-flex align-items-center gap-2">
      <i class="bi bi-shield-check text-warning"></i> Admin Panel
    </div>

    <!-- Menu -->
    <nav class="nav flex-column gap-1">
      <a href="/BTL-MNM/admin/dashboard.php"
         class="nav-link px-3 py-2 d-flex align-items-center gap-2 <?= $currentPage==='dashboard.php' ? 'active' : '' ?>">
        <i class="bi bi-speedometer2"></i> Dashboard
      </a>

      <a href="/BTL-MNM/admin/users.php"
         class="nav-link px-3 py-2 d-flex align-items-center gap-2 <?= $currentPage==='users.php' ? 'active' : '' ?>">
        <i class="bi bi-people"></i> Quản lý User
      </a>

      <a href="/BTL-MNM/admin/transactions.php"
         class="nav-link px-3 py-2 d-flex align-items-center gap-2 <?= $currentPage==='transactions.php' ? 'active' : '' ?>">
        <i class="bi bi-arrow-left-right"></i> Giao dịch
      </a>

      <!-- Menu duyệt ngân hàng — có badge đỏ nếu có TK chờ duyệt -->
      <a href="/BTL-MNM/admin/bank_accounts.php"
         class="nav-link px-3 py-2 d-flex align-items-center gap-2 <?= $currentPage==='bank_accounts.php' ? 'active' : '' ?>">
        <i class="bi bi-bank"></i>
        <span class="flex-grow-1">Duyệt ngân hàng</span>
        <?php if ($pendingBankCount > 0): ?>
          <span class="badge bg-danger rounded-pill"><?= $pendingBankCount ?></span>
        <?php endif; ?>
      </a>
    </nav>

    <!-- Logout -->
    <div class="mt-auto">
      <hr style="border-color:rgba(255,255,255,.2)"/>
      <div class="d-flex align-items-center gap-2 px-2 mb-3">
        <img src="https://ui-avatars.com/api/?name=Admin&background=f59e0b&color=fff&size=32"
             class="rounded-circle" width="32" height="32" alt="admin"/>
        <div class="text-white small"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin') ?></div>
      </div>
      <a href="/BTL-MNM/api/auth_logout.php"
         class="nav-link px-3 py-2 d-flex align-items-center gap-2 text-danger">
        <i class="bi bi-box-arrow-right"></i> Đăng xuất
      </a>
    </div>
  </div>

  <!-- Nội dung chính -->
  <div class="flex-grow-1 d-flex flex-column">
    <!-- Topbar -->
    <div class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
      <h6 class="mb-0 fw-bold text-muted"><?= $pageTitle ?? 'Dashboard' ?></h6>
      <div class="text-muted small"><i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y H:i') ?></div>
    </div>
    <main class="p-4 flex-grow-1">