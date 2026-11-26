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
$sheetIndex = null;
$idx = 1;
foreach ($wb->sheets->sheet as $s) {
    if ((string)$s['name'] === 'HRG-005') {
        $sheetIndex = $idx;
        break;
    }
    $idx++;
}

echo "HRG-005 sheet index: {$sheetIndex}\n";
$sheetXml = $zip->getFromName("xl/worksheets/sheet{$sheetIndex}.xml");
$xml = simplexml_load_string($sheetXml);

$rows = [];
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
    $rows[$rowNum] = $cells;
}

echo "First 15 rows:\n";
for ($r = 1; $r <= 15; $r++) {
    if (isset($rows[$r])) {
        echo "Row {$r}: " . json_encode($rows[$r], JSON_UNESCAPED_UNICODE) . "\n";
    }
}

echo "\nRows 16 to 40:\n";
for ($r = 16; $r <= 40; $r++) {
    if (isset($rows[$r])) {
        echo "Row {$r}: " . json_encode($rows[$r], JSON_UNESCAPED_UNICODE) . "\n";
    }
}

echo "\nRows 41 to 75:\n";
for ($r = 41; $r <= 75; $r++) {
    if (isset($rows[$r])) {
        echo "Row {$r}: " . json_encode($rows[$r], JSON_UNESCAPED_UNICODE) . "\n";
    }
}
