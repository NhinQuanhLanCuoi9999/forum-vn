<?php
// Lấy giới hạn post_max_size từ php.ini (đổi sang bytes)
function convertToBytes($value) {
    $unit = strtoupper(substr($value, -1));
    $bytes = (int)$value;
    $multipliers = ['K' => 1024, 'M' => 1024 ** 2, 'G' => 1024 ** 3];
    
    return isset($multipliers[$unit]) ? $bytes * $multipliers[$unit] : $bytes;
}

function formatSize($bytes) {
    if ($bytes >= 1073741824) { // 1GB = 1024^3 bytes
        return number_format($bytes / 1073741824, 2, ',', '') . ' GB';
    } elseif ($bytes >= 1048576) { // 1MB = 1024^2 bytes
        return number_format($bytes / 1048576, 2, ',', '') . ' MB';
    } else {
        return number_format($bytes / 1024, 2, ',', '') . ' KB';
    }
}

$maxPostSize = convertToBytes(ini_get('post_max_size'));
?>