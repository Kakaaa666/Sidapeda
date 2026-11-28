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

    $grouped = [];
    foreach ($ews as $item) {
        $code = trim($item['code']);
        if (empty($code)) continue;
        $item['sub_indicators'] = [];
        $grouped[$code] = $item;
    }
    $activeCat = '';
    ?>

    <!-- KONFIGURASI AMBANG BATAS & DATA TERVALIDASI -->
    <article class="card threshold-admin">
        <div class="card-head" style="flex-wrap: wrap; gap: 14px;">
            <div style="flex: 1; min-width: 280px;">
                <span class="eyebrow">EARLY WARNING CONFIGURATION &bull; <?= !empty($selected_year) ? "TAHUN {$selected_year}" : "SEMUA PERIODE" ?></span>
                <h3>Konfigurasi Ambang Batas Early Warning System</h3>
                <p>Data indikator &amp; nilai riil bersumber langsung dari data tervalidasi. Anda hanya perlu menetapkan <strong>Ambang Batas</strong> dan <strong>Arah Kondisi Kritis</strong>.</p>
            </div>
            <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                <div id="admin-ews-summary-pills" class="ews-admin-summary-pills">
                    <span class="ews-summary-pill danger" id="admin-ews-kritis-pill">Kritis: <strong>0</strong></span>
                    <span class="ews-summary-pill warning" id="admin-ews-waspada-pill">Waspada: <strong>0</strong></span>
                    <span class="ews-summary-pill success" id="admin-ews-normal-pill">Normal: <strong>0</strong></span>
                </div>
                <span id="cat-filtered-count-label" class="badge info" style="font-size:11.5px; padding:6px 12px; font-weight:700; border-radius:20px;">
                    Total: <?= count($grouped) ?> Indikator
                </span>
                <button class="button primary" id="btn-save-all-thresholds">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Simpan Semua Ambang Batas
                </button>
            </div>
        </div>

        <div class="ews-admin-guide">
            <div class="ews-admin-guide-head">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <strong>Panduan Konfigurasi Ambang Batas EWS</strong>
            </div>
            <div class="ews-admin-guide-grid">
                <div><strong>Standar Kamus Data:</strong> Kode, Nama, Klaster, dan Satuan seragam untuk seluruh wilayah.</div>
                <div><strong>Nilai Riil Tervalidasi:</strong> Bersifat <em>read-only</em>, otomatis diambil dari data menu Validasi Data.</div>
                <div><strong>Ambang Batas &amp; Arah Kritis:</strong> Disesuaikan manual per daerah sesuai target RPJMD / toleransi risiko.</div>
                <div><strong>Sub-Indikator &amp; Rincian:</strong> Klik baris atau tombol hijau pada indikator untuk membuka rincian sub-indikator.</div>
            </div>
        </div>

        <div class="result-table-wrap">
            <table class="result-table threshold-admin-table" style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                <thead>
                    <tr style="display: table-row;">
                        <th style="width: 34%; text-align: left; vertical-align: middle;">Indikator</th>
                        <th style="width: 15%; text-align: right; vertical-align: middle;">Nilai Riil</th>
                        <th style="width: 22%; text-align: left; vertical-align: middle;">Ambang Batas (Editable)</th>
                        <th style="width: 18%; text-align: left; vertical-align: middle;">Arah Kondisi Kritis</th>
                        <th style="width: 11%; text-align: center; vertical-align: middle;">Status Risiko</th>
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
                            $hasSub = false;
                            $badgeCls = ($item['status'] === 'Kritis') ? 'badge danger' : (($item['status'] === 'Waspada') ? 'badge warning' : 'badge success');
                            $itemPrefix = strtoupper(explode('-', $item['code'])[0]);
                            $isMatchCat = true;
                            ?>
                            <tr class="threshold-admin-row matrix-row" data-code="<?= $item['code'] ?>" data-prefix="<?= $itemPrefix ?>" style="display: table-row;">
                                <td class="<?= $hasSub ? 'clickable-indicator-cell' : '' ?>" style="vertical-align: middle; cursor: <?= $hasSub ? 'pointer' : 'default' ?>;" <?= $hasSub ? 'onclick="toggleIndicatorSubRows(\''.htmlspecialchars($item['code']).'\', event);"' : '' ?>>
                                    <div style="display:flex; align-items:flex-start; gap:8px;">
                                        <code style="font-size:11px; font-weight:700; color:var(--blue); background:#eef4fa; padding:3px 7px; border-radius:5px; white-space:nowrap; flex-shrink:0; margin-top:1px;"><?= htmlspecialchars($item['code']) ?></code>
                                        <strong class="<?= $hasSub ? 'indicator-title-clickable' : '' ?>" style="color: var(--navy); font-size: 13px; line-height: 1.38;"><?= htmlspecialchars($item['name']) ?></strong>
                                    </div>
                                    <?php if ($hasSub || (!empty($item['klaster']) && $item['klaster'] !== 'Umum')): ?>
                                        <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap; margin-top:7px;">
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
                                                <button type="button" class="btn-toggle-subcategories" data-parent="<?= $item['code'] ?>" data-label-open="<?= $openLabel ?>" onclick="toggleIndicatorSubRows('<?= htmlspecialchars($item['code']) ?>', event);" title="Klik untuk membuka/menutup <?= $openLabel ?>">
                                                    <svg class="sub-arrow-icon" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                                                    <span class="sub-toggle-label"><?= $openLabel ?></span>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (!empty($item['klaster']) && $item['klaster'] !== 'Umum'): ?>
                                                <span class="badge secondary" style="font-size: 11px; padding:2px 8px; font-weight:600;"><?= htmlspecialchars($item['klaster']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="vertical-align: middle; text-align: right;">
                                    <?php
                                        $isRupiah = is_indicator_rupiah($item['unit'] ?? '', $item['code'] ?? '');
                                        $unitHasRp = (bool)preg_match('/^rp/i', trim((string)($item['unit'] ?? '')));
                                        $prefixRp = ($isRupiah && !$unitHasRp) ? 'Rp ' : '';
                                        $formattedVal = format_indicator_value($item['value'], $item['unit'] ?? '', $item['code'] ?? '', false);
                                        $formattedTh = format_indicator_value($item['threshold'], $item['unit'] ?? '', $item['code'] ?? '', false);
                                    ?>
                                    <div style="display: inline-flex; flex-direction: column; align-items: flex-end; gap: 2px;">
                                        <strong style="font-size: 13px; color: var(--navy); letter-spacing: -0.2px; white-space: nowrap;">
                                            <?= $prefixRp ?><?= $formattedVal ?>
                                        </strong>
                                        <span class="ews-unit-pill"><?= htmlspecialchars($item['unit']) ?></span>
                                    </div>
                                    <input type="hidden" class="value-input" data-code="<?= $item['code'] ?>" value="<?= $item['value'] ?>">
                                </td>
                                <td style="vertical-align: middle;">
                                    <div class="ews-threshold-field">
                                        <?php if ($isRupiah && !$unitHasRp): ?>
                                            <span class="ews-rp-prefix">Rp</span>
                                        <?php endif; ?>
                                        <input type="text" inputmode="decimal" class="threshold-input <?= $isRupiah ? 'is-rupiah-mask' : '' ?>" data-code="<?= $item['code'] ?>" value="<?= $formattedTh ?>">
                                        <span class="ews-unit-pill"><?= htmlspecialchars($item['unit']) ?></span>
                                    </div>
                                </td>
                                <td style="vertical-align: middle;">
                                    <select class="direction-input" data-code="<?= $item['code'] ?>">
                                        <option value="above" <?= ($item['direction'] === 'above') ? 'selected' : '' ?>>Kritis jika &gt; Ambang (Beban / Risiko)</option>
                                        <option value="below" <?= ($item['direction'] === 'below') ? 'selected' : '' ?>>Kritis jika &lt; Ambang (Target / Kinerja)</option>
                                    </select>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <span id="status-badge-<?= $item['code'] ?>" class="<?= $badgeCls ?> ews-status-pill">
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
                                            $cleanSubName = preg_replace('/^Harga:\s*/i', '', $sub['name']);
                                        } else {
                                            $cleanSubName = preg_replace('/^(PDRB\s+(ADHB|ADHK)|Pertumbuhan(\s+PDRB(\s+ADHK)?)?|Distribusi(\s+PDRB(\s+ADHB)?)?|Kontribusi|PDRB\s+Pengeluaran\s+(ADHB|ADHK)|Pengeluaran\s+(ADHB|ADHK)):\s*/i', '', $sub['name']);
                                        }
                                        $hrgMeta = ($item['code'] === 'HRG-005') ? get_hrg005_excel_row_meta($dispKey) : array();
                                    ?>
                                    <?php if (!empty($hrgMeta['group_header'])): ?>
                                        <?php $gH = $hrgMeta['group_header']; ?>
                                        <tr class="threshold-admin-row matrix-row matrix-sub-row hrg-group-header-row sub-row-HRG-005" data-parent="HRG-005" data-prefix="HRG" style="display:none;">
                                            <td colspan="5">
                                                <span style="display:inline-flex; align-items:center; gap:8px;">
                                                    <span class="hrg-group-num-pill"><?= $gH['no'] ?></span>
                                                    <span><?= htmlspecialchars($gH['name']) ?></span>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($hrgMeta['sub_header'])): ?>
                                        <tr class="threshold-admin-row matrix-row matrix-sub-row hrg-subheader-row sub-row-HRG-005" data-parent="HRG-005" data-prefix="HRG" style="display:none;">
                                            <td colspan="5">
                                                <?= htmlspecialchars($hrgMeta['sub_header']) ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr class="threshold-admin-row matrix-row matrix-sub-row sub-row-<?= $item['code'] ?>" data-code="<?= $sub['code'] ?>" data-parent="<?= $item['code'] ?>" data-prefix="<?= strtoupper(explode('-', $item['code'])[0]) ?>" style="display:none;">
                                        <td class="ews-admin-sub-cell" style="vertical-align: middle;">
                                            <div class="ews-sub-chips">
                                                <span class="risk-sub-branch">└──</span>
                                                <?php if (!empty($hrgMeta['direct_no'])): ?>
                                                    <span class="hrg-group-num-pill"><?= $hrgMeta['direct_no'] ?></span>
                                                <?php endif; ?>
                                                <code class="risk-sub-code"><?= htmlspecialchars($sub['code']) ?></code>
                                                <span class="risk-sub-key"><?= htmlspecialchars($dispKey) ?></span>
                                            </div>
                                            <div class="ews-sub-name"><?= htmlspecialchars($cleanSubName) ?></div>
                                        </td>
                                        <td style="vertical-align: middle; text-align: right;">
                                            <?php
                                                $subIsRupiah = is_indicator_rupiah($sub['unit'] ?? '', $sub['code'] ?? '');
                                                $subPrefixRp = $subIsRupiah ? 'Rp ' : '';
                                                $subFormattedVal = format_indicator_value($sub['value'], $sub['unit'] ?? '', $sub['code'] ?? '', false);
                                                $subFormattedTh = format_indicator_value($sub['threshold'], $sub['unit'] ?? '', $sub['code'] ?? '', false);
                                            ?>
                                            <div style="display: inline-flex; flex-direction: column; align-items: flex-end; gap: 2px;">
                                                <strong style="font-size: 12.5px; color: var(--navy); letter-spacing: -0.2px; white-space: nowrap;">
                                                    <?= $subPrefixRp ?><?= $subFormattedVal ?>
                                                </strong>
                                                <span class="ews-unit-pill"><?= htmlspecialchars($sub['unit']) ?></span>
                                            </div>
                                            <input type="hidden" class="value-input" data-code="<?= $sub['code'] ?>" value="<?= $sub['value'] ?>">
                                        </td>
                                        <td style="vertical-align: middle;">
                                            <div class="ews-threshold-field is-sub">
                                                <?php if ($subIsRupiah): ?>
                                                    <span class="ews-rp-prefix">Rp</span>
                                                <?php endif; ?>
                                                <input type="text" inputmode="decimal" class="threshold-input <?= $subIsRupiah ? 'is-rupiah-mask' : '' ?>" data-code="<?= $sub['code'] ?>" value="<?= $subFormattedTh ?>">
                                                <span class="ews-unit-pill"><?= htmlspecialchars($sub['unit']) ?></span>
                                            </div>
                                        </td>
                                        <td style="vertical-align: middle;">
                                            <select class="direction-input is-sub" data-code="<?= $sub['code'] ?>">
                                                <option value="above" <?= ($sub['direction'] === 'above') ? 'selected' : '' ?>>Kritis jika &gt; Ambang (Beban / Risiko)</option>
                                                <option value="below" <?= ($sub['direction'] === 'below') ? 'selected' : '' ?>>Kritis jika &lt; Ambang (Target / Kinerja)</option>
                                            </select>
                                        </td>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <span id="status-badge-<?= $sub['code'] ?>" class="<?= $subBadgeCls ?> ews-status-pill is-sub">
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
