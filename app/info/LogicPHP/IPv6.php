<?php
function getClientIP() {
    // Danh sách các biến có thể chứa địa chỉ IP
    $sources = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR'
    ];

    foreach ($sources as $source) {
        if (!empty($_SERVER[$source])) {
            $ip = $_SERVER[$source];
            $ipList = explode(',', $ip); // Nếu có nhiều IP, lấy cái đầu tiên
            $ip = trim($ipList[0]);

            // Kiểm tra IPv6
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                return $ip;
            }

            // Nếu là IPv4, chuyển đổi sang IPv4-mapped IPv6
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return "::ffff:$ip";
            }
        }
    }

    return 'Không tìm thấy địa chỉ IP';
}

$ipv6 = getClientIP();
?>
