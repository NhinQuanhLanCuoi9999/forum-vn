document.addEventListener("input", function (e) {
    let target = e.target;

    if (target.classList.contains("editable-input")) {
        let maxLength = target.id === "postContent" ? 222 : 500;
        let hiddenInput = target.id === "postContent" ? 
            document.getElementById("hiddenInput") : 
            document.getElementById("hiddenDescription");
        let charCount = target.id === "postContent" ? 
            document.getElementById("charCount") : 
            document.getElementById("descCharCount");

        let text = target.innerText.trim();

        // Giới hạn ký tự
        if (text.length > maxLength) {
            target.innerText = text.substring(0, maxLength);
            alert(`Chỉ được nhập tối đa ${maxLength} ký tự!`);
        }

        // Cập nhật input ẩn
        hiddenInput.value = target.innerText.trim();

        // Hiển thị số ký tự
        charCount.textContent = `${hiddenInput.value.length}/${maxLength}`;
    }
});
