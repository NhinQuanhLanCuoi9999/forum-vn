  // Kiểm tra kích thước màn hình
        function checkScreenSize() {
            const warning = document.getElementById('mobile-warning');
            if (window.innerWidth < 768) { // Nếu màn hình nhỏ hơn 768px
                warning.style.display = 'flex'; // Hiện thông báo
                document.body.classList.add('no-scroll'); // Ngăn cuộn trang
            } else {
                warning.style.display = 'none'; // Ẩn thông báo
                document.body.classList.remove('no-scroll'); // Cho phép cuộn trang
            }
        }

        // Gọi hàm khi trang được tải, khi kích thước màn hình thay đổi,
        // và khi thay đổi hướng màn hình
        window.onload = checkScreenSize;
        window.onresize = checkScreenSize;
        window.orientationchange = checkScreenSize; // Kiểm tra hướng màn hình