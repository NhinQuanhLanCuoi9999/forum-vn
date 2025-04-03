<?php
function renderPagination($current_section, $total_sections) {
    if (!isset($_SESSION['username'])) return;

    echo "<div class='pagination'>";

    if ($current_section > 1) {
        echo "<a href='index.php?section=1'>&lt;&lt;</a>";
        echo "<a href='index.php?section=" . ($current_section - 1) . "'>&lt;</a>";
    }

    for ($i = max(1, $current_section - 7); $i <= min($total_sections, $current_section + 7); $i++) {
        if ($i == $current_section) {
            echo "<strong>$i</strong>";
        } else {
            echo "<a href='index.php?section=$i'>$i</a>";
        }
    }

    if ($current_section < $total_sections) {
        echo "<a href='index.php?section=" . ($current_section + 1) . "'>&gt;</a>";
        echo "<a href='index.php?section=$total_sections'>&gt;&gt;</a>";
    }

    echo "</div>";
}
?>