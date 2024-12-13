<?php
// Thiết lập múi giờ cho Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Hàm định dạng nội dung
function formatText($text) {
    // Định dạng in đậm **text**
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text); 
    // Định dạng in nghiêng *text*
    $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text); 
    // Định dạng gạch ngang --text--
    $text = preg_replace('/--(.*?)--/', '<del>$1</del>', $text); 
    // Định dạng gạch chân __text__
    $text = preg_replace('/__(.*?)__/', '<u>$1</u>', $text);
    // Định dạng code inline `code`
    $text = preg_replace('/`(.*?)`/', '<code style="background-color: #f0f0f0; color: #333; padding: 2px 4px; border-radius: 3px;">$1</code>', $text);
    // Định dạng code block ```code```
    $text = preg_replace('/```(.*?)```/s', '<pre class="code-block">$1</pre>', $text);
    // Định dạng spoil block ||text|| với JavaScript và localStorage
    $text = preg_replace_callback('/\|\|(.*?)\|\|/', function ($matches) {
        $id = uniqid('spoil_'); // Tạo ID duy nhất cho mỗi spoil block
        return '<span id="' . $id . '" class="spoil" style="background-color: #333; color: #333; padding: 5px; cursor: pointer;" onclick="toggleSpoiler(this, \'' . $id . '\')">' . $matches[1] . '</span>';
    }, $text);
    return $text;
}
?>