function cleanFileName(fileName) {
    return fileName.replace(/_[A-Za-z0-9]{10}(?=\.[^.]+$)/, '');
  }
  document.addEventListener("DOMContentLoaded", function () {
    let fileLinks = document.querySelectorAll("a.file-link");
    fileLinks.forEach(fileLink => {
      let originalFileName = fileLink.textContent.trim();
      let cleanName = cleanFileName(originalFileName);
      fileLink.textContent = cleanName;
      fileLink.href = fileLink.href.replace(originalFileName, cleanName);
      fileLink.onclick = function () {
        return confirm(`Cảnh báo: Tệp "${cleanName}" có thể không an toàn. Bạn có chắc muốn tải xuống không?`);
      };
    });
  });