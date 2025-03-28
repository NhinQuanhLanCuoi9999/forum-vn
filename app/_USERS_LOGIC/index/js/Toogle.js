$(document).ready(function () {
  const authModal = $('#authModal');
  const mainContainer = $('#mainContainer');
  const loginBtn = $('#loginBtn');
  const registerBtn = $('#registerBtn');
  const authClose = $('#authClose');
  const tabLogin = $('#tabLogin');
  const tabRegister = $('#tabRegister');
  const loginFormContainer = $('#loginFormContainer');
  const registerFormContainer = $('#registerFormContainer');
  const agreeCheckbox = $('#agreeCheckbox');
  const registerSubmit = $('#registerSubmit');
  const optionsBtn = $('#optionsBtn');
  const optionsMenu = $('#optionsMenu');

  // Hàm hiển thị modal với hiệu ứng fade + blur
  function showModal(showTabCallback) {
    authModal.addClass('show');
    mainContainer.addClass('blur');
    showTabCallback();
  }

  // Hàm ẩn modal và xóa hiệu ứng blur
  function hideModal() {
    authModal.removeClass('show');
    mainContainer.removeClass('blur');
  }

  function showLoginTab() {
    loginFormContainer.show();
    registerFormContainer.hide();
  }

  function showRegisterTab() {
    loginFormContainer.hide();
    registerFormContainer.show();
  }

  // Bắt sự kiện click
  loginBtn.click(() => showModal(showLoginTab));
  registerBtn.click(() => showModal(showRegisterTab));
  authClose.click(hideModal);
  tabLogin.click(showLoginTab);
  tabRegister.click(showRegisterTab);

  // Đóng modal khi click ra ngoài
  $(window).click((e) => {
    if ($(e.target).is(authModal)) {
      hideModal();
    }
  });

  // Xử lý checkbox đăng ký
  agreeCheckbox.change(function () {
    if (this.checked) {
      registerSubmit.prop('disabled', false).css('background-color', '#28a745');
    } else {
      registerSubmit.prop('disabled', true).css('background-color', '#9e9e9e');
    }
  });

  // Dropdown hiệu ứng mượt cho menu Tùy chọn
  optionsBtn.click(() => optionsMenu.toggleClass('open'));
});
