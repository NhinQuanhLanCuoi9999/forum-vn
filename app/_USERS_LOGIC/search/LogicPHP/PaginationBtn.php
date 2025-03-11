<?php
if ($result->num_rows > 0) {
   
    // Phân trang
    if ($total_pages > 1) {
        echo "<div class='pagination'>";

        // Hiển thị <<< (trang đầu tiên)
        if ($page > 3) {
            echo "<a href='?page=1&search=$search&start_date=$start_date&end_date=$end_date'>&lt;&lt;&lt;</a>";
        }

        // Hiển thị các trang từ trang 1 đến trang cuối
        for ($i = 1; $i <= $total_pages; $i++) {
            $active = $i == $page ? 'class="active"' : ''; // Đánh dấu trang hiện tại
            echo "<a href='?page=$i&search=$search&start_date=$start_date&end_date=$end_date' $active>$i</a> ";
        }

        // Hiển thị >>> (trang cuối cùng)
        if ($page < $total_pages - 2) {
            echo "<a href='?page=$total_pages&search=$search&start_date=$start_date&end_date=$end_date'>&gt;&gt;&gt;</a>";
        }

        echo "</div>"; // Đóng thẻ div phân trang
    } // Đóng điều kiện phân trang
} // Đóng điều kiện kiểm tra số lượng bài viết
?>