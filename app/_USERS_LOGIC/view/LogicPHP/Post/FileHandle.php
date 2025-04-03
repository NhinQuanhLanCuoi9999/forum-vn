<?php
function cleanFileName($fileName) {return preg_replace('/_[A-Za-z0-9]{10,}\./', '.', $fileName);}

$safeFileName = htmlspecialchars($post['file'] ?? '', ENT_QUOTES, 'UTF-8');
$cleanName = cleanFileName($safeFileName);

// Hàm kiểm tra loại file
function isFileType($filePath, $allowedTypes) {if (!file_exists($filePath)) return false;return in_array(mime_content_type($filePath), $allowedTypes);}

// Kiểm tra ảnh
function isImage($filePath) {return isFileType($filePath, ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);}

// Kiểm tra video
function isVideo($filePath) {return isFileType($filePath, ['video/mp4', 'video/webm', 'video/ogg']);}

// Kiểm tra audio
function isAudio($filePath) {return isFileType($filePath, ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/ogg']);}

function shouldDisplayInline($filePath) {
    if (!file_exists($filePath)) return false;
    
    // Cho phép hiển thị nếu là ảnh, video hoặc audio
    if (isImage($filePath) || isVideo($filePath) || isAudio($filePath)) {
        return true;
    }
    
    return false;
}

?>
