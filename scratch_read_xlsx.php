<?php
$xlsxPath = 'C:/Users/MUHAMMAD EKA/.gemini/antigravity/brain/05deb551-0394-4a04-8e55-bd1dd91454af/.user_uploaded/media_1795602988914_e23f93d9.xlsx';
if (!file_exists($xlsxPath)) {
    die("File not found: " . $xlsxPath . "\n");
}

$zip = new ZipArchive();
if ($zip->open($xlsxPath) !== true) {
    die("Failed to open zip\n");
}

// 1. Get shared strings
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

// 2. Get workbook sheet names
$wbXml = $zip->getFromName('xl/workbook.xml');
$wb = simplexml_load_string($wbXml);
$sheets = [];
foreach ($wb->sheets->sheet as $s) {
    $sheets[(string)$s['name']] = (string)$s['sheetId'];
    echo "Sheet: " . (string)$s['name'] . "\n";
}

// 3. Inspect sheets matching HRG or sheet5
for ($i = 1; $i <= count($sheets); $i++) {
    $sheetXml = $zip->getFromName("xl/worksheets/sheet{$i}.xml");
    if (!$sheetXml) continue;
    $xml = simplexml_load_string($sheetXml);
    // Find sheet name
    $sheetName = array_keys($sheets)[$i-1];
    echo "\n--- Sheet {$i}: {$sheetName} ---\n";
    if (strpos($sheetName, 'HRG') !== false || strpos($sheetName, '005') !== false || $sheetName === 'Sheet5' || count($sheets) <= 5) {
        $rowIdx = 0;
        foreach ($xml->sheetData->row as $row) {
            $rowNum = (string)$row['r'];
            $cells = [];
            foreach ($row->c as $c) {
                $cellRef = (string)$c['r'];
                $val = (string)$c->v;
                $type = (string)$c['t'];
                if ($type === 's') {
                    $val = $sharedStrings[(int)$val] ?? $val;
                }
                $cells[$cellRef] = $val;
            }
            if (!empty($cells) && $rowIdx < 35) {
                echo "Row {$rowNum}: " . json_encode($cells, JSON_UNESCAPED_UNICODE) . "\n";
            }
            $rowIdx++;
        }
        echo "Total rows: {$rowIdx}\n";
    }
}
$zip->close();
