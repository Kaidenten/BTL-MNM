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
 
$db->prepare("DELETE FROM bank_accounts WHERE bank_account_id = ? AND user_id = ?")
   ->execute([$bankAccountId, $_SESSION['user_id']]);
 
setFlash('success', 'Đã xóa tài khoản ngân hàng.');
header('Location: /BTL-MNM/pages/bank_account.php');
exit();
