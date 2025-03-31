<?php
if (!function_exists('renderPagination')) {
    function renderPagination($param, $currentPage, $totalPages) {
        // Nếu có search thì không hiển thị phân trang
        if (!empty($_GET['search']) || $totalPages <= 1) return;
        
        echo '<nav><ul class="pagination">';
        echo '<li class="page-item ' . ($currentPage == 1 ? 'disabled' : '') . '">';
        echo '<a class="page-link" href="?' . $param . '=1"><<<</a></li>';

        if ($totalPages <= 7) {
            for ($i = 1; $i <= $totalPages; $i++) {
                echo '<li class="page-item ' . ($currentPage == $i ? 'active' : '') . '">';
                echo '<a class="page-link" href="?' . $param . '=' . $i . '">' . $i . '</a></li>';
            }
        } else {
            if ($currentPage <= 4) {
                for ($i = 1; $i <= 5; $i++) {
                    echo '<li class="page-item ' . ($currentPage == $i ? 'active' : '') . '">';
                    echo '<a class="page-link" href="?' . $param . '=' . $i . '">' . $i . '</a></li>';
                }
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                echo '<li class="page-item"><a class="page-link" href="?' . $param . '=' . $totalPages . '">' . $totalPages . '</a></li>';
            } elseif ($currentPage >= $totalPages - 3) {
                echo '<li class="page-item"><a class="page-link" href="?' . $param . '=1">1</a></li>';
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                for ($i = $totalPages - 4; $i <= $totalPages; $i++) {
                    echo '<li class="page-item ' . ($currentPage == $i ? 'active' : '') . '">';
                    echo '<a class="page-link" href="?' . $param . '=' . $i . '">' . $i . '</a></li>';
                }
            } else {
                echo '<li class="page-item"><a class="page-link" href="?' . $param . '=1">1</a></li>';
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                for ($i = $currentPage - 1; $i <= $currentPage + 1; $i++) {
                    echo '<li class="page-item ' . ($currentPage == $i ? 'active' : '') . '">';
                    echo '<a class="page-link" href="?' . $param . '=' . $i . '">' . $i . '</a></li>';
                }
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                echo '<li class="page-item"><a class="page-link" href="?' . $param . '=' . $totalPages . '">' . $totalPages . '</a></li>';
            }
        }
        
        echo '<li class="page-item ' . ($currentPage == $totalPages ? 'disabled' : '') . '">';
        echo '<a class="page-link" href="?' . $param . '=' . $totalPages . '">>>></a></li>';
        echo '</ul></nav>';
    }
}


?>