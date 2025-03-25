function toggleForms() {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');

    // Lấy trạng thái thực tế của form đăng nhập
    const loginDisplay = window.getComputedStyle(loginForm).display;

    if (loginDisplay === 'none') {
        loginForm.style.display = 'block';
        registerForm.style.display = 'none';
    } else {
        loginForm.style.display = 'none';
        registerForm.style.display = 'block';
    }
}
