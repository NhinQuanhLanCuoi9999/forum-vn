<?php
namespace App\_API\core;

abstract class BaseApi {
    protected $conn;
    protected $apiKey;
    protected $apiData;
    protected $limit = 50;

    public function __construct($conn, $apiKey) {
        $this->conn = $conn;
        $this->apiKey = $apiKey;
    }

    public function validateApiKey() {
        $stmt = $this->conn->prepare("SELECT id, remaining_uses FROM api_keys WHERE api_key = ? AND is_active = 1");
        $stmt->bind_param("s", $this->apiKey);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return [false, ["error" => "Invalid or inactive API key.", "code" => 403]];
        }
        $this->apiData = $result->fetch_assoc();
        if ($this->apiData['remaining_uses'] <= 0) {
            $delete_stmt = $this->conn->prepare("DELETE FROM api_keys WHERE id = ?");
            $delete_stmt->bind_param("i", $this->apiData['id']);
            $delete_stmt->execute();
            return [false, ["error" => "API key has no remaining uses.", "code" => 403]];
        }
        return [true, null];
    }

    public function rateLimit() {
        session_start();
        $current_time = time();
        if (!isset($_SESSION['api_count'])) {
            $_SESSION['api_count'] = 0;
            $_SESSION['api_count_reset_time'] = $current_time;
        } else {
            $elapsed_time = $current_time - $_SESSION['api_count_reset_time'];
            if ($elapsed_time > 60) {
                $_SESSION['api_count'] = 0;
                $_SESSION['api_count_reset_time'] = $current_time;
            }
        }
        $_SESSION['api_count'] += 1;
        if ($_SESSION['api_count'] > 10) {
            return [false, ["error" => "Too many requests. Please try again later.", "code" => 429]];
        }
        return [true, null];
    }

    public function decrementApiKey() {
        $update_stmt = $this->conn->prepare("UPDATE api_keys SET remaining_uses = remaining_uses - 1 WHERE id = ?");
        $update_stmt->bind_param("i", $this->apiData['id']);
        $update_stmt->execute();
    }

    protected function refValues($arr) {
        if (strnatcmp(phpversion(), '5.3') >= 0) {
            $refs = [];
            foreach ($arr as $key => $value) {
                $refs[$key] = &$arr[$key];
            }
            return $refs;
        }
        return $arr;
    }
}
