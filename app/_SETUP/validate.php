<?php
function getInstallationData($post) {
    // Danh sách các trường bắt buộc
    $required = [
        'host', 'user', 'admin_pass', 'title', 'name', 
        'turnstile_api_key', 'turnstile_site_key', 'ipinfo_api_key', 
        'account_smtp', 'password_smtp', 'google_client_id', 'google_client_secret'
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

    // Kiểm tra định dạng Google Client ID (phải chứa ".apps.googleusercontent.com")
    if (!str_contains($post['google_client_id'], '.apps.googleusercontent.com')) {
        header("Refresh: 3; url=" . $_SERVER['PHP_SELF']);
        exit("Google Client ID không hợp lệ.");
    }

    // Băm mật khẩu Admin với bcrypt cost = 10
    $adminPassHash = password_hash(trim($post['admin_pass']), PASSWORD_BCRYPT, ['cost' => 10]);

    // Lưu dữ liệu vào mảng
    $data = [];
    $data['host']                = trim($post['host']);
    $data['user']                = trim($post['user']);
    $data['pass']                = $post['pass']; // Mật khẩu có thể để trống.
    $data['admin_pass_plain']    = trim($post['admin_pass']);
    $data['admin_pass']          = $adminPassHash;
    $data['title']               = trim($post['title']);
    $data['name']                = trim($post['name']);
    $data['turnstile_api_key']   = trim($post['turnstile_api_key']);
    $data['turnstile_site_key']  = trim($post['turnstile_site_key']);
    $data['ipinfo_api_key']      = trim($post['ipinfo_api_key']);
    $data['smtp_account']        = trim($post['account_smtp']);
    $data['smtp_password']       = trim($post['password_smtp']);
    $data['google_client_id']    = trim($post['google_client_id']);
    $data['google_client_secret']= trim($post['google_client_secret']);
    $data['db']                  = trim($post['database']);

    return $data;
}
?>
