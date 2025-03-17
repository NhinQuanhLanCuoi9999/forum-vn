<?php
function writeConfigFile($configFile, $data) {
    $configContent = "<?php\n";
    $configContent .= "\$host = '" . $data['host'] . "';\n";
    $configContent .= "\$db = '" . $data['db'] . "';\n";
    $configContent .= "\$user = '" . $data['user'] . "';\n";
    $configContent .= "\$pass = '" . $data['pass'] . "';\n\n";
    $configContent .= "try {\n";
    $configContent .= "    \$conn = new mysqli(\$host, \$user, \$pass, \$db);\n\n";
    $configContent .= "    if (\$conn->connect_error) {\n";
    $configContent .= "        throw new Exception(\"Kết nối thất bại, vui lòng liên hệ Admin để trợ giúp.\");\n";
    $configContent .= "    }\n";
    $configContent .= "} catch (Exception \$e) {\n";
    $configContent .= "    die(\"Kết nối thất bại, vui lòng liên hệ Admin để trợ giúp.\");\n";
    $configContent .= "}\n";
    $configContent .= "?>\n";
    file_put_contents($configFile, $configContent);
}
?>
