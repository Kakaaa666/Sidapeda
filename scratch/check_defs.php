<?php
define('BASEPATH', 'C:/xampp/htdocs/Sidapeda/system/');
require_once 'C:/xampp/htdocs/Sidapeda/application/config/database.php';

$mysqli = new mysqli('localhost', 'root', '', 'sidapeda');
$parentCode = 'HRG-002';

// Check if HRG-002 definitions match
$kelompoks = array(
    '01' => 'Makanan, Minuman, dan Tembakau',
    '02' => 'Pakaian dan Alas Kaki',
    '03' => 'Perumahan, Air, Listrik, dan Bahan Bakar Rumah Tangga',
    '04' => 'Perlengkapan, Peralatan, dan Pemeliharaan Rutin Rumah Tangga',
    '05' => 'Kesehatan',
    '06' => 'Transportasi',
    '07' => 'Informasi, Komunikasi, dan Jasa Keuangan',
    '08' => 'Rekreasi, Olahraga, dan Budaya',
    '09' => 'Pendidikan',
    '10' => 'Penyediaan Makanan dan Minuman/Restoran',
    '11' => 'Perawatan Pribadi dan Jasa Lainnya'
);

echo "Total kelompoks: " . count($kelompoks) . "\n";
foreach ($kelompoks as $k => $v) {
    echo "{$k} => {$v}\n";
}
