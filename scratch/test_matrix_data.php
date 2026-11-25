<?php
$mysqli = new mysqli('localhost', 'root', '', 'sidapeda');
$kodeWil = '35.10';
$parentCode = 'HRG-002';

$isFixedParent = true;
$isHrg = (strpos($parentCode, 'HRG-') === 0);
$isHrgMonthlyGroup = in_array($parentCode, array('HRG-002', 'HRG-006'));
echo "isHrgMonthlyGroup: " . ($isHrgMonthlyGroup ? 'TRUE' : 'FALSE') . "\n";

// Query records
$query = "SELECT * FROM database_ekonomi WHERE kode_wilayah = '{$kodeWil}' AND (kode_indikator = '{$parentCode}' OR kode_indikator LIKE '{$parentCode}.%') AND is_deleted = 0 ORDER BY tahun ASC";
$res = $mysqli->query($query);
echo "Total records in DB: " . $res->num_rows . "\n";

$years = [];
$monthlyValues = [];
$standardMonths = array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');

while ($row = $res->fetch_assoc()) {
    $c = $row['kode_indikator'];
    $y = (int)$row['tahun'];
    $v = (float)$row['nilai'];
    $p = trim($row['periode'] ?? '');
    if (!in_array($y, $years)) $years[] = $y;

    $cleanMonth = '';
    if (strpos($p, 'Bulanan (') === 0) {
        $cleanMonth = trim(str_replace(array('Bulanan (', ')'), '', $p));
    } elseif ($p === 'Tahunan') {
        $cleanMonth = 'Tahunan';
    }
    if ($cleanMonth) {
        $monthlyValues[$c][$y][$cleanMonth] = $v;
    }
}
sort($years);
echo "Years: " . json_encode($years) . "\n";
echo "Monthly codes: " . json_encode(array_keys($monthlyValues)) . "\n";
