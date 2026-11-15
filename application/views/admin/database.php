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
                <span><strong>Tempat Sampah (Soft Delete):</strong> Menampilkan data yang dihapus sementara. Anda dapat memulihkan (Restore) kembali ke daftar aktif.</span>
            </div>
            <a href="<?= site_url('database') ?>" class="button secondary small" style="height:28px; padding:0 10px; font-size:8px;">Kembali ke Data Aktif</a>
        </div>
    <?php endif; ?>

    <!-- TOOLBAR -->
    <div class="management-toolbar">
        <div class="search wide">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
            <input type="text" id="admin-search-input" value="<?= htmlspecialchars($query ?? '') ?>" placeholder="Cari kode, indikator, klaster, wilayah...">
        </div>
        <select id="admin-filter-status">
            <option value="Semua status" <?= ($status === 'Semua status' || !$status) ? 'selected' : '' ?>>Semua status (Aktif)</option>
            <option value="Tervalidasi" <?= $status === 'Tervalidasi' ? 'selected' : '' ?>>Tervalidasi</option>
            <option value="Perlu review" <?= $status === 'Perlu review' ? 'selected' : '' ?>>Perlu review</option>
            <option value="Belum lengkap" <?= ($status === 'Belum lengkap' || $status === 'Ditolak') ? 'selected' : '' ?>>Belum lengkap / Ditolak</option>
            <option value="Terhapus" <?= $status === 'Terhapus' ? 'selected' : '' ?>>🗑️ Sampah / Terhapus (<?= (int)$trash_count ?>)</option>
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
                <span class="eyebrow"><?= ($status === 'Terhapus') ? 'TEMPAT SAMPAH (SOFT DELETED)' : 'DATABASE REPOSITORI' ?></span>
                <h3><?= ($status === 'Terhapus') ? 'Data Indikator Terhapus' : 'Database Indikator Ekonomi' ?></h3>
                <p>Menampilkan <?= count($records) ?> rekaman <?= ($status === 'Terhapus') ? 'di tempat sampah' : 'tersimpan dalam repositori' ?></p>
            </div>
        </div>

        <?php if ($status !== 'Terhapus' && !empty($records)): ?>
            <?php
            $valCount = 0; $reviewCount = 0; $clusterCounts = [];
            foreach ($records as $rec) {
                $st = $rec['status'] ?? 'Tervalidasi';
                if ($st === 'Tervalidasi') $valCount++;
                else $reviewCount++;
                $cl = $rec['klaster'] ?? 'Lainnya';
                $clusterCounts[$cl] = ($clusterCounts[$cl] ?? 0) + 1;
            }
            $totRec = count($records) ?: 1;
            $maxCl = !empty($clusterCounts) ? max($clusterCounts) : 1;
            ?>
            <div style="display:grid; grid-template-columns: 1fr 1.3fr; gap:16px; padding:14px 18px; background:#f8fafc; border-bottom:1px solid var(--line);">
                <div>
                    <strong style="font-size:9px; color:var(--navy); display:block; margin-bottom:6px;">Status Validasi Data</strong>
                    <div style="display:flex; height:10px; border-radius:5px; overflow:hidden; background:#e2e8f0; margin-bottom:6px;">
                        <div style="width:<?= round(($valCount/$totRec)*100) ?>%; background:var(--green);" title="Tervalidasi: <?= $valCount ?>"></div>
                        <div style="width:<?= round(($reviewCount/$totRec)*100) ?>%; background:var(--yellow);" title="Perlu Review: <?= $reviewCount ?>"></div>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:8px; color:var(--muted);">
                        <span><i style="display:inline-block; width:7px; height:7px; border-radius:50%; background:var(--green); margin-right:3px;"></i>Tervalidasi: <strong><?= $valCount ?></strong> (<?= round(($valCount/$totRec)*100) ?>%)</span>
                        <span><i style="display:inline-block; width:7px; height:7px; border-radius:50%; background:var(--yellow); margin-right:3px;"></i>Perlu Review: <strong><?= $reviewCount ?></strong></span>
                    </div>
                </div>
                <div>
                    <strong style="font-size:9px; color:var(--navy); display:block; margin-bottom:6px;">Distribusi Rekaman per Klaster</strong>
                    <div style="display:grid; gap:5px;">
                        <?php foreach (array_slice($clusterCounts, 0, 4) as $cName => $cCnt): ?>
                            <div style="display:grid; grid-template-columns: 120px 1fr 35px; align-items:center; gap:6px; font-size:8px;">
                                <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= htmlspecialchars($cName) ?></span>
                                <div style="height:6px; border-radius:3px; background:#e2e8f0; overflow:hidden;">
                                    <div style="width:<?= round(($cCnt/$maxCl)*100) ?>%; height:100%; background:var(--blue);"></div>
                                </div>
                                <strong style="text-align:right; font-size:8px;"><?= $cCnt ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

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
                    <div class="table-empty" style="padding:40px 20px; text-align:center; color:var(--muted); display:flex; flex-direction:column; align-items:center; gap:8px;">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v7c0 1.7 3.6 3 8 3s8-1.3 8-3V5M4 12v7c0 1.7 3.6 3 8 3s8-1.3 8-3v-7"/></svg>
                        <strong><?= ($status === 'Terhapus') ? 'Tempat sampah kosong. Tidak ada data yang dihapus.' : 'Tidak ada rekaman data yang cocok.' ?></strong>
                    </div>
                <?php else: ?>
                    <?php foreach ($records as $item): ?>
                        <div class="data-tr" data-id="<?= $item['id'] ?>" data-code="<?= $item['code'] ?>" data-year="<?= $item['year'] ?>">
                            <span><code><?= htmlspecialchars($item['code']) ?></code></span>
                            <span>
                                <strong><?= htmlspecialchars($item['indicator']) ?></strong>
                                <small><?= htmlspecialchars($item['region']) ?> • <?= htmlspecialchars($item['source']) ?></small>
                                <?php if (!empty($item['notes']) && $item['status'] !== 'Tervalidasi'): ?>
                                    <div class="table-rejection-note" style="margin-top: 5px; font-size: 11px; line-height: 1.4; color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 4px 8px; display: inline-flex; align-items: flex-start; gap: 5px; max-width: 100%;">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                        <span><strong>Catatan:</strong> <?= htmlspecialchars($item['notes']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </span>
                            <span><?= htmlspecialchars($item['cluster']) ?></span>
                            <span><?= htmlspecialchars($item['period']) ?> <?= $item['year'] ?></span>
                            <span><strong><?= number_format($item['value'], 2, ',', '.') ?></strong> <?= htmlspecialchars($item['unit']) ?></span>
                            <span>
                                <?php if ($status === 'Terhapus' || !empty($item['is_deleted'])): ?>
                                    <span class="badge danger" title="Dihapus pada: <?= htmlspecialchars($item['deleted_at'] ?? '-') ?>">
                                        Terhapus
                                    </span>
                                <?php else: ?>
                                    <span class="badge <?= $item['status'] === 'Tervalidasi' ? 'success' : ($item['status'] === 'Perlu review' ? 'warning' : 'danger') ?>">
                                        <?= $item['status'] ?>
                                    </span>
                                    <?php if (!empty($item['verified_by']) && $item['status'] !== 'Perlu review'): ?>
                                        <small style="display:block; font-size:10px; color:#64748b; margin-top:3px;">Oleh: <?= htmlspecialchars($item['verified_by']) ?></small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </span>
                            <span class="row-actions">
                                <?php if ($status === 'Terhapus' || !empty($item['is_deleted'])): ?>
                                    <!-- TOMBOL RESTORE (PULIHKAN DARI SOFT DELETE) -->
                                    <button type="button" class="btn-restore-record" data-id="<?= $item['id'] ?>" data-indicator="<?= htmlspecialchars($item['indicator']) ?>" title="Pulihkan Data Ini">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                        <span>Pulihkan</span>
                                    </button>
                                    <button type="button" class="btn-hard-delete-record danger" data-id="<?= $item['id'] ?>" data-indicator="<?= htmlspecialchars($item['indicator']) ?>" title="Hapus Permanen">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                <?php else: ?>
                                    <!-- TOMBOL EDIT DATA -->
                                    <button type="button" class="btn-edit-record" data-id="<?= $item['id'] ?>" title="Edit Data">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <!-- TOMBOL SOFT DELETE -->
                                    <button type="button" class="btn-delete-record danger" data-id="<?= $item['id'] ?>" data-indicator="<?= htmlspecialchars($item['indicator']) ?>" title="Hapus Data (Soft Delete)">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                    </button>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="pagination">
            <span>Total rekaman: <?= count($records) ?> data</span>
            <span>Data tersinkronisasi dengan sistem repositori daerah</span>
        </div>
    </article>

    <!-- MODAL TAMBAH DATA (CREATE) -->
    <div class="modal-backdrop" id="add-data-modal" style="display:none;">
        <div class="modal admin-data-modal">
            <div class="modal-head">
                <div>
                    <span class="badge info">TAMBAH DATA BARU</span>
                    <h2>Tambah Data Indikator Ekonomi</h2>
                    <p>Data baru akan otomatis tersimpan dengan status <strong>Perlu review</strong> untuk diverifikasi.</p>
                </div>
                <button class="icon-button" id="modal-btn-close-add">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="form-manual-add" method="POST" action="<?= site_url('database/create') ?>">
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
                        <input type="text" name="cluster" id="add-cluster" list="cluster-options" value="Output & Pertumbuhan" required placeholder="Pilih atau ketik klaster...">
                    </label>
                    <label>
                        Wilayah
                        <input type="text" name="region" id="add-region" value="<?= htmlspecialchars($nama_wilayah ?? 'Kabupaten Banyuwangi') ?>" required>
                        <input type="hidden" name="kode_wilayah" id="add-kode-wilayah" value="<?= htmlspecialchars($active_kode_wilayah ?? '35.10') ?>">
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

                <div id="add-modal-error" class="method-note" style="display:none; color:var(--red); margin-bottom:12px;"></div>

                <div class="modal-actions">
                    <button type="button" class="button ghost" id="modal-btn-cancel-add">Batal</button>
                    <button type="submit" class="button primary" id="modal-btn-submit-add">Simpan ke Database</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT DATA (UPDATE) -->
    <div class="modal-backdrop" id="edit-data-modal" style="display:none;">
        <div class="modal admin-data-modal">
            <div class="modal-head">
                <div>
                    <span class="badge warning">PERBARUI DATA</span>
                    <h2>Edit Data Indikator Ekonomi</h2>
                    <p>Perbarui informasi data. Data yang diedit otomatis berstatus <strong>Perlu review</strong> untuk diverifikasi ulang.</p>
                </div>
                <button class="icon-button" id="modal-btn-close-edit">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <!-- REJECTION REASON CALLOUT -->
            <div id="edit-rejection-callout" style="display:none; background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:10px 14px; margin-top:14px; font-size:12px; color:#991b1b;">
                <strong style="display:flex; align-items:center; gap:6px; margin-bottom:4px; font-size:12px; color:#b91c1c;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    Catatan Penolakan Sebelumnya:
                </strong>
                <span id="edit-rejection-reason-text" style="line-height:1.45; display:block;"></span>
                <div style="margin-top:6px; font-size:11px; color:#059669; font-weight:600; display:flex; align-items:center; gap:5px;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Status otomatis menjadi "Perlu review" dan catatan ini otomatis terhapus saat disimpan.</span>
                </div>
            </div>

            <form id="form-manual-edit" method="POST">
                <input type="hidden" name="id" id="edit-id">
                <div class="manual-form">
                    <label>
                        Kode Indikator
                        <input type="text" name="code" id="edit-code" required>
                    </label>
                    <label>
                        Nama Indikator
                        <input type="text" name="indicator" id="edit-indicator" required>
                    </label>
                    <label>
                        Klaster
                        <input type="text" name="cluster" id="edit-cluster" list="cluster-options" required placeholder="Pilih atau ketik klaster...">
                    </label>
                    <label>
                        Wilayah
                        <input type="text" name="region" id="edit-region" required>
                        <input type="hidden" name="kode_wilayah" id="edit-kode-wilayah" value="<?= htmlspecialchars($active_kode_wilayah ?? '35.10') ?>">
                    </label>
                    <label>
                        Periode
                        <select name="period" id="edit-period">
                            <option>Tahunan</option>
                            <option>Semesteran</option>
                            <option>Triwulanan</option>
                            <option>Bulanan</option>
                        </select>
                    </label>
                    <label>
                        Tahun
                        <input type="number" name="year" id="edit-year" required>
                    </label>
                    <label>
                        Nilai
                        <input type="number" step="any" name="value" id="edit-value" required>
                    </label>
                    <label>
                        Satuan
                        <input type="text" name="unit" id="edit-unit" required>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Sumber Data
                        <input type="text" name="source" id="edit-source" required>
                    </label>
                </div>

                <div id="edit-modal-error" class="method-note" style="display:none; color:var(--red); margin-bottom:12px;"></div>

                <div class="modal-actions">
                    <button type="button" class="button ghost" id="modal-btn-cancel-edit">Batal</button>
                    <button type="submit" class="button primary" id="modal-btn-submit-edit">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- DATALIST KLASTER INDIKATOR -->
    <datalist id="cluster-options">
        <option value="Output & Pertumbuhan">
        <option value="Harga & Inflasi">
        <option value="Ketenagakerjaan">
        <option value="Kesejahteraan Sosial">
        <option value="Investasi & Usaha">
        <option value="Fiskal Daerah">
        <option value="Pariwisata & Ekonomi Kreatif">
        <option value="Produksi Sektoral">
        <option value="Keterkaitan Antarsektor">
    </datalist>
</div>
