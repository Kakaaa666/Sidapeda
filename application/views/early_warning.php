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

    <div class="module-intro">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions">
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
                    <p>Status dihitung otomatis dari nilai riil dan batas ambang (threshold). Anda dapat menentukan sendiri nilai threshold dan arah kondisi risiko dengan mengklik baris indikator di bawah.</p>
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
                    <span style="text-align:right;">Status Risiko</span>
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
                    $dir = strtolower($item['direction'] ?? $item['arah_risiko'] ?? 'above');
                ?>
                    <div class="risk-row btn-alert-row <?= $rowStateCls ?>" data-code="<?= htmlspecialchars($code) ?>" role="button" tabindex="0" title="Klik untuk analisis & penyesuaian threshold">
                        <div class="risk-indicator">
                            <strong><?= htmlspecialchars($name) ?></strong>
                            <small>
                                <code><?= htmlspecialchars($code) ?></code>
                                <span style="color:var(--muted);">• <?= ($dir === 'above') ? 'Kritis jika > Ambang' : 'Kritis jika < Ambang' ?></span>
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
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="source" style="margin-top:14px; font-size:11px; color:var(--muted); padding-top:10px; border-top:1px dashed var(--line); line-height:1.6;">
                <strong>Kriteria Penentuan Status Risiko:</strong><br>
                • <strong>Indikator Beban/Risiko</strong> (Inflasi, Kemiskinan Ekstrem, Pengangguran): Nilai &lt; Ambang = <span class="badge success" style="font-size:9px; padding:2px 6px;">Normal</span>, Nilai &gt; Ambang = <span class="badge danger" style="font-size:9px; padding:2px 6px;">Kritis</span>.<br>
                • <strong>Indikator Kinerja/Target</strong> (Belanja Modal, Pertumbuhan Kredit): Nilai &gt; Ambang = <span class="badge success" style="font-size:9px; padding:2px 6px;">Normal</span>, Nilai &lt; Ambang = <span class="badge danger" style="font-size:9px; padding:2px 6px;">Kritis</span>.<br>
                • Nilai sama persis dengan ambang batas = <span class="badge warning" style="font-size:9px; padding:2px 6px;">Waspada</span>.
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

    <!-- MODAL ALERT DETAIL & THRESHOLD CUSTOMIZATION -->
    <div class="modal-backdrop" id="ews-modal-backdrop" style="display:none;">
        <div class="modal" id="ews-modal-card" style="max-width: 580px;">
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
                    <small>Nilai Riil Terkini</small>
                    <strong id="modal-metric-val">8,40 %</strong>
                </span>
                <span>
                    <small>Threshold Saat Ini</small>
                    <strong id="modal-metric-th">7,00 %</strong>
                </span>
                <span>
                    <small>Deviasi dari Ambang</small>
                    <strong id="modal-metric-dev" class="negative">+20,0%</strong>
                </span>
            </div>

            <!-- VALUE & THRESHOLD CUSTOMIZER -->
            <div class="modal-section threshold-editor" style="background:#f8fafc; border:1px solid var(--line); border-radius:10px; padding:16px; margin:16px 0;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                    <h3 style="margin:0; font-size:13px; font-weight:700; color:var(--navy); display:flex; align-items:center; gap:6px;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        Penyesuaian Nilai Riil & Ambang Batas
                    </h3>
                    <span id="modal-live-preview-badge" class="badge danger" style="font-size:10px;">Status: KRITIS</span>
                </div>
                
                <div style="display:grid; grid-template-columns: 1fr 1fr 1.2fr; gap:10px; margin-bottom:8px;">
                    <div>
                        <label style="display:block; font-size:11px; font-weight:600; color:var(--navy); margin-bottom:4px;">
                            Nilai Riil (<span class="modal-unit-label">%</span>)
                        </label>
                        <input type="number" step="any" id="modal-input-value" value="0.0" style="width:100%; height:36px; border:1px solid var(--line); border-radius:6px; padding:0 10px; font-size:13px; font-weight:600; color:var(--navy); background:#fff;">
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; font-weight:600; color:var(--navy); margin-bottom:4px;">
                            Batas Ambang (<span class="modal-unit-label">%</span>)
                        </label>
                        <input type="number" step="any" id="modal-input-threshold" value="7.0" style="width:100%; height:36px; border:1px solid var(--line); border-radius:6px; padding:0 10px; font-size:13px; font-weight:600; color:var(--navy); background:#fff;">
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; font-weight:600; color:var(--navy); margin-bottom:4px;">
                            Kondisi / Arah Batas Kritis
                        </label>
                        <select id="modal-input-direction" style="width:100%; height:36px; border:1px solid var(--line); border-radius:6px; padding:0 8px; font-size:11px; font-weight:500; color:var(--navy); background:#fff;">
                            <option value="above">Kritis jika Nilai &gt; Ambang (Beban)</option>
                            <option value="below">Kritis jika Nilai &lt; Ambang (Target)</option>
                        </select>
                    </div>
                </div>

                <small style="display:block; color:var(--muted); font-size:10px; line-height:1.4;">
                    Sesuaikan nilai riil atau ambang batas indikator di atas. Status risiko akan disimulasikan secara realtime dan tersimpan ke sistem.
                </small>
            </div>

            <div class="modal-section">
                <h3 style="font-size:12px; font-weight:700; color:var(--navy); margin-bottom:4px;">Interpretasi Otomatis Intelijen</h3>
                <p id="modal-alert-interpretation" style="font-size:12px; line-height:1.5; color:var(--slate);">Nilai telah melampaui batas ambang risiko sebesar 20,0%.</p>
            </div>

            <div class="modal-actions" style="display:flex; justify-content:space-between; align-items:center; margin-top:20px;">
                <button class="button secondary" id="modal-btn-toggle-followup" style="font-size:11px;">Tandai Ditindaklanjuti</button>
                <div style="display:flex; gap:8px;">
                    <button class="button secondary" id="modal-btn-cancel">Batal</button>
                    <button class="button primary" id="modal-btn-save-threshold">Simpan Nilai & Threshold</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.__EWS_DATA__ = <?= json_encode($ews, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>

