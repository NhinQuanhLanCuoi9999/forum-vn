function toggleComments(postId) {
    var commentsDiv = document.getElementById('comments-' + postId);
    var button = document.querySelector('.toggle-comments[data-post-id="' + postId + '"]');
    
    // Kiểm tra trạng thái hiện tại của phần bình luận
    if (commentsDiv.style.display === 'block') {
        let height = commentsDiv.scrollHeight;

        commentsDiv.animate([{ height: height + 'px' }, { height: '0' }], {
            duration: 100,
            fill: 'forwards'
        }).onfinish = function () {
            commentsDiv.style.display = 'none';
            button.textContent = 'Hiện bình luận'; // Cập nhật nút sau khi ẩn
            localStorage.setItem('commentsVisible-' + postId, 'false'); // Cập nhật trạng thái
        };
    } else {
        commentsDiv.style.display = 'block'; // Hiện phần bình luận
        commentsDiv.style.height = '0'; // Đặt chiều cao ban đầu
        let height = commentsDiv.scrollHeight; // Lấy chiều cao thực

        commentsDiv.animate([{ height: '0' }, { height: height + 'px' }], {
            duration: 100,
            fill: 'forwards'
        }).onfinish = function () {
            button.textContent = 'Ẩn bình luận'; // Cập nhật nút sau khi hiện
            localStorage.setItem('commentsVisible-' + postId, 'true'); // Cập nhật trạng thái
        };
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const toggleButtons = document.querySelectorAll('.toggle-comments');

    toggleButtons.forEach(function (button) {
        const postId = button.getAttribute('data-post-id');
        const commentsSection = document.getElementById('comments-' + postId);

        // Kiểm tra trạng thái trong localStorage
        if (localStorage.getItem('commentsVisible-' + postId) === 'true') {
            commentsSection.style.display = 'block';
            commentsSection.style.height = commentsSection.scrollHeight + 'px'; // Đặt chiều cao cho bình luận
            button.textContent = 'Ẩn bình luận';
        } else {
            commentsSection.style.display = 'none';
            commentsSection.style.height = '0'; // Đặt chiều cao cho bình luận khi ẩn
            button.textContent = 'Hiện bình luận';
        }

        // Gắn sự kiện click
        button.addEventListener('click', function () {
            toggleComments(postId);
        });
    });
});