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
        <!-- TEMPAT SAMPAH INSIGHT -->
        <article class="card large" style="margin-bottom:24px; border:1px dashed #f43f5e; background:rgba(244,63,94,0.03);">
            <div class="card-head">
                <div>
                    <span class="badge danger">TEMPAT SAMPAH (SOFT DELETE)</span>
                    <h3>Daftar Insight Kebijakan Terhapus (<?= count($insights) ?>)</h3>
                    <p>Klik tombol Pulihkan untuk mengembalikan insight ke ringkasan kebijakan aktif.</p>
                </div>
                <a href="<?= site_url('economic_insight') ?>" class="button ghost">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    <span>Kembali ke Insight Aktif</span>
                </a>
            </div>
            <div class="data-table">
                <div class="data-tr data-th" style="grid-template-columns: 2fr 1.2fr 1fr 1.2fr 120px;">
                    <span>Judul Insight</span>
                    <span>Kategori</span>
                    <span>Urgensi</span>
                    <span>Dihapus Pada</span>
                    <span>Aksi</span>
                </div>
                <?php if (empty($insights)): ?>
                    <div style="padding:24px; text-align:center; color:var(--muted); font-size:11px;">
                        Tempat sampah kosong. Tidak ada insight kebijakan yang terhapus.
                    </div>
                <?php else: ?>
                    <?php foreach ($insights as $item): ?>
                        <div class="data-tr" style="grid-template-columns: 2fr 1.2fr 1fr 1.2fr 120px;">
                            <span><strong><?= htmlspecialchars($item['judul']) ?></strong></span>
                            <span><?= htmlspecialchars($item['kategori']) ?></span>
                            <span><span class="badge <?= $item['tingkat_urgensi'] === 'Tinggi' ? 'danger' : ($item['tingkat_urgensi'] === 'Sedang' ? 'warning' : 'success') ?>"><?= $item['tingkat_urgensi'] ?></span></span>
                            <span><small><?= htmlspecialchars($item['deleted_at'] ?? '-') ?></small></span>
                            <span class="row-actions">
                                <button type="button" class="btn-restore-insight" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['judul']) ?>" title="Pulihkan Insight">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                    <span>Pulihkan</span>
                                </button>
                                <button type="button" class="btn-hard-delete-insight danger" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['judul']) ?>" title="Hapus Permanen">
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
                    <a href="<?= site_url('economic_insight') ?>" class="button ghost">
                        <span>Kembali ke Aktif</span>
                    </a>
                <?php else: ?>
                    <a href="<?= site_url('economic_insight?status=Terhapus') ?>" class="button ghost danger">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        <span>Sampah (<?= $trash_count ?>)</span>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            <button class="button primary" id="btn-open-add-insight">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Tambah Insight</span>
            </button>
            <a href="<?= site_url('laporan_ekonomi') ?>" class="button secondary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9l5 5v15H6V2Z"/><path d="M14 2v6h6M9 13h8M9 17h8"/></svg>
                <span>Buka Generator Laporan</span>
            </a>
        </div>
    </div>

    <!-- INSIGHT HERO BANNER -->
    <section class="insight-hero">
        <div>
            <span class="badge info">EXECUTIVE BRIEF • INTELIJEN EKONOMI</span>
            <h2>Ringkasan Kondisi Ekonomi<br>Kabupaten Banyuwangi.</h2>
            <p>Pertumbuhan ekonomi, stabilitas harga, dinamika pasar tenaga kerja, dan akselerasi investasi dianalisis terintegrasi sebagai landasan perencanaan pembangunan daerah.</p>
        </div>
        <div class="brief-score">
            <span>Total Temuan Kebijakan</span>
            <strong><?= count($insights) ?><small> Isu</small></strong>
            <span class="badge success">Aktif Dipantau</span>
        </div>
    </section>

    <!-- GRAFIK PEMETAAN REKOMENDASI KEBIJAKAN (Sesuai Isi Temuan & Urgensi Halaman) -->
    <article class="card large" style="margin-bottom:20px;">
        <div class="card-head">
            <div>
                <span class="eyebrow">DISTRIBUSI STRATEGIS REKOMENDASI</span>
                <h3>Grafik Pemetaan Rekomendasi Kebijakan per Kategori & Tingkat Urgensi</h3>
                <p>Distribusi temuan intelijen ekonomi berdasarkan klaster kategori tematik dan skala prioritas urgensi.</p>
            </div>
            <div class="legend" style="font-size:8px;">
                <i style="background:var(--red)"></i> Urgensi Tinggi
                <i style="background:var(--yellow)"></i> Urgensi Sedang
                <i style="background:var(--green)"></i> Urgensi Rendah
            </div>
        </div>
        <div id="insight-chart-container" class="chart-wrap dynamic-chart" style="height:auto; min-height:160px; padding:6px 0;">
            <!-- Rendered by JS -->
        </div>
    </article>

    <!-- ANALISIS GRAFIK INSIGHT -->
    <section class="two-col" style="margin-bottom:20px;">
        <!-- GRAFIK KATEGORI -->
        <article class="card">
            <div class="card-head">
                <div>
                    <span class="eyebrow">SEBARAN TEMUAN</span>
                    <h3>Komposisi Isu Kebijakan per Kategori</h3>
                    <p>Proporsi fokus perhatian pembangunan berdasarkan kategori isu ekonomi.</p>
                </div>
            </div>
            <div class="bar-chart" id="insight-category-chart" style="margin:14px 0;">
                <?php
                $catCounts = [];
                foreach ($insights as $ins) {
                    $c = $ins['kategori'] ?: 'Lainnya';
                    $catCounts[$c] = ($catCounts[$c] ?? 0) + 1;
                }
                $maxCat = !empty($catCounts) ? max($catCounts) : 1;
                $totalIns = count($insights) ?: 1;
                ?>
                <?php foreach ($catCounts as $catName => $cnt): ?>
                    <?php $pctWidth = round(($cnt / $maxCat) * 100); ?>
                    <div class="bar-row" style="grid-template-columns: 140px 1fr 65px; margin: 8px 0;">
                        <span style="font-size:9px; font-weight:600; color:var(--ink);"><?= htmlspecialchars($catName) ?></span>
                        <div class="bar-track" style="height:9px;">
                            <i style="width:<?= $pctWidth ?>%; background:var(--blue);"></i>
                        </div>
                        <strong style="font-size:9px;"><?= $cnt ?> isu <small style="color:var(--muted); font-size:7px;">(<?= round(($cnt / $totalIns) * 100) ?>%)</small></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <!-- GRAFIK URGENSI -->
        <article class="card">
            <div class="card-head">
                <div>
                    <span class="eyebrow">PRIORITAS INTERVENSI</span>
                    <h3>Tingkat Urgensi Kebijakan Daerah</h3>
                    <p>Klasifikasi kecepatan respon tindak lanjut yang diperlukan OPD terkait.</p>
                </div>
            </div>
            <div class="bar-chart" id="insight-urgency-chart" style="margin:14px 0;">
                <?php
                $urgCounts = ['Tinggi' => 0, 'Sedang' => 0, 'Rendah' => 0];
                foreach ($insights as $ins) {
                    $u = $ins['tingkat_urgensi'] ?: 'Sedang';
                    if (isset($urgCounts[$u])) $urgCounts[$u]++;
                    else $urgCounts[$u] = 1;
                }
                $maxUrg = max(array_values($urgCounts)) ?: 1;
                ?>
                <div class="bar-row" style="grid-template-columns: 100px 1fr 65px; margin: 8px 0;">
                    <span style="font-size:9px; font-weight:600; color:var(--red);">Urgensi Tinggi</span>
                    <div class="bar-track" style="height:9px;">
                        <i style="width:<?= round(($urgCounts['Tinggi'] / $maxUrg) * 100) ?>%; background:var(--red);"></i>
                    </div>
                    <strong style="color:var(--red); font-size:9px;"><?= $urgCounts['Tinggi'] ?> isu</strong>
                </div>
                <div class="bar-row" style="grid-template-columns: 100px 1fr 65px; margin: 8px 0;">
                    <span style="font-size:9px; font-weight:600; color:var(--yellow);">Urgensi Sedang</span>
                    <div class="bar-track" style="height:9px;">
                        <i style="width:<?= round(($urgCounts['Sedang'] / $maxUrg) * 100) ?>%; background:var(--yellow);"></i>
                    </div>
                    <strong style="color:var(--yellow); font-size:9px;"><?= $urgCounts['Sedang'] ?> isu</strong>
                </div>
                <div class="bar-row" style="grid-template-columns: 100px 1fr 65px; margin: 8px 0;">
                    <span style="font-size:9px; font-weight:600; color:var(--green);">Urgensi Rendah</span>
                    <div class="bar-track" style="height:9px;">
                        <i style="width:<?= round(($urgCounts['Rendah'] / $maxUrg) * 100) ?>%; background:var(--green);"></i>
                    </div>
                    <strong style="color:var(--green); font-size:9px;"><?= $urgCounts['Rendah'] ?> isu</strong>
                </div>
            </div>
            <div style="margin-top:12px; font-size:8px; color:var(--muted); border-top:1px solid var(--line); padding-top:8px;">
                Total <strong><?= count($insights) ?></strong> rekomendasi kebijakan aktif tercatat di database intelijen.
            </div>
        </article>
    </section>

    <!-- INSIGHT CARDS GRID -->
    <div class="insight-grid">
        <?php if (!empty($insights)): ?>
            <?php foreach ($insights as $item): ?>
                <article class="card insight-card" style="position:relative;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                        <span class="badge <?= $item['tingkat_urgensi'] === 'Tinggi' ? 'danger' : ($item['tingkat_urgensi'] === 'Sedang' ? 'warning' : 'success') ?>"><?= htmlspecialchars($item['tingkat_urgensi']) ?></span>
                        <div style="display:flex; gap:4px;">
                            <button type="button" class="btn-edit-insight" data-id="<?= $item['id'] ?>" title="Edit Insight" style="background:transparent; border:none; cursor:pointer; color:var(--text-muted); padding:2px;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                            <button type="button" class="btn-delete-insight" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['judul']) ?>" title="Hapus (Soft Delete)" style="background:transparent; border:none; cursor:pointer; color:var(--red); padding:2px;">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </div>
                    <span class="insight-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19V9M10 19V5M16 19v-7M22 19H2"/></svg>
                    </span>
                    <h3><?= htmlspecialchars($item['judul']) ?></h3>
                    <p><strong><?= htmlspecialchars($item['kategori']) ?>:</strong> <?= htmlspecialchars($item['ringkasan']) ?></p>
                    <button class="text-button btn-read-insight" data-title="<?= htmlspecialchars($item['judul']) ?>" data-detail="<?= htmlspecialchars($item['rekomendasi_kebijakan'] . ' (OPD: ' . $item['opd_penanggung_jawab'] . ' | Dampak: ' . $item['dampak_anggaran'] . ')') ?>">
                        <span>Baca rekomendasi</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; padding:24px; text-align:center; color:var(--muted); font-size:11px;">
                Belum ada data temuan ekonomi aktif.
            </div>
        <?php endif; ?>
    </div>

    <!-- POLICY CHECKLIST -->
    <article class="card recommendation">
        <div class="card-head">
            <div>
                <span class="eyebrow">POLICY ACTION CHECKLIST</span>
                <h3>Langkah Intervensi Kebijakan Strategis</h3>
            </div>
            <span class="badge info">Rekomendasi Pemda</span>
        </div>

        <?php if (!empty($insights)): ?>
            <?php $no = 1; foreach ($insights as $item): ?>
                <div class="recommend-row">
                    <b><?= sprintf('%02d', $no++) ?></b>
                    <div>
                        <strong><?= htmlspecialchars($item['judul']) ?></strong>
                        <small><?= htmlspecialchars($item['rekomendasi_kebijakan']) ?> (OPD: <?= htmlspecialchars($item['opd_penanggung_jawab']) ?>)</small>
                    </div>
                    <span class="badge <?= $item['tingkat_urgensi'] === 'Tinggi' ? 'success' : 'warning' ?>">Prioritas <?= $no - 1 ?></span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="recommend-row">
                <b>01</b>
                <div>
                    <strong>Akselerasi Hilirisasi Komoditas Unggulan</strong>
                    <small>Pengembangan sentra pengolahan kopi, kakao, dan perikanan tangkap untuk meningkatkan nilai tambah lokal sebelum ekspor.</small>
                </div>
                <span class="badge success">Prioritas 1</span>
            </div>
        <?php endif; ?>
    </article>

    <!-- INSIGHT DETAIL MODAL -->
    <div class="modal-backdrop" id="insight-modal-backdrop" style="display:none;">
        <div class="modal">
            <div class="modal-head">
                <div>
                    <span class="badge info">ECONOMIC INSIGHT DETAIL</span>
                    <h2 id="insight-modal-title">Judul Analisis</h2>
                </div>
                <button class="icon-button" id="insight-modal-close">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>
            <p id="insight-modal-body" style="font-size: 11px; line-height: 1.7; color: var(--muted); margin: 18px 0;"></p>
            <div class="modal-actions">
                <button class="button primary" id="insight-modal-btn-ok">Tutup</button>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH INSIGHT -->
    <div class="modal-backdrop" id="add-insight-modal" style="display:none;">
        <div class="modal admin-data-modal" style="max-width:540px;">
            <div class="modal-head">
                <div>
                    <span class="badge info">TAMBAH INSIGHT</span>
                    <h2>Tambah Insight & Rekomendasi</h2>
                    <p>Simpan temuan kebijakan ekonomi ke tabel database <code>economic_insight</code>.</p>
                </div>
                <button class="icon-button" id="modal-btn-close-add-insight">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="form-add-insight" method="POST" action="<?= site_url('economic_insight/create') ?>">
                <div class="manual-form" style="grid-template-columns: 1fr 1fr;">
                    <label style="grid-column: 1 / 3;">
                        Judul Analisis / Insight
                        <input type="text" name="judul" id="add-insight-title" placeholder="Contoh: Akselerasi Investasi Industri Maritim" required>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Ringkasan Temuan
                        <textarea name="ringkasan" id="add-insight-summary" rows="2" placeholder="Uraikan kondisi aktual hasil telaah data..." required></textarea>
                    </label>
                    <label>
                        Kategori Kebijakan
                        <input type="text" name="kategori" id="add-insight-category" placeholder="Contoh: Investasi / Pangan / Fiskal" required>
                    </label>
                    <label>
                        Tingkat Urgensi
                        <select name="tingkat_urgensi" id="add-insight-urgency">
                            <option value="Tinggi">Tinggi</option>
                            <option value="Sedang" selected>Sedang</option>
                            <option value="Stabil">Stabil</option>
                        </select>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Rekomendasi Kebijakan
                        <textarea name="rekomendasi_kebijakan" id="add-insight-rec" rows="2" placeholder="Langkah tindakan strategis bagi pemerintah daerah..." required></textarea>
                    </label>
                    <label>
                        Dampak Anggaran
                        <input type="text" name="dampak_anggaran" id="add-insight-impact" placeholder="Contoh: Efisiensi APBD / BTT">
                    </label>
                    <label>
                        OPD Penanggung Jawab
                        <input type="text" name="opd_penanggung_jawab" id="add-insight-opd" placeholder="Contoh: Dinas Perikanan / Bappeda">
                    </label>
                    <label>
                        Tahun Analisis
                        <input type="number" name="tahun" id="add-insight-year" value="2024">
                    </label>
                </div>

                <div id="add-insight-error" class="method-note" style="display:none; color:var(--red); margin-bottom:12px;"></div>

                <div class="modal-actions">
                    <button type="button" class="button ghost" id="modal-btn-cancel-add-insight">Batal</button>
                    <button type="submit" class="button primary" id="modal-btn-submit-add-insight">Simpan Insight</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT INSIGHT -->
    <div class="modal-backdrop" id="edit-insight-modal" style="display:none;">
        <div class="modal admin-data-modal" style="max-width:540px;">
            <div class="modal-head">
                <div>
                    <span class="badge warning">PERBARUI INSIGHT</span>
                    <h2>Edit Insight & Rekomendasi</h2>
                    <p>Perbarui judul temuan, rekomendasi kebijakan, atau OPD pengampu.</p>
                </div>
                <button class="icon-button" id="modal-btn-close-edit-insight">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="form-edit-insight" method="POST">
                <input type="hidden" name="id" id="edit-insight-id">
                <div class="manual-form" style="grid-template-columns: 1fr 1fr;">
                    <label style="grid-column: 1 / 3;">
                        Judul Analisis / Insight
                        <input type="text" name="judul" id="edit-insight-title" required>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Ringkasan Temuan
                        <textarea name="ringkasan" id="edit-insight-summary" rows="2" required></textarea>
                    </label>
                    <label>
                        Kategori Kebijakan
                        <input type="text" name="kategori" id="edit-insight-category" required>
                    </label>
                    <label>
                        Tingkat Urgensi
                        <select name="tingkat_urgensi" id="edit-insight-urgency">
                            <option value="Tinggi">Tinggi</option>
                            <option value="Sedang">Sedang</option>
                            <option value="Stabil">Stabil</option>
                        </select>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Rekomendasi Kebijakan
                        <textarea name="rekomendasi_kebijakan" id="edit-insight-rec" rows="2" required></textarea>
                    </label>
                    <label>
                        Dampak Anggaran
                        <input type="text" name="dampak_anggaran" id="edit-insight-impact">
                    </label>
                    <label>
                        OPD Penanggung Jawab
                        <input type="text" name="opd_penanggung_jawab" id="edit-insight-opd">
                    </label>
                    <label>
                        Tahun Analisis
                        <input type="number" name="tahun" id="edit-insight-year">
                    </label>
                </div>

                <div id="edit-insight-error" class="method-note" style="display:none; color:var(--red); margin-bottom:12px;"></div>

                <div class="modal-actions">
                    <button type="button" class="button ghost" id="modal-btn-cancel-edit-insight">Batal</button>
                    <button type="submit" class="button primary" id="modal-btn-submit-edit-insight">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

