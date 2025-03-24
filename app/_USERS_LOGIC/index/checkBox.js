document.addEventListener("DOMContentLoaded", function() {
    const agreeCheckbox = document.getElementById('agreeCheckbox');
    const registerBtn = document.getElementById('registerBtn');
    const registrationForm = document.getElementById('register-form');
  
    if (!agreeCheckbox || !registerBtn || !registrationForm) {
      console.error("❌ Missing element(s) in the form!");
      return;
    }
  
    function toggleSubmitButton() {
      registerBtn.disabled = !agreeCheckbox.checked;
      registerBtn.style.backgroundColor = agreeCheckbox.checked ? '#4CAF50' : '#9e9e9e';
    }
  
    // Gán sự kiện cho checkbox
    agreeCheckbox.addEventListener('change', toggleSubmitButton);
  
    // Kiểm tra trạng thái trước khi submit
    registrationForm.addEventListener('submit', function(event) {
      if (!agreeCheckbox.checked) {
        event.preventDefault();
        alert("Bạn chưa tick vào checkbox.");
      }
    });
  
    // Thiết lập trạng thái ban đầu của nút đăng ký
    toggleSubmitButton();
  });