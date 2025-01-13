<?php
$limit = 6; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page number
$offset = ($page - 1) * $limit; // Calculate the offset for SQL query

// Fetch total number of users for pagination calculation
if (isset($_GET['section']) && $_GET['section'] === 'users') {
    $total_users_result = $conn->query("SELECT COUNT(*) as count FROM users");
    $total_users = $total_users_result->fetch_assoc()['count'];
    $total_pages = ceil($total_users / $limit);

    // Fetch users with pagination
    $users_query = "SELECT * FROM users LIMIT $limit OFFSET $offset";
    $users_result = $conn->query($users_query);
}
?>