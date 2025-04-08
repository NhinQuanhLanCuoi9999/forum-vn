<?php
$mdFiles = [];
$dir = __DIR__ . '/changelogs';
if (is_dir($dir)) {
    foreach (scandir($dir) as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'md') {
            $mdFiles[] = $file;
        }
    }
    sort($mdFiles);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>📦 Changelog Viewer</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="/asset/css/GithubMarkdown.css" rel="stylesheet" />
  <style>
    body {
      background-color: #f4f6f8;
      padding: 2rem;
    }
    .markdown-body {
      background: #ffffff;
      padding: 2rem;
      border-radius: 1rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .dropdown {
      max-width: 300px;
      margin-bottom: 1.5rem;
    }
  </style>
</head>
<body>

  <div class="container">
    <h2 class="mb-4 fw-bold">📚 Changelog Viewer</h2>
    <div class="dropdown">
      <select id="fileSelector" class="form-select">
        <option value="">-- Chọn version cần xem --</option>
        <option value="_home">🏠 Về trang chủ</option>
        <?php foreach ($mdFiles as $file): ?>
          <option value="<?= htmlspecialchars($file) ?>"><?= htmlspecialchars(str_replace('.md', '', $file)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div id="markdownContent" class="markdown-body">
      <p><i>Chọn 1 version ở trên để xem nội dung markdown nhé</i></p>
    </div>
  </div>

  <!-- Libs -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

  <script>
    $(document).ready(function () {
      $('#fileSelector').change(function () {
        const file = $(this).val();
        if (file === '_home') {
          window.location.href = '/';
          return;
        }

        if (file) {
          $.get("changelogs/" + file, function (data) {
            const html = marked.parse(data);
            $('#markdownContent').html(html);
          }).fail(function () {
            $('#markdownContent').html('<div class="alert alert-danger">❌ Không thể tải file markdown này!</div>');
          });
        } else {
          $('#markdownContent').html('<p><i>Chọn 1 version ở trên để xem nội dung markdown nhé.</i></p>');
        }
      });
    });
  </script>

</body>
</html>
