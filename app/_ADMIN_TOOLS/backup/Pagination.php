<?php
include 'Logic.php';
                            // Số bản ghi mỗi trang
                            $recordsPerPage = 7;

                            // Lấy danh sách file
                            $files = glob($backupFolder . "*.sql");

                            // Sắp xếp file theo thời gian chỉnh sửa, mới nhất trước
                            usort($files, function($a, $b) {
                                return filemtime($b) - filemtime($a);
                            });

                            $totalRecords = count($files);
                            $totalPages = ceil($totalRecords / $recordsPerPage);
                            $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                            $startIndex = ($currentPage - 1) * $recordsPerPage;
                            $filesForPage = array_slice($files, $startIndex, $recordsPerPage);
?>