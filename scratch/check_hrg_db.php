<?php
$mysqli = new mysqli('localhost', 'root', '', 'sidapeda');
if ($mysqli->connect_error) {
    die("Connect failed: " . $mysqli->connect_error . "\n");
}

// Check database_ekonomi records for HRG-002 in 35.10
$res = $mysqli->query("SELECT kode_indikator, tahun, periode, nilai FROM database_ekonomi WHERE kode_wilayah = '35.10' AND (kode_indikator = 'HRG-002' OR kode_indikator LIKE 'HRG-002.%') AND is_deleted = 0 LIMIT 20");
echo "Found records: " . $res->num_rows . "\n";
while ($row = $res->fetch_assoc()) {
    echo "{$row['kode_indikator']} | {$row['tahun']} | {$row['periode']} | {$row['nilai']}\n";
}
