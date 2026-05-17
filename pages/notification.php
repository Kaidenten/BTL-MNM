<?php
$pageTitle = 'Thông báo';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/database.php';

$db = getDB();

// Xử lý đánh dấu tất cả đã đọc
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['read_all'])) {
    $db->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?")
       ->execute([$_SESSION['user_id']]);
    header('Location: /BTL-MNM/pages/notification.php');
    exit();
}

// Lấy số lượng thông báo chưa đọc
$stmtCount = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE");
$stmtCount->execute([$_SESSION['user_id']]);
$unreadCount = $stmtCount->fetchColumn();

$typeConfig = [
    'transaction' => ['icon'=>'bi-arrow-left-right', 'bg'=>'bg-primary-subtle',  'text'=>'text-primary'],
    'security'    => ['icon'=>'bi-shield-exclamation','bg'=>'bg-warning-subtle',  'text'=>'text-warning'],
    'system'      => ['icon'=>'bi-info-circle',       'bg'=>'bg-success-subtle',  'text'=>'text-success'],
    'promotion'   => ['icon'=>'bi-gift',              'bg'=>'bg-danger-subtle',   'text'=>'text-danger'],
];

// Thêm filter vào query nếu có
$filterType = $_GET['filter'] ?? '';
$where = "WHERE user_id = ?";
$params = [$_SESSION['user_id']];

if ($filterType === 'unread') {
    $where .= " AND is_read = FALSE";
} elseif (in_array($filterType, ['transaction','security','system','promotion'])) {
    $where .= " AND type = ?";
    $params[] = $filterType;
}

// Lấy danh sách thông báo
$stmt = $db->prepare("
    SELECT * FROM notifications $where
    ORDER BY created_at DESC LIMIT 50
");
$stmt->execute($params);
$notifications = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>
 
<main class="py-4">
<div class="container">
  <div class="row justify-content-center">
    <div class="col-lg-7">
 
      <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
          <a href="/BTL-MNM/pages/dashboard.php" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i></a>
          <div>
            <h5 class="mb-0 fw-bold">
              Thông báo
              <span id="header-unread-badge" class="badge bg-danger ms-1" <?= $unreadCount == 0 ? 'style="display:none;"' : '' ?>>
                <?= $unreadCount ?>
              </span>
            </h5>
            <small class="text-muted"><?= count($notifications) ?> thông báo</small>
          </div>
        </div>
        
        <form id="form-mark-all" action="" method="POST" <?= $unreadCount == 0 ? 'style="display:none;"' : '' ?>>
          <input type="hidden" name="read_all" value="1">
          <button type="submit" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-check2-all me-1"></i>Đánh dấu tất cả đã đọc
          </button>
        </form>
      </div>
 
      <div class="d-flex gap-2 mb-4 flex-wrap">
        <a href="?filter=" class="btn btn-sm <?= !isset($_GET['filter']) || $_GET['filter']==='' ? 'btn-primary' : 'btn-outline-secondary' ?>">
          Tất cả
        </a>
        
        <?php
          // Xử lý class riêng cho nút Chưa đọc
          $isUnreadFilter = ($_GET['filter'] ?? '') === 'unread';
          if ($isUnreadFilter) {
              $unreadBtnClass = 'btn-primary'; // Đang ở tab chưa đọc thì hiện màu xanh active
          } else {
              // Nếu có tin mới thì Đỏ, hết tin mới thì Trở về màu viền nhạt bình thường
              $unreadBtnClass = $unreadCount > 0 ? 'btn-danger text-white' : 'btn-outline-secondary';
          }
          // Badge màu ngược lại để dễ nhìn trên nền đỏ
          $badgeClass = ($unreadCount > 0 && !$isUnreadFilter) ? 'bg-light text-danger' : 'bg-danger';
        ?>
        <a href="?filter=unread" id="filter-unread-btn" class="btn btn-sm <?= $unreadBtnClass ?>" style="transition: all 0.3s;">
          Chưa đọc 
          <span id="unread-badge" class="badge <?= $badgeClass ?> ms-1" <?= $unreadCount == 0 ? 'style="display:none;"' : '' ?>>
            <?= $unreadCount ?>
          </span>
        </a>
        
        <a href="?filter=transaction" class="btn btn-sm <?= ($_GET['filter']??'') === 'transaction' ? 'btn-primary' : 'btn-outline-secondary' ?>">
          Giao dịch
        </a>
        <a href="?filter=security" class="btn btn-sm <?= ($_GET['filter']??'') === 'security' ? 'btn-primary' : 'btn-outline-secondary' ?>">
          Bảo mật
        </a>
        <a href="?filter=promotion" class="btn btn-sm <?= ($_GET['filter']??'') === 'promotion' ? 'btn-primary' : 'btn-outline-secondary' ?>">
          Khuyến mãi
        </a>
      </div>
 
      <div class="card border-0 shadow-sm">
        <div class="list-group list-group-flush">
          <?php foreach ($notifications as $notif):
            $cfg = $typeConfig[$notif['type']] ?? $typeConfig['system'];
          ?>
          <div class="list-group-item px-4 py-3 d-flex gap-3 align-items-start <?= !$notif['is_read'] ? 'bg-primary bg-opacity-5' : '' ?>
               border-start border-4 <?= !$notif['is_read'] ? 'border-primary' : 'border-transparent' ?>"
               style="cursor:pointer"
               onclick="markRead('<?= $notif['notification_id'] ?>', this)">
 
            <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center <?= $cfg['bg'] ?> <?= $cfg['text'] ?>"
                 style="width:44px;height:44px;font-size:1.1rem">
              <i class="bi <?= $cfg['icon'] ?>"></i>
            </div>
 
            <div class="flex-grow-1 min-width-0">
              <div class="d-flex justify-content-between align-items-start gap-2">
                <div class="fw-semibold <?= !$notif['is_read'] ? 'text-primary' : '' ?> small">
                  <?= htmlspecialchars($notif['title'] ?? '') ?>
                </div>
                
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                  <?php if (!$notif['is_read']): ?>
                    <span class="badge bg-primary rounded-pill" style="width:8px;height:8px;padding:0">&nbsp;</span>
                  <?php endif; ?>
                  <span class="text-muted" style="font-size:.72rem"><?= $notif['created_at'] ?></span>
                </div>
              </div>
              <div class="text-muted small mt-1"><?= htmlspecialchars($notif['content']) ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
 
    </div>
  </div>
</div>
</main>
 
<script>
function markRead(id, el) {
  // Nếu đã đọc rồi thì bỏ qua để không trừ lặp số lượng
  if (el.classList.contains('border-transparent')) return;

  // Đổi giao diện item thành đã đọc
  el.classList.remove('bg-primary', 'bg-opacity-5', 'border-primary');
  el.classList.add('border-transparent');
  const dot   = el.querySelector('.badge.bg-primary.rounded-pill');
  const title = el.querySelector('.fw-semibold');
  if (dot) dot.remove();
  if (title) title.classList.remove('text-primary');
 
  // Xử lý trừ số đếm và đổi màu nút Chưa đọc realtime
  const unreadBadge = document.getElementById('unread-badge');
  const headerBadge = document.getElementById('header-unread-badge');
  const filterUnreadBtn = document.getElementById('filter-unread-btn');
  const formMarkAll = document.getElementById('form-mark-all');

  if (unreadBadge) {
    let count = parseInt(unreadBadge.innerText);
    if (count > 0) {
      count--;
      unreadBadge.innerText = count;
      if (headerBadge) headerBadge.innerText = count;

      // Khi đọc cái cuối cùng (count về 0)
      if (count === 0) {
        // 1. Ẩn các số đếm đỏ
        unreadBadge.style.display = 'none';
        if (headerBadge) headerBadge.style.display = 'none';
        
        // 2. Ẩn nút "Đánh dấu tất cả"
        if (formMarkAll) formMarkAll.style.display = 'none';
        
        // 3. Chuyển nút Filter về màu nền xám nhạt (nếu đang không ở tab Chưa đọc)
        if (filterUnreadBtn && !filterUnreadBtn.classList.contains('btn-primary')) {
          filterUnreadBtn.classList.remove('btn-danger', 'text-white');
          filterUnreadBtn.classList.add('btn-outline-secondary');
        }
      }
    }
  }

  // Gọi API chạy ngầm
  fetch('/BTL-MNM/api/notification_read.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'notification_id=' + id
  });
}
</script>
 
<?php require_once __DIR__ . '/../includes/footer.php'; ?>