<?php
$data = json_decode(file_get_contents('scratch_hrg005_structure.json'), true);
echo "Total groups parsed: " . count($data) . "\n";
$totalItems = 0;
foreach ($data as $g) {
    echo "Group {$g['no']}: {$g['name']} (" . count($g['items']) . " items)\n";
    foreach ($g['items'] as $it) {
        $totalItems++;
        echo "   - {$it['name']} [{$it['satuan']}]\n";
    }
}
echo "Total items: $totalItems\n";
