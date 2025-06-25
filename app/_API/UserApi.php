<?php
namespace App\_API;

require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_API/core/BaseApi.php';

class UserApi extends core\BaseApi {
    public function getUsers($username = null, $desc = null, $sort = 'id:desc') {
        $sql = "SELECT id, username, gmail, is_active, `2fa`, description, role, created_at, last_login FROM users WHERE 1=1";
        $params = [];
        $param_types = '';

        if (!is_null($username)) {
            $sql .= " AND username = ?";
            $param_types .= 's';
            $params[] = &$username;
        }

        if (!is_null($desc)) {
            $sql .= " AND description LIKE ?";
            $param_types .= 's';
            $desc_like = '%' . $desc . '%';
            $params[] = &$desc_like;
        }

        $allowed_sort_columns = ['id', 'username', 'created_at'];
        $sort_parts = explode(':', $sort);
        $sort_column = in_array($sort_parts[0], $allowed_sort_columns) ? $sort_parts[0] : 'id';
        $sort_order = isset($sort_parts[1]) && strtolower($sort_parts[1]) === 'desc' ? 'DESC' : 'ASC';

        $sql .= " ORDER BY $sort_column $sort_order";
        $sql .= " LIMIT ?";

        $param_types .= 'i';
        $params[] = &$this->limit;

        $stmt = $this->conn->prepare($sql);
        if ($param_types !== '') {
            call_user_func_array([$stmt, 'bind_param'], $this->refValues(array_merge([$param_types], $params)));
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];

        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }

        return $users;
    }
}
