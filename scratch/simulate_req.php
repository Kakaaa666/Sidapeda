<?php
$_GET['kode_wilayah'] = '35.10';
$_GET['parent_code'] = 'HRG-002';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

ob_start();
require_once 'C:/xampp/htdocs/Sidapeda/index.php';
$output = ob_get_clean();
echo "Length: " . strlen($output) . "\n";
echo "Preview: " . substr($output, 0, 500) . "\n";
