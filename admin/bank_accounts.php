<?php
$pageTitle = 'Duyệt tài khoản ngân hàng';
require_once __DIR__ . '/admin_auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$db = getDB();

// Xử lý duyệt / từ chối
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bankAccountId = trim($_POST['bank_account_id'] ?? '');
    $action        = trim($_POST['action']          ?? '');

    if (!empty($bankAccountId) && in_array($action, ['approve', 'reject'])) {

        if ($action === 'approve') {
            // Duyệt TK
            $db->prepare("UPDATE bank_accounts SET is_verified = TRUE WHERE bank_account_id = ?")
               ->execute([$bankAccountId]);

            // Lấy thông tin để gửi thông báo
            $stmt = $db->prepare("SELECT ba.*, u.user_id FROM bank_accounts ba JOIN users u ON ba.user_id = u.user_id WHERE ba.bank_account_id = ?");
            $stmt->execute([$bankAccountId]);
            $ba = $stmt->fetch();

            if ($ba) {
                $db->prepare("
                    INSERT INTO notifications (notification_id, user_id, title, content, type)
                    VALUES (UUID(), ?, 'Tài khoản ngân hàng đã được xác thực', ?, 'system')
                ")->execute([$ba['user_id'],
                    'Tài khoản ' . $ba['bank_name'] . ' - ' . $ba['account_number'] . ' đã được xác thực thành công. Bạn có thể sử dụng để rút tiền và chuyển tiền.'
                ]);
            }

            setFlash('success', 'Đã duyệt tài khoản ngân hàng thành công!');

        } else {
            // Từ chối → Xóa khỏi DB
            $stmt = $db->prepare("SELECT ba.*, u.user_id FROM bank_accounts ba JOIN users u ON ba.user_id = u.user_id WHERE ba.bank_account_id = ?");
            $stmt->execute([$bankAccountId]);
            $ba = $stmt->fetch();

            if ($ba) {
                $db->prepare("DELETE FROM bank_accounts WHERE bank_account_id = ?")
                   ->execute([$bankAccountId]);

                $db->prepare("
                    INSERT INTO notifications (notification_id, user_id, title, content, type)
                    VALUES (UUID(), ?, 'Yêu cầu liên kết ngân hàng bị từ chối', ?, 'system')
                ")->execute([$ba['user_id'],
                    'Tài khoản ' . $ba['bank_name'] . ' - ' . $ba['account_number'] . ' đã bị từ chối. Vui lòng kiểm tra lại thông tin và thử lại.'
                ]);
            }

            setFlash('success', 'Đã từ chối yêu cầu liên kết ngân hàng.');
        }
    }

    header('Location: /BTL-MNM/admin/bank_accounts.php');
    exit();
}

// Lấy danh sách TK chờ duyệt
$pendingAccounts = $db->query("
    SELECT ba.*, u.full_name, u.email, u.phone_number
    FROM bank_accounts ba
    JOIN users u ON ba.user_id = u.user_id
    WHERE ba.is_verified = FALSE
    ORDER BY ba.created_at ASC
")->fetchAll();

// Lấy danh sách TK đã duyệt
$verifiedAccounts = $db->query("
    SELECT ba.*, u.full_name, u.email
    FROM bank_accounts ba
    JOIN users u ON ba.user_id = u.user_id
    WHERE ba.is_verified = TRUE
    ORDER BY ba.created_at DESC
    LIMIT 30
")->fetchAll();

$success = flash('success');
require_once __DIR__ . '/admin_navbar.php';
?>

<?php if ($success): ?>
  <div class="alert alert-success alert-auto-dismiss mb-4"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<!-- Stat cards -->
<div class="row g-3 mb-4">
  <div class="col-sm-6">
    <div class="card border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3 py-3">
        <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center"
             style="width:50px;height:50px;font-size:1.3rem">
          <i class="bi bi-hourglass-split"></i>
        </div>
        <div>
          <div class="text-muted small">Chờ duyệt</div>
          <div class="fw-bold fs-4"><?= count($pendingAccounts) ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6">
    <div class="card border-0 shadow-sm">
      <div class="card-body d-flex align-items-center gap-3 py-3">
        <div class="bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center"
             style="width:50px;height:50px;font-size:1.3rem">
          <i class="bi bi-check-circle"></i>
        </div>
        <div>
          <div class="text-muted small">Đã xác thực</div>
          <div class="fw-bold fs-4"><?= count($verifiedAccounts) ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Danh sách chờ duyệt -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
    <h6 class="mb-0 fw-bold">
      <i class="bi bi-hourglass-split me-2 text-warning"></i>Chờ duyệt
      <?php if (count($pendingAccounts) > 0): ?>
        <span class="badge bg-warning text-dark ms-1"><?= count($pendingAccounts) ?></span>
      <?php endif; ?>
    </h6>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0 small">
      <thead class="table-light">
        <tr>
          <th class="ps-4">Người dùng</th>
          <th>Ngân hàng</th>
          <th>Số tài khoản</th>
          <th>Chủ tài khoản</th>
          <th>Ngày yêu cầu</th>
          <th class="text-center">Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pendingAccounts as $ba): ?>
        <tr>
          <td class="ps-4">
            <div class="fw-semibold"><?= htmlspecialchars($ba['full_name']) ?></div>
            <div class="text-muted"><?= htmlspecialchars($ba['phone_number']) ?></div>
          </td>
          <td>
            <span class="badge bg-primary-subtle text-primary px-2 py-1">
              <?= htmlspecialchars($ba['bank_name']) ?>
            </span>
          </td>
          <td class="fw-bold"><?= htmlspecialchars($ba['account_number']) ?></td>
          <td><?= htmlspecialchars($ba['account_holder']) ?></td>
          <td class="text-muted"><?= date('H:i d/m/Y', strtotime($ba['created_at'])) ?></td>
          <td class="text-center">
            <div class="d-flex gap-2 justify-content-center">
              <!-- Duyệt -->
              <form method="POST" class="d-inline"
                    onsubmit="return confirm('Duyệt tài khoản <?= htmlspecialchars($ba['bank_name']) ?> - <?= htmlspecialchars($ba['account_number']) ?>?')">
                <input type="hidden" name="bank_account_id" value="<?= $ba['bank_account_id'] ?>"/>
                <input type="hidden" name="action" value="approve"/>
                <button type="submit" class="btn btn-sm btn-success px-3">
                  <i class="bi bi-check-lg me-1"></i>Duyệt
                </button>
              </form>
              <!-- Từ chối -->
              <form method="POST" class="d-inline"
                    onsubmit="return confirm('Từ chối yêu cầu này?')">
                <input type="hidden" name="bank_account_id" value="<?= $ba['bank_account_id'] ?>"/>
                <input type="hidden" name="action" value="reject"/>
                <button type="submit" class="btn btn-sm btn-outline-danger px-3">
                  <i class="bi bi-x-lg me-1"></i>Từ chối
                </button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>

        <?php if (empty($pendingAccounts)): ?>
        <tr>
          <td colspan="6" class="text-center text-muted py-5">
            <i class="bi bi-check2-circle fs-2 d-block mb-2 text-success"></i>
            Không có yêu cầu nào đang chờ duyệt
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Danh sách đã duyệt -->
<div class="card border-0 shadow-sm">
  <div class="card-header bg-white py-3">
    <h6 class="mb-0 fw-bold"><i class="bi bi-check-circle me-2 text-success"></i>Đã xác thực gần đây</h6>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0 small">
      <thead class="table-light">
        <tr>
          <th class="ps-4">Người dùng</th>
          <th>Ngân hàng</th>
          <th>Số tài khoản</th>
          <th>Chủ tài khoản</th>
          <th>Mặc định</th>
          <th>Ngày xác thực</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($verifiedAccounts as $ba): ?>
        <tr>
          <td class="ps-4">
            <div class="fw-semibold"><?= htmlspecialchars($ba['full_name']) ?></div>
            <div class="text-muted"><?= htmlspecialchars($ba['email']) ?></div>
          </td>
          <td>
            <span class="badge bg-primary-subtle text-primary px-2 py-1">
              <?= htmlspecialchars($ba['bank_name']) ?>
            </span>
          </td>
          <td class="fw-bold"><?= htmlspecialchars($ba['account_number']) ?></td>
          <td><?= htmlspecialchars($ba['account_holder']) ?></td>
          <td>
            <?php if ($ba['is_default']): ?>
              <span class="badge bg-primary">Mặc định</span>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
          <td class="text-muted"><?= date('H:i d/m/Y', strtotime($ba['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>

        <?php if (empty($verifiedAccounts)): ?>
        <tr>
          <td colspan="6" class="text-center text-muted py-4">Chưa có tài khoản nào được xác thực</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>