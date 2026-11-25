<div class="page admin-page">
    <div class="admin-intro">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions">
            <a href="<?= site_url('early_warning') ?>" class="button ghost" target="_blank">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px;"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                Lihat Monitoring EWS Publik
            </a>
        </div>
    </div>

    <!-- FILTER TAHUN EWS ADMIN (DROPDOWN) -->
    <div class="year-filter-bar">
        <div class="year-filter-info">
            <div class="year-filter-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div class="year-filter-text">
                <strong>Periode Tahun Indikator EWS</strong>
                <small>Nilai riil diambil otomatis dari data indikator yang telah tervalidasi pada tahun terpilih.</small>
            </div>
        </div>

        <div class="year-filter-control">
            <label for="ews-admin-year-select">Pilih Tahun:</label>
            <select id="ews-admin-year-select" class="year-filter-select" onchange="const cat = window._activeCategoryFilter || ''; window.location.href = this.value + (cat ? ('&category=' + encodeURIComponent(cat)) : '');">
                <option value="<?= site_url('ews_admin?year=all') ?>" <?= empty($selected_year) ? 'selected' : '' ?>>Semua Tahun (Terkini)</option>
                <?php if (!empty($available_years)): ?>
                    <?php foreach ($available_years as $ay): ?>
                        <option value="<?= site_url('ews_admin?year=' . $ay['tahun']) ?>" <?= (!empty($selected_year) && $selected_year == $ay['tahun']) ? 'selected' : '' ?>>
                            Tahun <?= $ay['tahun'] ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <?php
    // Urutan abjad sektor standar BPS (A s.d. U, plus Tanpa Migas)
    $sectorOrder = array('A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6, 'G' => 7, 'H' => 8, 'I' => 9, 'J' => 10, 'K' => 11, 'L' => 12, 'MN' => 13, 'O' => 14, 'P' => 15, 'Q' => 16, 'RSTU' => 17, 'TM' => 18);

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

    $grouped = [];
    $subGrouped = [];

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

    foreach ($ews as $item) {
        $code = trim($item['code']);
        $mainCode = explode('.', $code)[0];
        $prefix = strtoupper(explode('-', $mainCode)[0]);

        if (in_array($code, $excludedCodes) || in_array($mainCode, $excludedCodes) || in_array($prefix, ['KK1'])) {
            continue;
        }

        $isSub = (strpos($code, '.') !== false || $code === 'FIS-004');
        $parentCode = $isSub ? ($code === 'FIS-004' ? 'FIS-003' : explode('.', $code)[0]) : $code;
        $subKey = $isSub ? ($code === 'FIS-004' ? 'MOD' : explode('.', $code)[1]) : null;

        if ($isSub && in_array($parentCode, $sectoralParents)) {
            $item['sub_key'] = $subKey;
            $item['parent_code'] = $parentCode;
            $subGrouped[$parentCode][$code] = $item;
        } elseif (!$isSub) {
            $item['sub_indicators'] = [];
            $grouped[$code] = $item;
        }
    }

    $quarterOrder = array('Q1' => 1, 'Q2' => 2, 'Q3' => 3, 'Q4' => 4);

    $monthOrder = array('JAN' => 1, 'FEB' => 2, 'MAR' => 3, 'APR' => 4, 'MEI' => 5, 'JUN' => 6, 'JUL' => 7, 'AGS' => 8, 'SEP' => 9, 'OKT' => 10, 'NOV' => 11, 'DES' => 12);

    // Sambungkan sub-indikator HANYA ke parent yang ada di $sectoralParents
    foreach ($subGrouped as $pCode => $subs) {
        if (!in_array($pCode, $sectoralParents) || !isset($grouped[$pCode])) {
            continue;
        }
        $isInv = (strpos($pCode, 'INV-') === 0);
        uasort($subs, function($a, $b) use ($sectorOrder, $quarterOrder, $monthOrder, $isInv, $pCode) {
            $kA = $a['sub_key'] ?? '';
            $kB = $b['sub_key'] ?? '';
            if ($isInv) {
                $posA = $quarterOrder[$kA] ?? 99;
                $posB = $quarterOrder[$kB] ?? 99;
            } elseif (in_array($pCode, ['PAR-001', 'PAR-002', 'HRG-001', 'HRG-003', 'HRG-004'])) {
                $posA = $monthOrder[$kA] ?? 99;
                $posB = $monthOrder[$kB] ?? 99;
            } elseif (in_array($pCode, ['HRG-002', 'HRG-005', 'HRG-006'])) {
                $posA = is_numeric($kA) ? (int)$kA : 99;
                $posB = is_numeric($kB) ? (int)$kB : 99;
            } else {
                $posA = $sectorOrder[$kA] ?? 99;
                $posB = $sectorOrder[$kB] ?? 99;
            }
            if ($posA === $posB) return strcmp($kA, $kB);
            return $posA - $posB;
        });

        $grouped[$pCode]['sub_indicators'] = $subs;
    }

    // Urutkan master indikator mulai dari EKO, NAK, TK, dst
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

        if ($rankA !== $rankB) return $rankA - $rankB;
        if ($numA !== $numB) return $numA - $numB;
        return strcmp($codeA, $codeB);
    });

    // Hitung jumlah indikator per kategori prefix dari $grouped
    $catPrefixCounts = [];
    foreach ($grouped as $gCode => $gItem) {
        $p = strtoupper(explode('-', $gCode)[0]);
        $catPrefixCounts[$p] = ($catPrefixCounts[$p] ?? 0) + 1;
    }

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
                <?php if (isset($catPrefixCounts[$pref]) || empty($ews)): ?>
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

    <!-- KONFIGURASI AMBANG BATAS & DATA TERVALIDASI -->
    <article class="card threshold-admin">
        <div class="card-head">
            <div>
                <span class="eyebrow">EARLY WARNING CONFIGURATION • <?= !empty($selected_year) ? "TAHUN {$selected_year}" : "SEMUA PERIODE" ?></span>
                <h3>Konfigurasi Ambang Batas Early Warning System</h3>
                <p>Data indikator &amp; nilai riil bersumber langsung dari data tervalidasi. Anda hanya perlu menetapkan <strong>Ambang Batas</strong> dan <strong>Arah Kondisi Kritis</strong>.</p>
            </div>
            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <span id="cat-filtered-count-label" class="badge info" style="font-size:11px; padding:6px 14px; font-weight:700; border-radius:20px;">
                    Total: <?= count($grouped) ?> Indikator Tervalidasi
                </span>
                <button class="button primary" id="btn-save-all-thresholds">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Simpan Semua Ambang Batas
                </button>
            </div>
        </div>

        <div style="padding: 12px 18px; background: #f0f7ff; border-left: 4px solid var(--blue); border-radius: 6px; margin: 12px 0 20px 0; font-size: 11px; line-height: 1.6; color: var(--navy);">
            <strong>Panduan Konfigurasi Ambang Batas EWS:</strong>
            <ul style="margin: 4px 0 0 16px; padding: 0;">
                <li><strong>Standar Kamus Data:</strong> Kode, Nama Indikator, Klaster, Satuan, dan Definisi Operasional adalah <em>Data Kamus Umum</em> yang seragam untuk semua wilayah.</li>
                <li><strong>Nilai Riil Tervalidasi</strong> bersifat <em>read-only</em> dan langsung merujuk pada capaian riil daerah yang telah diverifikasi pada menu Validasi Data.</li>
                <li><strong>Ambang Batas (Threshold) &amp; Arah Kondisi Kritis</strong> diisi dan disesuaikan secara manual per daerah sesuai target RPJMD atau standar toleransi risiko makro daerah.</li>
                <li><strong>Sub-Indikator Sektoral &amp; Kuartal:</strong> Baris indikator yang memiliki sub-rincian (17 Sektor PDRB atau Kuartal Investasi Q1–Q4) dapat diklik langsung untuk membuka/menutup rincian sub-indikator.</li>
            </ul>
        </div>

        <div class="result-table-wrap">
            <table class="result-table" style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                <thead>
                    <tr style="display: table-row;">
                        <th style="width: 32%; text-align: left; vertical-align: middle; padding: 12px 14px;">Indikator</th>
                        <th style="width: 16%; text-align: left; vertical-align: middle; padding: 12px 14px;">Nilai Riil Tervalidasi</th>
                        <th style="width: 18%; text-align: left; vertical-align: middle; padding: 12px 14px;">Ambang Batas (Editable)</th>
                        <th style="width: 22%; text-align: left; vertical-align: middle; padding: 12px 14px;">Arah Kondisi Kritis</th>
                        <th style="width: 12%; text-align: center; vertical-align: middle; padding: 12px 14px;">Status Risiko</th>
                    </tr>
                </thead>
                <tbody id="threshold-admin-rows">
                    <?php if (empty($grouped)): ?>
                        <tr style="display: table-row;">
                            <td colspan="5" style="text-align: center; padding: 36px 16px; color: var(--muted); vertical-align: middle;">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:block; margin:0 auto 8px; opacity:0.5;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                Tidak ada indikator yang berstatus <strong>Tervalidasi</strong> <?= !empty($selected_year) ? "untuk tahun {$selected_year}" : "" ?>.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($grouped as $code => $item): ?>
                            <?php
                            $hasSub = (!empty($item['sub_indicators']) && in_array($item['code'], $sectoralParents));
                            $badgeCls = ($item['status'] === 'Kritis') ? 'badge danger' : (($item['status'] === 'Waspada') ? 'badge warning' : 'badge success');
                            $itemPrefix = strtoupper(explode('-', $item['code'])[0]);
                            $isMatchCat = ($itemPrefix === $activeCat);
                            ?>
                            <tr class="threshold-admin-row matrix-row <?= $hasSub ? 'has-subcategories' : '' ?>" data-code="<?= $item['code'] ?>" data-prefix="<?= $itemPrefix ?>" <?= $hasSub ? 'data-has-sub="true" title="Klik baris ini untuk membuka/menutup rincian sub-indikator" onclick="if(!event.target.closest(\'input, select, a, button:not(.btn-toggle-subcategories)\')) toggleIndicatorSubRows(\''.htmlspecialchars($item['code']).'\', event);"' : '' ?> style="display: <?= $isMatchCat ? 'table-row' : 'none' ?>;">
                                <td class="<?= $hasSub ? 'clickable-indicator-cell' : '' ?>" style="vertical-align: middle; padding: 12px 14px; cursor: <?= $hasSub ? 'pointer' : 'default' ?>;" <?= $hasSub ? 'onclick="toggleIndicatorSubRows(\''.htmlspecialchars($item['code']).'\', event);"' : '' ?>>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <code style="font-size:11px; font-weight:700; color:var(--blue); background:#eef4fa; padding:2px 6px; border-radius:4px; white-space:nowrap;"><?= htmlspecialchars($item['code']) ?></code>
                                        <strong class="<?= $hasSub ? 'indicator-title-clickable' : '' ?>" style="color: var(--navy); font-size: 13px;"><?= htmlspecialchars($item['name']) ?></strong>
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
                                        <div style="margin-top:5px;">
                                            <button type="button" class="btn-toggle-subcategories" data-parent="<?= $item['code'] ?>" data-label-open="<?= $openLabel ?>" onclick="toggleIndicatorSubRows('<?= htmlspecialchars($item['code']) ?>', event);" style="display:inline-flex; align-items:center; gap:5px; padding:3px 9px; font-size:11px; font-weight:700; background:#f0fdf4; color:#15803d; border:1px solid #86efac; border-radius:6px; cursor:pointer; transition:all 0.15s ease; white-space:nowrap;" title="Klik untuk membuka/menutup <?= $openLabel ?>">
                                                <svg class="sub-arrow-icon" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="transition:transform 0.2s ease;"><polyline points="6 9 12 15 18 9"/></svg>
                                                <span class="sub-toggle-label"><?= $openLabel ?></span>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($item['klaster']) && $item['klaster'] !== 'Umum'): ?>
                                        <div style="margin-top:4px;">
                                            <span class="badge secondary" style="font-size: 12px; padding:2px 6px;"><?= htmlspecialchars($item['klaster']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="vertical-align: middle; padding: 12px 14px;">
                                    <?php
                                        $isRupiah = is_indicator_rupiah($item['unit'] ?? '', $item['code'] ?? '');
                                        $prefixRp = $isRupiah ? 'Rp ' : '';
                                        $formattedVal = format_indicator_value($item['value'], $item['unit'] ?? '', $item['code'] ?? '', false);
                                        $formattedTh = format_indicator_value($item['threshold'], $item['unit'] ?? '', $item['code'] ?? '', false);
                                    ?>
                                    <div style="display: inline-flex; align-items: center; gap: 6px;">
                                        <strong style="font-size: 13px; color: var(--navy); letter-spacing: -0.2px;">
                                            <?= $prefixRp ?><?= $formattedVal ?>
                                        </strong>
                                        <span style="color: var(--muted); font-size: 11px; font-weight: 600;">
                                            <?= htmlspecialchars($item['unit']) ?>
                                        </span>
                                    </div>
                                    <input type="hidden" class="value-input" data-code="<?= $item['code'] ?>" value="<?= $item['value'] ?>">
                                </td>
                                <td style="vertical-align: middle; padding: 12px 14px;">
                                    <div style="display: flex; align-items: center; gap: 4px;">
                                        <?php if ($isRupiah): ?>
                                            <span style="font-size:11.5px; font-weight:700; color:#475569;">Rp</span>
                                        <?php endif; ?>
                                        <input type="text" inputmode="decimal" class="threshold-input <?= $isRupiah ? 'is-rupiah-mask' : '' ?>" data-code="<?= $item['code'] ?>" value="<?= $formattedTh ?>" style="width: 120px; height: 36px; border: 1px solid var(--line); border-radius: 6px; padding: 0 10px; font-weight: 700; color: var(--navy); font-size: 12px; background: #ffffff; text-align: right;">
                                        <small style="color: var(--muted); font-size: 12.5px; font-weight: 600;"><?= htmlspecialchars($item['unit']) ?></small>
                                    </div>
                                </td>
                                <td style="vertical-align: middle; padding: 12px 14px;">
                                    <select class="direction-input" data-code="<?= $item['code'] ?>" style="width: 100%; max-width: 250px; height: 36px; border: 1px solid var(--line); border-radius: 6px; padding: 0 8px; font-size: 11px; color: var(--navy); background: #ffffff;">
                                        <option value="above" <?= ($item['direction'] === 'above') ? 'selected' : '' ?>>Kritis jika &gt; Ambang (Beban / Risiko)</option>
                                        <option value="below" <?= ($item['direction'] === 'below') ? 'selected' : '' ?>>Kritis jika &lt; Ambang (Target / Kinerja)</option>
                                    </select>
                                </td>
                                <td style="text-align: center; vertical-align: middle; padding: 12px 14px;">
                                    <span id="status-badge-<?= $item['code'] ?>" class="<?= $badgeCls ?>" style="font-size: 12.5px; font-weight: 700; padding: 5px 12px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block; min-width: 75px;">
                                        <?= $item['status'] ?>
                                    </span>
                                </td>
                            </tr>

                            <!-- SUB-ROWS UNTUK RINCIAN SEKTORAL / KOMPONEN -->
                            <?php if (!empty($item['sub_indicators'])): ?>
                                <?php foreach ($item['sub_indicators'] as $subCode => $sub): ?>
                                    <?php
                                        $subBadgeCls = ($sub['status'] === 'Kritis') ? 'badge danger' : (($sub['status'] === 'Waspada') ? 'badge warning' : 'badge success');
                                        $dispKey = $sub['sub_key'];
                                        $isSubInv = (strpos($item['code'], 'INV-') === 0);
                                        if ($dispKey === 'MN') $dispKey = 'M,N';
                                        elseif ($dispKey === 'RSTU') $dispKey = 'R,S,T,U';
                                        if ($isSubInv) {
                                            $cleanSubName = 'Kuartal ' . substr($dispKey, 1) . ' (' . $dispKey . ')';
                                        } elseif ($item['code'] === 'HRG-005') {
                                            $cleanSubName = '- ' . preg_replace('/^Harga:\s*/i', '', $sub['name']);
                                        } else {
                                            $cleanSubName = preg_replace('/^(PDRB\s+(ADHB|ADHK)|Pertumbuhan(\s+PDRB(\s+ADHK)?)?|Distribusi(\s+PDRB(\s+ADHB)?)?|Kontribusi|PDRB\s+Pengeluaran\s+(ADHB|ADHK)|Pengeluaran\s+(ADHB|ADHK)):\s*/i', '', $sub['name']);
                                        }
                                        $hrgMeta = ($item['code'] === 'HRG-005') ? get_hrg005_excel_row_meta($dispKey) : array();
                                    ?>
                                    <?php if (!empty($hrgMeta['group_header'])): ?>
                                        <?php $gH = $hrgMeta['group_header']; ?>
                                        <tr class="threshold-admin-row matrix-row matrix-sub-row sub-row-HRG-005" data-parent="HRG-005" data-prefix="HRG" style="display:none; background:#e2e8f0; border-top:2px solid #cbd5e1; border-bottom:1px solid #cbd5e1;">
                                            <td colspan="5" style="padding:7px 14px 7px 24px; font-weight:800; font-size:12.5px; color:#0f172a; text-transform:uppercase;">
                                                <span style="display:inline-flex; align-items:center; gap:8px;">
                                                    <span style="font-weight:800; font-size:11.5px; color:#0369a1; background:#e0f2fe; border:1px solid #bae6fd; padding:2px 8px; border-radius:5px;"><?= $gH['no'] ?></span>
                                                    <span><?= htmlspecialchars($gH['name']) ?></span>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($hrgMeta['sub_header'])): ?>
                                        <tr class="threshold-admin-row matrix-row matrix-sub-row sub-row-HRG-005" data-parent="HRG-005" data-prefix="HRG" style="display:none; background:#f1f5f9; border-bottom:1px solid #e2e8f0;">
                                            <td colspan="5" style="padding:5px 14px 5px 42px; font-weight:700; font-style:italic; font-size:12px; color:#334155;">
                                                <?= htmlspecialchars($hrgMeta['sub_header']) ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr class="threshold-admin-row matrix-row matrix-sub-row sub-row-<?= $item['code'] ?>" data-code="<?= $sub['code'] ?>" data-parent="<?= $item['code'] ?>" data-prefix="<?= strtoupper(explode('-', $item['code'])[0]) ?>" style="display:none; background:#f8fafc;">
                                        <td style="vertical-align: middle; padding: 9px 14px 9px 24px;">
                                            <div style="display:flex; align-items:center; gap:6px;">
                                                <span style="color:#94a3b8; font-weight:700; font-size:11px;">└──</span>
                                                <?php if (!empty($hrgMeta['direct_no'])): ?>
                                                    <span style="font-weight:800; font-size:11.5px; color:#0369a1; background:#e0f2fe; border:1px solid #bae6fd; padding:1px 7px; border-radius:4px;"><?= $hrgMeta['direct_no'] ?></span>
                                                <?php endif; ?>
                                                <code style="font-size: 12.5px; font-weight:700; color:#0369a1; background:#e0f2fe; padding:2px 6px; border-radius:4px;"><?= htmlspecialchars($sub['code']) ?></code>
                                                <span style="font-size: 12px; font-weight:800; color:#475569; background:#e2e8f0; padding:1px 5px; border-radius:4px;"><?= htmlspecialchars($dispKey) ?></span>
                                                <span style="color:#1e293b; font-size:12px; font-weight:600;"><?= htmlspecialchars($cleanSubName) ?></span>
                                            </div>
                                        </td>
                                        <td style="vertical-align: middle; padding: 9px 14px;">
                                            <?php
                                                $subIsRupiah = is_indicator_rupiah($sub['unit'] ?? '', $sub['code'] ?? '');
                                                $subPrefixRp = $subIsRupiah ? 'Rp ' : '';
                                                $subFormattedVal = format_indicator_value($sub['value'], $sub['unit'] ?? '', $sub['code'] ?? '', false);
                                                $subFormattedTh = format_indicator_value($sub['threshold'], $sub['unit'] ?? '', $sub['code'] ?? '', false);
                                            ?>
                                            <div style="display: inline-flex; align-items: center; gap: 6px;">
                                                <strong style="font-size: 12px; color: var(--navy); letter-spacing: -0.2px;">
                                                    <?= $subPrefixRp ?><?= $subFormattedVal ?>
                                                </strong>
                                                <span style="color: var(--muted); font-size: 12.5px; font-weight: 600;">
                                                    <?= htmlspecialchars($sub['unit']) ?>
                                                </span>
                                            </div>
                                            <input type="hidden" class="value-input" data-code="<?= $sub['code'] ?>" value="<?= $sub['value'] ?>">
                                        </td>
                                        <td style="vertical-align: middle; padding: 9px 14px;">
                                            <div style="display: flex; align-items: center; gap: 4px;">
                                                <?php if ($subIsRupiah): ?>
                                                    <span style="font-size: 12.5px; font-weight:700; color:#475569;">Rp</span>
                                                <?php endif; ?>
                                                <input type="text" inputmode="decimal" class="threshold-input <?= $subIsRupiah ? 'is-rupiah-mask' : '' ?>" data-code="<?= $sub['code'] ?>" value="<?= $subFormattedTh ?>" style="width: 120px; height: 32px; border: 1px solid var(--line); border-radius: 6px; padding: 0 8px; font-weight: 700; color: var(--navy); font-size: 11.5px; background: #ffffff; text-align: right;">
                                                <small style="color: var(--muted); font-size: 12.5px; font-weight: 600;"><?= htmlspecialchars($sub['unit']) ?></small>
                                            </div>
                                        </td>
                                        <td style="vertical-align: middle; padding: 9px 14px;">
                                            <select class="direction-input" data-code="<?= $sub['code'] ?>" style="width: 100%; max-width: 250px; height: 32px; border: 1px solid var(--line); border-radius: 6px; padding: 0 6px; font-size: 12.5px; color: var(--navy); background: #ffffff;">
                                                <option value="above" <?= ($sub['direction'] === 'above') ? 'selected' : '' ?>>Kritis jika &gt; Ambang (Beban / Risiko)</option>
                                                <option value="below" <?= ($sub['direction'] === 'below') ? 'selected' : '' ?>>Kritis jika &lt; Ambang (Target / Kinerja)</option>
                                            </select>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle; padding: 9px 14px;">
                                            <span id="status-badge-<?= $sub['code'] ?>" class="<?= $subBadgeCls ?>" style="font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 5px; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block; min-width: 70px;">
                                                <?= $sub['status'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div id="ews-admin-feedback" class="method-note" style="display:none; margin-top: 15px;"></div>
    </article>
</div>
