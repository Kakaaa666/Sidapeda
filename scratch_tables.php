<?php
$conn = new mysqli('localhost', 'root', '', 'sidapeda');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$res = $conn->query("SHOW TABLES");
while ($r = $res->fetch_array()) {
    echo $r[0] . "\n";
}
