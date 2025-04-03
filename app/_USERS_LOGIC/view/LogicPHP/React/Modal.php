<?php
function renderReactionButtons($postId, $totalLikes, $totalDislikes) {
    if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
        return;
    }

    $postIdEscaped = htmlspecialchars($postId, ENT_QUOTES, 'UTF-8');
    $csrfToken = $_SESSION['csrf_token'];

    echo '
    <div class="react-icons">
        <div class="reaction-container" style="display: flex; gap: 10px; justify-content: center; align-items: center; border: 2px solid #ddd; border-radius: 8px; padding: 5px 10px; background-color: #f9f9f9;">
            <form action="view.php?id=' . $postIdEscaped . '" method="POST" class="d-inline">
                <input type="hidden" name="csrf_token" value="' . $csrfToken . '">
                <input type="hidden" name="reaction" value="like">
                <button type="submit" class="btn btn-outline-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Like: ' . $totalLikes . '" style="padding: 5px 10px; border-radius: 4px;">
                    👍
                </button>
            </form>
            <form action="view.php?id=' . $postIdEscaped . '" method="POST" class="d-inline">
                <input type="hidden" name="csrf_token" value="' . $csrfToken . '">
                <input type="hidden" name="reaction" value="dislike">
                <button type="submit" class="btn btn-outline-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Dislike: ' . $totalDislikes . '" style="padding: 5px 10px; border-radius: 4px;">
                    👎
                </button>
            </form>
        </div>
    </div>';


}
?>
