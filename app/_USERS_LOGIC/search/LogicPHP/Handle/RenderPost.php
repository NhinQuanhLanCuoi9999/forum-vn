<?php
function renderPosts($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='post'>";
            echo "<h3><a href='src/profile.php?username=" . htmlspecialchars($row['username']) . "'>" . htmlspecialchars($row['username']) . "</a></h3>";        
            echo "<p>" . htmlspecialchars($row['content']) . "</p>";
            echo "<small>Ngày đăng: " . htmlspecialchars($row['created_at']) . "</small>";
            echo " <a href='src/view.php?id=" . htmlspecialchars($row['id']) . "' class='view-more'>Xem thêm</a>";
            echo "</div>";
        }
    }
}
?>