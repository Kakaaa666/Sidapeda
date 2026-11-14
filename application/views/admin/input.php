<div class="page admin-page">
    <div class="admin-intro">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions">
            <button class="button secondary" id="btn-download-template-csv">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12M7 10l5 5 5-5M4 21h16"/></svg>
                <span>Unduh Template CSV</span>
            </button>
        </div>
    </div>

    <!-- CSV IMPORT WORKSPACE -->
    <article class="card import-workspace">
        <div class="card-head">
            <div>
                <span class="eyebrow">BATCH CSV IMPORT</span>
                <h3>Import Dataset Indikator dari CSV</h3>
                <p>Mendukung format UTF-8 CSV. Validasi kolom otomatis dan pencegahan duplikasi data.</p>
            </div>
        </div>

        <label class="file-picker" id="csv-drop-area">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 16V4M7 9l5-5 5 5M4 20h16"/></svg>
            <strong>Pilih atau Seret File CSV ke Sini</strong>
            <span id="csv-filename-label">Format kolom: kode, indikator, klaster, wilayah, periode, tahun, nilai, satuan, sumber</span>
            <input type="file" id="csv-file-input" accept=".csv,text/csv">
        </label>

        <!-- CSV PREVIEW CONTAINER -->
        <div id="csv-preview-container" style="display:none; margin-top: 18px;">
            <div class="import-preview-head">
                <strong id="csv-preview-count-label">Preview Data CSV</strong>
                <button class="button primary" id="btn-commit-import">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
                    <span>Simpan Data</span>
                </button>
            </div>

            <div class="result-table-wrap" style="margin-top: 10px;">
                <table class="result-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Indikator</th>
                            <th>Tahun</th>
                            <th>Nilai</th>
                            <th>Satuan</th>
                            <th>Status Awal</th>
                        </tr>
                    </thead>
                    <tbody id="csv-preview-tbody">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>
        </div>

        <div id="csv-feedback-box" class="method-note" style="display:none; margin-top: 15px;"></div>
    </article>

    <!-- PANDUAN INPUT MANUAL DI DATABASE EKONOMI -->
    <div style="margin-top: 20px; padding: 16px 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; display: flex; align-items: center; justify-content: space-between; gap: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 36px; height: 36px; border-radius: 9px; background: #e0f2fe; color: #0284c7; display: grid; place-items: center; flex-shrink: 0;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            </div>
            <div>
                <strong style="display: block; font-size: 13px; color: var(--navy); margin-bottom: 2px;">Input Data Manual</strong>
                <p style="margin: 0; font-size: 11.5px; color: #64748b;">Penambahan dan pembaruan data secara manual terpusat di menu <strong>Database Ekonomi</strong> melalui tombol <strong>+ Tambah Data Baru</strong>.</p>
            </div>
        </div>
        <a href="<?= site_url('database') ?>" class="button secondary" style="height: 34px; font-size: 11.5px; white-space: nowrap; display: inline-flex; align-items: center; gap: 6px;">
            <span>Buka Database Ekonomi</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
        </a>
    </div>
</div>

