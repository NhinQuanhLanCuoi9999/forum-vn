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