<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/admin_auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$db = getDB();

$totalUsers   = $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$activeUsers  = $db->query("SELECT COUNT(*) FROM users WHERE status = 'active' AND role = 'user'")->fetchColumn();
$lockedUsers  = $db->query("SELECT COUNT(*) FROM users WHERE status = 'locked'")->fetchColumn();
$totalTx      = $db->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
$successTx    = $db->query("SELECT COUNT(*) FROM transactions WHERE status = 'success'")->fetchColumn();
$pendingTx    = $db->query("SELECT COUNT(*) FROM transactions WHERE status = 'pending'")->fetchColumn();
$totalDeposit = $db->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='deposit' AND status='success'")->fetchColumn();
$totalBalance = $db->query("SELECT COALESCE(SUM(balance),0) FROM wallets")->fetchColumn();
$pendingBanks = $db->query("SELECT COUNT(*) FROM bank_accounts WHERE is_verified = FALSE")->fetchColumn();

// 10 giao dịch gần nhất
$recentTx = $db->query("
    SELECT t.*,
           us.full_name AS sender_name,
           ur.full_name AS receiver_name
    FROM transactions t
    LEFT JOIN wallets ws ON t.sender_wallet_id   = ws.wallet_id
    LEFT JOIN wallets wr ON t.receiver_wallet_id = wr.wallet_id
    LEFT JOIN users us ON ws.user_id = us.user_id
    LEFT JOIN users ur ON wr.user_id = ur.user_id
    ORDER BY t.created_at DESC LIMIT 10
")->fetchAll();

// Tỉ lệ loại giao dịch
$types = [
    ['label'=>'Nạp tiền',    'type'=>'deposit',  'color'=>'success'],
    ['label'=>'Chuyển tiền', 'type'=>'transfer',  'color'=>'primary'],
    ['label'=>'Rút tiền',    'type'=>'withdraw',  'color'=>'danger'],
    ['label'=>'Thanh toán',  'type'=>'payment',   'color'=>'warning'],
];

require_once __DIR__ . '/admin_navbar.php';
?>

<!-- Stat Cards -->
<div class="row g-4 mb-4">

  <div class="col-sm-6 col-xl-3">
    <div class="card border-0 shadow h-100 text-white"
         style="background:linear-gradient(135deg,#667eea,#764ba2)">
      <div class="card-body d-flex align-items-center gap-3">
        <i class="bi bi-people-fill fs-1 opacity-75"></i>
        <div>
          <div class="small opacity-75">Tổng người dùng</div>
          <div class="fw-bold fs-3"><?= number_format($totalUsers) ?></div>
          <div class="small opacity-75"><?= $activeUsers ?> hoạt động · <?= $lockedUsers ?> bị khóa</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card border-0 shadow h-100 text-white"
         style="background:linear-gradient(135deg,#11998e,#38ef7d)">
      <div class="card-body d-flex align-items-center gap-3">
        <i class="bi bi-arrow-left-right fs-1 opacity-75"></i>
        <div>
          <div class="small opacity-75">Tổng giao dịch</div>
          <div class="fw-bold fs-3"><?= number_format($totalTx) ?></div>
          <div class="small opacity-75"><?= $successTx ?> thành công · <?= $pendingTx ?> đang xử lý</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card border-0 shadow h-100 text-white"
         style="background:linear-gradient(135deg,#f7971e,#ffd200)">
      <div class="card-body d-flex align-items-center gap-3">
        <i class="bi bi-cash-coin fs-1 opacity-75"></i>
        <div>
          <div class="small opacity-75">Tổng tiền đã nạp</div>
          <div class="fw-bold fs-4"><?= formatMoney($totalDeposit) ?></div>
          <div class="small opacity-75">Tất cả giao dịch nạp</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="card border-0 shadow h-100 text-white"
         style="background:linear-gradient(135deg,#4facfe,#00f2fe)">
      <div class="card-body d-flex align-items-center gap-3">
        <i class="bi bi-wallet2 fs-1 opacity-75"></i>
        <div>
          <div class="small opacity-75">Tổng số dư hệ thống</div>
          <div class="fw-bold fs-4"><?= formatMoney($totalBalance) ?></div>
          <div class="small opacity-75">Toàn bộ các ví</div>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- Cảnh báo chờ duyệt ngân hàng -->
<?php if ($pendingBanks > 0): ?>
<div class="alert alert-warning d-flex align-items-center gap-3 mb-4">
  <i class="bi bi-hourglass-split fs-4"></i>
  <div class="flex-grow-1">
    <strong><?= $pendingBanks ?> tài khoản ngân hàng</strong> đang chờ xét duyệt.
  </div>
  <a href="/BTL-MNM/admin/bank_accounts.php" class="btn btn-warning btn-sm">Duyệt ngay</a>
</div>
<?php endif; ?>

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
              <td class="ps-3">
                <code class="text-primary"><?= substr($tx['transaction_id'], 0, 10) ?>...</code>
              </td>
              <td><?= txnTypeBadge($tx['type']) ?></td>
              <td><?= htmlspecialchars($tx['sender_name'] ?? '—') ?></td>
              <td>
                <?php if ($tx['receiver_name']): ?>
                  <i class="bi bi-wallet2 me-1 text-primary"></i><?= htmlspecialchars($tx['receiver_name']) ?>
                <?php elseif (!empty($tx['bank_info'])): ?>
                  <i class="bi bi-bank me-1 text-warning"></i><?= htmlspecialchars($tx['bank_info']) ?>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>
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

    <!-- Tỉ lệ giao dịch -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-pie-chart me-2 text-warning"></i>Tỉ lệ giao dịch</h6>
      </div>
      <div class="card-body">
        <?php foreach ($types as $t):
          $stmt = $db->prepare("SELECT COUNT(*) FROM transactions WHERE type = ?");
          $stmt->execute([$t['type']]);
          $count = $stmt->fetchColumn();
          $pct   = $totalTx > 0 ? round($count / $totalTx * 100) : 0;
        ?>
        <div class="mb-3">
          <div class="d-flex justify-content-between small mb-1">
            <span><?= $t['label'] ?></span>
            <span class="fw-bold"><?= number_format($count) ?> (<?= $pct ?>%)</span>
          </div>
          <div class="progress" style="height:8px">
            <div class="progress-bar bg-<?= $t['color'] ?>" style="width:<?= $pct ?>%"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Tình trạng tài khoản -->
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
        <div class="d-flex justify-content-between align-items-center mb-3">
          <span class="small">GD thành công</span>
          <span class="badge bg-primary fs-6"><?= $successTx ?></span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <span class="small">NH chờ duyệt</span>
          <span class="badge bg-warning text-dark fs-6"><?= $pendingBanks ?></span>
        </div>
      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>