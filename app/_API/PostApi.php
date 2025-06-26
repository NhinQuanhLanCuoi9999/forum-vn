<?php
namespace App\_API;

require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_API/core/BaseApi.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_API/core/QueryHelper.php';

class PostApi extends core\BaseApi {
    public function getPosts($username = null, $description = null, $content = null, $sort = 'id:desc') {
        $sql = "SELECT id, username, content, description, file, status, view, created_at FROM posts WHERE 1=1";
        $params = [];
        $param_types = '';

        \App\_API\core\QueryHelper::addWhereConditions($sql, $params, $param_types, [
            ['username', 's', $username],
            ['description', 's', $description, true],
            ['content', 's', $content, true],
        ]);

        $allowed_sort_columns = ['id', 'view', 'created_at'];
        \App\_API\core\QueryHelper::addOrderBy($sql, $sort, $allowed_sort_columns, 'id');
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
