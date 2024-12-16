<?php
// Đường dẫn đến thư mục logs trong htdocs
            $filePath = $_SERVER['DOCUMENT_ROOT'] . '/logs/' . $selectedLog;

            // Kiểm tra xem file có tồn tại và có quyền đọc không
            if (file_exists($filePath)) {
                // Kiểm tra quyền truy cập đọc file
                if (is_readable($filePath)) {
                    // Đọc nội dung file
                    $fileContent = file_get_contents($filePath);

                    // Chuyển nội dung sang JSON hoặc xử lý hiển thị theo yêu cầu
                    $logLines = explode(PHP_EOL, $fileContent); // Tách các dòng

                    // Duyệt qua từng dòng và thay thế \n bằng <br>
                    $logOutput = implode('<br>', array_map('htmlspecialchars', $logLines));

                    echo $logOutput; // Hiển thị logs với thẻ <br>
                } else {
                    echo '<span class="error">Không có quyền đọc file ' . htmlspecialchars($selectedLog) . '</span>';
                }
            } else {
                echo '<span class="error">Tệp ' . htmlspecialchars($selectedLog) . ' không tồn tại</span>';
            }
        ?>