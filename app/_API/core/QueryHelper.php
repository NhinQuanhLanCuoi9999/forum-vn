<?php
namespace App\_API\core;

class QueryHelper {
    /**
     * Add WHERE conditions for non-null parameters.
     * @param string $sql
     * @param array $params
     * @param array $param_types
     * @param array $conditions [ [ 'field', 'type', 'value', 'like' (bool) ] ]
     */
    public static function addWhereConditions(&$sql, &$params, &$param_types, $conditions) {
        foreach ($conditions as $cond) {
            list($field, $type, $value, $like) = array_pad($cond, 4, false);
            if (!is_null($value)) {
                if ($like) {
                    $sql .= " AND $field LIKE ?";
                    $param_types .= $type;
                    $params[] = "%" . $value . "%";
                } else {
                    $sql .= " AND $field = ?";
                    $param_types .= $type;
                    $params[] = $value;
                }
            }
        }
    }

    /**
     * Add ORDER BY clause with allowed columns and sort order.
     * @param string $sql
     * @param string $sort
     * @param array $allowed_columns
     * @param string $default_column
     */
    public static function addOrderBy(&$sql, $sort, $allowed_columns, $default_column) {
        $sort_parts = explode(':', $sort);
        $sort_column = in_array($sort_parts[0], $allowed_columns) ? $sort_parts[0] : $default_column;
        $sort_order = (isset($sort_parts[1]) && strtolower($sort_parts[1]) === 'desc') ? 'DESC' : 'ASC';
        $sql .= " ORDER BY $sort_column $sort_order";
    }
}
