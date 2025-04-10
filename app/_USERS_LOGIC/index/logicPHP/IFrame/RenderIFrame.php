<?php
function renderIFrame() {
  echo <<<HTML
<!-- View Modal -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="searchModalLabel">Chi tiết</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0" style="height: 80vh;">
        <iframe id="searchIframe" src="" style="width: 100%; height: 100%; border: none;"></iframe>
      </div>
    </div>
  </div>
</div>

<script>
  let bsModal, iframeEl;

  document.addEventListener("DOMContentLoaded", () => {
    const modalEl = document.getElementById("searchModal");
    iframeEl = document.getElementById("searchIframe");
    bsModal = new bootstrap.Modal(modalEl);

    iframeEl.addEventListener("load", () => {
      let url;
      try {
        url = iframeEl.contentWindow.location.pathname;
      } catch (e) {
        console.error("Cannot access iframe URL", e);
        return;
      }

      if (url.endsWith("/") || url.endsWith("/index.php") || url.endsWith("/index.html")) {
        bsModal.hide();
        setTimeout(() => {
          iframeEl.src = "";
        }, 300);
      }
    });
  });

  function openSearchModalWithId(id) {
    iframeEl.src = id === "search"
      ? "/src/search.php"
      : "/src/view.php?id=" + encodeURIComponent(id);
    bsModal.show();
  }

  function openProfileModal(username) {
    iframeEl.src = "/src/profile.php?username=" + encodeURIComponent(username);
    bsModal.show();
  }
</script>
HTML;
}
?>