<?php
/**
 * Import Script for INV Quarterly Sub-Indicators (Q1 - Q4)
 */
define('BASEPATH', 'dummy');
define('ENVIRONMENT', 'development');

require_once __DIR__ . '/inspect_excel.php';
require_once __DIR__ . '/../config/database.php';

$dbCfg = $db['default'];
$pdo = new PDO(
    "mysql:host={$dbCfg['hostname']};dbname={$dbCfg['database']};charset=utf8mb4",
    $dbCfg['username'],
    $dbCfg['password']
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Connected to database: {$dbCfg['database']}\n";

// 1. Ensure Catalog entries for Sub-Indicators
$quarters = [
    'Q1' => 'Kuartal 1 (Q1)',
    'Q2' => 'Kuartal 2 (Q2)',
    'Q3' => 'Kuartal 3 (Q3)',
    'Q4' => 'Kuartal 4 (Q4)',
];

$invParents = [
    'INV-001' => 'Realisasi investasi total PMA dan PMDN',
    'INV-002' => 'Realisasi investasi PMA',
    'INV-003' => 'Realisasi investasi PMDN'
];

$checkCatStmt = $pdo->prepare("SELECT id FROM katalog_indikator WHERE kode_indikator = ? LIMIT 1");
$insertCatStmt = $pdo->prepare("INSERT INTO katalog_indikator 
    (kode_wilayah, kode_indikator, nama_indikator, klaster, satuan, frekuensi, sumber_data, definisi_operasional, is_deleted) 
    VALUES ('35.10', ?, ?, 'Investasi, usaha, dan keuangan', 'Rupiah', 'Triwulanan', 'Kementerian Investasi/BKPM; DPMPTSP Kabupaten Banyuwangi', ?, 0)");

foreach ($invParents as $pCode => $pName) {
    foreach ($quarters as $qKey => $qLabel) {
        $subCode = "{$pCode}.{$qKey}";
        $checkCatStmt->execute([$subCode]);
        if (!$checkCatStmt->fetch()) {
            $subName = "{$pName} - {$qLabel}";
            $def = "Realisasi investasi capaian {$qLabel}";
            $insertCatStmt->execute([$subCode, $subName, $def]);
            echo "  [CATALOG ADDED] $subCode : $subName\n";
        }
    }
}

// 2. Load Region Mapping
$regStmt = $pdo->query("SELECT DISTINCT kode_wilayah, wilayah FROM database_ekonomi WHERE kode_wilayah != '35.00'");
$regionMap = $regStmt->fetchAll(PDO::FETCH_KEY_PAIR);

// 3. Process INV Excel for Quarters
$invFile = 'c:/Users/MUHAMMAD EKA/Downloads/INV-Data Dashboard ekonomi.xlsx';
$invReader = new SimpleXlsxReader($invFile);

$checkStmt = $pdo->prepare("SELECT id FROM database_ekonomi WHERE kode_wilayah = ? AND kode_indikator = ? AND tahun = ? LIMIT 1");

$updateStmt = $pdo->prepare("UPDATE database_ekonomi SET 
    nilai = ?, 
    satuan = 'Rupiah', 
    periode = 'Triwulanan',
    status_validasi = 'Tervalidasi', 
    diverifikasi_oleh = 'Administrator', 
    catatan = 'Diperbarui via Import Excel INV Kuartal', 
    is_deleted = 0 
    WHERE id = ?");

$insertStmt = $pdo->prepare("INSERT INTO database_ekonomi 
    (kode_wilayah, kode_indikator, nama_indikator, klaster, wilayah, periode, tahun, nilai, satuan, sumber_data, definisi_operasional, status_validasi, diverifikasi_oleh, catatan, is_deleted) 
    VALUES (?, ?, ?, 'Investasi, usaha, dan keuangan', ?, 'Triwulanan', ?, ?, 'Rupiah', 'Kementerian Investasi/BKPM; DPMPTSP Kabupaten Banyuwangi', ?, 'Tervalidasi', 'Administrator', 'Diimpor via Excel INV Kuartal', 0)");

$stats = ['inserted' => 0, 'updated' => 0, 'by_quarter' => []];

$pdo->beginTransaction();

try {
    foreach (['INV-001', 'INV-002', 'INV-003'] as $sheetName) {
        echo "Processing quarters for {$sheetName}...\n";
        $rows = $invReader->getRows($sheetName, 100);
        if (count($rows) < 4) continue;

        $rowYears = $rows[1];
        $rowQ = $rows[2];

        // Map column index to year & quarter
        $colMap = [];
        $curYear = null;
        for ($c = 2; $c < count($rowQ); $c++) {
            if (!empty($rowYears[$c]) && preg_match('/^(19|20)\d{2}$/', trim($rowYears[$c]), $ym)) {
                $curYear = (int)$ym[0];
            }
            $q = strtoupper(trim($rowQ[$c] ?? ''));
            if ($curYear !== null && in_array($q, ['Q1', 'Q2', 'Q3', 'Q4'])) {
                $colMap[$c] = ['year' => $curYear, 'quarter' => $q];
            }
        }

        $pName = $invParents[$sheetName];

        for ($rIdx = 3; $rIdx < count($rows); $rIdx++) {
            $row = $rows[$rIdx];
            $rawCode = trim($row[0] ?? '');
            if (empty($rawCode)) continue;
            $regCode = substr($rawCode, 0, 2) . '.' . substr($rawCode, 2, 2);
            $regName = $regionMap[$regCode] ?? trim($row[1] ?? '');
            if (empty($regName)) continue;

            foreach ($colMap as $c => $info) {
                $year = $info['year'];
                $q = $info['quarter'];
                $cellVal = trim($row[$c] ?? '');

                if ($cellVal !== '' && is_numeric($cellVal)) {
                    $nominalRupiah = round((float)$cellVal * 1000000000000, 2);
                    $subCode = "{$sheetName}.{$q}";
                    $subName = "{$pName} - Kuartal " . substr($q, 1) . " ({$q})";
                    $def = "Realisasi investasi {$pName} pada kuartal {$q}";

                    $checkStmt->execute([$regCode, $subCode, $year]);
                    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

                    if ($existing) {
                        $updateStmt->execute([$nominalRupiah, $existing['id']]);
                        $stats['updated']++;
                    } else {
                        $insertStmt->execute([$regCode, $subCode, $subName, $regName, $year, $nominalRupiah, $def]);
                        $stats['inserted']++;
                    }

                    if (!isset($stats['by_quarter'][$subCode])) {
                        $stats['by_quarter'][$subCode] = 0;
                    }
                    $stats['by_quarter'][$subCode]++;
                }
            }
        }
    }

    $pdo->commit();
    echo "Quarterly import committed successfully!\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR in quarterly import: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Summary:\n";
echo "Total Inserted: {$stats['inserted']}\n";
echo "Total Updated : {$stats['updated']}\n";
foreach ($stats['by_quarter'] as $code => $cnt) {
    echo "  {$code} : {$cnt} records\n";
}
