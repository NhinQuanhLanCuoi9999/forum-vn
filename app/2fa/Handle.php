
<?php

if (isset($_SESSION['username'])) {
  $username = $_SESSION['username'];

  // Lấy thông tin người dùng từ DB
  $query = "SELECT * FROM users WHERE username = '$username' AND is_active = 1";
  $result = mysqli_query($conn, $query);
  $user = mysqli_fetch_assoc($result);

  if ($user && $user['gmail'] && $user['2fa'] == '1') {
    $userGmail = $user['gmail'];

    // Lấy thông tin cấu hình email từ bảng misc
    $queryMisc = "SELECT * FROM misc WHERE id = 1";
    $resultMisc = mysqli_query($conn, $queryMisc);
    $misc = mysqli_fetch_assoc($resultMisc);

    if (isset($_POST['otp_input'])) {
      $otpInput = $_POST['otp_input'];

      if (isset($_SESSION['otp']) && $otpInput == $_SESSION['otp']) {
        $_SESSION['2fa'] = true;
        unset($_SESSION['otp']);
        unset($_SESSION['otp_attempts']);
        header("Location: /"); // Redirect to home
        exit();
      } else {
        $_SESSION['error'] = "Mã OTP không đúng. Vui lòng thử lại.";
      }
    } else {
      $otp = strtoupper(bin2hex(random_bytes(4))); 

      if (sendOTP($userGmail, $otp, $misc)) {
        $_SESSION['otp'] = $otp;
        $_SESSION['info'] = "OTP đã được gửi đến email của bạn, hãy nhập mã xác thực hai yếu tố vào phía bên dưới.";
      } else {
        $_SESSION['error'] = "Không thể gửi OTP. Vui lòng thử lại sau.";
      }
    }
  } else {
    header("Location: /"); // Redirect if no user found or 2FA not enabled
    exit();
  }
} else {
  header("Location: /"); // Redirect if no user is logged in
  exit();
}
?>
