<?php
function fetchPosts($conn, $search, $start_date, $end_date, $start_limit, $per_page) {
    $search_param = "%$search%";
    $query = "SELECT * FROM posts WHERE content LIKE ? ";
    if ($start_date && $end_date) {
        $query .= "AND created_at BETWEEN ? AND ? ";
    }
    $query .= "ORDER BY created_at DESC LIMIT ?, ?";
    $stmt = $conn->prepare($query);
    if ($start_date && $end_date) {
        $stmt->bind_param('sssii', $search_param, $start_date, $end_date, $start_limit, $per_page);
    } else {
        $stmt->bind_param('sii', $search_param, $start_limit, $per_page);
    }
    $stmt->execute();
    return $stmt->get_result();
}
?>