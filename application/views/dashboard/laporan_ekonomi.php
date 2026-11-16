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
            <button class="button secondary" id="btn-toggle-report-config" title="Sembunyikan / Tampilkan Panel Filter">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/></svg>
                <span id="btn-toggle-report-config-text">Sembunyikan Panel Filter</span>
            </button>
            <button class="button secondary" id="btn-export-report-csv">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12M7 10l5 5 5-5M4 21h16"/></svg>
                <span>Ekspor CSV</span>
            </button>
            <button class="button primary" id="btn-print-report" onclick="window.print()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                <span>Cetak / Simpan PDF</span>
            </button>
        </div>
    </div>

    <div class="report-builder">
        <!-- CONFIGURATION SIDEBAR -->
        <aside class="card report-config">
            <div class="panel-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1v.1h-4v-.1a1.7 1.7 0 0 0-1.1-1.6 1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1-.4h-.1v-4H3A1.7 1.7 0 0 0 4.6 8.5a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1v-.1h4V3a1.7 1.7 0 0 0 1.1 1.6 1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.4 9c.1.4.3.7.6 1 .3.2.6.4 1 .4h.1v4H21a1.7 1.7 0 0 0-1.6.6Z"/></svg>
                <strong>Konfigurasi Laporan</strong>
            </div>

            <div class="config-field-group">
                <label class="config-label">
                    <span>Jenis Dokumen Laporan</span>
                    <select id="rep-type-select" class="config-select">
                        <option value="Perkembangan Ekonomi Daerah">Perkembangan Ekonomi Daerah</option>
                        <option value="Ringkasan Indikator Ekonomi">Ringkasan Indikator Ekonomi</option>
                        <option value="Profil Sektoral Daerah">Profil Sektoral Daerah</option>
                    </select>
                </label>

                <label class="config-label">
                    <span>Periode Tahun Analisis</span>
                    <select id="rep-year-select" class="config-select">
                        <?php foreach (array_reverse($years) as $y): ?>
                            <option value="<?= $y ?>"><?= $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <div class="config-info-badge">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <div>
                        <small>Cakupan Administrasi</small>
                        <strong>Kabupaten Banyuwangi</strong>
                    </div>
                </div>
            </div>

            <div class="config-section-wrapper">
                <span class="config-subheading">Bagian Laporan yang Disertakan</span>
                <div class="section-check-list">
                    <label class="section-check-item">
                        <input type="checkbox" class="rep-section-cb" value="Ringkasan eksekutif" checked>
                        <div class="check-item-content">
                            <strong>1. Ringkasan Eksekutif</strong>
                            <small>Narasi performa makro daerah</small>
                        </div>
                    </label>
                    <label class="section-check-item">
                        <input type="checkbox" class="rep-section-cb" value="Indikator utama" checked>
                        <div class="check-item-content">
                            <strong>2. Indikator Kinerja Makro</strong>
                            <small>Pertumbuhan, inflasi, TPT & grafik tren</small>
                        </div>
                    </label>
                    <label class="section-check-item">
                        <input type="checkbox" class="rep-section-cb" value="Analisis sektoral" checked>
                        <div class="check-item-content">
                            <strong>3. Analisis Sektoral</strong>
                            <small>Top 5 sektor unggulan & komparasi</small>
                        </div>
                    </label>
                    <label class="section-check-item">
                        <input type="checkbox" class="rep-section-cb" value="Outlook ekonomi" checked>
                        <div class="check-item-content">
                            <strong>4. Prospek & Outlook</strong>
                            <small>Proyeksi berkala dan faktor penentu</small>
                        </div>
                    </label>
                    <label class="section-check-item">
                        <input type="checkbox" class="rep-section-cb" value="Rekomendasi" checked>
                        <div class="check-item-content">
                            <strong>5. Rekomendasi Kebijakan</strong>
                            <small>Strategi teknis intervensi daerah</small>
                        </div>
                    </label>
                </div>
            </div>

            <button type="button" class="button secondary small" id="btn-select-all-sections" style="width:100%; margin-top:14px; justify-content:center;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                <span>Pilih Semua Bagian</span>
            </button>

            <div class="config-doc-info">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <span>Dokumen dioptimalkan untuk cetak & simpan PDF ukuran standar A4.</span>
            </div>
        </aside>

        <!-- REPORT PREVIEW "PAPER" -->
        <main class="report-preview">
            <div class="paper" id="printable-report">
                <!-- OFFICIAL KOP / HEADER -->
                <div class="paper-head">
                    <div class="paper-brand">
                        <div class="paper-emblem">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                            </svg>
                        </div>
                        <div class="paper-brand-text">
                            <span class="gov-title">PEMERINTAH KABUPATEN BANYUWANGI</span>
                            <strong class="agency-title">BADAN PERENCANAAN PEMBANGUNAN DAERAH</strong>
                            <span class="system-sub">Sistem Informasi Data Pembangunan Daerah (SI-DAPEDA)</span>
                        </div>
                    </div>
                    <div class="paper-stamp">
                        <span class="official-badge">DOKUMEN RESMI</span>
                        <span class="doc-code">REG: DPD/BWI/<?= date('Y') ?>/EKON</span>
                        <span class="doc-class">Klasifikasi: Terbuka (Publik)</span>
                    </div>
                </div>

                <div class="paper-header-divider"></div>

                <!-- TITLE BLOCK -->
                <div class="paper-title-block">
                    <div class="paper-badge-wrap">
                        <span id="rep-paper-badge" class="paper-badge">PERKEMBANGAN EKONOMI DAERAH • 2024</span>
                    </div>
                    <h1 id="rep-paper-heading" class="paper-h1">Perkembangan Ekonomi Daerah</h1>
                    <p class="paper-desc">Telaah Kinerja Makro, Struktur Sektoral PDRB, dan Rekomendasi Kebijakan Daerah Kabupaten Banyuwangi</p>

                    <div class="paper-meta-grid">
                        <div class="meta-item">
                            <small>WILAYAH ANALISIS</small>
                            <strong>Kabupaten Banyuwangi</strong>
                        </div>
                        <div class="meta-item">
                            <small>STATUS DOKUMEN</small>
                            <strong class="text-success"><span class="status-dot"></span> Final Disahkan</strong>
                        </div>
                        <div class="meta-item">
                            <small>SUMBER DATA</small>
                            <strong>BPS & Bappeda BWI</strong>
                        </div>
                        <div class="meta-item">
                            <small>TANGGAL PENERBITAN</small>
                            <strong><?= date('d F Y') ?></strong>
                        </div>
                    </div>
                </div>

                <!-- SECTION 1: RINGKASAN EKSEKUTIF -->
                <section id="rep-sec-summary" class="paper-section">
                    <div class="section-title">
                        <span class="section-num">01</span>
                        <h3>Ringkasan Eksekutif</h3>
                    </div>
                    <div class="paper-callout">
                        <p id="rep-text-summary">
                            Pada tahun <strong id="rep-sum-year">2024</strong>, kinerja makro ekonomi Kabupaten Banyuwangi mencatat pertumbuhan sebesar <strong id="rep-sum-growth">5,18%</strong>. Capaian ini menunjukkan pemulihan stabil pasca-pandemi yang didukung akselerasi sektor pariwisata, pertanian tangguh, dan iklim investasi yang kondusif.
                        </p>
                    </div>
                </section>

                <!-- SECTION 2: INDIKATOR UTAMA -->
                <section id="rep-sec-indicators" class="paper-section">
                    <div class="section-title">
                        <span class="section-num">02</span>
                        <h3>Indikator Kinerja Makro</h3>
                    </div>

                    <div class="paper-kpi-grid" id="rep-stats-grid">
                        <div class="paper-kpi-card">
                            <span class="kpi-label">Pertumbuhan Ekonomi</span>
                            <div class="kpi-val-row">
                                <strong id="rep-stat-growth" class="kpi-num">5,18%</strong>
                                <span class="kpi-tag positive">+ Stabil</span>
                            </div>
                            <small class="kpi-sub">Laju Pertumbuhan PDRB ADHK</small>
                        </div>
                        <div class="paper-kpi-card">
                            <span class="kpi-label">Inflasi Tahunan</span>
                            <div class="kpi-val-row">
                                <strong id="rep-stat-inflation" class="kpi-num">2,41%</strong>
                                <span class="kpi-tag neutral">Terkendali</span>
                            </div>
                            <small class="kpi-sub">Indeks Harga Konsumen (YoY)</small>
                        </div>
                        <div class="paper-kpi-card">
                            <span class="kpi-label">Pengangguran Terbuka</span>
                            <div class="kpi-val-row">
                                <strong id="rep-stat-tpt" class="kpi-num">4,03%</strong>
                                <span class="kpi-tag success">Menurun</span>
                            </div>
                            <small class="kpi-sub">Tingkat Pengangguran Terbuka</small>
                        </div>
                    </div>

                    <div class="paper-chart-box">
                        <div class="chart-box-header">
                            <span class="chart-caption">Grafik Historis Indikator Makro Utama (Pertumbuhan, Inflasi, TPT)</span>
                        </div>
                        <div id="rep-macro-chart-container" class="chart-wrap dynamic-chart" style="height:190px; margin: 6px 0;">
                            <!-- Macro Historical Trend Chart for report -->
                        </div>
                    </div>
                </section>

                <!-- SECTION 3: ANALISIS SEKTORAL -->
                <section id="rep-sec-sectoral" class="paper-section">
                    <div class="section-title">
                        <span class="section-num">03</span>
                        <h3>Analisis Sektoral & Lapangan Usaha</h3>
                    </div>
                    <p class="section-intro">Pertumbuhan 5 sektor lapangan usaha dengan laju tertinggi terhadap rata-rata provinsi:</p>

                    <div class="paper-chart-box" style="margin-bottom: 12px;">
                        <div id="rep-sectoral-chart-container" class="chart-wrap dynamic-chart" style="height:auto; min-height:130px; margin: 4px 0;">
                            <!-- Sectoral Horizontal Bar Chart for report -->
                        </div>
                    </div>

                    <div id="rep-sectoral-table-wrap" class="paper-table-container">
                        <!-- Populated dynamically by JS -->
                    </div>
                </section>

                <!-- SECTION 4: OUTLOOK -->
                <section id="rep-sec-outlook" class="paper-section">
                    <div class="section-title">
                        <span class="section-num">04</span>
                        <h3>Prospek & Outlook Perekonomian</h3>
                    </div>
                    <div class="paper-outlook-box">
                        <div class="outlook-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                        </div>
                        <p id="rep-text-outlook" class="outlook-text">
                            Berdasarkan pemodelan proyeksi tren berkala, pertumbuhan ekonomi Kabupaten Banyuwangi diproyeksikan bergerak stabil pada rentang 5,10% – 5,45% untuk horizon 2025. Perluasan hilirisasi agroindustri dan stabilisasi harga bahan pokok menjadi faktor penentu tercapainya skenario optimistis.
                        </p>
                    </div>
                </section>

                <!-- SECTION 5: REKOMENDASI -->
                <section id="rep-sec-recommendation" class="paper-section">
                    <div class="section-title">
                        <span class="section-num">05</span>
                        <h3>Rekomendasi Kebijakan Strategis</h3>
                    </div>
                    <div class="recom-cards-list">
                        <div class="recom-card-item">
                            <span class="recom-badge">1</span>
                            <div class="recom-content">
                                <strong>Akselerasi Digitalisasi Perizinan Investasi</strong>
                                <p>Penguatan integrasi Mal Pelayanan Publik (MPP) digital guna memangkas durasi perizinan usaha dan meningkatkan realisasi Penanaman Modal Dalam Negeri (PMDN) & PMA.</p>
                            </div>
                        </div>
                        <div class="recom-card-item">
                            <span class="recom-badge">2</span>
                            <div class="recom-content">
                                <strong>Kemitraan Strategis Agrobisnis & Hilirisasi</strong>
                                <p>Memperluas kemitraan strategis petani lokal dengan off-taker industri dan retail modern untuk stabilitas harga komoditas pangan utama dan peningkatan nilai tambah hasil panen.</p>
                            </div>
                        </div>
                        <div class="recom-card-item">
                            <span class="recom-badge">3</span>
                            <div class="recom-content">
                                <strong>Penguatan Jaring Pengaman Sosial & Vokasi</strong>
                                <p>Melanjutkan program jaring pengaman sosial adaptif dan pelatihan vokasi berbasis potensi lokal guna menekan angka kemiskinan ekstrem dan disparitas ekonomi antarkecamatan.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- OFFICIAL SIGNATURE & VALIDATION FOOTER -->
                <div class="paper-sign-block">
                    <div class="sign-left">
                        <div class="doc-auth-note">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            <div>
                                <strong>Otoritas Dokumen Sistem SI-DAPEDA</strong>
                                <p>Dokumen ini diterbitkan secara otomatis dan terverifikasi oleh Sistem Informasi Data Pembangunan Daerah Kabupaten Banyuwangi.</p>
                            </div>
                        </div>
                    </div>
                    <div class="sign-right">
                        <div class="sign-meta">
                            <p class="sign-place-date">Banyuwangi, <?= date('d F Y') ?></p>
                            <p class="sign-role">Kepala Badan Perencanaan Pembangunan Daerah<br>Kabupaten Banyuwangi</p>
                            <div class="digital-stamp">
                                <span>TERVERIFIKASI DIGITAL</span>
                                <small>BAPPEDA KAB. BANYUWANGI</small>
                            </div>
                            <p class="sign-name"><strong>Dr. H. Suyanto Waspo Tondo W., M.Si.</strong></p>
                            <p class="sign-nip">NIP. 19680512 199303 1 005</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PREVIEW BOTTOM ACTIONS -->
            <div class="preview-actions">
                <button class="button secondary" id="btn-export-preview-csv">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12M7 10l5 5 5-5M4 21h16"/></svg>
                    <span>Ekspor CSV</span>
                </button>
                <button class="button primary" onclick="window.print()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    <span>Cetak / Simpan PDF</span>
                </button>
            </div>
        </main>
    </div>
</div>
