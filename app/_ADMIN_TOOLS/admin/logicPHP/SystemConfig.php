<?php
// Kết nối đến database và truy vấn bảng misc
$sql = "SELECT * FROM misc WHERE id = 1";
$result = $conn->query($sql);
$system_config = $result->fetch_assoc();

// Kiểm tra nếu form được submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $title = $_POST['title'];
    $name = $_POST['name'];
    $info = $_POST['info'];
    $turnstile_api_key = $_POST['turnstile_api_key'];
    $ipinfo_api_key = $_POST['ipinfo_api_key'];

    // Cập nhật bảng misc
    $update_sql = "UPDATE misc SET title = '$title', name = '$name', info = '$info', turnstile_api_key = '$turnstile_api_key', ipinfo_api_key = '$ipinfo_api_key' WHERE id = 1";
    if ($conn->query($update_sql) === TRUE) {
        // Sau khi lưu thành công, chuyển hướng lại trang này để làm mới và giữ lại phần section
        header('Location: admin.php?section=system_config');
        exit;
    } else {
        echo "Lỗi: " . $conn->error;
    }
}
?>