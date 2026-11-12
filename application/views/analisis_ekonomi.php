<div class="page">
    <div class="module-intro">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions">
            <span class="badge simulation">DATABASE CI 3</span>
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
                <span class="badge simulation">CI 3 + MYSQL</span>
            </div>

            <!-- DYNAMIC METHOD CONTENT -->
            <div id="analysis-content-container">
                <!-- Injected by JavaScript -->
            </div>
        </main>
    </div>
</div>

