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

// Kiểm tra file có hiển thị inline được không
function shouldDisplayInline($filePath) {
    if (!file_exists($filePath) || !isImage($filePath)) return false;
    list($width, $height) = getimagesize($filePath);
    return $width <= 1920 && $height <= 1080;
}
?>
