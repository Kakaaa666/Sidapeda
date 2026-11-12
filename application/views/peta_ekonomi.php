<div class="map-page economic-map-page">
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
        <!-- TEMPAT SAMPAH KECAMATAN -->
        <article class="card large" style="margin-bottom:24px; border:1px dashed #f43f5e; background:rgba(244,63,94,0.03);">
            <div class="card-head">
                <div>
                    <span class="badge danger">TEMPAT SAMPAH (SOFT DELETE)</span>
                    <h3>Daftar Kecamatan Terhapus (<?= count($districts) ?>)</h3>
                    <p>Klik tombol Pulihkan untuk mengembalikan kecamatan ke peta spasial dan dataset aktif.</p>
                </div>
                <a href="<?= site_url('peta_ekonomi') ?>" class="button ghost">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    <span>Kembali ke Peta Aktif</span>
                </a>
            </div>
            <div class="data-table">
                <div class="data-tr data-th" style="grid-template-columns: 2fr 1fr 1fr 1fr 1.2fr 120px;">
                    <span>Kecamatan</span>
                    <span>Porsi PDRB</span>
                    <span>Pertumbuhan</span>
                    <span>Kemiskinan</span>
                    <span>Dihapus Pada</span>
                    <span>Aksi</span>
                </div>
                <?php if (empty($districts)): ?>
                    <div style="padding:24px; text-align:center; color:var(--muted); font-size:11px;">
                        Tempat sampah kosong. Tidak ada kecamatan yang terhapus.
                    </div>
                <?php else: ?>
                    <?php foreach ($districts as $item): ?>
                        <div class="data-tr" style="grid-template-columns: 2fr 1fr 1fr 1fr 1.2fr 120px;">
                            <span><strong><?= htmlspecialchars($item['name'] ?? $item['nama_kecamatan'] ?? '') ?></strong></span>
                            <span><?= number_format($item['pdrbShare'] ?? $item['porsi_pdrb'] ?? 0, 2, ',', '.') ?>%</span>
                            <span><?= number_format($item['growth'] ?? $item['pertumbuhan'] ?? 0, 2, ',', '.') ?>%</span>
                            <span><?= number_format($item['poverty'] ?? $item['kemiskinan'] ?? 0, 2, ',', '.') ?>%</span>
                            <span><small><?= htmlspecialchars($item['deleted_at'] ?? '-') ?></small></span>
                            <span class="row-actions">
                                <button type="button" class="btn-restore-district" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name'] ?? $item['nama_kecamatan'] ?? '') ?>" title="Pulihkan Kecamatan">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                    <span>Pulihkan</span>
                                </button>
                                <button type="button" class="btn-hard-delete-district danger" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name'] ?? $item['nama_kecamatan'] ?? '') ?>" title="Hapus Permanen">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>
    <?php endif; ?>

    <div class="map-heading">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions">
            <?php if ($trash_count > 0): ?>
                <?php if ($status === 'Terhapus'): ?>
                    <a href="<?= site_url('peta_ekonomi') ?>" class="button ghost">
                        <span>Kembali ke Aktif</span>
                    </a>
                <?php else: ?>
                    <a href="<?= site_url('peta_ekonomi?status=Terhapus') ?>" class="button ghost danger">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        <span>Sampah (<?= $trash_count ?>)</span>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            <button class="button primary" id="btn-open-add-district">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Tambah Kecamatan</span>
            </button>
            <button class="button secondary" id="btn-export-map">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12M7 10l5 5 5-5M4 21h16"/></svg>
                <span>Ekspor Data Peta</span>
            </button>
        </div>
    </div>

    <!-- MAP MODE TABS -->
    <div class="map-mode-tabs" id="map-mode-tabs">
        <button class="active" data-mode="Thematic Layer">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 6 5-3 8 3 5-3v15l-5 3-8-3-5 3V6Z"/><path d="M8 3v15M16 6v15"/></svg>
            <span>Thematic Layer</span>
        </button>
        <button data-mode="Multi-Layer Map">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 6 5-3 8 3 5-3v15l-5 3-8-3-5 3V6Z"/><path d="M8 3v15M16 6v15"/></svg>
            <span>Multi-Layer Map</span>
        </button>
        <button data-mode="Regional Drill-Down">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 6 5-3 8 3 5-3v15l-5 3-8-3-5 3V6Z"/><path d="M8 3v15M16 6v15"/></svg>
            <span>Regional Drill-Down</span>
        </button>
        <button data-mode="Hotspot Analysis">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 17 9 11l4 4 8-9"/><path d="M15 6h6v6"/></svg>
            <span>Hotspot Analysis</span>
        </button>
        <button data-mode="Spatial Comparison">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h12M13 4l3 3-3 3M20 17H8M11 14l-3 3 3 3"/></svg>
            <span>Spatial Comparison</span>
        </button>
    </div>

    <!-- CONTEXT BAR -->
    <div class="map-context-bar">
        <div class="map-breadcrumb">
            <button id="map-bread-kab">Kabupaten Banyuwangi</button>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
            <span class="active" id="map-bread-level">Kecamatan</span>
        </div>
        <div>
            <span>Periode data: <strong>2024</strong></span>
            <span>Satuan: <strong id="map-context-unit">%</strong></span>
            <span>Klasifikasi: <strong>Interval Terkalibrasi (5 Kelas)</strong></span>
        </div>
    </div>

    <!-- MAP LAYOUT -->
    <div class="map-layout">
        <!-- FILTER ASIDE -->
        <aside class="map-filters">
            <div class="panel-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16l-6 7v6l-4 2v-8L4 5Z"/></svg>
                <strong>Konfigurasi Peta</strong>
            </div>

            <label>
                <span>Kelompok informasi</span>
                <select id="map-group-select">
                    <option value="Aktivitas ekonomi">Aktivitas ekonomi</option>
                    <option value="Kesejahteraan">Kesejahteraan</option>
                    <option value="Investasi">Investasi</option>
                    <option value="Produksi sektoral">Produksi sektoral</option>
                    <option value="Ketenagakerjaan">Ketenagakerjaan</option>
                    <option value="Pariwisata">Pariwisata</option>
                    <option value="Harga">Harga</option>
                </select>
            </label>

            <label>
                <span>Indikator tematik</span>
                <select id="map-indicator-select">
                    <!-- Populated by JS -->
                </select>
            </label>

            <label>
                <span>Level wilayah</span>
                <select id="map-level-select">
                    <option value="Kecamatan">Kecamatan</option>
                    <option value="Desa">Desa</option>
                </select>
            </label>

            <div class="layer-list">
                <div class="layer-title">
                    <strong>Lapisan tambahan</strong>
                    <span class="badge info" id="layer-active-badge">2 aktif</span>
                </div>
                <label class="check-row">
                    <input type="checkbox" class="map-layer-cb" value="Batas kecamatan" checked>
                    <span>Batas kecamatan</span>
                </label>
                <label class="check-row">
                    <input type="checkbox" class="map-layer-cb" value="Infrastruktur ekonomi" checked>
                    <span>Infrastruktur ekonomi</span>
                </label>
                <label class="check-row">
                    <input type="checkbox" class="map-layer-cb" value="Pusat industri">
                    <span>Pusat industri</span>
                </label>
                <label class="check-row">
                    <input type="checkbox" class="map-layer-cb" value="Konsentrasi tenaga kerja">
                    <span>Konsentrasi tenaga kerja</span>
                </label>
                <label class="check-row">
                    <input type="checkbox" class="map-layer-cb" value="Potensi sektoral">
                    <span>Potensi sektoral</span>
                </label>
                <label class="check-row">
                    <input type="checkbox" class="map-layer-cb" value="Destinasi wisata">
                    <span>Destinasi wisata</span>
                </label>
            </div>

            <div class="legend-block">
                <div class="layer-title">
                    <strong id="legend-title">Legenda</strong>
                    <button id="btn-reverse-legend">Balik urutan</button>
                </div>
                <div id="legend-items-container">
                    <!-- Populated by JS -->
                </div>
            </div>
        </aside>

        <!-- MAP CANVAS -->
        <main class="map-canvas" id="map-canvas-container">
            <div class="map-search">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                <input id="map-search-input" placeholder="Cari kecamatan demo...">
            </div>

            <div class="map-toolbar">
                <button title="Pilih wilayah" id="btn-tb-drill"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 6 5-3 8 3 5-3v15l-5 3-8-3-5 3V6Z"/><path d="M8 3v15M16 6v15"/></svg></button>
                <button title="Bandingkan" id="btn-tb-compare"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h12M13 4l3 3-3 3M20 17H8M11 14l-3 3 3 3"/></svg></button>
                <button title="Identifikasi hotspot" id="btn-tb-hotspot"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 17 9 11l4 4 8-9"/><path d="M15 6h6v6"/></svg></button>
                <button title="Toggle Infrastruktur" id="btn-tb-infra"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1v.1h-4v-.1a1.7 1.7 0 0 0-1.1-1.6 1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1-.4h-.1v-4H3A1.7 1.7 0 0 0 4.6 8.5a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1v-.1h4V3a1.7 1.7 0 0 0 1.1 1.6 1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06A1.7 1.7 0 0 0 19.4 9c.1.4.3.7.6 1 .3.2.6.4 1 .4h.1v4H21a1.7 1.7 0 0 0-1.6.6Z"/></svg></button>
            </div>

            <div class="zoom">
                <button id="btn-zoom-in">+</button>
                <button id="btn-zoom-out">−</button>
                <small id="zoom-level-text">100%</small>
            </div>

            <div class="active-map-label">
                <span id="active-map-mode-label">Thematic Layer</span>
                <strong id="active-map-indicator-label">Pertumbuhan ekonomi</strong>
                <small id="active-map-meta-label">Kecamatan • 2024</small>
            </div>

            <div id="spatial-comp-bar" class="comparison-divider" style="display:none;">
                <span id="comp-div-left">Banyuwangi: 5,80%</span>
                <i></i>
                <span id="comp-div-right">Rerata: 5,20%</span>
            </div>

            <div id="hotspot-note-box" class="hotspot-note" style="display:none;">
                <span class="pulse"></span>
                <div>
                    <strong>5 kecamatan perlu perhatian</strong>
                    <small>Pesanggaran, Genteng, Glenmore, Wongsorejo, Tegaldlimo</small>
                </div>
            </div>

            <!-- SVG BANYUWANGI MAP -->
            <svg class="banyuwangi-map" id="banyuwangi-svg" viewBox="0 0 480 650" role="img" aria-label="Peta skematik kecamatan Kabupaten Banyuwangi">
                <!-- BASE REGION SHAPE & DISTRICT BOUNDARIES -->
                <g id="layer-boundary">
                    <path class="map-shape" d="M217 20 277 47 298 104 328 154 310 216 356 273 333 324 375 374 346 425 325 487 282 521 271 588 218 628 184 571 150 545 138 477 105 430 132 374 90 319 127 273 112 212 157 170 164 98Z"></path>
                    <g class="district-lines">
                        <path d="m166 99 132 5-141 67 153 45-183 57 206 51-201 50 214 51-208 52 187 10-175 58"></path>
                        <path d="m215 20 15 63-22 76 25 57-24 60 36 66-39 61 39 69-27 156"></path>
                    </g>
                </g>

                <!-- HOTSPOT PULSES -->
                <g id="layer-hotspots" class="hotspots" style="display:none;"></g>

                <!-- INFRASTRUCTURE -->
                <g id="layer-infra" class="infrastructure-layer">
                    <path d="M220 70 C250 160 190 230 240 320 S195 470 215 575"></path>
                    <rect x="204" y="145" width="16" height="10" rx="2"></rect>
                    <rect x="226" y="235" width="16" height="10" rx="2"></rect>
                    <rect x="204" y="360" width="16" height="10" rx="2"></rect>
                    <rect x="226" y="485" width="16" height="10" rx="2"></rect>
                </g>

                <!-- INDUSTRY -->
                <g id="layer-industry" class="industry-layer" style="display:none;">
                    <rect x="260" y="392" width="13" height="13" rx="2"></rect>
                    <rect x="218" y="274" width="13" height="13" rx="2"></rect>
                    <rect x="199" y="478" width="13" height="13" rx="2"></rect>
                </g>

                <!-- EMPLOYMENT CONCENTRATION -->
                <g id="layer-employment" class="employment-layer" style="display:none;">
                    <circle cx="230" cy="83" r="20"></circle>
                    <circle cx="255" cy="145" r="16"></circle>
                    <circle cx="270" cy="405" r="22"></circle>
                    <circle cx="204" cy="470" r="14"></circle>
                </g>

                <!-- POTENTIAL SECTORS -->
                <g id="layer-potential" class="potential-layer" style="display:none;">
                    <path d="M230 76 l2 5 5 1 -4 3 1 5 -4 -3 -4 3 1 -5 -4 -3 5 -1Z"></path>
                    <path d="M255 138 l2 5 5 1 -4 3 1 5 -4 -3 -4 3 1 -5 -4 -3 5 -1Z"></path>
                    <path d="M225 198 l2 5 5 1 -4 3 1 5 -4 -3 -4 3 1 -5 -4 -3 5 -1Z"></path>
                    <path d="M270 398 l2 5 5 1 -4 3 1 5 -4 -3 -4 3 1 -5 -4 -3 5 -1Z"></path>
                    <path d="M204 463 l2 5 5 1 -4 3 1 5 -4 -3 -4 3 1 -5 -4 -3 5 -1Z"></path>
                </g>

                <!-- TOURISM -->
                <g id="layer-tourism" class="tourism-layer" style="display:none;">
                    <circle cx="230" cy="83" r="5"></circle>
                    <circle cx="255" cy="145" r="5"></circle>
                    <circle cx="210" cy="276" r="5"></circle>
                    <circle cx="270" cy="405" r="5"></circle>
                </g>

                <!-- DISTRICT MARKERS (Rendered dynamically) -->
                <g id="layer-markers"></g>
            </svg>

            <div id="map-overlay-message" class="map-empty-overlay" style="display:none;">
                <strong id="map-overlay-title">Data belum tersedia</strong>
                <span id="map-overlay-sub">Pilih kecamatan untuk melihat data.</span>
            </div>

            <div class="map-scale map-not-to-scale">
                <small>Posisi ilustratif • skala skematik</small>
            </div>
            <div class="map-attribution">Peta Spasial SI-DAPEDA Kabupaten Banyuwangi</div>
        </main>

        <!-- LOCATION DETAIL ASIDE -->
        <aside class="location-panel">
            <div class="location-cover">
                <div>
                    <span id="loc-detail-level">AREA DETAIL • KECAMATAN</span>
                    <h3 id="loc-detail-name">Banyuwangi</h3>
                    <p>Kabupaten Banyuwangi</p>
                </div>
                <button class="icon-button" id="btn-reset-location" title="Kembali ke kecamatan utama">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 7v5h-5M4 17v-5h5"/><path d="M5.5 9A7 7 0 0 1 18 6l2 1M4 17l2 1a7 7 0 0 0 12.5-3"/></svg>
                </button>
            </div>

            <div class="location-body" id="loc-body-container">
                <!-- Dynamically filled by JS -->
            </div>
        </aside>
    </div>

    <div class="map-data-note">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="M12 8v5M12 17h.01"/></svg>
        <p><strong>Catatan Interpretasi:</strong> Sebanyak 12 kecamatan contoh mencerminkan sebaran spasial wilayah utara, tengah, selatan, dan pesisir. Skema peta digunakan untuk analisis tren spasial tematik daerah.</p>
        <span>DATABASE CI 3</span>
    </div>

    <!-- GRAFIK KOMPARASI KECAMATAN (Disesuaikan dengan Konten Peta & Data Kecamatan) -->
    <article class="card large" style="margin-top:24px;">
        <div class="card-head">
            <div>
                <span class="eyebrow">VISUALISASI INDIKATOR SPASIAL</span>
                <h3 id="map-chart-title">Grafik Komparasi Indikator Antar-Kecamatan</h3>
                <p id="map-chart-desc">Perbandingan peringkat capaian seluruh kecamatan berdasarkan indikator spasial terpilih.</p>
            </div>
            <div style="display:flex; gap:10px; align-items:center;">
                <select id="map-chart-metric-select" style="height:32px; border:1px solid var(--line); border-radius:6px; font-size:11px; padding:0 8px; background:var(--surface); cursor:pointer;">
                    <option value="growth">Pertumbuhan Ekonomi (%)</option>
                    <option value="poverty">Tingkat Kemiskinan (%)</option>
                    <option value="pdrbShare">Porsi PDRB Daerah (%)</option>
                    <option value="investment">Indeks Investasi (Poin)</option>
                    <option value="density">Kepadatan Penduduk (Jiwa/km²)</option>
                </select>
            </div>
        </div>
        <div id="map-comparison-chart-container" class="chart-wrap dynamic-chart" style="height:auto; min-height:180px; padding:6px 0;">
            <!-- Rendered by JS -->
        </div>
    </article>

    <!-- TABEL DATASET SPASIAL KECAMATAN (CRUD & SOFT DELETE) -->
    <article class="card large" style="margin-top:24px;">
        <div class="card-head">
            <div>
                <span class="eyebrow">DATASET SPASIAL DAERAH</span>
                <h3>Daftar Kecamatan Spasial (<?= count($districts) ?>)</h3>
                <p>Kelola indikator ekonomi, porsi PDRB, kemiskinan, dan kepadatan per kecamatan.</p>
            </div>
            <button class="button primary" id="btn-open-add-district-2">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Tambah Kecamatan</span>
            </button>
        </div>

        <div class="data-table">
            <div class="data-tr data-th" style="grid-template-columns: 1.5fr 1fr 1fr 1fr 1fr 1.2fr 110px;">
                <span>Kecamatan</span>
                <span>Porsi PDRB</span>
                <span>Pertumbuhan</span>
                <span>Kemiskinan</span>
                <span>Kepadatan</span>
                <span>Kategori</span>
                <span>Aksi</span>
            </div>
            <?php foreach ($districts as $d): ?>
                <div class="data-tr" style="grid-template-columns: 1.5fr 1fr 1fr 1fr 1fr 1.2fr 110px;">
                    <span><strong><?= htmlspecialchars($d['name'] ?? $d['nama_kecamatan'] ?? '') ?></strong></span>
                    <span><?= number_format($d['pdrbShare'] ?? $d['porsi_pdrb'] ?? 0, 2, ',', '.') ?>%</span>
                    <span><?= number_format($d['growth'] ?? $d['pertumbuhan'] ?? 0, 2, ',', '.') ?>%</span>
                    <span><?= number_format($d['poverty'] ?? $d['kemiskinan'] ?? 0, 2, ',', '.') ?>%</span>
                    <span><?= number_format($d['density'] ?? $d['kepadatan'] ?? 0, 0, ',', '.') ?> jiwa/km²</span>
                    <span><span class="badge info"><?= htmlspecialchars($d['category'] ?? $d['kategori'] ?? 'Kembang') ?></span></span>
                    <span class="row-actions">
                        <button type="button" class="btn-edit-district" data-id="<?= $d['id'] ?>" title="Edit Kecamatan">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            <span>Edit</span>
                        </button>
                        <button type="button" class="btn-delete-district danger" data-id="<?= $d['id'] ?>" data-name="<?= htmlspecialchars($d['name'] ?? $d['nama_kecamatan'] ?? '') ?>" title="Hapus (Soft Delete)">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>

    <!-- MODAL TAMBAH KECAMATAN -->
    <div class="modal-backdrop" id="add-district-modal" style="display:none;">
        <div class="modal admin-data-modal" style="max-width:540px;">
            <div class="modal-head">
                <div>
                    <span class="badge info">TAMBAH KECAMATAN</span>
                    <h2>Tambah Kecamatan Baru</h2>
                    <p>Simpan dataset spasial wilayah baru ke tabel database <code>peta_ekonomi</code>.</p>
                </div>
                <button class="icon-button" id="modal-btn-close-add-district">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="form-add-district" method="POST" action="<?= site_url('peta_ekonomi/create') ?>">
                <div class="manual-form" style="grid-template-columns: 1fr 1fr;">
                    <label style="grid-column: 1 / 3;">
                        Nama Kecamatan
                        <input type="text" name="nama_kecamatan" id="add-district-name" placeholder="Contoh: Srono" required>
                    </label>
                    <label>
                        Porsi PDRB (%)
                        <input type="number" step="0.01" name="porsi_pdrb" id="add-district-share" placeholder="Contoh: 5.80" required>
                    </label>
                    <label>
                        Pertumbuhan (%)
                        <input type="number" step="0.01" name="pertumbuhan" id="add-district-growth" placeholder="Contoh: 5.15" required>
                    </label>
                    <label>
                        Tingkat Kemiskinan (%)
                        <input type="number" step="0.01" name="kemiskinan" id="add-district-poverty" placeholder="Contoh: 6.90" required>
                    </label>
                    <label>
                        Kepadatan (jiwa/km²)
                        <input type="number" name="kepadatan" id="add-district-density" placeholder="Contoh: 840" required>
                    </label>
                    <label>
                        Kategori Tipologi
                        <select name="kategori" id="add-district-category">
                            <option value="Maju">Maju</option>
                            <option value="Potensial">Potensial</option>
                            <option value="Berkembang" selected>Berkembang</option>
                            <option value="Tertinggal">Tertinggal</option>
                        </select>
                    </label>
                    <label>
                        Garis Lintang (Latitude)
                        <input type="number" step="any" name="garis_lintang" id="add-district-lat" placeholder="Contoh: -8.3850" value="-8.3850">
                    </label>
                    <label>
                        Garis Bujur (Longitude)
                        <input type="number" step="any" name="garis_bujur" id="add-district-lng" placeholder="Contoh: 114.2850" value="114.2850">
                    </label>
                </div>

                <div id="add-district-error" class="method-note" style="display:none; color:var(--red); margin-bottom:12px;"></div>

                <div class="modal-actions">
                    <button type="button" class="button ghost" id="modal-btn-cancel-add-district">Batal</button>
                    <button type="submit" class="button primary" id="modal-btn-submit-add-district">Simpan Kecamatan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT KECAMATAN -->
    <div class="modal-backdrop" id="edit-district-modal" style="display:none;">
        <div class="modal admin-data-modal" style="max-width:540px;">
            <div class="modal-head">
                <div>
                    <span class="badge warning">PERBARUI KECAMATAN</span>
                    <h2>Edit Data Spasial Kecamatan</h2>
                    <p>Perbarui nilai indikator makro dan koordinat spasial wilayah kecamatan.</p>
                </div>
                <button class="icon-button" id="modal-btn-close-edit-district">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="form-edit-district" method="POST">
                <input type="hidden" name="id" id="edit-district-id">
                <div class="manual-form" style="grid-template-columns: 1fr 1fr;">
                    <label style="grid-column: 1 / 3;">
                        Nama Kecamatan
                        <input type="text" name="nama_kecamatan" id="edit-district-name" required>
                    </label>
                    <label>
                        Porsi PDRB (%)
                        <input type="number" step="0.01" name="porsi_pdrb" id="edit-district-share" required>
                    </label>
                    <label>
                        Pertumbuhan (%)
                        <input type="number" step="0.01" name="pertumbuhan" id="edit-district-growth" required>
                    </label>
                    <label>
                        Tingkat Kemiskinan (%)
                        <input type="number" step="0.01" name="kemiskinan" id="edit-district-poverty" required>
                    </label>
                    <label>
                        Kepadatan (jiwa/km²)
                        <input type="number" name="kepadatan" id="edit-district-density" required>
                    </label>
                    <label>
                        Kategori Tipologi
                        <select name="kategori" id="edit-district-category">
                            <option value="Maju">Maju</option>
                            <option value="Potensial">Potensial</option>
                            <option value="Berkembang">Berkembang</option>
                            <option value="Tertinggal">Tertinggal</option>
                        </select>
                    </label>
                    <label>
                        Garis Lintang (Latitude)
                        <input type="number" step="any" name="garis_lintang" id="edit-district-lat">
                    </label>
                    <label>
                        Garis Bujur (Longitude)
                        <input type="number" step="any" name="garis_bujur" id="edit-district-lng">
                    </label>
                </div>

                <div id="edit-district-error" class="method-note" style="display:none; color:var(--red); margin-bottom:12px;"></div>

                <div class="modal-actions">
                    <button type="button" class="button ghost" id="modal-btn-cancel-edit-district">Batal</button>
                    <button type="submit" class="button primary" id="modal-btn-submit-edit-district">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

