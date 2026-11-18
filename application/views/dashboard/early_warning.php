<?php
// Grouping logic untuk Indikator Utama dan Sub-Indikator Sektoral (A s.d. U)
$sectorOrder = array('A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6, 'G' => 7, 'H' => 8, 'I' => 9, 'J' => 10, 'K' => 11, 'L' => 12, 'MN' => 13, 'O' => 14, 'P' => 15, 'Q' => 16, 'RSTU' => 17, 'TM' => 18);
$sectoralParents = array('EKO-003', 'EKO-004', 'EKO-006', 'EKO-007');
$indicatorPrefixOrder = array(
    'EKO' => 1, 'NAK' => 2, 'TK' => 3, 'INV' => 4,
    'HRG' => 5, 'KES' => 6, 'PAR' => 7, 'PRO' => 8,
    'FIS' => 9, 'GEO' => 10, 'EWS' => 11, 'ANA' => 12
);

$grouped = array();
$subGrouped = array();

foreach ($ews as $item) {
    $code = $item['code'] ?? $item['kode_indikator'] ?? '';
    if (empty($code)) continue;

    $isSub = (strpos($code, '.') !== false);
    $parentCode = $isSub ? explode('.', $code)[0] : $code;
    $subKey = $isSub ? explode('.', $code)[1] : null;

    if ($isSub && in_array($parentCode, $sectoralParents)) {
        $item['sub_key'] = $subKey;
        $item['parent_code'] = $parentCode;
        $subGrouped[$parentCode][$code] = $item;
    } elseif (!$isSub) {
        $item['sub_indicators'] = array();
        $grouped[$code] = $item;
    }
}

// Sambungkan sub-indikator HANYA ke parent yang ada di $sectoralParents
foreach ($subGrouped as $pCode => $subs) {
    if (!in_array($pCode, $sectoralParents) || !isset($grouped[$pCode])) {
        continue;
    }
    uasort($subs, function($a, $b) use ($sectorOrder) {
        $kA = $a['sub_key'] ?? '';
        $kB = $b['sub_key'] ?? '';
        $posA = $sectorOrder[$kA] ?? 99;
        $posB = $sectorOrder[$kB] ?? 99;
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
$catPrefixCounts = array();
foreach ($grouped as $gCode => $gItem) {
    $p = strtoupper(explode('-', $gCode)[0]);
    $catPrefixCounts[$p] = ($catPrefixCounts[$p] ?? 0) + 1;
}

$categoryMeta = array(
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
);

$activeCat = !empty($selected_category) ? strtoupper(trim($selected_category)) : '';
?>
<div class="page">
    <?php if ($this->session->flashdata('success')): ?>
        <div class="method-notice forecast" style="margin-bottom:18px; border-left:4px solid var(--teal); background:rgba(13,148,136,0.12);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <div>
                <strong>Berhasil</strong>
                <p><?= htmlspecialchars($this->session->flashdata('success')) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="method-notice forecast" style="margin-bottom:18px; border-left:4px solid var(--red); background:rgba(225,29,72,0.12);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            <div>
                <strong>Perhatian</strong>
                <p><?= htmlspecialchars($this->session->flashdata('error')) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <div class="module-intro">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions">
            <button class="button secondary" id="btn-export-ews">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12M7 10l5 5 5-5M4 21h16"/></svg>
                <span>Ekspor CSV</span>
            </button>
        </div>
    </div>

    <!-- FILTER BAR EARLY WARNING SYSTEM (PERIODE TAHUN) -->
    <div class="year-filter-bar" style="margin-top: 15px; margin-bottom: 14px;">
        <div class="year-filter-info">
            <div class="year-filter-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div class="year-filter-text">
                <strong>Filter & Periode Indikator EWS</strong>
                <small>Pantau status risiko, ambang batas, dan realisasi indikator peringatan dini berdasarkan tahun dan klaster tematik.</small>
            </div>
        </div>

        <div class="year-filter-control" style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <label for="ews-year-select">Pilih Tahun:</label>
                <select id="ews-year-select" class="year-filter-select" style="min-width: 170px;" onchange="window.location.href=this.value">
                    <option value="<?= site_url('early_warning?year=all' . ($status ? '&status='.$status : '') . (!empty($activeCat) ? '&category='.urlencode($activeCat) : '')) ?>" <?= empty($selected_year) ? 'selected' : '' ?>>Semua Tahun (Terkini)</option>
                    <?php if (!empty($available_years)): ?>
                        <?php foreach ($available_years as $ay): ?>
                            <option value="<?= site_url('early_warning?year=' . $ay['tahun'] . ($status ? '&status='.$status : '') . (!empty($activeCat) ? '&category='.urlencode($activeCat) : '')) ?>" <?= (!empty($selected_year) && $selected_year == $ay['tahun']) ? 'selected' : '' ?>>
                                Tahun <?= $ay['tahun'] ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- SLIDE FILTER KATEGORI (EKO, NAK, TK, INV, HRG, KES, PAR, PRO, FIS, GEO, EWS, ANA) -->
    <div class="category-slide-container" id="category-slide-container" style="margin-bottom: 20px;">
        <div class="category-slide-label">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
            <span>Kategori:</span>
        </div>
        <button type="button" class="category-slide-nav-btn" id="btn-slide-cat-prev" title="Geser Kiri (Sebelumnya)" aria-label="Geser Kiri">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <div class="category-slide-track" id="category-slide-track">
            <button type="button" class="category-slide-tab <?= empty($activeCat) ? 'active' : '' ?>" data-prefix="" onclick="applyCategoryFilter('', this)">
                <span>Semua</span>
                <span class="cat-count-pill"><?= count($grouped) ?></span>
            </button>
            <?php foreach ($categoryMeta as $pref => $label): ?>
                <?php if (isset($catPrefixCounts[$pref]) || empty($grouped)): ?>
                    <?php $cnt = $catPrefixCounts[$pref] ?? 0; ?>
                    <button type="button" class="category-slide-tab <?= ($activeCat === $pref) ? 'active' : '' ?>" data-prefix="<?= $pref ?>" onclick="applyCategoryFilter('<?= $pref ?>', this)">
                        <span class="cat-code-badge"><?= $pref ?></span>
                        <span><?= $label ?></span>
                        <span class="cat-count-pill"><?= $cnt ?></span>
                    </button>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <button type="button" class="category-slide-nav-btn" id="btn-slide-cat-next" title="Geser Kanan (Selanjutnya)" aria-label="Geser Kanan">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
    </div>

    <!-- STATS OVERVIEW -->
    <div class="risk-overview" id="ews-stats-container">
        <?php
        $kritisCnt = 0; $waspadaCnt = 0; $normalCnt = 0;
        foreach ($grouped as $item) {
            $st = $item['status'] ?? $item['status_risiko'] ?? 'Normal';
            if ($st === 'Kritis') $kritisCnt++;
            elseif ($st === 'Waspada') $waspadaCnt++;
            else $normalCnt++;
        }
        $totEws = count($grouped);
        ?>
        <div class="risk-stat danger">
            <span>Status Kritis</span>
            <strong id="stat-kritis-count"><?= $kritisCnt ?></strong>
            <small>Memerlukan intervensi segera</small>
        </div>
        <div class="risk-stat warning">
            <span>Status Waspada</span>
            <strong id="stat-waspada-count"><?= $waspadaCnt ?></strong>
            <small>Mendekati ambang batas</small>
        </div>
        <div class="risk-stat success">
            <span>Status Normal</span>
            <strong id="stat-normal-count"><?= $normalCnt ?></strong>
            <small>Dalam batas aman</small>
        </div>
        <div class="risk-stat neutral">
            <span>Total Indikator</span>
            <strong id="stat-total-count"><?= $totEws ?></strong>
            <small>Dipantau sistem</small>
        </div>
    </div>

    <!-- CONTENT GRID -->
    <section class="two-col risk-content">
        <!-- MONITORING TABLE -->
        <article class="card large">
            <div class="card-head">
                <div>
                    <span class="eyebrow">RISK MONITORING • <?= !empty($selected_year) ? "TAHUN {$selected_year}" : "SEMUA PERIODE" ?></span>
                    <h3>Indikator dalam Pemantauan Dini <?= !empty($selected_year) ? "(Tahun {$selected_year})" : "(Semua Periode)" ?></h3>
                    <p>Status dihitung otomatis dari nilai riil tervalidasi <?= !empty($selected_year) ? "tahun {$selected_year}" : "periode terkini" ?> dan batas ambang (threshold). Anda dapat menentukan sendiri nilai threshold dan arah kondisi risiko dengan mengklik baris indikator di bawah.</p>
                </div>
            </div>

            <div class="segmented ews-filters" id="ews-filter-tabs">
                <button class="active" data-status="Semua">Semua</button>
                <button data-status="Kritis">Kritis</button>
                <button data-status="Waspada">Waspada</button>
                <button data-status="Normal">Normal</button>
            </div>

            <div class="risk-table" id="ews-table-rows">
                <div class="risk-table-header">
                    <span>Indikator Pemantauan</span>
                    <span>Nilai Riil</span>
                    <span>Batas Ambang</span>
                    <span style="text-align:right;">Status Risiko</span>
                </div>
                <?php foreach ($grouped as $code => $item):
                    $hasSub = (!empty($item['sub_indicators']) && in_array($code, $sectoralParents));
                    $st = $item['status'] ?? $item['status_risiko'] ?? 'Normal';
                    $badgeCls = ($st === 'Kritis') ? 'danger' : (($st === 'Waspada') ? 'warning' : 'success');
                    $rowStateCls = ($st === 'Kritis') ? 'alerted' : (($st === 'Waspada') ? 'warning-row' : 'normal-row');
                    $name = $item['name'] ?? $item['nama_indikator'] ?? '';
                    $val = (float)($item['value'] ?? $item['nilai_terkini'] ?? $item['currentValue'] ?? 0);
                    $th = (float)($item['threshold'] ?? $item['ambang_batas'] ?? 0);
                    $unit = $item['unit'] ?? $item['satuan'] ?? '%';
                    $dir = strtolower($item['direction'] ?? $item['arah_risiko'] ?? 'above');
                    $rowYr = $item['year'] ?? $item['tahun'] ?? null;
                    $cl = $item['klaster'] ?? 'Umum';
                    $pPrefix = strtoupper(explode('-', $code)[0]);
                ?>
                    <div class="risk-row btn-alert-row <?= $rowStateCls ?> <?= $hasSub ? 'has-subcategories' : '' ?>" 
                         data-code="<?= htmlspecialchars($code) ?>" 
                         data-prefix="<?= $pPrefix ?>" 
                         data-status="<?= htmlspecialchars($st) ?>" 
                         data-category="<?= htmlspecialchars($cl) ?>" 
                         <?= $hasSub ? 'data-has-sub="true"' : '' ?>
                         role="button" tabindex="0" 
                         title="<?= $hasSub ? 'Klik baris ini untuk membuka/menutup rincian sub-indikator atau sesuaikan threshold' : 'Klik untuk analisis & penyesuaian threshold' ?>">
                        <div class="risk-indicator">
                            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap; margin-bottom:4px;">
                                <span class="badge info" style="display:inline-block; font-size:9.5px; padding:2px 8px; font-weight:600; text-transform:none;">
                                    <?= htmlspecialchars($cl) ?>
                                </span>
                                <?php if ($hasSub): ?>
                                    <button type="button" class="btn-toggle-subcategories" data-parent="<?= htmlspecialchars($code) ?>" data-label-open="Rincian Sektor (A–U)" onclick="toggleIndicatorSubRows('<?= htmlspecialchars($code) ?>', event);" style="display:inline-flex; align-items:center; gap:5px; padding:3px 8px; font-size:11px; font-weight:700; background:#f0fdf4; color:#15803d; border:1.2px solid #86efac; border-radius:6px; cursor:pointer; transition:all 0.15s ease;" title="Klik untuk membuka/menutup rincian sektor">
                                        <svg class="sub-arrow-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="transition:transform 0.2s ease;"><polyline points="6 9 12 15 18 9"/></svg>
                                        <span class="sub-toggle-label">Rincian Sektor (A–U)</span>
                                        <span style="background:#dcfce7; color:#166534; font-size:9.5px; padding:1px 6px; border-radius:10px; font-weight:800;">18 Sektor</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <strong style="font-size:13px; color:var(--navy);"><?= htmlspecialchars($name) ?></strong>
                            <small>
                                <code style="white-space: nowrap;"><?= htmlspecialchars($code) ?></code>
                                <?php if (!empty($rowYr)): ?>
                                    <span style="color:var(--navy); font-weight:600;">• Thn <?= $rowYr ?></span>
                                <?php endif; ?>
                                <span style="color:var(--muted);">• <?= ($dir === 'above') ? 'Kritis jika > Ambang' : 'Kritis jika < Ambang' ?></span>
                            </small>
                        </div>
                        <div class="risk-value">
                            <span>Nilai Riil</span>
                            <strong><?= number_format($val, 2, ',', '.') ?> <span style="font-size:11px; font-weight:500; color:var(--muted);"><?= htmlspecialchars($unit) ?></span></strong>
                        </div>
                        <div class="risk-threshold">
                            <span>Batas Ambang</span>
                            <strong><?= number_format($th, 2, ',', '.') ?> <span style="font-size:11px; font-weight:500; color:var(--muted);"><?= htmlspecialchars($unit) ?></span></strong>
                        </div>
                        <div class="risk-status">
                            <span class="badge <?= $badgeCls ?>"><?= htmlspecialchars($st) ?></span>
                        </div>
                    </div>

                    <!-- SUB-ROWS UNTUK RINCIAN SEKTORAL (A s.d. U) -->
                    <?php if ($hasSub): ?>
                        <?php foreach ($item['sub_indicators'] as $subCode => $sub):
                            $subSt = $sub['status'] ?? $sub['status_risiko'] ?? 'Normal';
                            $subBadgeCls = ($subSt === 'Kritis') ? 'danger' : (($subSt === 'Waspada') ? 'warning' : 'success');
                            $subRowStateCls = ($subSt === 'Kritis') ? 'alerted' : (($subSt === 'Waspada') ? 'warning-row' : 'normal-row');
                            $subVal = (float)($sub['value'] ?? $sub['nilai_terkini'] ?? 0);
                            $subTh = (float)($sub['threshold'] ?? $sub['ambang_batas'] ?? 0);
                            $subUnit = $sub['unit'] ?? $sub['satuan'] ?? '%';
                            $dispKey = $sub['sub_key'] ?? '';
                            if ($dispKey === 'MN') $dispKey = 'M,N';
                            elseif ($dispKey === 'RSTU') $dispKey = 'R,S,T,U';
                            $cleanSubName = preg_replace('/^(PDRB\s+(ADHB|ADHK)|Pertumbuhan(\s+PDRB(\s+ADHK)?)?|Distribusi(\s+PDRB(\s+ADHB)?)?|Kontribusi|PDRB\s+Pengeluaran\s+(ADHB|ADHK)|Pengeluaran\s+(ADHB|ADHK)):\s*/i', '', $sub['name'] ?? '');
                        ?>
                            <div class="risk-row btn-alert-row risk-sub-row sub-row-<?= htmlspecialchars($code) ?> <?= $subRowStateCls ?>" 
                                 data-code="<?= htmlspecialchars($subCode) ?>" 
                                 data-parent="<?= htmlspecialchars($code) ?>" 
                                 data-prefix="<?= $pPrefix ?>" 
                                 data-status="<?= htmlspecialchars($subSt) ?>" 
                                 data-category="<?= htmlspecialchars($cl) ?>" 
                                 style="display:none; background:#f8fafc; border-left: 5px solid <?= ($subSt === 'Kritis') ? '#ef4444' : (($subSt === 'Waspada') ? '#f59e0b' : '#10b981') ?>;" 
                                 role="button" tabindex="0" title="Klik untuk analisis & penyesuaian threshold sub-indikator <?= htmlspecialchars($subCode) ?>">
                                <div class="risk-indicator" style="padding-left:14px;">
                                    <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap; margin-bottom:2px;">
                                        <span style="color:#94a3b8; font-weight:700; font-size:11px;">└──</span>
                                        <code style="font-size:10.5px; font-weight:700; color:#0369a1; background:#e0f2fe; padding:2px 6px; border-radius:4px;"><?= htmlspecialchars($subCode) ?></code>
                                        <span style="font-size:9.5px; font-weight:800; color:#475569; background:#e2e8f0; padding:1px 5px; border-radius:4px;"><?= htmlspecialchars($dispKey) ?></span>
                                    </div>
                                    <strong style="font-size:12px; color:#1e293b;"><?= htmlspecialchars($cleanSubName) ?></strong>
                                    <small style="color:var(--muted);">Sektor <?= htmlspecialchars($dispKey) ?> • <?= ($sub['direction'] ?? 'above') === 'above' ? 'Kritis jika > Ambang' : 'Kritis jika < Ambang' ?></small>
                                </div>
                                <div class="risk-value">
                                    <span>Nilai Riil</span>
                                    <strong><?= number_format($subVal, 2, ',', '.') ?> <span style="font-size:11px; font-weight:500; color:var(--muted);"><?= htmlspecialchars($subUnit) ?></span></strong>
                                </div>
                                <div class="risk-threshold">
                                    <span>Batas Ambang</span>
                                    <strong><?= number_format($subTh, 2, ',', '.') ?> <span style="font-size:11px; font-weight:500; color:var(--muted);"><?= htmlspecialchars($subUnit) ?></span></strong>
                                </div>
                                <div class="risk-status">
                                    <span class="badge <?= $subBadgeCls ?>"><?= htmlspecialchars($subSt) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
                <div id="ews-empty-message" style="display: none; padding: 36px 20px; text-align: center; color: var(--muted); background: white; border: 1px dashed var(--line); border-radius: 10px; margin-top: 10px;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="margin-bottom: 8px; color: var(--muted);"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <strong style="display: block; font-size: 13px; color: var(--navy); margin-bottom: 3px;">Tidak ada indikator yang cocok</strong>
                    <small style="font-size: 11px;">Silakan ubah filter kategori atau status pemantauan.</small>
                </div>
            </div>

            <div class="source" style="margin-top:14px; font-size:11px; color:var(--muted); padding-top:10px; border-top:1px dashed var(--line); line-height:1.6;">
                <strong>Kriteria Penentuan Status Risiko:</strong><br>
                • <strong>Indikator Beban/Risiko</strong> (Inflasi, Kemiskinan Ekstrem, Pengangguran): Nilai &lt; Ambang = <span class="badge success" style="font-size:9px; padding:2px 6px;">Normal</span>, Nilai &gt; Ambang = <span class="badge danger" style="font-size:9px; padding:2px 6px;">Kritis</span>.<br>
                • <strong>Indikator Kinerja/Target</strong> (Belanja Modal, Pertumbuhan Kredit): Nilai &gt; Ambang = <span class="badge success" style="font-size:9px; padding:2px 6px;">Normal</span>, Nilai &lt; Ambang = <span class="badge danger" style="font-size:9px; padding:2px 6px;">Kritis</span>.<br>
                • Nilai sama persis dengan ambang batas = <span class="badge warning" style="font-size:9px; padding:2px 6px;">Waspada</span>.
            </div>
        </article>

        <!-- RISK MATRIX & PROGRESS -->
        <article class="card">
            <div class="card-head">
                <div>
                    <span class="eyebrow">RISK MATRIX & KOMPARASI</span>
                    <h3>Grafik Komparasi Deviasi Ambang</h3>
                    <p>Perbandingan nilai aktual terhadap batas ambang risiko (threshold).</p>
                </div>
            </div>

            <div id="ews-comparison-chart-container" class="chart-wrap dynamic-chart" style="height:auto; min-height:160px; margin-bottom:14px;">
                <!-- Rendered dynamically by JS -->
            </div>

            <div class="risk-distribution" id="ews-distribution-bars">
                <!-- Injected by JS -->
            </div>

            <div class="followup">
                <strong>Progress Tindak Lanjut Pemantauan</strong>
                <div>
                    <i id="ews-progress-bar" style="width: 20%"></i>
                    <span id="ews-progress-text">1 dari 5 indikator ditandai</span>
                    <b id="ews-progress-pct">20%</b>
                </div>
            </div>
        </article>
    </section>

    <!-- MODAL ALERT DETAIL & THRESHOLD CUSTOMIZATION -->
    <div class="modal-backdrop" id="ews-modal-backdrop" style="display:none;">
        <div class="modal" id="ews-modal-card" style="max-width: 580px;">
            <div class="modal-head">
                <div>
                    <span class="badge danger" id="modal-alert-badge">KRITIS</span>
                    <h2 id="modal-alert-name">Inflasi Pangan</h2>
                    <p id="modal-alert-meta">Panel Harga Pangan • HRG-01</p>
                </div>
                <button class="icon-button" id="modal-btn-close">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <div class="alert-metrics">
                <span>
                    <small>Nilai Riil Terkini</small>
                    <strong id="modal-metric-val">8,40 %</strong>
                </span>
                <span>
                    <small>Threshold Saat Ini</small>
                    <strong id="modal-metric-th">7,00 %</strong>
                </span>
                <span>
                    <small>Deviasi dari Ambang</small>
                    <strong id="modal-metric-dev" class="negative">+20,0%</strong>
                </span>
            </div>

            <!-- VALUE & THRESHOLD CUSTOMIZER -->
            <div class="modal-section threshold-editor" style="background:#f8fafc; border:1px solid var(--line); border-radius:10px; padding:16px; margin:16px 0;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                    <h3 style="margin:0; font-size:13px; font-weight:700; color:var(--navy); display:flex; align-items:center; gap:6px;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        Penyesuaian Nilai Riil & Ambang Batas
                    </h3>
                    <span id="modal-live-preview-badge" class="badge danger" style="font-size:10px;">Status: KRITIS</span>
                </div>
                
                <div style="display:grid; grid-template-columns: 1fr 1fr 1.2fr; gap:10px; margin-bottom:8px;">
                    <div>
                        <label style="display:block; font-size:11px; font-weight:600; color:var(--navy); margin-bottom:4px;">
                            Nilai Riil (<span class="modal-unit-label">%</span>)
                        </label>
                        <input type="number" step="any" id="modal-input-value" value="0.0" style="width:100%; height:36px; border:1px solid var(--line); border-radius:6px; padding:0 10px; font-size:13px; font-weight:600; color:var(--navy); background:#fff;">
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; font-weight:600; color:var(--navy); margin-bottom:4px;">
                            Batas Ambang (<span class="modal-unit-label">%</span>)
                        </label>
                        <input type="number" step="any" id="modal-input-threshold" value="7.0" style="width:100%; height:36px; border:1px solid var(--line); border-radius:6px; padding:0 10px; font-size:13px; font-weight:600; color:var(--navy); background:#fff;">
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; font-weight:600; color:var(--navy); margin-bottom:4px;">
                            Kondisi / Arah Batas Kritis
                        </label>
                        <select id="modal-input-direction" style="width:100%; height:36px; border:1px solid var(--line); border-radius:6px; padding:0 8px; font-size:11px; font-weight:500; color:var(--navy); background:#fff;">
                            <option value="above">Kritis jika Nilai &gt; Ambang (Beban)</option>
                            <option value="below">Kritis jika Nilai &lt; Ambang (Target)</option>
                        </select>
                    </div>
                </div>

                <small style="display:block; color:var(--muted); font-size:10px; line-height:1.4;">
                    Sesuaikan nilai riil atau ambang batas indikator di atas. Status risiko akan disimulasikan secara realtime dan tersimpan ke sistem.
                </small>
            </div>

            <div class="modal-section">
                <h3 style="font-size:12px; font-weight:700; color:var(--navy); margin-bottom:4px;">Interpretasi Otomatis Intelijen</h3>
                <p id="modal-alert-interpretation" style="font-size:12px; line-height:1.5; color:var(--slate);">Nilai telah melampaui batas ambang risiko sebesar 20,0%.</p>
            </div>

            <div class="modal-actions" style="display:flex; justify-content:space-between; align-items:center; margin-top:20px;">
                <button class="button secondary" id="modal-btn-toggle-followup" style="font-size:11px;">Tandai Ditindaklanjuti</button>
                <div style="display:flex; gap:8px;">
                    <button class="button secondary" id="modal-btn-cancel">Batal</button>
                    <button class="button primary" id="modal-btn-save-threshold">Simpan Nilai & Threshold</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.__EWS_DATA__ = <?= json_encode($ews, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>
