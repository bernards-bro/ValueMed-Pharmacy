<?php

echo "PHP version: " . PHP_VERSION . "<br>";
echo "PHP executable: " . PHP_BINARY . "<br>";
echo "Loaded php.ini: " . (php_ini_loaded_file() ?: "NONE") . "<br>";
echo "Extension directory: " . ini_get("extension_dir") . "<br>";

echo "<br>";

if (class_exists("mysqli")) {
    echo "MySQLi: WORKING";
} else {
    echo "MySQLi: NOT WORKING";
}
?>