<div class="page comparison-page">
    <div class="module-intro">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions" style="display:flex; align-items:center; gap:8px;">
            <button class="button primary" id="btn-open-add-regional-modal" title="Tambah Wilayah Baru">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                <span>Tambah Wilayah</span>
            </button>
            <?php if (!empty($trash_count) && $trash_count > 0): ?>
                <a href="<?= site_url('perbandingan_daerah?status=Terhapus') ?>" class="badge danger" style="text-decoration:none; padding:7px 11px; font-size:8px; font-weight:700;" title="Klik untuk melihat data di tempat sampah">
                    🗑️ <?= $trash_count ?> Terhapus
                </a>
            <?php endif; ?>
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

    <?php if ($status === 'Terhapus'): ?>
        <div class="alert alert-info" style="background:#eff6ff; border:1px solid #bfdbfe; color:#1e40af; padding:12px 16px; border-radius:9px; margin-bottom:16px; display:flex; justify-content:space-between; align-items:center; font-size:9px;">
            <div style="display:flex; align-items:center; gap:8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                <span><strong>Tempat Sampah Wilayah (Soft Delete):</strong> Menampilkan data wilayah yang dihapus sementara. Anda dapat memulihkannya (Restore) kapan saja ke ranking aktif.</span>
            </div>
            <a href="<?= site_url('perbandingan_daerah') ?>" class="button secondary small" style="height:28px; padding:0 10px; font-size:8px;">Kembali ke Wilayah Aktif</a>
        </div>

        <!-- TABEL TEMPAT SAMPAH UNTUK RESTORE WILAYAH -->
        <article class="card data-card" style="margin-bottom:20px;">
            <div class="card-head">
                <div>
                    <span class="eyebrow">TEMPAT SAMPAH (SOFT DELETE)</span>
                    <h3>Daftar Wilayah Terhapus (<?= count($regional) ?>)</h3>
                    <p>Klik tombol Pulihkan untuk mengembalikan wilayah ke tabel ranking dan benchmark.</p>
                </div>
            </div>
            <div class="data-table">
                <div class="data-tr data-th" style="grid-template-columns: 1.5fr 1fr 1fr 1.2fr 110px;">
                    <span>Wilayah</span>
                    <span>Pertumbuhan</span>
                    <span>Perubahan</span>
                    <span>Dihapus Pada</span>
                    <span>Aksi</span>
                </div>
                <?php if (empty($regional)): ?>
                    <div style="padding:24px; text-align:center; color:var(--muted); font-size:9px;">
                        Tempat sampah kosong. Tidak ada wilayah yang terhapus.
                    </div>
                <?php else: ?>
                    <?php foreach ($regional as $item): ?>
                        <div class="data-tr" style="grid-template-columns: 1.5fr 1fr 1fr 1.2fr 110px;">
                            <span><strong><?= htmlspecialchars($item['name']) ?></strong></span>
                            <span><?= number_format($item['value'], 2, ',', '.') ?>%</span>
                            <span><?= ($item['change'] >= 0 ? '+' : '') . number_format($item['change'], 2, ',', '.') ?> pp</span>
                            <span><small><?= htmlspecialchars($item['deleted_at'] ?? '-') ?></small></span>
                            <span class="row-actions">
                                <button type="button" class="btn-restore-regional" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>" title="Pulihkan Wilayah">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                    <span>Pulihkan</span>
                                </button>
                                <button type="button" class="btn-hard-delete-regional danger" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>" title="Hapus Permanen">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>
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

        <div class="selected-regions">
            <span>Wilayah dalam ranking:</span>
            <?php foreach ($all_active_regional as $r): ?>
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

                <div class="comparison-table-head" style="grid-template-columns: 50px 1.3fr .7fr .7fr .7fr 80px;">
                    <span>Peringkat</span>
                    <span>Wilayah</span>
                    <span>Pertumbuhan</span>
                    <span>Perubahan Tahunan</span>
                    <span>Gap ke Rerata</span>
                    <span>Aksi</span>
                </div>
                <div class="comparison-ranking">
                    <?php foreach ($all_active_regional as $idx => $r):
                        $isBwi = ($r['name'] === 'Kab. Banyuwangi');
                        $diff = round($r['value'] - $peerAvg, 2);
                    ?>
                        <div class="comparison-table-row <?= $isBwi ? 'selected' : '' ?>" style="grid-template-columns: 50px 1.3fr .7fr .7fr .7fr 80px;">
                            <b><?= $idx + 1 ?></b>
                            <span>
                                <i class="region-avatar"><?= strtoupper(substr(str_replace(['Kab. ', 'Kota '], '', $r['name']), 0, 2)) ?></i>
                                <strong><?= htmlspecialchars($r['name']) ?></strong>
                            </span>
                            <strong><?= number_format($r['value'], 2, ',', '.') ?>%</strong>
                            <small class="<?= $r['change'] >= 0 ? 'positive' : 'negative' ?>"><?= ($r['change'] >= 0 ? '+' : '') . number_format($r['change'], 2, ',', '.') ?> pp</small>
                            <span class="badge <?= $diff >= 0 ? 'success' : 'warning' ?>"><?= ($diff >= 0 ? '+' : '') . number_format($diff, 2, ',', '.') ?> pp</span>
                            <span class="row-actions">
                                <button type="button" class="btn-edit-regional" data-id="<?= $r['id'] ?>" title="Edit Data Wilayah">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <?php if (!$isBwi): ?>
                                    <button type="button" class="btn-delete-regional danger" data-id="<?= $r['id'] ?>" data-name="<?= htmlspecialchars($r['name']) ?>" title="Hapus Wilayah (Soft Delete)">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="source">Sumber nilai: BPS Provinsi Jawa Timur & Dataset Internal SI-DAPEDA</div>
        </article>
    </section>

    <!-- TABEL DATASET WILAYAH PEMBANDING (CRUD & SOFT DELETE) -->
    <article class="card large" style="margin-top:24px;">
        <div class="card-head">
            <div>
                <span class="eyebrow">MANAJEMEN DATASET WILAYAH</span>
                <h3>Daftar Wilayah Pembanding (<?= count($all_active_regional) ?>)</h3>
                <p>Kelola data pertumbuhan ekonomi daerah, perbarui angka, atau hapus wilayah (Soft Delete).</p>
            </div>
            <button class="button primary" id="btn-open-add-regional-2">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Tambah Wilayah</span>
            </button>
        </div>

        <div class="data-table">
            <div class="data-tr data-th" style="grid-template-columns: 50px 1.5fr 1fr 1fr 1fr 110px;">
                <span>No</span>
                <span>Wilayah</span>
                <span>Pertumbuhan</span>
                <span>Perubahan</span>
                <span>Status</span>
                <span>Aksi</span>
            </div>
            <?php foreach ($all_active_regional as $idx => $r): ?>
                <div class="data-tr" style="grid-template-columns: 50px 1.5fr 1fr 1fr 1fr 110px;">
                    <span><strong><?= $idx + 1 ?></strong></span>
                    <span>
                        <i class="region-avatar" style="display:inline-flex; width:22px; height:22px; border-radius:50%; background:#e0f2fe; color:#0369a1; font-size:9px; align-items:center; justify-content:center; font-weight:700; margin-right:6px;"><?= strtoupper(substr(str_replace(['Kab. ', 'Kota '], '', $r['name']), 0, 2)) ?></i>
                        <strong><?= htmlspecialchars($r['name']) ?></strong>
                    </span>
                    <span><strong><?= number_format($r['value'], 2, ',', '.') ?>%</strong></span>
                    <span><small class="<?= $r['change'] >= 0 ? 'positive' : 'negative' ?>"><?= ($r['change'] >= 0 ? '+' : '') . number_format($r['change'], 2, ',', '.') ?> pp</small></span>
                    <span><span class="badge success">Aktif</span></span>
                    <span class="row-actions">
                        <button type="button" class="btn-edit-regional" data-id="<?= $r['id'] ?>" title="Edit Data Wilayah">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            <span>Edit</span>
                        </button>
                        <?php if ($r['name'] !== 'Kab. Banyuwangi'): ?>
                            <button type="button" class="btn-delete-regional danger" data-id="<?= $r['id'] ?>" data-name="<?= htmlspecialchars($r['name']) ?>" title="Hapus Wilayah (Soft Delete)">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                            </button>
                        <?php endif; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>

    <!-- MODAL TAMBAH WILAYAH (CREATE MODAL) -->
    <div class="modal-backdrop" id="add-regional-modal" style="display:none;">
        <div class="modal admin-data-modal" style="max-width:520px;">
            <div class="modal-head">
                <div>
                    <span class="badge info">TAMBAH WILAYAH</span>
                    <h2>Tambah Wilayah Pembanding</h2>
                    <p>Tambahkan kabupaten/kota baru untuk perbandingan pertumbuhan ekonomi.</p>
                </div>
                <button class="icon-button" id="modal-btn-close-add-regional">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="form-add-regional" method="POST" action="<?= site_url('perbandingan_daerah/create') ?>">
                <div class="manual-form" style="grid-template-columns: 1fr 1fr;">
                    <label style="grid-column: 1 / 3;">
                        Nama Kabupaten / Kota
                        <input type="text" name="nama_wilayah" id="add-regional-name" placeholder="Contoh: Kab. Lumajang" required>
                    </label>
                    <label>
                        Pertumbuhan PDRB (%)
                        <input type="number" step="0.01" name="nilai_pertumbuhan" id="add-regional-value" placeholder="Contoh: 5.25" required>
                    </label>
                    <label>
                        Perubahan Tahunan (pp)
                        <input type="number" step="0.01" name="perubahan" id="add-regional-change" placeholder="Contoh: 0.20" required>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Deret Historis (Opsional)
                        <input type="text" name="deret_historis" id="add-regional-series" placeholder="Contoh: 5.10, 5.20, 5.30, 5.50, 5.40, -3.20, 4.00, 4.80, 5.05, 5.25">
                        <small style="color:var(--muted); font-size:7px; margin-top:3px; display:block;">Kosongkan jika ingin sistem menghitung proyeksi historis 10 tahun otomatis.</small>
                    </label>
                </div>

                <div id="add-regional-error" class="method-note" style="display:none; color:var(--red); margin-bottom:12px;"></div>

                <div class="modal-actions">
                    <button type="button" class="button ghost" id="modal-btn-cancel-add-regional">Batal</button>
                    <button type="submit" class="button primary" id="modal-btn-submit-add-regional">Simpan Wilayah</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT WILAYAH (UPDATE MODAL) -->
    <div class="modal-backdrop" id="edit-regional-modal" style="display:none;">
        <div class="modal admin-data-modal" style="max-width:520px;">
            <div class="modal-head">
                <div>
                    <span class="badge warning">PERBARUI WILAYAH</span>
                    <h2>Edit Data Wilayah Pembanding</h2>
                    <p>Perbarui nilai pertumbuhan dan perubahan tren wilayah yang tersimpan di MySQL.</p>
                </div>
                <button class="icon-button" id="modal-btn-close-edit-regional">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="form-edit-regional" method="POST" action="<?= site_url('perbandingan_daerah/update') ?>">
                <input type="hidden" name="id" id="edit-regional-id">
                <div class="manual-form" style="grid-template-columns: 1fr 1fr;">
                    <label style="grid-column: 1 / 3;">
                        Nama Kabupaten / Kota
                        <input type="text" name="nama_wilayah" id="edit-regional-name" required>
                    </label>
                    <label>
                        Pertumbuhan PDRB (%)
                        <input type="number" step="0.01" name="nilai_pertumbuhan" id="edit-regional-value" required>
                    </label>
                    <label>
                        Perubahan Tahunan (pp)
                        <input type="number" step="0.01" name="perubahan" id="edit-regional-change" required>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Deret Historis (10 Nilai dipisah koma)
                        <input type="text" name="deret_historis" id="edit-regional-series">
                    </label>
                </div>

                <div id="edit-regional-error" class="method-note" style="display:none; color:var(--red); margin-bottom:12px;"></div>

                <div class="modal-actions">
                    <button type="button" class="button ghost" id="modal-btn-cancel-edit-regional">Batal</button>
                    <button type="submit" class="button primary" id="modal-btn-submit-edit-regional">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
