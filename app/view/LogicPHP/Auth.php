<?php
include '../app/view/LogicPHP/Auth2.php';
include 'badWord.php';

// Kiểm tra nếu có id trong URL
if (isset($_GET['id'])) {
    $postId = intval($_GET['id']);  // Chuyển ID sang kiểu số nguyên để bảo vệ khỏi injection

    // Lấy thông tin bài viết theo id
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();

    // Nếu bài viết không tồn tại
    if (!$post) {
        echo "Bài viết không tồn tại.";
        exit;
    }

    // Lấy danh sách bình luận của bài viết
    $stmt_comments = $conn->prepare("SELECT * FROM comments WHERE post_id = ? ORDER BY created_at DESC");
    $stmt_comments->bind_param("i", $postId);
    $stmt_comments->execute();
    $comments = $stmt_comments->get_result();

    // Kiểm tra nếu người dùng đã đăng nhập
    $isLoggedIn = isset($_SESSION['username']);
    $isOwner = $isLoggedIn && $_SESSION['username'] === $post['username']; // Kiểm tra nếu người dùng là chủ bài đăng

    // Thêm bình luận
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
        $content = $_POST['comment'];
        $post_id = $postId;

        // Kiểm tra nếu nội dung bình luận không rỗng và có chứa từ cấm
        if (!empty($content) && containsBadWords($content)) {
            $_SESSION['error'] = "Nội dung không phù hợp, vui lòng kiểm tra lại.";
        } else {
            // Định dạng nội dung bình luận
            $formatted_content = formatText($content);

           // Sử dụng prepared statements khi thêm bình luận
$stmt_insert_comment = $conn->prepare("INSERT INTO comments (post_id, content, username) VALUES (?, ?, ?)");
$stmt_insert_comment->bind_param("iss", $post_id, $formatted_content, $_SESSION['username']);
$stmt_insert_comment->execute();

// Lấy thời gian hiện tại theo định dạng dd/mm/yyyy | hh:mm:ss
$datetime = date("d/m/Y | H:i:s");

// Ghi log với định dạng: [dd/mm/yyyy | hh:mm:ss] Người dùng : [tên user] đã đăng bình luận vào $post_id với nội dung : [content]
logAction("[$datetime] Người dùng: {$_SESSION['username']} đã đăng bình luận vào bài viết ID: $post_id với nội dung: $formatted_content");

        }

        // Chuyển hướng sau khi bình luận để ngăn việc gửi form lặp lại
        header("Location: view.php?id=$postId");
        exit();
    }

    // Xóa bình luận
    if ($isLoggedIn && isset($_GET['delete_comment'])) {
        $commentId = intval($_GET['delete_comment']);  // Kiểm tra và bảo vệ id

 // Bước 1: Lấy thông tin bình luận sẽ xóa (nội dung)
$stmt_get_comment = $conn->prepare("SELECT content FROM comments WHERE id = ? AND username = ?");
$stmt_get_comment->bind_param("is", $commentId, $_SESSION['username']);
$stmt_get_comment->execute();
$result = $stmt_get_comment->get_result();

if ($row = $result->fetch_assoc()) {
    $deleted_content = $row['content'];
} else {
    // Nếu không tìm thấy bình luận hoặc xảy ra lỗi, có thể gán giá trị mặc định hoặc xử lý lỗi theo yêu cầu
    $deleted_content = "[Không lấy được nội dung bình luận]";
}

// Bước 2: Xóa bình luận sử dụng prepared statements
$stmt_delete_comment = $conn->prepare("DELETE FROM comments WHERE id = ? AND username = ?");
$stmt_delete_comment->bind_param("is", $commentId, $_SESSION['username']);
$stmt_delete_comment->execute();

// Lấy thời gian hiện tại theo định dạng dd/mm/yyyy | hh:mm:ss
$datetime = date("d/m/Y | H:i:s");

// Ghi log với định dạng: 
// [dd/mm/yyyy | hh:mm:ss] Người dùng: [tên user] đã xóa bình luận ID: [commentId] với nội dung: [deleted_content]
logAction("[$datetime] Người dùng: {$_SESSION['username']} đã xóa bình luận ID: $commentId với nội dung: $deleted_content");

        // Chuyển hướng để làm mới trang
        header("Location: view.php?id=$postId");
        exit();
    }

    // Xử lý xóa bài viết
    if ($isOwner && isset($_GET['delete_post'])) {
      // Bước 1: Lấy thông tin bài viết sẽ xóa (nội dung)
$stmt_get_post = $conn->prepare("SELECT content FROM posts WHERE id = ?");
$stmt_get_post->bind_param("i", $postId);
$stmt_get_post->execute();
$result = $stmt_get_post->get_result();

if ($row = $result->fetch_assoc()) {
    $deleted_content = $row['content'];
} else {
    // Nếu không tìm thấy bài viết hoặc xảy ra lỗi, gán giá trị mặc định
    $deleted_content = "[Không lấy được nội dung bài viết]";
}

// Bước 2: Xóa bài viết sử dụng prepared statements
$stmt_delete_post = $conn->prepare("DELETE FROM posts WHERE id = ?");
$stmt_delete_post->bind_param("i", $postId);
$stmt_delete_post->execute();

// Lấy thời gian hiện tại theo định dạng dd/mm/yyyy | hh:mm:ss
$datetime = date("d/m/Y | H:i:s");

// Ghi log với định dạng: 
// [dd/mm/yyyy | hh:mm:ss] Người dùng: [tên user] đã xóa bài viết ID: [postId] với nội dung: [deleted_content]
logAction("[$datetime] Người dùng: {$_SESSION['username']} đã xóa bài viết ID: $postId với nội dung: $deleted_content");

        header("Location: index.php"); // Quay lại trang chủ sau khi xóa
        exit;
    }

} else {
    header('Location: ../index.php');
    exit;
}

?>
