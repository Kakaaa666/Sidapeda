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



    <!-- WORKSPACE WITH ANALYSIS PANEL -->
    <div class="workspace analysis-workspace">
        <!-- SIDEBAR PANEL -->
        <aside class="analysis-panel">
            <div class="panel-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16l-6 7v6l-4 2v-8L4 5Z"/></svg>
                <strong>Menu Analisa Ekonomi</strong>
            </div>

            <div class="analysis-menu-group" style="margin-top: 14px;">
                <span class="eyebrow" style="display:block; font-size:10.5px; font-weight:700; color:#64748b; letter-spacing:0.06em; margin-bottom:8px; padding-left:4px;">FUNGSI PEMBANDING</span>
                <a href="<?= site_url('perbandingan_daerah') ?>" class="analysis-link active" style="text-decoration:none; display:flex; align-items:center; justify-content:space-between;">
                    <span>Perbandingan Antar Daerah</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            </div>

            <div class="divider" style="margin: 14px 0;"></div>

            <div class="analysis-menu-group">
                <span class="eyebrow" style="display:block; font-size:10.5px; font-weight:700; color:#64748b; letter-spacing:0.06em; margin-bottom:8px; padding-left:4px;">METODE ANALISIS REGIONAL</span>
                <div id="analysis-menu-links">
                    <a href="<?= site_url('analisis_ekonomi?mode=lq') ?>" class="analysis-link" style="text-decoration:none; display:flex; align-items:center; justify-content:space-between;">
                        <span>Location Quotient (LQ)</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                    <a href="<?= site_url('analisis_ekonomi?mode=shift-share') ?>" class="analysis-link" style="text-decoration:none; display:flex; align-items:center; justify-content:space-between;">
                        <span>Shift Share Analysis</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                    <a href="<?= site_url('analisis_ekonomi?mode=klassen') ?>" class="analysis-link" style="text-decoration:none; display:flex; align-items:center; justify-content:space-between;">
                        <span>Tipologi Klassen</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                    <a href="<?= site_url('analisis_ekonomi?mode=korelasi') ?>" class="analysis-link" style="text-decoration:none; display:flex; align-items:center; justify-content:space-between;">
                        <span>Analisis Korelasi</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                </div>
            </div>

            <div class="divider" style="margin: 14px 0;"></div>
            <span class="eyebrow">DATASET DIGUNAKAN</span>
            <p class="panel-help">Dataset pertumbuhan ekonomi Jawa Timur dan perbandingan Kabupaten/Kota 2015–2024.</p>
        </aside>

        <!-- MAIN COMPARISON CONTENT -->
        <div class="comparison-main-column" style="min-width: 0;">
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
                    <span class="dropdown-btn-text" id="region-dropdown-label">Pilih Wilayah (0 dipilih)</span>
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
                        <span class="region-selected-counter" id="region-selected-counter">0 / <?= count($all_active_regional) ?> dipilih</span>
                    </div>

                    <!-- Daftar Pilihan Checkbox -->
                    <div class="region-options-list" id="region-options-list">
                        <?php foreach ($all_active_regional as $r): ?>
                            <label class="region-option-item" data-name="<?= strtolower(htmlspecialchars($r['name'])) ?>">
                                <input type="checkbox" class="region-cb" value="<?= htmlspecialchars($r['name']) ?>">
                                <span class="option-name"><?= htmlspecialchars($r['name']) ?></span>
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

    <!-- KPIS -->
    <div class="comparison-kpis" id="comp-kpi-container">
        <div class="comparison-kpi">
            <div><span>Wilayah Terpilih</span><span class="badge neutral">0 Daerah</span></div>
            <strong>0</strong>
            <small>Belum ada wilayah dipilih</small>
        </div>
        <div class="comparison-kpi">
            <div><span>Rata-rata Pertumbuhan</span><span class="badge neutral">—</span></div>
            <strong>—</strong>
            <small>Pilih wilayah pembanding</small>
        </div>
        <div class="comparison-kpi">
            <div><span>Pertumbuhan Tertinggi</span><span class="badge neutral">—</span></div>
            <strong>—</strong>
            <small>Pilih wilayah pembanding</small>
        </div>
        <div class="comparison-kpi">
            <div><span>Disparitas Laju</span><span class="badge neutral">—</span></div>
            <strong>—</strong>
            <small>Pilih wilayah pembanding</small>
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
                <div class="empty-state" style="padding: 48px 24px; text-align: center; background: #f8fafc; border: 1px dashed var(--line); border-radius: 12px; margin: 16px 0;">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--muted); margin-bottom: 12px;"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/><circle cx="12" cy="10" r="3"/></svg>
                    <h4 style="font-size: 14px; font-weight: 700; color: var(--navy); margin: 0 0 6px 0;">Tidak Ada Wilayah yang Dipilih</h4>
                    <p style="font-size: 11px; color: var(--muted); max-width: 440px; margin: 0 auto 16px;">Silakan pilih satu atau lebih kabupaten/kota pada menu dropdown wilayah di atas untuk menampilkan analisis perbandingan dan ranking data.</p>
                </div>
            </div>

            <div class="source">Sumber nilai: BPS Provinsi Jawa Timur & Dataset Internal SI-DAPEDA</div>
        </article>
    </section>

    <!-- ECONOMIC AI ASSISTANT COMPONENT -->
    <?php $this->load->view('dashboard/ai_assistant'); ?>

        </div><!-- /.comparison-main-column -->
    </div><!-- /.analysis-workspace -->

</div>

<script>
    window.__REGIONAL_DATA__ = <?= json_encode($all_active_regional, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    window.__SERIES_DATA__ = <?= json_encode($series, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    window.__SECTORS_DATA__ = <?= json_encode($sectors, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>
