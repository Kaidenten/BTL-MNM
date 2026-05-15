<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: /BTL-MNM/pages/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}
