<div class="page comparison-page">
    <div class="module-intro">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions" style="display:flex; align-items:center; gap:8px;">
            <button class="button secondary" id="btn-export-comparison">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12M7 10l5 5 5-5M4 21h16"/></svg>
                <span>Ekspor CSV</span>
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



    <!-- COMPARISON BUILDER -->
    <section class="comparison-builder">
        <div class="builder-heading">
            <div>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16l-6 7v6l-4 2v-8L4 5Z"/></svg>
                <div>
                    <strong>Konfigurasi Perbandingan Daerah</strong>
                    <small>Dataset pertumbuhan ekonomi Jawa Timur & Kabupaten/Kota</small>
                </div>
            </div>
            <span>Periode Data: 2015–2024</span>
        </div>
        <div class="builder-grid">
            <label>
                <span>Indikator regional</span>
                <select disabled><option>Pertumbuhan ekonomi (PDRB ADHK)</option></select>
            </label>
            <label>
                <span>Rentang periode</span>
                <select id="comp-period">
                    <option value="10">2015–2024 (10 Tahun)</option>
                    <option value="5">2020–2024 (5 Tahun Terakhir)</option>
                </select>
            </label>
            <label>
                <span>Benchmark tren</span>
                <select disabled><option>Jawa Timur dan Nasional</option></select>
            </label>
            <button class="button secondary" id="btn-reset-regions">Reset Wilayah</button>
        </div>

        <div class="selected-regions selected-regions-bar">
            <span class="regions-bar-label">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/><circle cx="12" cy="10" r="3"/></svg>
                <span>Wilayah dalam ranking:</span>
            </span>

            <!-- Dropdown Multiselect Checkbox dengan Pencarian -->
            <div class="region-multiselect" id="region-multiselect">
                <button type="button" class="region-dropdown-btn" id="btn-toggle-region-dropdown" onclick="toggleRegionDropdown(event)" aria-haspopup="true" aria-expanded="false">
                    <span class="dropdown-btn-text" id="region-dropdown-label">Semua Wilayah Terpilih (<?= count($all_active_regional) ?>)</span>
                    <svg class="dropdown-chevron-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                </button>

                <div class="region-dropdown-panel" id="region-dropdown-panel" style="display:none;">
                    <!-- Input Pencarian Wilayah -->
                    <div class="region-search-box">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" id="region-search-input" placeholder="Cari nama kabupaten / kota..." autocomplete="off">
                        <button type="button" id="btn-clear-region-search" class="clear-search-btn" style="display:none;" title="Bersihkan pencarian">&times;</button>
                    </div>

                    <!-- Header Aksi Cepat & Counter -->
                    <div class="region-dropdown-header">
                        <div class="dropdown-quick-actions">
                            <button type="button" id="btn-select-all-regions" class="quick-link">Pilih Semua</button>
                            <span class="dot-sep">•</span>
                            <button type="button" id="btn-deselect-all-regions" class="quick-link">Hapus Semua</button>
                        </div>
                        <span class="region-selected-counter" id="region-selected-counter"><?= count($all_active_regional) ?> / <?= count($all_active_regional) ?> dipilih</span>
                    </div>

                    <!-- Daftar Pilihan Checkbox -->
                    <div class="region-options-list" id="region-options-list">
                        <?php foreach ($all_active_regional as $r): ?>
                            <label class="region-option-item" data-name="<?= strtolower(htmlspecialchars($r['name'])) ?>">
                                <input type="checkbox" class="region-cb" value="<?= htmlspecialchars($r['name']) ?>" checked <?= $r['name'] === 'Kab. Banyuwangi' ? 'disabled' : '' ?>>
                                <span class="option-name"><?= htmlspecialchars($r['name']) ?></span>
                                <?php if ($r['name'] === 'Kab. Banyuwangi'): ?>
                                    <span class="badge info main-badge" title="Wilayah acuan utama">Utama</span>
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                        <div id="region-search-not-found" class="region-not-found" style="display:none;">
                            Wilayah tidak ditemukan
                        </div>
                    </div>
                </div>
            </div>

            <script>
            function toggleRegionDropdown(e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                var panel = document.getElementById('region-dropdown-panel');
                var btn = document.getElementById('btn-toggle-region-dropdown');
                var search = document.getElementById('region-search-input');
                if (!panel) return;
                var isHidden = (panel.style.display === 'none' || !panel.style.display || panel.style.display === '');
                panel.style.display = isHidden ? 'block' : 'none';
                if (btn) {
                    btn.classList.toggle('active', isHidden);
                    btn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
                }
                if (isHidden && search) {
                    setTimeout(function() { search.focus(); }, 60);
                }
            }
            </script>
        </div>
    </section>

    <!-- CONTEXT BANNER -->
    <div class="comparison-context">
        <div>
            <span class="pulse"></span>
            <p>Menampilkan analisis perbandingan untuk <strong id="comp-indicator-name">Pertumbuhan Ekonomi</strong>, periode <strong id="comp-period-label">2015–2024</strong>.</p>
        </div>
    </div>

    <?php
    $regCount = count($all_active_regional) ?: 1;
    $sumVal = array_sum(array_column($all_active_regional, 'value'));
    $peerAvg = round($sumVal / $regCount, 2);
    $bwiItem = null;
    $bwiRank = 1;
    foreach ($all_active_regional as $idx => $item) {
        if ($item['name'] === 'Kab. Banyuwangi') {
            $bwiItem = $item;
            $bwiRank = $idx + 1;
            break;
        }
    }
    $bwiVal = $bwiItem ? $bwiItem['value'] : 5.18;
    $bwiGap = round($bwiVal - $peerAvg, 2);
    ?>

    <!-- KPIS -->
    <div class="comparison-kpis" id="comp-kpi-container">
        <div class="comparison-kpi">
            <div><span>Peringkat Banyuwangi</span><span class="badge success">Posisi #<?= $bwiRank ?></span></div>
            <strong>#<?= $bwiRank ?></strong>
            <small>dari <?= $regCount ?> wilayah aktif</small>
        </div>
        <div class="comparison-kpi">
            <div><span>Rata-rata Banyuwangi</span><span class="badge info">Periode 2024</span></div>
            <strong><?= number_format($bwiVal, 2, ',', '.') ?>%</strong>
            <small>pertumbuhan ekonomi</small>
        </div>
        <div class="comparison-kpi">
            <div><span>Rerata Wilayah Pembanding</span><span class="badge neutral">Benchmark</span></div>
            <strong><?= number_format($peerAvg, 2, ',', '.') ?>%</strong>
            <small><?= $regCount ?> kabupaten/kota</small>
        </div>
        <div class="comparison-kpi">
            <div><span>Gap terhadap Rerata</span><span class="badge <?= $bwiGap >= 0 ? 'success' : 'warning' ?>">Selisih</span></div>
            <strong class="<?= $bwiGap >= 0 ? 'positive' : 'negative' ?>"><?= ($bwiGap >= 0 ? '+' : '') . number_format($bwiGap, 2, ',', '.') ?> pp</strong>
            <small><?= $bwiGap >= 0 ? 'Lebih tinggi' : 'Lebih rendah' ?> dari rerata</small>
        </div>
    </div>

    <!-- WORKSPACE TABS -->
    <section class="comparison-workspace">
        <div class="feature-tabs" id="comp-feature-tabs">
            <button class="active" data-tab="Regional Ranking">Regional Ranking</button>
            <button data-tab="Benchmarking">Benchmarking</button>
            <button data-tab="Gap Analysis">Gap Analysis</button>
            <button data-tab="Regional Profiling">Regional Profiling</button>
            <button data-tab="Tipologi Klassen">Tipologi Klassen</button>
            <button data-tab="Peer Comparison">Peer Comparison</button>
        </div>

        <article class="card feature-output" style="grid-column: 1 / 3;">
            <div class="card-head">
                <div>
                    <span class="eyebrow" id="comp-tab-eyebrow">REGIONAL RANKING</span>
                    <h3 id="comp-tab-title">Peringkat rata-rata pertumbuhan ekonomi</h3>
                    <p id="comp-tab-desc">Peringkat berdasarkan rata-rata pertumbuhan tahunan selama periode terpilih.</p>
                </div>
                <div class="output-actions">
                    <span class="badge info" id="comp-tab-period-badge">2015–2024</span>
                </div>
            </div>

            <!-- TAB CONTENT CONTAINER -->
            <div id="comp-tab-content">
                <div style="margin-bottom:20px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <span class="eyebrow">VISUALISASI PERINGKAT KOMPARASI</span>
                        <span class="badge info">5 Wilayah Terpilih</span>
                    </div>
                    <div style="display:grid; gap:8px; padding:12px; background:#f8fafc; border-radius:8px; border:1px solid var(--line);">
                        <?php
                        $maxVal = max(array_column($all_active_regional, 'value')) ?: 6.0;
                        foreach ($all_active_regional as $idx => $r):
                            $isBwi = ($r['name'] === 'Kab. Banyuwangi');
                            $pct = round(($r['value'] / $maxVal) * 100);
                        ?>
                            <div style="display:grid; grid-template-columns: 140px 1fr 60px; align-items:center; gap:10px; font-size:9px;">
                                <span style="font-weight:<?= $isBwi ? '700' : '500' ?>; color:<?= $isBwi ? 'var(--blue)' : 'inherit' ?>;">
                                    #<?= $idx + 1 ?> <?= htmlspecialchars($r['name']) ?>
                                </span>
                                <div style="height:10px; border-radius:5px; background:#e2e8f0; overflow:hidden;">
                                    <div style="width:<?= $pct ?>%; height:100%; background:<?= $isBwi ? 'var(--blue)' : 'var(--teal)' ?>;"></div>
                                </div>
                                <strong style="text-align:right;"><?= number_format($r['value'], 2, ',', '.') ?>%</strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="comparison-table-head" style="grid-template-columns: 50px 1.4fr .8fr .8fr .8fr;">
                    <span>Peringkat</span>
                    <span>Wilayah</span>
                    <span>Pertumbuhan</span>
                    <span>Perubahan Tahunan</span>
                    <span>Gap ke Rerata</span>
                </div>
                <div class="comparison-ranking">
                    <?php foreach ($all_active_regional as $idx => $r):
                        $isBwi = ($r['name'] === 'Kab. Banyuwangi');
                        $diff = round($r['value'] - $peerAvg, 2);
                    ?>
                        <div class="comparison-table-row <?= $isBwi ? 'selected' : '' ?>" style="grid-template-columns: 50px 1.4fr .8fr .8fr .8fr;">
                            <b><?= $idx + 1 ?></b>
                            <span>
                                <i class="region-avatar"><?= strtoupper(substr(str_replace(['Kab. ', 'Kota '], '', $r['name']), 0, 2)) ?></i>
                                <strong><?= htmlspecialchars($r['name']) ?></strong>
                            </span>
                            <strong><?= number_format($r['value'], 2, ',', '.') ?>%</strong>
                            <small class="<?= $r['change'] >= 0 ? 'positive' : 'negative' ?>"><?= ($r['change'] >= 0 ? '+' : '') . number_format($r['change'], 2, ',', '.') ?> pp</small>
                            <span class="badge <?= $diff >= 0 ? 'success' : 'warning' ?>"><?= ($diff >= 0 ? '+' : '') . number_format($diff, 2, ',', '.') ?> pp</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="source">Sumber nilai: BPS Provinsi Jawa Timur & Dataset Internal SI-DAPEDA</div>
        </article>
    </section>


</div>

<script>
    window.__REGIONAL_DATA__ = <?= json_encode($all_active_regional, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    window.__SERIES_DATA__ = <?= json_encode($series, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    window.__SECTORS_DATA__ = <?= json_encode($sectors, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>
