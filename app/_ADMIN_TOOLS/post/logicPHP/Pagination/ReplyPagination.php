<?php
function getRepliesPagination($conn, $comment_id) {
    $replies_per_page = 7;
    $rpage_param = "rpage_" . $comment_id;
    $rpage = isset($_GET[$rpage_param]) ? (int)$_GET[$rpage_param] : 1;
    if ($rpage < 1) $rpage = 1;

    $total_replies_query = "SELECT COUNT(*) as total FROM replies WHERE comment_id = ?";
    $stmt = mysqli_prepare($conn, $total_replies_query);
    mysqli_stmt_bind_param($stmt, "i", $comment_id);
    mysqli_stmt_execute($stmt);
    $result_reply_count = mysqli_stmt_get_result($stmt);
    $total_replies_row = mysqli_fetch_assoc($result_reply_count);
    $total_replies = $total_replies_row['total'];
    $total_reply_pages = ceil($total_replies / $replies_per_page);
    mysqli_stmt_close($stmt);

    $reply_offset = ($rpage - 1) * $replies_per_page;

    $query_replies = "SELECT replies.*, users.role AS user_role FROM replies JOIN users ON replies.username = users.username WHERE comment_id = ? ORDER BY created_at DESC LIMIT ?, ?";
    $stmt = mysqli_prepare($conn, $query_replies);
    mysqli_stmt_bind_param($stmt, "iii", $comment_id, $reply_offset, $replies_per_page);
    mysqli_stmt_execute($stmt);
    $result_replies = mysqli_stmt_get_result($stmt);

    return [
        'result_replies'    => $result_replies,
        'rpage_param'       => $rpage_param,
        'rpage'             => $rpage,
        'total_reply_pages' => $total_reply_pages
    ];
}
?>