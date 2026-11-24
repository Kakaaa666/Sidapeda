<?php
// Ambil daftar sektor untuk dropdown filter spesifik
$sectorList = !empty($sectors) ? $sectors : (!empty($active_sectors) ? $active_sectors : []);
?>
<!-- ECONOMIC AI ASSISTANT CARD -->
<article class="card ai-assistant-card" id="economic-ai-assistant" style="margin-top: 24px; padding: 22px 24px; border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff; box-shadow: 0 2px 12px rgba(15, 23, 42, 0.04);">
    <!-- CARD HEADER -->
    <div class="ai-assistant-header" style="display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 18px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div class="ai-assistant-avatar" style="width: 44px; height: 44px; border-radius: 10px; background: #0f2942; display: flex; align-items: center; justify-content: center; color: #ffffff; flex-shrink: 0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                    <line x1="8" y1="21" x2="16" y2="21"/>
                    <line x1="12" y1="17" x2="12" y2="21"/>
                </svg>
            </div>
            <div>
                <h3 style="font: 700 18px 'Plus Jakarta Sans', sans-serif; color: #0f2942; margin: 0 0 3px; letter-spacing: -0.01em;">Asisten Analisis Ekonomi</h3>
                <p style="font-size: 12px; color: #64748b; margin: 0;">Hasilkan telaah analitis dan rekomendasi kebijakan daerah berbasis data riil Kabupaten Banyuwangi.</p>
            </div>
        </div>
    </div>

    <!-- MAIN NAVIGATION TABS -->
    <div class="ai-assistant-tabs" style="display: flex; gap: 6px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; margin-bottom: 18px; overflow-x: auto;">
        <button type="button" class="ai-tab-btn active" data-tab="generator">Generator Laporan</button>
        <button type="button" class="ai-tab-btn" data-tab="anomali">Deteksi Anomali</button>
        <button type="button" class="ai-tab-btn" data-tab="prediksi">Proyeksi Tren</button>
        <button type="button" class="ai-tab-btn" data-tab="rekomendasi">Rekomendasi Cepat</button>
    </div>

    <!-- TAB PANEL 1: GENERATOR LAPORAN OTOMATIS (PRIMARY TAB) -->
    <div class="ai-tab-panel" id="ai-panel-generator" style="display: block;">
        
        <!-- FORM PENGATURAN ANALISIS (TATA LETAK PROPORSIONAL & KOMPAK) -->
        <div class="ai-config-box" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px;">
            
            <!-- BARIS 1: PEMILIH METODE ANALISIS (SEGMEN HORIZONTAL RAPI) -->
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 11.5px; font-weight: 700; color: #334155; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.04em;">
                    Metode Analisis
                </label>
                <div class="ai-method-selector" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 8px;">
                    <label class="ai-method-btn active" data-method="all">
                        <input type="radio" name="ai_method_choice" value="all" checked style="display: none;">
                        <span class="btn-title">Semua Metode</span>
                        <span class="btn-sub">Sintesis Terpadu</span>
                    </label>
                    <label class="ai-method-btn" data-method="lq">
                        <input type="radio" name="ai_method_choice" value="lq" style="display: none;">
                        <span class="btn-title">Location Quotient</span>
                        <span class="btn-sub">Sektor Basis</span>
                    </label>
                    <label class="ai-method-btn" data-method="shift_share">
                        <input type="radio" name="ai_method_choice" value="shift_share" style="display: none;">
                        <span class="btn-title">Shift Share</span>
                        <span class="btn-sub">Daya Saing</span>
                    </label>
                    <label class="ai-method-btn" data-method="klassen">
                        <input type="radio" name="ai_method_choice" value="klassen" style="display: none;">
                        <span class="btn-title">Tipologi Klassen</span>
                        <span class="btn-sub">4 Kuadran</span>
                    </label>
                    <label class="ai-method-btn" data-method="korelasi">
                        <input type="radio" name="ai_method_choice" value="korelasi" style="display: none;">
                        <span class="btn-title">Korelasi Makro</span>
                        <span class="btn-sub">Pearson r</span>
                    </label>
                </div>
            </div>

            <!-- BARIS 2: PENGATURAN FOKUS DAN SPESIFIKASI (GRID 2 KOLOM SEIMBANG) -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <!-- KOLOM KIRI: TUJUAN LAPORAN & FOKUS SEKTOR -->
                <div>
                    <label for="ai-focus-select" style="display: block; font-size: 11.5px; font-weight: 700; color: #334155; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">
                        Tujuan / Kebutuhan Laporan
                    </label>
                    <select id="ai-focus-select" class="form-control" style="width: 100%; height: 38px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 10px; font-size: 12.5px; color: #0f172a; background: #ffffff;">
                        <option value="kebijakan" selected>Bahan Perencanaan Daerah (RKPD)</option>
                        <option value="komprehensif">Ringkasan Eksekutif Pimpinan (360°)</option>
                        <option value="hilirisasi">Pemetaan Hilirisasi & Sektor Unggulan</option>
                        <option value="ketenagakerjaan">Penyerapan Tenaga Kerja & TPT</option>
                        <option value="mitigasi">Mitigasi Risiko & Sektor Tertinggal</option>
                        <option value="custom">Pertanyaan Khusus / Kustom</option>
                    </select>

                    <div style="margin-top: 10px;">
                        <label for="ai-sector-select" style="display: block; font-size: 11.5px; font-weight: 700; color: #334155; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">
                            Fokus Lapangan Usaha (Opsional)
                        </label>
                        <select id="ai-sector-select" class="form-control" style="width: 100%; height: 38px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 10px; font-size: 12.5px; color: #0f172a; background: #ffffff;">
                            <option value="">-- Seluruh Sektor Lapangan Usaha (Agregat) --</option>
                            <?php if (!empty($sectorList)): ?>
                                <?php foreach ($sectorList as $sec): 
                                    $secName = $sec['nama_sektor'] ?? $sec['name'] ?? '';
                                    if (!empty($secName)):
                                ?>
                                    <option value="<?= htmlspecialchars($secName) ?>"><?= htmlspecialchars($secName) ?></option>
                                <?php endif; endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <!-- KOLOM KANAN: PERTANYAAN KHUSUS & PRESET CEPAT -->
                <div>
                    <label for="ai-query-input" style="display: block; font-size: 11.5px; font-weight: 700; color: #334155; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.04em;">
                        Instruksi / Pertanyaan Spesifik (Opsional)
                    </label>
                    <textarea id="ai-query-input" placeholder="Tuliskan pertanyaan tambahan di sini jika ada hal tertentu yang ingin dibahas..." style="width: 100%; height: 86px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 9px 12px; font-size: 12px; color: #0f172a; resize: none; font-family: inherit; box-sizing: border-box; line-height: 1.5;"></textarea>
                </div>
            </div>

            <!-- BARIS 3: PRESET PERTANYAAN CEPAT (MINIMALIS & TANPA EMOJI) -->
            <div style="margin-bottom: 16px;">
                <span style="font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 6px; display: block;">Contoh Pertanyaan Cepat:</span>
                <div class="ai-chips-list" style="display: flex; gap: 6px; flex-wrap: wrap;">
                    <button type="button" class="ai-prompt-chip" data-method="all" data-focus="kebijakan" data-prompt="Apa rumusan strategi kebijakan prioritas RKPD Kabupaten Banyuwangi berbasis temuan metode ekonometrik ini?">
                        Prioritas Kebijakan RKPD
                    </button>
                    <button type="button" class="ai-prompt-chip" data-method="lq" data-focus="hilirisasi" data-prompt="Identifikasi sektor mana yang memiliki LQ tertinggi dan bagaimana strategi hilirisasi produknya agar nilai tambah meningkat?">
                        Hilirisasi Sektor Basis LQ
                    </button>
                    <button type="button" class="ai-prompt-chip" data-method="korelasi" data-focus="ketenagakerjaan" data-prompt="Bagaimana pengaruh pertumbuhan ekonomi terhadap penyerapan tenaga kerja dan penurunan angka pengangguran di Banyuwangi?">
                        Dampak Pertumbuhan vs TPT
                    </button>
                    <button type="button" class="ai-prompt-chip" data-method="shift_share" data-focus="komprehensif" data-prompt="Uraikan interpretasi dari angka Regional Shift dan Bauran Industri Banyuwangi terhadap ketahanan ekonomi.">
                        Daya Saing Regional Shift
                    </button>
                    <button type="button" class="ai-prompt-chip" data-method="klassen" data-focus="mitigasi" data-prompt="Sektor apa saja yang masuk Kuadran IV Klassen dan bagaimana rekomendasi program intervensi untuk mengentaskannya?">
                        Intervensi Kuadran IV Klassen
                    </button>
                </div>
            </div>

            <!-- BARIS 4: ACTION BAR (PROPORSIONAL, TEPAT, & EFISIEN) -->
            <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #e2e8f0; padding-top: 14px; gap: 12px; flex-wrap: wrap;">
                <div style="font-size: 11.5px; color: #64748b;">
                    Data diolah langsung dari indikator riil pembangunan Kabupaten Banyuwangi.
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="button" id="btn-reset-ai" class="button secondary" style="height: 36px; padding: 0 14px; font-size: 12px; border-radius: 6px;">
                        Reset
                    </button>
                    <button type="button" id="btn-submit-ai" class="button primary" style="height: 36px; padding: 0 20px; font-size: 12.5px; font-weight: 600; border-radius: 6px; background: #0f2942;">
                        <span>Generate Analisis</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- TAMPILAN LOADING ELEGAN & SERAGAM -->
        <div id="ai-response-loading" style="display: none; padding: 28px 20px; border-radius: 10px; background: #f8fafc; border: 1px dashed #cbd5e1; margin-top: 16px; text-align: center;">
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 50%; background: #e0f2fe; margin-bottom: 10px; color: #0284c7;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="ai-spin-icon" style="animation: spin 1.2s linear infinite;">
                    <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                </svg>
            </div>
            <h4 style="font-size: 14px; color: #0f2942; font-weight: 700; margin: 0 0 4px;">Sedang Menyusun Laporan Analisis Ekonomi</h4>
            <p id="ai-loading-step-text" style="font-size: 12px; color: #64748b; margin: 0;">Mengekstrak dataset sektoral dan memproses parameter ekonometrik daerah...</p>
        </div>

        <!-- TAMPILAN HASIL LAPORAN (SERAGAM & BERSIH) -->
        <div id="ai-response-container" style="display: none; border-radius: 10px; background: #ffffff; border: 1px solid #cbd5e1; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.05); overflow: hidden; margin-top: 20px;">
            <!-- HEADER LAPORAN -->
            <div style="background: #0f2942; color: #ffffff; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div>
                    <div style="font-size: 10px; font-weight: 700; color: #94a3b8; letter-spacing: 0.06em; text-transform: uppercase;">
                        PEMERINTAH KABUPATEN BANYUWANGI • SIDAPEDA
                    </div>
                    <div style="font-size: 15px; font-weight: 700; color: #ffffff; margin-top: 2px;">
                        TELAAH ANALISIS EKONOMI DAERAH
                    </div>
                </div>
                
                <!-- ACTION BUTTONS -->
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button type="button" id="btn-copy-ai" class="button secondary small" style="height: 30px; padding: 0 12px; font-size: 11.5px; border-radius: 6px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.25); color: #ffffff;">
                        <span id="copy-btn-label">Salin Teks</span>
                    </button>
                    <button type="button" id="btn-download-ai" class="button secondary small" style="height: 30px; padding: 0 12px; font-size: 11.5px; border-radius: 6px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.25); color: #ffffff;">
                        <span>Unduh .md</span>
                    </button>
                    <button type="button" id="btn-print-ai" class="button secondary small" style="height: 30px; padding: 0 12px; font-size: 11.5px; border-radius: 6px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.25); color: #ffffff;">
                        <span>Cetak</span>
                    </button>
                </div>
            </div>

            <!-- SUB-BAR METADATA LAPORAN (SERAGAM & BERSIH) -->
            <div style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 8px 20px; display: flex; justify-content: space-between; align-items: center; font-size: 11.5px; color: #475569; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <span>Metode: <strong id="ai-meta-method" style="color: #0f2942;">Semua Metode</strong></span>
                    <span>Tujuan: <strong id="ai-meta-focus" style="color: #0f2942;">Bahan Perencanaan Daerah</strong></span>
                    <span>Status: <strong style="color: #166534;">Terverifikasi</strong></span>
                </div>
                <div>
                    <span>Waktu: <strong id="ai-meta-time" style="color: #0f2942;">-</strong></span>
                </div>
            </div>

            <!-- ISI DOKUMEN LAPORAN (DESAIN SERAGAM MONOKROM PROFESIONAL) -->
            <div id="ai-response-content" class="ai-uniform-content" style="padding: 22px 24px; font-size: 13px; line-height: 1.75; color: #334155; background: #ffffff;">
                <!-- Generated content injected here -->
            </div>

            <!-- FOOTER LAPORAN -->
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 10px 20px; font-size: 11px; color: #64748b; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                <span>Dokumen diolah oleh Sistem Intelijen Ekonomi Sidapeda Kabupaten Banyuwangi.</span>
                <button type="button" onclick="document.getElementById('economic-ai-assistant').scrollIntoView({behavior:'smooth'})" style="background: none; border: none; color: #0284c7; font-weight: 600; cursor: pointer; font-size: 11.5px; padding: 0;">
                    Kembali ke Atas ↑
                </button>
            </div>
        </div>
    </div>

    <!-- TAB PANEL 2: ANOMALI -->
    <div class="ai-tab-panel" id="ai-panel-anomali" style="display: none;">
        <div style="font-size: 12px; color: #64748b; margin-bottom: 14px;">Deteksi otomatis diskrepansi dan anomali pada indikator makro serta sektoral daerah:</div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px;">
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #dc2626;"></span>
                    <strong style="font-size: 12.5px; color: #0f2942;">Divergensi Pertumbuhan vs Pengangguran</strong>
                </div>
                <p style="font-size: 12px; color: #475569; margin: 0; line-height: 1.6;">
                    Laju PDRB ADHK tercatat tumbuh solid (5.03%), namun penyerapan tenaga kerja pada sektor pendorong utama relatif padat modal, sehingga laju penurunan TPT cenderung melambat.
                </p>
            </div>
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #d97706;"></span>
                    <strong style="font-size: 12.5px; color: #0f2942;">Efek Bauran Industri Negatif</strong>
                </div>
                <p style="font-size: 12px; color: #475569; margin: 0; line-height: 1.6;">
                    Analisis Shift-Share mengindikasikan komponen <em>industry mix</em> berkontribusi negatif (-380), mencerminkan ketergantungan pada sektor dengan tren pertumbuhan moderat di level Jawa Timur.
                </p>
            </div>
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #0284c7;"></span>
                    <strong style="font-size: 12.5px; color: #0f2942;">Konsentrasi Kuadran Klassen</strong>
                </div>
                <p style="font-size: 12px; color: #475569; margin: 0; line-height: 1.6;">
                    Mayoritas nilai tambah bertumpu pada Kuadran I dan II, menuntut perlunya diversifikasi industri pengolahan agar sektor Kuadran IV mampu bertransformasi.
                </p>
            </div>
        </div>
    </div>

    <!-- TAB PANEL 3: PREDIKSI TREN -->
    <div class="ai-tab-panel" id="ai-panel-prediksi" style="display: none;">
        <div style="font-size: 12px; color: #64748b; margin-bottom: 14px;">Estimasi proyeksi tren ekstrapolasi linear & regresi berbasis seri data historis:</div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px;">
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px;">
                <span class="eyebrow" style="font-size: 10px; color: #64748b; font-weight: 700;">PROYEKSI PDRB 2025</span>
                <h4 style="font-size: 18px; color: #0f2942; margin: 4px 0 6px; font-weight: 700;">5.18% – 5.35%</h4>
                <p style="font-size: 11.5px; color: #475569; margin: 0; line-height: 1.5;">Tren ekspansi berlanjut didorong konsumsi rumah tangga dan penguatan pariwisata daerah.</p>
            </div>
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px;">
                <span class="eyebrow" style="font-size: 10px; color: #64748b; font-weight: 700;">PROYEKSI TPT 2025</span>
                <h4 style="font-size: 18px; color: #0f2942; margin: 4px 0 6px; font-weight: 700;">4.40% – 4.55%</h4>
                <p style="font-size: 11.5px; color: #475569; margin: 0; line-height: 1.5;">Penurunan pengangguran terbuka konsisten, dengan korelasi negatif kuat terhadap laju PDRB.</p>
            </div>
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px;">
                <span class="eyebrow" style="font-size: 10px; color: #64748b; font-weight: 700;">KESTABILAN HARGA (INFLASI)</span>
                <h4 style="font-size: 18px; color: #0f2942; margin: 4px 0 6px; font-weight: 700;">2.45% – 2.90%</h4>
                <p style="font-size: 11.5px; color: #475569; margin: 0; line-height: 1.5;">Rentang inflasi terkendali dalam sasaran TPID Kabupaten Banyuwangi (2.5 ± 1%).</p>
            </div>
        </div>
    </div>

    <!-- TAB PANEL 4: REKOMENDASI CEPAT -->
    <div class="ai-tab-panel" id="ai-panel-rekomendasi" style="display: none;">
        <div style="font-size: 12px; color: #64748b; margin-bottom: 14px;">Rekomendasi kebijakan berbasis data (Evidence-Based Policy Making):</div>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-left: 3px solid #0284c7; border-radius: 8px; padding: 12px 16px;">
                <strong style="font-size: 12.5px; color: #0f2942; display: block; margin-bottom: 2px;">Optimalisasi Sektor Basis LQ (Location Quotient > 1.0)</strong>
                <span style="font-size: 12px; color: #475569; line-height: 1.6;">Tingkatkan hilirisasi komoditas pertanian dan perikanan dengan fasilitas pengolahan pascapanen agar nilai tambah tetap berputar di Banyuwangi.</span>
            </div>
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-left: 3px solid #0284c7; border-radius: 8px; padding: 12px 16px;">
                <strong style="font-size: 12.5px; color: #0f2942; display: block; margin-bottom: 2px;">Penguatan Daya Saing Lokal (Shift Share Regional Shift)</strong>
                <span style="font-size: 12px; color: #475569; line-height: 1.6;">Pertahankan keunggulan kompetitif sektor perdagangan, transportasi, dan pariwisata melalui integrasi rantai pasok dan standarisasi mutu.</span>
            </div>
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-left: 3px solid #0284c7; border-radius: 8px; padding: 12px 16px;">
                <strong style="font-size: 12.5px; color: #0f2942; display: block; margin-bottom: 2px;">Program Peningkatan Kesempatan Kerja Tematik</strong>
                <span style="font-size: 12px; color: #475569; line-height: 1.6;">Sinkronkan kurikulum pelatihan vokasi BLK dengan kebutuhan sektor basis berdaya saing tinggi guna mengikis disparitas TPT.</span>
            </div>
        </div>
    </div>
</article>

<style>
/* STYLING MINIMALIS & PROPORSIONAL */
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* TOMBOL METODE (SEGMEN HORIZONTAL RAPI) */
.ai-method-btn {
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 8px 10px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.15s ease;
    text-align: left;
}
.ai-method-btn:hover {
    border-color: #0284c7;
    background: #f8fafc;
}
.ai-method-btn.active {
    border-color: #0284c7;
    background: #eff6fc;
}
.ai-method-btn .btn-title {
    font-size: 12px;
    font-weight: 700;
    color: #1e293b;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ai-method-btn.active .btn-title {
    color: #0284c7;
}
.ai-method-btn .btn-sub {
    font-size: 10.5px;
    color: #64748b;
    margin-top: 1px;
    display: block;
}
.ai-method-btn.active .btn-sub {
    color: #0369a1;
}

@media (max-width: 900px) {
    .ai-method-selector {
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)) !important;
    }
}

/* FORMAT LAPORAN SERAGAM (UNIFORM MONOCHROME REPORT) */
.ai-uniform-content .report-section {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px 18px;
    margin-bottom: 14px;
    background: #ffffff;
}
.ai-uniform-content .report-section:last-child {
    margin-bottom: 0;
}
.ai-uniform-content .section-title {
    font-size: 13.5px;
    font-weight: 700;
    color: #0f2942;
    padding-bottom: 6px;
    border-bottom: 1px solid #f1f5f9;
    margin: 0 0 10px 0;
    letter-spacing: -0.01em;
}
.ai-uniform-content p {
    margin: 0 0 10px 0;
    line-height: 1.7;
    color: #334155;
    font-size: 12.5px;
}
.ai-uniform-content p:last-child {
    margin-bottom: 0;
}
.ai-uniform-content ul, .ai-uniform-content ol {
    margin: 6px 0 10px 18px;
    padding-left: 0;
}
.ai-uniform-content li {
    margin-bottom: 6px;
    line-height: 1.65;
    font-size: 12.5px;
    color: #334155;
}
.ai-uniform-content li:last-child {
    margin-bottom: 0;
}
.ai-uniform-content strong {
    color: #0f2942;
    font-weight: 700;
}
.ai-uniform-content .data-metric-tag {
    background: #f1f5f9;
    color: #0f2942;
    border: 1px solid #e2e8f0;
    padding: 1px 5px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 11.5px;
}

@media print {
    body * {
        visibility: hidden;
    }
    #ai-response-container, #ai-response-container * {
        visibility: visible;
    }
    #ai-response-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        border: none !important;
        box-shadow: none !important;
    }
    #btn-copy-ai, #btn-download-ai, #btn-print-ai {
        display: none !important;
    }
}
</style>
