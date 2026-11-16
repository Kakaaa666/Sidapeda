<div class="page admin-page">
    <div class="admin-intro">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions">
            <a href="<?= site_url('early_warning') ?>" class="button ghost" target="_blank">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px;"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                Lihat Monitoring EWS Publik
            </a>
        </div>
    </div>

    <!-- FILTER TAHUN EWS ADMIN (DROPDOWN) -->
    <div class="year-filter-bar">
        <div class="year-filter-info">
            <div class="year-filter-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div class="year-filter-text">
                <strong>Periode Tahun Indikator EWS</strong>
                <small>Nilai riil diambil otomatis dari data indikator yang telah tervalidasi pada tahun terpilih.</small>
            </div>
        </div>

        <div class="year-filter-control">
            <label for="ews-admin-year-select">Pilih Tahun:</label>
            <select id="ews-admin-year-select" class="year-filter-select" onchange="window.location.href=this.value">
                <option value="<?= site_url('ews_admin?year=all') ?>" <?= empty($selected_year) ? 'selected' : '' ?>>Semua Tahun (Terkini)</option>
                <?php if (!empty($available_years)): ?>
                    <?php foreach ($available_years as $ay): ?>
                        <option value="<?= site_url('ews_admin?year=' . $ay['tahun']) ?>" <?= (!empty($selected_year) && $selected_year == $ay['tahun']) ? 'selected' : '' ?>>
                            Tahun <?= $ay['tahun'] ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <!-- KONFIGURASI AMBANG BATAS & DATA TERVALIDASI -->
    <article class="card threshold-admin">
        <div class="card-head">
            <div>
                <span class="eyebrow">EARLY WARNING CONFIGURATION • <?= !empty($selected_year) ? "TAHUN {$selected_year}" : "SEMUA PERIODE" ?></span>
                <h3>Konfigurasi Ambang Batas Early Warning System</h3>
                <p>Data indikator &amp; nilai riil bersumber langsung dari data tervalidasi. Anda hanya perlu menetapkan <strong>Ambang Batas</strong> dan <strong>Arah Kondisi Kritis</strong>.</p>
            </div>
            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <span class="badge info" style="font-size:11px; padding:6px 14px; font-weight:700; border-radius:20px;">
                    Total: <?= count($ews) ?> Indikator Tervalidasi
                </span>
                <button class="button primary" id="btn-save-all-thresholds">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Simpan Semua Ambang Batas
                </button>
            </div>
        </div>

        <div style="padding: 12px 18px; background: #f0f7ff; border-left: 4px solid var(--blue); border-radius: 6px; margin: 12px 0 20px 0; font-size: 11px; line-height: 1.6; color: var(--navy);">
            <strong>Panduan Konfigurasi Ambang Batas EWS:</strong>
            <ul style="margin: 4px 0 0 16px; padding: 0;">
                <li><strong>Nilai Riil Tervalidasi</strong> bersifat <em>read-only</em> dan langsung merujuk pada capaian riil yang telah diverifikasi pada menu Validasi Data.</li>
                <li><strong>Ambang Batas</strong> adalah batas toleransi risiko yang dapat disesuaikan dengan target RPJMD atau standar makro daerah.</li>
                <li><strong>Arah Kondisi Kritis</strong>:
                    <ul style="margin: 2px 0 0 14px;">
                        <li><em>Kritis jika &gt; Ambang (Beban / Risiko)</em>: Untuk indikator beban seperti Inflasi, Kemiskinan, dan Pengangguran Terbuka.</li>
                        <li><em>Kritis jika &lt; Ambang (Target / Kinerja)</em>: Untuk indikator target capaian seperti Pertumbuhan PDRB, PAD, Investasi Daerah, dan IPM.</li>
                    </ul>
                </li>
            </ul>
        </div>

        <div class="result-table-wrap">
            <table class="result-table" style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                <thead>
                    <tr style="display: table-row;">
                        <th style="width: 32%; text-align: left; vertical-align: middle; padding: 12px 14px;">Indikator</th>
                        <th style="width: 16%; text-align: left; vertical-align: middle; padding: 12px 14px;">Nilai Riil Tervalidasi</th>
                        <th style="width: 18%; text-align: left; vertical-align: middle; padding: 12px 14px;">Ambang Batas (Editable)</th>
                        <th style="width: 22%; text-align: left; vertical-align: middle; padding: 12px 14px;">Arah Kondisi Kritis</th>
                        <th style="width: 12%; text-align: center; vertical-align: middle; padding: 12px 14px;">Status Risiko</th>
                    </tr>
                </thead>
                <tbody id="threshold-admin-rows">
                    <?php if (empty($ews)): ?>
                        <tr style="display: table-row;">
                            <td colspan="5" style="text-align: center; padding: 36px 16px; color: var(--muted); vertical-align: middle;">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display:block; margin:0 auto 8px; opacity:0.5;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                Tidak ada indikator yang berstatus <strong>Tervalidasi</strong> <?= !empty($selected_year) ? "untuk tahun {$selected_year}" : "" ?>.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($ews as $item): ?>
                            <?php
                            $badgeCls = ($item['status'] === 'Kritis') ? 'badge danger' : (($item['status'] === 'Waspada') ? 'badge warning' : 'badge success');
                            ?>
                            <tr class="threshold-admin-row" data-code="<?= $item['code'] ?>" style="display: table-row;">
                                <td style="vertical-align: middle; padding: 12px 14px;">
                                    <strong style="color: var(--navy); font-size: 13px;"><?= htmlspecialchars($item['name']) ?></strong>
                                </td>
                                <td style="vertical-align: middle; padding: 12px 14px;">
                                    <div style="display: inline-flex; align-items: center; gap: 6px;">
                                        <strong style="font-size: 13px; color: var(--navy); letter-spacing: -0.2px;">
                                            <?= number_format($item['value'], 2, ',', '.') ?>
                                        </strong>
                                        <span style="color: var(--muted); font-size: 11px; font-weight: 600;">
                                            <?= htmlspecialchars($item['unit']) ?>
                                        </span>
                                    </div>
                                    <input type="hidden" class="value-input" data-code="<?= $item['code'] ?>" value="<?= $item['value'] ?>">
                                </td>
                                <td style="vertical-align: middle; padding: 12px 14px;">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <input type="number" step="any" class="threshold-input" data-code="<?= $item['code'] ?>" value="<?= $item['threshold'] ?>" style="width: 100px; height: 36px; border: 1px solid var(--line); border-radius: 6px; padding: 0 10px; font-weight: 700; color: var(--navy); font-size: 12px; background: #ffffff;">
                                        <small style="color: var(--muted); font-size: 10px; font-weight: 600;"><?= htmlspecialchars($item['unit']) ?></small>
                                    </div>
                                </td>
                                <td style="vertical-align: middle; padding: 12px 14px;">
                                    <select class="direction-input" data-code="<?= $item['code'] ?>" style="width: 100%; max-width: 250px; height: 36px; border: 1px solid var(--line); border-radius: 6px; padding: 0 8px; font-size: 11px; color: var(--navy); background: #ffffff;">
                                        <option value="above" <?= ($item['direction'] === 'above') ? 'selected' : '' ?>>Kritis jika &gt; Ambang (Beban / Risiko)</option>
                                        <option value="below" <?= ($item['direction'] === 'below') ? 'selected' : '' ?>>Kritis jika &lt; Ambang (Target / Kinerja)</option>
                                    </select>
                                </td>
                                <td style="text-align: center; vertical-align: middle; padding: 12px 14px;">
                                    <span id="status-badge-<?= $item['code'] ?>" class="<?= $badgeCls ?>" style="font-size: 10px; font-weight: 700; padding: 5px 12px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block; min-width: 75px;">
                                        <?= $item['status'] ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div id="ews-admin-feedback" class="method-note" style="display:none; margin-top: 15px;"></div>
    </article>
</div>
