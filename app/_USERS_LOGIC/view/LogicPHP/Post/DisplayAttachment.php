<?php
function displayAttachment($fileName, $safeFileName, $cleanName) {
    $filePath = "../uploads/" . $safeFileName;

    if ($fileName) {
        if (isImage($filePath)) {
            return '
                <div class="text-center mb-3">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="updateModalImage(\'' . $filePath . '\')" style="text-decoration: none;">
                        <img src="' . $filePath . '" alt="' . $cleanName . '" style="max-width:100%; height:auto; border-radius: 15px;">
                    </a>
                </div>';
        } elseif (isVideo($filePath)) {
            return '
                <div class="text-center mb-3">
                    <video controls style="max-width:100%; height:auto;">
                        <source src="' . $filePath . '" type="' . mime_content_type($filePath) . '">
                        Trình duyệt của bạn không hỗ trợ video.
                    </video>
                </div>';
        } elseif (isAudio($filePath)) {
            return '
                <div class="text-center mb-3">
                    <audio controls style="width:100%;">
                        <source src="' . $filePath . '" type="' . mime_content_type($filePath) . '">
                        Trình duyệt của bạn không hỗ trợ audio.
                    </audio>
                </div>';
        } else {
            return '
                <p><strong>Tệp đính kèm: </strong>
                    <a href="' . $filePath . '" download onclick="return confirmDownload(\'' . $cleanName . '\')" style="text-decoration: none;">
                        ' . $cleanName . '
                    </a>
                </p>
                <script>function confirmDownload(fileName) { return confirm(`Cảnh báo: Tệp "${fileName}" có thể không an toàn. Bạn có chắc muốn tải xuống không?`); }</script>';
        }
    }

    return ''; // Nếu không có tệp, trả về chuỗi rỗng
}
?>
