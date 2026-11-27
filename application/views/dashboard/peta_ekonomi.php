<div class="map-page economic-map-page">
    <script>
        window.__JATIM_REGIONS__ = <?= json_encode($jatim_regions ?? array(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        window.__DISTRICTS__ = <?= json_encode($districts ?? array(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        window.__JATIM_GEOJSON_URL__ = '<?= base_url("assets/data/jatim_kab_kota.geojson") ?>';
        window.__JATIM_KECAMATAN_URL__ = '<?= base_url("assets/data/jatim_kecamatan.json") ?>';
        window.__JATIM_KECAMATAN_GEOJSON_URL__ = '<?= base_url("assets/data/jatim_kecamatan.geojson") ?>';
        window.__JATIM_REGENCIES_SUMMARY_URL__ = '<?= base_url("assets/data/jatim_regencies_summary.json") ?>';
    </script>

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

    <div class="map-heading">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions">
            <button class="button secondary" id="btn-export-map">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12M7 10l5 5 5-5M4 21h16"/></svg>
                <span>Ekspor Data Peta</span>
            </button>
        </div>
    </div>

    <!-- MAP MODE TABS -->
    <div class="map-mode-tabs" id="map-mode-tabs">
        <button class="active" data-mode="Thematic Layer">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
            <span>Thematic Layer</span>
        </button>
        <button data-mode="Multi-Layer Map">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
            <span>Multi-Layer Map</span>
        </button>
        <button data-mode="Regional Drill-Down">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="22" y1="12" x2="18" y2="12"/><line x1="6" y1="12" x2="2" y2="12"/><line x1="12" y1="6" x2="12" y2="2"/><line x1="12" y1="22" x2="12" y2="18"/></svg>
            <span>Regional Drill-Down</span>
        </button>
        <button data-mode="Hotspot Analysis">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            <span>Hotspot Analysis</span>
        </button>
        <button data-mode="Spatial Comparison">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            <span>Spatial Comparison</span>
        </button>
    </div>

    <!-- CONTEXT BAR -->
    <div class="map-context-bar">
        <div class="map-breadcrumb">
            <button id="map-bread-kab">Provinsi Jawa Timur</button>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
            <span class="active" id="map-bread-level">38 Kabupaten & Kota</span>
        </div>
        <div class="map-context-meta">
            <span>Periode data: <strong>2024 / 2025</strong></span>
            <span>Satuan: <strong id="map-context-unit">%</strong></span>
            <span>Klasifikasi: <strong>Interval Terkalibrasi (4 Kelas)</strong></span>
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
                    <option value="Aktivitas ekonomi" selected>Aktivitas ekonomi & PDRB</option>
                    <option value="Kesejahteraan">Kesejahteraan & Kemiskinan</option>
                    <option value="Investasi">Investasi & Penanaman Modal</option>
                    <option value="Pendidikan">Pendidikan & IPM</option>
                    <option value="Wilayah">Wilayah & Demografi</option>
                </select>
            </label>

            <label>
                <span>Indikator tematik</span>
                <select id="map-indicator-select">
                    <!-- Populated by JS -->
                </select>
            </label>

            <label>
                <span>Skema pewarnaan peta</span>
                <select id="map-color-palette-select">
                    <option value="auto" selected>Otomatis (Sesuai Peta Dasar)</option>
                    <option value="mapid-red">Gradasi Merah Tematik (MAPID - Gambar 1)</option>
                    <option value="yellow-contrast">Kuning Kontras Administratif (Gambar 2)</option>
                    <option value="bappeda">Standar GIS 4-Kelas (Emerald-Blue-Amber-Red)</option>
                    <option value="blue">Biru Samudra (Oceanic Blues Gradient)</option>
                    <option value="emerald">Hijau Zamrud (Viridis Emerald)</option>
                </select>
            </label>

            <label>
                <span>Level cakupan wilayah</span>
                <select id="map-level-select">
                    <option value="Jawa Timur" selected>Seluruh Jawa Timur (38 Kab/Kota)</option>
                    <option value="Kecamatan">Kecamatan di Kab. Banyuwangi</option>
                    <option value="Gabungan">Gabungan (Provinsi & Kabupaten)</option>
                </select>
            </label>

            <label>
                <span>Format angka balon GIS</span>
                <select id="map-pin-display-select">
                    <option value="value" selected>Nilai Indikator (Contoh: 5.7% / Rp 120 T)</option>
                    <option value="rank">Peringkat Daerah (#1 s/d #38)</option>
                    <option value="class">Kelas Tingkatan (Kelas 1 s/d 5)</option>
                    <option value="code">Nomor Wilayah (Contoh: 10, 01, 78)</option>
                </select>
            </label>

            <label>
                <span>Tipe Peta Dasar (100% Bebas Watermark & API Key)</span>
                <select id="map-basemap-select">
                    <option value="osm" selected>OpenStreetMap Standar (100% Bebas Watermark)</option>
                    <option value="mapid-red">MAPID Tematik Merah (Choropleth - Bebas Watermark)</option>
                    <option value="yellow-contrast">Peta Kontras Kuning (Atlas Bahari - Bebas Watermark)</option>
                    <option value="canvas-clean">Kanvas Poligon Murni (Tanpa Peta Dasar / Zero Watermark)</option>
                    <option value="osm-hot">OpenStreetMap Humaniter (Kontras Tinggi)</option>
                    <option value="opentopomap">OpenTopoMap (Topografi OpenStreetMap)</option>
                    <option value="esri-sat">Citra Satelit HD Resmi (Esri ArcGIS - Bebas Watermark)</option>
                    <option value="carto-light">Minimalis Terang (Light Gray Canvas - Bebas Watermark)</option>
                    <option value="carto-dark">Mode Gelap (Dark Gray Canvas - Bebas Watermark)</option>
                    <option value="esri-gray">Esri Gray Canvas (Netral Monokrom)</option>
                </select>
            </label>

            <div class="layer-list">
                <div class="layer-title">
                    <strong>Lapisan Peta (Layers)</strong>
                    <span class="badge info" id="layer-active-badge">4 aktif</span>
                </div>
                <label class="check-row">
                    <input type="checkbox" class="map-layer-cb" id="layer-cb-osm" value="osm" checked>
                    <span>Peta Dasar OpenStreetMap (100% Bebas API Key)</span>
                </label>
                <label class="check-row">
                    <input type="checkbox" class="map-layer-cb" id="layer-cb-boundaries" value="boundaries" checked>
                    <span>Batas Wilayah Riil GIS (Batas Antar Kota)</span>
                </label>
                <label class="check-row">
                    <input type="checkbox" class="map-layer-cb" id="layer-cb-markers" value="markers" checked>
                    <span>Titik Balon GIS (Pin Bersih Tanpa Angka)</span>
                </label>
                <label class="check-row">
                    <input type="checkbox" class="map-layer-cb" id="layer-cb-labels" value="labels" checked>
                    <span>Label Wilayah (Nama Kabupaten/Kota)</span>
                </label>
                <label class="check-row">
                    <input type="checkbox" class="map-layer-cb" id="layer-cb-hotspot" value="hotspot">
                    <span>Sorotan Wilayah Strategis</span>
                </label>
            </div>

            <div class="legend-block">
                <div class="legend-head">
                    <div class="legend-heading-left">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="legend-icon"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>
                        <strong id="legend-title">Legenda Klasifikasi</strong>
                    </div>
                    <button type="button" id="btn-reverse-legend" class="btn-reverse-legend" title="Balik urutan kelas (Tertinggi / Terendah)">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m7 15 5 5 5-5M7 9l5-5 5 5"/></svg>
                        <span>Balik urutan</span>
                    </button>
                </div>
                <div id="legend-items-container" class="legend-items-wrap">
                    <!-- Populated by JS -->
                </div>
            </div>
        </aside>

        <!-- MAP CANVAS CONTAINER WITH LEAFLET OPENSTREETMAP -->
        <main class="map-canvas" id="map-canvas-container">
            <div class="map-search">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input id="map-search-input" placeholder="Cari kode (35.10) atau nama kota...">
            </div>

            <div class="basemap-switcher-pills" id="basemap-pills" title="Pilihan Peta Dasar (100% Bebas Watermark)">
                <button type="button" class="basemap-pill-btn active" data-basemap="osm" title="OpenStreetMap Standar (100% Bebas Watermark)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" x2="9" y1="3" y2="18"/><line x1="15" x2="15" y1="6" y2="21"/></svg>
                    <span>OpenStreetMap</span>
                </button>
                <button type="button" class="basemap-pill-btn" data-basemap="mapid-red" title="MAPID Tematik Merah (Choropleth - Bebas Watermark)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                    <span>MAPID Merah</span>
                </button>
                <button type="button" class="basemap-pill-btn" data-basemap="yellow-contrast" title="Peta Kontras Kuning (Atlas Bahari - Bebas Watermark)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                    <span>Kuning Kontras</span>
                </button>
                <button type="button" class="basemap-pill-btn" data-basemap="canvas-clean" title="Kanvas Poligon Murni (Tanpa Peta Dasar / Zero Watermark)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
                    <span>Kanvas Murni</span>
                </button>
                <button type="button" class="basemap-pill-btn" data-basemap="esri-sat" title="Citra Satelit HD Resmi (Esri ArcGIS - 100% Bebas Watermark)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                    <span>Satelit HD</span>
                </button>
                <button type="button" class="basemap-pill-btn" data-basemap="opentopomap" title="OpenTopoMap (Topografi OpenStreetMap - Bebas Watermark)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    <span>Topografi</span>
                </button>
            </div>

            <div class="zoom">
                <button type="button" id="btn-zoom-in" title="Perbesar Tampilan">+</button>
                <button type="button" id="btn-zoom-out" title="Perkecil Tampilan">−</button>
                <small id="zoom-level-text">Z8</small>
            </div>

            <div id="spatial-comp-bar" class="comparison-divider" style="display:none; z-index:1000;">
                <span id="comp-div-left">Kabupaten Banyuwangi: 5,65%</span>
                <i></i>
                <span id="comp-div-right">Rerata Jawa Timur: 5,32%</span>
            </div>

            <div id="hotspot-note-box" class="hotspot-note" style="display:none; z-index:1000;">
                <span class="pulse"></span>
                <div>
                    <strong id="hotspot-note-title">Kawasan Pertumbuhan Cepat (Laju > 5.8%)</strong>
                    <small id="hotspot-note-list">Pacitan, Gresik, Bojonegoro, Sumenep, Surabaya, Kota Malang</small>
                </div>
            </div>

            <!-- FLOATING BACK TO PROVINCE BUTTON -->
            <button id="btn-back-to-province" class="map-floating-back-btn" style="display:none;" title="Kembali ke tampilan seluruh Jawa Timur">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                <span>Kembali ke Seluruh Jawa Timur (38 Kab/Kota)</span>
            </button>

            <!-- LEAFLET OPENSTREETMAP CONTAINER -->
            <div id="leaflet-osm-map"></div>

            <div id="map-overlay-message" class="map-empty-overlay" style="display:none; z-index:1005;">
                <strong id="map-overlay-title">Data Peta Sedang Dimuat...</strong>
                <span id="map-overlay-sub">Menghubungkan ke API Peta</span>
            </div>
        </main>

        <!-- LOCATION DETAIL ASIDE -->
        <aside class="location-panel">
            <div class="location-cover">
                <div>
                    <span id="loc-detail-level">PROFIL WILAYAH • KABUPATEN</span>
                    <h3 id="loc-detail-name">Kabupaten Banyuwangi</h3>
                    <p id="loc-detail-code">Kode Wilayah: 35.10 • BIG / Kemendagri</p>
                </div>
                <button class="icon-button" id="btn-reset-location" title="Kembali ke fokus Jawa Timur">
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
        <p><strong>Sumber Data & Geospatial Intelligence:</strong> Peta ekonomi mengintegrasikan OpenStreetMap Tile API dengan batasan geografis latitude & longitude resmi BIG / Kemendagri untuk 38 Kabupaten/Kota di Jawa Timur serta titik koordinat kecamatan di Kabupaten Banyuwangi. Data indikator diperbarui secara riil dari basis data sistem.</p>
    </div>

    <!-- GRAFIK KOMPARASI SPASIAL -->
    <article class="card large" style="margin-top:24px;">
        <div class="card-head">
            <div>
                <span class="eyebrow">VISUALISASI INDIKATOR SPASIAL</span>
                <h3 id="map-chart-title">Grafik Komparasi Indikator Daerah</h3>
                <p id="map-chart-desc">Perbandingan peringkat capaian wilayah se-Jawa Timur berdasarkan indikator terpilih.</p>
            </div>
            <div style="display:flex; gap:10px; align-items:center;">
                <select id="map-chart-metric-select" style="height:32px; border:1px solid var(--line); border-radius:6px; font-size:11px; padding:0 8px; background:var(--surface); cursor:pointer;">
                    <option value="growth">Pertumbuhan Ekonomi (%)</option>
                    <option value="change">Perubahan Laju YoY (% poin)</option>
                    <option value="pdrb">Nilai PDRB ADHB (Miliar Rp)</option>
                    <option value="luas">Luas Wilayah (km²)</option>
                </select>
            </div>
        </div>
        <div id="map-comparison-chart-container" class="chart-wrap dynamic-chart" style="height:auto; min-height:200px; padding:6px 0;">
            <!-- Rendered by JS -->
        </div>
    </article>


</div>

