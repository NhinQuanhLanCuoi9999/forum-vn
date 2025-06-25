<?php
namespace App\_API\core;

class ApiResponse {
    public static function requireApiKey($api_key) {
        if (!$api_key) {
            self::error(400, "API key is required.");
        }
    }

    public static function check($condition, $error) {
        if (!$condition) {
            $code = $error['code'] ?? 400;
            $msg = $error['error'] ?? 'Error';
            self::error($code, $msg);
        }
    }

    public static function error($code, $msg) {
        http_response_code($code);
        echo json_encode(["error" => $msg], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function checkEmpty($data) {
        if (is_array($data) && empty($data)) {
            self::error(404, "Không có dữ liệu.");
        }
    }
}
