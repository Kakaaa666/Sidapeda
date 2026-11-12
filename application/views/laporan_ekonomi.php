<div class="page">
    <div class="module-intro">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions">
            <span class="badge simulation">DATABASE CI 3</span>
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

            <label>
                <span>Jenis Laporan</span>
                <select id="rep-type-select">
                    <option value="Perkembangan Ekonomi Daerah">Perkembangan Ekonomi Daerah</option>
                    <option value="Ringkasan Indikator Ekonomi">Ringkasan Indikator Ekonomi</option>
                    <option value="Profil Sektoral Daerah">Profil Sektoral Daerah</option>
                </select>
            </label>

            <label>
                <span>Periode Tahun</span>
                <select id="rep-year-select">
                    <?php foreach (array_reverse($years) as $y): ?>
                        <option value="<?= $y ?>"><?= $y ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                <span>Wilayah Administrasi</span>
                <select disabled><option>Kabupaten Banyuwangi</option></select>
            </label>

            <div>
                <span class="label">Bagian Laporan yang Disertakan</span>
                <label class="check-row">
                    <input type="checkbox" class="rep-section-cb" value="Ringkasan eksekutif" checked>
                    <span>Ringkasan eksekutif</span>
                </label>
                <label class="check-row">
                    <input type="checkbox" class="rep-section-cb" value="Indikator utama" checked>
                    <span>Indikator utama</span>
                </label>
                <label class="check-row">
                    <input type="checkbox" class="rep-section-cb" value="Analisis sektoral" checked>
                    <span>Analisis sektoral</span>
                </label>
                <label class="check-row">
                    <input type="checkbox" class="rep-section-cb" value="Outlook ekonomi" checked>
                    <span>Outlook ekonomi</span>
                </label>
                <label class="check-row">
                    <input type="checkbox" class="rep-section-cb" value="Rekomendasi" checked>
                    <span>Rekomendasi kebijakan</span>
                </label>
            </div>

            <button class="button secondary" id="btn-select-all-sections">Pilih Semua Bagian</button>
        </aside>

        <!-- REPORT PREVIEW "PAPER" -->
        <main class="report-preview">
            <div class="paper" id="printable-report">
                <div class="paper-head">
                    <div class="logo">
                        <div class="logo-mark">
                            <span></span><span></span><span></span>
                        </div>
                        <div>
                            <strong>SI-DAPEDA</strong>
                            <small>Pemerintah Kabupaten Banyuwangi</small>
                        </div>
                    </div>
                    <span class="badge simulation">DOKUMEN RESMI CI 3</span>
                </div>

                <div class="paper-title">
                    <span id="rep-paper-badge">PERKEMBANGAN EKONOMI DAERAH • 2024</span>
                    <h1 id="rep-paper-heading">Perkembangan Ekonomi Daerah</h1>
                    <p>Pemerintah Kabupaten Banyuwangi — Bappeda & Bagian Perekonomian</p>
                </div>

                <div class="paper-rule"></div>

                <!-- SECTION 1: RINGKASAN EKSEKUTIF -->
                <section id="rep-sec-summary">
                    <h3>1. Ringkasan Eksekutif</h3>
                    <p id="rep-text-summary">
                        Pada tahun <strong id="rep-sum-year">2024</strong>, kinerja makro ekonomi Kabupaten Banyuwangi mencatat pertumbuhan sebesar <strong id="rep-sum-growth">5,18%</strong>. Capaian ini menunjukkan pemulihan stabil pasca-pandemi yang didukung akselerasi sektor pariwisata, pertanian tangguh, dan iklim investasi yang kondusif.
                    </p>
                </section>

                <!-- SECTION 2: INDIKATOR UTAMA -->
                <section id="rep-sec-indicators">
                    <h3>2. Indikator Kinerja Makro</h3>
                    <div class="paper-stats" id="rep-stats-grid">
                        <span>
                            <strong id="rep-stat-growth">5,18%</strong>
                            <small>Pertumbuhan Ekonomi</small>
                        </span>
                        <span>
                            <strong id="rep-stat-inflation">2,41%</strong>
                            <small>Inflasi Tahunan</small>
                        </span>
                        <span>
                            <strong id="rep-stat-tpt">4,03%</strong>
                            <small>Pengangguran Terbuka</small>
                        </span>
                    </div>
                </section>

                <!-- SECTION 3: ANALISIS SEKTORAL -->
                <section id="rep-sec-sectoral">
                    <h3>3. Analisis Sektoral & Lapangan Usaha</h3>
                    <p>Pertumbuhan per lapangan usaha utama Kabupaten Banyuwangi:</p>
                    <div id="rep-sectoral-table-wrap">
                        <!-- Populated by JS -->
                    </div>
                </section>

                <!-- SECTION 4: OUTLOOK -->
                <section id="rep-sec-outlook">
                    <h3>4. Prospek & Outlook Perekonomian</h3>
                    <p id="rep-text-outlook">
                        Berdasarkan pemodelan proyeksi tren berkala, pertumbuhan ekonomi Kabupaten Banyuwangi diproyeksikan bergerak stabil pada rentang 5,10% – 5,45% untuk horizon 2025. Perluasan hilirisasi agroindustri dan stabilisasi harga bahan pokok menjadi faktor penentu tercapainya skenario optimistis.
                    </p>
                </section>

                <!-- SECTION 5: REKOMENDASI -->
                <section id="rep-sec-recommendation">
                    <h3>5. Rekomendasi Kebijakan Strategis</h3>
                    <ul>
                        <li>Mempercepat digitalisasi perizinan investasi melalui penguatan Mal Pelayanan Publik (MPP).</li>
                        <li>Memperluas kemitraan strategis petani lokal dengan off-taker industri dan retail modern.</li>
                        <li>Melanjutkan program jaring pengaman sosial dan beasiswa pendidikan vokasi untuk menekan kemiskinan ekstrem.</li>
                    </ul>
                </section>
            </div>

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

