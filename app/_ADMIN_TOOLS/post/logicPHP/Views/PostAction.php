<?php
// Xử lý toggle bình luận
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle'])) {
    session_start();

    $post_id = intval($_POST['toggle']);
    $currentUser = $_SESSION['username'];
    $currentUserRole = $_SESSION['role'];

    $sql = "SELECT status, username FROM posts WHERE id = $post_id";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $currentStatus = $row['status'];
        $postUsername = $row['username'];

        // Lấy role của người tạo bài viết
        $authorSql = "SELECT role FROM users WHERE username = '$postUsername'";
        $authorResult = mysqli_query($conn, $authorSql);
        $authorRole = 'member';

        if ($authorResult && mysqli_num_rows($authorResult) > 0) {
            $authorRow = mysqli_fetch_assoc($authorResult);
            $authorRole = $authorRow['role'];
        }

        // Xử lý quyền chỉnh sửa:
        $canEdit = false;

        if ($currentUserRole === 'owner') {
            $canEdit = true; // owner chỉnh all
        } elseif ($currentUserRole === 'admin') {
            if ($authorRole !== 'owner' && $postUsername !== $currentUser) {
                $canEdit = true; // admin chỉnh bài của member khác hoặc admin khác (trừ owner)
            } elseif ($postUsername === $currentUser) {
                $canEdit = true; // admin chỉnh bài của chính nó
            }
        } elseif ($currentUserRole === 'member') {
            // member KHÔNG được chỉnh bất cứ bài nào
            $canEdit = false;
        }

        if ($canEdit) {
            $newStatus = ($currentStatus == '2') ? '0' : '2';
            $updateSql = "UPDATE posts SET status = '$newStatus' WHERE id = $post_id";
            mysqli_query($conn, $updateSql);

            $_SESSION['alert'] = $newStatus == '2'
                ? '<div class="alert alert-success alert-dismissible fade show" role="alert">Bình luận đã được tắt.</div>'
                : '<div class="alert alert-success alert-dismissible fade show" role="alert">Bình luận đã được bật.</div>';
        } else {
            $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Bạn không có quyền chỉnh sửa bài này.</div>';
        }

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    } else {
        $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Bài đăng không tồn tại!</div>';
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}

?>
<?php
function renderPostActions($row) {
    ob_start();
    ?>
    <div class="dropdown mb-2">
        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton<?= $row['id'] ?>" data-bs-toggle="dropdown" aria-expanded="false">
            Hành động
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?= $row['id'] ?>">
            <li>
                <a class="dropdown-item" href="/src/view.php?id=<?= $row['id'] ?>">Xem bài đăng</a>
            </li>
            <li>
                <a class="dropdown-item" data-bs-toggle="collapse" href="#collapseComments<?= $row['id'] ?>" role="button" aria-expanded="false" aria-controls="collapseComments<?= $row['id'] ?>">
                    Xem Bình Luận
                </a>
            </li>
            <?php if ($_SESSION['role'] === 'owner' || ($_SESSION['role'] === 'admin' && ($row['role'] ?? 'member') === 'member')): ?>
                <li>
                    <!-- Form POST để xóa bài đăng -->
                    <form method="POST" action="" style="display:inline;">
                        <input type="hidden" name="delete" value="<?= $row['id'] ?>"> <!-- ID của bài đăng cần xóa -->
                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa bài đăng này không?');">
                            Xóa
                        </button>
                    </form>
                </li>
            <?php endif; ?>
            <li>
                <!-- Form POST để toggle bình luận -->
                <form method="POST" action="" style="display:inline;">
                    <input type="hidden" name="toggle" value="<?= $row['id'] ?>">
                    <button type="submit" class="dropdown-item">
                        <?= ($row['status'] == 2) ? 'Bật bình luận' : 'Tắt bình luận'; ?>
                    </button>
                </form>
            </li>
        </ul>
    </div>
    <?php
    return ob_get_clean();
}
?>
