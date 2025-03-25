  document.getElementById('password').addEventListener('input', function() {
    var password = this.value;
    var strength = checkPasswordStrength(password);
    animateProgressBar(strength);
    checkPasswordMatch();
});

function checkPasswordStrength(password) {
    var strength = 0;
    
    // Kiểm tra độ dài mật khẩu
    if (password.length >= 6) strength += 20;
    if (password.length >= 10) strength += 20;
    
    // Kiểm tra sự kết hợp chữ hoa, chữ thường và số
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 20;
    
    // Kiểm tra các ký tự đặc biệt
    if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 20;
    
    // Kiểm tra số
    if (/\d/.test(password)) strength += 20;
    
    return strength;
}

function animateProgressBar(strength) {
    var progress = document.getElementById('passwordStrength');
    var strengthText = document.getElementById('passwordStrengthText');
    var container = document.getElementById('passwordStrengthContainer');
    
    // Ẩn thanh tiến độ khi chưa có giá trị
    container.style.display = 'block';
    
    var currentValue = progress.value;
    var targetValue = strength;

    var interval = setInterval(function() {
        if (currentValue < targetValue) {
            currentValue++;
            progress.value = currentValue;
        } else if (currentValue > targetValue) {
            currentValue--;
            progress.value = currentValue;
        } else {
            clearInterval(interval);
        }
    }, 10); // Thời gian di chuyển 1 đơn vị (mỗi 10ms)
    
    // Cập nhật thông tin độ mạnh mật khẩu
    if (strength < 40) {
        strengthText.textContent = "Mật khẩu yếu";
        strengthText.style.color = "red";
    } else if (strength < 70) {
        strengthText.textContent = "Mật khẩu trung bình";
        strengthText.style.color = "orange";
    } else {
        strengthText.textContent = "Mật khẩu mạnh";
        strengthText.style.color = "green";
    }
}

function checkPasswordMatch() {
    var password = document.getElementById('password').value;
    var confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        document.getElementById('registerBtn').disabled = true;
    } else {
        document.getElementById('registerBtn').disabled = false;
    }
}

function toggleSubmitButton() {
    var checkbox = document.getElementById('agreeCheckbox');
    var submitButton = document.getElementById('registerBtn');
    submitButton.disabled = !checkbox.checked;
}