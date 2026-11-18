<?php
require __DIR__ . '/inspect_xlsx.php';
define('BASEPATH', 'dummy');
require __DIR__ . '/application/config/database.php';
$dbCfg = $db['default'];
$pdo = new PDO("mysql:host={$dbCfg['hostname']};dbname={$dbCfg['database']};charset=utf8mb4", $dbCfg['username'], $dbCfg['password']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$xlsxPath = 'C:/Users/MUHAMMAD EKA/.gemini/antigravity/brain/05deb551-0394-4a04-8e55-bd1dd91454af/.user_uploaded/media_1794908089409_0852a346.xlsx';
$parser = new SimpleXLSXParser($xlsxPath);

$regionMapByCode = [
    '3501' => ['kode' => '35.01', 'nama' => 'Kabupaten Pacitan'],
    '3502' => ['kode' => '35.02', 'nama' => 'Kabupaten Ponorogo'],
    '3503' => ['kode' => '35.03', 'nama' => 'Kabupaten Trenggalek'],
    '3504' => ['kode' => '35.04', 'nama' => 'Kabupaten Tulungagung'],
    '3505' => ['kode' => '35.05', 'nama' => 'Kabupaten Blitar'],
    '3506' => ['kode' => '35.06', 'nama' => 'Kabupaten Kediri'],
    '3507' => ['kode' => '35.07', 'nama' => 'Kabupaten Malang'],
    '3508' => ['kode' => '35.08', 'nama' => 'Kabupaten Lumajang'],
    '3509' => ['kode' => '35.09', 'nama' => 'Kabupaten Jember'],
    '3510' => ['kode' => '35.10', 'nama' => 'Kabupaten Banyuwangi'],
    '3511' => ['kode' => '35.11', 'nama' => 'Kabupaten Bondowoso'],
    '3512' => ['kode' => '35.12', 'nama' => 'Kabupaten Situbondo'],
    '3513' => ['kode' => '35.13', 'nama' => 'Kabupaten Probolinggo'],
    '3514' => ['kode' => '35.14', 'nama' => 'Kabupaten Pasuruan'],
    '3515' => ['kode' => '35.15', 'nama' => 'Kabupaten Sidoarjo'],
    '3516' => ['kode' => '35.16', 'nama' => 'Kabupaten Mojokerto'],
    '3517' => ['kode' => '35.17', 'nama' => 'Kabupaten Jombang'],
    '3518' => ['kode' => '35.18', 'nama' => 'Kabupaten Nganjuk'],
    '3519' => ['kode' => '35.19', 'nama' => 'Kabupaten Madiun'],
    '3520' => ['kode' => '35.20', 'nama' => 'Kabupaten Magetan'],
    '3521' => ['kode' => '35.21', 'nama' => 'Kabupaten Ngawi'],
    '3522' => ['kode' => '35.22', 'nama' => 'Kabupaten Bojonegoro'],
    '3523' => ['kode' => '35.23', 'nama' => 'Kabupaten Tuban'],
    '3524' => ['kode' => '35.24', 'nama' => 'Kabupaten Lamongan'],
    '3525' => ['kode' => '35.25', 'nama' => 'Kabupaten Gresik'],
    '3526' => ['kode' => '35.26', 'nama' => 'Kabupaten Bangkalan'],
    '3527' => ['kode' => '35.27', 'nama' => 'Kabupaten Sampang'],
    '3528' => ['kode' => '35.28', 'nama' => 'Kabupaten Pamekasan'],
    '3529' => ['kode' => '35.29', 'nama' => 'Kabupaten Sumenep'],
    '3571' => ['kode' => '35.71', 'nama' => 'Kota Kediri'],
    '3572' => ['kode' => '35.72', 'nama' => 'Kota Blitar'],
    '3573' => ['kode' => '35.73', 'nama' => 'Kota Malang'],
    '3574' => ['kode' => '35.74', 'nama' => 'Kota Probolinggo'],
    '3575' => ['kode' => '35.75', 'nama' => 'Kota Pasuruan'],
    '3576' => ['kode' => '35.76', 'nama' => 'Kota Mojokerto'],
    '3577' => ['kode' => '35.77', 'nama' => 'Kota Madiun'],
    '3578' => ['kode' => '35.78', 'nama' => 'Kota Surabaya'],
    '3579' => ['kode' => '35.79', 'nama' => 'Kota Batu'],
];

function findRegionByName($raw, $regionMap) {
    $clean = strtolower(trim($raw));
    $clean = preg_replace('/^(kabupaten|kab\.|kota)\s+/i', '', $clean);
    $clean = trim($clean);
    
    foreach ($regionMap as $code => $info) {
        $regClean = strtolower(preg_replace('/^(kabupaten|kota)\s+/i', '', $info['nama']));
        if ($clean === $regClean) {
            return $info;
        }
    }
    return null;
}

// Prepared Statements untuk Database Ekonomi
$stmtCheck = $pdo->prepare("SELECT id FROM database_ekonomi WHERE kode_wilayah = ? AND kode_indikator = ? AND tahun = ? LIMIT 1");
$stmtUpdate = $pdo->prepare("UPDATE database_ekonomi SET nilai = ?, satuan = ?, nama_indikator = ?, wilayah = ?, klaster = ?, sumber_data = ?, definisi_operasional = ?, status_validasi = 'Tervalidasi', is_deleted = 0 WHERE id = ?");
$stmtInsert = $pdo->prepare("INSERT INTO database_ekonomi (kode_wilayah, kode_indikator, nama_indikator, klaster, wilayah, periode, tahun, nilai, satuan, sumber_data, definisi_operasional, status_validasi, is_deleted, dibuat_pada) VALUES (?, ?, ?, ?, ?, 'Tahunan', ?, ?, ?, ?, ?, 'Tervalidasi', 0, NOW())");

// Prepared Statements untuk Katalog Indikator
$stmtCatCheck = $pdo->prepare("SELECT id FROM katalog_indikator WHERE kode_indikator = ? LIMIT 1");
$stmtCatUpdate = $pdo->prepare("UPDATE katalog_indikator SET nama_indikator = ?, klaster = ?, satuan = ?, sumber_data = ?, definisi_operasional = ?, is_deleted = 0 WHERE id = ?");
$stmtCatInsert = $pdo->prepare("INSERT INTO katalog_indikator (kode_indikator, nama_indikator, klaster, satuan, frekuensi, sumber_data, definisi_operasional, is_deleted) VALUES (?, ?, ?, ?, 'Tahunan', ?, ?, 0)");

function ensureCatalog($pdo, $stmtCatCheck, $stmtCatUpdate, $stmtCatInsert, $code, $name, $cluster, $unit, $source, $def) {
    $stmtCatCheck->execute([$code]);
    $cat = $stmtCatCheck->fetch(PDO::FETCH_ASSOC);
    if ($cat) {
        $stmtCatUpdate->execute([$name, $cluster, $unit, $source, $def, $cat['id']]);
    } else {
        $stmtCatInsert->execute([$code, $name, $cluster, $unit, $source, $def]);
    }
}

function saveDbRecord($stmtCheck, $stmtUpdate, $stmtInsert, $kodeWil, $namaWil, $kodeInd, $namaInd, $klaster, $tahun, $nilai, $satuan, $sumber, $definisi) {
    $stmtCheck->execute([$kodeWil, $kodeInd, $tahun]);
    $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    if ($existing) {
        $stmtUpdate->execute([$nilai, $satuan, $namaInd, $namaWil, $klaster, $sumber, $definisi, $existing['id']]);
    } else {
        $stmtInsert->execute([$kodeWil, $kodeInd, $namaInd, $klaster, $namaWil, $tahun, $nilai, $satuan, $sumber, $definisi]);
    }
}

$sectorDefs = [
    'A' => ['key' => 'A', 'name' => 'Pertanian, Kehutanan, dan Perikanan', 'def' => 'Nilai tambah bruto lapangan usaha pertanian, kehutanan, dan perikanan.'],
    'B' => ['key' => 'B', 'name' => 'Pertambangan dan Penggalian', 'def' => 'Nilai tambah bruto lapangan usaha pertambangan dan penggalian.'],
    'C' => ['key' => 'C', 'name' => 'Industri Pengolahan', 'def' => 'Nilai tambah bruto lapangan usaha industri pengolahan.'],
    'D' => ['key' => 'D', 'name' => 'Pengadaan Listrik dan Gas', 'def' => 'Nilai tambah bruto lapangan usaha pengadaan listrik dan gas.'],
    'E' => ['key' => 'E', 'name' => 'Pengadaan Air, Pengelolaan Sampah, Limbah', 'def' => 'Nilai tambah bruto lapangan usaha pengadaan air, pengelolaan sampah, limbah dan daur ulang.'],
    'F' => ['key' => 'F', 'name' => 'Konstruksi', 'def' => 'Nilai tambah bruto lapangan usaha konstruksi.'],
    'G' => ['key' => 'G', 'name' => 'Perdagangan Besar dan Eceran; Reparasi Mobil', 'def' => 'Nilai tambah bruto perdagangan besar dan eceran; reparasi mobil dan sepeda motor.'],
    'H' => ['key' => 'H', 'name' => 'Transportasi dan Pergudangan', 'def' => 'Nilai tambah bruto lapangan usaha transportasi dan pergudangan.'],
    'I' => ['key' => 'I', 'name' => 'Penyediaan Akomodasi dan Makan Minum', 'def' => 'Nilai tambah bruto penyediaan akomodasi dan penyediaan makan minum.'],
    'J' => ['key' => 'J', 'name' => 'Informasi dan Komunikasi', 'def' => 'Nilai tambah bruto lapangan usaha informasi dan komunikasi.'],
    'K' => ['key' => 'K', 'name' => 'Jasa Keuangan dan Asuransi', 'def' => 'Nilai tambah bruto lapangan usaha jasa keuangan dan asuransi.'],
    'L' => ['key' => 'L', 'name' => 'Real Estate', 'def' => 'Nilai tambah bruto lapangan usaha real estate.'],
    'MN' => ['key' => 'MN', 'name' => 'Jasa Perusahaan', 'def' => 'Nilai tambah bruto lapangan usaha jasa perusahaan.'],
    'O' => ['key' => 'O', 'name' => 'Adm. Pemerintah, Pertahanan & Jaminan Sosial Wajib', 'def' => 'Nilai tambah bruto administrasi pemerintahan, pertahanan dan jaminan sosial wajib.'],
    'P' => ['key' => 'P', 'name' => 'Jasa Pendidikan', 'def' => 'Nilai tambah bruto lapangan usaha jasa pendidikan.'],
    'Q' => ['key' => 'Q', 'name' => 'Jasa Kesehatan dan Kegiatan Sosial', 'def' => 'Nilai tambah bruto lapangan usaha jasa kesehatan dan kegiatan sosial.'],
    'RSTU' => ['key' => 'RSTU', 'name' => 'Jasa lainnya', 'def' => 'Nilai tambah bruto lapangan usaha jasa lainnya.'],
    'TM' => ['key' => 'TM', 'name' => 'PDRB Tanpa Migas', 'def' => 'Total produk domestik regional bruto dikurangi sektor minyak dan gas bumi.']
];

$expComponents = [
    '1' => ['key' => '1', 'name' => 'Pengeluaran Konsumsi Rumah Tangga', 'def' => 'Pengeluaran atas barang dan jasa yang dilakukan oleh rumah tangga untuk tujuan konsumsi.'],
    '2' => ['key' => '2', 'name' => 'Pengeluaran Konsumsi LNPRT', 'def' => 'Pengeluaran konsumsi lembaga non-profit yang melayani rumah tangga.'],
    '3' => ['key' => '3', 'name' => 'Pengeluaran Konsumsi Pemerintah', 'def' => 'Pengeluaran pemerintah untuk barang dan jasa bagi kepentingan kolektif maupun individual.'],
    '4' => ['key' => '4', 'name' => 'Pembentukan Modal Tetap Bruto', 'def' => 'Pengeluaran unit produksi untuk menambah aset tetap dikurangi pelepasan aset tetap.'],
    '5' => ['key' => '5', 'name' => 'Perubahan Inventori', 'def' => 'Perubahan nilai stok persediaan barang pada awal dan akhir periode akuntansi.'],
    '6' => ['key' => '6', 'name' => 'Net Ekspor', 'def' => 'Nilai ekspor neto (selisih ekspor barang/jasa luar daerah dikurangi impor).']
];

// REGISTER MASTER SUB-INDIKATORS DALAM KATALOG
echo "--- Registering Sub-Indicators in Katalog ---\n";
// Sektoral untuk EKO-003, EKO-004, EKO-006, EKO-007
foreach ($sectorDefs as $k => $sd) {
    ensureCatalog($pdo, $stmtCatCheck, $stmtCatUpdate, $stmtCatInsert, "EKO-003.{$sd['key']}", "PDRB ADHB: {$sd['name']}", "Produksi sektoral", "Miliar rupiah", "BPS Kabupaten/Kota", $sd['def']);
    ensureCatalog($pdo, $stmtCatCheck, $stmtCatUpdate, $stmtCatInsert, "EKO-004.{$sd['key']}", "PDRB ADHK: {$sd['name']}", "Produksi sektoral", "Miliar rupiah", "BPS Kabupaten/Kota", $sd['def']);
    ensureCatalog($pdo, $stmtCatCheck, $stmtCatUpdate, $stmtCatInsert, "EKO-006.{$sd['key']}", "Pertumbuhan: {$sd['name']}", "Produksi sektoral", "Persen", "BPS Kabupaten/Kota", "Laju pertumbuhan tahunan lapangan usaha {$sd['name']}.");
    ensureCatalog($pdo, $stmtCatCheck, $stmtCatUpdate, $stmtCatInsert, "EKO-007.{$sd['key']}", "Kontribusi: {$sd['name']}", "Produksi sektoral", "Persen", "BPS Kabupaten/Kota", "Pangsa/kontribusi lapangan usaha {$sd['name']} terhadap total PDRB.");
}

// Komponen Pengeluaran untuk EKO-015 dan EKO-016
foreach ($expComponents as $k => $ec) {
    ensureCatalog($pdo, $stmtCatCheck, $stmtCatUpdate, $stmtCatInsert, "EKO-015.{$ec['key']}", "PDRB Pengeluaran ADHB: {$ec['name']}", "Output dan pertumbuhan", "Miliar rupiah", "BPS Kabupaten/Kota", $ec['def']);
    ensureCatalog($pdo, $stmtCatCheck, $stmtCatUpdate, $stmtCatInsert, "EKO-016.{$ec['key']}", "PDRB Pengeluaran ADHK: {$ec['name']}", "Output dan pertumbuhan", "Miliar rupiah", "BPS Kabupaten/Kota", $ec['def']);
}
echo "Katalog sub-indikator registered successfully!\n\n";

$pdo->beginTransaction();

$stats = [];

// Helper untuk parsing sheet tipe 1 (Tabel Tunggal: Kode, Wilayah, 2016-2025)
function parseSingleTableSheet($parser, $sheetName, $kodeInd, $namaInd, $klaster, $satuan, $sumber, $definisi, $regionMapByCode, $stmtCheck, $stmtUpdate, $stmtInsert, &$stats) {
    $rows = $parser->getSheetData($sheetName);
    $count = 0;
    $yearsHeader = [];
    
    foreach ($rows as $r) {
        if (in_array('2024', $r) && in_array('2025', $r)) {
            $yearsHeader = $r;
            continue;
        }
        if (empty($yearsHeader)) continue;
        $rawCode = trim($r[0] ?? '');
        $rawName = trim($r[1] ?? '');
        if (empty($rawCode) && empty($rawName)) continue;
        
        $info = null;
        if (isset($regionMapByCode[$rawCode])) {
            $info = $regionMapByCode[$rawCode];
        } elseif (stripos($rawName, 'jatim') !== false) {
            $info = ['kode' => '35.00', 'nama' => 'Provinsi Jawa Timur'];
        }
        if ($info) {
            for ($c = 2; $c < count($r); $c++) {
                $yr = (int)($yearsHeader[$c] ?? 0);
                $valStr = trim((string)($r[$c] ?? ''));
                if ($yr >= 2016 && $yr <= 2025 && $valStr !== '' && is_numeric($valStr)) {
                    saveDbRecord($stmtCheck, $stmtUpdate, $stmtInsert, $info['kode'], $info['nama'], $kodeInd, $namaInd, $klaster, $yr, (float)$valStr, $satuan, $sumber, $definisi);
                    $count++;
                }
            }
        }
    }
    $stats[$sheetName] = $count;
    echo "Sheet $sheetName ($kodeInd): $count records saved.\n";
}

// 1. EKO-001
parseSingleTableSheet($parser, 'EKO-001', 'EKO-001', 'PDRB atas dasar harga berlaku (ADHB) – total', 'Output dan pertumbuhan', 'Miliar rupiah', 'BPS Provinsi Jawa Timur', 'Nilai seluruh barang dan jasa akhir yang dihasilkan oleh seluruh unit ekonomi di suatu wilayah dalam satu tahun atas dasar harga berlaku.', $regionMapByCode, $stmtCheck, $stmtUpdate, $stmtInsert, $stats);

// 2. EKO-002
parseSingleTableSheet($parser, 'EKO-002', 'EKO-002', 'PDRB atas dasar harga konstan (ADHK) – total', 'Output dan pertumbuhan', 'Miliar rupiah', 'BPS Provinsi Jawa Timur', 'Nilai seluruh barang dan jasa akhir yang dihasilkan oleh seluruh unit ekonomi dinilai berdasarkan harga tahun dasar 2010.', $regionMapByCode, $stmtCheck, $stmtUpdate, $stmtInsert, $stats);

// 3. EKO-005 (Pertumbuhan Ekonomi)
parseSingleTableSheet($parser, 'EKO-005', 'EKO-005', 'Pertumbuhan ekonomi (PDRB ADHK)', 'Output dan pertumbuhan', 'Persen', 'BPS Provinsi Jawa Timur', 'Laju pertumbuhan PDRB atas dasar harga konstan 2010 mencerminkan pertumbuhan ekonomi riil daerah.', $regionMapByCode, $stmtCheck, $stmtUpdate, $stmtInsert, $stats);

// 4. EKO-008 (PDRB per kapita ADHB)
parseSingleTableSheet($parser, 'EKO-008', 'EKO-008', 'PDRB per kapita ADHB', 'Output dan pertumbuhan', 'Rupiah/kapita/tahun', 'BPS Provinsi Jawa Timur', 'PDRB atas dasar harga berlaku dibagi jumlah penduduk pertengahan tahun.', $regionMapByCode, $stmtCheck, $stmtUpdate, $stmtInsert, $stats);

// 5. EKO-009 (PDRB per kapita ADHK)
parseSingleTableSheet($parser, 'EKO-009', 'EKO-009', 'PDRB per kapita ADHK', 'Output dan pertumbuhan', 'Rupiah konstan/kapita/tahun', 'BPS Provinsi Jawa Timur', 'PDRB atas dasar harga konstan dibagi jumlah penduduk pertengahan tahun.', $regionMapByCode, $stmtCheck, $stmtUpdate, $stmtInsert, $stats);

// Helper untuk parsing sheet sektoral (EKO-003, EKO-004, EKO-006, EKO-007)
function parseSectoralSheet($parser, $sheetName, $parentCode, $parentTitle, $klaster, $satuan, $sumber, $minYear, $sectorDefs, $regionMapByCode, $stmtCheck, $stmtUpdate, $stmtInsert, &$stats) {
    $rows = $parser->getSheetData($sheetName);
    $count = 0;
    $currentRegion = null;
    $yearsHeader = [];
    
    foreach ($rows as $r) {
        $firstCell = trim((string)($r[0] ?? ''));
        $secondCell = trim((string)($r[1] ?? ''));
        
        // Deteksi Header Wilayah
        if (!empty($firstCell) && empty($secondCell) && !in_array($firstCell, ['A','B','C','D','E','F','G','H','I','J','K','L','M,N','MN','O','P','Q','R,S,T,U','RSTU'])) {
            $matched = findRegionByName($firstCell, $regionMapByCode);
            if ($matched) {
                $currentRegion = $matched;
                continue;
            }
        }
        
        // Deteksi baris header tahun
        if (in_array('2024', $r) && in_array('2025', $r)) {
            $yearsHeader = $r;
            continue;
        }
        
        if (!$currentRegion || empty($yearsHeader)) continue;
        
        $subKey = null;
        $indName = null;
        $def = '';
        
        if (in_array($firstCell, ['A','B','C','D','E','F','G','H','I','J','K','L','M,N','MN','O','P','Q','R,S,T,U','RSTU'])) {
            $cleanKey = ($firstCell === 'M,N') ? 'MN' : (($firstCell === 'R,S,T,U') ? 'RSTU' : $firstCell);
            $subKey = "{$parentCode}.{$cleanKey}";
            $sName = $sectorDefs[$cleanKey]['name'] ?? $secondCell;
            $indName = "{$parentTitle}: {$sName}";
            $def = $sectorDefs[$cleanKey]['def'] ?? '';
        } elseif (stripos($secondCell, 'PRODUK DOMESTIK REGIONAL BRUTO') !== false) {
            $subKey = $parentCode;
            $indName = $parentTitle;
            $def = "Total {$parentTitle} seluruh sektor.";
        } elseif (stripos($secondCell, 'TANPA MIGAS') !== false) {
            $subKey = "{$parentCode}.TM";
            $indName = "{$parentTitle}: PDRB Tanpa Migas";
            $def = "Total {$parentTitle} dikurangi minyak dan gas bumi.";
        }
        
        if ($subKey) {
            for ($c = 2; $c < count($r); $c++) {
                $yr = (int)($yearsHeader[$c] ?? 0);
                $valStr = trim((string)($r[$c] ?? ''));
                if ($yr >= $minYear && $yr <= 2025 && $valStr !== '' && is_numeric($valStr)) {
                    saveDbRecord($stmtCheck, $stmtUpdate, $stmtInsert, $currentRegion['kode'], $currentRegion['nama'], $subKey, $indName, $klaster, $yr, (float)$valStr, $satuan, $sumber, $def);
                    $count++;
                }
            }
        }
    }
    $stats[$sheetName] = $count;
    echo "Sheet $sheetName ($parentCode): $count records saved.\n";
}

// 6. EKO-003 (PDRB ADHB Lapangan Usaha 2018-2025)
parseSectoralSheet($parser, 'EKO-003', 'EKO-003', 'PDRB ADHB menurut lapangan usaha', 'Produksi sektoral', 'Miliar rupiah', 'BPS Kabupaten/Kota', 2018, $sectorDefs, $regionMapByCode, $stmtCheck, $stmtUpdate, $stmtInsert, $stats);

// 7. EKO-004 (PDRB ADHK Lapangan Usaha 2021-2025)
parseSectoralSheet($parser, 'EKO-004', 'EKO-004', 'PDRB ADHK menurut lapangan usaha', 'Produksi sektoral', 'Miliar rupiah', 'BPS Kabupaten/Kota', 2021, $sectorDefs, $regionMapByCode, $stmtCheck, $stmtUpdate, $stmtInsert, $stats);

// 8. EKO-006 (Pertumbuhan PDRB Lapangan Usaha 2021-2025)
parseSectoralSheet($parser, 'EKO-006', 'EKO-006', 'Pertumbuhan PDRB menurut lapangan usaha', 'Produksi sektoral', 'Persen', 'BPS Kabupaten/Kota', 2021, $sectorDefs, $regionMapByCode, $stmtCheck, $stmtUpdate, $stmtInsert, $stats);

// 9. EKO-007 (Kontribusi/Share Lapangan Usaha 2021-2025)
parseSectoralSheet($parser, 'EKO-007', 'EKO-007', 'Kontribusi / share lapangan usaha terhadap PDRB', 'Produksi sektoral', 'Persen', 'BPS Kabupaten/Kota', 2021, $sectorDefs, $regionMapByCode, $stmtCheck, $stmtUpdate, $stmtInsert, $stats);

// Helper untuk parsing sheet komponen pengeluaran (EKO-015, EKO-016)
function parseExpenditureSheet($parser, $sheetName, $parentCode, $parentTitle, $klaster, $satuan, $sumber, $expComponents, $regionMapByCode, $stmtCheck, $stmtUpdate, $stmtInsert, &$stats) {
    $rows = $parser->getSheetData($sheetName);
    $count = 0;
    $currentRegion = null;
    $yearsHeader = [];
    
    foreach ($rows as $r) {
        $firstCell = trim((string)($r[0] ?? ''));
        $secondCell = trim((string)($r[1] ?? ''));
        
        if (!empty($firstCell) && empty($secondCell) && !is_numeric($firstCell)) {
            $matched = findRegionByName($firstCell, $regionMapByCode);
            if ($matched) {
                $currentRegion = $matched;
                continue;
            }
        }
        
        if (in_array('2024', $r) && in_array('2025', $r)) {
            $yearsHeader = $r;
            continue;
        }
        
        if (!$currentRegion || empty($yearsHeader)) continue;
        
        $subKey = null;
        $indName = null;
        $def = '';
        
        if (is_numeric($firstCell) && (int)$firstCell >= 1 && (int)$firstCell <= 6) {
            $cNum = (string)(int)$firstCell;
            $subKey = "{$parentCode}.{$cNum}";
            $cName = $expComponents[$cNum]['name'] ?? $secondCell;
            $indName = "{$parentTitle}: {$cName}";
            $def = $expComponents[$cNum]['def'] ?? '';
        } elseif (stripos($secondCell, 'PDRB') !== false) {
            $subKey = $parentCode;
            $indName = $parentTitle;
            $def = "Total {$parentTitle}.";
        }
        
        if ($subKey) {
            for ($c = 2; $c < count($r); $c++) {
                $yr = (int)($yearsHeader[$c] ?? 0);
                $valStr = trim((string)($r[$c] ?? ''));
                if ($yr >= 2021 && $yr <= 2025 && $valStr !== '' && is_numeric($valStr)) {
                    saveDbRecord($stmtCheck, $stmtUpdate, $stmtInsert, $currentRegion['kode'], $currentRegion['nama'], $subKey, $indName, $klaster, $yr, (float)$valStr, $satuan, $sumber, $def);
                    $count++;
                }
            }
        }
    }
    $stats[$sheetName] = $count;
    echo "Sheet $sheetName ($parentCode): $count records saved.\n";
}

// 10. EKO-015 (PDRB Pengeluaran ADHB 2021-2025)
parseExpenditureSheet($parser, 'EKO-015', 'EKO-015', 'PDRB Pengeluaran ADHB', 'Output dan pertumbuhan', 'Miliar rupiah', 'BPS Kabupaten/Kota', $expComponents, $regionMapByCode, $stmtCheck, $stmtUpdate, $stmtInsert, $stats);

// 11. EKO-016 (PDRB Pengeluaran ADHK 2021-2025)
parseExpenditureSheet($parser, 'EKO-016', 'EKO-016', 'PDRB Pengeluaran ADHK', 'Output dan pertumbuhan', 'Miliar rupiah', 'BPS Kabupaten/Kota', $expComponents, $regionMapByCode, $stmtCheck, $stmtUpdate, $stmtInsert, $stats);

// 12. EKO-010, EKO-011, EKO-012 (Single components with ADHB / ADHK blocks)
function parseTwoBlockSheet($parser, $sheetName, $kodeInd, $namaInd, $klaster, $satuan, $sumber, $definisi, $regionMapByCode, $stmtCheck, $stmtUpdate, $stmtInsert, &$stats) {
    $rows = $parser->getSheetData($sheetName);
    $count = 0;
    $currentBlock = null;
    $yearsHeader = [];
    
    foreach ($rows as $r) {
        $firstCell = strtoupper(trim((string)($r[0] ?? '')));
        if ($firstCell === 'ADHB' || $firstCell === 'ADHK') {
            $currentBlock = $firstCell;
            $yearsHeader = [];
            continue;
        }
        
        if (in_array('2024', $r) && in_array('2025', $r)) {
            $yearsHeader = $r;
            continue;
        }
        
        if (empty($yearsHeader)) continue;
        
        $rawCode = trim((string)($r[0] ?? ''));
        $rawName = trim((string)($r[1] ?? ''));
        if (empty($rawCode) && empty($rawName)) continue;
        
        $info = null;
        if (isset($regionMapByCode[$rawCode])) {
            $info = $regionMapByCode[$rawCode];
        } elseif (stripos($rawName, 'jatim') !== false) {
            $info = ['kode' => '35.00', 'nama' => 'Provinsi Jawa Timur'];
        }
        
        if ($info) {
            // Jika ADHB gunakan $kodeInd, jika ADHK kita bisa gunakan $kodeInd atau target khusus
            $targetCode = ($currentBlock === 'ADHK') ? "{$kodeInd}.K" : $kodeInd;
            $targetTitle = ($currentBlock === 'ADHK') ? "{$namaInd} (ADHK)" : "{$namaInd} (ADHB)";
            
            for ($c = 2; $c < count($r); $c++) {
                $yr = (int)($yearsHeader[$c] ?? 0);
                $valStr = trim((string)($r[$c] ?? ''));
                if ($yr >= 2021 && $yr <= 2025 && $valStr !== '' && is_numeric($valStr)) {
                    saveDbRecord($stmtCheck, $stmtUpdate, $stmtInsert, $info['kode'], $info['nama'], $targetCode, $targetTitle, $klaster, $yr, (float)$valStr, $satuan, $sumber, $definisi);
                    $count++;
                }
            }
        }
    }
    $stats[$sheetName] = $count;
    echo "Sheet $sheetName: $count records saved.\n";
}

parseTwoBlockSheet($parser, 'EKO-010', 'EKO-010', 'PDRB menurut pengeluaran – konsumsi rumah tangga', 'Output dan pertumbuhan', 'Miliar rupiah', 'BPS Kabupaten/Kota', 'Pengeluaran atas barang dan jasa yang dilakukan oleh rumah tangga untuk tujuan konsumsi.', $regionMapByCode, $stmtCheck, $stmtUpdate, $stmtInsert, $stats);

parseTwoBlockSheet($parser, 'EKO-011', 'EKO-011', 'Pembentukan Modal Tetap Bruto (PMTB)', 'Output dan pertumbuhan', 'Miliar rupiah', 'BPS Kabupaten/Kota', 'Pengeluaran unit produksi untuk menambah aset tetap dikurangi pelepasan aset tetap.', $regionMapByCode, $stmtCheck, $stmtUpdate, $stmtInsert, $stats);

parseTwoBlockSheet($parser, 'EKO-012', 'EKO-012', 'Konsumsi pemerintah dalam PDRB', 'Output dan pertumbuhan', 'Miliar rupiah', 'BPS Kabupaten/Kota', 'Pengeluaran pemerintah untuk barang dan jasa bagi kepentingan kolektif maupun individual.', $regionMapByCode, $stmtCheck, $stmtUpdate, $stmtInsert, $stats);

// 13. UPDATE PERBANDINGAN DAERAH DENGAN DATA EKO-005 (Pertumbuhan Ekonomi 2016-2025)
echo "\n--- Updating perbandingan_daerah ---\n";
$stmtPerbCheck = $pdo->prepare("SELECT id FROM perbandingan_daerah WHERE kode_wilayah = ? LIMIT 1");
$stmtPerbUpdate = $pdo->prepare("UPDATE perbandingan_daerah SET nama_wilayah = ?, nilai_pertumbuhan = ?, perubahan = ?, deret_historis = ?, is_deleted = 0 WHERE id = ?");
$stmtPerbInsert = $pdo->prepare("INSERT INTO perbandingan_daerah (kode_wilayah, nama_wilayah, nilai_pertumbuhan, perubahan, deret_historis, is_deleted) VALUES (?, ?, ?, ?, ?, 0)");

$eko5Rows = $parser->getSheetData('EKO-005');
$eko5Years = [];
$perbCount = 0;

foreach ($eko5Rows as $r) {
    if (in_array('2024', $r) && in_array('2025', $r)) {
        $eko5Years = $r;
        continue;
    }
    if (empty($eko5Years)) continue;
    
    $rawCode = trim((string)($r[0] ?? ''));
    if (!isset($regionMapByCode[$rawCode])) continue;
    $reg = $regionMapByCode[$rawCode];
    
    $series = [];
    for ($c = 2; $c < count($r); $c++) {
        $yr = (int)($eko5Years[$c] ?? 0);
        $valStr = trim((string)($r[$c] ?? ''));
        if ($yr >= 2016 && $yr <= 2025 && $valStr !== '' && is_numeric($valStr)) {
            $series[] = (float)$valStr;
        }
    }
    
    if (count($series) >= 2) {
        $latest = $series[count($series) - 1]; // 2025
        $prev = $series[count($series) - 2];   // 2024
        $change = round($latest - $prev, 2);
        $jsonSeries = json_encode($series);
        
        $stmtPerbCheck->execute([$reg['kode']]);
        $row = $stmtPerbCheck->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $stmtPerbUpdate->execute([$reg['nama'], $latest, $change, $jsonSeries, $row['id']]);
        } else {
            $stmtPerbInsert->execute([$reg['kode'], $reg['nama'], $latest, $change, $jsonSeries]);
        }
        $perbCount++;
    }
}
echo "perbandingan_daerah updated for $perbCount regions.\n";

$pdo->commit();
$parser->close();

echo "\n============================================\n";
echo "IMPORT COMPLETED SUCCESSFULLY!\n";
echo "Total Records Processed Across All Sheets:\n";
$grandTotal = 0;
foreach ($stats as $s => $cnt) {
    echo "  - $s: $cnt records\n";
    $grandTotal += $cnt;
}
echo "GRAND TOTAL: $grandTotal records stored in database_ekonomi.\n";
echo "============================================\n";

