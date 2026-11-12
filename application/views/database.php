<div class="page admin-page">
    <div class="admin-intro">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions">
            <button class="button primary" id="btn-open-add-modal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14M5 12h14"/></svg>
                <span>Tambah Data</span>
            </button>
            <span class="badge simulation">DATABASE CI 3</span>
        </div>
    </div>

    <!-- TOOLBAR -->
    <div class="management-toolbar">
        <div class="search wide">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
            <input type="text" id="admin-search-input" value="<?= htmlspecialchars($query ?? '') ?>" placeholder="Cari kode, indikator, klaster, wilayah...">
        </div>
        <select id="admin-filter-status">
            <option value="Semua status" <?= ($status === 'Semua status' || !$status) ? 'selected' : '' ?>>Semua status</option>
            <option value="Tervalidasi" <?= $status === 'Tervalidasi' ? 'selected' : '' ?>>Tervalidasi</option>
            <option value="Perlu review" <?= $status === 'Perlu review' ? 'selected' : '' ?>>Perlu review</option>
            <option value="Belum lengkap" <?= $status === 'Belum lengkap' ? 'selected' : '' ?>>Belum lengkap</option>
        </select>
        <select id="admin-sort">
            <option value="year" <?= $sort === 'year' ? 'selected' : '' ?>>Urut tahun terbaru</option>
            <option value="indicator" <?= $sort === 'indicator' ? 'selected' : '' ?>>Nama indikator</option>
            <option value="value" <?= $sort === 'value' ? 'selected' : '' ?>>Nilai terbesar</option>
        </select>
        <button class="button secondary" id="btn-export-database-csv">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12M7 10l5 5 5-5M4 21h16"/></svg>
            <span>Ekspor CSV</span>
        </button>
    </div>

    <!-- TABLE CARD -->
    <article class="card data-card">
        <div class="card-head">
            <div>
                <span class="eyebrow">DATABASE REPOSITORI</span>
                <h3>Database Indikator Ekonomi</h3>
                <p>Menampilkan <?= count($records) ?> rekaman tersimpan di MySQL</p>
            </div>
        </div>

        <div class="data-table">
            <div class="data-tr data-th">
                <span>Kode</span>
                <span>Indikator</span>
                <span>Klaster</span>
                <span>Periode</span>
                <span>Nilai</span>
                <span>Status</span>
                <span>Aksi</span>
            </div>
            
            <div id="admin-table-body">
                <?php if (empty($records)): ?>
                    <div class="table-empty">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v7c0 1.7 3.6 3 8 3s8-1.3 8-3V5M4 12v7c0 1.7 3.6 3 8 3s8-1.3 8-3v-7"/></svg>
                        <strong>Tidak ada rekaman data yang cocok.</strong>
                    </div>
                <?php else: ?>
                    <?php foreach ($records as $item): ?>
                        <div class="data-tr" data-code="<?= $item['code'] ?>" data-year="<?= $item['year'] ?>">
                            <span><code><?= htmlspecialchars($item['code']) ?></code></span>
                            <span>
                                <strong><?= htmlspecialchars($item['indicator']) ?></strong>
                                <small><?= htmlspecialchars($item['region']) ?> • <?= htmlspecialchars($item['source']) ?></small>
                            </span>
                            <span><?= htmlspecialchars($item['cluster']) ?></span>
                            <span><?= htmlspecialchars($item['period']) ?> <?= $item['year'] ?></span>
                            <span><strong><?= number_format($item['value'], 2, ',', '.') ?></strong> <?= htmlspecialchars($item['unit']) ?></span>
                            <span>
                                <span class="badge <?= $item['status'] === 'Tervalidasi' ? 'success' : ($item['status'] === 'Perlu review' ? 'warning' : 'neutral') ?>">
                                    <?= $item['status'] ?>
                                </span>
                            </span>
                            <span class="row-actions">
                                <button class="btn-toggle-record-status" title="Ubah status validasi" data-code="<?= $item['code'] ?>" data-year="<?= $item['year'] ?>" data-status="<?= $item['status'] ?>">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1v.1h-4v-.1a1.7 1.7 0 0 0-1.1-1.6 1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1-.4h-.1v-4H3A1.7 1.7 0 0 0 4.6 8.5a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1v-.1h4V3a1.7 1.7 0 0 0 1.1 1.6 1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.4 9c.1.4.3.7.6 1 .3.2.6.4 1 .4h.1v4H21a1.7 1.7 0 0 0-1.6.6Z"/></svg>
                                </button>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="pagination">
            <span>Total rekaman: <?= count($records) ?> data</span>
            <span>Data tersinkronisasi dengan MySQL Database</span>
        </div>
    </article>

    <!-- MODAL TAMBAH DATA -->
    <div class="modal-backdrop" id="add-data-modal" style="display:none;">
        <div class="modal admin-data-modal">
            <div class="modal-head">
                <div>
                    <span class="badge info">INPUT MANUAL</span>
                    <h2>Tambah Data Indikator Ekonomi</h2>
                    <p>Data baru akan tersimpan ke database MySQL dengan status Perlu review.</p>
                </div>
                <button class="icon-button" id="modal-btn-close-add">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="form-manual-add">
                <div class="manual-form">
                    <label>
                        Kode Indikator
                        <input type="text" name="code" id="add-code" placeholder="EKO-100" required>
                    </label>
                    <label>
                        Nama Indikator
                        <input type="text" name="indicator" id="add-indicator" placeholder="Pertumbuhan Ekonomi" required>
                    </label>
                    <label>
                        Klaster
                        <select name="cluster" id="add-cluster">
                            <option>Output & Pertumbuhan</option>
                            <option>Produksi Sektoral</option>
                            <option>Pariwisata & Ekonomi Kreatif</option>
                            <option>Ketenagakerjaan</option>
                            <option>Harga & Daya Beli</option>
                            <option>Investasi & Usaha</option>
                            <option>Fiskal Daerah</option>
                            <option>Kesejahteraan & Inklusivitas</option>
                            <option>Keterkaitan Antarsektor</option>
                        </select>
                    </label>
                    <label>
                        Wilayah
                        <input type="text" name="region" id="add-region" value="Kabupaten Banyuwangi" required>
                    </label>
                    <label>
                        Periode
                        <select name="period" id="add-period">
                            <option>Tahunan</option>
                            <option>Semesteran</option>
                            <option>Triwulanan</option>
                            <option>Bulanan</option>
                        </select>
                    </label>
                    <label>
                        Tahun
                        <input type="number" name="year" id="add-year" value="2024" required>
                    </label>
                    <label>
                        Nilai
                        <input type="number" step="any" name="value" id="add-value" placeholder="5.18" required>
                    </label>
                    <label>
                        Satuan
                        <input type="text" name="unit" id="add-unit" value="%" required>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Sumber Data
                        <input type="text" name="source" id="add-source" value="BPS Kabupaten Banyuwangi" required>
                    </label>
                </div>

                <div id="add-modal-error" class="method-note" style="display:none; color:var(--red);"></div>

                <div class="modal-actions">
                    <button type="button" class="button ghost" id="modal-btn-cancel-add">Batal</button>
                    <button type="submit" class="button primary" id="modal-btn-submit-add">Simpan ke Database</button>
                </div>
            </form>
        </div>
    </div>
</div>

