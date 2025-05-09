function toggleForms() {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');

    // Toggle the display of the forms
    if (loginForm.style.display === 'none') {
        loginForm.style.display = 'block';
        registerForm.style.display = 'none';
    } else {
        loginForm.style.display = 'none';
        registerForm.style.display = 'block';
    }
}

// Biến http:// hoặc https:// thành liên kết
document.addEventListener('DOMContentLoaded', () => {
    const posts = document.querySelectorAll('.post');
    posts.forEach(post => {
        const content = post.querySelector('h3');
        content.innerHTML = convertLinks(content.innerHTML);
    });
});

function convertLinks(text) {
    const regex = /(https?:\/\/[^\s<>()]+(?:\s[^\s<>()]*[._\-/][^\s<>()]*)*)/g;

    return text.replace(regex, (match) => {
        // Thay thế khoảng trắng trong link thành %20
        const encoded = match.replace(/\s/g, '%20');
        return `<a href="${encoded}" target="_blank">${match}</a>`;
    });
}