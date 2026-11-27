<?php
/**
 * Importer & Data Integrity Sync Script for TK & PAR Workbooks
 * Sources:
 *  - c:/Users/MUHAMMAD EKA/Downloads/TK - Data Dashboard ekonom.xlsx
 *  - c:/Users/MUHAMMAD EKA/Downloads/PAR - Data Dashboard ekonomi.xlsx
 *
 * Imports/syncs all regional data across 38 Kab/Kota (35.01..35.79) + Provinsi Jawa Timur (35.00):
 *  - NAK-001 (2017..2025, 39 regions, Tahunan)
 *  - TK-001..TK-006, TK-009 (2017..2025, 38 regions, Agustus -> stored in both TK-00X and TK-00X.AGS)
 *    * Fixes 1-row upward shift in Excel sheets TK-003 and TK-006 (where Pacitan was omitted in sheet)
 *    * Computes missing 35.01 Pacitan TK-003 = TK-002 - TK-004 and TK-006 = round(TK-002 / TK-001 * 100, 2)
 *  - TK-010, TK-010.AGS, TK-010.JML (2025, 38 regions, Agustus)
 *    * Fixes 35.08 Kab. Lumajang "26.883" thousand-separator typo -> 26883 and recalculates TK-010 %
 *  - PAR-005, PAR-005.BIN, PAR-005.NON (2022..2024, 39 regions, Tahunan)
 *  - Syncs 35.10 early_warning values & memastikan status_validasi='Tervalidasi'
 */

$pdo = new PDO('mysql:host=localhost;dbname=sidapeda;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

class SimpleXLSXParser {
    private $zip;
    private $sharedStrings = [];

    public function __construct($file) {
        $this->zip = new ZipArchive();
        if ($this->zip->open($file) !== true) {
            throw new RuntimeException("Cannot open XLSX: $file");
        }
        $ss = $this->zip->getFromName('xl/sharedStrings.xml');
        if ($ss) {
            $xml = simplexml_load_string($ss);
            foreach ($xml->si as $si) {
                $t = '';
                if (isset($si->t)) {
                    $t = (string)$si->t;
                } elseif (isset($si->r)) {
                    foreach ($si->r as $r) $t .= (string)$r->t;
                }
                $this->sharedStrings[] = $t;
            }
        }
    }

    public function getSheets() {
        $wb = simplexml_load_string($this->zip->getFromName('xl/workbook.xml'));
        $rels = simplexml_load_string($this->zip->getFromName('xl/_rels/workbook.xml.rels'));
        $map = [];
        foreach ($rels->Relationship as $rel) {
            $map[(string)$rel['Id']] = (string)$rel['Target'];
        }
        $sheets = [];
        foreach ($wb->sheets->sheet as $s) {
            $rid = (string)$s->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
            $target = ltrim($map[$rid], '/');
            if (strpos($target, 'xl/') !== 0) $target = 'xl/' . $target;
            $sheets[(string)$s['name']] = $target;
        }
        return $sheets;
    }

    private function colIdx($cellRef) {
        preg_match('/^([A-Z]+)/', $cellRef, $m);
        $col = $m[1] ?? 'A';
        $idx = 0;
        for ($i = 0; $i < strlen($col); $i++) {
            $idx = $idx * 26 + (ord($col[$i]) - 64);
        }
        return $idx - 1;
    }

    public function parseSheet($path) {
        $xmlStr = $this->zip->getFromName($path);
        if (!$xmlStr) return [];
        $xml = simplexml_load_string($xmlStr);
        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $r = [];
            foreach ($row->c as $c) {
                $idx = $this->colIdx((string)$c['r']);
                $type = (string)$c['t'];
                $val = '';
                if ($type === 's') $val = $this->sharedStrings[(int)$c->v] ?? '';
                elseif ($type === 'inlineStr') $val = (string)$c->is->t;
                elseif ($type === 'str') $val = (string)$c->v;
                elseif (isset($c->v)) $val = (string)$c->v;
                $r[$idx] = trim($val);
            }
            if (!empty($r)) {
                $max = max(array_keys($r));
                $arr = [];
                for ($i = 0; $i <= $max; $i++) $arr[] = $r[$i] ?? '';
                $rows[] = $arr;
            }
        }
        return $rows;
    }

    public function close() {
        $this->zip->close();
    }
}

// Canonical region lookup: 35.00..35.79
$regionNames = ['35.00' => 'Provinsi Jawa Timur'];
foreach ($pdo->query("SELECT DISTINCT kode_wilayah, wilayah FROM database_ekonomi WHERE kode_wilayah LIKE '35.%' ORDER BY kode_wilayah") as $r) {
    $kw = trim($r['kode_wilayah']);
    $nm = trim($r['wilayah']);
    if ($kw !== '' && $nm !== '') $regionNames[$kw] = $nm;
}
foreach ($pdo->query("SELECT kode_wilayah, nama_wilayah FROM perbandingan_daerah WHERE kode_wilayah LIKE '35.%'") as $r) {
    $kw = trim($r['kode_wilayah']);
    $nm = trim($r['nama_wilayah']);
    if ($kw !== '' && $nm !== '' && !isset($regionNames[$kw])) $regionNames[$kw] = $nm;
}

function findRegionByParName($rawName, $regionNames) {
    $clean = strtolower(trim(preg_replace('/\s+/', ' ', $rawName)));
    if ($clean === 'jawa timur' || $clean === 'provinsi jawa timur') return '35.00';
    $isKota = (strpos($clean, 'kota ') === 0);
    $base = preg_replace('/^(kabupaten|kab\.?|kota)\s+/i', '', $clean);
    foreach ($regionNames as $kw => $dbName) {
        if ($kw === '35.00') continue;
        $dbClean = strtolower(trim($dbName));
        $dbIsKota = (strpos($dbClean, 'kota ') === 0);
        if ($isKota !== $dbIsKota) continue;
        $dbBase = preg_replace('/^(kabupaten|kab\.?|kota)\s+/i', '', $dbClean);
        if ($base === $dbBase) return $kw;
    }
    return null;
}

// Load katalog_indikator metadata
$katalog = [];
foreach ($pdo->query("SELECT kode_indikator, nama_indikator, klaster, satuan, sumber_data, definisi_operasional FROM katalog_indikator") as $k) {
    $katalog[$k['kode_indikator']] = $k;
}

$checkStmt = $pdo->prepare("SELECT id, nilai FROM database_ekonomi WHERE kode_wilayah = ? AND kode_indikator = ? AND tahun = ? AND periode = ? AND is_deleted = 0 LIMIT 1");
$updateStmt = $pdo->prepare("UPDATE database_ekonomi SET wilayah = ?, nama_indikator = ?, klaster = ?, satuan = ?, sumber_data = ?, definisi_operasional = ?, nilai = ?, status_validasi = 'Tervalidasi' WHERE id = ?");
$insertStmt = $pdo->prepare("INSERT INTO database_ekonomi (kode_wilayah, kode_indikator, nama_indikator, klaster, wilayah, periode, tahun, nilai, satuan, sumber_data, definisi_operasional, status_validasi, diverifikasi_oleh, catatan, dibuat_pada) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Tervalidasi', 'BPS Jawa Timur / Importer', 'Sinkronisasi Data Dashboard', NOW())");

$stats = ['inserted' => 0, 'updated' => 0, 'unchanged' => 0];

function upsertRecord($kw, $wilayah, $code, $tahun, $periode, $nilai, $katalog, $checkStmt, $updateStmt, $insertStmt, &$stats) {
    $meta = $katalog[$code] ?? null;
    if (!$meta) {
        throw new RuntimeException("Missing katalog_indikator metadata for $code");
    }
    $valRounded = round((float)$nilai, 2);
    $checkStmt->execute([$kw, $code, (int)$tahun, $periode]);
    $existing = $checkStmt->fetch();
    if ($existing) {
        if (abs((float)$existing['nilai'] - $valRounded) > 0.005) {
            $updateStmt->execute([
                $wilayah, $meta['nama_indikator'], $meta['klaster'], $meta['satuan'],
                $meta['sumber_data'], $meta['definisi_operasional'], $valRounded, $existing['id']
            ]);
            $stats['updated']++;
        } else {
            $stats['unchanged']++;
        }
    } else {
        $insertStmt->execute([
            $kw, $code, $meta['nama_indikator'], $meta['klaster'], $wilayah,
            $periode, (int)$tahun, $valRounded, $meta['satuan'],
            $meta['sumber_data'], $meta['definisi_operasional']
        ]);
        $stats['inserted']++;
    }
}

$tkFile = 'c:/Users/MUHAMMAD EKA/Downloads/TK - Data Dashboard ekonom.xlsx';
$parFile = 'c:/Users/MUHAMMAD EKA/Downloads/PAR - Data Dashboard ekonomi.xlsx';

$pdo->beginTransaction();

// ================= 1. TK WORKBOOK =================
$tkXlsx = new SimpleXLSXParser($tkFile);
$tkSheets = $tkXlsx->getSheets();

// 1a. NAK-001 (2017..2025, 39 regions)
$nakRows = $tkXlsx->parseSheet($tkSheets['NAK-001']);
foreach (array_slice($nakRows, 2) as $row) {
    $rawCode = trim($row[0] ?? '');
    if ($rawCode === '' && !empty($row[3])) $kw = '35.00';
    elseif (preg_match('/^(\d{2})(\d{2})$/', $rawCode, $m)) $kw = "{$m[1]}.{$m[2]}";
    else continue;
    $wilayah = $regionNames[$kw] ?? trim($row[1] ?? '');
    for ($ci = 3; $ci <= 11; $ci++) { // 2017..2025 (2016 only has Jawa Timur total)
        $yr = 2014 + $ci;
        if (!isset($row[$ci]) || !is_numeric($row[$ci])) continue;
        upsertRecord($kw, $wilayah, 'NAK-001', $yr, 'Tahunan', (float)$row[$ci], $katalog, $checkStmt, $updateStmt, $insertStmt, $stats);
    }
}

// 1b. Parse TK-001..TK-006, TK-009 (August columns 5,7,9,11,13,15,17,19,21 => years 2017..2025)
$tkGrid = [];
foreach (['TK-001', 'TK-002', 'TK-003', 'TK-004', 'TK-005', 'TK-006', 'TK-009'] as $code) {
    $rows = $tkXlsx->parseSheet($tkSheets[$code]);
    foreach (array_slice($rows, 3) as $ri => $row) {
        $tkGrid[$code][$ri] = $row;
    }
}

// Ordered list of 38 region codes from TK-001 rows (idx 0..37 correspond to 35.01..35.79)
for ($ri = 0; $ri < count($tkGrid['TK-001']); $ri++) {
    $rawCode = trim($tkGrid['TK-001'][$ri][0] ?? '');
    if (!preg_match('/^(\d{2})(\d{2})$/', $rawCode, $m)) continue;
    $kw = "{$m[1]}.{$m[2]}";
    $wilayah = $regionNames[$kw] ?? trim($tkGrid['TK-001'][$ri][1] ?? '');

    for ($ci = 5; $ci <= 21; $ci += 2) {
        $yr = 2015 + (int)(($ci - 1) / 2);

        $v1 = is_numeric($tkGrid['TK-001'][$ri][$ci] ?? '') ? (float)$tkGrid['TK-001'][$ri][$ci] : null;
        $v2 = is_numeric($tkGrid['TK-002'][$ri][$ci] ?? '') ? (float)$tkGrid['TK-002'][$ri][$ci] : null;
        $v4 = is_numeric($tkGrid['TK-004'][$ri][$ci] ?? '') ? (float)$tkGrid['TK-004'][$ri][$ci] : null;
        $v5 = is_numeric($tkGrid['TK-005'][$ri][$ci] ?? '') ? (float)$tkGrid['TK-005'][$ri][$ci] : null;
        $v9 = is_numeric($tkGrid['TK-009'][$ri][$ci] ?? '') ? (float)$tkGrid['TK-009'][$ri][$ci] : null;

        // Unshift TK-003 and TK-006: Pacitan (ri=0) is computed from TK-002 & TK-004 / TK-001; ri>=1 reads from ri-1
        if ($ri === 0) {
            $v3 = ($v2 !== null && $v4 !== null) ? ($v2 - $v4) : null;
            $v6 = ($v2 !== null && $v1 > 0) ? round(($v2 / $v1) * 100, 2) : null;
        } else {
            $v3raw = $tkGrid['TK-003'][$ri - 1][$ci] ?? '';
            $v6raw = $tkGrid['TK-006'][$ri - 1][$ci] ?? '';
            $v3 = is_numeric($v3raw) ? (float)$v3raw : (($v2 !== null && $v4 !== null) ? ($v2 - $v4) : null);
            $v6 = is_numeric($v6raw) ? (float)$v6raw : (($v2 !== null && $v1 > 0) ? round(($v2 / $v1) * 100, 2) : null);
        }

        $pairs = [
            'TK-001' => $v1,
            'TK-002' => $v2,
            'TK-003' => $v3,
            'TK-004' => $v4,
            'TK-005' => $v5,
            'TK-006' => $v6,
            'TK-009' => $v9,
        ];
        foreach ($pairs as $code => $val) {
            if ($val === null) continue;
            upsertRecord($kw, $wilayah, $code, $yr, 'Agustus', $val, $katalog, $checkStmt, $updateStmt, $insertStmt, $stats);
            upsertRecord($kw, $wilayah, $code . '.AGS', $yr, 'Agustus', $val, $katalog, $checkStmt, $updateStmt, $insertStmt, $stats);
        }
    }
}

// 1c. TK-010 (Setengah Pengangguran: 2025 col=21 has Jumlah Orang; fix Lumajang "26.883" -> 26883 and recalculate %)
$tk10Rows = $tkXlsx->parseSheet($tkSheets['TK-010']);
foreach (array_slice($tk10Rows, 3) as $ri => $row) {
    $rawCode = trim($row[0] ?? '');
    if (!preg_match('/^(\d{2})(\d{2})$/', $rawCode, $m)) continue;
    $kw = "{$m[1]}.{$m[2]}";
    $wilayah = $regionNames[$kw] ?? trim($row[1] ?? '');

    $rawVal = trim($row[21] ?? '');
    if ($rawVal === '' || !is_numeric($rawVal)) continue;
    $jml = (float)$rawVal;
    // Detect thousand-separator dot typo on integer person count (e.g. Lumajang "26.882999999999999" -> 26883)
    if ($jml > 0 && $jml < 200) {
        $jml = round($jml * 1000);
    }

    upsertRecord($kw, $wilayah, 'TK-010.JML', 2025, 'Agustus', $jml, $katalog, $checkStmt, $updateStmt, $insertStmt, $stats);

    // Ensure TK-010 and TK-010.AGS (%) are consistent for 35.08 Kab. Lumajang (where old import stored 0.00%)
    if ($kw === '35.08') {
        $bekerja2025 = ($tkGrid['TK-002'][$ri][21] ?? 0) - ($tkGrid['TK-004'][$ri][21] ?? 0);
        if ($bekerja2025 > 0) {
            $pct = round(($jml / $bekerja2025) * 100, 2);
            upsertRecord($kw, $wilayah, 'TK-010', 2025, 'Agustus', $pct, $katalog, $checkStmt, $updateStmt, $insertStmt, $stats);
            upsertRecord($kw, $wilayah, 'TK-010.AGS', 2025, 'Agustus', $pct, $katalog, $checkStmt, $updateStmt, $insertStmt, $stats);
        }
    }
}
$tkXlsx->close();

// ================= 2. PAR WORKBOOK =================
$parXlsx = new SimpleXLSXParser($parFile);
$parSheets = $parXlsx->getSheets();

// PAR-005 (39 regions, 2022..2024: .BIN, .NON, Total)
$p5Rows = $parXlsx->parseSheet($parSheets['PAR-005']);
$p5Cols = [
    1 => ['PAR-005.BIN', 2022], 2 => ['PAR-005.NON', 2022], 3 => ['PAR-005', 2022],
    4 => ['PAR-005.BIN', 2023], 5 => ['PAR-005.NON', 2023], 6 => ['PAR-005', 2023],
    7 => ['PAR-005.BIN', 2024], 8 => ['PAR-005.NON', 2024], 9 => ['PAR-005', 2024],
];
foreach (array_slice($p5Rows, 3) as $row) {
    $rawName = trim($row[0] ?? '');
    if ($rawName === '') continue;
    $kw = findRegionByParName($rawName, $regionNames);
    if (!$kw) {
        echo "WARN: Unmatched PAR-005 region '$rawName'\n";
        continue;
    }
    $wilayah = $regionNames[$kw];
    foreach ($p5Cols as $ci => [$code, $yr]) {
        $vRaw = trim($row[$ci] ?? '');
        $val = ($vRaw !== '' && is_numeric($vRaw)) ? (float)$vRaw : 0.0;
        upsertRecord($kw, $wilayah, $code, $yr, 'Tahunan', $val, $katalog, $checkStmt, $updateStmt, $insertStmt, $stats);
    }
}
$parXlsx->close();

// Clean up legacy '35' duplicate rows where canonical '35.00' now holds Provinsi Jawa Timur
$pdo->exec("DELETE FROM database_ekonomi WHERE kode_wilayah = '35' AND kode_indikator IN ('PAR-005','PAR-005.BIN','PAR-005.NON')");

// Sync early_warning for 35.10 TK-003 & TK-006 (latest year 2025)
$ewsCheck = $pdo->prepare("SELECT id, ambang_batas, arah_risiko FROM early_warning WHERE kode_wilayah = '35.10' AND kode_indikator = ? LIMIT 1");
$ewsUpdate = $pdo->prepare("UPDATE early_warning SET nilai_terkini = ?, status_risiko = ? WHERE id = ?");
foreach (['TK-003', 'TK-006'] as $ewsCode) {
    $st = $pdo->prepare("SELECT nilai FROM database_ekonomi WHERE kode_wilayah='35.10' AND kode_indikator=? AND tahun=2025 AND periode='Agustus' AND is_deleted=0 LIMIT 1");
    $st->execute([$ewsCode]);
    $val = $st->fetchColumn();
    if ($val === false) continue;
    $ewsCheck->execute([$ewsCode]);
    $ewsRow = $ewsCheck->fetch();
    if (!$ewsRow) continue;
    $v = (float)$val;
    $th = (float)$ewsRow['ambang_batas'];
    $dir = $ewsRow['arah_risiko'];
    if ($th <= 0) $risk = 'Normal';
    elseif ($dir === 'above') $risk = ($v > $th * 1.05) ? 'Kritis' : (($v > $th) ? 'Waspada' : 'Normal');
    else $risk = ($v < $th * 0.95) ? 'Kritis' : (($v < $th) ? 'Waspada' : 'Normal');
    $ewsUpdate->execute([$v, $risk, $ewsRow['id']]);
}

$pdo->commit();

echo "Import TK & PAR complete!\n";
echo "  Inserted : {$stats['inserted']}\n";
echo "  Updated  : {$stats['updated']}\n";
echo "  Unchanged: {$stats['unchanged']}\n";
