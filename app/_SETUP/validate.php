<?php
function getInstallationData($post) {
    // Kiểm tra CSRF token
    if (!isset($post['csrf_token']) || !isset($_SESSION['csrf_token']) || $post['csrf_token'] !== $_SESSION['csrf_token']) {
        exit('Yêu cầu không hợp lệ.');
    }
    // Xóa token sau khi dùng
    unset($_SESSION['csrf_token']);

    // Danh sách các trường bắt buộc
    $required = [
       'host', 'user', 'admin_pass', 'title', 'name', 
       'hcaptcha_api_key', 'hcaptcha_site_key', 'ipinfo_api_key', 
       'account_smtp', 'password_smtp'
    ];
    foreach ($required as $field) {
        if (empty(trim($post[$field] ?? ''))) {
           header("Refresh: 3; url=" . $_SERVER['PHP_SELF']);
           exit("$field không được để trống.");
        }
    }
    // Kiểm tra định dạng email cho SMTP account
    if (!filter_var(trim($post['account_smtp']), FILTER_VALIDATE_EMAIL)) {
         header("Refresh: 3; url=" . $_SERVER['PHP_SELF']);
         exit("Tài khoản Gmail không hợp lệ.");
    }
    // Kiểm tra độ dài mật khẩu Admin và SMTP
    if (strlen(trim($post['admin_pass'])) < 6) {
         header("Refresh: 3; url=" . $_SERVER['PHP_SELF']);
         exit("Mật khẩu Admin phải có ít nhất 6 ký tự.");
    }
    if (strlen(trim($post['password_smtp'])) < 6) {
         header("Refresh: 3; url=" . $_SERVER['PHP_SELF']);
         exit("Mật khẩu SMTP phải có ít nhất 6 ký tự.");
    }

    $data = [];
    $data['host']               = trim($post['host']);
    $data['user']               = trim($post['user']);
    $data['pass']               = $post['pass']; // Mật khẩu có thể để trống.
    $data['admin_pass_plain']   = trim($post['admin_pass']);
    $data['admin_pass']         = password_hash($data['admin_pass_plain'], PASSWORD_BCRYPT);
    $data['title']              = trim($post['title']);
    $data['name']               = trim($post['name']);
    $data['hcaptcha_api_key']   = trim($post['hcaptcha_api_key']);
    $data['hcaptcha_site_key']  = trim($post['hcaptcha_site_key']);
    $data['ipinfo_api_key']     = trim($post['ipinfo_api_key']);
    $data['smtp_account']       = trim($post['account_smtp']);
    $data['smtp_password']      = trim($post['password_smtp']);
    $data['db']                 = trim($post['database']);

    return $data;
}
?>
