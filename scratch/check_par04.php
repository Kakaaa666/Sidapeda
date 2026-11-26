<?php
define('BASEPATH', '1');
require_once __DIR__ . '/../application/config/database.php';
$dbConfig = $db['default'];
$mysqli = new mysqli($dbConfig['hostname'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);

echo "=== KATALOG INDIKATOR PAR-004% ===\n";
$resKatalog = $mysqli->query("SELECT kode_indikator, nama_indikator, satuan, frekuensi FROM katalog_indikator WHERE kode_indikator LIKE 'PAR-004%'");
while ($r = $resKatalog->fetch_assoc()) {
    echo "{$r['kode_indikator']} | {$r['nama_indikator']} | {$r['satuan']} | {$r['frekuensi']}\n";
}

echo "\n=== DATABASE EKONOMI PAR-004% ===\n";
$resData = $mysqli->query("SELECT kode_indikator, tahun, periode, nilai FROM database_ekonomi WHERE kode_indikator LIKE 'PAR-004%' ORDER BY tahun ASC, kode_indikator ASC, periode ASC");
$count = 0;
while ($r = $resData->fetch_assoc()) {
    echo "{$r['kode_indikator']} | {$r['tahun']} | {$r['periode']} | {$r['nilai']}\n";
    $count++;
    if ($count > 30) {
        echo "... (more records truncated)\n";
        break;
    }
}
$resTotal = $mysqli->query("SELECT COUNT(*) as c FROM database_ekonomi WHERE kode_indikator LIKE 'PAR-004%'");
echo "Total rows: " . $resTotal->fetch_assoc()['c'] . "\n";
