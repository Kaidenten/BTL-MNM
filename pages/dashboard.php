<?php
$pageTitle = 'Trang chủ';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

$db      = getDB();
$balance = $_SESSION['balance'] ?? 0;
$success = flash('success');

// Lấy 5 giao dịch gần nhất từ DB
$stmt = $db->prepare("
    SELECT t.*,
           us.full_name AS sender_name,
           ur.full_name AS receiver_name
    FROM transactions t
    LEFT JOIN wallets ws ON t.sender_wallet_id   = ws.wallet_id
    LEFT JOIN wallets wr ON t.receiver_wallet_id = wr.wallet_id
    LEFT JOIN users us   ON ws.user_id = us.user_id
    LEFT JOIN users ur   ON wr.user_id = ur.user_id
    WHERE ws.user_id = ? OR wr.user_id = ?
    ORDER BY t.created_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$recentTx = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<main class="py-4">
<div class="container">

  <?php if ($success): ?>
    <div class="alert alert-success alert-auto-dismiss d-flex align-items-center gap-2 mb-4">
      <i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($success) ?>
    </div>
  <?php endif; ?>

  <div class="row g-4 justify-content-center">

    <!-- Cột chính -->
    <div class="col-lg-7">

      <!-- Wallet Card -->
      <div class="wallet-card mb-3">
        <div class="label mb-1"><i class="bi bi-wallet2 me-1"></i>Số dư khả dụng</div>
        <div class="balance mb-3"><?= formatMoney($balance) ?></div>
        <div class="d-flex justify-content-between align-items-center">
          <div class="small opacity-75">
            <i class="bi bi-person me-1"></i><?= htmlspecialchars($_SESSION['full_name'] ?? 'Người dùng') ?>
          </div>
          <span class="badge bg-white text-primary">
            <i class="bi bi-shield-check me-1"></i>Bảo mật
          </span>
        </div>
      </div>


      <!-- Banner khuyến mãi -->
<div id="promoBanner" class="carousel slide mb-3" data-bs-ride="carousel">
  <div class="carousel-inner rounded-3 shadow-sm">

    <div class="carousel-item active">
      <a href="/BTL-MNM/pages/deposit.php" class="text-decoration-none">
        <div class="p-4 text-white rounded-3 d-flex align-items-center justify-content-between"
             style="background:linear-gradient(135deg,#f59e0b,#d97706);min-height:100px">
          <div>
            <div class="fw-bold fs-5">🎉 Hoàn tiền 5%</div>
            <div class="small opacity-90">Nạp ví từ 500.000 ₫ — Áp dụng đến 31/05/2026</div>
            <span class="badge bg-white text-warning mt-2">Nạp tiền ngay →</span>
          </div>
          <i class="bi bi-gift-fill fs-1 opacity-50"></i>
        </div>
      </a>
    </div>

    <div class="carousel-item">
      <a href="/BTL-MNM/pages/transfer.php" class="text-decoration-none">
        <div class="p-4 text-white rounded-3 d-flex align-items-center justify-content-between"
             style="background:linear-gradient(135deg,#10b981,#059669);min-height:100px">
          <div>
            <div class="fw-bold fs-5">⚡ Miễn phí chuyển tiền</div>
            <div class="small opacity-90">0 đồng phí chuyển khoản nội bộ tháng này</div>
            <span class="badge bg-white text-success mt-2">Chuyển tiền ngay →</span>
          </div>
          <i class="bi bi-lightning-charge-fill fs-1 opacity-50"></i>
        </div>
      </a>
    </div>

    <div class="carousel-item">
      <a href="/BTL-MNM/pages/payment.php" class="text-decoration-none">
        <div class="p-4 text-white rounded-3 d-flex align-items-center justify-content-between"
             style="background:linear-gradient(135deg,#3b82f6,#2563eb);min-height:100px">
          <div>
            <div class="fw-bold fs-5">🏠 Thanh toán điện nước</div>
            <div class="small opacity-90">Giảm ngay 10.000 ₫ khi thanh toán hóa đơn qua ví</div>
            <span class="badge bg-white text-primary mt-2">Thanh toán ngay →</span>
          </div>
          <i class="bi bi-house-fill fs-1 opacity-50"></i>
        </div>
      </a>
    </div>

  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#promoBanner" data-bs-slide="prev">
    <span class="carousel-control-prev-icon"></span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#promoBanner" data-bs-slide="next">
    <span class="carousel-control-next-icon"></span>
  </button>
</div>

      <!-- Thao tác nhanh -->
      <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
          <h6 class="fw-bold mb-3 text-muted text-uppercase" style="font-size:.75rem;letter-spacing:.5px">Thao tác nhanh</h6>
          <div class="row g-2 quick-action text-center">
            <div class="col-6">
              <a href="/BTL-MNM/pages/deposit.php" class="btn btn-outline-success w-100">
                <i class="bi bi-arrow-down-circle"></i>Nạp tiền
              </a>
            </div>
            <div class="col-6">
              <a href="/BTL-MNM/pages/withdraw.php" class="btn btn-outline-danger w-100">
                <i class="bi bi-arrow-up-circle"></i>Rút tiền
              </a>
            </div>
            <div class="col-6">
              <a href="/BTL-MNM/pages/transfer.php" class="btn btn-outline-primary w-100">
                <i class="bi bi-send"></i>Chuyển tiền
              </a>
            </div>
            <div class="col-6">
              <a href="/BTL-MNM/pages/payment.php" class="btn btn-outline-warning w-100">
                <i class="bi bi-receipt"></i>Thanh toán
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Giao dịch gần đây -->
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
          <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Giao dịch gần đây</h6>
          <a href="/BTL-MNM/pages/history.php" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
        </div>
        <div class="card-body p-0">
          <?php if (empty($recentTx)): ?>
            <div class="text-center text-muted py-5">
              <i class="bi bi-inbox fs-2 d-block mb-2"></i>Chưa có giao dịch nào
            </div>
          <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($recentTx as $tx):
              $isReceived = ($tx['type'] === 'deposit' ||
                ($tx['type'] === 'transfer' && $tx['receiver_name'] === ($_SESSION['full_name'] ?? '')));
              $iconMap = [
                'deposit'  => ['bg-success-subtle text-success', 'bi-arrow-down-circle-fill'],
                'transfer' => ['bg-primary-subtle text-primary', 'bi-send-fill'],
                'payment'  => ['bg-warning-subtle text-warning', 'bi-receipt-cutoff'],
                'withdraw' => ['bg-danger-subtle text-danger',   'bi-arrow-up-circle-fill'],
              ];
              [$bg, $ico] = $iconMap[$tx['type']] ?? ['bg-secondary-subtle text-secondary', 'bi-question'];
            ?>
            <div class="list-group-item px-4 py-3 d-flex align-items-center gap-3">
              <div class="tx-icon <?= $bg ?>"><i class="bi <?= $ico ?>"></i></div>
              <div class="flex-grow-1">
                <div class="fw-semibold small"><?= htmlspecialchars($tx['message'] ?? '—') ?></div>
                <div class="text-muted" style="font-size:.75rem"><?= $tx['created_at'] ?></div>
              </div>
              <div class="text-end">
                <div class="fw-bold <?= $isReceived ? 'text-success' : 'text-danger' ?>">
                  <?= $isReceived ? '+' : '-' ?><?= formatMoney($tx['amount']) ?>
                </div>
                <div><?= statusBadge($tx['status']) ?></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>