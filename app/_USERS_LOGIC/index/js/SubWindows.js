// Hàm mở popup dùng chung, giữ nguyên logic cũ nhưng thêm console.log để debug
function openPopup(url, width = 500, height = 600, onMessage = null, allowFallback = true) {
    const left = (screen.width - width) / 2;
    const top = (screen.height - height) / 2;
  
    // Debug: log trước khi mở popup
    console.log(`Mở popup với url: ${url}`);
  
    const popup = window.open(
      url,
      'PopupWindow',
      `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`
    );
  
    // Debug: log popup object để kiểm tra nếu bị null (tức bị block)
    console.log("Popup object:", popup);
  
    if (popup && !popup.closed) {
      popup.focus();
  
      // Thêm interval kiểm tra URL của popup, nếu popup chuyển về index thì auto đóng
      const checkPopupURL = setInterval(() => {
        try {
          if (popup.location.href.includes('/index.php')) {
            clearInterval(checkPopupURL);
            console.log("Popup chuyển về index, đóng popup");
            popup.close();
          }
        } catch (error) {
          // Nếu bị lỗi do cross-origin, log ra cho chắc
          console.log("Lỗi khi kiểm tra URL của popup:", error);
        }
      }, 500);
  
      // Nếu có callback onMessage thì thiết lập event listener message
      if (onMessage && typeof onMessage === 'function') {
        const listener = function(event) {
          // Chỉ chấp nhận message từ cùng domain
          if (event.origin !== window.location.origin) return;
          console.log("Nhận message từ popup:", event.data);
          onMessage(event.data);
          window.removeEventListener('message', listener);
          popup.close();
        };
        window.addEventListener('message', listener);
      }
    } else {
      // Nếu popup bị block (popup == null) hoặc không mở được
      console.log("Popup bị block hoặc không mở được.");
      if (allowFallback) {
        window.location.href = url;
      } else {
        alert("Popup bị block, hãy cho phép popup để tiếp tục.");
      }
    }
  }
  
  // Google Login
  document.getElementById('googleLoginBtn')?.addEventListener('click', function(e) {
    e.preventDefault();
    openPopup(
      '/src/google_auth/google_login.php?popup=true',
      500,
      600,
      function(data) {
        if (data === 'auth_success') {
          window.location.reload();
        }
      },
      true
    );
  });
  
  // Forgot Password
  document.getElementById('forgotPasswordBtn')?.addEventListener('click', function(e) {
    e.preventDefault();
    openPopup('/src/forget_pass.php?popup=true', 500, 400, null, false);
  });
  
  // Đổi mật khẩu
  document.getElementById('changePasswordBtn')?.addEventListener('click', function(e) {
    e.preventDefault();
    // Nếu file change_password.php nằm trong folder /app, thay đổi đường dẫn tương ứng
    openPopup('/src/change_password.php?popup=true', 500, 500, null, false);
  });
  