<?php
namespace App\_API;

require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_API/core/BaseApi.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_API/core/QueryHelper.php';

class BanApi extends core\BaseApi {
    public function getBans($username = null, $ip = null, $sort = 'id:desc') {
        $sql = "SELECT id, username, ip_address, reason, ban_start, ban_end, permanent FROM bans WHERE 1=1";
        $params = [];
        $param_types = '';

        \App\_API\core\QueryHelper::addWhereConditions($sql, $params, $param_types, [
            ['username', 's', $username],
            ['ip_address', 's', $ip],
        ]);

        $allowed_sort_columns = ['username', 'ban_start', 'ban_end'];
        \App\_API\core\QueryHelper::addOrderBy($sql, $sort, $allowed_sort_columns, 'ban_start');
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
