<?php
namespace App\_API;

require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_API/core/BaseApi.php';

class BanApi extends core\BaseApi {
    public function getBans($username = null, $ip = null, $sort = 'id:desc') {
        $sql = "SELECT id, username, ip_address, reason, ban_start, ban_end, permanent FROM bans WHERE 1=1";
        $params = [];
        $param_types = '';

        if (!is_null($username)) {
            $sql .= " AND username = ?";
            $param_types .= 's';
            $params[] = $username;
        }

        if (!is_null($ip)) {
            $sql .= " AND ip_address = ?";
            $param_types .= 's';
            $params[] = $ip;
        }

        $allowed_sort_columns = ['username', 'ban_start', 'ban_end'];
        $sort_parts = explode(':', $sort);
        $sort_column = in_array($sort_parts[0], $allowed_sort_columns) ? $sort_parts[0] : 'ban_start';
        $sort_order = (isset($sort_parts[1]) && strtolower($sort_parts[1]) === 'desc') ? 'DESC' : 'ASC';

        $sql .= " ORDER BY $sort_column $sort_order";
        $sql .= " LIMIT ?";
        $param_types .= 'i';
        $params[] = $this->limit;

        $stmt = $this->conn->prepare($sql);
        if ($param_types !== '') {
            call_user_func_array([$stmt, 'bind_param'], $this->refValues(array_merge([$param_types], $params)));
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $bans = [];
        while ($row = $result->fetch_assoc()) {
            $bans[] = $row;
        }
        return $bans;
    }
}
