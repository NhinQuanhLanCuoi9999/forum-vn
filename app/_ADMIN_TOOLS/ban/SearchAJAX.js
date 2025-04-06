   // Hàm gửi Ajax cho kết quả tìm kiếm, nhận thêm tham số trang (page)
   function fetchSearchResults(page = 1) {
    var query = document.getElementById('searchInput').value.trim();
    if(query === "") {
        document.getElementById('searchResults').innerHTML = '';
        return;
    }
    fetch("ban.php?action=search&q=" + encodeURIComponent(query) + "&page=" + page)
        .then(function(response) {
            return response.text();
        })
        .then(function(html) {
            document.getElementById('searchResults').innerHTML = html;
        })
        .catch(function(err) {
            document.getElementById('searchResults').innerHTML = '<p class="text-danger">Đã xảy ra lỗi.</p>';
        });
}

// Sử dụng debounce để giảm số lần gọi Ajax khi nhập tìm kiếm
let debounceTimeout;
document.getElementById('searchInput').addEventListener('keyup', function() {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        // Luôn tải trang 1 khi tìm kiếm mới
        fetchSearchResults(1);
    }, 300);
});

// Lắng nghe sự kiện click từ các đường link phân trang của kết quả tìm kiếm
document.getElementById('searchResults').addEventListener('click', function(e) {
    if(e.target && e.target.matches('.search-page')) {
        e.preventDefault();
        var page = e.target.getAttribute('data-page');
        fetchSearchResults(page);
    }
});

// Hàm xác nhận khi ban
function confirmBan() {
    return confirm("Bạn có chắc chắn muốn cấm người dùng này?");
}
// Hàm xác nhận khi hủy cấm
function confirmUnban() {
    return confirm("Bạn có chắc chắn muốn hủy cấm?");
}