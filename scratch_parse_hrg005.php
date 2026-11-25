<?php
$xlsxPath = 'C:/Users/MUHAMMAD EKA/.gemini/antigravity/brain/05deb551-0394-4a04-8e55-bd1dd91454af/.user_uploaded/media_1795602988914_e23f93d9.xlsx';
$zip = new ZipArchive();
$zip->open($xlsxPath);

$sharedStrings = [];
$ssXml = $zip->getFromName('xl/sharedStrings.xml');
if ($ssXml) {
    $xml = simplexml_load_string($ssXml);
    foreach ($xml->si as $si) {
        if (isset($si->t)) {
            $sharedStrings[] = (string)$si->t;
        } elseif (isset($si->r)) {
            $text = '';
            foreach ($si->r as $r) {
                $text .= (string)$r->t;
            }
            $sharedStrings[] = $text;
        } else {
            $sharedStrings[] = '';
        }
    }
}

// Find HRG-005 sheet
$wbXml = $zip->getFromName('xl/workbook.xml');
$wb = simplexml_load_string($wbXml);
$sheetIndex = 4;

$sheetXml = $zip->getFromName("xl/worksheets/sheet{$sheetIndex}.xml");
$xml = simplexml_load_string($sheetXml);

$rows = [];
foreach ($xml->sheetData->row as $row) {
    $rowNum = (string)$row['r'];
    $cells = [];
    foreach ($row->c as $c) {
        $cellRef = (string)$c['r'];
        $colLetter = preg_replace('/[0-9]/', '', $cellRef);
        $val = (string)$c->v;
        $type = (string)$c['t'];
        if ($type === 's') {
            $val = $sharedStrings[(int)$val] ?? $val;
        }
        $cells[$colLetter] = $val;
    }
    $rows[$rowNum] = $cells;
}

// Date row: Row 4
echo "=== DATES IN ROW 4 ===\n";
foreach ($rows['4'] as $col => $val) {
    if (is_numeric($val) && $val > 40000) {
        $unixTimestamp = ($val - 25569) * 86400;
        $dateStr = gmdate('Y-m-d', $unixTimestamp);
        echo "$col: $val => $dateStr\n";
    } else {
        echo "$col: $val\n";
    }
}

echo "\n=== GROUPS AND ITEMS ===\n";
$currentGroup = null;
$structure = [];
for ($r = 5; $r <= 70; $r++) {
    if (!isset($rows[$r])) continue;
    $row = $rows[$r];
    $no = trim($row['A'] ?? '');
    $nama = trim($row['B'] ?? '');
    $satuan = trim($row['C'] ?? '');
    
    if ($no !== '' && $nama !== '') {
        $currentGroup = [
            'no' => $no,
            'name' => $nama,
            'items' => []
        ];
        $structure[] = &$currentGroup;
        echo "\n[GROUP $no] $nama\n";
    } elseif ($nama !== '') {
        $cleanName = preg_replace('/^[-•\s]+/', '', $nama);
        echo "  - Item: '$cleanName' | Satuan: '$satuan'\n";
        if ($currentGroup !== null) {
            $currentGroup['items'][] = [
                'name' => $cleanName,
                'satuan' => $satuan
            ];
        }
    }
}

file_put_contents('scratch_hrg005_structure.json', json_encode($structure, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
