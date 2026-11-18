<div class="page admin-page">
    <div class="admin-intro">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions" style="display:flex; gap:10px; align-items:center;">
            <button type="button" class="button secondary" id="btn-open-sectoral-modal" style="font-weight:700; background:#f0fdf4; color:#15803d; border:1.5px solid #86efac; display:inline-flex; align-items:center; gap:6px; cursor:pointer;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>
                <span>Input 17 Sektor PDRB</span>
            </button>
            <button class="button primary" id="btn-open-add-modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14M5 12h14"/></svg>
                <span>Tambah Data</span>
            </button>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success" style="background:#eaf7ee; border:1px solid #95d5a6; color:#1e6a38; padding:12px 16px; border-radius:9px; margin-bottom:16px; display:flex; align-items:center; gap:10px; font-size:9px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
            <span><?= $this->session->flashdata('success') ?></span>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-error" style="background:#fdf2f2; border:1px solid #f8b4b4; color:#9b1c1c; padding:12px 16px; border-radius:9px; margin-bottom:16px; display:flex; align-items:center; gap:10px; font-size:9px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            <span><?= $this->session->flashdata('error') ?></span>
        </div>
    <?php endif; ?>

    <?php if ($status === 'Terhapus'): ?>
        <div class="alert alert-info" style="background:#eff6ff; border:1px solid #bfdbfe; color:#1e40af; padding:12px 16px; border-radius:9px; margin-bottom:16px; display:flex; justify-content:space-between; align-items:center; font-size:9px;">
            <div style="display:flex; align-items:center; gap:8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                <span><strong>Tempat Sampah (Soft Delete):</strong> Menampilkan data yang dihapus sementara. Anda dapat memulihkan (Restore) kembali ke daftar aktif.</span>
            </div>
            <a href="<?= site_url('database') ?>" class="button secondary small" style="height:28px; padding:0 10px; font-size:8px;">Kembali ke Data Aktif</a>
        </div>
    <?php endif; ?>

    <!-- TOOLBAR -->
    <div class="management-toolbar">
        <div class="search wide">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
            <input type="text" id="admin-search-input" value="<?= htmlspecialchars($query ?? '') ?>" placeholder="Cari kode, indikator, klaster, wilayah...">
        </div>
        <select id="admin-filter-year">
            <option value="" <?= empty($selected_year) ? 'selected' : '' ?>>Semua Tahun</option>
            <?php if (!empty($available_years)): ?>
                <?php foreach ($available_years as $ay): ?>
                    <option value="<?= $ay['tahun'] ?>" <?= (!empty($selected_year) && $selected_year == $ay['tahun']) ? 'selected' : '' ?>>
                        Tahun <?= $ay['tahun'] ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        <select id="admin-filter-cluster">
            <option value="" <?= empty($selected_cluster) ? 'selected' : '' ?>>Semua Klaster (9 Klaster)</option>
            <?php if (!empty($clusters)): ?>
                <?php foreach ($clusters as $c): ?>
                    <option value="<?= htmlspecialchars($c['klaster']) ?>" <?= (!empty($selected_cluster) && $selected_cluster === $c['klaster']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['klaster']) ?> (<?= $c['total_indicators'] ?>)
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        <select id="admin-filter-status">
            <option value="Semua status" <?= ($status === 'Semua status' || !$status) ? 'selected' : '' ?>>Semua status (Aktif)</option>
            <option value="Tervalidasi" <?= $status === 'Tervalidasi' ? 'selected' : '' ?>>Tervalidasi</option>
            <option value="Perlu review" <?= $status === 'Perlu review' ? 'selected' : '' ?>>Perlu review</option>
            <option value="Belum lengkap" <?= ($status === 'Belum lengkap' || $status === 'Ditolak') ? 'selected' : '' ?>>Belum lengkap / Ditolak</option>
            <option value="Terhapus" <?= $status === 'Terhapus' ? 'selected' : '' ?>>🗑️ Sampah / Terhapus (<?= (int)$trash_count ?>)</option>
        </select>
        <select id="admin-sort">
            <option value="code" <?= ($sort === 'code' || empty($sort)) ? 'selected' : '' ?>>Urut kode (EKO-001 dst)</option>
            <option value="year" <?= $sort === 'year' ? 'selected' : '' ?>>Urut tahun terbaru</option>
            <option value="indicator" <?= $sort === 'indicator' ? 'selected' : '' ?>>Nama indikator</option>
            <option value="value" <?= $sort === 'value' ? 'selected' : '' ?>>Nilai terbesar</option>
        </select>
        <button class="button secondary" id="btn-export-database-csv">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12M7 10l5 5 5-5M4 21h16"/></svg>
            <span>Ekspor CSV</span>
        </button>
    </div>



    <!-- TABLE CARD -->
    <?php
    // Kelompokkan rekaman per indikator untuk tampilan matriks/pivot tahun ke samping
    $grouped = [];
    $subGrouped = []; // sub-indikator berinduk (misal EKO-003.A berinduk ke EKO-003)
    $yearSet = [];

    // Daftar resmi parent yang benar-benar memiliki sub-indikator sektoral
    $sectoralParents = ['EKO-003', 'EKO-004', 'EKO-006', 'EKO-007'];
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

        $isSub = (strpos($code, '.') !== false);
        $parentCode = $isSub ? explode('.', $code)[0] : $code;
        $subKey = $isSub ? explode('.', $code)[1] : null;

        if ($isSub && in_array($parentCode, $sectoralParents)) {
            if (!isset($subGrouped[$parentCode])) {
                $subGrouped[$parentCode] = [];
            }
            if (!isset($subGrouped[$parentCode][$code])) {
                $subGrouped[$parentCode][$code] = [
                    'code' => $code,
                    'parent_code' => $parentCode,
                    'sub_key' => $subKey,
                    'indicator' => $r['indicator'],
                    'cluster' => $r['cluster'],
                    'region' => $r['region'],
                    'unit' => $r['unit'],
                    'source' => $r['source'],
                    'definition' => $r['definition'] ?? '',
                    'period' => $r['period'],
                    'status' => $r['status'],
                    'verified_by' => $r['verified_by'],
                    'notes' => $r['notes'] ?? '',
                    'is_deleted' => $r['is_deleted'] ?? 0,
                    'deleted_at' => $r['deleted_at'] ?? null,
                    'latest_id' => $r['id'],
                    'latest_year' => $yr,
                    'years' => []
                ];
            }
            $subGrouped[$parentCode][$code]['years'][$yr] = $r;
            if ($yr >= $subGrouped[$parentCode][$code]['latest_year']) {
                $subGrouped[$parentCode][$code]['latest_id'] = $r['id'];
                $subGrouped[$parentCode][$code]['latest_year'] = $yr;
                $subGrouped[$parentCode][$code]['status'] = $r['status'];
            }
        } elseif (!$isSub) {
            if (!isset($grouped[$code])) {
                $grouped[$code] = [
                    'code' => $code,
                    'indicator' => $r['indicator'],
                    'cluster' => $r['cluster'],
                    'region' => $r['region'],
                    'unit' => $r['unit'],
                    'source' => $r['source'],
                    'definition' => $r['definition'] ?? '',
                    'period' => $r['period'],
                    'status' => $r['status'],
                    'verified_by' => $r['verified_by'],
                    'notes' => $r['notes'] ?? '',
                    'is_deleted' => $r['is_deleted'] ?? 0,
                    'deleted_at' => $r['deleted_at'] ?? null,
                    'latest_id' => $r['id'],
                    'latest_year' => $yr,
                    'years' => [],
                    'sub_indicators' => []
                ];
            }
            $grouped[$code]['years'][$yr] = $r;
            if ($yr >= $grouped[$code]['latest_year']) {
                $grouped[$code]['latest_id'] = $r['id'];
                $grouped[$code]['latest_year'] = $yr;
                $grouped[$code]['status'] = $r['status'];
                $grouped[$code]['verified_by'] = $r['verified_by'];
                $grouped[$code]['notes'] = $r['notes'] ?? '';
            }
        }
    }

    // Urutan abjad sektor standar BPS (A s.d. U, plus Tanpa Migas)
    $sectorOrder = array('A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6, 'G' => 7, 'H' => 8, 'I' => 9, 'J' => 10, 'K' => 11, 'L' => 12, 'MN' => 13, 'O' => 14, 'P' => 15, 'Q' => 16, 'RSTU' => 17, 'TM' => 18);

    // Sambungkan sub_indicators HANYA ke parent yang ada di $sectoralParents
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
            <button type="button" class="category-slide-tab <?= empty($activeCat) ? 'active' : '' ?>" data-prefix="" onclick="applyCategoryFilter('', this)">
                <span>Semua</span>
                <span class="cat-count-pill"><?= count($grouped) ?></span>
            </button>
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
                <span class="eyebrow"><?= ($status === 'Terhapus') ? 'TEMPAT SAMPAH (SOFT DELETED)' : 'DATABASE REPOSITORI & DERET WAKTU' ?></span>
                <h3><?= ($status === 'Terhapus') ? 'Data Indikator Terhapus' : 'Database Indikator Ekonomi' ?></h3>
                <p><span id="cat-filtered-count-label">Menampilkan <?= count($grouped) ?> indikator</span> (<?= count($records) ?> data realisasi tahunan) <?= ($status === 'Terhapus') ? 'di tempat sampah' : 'tersimpan dalam repositori daerah' ?></p>
            </div>
            <?php if (!empty($selected_year)): ?>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span class="badge info">Filter Tahun: <?= $selected_year ?></span>
                    <a href="<?= site_url('database' . (!empty($cluster) ? '?cluster=' . urlencode($cluster) : '')) ?>" class="text-button" style="font-size:11px;">Tampilkan Seluruh Tahun (2016–2025)</a>
                </div>
            <?php else: ?>
                <div style="font-size:11px; color:var(--muted); background:#f1f5f9; padding:5px 11px; border-radius:6px; border:1px solid #e2e8f0;">
                    <span style="color:#0f172a; font-weight:700;">Seri Tahun:</span> <?= !empty($display_years) ? (min($display_years) . ' s.d. ' . max($display_years) . ' (' . count($display_years) . ' Tahun)') : '-' ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($status !== 'Terhapus' && !empty($records)): ?>
            <?php
            $valCount = 0; $reviewCount = 0; $clusterCounts = [];
            foreach ($records as $rec) {
                $st = $rec['status'] ?? 'Tervalidasi';
                if ($st === 'Tervalidasi') $valCount++;
                else $reviewCount++;
                $cl = $rec['klaster'] ?? 'Lainnya';
                $clusterCounts[$cl] = ($clusterCounts[$cl] ?? 0) + 1;
            }
            $totRec = count($records) ?: 1;
            $maxCl = !empty($clusterCounts) ? max($clusterCounts) : 1;
            ?>
            <div style="display:grid; grid-template-columns: 1fr 1.3fr; gap:16px; padding:14px 18px; background:#f8fafc; border-bottom:1px solid var(--line);">
                <div>
                    <strong style="font-size:9px; color:var(--navy); display:block; margin-bottom:6px;">Status Validasi Data</strong>
                    <div style="display:flex; height:10px; border-radius:5px; overflow:hidden; background:#e2e8f0; margin-bottom:6px;">
                        <div style="width:<?= round(($valCount/$totRec)*100) ?>%; background:var(--green);" title="Tervalidasi: <?= $valCount ?>"></div>
                        <div style="width:<?= round(($reviewCount/$totRec)*100) ?>%; background:var(--yellow);" title="Perlu Review: <?= $reviewCount ?>"></div>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:8px; color:var(--muted);">
                        <span><i style="display:inline-block; width:7px; height:7px; border-radius:50%; background:var(--green); margin-right:3px;"></i>Tervalidasi: <strong><?= $valCount ?></strong> (<?= round(($valCount/$totRec)*100) ?>%)</span>
                        <span><i style="display:inline-block; width:7px; height:7px; border-radius:50%; background:var(--yellow); margin-right:3px;"></i>Perlu Review: <strong><?= $reviewCount ?></strong></span>
                    </div>
                </div>
                <div>
                    <strong style="font-size:9px; color:var(--navy); display:block; margin-bottom:6px;">Distribusi Rekaman per Klaster</strong>
                    <div style="display:grid; gap:5px;">
                        <?php foreach (array_slice($clusterCounts, 0, 4) as $cName => $cCnt): ?>
                            <div style="display:grid; grid-template-columns: 120px 1fr 35px; align-items:center; gap:6px; font-size:8px;">
                                <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= htmlspecialchars($cName) ?></span>
                                <div style="height:6px; border-radius:3px; background:#e2e8f0; overflow:hidden;">
                                    <div style="width:<?= round(($cCnt/$maxCl)*100) ?>%; height:100%; background:var(--blue);"></div>
                                </div>
                                <strong style="text-align:right; font-size:8px;"><?= $cCnt ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- TABEL DERET WAKTU: TAHUN KESAMPING DENGAN NILAI -->
        <div class="data-table-matrix-wrapper" style="overflow-x:auto; -webkit-overflow-scrolling:touch; width:100%; border-top:1px solid var(--line);">
            <table class="data-matrix-table" id="admin-matrix-table" style="width:100%; min-width:<?= 600 + (count($display_years) * 98) ?>px; border-collapse:separate; border-spacing:0;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th class="matrix-sticky-col-1" style="position:sticky; left:0; background:#f8fafc; z-index:3; width:85px; padding:12px 14px; font-size:11px; font-weight:700; color:#475569; border-bottom:1px solid var(--line); box-shadow:2px 0 5px rgba(0,0,0,0.03); text-align:left;">
                            Kode
                        </th>
                        <th style="min-width:240px; padding:12px 16px; font-size:11px; font-weight:700; color:#475569; border-bottom:1px solid var(--line); text-align:left;">
                            Nama Indikator
                        </th>
                        <th style="min-width:130px; padding:12px 14px; font-size:11px; font-weight:700; color:#475569; border-bottom:1px solid var(--line); text-align:left;">
                            Klaster
                        </th>
                        <th style="min-width:95px; padding:12px 14px; font-size:11px; font-weight:700; color:#475569; border-bottom:1px solid var(--line); text-align:left;">
                            Satuan
                        </th>
                        <?php foreach ($display_years as $yr): ?>
                            <th class="th-matrix-year <?= (!empty($selected_year) && $selected_year == $yr) ? 'active-year-col' : '' ?>" style="min-width:98px; text-align:right; padding:12px 14px; font-size:12px; font-weight:700; color:#1e293b; border-bottom:1px solid var(--line); border-left:1px solid #edf2f7; <?= ($yr == 2024 || $yr == $selected_year) ? 'background:#e0f2fe; color:#0369a1;' : '' ?>">
                                <?= $yr ?>
                            </th>
                        <?php endforeach; ?>
                        <th class="matrix-sticky-col-last" style="position:sticky; right:0; background:#f8fafc; z-index:3; width:105px; padding:12px 12px; text-align:center; font-size:11px; font-weight:700; color:#475569; border-bottom:1px solid var(--line); box-shadow:-2px 0 5px rgba(0,0,0,0.03);">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody id="admin-table-body">
                    <?php if (empty($grouped)): ?>
                        <tr>
                            <td colspan="<?= 5 + count($display_years) ?>" style="padding:48px 20px; text-align:center; color:var(--muted);">
                                <div style="display:flex; flex-direction:column; align-items:center; gap:8px;">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v7c0 1.7 3.6 3 8 3s8-1.3 8-3V5M4 12v7c0 1.7 3.6 3 8 3s8-1.3 8-3v-7"/></svg>
                                    <strong><?= ($status === 'Terhapus') ? 'Tempat sampah kosong. Tidak ada data yang dihapus.' : 'Tidak ada indikator yang cocok dengan filter.' ?></strong>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($grouped as $code => $item): ?>
                            <?php $hasSub = (!empty($item['sub_indicators']) && in_array($item['code'], $sectoralParents)); ?>
                            <tr class="matrix-row <?= $hasSub ? 'has-subcategories' : '' ?>" data-code="<?= $item['code'] ?>" data-prefix="<?= strtoupper(explode('-', $item['code'])[0]) ?>" data-cluster="<?= htmlspecialchars($item['cluster'] ?? '') ?>" data-definition="<?= htmlspecialchars($item['definition'] ?? '') ?>" <?= $hasSub ? 'data-has-sub="true" title="Klik baris ini untuk membuka/menutup rincian sub-indikator" onclick="if(!event.target.closest(\'button, a, input, select, .matrix-sticky-col-last\')) toggleIndicatorSubRows(\''.htmlspecialchars($item['code']).'\', event);"' : '' ?>>
                                <td class="matrix-sticky-col-1" style="position:sticky; left:0; background:#fff; z-index:2; padding:11px 14px; border-bottom:1px solid #edf0f4; box-shadow:2px 0 5px rgba(0,0,0,0.03); white-space:nowrap;">
                                    <code style="font-size:11px; font-weight:700; color:var(--blue); background:#eef4fa; padding:3px 7px; border-radius:5px; display:inline-block;"><?= htmlspecialchars($item['code']) ?></code>
                                </td>
                                <td class="<?= $hasSub ? 'clickable-indicator-cell' : '' ?>" style="padding:11px 16px; border-bottom:1px solid #edf0f4; vertical-align:middle; cursor:<?= $hasSub ? 'pointer' : 'default' ?>;" <?= $hasSub ? 'data-parent="'.htmlspecialchars($item['code']).'" onclick="toggleIndicatorSubRows(\''.htmlspecialchars($item['code']).'\', event);"' : '' ?>>
                                    <div style="font-size:12.5px; font-weight:700; color:#1e293b; line-height:1.4; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                        <span class="<?= $hasSub ? 'indicator-title-clickable' : '' ?>"><?= htmlspecialchars($item['indicator']) ?></span>
                                        <?php if ($hasSub): ?>
                                            <button type="button" class="btn-toggle-subcategories" data-parent="<?= $item['code'] ?>" data-label-open="Rincian Sektor (A–U)" onclick="toggleIndicatorSubRows('<?= htmlspecialchars($item['code']) ?>', event);" style="display:inline-flex; align-items:center; gap:5px; padding:3px 8px; font-size:11px; font-weight:700; background:#f0fdf4; color:#15803d; border:1.2px solid #86efac; border-radius:6px; cursor:pointer; transition:all 0.15s ease;" title="Klik untuk membuka/menutup rincian sektor">
                                                <svg class="sub-arrow-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="transition:transform 0.2s ease;"><polyline points="6 9 12 15 18 9"/></svg>
                                                <span class="sub-toggle-label">Rincian Sektor (A–U)</span>
                                                <span style="background:#dcfce7; color:#166534; font-size:9.5px; padding:1px 6px; border-radius:10px; font-weight:800;">18 Sektor</span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <small style="font-size:10.5px; color:#64748b; display:block; margin-top:2px;">
                                        <?= htmlspecialchars($item['source']) ?>
                                    </small>
                                    <?php if (!empty($item['definition'])): ?>
                                        <div class="table-definition-preview" title="<?= htmlspecialchars($item['definition']) ?>" style="font-size:10px; color:#94a3b8; margin-top:3px; line-height:1.3; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; max-width:320px;">
                                            <span style="color:#64748b; font-weight:600;">Definisi:</span> <?= htmlspecialchars($item['definition']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($item['notes']) && $item['status'] !== 'Tervalidasi'): ?>
                                        <div style="margin-top:4px; font-size:10px; line-height:1.3; color:#b91c1c; background:#fef2f2; border:1px solid #fecaca; border-radius:4px; padding:2px 6px; display:inline-flex; align-items:center; gap:4px;">
                                            <span><strong>Catatan:</strong> <?= htmlspecialchars($item['notes']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:11px 14px; border-bottom:1px solid #edf0f4; vertical-align:middle;">
                                    <span class="badge secondary" style="font-size:10px; padding:3px 7px; white-space:nowrap;">
                                        <?= htmlspecialchars($item['cluster']) ?>
                                    </span>
                                </td>
                                <td style="padding:11px 14px; border-bottom:1px solid #edf0f4; font-size:11px; color:#475569; vertical-align:middle; white-space:nowrap;">
                                    <?= htmlspecialchars($item['unit']) ?>
                                </td>
                                <?php foreach ($display_years as $yr): ?>
                                    <td class="matrix-cell-td <?= (!empty($selected_year) && $selected_year == $yr) ? 'active-year-cell' : '' ?>" style="padding:9px 12px; border-bottom:1px solid #edf0f4; border-left:1px solid #f1f5f9; text-align:right; font-variant-numeric:tabular-nums; vertical-align:middle; <?= ($yr == 2024 || $yr == $selected_year) ? 'background:#f8fbff;' : '' ?>">
                                        <?php if (isset($item['years'][$yr])): ?>
                                            <?php $rec = $item['years'][$yr]; ?>
                                            <div class="matrix-val-pill" title="<?= htmlspecialchars($item['indicator']) ?> (<?= $yr ?>): <?= number_format($rec['value'], 2, ',', '.') ?> <?= htmlspecialchars($item['unit']) ?> [<?= $rec['status'] ?>]">
                                                <span class="matrix-val-text <?= $rec['status'] === 'Perlu review' ? 'val-warning' : ($rec['status'] === 'Ditolak' ? 'val-danger' : '') ?>" style="font-weight:600; font-size:12px; color:#0f172a;">
                                                    <?= number_format($rec['value'], 2, ',', '.') ?>
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <div class="matrix-empty-pill" title="Belum ada data untuk <?= $yr ?>">
                                                <span class="matrix-dash" style="color:#cbd5e1; font-size:12px;">-</span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                                <td class="matrix-sticky-col-last" style="position:sticky; right:0; background:#fff; z-index:2; padding:11px 12px; border-bottom:1px solid #edf0f4; box-shadow:-2px 0 5px rgba(0,0,0,0.03); text-align:center; vertical-align:middle; white-space:nowrap;">
                                    <?php if ($status === 'Terhapus' || !empty($item['is_deleted'])): ?>
                                        <div style="display:inline-flex; align-items:center; gap:5px;">
                                            <button type="button" class="btn-restore-series icon-button small" data-code="<?= $item['code'] ?>" data-indicator="<?= htmlspecialchars($item['indicator']) ?>" title="Pulihkan Seluruh Data <?= $item['code'] ?>" style="width:28px; height:28px; color:var(--green); background:#f0fdf4; border:1px solid #bbf7d0;">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                            </button>
                                            <button type="button" class="btn-hard-delete-series icon-button small danger" data-code="<?= $item['code'] ?>" data-indicator="<?= htmlspecialchars($item['indicator']) ?>" title="Hapus Permanen Seluruh Data <?= $item['code'] ?>" style="width:28px; height:28px;">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <div style="display:inline-flex; align-items:center; gap:6px;">
                                            <?php if (!empty($item['sub_indicators'])): ?>
                                                <?php
                                                    $isExp = in_array($item['code'], ['EKO-015', 'EKO-016']);
                                                    $matrixBtnLabel = $isExp ? '6 Komponen' : '17 Sektor';
                                                    $matrixBtnTitle = $isExp ? 'Input & Kelola Seluruh Komponen Pengeluaran Sekaligus' : 'Input & Kelola Seluruh 17 Sektor Lapangan Usaha Sekaligus';
                                                ?>
                                                <button type="button" class="button small secondary btn-open-sectoral-from-row" data-parent="<?= htmlspecialchars($item['code']) ?>" title="<?= $matrixBtnTitle ?>" style="padding:4px 9px; font-size:11px; font-weight:700; background:#f0fdf4; color:#15803d; border:1px solid #86efac; border-radius:6px; display:inline-flex; align-items:center; gap:4px; margin-right:2px; cursor:pointer;">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>
                                                    <?= $matrixBtnLabel ?>
                                                </button>
                                            <?php endif; ?>
                                            <button type="button" class="btn-edit-series icon-button small" data-code="<?= $item['code'] ?>" title="Edit Seluruh Data <?= $item['code'] ?> (Mencakup Seluruh Tahun)" style="width:28px; height:28px; color:var(--blue); background:#eff6ff; border:1px solid #bfdbfe;">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </button>
                                            <button type="button" class="btn-delete-series icon-button small danger" data-code="<?= $item['code'] ?>" data-indicator="<?= htmlspecialchars($item['indicator']) ?>" title="Hapus Indikator Ini (Soft Delete)" style="width:28px; height:28px;">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- SUB-ROWS UNTUK RINCIAN KATEGORI SEKTORAL (MISAL KATEGORI A S.D. U) -->
                            <?php if (!empty($item['sub_indicators'])): ?>
                                <?php foreach ($item['sub_indicators'] as $subCode => $sub): ?>
                                    <?php
                                        $dispKey = $sub['sub_key'];
                                        if ($dispKey === 'MN') $dispKey = 'M,N';
                                        elseif ($dispKey === 'RSTU') $dispKey = 'R,S,T,U';
                                        $cleanSubName = preg_replace('/^(PDRB\s+(ADHB|ADHK)|Pertumbuhan(\s+PDRB(\s+ADHK)?)?|Distribusi(\s+PDRB(\s+ADHB)?)?|Kontribusi|PDRB\s+Pengeluaran\s+(ADHB|ADHK)|Pengeluaran\s+(ADHB|ADHK)):\s*/i', '', $sub['indicator']);
                                    ?>
                                    <tr class="matrix-row matrix-sub-row sub-row-<?= $item['code'] ?>" data-code="<?= $sub['code'] ?>" data-parent="<?= $item['code'] ?>" data-prefix="<?= strtoupper(explode('-', $item['code'])[0]) ?>" style="display:none; background:#f8fafc;">
                                        <td class="matrix-sticky-col-1" style="position:sticky; left:0; background:#f8fafc; z-index:2; padding:8px 12px 8px 16px; border-bottom:1px solid #edf0f4; box-shadow:2px 0 5px rgba(0,0,0,0.03); white-space:nowrap;">
                                            <div style="display:flex; align-items:center; gap:5px;">
                                                <span style="color:#94a3b8; font-weight:700; font-size:11px;">└──</span>
                                                <code style="font-size:10.5px; font-weight:700; color:#0369a1; background:#e0f2fe; padding:2px 6px; border-radius:4px;"><?= htmlspecialchars($sub['code']) ?></code>
                                                <span style="font-size:9.5px; font-weight:800; color:#475569; background:#e2e8f0; padding:1px 5px; border-radius:4px;"><?= htmlspecialchars($dispKey) ?></span>
                                            </div>
                                        </td>
                                        <td style="padding:8px 14px; border-bottom:1px solid #edf0f4; vertical-align:middle; background:#f8fafc;">
                                            <div style="font-size:12px; font-weight:700; color:#1e293b; line-height:1.35;">
                                                <?= htmlspecialchars($sub['indicator']) ?>
                                            </div>
                                            <div style="font-size:11px; color:#475569; margin-top:2px;">
                                                <span style="color:#0284c7; font-weight:600;">Kategori:</span> <?= htmlspecialchars($cleanSubName) ?>
                                            </div>
                                            <small style="font-size:10px; color:#64748b; display:block; margin-top:2px;"><?= htmlspecialchars($sub['source']) ?></small>
                                        </td>
                                        <td style="padding:7px 14px; border-bottom:1px solid #edf0f4; vertical-align:middle; background:#f8fafc;">
                                            <span class="badge secondary" style="font-size:9.5px; padding:2px 6px;"><?= htmlspecialchars($sub['cluster']) ?></span>
                                        </td>
                                        <td style="padding:7px 14px; border-bottom:1px solid #edf0f4; font-size:11px; color:#475569; vertical-align:middle; white-space:nowrap; background:#f8fafc;">
                                            <?= htmlspecialchars($sub['unit']) ?>
                                        </td>
                                        <?php foreach ($display_years as $yr): ?>
                                            <td class="matrix-cell-td" style="padding:7px 12px; border-bottom:1px solid #edf0f4; border-left:1px solid #f1f5f9; text-align:right; font-variant-numeric:tabular-nums; vertical-align:middle; background:#f8fafc;">
                                                <?php if (isset($sub['years'][$yr])): ?>
                                                    <?php $sRec = $sub['years'][$yr]; ?>
                                                    <span style="font-weight:600; font-size:12px; color:#0f172a;">
                                                        <?= number_format($sRec['value'], 2, ',', '.') ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span style="color:#cbd5e1; font-size:12px;">-</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                        <td class="matrix-sticky-col-last" style="position:sticky; right:0; background:#f8fafc; z-index:2; padding:7px 12px; border-bottom:1px solid #edf0f4; text-align:center; vertical-align:middle; white-space:nowrap;">
                                            <button type="button" class="btn-edit-series icon-button small" data-code="<?= $sub['code'] ?>" title="Edit Data <?= $sub['code'] ?>" style="width:26px; height:26px; color:var(--blue); background:#eff6ff; border:1px solid #bfdbfe;">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </button>
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
            <span>Total indikator: <strong><?= count($grouped) ?></strong> indikator (<strong><?= count($records) ?></strong> rekaman data tahunan)</span>
            <span>Data tersinkronisasi dengan rilis resmi BPS & repositori daerah</span>
        </div>
    </article>

    <!-- MODAL TAMBAH DATA (CREATE) -->
    <div class="modal-backdrop" id="add-data-modal" style="display:none;">
        <div class="modal admin-data-modal">
            <div class="modal-head">
                <div>
                    <span class="badge info">TAMBAH DATA BARU</span>
                    <h2>Tambah Data Indikator Ekonomi</h2>
                    <p>Pilih indikator dari kamus master, lalu tentukan tahun dan nilai realisasi capaian daerah.</p>
                </div>
                <button class="icon-button" id="modal-btn-close-add" title="Tutup Modal">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="form-manual-add" method="POST" action="<?= site_url('database/create') ?>">
                <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:11px 14px; margin-bottom:14px; font-size:11.5px; color:#1e40af; line-height:1.5; display:flex; align-items:flex-start; gap:10px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0; margin-top:2px; color:#2563eb;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <div>
                        <strong>Standar Kamus Data Terintegrasi:</strong> Kode, Nama, Klaster, Satuan, Sumber, dan Definisi Operasional merupakan <strong>Data Umum Kamus</strong> yang berlaku seragam untuk seluruh wilayah. Di tingkat daerah, operator hanya mengisikan <strong>Tahun</strong> dan <strong>Nilai Realisasi</strong>.
                    </div>
                </div>

                <!-- LANGKAH 1: PILIH KATEGORI & INDIKATOR MASTER -->
                <div style="background:#f8fafc; border:1.5px solid #e2e8f0; border-radius:10px; padding:12px 14px; margin-bottom:14px;">
                    <div style="display:grid; grid-template-columns: 1fr 1.6fr; gap:12px; margin-bottom:10px;">
                        <div>
                            <label for="add-category-filter-select" style="display:block; margin-bottom:5px; font-size:11.5px; font-weight:700; color:#1e293b;">
                                1. Pilih Kategori / Klaster
                            </label>
                            <select id="add-category-filter-select" style="width:100%; height:38px; font-size:12px; font-weight:600; padding:0 10px; border-radius:8px; border:1.5px solid #cbd5e1; background:#ffffff; color:#0f172a; cursor:pointer;">
                                <option value="">-- Semua Kategori / Klaster --</option>
                                <?php if (!empty($clusters)): ?>
                                    <?php foreach ($clusters as $c): ?>
                                        <option value="<?= htmlspecialchars($c['klaster']) ?>">
                                            <?= htmlspecialchars($c['klaster']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div>
                            <label for="add-indicator-master-select" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px; font-size:11.5px; font-weight:700; color:#1e293b;">
                                <span>2. Pilih Indikator Master (Data Umum)</span>
                                <span style="font-size:10px; font-weight:700; color:#0284c7; background:#e0f2fe; padding:2px 7px; border-radius:10px;">Auto-Fill</span>
                            </label>
                            <select id="add-indicator-master-select" style="width:100%; height:38px; font-size:12px; font-weight:600; padding:0 12px; border-radius:8px; border:1.5px solid #cbd5e1; background:#ffffff; color:#0f172a; cursor:pointer;">
                                <option value="">-- Pilih Indikator Master untuk Mengisi Otomatis --</option>
                                <?php if (!empty($catalog_indicators)): ?>
                                    <?php foreach ($catalog_indicators as $catItem): ?>
                                        <?php if (strpos($catItem['code'], '.') !== false) continue; // Jangan tampilkan sub-kode di dropdown master utama ?>
                                        <option value="<?= htmlspecialchars($catItem['code']) ?>" data-cluster="<?= htmlspecialchars($catItem['cluster']) ?>">
                                            [<?= htmlspecialchars($catItem['code']) ?>] <?= htmlspecialchars($catItem['name']) ?> &bull; <?= htmlspecialchars($catItem['cluster']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div style="font-size:10.5px; color:#64748b; margin-top:3px; display:flex; align-items:center; gap:4px;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        Memilih kategori atau indikator master akan otomatis mengisi sendiri seluruh atribut data umum di bawah ini.
                    </div>

                    <!-- PILIH RINCIAN KATEGORI JIKA INDIKATOR MEMILIKI SUB-KATEGORI (MISAL EKO-003, EKO-004, EKO-006, EKO-007, EKO-015, EKO-016) -->
                    <div id="add-sectoral-category-group" style="display:none; margin-top:12px; background:#f0fdf4; border:1.5px solid #86efac; border-radius:8px; padding:10px 14px;">
                        <label for="add-sectoral-category-select" style="font-size:11.5px; font-weight:700; color:#166534; display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                            <span id="add-sectoral-category-label" style="display:flex; align-items:center; gap:6px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
                                Rincian Kategori Sub-Indikator
                            </span>
                            <span id="add-sectoral-badge-label" style="font-size:9.5px; font-weight:700; background:#dcfce7; color:#15803d; padding:2px 6px; border-radius:8px;">Pilih Rincian</span>
                        </label>
                        <select id="add-sectoral-category-select" style="width:100%; height:36px; font-size:12px; font-weight:700; padding:0 10px; border-radius:6px; border:1.5px solid #86efac; background:#fff; color:#0f172a; cursor:pointer;">
                            <!-- Di-generate dinamis oleh JS -->
                        </select>
                        <div style="font-size:10px; color:#15803d; margin-top:6px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:6px;">
                            <span>Pilih salah satu kategori/rincian di atas untuk otomatis mengisi data indikator tersebut.</span>
                            <button type="button" id="btn-quick-open-sectoral-matrix" style="background:#dcfce7; color:#166534; border:1px solid #86efac; border-radius:5px; padding:3px 9px; font-size:10.5px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:4px;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>
                                Buka Spreadsheet Matriks Lengkap &rarr;
                            </button>
                        </div>
                    </div>
                </div>

                <div class="manual-form">
                    <label>
                        Kode Indikator
                        <input type="text" name="code" id="add-code" placeholder="Pilih dari master..." list="catalog-code-list" autocomplete="off" readonly required>
                    </label>
                    <label>
                        Satuan Ukuran
                        <input type="text" name="unit" id="add-unit" placeholder="Satuan..." readonly required>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Nama Indikator
                        <input type="text" name="indicator" id="add-indicator" placeholder="Otomatis dari master..." readonly required>
                    </label>
                    <label>
                        Klaster Pembangunan
                        <input type="text" name="cluster" id="add-cluster" placeholder="Klaster..." readonly required>
                    </label>
                    <label>
                        Sumber Data / Walidata
                        <input type="text" name="source" id="add-source" placeholder="Otomatis dari master..." readonly required>
                    </label>
                    <input type="hidden" name="region" id="add-region" value="<?= htmlspecialchars($nama_wilayah ?? 'Kabupaten Banyuwangi') ?>">
                    <input type="hidden" name="kode_wilayah" id="add-kode-wilayah" value="<?= htmlspecialchars($active_kode_wilayah ?? '35.10') ?>">
                    <label style="grid-column: 1 / 3;">
                        Definisi Operasional
                        <textarea name="definition" id="add-definition" rows="2" readonly placeholder="Definisi operasional terstandarisasi dari katalog..."></textarea>
                    </label>

                    <!-- LANGKAH 2: INPUT REALISASI DAERAH (BISA SATU ATAU BANYAK TAHUN SEKALIGUS) -->
                    <div style="grid-column: 1 / 3; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 10px; padding: 14px 16px; margin-top: 4px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
                            <div>
                                <span style="font-size:11.5px; font-weight:700; color:#166534; text-transform:uppercase; letter-spacing:0.04em; display:flex; align-items:center; gap:6px;">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                    Langkah 2 &bull; Input Realisasi Daerah (<?= htmlspecialchars($nama_wilayah ?? 'Kabupaten Banyuwangi') ?>)
                                </span>
                                <small style="font-size:11px; color:#15803d; display:block; margin-top:2px;">
                                    Masukkan angka dan tahunnya. Anda dapat menghapus baris atau menambah tahun (sebelumnya / berikutnya) dengan tombol di samping kanan.
                                </small>
                            </div>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <button type="button" class="button small secondary btn-add-year-prev" data-target="add" style="padding:5px 11px; font-size:11px; font-weight:700; background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; border-radius:6px; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                                    Tahun Sebelumnya
                                </button>
                                <button type="button" class="button small secondary btn-add-year-next" data-target="add" style="padding:5px 11px; font-size:11px; font-weight:700; background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; border-radius:6px; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                                    Tahun Sesudahnya
                                </button>
                            </div>
                        </div>

                        <div class="edit-series-table-wrap" style="max-height: 250px;">
                            <table class="edit-series-table">
                                <thead>
                                    <tr>
                                        <th style="width: 105px; text-align: left;">Tahun</th>
                                        <th style="text-align: left;">Nilai Realisasi</th>
                                        <th style="width: 120px; text-align: left;">Satuan</th>
                                        <th style="width: 55px; text-align: center;">Hapus</th>
                                    </tr>
                                </thead>
                                <tbody id="add-series-table-body">
                                    <tr class="series-table-row">
                                        <td style="padding: 7px 10px; vertical-align: middle;">
                                            <input type="number" name="series_years[]" class="series-input-year" value="" placeholder="Tahun" min="1900" max="2100" style="width:100%; height:36px; padding:0 8px; font-weight:800; font-size:13px; border:1.5px solid #cbd5e1; border-radius:6px; text-align:center; background:#fff;">
                                        </td>
                                        <td style="padding: 6px 10px; vertical-align: middle;">
                                            <input type="number" step="any" name="series_values[]" class="series-val-input-row" placeholder="Contoh: 5.18" required style="width:100%; height:36px; padding:0 12px; font-weight:700; font-size:13px; border:1.5px solid #cbd5e1; border-radius:6px; text-align:right; background:#fff;">
                                        </td>
                                        <td class="add-series-unit-cell" style="padding: 7px 10px; vertical-align: middle; font-size:12px; font-weight:700; color:#475569; white-space:nowrap;">
                                            %
                                        </td>
                                        <td style="padding: 7px 8px; vertical-align: middle; text-align: center;">
                                            <button type="button" class="btn-remove-series-row icon-button small danger" title="Hapus Baris Ini" style="width:28px; height:28px; margin:0 auto; color:#dc2626; background:#fee2e2; border:1px solid #fca5a5;">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="add-modal-error" class="method-note" style="display:none; color:var(--red); margin-bottom:12px;"></div>

                <div class="modal-actions">
                    <button type="button" class="button ghost" id="modal-btn-cancel-add">Batal</button>
                    <button type="submit" class="button primary" id="modal-btn-submit-add">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Simpan ke Database
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT DATA (UPDATE MULTI-TAHUN MENCAKUP SELURUH DATA YANG DIUNGGAH) -->
    <div class="modal-backdrop" id="edit-data-modal" style="display:none;">
        <div class="modal admin-data-modal" style="max-width:840px; width:95%;">
            <div class="modal-head">
                <div>
                    <span class="badge warning">PERBARUI DERET WAKTU</span>
                    <h2>Edit Data Indikator Ekonomi</h2>
                    <p>Perbarui nilai realisasi tahunan yang telah diunggah untuk wilayah <strong id="edit-region-label"><?= htmlspecialchars($nama_wilayah ?? 'Kabupaten Banyuwangi') ?></strong>. Seluruh nilai yang diubah otomatis berstatus <strong>Perlu review</strong>.</p>
                </div>
                <button class="icon-button" id="modal-btn-close-edit" title="Tutup Modal">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <!-- REJECTION REASON CALLOUT -->
            <div id="edit-rejection-callout" style="display:none; background:#fef2f2; border:1px solid #fecaca; border-radius:10px; padding:11px 14px; margin-top:14px; font-size:12px; color:#991b1b;">
                <strong style="display:flex; align-items:center; gap:6px; margin-bottom:4px; font-size:12px; color:#b91c1c;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    Catatan Penolakan Sebelumnya:
                </strong>
                <span id="edit-rejection-reason-text" style="line-height:1.45; display:block;"></span>
                <div style="margin-top:6px; font-size:11px; color:#059669; font-weight:600; display:flex; align-items:center; gap:5px;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Status otomatis menjadi "Perlu review" dan catatan penolakan ini otomatis terhapus saat disimpan.</span>
                </div>
            </div>

            <form id="form-manual-edit" method="POST" action="<?= site_url('database/update_series') ?>">
                <input type="hidden" name="id" id="edit-id">
                <input type="hidden" name="code" id="edit-code">
                <input type="hidden" name="region" id="edit-region" value="<?= htmlspecialchars($nama_wilayah ?? 'Kabupaten Banyuwangi') ?>">
                <input type="hidden" name="kode_wilayah" id="edit-kode-wilayah" value="<?= htmlspecialchars($active_kode_wilayah ?? '35.10') ?>">

                <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:11px 14px; margin-top:14px; margin-bottom:14px; font-size:11.5px; color:#1e40af; line-height:1.5; display:flex; align-items:flex-start; gap:10px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0; margin-top:2px; color:#2563eb;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <div>
                        <strong>Standar Kamus Data Umum:</strong> Kode, Nama, Klaster, Satuan, Sumber, dan Definisi Operasional merupakan <strong>Data Umum Kamus</strong> (standar seluruh wilayah). Anda dapat memperbarui <strong>seluruh nilai realisasi tahunan yang telah di-upload</strong> pada deret waktu di bawah ini.
                    </div>
                </div>

                <div class="manual-form" style="margin-bottom:14px;">
                    <label>
                        Kode Indikator
                        <input type="text" id="edit-code-display" readonly style="background:#f8fafc; font-weight:700; color:var(--blue);">
                    </label>
                    <label>
                        Satuan Ukuran
                        <input type="text" name="unit" id="edit-unit" readonly style="background:#f8fafc; font-weight:600;">
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Nama Indikator
                        <input type="text" name="indicator" id="edit-indicator" readonly style="background:#f8fafc; font-weight:600;">
                    </label>
                    <label>
                        Klaster Pembangunan
                        <input type="text" name="cluster" id="edit-cluster" readonly style="background:#f8fafc;">
                    </label>
                    <label>
                        Sumber Data / Walidata
                        <input type="text" name="source" id="edit-source" readonly style="background:#f8fafc;">
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Definisi Operasional
                        <textarea name="definition" id="edit-definition" rows="2" readonly placeholder="Definisi operasional..." style="background:#f8fafc; font-size:11px;"></textarea>
                    </label>
                </div>

                <!-- BLOK PEMBARUAN SELURUH DERET TAHUN (DATA YANG SUDAH DI-UPLOAD) -->
                <div style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 10px; padding: 16px 18px; margin-top: 4px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
                        <div>
                            <span style="font-size:11.5px; font-weight:700; color:#166534; text-transform:uppercase; letter-spacing:0.04em; display:flex; align-items:center; gap:6px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Deret Nilai Realisasi Tahunan (Seluruh Data yang Di-upload)
                            </span>
                            <small style="color:#15803d; font-size:11px; display:block; margin-top:2px;">
                                Hapus tahun/nilai dengan tombol di samping kolom, atau tambah tahun (sebelumnya / berikutnya) dengan tombol di samping kanan.
                            </small>
                        </div>
                        <div style="display:flex; align-items:center; gap:6px;">
                            <button type="button" class="button small secondary btn-add-year-prev" data-target="edit" style="padding:5px 11px; font-size:11px; font-weight:700; background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; border-radius:6px; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                                Tahun Sebelumnya
                            </button>
                            <button type="button" class="button small secondary btn-add-year-next" data-target="edit" style="padding:5px 11px; font-size:11px; font-weight:700; background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; border-radius:6px; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                                Tahun Sesudahnya
                            </button>
                            <span id="edit-series-uploaded-count" style="font-size:10.5px; font-weight:700; color:#166534; background:#dcfce7; padding:3px 10px; border-radius:12px; border:1px solid #bbf7d0;">
                                Memuat data tahun...
                            </span>
                        </div>
                    </div>

                    <!-- TABEL DERET TAHUN KE BAWAH -->
                    <div class="edit-series-table-wrap">
                        <table class="edit-series-table">
                            <thead>
                                <tr>
                                    <th style="width: 105px; text-align: left;">Tahun</th>
                                    <th style="width: 110px; text-align: left;">Status Data</th>
                                    <th style="text-align: left;">Nilai Realisasi</th>
                                    <th style="width: 120px; text-align: left;">Satuan</th>
                                    <th style="width: 55px; text-align: center;">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="edit-series-grid">
                                <!-- Di-generate dinamis oleh AJAX JS get_series (tersusun ke bawah) -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="edit-modal-error" class="method-note" style="display:none; color:var(--red); margin-top:12px; margin-bottom:12px;"></div>

                <div class="modal-actions" style="margin-top:16px;">
                    <button type="button" class="button ghost" id="modal-btn-cancel-edit">Batal</button>
                    <button type="submit" class="button primary" id="modal-btn-submit-edit">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Simpan Seluruh Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL INPUT & KELOLA PDRB SEKTORAL (17 LAPANGAN USAHA BPS) -->
    <div class="modal-backdrop" id="modal-sectoral-matrix" style="display:none; z-index:1050; align-items:center; justify-content:center;">
        <div class="modal admin-data-modal" style="max-width:1250px; width:95%; max-height:92vh; display:flex; flex-direction:column; padding:0; border-radius:12px; overflow:hidden; background:#fff; box-shadow:0 25px 50px -12px rgba(0, 0, 0, 0.25);">
            <!-- MODAL HEADER -->
            <div class="modal-head" style="padding:18px 24px; border-bottom:1px solid var(--line); background:#fff; flex-shrink:0;">
                <div style="display:flex; justify-content:space-between; align-items:center; width:100%;">
                    <div>
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                            <span class="badge info" id="sec-modal-badge" style="background:#e0f2fe; color:#0369a1; font-weight:700; font-size:10px;">SEKTORAL PDRB &bull; STANDAR BPS</span>
                            <span style="font-size:11px; color:#15803d; font-weight:700; background:#f0fdf4; border:1px solid #bbf7d0; padding:2px 8px; border-radius:12px;">
                                <?= htmlspecialchars($nama_wilayah ?? 'Kabupaten Banyuwangi') ?>
                            </span>
                        </div>
                        <h2 id="sec-modal-title" style="font-size:18px; margin:0 0 2px 0; color:#0f172a; font-weight:800;">Input & Kelola Rincian Kategori Sektoral / Pengeluaran</h2>
                        <p id="sec-modal-desc" style="margin:0; font-size:12px; color:#64748b;">Kelola nilai realisasi seluruh kategori ekonomi per tahun sekaligus dalam satu tabel matriks.</p>
                    </div>
                    <button type="button" class="icon-button" id="modal-btn-close-sectoral" title="Tutup Modal" style="width:34px; height:34px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 6 12 12M18 6 6 18"/></svg>
                    </button>
                </div>
            </div>

            <!-- MODAL TOOLBAR -->
            <div style="padding:10px 24px; background:#f8fafc; border-bottom:1px solid var(--line); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; flex-shrink:0;">
                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                    <div style="display:flex; align-items:center; gap:6px;">
                        <label for="sec-select-indicator-category" style="font-size:11.5px; font-weight:700; color:#334155; margin:0;">Pilih Kategori Indikator:</label>
                        <select id="sec-select-indicator-category" style="height:32px; font-size:11.5px; font-weight:700; padding:0 10px; border-radius:6px; border:1.5px solid #cbd5e1; background:#fff; color:#0f172a; cursor:pointer;">
                            <option value="EKO-003">[EKO-003] PDRB ADHB Menurut Lapangan Usaha (17 Sektor A–U) &bull; Miliar Rp</option>
                            <option value="EKO-004">[EKO-004] PDRB ADHK Menurut Lapangan Usaha (17 Sektor A–U) &bull; Miliar Rp</option>
                            <option value="EKO-006">[EKO-006] Laju Pertumbuhan PDRB Menurut Lapangan Usaha (17 Sektor A–U) &bull; %</option>
                            <option value="EKO-007">[EKO-007] Distribusi / Share PDRB Menurut Lapangan Usaha (17 Sektor A–U) &bull; %</option>
                            <option value="EKO-015">[EKO-015] PDRB Pengeluaran ADHB Menurut Komponen (6 Komponen) &bull; Miliar Rp</option>
                            <option value="EKO-016">[EKO-016] PDRB Pengeluaran ADHK Menurut Komponen (6 Komponen) &bull; Miliar Rp</option>
                        </select>
                    </div>
                    <div style="display:flex; align-items:center; gap:6px;">
                        <span style="font-size:11.5px; font-weight:700; color:#334155;">Satuan:</span>
                        <span id="sec-modal-unit-badge" style="font-size:11px; font-weight:700; color:#0284c7; background:#e0f2fe; padding:3px 10px; border-radius:6px; border:1px solid #bae6fd;">Miliar rupiah</span>
                    </div>
                    <span id="sec-modal-help-text" style="font-size:11px; color:#15803d; background:#f0fdf4; border:1px solid #bbf7d0; padding:3px 8px; border-radius:6px; display:inline-flex; align-items:center; gap:4px;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        Total otomatis terhitung real-time saat Anda mengetik nilai
                    </span>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <button type="button" class="button small secondary" id="btn-sec-add-cat-row" style="padding:6px 12px; font-size:11.5px; font-weight:700; background:#f0fdf4; color:#047857; border:1.5px solid #a7f3d0; border-radius:6px; cursor:pointer; display:inline-flex; align-items:center; gap:5px;" title="Tambah Baris Kategori Baru">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                        Tambah Baris Kategori
                    </button>
                    <button type="button" class="button small secondary" id="btn-sec-add-prev-year" style="padding:6px 12px; font-size:11.5px; font-weight:700; background:#f0fdf4; color:#166534; border:1.5px solid #86efac; border-radius:6px; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                        Tahun Sebelumnya
                    </button>
                    <button type="button" class="button small secondary" id="btn-sec-add-next-year" style="padding:6px 12px; font-size:11.5px; font-weight:700; background:#eff6ff; color:#1d4ed8; border:1.5px solid #93c5fd; border-radius:6px; cursor:pointer; display:inline-flex; align-items:center; gap:5px;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                        Tahun Sesudahnya
                    </button>
                </div>
            </div>

            <!-- MODAL BODY: SPREADSHEET TABLE -->
            <div style="padding:0; overflow-y:auto; overflow-x:auto; flex:1; -webkit-overflow-scrolling:touch; background:#fff; max-height:calc(92vh - 190px);">
                <form id="form-sectoral-matrix">
                    <input type="hidden" name="kode_wilayah" id="sec-form-kode-wilayah" value="<?= htmlspecialchars($active_kode_wilayah ?? '35.10') ?>">
                    <input type="hidden" name="parent_code" id="sec-form-parent-code" value="EKO-003">
                    <table class="data-matrix-table" id="sectoral-matrix-table" style="width:100%; border-collapse:separate; border-spacing:0; font-size:12px;">
                        <thead id="sectoral-matrix-thead" style="position:sticky; top:0; z-index:10; background:#f1f5f9;">
                            <!-- Di-generate dinamis oleh JS -->
                        </thead>
                        <tbody id="sectoral-matrix-tbody">
                            <!-- Di-generate dinamis oleh JS -->
                        </tbody>
                    </table>
                </form>
            </div>

            <!-- MODAL FOOTER -->
            <div style="padding:14px 24px; border-top:1px solid var(--line); background:#f8fafc; display:flex; justify-content:space-between; align-items:center; flex-shrink:0;">
                <div id="sec-modal-msg" style="font-size:12px; font-weight:600; color:#15803d; display:flex; align-items:center; gap:6px;">
                    <!-- status note -->
                </div>
                <div style="display:flex; gap:10px; align-items:center;">
                    <button type="button" class="button ghost" id="modal-btn-cancel-sectoral">Batal</button>
                    <button type="button" class="button primary" id="modal-btn-save-sectoral" style="display:inline-flex; align-items:center; gap:6px; font-weight:700;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Simpan Seluruh Sektor
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($catalog_indicators)): ?>
        <datalist id="catalog-code-list">
            <?php foreach ($catalog_indicators as $catItem): ?>
                <option value="<?= htmlspecialchars($catItem['code']) ?>" label="<?= htmlspecialchars($catItem['name']) ?> (<?= htmlspecialchars($catItem['cluster']) ?>)">
            <?php endforeach; ?>
        </datalist>
        <script>
            window.CATALOG_MASTER_LOOKUP = <?= json_encode(array_map(function($ci) {
                return array(
                    'code' => $ci['code'],
                    'name' => $ci['name'],
                    'cluster' => $ci['cluster'],
                    'unit' => $ci['unit'],
                    'source' => $ci['source'],
                    'definition' => $ci['definition'] ?? ''
                );
            }, $catalog_indicators)) ?>;
        </script>
    <?php endif; ?>

</div>
