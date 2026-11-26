<?php
$conn = new mysqli('localhost', 'root', '', 'sidapeda');
$res = $conn->query("SELECT DISTINCT tahun, periode FROM database_ekonomi WHERE kode_indikator LIKE 'HRG-005%' ORDER BY tahun DESC, periode ASC");
while ($r = $res->fetch_assoc()) {
    echo "Tahun: {$r['tahun']} | Periode: {$r['periode']}\n";
}
