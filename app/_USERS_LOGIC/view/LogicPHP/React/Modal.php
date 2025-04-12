<?php
function renderReactionButtons($postId, $totalLikes, $totalDislikes) {
    if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
        return;
    }

    $postIdEscaped = htmlspecialchars($postId, ENT_QUOTES, 'UTF-8');
    $csrfToken = $_SESSION['csrf_token'];

    echo '
   <style>
@media (max-width:768px){.reaction-container-wrapper{display:flex;flex-direction:column}
.reaction-container{order:3;margin-top:12px}
.reaction-form{flex-direction:column;align-items:center}
.reaction-count{display:inline!important;font-size:14px;margin-top:4px}
.reaction-button{pointer-events:auto}}@media (min-width:769px)
{.reaction-container-wrapper{display:flex;justify-content:flex-end;align-items:center}
.reaction-container{order:0;margin:0}.reaction-form{flex-direction:row;align-items:center}
.reaction-button{pointer-events:auto;margin-left:10px}
.reaction-count{display:none!important;font-size:14px;margin-left:8px}
.reaction-container:hover .reaction-count{display:inline!important}}
</style>


    
    <div class="reaction-container-wrapper">
        <div class="reaction-container" style="display: flex; gap: 10px; justify-content: center; align-items: center; border: 2px solid #ddd; border-radius: 8px; padding: 5px 10px; background-color: #f9f9f9;">
            <form action="view.php?id=' . $postIdEscaped . '" method="POST" class="d-inline reaction-form" style="display: flex; align-items: center; gap: 5px;">
                <input type="hidden" name="csrf_token" value="' . $csrfToken . '">
                <input type="hidden" name="reaction" value="like">
                <button type="submit" class="btn btn-outline-success reaction-button" data-bs-toggle="tooltip" data-bs-placement="top" title="Like: ' . $totalLikes . '" style="padding: 5px 10px; border-radius: 4px;">
                    👍
                </button>
                <span class="reaction-count">' . $totalLikes . '</span>
            </form>
            <form action="view.php?id=' . $postIdEscaped . '" method="POST" class="d-inline reaction-form" style="display: flex; align-items: center; gap: 5px;">
                <input type="hidden" name="csrf_token" value="' . $csrfToken . '">
                <input type="hidden" name="reaction" value="dislike">
                <button type="submit" class="btn btn-outline-danger reaction-button" data-bs-toggle="tooltip" data-bs-placement="top" title="Dislike: ' . $totalDislikes . '" style="padding: 5px 10px; border-radius: 4px;">
                    👎
                </button>
                <span class="reaction-count">' . $totalDislikes . '</span>
            </form>
        </div>
    </div>';
    
}
?>
