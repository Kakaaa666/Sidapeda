<div class="page admin-page">
    <div class="admin-intro">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions">
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success" style="background:#eaf7ee; border:1px solid #95d5a6; color:#1e6a38; padding:12px 16px; border-radius:9px; margin-bottom:16px; display:flex; align-items:center; gap:10px; font-size:12px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
            <span><?= $this->session->flashdata('success') ?></span>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-error" style="background:#fdf2f2; border:1px solid #f8b4b4; color:#9b1c1c; padding:12px 16px; border-radius:9px; margin-bottom:16px; display:flex; align-items:center; gap:10px; font-size:12px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            <span><?= $this->session->flashdata('error') ?></span>
        </div>
    <?php endif; ?>

    <!-- STATUS SUMMARY METRICS -->
    <div class="metric-row" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 16px;">
        <div class="metric-box">
            <strong><?= $stats['total'] ?></strong>
            <span>Total Data Indikator</span>
            <small>Semua data aktif</small>
        </div>
        <div class="metric-box" style="border-left: 3px solid var(--yellow, #eab308);">
            <strong style="color: #b45309;"><?= $stats['review'] ?></strong>
            <span>Perlu Review</span>
            <small>Menunggu persetujuan</small>
        </div>
        <div class="metric-box" style="border-left: 3px solid var(--green, #16a34a);">
            <strong style="color: #15803d;"><?= $stats['tervalidasi'] ?></strong>
            <span>Tervalidasi</span>
            <small>Publik & terverifikasi</small>
        </div>
        <div class="metric-box" style="border-left: 3px solid var(--red, #ef4444);">
            <strong style="color: #dc2626;"><?= $stats['ditolak'] ?></strong>
            <span>Ditolak / Belum Lengkap</span>
            <small>Perlu revisi data</small>
        </div>
    </div>

    <!-- TOOLBAR FILTER & SEARCH -->
    <div class="management-toolbar">
        <div class="search wide">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
            <input type="text" id="validation-search-input" value="<?= htmlspecialchars($query ?? '') ?>" placeholder="Cari kode, indikator, klaster, wilayah...">
        </div>
        <select id="validation-filter-status">
            <option value="Semua status" <?= ($status === 'Semua status' || !$status) ? 'selected' : '' ?>>Semua Status (Aktif) (<?= $stats['total'] ?>)</option>
            <option value="Perlu review" <?= $status === 'Perlu review' ? 'selected' : '' ?>>Perlu review (<?= $stats['review'] ?>)</option>
            <option value="Tervalidasi" <?= $status === 'Tervalidasi' ? 'selected' : '' ?>>Tervalidasi (<?= $stats['tervalidasi'] ?>)</option>
            <option value="Belum lengkap" <?= ($status === 'Belum lengkap' || $status === 'Ditolak') ? 'selected' : '' ?>>Ditolak / Belum Lengkap (<?= $stats['ditolak'] ?>)</option>
        </select>
        <select id="validation-sort">
            <option value="status" <?= ($sort === 'status' || !$sort) ? 'selected' : '' ?>>Urut: Status (Perlu Review Dahulu)</option>
            <option value="year" <?= $sort === 'year' ? 'selected' : '' ?>>Urut tahun terbaru</option>
            <option value="indicator" <?= $sort === 'indicator' ? 'selected' : '' ?>>Nama indikator</option>
            <option value="value" <?= $sort === 'value' ? 'selected' : '' ?>>Nilai terbesar</option>
        </select>
    </div>

    <article class="card data-card">
        <div class="card-head">
            <div>
                <span class="eyebrow">VERIFIKASI & RIWAYAT VALIDASI</span>
                <h3>Daftar Validasi Data Indikator</h3>
                <p>Menampilkan <?= count($records) ?> data. Tinjau antrean verifikasi serta riwayat data yang telah divalidasi maupun ditolak.</p>
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
                <span>Aksi Validasi</span>
            </div>

            <div id="validation-table-body">
                <?php if (empty($records)): ?>
                    <div class="table-empty">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
                        <strong>Tidak ada data yang sesuai dengan filter saat ini.</strong>
                    </div>
                <?php else: ?>
                    <?php foreach ($records as $item): ?>
                        <?php
                            $st = $item['status'] ?? $item['status_validasi'] ?? 'Perlu review';
                            $isValid = ($st === 'Tervalidasi');
                            $isRejected = ($st === 'Belum lengkap' || $st === 'Ditolak');
                            $isReview = ($st === 'Perlu review');
                        ?>
                        <div class="data-tr" data-code="<?= $item['code'] ?>" data-year="<?= $item['year'] ?>">
                            <span><code><?= htmlspecialchars($item['code']) ?></code></span>
                            <span>
                                <strong><?= htmlspecialchars($item['indicator']) ?></strong>
                                <small><?= htmlspecialchars($item['region']) ?> • <?= htmlspecialchars($item['source']) ?></small>
                                <?php if (!empty($item['notes']) && !$isValid): ?>
                                    <div class="table-rejection-note" style="margin-top: 5px; font-size: 11px; line-height: 1.4; color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 4px 8px; display: inline-flex; align-items: flex-start; gap: 5px; max-width: 100%;">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                        <span><strong>Alasan Penolakan:</strong> <?= htmlspecialchars($item['notes']) ?></span>
                                    </div>
                                <?php endif; ?>
                            </span>
                            <span><?= htmlspecialchars($item['cluster']) ?></span>
                            <span><?= htmlspecialchars($item['period']) ?> <?= $item['year'] ?></span>
                            <span><strong><?= number_format($item['value'], 2, ',', '.') ?></strong> <?= htmlspecialchars($item['unit']) ?></span>
                            <span>
                                <span class="badge <?= $isValid ? 'success' : ($isReview ? 'warning' : 'danger') ?>">
                                    <?= $st ?>
                                </span>
                                <?php if (!empty($item['verified_by']) && !$isReview): ?>
                                    <small style="display:block; font-size:10px; color:#64748b; margin-top:3px;">Oleh: <?= htmlspecialchars($item['verified_by']) ?></small>
                                <?php endif; ?>
                            </span>
                            <span class="row-actions">
                                <button type="button" class="btn-verify-record success <?= $isValid ? 'is-active' : '' ?>" title="<?= $isValid ? 'Data sudah tervalidasi (klik untuk konfirmasi ulang)' : 'Setujui Data (Validasi)' ?>" data-code="<?= $item['code'] ?>" data-year="<?= $item['year'] ?>" data-indicator="<?= htmlspecialchars($item['indicator']) ?>" data-status="Tervalidasi">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
                                </button>
                                <button type="button" class="btn-reject-record danger <?= $isRejected ? 'is-active' : '' ?>" title="<?= $isRejected ? 'Data sudah ditolak (klik untuk perbarui alasan penolakan)' : 'Tolak Data (Harus Disertai Alasan)' ?>" data-code="<?= $item['code'] ?>" data-year="<?= $item['year'] ?>" data-indicator="<?= htmlspecialchars($item['indicator']) ?>" data-val="<?= number_format($item['value'], 2, ',', '.') ?> <?= htmlspecialchars($item['unit']) ?>" data-period="<?= htmlspecialchars($item['period']) ?> <?= $item['year'] ?>" data-notes="<?= htmlspecialchars($item['notes'] ?? '') ?>">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/></svg>
                                </button>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="pagination">
            <span>Menampilkan <?= count($records) ?> data indikator (<?= $stats['review'] ?> perlu review, <?= $stats['tervalidasi'] ?> tervalidasi, <?= $stats['ditolak'] ?> ditolak)</span>
            <span>Verifikasi langsung mengubah status data di database</span>
        </div>
    </article>

    <!-- MODAL ALASAN PENOLAKAN / REVISI VALIDASI -->
    <div class="modal-backdrop" id="modal-reject-record" style="display:none;">
        <div class="modal" style="max-width: 520px;">
            <div class="modal-head">
                <div>
                    <span class="badge danger" style="background:#fee2e2; color:#dc2626; border-color:#fca5a5;">TOLAK / PERLU REVISI</span>
                    <h2>Tolak Validasi Data</h2>
                    <p>Sertakan alasan penolakan atau perbaikan data agar tampil di menu Database Ekonomi.</p>
                </div>
                <button type="button" class="icon-button" id="btn-close-reject-modal">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="form-reject-record" style="margin-top: 15px;">
                <input type="hidden" id="reject-code" name="code">
                <input type="hidden" id="reject-year" name="year">

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; margin-bottom: 14px; font-size: 12px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span style="color: var(--muted);">Kode Indikator:</span>
                        <strong id="reject-display-code" style="color: var(--navy); font-family: monospace;">-</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span style="color: var(--muted);">Nama Indikator:</span>
                        <strong id="reject-display-indicator" style="color: var(--navy); text-align: right; max-width: 300px;">-</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span style="color: var(--muted);">Periode / Tahun:</span>
                        <span id="reject-display-period" style="font-weight: 600;">-</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--muted);">Nilai Tercatat:</span>
                        <strong id="reject-display-value" style="color: var(--teal);">-</strong>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label for="reject-reason" style="font-size: 12px; font-weight: 600; color: var(--navy);">
                        Alasan Penolakan / Catatan Perbaikan <span style="color: #dc2626;">*</span>
                    </label>
                    <textarea id="reject-reason" name="notes" rows="4" required placeholder="Tuliskan alasan penolakan, data yang tidak sesuai, atau detail perbaikan yang diperlukan..." style="width: 100%; border: 1px solid var(--line); border-radius: 7px; padding: 10px; font-size: 12.5px; font-family: inherit; line-height: 1.5; resize: vertical; box-sizing: border-box;"></textarea>
                    <small id="reject-reason-error" style="color: #dc2626; font-size: 11px; display: none;">Alasan penolakan wajib disertakan sebelum mengirim.</small>
                </div>

                <div class="modal-actions" style="margin-top: 18px; display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" class="button ghost" id="btn-cancel-reject">Batal</button>
                    <button type="submit" class="button" id="btn-submit-reject" style="background: #dc2626; color: white; border-color: #dc2626; display: inline-flex; align-items: center; gap: 6px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/></svg>
                        <span>Simpan Penolakan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
