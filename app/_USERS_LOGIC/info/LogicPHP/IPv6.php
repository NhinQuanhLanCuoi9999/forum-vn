<?php
function getClientIPv6() {
    $sources = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR'
    ];

    foreach ($sources as $source) {
        if (!empty($_SERVER[$source])) {
            $ipList = explode(',', $_SERVER[$source]); // Nếu có nhiều IP, lấy cái đầu tiên
            foreach ($ipList as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    return $ip;
                }
            }
        }
    }

    return 'Không lấy được địa chỉ IPv6';
}

$ipv6 = getClientIPv6();

?>
