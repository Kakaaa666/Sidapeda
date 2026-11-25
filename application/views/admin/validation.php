<?php
$canValidate = !empty($user) && in_array((int)($user['level'] ?? 1), array(1, 2));
?>
<div class="page admin-page">
    <div class="admin-intro">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions">
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success" style="background:#eaf7ee; border:1px solid #95d5a6; color:#1e6a38; padding:12px 16px; border-radius:9px; margin-bottom:16px; display:flex; align-items:center; gap:10px; font-size:12px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
            <span><?= $this->session->flashdata('success') ?></span>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-error" style="background:#fdf2f2; border:1px solid #f8b4b4; color:#9b1c1c; padding:12px 16px; border-radius:9px; margin-bottom:16px; display:flex; align-items:center; gap:10px; font-size:12px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            <span><?= $this->session->flashdata('error') ?></span>
        </div>
    <?php endif; ?>

    <!-- STATUS SUMMARY METRICS -->
    <div class="metric-row" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 16px;">
        <div class="metric-box">
            <strong><?= $stats['total'] ?></strong>
            <span>Total Data Indikator</span>
            <small>Semua data aktif</small>
        </div>
        <div class="metric-box" style="border-left: 3px solid var(--yellow, #eab308);">
            <strong style="color: #b45309;"><?= $stats['review'] ?></strong>
            <span>Perlu Review</span>
            <small>Menunggu persetujuan</small>
        </div>
        <div class="metric-box" style="border-left: 3px solid var(--green, #16a34a);">
            <strong style="color: #15803d;"><?= $stats['tervalidasi'] ?></strong>
            <span>Tervalidasi</span>
            <small>Publik & terverifikasi</small>
        </div>
        <div class="metric-box" style="border-left: 3px solid var(--red, #ef4444);">
            <strong style="color: #dc2626;"><?= $stats['ditolak'] ?></strong>
            <span>Ditolak / Belum Lengkap</span>
            <small>Perlu revisi data</small>
        </div>
    </div>

    <!-- TOOLBAR FILTER & SEARCH -->
    <div class="management-toolbar">
        <div class="search wide">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" id="validation-search-input" value="<?= htmlspecialchars($query ?? '') ?>" placeholder="Cari kode, indikator, klaster, wilayah...">
        </div>
        <select id="validation-filter-year">
            <option value="" <?= empty($selected_year) ? 'selected' : '' ?>>Semua Tahun</option>
            <?php if (!empty($available_years)): ?>
                <?php foreach ($available_years as $ay): ?>
                    <option value="<?= $ay['tahun'] ?>" <?= (!empty($selected_year) && $selected_year == $ay['tahun']) ? 'selected' : '' ?>>
                        Tahun <?= $ay['tahun'] ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        <select id="validation-filter-cluster">
            <option value="" <?= empty($selected_cluster) ? 'selected' : '' ?>>Semua Klaster (9 Klaster)</option>
            <?php if (!empty($clusters)): ?>
                <?php foreach ($clusters as $c): ?>
                    <option value="<?= htmlspecialchars($c['klaster']) ?>" <?= (!empty($selected_cluster) && $selected_cluster === $c['klaster']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['klaster']) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        <select id="validation-filter-status">
            <option value="Semua status" <?= ($status === 'Semua status' || empty($status)) ? 'selected' : '' ?>>Semua Status (<?= $stats['total'] ?>)</option>
            <option value="Perlu review" <?= $status === 'Perlu review' ? 'selected' : '' ?>>Perlu Review (<?= $stats['review'] ?>)</option>
            <option value="Tervalidasi" <?= $status === 'Tervalidasi' ? 'selected' : '' ?>>Tervalidasi (<?= $stats['tervalidasi'] ?>)</option>
            <option value="Belum lengkap" <?= ($status === 'Belum lengkap' || $status === 'Ditolak') ? 'selected' : '' ?>>Ditolak / Belum Lengkap (<?= $stats['ditolak'] ?>)</option>
        </select>
    </div>

    <?php
    // Kelompokkan rekaman per indikator untuk tampilan matriks/pivot tahun ke samping (persis seperti Database Ekonomi)
    $grouped = [];
    $subGrouped = [];
    $yearSet = [];

    // Daftar resmi parent yang benar-benar memiliki sub-indikator sektoral, kuartal & kategori
    $sectoralParents = [
        'EKO-003', 'EKO-004', 'EKO-006', 'EKO-007', 
        'INV-001', 'INV-002', 'INV-003', 
        'TK-007', 'TK-008', 'TK-010',
        'PAR-001', 'PAR-002', 'PAR-003', 'PAR-004', 'PAR-005', 'PAR-006',
        'PRO-001', 'PRO-002', 'PRO-004',
        'FIS-001', 'FIS-002', 'FIS-003',
        'HRG-001', 'HRG-002', 'HRG-003', 'HRG-004', 'HRG-005', 'HRG-006'
    ];
    $excludedCodes = ['kk1', 'EKO-015', 'EKO-016'];

    foreach ($records as $r) {
        $code = trim($r['code']);
        $mainCode = explode('.', $code)[0];
        $prefix = strtoupper(explode('-', $mainCode)[0]);

        if (in_array($code, $excludedCodes) || in_array($mainCode, $excludedCodes) || in_array($prefix, ['KK1'])) {
            continue;
        }

        $yr = (int)$r['year'];
        $yearSet[$yr] = true;

        $isSub = (strpos($code, '.') !== false || $code === 'FIS-004');
        $parentCode = $isSub ? ($code === 'FIS-004' ? 'FIS-003' : explode('.', $code)[0]) : $code;
        $subKey = $isSub ? ($code === 'FIS-004' ? 'MOD' : explode('.', $code)[1]) : null;

        if ($isSub && in_array($parentCode, $sectoralParents)) {
            if (!isset($subGrouped[$parentCode])) {
                $subGrouped[$parentCode] = [];
            }
            if (!isset($subGrouped[$parentCode][$code])) {
                $subGrouped[$parentCode][$code] = [
                    'code' => $code,
                    'parent_code' => $parentCode,
                    'sub_key' => $subKey,
                    'indicator' => $r['indicator'] ?? $r['nama_indikator'] ?? '',
                    'cluster' => $r['cluster'] ?? $r['klaster'] ?? '',
                    'region' => $r['region'] ?? $r['wilayah'] ?? '',
                    'unit' => $r['unit'] ?? $r['satuan'] ?? '',
                    'source' => $r['source'] ?? $r['sumber_data'] ?? '',
                    'definition' => $r['definition'] ?? $r['definisi_operasional'] ?? '',
                    'period' => $r['period'] ?? $r['periode'] ?? 'Tahunan',
                    'status' => $r['status'] ?? $r['status_validasi'] ?? 'Perlu review',
                    'verified_by' => $r['verified_by'] ?? $r['diverifikasi_oleh'] ?? '',
                    'notes' => $r['notes'] ?? $r['catatan'] ?? '',
                    'is_deleted' => $r['is_deleted'] ?? 0,
                    'deleted_at' => $r['deleted_at'] ?? null,
                    'latest_id' => $r['id'],
                    'latest_year' => $yr,
                    'years' => []
                ];
            }
            // Prioritaskan rekaman Tahunan / Semesteran dibanding bulanan agar sel matriks tahunan akurat
            if (!isset($subGrouped[$parentCode][$code]['years'][$yr]) || ($r['period'] ?? $r['periode'] ?? '') === 'Tahunan' || ($r['period'] ?? $r['periode'] ?? '') === 'Semesteran (Agustus)') {
                $subGrouped[$parentCode][$code]['years'][$yr] = $r;
            }
            if ($yr >= $subGrouped[$parentCode][$code]['latest_year']) {
                $subGrouped[$parentCode][$code]['latest_id'] = $r['id'];
                $subGrouped[$parentCode][$code]['latest_year'] = $yr;
                $subGrouped[$parentCode][$code]['status'] = $r['status'] ?? $r['status_validasi'] ?? 'Perlu review';
            }
        } elseif (!$isSub) {
            if (!isset($grouped[$code])) {
                $grouped[$code] = [
                    'code' => $code,
                    'indicator' => $r['indicator'] ?? $r['nama_indikator'] ?? '',
                    'cluster' => $r['cluster'] ?? $r['klaster'] ?? '',
                    'region' => $r['region'] ?? $r['wilayah'] ?? '',
                    'unit' => $r['unit'] ?? $r['satuan'] ?? '',
                    'source' => $r['source'] ?? $r['sumber_data'] ?? '',
                    'definition' => $r['definition'] ?? $r['definisi_operasional'] ?? '',
                    'period' => $r['period'] ?? $r['periode'] ?? 'Tahunan',
                    'status' => $r['status'] ?? $r['status_validasi'] ?? 'Perlu review',
                    'verified_by' => $r['verified_by'] ?? $r['diverifikasi_oleh'] ?? '',
                    'notes' => $r['notes'] ?? $r['catatan'] ?? '',
                    'is_deleted' => $r['is_deleted'] ?? 0,
                    'deleted_at' => $r['deleted_at'] ?? null,
                    'latest_id' => $r['id'],
                    'latest_year' => $yr,
                    'years' => [],
                    'sub_indicators' => []
                ];
            }
            // Prioritaskan rekaman Tahunan / Semesteran dibanding bulanan agar sel matriks tahunan akurat
            if (!isset($grouped[$code]['years'][$yr]) || ($r['period'] ?? $r['periode'] ?? '') === 'Tahunan' || ($r['period'] ?? $r['periode'] ?? '') === 'Semesteran (Agustus)') {
                $grouped[$code]['years'][$yr] = $r;
            }
            if ($yr >= $grouped[$code]['latest_year']) {
                $grouped[$code]['latest_id'] = $r['id'];
                $grouped[$code]['latest_year'] = $yr;
                $grouped[$code]['status'] = $r['status'] ?? $r['status_validasi'] ?? 'Perlu review';
                $grouped[$code]['verified_by'] = $r['verified_by'] ?? $r['diverifikasi_oleh'] ?? '';
                $grouped[$code]['notes'] = $r['notes'] ?? $r['catatan'] ?? '';
            }
        }
    }

    // Urutan abjad sektor standar BPS (A s.d. U, plus Tanpa Migas), kuartal investasi, TK, PAR, PRO, & FIS
    $sectorOrder = array('A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6, 'G' => 7, 'H' => 8, 'I' => 9, 'J' => 10, 'K' => 11, 'L' => 12, 'MN' => 13, 'O' => 14, 'P' => 15, 'Q' => 16, 'RSTU' => 17, 'TM' => 18);
    $quarterOrder = array('Q1' => 1, 'Q2' => 2, 'Q3' => 3, 'Q4' => 4);
    $monthOrder = array('JAN' => 1, 'FEB' => 2, 'MAR' => 3, 'APR' => 4, 'MEI' => 5, 'JUN' => 6, 'JUL' => 7, 'AGS' => 8, 'SEP' => 9, 'OKT' => 10, 'NOV' => 11, 'DES' => 12);
    $tk07Order = array('AGR' => 1, 'MAN' => 2, 'SRV' => 3, 'S1' => 4, 'S2' => 5, 'S3' => 6, 'S4' => 7, 'S5' => 8, 'S6' => 9);
    $tk08Order = array('FORMAL' => 1, 'INFORMAL' => 2);
    $tk10Order = array('JML' => 1);
    $par03Order = array('DOM' => 1, 'MAN' => 2);
    $par04Order = array('BIN' => 1, 'NON' => 2);
    $par05Order = array('BIN' => 1, 'NON' => 2);
    $par06Order = array('BIN' => 1, 'NON' => 2, 'RES' => 3);
    $proPanganOrder = array('PADI' => 1, 'JAG' => 2, 'KDL' => 3, 'UBK' => 4, 'UBJ' => 5);
    $pro04Order = array('LAUT' => 1, 'UMUM' => 2);
    $fis01Order = array('PAJ' => 1, 'RET' => 2, 'KKY' => 3, 'LL' => 4);
    $fis02Order = array('PUS' => 1, 'DAE' => 2);

    // Sambungkan sub_indicators HANYA ke parent yang ada di $sectoralParents
    foreach ($subGrouped as $pCode => $subs) {
        if (!in_array($pCode, $sectoralParents) || !isset($grouped[$pCode])) {
            continue;
        }

        uasort($subs, function($a, $b) use (
            $sectorOrder, $quarterOrder, $monthOrder, $tk07Order, $tk08Order, $tk10Order, 
            $par03Order, $par04Order, $par05Order, $par06Order, $proPanganOrder, $pro04Order, 
            $fis01Order, $fis02Order, $pCode
        ) {
            $kA = $a['sub_key'] ?? '';
            $kB = $b['sub_key'] ?? '';
            if (strpos($pCode, 'INV-') === 0) {
                $posA = $quarterOrder[$kA] ?? 99;
                $posB = $quarterOrder[$kB] ?? 99;
            } elseif (in_array($pCode, ['PAR-001', 'PAR-002', 'HRG-001', 'HRG-003', 'HRG-004'])) {
                $posA = $monthOrder[$kA] ?? 99;
                $posB = $monthOrder[$kB] ?? 99;
            } elseif (in_array($pCode, ['HRG-002', 'HRG-005', 'HRG-006'])) {
                $posA = is_numeric($kA) ? (int)$kA : 99;
                $posB = is_numeric($kB) ? (int)$kB : 99;
            } elseif ($pCode === 'TK-007') {
                $posA = $tk07Order[$kA] ?? 99;
                $posB = $tk07Order[$kB] ?? 99;
            } elseif ($pCode === 'TK-008') {
                $posA = $tk08Order[$kA] ?? 99;
                $posB = $tk08Order[$kB] ?? 99;
            } elseif ($pCode === 'TK-010') {
                $posA = $tk10Order[$kA] ?? 99;
                $posB = $tk10Order[$kB] ?? 99;
            } elseif ($pCode === 'PAR-003') {
                $posA = $par03Order[$kA] ?? 99;
                $posB = $par03Order[$kB] ?? 99;
            } elseif ($pCode === 'PAR-004') {
                $posA = $par04Order[$kA] ?? 99;
                $posB = $par04Order[$kB] ?? 99;
            } elseif ($pCode === 'PAR-005') {
                $posA = $par05Order[$kA] ?? 99;
                $posB = $par05Order[$kB] ?? 99;
            } elseif ($pCode === 'PAR-006') {
                $posA = $par06Order[$kA] ?? 99;
                $posB = $par06Order[$kB] ?? 99;
            } elseif (in_array($pCode, ['PRO-001', 'PRO-002'])) {
                $posA = $proPanganOrder[$kA] ?? 99;
                $posB = $proPanganOrder[$kB] ?? 99;
            } elseif ($pCode === 'PRO-004') {
                $posA = $pro04Order[$kA] ?? 99;
                $posB = $pro04Order[$kB] ?? 99;
            } elseif ($pCode === 'FIS-001') {
                $posA = $fis01Order[$kA] ?? 99;
                $posB = $fis01Order[$kB] ?? 99;
            } elseif ($pCode === 'FIS-002') {
                $posA = $fis02Order[$kA] ?? 99;
                $posB = $fis02Order[$kB] ?? 99;
            } elseif ($pCode === 'FIS-003') {
                $posA = 1;
                $posB = 1;
            } else {
                $posA = $sectorOrder[$kA] ?? 99;
                $posB = $sectorOrder[$kB] ?? 99;
            }
            if ($posA === $posB) return strcmp($kA, $kB);
            return $posA - $posB;
        });

        $grouped[$pCode]['sub_indicators'] = $subs;

        // Auto-akumulasi nilai TOTAL pada baris parent untuk indikator sektoral / kuartal
        $canAutoSum = in_array($pCode, [
            'EKO-003', 'EKO-004', 'EKO-007', 
            'INV-001', 'INV-002', 'INV-003', 
            'TK-008',
            'PAR-001', 'PAR-002', 'PAR-003', 'PAR-005', 'PAR-006',
            'PRO-001', 'PRO-002', 'PRO-004',
            'FIS-001', 'FIS-002'
        ]);
        if ($canAutoSum) {
            $subYears = [];
            foreach ($subs as $sCode => $sData) {
                // Hindari duplikasi TM (Tanpa Migas) pada PDRB dan RES (Restoran) pada Total Hotel
                if ($sCode === $pCode . '.TM' || $sCode === $pCode . '.RES') continue;

                if (!empty($sData['years'])) {
                    foreach ($sData['years'] as $sYr => $sRec) {
                        $sYr = (int)$sYr;
                        if (!isset($subYears[$sYr])) {
                            $subYears[$sYr] = [
                                'total' => 0,
                                'count' => 0,
                                'status' => $sRec['status'] ?? $sRec['status_validasi'] ?? 'Tervalidasi',
                                'id' => $sRec['id'] ?? 0
                            ];
                        }
                        $subYears[$sYr]['total'] += (float)($sRec['value'] ?? $sRec['nilai'] ?? 0);
                        $subYears[$sYr]['count']++;
                    }
                }
            }

            foreach ($subYears as $sYr => $syData) {
                if ($syData['count'] > 0) {
                    if (!isset($grouped[$pCode]['years'][$sYr])) {
                        $grouped[$pCode]['years'][$sYr] = [
                            'id' => $syData['id'],
                            'code' => $pCode,
                            'year' => $sYr,
                            'value' => $syData['total'],
                            'nilai' => $syData['total'],
                            'status' => $syData['status'],
                            'status_validasi' => $syData['status'],
                            'period' => 'Tahunan',
                            'periode' => 'Tahunan',
                            'unit' => $grouped[$pCode]['unit'] ?? '',
                            'satuan' => $grouped[$pCode]['unit'] ?? '',
                            'is_total' => true
                        ];
                    } else {
                        $grouped[$pCode]['years'][$sYr]['value'] = $syData['total'];
                        $grouped[$pCode]['years'][$sYr]['nilai'] = $syData['total'];
                        $grouped[$pCode]['years'][$sYr]['is_total'] = true;
                    }
                }
            }
        }
    }

    // Urutan resmi prefix indikator (dimulai dari EKO, NAK, TK, INV, HRG, KES, PAR, PRO, FIS, GEO, EWS, ANA)
    $indicatorPrefixOrder = array(
        'EKO' => 1,
        'NAK' => 2,
        'TK'  => 3,
        'INV' => 4,
        'HRG' => 5,
        'KES' => 6,
        'PAR' => 7,
        'PRO' => 8,
        'FIS' => 9,
        'GEO' => 10,
        'EWS' => 11,
        'ANA' => 12
    );

    if ($sort === 'code' || empty($sort)) {
        uasort($grouped, function($itemA, $itemB) use ($indicatorPrefixOrder) {
            $codeA = $itemA['code'] ?? '';
            $codeB = $itemB['code'] ?? '';

            $mainA = explode('.', $codeA)[0];
            $mainB = explode('.', $codeB)[0];

            $partsA = explode('-', $mainA);
            $prefA = strtoupper($partsA[0]);
            $numA = isset($partsA[1]) ? (int)$partsA[1] : 0;

            $partsB = explode('-', $mainB);
            $prefB = strtoupper($partsB[0]);
            $numB = isset($partsB[1]) ? (int)$partsB[1] : 0;

            $rankA = $indicatorPrefixOrder[$prefA] ?? 99;
            $rankB = $indicatorPrefixOrder[$prefB] ?? 99;

            if ($rankA !== $rankB) {
                return $rankA - $rankB;
            }
            if ($numA !== $numB) {
                return $numA - $numB;
            }
            return strcmp($codeA, $codeB);
        });
    }

    // Tentukan daftar kolom tahun
    if (!empty($selected_year)) {
        $display_years = [(int)$selected_year];
    } else {
        if (!empty($available_years)) {
            $display_years = array_map('intval', array_column($available_years, 'tahun'));
        } else {
            $display_years = array_keys($yearSet);
        }
        sort($display_years);
        if (empty($display_years)) {
            $display_years = [2016, 2017, 2018, 2019, 2020, 2021, 2022, 2023, 2024, 2025];
        }
    }

    // Urutan resmi prefix kategori dan label deskripsinya
    $categoryMeta = [
        'EKO' => 'PDRB & Ekonomi',
        'NAK' => 'Kependudukan',
        'TK'  => 'Ketenagakerjaan',
        'INV' => 'Investasi & Usaha',
        'HRG' => 'Harga & Inflasi',
        'KES' => 'Kesejahteraan Sosial',
        'PAR' => 'Pariwisata & Ekraf',
        'PRO' => 'Produksi Sektoral',
        'FIS' => 'Fiskal & Keuangan',
        'GEO' => 'Geografi & Wilayah',
        'EWS' => 'Early Warning System',
        'ANA' => 'Analisis Makro'
    ];

    // Hitung jumlah indikator per kategori prefix dari $grouped
    $catPrefixCounts = [];
    foreach ($grouped as $gCode => $gItem) {
        $p = strtoupper(explode('-', $gCode)[0]);
        $catPrefixCounts[$p] = ($catPrefixCounts[$p] ?? 0) + 1;
    }

    $activeCat = !empty($selected_category) ? strtoupper(trim($selected_category)) : '';
    // Wajib memilih salah 1 kategori (tanpa opsi "Semua"): default ke kategori pertama yang tersedia (misal EKO)
    if (empty($activeCat) || (!isset($catPrefixCounts[$activeCat]) && !empty($catPrefixCounts))) {
        foreach ($categoryMeta as $pref => $label) {
            if (isset($catPrefixCounts[$pref]) && $catPrefixCounts[$pref] > 0) {
                $activeCat = $pref;
                break;
            }
        }
        if (empty($activeCat)) {
            $catKeys = array_keys($catPrefixCounts);
            $activeCat = !empty($catKeys) ? $catKeys[0] : 'EKO';
        }
    }
    ?>

    <!-- SLIDE FILTER KATEGORI (EKO, NAK, TK, INV, HRG, KES, PAR, PRO, FIS, GEO, EWS, ANA) -->
    <div class="category-slide-container" id="category-slide-container">
        <div class="category-slide-label">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
            <span>Kategori:</span>
        </div>
        <button type="button" class="category-slide-nav-btn" id="btn-slide-cat-prev" title="Geser Kiri (Sebelumnya)" aria-label="Geser Kiri">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <div class="category-slide-track" id="category-slide-track">
            <?php foreach ($categoryMeta as $pref => $label): ?>
                <?php if (isset($catPrefixCounts[$pref]) || empty($records)): ?>
                    <?php $cnt = $catPrefixCounts[$pref] ?? 0; ?>
                    <button type="button" class="category-slide-tab <?= ($activeCat === $pref) ? 'active' : '' ?>" data-prefix="<?= $pref ?>" onclick="applyCategoryFilter('<?= $pref ?>', this)">
                        <span class="cat-code-badge"><?= $pref ?></span>
                        <span><?= $label ?></span>
                        <span class="cat-count-pill"><?= $cnt ?></span>
                    </button>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php foreach ($catPrefixCounts as $pref => $cnt): ?>
                <?php if (!isset($categoryMeta[$pref])): ?>
                    <button type="button" class="category-slide-tab <?= ($activeCat === $pref) ? 'active' : '' ?>" data-prefix="<?= $pref ?>" onclick="applyCategoryFilter('<?= $pref ?>', this)">
                        <span class="cat-code-badge"><?= $pref ?></span>
                        <span><?= $pref ?></span>
                        <span class="cat-count-pill"><?= $cnt ?></span>
                    </button>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <button type="button" class="category-slide-nav-btn" id="btn-slide-cat-next" title="Geser Kanan (Selanjutnya)" aria-label="Geser Kanan">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
    </div>

    <article class="card data-card">
        <div class="card-head" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <div>
                <span class="eyebrow">VERIFIKASI & RIWAYAT VALIDASI</span>
                <h3>Daftar Validasi Data Indikator</h3>
                <p><span id="cat-filtered-count-label">Menampilkan <?= $catPrefixCounts[$activeCat] ?? count($grouped) ?> indikator kategori <?= $activeCat ?></span> (<?= count($records) ?> data tahunan). Tinjau antrean verifikasi serta riwayat data yang telah divalidasi maupun ditolak.</p>
            </div>
            <?php if (!empty($selected_year)): ?>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span class="badge info">Filter Tahun: <?= $selected_year ?></span>
                    <a href="<?= site_url('validation' . (!empty($cluster) ? '?cluster=' . urlencode($cluster) : '')) ?>" class="text-button" style="font-size:11px;">Tampilkan Seluruh Tahun</a>
                </div>
            <?php else: ?>
                <div style="font-size:11px; color:var(--muted); background:#f1f5f9; padding:5px 11px; border-radius:6px; border:1px solid #e2e8f0;">
                    <span style="color:#0f172a; font-weight:700;">Seri Tahun:</span> <?= !empty($display_years) ? (min($display_years) . ' s.d. ' . max($display_years) . ' (' . count($display_years) . ' Tahun)') : '-' ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- TABEL DERET WAKTU: TAHUN KESAMPING (MATRIKS DATABASE EKONOMI) -->
        <div class="data-table-matrix-wrapper" style="overflow-x:auto; -webkit-overflow-scrolling:touch; width:100%; border-top:1px solid var(--line);">
            <table class="data-matrix-table" id="admin-matrix-table" style="width:100%; min-width:<?= 720 + (count($display_years) * 120) ?>px; border-collapse:separate; border-spacing:0;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th class="matrix-sticky-col-1" style="position:sticky; left:0; background:#f8fafc; z-index:3; width:90px; min-width:90px; padding:12px 14px; font-size:11px; font-weight:700; color:#475569; border-bottom:1px solid var(--line); box-shadow:2px 0 5px rgba(0,0,0,0.03); text-align:left;">
                            Kode
                        </th>
                        <th style="min-width:350px; width:360px; padding:12px 16px; font-size:11px; font-weight:700; color:#475569; border-bottom:1px solid var(--line); text-align:left;">
                            Nama Indikator
                        </th>
                        <th style="min-width:145px; padding:12px 14px; font-size:11px; font-weight:700; color:#475569; border-bottom:1px solid var(--line); text-align:left;">
                            Klaster
                        </th>
                        <th style="min-width:100px; padding:12px 14px; font-size:11px; font-weight:700; color:#475569; border-bottom:1px solid var(--line); text-align:left;">
                            Satuan
                        </th>
                        <?php foreach ($display_years as $yr): ?>
                            <th class="th-matrix-year <?= (!empty($selected_year) && $selected_year == $yr) ? 'active-year-col' : '' ?>" style="min-width:120px; text-align:right; padding:12px 14px; font-size:12px; font-weight:700; color:#1e293b; border-bottom:1px solid var(--line); border-left:1px solid #edf2f7; <?= ($yr == 2024 || $yr == $selected_year) ? 'background:#e0f2fe; color:#0369a1;' : '' ?>">
                                <?= $yr ?>
                            </th>
                        <?php endforeach; ?>
                        <th class="matrix-sticky-col-last" style="position:sticky; right:0; background:#f8fafc; z-index:3; width:130px; padding:12px 12px; text-align:center; font-size:11px; font-weight:700; color:#475569; border-bottom:1px solid var(--line); box-shadow:-2px 0 5px rgba(0,0,0,0.03);">
                            Aksi Validasi
                        </th>
                    </tr>
                </thead>
                <tbody id="validation-table-body">
                    <?php if (empty($grouped)): ?>
                        <tr>
                            <td colspan="<?= 5 + count($display_years) ?>" style="padding:48px 20px; text-align:center; color:var(--muted);">
                                <div style="display:flex; flex-direction:column; align-items:center; gap:8px;">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
                                    <strong>Tidak ada indikator yang cocok dengan filter saat ini.</strong>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($grouped as $code => $item): ?>
                            <?php
                                $hasSub = !empty($item['sub_indicators']);
                                $unverifiedCount = 0;
                                $rejectedCount = 0;
                                $validCount = 0;
                                foreach ($item['years'] as $yRec) {
                                    $ySt = $yRec['status'] ?? 'Perlu review';
                                    if ($ySt === 'Tervalidasi') $validCount++;
                                    elseif ($ySt === 'Belum lengkap' || $ySt === 'Ditolak') $rejectedCount++;
                                    else $unverifiedCount++;
                                }
                                $hasSub = (!empty($item['sub_indicators']) && in_array($item['code'], $sectoralParents));
                                $itemPrefix = strtoupper(explode('-', $item['code'])[0]);
                                $isMatchCat = ($itemPrefix === $activeCat);
                            ?>
                            <tr class="matrix-row <?= $hasSub ? 'has-subcategories' : '' ?>" data-code="<?= $item['code'] ?>" data-prefix="<?= $itemPrefix ?>" <?= $hasSub ? 'data-has-sub="true" title="Klik baris ini untuk membuka/menutup rincian sub-indikator" onclick="if(!event.target.closest(\'button, a, input, select, .matrix-sticky-col-last\')) toggleIndicatorSubRows(\''.htmlspecialchars($item['code']).'\', event);"' : '' ?> style="display: <?= $isMatchCat ? 'table-row' : 'none' ?>;">
                                <td class="matrix-sticky-col-1" style="position:sticky; left:0; background:#fff; z-index:2; padding:11px 14px; border-bottom:1px solid #edf0f4; box-shadow:2px 0 5px rgba(0,0,0,0.03); white-space:nowrap;">
                                    <code style="font-size:11px; font-weight:700; color:var(--blue); background:#eef4fa; padding:3px 7px; border-radius:5px; display:inline-block;"><?= htmlspecialchars($item['code']) ?></code>
                                </td>
                                <td class="<?= $hasSub ? 'clickable-indicator-cell' : '' ?>" style="min-width:350px; max-width:440px; padding:12px 16px; border-bottom:1px solid #edf0f4; vertical-align:middle; cursor:<?= $hasSub ? 'pointer' : 'default' ?>;" <?= $hasSub ? 'data-parent="'.$item['code'].'" onclick="toggleIndicatorSubRows(\''.htmlspecialchars($item['code']).'\', event);"' : '' ?>>
                                    <div style="font-size:13px; font-weight:700; color:#1e293b; line-height:1.4;">
                                        <span class="<?= $hasSub ? 'indicator-title-clickable' : '' ?>"><?= htmlspecialchars($item['indicator']) ?></span>
                                    </div>
                                    <?php if ($hasSub): ?>
                                        <?php 
                                            $isInv = (strpos($item['code'], 'INV-') === 0);
                                            $isTk07 = ($item['code'] === 'TK-007');
                                            $isTk08 = ($item['code'] === 'TK-008');
                                            $isTk10 = ($item['code'] === 'TK-010');
                                            if ($isInv) {
                                                $openLabel = 'Rincian 4 Kuartal';
                                            } elseif ($isTk07) {
                                                $openLabel = 'Rincian Sektor & Status';
                                            } elseif ($isTk08) {
                                                $openLabel = 'Rincian Formal & Informal';
                                            } elseif ($isTk10) {
                                                $openLabel = 'Rincian Setengah Pengangguran';
                                            } elseif (in_array($item['code'], ['PAR-001', 'PAR-002', 'HRG-001', 'HRG-003', 'HRG-004'])) {
                                                $openLabel = 'Rincian 12 Bulan';
                                            } elseif ($item['code'] === 'PAR-003') {
                                                $openLabel = 'Rincian Tamu Hotel';
                                            } elseif ($item['code'] === 'PAR-004') {
                                                $openLabel = 'Rincian TPK Hotel';
                                            } elseif ($item['code'] === 'PAR-005') {
                                                $openLabel = 'Rincian Kamar Hotel';
                                            } elseif ($item['code'] === 'PAR-006') {
                                                $openLabel = 'Rincian Hotel & Restoran';
                                            } elseif (in_array($item['code'], ['PRO-001', 'PRO-002', 'PRO-004'])) {
                                                $openLabel = 'Rincian 25 Kecamatan';
                                            } elseif ($item['code'] === 'FIS-001') {
                                                $openLabel = 'Rincian 4 Komponen PAD';
                                            } elseif ($item['code'] === 'FIS-002') {
                                                $openLabel = 'Rincian Transfer Daerah';
                                            } elseif ($item['code'] === 'FIS-003') {
                                                $openLabel = 'Rincian Komponen';
                                            } elseif (in_array($item['code'], ['HRG-002', 'HRG-006'])) {
                                                $openLabel = 'Rincian 11 Kelompok';
                                            } elseif ($item['code'] === 'HRG-005') {
                                                $openLabel = 'Rincian 20 Kelompok Pangan (48 Komoditas)';
                                            } else {
                                                $openLabel = 'Rincian 17 Sektor';
                                            }
                                        ?>
                                        <div style="margin-top:5px; margin-bottom:2px;">
                                            <button type="button" class="btn-toggle-subcategories" data-parent="<?= $item['code'] ?>" data-label-open="<?= $openLabel ?>" onclick="toggleIndicatorSubRows('<?= htmlspecialchars($item['code']) ?>', event);" style="display:inline-flex; align-items:center; gap:5px; padding:3px 9px; font-size:11px; font-weight:700; background:#f0fdf4; color:#15803d; border:1px solid #86efac; border-radius:6px; cursor:pointer; transition:all 0.15s ease; white-space:nowrap;" title="Klik untuk membuka/menutup <?= $openLabel ?>">
                                                <svg class="sub-arrow-icon" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="transition:transform 0.2s ease;"><polyline points="6 9 12 15 18 9"/></svg>
                                                <span class="sub-toggle-label"><?= $openLabel ?></span>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                    <small style="font-size: 11.5px; color:#64748b; display:block; margin-top:3px;">
                                        <?= htmlspecialchars($item['source']) ?>
                                    </small>
                                    <?php if (!empty($item['definition'])): ?>
                                        <div class="table-definition-preview" title="<?= htmlspecialchars($item['definition']) ?>" style="font-size: 11.5px; color:#94a3b8; margin-top:3px; line-height:1.35; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; max-width:340px;">
                                            <span style="color:#64748b; font-weight:600;">Definisi:</span> <?= htmlspecialchars($item['definition']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($item['notes']) && $item['status'] !== 'Tervalidasi'): ?>
                                        <div style="margin-top:4px; font-size: 11px; line-height:1.3; color:#b91c1c; background:#fef2f2; border:1px solid #fecaca; border-radius:4px; padding:2px 6px; display:inline-flex; align-items:center; gap:4px;">
                                            <span><strong>Catatan:</strong> <?= htmlspecialchars($item['notes']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="min-width:145px; padding:11px 14px; border-bottom:1px solid #edf0f4; vertical-align:middle; white-space:nowrap;">
                                    <span class="badge secondary" style="font-size: 11.5px; padding:3px 8px; white-space:nowrap;">
                                        <?= htmlspecialchars($item['cluster']) ?>
                                    </span>
                                </td>
                                <td style="min-width:100px; padding:11px 14px; border-bottom:1px solid #edf0f4; font-size:11.5px; color:#475569; vertical-align:middle; white-space:nowrap;">
                                    <?= htmlspecialchars($item['unit']) ?>
                                </td>
                                <?php foreach ($display_years as $yr): ?>
                                    <td class="matrix-cell-td <?= (!empty($selected_year) && $selected_year == $yr) ? 'active-year-cell' : '' ?>" style="padding:8px 10px; border-bottom:1px solid #edf0f4; border-left:1px solid #f1f5f9; text-align:right; font-variant-numeric:tabular-nums; vertical-align:middle; <?= ($yr == 2024 || $yr == $selected_year) ? 'background:#f8fbff;' : '' ?>">
                                        <?php if (isset($item['years'][$yr])): ?>
                                            <?php
                                                $rec = $item['years'][$yr];
                                                $isRupiah = is_indicator_rupiah($item['unit'] ?? '', $item['code'] ?? '');
                                                $prefixRp = $isRupiah ? 'Rp ' : '';
                                                $formattedVal = format_indicator_value($rec['value'], $item['unit'] ?? '', $item['code'] ?? '', false);
                                                $st = $rec['status'] ?? 'Perlu review';
                                                $isValid = ($st === 'Tervalidasi');
                                                $isRejected = ($st === 'Belum lengkap' || $st === 'Ditolak');
                                                $isReview = ($st === 'Perlu review');
                                                $bColor = $isValid ? '#15803d' : ($isRejected ? '#dc2626' : '#b45309');
                                                $bBg = $isValid ? '#dcfce7' : ($isRejected ? '#fee2e2' : '#fef3c7');
                                            ?>
                                            <div style="display:flex; flex-direction:column; align-items:flex-end; gap:3px;">
                                                <span class="matrix-val-text" style="font-weight:700; font-size:12px; color:#0f172a;">
                                                    <?= $prefixRp ?><?= $formattedVal ?>
                                                </span>
                                                <div style="display:flex; align-items:center; gap:3px;">
                                                    <span style="font-size: 12px; font-weight:700; color:<?= $bColor ?>; background:<?= $bBg ?>; padding:1px 5px; border-radius:4px; white-space:nowrap;">
                                                        <?= $isValid ? '✓ Valid' : ($isRejected ? '✕ Ditolak' : '⏱ Review') ?>
                                                    </span>
                                                    <div style="display:inline-flex; align-items:center; gap:2px;">
                                                    <?php if ($canValidate): ?>
                                                        <button type="button" class="btn-verify-record" style="width:20px; height:20px; border-radius:4px; border:1px solid <?= $isValid ? '#86efac' : '#cbd5e1' ?>; background:<?= $isValid ? '#16a34a' : '#fff' ?>; color:<?= $isValid ? '#fff' : '#16a34a' ?>; display:flex; align-items:center; justify-content:center; cursor:pointer; padding:0;" title="<?= $isValid ? 'Sudah Tervalidasi (klik untuk konfirmasi ulang)' : 'Setujui Data (Validasi)' ?>" data-code="<?= $item['code'] ?>" data-year="<?= $yr ?>" data-indicator="<?= htmlspecialchars($item['indicator']) ?>" data-status="Tervalidasi">
                                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                        </button>
                                                        <button type="button" class="btn-reject-record" style="width:20px; height:20px; border-radius:4px; border:1px solid <?= $isRejected ? '#fca5a5' : '#cbd5e1' ?>; background:<?= $isRejected ? '#dc2626' : '#fff' ?>; color:<?= $isRejected ? '#fff' : '#dc2626' ?>; display:flex; align-items:center; justify-content:center; cursor:pointer; padding:0;" title="<?= $isRejected ? 'Sudah Ditolak (klik untuk perbarui alasan)' : 'Tolak Data (Harus Disertai Alasan)' ?>" data-code="<?= $item['code'] ?>" data-year="<?= $yr ?>" data-indicator="<?= htmlspecialchars($item['indicator']) ?>" data-val="<?= $prefixRp ?><?= $formattedVal ?> <?= htmlspecialchars($item['unit']) ?>" data-period="<?= htmlspecialchars($rec['period'] ?? 'Tahunan') ?> <?= $yr ?>" data-notes="<?= htmlspecialchars($rec['notes'] ?? '') ?>">
                                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                        </button>
                                                    <?php endif; ?>
                                                    </div>
                                                </div>
                                                <?php if (!empty($rec['notes']) && !$isValid): ?>
                                                    <div style="font-size: 12px; color:#b91c1c; background:#fff; border:1px solid #fecaca; border-radius:3px; padding:1px 4px; max-width:105px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="Catatan: <?= htmlspecialchars($rec['notes']) ?>">
                                                        <?= htmlspecialchars($rec['notes']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="matrix-empty-pill" title="Belum ada data untuk <?= $yr ?>">
                                                <span class="matrix-dash" style="color:#cbd5e1; font-size:12px;">-</span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                                <td class="matrix-sticky-col-last" style="position:sticky; right:0; background:#fff; z-index:2; padding:11px 12px; border-bottom:1px solid #edf0f4; box-shadow:-2px 0 5px rgba(0,0,0,0.03); text-align:center; vertical-align:middle; white-space:nowrap;">
                                    <?php if ($canValidate && ($unverifiedCount > 0 || $rejectedCount > 0)): ?>
                                        <button type="button" class="btn-verify-series button small success" data-code="<?= $item['code'] ?>" data-indicator="<?= htmlspecialchars($item['indicator']) ?>" title="Setujui seluruh tahun yang belum tervalidasi untuk indikator ini" style="padding:4px 9px; font-size:11px; font-weight:700; display:inline-flex; align-items:center; gap:4px; white-space:nowrap; cursor:pointer;">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                            <span>Setujui Semua</span>
                                        </button>
                                    <?php elseif ($unverifiedCount === 0 && $rejectedCount === 0): ?>
                                        <span class="badge success" style="font-size: 12.5px; padding:3px 8px; display:inline-flex; align-items:center; gap:3px;">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                            <span>Semua Valid</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge warning" style="font-size: 11px; padding:3px 7px;">Menunggu Validasi</span>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- SUB-ROWS UNTUK RINCIAN KATEGORI SEKTORAL (MISAL KATEGORI A S.D. U) -->
                            <?php if (!empty($item['sub_indicators'])): ?>
                                <?php foreach ($item['sub_indicators'] as $subCode => $sub): ?>
                                    <?php
                                        $dispKey = $sub['sub_key'];
                                        $isSubInv = (strpos($item['code'], 'INV-') === 0);
                                        $isSubMonthly = in_array($item['code'], ['PAR-001', 'PAR-002']);
                                        $isSubTk07 = (strpos($item['code'], 'TK-007') === 0);
                                        $isSubTk08 = (strpos($item['code'], 'TK-008') === 0);
                                        $isSubTk10 = (strpos($item['code'], 'TK-010') === 0);
                                        $isSubFis = (strpos($item['code'], 'FIS-') === 0);
                                        $isSubPro = (strpos($item['code'], 'PRO-') === 0);
                                        $isSubPar = (strpos($item['code'], 'PAR-') === 0);
                                        $isSubHrg = (strpos($item['code'], 'HRG-') === 0);
                                        if ($dispKey === 'MN') $dispKey = 'M,N';
                                        elseif ($dispKey === 'RSTU') $dispKey = 'R,S,T,U';
                                        if ($isSubInv) {
                                            $cleanSubName = 'Kuartal ' . substr($dispKey, 1) . ' (' . $dispKey . ')';
                                            $subTypeLabel = 'Periode:';
                                        } elseif ($isSubMonthly) {
                                            $cleanSubName = $sub['period'] ?: $dispKey;
                                            $subTypeLabel = 'Bulan:';
                                        } elseif ($isSubTk07) {
                                            $cleanSubName = preg_replace('/^(Penduduk\s+Bekerja\s+Lapangan\s+Usaha:\s*|Status\s+Pekerjaan:\s*)/i', '', $sub['indicator']);
                                            $subTypeLabel = (strpos($dispKey, 'S') === 0) ? 'Status:' : 'Sektor:';
                                        } elseif ($isSubTk08) {
                                            $cleanSubName = preg_replace('/^(Pekerja\s*)/i', '', $sub['indicator']);
                                            $subTypeLabel = 'Kategori:';
                                        } elseif ($isSubTk10) {
                                            $cleanSubName = 'Jumlah Setengah Pengangguran (Orang)';
                                            $subTypeLabel = 'Satuan:';
                                        } elseif ($isSubFis) {
                                            $cleanSubName = $sub['indicator'];
                                            $subTypeLabel = 'Komponen:';
                                        } elseif ($isSubPro) {
                                            $cleanSubName = $sub['indicator'];
                                            $subTypeLabel = 'Kecamatan:';
                                        } elseif ($isSubPar) {
                                            $cleanSubName = $sub['indicator'];
                                            $subTypeLabel = 'Kategori:';
                                        } elseif ($isSubHrg) {
                                            if (in_array($item['code'], ['HRG-001', 'HRG-003', 'HRG-004'])) {
                                                $cleanSubName = $sub['indicator'];
                                                $subTypeLabel = 'Bulan:';
                                            } elseif ($item['code'] === 'HRG-005') {
                                                $cleanSubName = '- ' . preg_replace('/^Harga:\s*/i', '', $sub['indicator']);
                                                $subTypeLabel = 'Komoditas:';
                                            } else {
                                                $cleanSubName = $sub['indicator'];
                                                $subTypeLabel = 'Kelompok:';
                                            }
                                        } else {
                                            $cleanSubName = preg_replace('/^(PDRB\s+(ADHB|ADHK)|Pertumbuhan(\s+PDRB(\s+ADHK)?)?|Distribusi(\s+PDRB(\s+ADHB)?)?|Kontribusi|PDRB\s+Pengeluaran\s+(ADHB|ADHK)|Pengeluaran\s+(ADHB|ADHK)):\s*/i', '', $sub['indicator']);
                                            $subTypeLabel = 'Kategori:';
                                        }
                                        $hrgMeta = ($item['code'] === 'HRG-005') ? get_hrg005_excel_row_meta($dispKey) : array();

                                        $sUnverified = 0; $sRejected = 0; $sValid = 0;
                                        foreach ($sub['years'] as $syRec) {
                                            $sySt = $syRec['status'] ?? 'Perlu review';
                                            if ($sySt === 'Tervalidasi') $sValid++;
                                            elseif ($sySt === 'Belum lengkap' || $sySt === 'Ditolak') $sRejected++;
                                            else $sUnverified++;
                                        }
                                    ?>
                                    <?php if (!empty($hrgMeta['group_header'])): ?>
                                        <?php $gH = $hrgMeta['group_header']; ?>
                                        <tr class="matrix-row matrix-sub-row sub-row-HRG-005" data-parent="HRG-005" data-prefix="HRG" style="display:none; background:#e2e8f0; border-top:2px solid #cbd5e1; border-bottom:1px solid #cbd5e1;">
                                            <td class="matrix-sticky-col-1" style="position:sticky; left:0; background:#e2e8f0; z-index:2; padding:7px 12px 7px 16px; border-bottom:1px solid #cbd5e1; text-align:center;">
                                                <span style="font-weight:800; font-size:11.5px; color:#0369a1; background:#e0f2fe; border:1px solid #bae6fd; padding:2px 8px; border-radius:5px;"><?= $gH['no'] ?></span>
                                            </td>
                                            <td colspan="<?= 4 + count($display_years) ?>" style="padding:7px 14px; font-weight:800; font-size:12.5px; color:#0f172a; text-transform:uppercase; letter-spacing:0.02em;">
                                                <?= htmlspecialchars($gH['name']) ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($hrgMeta['sub_header'])): ?>
                                        <tr class="matrix-row matrix-sub-row sub-row-HRG-005" data-parent="HRG-005" data-prefix="HRG" style="display:none; background:#f1f5f9; border-bottom:1px solid #e2e8f0;">
                                            <td class="matrix-sticky-col-1" style="position:sticky; left:0; background:#f1f5f9; z-index:2; padding:5px 12px 5px 16px; border-bottom:1px solid #e2e8f0;"></td>
                                            <td colspan="<?= 4 + count($display_years) ?>" style="padding:5px 14px 5px 22px; font-weight:700; font-style:italic; font-size:12px; color:#334155;">
                                                <?= htmlspecialchars($hrgMeta['sub_header']) ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr class="matrix-row matrix-sub-row sub-row-<?= $item['code'] ?>" data-code="<?= $sub['code'] ?>" data-parent="<?= $item['code'] ?>" data-prefix="<?= strtoupper(explode('-', $item['code'])[0]) ?>" style="display:none; background:#f8fafc;">
                                        <td class="matrix-sticky-col-1" style="position:sticky; left:0; background:#f8fafc; z-index:2; padding:8px 12px 8px 16px; border-bottom:1px solid #edf0f4; box-shadow:2px 0 5px rgba(0,0,0,0.03); white-space:nowrap;">
                                            <div style="display:flex; align-items:center; gap:5px;">
                                                 <span style="color:#94a3b8; font-weight:700; font-size:11px;">└──</span>
                                                <?php if (!empty($hrgMeta['direct_no'])): ?>
                                                    <span style="font-weight:800; font-size:11.5px; color:#0369a1; background:#e0f2fe; border:1px solid #bae6fd; padding:2px 7px; border-radius:5px;"><?= $hrgMeta['direct_no'] ?></span>
                                                <?php endif; ?>
                                                <code style="font-size: 12.5px; font-weight:700; color:#0369a1; background:#e0f2fe; padding:2px 6px; border-radius:4px;"><?= htmlspecialchars($sub['code']) ?></code>
                                                <span style="font-size: 12px; font-weight:800; color:#475569; background:#e2e8f0; padding:1px 5px; border-radius:4px;"><?= htmlspecialchars($dispKey) ?></span>
                                            </div>
                                        </td>
                                        <td style="padding:8px 14px; border-bottom:1px solid #edf0f4; vertical-align:middle; background:#f8fafc;">
                                            <div style="font-size:12px; font-weight:700; color:#1e293b; line-height:1.35;">
                                                <?= htmlspecialchars($sub['indicator']) ?>
                                            </div>
                                            <div style="font-size:11px; color:#475569; margin-top:2px;">
                                                <span style="color:#0284c7; font-weight:600;"><?= $subTypeLabel ?></span> <?= htmlspecialchars($cleanSubName) ?>
                                            </div>
                                            <?php if (in_array($item['code'], ['HRG-002', 'HRG-006'])): ?>
                                                <div style="font-size:10.5px; color:#0369a1; background:#e0f2fe; border:1px solid #bae6fd; border-radius:4px; padding:1px 6px; display:inline-flex; align-items:center; gap:4px; margin-top:2px;" title="Rincian data bulanan (Januari s.d. Desember) tersimpan di database">
                                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                                    <span>12 Bulan: Tersedia (Jan–Des). Nilai kolom matriks: Rata-rata Tahunan.</span>
                                                </div>
                                            <?php endif; ?>
                                            <small style="font-size: 12.5px; color:#64748b; display:block; margin-top:2px;"><?= htmlspecialchars($sub['source']) ?></small>
                                        </td>
                                        <td style="padding:7px 14px; border-bottom:1px solid #edf0f4; vertical-align:middle; background:#f8fafc;">
                                            <span class="badge secondary" style="font-size: 12px; padding:2px 6px;"><?= htmlspecialchars($sub['cluster']) ?></span>
                                        </td>
                                        <td style="padding:7px 14px; border-bottom:1px solid #edf0f4; font-size:11px; color:#475569; vertical-align:middle; white-space:nowrap; background:#f8fafc;">
                                            <?= htmlspecialchars($sub['unit']) ?>
                                        </td>
                                        <?php foreach ($display_years as $yr): ?>
                                            <td class="matrix-cell-td" style="padding:7px 10px; border-bottom:1px solid #edf0f4; border-left:1px solid #f1f5f9; text-align:right; font-variant-numeric:tabular-nums; vertical-align:middle; background:#f8fafc;">
                                                <?php if (isset($sub['years'][$yr])): ?>
                                                    <?php
                                                        $sRec = $sub['years'][$yr];
                                                        $sIsRupiah = is_indicator_rupiah($sub['unit'] ?? '', $sub['code'] ?? '');
                                                        $sPrefixRp = $sIsRupiah ? 'Rp ' : '';
                                                        $sFormattedVal = format_indicator_value($sRec['value'], $sub['unit'] ?? '', $sub['code'] ?? '', false);
                                                        $sSt = $sRec['status'] ?? 'Perlu review';
                                                        $sIsValid = ($sSt === 'Tervalidasi');
                                                        $sIsRejected = ($sSt === 'Belum lengkap' || $sSt === 'Ditolak');
                                                        $sbColor = $sIsValid ? '#15803d' : ($sIsRejected ? '#dc2626' : '#b45309');
                                                        $sbBg = $sIsValid ? '#dcfce7' : ($sIsRejected ? '#fee2e2' : '#fef3c7');
                                                    ?>
                                                    <div style="display:flex; flex-direction:column; align-items:flex-end; gap:3px;">
                                                        <span style="font-weight:600; font-size:12px; color:#0f172a;">
                                                            <?= $sPrefixRp ?><?= $sFormattedVal ?>
                                                        </span>
                                                        <div style="display:flex; align-items:center; gap:3px;">
                                                            <span style="font-size: 12px; font-weight:700; color:<?= $sbColor ?>; background:<?= $sbBg ?>; padding:1px 5px; border-radius:4px; white-space:nowrap;">
                                                                <?= $sIsValid ? '✓ Valid' : ($sIsRejected ? '✕ Ditolak' : '⏱ Review') ?>
                                                            </span>
                                                            <div style="display:inline-flex; align-items:center; gap:2px;">
                                                            <?php if ($canValidate): ?>
                                                                <button type="button" class="btn-verify-record" style="width:19px; height:19px; border-radius:4px; border:1px solid <?= $sIsValid ? '#86efac' : '#cbd5e1' ?>; background:<?= $sIsValid ? '#16a34a' : '#fff' ?>; color:<?= $sIsValid ? '#fff' : '#16a34a' ?>; display:flex; align-items:center; justify-content:center; cursor:pointer; padding:0;" title="<?= $sIsValid ? 'Sudah Tervalidasi (klik untuk konfirmasi ulang)' : 'Setujui Data (Validasi)' ?>" data-code="<?= $sub['code'] ?>" data-year="<?= $yr ?>" data-indicator="<?= htmlspecialchars($sub['indicator']) ?>" data-status="Tervalidasi">
                                                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                                </button>
                                                                <button type="button" class="btn-reject-record" style="width:19px; height:19px; border-radius:4px; border:1px solid <?= $sIsRejected ? '#fca5a5' : '#cbd5e1' ?>; background:<?= $sIsRejected ? '#dc2626' : '#fff' ?>; color:<?= $sIsRejected ? '#fff' : '#dc2626' ?>; display:flex; align-items:center; justify-content:center; cursor:pointer; padding:0;" title="<?= $sIsRejected ? 'Sudah Ditolak (klik untuk perbarui alasan)' : 'Tolak Data (Harus Disertai Alasan)' ?>" data-code="<?= $sub['code'] ?>" data-year="<?= $yr ?>" data-indicator="<?= htmlspecialchars($sub['indicator']) ?>" data-val="<?= $sPrefixRp ?><?= $sFormattedVal ?> <?= htmlspecialchars($sub['unit']) ?>" data-period="<?= htmlspecialchars($sRec['period'] ?? 'Tahunan') ?> <?= $yr ?>" data-notes="<?= htmlspecialchars($sRec['notes'] ?? '') ?>">
                                                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                                </button>
                                                            <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <?php if (!empty($sRec['notes']) && !$sIsValid): ?>
                                                            <div style="font-size: 12px; color:#b91c1c; background:#fff; border:1px solid #fecaca; border-radius:3px; padding:1px 4px; max-width:105px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="Catatan: <?= htmlspecialchars($sRec['notes']) ?>">
                                                                <?= htmlspecialchars($sRec['notes']) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span style="color:#cbd5e1; font-size:12px;">-</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                        <td class="matrix-sticky-col-last" style="position:sticky; right:0; background:#f8fafc; z-index:2; padding:7px 12px; border-bottom:1px solid #edf0f4; text-align:center; vertical-align:middle; white-space:nowrap;">
                                            <?php if ($canValidate && ($sUnverified > 0 || $sRejected > 0)): ?>
                                                <button type="button" class="btn-verify-series button small success" data-code="<?= $sub['code'] ?>" data-indicator="<?= htmlspecialchars($sub['indicator']) ?>" title="Setujui seluruh tahun yang belum tervalidasi untuk sub-indikator ini" style="padding:3px 7px; font-size: 12.5px; font-weight:700; display:inline-flex; align-items:center; gap:3px; white-space:nowrap; cursor:pointer;">
                                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                    <span>Setujui</span>
                                                </button>
                                            <?php elseif ($sUnverified === 0 && $sRejected === 0): ?>
                                                <span class="badge success" style="font-size: 12px; padding:2px 6px;">✓ Valid</span>
                                            <?php else: ?>
                                                <span class="badge warning" style="font-size: 11px; padding:2px 5px;">Menunggu</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <span>Menampilkan <strong><?= count($grouped) ?></strong> indikator (<strong><?= count($records) ?></strong> rekaman data tahunan)</span>
            <span>Verifikasi langsung mengubah status data di database</span>
        </div>
    </article>

    <!-- MODAL ALASAN PENOLAKAN / REVISI VALIDASI -->
    <div class="modal-backdrop" id="modal-reject-record" style="display:none;">
        <div class="modal" style="max-width: 520px;">
            <div class="modal-head">
                <div>
                    <span class="badge danger" style="background:#fee2e2; color:#dc2626; border-color:#fca5a5;">TOLAK / PERLU REVISI</span>
                    <h2>Tolak Validasi Data</h2>
                    <p>Sertakan alasan penolakan atau perbaikan data agar tampil di menu Database Ekonomi.</p>
                </div>
                <button type="button" class="icon-button" id="btn-close-reject-modal">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="form-reject-record" style="margin-top: 15px;">
                <input type="hidden" id="reject-code" name="code">
                <input type="hidden" id="reject-year" name="year">

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; margin-bottom: 14px; font-size: 12px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span style="color: var(--muted);">Kode Indikator:</span>
                        <strong id="reject-display-code" style="color: var(--navy); font-family: monospace;">-</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span style="color: var(--muted);">Nama Indikator:</span>
                        <strong id="reject-display-indicator" style="color: var(--navy); text-align: right; max-width: 300px;">-</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span style="color: var(--muted);">Periode / Tahun:</span>
                        <span id="reject-display-period" style="font-weight: 600;">-</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--muted);">Nilai Tercatat:</span>
                        <strong id="reject-display-value" style="color: var(--teal);">-</strong>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label for="reject-reason" style="font-size: 12px; font-weight: 600; color: var(--navy);">
                        Alasan Penolakan / Catatan Perbaikan <span style="color: #dc2626;">*</span>
                    </label>
                    <textarea id="reject-reason" name="notes" rows="4" required placeholder="Tuliskan alasan penolakan, data yang tidak sesuai, atau detail perbaikan yang diperlukan..." style="width: 100%; border: 1px solid var(--line); border-radius: 7px; padding: 10px; font-size: 12.5px; font-family: inherit; line-height: 1.5; resize: vertical; box-sizing: border-box;"></textarea>
                    <small id="reject-reason-error" style="color: #dc2626; font-size: 11px; display: none;">Alasan penolakan wajib disertakan sebelum mengirim.</small>
                </div>

                <div class="modal-actions" style="margin-top: 18px; display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" class="button ghost" id="btn-cancel-reject">Batal</button>
                    <button type="submit" class="button" id="btn-submit-reject" style="background: #dc2626; color: white; border-color: #dc2626; display: inline-flex; align-items: center; gap: 6px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/></svg>
                        <span>Simpan Penolakan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
