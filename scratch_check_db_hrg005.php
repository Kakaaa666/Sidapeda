<?php
$conn = new mysqli('localhost', 'root', '', 'sidapeda');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$res = $conn->query("SELECT id, code, indicator, year, period, value, unit, status FROM database_ekonomi WHERE code LIKE 'HRG-005%' ORDER BY year DESC, code ASC LIMIT 100");
$count = $conn->query("SELECT count(*) as cnt FROM database_ekonomi WHERE code LIKE 'HRG-005%'")->fetch_assoc()['cnt'];
echo "Total records for HRG-005: $count\n";

$rows = [];
while ($row = $res->fetch_assoc()) {
    $rows[] = $row;
}
echo json_encode(array_slice($rows, 0, 25), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// Check distinct years and periods for HRG-005
$resP = $conn->query("SELECT DISTINCT year, period FROM database_ekonomi WHERE code LIKE 'HRG-005%' ORDER BY year DESC, period ASC");
echo "\nDistinct year & period:\n";
while ($r = $resP->fetch_assoc()) {
    echo "Year: {$r['year']} | Period: {$r['period']}\n";
}
