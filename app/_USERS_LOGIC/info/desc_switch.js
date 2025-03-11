  // Hiển thị/ẩn form cập nhật mô tả với hiệu ứng
  function toggleDescForm() {
    const form = document.getElementById("update-desc-form");
    form.style.display = (form.style.display === "none" || form.style.display === "") ? "block" : "none";
    if (form.style.display === "block") {
        // Thêm hiệu ứng fade-in
        form.classList.add("fade-in");
    } else {
        // Xóa hiệu ứng fade-in khi ẩn
        form.classList.remove("fade-in");
    }
}