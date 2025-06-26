<?php
namespace App\_API;

require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_API/core/BaseApi.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_API/core/QueryHelper.php';

class UserApi extends core\BaseApi {
    public function getUsers($username = null, $desc = null, $sort = 'id:desc') {
        $sql = "SELECT id, username, gmail, is_active, `2fa`, description, role, created_at, last_login FROM users WHERE 1=1";
        $params = [];
        $param_types = '';

        \App\_API\core\QueryHelper::addWhereConditions($sql, $params, $param_types, [
            ['username', 's', $username],
            ['description', 's', $desc, true],
        ]);

        $allowed_sort_columns = ['id', 'username', 'created_at'];
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
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        return $users;
    }
}
