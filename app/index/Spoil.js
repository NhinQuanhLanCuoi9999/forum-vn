// Hàm để mở/đóng spoil block
function toggleSpoiler(element, id) {
    let isRevealed = localStorage.getItem(id) === 'true';
    if (isRevealed) {
        // Đóng spoil block
        element.style.color = '#333';
        localStorage.setItem(id, 'false');
    } else {
        // Mở spoil block
        element.style.color = '#fff';
        localStorage.setItem(id, 'true');
    }
}

// Khi trang tải lại, kiểm tra trạng thái của spoil block từ localStorage
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.spoil').forEach(function (element) {
        let id = element.id;
        let isRevealed = localStorage.getItem(id) === 'true';
        if (isRevealed) {
            element.style.color = '#fff'; // Mở spoil block
        }
    });
});