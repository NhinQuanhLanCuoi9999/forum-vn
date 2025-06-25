<?php
namespace App\_API;

require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_API/core/BaseApi.php';

class PostApi extends core\BaseApi {
    public function getPosts($username = null, $description = null, $content = null, $sort = 'id:desc') {
        $sql = "SELECT id, username, content, description, file, status, view, created_at FROM posts WHERE 1=1";
        $params = [];
        $param_types = '';

        if (!is_null($username)) {
            $sql .= " AND username = ?";
            $param_types .= 's';
            $params[] = $username;
        }

        if (!is_null($description)) {
            $sql .= " AND description LIKE ?";
            $param_types .= 's';
            $params[] = "%" . $description . "%";
        }

        if (!is_null($content)) {
            $sql .= " AND content LIKE ?";
            $param_types .= 's';
            $params[] = "%" . $content . "%";
        }

        $allowed_sort_columns = ['id', 'view', 'created_at'];
        $sort_parts = explode(':', $sort);
        $sort_column = in_array($sort_parts[0], $allowed_sort_columns) ? $sort_parts[0] : 'id';
        $sort_order = (isset($sort_parts[1]) && strtolower($sort_parts[1]) === 'desc') ? 'DESC' : 'ASC';

        $sql .= " ORDER BY $sort_column $sort_order";
        $sql .= " LIMIT ?";

        $param_types .= 'i';
        $params[] = $this->limit;

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return ['error' => 'Prepare failed: ' . $this->conn->error];
        }

        if (!empty($param_types)) {
            $bind_names = [];
            $bind_names[] = $param_types;
            foreach ($params as $key => $value) {
                $bind_names[] = &$params[$key];
            }
            call_user_func_array([$stmt, 'bind_param'], $this->refValues($bind_names));
        }

        if (!$stmt->execute()) {
            return ['error' => 'Execute failed: ' . $stmt->error];
        }

        $result = $stmt->get_result();
        $posts = [];

        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }

        return $posts;
    }
}
?>