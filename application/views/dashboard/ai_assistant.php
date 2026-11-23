<!-- ECONOMIC AI ASSISTANT CARD -->
<article class="card ai-assistant-card" id="economic-ai-assistant" style="margin-top: 24px; padding: 22px 24px; border: 1px solid #e2e8f0; border-radius: 14px; background: #ffffff; box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);">
    <!-- CARD HEADER -->
    <div class="ai-assistant-header" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div class="ai-assistant-avatar" style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #0e385e 0%, #164e63 100%); display: flex; align-items: center; justify-content: center; color: #ffffff; flex-shrink: 0; box-shadow: 0 4px 12px rgba(14, 56, 94, 0.2);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3L12 3z"/>
                </svg>
            </div>
            <div>
                <span class="eyebrow" style="font-size: 10.5px; font-weight: 700; color: #0284c7; letter-spacing: 0.06em; margin: 0; text-transform: uppercase;">ECONOMIC AI ASSISTANT</span>
                <h3 style="font: 700 18.5px 'Plus Jakarta Sans', sans-serif; color: #0f2942; margin: 3px 0 2px; letter-spacing: -0.01em;">Asisten AI Analisis Ekonomi</h3>
                <p style="font-size: 11.5px; color: #64748b; margin: 0;">Tanyakan data, deteksi anomali, proyeksikan tren, dan susun rekomendasi tindakan.</p>
            </div>
        </div>
        <div class="ai-mode-badge" style="display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 20px; background: #fef9ee; border: 1px solid #fde68a; font-size: 11px; font-weight: 600; color: #92400e;">
            <span style="width: 7px; height: 7px; border-radius: 50%; background: #d97706; display: inline-block;"></span>
            <span>Mode lokal</span>
        </div>
    </div>

    <!-- TABS -->
    <div class="ai-assistant-tabs" style="display: flex; gap: 6px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin: 18px 0 16px; overflow-x: auto;">
        <button type="button" class="ai-tab-btn active" data-tab="tanya">Tanya AI</button>
        <button type="button" class="ai-tab-btn" data-tab="anomali">Anomali</button>
        <button type="button" class="ai-tab-btn" data-tab="prediksi">Prediksi tren</button>
        <button type="button" class="ai-tab-btn" data-tab="rekomendasi">Rekomendasi</button>
    </div>

    <!-- TAB PANEL: TANYA AI (DEFAULT) -->
    <div class="ai-tab-panel" id="ai-panel-tanya">
        <div style="font-size: 11.5px; color: #64748b; margin-bottom: 10px;">Ajukan pertanyaan dengan bahasa sehari-hari</div>
        
        <!-- PROMPT CHIPS -->
        <div class="ai-chips-list" style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px;">
            <button type="button" class="ai-prompt-chip" data-prompt="Apa perubahan paling penting?">Apa perubahan paling penting?</button>
            <button type="button" class="ai-prompt-chip" data-prompt="Apa yang perlu diwaspadai?">Apa yang perlu diwaspadai?</button>
            <button type="button" class="ai-prompt-chip" data-prompt="Apa rekomendasi tindak lanjut?">Apa rekomendasi tindak lanjut?</button>
        </div>

        <!-- FORM QUERY -->
        <div class="ai-input-wrapper" style="display: flex; gap: 12px; align-items: stretch;">
            <textarea id="ai-query-input" placeholder="Contoh: Mengapa pertumbuhan ekonomi naik tetapi pengangguran belum turun?" style="flex: 1; border: 1px solid #cbd5e1; border-radius: 10px; padding: 12px 14px; font-size: 12.5px; color: #0f172a; resize: none; min-height: 64px; outline: none; font-family: inherit; line-height: 1.5;"></textarea>
            <button type="button" id="btn-submit-ai" style="background: #64748b; color: #ffffff; border: none; border-radius: 10px; padding: 0 22px; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; cursor: pointer; min-width: 120px; transition: all 0.15s ease;">
                <span>Tanyakan</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
            </button>
        </div>

        <!-- AI RESPONSE BOX -->
        <div id="ai-response-container" style="display: none; margin-top: 14px; padding: 16px 18px; border-radius: 10px; background: #f8fafc; border: 1px solid #e2e8f0; position: relative;">
            <div id="ai-response-loading" style="display: none; align-items: center; gap: 10px; color: #0284c7; font-size: 12px; font-weight: 600;">
                <span class="pulse" style="width: 8px; height: 8px; border-radius: 50%; background: #0284c7; display: inline-block;"></span>
                <span>Asisten AI sedang menganalisis data ekonomi daerah...</span>
            </div>
            <div id="ai-response-content" style="font-size: 12.5px; line-height: 1.65; color: #1e293b;"></div>
        </div>
    </div>

    <!-- TAB PANEL: ANOMALI -->
    <div class="ai-tab-panel" id="ai-panel-anomali" style="display: none;">
        <div style="font-size: 11.5px; color: #64748b; margin-bottom: 12px;">Deteksi otomatis diskrepansi dan anomali pada indikator makro serta sektoral daerah:</div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px;">
            <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 14px;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #ef4444;"></span>
                    <strong style="font-size: 12.5px; color: #991b1b;">Divergensi Pertumbuhan vs Pengangguran</strong>
                </div>
                <p style="font-size: 11.5px; color: #7f1d1d; margin: 0; line-height: 1.55;">
                    Laju PDRB ADHK tercatat tumbuh solid, namun elastisitas penyerapan tenaga kerja pada sektor pendorong utama relatif padat modal, sehingga laju penurunan TPT cenderung melambat.
                </p>
            </div>
            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 14px;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #f59e0b;"></span>
                    <strong style="font-size: 12.5px; color: #92400e;">Efek Bauran Industri Negatif</strong>
                </div>
                <p style="font-size: 11.5px; color: #78350f; margin: 0; line-height: 1.55;">
                    Analisis Shift-Share mengindikasikan komponen <em>industry mix</em> berkontribusi negatif (-380), mencerminkan ketergantungan pada sektor dengan tren pertumbuhan moderat di level Jawa Timur.
                </p>
            </div>
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 14px;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #3b82f6;"></span>
                    <strong style="font-size: 12.5px; color: #1e40af;">Konsentrasi Kuadran Klassen</strong>
                </div>
                <p style="font-size: 11.5px; color: #1e3a8a; margin: 0; line-height: 1.55;">
                    Mayoritas nilai tambah bertumpu pada Kuadran I dan II, menuntut perlunya akselerasi diversifikasi industri pengolahan agar sektor Kuadran IV mampu terangkat.
                </p>
            </div>
        </div>
    </div>

    <!-- TAB PANEL: PREDIKSI TREN -->
    <div class="ai-tab-panel" id="ai-panel-prediksi" style="display: none;">
        <div style="font-size: 11.5px; color: #64748b; margin-bottom: 12px;">Estimasi proyeksi tren ekstrapolasi linear & regresi berbasis seri data historis:</div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px;">
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px;">
                <span class="eyebrow" style="font-size: 10px; color: #64748b; font-weight: 700;">PROYEKSI PDRB 2025</span>
                <h4 style="font-size: 18px; color: #0284c7; margin: 4px 0 6px; font-weight: 700;">5.18% – 5.35%</h4>
                <p style="font-size: 11.5px; color: #475569; margin: 0; line-height: 1.5;">Tren ekspansi diperkirakan berlanjut didorong konsumsi rumah tangga dan penguatan pariwisata daerah.</p>
            </div>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px;">
                <span class="eyebrow" style="font-size: 10px; color: #64748b; font-weight: 700;">PROYEKSI TPT 2025</span>
                <h4 style="font-size: 18px; color: #10b981; margin: 4px 0 6px; font-weight: 700;">4.40% – 4.55%</h4>
                <p style="font-size: 11.5px; color: #475569; margin: 0; line-height: 1.5;">Tren penurunan pengangguran terbuka konsisten, dengan korelasi negatif kuat terhadap laju PDRB.</p>
            </div>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px;">
                <span class="eyebrow" style="font-size: 10px; color: #64748b; font-weight: 700;">KESTABILAN HARGA (INFLASI)</span>
                <h4 style="font-size: 18px; color: #f59e0b; margin: 4px 0 6px; font-weight: 700;">2.45% – 2.90%</h4>
                <p style="font-size: 11.5px; color: #475569; margin: 0; line-height: 1.5;">Rentang inflasi terkendali dalam koridor target TPID Kabupaten Banyuwangi (2.5 ± 1%).</p>
            </div>
        </div>
    </div>

    <!-- TAB PANEL: REKOMENDASI -->
    <div class="ai-tab-panel" id="ai-panel-rekomendasi" style="display: none;">
        <div style="font-size: 11.5px; color: #64748b; margin-bottom: 12px;">Rekomendasi kebijakan berbasis data (Evidence-Based Policy Making):</div>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 12px 14px; display: flex; align-items: flex-start; gap: 10px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                <div>
                    <strong style="font-size: 12px; color: #166534; display: block; margin-bottom: 2px;">Optimalisasi Sektor Basis LQ (Location Quotient > 1.0)</strong>
                    <span style="font-size: 11.5px; color: #15803d; line-height: 1.55;">Tingkatkan hilirisasi komoditas pertanian dan perikanan dengan fasilitas pengolahan pascapanen agar nilai tambah tetap berputar di Banyuwangi.</span>
                </div>
            </div>
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 12px 14px; display: flex; align-items: flex-start; gap: 10px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                <div>
                    <strong style="font-size: 12px; color: #166534; display: block; margin-bottom: 2px;">Penguatan Daya Saing Lokal (Shift Share Regional Shift)</strong>
                    <span style="font-size: 11.5px; color: #15803d; line-height: 1.55;">Pertahankan keunggulan kompetitif sektor perdagangan, transportasi, dan akomodasi pariwisata melalui integrasi digital dan sertifikasi standar mutu.</span>
                </div>
            </div>
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 12px 14px; display: flex; align-items: flex-start; gap: 10px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                <div>
                    <strong style="font-size: 12px; color: #166534; display: block; margin-bottom: 2px;">Program Peningkatan Kesempatan Kerja Tematik</strong>
                    <span style="font-size: 11.5px; color: #15803d; line-height: 1.55;">Sinkronkan kurikulum vokasi dan pelatihan tenaga kerja BLK dengan kebutuhan sektor basis berdaya saing tinggi guna mengikis disparitas TPT.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- DISCLAIMER -->
    <div class="ai-assistant-disclaimer" style="display: flex; align-items: flex-start; gap: 8px; margin-top: 16px; font-size: 10.5px; color: #94a3b8; line-height: 1.55;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="flex-shrink: 0; margin-top: 1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        <span>AI hanya menganalisis data dan metadata yang tersedia pada konteks ini. Periksa kembali sumber, periode, satuan, dan status validasi; rekomendasi merupakan bahan pertimbangan, bukan keputusan otomatis.</span>
    </div>
</article>
