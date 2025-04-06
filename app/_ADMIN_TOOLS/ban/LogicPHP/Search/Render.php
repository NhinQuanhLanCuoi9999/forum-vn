<?php

// Hàm render danh sách ban (chung cho cả trang chính và search)
function renderBanList($result) {
    if ($result->num_rows === 0) {
        echo '<p class="text-muted">Không tìm thấy kết quả.</p>';
    } else {
        while ($ban = $result->fetch_assoc()) {
            echo '<div class="list-group-item d-flex justify-content-between align-items-center">';
            echo '<div>';
            echo '<strong>' . htmlspecialchars($ban['username'] ?? 'Không xác định') . '</strong> - ';
            echo 'IP: ' . htmlspecialchars($ban['ip_address']) . ' - ';
            echo 'Lý do: ' . htmlspecialchars($ban['reason']) . ' - ';
            echo 'Đến: ' . htmlspecialchars($ban['ban_end']);
            echo '</div>';
            echo '<form method="post" action="ban.php" onsubmit="return confirmUnban();">';
            echo '<input type="hidden" name="unban" value="' . $ban['id'] . '">';
            echo '<button type="submit" class="btn btn-warning btn-sm">Hủy cấm</button>';
            echo '</form>';
            echo '</div>';
        }
    }
}
?>