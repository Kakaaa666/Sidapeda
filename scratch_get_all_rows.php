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

$sheetXml = $zip->getFromName("xl/worksheets/sheet4.xml");
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

$excelItems = [];
for ($r = 5; $r <= 70; $r++) {
    if (!isset($rows[$r])) continue;
    $no = trim($rows[$r]['A'] ?? '');
    $nama = trim($rows[$r]['B'] ?? '');
    $satuan = trim($rows[$r]['C'] ?? '');
    if ($no === '' && $nama === '' && $satuan === '') continue;
    $excelItems[] = ['row' => $r, 'no' => $no, 'nama' => $nama, 'satuan' => $satuan];
    echo "Row $r: NO='$no' | NAMA='$nama' | SATUAN='$satuan'\n";
}
file_put_contents('scratch_excel_rows.json', json_encode($excelItems, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
