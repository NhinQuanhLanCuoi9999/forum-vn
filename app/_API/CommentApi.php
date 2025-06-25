<?php
namespace App\_API;

require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_API/core/BaseApi.php';

class CommentApi extends core\BaseApi {
    public function getComments($post_id = null, $username = null, $content = null, $sort = 'id:desc') {
        $sql = "SELECT id, post_id, username, content, created_at FROM comments WHERE 1=1";
        $params = [];
        $param_types = '';

        if (!is_null($post_id)) {
            $sql .= " AND post_id = ?";
            $param_types .= 'i';
            $params[] = &$post_id;
        }

        if (!is_null($username)) {
            $sql .= " AND username = ?";
            $param_types .= 's';
            $params[] = &$username;
        }

        if (!is_null($content)) {
            $like = "%" . $content . "%";
            $sql .= " AND content LIKE ?";
            $param_types .= 's';
            $params[] = &$like;
        }

        $allowed_sort_columns = ['id', 'created_at'];
        $sort_parts = explode(':', $sort);
        $sort_column = in_array($sort_parts[0], $allowed_sort_columns) ? $sort_parts[0] : 'id';
        $sort_order = (isset($sort_parts[1]) && strtolower($sort_parts[1]) === 'desc') ? 'DESC' : 'ASC';
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
        $comments = [];
        while ($row = $result->fetch_assoc()) {
            $comments[] = $row;
        }
        return $comments;
    }
}
