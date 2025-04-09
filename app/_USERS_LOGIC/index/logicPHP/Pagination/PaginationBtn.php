<?php
function renderPagination($current_section, $total_sections) {
    if (!isset($_SESSION['username'])) return;

    echo "<nav class='d-flex justify-content-center mt-4'>";
    echo "<ul class='pagination'>";

    if ($current_section > 1) {
        echo "<li class='page-item'><a class='page-link' href='index.php?section=1'>&laquo;</a></li>";
        echo "<li class='page-item'><a class='page-link' href='index.php?section=" . ($current_section - 1) . "'>&lt;</a></li>";
    }

    for ($i = max(1, $current_section - 7); $i <= min($total_sections, $current_section + 7); $i++) {
        if ($i == $current_section) {
            echo "<li class='page-item active'><span class='page-link'>$i</span></li>";
        } else {
            echo "<li class='page-item'><a class='page-link' href='index.php?section=$i'>$i</a></li>";
        }
    }

    if ($current_section < $total_sections) {
        echo "<li class='page-item'><a class='page-link' href='index.php?section=" . ($current_section + 1) . "'>&gt;</a></li>";
        echo "<li class='page-item'><a class='page-link' href='index.php?section=$total_sections'>&raquo;</a></li>";
    }

    echo "</ul>";
    echo "</nav>";
}
?>
