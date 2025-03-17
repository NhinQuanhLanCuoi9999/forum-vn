<?php

// Sử dụng PHPMailer từ document root
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$rootPath = $_SERVER['DOCUMENT_ROOT'] . '/app/vendor/phpmailer/phpmailer/src/';
require $rootPath . 'Exception.php';
require $rootPath . 'PHPMailer.php';
require $rootPath . 'SMTP.php';

if (!function_exists('sendOTP')) {
  // Hàm gửi OTP bằng PHPMailer với nội dung email "xịn xò"
  function sendOTP($to, $otp, $misc) {
      $mail = new PHPMailer(true);
      try {
          // Fix lỗi font Tiếng Việt
          $mail->CharSet = 'UTF-8';
          $mail->Encoding = 'base64';

          // Cấu hình SMTP với thông tin từ DB và giá trị mặc định cho host & port
          $mail->isSMTP();
          $mail->Host       = 'smtp.gmail.com';               // Host mặc định (thay đổi nếu cần)
          $mail->SMTPAuth   = true;
          $mail->Username   = $misc['account_smtp'];            // Lấy từ DB
          $mail->Password   = $misc['password_smtp'];           // Lấy từ DB
          $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
          $mail->Port       = 587;                              // Port mặc định (của Gmail)

          // Người gửi và người nhận
          $mail->setFrom($misc['account_smtp'], 'YourAppName');
          $mail->addAddress($to);

          // Nội dung email với giao diện HTML "chất"
          $mail->isHTML(true);
          $mail->Subject = "Mã OTP xác thực 2FA";

          $message = "
          <html>
          <body style='font-family: Arial, sans-serif; background-color: #f7f7f7; margin: 0; padding: 0;'>
              <div style='max-width: 600px; margin: 20px auto; background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);'>
                  <div style='text-align: center; padding: 10px; font-size: 18px; color: #3b5998;'>
                      Xác thực tài khoản của bạn
                  </div>
                  <div style='font-size: 16px; color: #333333; line-height: 1.5;'>
                      <p>Xin chào,</p>
                      <p>Mã OTP của bạn là:</p>
                      <div style='font-size: 24px; font-weight: bold; color: #e74c3c; padding: 10px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 5px; text-align: center; margin-top: 20px;'>
                          {$otp}
                      </div>
                      <p>Vui lòng sử dụng mã này để hoàn tất quá trình xác thực 2FA của bạn.</p>
                      <p>Chúc bạn một ngày tuyệt vời!</p>
                  </div>
                  <div style='font-size: 12px; text-align: center; color: #aaaaaa; margin-top: 30px;'>
                      <p>Bạn nhận được email này vì đã yêu cầu xác thực tài khoản. Nếu bạn không yêu cầu, vui lòng bỏ qua email này.</p>
                  </div>
              </div>
          </body>
          </html>
          ";
          $mail->Body    = $message;
          $mail->AltBody = "Mã OTP của bạn là: $otp";

          $mail->send();
          return true;
      } catch (Exception $e) {
          error_log("Lỗi gửi mail: " . $mail->ErrorInfo);
          return false;
      }
  }
}


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
                header("Location: /"); // Redirect về trang chủ
                exit();
            } else {
                $_SESSION['error'] = "Mã OTP không đúng. Vui lòng thử lại.";
            }
        } else {
            // Sinh OTP mới
            $otp = strtoupper(bin2hex(random_bytes(4)));

            if (sendOTP($userGmail, $otp, $misc)) {
                $_SESSION['otp'] = $otp;
                $_SESSION['info'] = "OTP đã được gửi đến email của bạn, hãy nhập mã xác thực 2FA bên dưới.";
            } else {
                $_SESSION['error'] = "Không thể gửi OTP. Vui lòng thử lại sau.";
            }
        }
    } else {
        header("Location: /"); // Redirect nếu không có user hoặc 2FA không bật
        exit();
    }
} else {
    header("Location: /"); // Redirect nếu chưa đăng nhập
    exit();
}
?>
