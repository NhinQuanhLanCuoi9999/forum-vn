<?php
// Kiểm tra nếu session 'username' tồn tại
if (isset($_SESSION['username'])) {
    echo "<div class='pagination'>";

    // Liên kết đến section đầu tiên
    if ($current_section > 1) {
        echo "<a href='index.php?section=1'>&lt;&lt;</a> ";
    }

    // Liên kết đến section trước
    if ($current_section > 1) {
        echo "<a href='index.php?section=" . ($current_section - 1) . "'>&lt;</a> ";
    }

    // Hiển thị các liên kết section gần với section hiện tại
    $range = 7; // Số section hiển thị xung quanh section hiện tại
    for ($i = max(1, $current_section - $range); $i <= min($total_sections, $current_section + $range); $i++) {
        if ($i == $current_section) {
            echo "<strong>$i</strong> "; // Đánh dấu section hiện tại
        } else {
            echo "<a href='index.php?section=$i'>$i</a> ";
        }
    }

    // Liên kết đến section tiếp theo
    if ($current_section < $total_sections) {
        echo "<a href='index.php?section=" . ($current_section + 1) . "'>&gt;</a> ";
    }

    // Liên kết đến section cuối cùng
    if ($current_section < $total_sections) {
        echo "<a href='index.php?section=$total_sections'>&gt;&gt;</a>";
    }

    echo "</div>";
} else {
    echo "";
}
?>
