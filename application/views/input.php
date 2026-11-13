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

    <!-- MANUAL FORM CARD -->
    <article class="card" style="margin-top: 20px;">
        <div class="card-head">
            <div>
                <span class="eyebrow">FORM INPUT TUNGGAL</span>
                <h3>Input Manual Indikator</h3>
                <p>Tambah data langsung ke tabel <code>indicator_records</code>.</p>
            </div>
        </div>

        <form id="form-direct-input" style="margin-top: 15px;">
            <div class="manual-form">
                <label>
                    Kode Indikator
                    <input type="text" id="dir-code" placeholder="EKO-201" required>
                </label>
                <label>
                    Nama Indikator
                    <input type="text" id="dir-indicator" placeholder="PDRB Sektor Tertentu" required>
                </label>
                <label>
                    Klaster
                    <select id="dir-cluster">
                        <option>Output & Pertumbuhan</option>
                        <option>Harga & Inflasi</option>
                        <option>Ketenagakerjaan</option>
                        <option>Kesejahteraan Sosial</option>
                        <option>Investasi & Usaha</option>
                        <option>Fiskal Daerah</option>
                        <option>Pariwisata & Ekonomi Kreatif</option>
                        <option>Produksi Sektoral</option>
                        <option>Keterkaitan Antarsektor</option>
                    </select>
                </label>
                <label>
                    Wilayah
                    <input type="text" id="dir-region" value="Kabupaten Banyuwangi" required>
                </label>
                <label>
                    Periode
                    <select id="dir-period">
                        <option>Tahunan</option>
                        <option>Semesteran</option>
                        <option>Triwulanan</option>
                        <option>Bulanan</option>
                    </select>
                </label>
                <label>
                    Tahun
                    <input type="number" id="dir-year" value="2024" required>
                </label>
                <label>
                    Nilai
                    <input type="number" step="any" id="dir-value" placeholder="10.5" required>
                </label>
                <label>
                    Satuan
                    <input type="text" id="dir-unit" value="%" required>
                </label>
                <label>
                    Status Validasi
                    <select id="dir-status">
                        <option value="Tervalidasi" selected>Tervalidasi (Langsung Masuk Beranda)</option>
                        <option value="Perlu review">Perlu review</option>
                    </select>
                </label>
                <label style="grid-column: 1 / 3;">
                    Sumber Data
                    <input type="text" id="dir-source" value="BPS Kabupaten Banyuwangi" required>
                </label>
            </div>

            <div id="dir-feedback-box" class="method-note" style="display:none;"></div>

            <div style="text-align: right; margin-top: 10px;">
                <button type="submit" class="button primary" id="btn-submit-direct">Simpan Rekaman Baru</button>
            </div>
        </form>
    </article>
</div>

