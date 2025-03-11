<?php
// Kiểm tra xem người dùng đã đăng nhập chưa
if (isset($_SESSION['username'])) {
    // Lấy giá trị username từ session
    $username = $_SESSION['username'];

    // Truy vấn vào bảng users để lấy giá trị 2fa
    $sql = "SELECT 2fa FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $twofa = $row['2fa'];

        // Nếu giá trị 2fa bằng 0, chuyển hướng về trang chính "/"
        if ($twofa == 0) {
            header("Location: /");
            exit();
        }
    }
}

// Kiểm tra nếu tham số logout được truyền vào URL thì sẽ logout
if (isset($_GET['logout'])) {
    if (isset($_SESSION['username'])) {unset($_SESSION['username']);unset($_SESSION['otp']);unset($_SESSION['otp_attempts']);}}

// Kiểm tra số lần nhập sai OTP
if (!isset($_SESSION['otp_attempts'])) {
    $_SESSION['otp_attempts'] = 0;
  }
  
  // Nếu người dùng nhập sai OTP quá 5 lần
  if ($_SESSION['otp_attempts'] >= 5) {
    // Xóa session và chuyển hướng về trang chủ
    session_destroy();
    header("Refresh: 4; url=/");
    echo "<div class='alert alert-danger' role='alert'>Bạn đã nhập sai quá 5 lần. Quá trình xác thực thất bại.Đang chuyển hướng...</div>";
    exit();
  }
  
  // Nếu nhập sai OTP, tăng số lần thử
  if (isset($_POST['otp_input']) && (!isset($_SESSION['otp']) || $_POST['otp_input'] != $_SESSION['otp'])) {
    $_SESSION['otp_attempts']++;
    echo "<div class='alert alert-danger' role='alert'>Mã OTP không đúng. Bạn còn " . (5 - $_SESSION['otp_attempts']) . " lần thử.</div>";
  }


// Kiểm tra nếu session '2fa' có giá trị là 1
if (isset($_SESSION['2fa']) && $_SESSION['2fa'] == 1) {unset($_SESSION['otp']);unset($_SESSION['otp_attempts']);
  header("Refresh: 1; url=/");
    exit();
}
?>