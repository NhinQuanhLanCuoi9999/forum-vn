 // Lấy domain hiện tại
 const domain = window.location.origin;

 // Cập nhật các URL API động
 document.getElementById('post-api').href = domain + '/api/Post.php?api=[api key]';
 document.getElementById('bans-api').href = domain + '/api/Bans.php?api=[api key]';
 document.getElementById('comments-api').href = domain + '/api/Comment.php?api=[api key]';
 document.getElementById('user-api').href = domain + '/api/User.php?api=[api key]';