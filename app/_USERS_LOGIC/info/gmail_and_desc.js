document.addEventListener("DOMContentLoaded", function() {
  const editGmailBtn = document.getElementById('editGmailBtn');
  const gmailForm = document.getElementById('gmailForm');
  const gmailText = document.getElementById('gmailText');
  const cancelEdit = document.getElementById('cancelEdit');
  const descForm = document.getElementById("update-desc-form");

  // Cài đặt ban đầu cho các element có hiệu ứng trượt
  [gmailForm, descForm].forEach(el => {
    el.style.overflow = "hidden";
    el.style.transition = "height 0.3s ease";
    el.style.height = "0px";
    el.style.display = "none";
  });

  if (editGmailBtn) {
    editGmailBtn.addEventListener('click', function() {
      slideDown(gmailForm);
      editGmailBtn.style.display = 'none';
      gmailText.style.display = 'none';
    });
  }

  if (cancelEdit) {
    cancelEdit.addEventListener('click', function() {
      slideUp(gmailForm);
      editGmailBtn.style.display = 'inline-block';
      gmailText.style.display = 'inline';
    });
  }
});

function toggleDescForm() {
  const form = document.getElementById("update-desc-form");
  if (window.getComputedStyle(form).display === "none") {
    slideDown(form);
  } else {
    slideUp(form);
  }
}

function slideDown(element) {
  element.style.display = "block";
  // Đặt lại chiều cao ban đầu là 0
  element.style.height = "0px";
  // Lấy chiều cao cần đạt được
  let targetHeight = element.scrollHeight;
  // Sử dụng requestAnimationFrame để đảm bảo chuyển động mượt
  requestAnimationFrame(() => {
    element.style.height = targetHeight + "px";
  });
  // Khi kết thúc transition, bỏ giá trị height để cho element tự động mở rộng
  element.addEventListener("transitionend", function handler() {
    element.style.height = "auto";
    element.removeEventListener("transitionend", handler);
  });
}

function slideUp(element) {
  // Đặt chiều cao hiện tại là auto về giá trị cụ thể trước khi chuyển về 0
  element.style.height = element.scrollHeight + "px";
  // Sau đó, bắt đầu chuyển đổi về 0
  requestAnimationFrame(() => {
    element.style.height = "0px";
  });
  // Khi kết thúc, ẩn element đi
  element.addEventListener("transitionend", function handler() {
    element.style.display = "none";
    element.removeEventListener("transitionend", handler);
  });
}