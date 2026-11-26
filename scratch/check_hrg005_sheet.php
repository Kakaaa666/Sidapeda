<?php
$zip = new ZipArchive();
$xlsx = 'C:/Users/MUHAMMAD EKA/.gemini/antigravity/brain/05deb551-0394-4a04-8e55-bd1dd91454af/.user_uploaded/media_1795590751262_4622b81b.xlsx';
if ($zip->open($xlsx) === TRUE) {
    $strings = [];
    $strXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($strXml) {
        $xml = simplexml_load_string($strXml);
        foreach ($xml->si as $si) {
            $strings[] = (string)($si->t ?? $si->r->t ?? '');
        }
    }
    // Find sheet target for HRG-005
    $wb = $zip->getFromName('xl/workbook.xml');
    preg_match('/<sheet [^>]*name="HRG-005"[^>]*r:id="([^"]+)"/i', $wb, $m);
    $rId = $m[1] ?? '';
    $rels = $zip->getFromName('xl/_rels/workbook.xml.rels');
    preg_match('/<Relationship [^>]*Id="' . preg_quote($rId, '/') . '"[^>]*Target="([^"]+)"/i', $rels, $m2);
    $target = 'xl/' . $m2[1];
    echo "Target: $target\n";
    
    $sheetXml = $zip->getFromName($target);
    if ($sheetXml) {
        $xml = simplexml_load_string($sheetXml);
        $rows = $xml->sheetData->row;
        $cnt = 0;
        foreach ($rows as $r) {
            $rowVals = [];
            foreach ($r->c as $c) {
                $v = (string)$c->v;
                $t = (string)$c['t'];
                if ($t === 's' && isset($strings[(int)$v])) {
                    $v = $strings[(int)$v];
                }
                $rowVals[] = (string)$c['r'] . ': ' . $v;
            }
            echo implode(" | ", $rowVals) . "\n";
            if (++$cnt > 40) break;
        }
    }
    $zip->close();
}
