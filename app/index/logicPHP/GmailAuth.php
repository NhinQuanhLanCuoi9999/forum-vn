<?php
// Nếu session 'username' tồn tại thì thực hiện các đoạn code bên dưới
if (isset($_SESSION['username'])) {
    // Lấy username từ session
    $username = $_SESSION['username'];

    // Chuẩn bị câu truy vấn an toàn với prepared statement
    $stmt = $conn->prepare("SELECT is_active, gmail FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    $row = $result->fetch_assoc();

    // Kiểm tra trạng thái xác thực (is_active)
    if ($row['is_active'] == 0) {
        echo '<div style="color: #a94442; background-color: #f2dede; border: 1px solid #ebccd1; padding: 15px; border-radius: 4px; position: relative; margin-bottom: 20px;" class="fade in">';
        echo '<button type="button" onclick="this.parentElement.style.display=\'none\';" style="color: black; position: absolute; top: 5px; right: 10px; background: transparent; border: none; font-size: 20px; line-height: 20px; cursor: pointer;">&times;</button>';
        echo 'Tài khoản của bạn chưa được xác thực, vui lòng xác thực <a href="src/verify.php">Tại đây</a>.';
        echo '</div>';
    }

    // Kiểm tra trường gmail (nếu null hoặc rỗng)
    if (empty($row['gmail'])) {
        echo '<div style="color: #a94442; background-color: #f2dede; border: 1px solid #ebccd1; padding: 15px; border-radius: 4px; position: relative; margin-bottom: 20px;" class="fade in">';
        echo '<button type="button" onclick="this.parentElement.style.display=\'none\';" style="color: black; position: absolute; top: 5px; right: 10px; background: transparent; border: none; font-size: 20px; line-height: 20px; cursor: pointer;">&times;</button>';
        echo 'Cảnh báo: Tài khoản của bạn chưa có gmail, vui lòng thêm gmail <a href="src/info_user.php">Tại đây</a>.';
        echo '</div>';
    }
}
?>