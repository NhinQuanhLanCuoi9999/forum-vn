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