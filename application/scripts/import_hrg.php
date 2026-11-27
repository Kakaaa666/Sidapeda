<?php
/**
 * Importer & Sync Script for HRG-Data Dashboard Ekonom.xlsx
 * Covers 10 IHK Kab/Kota in Jawa Timur + Provinsi Jawa Timur (35.00):
 *  - HRG-001 (+ .JAN..DES & Tahunan average) : 11 regions (2022..2026)
 *  - HRG-002.01..11 (+ Bulanan & Tahunan avg) : 10 Kab/Kota IHK (2024..2026)
 *  - HRG-003 (+ .JAN..DES & Tahunan 2022-2023): 11 regions (2022..2026)
 *  - HRG-004 (+ .JAN..DES & Tahunan YoY)      : 11 regions (2024..2026, Tahunan uses Dec for completed years & latest month Oct for 2026)
 */

$pdo = new PDO('mysql:host=localhost;dbname=sidapeda;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

class SimpleXLSXParser {
    private $zip, $sharedStrings = [];
    public function __construct($file) {
        $this->zip = new ZipArchive();
        if ($this->zip->open($file) !== true) throw new RuntimeException("Cannot open XLSX: $file");
        $ss = $this->zip->getFromName('xl/sharedStrings.xml');
        if ($ss) {
            $xml = simplexml_load_string($ss);
            foreach ($xml->si as $si) {
                $t = '';
                if (isset($si->t)) $t = (string)$si->t;
                elseif (isset($si->r)) foreach ($si->r as $r) $t .= (string)$r->t;
                $this->sharedStrings[] = $t;
            }
        }
    }
    public function getSheets() {
        $wb = simplexml_load_string($this->zip->getFromName('xl/workbook.xml'));
        $rels = simplexml_load_string($this->zip->getFromName('xl/_rels/workbook.xml.rels'));
        $map = [];
        foreach ($rels->Relationship as $rel) $map[(string)$rel['Id']] = (string)$rel['Target'];
        $sheets = [];
        foreach ($wb->sheets->sheet as $s) {
            $rid = (string)$s->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
            $t = ltrim($map[$rid], '/'); if (strpos($t, 'xl/') !== 0) $t = 'xl/' . $t;
            $sheets[(string)$s['name']] = $t;
        }
        return $sheets;
    }
    private function colIdx($cellRef) {
        preg_match('/^([A-Z]+)/', $cellRef, $m);
        $col = $m[1] ?? 'A'; $idx = 0;
        for ($i = 0; $i < strlen($col); $i++) $idx = $idx * 26 + (ord($col[$i]) - 64);
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
                $idx = $this->colIdx((string)$c['r']); $type = (string)$c['t']; $val = '';
                if ($type === 's') $val = $this->sharedStrings[(int)$c->v] ?? '';
                elseif ($type === 'inlineStr') $val = (string)$c->is->t;
                elseif ($type === 'str') $val = (string)$c->v;
                elseif (isset($c->v)) $val = (string)$c->v;
                $r[$idx] = trim($val);
            }
            if (!empty($r)) {
                $max = max(array_keys($r)); $arr = [];
                for ($i = 0; $i <= $max; $i++) $arr[] = $r[$i] ?? '';
                $rows[] = $arr;
            }
        }
        return $rows;
    }
    public function close() { $this->zip->close(); }
}

$regionNames = ['35.00' => 'Provinsi Jawa Timur'];
foreach ($pdo->query("SELECT DISTINCT kode_wilayah, wilayah FROM database_ekonomi WHERE kode_wilayah LIKE '35.%' ORDER BY kode_wilayah") as $r) {
    $kw = trim($r['kode_wilayah']); $nm = trim($r['wilayah']);
    if ($kw !== '' && $nm !== '') $regionNames[$kw] = $nm;
}

$katalog = [];
foreach ($pdo->query("SELECT kode_indikator, nama_indikator, klaster, satuan, sumber_data, definisi_operasional FROM katalog_indikator") as $k) {
    $katalog[$k['kode_indikator']] = $k;
}

$checkStmt = $pdo->prepare("SELECT id, nilai FROM database_ekonomi WHERE kode_wilayah = ? AND kode_indikator = ? AND tahun = ? AND periode = ? AND is_deleted = 0 LIMIT 1");
$updateStmt = $pdo->prepare("UPDATE database_ekonomi SET wilayah = ?, nama_indikator = ?, klaster = ?, satuan = ?, sumber_data = ?, definisi_operasional = ?, nilai = ?, status_validasi = 'Tervalidasi' WHERE id = ?");
$insertStmt = $pdo->prepare("INSERT INTO database_ekonomi (kode_wilayah, kode_indikator, nama_indikator, klaster, wilayah, periode, tahun, nilai, satuan, sumber_data, definisi_operasional, status_validasi, diverifikasi_oleh, catatan, dibuat_pada) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Tervalidasi', 'BPS Jawa Timur / Importer', 'Sinkronisasi Data HRG', NOW())");

$stats = ['inserted' => 0, 'updated' => 0, 'unchanged' => 0];

function upsertRecord($kw, $wilayah, $code, $tahun, $periode, $nilai, $katalog, $checkStmt, $updateStmt, $insertStmt, &$stats) {
    $meta = $katalog[$code] ?? null;
    if (!$meta) throw new RuntimeException("Missing katalog_indikator for $code");
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

$monthMap = [
    'Januari' => 'JAN', 'Februari' => 'FEB', 'Maret' => 'MAR', 'April' => 'APR',
    'Mei' => 'MEI', 'Juni' => 'JUN', 'Juli' => 'JUL', 'Agustus' => 'AGS',
    'September' => 'SEP', 'Oktober' => 'OKT', 'November' => 'NOV', 'Desember' => 'DES'
];

$file = 'c:/Users/MUHAMMAD EKA/Downloads/HRG-Data Dashboard Ekonom.xlsx';
$x = new SimpleXLSXParser($file);
$sh = $x->getSheets();

$pdo->beginTransaction();

// 1. HRG-001, HRG-003, HRG-004 (monthly columns + annual computation)
foreach (['HRG-001', 'HRG-003', 'HRG-004'] as $code) {
    $rows = $x->parseSheet($sh[$code]);
    $yrsRow = $rows[1];
    $monRow = $rows[2];
    $colMeta = [];
    $curYr = 0;
    for ($c = 2; $c < count($monRow); $c++) {
        if (is_numeric($yrsRow[$c] ?? '')) $curYr = (int)$yrsRow[$c];
        $p = trim($monRow[$c] ?? '');
        if ($curYr > 0 && $p !== '') $colMeta[$c] = [$curYr, $p];
    }

    foreach (array_slice($rows, 3) as $row) {
        $rawC = trim($row[0] ?? '');
        if ($rawC === '' && stripos($row[1] ?? '', 'Jawa Timur') !== false) $kw = '35.00';
        elseif (preg_match('/^(\d{2})(\d{2})$/', $rawC, $m)) $kw = "{$m[1]}.{$m[2]}";
        else continue;
        $wilayah = $regionNames[$kw] ?? trim($row[1] ?? '');

        $byYearMonths = [];
        $explicitAnnual = [];
        foreach ($colMeta as $c => [$yr, $p]) {
            $vRaw = trim($row[$c] ?? '');
            if ($vRaw === '' || $vRaw === '-' || !is_numeric($vRaw)) continue;
            $val = (float)$vRaw;
            if ($p === 'Tahunan') {
                $explicitAnnual[$yr] = $val;
                upsertRecord($kw, $wilayah, $code, $yr, 'Tahunan', $val, $katalog, $checkStmt, $updateStmt, $insertStmt, $stats);
            } elseif (isset($monthMap[$p])) {
                $byYearMonths[$yr][$p] = $val;
                upsertRecord($kw, $wilayah, $code . '.' . $monthMap[$p], $yr, "Bulanan ($p)", $val, $katalog, $checkStmt, $updateStmt, $insertStmt, $stats);
            }
        }

        // Fill/update Tahunan summary for HRG-001 (mean IHK) and HRG-004 (end-of-year / latest YoY) across ALL 11 regions
        foreach ($byYearMonths as $yr => $months) {
            if (isset($explicitAnnual[$yr])) continue;
            if ($code === 'HRG-001' && !empty($months)) {
                $avg = array_sum($months) / count($months);
                upsertRecord($kw, $wilayah, 'HRG-001', $yr, 'Tahunan', $avg, $katalog, $checkStmt, $updateStmt, $insertStmt, $stats);
            } elseif ($code === 'HRG-004' && !empty($months)) {
                // Year-end Dec if available, otherwise latest available month in the year (e.g. Oct 2026)
                $latestVal = end($months);
                upsertRecord($kw, $wilayah, 'HRG-004', $yr, 'Tahunan', $latestVal, $katalog, $checkStmt, $updateStmt, $insertStmt, $stats);
            }
        }
    }
}

// 2. HRG-002 (10 Kab/Kota IHK blocks x 11 expenditure groups)
$r2 = $x->parseSheet($sh['HRG-002']);
$curKw = null;
$curCols = [];
foreach ($r2 as $row) {
    if (preg_match('/^(\d{2})(\d{2})$/', trim($row[0] ?? ''), $m)) {
        $curKw = "{$m[1]}.{$m[2]}";
        $curCols = [];
        continue;
    }
    if ($curKw && trim($row[0] ?? '') === 'No') {
        // Year row -> next row is month row, handled by tracking last year row
        $curYearRow = $row;
        continue;
    }
    if ($curKw && isset($curYearRow) && trim($row[0] ?? '') === '' && trim($row[1] ?? '') === '' && trim($row[2] ?? '') === 'Januari') {
        $curYr = 0;
        for ($c = 2; $c < count($row); $c++) {
            if (is_numeric($curYearRow[$c] ?? '')) $curYr = (int)$curYearRow[$c];
            $p = trim($row[$c] ?? '');
            if ($curYr > 0 && $p !== '') $curCols[$c] = [$curYr, $p];
        }
        continue;
    }
    if ($curKw && !empty($curCols) && is_numeric(trim($row[0] ?? ''))) {
        $grpNo = (int)trim($row[0]);
        if ($grpNo < 1 || $grpNo > 11) continue;
        $subCode = sprintf('HRG-002.%02d', $grpNo);
        $wilayah = $regionNames[$curKw] ?? $curKw;
        $byYearMonths = [];
        foreach ($curCols as $c => [$yr, $p]) {
            $vRaw = trim($row[$c] ?? '');
            if ($vRaw === '' || $vRaw === '-' || !is_numeric($vRaw)) continue;
            $val = (float)$vRaw;
            if (isset($monthMap[$p])) {
                $byYearMonths[$yr][] = $val;
                upsertRecord($curKw, $wilayah, $subCode, $yr, "Bulanan ($p)", $val, $katalog, $checkStmt, $updateStmt, $insertStmt, $stats);
            }
        }
        foreach ($byYearMonths as $yr => $vals) {
            $avg = array_sum($vals) / count($vals);
            upsertRecord($curKw, $wilayah, $subCode, $yr, 'Tahunan', $avg, $katalog, $checkStmt, $updateStmt, $insertStmt, $stats);
        }
    }
}

$x->close();

// 3. Sync early_warning for 35.10 HRG-001, HRG-003, HRG-004 (latest Tahunan)
$ewsCheck = $pdo->prepare("SELECT id, ambang_batas, arah_risiko FROM early_warning WHERE kode_wilayah = '35.10' AND kode_indikator = ? LIMIT 1");
$ewsUpdate = $pdo->prepare("UPDATE early_warning SET nilai_terkini = ?, status_risiko = ? WHERE id = ?");
$latestStmt = $pdo->prepare("SELECT nilai FROM database_ekonomi WHERE kode_wilayah = '35.10' AND kode_indikator = ? AND periode = 'Tahunan' AND is_deleted = 0 ORDER BY tahun DESC LIMIT 1");
foreach (['HRG-001', 'HRG-003', 'HRG-004'] as $ewsCode) {
    $latestStmt->execute([$ewsCode]);
    $val = $latestStmt->fetchColumn();
    if ($val === false) continue;
    $ewsCheck->execute([$ewsCode]);
    $ewsRow = $ewsCheck->fetch();
    if (!$ewsRow) continue;
    $v = (float)$val; $th = (float)$ewsRow['ambang_batas']; $dir = $ewsRow['arah_risiko'];
    if ($th <= 0) $risk = 'Normal';
    elseif ($dir === 'above') $risk = ($v > $th * 1.05) ? 'Kritis' : (($v > $th) ? 'Waspada' : 'Normal');
    else $risk = ($v < $th * 0.95) ? 'Kritis' : (($v < $th) ? 'Waspada' : 'Normal');
    $ewsUpdate->execute([$v, $risk, $ewsRow['id']]);
}

$pdo->commit();

echo "Import HRG complete!\n";
echo "  Inserted : {$stats['inserted']}\n";
echo "  Updated  : {$stats['updated']}\n";
echo "  Unchanged: {$stats['unchanged']}\n";
