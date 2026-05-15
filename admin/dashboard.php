<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/admin_auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
 
$db = getDB();
 
// Thống kê tổng quan
$totalUsers   = $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$activeUsers  = $db->query("SELECT COUNT(*) FROM users WHERE status = 'active' AND role = 'user'")->fetchColumn();
$lockedUsers  = $db->query("SELECT COUNT(*) FROM users WHERE status = 'locked'")->fetchColumn();
$totalTx      = $db->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
$successTx    = $db->query("SELECT COUNT(*) FROM transactions WHERE status = 'success'")->fetchColumn();
$pendingTx    = $db->query("SELECT COUNT(*) FROM transactions WHERE status = 'pending'")->fetchColumn();
$totalDeposit = $db->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='deposit' AND status='success'")->fetchColumn();
$totalTransfer= $db->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='transfer' AND status='success'")->fetchColumn();
$totalBalance = $db->query("SELECT COALESCE(SUM(balance),0) FROM wallets")->fetchColumn();
 
// 10 giao dịch gần nhất
$recentTx = $db->query("
    SELECT t.*, 
           ws.user_id AS sender_user_id,
           wr.user_id AS receiver_user_id,
           us.full_name AS sender_name,
           ur.full_name AS receiver_name
    FROM transactions t
    LEFT JOIN wallets ws ON t.sender_wallet_id   = ws.wallet_id
    LEFT JOIN wallets wr ON t.receiver_wallet_id = wr.wallet_id
    LEFT JOIN users us ON ws.user_id = us.user_id
    LEFT JOIN users ur ON wr.user_id = ur.user_id
    ORDER BY t.created_at DESC LIMIT 10
")->fetchAll();
 
// Thống kê giao dịch 7 ngày gần nhất
$txByDay = $db->query("
    SELECT DATE(created_at) as day, COUNT(*) as count, COALESCE(SUM(amount),0) as total
    FROM transactions
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND status = 'success'
    GROUP BY DATE(created_at)
    ORDER BY day ASC
")->fetchAll();
 
require_once __DIR__ . '/admin_navbar.php';
?>
 
<!-- Stat Cards -->
<div class="row g-4 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="card border-0 shadow h-100" style="background:linear-gradient(135deg,#667eea,#764ba2)">
  <div class="card-body text-white d-flex align-items-center gap-3">
    <i class="bi bi-people-fill fs-1 opacity-75"></i>
    <div>
      <div class="small opacity-75">Tổng người dùng</div>
      <div class="fw-bold fs-3"><?= number_format($totalUsers) ?></div>
    </div>
  </div>
</div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="background:linear-gradient(135deg,#11998e,#38ef7d)">
          <i class="bi bi-arrow-left-right"></i>
        </div>
        <div>
          <div class="text-muted small">Tổng giao dịch</div>
          <div class="fw-bold fs-4"><?= number_format($totalTx) ?></div>
          <div class="small text-warning"><i class="bi bi-clock me-1"></i><?= $pendingTx ?> đang xử lý</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center" style="background:linear-gradient(135deg,#f7971e,#ffd200)">
          <i class="bi bi-cash-coin"></i>
        </div>
        <div>
          <div class="text-muted small">Tổng tiền đã nạp</div>
          <div class="fw-bold fs-5"><?= formatMoney($totalDeposit) ?></div>
          <div class="small text-muted">Qua VNPAY QR</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body d-flex align-items-center gap-3">
        <div class="bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center" style="background:linear-gradient(135deg,#4facfe,#00f2fe)">
          <i class="bi bi-wallet2"></i>
        </div>
        <div>
          <div class="text-muted small">Tổng số dư hệ thống</div>
          <div class="fw-bold fs-5"><?= formatMoney($totalBalance) ?></div>
          <div class="small text-muted">Toàn bộ các ví</div>
        </div>
      </div>
    </div>
  </div>
</div>
 
<div class="row g-4">
  <!-- Giao dịch gần nhất -->
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Giao dịch gần nhất</h6>
        <a href="/BTL-MNM/admin/transactions.php" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
          <thead class="table-light">
            <tr>
              <th class="ps-3">Mã GD</th>
              <th>Loại</th>
              <th>Người gửi</th>
              <th>Người nhận</th>
              <th>Số tiền</th>
              <th>Trạng thái</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentTx as $tx): ?>
            <tr>
              <td class="ps-3"><code class="text-primary"><?= substr($tx['transaction_id'], 0, 10) ?>...</code></td>
              <td><?= txnTypeBadge($tx['type']) ?></td>
              <td><?= htmlspecialchars($tx['sender_name']   ?? '—') ?></td>
              <td><?= htmlspecialchars($tx['receiver_name'] ?? '—') ?></td>
              <td class="fw-bold"><?= formatMoney($tx['amount']) ?></td>
              <td><?= statusBadge($tx['status']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
 
  <!-- Thống kê nhanh -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-pie-chart me-2 text-warning"></i>Tỉ lệ giao dịch</h6>
      </div>
      <div class="card-body">
        <?php
        $types = [
          ['label'=>'Nạp tiền',   'type'=>'deposit',  'color'=>'success'],
          ['label'=>'Chuyển tiền','type'=>'transfer',  'color'=>'primary'],
          ['label'=>'Rút tiền',   'type'=>'withdraw',  'color'=>'danger'],
          ['label'=>'Thanh toán', 'type'=>'payment',   'color'=>'warning'],
        ];
        foreach ($types as $t):
          $cnt  = $db->prepare("SELECT COUNT(*) FROM transactions WHERE type = ?");
          $cnt->execute([$t['type']]);
          $count = $cnt->fetchColumn();
          $pct   = $totalTx > 0 ? round($count / $totalTx * 100) : 0;
        ?>
        <div class="mb-3">
          <div class="d-flex justify-content-between small mb-1">
            <span><?= $t['label'] ?></span>
            <span class="fw-bold"><?= $count ?> (<?= $pct ?>%)</span>
          </div>
          <div class="progress" style="height:8px">
            <div class="progress-bar bg-<?= $t['color'] ?>" style="width:<?= $pct ?>%"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
 
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-people me-2 text-danger"></i>Tình trạng tài khoản</h6>
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="small">Đang hoạt động</span>
          <span class="badge bg-success fs-6"><?= $activeUsers ?></span>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="small">Bị khóa</span>
          <span class="badge bg-danger fs-6"><?= $lockedUsers ?></span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <span class="small">GD thành công</span>
          <span class="badge bg-primary fs-6"><?= $successTx ?></span>
        </div>
      </div>
    </div>
  </div>
</div>
