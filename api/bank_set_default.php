<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /BTL-MNM/pages/bank_account.php'); exit();
}
 
$bankAccountId = trim($_POST['bank_account_id'] ?? '');
$db = getDB();
 
// Bỏ mặc định tất cả TK cũ
$db->prepare("UPDATE bank_accounts SET is_default = FALSE WHERE user_id = ?")
   ->execute([$_SESSION['user_id']]);
 
// Đặt TK mới làm mặc định
$db->prepare("UPDATE bank_accounts SET is_default = TRUE WHERE bank_account_id = ? AND user_id = ?")
   ->execute([$bankAccountId, $_SESSION['user_id']]);
 
setFlash('success', 'Đã đặt làm tài khoản mặc định.');
header('Location: /BTL-MNM/pages/bank_account.php');
exit();
