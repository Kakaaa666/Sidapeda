<div class="page comparison-page">
    <div class="module-intro">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions">
            <span class="badge simulation">DATABASE CI 3</span>
            <button class="button secondary" id="btn-export-comparison">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12M7 10l5 5 5-5M4 21h16"/></svg>
                <span>Ekspor CSV</span>
            </button>
        </div>
    </div>

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

        <div class="selected-regions">
            <span>Wilayah dalam ranking:</span>
            <?php foreach ($regional as $r): ?>
                <label class="region-toggle">
                    <input type="checkbox" class="region-cb" value="<?= htmlspecialchars($r['name']) ?>" checked <?= $r['name'] === 'Kab. Banyuwangi' ? 'disabled' : '' ?>>
                    <?= htmlspecialchars($r['name']) ?>
                </label>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- CONTEXT BANNER -->
    <div class="comparison-context">
        <div>
            <span class="pulse"></span>
            <p>Menampilkan analisis perbandingan untuk <strong id="comp-indicator-name">Pertumbuhan Ekonomi</strong>, periode <strong id="comp-period-label">2015–2024</strong>.</p>
        </div>
        <span class="badge simulation">DATABASE CI 3</span>
    </div>

    <!-- KPIS -->
    <div class="comparison-kpis" id="comp-kpi-container">
        <!-- Rendered dynamically -->
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
                <!-- Dynamically populated by app.js -->
            </div>

            <div class="source">Sumber nilai: BPS Provinsi Jawa Timur & Dataset Internal SI-DAPEDA</div>
        </article>
    </section>
</div>

