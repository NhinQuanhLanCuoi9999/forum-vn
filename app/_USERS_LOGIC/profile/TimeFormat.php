<?php
function formatTimeDiff($datetime) {
    try {
        $now = new DateTime();
        $target = new DateTime($datetime);
        $diff = $now->getTimestamp() - $target->getTimestamp();

        if ($diff < 60) {
            return $diff . " giây trước";
        } elseif ($diff < 3600) {
            return floor($diff / 60) . " phút trước";
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . " giờ trước";
        } elseif ($diff < 604800) {
            return floor($diff / 86400) . " ngày trước";
        } else {
            return $target->format('d/m/Y | H:i:s');
        }
    } catch (Exception $e) {
        return "Không xác định";
    }
}
?>