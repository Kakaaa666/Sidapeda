<?php
define('BASEPATH', 'true');
define('ENVIRONMENT', 'development');
require_once 'application/config/database.php';
$dbCfg = $db['default'];
$mysqli = new mysqli($dbCfg['hostname'], $dbCfg['username'], $dbCfg['password'], $dbCfg['database']);

if ($mysqli->connect_error) {
    die("Connect failed: " . $mysqli->connect_error);
}

$regionCodeMap = [
    '3509' => ['kode' => '35.09', 'nama' => 'Kabupaten Jember'],
    '3510' => ['kode' => '35.10', 'nama' => 'Kabupaten Banyuwangi'],
    '3522' => ['kode' => '35.22', 'nama' => 'Kabupaten Bojonegoro'],
    '3525' => ['kode' => '35.25', 'nama' => 'Kabupaten Gresik'],
    '3529' => ['kode' => '35.29', 'nama' => 'Kabupaten Sumenep'],
    '3571' => ['kode' => '35.71', 'nama' => 'Kota Kediri'],
    '3573' => ['kode' => '35.73', 'nama' => 'Kota Malang'],
    '3574' => ['kode' => '35.74', 'nama' => 'Kota Probolinggo'],
    '3577' => ['kode' => '35.77', 'nama' => 'Kota Madiun'],
    '3578' => ['kode' => '35.78', 'nama' => 'Kota Surabaya'],
    'Jawa Timur' => ['kode' => '35.00', 'nama' => 'Provinsi Jawa Timur']
];

function upsert_record($mysqli, $data) {
    $stmt = $mysqli->prepare("SELECT id FROM database_ekonomi WHERE kode_wilayah = ? AND kode_indikator = ? AND tahun = ? AND periode = ? AND is_deleted = 0 LIMIT 1");
    $stmt->bind_param("ssis", $data['kode_wilayah'], $data['kode_indikator'], $data['tahun'], $data['periode']);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    if ($row) {
        $uStmt = $mysqli->prepare("UPDATE database_ekonomi SET nilai = ?, satuan = ?, nama_indikator = ?, klaster = ?, wilayah = ?, sumber_data = ?, definisi_operasional = ?, status_validasi = 'Tervalidasi' WHERE id = ?");
        $uStmt->bind_param("dssssssi", $data['nilai'], $data['satuan'], $data['nama_indikator'], $data['klaster'], $data['wilayah'], $data['sumber_data'], $data['definisi_operasional'], $row['id']);
        $uStmt->execute();
        $uStmt->close();
        return 'updated';
    } else {
        $iStmt = $mysqli->prepare("INSERT INTO database_ekonomi (kode_wilayah, kode_indikator, nama_indikator, klaster, wilayah, periode, tahun, nilai, satuan, sumber_data, definisi_operasional, status_validasi, is_deleted, dibuat_pada) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Tervalidasi', 0, NOW())");
        $iStmt->bind_param("ssssssissss", $data['kode_wilayah'], $data['kode_indikator'], $data['nama_indikator'], $data['klaster'], $data['wilayah'], $data['periode'], $data['tahun'], $data['nilai'], $data['satuan'], $data['sumber_data'], $data['definisi_operasional']);
        $iStmt->execute();
        $iStmt->close();
        return 'inserted';
    }
}

function ensure_katalog($mysqli, $code, $name, $cluster, $unit, $freq, $source, $def, $wilayah = '35.10') {
    $stmt = $mysqli->prepare("SELECT id FROM katalog_indikator WHERE kode_indikator = ? LIMIT 1");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    if (!$row) {
        $iStmt = $mysqli->prepare("INSERT INTO katalog_indikator (kode_wilayah, kode_indikator, nama_indikator, klaster, satuan, frekuensi, sumber_data, definisi_operasional, is_deleted) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)");
        $iStmt->bind_param("ssssssss", $wilayah, $code, $name, $cluster, $unit, $freq, $source, $def);
        $iStmt->execute();
        $iStmt->close();
    }
}

$rawLines = file('scratch/hrg_input.csv');
$totalLines = count($rawLines);

echo "Starting import of HRG-002 and HRG-006 months...\n";

// --- SECTION 2: HRG-002 ---
$curReg = null;
$kelColMap = [];
$h002Count = 0;

for ($i = 15; $i < 167; $i++) {
    $line = trim($rawLines[$i]);
    if (empty($line) || $line === ',,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,') continue;
    $row = str_getcsv($rawLines[$i]);
    $c0 = trim($row[0] ?? '');
    $c1 = trim($row[1] ?? '');

    if (isset($regionCodeMap[$c0])) {
        $curReg = $regionCodeMap[$c0];
        $i++;
        $yRow = str_getcsv($rawLines[$i]);
        $i++;
        $mRow = str_getcsv($rawLines[$i]);

        $kelColMap = [];
        $kYear = 0;
        for ($c = 2; $c < count($mRow); $c++) {
            if (!empty($yRow[$c])) $kYear = (int)$yRow[$c];
            $m = trim($mRow[$c]);
            if ($kYear > 0 && !empty($m)) {
                $kelColMap[$c] = [
                    'year' => $kYear,
                    'month' => $m,
                    'is_annual' => ($m === 'Tahunan')
                ];
            }
        }
        continue;
    }

    if ($curReg && is_numeric($c0) && !empty($c1)) {
        $kNum = str_pad($c0, 2, '0', STR_PAD_LEFT);
        $kName = $c1;
        $kCode = "HRG-002.{$kNum}";

        ensure_katalog(
            $mysqli, 
            $kCode, 
            "IHK: {$kName}", 
            "Harga dan daya beli", 
            "Indeks", 
            "Bulanan", 
            "BPS Jawa Timur / BPS Kabupaten/Kota", 
            "Indeks harga konsumen menurut kelompok pengeluaran {$kName} di {$curReg['nama']}.",
            $curReg['kode']
        );

        $annAverages = []; // year => [sum, count]
        foreach ($kelColMap as $c => $inf) {
            $valStr = trim($row[$c] ?? '');
            if ($valStr === '' || $valStr === '-') continue;
            $val = (float)str_replace(',', '.', $valStr);
            $yr = $inf['year'];
            $month = $inf['month'];

            if ($inf['is_annual']) {
                // Explicit annual
                upsert_record($mysqli, [
                    'kode_wilayah' => $curReg['kode'],
                    'kode_indikator' => $kCode,
                    'nama_indikator' => "IHK: {$kName}",
                    'klaster' => 'Harga dan daya beli',
                    'wilayah' => $curReg['nama'],
                    'periode' => 'Tahunan',
                    'tahun' => $yr,
                    'nilai' => $val,
                    'satuan' => 'Indeks',
                    'sumber_data' => 'BPS Jawa Timur / BPS Kabupaten Banyuwangi',
                    'definisi_operasional' => "Indeks harga konsumen menurut kelompok pengeluaran {$kName} di {$curReg['nama']} (Tahunan)."
                ]);
                $annAverages[$yr]['explicit'] = $val;
            } else {
                if (!isset($annAverages[$yr])) $annAverages[$yr] = ['sum' => 0, 'count' => 0, 'explicit' => null];
                $annAverages[$yr]['sum'] += $val;
                $annAverages[$yr]['count']++;

                // INSERT MONTHLY RECORD
                upsert_record($mysqli, [
                    'kode_wilayah' => $curReg['kode'],
                    'kode_indikator' => $kCode,
                    'nama_indikator' => "IHK: {$kName}",
                    'klaster' => 'Harga dan daya beli',
                    'wilayah' => $curReg['nama'],
                    'periode' => "Bulanan ({$month})",
                    'tahun' => $yr,
                    'nilai' => $val,
                    'satuan' => 'Indeks',
                    'sumber_data' => 'BPS Jawa Timur / BPS Kabupaten Banyuwangi',
                    'definisi_operasional' => "Indeks harga konsumen menurut kelompok pengeluaran {$kName} di {$curReg['nama']} pada bulan {$month} {$yr}."
                ]);
                $h002Count++;
            }
        }

        // Save annual average if not explicit
        foreach ($annAverages as $yr => $av) {
            if (!isset($av['explicit']) && $av['count'] > 0) {
                $meanVal = round($av['sum'] / $av['count'], 2);
                upsert_record($mysqli, [
                    'kode_wilayah' => $curReg['kode'],
                    'kode_indikator' => $kCode,
                    'nama_indikator' => "IHK: {$kName}",
                    'klaster' => 'Harga dan daya beli',
                    'wilayah' => $curReg['nama'],
                    'periode' => 'Tahunan',
                    'tahun' => $yr,
                    'nilai' => $meanVal,
                    'satuan' => 'Indeks',
                    'sumber_data' => 'BPS Jawa Timur / BPS Kabupaten Banyuwangi',
                    'definisi_operasional' => "Indeks harga konsumen menurut kelompok pengeluaran {$kName} di {$curReg['nama']} (Rata-rata Tahunan)."
                ]);
            }
        }
    }
}
echo "HRG-002 done: $h002Count monthly records upserted across 10 regions.\n";

// --- SECTION 6: HRG-006 ---
$curReg6 = $regionCodeMap['3510'];
$yRow6 = str_getcsv($rawLines[269]);
$mRow6 = str_getcsv($rawLines[270]);
$colMap6 = [];
$kYear6 = 0;
for ($c = 2; $c < count($mRow6); $c++) {
    if (!empty($yRow6[$c])) $kYear6 = (int)$yRow6[$c];
    $m = trim($mRow6[$c]);
    if ($kYear6 > 0 && !empty($m)) {
        $colMap6[$c] = [
            'year' => $kYear6,
            'month' => $m,
            'is_annual' => ($m === 'Tahunan')
        ];
    }
}

$h006Count = 0;
for ($i = 271; $i <= 281; $i++) {
    if (empty($rawLines[$i])) continue;
    $row = str_getcsv($rawLines[$i]);
    $c0 = trim($row[0] ?? '');
    $c1 = trim($row[1] ?? '');
    if (is_numeric($c0) && !empty($c1)) {
        $kNum = str_pad($c0, 2, '0', STR_PAD_LEFT);
        $kName = $c1;
        $kCode = "HRG-006.{$kNum}";

        ensure_katalog(
            $mysqli, 
            $kCode, 
            "Bobot/Andil IHK: {$kName}", 
            "Harga dan daya beli", 
            "Bobot/indeks", 
            "Bulanan", 
            "BPS Kabupaten Banyuwangi", 
            "Bobot dan andil komoditas kelompok {$kName} dalam keranjang IHK Kabupaten Banyuwangi.",
            '35.10'
        );

        $annSums = [];
        foreach ($colMap6 as $c => $inf) {
            $valStr = trim($row[$c] ?? '');
            if ($valStr === '' || $valStr === '-' || $valStr === '~0') continue;
            $val = (float)str_replace(',', '.', $valStr);
            $yr = $inf['year'];
            $month = $inf['month'];

            if ($inf['is_annual']) {
                upsert_record($mysqli, [
                    'kode_wilayah' => $curReg6['kode'],
                    'kode_indikator' => $kCode,
                    'nama_indikator' => "Bobot/Andil IHK: {$kName}",
                    'klaster' => 'Harga dan daya beli',
                    'wilayah' => $curReg6['nama'],
                    'periode' => 'Tahunan',
                    'tahun' => $yr,
                    'nilai' => $val,
                    'satuan' => 'Bobot/indeks',
                    'sumber_data' => 'BPS Kabupaten Banyuwangi',
                    'definisi_operasional' => "Bobot dan andil komoditas kelompok {$kName} dalam keranjang IHK Kabupaten Banyuwangi (Tahunan)."
                ]);
                $annSums[$yr]['explicit'] = $val;
            } else {
                if (!isset($annSums[$yr])) $annSums[$yr] = ['sum' => 0, 'count' => 0, 'explicit' => null];
                $annSums[$yr]['sum'] += $val;
                $annSums[$yr]['count']++;

                // INSERT MONTHLY RECORD
                upsert_record($mysqli, [
                    'kode_wilayah' => $curReg6['kode'],
                    'kode_indikator' => $kCode,
                    'nama_indikator' => "Bobot/Andil IHK: {$kName}",
                    'klaster' => 'Harga dan daya beli',
                    'wilayah' => $curReg6['nama'],
                    'periode' => "Bulanan ({$month})",
                    'tahun' => $yr,
                    'nilai' => $val,
                    'satuan' => 'Bobot/indeks',
                    'sumber_data' => 'BPS Kabupaten Banyuwangi',
                    'definisi_operasional' => "Bobot dan andil komoditas kelompok {$kName} dalam keranjang IHK Kabupaten Banyuwangi pada bulan {$month} {$yr}."
                ]);
                $h006Count++;
            }
        }

        foreach ($annSums as $yr => $av) {
            if (!isset($av['explicit']) && $av['count'] > 0) {
                $meanVal = round($av['sum'] / $av['count'], 2);
                upsert_record($mysqli, [
                    'kode_wilayah' => $curReg6['kode'],
                    'kode_indikator' => $kCode,
                    'nama_indikator' => "Bobot/Andil IHK: {$kName}",
                    'klaster' => 'Harga dan daya beli',
                    'wilayah' => $curReg6['nama'],
                    'periode' => 'Tahunan',
                    'tahun' => $yr,
                    'nilai' => $meanVal,
                    'satuan' => 'Bobot/indeks',
                    'sumber_data' => 'BPS Kabupaten Banyuwangi',
                    'definisi_operasional' => "Bobot dan andil komoditas kelompok {$kName} dalam keranjang IHK Kabupaten Banyuwangi (Rata-rata Tahunan)."
                ]);
            }
        }
    }
}
echo "HRG-006 done: $h006Count monthly records upserted.\n";

$mysqli->close();
echo "Import finished successfully!\n";
