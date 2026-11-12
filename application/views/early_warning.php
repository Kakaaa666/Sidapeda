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
        <!-- TEMPAT SAMPAH EWS -->
        <article class="card large" style="margin-bottom:24px; border:1px dashed #f43f5e; background:rgba(244,63,94,0.03);">
            <div class="card-head">
                <div>
                    <span class="badge danger">TEMPAT SAMPAH (SOFT DELETE)</span>
                    <h3>Daftar Indikator EWS Terhapus (<?= count($ews) ?>)</h3>
                    <p>Klik tombol Pulihkan untuk mengembalikan indikator ke sistem pemantauan dini aktif.</p>
                </div>
                <a href="<?= site_url('early_warning') ?>" class="button ghost">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    <span>Kembali ke EWS Aktif</span>
                </a>
            </div>
            <div class="data-table">
                <div class="data-tr data-th" style="grid-template-columns: 1fr 2fr 1fr 1fr 1.2fr 120px;">
                    <span>Kode</span>
                    <span>Nama Indikator</span>
                    <span>Nilai</span>
                    <span>Threshold</span>
                    <span>Dihapus Pada</span>
                    <span>Aksi</span>
                </div>
                <?php if (empty($ews)): ?>
                    <div style="padding:24px; text-align:center; color:var(--muted); font-size:11px;">
                        Tempat sampah kosong. Tidak ada indikator EWS yang terhapus.
                    </div>
                <?php else: ?>
                    <?php foreach ($ews as $item): ?>
                        <div class="data-tr" style="grid-template-columns: 1fr 2fr 1fr 1fr 1.2fr 120px;">
                            <span><code><?= htmlspecialchars($item['code']) ?></code></span>
                            <span><strong><?= htmlspecialchars($item['name']) ?></strong></span>
                            <span><?= number_format($item['value'] ?? $item['nilai_terkini'] ?? $item['currentValue'] ?? 0, 2, ',', '.') ?> <?= htmlspecialchars($item['unit'] ?? $item['satuan'] ?? '%') ?></span>
                            <span><?= number_format($item['threshold'], 2, ',', '.') ?> <?= htmlspecialchars($item['unit']) ?></span>
                            <span><small><?= htmlspecialchars($item['deleted_at'] ?? '-') ?></small></span>
                            <span class="row-actions">
                                <button type="button" class="btn-restore-ews" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>" title="Pulihkan Indikator">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                    <span>Pulihkan</span>
                                </button>
                                <button type="button" class="btn-hard-delete-ews danger" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>" title="Hapus Permanen">
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
                    <a href="<?= site_url('early_warning') ?>" class="button ghost">
                        <span>Kembali ke Aktif</span>
                    </a>
                <?php else: ?>
                    <a href="<?= site_url('early_warning?status=Terhapus') ?>" class="button ghost danger">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        <span>Sampah (<?= $trash_count ?>)</span>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            <button class="button primary" id="btn-open-add-ews">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Tambah Indikator EWS</span>
            </button>
            <button class="button secondary" id="btn-export-ews">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12M7 10l5 5 5-5M4 21h16"/></svg>
                <span>Ekspor CSV</span>
            </button>
        </div>
    </div>

    <!-- STATS OVERVIEW -->
    <div class="risk-overview" id="ews-stats-container">
        <?php
        $kritisCnt = 0; $waspadaCnt = 0; $normalCnt = 0;
        foreach ($ews as $item) {
            $st = $item['status'] ?? $item['status_risiko'] ?? 'Normal';
            if ($st === 'Kritis') $kritisCnt++;
            elseif ($st === 'Waspada') $waspadaCnt++;
            else $normalCnt++;
        }
        $totEws = count($ews);
        ?>
        <div class="risk-stat danger">
            <span>Status Kritis</span>
            <strong><?= $kritisCnt ?></strong>
            <small>Memerlukan intervensi segera</small>
        </div>
        <div class="risk-stat warning">
            <span>Status Waspada</span>
            <strong><?= $waspadaCnt ?></strong>
            <small>Mendekati ambang batas</small>
        </div>
        <div class="risk-stat success">
            <span>Status Normal</span>
            <strong><?= $normalCnt ?></strong>
            <small>Dalam batas aman</small>
        </div>
        <div class="risk-stat neutral">
            <span>Total Indikator</span>
            <strong><?= $totEws ?></strong>
            <small>Dipantau sistem</small>
        </div>
    </div>

    <!-- CONTENT GRID -->
    <section class="two-col risk-content">
        <!-- MONITORING TABLE -->
        <article class="card large">
            <div class="card-head">
                <div>
                    <span class="eyebrow">RISK MONITORING</span>
                    <h3>Indikator dalam Pemantauan Dini</h3>
                    <p>Status dihitung otomatis dari nilai riil dan threshold konfigurasi. Klik baris indikator untuk detail analisis dan penyesuaian threshold.</p>
                </div>
            </div>

            <div class="segmented ews-filters" id="ews-filter-tabs">
                <button class="active" data-status="Semua">Semua</button>
                <button data-status="Kritis">Kritis</button>
                <button data-status="Waspada">Waspada</button>
                <button data-status="Normal">Normal</button>
            </div>

            <div class="risk-table" id="ews-table-rows">
                <div class="risk-table-header">
                    <span>Indikator Pemantauan</span>
                    <span>Nilai Riil</span>
                    <span>Batas Ambang</span>
                    <span>Status Risiko</span>
                    <span style="text-align:right;">Aksi</span>
                </div>
                <?php foreach ($ews as $item):
                    $st = $item['status'] ?? $item['status_risiko'] ?? 'Normal';
                    $badgeCls = ($st === 'Kritis') ? 'danger' : (($st === 'Waspada') ? 'warning' : 'success');
                    $rowStateCls = ($st === 'Kritis') ? 'alerted' : (($st === 'Waspada') ? 'warning-row' : 'normal-row');
                    $code = $item['code'] ?? $item['kode_indikator'] ?? '';
                    $name = $item['name'] ?? $item['nama_indikator'] ?? '';
                    $val = (float)($item['value'] ?? $item['nilai_terkini'] ?? $item['currentValue'] ?? 0);
                    $th = (float)($item['threshold'] ?? $item['ambang_batas'] ?? 0);
                    $unit = $item['unit'] ?? $item['satuan'] ?? '%';
                    $dir = $item['direction'] ?? $item['arah_risiko'] ?? 'above';
                    $dirText = ($dir === 'above') ? 'Peringatan jika > Ambang' : 'Peringatan jika < Ambang';
                ?>
                    <div class="risk-row btn-alert-row <?= $rowStateCls ?>" data-code="<?= htmlspecialchars($code) ?>" role="button" tabindex="0">
                        <div class="risk-indicator">
                            <strong><?= htmlspecialchars($name) ?></strong>
                            <small>
                                <code><?= htmlspecialchars($code) ?></code>
                                <span>• <?= $dirText ?></span>
                            </small>
                        </div>
                        <div class="risk-value">
                            <span>Nilai Riil</span>
                            <strong><?= number_format($val, 2, ',', '.') ?> <span style="font-size:11px; font-weight:500; color:var(--muted);"><?= htmlspecialchars($unit) ?></span></strong>
                        </div>
                        <div class="risk-threshold">
                            <span>Batas Ambang</span>
                            <strong><?= number_format($th, 2, ',', '.') ?> <span style="font-size:11px; font-weight:500; color:var(--muted);"><?= htmlspecialchars($unit) ?></span></strong>
                        </div>
                        <div class="risk-status">
                            <span class="badge <?= $badgeCls ?>"><?= htmlspecialchars($st) ?></span>
                        </div>
                        <div class="risk-action">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="source" style="margin-top:14px; font-size:11px; color:var(--muted); padding-top:10px; border-top:1px dashed var(--line);">
                <strong>Kriteria Sistem:</strong> Deviasi &lt; 0% = <span class="badge success" style="font-size:9px; padding:2px 6px;">Normal</span>; 0–9.9% = <span class="badge warning" style="font-size:9px; padding:2px 6px;">Waspada</span>; &ge; 10% = <span class="badge danger" style="font-size:9px; padding:2px 6px;">Kritis</span>.
            </div>
        </article>

        <!-- RISK MATRIX & PROGRESS -->
        <article class="card">
            <div class="card-head">
                <div>
                    <span class="eyebrow">RISK MATRIX & KOMPARASI</span>
                    <h3>Grafik Komparasi Deviasi Ambang</h3>
                    <p>Perbandingan nilai aktual terhadap batas ambang risiko (threshold).</p>
                </div>
            </div>

            <div id="ews-comparison-chart-container" class="chart-wrap dynamic-chart" style="height:auto; min-height:160px; margin-bottom:14px;">
                <!-- Rendered dynamically by JS -->
            </div>

            <div class="risk-distribution" id="ews-distribution-bars">
                <!-- Injected by JS -->
            </div>

            <div class="followup">
                <strong>Progress Tindak Lanjut Pemantauan</strong>
                <div>
                    <i id="ews-progress-bar" style="width: 20%"></i>
                    <span id="ews-progress-text">1 dari 5 indikator ditandai</span>
                    <b id="ews-progress-pct">20%</b>
                </div>
            </div>
        </article>
    </section>

    <!-- MODAL ALERT DETAIL -->
    <div class="modal-backdrop" id="ews-modal-backdrop" style="display:none;">
        <div class="modal" id="ews-modal-card">
            <div class="modal-head">
                <div>
                    <span class="badge danger" id="modal-alert-badge">KRITIS</span>
                    <h2 id="modal-alert-name">Inflasi Pangan</h2>
                    <p id="modal-alert-meta">Panel Harga Pangan • HRG-01</p>
                </div>
                <button class="icon-button" id="modal-btn-close">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <div class="alert-metrics">
                <span>
                    <small>Nilai Terkini</small>
                    <strong id="modal-metric-val">8,40 % yoy</strong>
                </span>
                <span>
                    <small>Threshold Ambang</small>
                    <strong id="modal-metric-th">7,00 % yoy</strong>
                </span>
                <span>
                    <small>Deviasi dari Ambang</small>
                    <strong id="modal-metric-dev" class="negative">+20,0%</strong>
                </span>
            </div>

            <div class="threshold-editor">
                <label>
                    Ubah Nilai Threshold: <strong id="modal-editor-label">7,00 % yoy</strong>
                    <input type="number" step="any" id="modal-input-threshold" value="7.0">
                </label>
                <small>Perubahan disimpan langsung ke MySQL dan memperbarui status secara realtime.</small>
            </div>

            <div class="modal-section">
                <h3>Interpretasi Otomatis Intelijen</h3>
                <p id="modal-alert-interpretation">Nilai telah melampaui batas ambang risiko sebesar 20,0%. Direkomendasikan koordinasi lintas OPD untuk intervensi harga pasar.</p>
            </div>

            <div class="modal-actions">
                <button class="button ghost" id="modal-btn-cancel">Tutup</button>
                <button class="button primary" id="modal-btn-save-threshold">Simpan Threshold</button>
                <button class="button secondary" id="modal-btn-toggle-followup">Tandai Ditindaklanjuti</button>
            </div>
        </div>
    </div>

    <!-- TABEL DATASET INDIKATOR EARLY WARNING (CRUD & SOFT DELETE) -->
    <article class="card large" style="margin-top:24px;">
        <div class="card-head">
            <div>
                <span class="eyebrow">PENGATURAN INDIKATOR EWS</span>
                <h3>Daftar Indikator Pemantauan Risiko (<?= count($ews) ?>)</h3>
                <p>Kelola data riil, ambang batas threshold, serta arah risiko indikator EWS.</p>
            </div>
            <button class="button primary" id="btn-open-add-ews-2">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Tambah Indikator</span>
            </button>
        </div>

        <div class="data-table">
            <div class="data-tr data-th" style="grid-template-columns: 120px 2fr 1fr 1fr 1fr 1fr 110px;">
                <span>Kode</span>
                <span>Nama Indikator</span>
                <span>Nilai Terkini</span>
                <span>Threshold</span>
                <span>Arah Risiko</span>
                <span>Status</span>
                <span>Aksi</span>
            </div>
            <?php foreach ($ews as $item): ?>
                <div class="data-tr" style="grid-template-columns: 120px 2fr 1fr 1fr 1fr 1fr 110px;">
                    <span><code><?= htmlspecialchars($item['code']) ?></code></span>
                    <span><strong><?= htmlspecialchars($item['name']) ?></strong></span>
                    <span><?= number_format($item['value'] ?? $item['nilai_terkini'] ?? $item['currentValue'] ?? 0, 2, ',', '.') ?> <?= htmlspecialchars($item['unit'] ?? $item['satuan'] ?? '%') ?></span>
                    <span><?= number_format($item['threshold'] ?? $item['ambang_batas'] ?? 0, 2, ',', '.') ?> <?= htmlspecialchars($item['unit'] ?? $item['satuan'] ?? '%') ?></span>
                    <span><small><?= ($item['direction'] ?? $item['arah_risiko'] ?? 'above') === 'above' ? 'Di atas batas' : 'Di bawah batas' ?></small></span>
                    <span><span class="badge <?= ($item['status'] ?? $item['status_risiko'] ?? 'Normal') === 'Kritis' ? 'danger' : ((($item['status'] ?? $item['status_risiko'] ?? 'Normal') === 'Waspada') ? 'warning' : 'success') ?>"><?= htmlspecialchars($item['status'] ?? $item['status_risiko'] ?? 'Normal') ?></span></span>
                    <span class="row-actions">
                        <button type="button" class="btn-edit-ews" data-id="<?= $item['id'] ?>" title="Edit Indikator">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            <span>Edit</span>
                        </button>
                        <button type="button" class="btn-delete-ews danger" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>" title="Hapus (Soft Delete)">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>

    <!-- MODAL TAMBAH INDIKATOR EWS -->
    <div class="modal-backdrop" id="add-ews-modal" style="display:none;">
        <div class="modal admin-data-modal" style="max-width:520px;">
            <div class="modal-head">
                <div>
                    <span class="badge info">TAMBAH EWS</span>
                    <h2>Tambah Indikator Peringatan Dini</h2>
                    <p>Simpan indikator pemantauan risiko baru ke tabel database <code>early_warning</code>.</p>
                </div>
                <button class="icon-button" id="modal-btn-close-add-ews">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="form-add-ews" method="POST" action="<?= site_url('early_warning/create') ?>">
                <div class="manual-form" style="grid-template-columns: 1fr 1fr;">
                    <label>
                        Kode Indikator
                        <input type="text" name="kode_indikator" id="add-ews-code" placeholder="Contoh: ews_kemiskinan" required>
                    </label>
                    <label>
                        Satuan Ukuran
                        <input type="text" name="satuan" id="add-ews-unit" placeholder="Contoh: % / Poin / Indeks" required>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Nama Indikator EWS
                        <input type="text" name="nama_indikator" id="add-ews-name" placeholder="Contoh: Rasio Kenaikan Harga Sembako" required>
                    </label>
                    <label>
                        Nilai Riil Terkini
                        <input type="number" step="0.01" name="nilai_terkini" id="add-ews-current" placeholder="Contoh: 4.50" required>
                    </label>
                    <label>
                        Batas Ambang (Threshold)
                        <input type="number" step="0.01" name="ambang_batas" id="add-ews-threshold" placeholder="Contoh: 5.00" required>
                    </label>
                    <label>
                        Kondisi Berisiko
                        <select name="arah_risiko" id="add-ews-direction">
                            <option value="above">Jika Melampaui Nilai Ambang (&gt; Threshold)</option>
                            <option value="below">Jika Berada Di Bawah Ambang (&lt; Threshold)</option>
                        </select>
                    </label>
                    <label>
                        Status Risiko
                        <select name="status_risiko" id="add-ews-status">
                            <option value="Normal">Normal</option>
                            <option value="Waspada">Waspada</option>
                            <option value="Kritis">Kritis</option>
                        </select>
                    </label>
                </div>

                <div id="add-ews-error" class="method-note" style="display:none; color:var(--red); margin-bottom:12px;"></div>

                <div class="modal-actions">
                    <button type="button" class="button ghost" id="modal-btn-cancel-add-ews">Batal</button>
                    <button type="submit" class="button primary" id="modal-btn-submit-add-ews">Simpan Indikator</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT INDIKATOR EWS -->
    <div class="modal-backdrop" id="edit-ews-modal" style="display:none;">
        <div class="modal admin-data-modal" style="max-width:520px;">
            <div class="modal-head">
                <div>
                    <span class="badge warning">PERBARUI EWS</span>
                    <h2>Edit Indikator Peringatan Dini</h2>
                    <p>Perbarui nilai aktual, batas ambang, dan arah risiko indikator EWS.</p>
                </div>
                <button class="icon-button" id="modal-btn-close-edit-ews">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="form-edit-ews" method="POST">
                <input type="hidden" name="id" id="edit-ews-id">
                <div class="manual-form" style="grid-template-columns: 1fr 1fr;">
                    <label>
                        Kode Indikator
                        <input type="text" name="kode_indikator" id="edit-ews-code" required>
                    </label>
                    <label>
                        Satuan Ukuran
                        <input type="text" name="satuan" id="edit-ews-unit" required>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Nama Indikator EWS
                        <input type="text" name="nama_indikator" id="edit-ews-name" required>
                    </label>
                    <label>
                        Nilai Riil Terkini
                        <input type="number" step="0.01" name="nilai_terkini" id="edit-ews-current" required>
                    </label>
                    <label>
                        Batas Ambang (Threshold)
                        <input type="number" step="0.01" name="ambang_batas" id="edit-ews-threshold" required>
                    </label>
                    <label>
                        Kondisi Berisiko
                        <select name="arah_risiko" id="edit-ews-direction">
                            <option value="above">Jika Melampaui Nilai Ambang (&gt; Threshold)</option>
                            <option value="below">Jika Berada Di Bawah Ambang (&lt; Threshold)</option>
                        </select>
                    </label>
                    <label>
                        Status Risiko
                        <select name="status_risiko" id="edit-ews-status">
                            <option value="Normal">Normal</option>
                            <option value="Waspada">Waspada</option>
                            <option value="Kritis">Kritis</option>
                        </select>
                    </label>
                </div>

                <div id="edit-ews-error" class="method-note" style="display:none; color:var(--red); margin-bottom:12px;"></div>

                <div class="modal-actions">
                    <button type="button" class="button ghost" id="modal-btn-cancel-edit-ews">Batal</button>
                    <button type="submit" class="button primary" id="modal-btn-submit-edit-ews">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    window.__EWS_DATA__ = <?= json_encode($ews, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>

