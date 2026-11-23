<div class="page outlook-page">
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
            <button class="button secondary" id="btn-export-outlook">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12M7 10l5 5 5-5M4 21h16"/></svg>
                <span>Ekspor Proyeksi</span>
            </button>
        </div>
    </div>

    <!-- MODE SWITCH -->
    <div class="outlook-mode-switch">
        <button class="active" id="btn-mode-forecast">
            <span class="mode-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 17 9 11l4 4 8-9"/><path d="M15 6h6v6"/></svg>
            </span>
            <div>
                <strong>Economic Forecasting</strong>
                <small>Metode tren berkala berbasis data makro ekonomi</small>
            </div>
            <span class="badge info">MODEL STATISTIK</span>
        </button>

        <button id="btn-mode-scenario">
            <span class="mode-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1v.1h-4v-.1a1.7 1.7 0 0 0-1.1-1.6 1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1-.4h-.1v-4H3A1.7 1.7 0 0 0 4.6 8.5a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1v-.1h4V3a1.7 1.7 0 0 0 1.1 1.6 1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.4 9c.1.4.3.7.6 1 .3.2.6.4 1 .4h.1v4H21a1.7 1.7 0 0 0-1.6.6Z"/></svg>
            </span>
            <div>
                <strong>Scenario Analysis</strong>
                <small>Simulasi sensitivitas berbasis asumsi kebijakan</small>
            </div>
            <span class="badge warning">ASUMSI KEBIJAKAN</span>
        </button>
    </div>

    <div class="method-notice forecast" id="outlook-notice">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 17 9 11l4 4 8-9"/><path d="M15 6h6v6"/></svg>
        <div>
            <strong id="outlook-notice-title">Estimasi model statistik indikator ekonomi</strong>
            <p id="outlook-notice-desc">Metode yang tersedia mencakup Tren Linear, Rata-rata Bergerak 3 Tahun, dan Pemulusan Eksponensial sederhana. Rentang indikatif dihitung menggunakan galat residual in-sample.</p>
        </div>
    </div>

    <!-- FORECAST CONTAINER -->
    <div id="forecast-section-container">
        <section class="forecast-config">
            <label>
                <span>Indikator yang diproyeksikan</span>
                <select id="fc-indicator">
                    <option value="Pertumbuhan ekonomi">Pertumbuhan ekonomi (%)</option>
                    <option value="Inflasi">Inflasi (%)</option>
                    <option value="Tingkat pengangguran">Tingkat pengangguran (%)</option>
                    <option value="Pertumbuhan investasi">Pertumbuhan investasi (%)</option>
                    <option value="Tingkat kemiskinan">Tingkat kemiskinan (%)</option>
                    <option value="IPM">IPM (poin)</option>
                    <option value="PDRB per kapita">PDRB per kapita (juta rupiah)</option>
                </select>
            </label>
            <label>
                <span>Data historis</span>
                <select id="fc-history">
                    <option value="10">2015–2024 (10 Observasi)</option>
                    <option value="5">2020–2024 (5 Observasi)</option>
                </select>
            </label>
            <label>
                <span>Horizon proyeksi</span>
                <select id="fc-horizon">
                    <option value="1">1 Tahun (2025)</option>
                    <option value="3" selected>3 Tahun (2025–2027)</option>
                    <option value="5">5 Tahun (2025–2029)</option>
                </select>
            </label>
            <label>
                <span>Metode</span>
                <select id="fc-method">
                    <option value="Tren Linear">Tren Linear</option>
                    <option value="Rata-rata Bergerak 3 Tahun">Rata-rata Bergerak 3 Tahun</option>
                    <option value="Pemulusan Eksponensial">Pemulusan Eksponensial (α=0.5)</option>
                </select>
            </label>
            <button class="button secondary" id="btn-recalculate-forecast">Hitung Ulang</button>
        </section>

        <!-- FORECAST SUMMARY CARDS -->
        <div class="forecast-summary" id="fc-summary-cards">
            <!-- Dynamically injected -->
        </div>

        <section class="forecast-layout">
            <article class="card forecast-main">
                <div class="card-head">
                    <div>
                        <span class="eyebrow">HISTORICAL VS FORECAST</span>
                        <h3 id="fc-chart-title">Proyeksi Pertumbuhan Ekonomi</h3>
                        <p id="fc-chart-meta">2015–2027 • Tren Linear</p>
                    </div>
                </div>

                <div id="fc-chart-canvas" class="chart-wrap dynamic-chart forecast-chart" style="height: 250px;">
                    <!-- SVG Chart injected -->
                </div>

                <div id="fc-result-table-wrap">
                    <!-- Table injected -->
                </div>

                <div class="source">Model Tren Berkala SI-DAPEDA • Rentang indikatif berbasis ±1,96 RMSE residual</div>
            </article>

            <aside class="card model-info">
                <div class="card-head">
                    <div>
                        <span class="eyebrow">MODEL INFORMATION</span>
                        <h3>Transparansi Metode</h3>
                    </div>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9l5 5v15H6V2Z"/><path d="M14 2v6h6M9 13h8M9 17h8"/></svg>
                </div>

                <div id="fc-model-details">
                    <!-- Injected -->
                </div>

                <div class="assumption-box">
                    <strong>Keterbatasan & Catatan:</strong>
                    <ul>
                        <li>Model proyeksi berbasis tren murni deret waktu.</li>
                        <li>Sensitif terhadap pergeseran struktural dan faktor tak terduga.</li>
                        <li>Gunakan sebagai acuan awal sebelum perencanaan teknis OPD.</li>
                    </ul>
                </div>
            </aside>
        </section>
    </div>

    <!-- SCENARIO CONTAINER -->
    <div id="scenario-section-container" style="display:none;">
        <div class="scenario-tabs">
            <button class="active" data-scenario="Moderat">
                <span class="signal info"></span>
                <div>
                    <strong>Moderat</strong>
                    <small>Mengikuti baseline tren deret berkala historis</small>
                </div>
                <span class="badge info">Baseline</span>
            </button>
            <button data-scenario="Optimistis">
                <span class="signal success"></span>
                <div>
                    <strong>Optimistis</strong>
                    <small>Aktivitas ekonomi dan iklim investasi menguat</small>
                </div>
                <span class="badge success">Alternatif +</span>
            </button>
            <button data-scenario="Pesimistis">
                <span class="signal warning"></span>
                <div>
                    <strong>Pesimistis</strong>
                    <small>Terjadi perlambatan global atau cuaca ekstrem</small>
                </div>
                <span class="badge warning">Alternatif -</span>
            </button>
        </div>

        <section class="scenario-workspace">
            <aside class="card assumption-panel">
                <div class="card-head">
                    <div>
                        <span class="eyebrow">SCENARIO ASSUMPTIONS</span>
                        <h3>Atur Asumsi Sensitivitas</h3>
                        <p>Geser nilai untuk menguji elastisitas terhadap baseline 2024.</p>
                    </div>
                </div>

                <label>
                    <div>
                        <span>Pertumbuhan investasi industri</span>
                        <strong id="val-slider-inv" class="positive">+10%</strong>
                    </div>
                    <input type="range" id="slider-inv" min="-10" max="25" value="10">
                    <small>-10% <i></i> +25%</small>
                </label>

                <label>
                    <div>
                        <span>Perubahan produksi pertanian</span>
                        <strong id="val-slider-agri" class="positive">+2%</strong>
                    </div>
                    <input type="range" id="slider-agri" min="-15" max="15" value="2">
                    <small>-15% <i></i> +15%</small>
                </label>

                <label>
                    <div>
                        <span>Pertumbuhan kunjungan wisatawan</span>
                        <strong id="val-slider-tour" class="positive">+7%</strong>
                    </div>
                    <input type="range" id="slider-tour" min="-20" max="30" value="7">
                    <small>-20% <i></i> +30%</small>
                </label>

                <div class="assumption-disclaimer">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="M12 8v5M12 17h.01"/></svg>
                    <p>Koefisien sensitivitas didasarkan pada bobot elastisitas sektoral Kabupaten Banyuwangi.</p>
                </div>
            </aside>

            <article class="card scenario-result">
                <div class="card-head">
                    <div>
                        <span class="eyebrow">SIMULATION RESULT</span>
                        <h3 id="scenario-result-heading">Dampak Skenario Moderat</h3>
                        <p>Perubahan terhadap proyeksi pertumbuhan ekonomi baseline.</p>
                    </div>
                    <span class="badge info" id="scenario-result-badge">MODERAT</span>
                </div>

                <div class="scenario-hero-number">
                    <span>Estimasi Pertumbuhan Ekonomi (Simulasi Skenario)</span>
                    <strong id="scenario-hero-growth">5,18<small>%</small></strong>
                    <p>Baseline 2024: 5,18% <i></i> Dampak gabungan asumsi: <b id="scenario-hero-impact" class="positive">+0,00 pp</b></p>
                </div>

                <div class="impact-bars">
                    <div>
                        <span>Investasi</span>
                        <div><i id="bar-impact-inv" class="positive-bar" style="width: 40%"></i></div>
                        <strong id="bar-impact-inv-val">+10%</strong>
                    </div>
                    <div>
                        <span>Pertanian</span>
                        <div><i id="bar-impact-agri" class="positive-bar" style="width: 15%"></i></div>
                        <strong id="bar-impact-agri-val">+2%</strong>
                    </div>
                    <div>
                        <span>Pariwisata</span>
                        <div><i id="bar-impact-tour" class="positive-bar" style="width: 25%"></i></div>
                        <strong id="bar-impact-tour-val">+7%</strong>
                    </div>
                </div>

                <!-- SCENARIO COMPARISON TRAJECTORY CHART -->
                <div style="margin-top:18px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                        <span class="eyebrow">LINTASAN PROYEKSI PERTUMBUHAN (2024–2027)</span>
                        <div class="legend" style="font-size: 11.5px;">
                            <i style="background:var(--blue)"></i> Baseline
                            <i style="background:var(--teal)"></i> Skenario
                        </div>
                    </div>
                    <div id="scenario-comparison-chart" class="chart-wrap dynamic-chart" style="height:185px;">
                        <!-- Rendered by JS -->
                    </div>
                </div>

                <div class="scenario-implications">
                    <strong>Implikasi Strategis Kebijakan:</strong>
                    <div>
                        <span>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
                            Peningkatan realisasi investasi berdampak langsung ke penyerapan tenaga kerja.
                        </span>
                        <span>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="M12 8v5M12 17h.01"/></svg>
                            Penguatan rantai pasok pertanian penting untuk menjaga stabilitas inflasi pangan.
                        </span>
                        <span>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 18h6M10 22h4M8.5 15a7 7 0 1 1 7 0c-.9.7-1.3 1.5-1.5 2h-4c-.2-.5-.6-1.3-1.5-2Z"/></svg>
                            Multi-event pariwisata Banyuwangi mengungkit sektor akomodasi, kuliner, dan UMKM.
                        </span>
                    </div>
                </div>
            </article>
        </section>
    </div>

    <!-- SECTORAL OUTLOOK TABLE -->
    <section class="sector-outlook">
        <div class="section-heading">
            <div>
                <span class="eyebrow">SECTORAL ECONOMIC OUTLOOK</span>
                <h2>Indikator Pertumbuhan Sektoral</h2>
                <p>Urutan berdasarkan pertumbuhan lokal Kabupaten Banyuwangi.</p>
            </div>
        </div>

        <div class="sector-table">
            <div class="sector-tr sector-th">
                <span>Sektor</span>
                <span>Pertumbuhan Lokal</span>
                <span>Pertumbuhan Provinsi</span>
                <span>Selisih (pp)</span>
                <span>Status Relatif</span>
            </div>
            <?php
            $sortedSectors = $sectors;
            usort($sortedSectors, function($a, $b) {
                return $b['growthLocal'] <=> $a['growthLocal'];
            });
            foreach ($sortedSectors as $s):
                $gap = round($s['growthLocal'] - $s['growthProvince'], 1);
            ?>
                <div class="sector-tr">
                    <span>
                        <div class="sector-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19V9M10 19V5M16 19v-7M22 19H2"/></svg>
                        </div>
                        <strong><?= htmlspecialchars($s['short']) ?></strong>
                    </span>
                    <strong><?= number_format($s['growthLocal'], 1, ',', '.') ?>%</strong>
                    <span><?= number_format($s['growthProvince'], 1, ',', '.') ?>%</span>
                    <span class="<?= $gap >= 0 ? 'positive' : 'negative' ?>">
                        <?= $gap >= 0 ? '+' : '' ?><?= number_format($gap, 1, ',', '.') ?> pp
                    </span>
                    <span class="badge <?= $gap >= 0 ? 'success' : 'warning' ?>">
                        <?= $gap >= 0 ? 'Di atas' : 'Di bawah' ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>


</div>

