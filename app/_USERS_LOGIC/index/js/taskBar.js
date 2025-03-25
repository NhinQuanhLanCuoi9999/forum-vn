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