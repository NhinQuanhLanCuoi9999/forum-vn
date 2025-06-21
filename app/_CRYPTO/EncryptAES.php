<?php
require_once 'AES_env.php';

function encryptDataAES($plaintext) {
    $key = getAESKey();
    if (!$key) return null;

    $iv = random_bytes(16);
    $ciphertext = openssl_encrypt($plaintext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $ciphertext);
}
?>
