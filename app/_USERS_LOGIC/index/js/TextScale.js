
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('postForm');
    
    const config = [
      {
        editableId: 'postContent',
        hiddenId: 'hiddenInput',
        counterId: 'charCount',
        maxLength: 500
      },
      {
        editableId: 'postDescription',
        hiddenId: 'hiddenDescription',
        counterId: 'descCharCount',
        maxLength: 4096
      }
    ];

    config.forEach(({ editableId, hiddenId, counterId, maxLength }) => {
      const editableDiv = document.getElementById(editableId);
      const hiddenInput = document.getElementById(hiddenId);
      const counterEl = document.getElementById(counterId);

      editableDiv.addEventListener('input', () => {
        let text = editableDiv.innerText;

        // Trim và giới hạn ký tự
        if (text.length > maxLength) {
          text = text.substring(0, maxLength);
          editableDiv.innerText = text;
          alert(`Chỉ được nhập tối đa ${maxLength} ký tự!`);

          // Reset lại con trỏ về cuối
          const range = document.createRange();
          const sel = window.getSelection();
          range.selectNodeContents(editableDiv);
          range.collapse(false);
          sel.removeAllRanges();
          sel.addRange(range);
        }

        const cleanText = editableDiv.innerText.trim();

        // Cập nhật hidden input & char count
        hiddenInput.value = cleanText;
        counterEl.textContent = `${cleanText.length}/${maxLength}`;
      });
    });

    // Trước khi submit thì đồng bộ lại value vào hidden input
    form.addEventListener('submit', () => {
      config.forEach(({ editableId, hiddenId }) => {
        const editableDiv = document.getElementById(editableId);
        const hiddenInput = document.getElementById(hiddenId);
        hiddenInput.value = editableDiv.innerText.trim();
      });
    });
  });