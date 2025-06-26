 // Lấy domain hiện tại
 const domain = window.location.origin;

 // Cập nhật các URL API động
 document.getElementById('post-api').href = domain + '/api/Post.php';
 document.getElementById('bans-api').href = domain + '/api/Bans.php';
 document.getElementById('comments-api').href = domain + '/api/Comment.php';
 document.getElementById('user-api').href = domain + '/api/User.php';