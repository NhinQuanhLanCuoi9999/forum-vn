<?php
session_start();
include('../config.php');
include('../app/admin/logicPHP/Auth.php');

if (!function_exists('logAction')) {
    function logAction($message) {
        error_log($message);
    }
}

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    die("Bạn không có quyền truy cập trang này.");
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
$session_role = $_SESSION['role'];

// Biến lưu thông báo để hiển thị
$message = '';

// Xử lý DELETE user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf_token) {
        $message = '<div class="alert alert-danger">Lỗi xác thực. Vui lòng thử lại sau.</div>';
    } else {
        $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $user_id)) {
            $message = '<div class="alert alert-danger">ID người dùng không hợp lệ.</div>';
        } else {
            $stmt = $conn->prepare("SELECT username, role FROM users WHERE id = ?");
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                // Kiểm tra nếu user cần xóa là admin/owner
                if ($user['role'] === 'admin' || $user['role'] === 'owner') {
                    if ($session_role === 'admin' || ($session_role === 'owner' && $user['role'] === 'owner')) {
                        $message = '<div class="alert alert-danger">Bạn không có quyền xóa tài khoản này.</div>';
                    } else {
                        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                        $stmt->bind_param("s", $user_id);
                        $stmt->execute();
                        logAction("Đã xóa tài khoản '{$user['username']}' thành công.");
                        $message = "<div class='alert alert-success'>Đã xóa tài khoản '{$user['username']}' thành công.</div>";
                    }
                } else {
                    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->bind_param("s", $user_id);
                    $stmt->execute();
                    logAction("Đã xóa tài khoản '{$user['username']}' thành công.");
                    $message = "<div class='alert alert-success'>Đã xóa tài khoản '{$user['username']}' thành công.</div>";
                }
            } else {
                $message = '<div class="alert alert-danger">Người dùng không tồn tại.</div>';
            }
        }
    }
}

// Xử lý EDIT user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf_token) {
        $message = '<div class="alert alert-danger">Lỗi xác thực. Vui lòng thử lại sau.</div>';
    } else {
        $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $user_id)) {
            $message = '<div class="alert alert-danger">ID người dùng không hợp lệ.</div>';
        } else {
            $new_username = filter_input(INPUT_POST, 'new_username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $new_username = trim($new_username);
            if (!preg_match('/^[a-zA-Z0-9]+$/', $new_username)) {
                $message = '<div class="alert alert-danger">Tên người dùng chỉ được chứa chữ cái và số.</div>';
            } else {
                $stmt = $conn->prepare("SELECT username, role FROM users WHERE id = ?");
                $stmt->bind_param("s", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    $message = '<div class="alert alert-danger">Người dùng không tồn tại.</div>';
                } else {
                    $user = $result->fetch_assoc();
                    if ($user['role'] === 'admin' || $user['role'] === 'owner') {
                        if ($session_role === 'admin' || ($session_role === 'owner' && $user['role'] === 'owner')) {
                            $message = '<div class="alert alert-danger">Bạn không có quyền chỉnh sửa tài khoản này.</div>';
                        } else {
                            // Kiểm tra tên người dùng hợp lệ
                            if (empty($new_username) || strlen($new_username) < 6 || strtolower($new_username) === 'admin') {
                                $message = '<div class="alert alert-danger">Tên người dùng không hợp lệ.</div>';
                            } else {
                                $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                                $stmt->bind_param("s", $new_username);
                                $stmt->execute();
                                $count_result = $stmt->get_result();
                                $count = $count_result->fetch_row()[0];

                                if ($count > 0) {
                                    $message = '<div class="alert alert-danger">Tên đã bị trùng.</div>';
                                } else {
                                    try {
                                        $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
                                        $stmt->bind_param("ss", $new_username, $user_id);
                                        $stmt->execute();

                                        if ($stmt->affected_rows === 0) {
                                            throw new Exception("Không có thay đổi nào được thực hiện.");
                                        }

                                        $tables = ['posts', 'comments', 'bans'];
                                        foreach ($tables as $table) {
                                            $stmt = $conn->prepare("UPDATE $table SET username = ? WHERE username = ?");
                                            $stmt->bind_param("ss", $new_username, $user['username']);
                                            $stmt->execute();
                                        }
                                        $message = "<div class='alert alert-success'>Đã đổi tên tài khoản '{$user['username']}' thành '$new_username' thành công.</div>";
                                        logAction("Đã đổi tên tài khoản '{$user['username']}' thành '$new_username' thành công.");
                                    } catch (Exception $e) {
                                        $message = '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
                                    }
                                }
                            }
                        }
                    } else {
                        // Nếu user không phải admin/owner
                        if (empty($new_username) || strlen($new_username) < 6 || strtolower($new_username) === 'admin') {
                            $message = '<div class="alert alert-danger">Tên người dùng không hợp lệ.</div>';
                        } else {
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                            $stmt->bind_param("s", $new_username);
                            $stmt->execute();
                            $count_result = $stmt->get_result();
                            $count = $count_result->fetch_row()[0];

                            if ($count > 0) {
                                $message = '<div class="alert alert-danger">Tên đã bị trùng.</div>';
                            } else {
                                try {
                                    $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
                                    $stmt->bind_param("ss", $new_username, $user_id);
                                    $stmt->execute();

                                    if ($stmt->affected_rows === 0) {
                                        throw new Exception("Không có thay đổi nào được thực hiện.");
                                    }

                                    $tables = ['posts', 'comments', 'bans'];
                                    foreach ($tables as $table) {
                                        $stmt = $conn->prepare("UPDATE $table SET username = ? WHERE username = ?");
                                        $stmt->bind_param("ss", $new_username, $user['username']);
                                        $stmt->execute();
                                    }
                                    $message = "<div class='alert alert-success'>Đã đổi tên tài khoản '{$user['username']}' thành '$new_username' thành công.</div>";
                                    logAction("Đã đổi tên tài khoản '{$user['username']}' thành '$new_username' thành công.");
                                } catch (Exception $e) {
                                    $message = '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

// Xử lý tìm kiếm & phân trang (vẫn dùng POST)
$search_term = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_submit'])) {
    $search_term = trim($_POST['search']);
}

$limit = 6;
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$offset = ($page - 1) * $limit;
$total_pages = 0;

if (!empty($search_term)) {
    $search_param = "%" . $conn->real_escape_string($search_term) . "%";
    $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE username LIKE ?");
    if (!$count_stmt) {
        die("Lỗi prepare (COUNT search): " . $conn->error);
    }
    $count_stmt->bind_param("s", $search_param);
    if (!$count_stmt->execute()) {
        die("Lỗi execute (COUNT search): " . $count_stmt->error);
    }
    $count_result = $count_stmt->get_result();
    $total_users = $count_result->fetch_assoc()['count'];
    $total_pages = ceil($total_users / $limit);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username LIKE ? LIMIT ? OFFSET ?");
    if (!$stmt) {
        die("Lỗi prepare (SELECT search): " . $conn->error);
    }
    $stmt->bind_param("sii", $search_param, $limit, $offset);
    if (!$stmt->execute()) {
        die("Lỗi execute (SELECT search): " . $stmt->error);
    }
    $users_result = $stmt->get_result();
} else {
    $total_users_result = $conn->query("SELECT COUNT(*) as count FROM users");
    if (!$total_users_result) {
        die("Lỗi query (COUNT all users): " . $conn->error);
    }
    $total_users = $total_users_result->fetch_assoc()['count'];
    $total_pages = ceil($total_users / $limit);

    $users_query = "SELECT * FROM users LIMIT $limit OFFSET $offset";
    $users_result = $conn->query($users_query);
    if (!$users_result) {
        die("Lỗi query (SELECT all users): " . $conn->error);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý người dùng</title>
  <!-- Thêm Bootstrap CSS -->
  <link href="/asset/css/Bootstrap.min.css" rel="stylesheet">
</head>
<body>
 <!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">Quản lý người dùng</a>

    <!-- Nút "Về trang Admin" -->
    <a href="/admin_tool/admin.php" class="btn btn-outline-light ms-auto">Về trang Admin</a>
  </div>
</nav>

  <div class="container my-4">
    <h2 class="mb-4">Quản lý người dùng</h2>
    
    <!-- In thông báo nếu có -->
    <?php if (!empty($message)) {
      echo $message;
    } ?>

    <!-- Form tìm kiếm -->
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="mb-4">
      <div class="input-group">
        <input type="text" name="search" class="form-control" placeholder="Tìm kiếm người dùng" value="<?php echo htmlspecialchars($search_term); ?>">
        <button type="submit" name="search_submit" class="btn btn-primary">Tìm kiếm</button>
      </div>
    </form>

    <!-- Danh sách người dùng -->
    <div class="user-list">
      <?php if ($users_result && $users_result->num_rows > 0): ?>
        <?php while ($user = $users_result->fetch_assoc()): ?>
          <div class="card mb-3">
            <div class="card-body d-flex justify-content-between align-items-center">
              <span><?php echo htmlspecialchars($user['username']); ?></span>
              <div>
                <!-- Form chỉnh sửa -->
                <form method="POST" class="d-inline-block">
                  <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                  <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                  <input type="text" name="new_username" class="form-control d-inline-block" style="width: auto;" placeholder="Tên mới" required pattern="^[a-zA-Z0-9]+$">
                  <button type="submit" name="edit_user" class="btn btn-warning btn-sm ms-2">Chỉnh sửa</button>
                </form>
                <!-- Form xóa -->
                <form method="POST" class="d-inline-block ms-2" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài khoản này không?');">
                  <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                  <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                  <button type="submit" name="delete_user" class="btn btn-danger btn-sm">Xóa</button>
                </form>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-center">Chưa có người dùng nào.</p>
      <?php endif; ?>
    </div>

    <!-- Phân trang qua POST -->
    <div class="d-flex justify-content-center">
      <?php if ($page > 1): ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="d-inline-block me-2">
          <?php if (!empty($search_term)): ?>
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
            <input type="hidden" name="search_submit" value="1">
          <?php endif; ?>
          <input type="hidden" name="page" value="<?php echo $page - 1; ?>">
          <button type="submit" class="btn btn-secondary">&lt;&lt; Trước</button>
        </form>
      <?php endif; ?>

      <?php
        $start = max(1, $page - 2);
        $end = min($total_pages, $page + 2);
        for ($i = $start; $i <= $end; $i++):
      ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="d-inline-block me-2">
          <?php if (!empty($search_term)): ?>
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
            <input type="hidden" name="search_submit" value="1">
          <?php endif; ?>
          <input type="hidden" name="page" value="<?php echo $i; ?>">
          <button type="submit" class="btn <?php echo ($i == $page) ? 'btn-primary' : 'btn-outline-secondary'; ?>"><?php echo $i; ?></button>
        </form>
      <?php endfor; ?>

      <?php if ($page < $total_pages): ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="d-inline-block">
          <?php if (!empty($search_term)): ?>
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
            <input type="hidden" name="search_submit" value="1">
          <?php endif; ?>
          <input type="hidden" name="page" value="<?php echo $page + 1; ?>">
          <button type="submit" class="btn btn-secondary">Tiếp theo &gt;&gt;</button>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <!-- Bootstrap Bundle JS (bao gồm Popper) -->
  <script src="/asset/js/Bootstrap.bundle.min.js"></script>
</body>
</html>
