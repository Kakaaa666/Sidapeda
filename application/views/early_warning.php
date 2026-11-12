<div class="page">
    <div class="module-intro">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions">
            <span class="badge simulation">DATABASE CI 3</span>
            <button class="button secondary" id="btn-export-ews">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12M7 10l5 5 5-5M4 21h16"/></svg>
                <span>Ekspor CSV</span>
            </button>
        </div>
    </div>

    <!-- STATS OVERVIEW -->
    <div class="risk-overview" id="ews-stats-container">
        <!-- Rendered by JS / PHP -->
    </div>

    <!-- CONTENT GRID -->
    <section class="two-col risk-content">
        <!-- MONITORING TABLE -->
        <article class="card large">
            <div class="card-head">
                <div>
                    <span class="eyebrow">RISK MONITORING</span>
                    <h3>Indikator dalam Pemantauan Dini</h3>
                    <p>Status dihitung otomatis dari nilai riil dan threshold konfigurasi.</p>
                </div>
            </div>

            <div class="segmented ews-filters" id="ews-filter-tabs">
                <button class="active" data-status="Semua">Semua</button>
                <button data-status="Kritis">Kritis</button>
                <button data-status="Waspada">Waspada</button>
                <button data-status="Normal">Normal</button>
            </div>

            <div class="risk-table" id="ews-table-rows">
                <!-- Injected by JS -->
            </div>

            <div class="source">Kriteria Sistem: Deviasi &lt; 0% = Normal; 0–9.9% = Waspada; ≥ 10% = Kritis.</div>
        </article>

        <!-- RISK MATRIX & PROGRESS -->
        <article class="card">
            <div class="card-head">
                <div>
                    <span class="eyebrow">RISK MATRIX</span>
                    <h3>Ringkasan Deviasi Ambang</h3>
                </div>
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
</div>

