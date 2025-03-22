<?php
/* Hàm xử lý phân trang cho bình luận của 1 bài đăng */
function getCommentsPagination($conn, $post_id) {
    $comments_per_page = 7;
    $cpage_param = "cpage_" . $post_id;
    $cpage = isset($_GET[$cpage_param]) ? (int)$_GET[$cpage_param] : 1;
    if ($cpage < 1) $cpage = 1;

    // Đếm tổng số bình luận cho bài đăng này
    $total_comments_query = "SELECT COUNT(*) as total FROM comments WHERE post_id = $post_id";
    $total_comments_result = mysqli_query($conn, $total_comments_query);
    $total_comments_row = mysqli_fetch_assoc($total_comments_result);
    $total_comments = $total_comments_row['total'];
    $total_comment_pages = ceil($total_comments / $comments_per_page);
    $comment_offset = ($cpage - 1) * $comments_per_page;

    $query_comments = "SELECT comments.*, users.role AS user_role 
                       FROM comments 
                       JOIN users ON comments.username = users.username 
                       WHERE post_id = $post_id 
                       ORDER BY created_at DESC
                       LIMIT $comment_offset, $comments_per_page";
    $result_comments = mysqli_query($conn, $query_comments);

    return [
        'result_comments'    => $result_comments,
        'cpage_param'        => $cpage_param,
        'cpage'              => $cpage,
        'total_comment_pages'=> $total_comment_pages
    ];
}
?>