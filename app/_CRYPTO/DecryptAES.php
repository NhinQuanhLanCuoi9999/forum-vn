<?php
require_once 'AES_env.php';

function decryptDataAES($encoded) {
    $key = getAESKey();
    if (!$key) return null;

    $data = base64_decode($encoded);
    $iv = substr($data, 0, 16);
    $cipher = substr($data, 16);
    return openssl_decrypt($cipher, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
}
?>
