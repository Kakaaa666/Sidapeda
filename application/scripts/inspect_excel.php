<?php

class SimpleXlsxReader {
    private $zip;
    private $sharedStrings = [];
    private $sheets = [];

    public function __construct($filename) {
        $this->zip = new ZipArchive();
        if ($this->zip->open($filename) !== true) {
            throw new Exception("Cannot open $filename");
        }
        $this->loadSharedStrings();
        $this->loadSheets();
    }

    private function loadSharedStrings() {
        $xmlContent = $this->zip->getFromName('xl/sharedStrings.xml');
        if ($xmlContent !== false) {
            $xml = simplexml_load_string($xmlContent);
            foreach ($xml->si as $si) {
                if (isset($si->t)) {
                    $this->sharedStrings[] = (string)$si->t;
                } elseif (isset($si->r)) {
                    $t = '';
                    foreach ($si->r as $r) {
                        $t .= (string)$r->t;
                    }
                    $this->sharedStrings[] = $t;
                } else {
                    $this->sharedStrings[] = '';
                }
            }
        }
    }

    private function loadSheets() {
        $wbContent = $this->zip->getFromName('xl/workbook.xml');
        if ($wbContent === false) return;
        $wb = simplexml_load_string($wbContent);
        
        $relsContent = $this->zip->getFromName('xl/_rels/workbook.xml.rels');
        $sheetFiles = [];
        if ($relsContent !== false) {
            $rels = simplexml_load_string($relsContent);
            foreach ($rels->Relationship as $rel) {
                $id = (string)$rel['Id'];
                $target = (string)$rel['Target'];
                $sheetFiles[$id] = 'xl/' . ltrim($target, '/');
            }
        }

        $i = 1;
        foreach ($wb->sheets->sheet as $s) {
            $name = (string)$s['name'];
            $rId = (string)$s->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
            $file = isset($sheetFiles[$rId]) ? $sheetFiles[$rId] : "xl/worksheets/sheet{$i}.xml";
            $this->sheets[$name] = $file;
            $i++;
        }
    }

    public function getSheetNames() {
        return array_keys($this->sheets);
    }

    public function getRows($sheetName, $maxRows = 50) {
        if (!isset($this->sheets[$sheetName])) return [];
        $file = $this->sheets[$sheetName];
        $xmlContent = $this->zip->getFromName($file);
        if ($xmlContent === false) return [];
        
        $xml = simplexml_load_string($xmlContent);
        $rows = [];
        $rowCount = 0;

        foreach ($xml->sheetData->row as $r) {
            $rowNum = (int)$r['r'];
            $rowCells = [];
            foreach ($r->c as $c) {
                $cellRef = (string)$c['r'];
                // Extract column letters
                preg_match('/^([A-Z]+)/', $cellRef, $m);
                $colLetters = $m[1];
                $colIndex = 0;
                for ($j = 0; $j < strlen($colLetters); $j++) {
                    $colIndex = $colIndex * 26 + (ord($colLetters[$j]) - ord('A') + 1);
                }
                
                $type = (string)$c['t'];
                $val = isset($c->v) ? (string)$c->v : '';
                if ($type === 's') {
                    $idx = (int)$val;
                    $cellVal = isset($this->sharedStrings[$idx]) ? $this->sharedStrings[$idx] : '';
                } elseif ($type === 'inlineStr' && isset($c->is->t)) {
                    $cellVal = (string)$c->is->t;
                } else {
                    $cellVal = $val;
                }
                $rowCells[$colIndex] = $cellVal;
            }
            // Fill empty columns up to max column
            $maxCol = !empty($rowCells) ? max(array_keys($rowCells)) : 0;
            $denseRow = [];
            for ($k = 1; $k <= $maxCol; $k++) {
                $denseRow[] = isset($rowCells[$k]) ? $rowCells[$k] : '';
            }
            $rows[] = $denseRow;
            $rowCount++;
            if ($maxRows > 0 && $rowCount >= $maxRows) break;
        }
        return $rows;
    }

    public function __destruct() {
        if ($this->zip) {
            $this->zip->close();
        }
    }
}

$files = [
    'KES' => 'c:/Users/MUHAMMAD EKA/Downloads/KES - Data Dashboard ekonomi.xlsx',
    'INV' => 'c:/Users/MUHAMMAD EKA/Downloads/INV-Data Dashboard ekonomi.xlsx'
];

foreach ($files as $tag => $f) {
    echo "=========================================================\n";
    echo "FILE: $tag -> $f\n";
    if (!file_exists($f)) {
        echo "FILE NOT FOUND!\n";
        continue;
    }
    echo "File size: " . filesize($f) . " bytes\n";
    try {
        $reader = new SimpleXlsxReader($f);
        $sheets = $reader->getSheetNames();
        echo "Sheets (" . count($sheets) . "): " . implode(', ', $sheets) . "\n\n";

        foreach ($sheets as $sName) {
            echo "--- SHEET: $sName ---\n";
            $rows = $reader->getRows($sName, 15);
            echo "Total preview rows: " . count($rows) . "\n";
            foreach ($rows as $idx => $r) {
                // Filter out empty trailing columns for display
                $display = array_slice($r, 0, 15);
                echo "Row " . ($idx + 1) . ": " . json_encode($display, JSON_UNESCAPED_UNICODE) . "\n";
            }
            echo "\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
