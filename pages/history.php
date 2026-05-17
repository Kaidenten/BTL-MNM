<?php
$pageTitle = 'Lịch sử giao dịch';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php'; // ← THÊM DÒNG NÀY

$db = getDB();

$filterType   = $_GET['type']   ?? '';
$filterStatus = $_GET['status'] ?? '';
$filterFrom   = $_GET['from']   ?? '';
$filterTo     = $_GET['to']     ?? '';

$where  = "WHERE (ws.user_id = ? OR wr.user_id = ?)";
$params = [$_SESSION['user_id'], $_SESSION['user_id']];

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

// SỬA query — thêm t.bank_info
$stmt = $db->prepare("
    SELECT t.*,
           t.bank_info,
           us.full_name AS sender_name,
           ur.full_name AS receiver_name
    FROM transactions t
    LEFT JOIN wallets ws ON t.sender_wallet_id   = ws.wallet_id
    LEFT JOIN wallets wr ON t.receiver_wallet_id = wr.wallet_id
    LEFT JOIN users us   ON ws.user_id = us.user_id
    LEFT JOIN users ur   ON wr.user_id = ur.user_id
    $where
    ORDER BY t.created_at DESC
    LIMIT 20
");
$stmt->execute($params);
$transactions = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
 
<main class="py-4">
<div class="container">
 
  <div class="d-flex align-items-center gap-3 mb-4">
    <a href="/BTL-MNM/pages/dashboard.php" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
    <div>
      <h5 class="mb-0 fw-bold">Lịch sử giao dịch</h5>
      <small class="text-muted">Tất cả giao dịch của bạn</small>
    </div>
  </div>
 
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
      <form method="GET" class="row g-2 align-items-end">
        <div class="col-sm-3">
          <label class="form-label small fw-semibold mb-1">Loại giao dịch</label>
          <select name="type" class="form-select form-select-sm">
            <option value="">Tất cả</option>
            <option value="deposit"  <?= $filterType==='deposit'  ? 'selected':'' ?>>Nạp tiền</option>
            <option value="withdraw" <?= $filterType==='withdraw' ? 'selected':'' ?>>Rút tiền</option>
            <option value="transfer" <?= $filterType==='transfer' ? 'selected':'' ?>>Chuyển tiền</option>
            <option value="payment"  <?= $filterType==='payment'  ? 'selected':'' ?>>Thanh toán</option>
          </select>
        </div>
        <div class="col-sm-3">
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
        <div class="col-sm-2 d-flex gap-2">
          <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-funnel me-1"></i>Lọc</button>
          <a href="/BTL-MNM/pages/history.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i></a>
        </div>
      </form>
    </div>
  </div>
 
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
      <span class="fw-bold"><i class="bi bi-list-ul me-2 text-primary"></i>Danh sách giao dịch</span>
      <small class="text-muted"><?= count($transactions) ?> giao dịch</small>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4">Mã GD</th>
            <th>Loại</th>
            <th>Nội dung</th>
            <th>Số tiền</th>
            <th>Trạng thái</th>
            <th>Đối tác</th> <th>Thời gian</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($transactions as $tx): ?>
          <tr>
            <td class="ps-4"><code class="text-primary"><?= htmlspecialchars($tx['transaction_id']) ?></code></td>
            <td><?= txnTypeBadge($tx['type']) ?></td>
            <td class="text-muted small"
    title="<?= htmlspecialchars($tx['message'] ?? '') ?>"
    style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
  <?= $tx['message'] ? htmlspecialchars($tx['message']) : '<span class="text-muted fst-italic">Không có lời nhắn</span>' ?>
</td>
            
            <?php 
              $isReceived = ($tx['type'] === 'deposit' || ($tx['type'] === 'transfer' && $tx['receiver_name'] === ($_SESSION['full_name'] ?? '')));
            ?>
            <td class="fw-bold <?= $isReceived ? 'text-success' : 'text-danger' ?>">
              <?= $isReceived ? '+' : '-' ?><?= formatMoney($tx['amount']) ?>
            </td>
            
            <td><?= statusBadge($tx['status']) ?></td>
            

 <td class="text-muted small">
  <?php if ($tx['type'] === 'transfer'): ?>
    <?php $isSender = $tx['sender_name'] === ($_SESSION['full_name'] ?? ''); ?>
    <?php if ($isSender): ?>
      <?php if ($tx['receiver_name']): ?>
        <span class="badge bg-primary-subtle text-primary">
          <i class="bi bi-wallet2 me-1"></i><?= htmlspecialchars($tx['receiver_name']) ?>
        </span>
      <?php else: ?>
<span style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block;vertical-align:middle"
      title="<?= htmlspecialchars($tx['bank_info'] ?? '') ?>">
  <i class="bi bi-bank me-1 text-warning"></i>
  <?= !empty($tx['bank_info']) ? htmlspecialchars($tx['bank_info']) : '—' ?>
</span>
      <?php endif; ?>
    <?php else: ?>
      <span class="badge bg-success-subtle text-success">
        <i class="bi bi-arrow-down me-1"></i>Từ: <?= htmlspecialchars($tx['sender_name'] ?? '—') ?>
      </span>
    <?php endif; ?>
  <?php else: ?>
    —
  <?php endif; ?>
</td>

            <td class="text-muted small"><?= $tx['created_at'] ?></td>
            <td>
             <button class="btn btn-sm btn-outline-secondary"
        data-bs-toggle="modal" data-bs-target="#txModal"
        data-id="<?= htmlspecialchars($tx['transaction_id']) ?>"
        data-msg="<?= htmlspecialchars($tx['message'] ?? '') ?>"
        data-amount="<?= formatMoney($tx['amount']) ?>"
        data-type="<?= htmlspecialchars($tx['type']) ?>"
        data-status="<?= htmlspecialchars($tx['status']) ?>"
        data-date="<?= htmlspecialchars($tx['created_at']) ?>">
  <i class="bi bi-eye"></i>
</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
      <small class="text-muted">Hiển thị 1–<?= count($transactions) ?> / <?= count($transactions) ?></small>
      <nav><ul class="pagination pagination-sm mb-0">
        <li class="page-item disabled"><a class="page-link" href="#">«</a></li>
        <li class="page-item active"><a class="page-link" href="#">1</a></li>
        <li class="page-item disabled"><a class="page-link" href="#">»</a></li>
      </ul></nav>
    </div>
  </div>
 
</div>
</main>
 
<div class="modal fade" id="txModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0">
        <h6 class="modal-title fw-bold"><i class="bi bi-receipt me-2"></i>Chi tiết giao dịch</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <table class="table table-borderless small">
          <tr><td class="text-muted fw-semibold" width="40%">Mã GD</td><td id="md-id" class="fw-bold text-primary"></td></tr>
          <tr><td class="text-muted fw-semibold">Loại</td><td id="md-type"></td></tr>
          <tr><td class="text-muted fw-semibold">Nội dung</td><td id="md-msg"></td></tr>
          <tr><td class="text-muted fw-semibold">Số tiền</td><td id="md-amount" class="fw-bold fs-5"></td></tr>
          <tr><td class="text-muted fw-semibold">Trạng thái</td><td id="md-status"></td></tr>
          <tr><td class="text-muted fw-semibold">Thời gian</td><td id="md-date"></td></tr>
        </table>
      </div>
    </div>
  </div>
</div>
 
<script>
document.getElementById('txModal').addEventListener('show.bs.modal', function (e) {
  const btn = e.relatedTarget;

  const typeMap = {
    'deposit':  '<span class="badge bg-success"><i class="bi bi-arrow-down-circle me-1"></i>Nạp tiền</span>',
    'withdraw': '<span class="badge bg-danger"><i class="bi bi-arrow-up-circle me-1"></i>Rút tiền</span>',
    'transfer': '<span class="badge bg-primary"><i class="bi bi-send me-1"></i>Chuyển tiền</span>',
    'payment':  '<span class="badge bg-warning text-dark"><i class="bi bi-receipt me-1"></i>Thanh toán</span>',
  };

  const statusMap = {
    'success':   '<span class="badge bg-success">Thành công</span>',
    'pending':   '<span class="badge bg-warning text-dark">Đang xử lý</span>',
    'failed':    '<span class="badge bg-danger">Thất bại</span>',
    'cancelled': '<span class="badge bg-secondary">Đã hủy</span>',
  };

  document.getElementById('md-id').textContent      = btn.dataset.id;
  document.getElementById('md-msg').textContent     = btn.dataset.msg;
  document.getElementById('md-amount').textContent  = btn.dataset.amount;
  document.getElementById('md-date').textContent    = btn.dataset.date;
  document.getElementById('md-type').innerHTML      = typeMap[btn.dataset.type]   || btn.dataset.type;
  document.getElementById('md-status').innerHTML    = statusMap[btn.dataset.status] || btn.dataset.status;
});
</script>
 
<?php require_once __DIR__ . '/../includes/footer.php'; ?>