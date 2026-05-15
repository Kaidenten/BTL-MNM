<?php
$pageTitle = 'Quản lý giao dịch';
require_once __DIR__ . '/admin_auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
 
$db = getDB();
 
// Bộ lọc
$filterType   = $_GET['type']   ?? '';
$filterStatus = $_GET['status'] ?? '';
$filterFrom   = $_GET['from']   ?? '';
$filterTo     = $_GET['to']     ?? '';
$search       = trim($_GET['search'] ?? '');
 
$where  = "WHERE 1=1";
$params = [];
 
if (!empty($filterType)) {
    $where   .= " AND t.type = ?";
    $params[] = $filterType;
}
if (!empty($filterStatus)) {
    $where   .= " AND t.status = ?";
    $params[] = $filterStatus;
}
if (!empty($filterFrom)) {
    $where   .= " AND DATE(t.created_at) >= ?";
    $params[] = $filterFrom;
}
if (!empty($filterTo)) {
    $where   .= " AND DATE(t.created_at) <= ?";
    $params[] = $filterTo;
}
if (!empty($search)) {
    $where   .= " AND (t.transaction_id LIKE ? OR t.reference_code LIKE ? OR us.full_name LIKE ? OR ur.full_name LIKE ?)";
    $params   = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
}
 
// Phân trang
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset  = ($page - 1) * $perPage;
 
$baseQuery = "
    FROM transactions t
    LEFT JOIN wallets ws ON t.sender_wallet_id   = ws.wallet_id
    LEFT JOIN wallets wr ON t.receiver_wallet_id = wr.wallet_id
    LEFT JOIN users us   ON ws.user_id = us.user_id
    LEFT JOIN users ur   ON wr.user_id = ur.user_id
    $where
";
 
$stmtCount = $db->prepare("SELECT COUNT(*) $baseQuery");
$stmtCount->execute($params);
$totalTx    = $stmtCount->fetchColumn();
$totalPages = ceil($totalTx / $perPage);
 
$stmtTx = $db->prepare("
    SELECT t.*,
           us.full_name AS sender_name,
           ur.full_name AS receiver_name
    $baseQuery
    ORDER BY t.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmtTx->execute($params);
$transactions = $stmtTx->fetchAll();
 
// Tổng tiền theo loại (dùng cho summary cards)
$summary = $db->query("
    SELECT type,
           COUNT(*) as count,
           COALESCE(SUM(CASE WHEN status='success' THEN amount ELSE 0 END), 0) as total
    FROM transactions GROUP BY type
")->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
 
require_once __DIR__ . '/admin_navbar.php';
?>
 
<!-- Summary Cards -->
<div class="row g-3 mb-4">
  <?php
  $cards = [
    ['type'=>'deposit',  'label'=>'Tổng nạp',    'color'=>'success', 'icon'=>'bi-arrow-down-circle'],
    ['type'=>'withdraw', 'label'=>'Tổng rút',     'color'=>'danger',  'icon'=>'bi-arrow-up-circle'],
    ['type'=>'transfer', 'label'=>'Tổng chuyển',  'color'=>'primary', 'icon'=>'bi-send'],
    ['type'=>'payment',  'label'=>'Tổng TT DV',   'color'=>'warning', 'icon'=>'bi-receipt'],
  ];
  foreach ($cards as $c):
    $data = $summary[$c['type']] ?? ['count'=>0,'total'=>0];
  ?>
  <div class="col-sm-6 col-xl-3">
    <div class="card border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3 py-3">
        <div class="bg-<?= $c['color'] ?>-subtle text-<?= $c['color'] ?> rounded-circle d-flex align-items-center justify-content-center"
             style="width:46px;height:46px;font-size:1.2rem">
          <i class="bi <?= $c['icon'] ?>"></i>
        </div>
        <div>
          <div class="text-muted small"><?= $c['label'] ?></div>
          <div class="fw-bold"><?= formatMoney($data['total']) ?></div>
          <div class="text-muted" style="font-size:.75rem"><?= number_format($data['count']) ?> giao dịch</div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
 
<!-- Bộ lọc -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body p-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-sm-3">
        <label class="form-label small fw-semibold mb-1">Tìm kiếm</label>
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Mã GD, tên người dùng..." value="<?= htmlspecialchars($search) ?>"/>
      </div>
      <div class="col-sm-2">
        <label class="form-label small fw-semibold mb-1">Loại</label>
        <select name="type" class="form-select form-select-sm">
          <option value="">Tất cả</option>
          <option value="deposit"  <?= $filterType==='deposit'  ? 'selected':'' ?>>Nạp tiền</option>
          <option value="withdraw" <?= $filterType==='withdraw' ? 'selected':'' ?>>Rút tiền</option>
          <option value="transfer" <?= $filterType==='transfer' ? 'selected':'' ?>>Chuyển tiền</option>
          <option value="payment"  <?= $filterType==='payment'  ? 'selected':'' ?>>Thanh toán</option>
        </select>
      </div>
      <div class="col-sm-2">
        <label class="form-label small fw-semibold mb-1">Trạng thái</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">Tất cả</option>
          <option value="success"   <?= $filterStatus==='success'   ? 'selected':'' ?>>Thành công</option>
          <option value="pending"   <?= $filterStatus==='pending'   ? 'selected':'' ?>>Đang xử lý</option>
          <option value="failed"    <?= $filterStatus==='failed'    ? 'selected':'' ?>>Thất bại</option>
          <option value="cancelled" <?= $filterStatus==='cancelled' ? 'selected':'' ?>>Đã hủy</option>
        </select>
      </div>
      <div class="col-sm-2">
        <label class="form-label small fw-semibold mb-1">Từ ngày</label>
        <input type="date" name="from" class="form-control form-control-sm" value="<?= htmlspecialchars($filterFrom) ?>"/>
      </div>
      <div class="col-sm-2">
        <label class="form-label small fw-semibold mb-1">Đến ngày</label>
        <input type="date" name="to" class="form-control form-control-sm" value="<?= htmlspecialchars($filterTo) ?>"/>
      </div>
      <div class="col-sm-1 d-flex gap-1">
        <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-funnel"></i></button>
        <a href="/BTL-MNM/admin/transactions.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i></a>
      </div>
    </form>
  </div>
</div>
 
<!-- Bảng giao dịch -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
    <h6 class="mb-0 fw-bold"><i class="bi bi-list-ul me-2 text-primary"></i>Danh sách giao dịch</h6>
    <small class="text-muted"><?= number_format($totalTx) ?> giao dịch</small>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0 small">
      <thead class="table-light">
        <tr>
          <th class="ps-4">Mã GD</th>
          <th>Loại</th>
          <th>Người gửi</th>
          <th>Người nhận</th>
          <th>Số tiền</th>
          <th>Nội dung</th>
          <th>Trạng thái</th>
          <th>Thời gian</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($transactions as $tx): ?>
        <tr>
          <td class="ps-4"><code class="text-primary small"><?= substr($tx['transaction_id'],0,12) ?>...</code></td>
          <td><?= txnTypeBadge($tx['type']) ?></td>
          <td><?= htmlspecialchars($tx['sender_name']   ?? '—') ?></td>
          <td><?= htmlspecialchars($tx['receiver_name'] ?? '—') ?></td>
          <td class="fw-bold"><?= formatMoney($tx['amount']) ?></td>
          <td class="text-muted" style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
            <?= htmlspecialchars($tx['message'] ?? '—') ?>
          </td>
          <td><?= statusBadge($tx['status']) ?></td>
          <td class="text-muted"><?= date('H:i d/m/Y', strtotime($tx['created_at'])) ?></td>
          <td>
            <button class="btn btn-sm btn-outline-secondary"
                    data-bs-toggle="modal" data-bs-target="#txModal"
                    data-id="<?=     htmlspecialchars($tx['transaction_id']) ?>"
                    data-type="<?=   $tx['type'] ?>"
                    data-sender="<?= htmlspecialchars($tx['sender_name']   ?? '—') ?>"
                    data-recv="<?=   htmlspecialchars($tx['receiver_name'] ?? '—') ?>"
                    data-amount="<?= formatMoney($tx['amount']) ?>"
                    data-fee="<?=    formatMoney($tx['fee'] ?? 0) ?>"
                    data-msg="<?=    htmlspecialchars($tx['message'] ?? '—') ?>"
                    data-status="<?= $tx['status'] ?>"
                    data-date="<?=   date('H:i d/m/Y', strtotime($tx['created_at'])) ?>">
              <i class="bi bi-eye"></i>
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
 
        <?php if (empty($transactions)): ?>
        <tr>
          <td colspan="9" class="text-center text-muted py-5">
            <i class="bi bi-inbox fs-2 d-block mb-2"></i>Không có giao dịch nào
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
 
  <!-- Phân trang -->
  <?php if ($totalPages > 1): ?>
  <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
    <small class="text-muted">Trang <?= $page ?> / <?= $totalPages ?></small>
    <nav><ul class="pagination pagination-sm mb-0">
      <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
        <a class="page-link" href="?page=<?= $page-1 ?>&type=<?= $filterType ?>&status=<?= $filterStatus ?>&from=<?= $filterFrom ?>&to=<?= $filterTo ?>&search=<?= urlencode($search) ?>">«</a>
      </li>
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <li class="page-item <?= $i === $page ? 'active' : '' ?>">
        <a class="page-link" href="?page=<?= $i ?>&type=<?= $filterType ?>&status=<?= $filterStatus ?>&from=<?= $filterFrom ?>&to=<?= $filterTo ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
      </li>
      <?php endfor; ?>
      <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
        <a class="page-link" href="?page=<?= $page+1 ?>&type=<?= $filterType ?>&status=<?= $filterStatus ?>&from=<?= $filterFrom ?>&to=<?= $filterTo ?>&search=<?= urlencode($search) ?>">»</a>
      </li>
    </ul></nav>
  </div>
  <?php endif; ?>
</div>
 
<!-- Modal chi tiết giao dịch -->
<div class="modal fade" id="txModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0">
        <h6 class="modal-title fw-bold"><i class="bi bi-receipt me-2"></i>Chi tiết giao dịch</h6>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body px-4">
        <table class="table table-borderless small">
          <tr><td class="text-muted fw-semibold" width="40%">Mã GD</td>      <td id="m-id"     class="fw-bold text-primary small"></td></tr>
          <tr><td class="text-muted fw-semibold">Loại</td>                   <td id="m-type"></td></tr>
          <tr><td class="text-muted fw-semibold">Người gửi</td>              <td id="m-sender"></td></tr>
          <tr><td class="text-muted fw-semibold">Người nhận</td>             <td id="m-recv"></td></tr>
          <tr><td class="text-muted fw-semibold">Số tiền</td>                <td id="m-amount" class="fw-bold fs-5"></td></tr>
          <tr><td class="text-muted fw-semibold">Phí</td>                    <td id="m-fee"></td></tr>
          <tr><td class="text-muted fw-semibold">Nội dung</td>               <td id="m-msg"></td></tr>
          <tr><td class="text-muted fw-semibold">Trạng thái</td>             <td id="m-status"></td></tr>
          <tr><td class="text-muted fw-semibold">Thời gian</td>              <td id="m-date"></td></tr>
        </table>
      </div>
    </div>
  </div>
</div>
 
<script>
document.getElementById('txModal').addEventListener('show.bs.modal', function (e) {
  const b = e.relatedTarget;
  document.getElementById('m-id').textContent     = b.dataset.id;
  document.getElementById('m-sender').textContent = b.dataset.sender;
  document.getElementById('m-recv').textContent   = b.dataset.recv;
  document.getElementById('m-amount').textContent = b.dataset.amount;
  document.getElementById('m-fee').textContent    = b.dataset.fee;
  document.getElementById('m-msg').textContent    = b.dataset.msg;
  document.getElementById('m-date').textContent   = b.dataset.date;
});
</script>
 
<?php require_once __DIR__ . '/admin_footer.php'; ?>
