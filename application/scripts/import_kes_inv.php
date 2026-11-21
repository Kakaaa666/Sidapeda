<?php
/**
 * Import Script for KES and INV Indicators into database_ekonomi & early_warning
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

// 1. Load Region Mapping (35.01 to 35.79)
$regStmt = $pdo->query("SELECT DISTINCT kode_wilayah, wilayah FROM database_ekonomi WHERE kode_wilayah != '35.00'");
$regionMap = $regStmt->fetchAll(PDO::FETCH_KEY_PAIR);
echo "Loaded " . count($regionMap) . " region mappings.\n";

// 2. Load Indicator Catalog Metadata
$catStmt = $pdo->query("SELECT kode_indikator, nama_indikator, klaster, satuan, sumber_data, definisi_operasional FROM katalog_indikator");
$catalog = [];
while ($row = $catStmt->fetch(PDO::FETCH_ASSOC)) {
    $catalog[$row['kode_indikator']] = $row;
}
echo "Loaded " . count($catalog) . " catalog indicator definitions.\n";

// Prepare Upsert Queries
$checkStmt = $pdo->prepare("SELECT id FROM database_ekonomi WHERE kode_wilayah = ? AND kode_indikator = ? AND tahun = ? AND periode = 'Tahunan' LIMIT 1");

$updateStmt = $pdo->prepare("UPDATE database_ekonomi SET 
    nilai = ?, 
    satuan = ?, 
    sumber_data = ?, 
    definisi_operasional = ?, 
    status_validasi = 'Tervalidasi', 
    diverifikasi_oleh = 'Administrator', 
    catatan = 'Diperbarui via Import Excel KES/INV', 
    is_deleted = 0 
    WHERE id = ?");

$insertStmt = $pdo->prepare("INSERT INTO database_ekonomi 
    (kode_wilayah, kode_indikator, nama_indikator, klaster, wilayah, periode, tahun, nilai, satuan, sumber_data, definisi_operasional, status_validasi, diverifikasi_oleh, catatan, is_deleted) 
    VALUES (?, ?, ?, ?, ?, 'Tahunan', ?, ?, ?, ?, ?, 'Tervalidasi', 'Administrator', 'Diimpor via Excel KES/INV', 0)");

$stats = [
    'inserted' => 0,
    'updated' => 0,
    'by_indicator' => []
];

function upsertRecord($pdo, $checkStmt, $updateStmt, $insertStmt, &$stats, $code, $regCode, $regName, $year, $val, $cat) {
    if (!is_numeric($val)) return false;
    $valFloat = round((float)$val, 2);

    $checkStmt->execute([$regCode, $code, $year]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $updateStmt->execute([
            $valFloat,
            $cat['satuan'] ?? '',
            $cat['sumber_data'] ?? '',
            $cat['definisi_operasional'] ?? '',
            $existing['id']
        ]);
        $stats['updated']++;
    } else {
        $insertStmt->execute([
            $regCode,
            $code,
            $cat['nama_indikator'] ?? $code,
            $cat['klaster'] ?? 'Kesejahteraan dan inklusivitas',
            $regName,
            $year,
            $valFloat,
            $cat['satuan'] ?? '',
            $cat['sumber_data'] ?? '',
            $cat['definisi_operasional'] ?? ''
        ]);
        $stats['inserted']++;
    }

    if (!isset($stats['by_indicator'][$code])) {
        $stats['by_indicator'][$code] = ['inserted' => 0, 'updated' => 0, 'years' => []];
    }
    if ($existing) {
        $stats['by_indicator'][$code]['updated']++;
    } else {
        $stats['by_indicator'][$code]['inserted']++;
    }
    $stats['by_indicator'][$code]['years'][$year] = true;
    return true;
}

// =========================================================================
// PART A: IMPORT KES WORKBOOK
// =========================================================================
$kesFile = 'c:/Users/MUHAMMAD EKA/Downloads/KES - Data Dashboard ekonomi.xlsx';
echo "\n=======================================================\n";
echo "PROCESSING: $kesFile\n";
echo "=======================================================\n";

$kesReader = new SimpleXlsxReader($kesFile);
$pdo->beginTransaction();

try {
    // 1. Standard KES Sheets (Row 2 = Years, Row 3..40 = Regions)
    $standardKesSheets = [
        'KES-001', 'KES-002', 'KES-003', 'KES-004', 'KES-005',
        'KES-007', 'KES-008', 'KES-009', 'KES-010', 'KES-011'
    ];

    foreach ($standardKesSheets as $sheetName) {
        echo "Processing sheet: {$sheetName}...\n";
        $rows = $kesReader->getRows($sheetName, 100);
        if (count($rows) < 3) {
            echo "  [SKIP] Not enough rows in {$sheetName}.\n";
            continue;
        }

        $headerRow = $rows[1]; // Row 2
        $yearColMap = [];
        for ($col = 2; $col < count($headerRow); $col++) {
            $hVal = trim($headerRow[$col] ?? '');
            if (preg_match('/^(19|20)\d{2}$/', $hVal, $ym)) {
                $yearColMap[$col] = (int)$ym[0];
            }
        }

        $catMeta = $catalog[$sheetName] ?? [
            'nama_indikator' => $rows[0][0] ?? $sheetName,
            'klaster' => 'Kesejahteraan dan inklusivitas',
            'satuan' => '',
            'sumber_data' => 'BPS',
            'definisi_operasional' => ''
        ];

        // Process region rows (Row 3 to end)
        for ($rIdx = 2; $rIdx < count($rows); $rIdx++) {
            $row = $rows[$rIdx];
            $rawCode = trim($row[0] ?? '');
            if (empty($rawCode)) continue;
            $regCode = substr($rawCode, 0, 2) . '.' . substr($rawCode, 2, 2);
            $regName = $regionMap[$regCode] ?? trim($row[1] ?? '');
            if (empty($regName)) continue;

            foreach ($yearColMap as $col => $year) {
                $cellVal = trim($row[$col] ?? '');
                if ($cellVal !== '' && is_numeric($cellVal)) {
                    upsertRecord($pdo, $checkStmt, $updateStmt, $insertStmt, $stats, $sheetName, $regCode, $regName, $year, $cellVal, $catMeta);
                }
            }
        }
    }

    // 2. KES-012 Sheet (Triplet columns: Makanan, Bukan Makanan, Total)
    echo "Processing sheet: KES-012...\n";
    $kes012Rows = $kesReader->getRows('KES-012', 100);
    if (count($kes012Rows) >= 4) {
        $rowYears = $kes012Rows[1];
        $rowSub = $kes012Rows[2];

        // Map Total columns
        $totalColMap = [];
        $curYear = null;
        for ($c = 2; $c < count($rowSub); $c++) {
            if (!empty($rowYears[$c]) && preg_match('/^(19|20)\d{2}$/', trim($rowYears[$c]), $ym)) {
                $curYear = (int)$ym[0];
            }
            $subHeader = strtolower(trim($rowSub[$c] ?? ''));
            if ($subHeader === 'total' && $curYear !== null) {
                $totalColMap[$c] = $curYear;
            }
        }

        $catMeta012 = $catalog['KES-012'] ?? [
            'nama_indikator' => 'Rata-rata pengeluaran per kapita rumah tangga',
            'klaster' => 'Kesejahteraan dan inklusivitas',
            'satuan' => 'Rupiah/kapita/bulan',
            'sumber_data' => 'BPS – Susenas',
            'definisi_operasional' => 'Rata-rata pengeluaran per kapita sebulan'
        ];

        for ($rIdx = 3; $rIdx < count($kes012Rows); $rIdx++) {
            $row = $kes012Rows[$rIdx];
            $rawCode = trim($row[0] ?? '');
            if (empty($rawCode)) continue;
            $regCode = substr($rawCode, 0, 2) . '.' . substr($rawCode, 2, 2);
            $regName = $regionMap[$regCode] ?? trim($row[1] ?? '');
            if (empty($regName)) continue;

            foreach ($totalColMap as $col => $year) {
                $cellVal = trim($row[$col] ?? '');
                if ($cellVal !== '' && is_numeric($cellVal)) {
                    upsertRecord($pdo, $checkStmt, $updateStmt, $insertStmt, $stats, 'KES-012', $regCode, $regName, $year, $cellVal, $catMeta012);
                }
            }
        }
    }

    $pdo->commit();
    echo "KES workbook successfully committed!\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR in KES import: " . $e->getMessage() . "\n";
    exit(1);
}

// =========================================================================
// PART B: IMPORT INV WORKBOOK
// =========================================================================
$invFile = 'c:/Users/MUHAMMAD EKA/Downloads/INV-Data Dashboard ekonomi.xlsx';
echo "\n=======================================================\n";
echo "PROCESSING: $invFile\n";
echo "=======================================================\n";

$invReader = new SimpleXlsxReader($invFile);
$pdo->beginTransaction();

try {
    $invSheets = ['INV-001', 'INV-002', 'INV-003'];

    foreach ($invSheets as $sheetName) {
        echo "Processing sheet: {$sheetName}...\n";
        $rows = $invReader->getRows($sheetName, 100);
        if (count($rows) < 4) {
            echo "  [SKIP] Not enough rows in {$sheetName}.\n";
            continue;
        }

        $rowYears = $rows[1]; // Row 2
        $rowQ = $rows[2];     // Row 3

        // Group columns by Year
        $yearQuarters = [];
        $curYear = null;
        for ($c = 2; $c < count($rowQ); $c++) {
            if (!empty($rowYears[$c]) && preg_match('/^(19|20)\d{2}$/', trim($rowYears[$c]), $ym)) {
                $curYear = (int)$ym[0];
            }
            $q = trim($rowQ[$c] ?? '');
            if ($curYear !== null && !empty($q)) {
                $yearQuarters[$curYear][] = $c;
            }
        }

        $catMeta = $catalog[$sheetName] ?? [
            'nama_indikator' => $rows[0][0] ?? $sheetName,
            'klaster' => 'Investasi, usaha, dan keuangan',
            'satuan' => 'Rupiah',
            'sumber_data' => 'Kementerian Investasi/BKPM; DPMPTSP Kabupaten Banyuwangi',
            'definisi_operasional' => 'Nilai realisasi investasi'
        ];

        // Process region rows (Row 4 to end)
        for ($rIdx = 3; $rIdx < count($rows); $rIdx++) {
            $row = $rows[$rIdx];
            $rawCode = trim($row[0] ?? '');
            if (empty($rawCode)) continue;
            $regCode = substr($rawCode, 0, 2) . '.' . substr($rawCode, 2, 2);
            $regName = $regionMap[$regCode] ?? trim($row[1] ?? '');
            if (empty($regName)) continue;

            foreach ($yearQuarters as $year => $cols) {
                $quarterSum = 0;
                $hasVal = false;
                foreach ($cols as $col) {
                    $cellVal = trim($row[$col] ?? '');
                    if ($cellVal !== '' && is_numeric($cellVal)) {
                        $quarterSum += (float)$cellVal;
                        $hasVal = true;
                    }
                }

                if ($hasVal) {
                    // Convert from Triliun Rupiah to full Rupiah nominal
                    $nominalRupiah = round($quarterSum * 1000000000000, 2);
                    upsertRecord($pdo, $checkStmt, $updateStmt, $insertStmt, $stats, $sheetName, $regCode, $regName, $year, $nominalRupiah, $catMeta);
                }
            }
        }
    }

    $pdo->commit();
    echo "INV workbook successfully committed!\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR in INV import: " . $e->getMessage() . "\n";
    exit(1);
}

// =========================================================================
// PART C: SYNC LATEST VALUES TO EARLY WARNING (BANYUWANGI 35.10 & OTHERS)
// =========================================================================
echo "\n=======================================================\n";
echo "SYNCHRONIZING EARLY WARNING TABLE (Banyuwangi 35.10)\n";
echo "=======================================================\n";

$targetIndicators = [
    'KES-001', 'KES-002', 'KES-003', 'KES-004', 'KES-005',
    'KES-007', 'KES-008', 'KES-009', 'KES-010', 'KES-011', 'KES-012',
    'INV-001', 'INV-002', 'INV-003'
];

$ewsCheck = $pdo->prepare("SELECT id, ambang_batas, arah_risiko FROM early_warning WHERE kode_wilayah = '35.10' AND kode_indikator = ? LIMIT 1");
$ewsUpdate = $pdo->prepare("UPDATE early_warning SET nilai_terkini = ?, status_risiko = ? WHERE id = ?");

$latestValStmt = $pdo->prepare("SELECT nilai, tahun FROM database_ekonomi WHERE kode_wilayah = '35.10' AND kode_indikator = ? AND periode = 'Tahunan' AND status_validasi = 'Tervalidasi' AND is_deleted = 0 ORDER BY tahun DESC LIMIT 1");

$ewsSyncCount = 0;
foreach ($targetIndicators as $indCode) {
    $latestValStmt->execute([$indCode]);
    $latest = $latestValStmt->fetch(PDO::FETCH_ASSOC);
    if (!$latest) continue;

    $val = (float)$latest['nilai'];
    $year = $latest['tahun'];

    $ewsCheck->execute([$indCode]);
    $ewsRow = $ewsCheck->fetch(PDO::FETCH_ASSOC);
    if ($ewsRow) {
        $th = (float)$ewsRow['ambang_batas'];
        $dir = $ewsRow['arah_risiko'];

        // Determine risk status
        if ($th <= 0) {
            $risk = 'Normal';
        } elseif ($dir === 'above') {
            if ($val > $th * 1.05) {
                $risk = 'Kritis';
            } elseif ($val > $th) {
                $risk = 'Waspada';
            } else {
                $risk = 'Normal';
            }
        } else { // below
            if ($val < $th * 0.95) {
                $risk = 'Kritis';
            } elseif ($val < $th) {
                $risk = 'Waspada';
            } else {
                $risk = 'Normal';
            }
        }

        $ewsUpdate->execute([$val, $risk, $ewsRow['id']]);
        $ewsSyncCount++;
        echo "  [EWS SYNC] $indCode (Tahun $year): Nilai={$val}, Ambang={$th}, Arah={$dir} => Status: {$risk}\n";
    }
}
echo "Synchronized $ewsSyncCount indicators in early_warning.\n";

// =========================================================================
// SUMMARY
// =========================================================================
echo "\n=======================================================\n";
echo "IMPORT COMPLETE SUMMARY\n";
echo "=======================================================\n";
echo "Total Inserted Records: {$stats['inserted']}\n";
echo "Total Updated Records : {$stats['updated']}\n";
echo "Breakdown by Indicator:\n";
foreach ($stats['by_indicator'] as $code => $info) {
    $yearsArr = array_keys($info['years']);
    sort($yearsArr);
    $yearRange = min($yearsArr) . ' - ' . max($yearsArr) . ' (' . count($yearsArr) . ' thn)';
    echo sprintf("  %-10s | Inserted: %-4d | Updated: %-4d | Rentang Tahun: %s\n", 
        $code, $info['inserted'], $info['updated'], $yearRange
    );
}
