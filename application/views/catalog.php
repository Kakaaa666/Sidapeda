<div class="page admin-page">
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
        <!-- TEMPAT SAMPAH KATALOG -->
        <article class="card large" style="margin-bottom:24px; border:1px dashed #f43f5e; background:rgba(244,63,94,0.03);">
            <div class="card-head">
                <div>
                    <span class="badge danger">TEMPAT SAMPAH (SOFT DELETE)</span>
                    <h3>Daftar Master Indikator Terhapus (<?= count($catalog) ?>)</h3>
                    <p>Klik tombol Pulihkan untuk mengembalikan indikator ke katalog aktif.</p>
                </div>
                <a href="<?= site_url('catalog') ?>" class="button ghost">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    <span>Kembali ke Katalog Aktif</span>
                </a>
            </div>
            <div class="data-table">
                <div class="data-tr data-th" style="grid-template-columns: 120px 2fr 1.5fr 1.2fr 120px;">
                    <span>Kode</span>
                    <span>Nama Indikator</span>
                    <span>Klaster</span>
                    <span>Dihapus Pada</span>
                    <span>Aksi</span>
                </div>
                <?php if (empty($catalog)): ?>
                    <div style="padding:24px; text-align:center; color:var(--muted); font-size:11px;">
                        Tempat sampah kosong. Tidak ada indikator katalog yang terhapus.
                    </div>
                <?php else: ?>
                    <?php foreach ($catalog as $item): ?>
                        <div class="data-tr" style="grid-template-columns: 120px 2fr 1.5fr 1.2fr 120px;">
                            <span><code><?= htmlspecialchars($item['code']) ?></code></span>
                            <span><strong><?= htmlspecialchars($item['name']) ?></strong></span>
                            <span><?= htmlspecialchars($item['cluster']) ?></span>
                            <span><small><?= htmlspecialchars($item['deleted_at'] ?? '-') ?></small></span>
                            <span class="row-actions">
                                <button type="button" class="btn-restore-catalog" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>" title="Pulihkan Indikator">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                    <span>Pulihkan</span>
                                </button>
                                <button type="button" class="btn-hard-delete-catalog danger" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>" title="Hapus Permanen">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>
    <?php endif; ?>

    <div class="admin-intro">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions">
            <?php if ($trash_count > 0): ?>
                <?php if ($status === 'Terhapus'): ?>
                    <a href="<?= site_url('catalog') ?>" class="button ghost">
                        <span>Kembali ke Aktif</span>
                    </a>
                <?php else: ?>
                    <a href="<?= site_url('catalog?status=Terhapus') ?>" class="button ghost danger">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        <span>Sampah (<?= $trash_count ?>)</span>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            <button class="button primary" id="btn-open-add-catalog">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Tambah Indikator Master</span>
            </button>
            <button class="button secondary" id="btn-export-catalog-csv">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12M7 10l5 5 5-5M4 21h16"/></svg>
                <span>Ekspor CSV</span>
            </button>
        </div>
    </div>

    <!-- GRAFIK DISTRIBUSI KLASTER INDIKATOR (Sesuai Data Katalog) -->
    <article class="card large" style="margin-bottom:20px;">
        <div class="card-head">
            <div>
                <span class="eyebrow">DISTRIBUSI KLASTER PEMBANGUNAN</span>
                <h3>Grafik Komposisi Indikator per Klaster Data</h3>
                <p>Proporsi sebaran indikator strategis daerah menurut kelompok klaster tema pembangunan.</p>
            </div>
        </div>
        <div id="catalog-cluster-chart" class="chart-wrap dynamic-chart" style="height:auto; min-height:140px; padding:6px 0;">
            <!-- Rendered dynamically by JS -->
        </div>
    </article>

    <article class="card data-card">
        <div class="card-head">
            <div>
                <span class="eyebrow">STANDAR METADATA</span>
                <h3>Daftar Master Indikator Daerah (<?= count($catalog) ?>)</h3>
                <p>Memuat metadata, definisi operasional, dan walidata indikator strategis.</p>
            </div>
        </div>

        <div class="result-table-wrap">
            <table class="result-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Indikator</th>
                        <th>Klaster Pembangunan</th>
                        <th>Satuan</th>
                        <th>Frekuensi</th>
                        <th>Walidata / Sumber</th>
                        <th>Definisi Operasional</th>
                        <th style="width:110px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($catalog)): ?>
                        <tr>
                            <td colspan="8" style="text-align:center; padding:24px; color:var(--muted);">Tidak ada indikator katalog ditemukan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($catalog as $item): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($item['code']) ?></code></td>
                                <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
                                <td><?= htmlspecialchars($item['cluster']) ?></td>
                                <td><?= htmlspecialchars($item['unit']) ?></td>
                                <td><?= htmlspecialchars($item['frequency']) ?></td>
                                <td><?= htmlspecialchars($item['source']) ?></td>
                                <td><?= htmlspecialchars($item['definition']) ?></td>
                                <td>
                                    <div class="row-actions">
                                        <button type="button" class="btn-edit-catalog" data-id="<?= $item['id'] ?>" title="Edit Indikator">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            <span>Edit</span>
                                        </button>
                                        <button type="button" class="btn-delete-catalog danger" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>" title="Hapus (Soft Delete)">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>

    <!-- MODAL TAMBAH KATALOG -->
    <div class="modal-backdrop" id="add-catalog-modal" style="display:none;">
        <div class="modal admin-data-modal" style="max-width:540px;">
            <div class="modal-head">
                <div>
                    <span class="badge info">TAMBAH KATALOG</span>
                    <h2>Tambah Master Indikator</h2>
                    <p>Simpan metadata indikator ke tabel database <code>katalog_indikator</code>.</p>
                </div>
                <button class="icon-button" id="modal-btn-close-add-catalog">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="form-add-catalog" method="POST" action="<?= site_url('catalog/create') ?>">
                <div class="manual-form" style="grid-template-columns: 1fr 1fr;">
                    <label>
                        Kode Indikator
                        <input type="text" name="kode_indikator" id="add-catalog-code" placeholder="Contoh: ind_pdrb_kapita" required>
                    </label>
                    <label>
                        Satuan Ukuran
                        <input type="text" name="satuan" id="add-catalog-unit" placeholder="Contoh: % / Poin / Juta Rp" required>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Nama Indikator
                        <input type="text" name="nama_indikator" id="add-catalog-name" placeholder="Contoh: PDRB per Kapita ADHB" required>
                    </label>
                    <label>
                        Klaster Pembangunan
                        <input type="text" name="klaster" id="add-catalog-cluster" placeholder="Contoh: Output & Pertumbuhan" required>
                    </label>
                    <label>
                        Frekuensi Rilis
                        <select name="frekuensi" id="add-catalog-freq">
                            <option value="Tahunan">Tahunan</option>
                            <option value="Semesteran">Semesteran</option>
                            <option value="Triwulanan">Triwulanan</option>
                            <option value="Bulanan">Bulanan</option>
                        </select>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Sumber Data / Walidata
                        <input type="text" name="sumber_data" id="add-catalog-source" placeholder="Contoh: BPS Kabupaten Banyuwangi" required>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Definisi Operasional
                        <textarea name="definisi_operasional" id="add-catalog-def" rows="2" placeholder="Uraikan konsep dan batasan penghitungan indikator..."></textarea>
                    </label>
                </div>

                <div id="add-catalog-error" class="method-note" style="display:none; color:var(--red); margin-bottom:12px;"></div>

                <div class="modal-actions">
                    <button type="button" class="button ghost" id="modal-btn-cancel-add-catalog">Batal</button>
                    <button type="submit" class="button primary" id="modal-btn-submit-add-catalog">Simpan Indikator</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT KATALOG -->
    <div class="modal-backdrop" id="edit-catalog-modal" style="display:none;">
        <div class="modal admin-data-modal" style="max-width:540px;">
            <div class="modal-head">
                <div>
                    <span class="badge warning">PERBARUI KATALOG</span>
                    <h2>Edit Master Indikator</h2>
                    <p>Perbarui definisi operasional, walidata, atau klaster indikator.</p>
                </div>
                <button class="icon-button" id="modal-btn-close-edit-catalog">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="form-edit-catalog" method="POST">
                <input type="hidden" name="id" id="edit-catalog-id">
                <div class="manual-form" style="grid-template-columns: 1fr 1fr;">
                    <label>
                        Kode Indikator
                        <input type="text" name="kode_indikator" id="edit-catalog-code" required>
                    </label>
                    <label>
                        Satuan Ukuran
                        <input type="text" name="satuan" id="edit-catalog-unit" required>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Nama Indikator
                        <input type="text" name="nama_indikator" id="edit-catalog-name" required>
                    </label>
                    <label>
                        Klaster Pembangunan
                        <input type="text" name="klaster" id="edit-catalog-cluster" required>
                    </label>
                    <label>
                        Frekuensi Rilis
                        <select name="frekuensi" id="edit-catalog-freq">
                            <option value="Tahunan">Tahunan</option>
                            <option value="Semesteran">Semesteran</option>
                            <option value="Triwulanan">Triwulanan</option>
                            <option value="Bulanan">Bulanan</option>
                        </select>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Sumber Data / Walidata
                        <input type="text" name="sumber_data" id="edit-catalog-source" required>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Definisi Operasional
                        <textarea name="definisi_operasional" id="edit-catalog-def" rows="2"></textarea>
                    </label>
                </div>

                <div id="edit-catalog-error" class="method-note" style="display:none; color:var(--red); margin-bottom:12px;"></div>

                <div class="modal-actions">
                    <button type="button" class="button ghost" id="modal-btn-cancel-edit-catalog">Batal</button>
                    <button type="submit" class="button primary" id="modal-btn-submit-edit-catalog">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

