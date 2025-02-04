document.addEventListener("DOMContentLoaded", function() {
    const openBtn = document.getElementById("open-btn");
    const sidebar = document.getElementById("sidebar");
    const mainContent = document.querySelector(".main-content");
    const contentDiv = document.getElementById("content");
    const welcomeDiv = document.querySelector(".welcome"); // Thêm dòng này

    // Sự kiện click để mở/đóng sidebar
    openBtn.addEventListener("click", () => {
        if (sidebar.classList.contains("show-sidebar")) {
            sidebar.classList.remove("show-sidebar");
            mainContent.classList.remove("show-content");
            openBtn.textContent = "☰ Mở Menu"; // Đổi chữ về "Mở Menu"
        } else {
            sidebar.classList.add("show-sidebar");
            mainContent.classList.add("show-content");
            openBtn.textContent = "✖ Đóng Menu"; // Đổi chữ thành "Đóng Menu"
        }
    });

    // Hiển thị nội dung nếu có section
    const urlParams = new URLSearchParams(window.location.search);
    const section = urlParams.get('section');
    if (section) {
        contentDiv.classList.remove('hidden');
        welcomeDiv.style.display = 'none'; // Ẩn div "welcome"
    }
});

function changeSection(section) {
    window.location.href = "admin_tool/admin.php?section=" + section;
}