<?php
// Kiểm tra nếu không có session username hoặc username không phải là "admin"
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: /");
    exit(); 
}
?>