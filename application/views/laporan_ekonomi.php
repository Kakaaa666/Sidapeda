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

    <?php if (!empty($status) && ($status === 'Terhapus' || $status === 'Sampah')): ?>
        <!-- TEMPAT SAMPAH LAPORAN -->
        <article class="card large" style="margin-bottom:24px; border:1px dashed #f43f5e; background:rgba(244,63,94,0.03);">
            <div class="card-head">
                <div>
                    <span class="badge danger">TEMPAT SAMPAH (SOFT DELETE)</span>
                    <h3>Daftar Dokumen Laporan Terhapus (<?= count($reports) ?>)</h3>
                    <p>Klik tombol Pulihkan untuk mengembalikan dokumen laporan ke arsip laporan aktif.</p>
                </div>
                <a href="<?= site_url('laporan_ekonomi') ?>" class="button ghost">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    <span>Kembali ke Laporan Aktif</span>
                </a>
            </div>
            <div class="data-table">
                <div class="data-tr data-th" style="grid-template-columns: 1.2fr 2fr 1.2fr 1.2fr 120px;">
                    <span>Nomor</span>
                    <span>Judul Laporan</span>
                    <span>Jenis</span>
                    <span>Dihapus Pada</span>
                    <span>Aksi</span>
                </div>
                <?php if (empty($reports)): ?>
                    <div style="padding:24px; text-align:center; color:var(--muted); font-size:11px;">
                        Tempat sampah kosong. Tidak ada dokumen laporan yang terhapus.
                    </div>
                <?php else: ?>
                    <?php foreach ($reports as $item): ?>
                        <div class="data-tr" style="grid-template-columns: 1.2fr 2fr 1.2fr 1.2fr 120px;">
                            <span><code><?= htmlspecialchars($item['nomor_laporan']) ?></code></span>
                            <span><strong><?= htmlspecialchars($item['judul_laporan']) ?></strong></span>
                            <span><?= htmlspecialchars($item['jenis_laporan']) ?></span>
                            <span><small><?= htmlspecialchars($item['deleted_at'] ?? '-') ?></small></span>
                            <span class="row-actions">
                                <button type="button" class="btn-restore-laporan" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['judul_laporan']) ?>" title="Pulihkan Laporan">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                    <span>Pulihkan</span>
                                </button>
                                <button type="button" class="btn-hard-delete-laporan danger" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['judul_laporan']) ?>" title="Hapus Permanen">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>
    <?php endif; ?>

    <div class="module-intro">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions">
            <?php if ($trash_count > 0): ?>
                <?php if ($status === 'Terhapus'): ?>
                    <a href="<?= site_url('laporan_ekonomi') ?>" class="button ghost">
                        <span>Kembali ke Aktif</span>
                    </a>
                <?php else: ?>
                    <a href="<?= site_url('laporan_ekonomi?status=Terhapus') ?>" class="button ghost danger">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        <span>Sampah (<?= $trash_count ?>)</span>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            <button class="button primary" id="btn-open-add-report">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Tambah Laporan Baru</span>
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
                    <div id="rep-macro-chart-container" class="chart-wrap dynamic-chart" style="height:190px; margin: 14px 0 8px;">
                        <!-- Macro Historical Trend Chart for report -->
                    </div>
                </section>

                <!-- SECTION 3: ANALISIS SEKTORAL -->
                <section id="rep-sec-sectoral">
                    <h3>3. Analisis Sektoral & Lapangan Usaha</h3>
                    <p>Pertumbuhan per lapangan usaha utama Kabupaten Banyuwangi:</p>
                    <div id="rep-sectoral-chart-container" class="chart-wrap dynamic-chart" style="height:auto; min-height:130px; margin: 10px 0 12px;">
                        <!-- Sectoral Horizontal Bar Chart for report -->
                    </div>
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

    <!-- TABEL ARSIP LAPORAN EKONOMI (CRUD & SOFT DELETE) -->
    <article class="card large" style="margin-top:24px;">
        <div class="card-head">
            <div>
                <span class="eyebrow">ARSIP LAPORAN TERSIMPAN</span>
                <h3>Daftar Dokumen Laporan Ekonomi (<?= count($reports) ?>)</h3>
                <p>Kelola metadata dokumen laporan resmi, status publikasi, dan penyusun laporan daerah.</p>
            </div>
            <button class="button primary" id="btn-open-add-report-2">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Tambah Laporan</span>
            </button>
        </div>

        <div class="data-table">
            <div class="data-tr data-th" style="grid-template-columns: 130px 2fr 1.2fr 80px 1.2fr 100px 110px;">
                <span>Nomor Laporan</span>
                <span>Judul Laporan</span>
                <span>Jenis</span>
                <span>Tahun</span>
                <span>Penyusun</span>
                <span>Status</span>
                <span>Aksi</span>
            </div>
            <?php foreach ($reports as $r): ?>
                <div class="data-tr" style="grid-template-columns: 130px 2fr 1.2fr 80px 1.2fr 100px 110px;">
                    <span><code><?= htmlspecialchars($r['nomor_laporan']) ?></code></span>
                    <span><strong><?= htmlspecialchars($r['judul_laporan']) ?></strong></span>
                    <span><?= htmlspecialchars($r['jenis_laporan']) ?></span>
                    <span><?= $r['periode_tahun'] ?></span>
                    <span><small><?= htmlspecialchars($r['penyusun']) ?></small></span>
                    <span><span class="badge <?= $r['status'] === 'Terpublikasi' ? 'success' : 'neutral' ?>"><?= $r['status'] ?></span></span>
                    <span class="row-actions">
                        <button type="button" class="btn-edit-report" data-id="<?= $r['id'] ?>" title="Edit Laporan">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            <span>Edit</span>
                        </button>
                        <button type="button" class="btn-delete-report danger" data-id="<?= $r['id'] ?>" data-name="<?= htmlspecialchars($r['judul_laporan']) ?>" title="Hapus (Soft Delete)">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>

    <!-- MODAL TAMBAH LAPORAN -->
    <div class="modal-backdrop" id="add-report-modal" style="display:none;">
        <div class="modal admin-data-modal" style="max-width:540px;">
            <div class="modal-head">
                <div>
                    <span class="badge info">TAMBAH LAPORAN</span>
                    <h2>Daftarkan Dokumen Laporan</h2>
                    <p>Simpan metadata laporan ekonomi daerah ke tabel database <code>laporan_ekonomi</code>.</p>
                </div>
                <button class="icon-button" id="modal-btn-close-add-report">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="form-add-report" method="POST" action="<?= site_url('laporan_ekonomi/create') ?>">
                <div class="manual-form" style="grid-template-columns: 1fr 1fr;">
                    <label>
                        Nomor Laporan
                        <input type="text" name="nomor_laporan" id="add-report-number" placeholder="Contoh: LAP/BAPPEDA/2024/004" required>
                    </label>
                    <label>
                        Periode Tahun
                        <input type="number" name="periode_tahun" id="add-report-year" value="2024" required>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Judul Laporan Lengkap
                        <input type="text" name="judul_laporan" id="add-report-title" placeholder="Contoh: Laporan Kinerja Perekonomian Triwulan IV 2024" required>
                    </label>
                    <label>
                        Jenis Laporan
                        <select name="jenis_laporan" id="add-report-type">
                            <option value="Perkembangan Ekonomi Daerah">Perkembangan Ekonomi Daerah</option>
                            <option value="Ringkasan Indikator Ekonomi">Ringkasan Indikator Ekonomi</option>
                            <option value="Profil Sektoral Daerah">Profil Sektoral Daerah</option>
                        </select>
                    </label>
                    <label>
                        Status Publikasi
                        <select name="status" id="add-report-status">
                            <option value="Terpublikasi">Terpublikasi</option>
                            <option value="Draf">Draf</option>
                        </select>
                    </label>
                    <label>
                        Penyusun / Bidang
                        <input type="text" name="penyusun" id="add-report-author" value="Bidang Perekonomian Bappeda" required>
                    </label>
                    <label>
                        Tanggal Terbit
                        <input type="date" name="tanggal_terbit" id="add-report-date" value="<?= date('Y-m-d') ?>">
                    </label>
                </div>

                <div id="add-report-error" class="method-note" style="display:none; color:var(--red); margin-bottom:12px;"></div>

                <div class="modal-actions">
                    <button type="button" class="button ghost" id="modal-btn-cancel-add-report">Batal</button>
                    <button type="submit" class="button primary" id="modal-btn-submit-add-report">Simpan Laporan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT LAPORAN -->
    <div class="modal-backdrop" id="edit-report-modal" style="display:none;">
        <div class="modal admin-data-modal" style="max-width:540px;">
            <div class="modal-head">
                <div>
                    <span class="badge warning">PERBARUI LAPORAN</span>
                    <h2>Edit Metadata Laporan</h2>
                    <p>Perbarui judul dokumen, status publikasi, atau tanggal terbit laporan.</p>
                </div>
                <button class="icon-button" id="modal-btn-close-edit-report">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="form-edit-report" method="POST">
                <input type="hidden" name="id" id="edit-report-id">
                <div class="manual-form" style="grid-template-columns: 1fr 1fr;">
                    <label>
                        Nomor Laporan
                        <input type="text" name="nomor_laporan" id="edit-report-number" required>
                    </label>
                    <label>
                        Periode Tahun
                        <input type="number" name="periode_tahun" id="edit-report-year" required>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Judul Laporan Lengkap
                        <input type="text" name="judul_laporan" id="edit-report-title" required>
                    </label>
                    <label>
                        Jenis Laporan
                        <select name="jenis_laporan" id="edit-report-type">
                            <option value="Perkembangan Ekonomi Daerah">Perkembangan Ekonomi Daerah</option>
                            <option value="Ringkasan Indikator Ekonomi">Ringkasan Indikator Ekonomi</option>
                            <option value="Profil Sektoral Daerah">Profil Sektoral Daerah</option>
                        </select>
                    </label>
                    <label>
                        Status Publikasi
                        <select name="status" id="edit-report-status">
                            <option value="Terpublikasi">Terpublikasi</option>
                            <option value="Draf">Draf</option>
                        </select>
                    </label>
                    <label>
                        Penyusun / Bidang
                        <input type="text" name="penyusun" id="edit-report-author" required>
                    </label>
                    <label>
                        Tanggal Terbit
                        <input type="date" name="tanggal_terbit" id="edit-report-date">
                    </label>
                </div>

                <div id="edit-report-error" class="method-note" style="display:none; color:var(--red); margin-bottom:12px;"></div>

                <div class="modal-actions">
                    <button type="button" class="button ghost" id="modal-btn-cancel-edit-report">Batal</button>
                    <button type="submit" class="button primary" id="modal-btn-submit-edit-report">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

