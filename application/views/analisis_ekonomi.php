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
        <!-- TEMPAT SAMPAH SEKTOR EKONOMI -->
        <article class="card large" style="margin-bottom:24px; border:1px dashed #f43f5e; background:rgba(244,63,94,0.03);">
            <div class="card-head">
                <div>
                    <span class="badge danger">TEMPAT SAMPAH (SOFT DELETE)</span>
                    <h3>Daftar Sektor Ekonomi Terhapus (<?= count($sectors) ?>)</h3>
                    <p>Klik tombol Pulihkan untuk mengembalikan sektor ke kalkulasi analisis ekonomi.</p>
                </div>
                <a href="<?= site_url('analisis_ekonomi') ?>" class="button ghost">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    <span>Kembali ke Sektor Aktif</span>
                </a>
            </div>
            <div class="data-table">
                <div class="data-tr data-th" style="grid-template-columns: 1fr 2fr 1fr 1fr 1.2fr 120px;">
                    <span>Kode</span>
                    <span>Nama Sektor</span>
                    <span>Porsi PDRB</span>
                    <span>Pertumbuhan</span>
                    <span>Dihapus Pada</span>
                    <span>Aksi</span>
                </div>
                <?php if (empty($sectors)): ?>
                    <div style="padding:24px; text-align:center; color:var(--muted); font-size:11px;">
                        Tempat sampah kosong. Tidak ada sektor yang terhapus.
                    </div>
                <?php else: ?>
                    <?php foreach ($sectors as $item): ?>
                        <div class="data-tr" style="grid-template-columns: 1fr 2fr 1fr 1fr 1.2fr 120px;">
                            <span><code><?= htmlspecialchars($item['key'] ?? $item['kode_sektor'] ?? '') ?></code></span>
                            <span><strong><?= htmlspecialchars($item['name'] ?? $item['nama_sektor'] ?? '') ?></strong></span>
                            <span><?= number_format($item['shareLocal'] ?? 0, 2, ',', '.') ?>%</span>
                            <span><?= number_format($item['growthLocal'] ?? 0, 2, ',', '.') ?>%</span>
                            <span><small><?= htmlspecialchars($item['deleted_at'] ?? '-') ?></small></span>
                            <span class="row-actions">
                                <button type="button" class="btn-restore-sector" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name'] ?? $item['nama_sektor'] ?? '') ?>" title="Pulihkan Sektor">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                    <span>Pulihkan</span>
                                </button>
                                <button type="button" class="btn-hard-delete-sector danger" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name'] ?? $item['nama_sektor'] ?? '') ?>" title="Hapus Permanen">
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
                    <a href="<?= site_url('analisis_ekonomi') ?>" class="button ghost">
                        <span>Kembali ke Aktif</span>
                    </a>
                <?php else: ?>
                    <a href="<?= site_url('analisis_ekonomi?status=Terhapus') ?>" class="button ghost danger">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        <span>Sampah (<?= $trash_count ?>)</span>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            <button class="button primary" id="btn-open-add-sector">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Tambah Sektor</span>
            </button>
            <button class="button secondary" id="btn-export-analysis">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12M7 10l5 5 5-5M4 21h16"/></svg>
                <span>Ekspor CSV</span>
            </button>
        </div>
    </div>

    <div class="workspace analysis-workspace">
        <!-- SIDEBAR PANEL -->
        <aside class="analysis-panel">
            <div class="panel-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16l-6 7v6l-4 2v-8L4 5Z"/></svg>
                <strong>Metode Analisis Ekonomi Regional</strong>
            </div>

            <div id="analysis-menu-links">
                <button class="analysis-link active" data-mode="Location Quotient (LQ)">
                    <span>Location Quotient (LQ)</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
                </button>
                <button class="analysis-link" data-mode="Shift Share Analysis">
                    <span>Shift Share Analysis</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
                </button>
                <button class="analysis-link" data-mode="Tipologi Klassen">
                    <span>Tipologi Klassen</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
                </button>
                <button class="analysis-link" data-mode="Analisis Korelasi">
                    <span>Analisis Korelasi</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>

            <div class="divider"></div>
            <span class="eyebrow">DATASET DIGUNAKAN</span>
            <p class="panel-help">Dataset sektoral 7 lapangan usaha dan deret berkala indikator makro 2015–2024 Kabupaten Banyuwangi.</p>

            <button class="text-button" id="btn-toggle-formula">
                <span id="formula-btn-text">Tampilkan Formula</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 9 6 6 6-6"/></svg>
            </button>

            <div class="formula-panel" id="formula-box" style="display:none;">
                LQ = (Tenaga kerja sektor lokal / total lokal) ÷ (Tenaga kerja sektor provinsi / total provinsi).
            </div>
        </aside>

        <!-- MAIN ANALYSIS WORKSPACE -->
        <main class="correlation-workspace">
            <div class="correlation-toolbar">
                <div>
                    <span class="eyebrow">METODE ANALISIS</span>
                    <h2 id="active-method-heading">Location Quotient (LQ)</h2>
                    <p id="active-method-sub">Perhitungan ekonomi regional berbasis database SI-DAPEDA Banyuwangi.</p>
                </div>
            </div>

            <!-- DYNAMIC METHOD CONTENT -->
            <div id="analysis-content-container">
                <?php
                $localTot = 0;
                $provTot = 0;
                foreach ($sectors as $s) {
                    $localTot += (int)($s['tenaga_kerja_kini'] ?? $s['employmentCurrent'] ?? 1);
                    $provTot += (int)($s['tenaga_kerja_provinsi_kini'] ?? $s['provinceEmploymentCurrent'] ?? 1);
                }
                $localTot = $localTot ?: 1;
                $provTot = $provTot ?: 1;
                $basisCount = 0;
                $lqList = [];
                foreach ($sectors as $s) {
                    $lCur = (int)($s['tenaga_kerja_kini'] ?? $s['employmentCurrent'] ?? 1);
                    $pCur = (int)($s['tenaga_kerja_provinsi_kini'] ?? $s['provinceEmploymentCurrent'] ?? 1);
                    $lShare = ($lCur / $localTot) * 100;
                    $pShare = ($pCur / $provTot) * 100;
                    $lq = $pShare > 0 ? ($lShare / $pShare) : 0;
                    $isBasis = ($lq > 1.0);
                    if ($isBasis) $basisCount++;
                    $lqList[] = [
                        'name' => $s['nama_sektor'] ?? $s['name'] ?? '',
                        'short' => $s['nama_pendek'] ?? $s['short'] ?? '',
                        'lShare' => $lShare,
                        'pShare' => $pShare,
                        'lq' => $lq,
                        'isBasis' => $isBasis
                    ];
                }
                $maxLq = !empty($lqList) ? max(array_column($lqList, 'lq')) : 1.5;
                $nonBasis = count($sectors) - $basisCount;
                ?>
                <div class="method-summary-grid">
                    <div class="method-metric"><span>Sektor Basis</span><strong><?= $basisCount ?></strong><small>Nilai LQ > 1.0</small></div>
                    <div class="method-metric"><span>Sektor Nonbasis</span><strong><?= $nonBasis ?></strong><small>Nilai LQ ≤ 1.0</small></div>
                    <div class="method-metric"><span>LQ Tertinggi</span><strong><?= number_format($maxLq, 2, ',', '.') ?></strong><small>Sektor Unggulan</small></div>
                </div>
                <article class="card method-card">
                    <div class="card-head">
                        <div><span class="eyebrow">BARS HASIL PERHITUNGAN</span><h3>Location Quotient per Lapangan Usaha</h3></div>
                    </div>
                    <div class="lq-bars" style="margin: 16px 0;">
                        <?php foreach ($lqList as $r):
                            $w = min(100, round(($r['lq'] / max(2.0, $maxLq)) * 100));
                            $benchmarkX = round((1.0 / max(2.0, $maxLq)) * 100);
                        ?>
                            <div class="lq-bar-row">
                                <span title="<?= htmlspecialchars($r['name']) ?>"><?= htmlspecialchars($r['short']) ?></span>
                                <div class="lq-track">
                                    <i class="<?= $r['isBasis'] ? 'basis' : '' ?>" style="width: <?= $w ?>%"></i>
                                    <b style="left: <?= $benchmarkX ?>%"></b>
                                </div>
                                <strong><?= number_format($r['lq'], 2, ',', '.') ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="result-table-wrap">
                        <table class="result-table">
                            <thead><tr><th>Lapangan Usaha</th><th>Share Lokal</th><th>Share Provinsi</th><th>Nilai LQ</th><th>Klasifikasi</th></tr></thead>
                            <tbody>
                                <?php foreach ($lqList as $r): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($r['name']) ?></strong></td>
                                        <td><?= number_format($r['lShare'], 2, ',', '.') ?>%</td>
                                        <td><?= number_format($r['pShare'], 2, ',', '.') ?>%</td>
                                        <td><strong><?= number_format($r['lq'], 3, ',', '.') ?></strong></td>
                                        <td><span class="badge <?= $r['isBasis'] ? 'success' : 'neutral' ?>"><?= $r['isBasis'] ? 'Basis' : 'Nonbasis' ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
            </div>
        </main>
    </div>

    <!-- TABEL DATASET SEKTOR EKONOMI (CRUD & SOFT DELETE) -->
    <article class="card large" style="margin-top:24px;">
        <div class="card-head">
            <div>
                <span class="eyebrow">MANAJEMEN DATASET SEKTOR</span>
                <h3>Daftar Sektor & Lapangan Usaha (<?= count($sectors) ?>)</h3>
                <p>Kelola data porsi PDRB, pertumbuhan, dan serapan tenaga kerja sektor ekonomi Banyuwangi.</p>
            </div>
        </div>

        <div class="data-table">
            <div class="data-tr data-th" style="grid-template-columns: 100px 2.2fr 1.2fr 1.2fr 1.5fr 110px;">
                <span>Kode</span>
                <span>Nama Sektor</span>
                <span>Porsi PDRB</span>
                <span>Pertumbuhan</span>
                <span>Tenaga Kerja Kini</span>
                <span>Aksi</span>
            </div>
            <?php foreach ($sectors as $s): ?>
                <div class="data-tr" style="grid-template-columns: 100px 2.2fr 1.2fr 1.2fr 1.5fr 110px;">
                    <span><code><?= htmlspecialchars($s['key'] ?? $s['kode_sektor'] ?? '') ?></code></span>
                    <span><strong><?= htmlspecialchars($s['name'] ?? $s['nama_sektor'] ?? '') ?></strong></span>
                    <span><?= number_format($s['shareLocal'] ?? 0, 2, ',', '.') ?>%</span>
                    <span><?= number_format($s['growthLocal'] ?? 0, 2, ',', '.') ?>%</span>
                    <span><?= number_format($s['workersLocalCurrent'] ?? 0, 0, ',', '.') ?> orang</span>
                    <span class="row-actions">
                        <button type="button" class="btn-edit-sector" data-id="<?= $s['id'] ?>" title="Edit Sektor">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button type="button" class="btn-delete-sector danger" data-id="<?= $s['id'] ?>" data-name="<?= htmlspecialchars($s['name'] ?? $s['nama_sektor'] ?? '') ?>" title="Hapus (Soft Delete)">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>

    <!-- MODAL TAMBAH SEKTOR -->
    <div class="modal-backdrop" id="add-sector-modal" style="display:none;">
        <div class="modal admin-data-modal" style="max-width:540px;">
            <div class="modal-head">
                <div>
                    <span class="badge info">TAMBAH SEKTOR</span>
                    <h2>Tambah Sektor Ekonomi Baru</h2>
                    <p>Simpan dataset sektor lapangan usaha baru ke tabel database <code>analisis_ekonomi</code>.</p>
                </div>
                <button class="icon-button" id="modal-btn-close-add-sector">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="form-add-sector" method="POST" action="<?= site_url('analisis_ekonomi/create') ?>">
                <div class="manual-form" style="grid-template-columns: 1fr 1fr;">
                    <label>
                        Kode Sektor
                        <input type="text" name="kode_sektor" id="add-sector-code" placeholder="Contoh: sektor_ict" required>
                    </label>
                    <label>
                        Nama Pendek
                        <input type="text" name="nama_pendek" id="add-sector-short" placeholder="Contoh: Informasi & Komunikasi" required>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Nama Lengkap Sektor
                        <input type="text" name="nama_sektor" id="add-sector-name" placeholder="Contoh: Informasi dan Komunikasi Terpadu" required>
                    </label>
                    <label>
                        Porsi Daerah (%)
                        <input type="number" step="0.01" name="porsi_daerah" id="add-sector-share-local" placeholder="Contoh: 4.80" required>
                    </label>
                    <label>
                        Porsi Provinsi (%)
                        <input type="number" step="0.01" name="porsi_provinsi" id="add-sector-share-prov" placeholder="Contoh: 5.20" required>
                    </label>
                    <label>
                        Pertumbuhan Daerah (%)
                        <input type="number" step="0.01" name="pertumbuhan_daerah" id="add-sector-growth-local" placeholder="Contoh: 6.40" required>
                    </label>
                    <label>
                        Pertumbuhan Provinsi (%)
                        <input type="number" step="0.01" name="pertumbuhan_provinsi" id="add-sector-growth-prov" placeholder="Contoh: 6.10" required>
                    </label>
                    <label>
                        Tenaga Kerja Daerah Kini (Orang)
                        <input type="number" name="tenaga_kerja_kini" id="add-sector-workers" placeholder="Contoh: 18500" required>
                    </label>
                    <label>
                        Tenaga Kerja Provinsi Kini (Orang)
                        <input type="number" name="tenaga_kerja_provinsi_kini" id="add-sector-workers-prov" placeholder="Contoh: 420000" required>
                    </label>
                </div>

                <div id="add-sector-error" class="method-note" style="display:none; color:var(--red); margin-bottom:12px;"></div>

                <div class="modal-actions">
                    <button type="button" class="button ghost" id="modal-btn-cancel-add-sector">Batal</button>
                    <button type="submit" class="button primary" id="modal-btn-submit-add-sector">Simpan Sektor</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT SEKTOR -->
    <div class="modal-backdrop" id="edit-sector-modal" style="display:none;">
        <div class="modal admin-data-modal" style="max-width:540px;">
            <div class="modal-head">
                <div>
                    <span class="badge warning">PERBARUI SEKTOR</span>
                    <h2>Edit Data Sektor Ekonomi</h2>
                    <p>Perbarui nilai porsi, pertumbuhan, dan jumlah tenaga kerja sektor.</p>
                </div>
                <button class="icon-button" id="modal-btn-close-edit-sector">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="form-edit-sector" method="POST">
                <input type="hidden" name="id" id="edit-sector-id">
                <div class="manual-form" style="grid-template-columns: 1fr 1fr;">
                    <label>
                        Kode Sektor
                        <input type="text" name="kode_sektor" id="edit-sector-code" required>
                    </label>
                    <label>
                        Nama Pendek
                        <input type="text" name="nama_pendek" id="edit-sector-short" required>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Nama Lengkap Sektor
                        <input type="text" name="nama_sektor" id="edit-sector-name" required>
                    </label>
                    <label>
                        Porsi Daerah (%)
                        <input type="number" step="0.01" name="porsi_daerah" id="edit-sector-share-local" required>
                    </label>
                    <label>
                        Porsi Provinsi (%)
                        <input type="number" step="0.01" name="porsi_provinsi" id="edit-sector-share-prov" required>
                    </label>
                    <label>
                        Pertumbuhan Daerah (%)
                        <input type="number" step="0.01" name="pertumbuhan_daerah" id="edit-sector-growth-local" required>
                    </label>
                    <label>
                        Pertumbuhan Provinsi (%)
                        <input type="number" step="0.01" name="pertumbuhan_provinsi" id="edit-sector-growth-prov" required>
                    </label>
                    <label>
                        Tenaga Kerja Daerah Kini (Orang)
                        <input type="number" name="tenaga_kerja_kini" id="edit-sector-workers" required>
                    </label>
                    <label>
                        Tenaga Kerja Provinsi Kini (Orang)
                        <input type="number" name="tenaga_kerja_provinsi_kini" id="edit-sector-workers-prov" required>
                    </label>
                </div>

                <div id="edit-sector-error" class="method-note" style="display:none; color:var(--red); margin-bottom:12px;"></div>

                <div class="modal-actions">
                    <button type="button" class="button ghost" id="modal-btn-cancel-edit-sector">Batal</button>
                    <button type="submit" class="button primary" id="modal-btn-submit-edit-sector">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

