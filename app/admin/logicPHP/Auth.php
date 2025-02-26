<?php

// Kiểm tra xem người dùng có phải là admin hoặc owner không
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'owner')) {
    header("Location: ../index.php");
    exit();
}

?>