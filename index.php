<?php
session_start(); // Đảm bảo session đã được bắt đầu
include('config.php'); // Bao gồm file config để kết nối DB

// Kiểm tra xem file config.php có tồn tại không
if (!file_exists('config.php')) {
    // Nếu không tồn tại, chuyển hướng đến trang setup.php
    header('Location: setup.php');
    exit; // Đảm bảo không có mã nào được thực thi sau khi chuyển hướng
}

// Kiểm tra trạng thái cấm trước khi cho phép truy cập vào index.php
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;
$ip_address = $_SERVER['REMOTE_ADDR'];

// Kiểm tra nếu username đã đăng nhập
if ($username) {
    // Chuẩn bị câu truy vấn kiểm tra xem có bản ghi nào trong bảng bans với username hoặc ip_address
    $stmt = $conn->prepare("SELECT * FROM bans WHERE username = ? OR ip_address = ?");
    $stmt->bind_param("ss", $username, $ip_address); // Liên kết biến với câu truy vấn
    $stmt->execute(); // Thực thi câu truy vấn
    $result = $stmt->get_result(); // Lấy kết quả từ câu truy vấn

    // Kiểm tra nếu có bản ghi nào (người dùng bị cấm)
    if ($result->num_rows > 0) {
        // Nếu bị cấm, chuyển hướng đến warning.php
        header("Location: warning.php");
        exit(); // Dừng mọi mã PHP còn lại
    }
}

// Bao gồm tệp index.php sau khi kiểm tra cấm xong
include('index/php.php');
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Forum</title>
    <link rel="icon" href="favicon.png" type="image/png">
    <link rel="stylesheet" type="text/css" href="index/styles.css">

   <script src = index/Toogle.js></script>
    
  <script src = index/Refersh.js></script>
<script src = index/Spoil.js></script>
</head>
<body>
   <div id="mobile-warning">
        Vui lòng bật chế độ xem trên máy tính
    </div>

    <script src = index/Size.js></script>
<div class="container">
    <h1>Forum</h1>
    <?php if (!isset($_SESSION['username'])): ?>
        <!-- Hiển thị form nếu chưa đăng nhập -->
        <form id="login-form" method="post" action="index.php" style="display: block;">
            <h2>Đăng nhập</h2>
            <input type="text" name="username" placeholder="Tên đăng nhập" required maxlength="50">
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <button type="submit" name="login">Đăng nhập</button>
            <p>Chưa có tài khoản? <span class="toggle-link" style="color: red;"  onclick="toggleForms()">Đăng ký</span></p>
        </form>
        <form id="register-form" method="post" action="index.php" style="display: none;">
        <h2>Đăng ký</h2>
<form id="registrationForm">
    <input type="text" name="username" placeholder="Tên đăng nhập" required pattern="^[a-zA-Z0-9]{5,30}$"
        title="Vui lòng chỉ nhập ký tự chữ và số không dấu và không có khoảng trắng hoặc ký tự đặc biệt. Nhập từ 5 đến 30 ký tự.">
    <input type="password" name="password" placeholder="Mật khẩu" required 
        minlength="6" maxlength="30" 
        pattern="^[a-zA-Z0-9]{6,30}$"
        title="Vui lòng chỉ nhập ký tự chữ và số, không có khoảng trắng hoặc ký tự đặc biệt. Nhập từ 6 đến 30 ký tự.">
    <input type="password" name="confirm_password" placeholder="Nhập lại mật khẩu" required>
    
    <!-- Checkbox và liên kết -->
    <label>
        <input type="checkbox" id="agreeCheckbox"> 
        Bằng cách nhấn vào nút này, bạn đồng ý <a href="tos.html" target="_blank"><strong>Điều khoản dịch vụ</strong><b>.</b></a> <br>
    </label>
    
    <!-- Nút đăng ký mặc định xám và có hiệu ứng chuyển màu -->
    <button type="submit" name="register" id="registerBtn" disabled style="background-color: #9e9e9e;">Đăng ký</button>
 <p>Đã có tài khoản? <span class="toggle-link" style="color: red;" onclick="toggleForms()">Đăng nhập</span></p></form>


<script src = index/checkBox.js></script>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error"><?php echo $_SESSION['error']; ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="success"><?php echo $_SESSION['success']; ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
        </form>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
    <?php else: ?>
        <!-- Hiển thị form đăng bài nếu đã đăng nhập -->
   <form action="index.php" method="POST" enctype="multipart/form-data">
    <h2>Đăng bài viết</h2>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <textarea name="content" placeholder="Nội dung bài viết" required maxlength="200"></textarea>
    <input type="text" name="description" placeholder="Mô tả ngắn" required maxlength="500">
    
    <button type="submit" name="post">Đăng bài</button>
</form>
</head>
<body>

<button id="optionsBtn">Tùy chọn</button>

<div id="optionsMenu" class="dropdown-content">
    <a href="info_user.php"><i class="fas fa-user"></i> Thông Tin</a>
    <a href="network-config.php"><i class="fas fa-network-wired"></i> Cấu Hình IP</a>
    <a href="tos.html"><i class="fas fa-file-contract"></i> Điều khoản dịch vụ</a>
    <a href="index.php?logout=true"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
</div>
<script src = index/taskBar.js></script>
<?php
echo "<div class='pagination'>";

// Liên kết đến section đầu tiên
if ($current_section > 1) {
    echo "<a href='index.php?section=1'>&lt;&lt;</a> ";
}

// Liên kết đến section trước
if ($current_section > 1) {
    echo "<a href='index.php?section=" . ($current_section - 1) . "'>&lt;</a> ";
}

// Hiển thị các liên kết section gần với section hiện tại
$range = 7; // Số section hiển thị xung quanh section hiện tại
for ($i = max(1, $current_section - $range); $i <= min($total_sections, $current_section + $range); $i++) {
    if ($i == $current_section) {
        echo "<strong>$i</strong> "; // Đánh dấu section hiện tại
    } else {
        echo "<a href='index.php?section=$i'>$i</a> ";
    }
}

// Liên kết đến section tiếp theo
if ($current_section < $total_sections) {
    echo "<a href='index.php?section=" . ($current_section + 1) . "'>&gt;</a> ";
}

// Liên kết đến section cuối cùng
if ($current_section < $total_sections) {
    echo "<a href='index.php?section=$total_sections'>&gt;&gt;</a>";
}

echo "</div>";
 
?>
        <h2>Các bài viết</h2>
        <?php if ($posts->num_rows > 0): ?>
            <?php while ($post = $posts->fetch_assoc()): ?>
      <div class="post">
    <h3><?php echo formatText($post['content']); ?></h3> <!-- Sử dụng formatText để định dạng nội dung -->
    <p><?php echo htmlspecialchars($post['description']); ?></p>
    <!-- Hiển thị liên kết tải xuống nếu có tệp tin -->
    <?php if ($post['file']): ?>
        <p>Tệp đính kèm: <a href="uploads/<?php echo htmlspecialchars($post['file']); ?>" download><?php echo htmlspecialchars($post['file']); ?></a></p>
    <?php endif; ?>
    <?php
        // Định dạng ngày tháng
        $createdAt = DateTime::createFromFormat('Y-m-d H:i:s', $post['created_at']);
        if ($createdAt) {
            $formattedDate = $createdAt->format('d/n/Y | H:i:s');
        } else {
            $formattedDate = 'Ngày không hợp lệ'; // hoặc một giá trị mặc định khác
        }
    ?>
    <small>Đăng bởi: <?php echo htmlspecialchars($post['username']); ?> vào <?php echo $formattedDate; ?></small>
    <?php if ($post['username'] == $_SESSION['username']): ?>
        <form method="get" action="index.php" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này không?');">
            <input type="hidden" name="delete" value="<?php echo $post['id']; ?>">
            <button type="submit" class="delete-button">Xóa bài viết</button>
        </form>
    <?php endif; ?>
    
    <!-- Nút hiện/ẩn bình luận -->
    <button class="toggle-comments" data-post-id="<?php echo $post['id']; ?>">Hiện bình luận</button>
    <div class="comments" id="comments-<?php echo $post['id']; ?>" style="display: none;">
        <h4>Bình luận:</h4>
        <form method="post" action="index.php" class="comment-form">
            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
            <textarea name="content" placeholder="Nhập bình luận" required></textarea>
            <button type="submit" name="comment">Gửi bình luận</button>
        </form>
        <?php
        $post_id = $post['id'];
        $comments = $conn->query("SELECT * FROM comments WHERE post_id = $post_id ORDER BY created_at DESC");
        if ($comments->num_rows > 0):
            while ($comment = $comments->fetch_assoc()): ?>
                <div class="comment">
                    <strong><?php echo htmlspecialchars($comment['username']); ?></strong>: 
                    <span><?php echo $comment['content']; ?></span> <!-- Xóa htmlspecialchars ở đây -->
                    <?php if ($comment['username'] == $_SESSION['username']): ?>
                        <a href="index.php?delete_comment=<?php echo $comment['id']; ?>">Xóa bình luận</a>
                    <?php endif; ?>
                </div>
        <?php endwhile; ?>
        <?php else: ?>
            <p class="no-posts">Chưa có bình luận nào.</p>
        <?php endif; ?>
    </div>
</div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-posts">Chưa có bài viết nào.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>
<script>
    function toggleComments(postId) {
    var commentsDiv = document.getElementById('comments-' + postId);
    var button = document.querySelector('.toggle-comments[data-post-id="' + postId + '"]');
    
    // Kiểm tra trạng thái hiện tại của phần bình luận
    if (commentsDiv.style.display === 'block') {
        let height = commentsDiv.scrollHeight;

        commentsDiv.animate([{ height: height + 'px' }, { height: '0' }], {
            duration: 100,
            fill: 'forwards'
        }).onfinish = function () {
            commentsDiv.style.display = 'none';
            button.textContent = 'Hiện bình luận'; // Cập nhật nút sau khi ẩn
            localStorage.setItem('commentsVisible-' + postId, 'false'); // Cập nhật trạng thái
        };
    } else {
        commentsDiv.style.display = 'block'; // Hiện phần bình luận
        commentsDiv.style.height = '0'; // Đặt chiều cao ban đầu
        let height = commentsDiv.scrollHeight; // Lấy chiều cao thực

        commentsDiv.animate([{ height: '0' }, { height: height + 'px' }], {
            duration: 100,
            fill: 'forwards'
        }).onfinish = function () {
            button.textContent = 'Ẩn bình luận'; // Cập nhật nút sau khi hiện
            localStorage.setItem('commentsVisible-' + postId, 'true'); // Cập nhật trạng thái
        };
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const toggleButtons = document.querySelectorAll('.toggle-comments');

    toggleButtons.forEach(function (button) {
        const postId = button.getAttribute('data-post-id');
        const commentsSection = document.getElementById('comments-' + postId);

        // Kiểm tra trạng thái trong localStorage
        if (localStorage.getItem('commentsVisible-' + postId) === 'true') {
            commentsSection.style.display = 'block';
            commentsSection.style.height = commentsSection.scrollHeight + 'px'; // Đặt chiều cao cho bình luận
            button.textContent = 'Ẩn bình luận';
        } else {
            commentsSection.style.display = 'none';
            commentsSection.style.height = '0'; // Đặt chiều cao cho bình luận khi ẩn
            button.textContent = 'Hiện bình luận';
        }

        // Gắn sự kiện click
        button.addEventListener('click', function () {
            toggleComments(postId);
        });
    });
});
</script>
</body>
</html>