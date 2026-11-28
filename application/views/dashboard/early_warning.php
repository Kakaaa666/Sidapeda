<?php
// Grouping logic untuk Indikator Utama dan Sub-Indikator Sektoral (A s.d. U)
$sectorOrder = array('A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6, 'G' => 7, 'H' => 8, 'I' => 9, 'J' => 10, 'K' => 11, 'L' => 12, 'MN' => 13, 'O' => 14, 'P' => 15, 'Q' => 16, 'RSTU' => 17, 'TM' => 18);
$quarterOrder = array('Q1' => 1, 'Q2' => 2, 'Q3' => 3, 'Q4' => 4);
$sectoralParents = array(
    'EKO-003', 'EKO-004', 'EKO-006', 'EKO-007', 
    'INV-001', 'INV-002', 'INV-003', 
    'TK-007', 'TK-008', 'TK-010',
    'PAR-001', 'PAR-002', 'PAR-003', 'PAR-004', 'PAR-005', 'PAR-006',
    'PRO-001', 'PRO-002', 'PRO-004',
    'FIS-001', 'FIS-002', 'FIS-003',
    'HRG-001', 'HRG-002', 'HRG-003', 'HRG-004', 'HRG-005', 'HRG-006'
);
$monthOrder = array('JAN' => 1, 'FEB' => 2, 'MAR' => 3, 'APR' => 4, 'MEI' => 5, 'JUN' => 6, 'JUL' => 7, 'AGS' => 8, 'SEP' => 9, 'OKT' => 10, 'NOV' => 11, 'DES' => 12);
$indicatorPrefixOrder = array(
    'EKO' => 1, 'NAK' => 2, 'TK' => 3, 'INV' => 4,
    'HRG' => 5, 'KES' => 6, 'PAR' => 7, 'PRO' => 8,
    'FIS' => 9, 'GEO' => 10, 'EWS' => 11, 'ANA' => 12
);

$grouped = array();
foreach ($ews as $item) {
    $code = $item['code'] ?? $item['kode_indikator'] ?? '';
    if (empty($code)) continue;
    $item['sub_indicators'] = array();
    $grouped[$code] = $item;
}
$activeCat = '';
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
                    <span>Indikator</span>
                    <span style="text-align:right;">Nilai</span>
                    <span style="text-align:right;">Threshold</span>
                    <span style="text-align:right;">Status</span>
                </div>
                <?php foreach ($grouped as $code => $item):
                    $hasSub = false;
                    $st = $item['status'] ?? $item['status_risiko'] ?? 'Normal';
                    $badgeCls = ($st === 'Kritis') ? 'danger' : (($st === 'Waspada') ? 'warning' : 'success');
                    $rowStateCls = ($st === 'Kritis') ? 'alerted' : (($st === 'Waspada') ? 'warning-row' : 'normal-row');
                    $name = $item['name'] ?? $item['nama_indikator'] ?? '';
                    $src = $item['source'] ?? $item['sumber_data'] ?? $code;
                    $val = (float)($item['value'] ?? $item['nilai_terkini'] ?? $item['currentValue'] ?? 0);
                    $th = (float)($item['threshold'] ?? $item['ambang_batas'] ?? 0);
                    $unit = $item['unit'] ?? $item['satuan'] ?? '%';
                    $dir = strtolower($item['direction'] ?? $item['arah_risiko'] ?? 'above');
                    $rowYr = $item['year'] ?? $item['tahun'] ?? null;
                    $cl = $item['klaster'] ?? 'Umum';
                    $pPrefix = strtoupper(explode('-', $code)[0]);
                ?>
                    <div class="risk-row btn-alert-row <?= $rowStateCls ?>" 
                         data-code="<?= htmlspecialchars($code) ?>" 
                         data-prefix="<?= $pPrefix ?>" 
                         data-status="<?= htmlspecialchars($st) ?>" 
                         data-category="<?= htmlspecialchars($cl) ?>" 
                         role="button" tabindex="0" 
                         title="Klik untuk analisis &amp; penyesuaian threshold"
                         style="display: grid;">
                        <div class="risk-indicator">
                            <strong class="risk-title"><?= htmlspecialchars($name) ?></strong>
                            <small class="risk-meta">
                                <span><?= htmlspecialchars($src) ?></span>
                                <span class="risk-meta-rule">&bull; <?= ($dir === 'above') ? 'Risiko jika melebihi ambang' : 'Risiko jika di bawah ambang' ?></span>
                                <?php if (!empty($rowYr)): ?>
                                    <span class="risk-meta-year">&bull; Data <?= $rowYr ?></span>
                                <?php endif; ?>
                            </small>
                        </div>
                        <?php
                            $isRupiah = is_indicator_rupiah($unit ?? '', $item['code'] ?? '');
                            $unitHasRp = (bool)preg_match('/^rp/i', trim((string)$unit));
                            $prefixRp = ($isRupiah && !$unitHasRp) ? 'Rp ' : '';
                            $formattedVal = format_indicator_value($val, $unit ?? '', $item['code'] ?? '', false);
                            $formattedTh = format_indicator_value($th, $unit ?? '', $item['code'] ?? '', false);
                        ?>
                        <div class="risk-value">
                            <span class="risk-mobile-label">Nilai</span>
                            <strong><?= $prefixRp ?><?= $formattedVal ?> <span class="risk-unit-text"><?= htmlspecialchars($unit) ?></span></strong>
                        </div>
                        <div class="risk-threshold">
                            <span class="risk-mobile-label">Threshold</span>
                            <strong><?= $prefixRp ?><?= $formattedTh ?> <span class="risk-unit-text"><?= htmlspecialchars($unit) ?></span></strong>
                        </div>
                        <div class="risk-status">
                            <span class="badge <?= $badgeCls ?>"><?= htmlspecialchars($st) ?></span>
                        </div>
                    </div>

                    <!-- SUB-ROWS UNTUK RINCIAN SEKTORAL (A s.d. U) / KUARTAL (Q1 s.d. Q4) -->
                    <?php if ($hasSub): ?>
                        <?php foreach ($item['sub_indicators'] as $subCode => $sub):
                            $subSt = $sub['status'] ?? $sub['status_risiko'] ?? 'Normal';
                            $subBadgeCls = ($subSt === 'Kritis') ? 'danger' : (($subSt === 'Waspada') ? 'warning' : 'success');
                            $subRowStateCls = ($subSt === 'Kritis') ? 'alerted' : (($subSt === 'Waspada') ? 'warning-row' : 'normal-row');
                            $subVal = (float)($sub['value'] ?? $sub['nilai_terkini'] ?? 0);
                            $subTh = (float)($sub['threshold'] ?? $sub['ambang_batas'] ?? 0);
                            $subUnit = $sub['unit'] ?? $sub['satuan'] ?? '%';
                            $dispKey = $sub['sub_key'] ?? '';
                            $isSubInv = (strpos($code, 'INV-') === 0);
                            if ($dispKey === 'MN') $dispKey = 'M,N';
                            elseif ($dispKey === 'RSTU') $dispKey = 'R,S,T,U';
                            if ($isSubInv) {
                                $cleanSubName = 'Kuartal ' . substr($dispKey, 1) . ' (' . $dispKey . ')';
                                $subTypeDesc = 'Kuartal ' . htmlspecialchars($dispKey);
                            } elseif ($code === 'HRG-005') {
                                $cleanSubName = preg_replace('/^Harga:\s*/i', '', $sub['name'] ?? '');
                                $subTypeDesc = 'Komoditas ' . htmlspecialchars($dispKey);
                            } else {
                                $cleanSubName = preg_replace('/^(PDRB\s+(ADHB|ADHK)|Pertumbuhan(\s+PDRB(\s+ADHK)?)?|Distribusi(\s+PDRB(\s+ADHB)?)?|Kontribusi|PDRB\s+Pengeluaran\s+(ADHB|ADHK)|Pengeluaran\s+(ADHB|ADHK)):\s*/i', '', $sub['name'] ?? '');
                                $subTypeDesc = 'Sektor ' . htmlspecialchars($dispKey);
                            }
                            $hrgMeta = ($code === 'HRG-005') ? get_hrg005_excel_row_meta($dispKey) : array();
                        ?>
                            <?php if (!empty($hrgMeta['group_header'])): ?>
                                <?php $gH = $hrgMeta['group_header']; ?>
                                <div class="risk-row risk-sub-row risk-sub-group-header sub-row-HRG-005" data-parent="HRG-005" data-prefix="HRG" style="display:none;">
                                    <div style="grid-column: 1 / -1; display:flex; align-items:center; gap:8px; font-weight:800; font-size:12px; color:#0f172a; text-transform:uppercase;">
                                        <span class="hrg-group-num-pill"><?= $gH['no'] ?></span>
                                        <span><?= htmlspecialchars($gH['name']) ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($hrgMeta['sub_header'])): ?>
                                <div class="risk-row risk-sub-row risk-sub-subheader sub-row-HRG-005" data-parent="HRG-005" data-prefix="HRG" style="display:none;">
                                    <div style="grid-column: 1 / -1; font-weight:700; font-style:italic; font-size:11.5px; color:#334155;">
                                        <?= htmlspecialchars($hrgMeta['sub_header']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="risk-row btn-alert-row risk-sub-row sub-row-<?= htmlspecialchars($code) ?> <?= $subRowStateCls ?>" 
                                 data-code="<?= htmlspecialchars($subCode) ?>" 
                                 data-parent="<?= htmlspecialchars($code) ?>" 
                                 data-prefix="<?= $pPrefix ?>" 
                                 data-status="<?= htmlspecialchars($subSt) ?>" 
                                 data-category="<?= htmlspecialchars($cl) ?>" 
                                 style="display:none;" 
                                 role="button" tabindex="0" title="Klik untuk analisis & penyesuaian threshold sub-indikator <?= htmlspecialchars($subCode) ?>">
                                <div class="risk-indicator risk-sub-indicator">
                                    <div class="risk-sub-chips">
                                        <span class="risk-sub-branch">└──</span>
                                        <?php if (!empty($hrgMeta['direct_no'])): ?>
                                            <span class="hrg-group-num-pill"><?= $hrgMeta['direct_no'] ?></span>
                                        <?php endif; ?>
                                        <code class="risk-sub-code"><?= htmlspecialchars($subCode) ?></code>
                                        <span class="risk-sub-key"><?= htmlspecialchars($dispKey) ?></span>
                                    </div>
                                    <strong class="risk-sub-title"><?= htmlspecialchars($cleanSubName) ?></strong>
                                    <small class="risk-meta"><?= $subTypeDesc ?> &bull; <?= ($sub['direction'] ?? 'above') === 'above' ? 'Kritis jika &gt; Ambang' : 'Kritis jika &lt; Ambang' ?></small>
                                </div>
                                <?php
                                    $subIsRupiah = is_indicator_rupiah($subUnit ?? '', $subCode ?? '');
                                    $subPrefixRp = $subIsRupiah ? 'Rp ' : '';
                                    $subFormattedVal = format_indicator_value($subVal, $subUnit ?? '', $subCode ?? '', false);
                                    $subFormattedTh = format_indicator_value($subTh, $subUnit ?? '', $subCode ?? '', false);
                                ?>
                                <div class="risk-value">
                                    <span class="risk-mobile-label">Nilai Riil</span>
                                    <strong><?= $subPrefixRp ?><?= $subFormattedVal ?> <span class="risk-unit-text"><?= htmlspecialchars($subUnit) ?></span></strong>
                                </div>
                                <div class="risk-threshold">
                                    <span class="risk-mobile-label">Batas Ambang</span>
                                    <strong><?= $subPrefixRp ?><?= $subFormattedTh ?> <span class="risk-unit-text"><?= htmlspecialchars($subUnit) ?></span></strong>
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
                • <strong>Normal</strong>: Nilai berada di sisi aman (tidak melampaui threshold).<br>
                • <strong>Waspada</strong>: Nilai menyentuh atau melampaui threshold dengan deviasi &lt; 10%.<br>
                • <strong>Kritis</strong>: Nilai melampaui threshold dengan deviasi &ge; 10% (memerlukan intervensi).
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

            <div id="ews-comparison-chart-container" class="chart-wrap dynamic-chart" style="height:auto; min-height:140px; margin-bottom:16px;">
                <!-- Rendered dynamically by JS -->
            </div>

            <div class="risk-dist-section-head">
                <strong>Tingkat Jarak Terhadap Ambang</strong>
                <small>Rasio kedekatan indikator pada kategori aktif</small>
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
                    <span id="modal-live-preview-badge" class="badge danger" style="font-size: 12.5px;">Status: KRITIS</span>
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

                <small style="display:block; color:var(--muted); font-size: 12.5px; line-height:1.4;">
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
