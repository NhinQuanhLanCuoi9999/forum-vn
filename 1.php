<?php
session_start();
include('config.php');
include('index\php.php')
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
    <link rel="stylesheet" type="text/css" href="styles.css">
 <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(90deg, lavender, lightcyan); /* Gradient từ lavender sang lightcyan */
        margin: 0;
        padding: 0;
    }

    h1, h2 {
        color: #333;
        text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3); /* Thêm bóng cho tiêu đề */
        text-transform: uppercase; /* Chuyển thành chữ in hoa */
    }

    .container {
        width: 95%;
        margin: auto;
        overflow: hidden;
        border: 2px solid #ddd; /* Thêm viền cho container */
        padding: 20px; /* Thêm padding */
        background: rgba(255, 255, 255, 0.9); /* Nền trắng mờ */
        border-radius: 15px; /* Bo góc */
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2); /* Đổ bóng */
    }

    form {
        background: #fff;
        padding: 30px;
        margin: 30px 0;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
    }

    input[type="text"],
    input[type="password"],
    textarea {
        width: 100%;
        padding: 15px;
        margin: 15px 0;
        border: 2px solid #ccc;
        border-radius: 10px;
        font-size: 16px;
        box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.1); /* Đổ bóng vào bên trong */
    }

    button {
        background: #5cb85c;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 10px;
        font-size: 18px;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.3s; /* Thêm hiệu ứng chuyển màu và phóng to */
    }

    button:hover {
        background: #4cae4c;
        transform: scale(1.05); /* Phóng to khi hover */
    }

    .error {
        color: red;
        font-weight: bold;
        margin: 15px 0;
        text-transform: uppercase; /* Chuyển thành chữ in hoa */
    }

    .success {
        color: green;
        margin: 15px 0;
        font-size: 18px;
        font-style: italic; /* In nghiêng */
    }

    .spoil {
        background-color: #333;
        color: #333;
        padding: 10px;
        cursor: pointer;
        border-radius: 10px;
        transition: color 0.5s ease, opacity 0.5s ease;
        opacity: 1;
        font-weight: bold; /* In đậm */
    }

    .spoil.open {
        color: #fff;
        opacity: 0.3;
    }

    .post {
        background: linear-gradient(to bottom right, #e0f7fa, #b2ebf2); /* Gradient từ màu xanh biển rất nhạt */
        padding: 15px;
        margin: 15px 0;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    }

    .comment {
        background: linear-gradient(to bottom right, #fffde7, #fff9c4); /* Gradient từ màu vàng nhạt */
        padding: 10px;
        margin: 10px 0;
        border-radius: 5px;
    }


.admin-button {
    background-color: #FFD700;
    color: black; /* Màu chữ */
    font-family: 'Poppins', sans-serif;
    padding: 15px 25px; /* Padding lớn hơn */
    font-size: 18px; /* Kích thước chữ lớn hơn */
    margin: 10px; /* Margin */
    cursor: pointer; /* Con trỏ chuột */
    border: none; /* Không viền */
    border-radius: 20px; /* Bo góc nhiều hơn */
    transition: background-color 0.3s, transform 0.3s, box-shadow 0.3s; /* Thêm hiệu ứng chuyển đổi cho box-shadow và transform */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Hiệu ứng đổ bóng */
    display: flex; /* Sử dụng flexbox */
    justify-content: center;
    align-items: center;
}

.admin-button:hover {
    background-color: #45a049; /* Màu khi hover */
    transform: scale(1.1); /* Tăng kích thước khi hover */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3); /* Bóng đổ đậm hơn khi hover */
}

    .alert {
        padding: 10px;
        margin-bottom: 20px;
        border: 2px solid transparent;
        border-radius: 10px;
        background-color: rgba(255, 0, 0, 0.8); /* Nền đỏ mờ */
        color: white;
        font-size: 18px;
    }

    .delete-button {
        background-color: darkred;
        color: white;
        border: none;
        padding: 12px 25px;
        font-size: 18px;
        cursor: pointer;
        border-radius: 10px;
        transition: background-color 0.3s, transform 0.3s;
    }

    .delete-button:hover {
        background-color: red;
        transform: scale(1.05);
    }

    .comments-container {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.5s ease-out;
    }

    .comments-container.open {
        max-height: 500px;
    }
#mobile-warning {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: white; /* Nền đỏ hoàn toàn */
  z-index: 3000;
  display: flex;
  justify-content: center;
  align-items: center;
}

#mobile-warning .content {
  position: relative;
  background-color: transparent;
  color: white; /* Màu chữ */
  z-index: 3001; /* Đảm bảo chữ hiển thị trên nền */
}
</style>
   <script>
        function toggleForms() {
            const loginForm = document.getElementById('login-form');
            const registerForm = document.getElementById('register-form');
            
            // Toggle the display of the forms
            if (loginForm.style.display === 'none') {
                loginForm.style.display = 'block';
                registerForm.style.display = 'none';
            } else {
                loginForm.style.display = 'none';
                registerForm.style.display = 'block';
            }
        }
    </script>
  <script>
    let isFormFocused = false;
let isFormFilled = false;
let isRefreshing = true;

function toggleForms() {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    
    // Toggle the display of the forms
    if (loginForm.style.display === 'none') {
        loginForm.style.display = 'block';
        registerForm.style.display = 'none';
    } else {
        loginForm.style.display = 'none';
        registerForm.style.display = 'block';
    }
}

// Không làm mới trang khi quét khối
document.addEventListener('selectionchange', () => {
    if (document.getSelection().toString()) {
        isRefreshing = false; // Ngừng refresh khi có lựa chọn
    } else {
        isRefreshing = true; // Bắt đầu refresh lại khi không có lựa chọn
    }
});

// Biến http:// hoặc https:// thành liên kết
document.addEventListener('DOMContentLoaded', () => {
    const posts = document.querySelectorAll('.post');
    posts.forEach(post => {
        const content = post.querySelector('h3');
        content.innerHTML = convertLinks(content.innerHTML);
    });
});

function convertLinks(text) {
    return text.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank">$1</a>');
}

// Kiểm tra khi form đang được focus (đang có con trỏ trong form)
document.addEventListener('focusin', function (event) {
    if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') {
        isFormFocused = true;
    }
});

// Kiểm tra khi form không còn được focus
document.addEventListener('focusout', function (event) {
    if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') {
        isFormFocused = false;
    }
});

// Kiểm tra nếu form đã có ký tự
document.addEventListener('input', function (event) {
    const inputs = document.querySelectorAll('input[type="text"], textarea');
    isFormFilled = Array.from(inputs).some(input => input.value.trim() !== '');
});

// Refresh trang mỗi 10 giây, trừ khi đang nhập liệu
setInterval(function () {
    if (isRefreshing && !isFormFocused && !isFormFilled) {
        location.reload();
    }
}, 10000); // 10000 milliseconds = 10 seconds
</script>
<script>
// Hàm để mở/đóng spoil block
function toggleSpoiler(element, id) {
    let isRevealed = localStorage.getItem(id) === 'true';
    if (isRevealed) {
        // Đóng spoil block
        element.style.color = '#333';
        localStorage.setItem(id, 'false');
    } else {
        // Mở spoil block
        element.style.color = '#fff';
        localStorage.setItem(id, 'true');
    }
}

// Khi trang tải lại, kiểm tra trạng thái của spoil block từ localStorage
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.spoil').forEach(function (element) {
        let id = element.id;
        let isRevealed = localStorage.getItem(id) === 'true';
        if (isRevealed) {
            element.style.color = '#fff'; // Mở spoil block
        }
    });
});
</script>
</head>
<body>
   <div id="mobile-warning">
        Vui lòng bật chế độ xem trên máy tính
    </div>

    <script>
        // Kiểm tra kích thước màn hình
        function checkScreenSize() {
            const warning = document.getElementById('mobile-warning');
            if (window.innerWidth < 768) { // Nếu màn hình nhỏ hơn 768px
                warning.style.display = 'flex'; // Hiện thông báo
                document.body.classList.add('no-scroll'); // Ngăn cuộn trang
            } else {
                warning.style.display = 'none'; // Ẩn thông báo
                document.body.classList.remove('no-scroll'); // Cho phép cuộn trang
            }
        }

        // Gọi hàm khi trang được tải, khi kích thước màn hình thay đổi,
        // và khi thay đổi hướng màn hình
        window.onload = checkScreenSize;
        window.onresize = checkScreenSize;
        window.orientationchange = checkScreenSize; // Kiểm tra hướng màn hình
    </script>
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

<style>
    /* Thêm hiệu ứng chuyển màu */
    #registerBtn {
        transition: background-color 0.3s ease, opacity 0.3s ease;
        opacity: 0.7; /* Mờ dần khi không thể bấm */
    }
    
    #registerBtn:enabled {
        opacity: 1; /* Đậm lên khi bật */
    }
</style>

<script>
    const agreeCheckbox = document.getElementById('agreeCheckbox');
    const registerBtn = document.getElementById('registerBtn');
    const registrationForm = document.getElementById('registrationForm');

    // Xử lý checkbox và nút đăng ký
    agreeCheckbox.addEventListener('change', function() {
        if (this.checked) {
            registerBtn.style.backgroundColor = '#4CAF50';  // Màu khi checkbox được chọn
            registerBtn.disabled = false;
        } else {
            registerBtn.style.backgroundColor = '#9e9e9e';  // Màu khi checkbox chưa chọn
            registerBtn.disabled = true;
        }
    });

    // Kiểm tra trước khi submit
    registrationForm.addEventListener('submit', function(event) {
        if (!agreeCheckbox.checked) {
            event.preventDefault();  // Ngừng submit form
            alert("Bạn chưa tick vào checkbox.");
        }
    });
</script>
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
   <style>
        /* Định dạng cho menu */
        #optionsMenu {
            display: block;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            opacity: 0;
            pointer-events: none; /* Vô hiệu hóa sự kiện nhấp chuột khi menu ẩn */
            transition: opacity 0.5s ease, max-height 0.5s ease;
            max-height: 0;
        }

        /* Định dạng cho từng tùy chọn */
        #optionsMenu a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        #optionsMenu a:hover {
            background-color: #f1f1f1;
        }

        /* Định dạng cho nút */
        #optionsBtn {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            font-size: 16px;
            border: none;
            cursor: pointer;
        }

        #optionsBtn:hover {
            background-color: #3e8e41;
        }
        .pagination {
    display: flex;
    justify-content: center; /* Căn giữa các nút phân trang */
    align-items: center; /* Căn giữa theo chiều dọc */
    margin: 20px 0; /* Khoảng cách trên và dưới */
}

.pagination a {
    text-decoration: none; /* Bỏ gạch chân */
    color: #007bff; /* Màu chữ cho các liên kết */
    padding: 10px 15px; /* Khoảng cách cho các nút */
    margin: 0 5px; /* Khoảng cách giữa các nút */
    border: 1px solid #007bff; /* Đường viền cho các nút */
    border-radius: 5px; /* Bo tròn góc */
    transition: background-color 0.3s, color 0.3s; /* Hiệu ứng chuyển tiếp */
}

.pagination a:hover {
    background-color: #007bff; /* Màu nền khi hover */
    color: white; /* Màu chữ khi hover */
}

.pagination strong {
    background-color: #007bff; /* Màu nền cho section hiện tại */
    color: white; /* Màu chữ cho section hiện tại */
    padding: 10px 15px; /* Khoảng cách cho nút hiện tại */
    border-radius: 5px; /* Bo tròn góc */
}
    </style>
</head>
<body>

<button id="optionsBtn">Tùy chọn</button>

<div id="optionsMenu" class="dropdown-content">
    <a href="info_user.php"><i class="fas fa-user"></i> Thông Tin</a>
    <a href="network-config.php"><i class="fas fa-network-wired"></i> Cấu Hình IP</a>
    <a href="tos.html"><i class="fas fa-file-contract"></i> Điều khoản dịch vụ</a>
    <a href="index.php?logout=true"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
</div>
<script>
    var menu = document.getElementById("optionsMenu");

    document.getElementById("optionsBtn").addEventListener("click", function() {
        if (menu.style.opacity === "1") {
            // Khi menu đang mở, đóng lại
            menu.style.opacity = "0";
            menu.style.maxHeight = "0";
            menu.style.pointerEvents = "none";  // Vô hiệu hóa nhấp chuột khi menu đóng
        } else {
            // Khi menu đóng, mở lại
            menu.style.display = "block";  // Đảm bảo menu luôn hiển thị
            setTimeout(function() {
                menu.style.opacity = "1";
                menu.style.maxHeight = "200px"; // Tùy chỉnh chiều cao tối đa của menu
                menu.style.pointerEvents = "auto";  // Bật lại sự kiện nhấp chuột khi menu mở
            }, 10);
        }
    });

    // Khi nhấn ra ngoài menu sẽ đóng
    window.onclick = function(event) {
        if (!event.target.matches('#optionsBtn')) {
            if (menu.style.opacity === "1") {
                menu.style.opacity = '0';
                menu.style.maxHeight = '0';
                menu.style.pointerEvents = 'none';  // Vô hiệu hóa nhấp chuột khi menu đóng
                setTimeout(function() {
                    menu.style.display = 'none';
                }, 500); // Thời gian khớp với transition
            }
        }
    }
</script>
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