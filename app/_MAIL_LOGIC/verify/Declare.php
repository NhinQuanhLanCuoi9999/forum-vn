<?php
// Định nghĩa biến $gmail và $is_active
$gmail = "";
$is_active = "0";

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Lấy địa chỉ email và trạng thái kích hoạt từ bảng users dựa vào username
    $user_query = $conn->prepare("SELECT gmail, is_active FROM users WHERE username = ?");
    $user_query->bind_param("s", $username);
    $user_query->execute();
    $user_query->bind_result($gmail, $is_active);
    $user_query->fetch();
    $user_query->close();
}

// Kiểm tra trạng thái kích hoạt tài khoản
if ($is_active === '1') {
    echo "<div class='alert alert-success'>Tài khoản của bạn đã được kích hoạt. Bạn sẽ được chuyển hướng đến trang chủ sau 3 giây.</div>";
    header("Refresh: 3; url=index.php");
    exit();
}

?>