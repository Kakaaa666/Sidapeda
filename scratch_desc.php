<?php
$conn = new mysqli('localhost', 'root', '', 'sidapeda');
$res = $conn->query("DESCRIBE database_ekonomi");
while ($r = $res->fetch_assoc()) {
    echo $r['Field'] . " (" . $r['Type'] . ")\n";
}
