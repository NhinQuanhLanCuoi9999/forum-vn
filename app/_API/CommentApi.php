<?php
namespace App\_API;

require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_API/core/BaseApi.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_API/core/QueryHelper.php';

class CommentApi extends core\BaseApi {
    public function getComments($post_id = null, $username = null, $content = null, $sort = 'id:desc') {
        $sql = "SELECT id, post_id, username, content, created_at FROM comments WHERE 1=1";
        $params = [];
        $param_types = '';

        \App\_API\core\QueryHelper::addWhereConditions($sql, $params, $param_types, [
            ['post_id', 'i', $post_id],
            ['username', 's', $username],
            ['content', 's', $content, true],
        ]);

        $allowed_sort_columns = ['id', 'created_at'];
        \App\_API\core\QueryHelper::addOrderBy($sql, $sort, $allowed_sort_columns, 'id');
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
