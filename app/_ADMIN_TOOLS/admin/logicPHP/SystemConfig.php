<?php
// Kết nối đến database và truy vấn bảng misc
$sql = "SELECT * FROM misc WHERE id = 1";
$result = $conn->query($sql);
$system_config = $result->fetch_assoc();

// Kiểm tra nếu form được submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form và đảm bảo an toàn
    $title = htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8');
    $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
    $info = htmlspecialchars($_POST['info'], ENT_QUOTES, 'UTF-8');
    $turnstile_site_key = htmlspecialchars($_POST['turnstile_site_key'], ENT_QUOTES, 'UTF-8');
    $turnstile_api_key = htmlspecialchars($_POST['turnstile_api_key'], ENT_QUOTES, 'UTF-8');
    $ipinfo_api_key = htmlspecialchars($_POST['ipinfo_api_key'], ENT_QUOTES, 'UTF-8');

    // Sử dụng Prepared Statement để tránh SQL Injection
    $update_sql = "UPDATE misc SET title = ?, name = ?, info = ?, turnstile_site_key = ?, turnstile_api_key = ?, ipinfo_api_key = ? WHERE id = 1";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssss", $title, $name, $info, $turnstile_site_key, $turnstile_api_key, $ipinfo_api_key);

    if ($stmt->execute()) {
        // Sau khi lưu thành công, chuyển hướng lại trang này để làm mới và giữ lại phần section
        header('Location: admin.php?section=system_config');
        exit;
    } else {
        echo "Lỗi: " . $stmt->error;
    }
    $stmt->close();
}
?>
