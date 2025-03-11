<?php

// Kiểm tra nếu có session "username"
if (isset($_SESSION['username'])) {
    // Truy vấn để lấy giá trị từ bảng misc
    $query = "SELECT info FROM misc LIMIT 1";  // Giả sử chỉ lấy 1 giá trị, có thể thay đổi theo nhu cầu
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $misc_name = $row['info'];  // Lưu giá trị vào biến

    // Kiểm tra nếu giá trị của info là null hoặc chuỗi rỗng
    if (empty($misc_name)) {
        return;  // Nếu giá trị là null hoặc chuỗi rỗng, dừng thực thi mã và không hiển thị phần echo
    }

    // Mã hóa dữ liệu trước khi chèn vào HTML
    $safe_misc_name = htmlspecialchars($misc_name, ENT_QUOTES, 'UTF-8');

    // Xuất JavaScript vào phần đầu trang HTML
    echo '<script>
            // Kiểm tra nếu đã có thông báo trong localStorage và đã hết thời gian 2 giờ
            var dismissedTime = localStorage.getItem("dismissedTime");
            var currentTime = new Date().getTime();

            if (!dismissedTime || (currentTime - dismissedTime) > 7200000) {  // 7200000 ms = 2 giờ
                document.write(\'<div id="alert-message" style="background-color: #f8d7da; color: #721c24; padding: 15px; margin: 16px 0; border: 1px solid #f5c6cb; border-radius: 2px; position: relative; font-size: 16px;">\');
                document.write(\'<span>\' + "' ."Thông báo: ". $safe_misc_name . '" + \'</span>\');
                document.write(\'<span onclick="closeAlert()" style="position: absolute; top: 5px; right: 10px; cursor: pointer; font-size: 18px; color: #721c24;">×</span>\');
                document.write(\'</div>\');
            }

            function closeAlert() {
                var alertMessage = document.getElementById("alert-message");
                alertMessage.style.transition = "opacity 0.5s ease";  // Thêm hiệu ứng mờ
                alertMessage.style.opacity = "0";  // Đặt độ mờ thành 0
                
                // Lưu trạng thái đóng và thời gian vào localStorage
                setTimeout(function() {
                    localStorage.setItem("dismissed", "true");
                    localStorage.setItem("dismissedTime", new Date().getTime()); // Lưu thời gian đóng
                    alertMessage.style.display = "none";  // Ẩn hoàn toàn sau khi hiệu ứng mờ hoàn thành
                }, 500);  // Thời gian chờ 500ms (tương ứng với hiệu ứng mờ hoàn thành)
            }
          </script>';
}
?>
