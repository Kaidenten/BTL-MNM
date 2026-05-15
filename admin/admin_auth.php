<?php
// Khởi tạo session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: /BTL-MNM/pages/login.php');
    exit();
}

// Kiểm tra quyền (Chỉ cho phép admin truy cập)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Nếu là user bình thường chui vào trang admin thì đẩy ra trang chủ
    header('Location: /BTL-MNM/pages/dashboard.php'); 
    exit();
}
?>