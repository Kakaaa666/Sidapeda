<?php
$conn = new mysqli('localhost', 'root', '', 'sidapeda');
$res = $conn->query("SELECT kode_indikator, nama_indikator, tahun, periode, nilai, satuan FROM database_ekonomi WHERE kode_indikator LIKE 'HRG-005%' AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY tahun DESC, kode_indikator ASC LIMIT 30");
while ($r = $res->fetch_assoc()) {
    echo "{$r['kode_indikator']} | {$r['nama_indikator']} | {$r['tahun']} | {$r['periode']} | {$r['nilai']} | {$r['satuan']}\n";
}
$cnt = $conn->query("SELECT count(*) as cnt FROM database_ekonomi WHERE kode_indikator LIKE 'HRG-005%' AND (is_deleted = 0 OR is_deleted IS NULL)")->fetch_assoc()['cnt'];
echo "Total rows: $cnt\n";
