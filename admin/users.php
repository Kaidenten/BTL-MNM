<?php
$pageTitle = 'Quản lý người dùng';
require_once __DIR__ . '/admin_auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
 
$db = getDB();
 
// Xử lý khóa / mở khóa tài khoản
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'] ?? '';
    $action = $_POST['action'] ?? '';
 
    if (!empty($userId) && in_array($action, ['lock', 'unlock'])) {
        $status = $action === 'lock' ? 'locked' : 'active';
        $db->prepare("UPDATE users SET status = ? WHERE user_id = ? AND role = 'user'")
           ->execute([$status, $userId]);
        $_SESSION['success_message'] = $action === 'lock' ? 'Đã khóa tài khoản.' : 'Đã mở khóa tài khoản.';
    }
    header('Location: /BTL-MNM/admin/users.php');
    exit();
}
 
// Tìm kiếm
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
 
$where  = "WHERE role = 'user'";
$params = [];
 
if (!empty($search)) {
    $where .= " AND (full_name LIKE ? OR email LIKE ? OR phone_number LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}
if (!empty($status)) {
    $where .= " AND status = ?";
    $params[] = $status;
}
 
// Phân trang
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;
 
$stmtCount = $db->prepare("SELECT COUNT(*) FROM users $where");
$stmtCount->execute($params);
$totalUsers = $stmtCount->fetchColumn();
$totalPages = ceil($totalUsers / $perPage);
 
$stmtUsers = $db->prepare("
    SELECT u.*, w.balance, w.status AS wallet_status
    FROM users u
    LEFT JOIN wallets w ON u.user_id = w.user_id
    $where
    ORDER BY u.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmtUsers->execute($params);
$users = $stmtUsers->fetchAll();
 
$success = flash('success');
require_once __DIR__ . '/admin_navbar.php';
?>
 
<?php if ($success): ?>
  <div class="alert alert-success alert-auto-dismiss mb-4"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
 
<!-- Tìm kiếm & lọc -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body p-3">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-sm-5">
        <label class="form-label small fw-semibold mb-1">Tìm kiếm</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input type="text" name="search" class="form-control" placeholder="Tên, email, SĐT..." value="<?= htmlspecialchars($search) ?>"/>
        </div>
      </div>
      <div class="col-sm-3">
        <label class="form-label small fw-semibold mb-1">Trạng thái</label>
        <select name="status" class="form-select">
          <option value="">Tất cả</option>
          <option value="active"  <?= $status === 'active'  ? 'selected' : '' ?>>Đang hoạt động</option>
          <option value="locked"  <?= $status === 'locked'  ? 'selected' : '' ?>>Bị khóa</option>
          <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Chờ kích hoạt</option>
        </select>
      </div>
      <div class="col-sm-2 d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i>Lọc</button>
        <a href="/BTL-MNM/admin/users.php" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
      </div>
    </form>
  </div>
</div>
 
<!-- Bảng người dùng -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
    <h6 class="mb-0 fw-bold"><i class="bi bi-people me-2 text-primary"></i>Danh sách người dùng</h6>
    <small class="text-muted"><?= number_format($totalUsers) ?> người dùng</small>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0 small">
      <thead class="table-light">
        <tr>
          <th class="ps-4">Người dùng</th>
          <th>Số điện thoại</th>
          <th>Số dư ví</th>
          <th>TK người dùng</th>
          <th>Ví</th>
          <th>Ngày đăng ký</th>
          <th class="text-center">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $user): ?>
        <tr>
          <td class="ps-4">
            <div class="d-flex align-items-center gap-2">
              <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['full_name']) ?>&background=0d6efd&color=fff&size=36"
                   class="rounded-circle" width="36" height="36" alt="avatar"/>
              <div>
                <div class="fw-semibold"><?= htmlspecialchars($user['full_name']) ?></div>
                <div class="text-muted"><?= htmlspecialchars($user['email']) ?></div>
              </div>
            </div>
          </td>
          <td><?= htmlspecialchars($user['phone_number']) ?></td>
          <td class="fw-bold text-success"><?= formatMoney($user['balance'] ?? 0) ?></td>
          <td>
            <?php if ($user['status'] === 'active'): ?>
              <span class="badge bg-success">Hoạt động</span>
            <?php elseif ($user['status'] === 'locked'): ?>
              <span class="badge bg-danger">Bị khóa</span>
            <?php else: ?>
              <span class="badge bg-secondary">Chờ kích hoạt</span>
            <?php endif; ?>
          </td>
          <td>
            <?php if (($user['wallet_status'] ?? '') === 'active'): ?>
              <span class="badge bg-success">Hoạt động</span>
            <?php else: ?>
              <span class="badge bg-danger">Bị khóa</span>
            <?php endif; ?>
          </td>
          <td class="text-muted"><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
          <td class="text-center">
            <div class="d-flex gap-1 justify-content-center">
              <!-- Xem chi tiết -->
              <button class="btn btn-sm btn-outline-primary"
                      data-bs-toggle="modal" data-bs-target="#userModal"
                      data-name="<?= htmlspecialchars($user['full_name']) ?>"
                      data-email="<?= htmlspecialchars($user['email']) ?>"
                      data-phone="<?= htmlspecialchars($user['phone_number']) ?>"
                      data-balance="<?= formatMoney($user['balance'] ?? 0) ?>"
                      data-status="<?= $user['status'] ?>"
                      data-date="<?= date('d/m/Y', strtotime($user['created_at'])) ?>">
                <i class="bi bi-eye"></i>
              </button>
 
              <!-- Khóa / Mở khóa -->
              <?php if ($user['status'] !== 'locked'): ?>
              <form method="POST" onsubmit="return confirm('Khóa tài khoản <?= htmlspecialchars($user['full_name']) ?>?')">
                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>"/>
                <input type="hidden" name="action"  value="lock"/>
                <button type="submit" class="btn btn-sm btn-outline-danger" title="Khóa tài khoản">
                  <i class="bi bi-lock"></i>
                </button>
              </form>
              <?php else: ?>
              <form method="POST">
                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>"/>
                <input type="hidden" name="action"  value="unlock"/>
                <button type="submit" class="btn btn-sm btn-outline-success" title="Mở khóa">
                  <i class="bi bi-unlock"></i>
                </button>
              </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
 
        <?php if (empty($users)): ?>
        <tr>
          <td colspan="7" class="text-center text-muted py-5">
            <i class="bi bi-inbox fs-2 d-block mb-2"></i>Không tìm thấy người dùng nào
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
 
  <!-- Phân trang -->
  <?php if ($totalPages > 1): ?>
  <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
    <small class="text-muted">
      Trang <?= $page ?> / <?= $totalPages ?> — <?= number_format($totalUsers) ?> người dùng
    </small>
    <nav><ul class="pagination pagination-sm mb-0">
      <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
        <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>">«</a>
      </li>
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <li class="page-item <?= $i === $page ? 'active' : '' ?>">
        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>"><?= $i ?></a>
      </li>
      <?php endfor; ?>
      <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
        <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>">»</a>
      </li>
    </ul></nav>
  </div>
  <?php endif; ?>
</div>
 
<!-- Modal xem chi tiết user -->
<div class="modal fade" id="userModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0">
        <h6 class="modal-title fw-bold"><i class="bi bi-person me-2"></i>Chi tiết người dùng</h6>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body px-4">
        <table class="table table-borderless small">
          <tr><td class="text-muted fw-semibold" width="40%">Họ tên</td>      <td id="md-name"   class="fw-bold"></td></tr>
          <tr><td class="text-muted fw-semibold">Email</td>                   <td id="md-email"></td></tr>
          <tr><td class="text-muted fw-semibold">Số điện thoại</td>           <td id="md-phone"></td></tr>
          <tr><td class="text-muted fw-semibold">Số dư ví</td>                <td id="md-balance" class="fw-bold text-success"></td></tr>
          <tr><td class="text-muted fw-semibold">Trạng thái</td>              <td id="md-status"></td></tr>
          <tr><td class="text-muted fw-semibold">Ngày đăng ký</td>            <td id="md-date"></td></tr>
        </table>
      </div>
    </div>
  </div>
</div>
 
<script>
document.getElementById('userModal').addEventListener('show.bs.modal', function (e) {
  const btn = e.relatedTarget;
  document.getElementById('md-name').textContent    = btn.dataset.name;
  document.getElementById('md-email').textContent   = btn.dataset.email;
  document.getElementById('md-phone').textContent   = btn.dataset.phone;
  document.getElementById('md-balance').textContent = btn.dataset.balance;
  document.getElementById('md-date').textContent    = btn.dataset.date;
  const s = btn.dataset.status;
  document.getElementById('md-status').innerHTML =
    s === 'active'  ? '<span class="badge bg-success">Hoạt động</span>' :
    s === 'locked'  ? '<span class="badge bg-danger">Bị khóa</span>'    :
                      '<span class="badge bg-secondary">Chờ kích hoạt</span>';
});
</script>
 
<?php require_once __DIR__ . '/admin_footer.php'; ?>
