<div class="page">
    <!-- HERO SECTION -->
    <section class="hero">
        <div>
            <span class="badge info">EXECUTIVE ECONOMIC OVERVIEW</span>
            <h1>Dashboard Perekonomian<br>Kabupaten Banyuwangi</h1>
            <p>Pantau perkembangan, analisis, dan prospek perekonomian daerah<br>dalam satu platform intelijen ekonomi terintegrasi.</p>
        </div>
        <div class="hero-meta">
            <div>
                <span class="pulse"></span>
                <small>Periode referensi</small>
                <strong id="hero-selected-year"><?= $selected_year ?> • Tahunan</strong>
            </div>
            <span class="badge simulation">DATABASE CI 3</span>
        </div>
    </section>

    <!-- FILTER BAR -->
    <div class="filterbar">
        <div class="filter-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16l-6 7v6l-4 2v-8L4 5Z"/></svg>
            <div>
                <strong>Filter Data Ekonomi</strong>
                <small>Pilih tahun dan wilayah analisis</small>
            </div>
        </div>
        <label>
            <span>Wilayah</span>
            <select id="filter-region">
                <option value="Kabupaten Banyuwangi">Kabupaten Banyuwangi</option>
                <option value="Jawa Timur">Provinsi Jawa Timur</option>
                <option value="Nasional">Nasional</option>
            </select>
        </label>
        <label>
            <span>Tahun</span>
            <select id="filter-year">
                <?php foreach (array_reverse($years) as $y): ?>
                    <option value="<?= $y ?>" <?= $y == $selected_year ? 'selected' : '' ?>><?= $y ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            <span>Periode</span>
            <select disabled><option>Tahunan</option></select>
        </label>
        <label>
            <span>Sumber Data</span>
            <select disabled><option>BPS & DPMPTSP</option></select>
        </label>
        <button class="button primary" id="btn-apply-filters">Terapkan Filter</button>
    </div>

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
        <!-- TEMPAT SAMPAH INDIKATOR BERANDA -->
        <article class="card large" style="margin-bottom:24px; border:1px dashed #f43f5e; background:rgba(244,63,94,0.03);">
            <div class="card-head">
                <div>
                    <span class="badge danger">TEMPAT SAMPAH (SOFT DELETE)</span>
                    <h3>Daftar Indikator KPI Beranda Terhapus (<?= count($beranda_cards) ?>)</h3>
                    <p>Klik tombol Pulihkan untuk mengembalikan indikator KPI ke dashboard utama.</p>
                </div>
                <a href="<?= site_url('beranda') ?>" class="button ghost">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    <span>Kembali ke Indikator Aktif</span>
                </a>
            </div>
            <div class="data-table">
                <div class="data-tr data-th" style="grid-template-columns: 2fr 1.2fr 1fr 1fr 1.2fr 120px;">
                    <span>Indikator</span>
                    <span>Nilai</span>
                    <span>Satuan</span>
                    <span>Perubahan</span>
                    <span>Dihapus Pada</span>
                    <span>Aksi</span>
                </div>
                <?php if (empty($beranda_cards)): ?>
                    <div style="padding:24px; text-align:center; color:var(--muted); font-size:11px;">
                        Tempat sampah kosong. Tidak ada indikator beranda yang terhapus.
                    </div>
                <?php else: ?>
                    <?php foreach ($beranda_cards as $item): ?>
                        <div class="data-tr" style="grid-template-columns: 2fr 1.2fr 1fr 1fr 1.2fr 120px;">
                            <span><strong><?= htmlspecialchars($item['nama_indikator']) ?></strong></span>
                            <span><?= htmlspecialchars($item['nilai']) ?></span>
                            <span><?= htmlspecialchars($item['satuan']) ?></span>
                            <span><?= htmlspecialchars($item['perubahan']) ?></span>
                            <span><small><?= htmlspecialchars($item['deleted_at'] ?? '-') ?></small></span>
                            <span class="row-actions">
                                <button type="button" class="btn-restore-beranda" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['nama_indikator']) ?>" title="Pulihkan Indikator">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                    <span>Pulihkan</span>
                                </button>
                                <button type="button" class="btn-hard-delete-beranda danger" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['nama_indikator']) ?>" title="Hapus Permanen">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>
    <?php endif; ?>

    <!-- KPI GRID -->
    <section class="section">
        <div class="section-heading">
            <div>
                <span class="eyebrow">RINGKASAN KINERJA</span>
                <h2>Indikator Utama Ekonomi</h2>
                <p>Perkembangan indikator strategis daerah pada periode berjalan.</p>
            </div>
            <div style="display:flex; gap:8px; align-items:center;">
                <?php if ($trash_count > 0): ?>
                    <?php if ($status === 'Terhapus'): ?>
                        <a href="<?= site_url('beranda') ?>" class="button ghost">
                            <span>Kembali ke Aktif</span>
                        </a>
                    <?php else: ?>
                        <a href="<?= site_url('beranda?status=Terhapus') ?>" class="button ghost danger">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            <span>Sampah (<?= $trash_count ?>)</span>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
                <button type="button" class="button primary" id="btn-open-add-beranda">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>Tambah Indikator KPI</span>
                </button>
                <a href="<?= site_url('analisis_ekonomi') ?>" class="button ghost">
                    <span>Lihat analisis</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>
        </div>

        <div class="kpi-grid" id="kpi-container">
            <?php
            // Use beranda_cards from database if available
            $display_cards = !empty($beranda_cards) ? $beranda_cards : array();
            
            // Fallback to calculated series if DB table empty
            if (empty($display_cards)) {
                $idx = $year_idx;
                $prev = $prev_idx;
                $gNow = $series['growth'][$idx] ?? 0;
                $gPrev = $series['growth'][$prev] ?? 0;
                $gDelta = round($gNow - $gPrev, 2);
                $pcapNow = $series['perCapita'][$idx] ?? 0;
                $pcapPrev = $series['perCapita'][$prev] ?? 0;
                $pcapDelta = round($pcapNow - $pcapPrev, 2);
                $infNow = $series['inflation'][$idx] ?? 0;
                $infPrev = $series['inflation'][$prev] ?? 0;
                $infDelta = round($infNow - $infPrev, 2);
                $tptNow = $series['tpt'][$idx] ?? 0;
                $tptPrev = $series['tpt'][$prev] ?? 0;
                $tptDelta = round($tptNow - $tptPrev, 2);
                $povNow = $series['poverty'][$idx] ?? 0;
                $povPrev = $series['poverty'][$prev] ?? 0;
                $povDelta = round($povNow - $povPrev, 2);
                $ipmNow = $series['ipm'][$idx] ?? 0;
                $ipmPrev = $series['ipm'][$prev] ?? 0;
                $ipmDelta = round($ipmNow - $ipmPrev, 2);
                $invNow = $series['investmentGrowth'][$idx] ?? 0;
                $invPrev = $series['investmentGrowth'][$prev] ?? 0;
                $invDelta = round($invNow - $invPrev, 2);

                $display_cards = array(
                    array('id' => 1, 'nama_indikator' => 'Pertumbuhan Ekonomi', 'nilai' => number_format($gNow, 2, ',', '.'), 'satuan' => '%', 'perubahan' => ($gDelta >= 0 ? '+' : '') . number_format($gDelta, 2, ',', '.') . ' pp', 'arah_tren' => ($gDelta >= 0 ? 'up' : 'down'), 'status' => ($gDelta >= 0 ? 'Meningkat' : 'Perlu perhatian'), 'deret_mini' => '[18, 25, 20, 31, 28, 39, 44]', 'tahun' => $selected_year),
                    array('id' => 2, 'nama_indikator' => 'PDRB per Kapita', 'nilai' => 'Rp ' . number_format($pcapNow, 1, ',', '.'), 'satuan' => 'Juta', 'perubahan' => ($pcapDelta >= 0 ? '+' : '') . number_format($pcapDelta, 1, ',', '.') . ' juta', 'arah_tren' => ($pcapDelta >= 0 ? 'up' : 'down'), 'status' => ($pcapDelta >= 0 ? 'Meningkat' : 'Menurun'), 'deret_mini' => '[16, 20, 24, 28, 31, 37, 42]', 'tahun' => $selected_year),
                    array('id' => 3, 'nama_indikator' => 'Tingkat Inflasi', 'nilai' => number_format($infNow, 2, ',', '.'), 'satuan' => '% yoy', 'perubahan' => ($infDelta <= 0 ? '' : '+') . number_format($infDelta, 2, ',', '.') . ' pp', 'arah_tren' => ($infDelta <= 0 ? 'up' : 'down'), 'status' => ($infNow <= 4 ? 'Terkendali' : 'Perlu perhatian'), 'deret_mini' => '[28, 35, 30, 38, 26, 24, 22]', 'tahun' => $selected_year),
                    array('id' => 4, 'nama_indikator' => 'Pengangguran Terbuka', 'nilai' => number_format($tptNow, 2, ',', '.'), 'satuan' => '%', 'perubahan' => ($tptDelta <= 0 ? '' : '+') . number_format($tptDelta, 2, ',', '.') . ' pp', 'arah_tren' => ($tptDelta <= 0 ? 'up' : 'down'), 'status' => ($tptDelta <= 0 ? 'Membaik' : 'Meningkat'), 'deret_mini' => '[40, 38, 35, 37, 30, 27, 25]', 'tahun' => $selected_year),
                    array('id' => 5, 'nama_indikator' => 'Tingkat Kemiskinan', 'nilai' => number_format($povNow, 2, ',', '.'), 'satuan' => '%', 'perubahan' => ($povDelta <= 0 ? '' : '+') . number_format($povDelta, 2, ',', '.') . ' pp', 'arah_tren' => ($povDelta <= 0 ? 'up' : 'down'), 'status' => ($povDelta <= 0 ? 'Membaik' : 'Perhatian'), 'deret_mini' => '[43, 39, 37, 35, 32, 28, 26]', 'tahun' => $selected_year),
                    array('id' => 6, 'nama_indikator' => 'IPM', 'nilai' => number_format($ipmNow, 2, ',', '.'), 'satuan' => 'Poin', 'perubahan' => ($ipmDelta >= 0 ? '+' : '') . number_format($ipmDelta, 2, ',', '.') . ' poin', 'arah_tren' => ($ipmDelta >= 0 ? 'up' : 'down'), 'status' => ($ipmDelta >= 0 ? 'Meningkat' : 'Menurun'), 'deret_mini' => '[20, 23, 25, 28, 32, 36, 40]', 'tahun' => $selected_year),
                    array('id' => 7, 'nama_indikator' => 'Realisasi Investasi', 'nilai' => ($selected_year == 2024 ? 'Rp 3,82' : 'Rp ' . number_format(3.82 * pow(1.08, $idx - 9), 2, ',', '.')), 'satuan' => 'Triliun', 'perubahan' => '+12,4%', 'arah_tren' => 'up', 'status' => 'Meningkat', 'deret_mini' => '[15, 19, 28, 24, 31, 37, 43]', 'tahun' => $selected_year),
                    array('id' => 8, 'nama_indikator' => 'Pertumbuhan Investasi', 'nilai' => number_format($invNow, 2, ',', '.'), 'satuan' => '%', 'perubahan' => ($invDelta >= 0 ? '+' : '') . number_format($invDelta, 2, ',', '.') . ' pp', 'arah_tren' => ($invDelta >= 0 ? 'up' : 'down'), 'status' => ($invDelta >= 0 ? 'Meningkat' : 'Perlu perhatian'), 'deret_mini' => '[39, 36, 42, 35, 31, 28, 25]', 'tahun' => $selected_year)
                );
            }

            foreach ($display_cards as $item):
                // Decode or use series for sparkline
                $sVals = array(20, 25, 24, 28, 32, 36, 40);
                if (!empty($item['deret_mini'])) {
                    $decoded = is_array($item['deret_mini']) ? $item['deret_mini'] : json_decode($item['deret_mini'], true);
                    if (is_array($decoded) && count($decoded) > 0) {
                        $sVals = $decoded;
                    }
                }
                $minS = min($sVals);
                $maxS = max($sVals);
                $rangeS = $maxS - $minS ?: 1;
                $points = array();
                $c = count($sVals);
                foreach ($sVals as $iS => $valS) {
                    $xS = $iS * (84 / max(1, $c - 1));
                    $yS = 42 - (($valS - $minS) / $rangeS) * 32;
                    $points[] = round($xS, 1) . ',' . round($yS, 1);
                }
                $ptsStr = implode(' ', $points);
                $trend = $item['arah_tren'] ?? 'up';
                $tone = (in_array(strtolower($item['status']), array('meningkat','membaik','stabil','terkendali')) || $trend === 'up') ? 'success' : 'warning';
                
                $nameLower = strtolower($item['nama_indikator']);
                $indKey = 'growth';
                if (strpos($nameLower, 'inflasi') !== false) $indKey = 'inflation';
                elseif (strpos($nameLower, 'kapita') !== false) $indKey = 'perCapita';
                elseif (strpos($nameLower, 'pengangguran') !== false || strpos($nameLower, 'tpt') !== false) $indKey = 'tpt';
                elseif (strpos($nameLower, 'kemiskinan') !== false) $indKey = 'poverty';
                elseif (strpos($nameLower, 'ipm') !== false) $indKey = 'ipm';
                elseif (strpos($nameLower, 'investasi') !== false) $indKey = 'investmentGrowth';
            ?>
                <div class="kpi-card" data-indicator-key="<?= $indKey ?>" style="position:relative; cursor:pointer;" title="Klik untuk menampilkan grafik tren indikator ini">
                    <div class="kpi-top">
                        <span><?= htmlspecialchars($item['nama_indikator']) ?></span>
                        <span class="badge <?= $tone ?>"><?= htmlspecialchars($item['status']) ?></span>
                    </div>
                    <div class="kpi-value">
                        <strong><?= htmlspecialchars($item['nilai']) ?></strong>
                        <span><?= htmlspecialchars($item['satuan']) ?></span>
                    </div>
                    <div class="kpi-bottom">
                        <div>
                            <span class="<?= $trend === 'up' ? 'positive' : 'negative' ?>">
                                <?= $trend === 'up' ? '↗' : '↘' ?> <?= htmlspecialchars($item['perubahan']) ?>
                            </span>
                            <small>dari periode lalu</small>
                        </div>
                        <svg class="spark" viewBox="0 0 84 48" preserveAspectRatio="none">
                            <polyline points="<?= $ptsStr ?>" class="<?= $trend === 'down' ? 'spark-negative' : '' ?>"></polyline>
                        </svg>
                    </div>
                    <div class="kpi-period" style="display:flex; justify-content:space-between; align-items:center;">
                        <span><?= $item['tahun'] ?? $selected_year ?> • Banyuwangi</span>
                        <span style="display:flex; gap:4px;">
                            <button type="button" class="btn-edit-beranda" data-id="<?= $item['id'] ?>" title="Edit Indikator" style="background:transparent; border:none; cursor:pointer; color:var(--text-muted); padding:2px;" onclick="event.stopPropagation();">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                            <button type="button" class="btn-delete-beranda" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['nama_indikator']) ?>" title="Hapus (Soft Delete)" style="background:transparent; border:none; cursor:pointer; color:var(--red); padding:2px;" onclick="event.stopPropagation();">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- TWO COLUMNS SECTION -->
    <section class="two-col">
        <!-- LINE CHART -->
        <article class="card chart-card large">
            <div class="card-head">
                <div>
                    <span class="eyebrow" id="trend-eyebrow">HISTORIS HINGGA <?= $selected_year ?></span>
                    <h3 id="overview-chart-title">Tren Pertumbuhan Ekonomi</h3>
                    <p id="overview-chart-desc">Persentase pertumbuhan PDRB ADHK • perbandingan wilayah</p>
                </div>
                <div style="display:flex; flex-direction:column; align-items:flex-end; gap:6px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <select id="overview-indicator-select" style="height:32px; border:1px solid var(--line); border-radius:6px; font-size:11px; padding:0 8px; background:var(--surface); cursor:pointer;">
                            <option value="growth">Pertumbuhan Ekonomi (%)</option>
                            <option value="perCapita">PDRB Per Kapita (Juta Rp)</option>
                            <option value="inflation">Inflasi Tahunan (%)</option>
                            <option value="tpt">Tingkat Pengangguran (TPT %)</option>
                            <option value="poverty">Tingkat Kemiskinan (%)</option>
                            <option value="investmentGrowth">Pertumbuhan Investasi (%)</option>
                            <option value="ipm">Indeks Pembangunan Manusia (IPM)</option>
                        </select>
                    </div>
                    <div class="legend" id="trend-legend">
                        <i style="background:var(--blue)"></i> <span id="legend-label-0">Banyuwangi</span>
                        <i style="background:var(--teal)"></i> <span id="legend-label-1">Jawa Timur</span>
                        <i style="background:#d29a28"></i> <span id="legend-label-2">Nasional</span>
                    </div>
                </div>
            </div>
            
            <div id="overview-line-chart-container" class="chart-wrap dynamic-chart">
                <!-- Rendered dynamically by JS -->
            </div>
            <div class="source">Sumber: PDRB ADHK Kabupaten Banyuwangi & BPS Provinsi Jawa Timur</div>
        </article>

        <!-- SECTORAL BAR CHART -->
        <article class="card">
            <div class="card-head">
                <div>
                    <span class="eyebrow">LAPANGAN USAHA</span>
                    <h3>Pertumbuhan Sektoral</h3>
                    <p>Pertumbuhan tahunan per sektor (Tahun <?= $selected_year ?>)</p>
                </div>
                <span class="more" aria-hidden="true">•••</span>
            </div>

            <div class="bar-chart" id="overview-sector-bars">
                <?php
                // Sort sectors by selected year growth
                $sectorSort = $sectors;
                usort($sectorSort, function($a, $b) use ($year_idx) {
                    $gA = $a['growthSeries'][$year_idx] ?? $a['growthLocal'];
                    $gB = $b['growthSeries'][$year_idx] ?? $b['growthLocal'];
                    return $gB <=> $gA;
                });
                $top5 = array_slice($sectorSort, 0, 5);
                $topSector = $top5[0];

                $allGrowth = array_map(function($s) use ($year_idx) { return $s['growthSeries'][$year_idx] ?? $s['growthLocal']; }, $top5);
                $minG = min(0, min($allGrowth));
                $maxG = max(0, max($allGrowth));
                $rangeG = $maxG - $minG ?: 1;
                $zeroG = (0 - $minG) / $rangeG * 100;

                foreach ($top5 as $s):
                    $curG = $s['growthSeries'][$year_idx] ?? $s['growthLocal'];
                    $w = abs($curG) / $rangeG * 100;
                    $l = $curG >= 0 ? $zeroG : $zeroG - $w;
                ?>
                    <div class="bar-row">
                        <span title="<?= htmlspecialchars($s['name']) ?>"><?= htmlspecialchars($s['short']) ?></span>
                        <div class="bar-track">
                            <b class="bar-zero" style="left: <?= $zeroG ?>%"></b>
                            <i class="<?= $curG < 0 ? 'negative' : '' ?>" style="left: <?= $l ?>%; width: <?= $w ?>%"></i>
                        </div>
                        <strong class="<?= $curG < 0 ? 'negative' : '' ?>"><?= number_format($curG, 1, ',', '.') ?>%</strong>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="insight-strip">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 18h6M10 22h4M8.5 15a7 7 0 1 1 7 0c-.9.7-1.3 1.5-1.5 2h-4c-.2-.5-.6-1.3-1.5-2Z"/></svg>
                <span><strong><?= $topSector['short'] ?></strong> mencatat laju pertumbuhan tertinggi pada tahun <?= $selected_year ?>: <?= number_format($topSector['growthSeries'][$year_idx] ?? $topSector['growthLocal'], 1, ',', '.') ?>%.</span>
            </div>
            <div class="source">Sumber: PDRB Lapangan Usaha Kabupaten Banyuwangi</div>
        </article>
    </section>

    <!-- THREE COLUMNS SECTION -->
    <section class="three-col">
        <!-- HIGHLIGHTS -->
        <article class="card highlights">
            <div class="card-head">
                <div>
                    <span class="eyebrow">ECONOMIC INTELLIGENCE</span>
                    <h3>Sorotan Perekonomian</h3>
                </div>
                <a href="<?= site_url('economic_insight') ?>" class="text-button">
                    <span>Semua insight</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>

            <a href="<?= site_url('economic_insight') ?>" class="highlight-row">
                <span class="signal <?= $gNow >= 0 ? 'success' : 'warning' ?>"></span>
                <div>
                    <strong><?= $gNow >= 0 ? 'Pertumbuhan ekonomi positif' : 'Pertumbuhan ekonomi terkontraksi' ?></strong>
                    <p>Pertumbuhan ekonomi <?= number_format($gNow, 2, ',', '.') ?>% pada <?= $selected_year ?>; sektor <?= $topSector['short'] ?> memimpin laju pertumbuhan sektoral.</p>
                </div>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
            </a>

            <a href="<?= site_url('economic_insight') ?>" class="highlight-row">
                <span class="signal info"></span>
                <div>
                    <strong>Dinamika investasi daerah</strong>
                    <p>Realisasi investasi tumbuh <?= number_format($invNow, 2, ',', '.') ?>% pada tahun <?= $selected_year ?> mendukung penyerapan tenaga kerja lokal.</p>
                </div>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
            </a>

            <a href="<?= site_url('economic_insight') ?>" class="highlight-row">
                <span class="signal <?= $infNow <= 4 ? 'success' : 'warning' ?>"></span>
                <div>
                    <strong>Perkembangan harga & inflasi</strong>
                    <p>Inflasi tercatat <?= number_format($infNow, 2, ',', '.') ?>% pada tahun <?= $selected_year ?>. Stabilitas harga terjaga melalui koordinasi TPID.</p>
                </div>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
            </a>
        </article>

        <!-- EWS RISK DONUT -->
        <article class="card risk-card">
            <?php
            $normalCount = 0;
            $warningCount = 0;
            $criticalCount = 0;
            $activeAlert = null;
            $maxDev = -1;

            foreach ($ews as $item) {
                if ($item['status'] === 'Normal') $normalCount++;
                elseif ($item['status'] === 'Waspada') $warningCount++;
                elseif ($item['status'] === 'Kritis') $criticalCount++;

                if ($item['status'] !== 'Normal' && $item['distance'] > $maxDev) {
                    $maxDev = $item['distance'];
                    $activeAlert = $item;
                }
            }
            $totalEws = count($ews) ?: 1;
            $normPct = ($normalCount / $totalEws) * 100;
            $warnPct = (($normalCount + $warningCount) / $totalEws) * 100;
            $riskBg = "conic-gradient(var(--green) 0 {$normPct}%, var(--yellow) {$normPct}% {$warnPct}%, var(--red) {$warnPct}% 100%)";
            ?>
            <div class="card-head">
                <div>
                    <span class="eyebrow">EARLY WARNING SYSTEM</span>
                    <h3>Status Risiko Ekonomi</h3>
                </div>
                <span class="badge <?= $criticalCount ? 'danger' : ($warningCount ? 'warning' : 'success') ?>">
                    <?= ($warningCount + $criticalCount) ?> ALERT AKTIF
                </span>
            </div>

            <div class="risk-donut" style="background: <?= $riskBg ?>">
                <div>
                    <strong><?= count($ews) ?></strong>
                    <span>indikator risiko</span>
                </div>
            </div>

            <div class="risk-legend">
                <span><i class="green"></i><?= $normalCount ?> Normal</span>
                <span><i class="yellow"></i><?= $warningCount ?> Waspada</span>
                <span><i class="red"></i><?= $criticalCount ?> Kritis</span>
            </div>

            <?php if ($activeAlert): ?>
                <a href="<?= site_url('early_warning') ?>" class="alert-preview">
                    <span class="alert-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="M12 8v5M12 17h.01"/></svg>
                    </span>
                    <div>
                        <strong><?= htmlspecialchars($activeAlert['name']) ?></strong>
                        <small><?= number_format($activeAlert['distance'], 1, ',', '.') ?>% dari ambang batas</small>
                    </div>
                    <span class="badge <?= $activeAlert['status'] === 'Kritis' ? 'danger' : 'warning' ?>">
                        <?= $activeAlert['status'] ?>
                    </span>
                </a>
            <?php endif; ?>
        </article>

        <!-- OUTLOOK SUMMARY -->
        <article class="card outlook">
            <div class="card-head">
                <div>
                    <span class="eyebrow">PROYEKSI 2025</span>
                    <h3>Economic Outlook</h3>
                </div>
                <a href="<?= site_url('prospek_ekonomi') ?>" class="text-button">
                    <span>Detail</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>

            <div class="forecast-number">
                <span>Tren linear sederhana</span>
                <strong><?= number_format($forecast['forecast'][0] ?? 5.25, 2, ',', '.') ?><small>%</small></strong>
                <span class="badge info">Model CI 3</span>
            </div>

            <div class="forecast-mini">
                <svg viewBox="0 0 280 85" preserveAspectRatio="none">
                    <path d="M0 65 C45 52 72 55 105 44 S172 42 205 25 S250 23 280 11"></path>
                    <path class="dashed" d="M175 40 C205 29 241 22 280 11"></path>
                </svg>
            </div>

            <div class="forecast-foot">
                <span>
                    <small>Batas bawah</small>
                    <strong><?= number_format($forecast['lower'][0] ?? 4.80, 2, ',', '.') ?>%</strong>
                </span>
                <span>
                    <small>Estimasi 2025</small>
                    <strong><?= number_format($forecast['forecast'][0] ?? 5.25, 2, ',', '.') ?>%</strong>
                </span>
                <span>
                    <small>Batas atas</small>
                    <strong><?= number_format($forecast['upper'][0] ?? 5.70, 2, ',', '.') ?>%</strong>
                </span>
            </div>
        </article>
    </section>

    <!-- MODAL TAMBAH INDIKATOR BERANDA -->
    <div class="modal-backdrop" id="add-beranda-modal" style="display:none;">
        <div class="modal admin-data-modal" style="max-width:520px;">
            <div class="modal-head">
                <div>
                    <span class="badge info">TAMBAH INDIKATOR KPI</span>
                    <h2>Tambah Indikator Baru</h2>
                    <p>Simpan metrik kinerja ekonomi baru ke tabel database <code>beranda</code>.</p>
                </div>
                <button class="icon-button" id="modal-btn-close-add-beranda">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="form-add-beranda" method="POST" action="<?= site_url('beranda/create') ?>">
                <div class="manual-form" style="grid-template-columns: 1fr 1fr;">
                    <label style="grid-column: 1 / 3;">
                        Nama Indikator KPI
                        <input type="text" name="nama_indikator" id="add-beranda-name" placeholder="Contoh: Realisasi Ekspor Non-Migas" required>
                    </label>
                    <label>
                        Nilai Capaian
                        <input type="text" name="nilai" id="add-beranda-value" placeholder="Contoh: 1.450,20" required>
                    </label>
                    <label>
                        Satuan Ukuran
                        <input type="text" name="satuan" id="add-beranda-unit" placeholder="Contoh: Miliar Rp / % / Poin" required>
                    </label>
                    <label>
                        Perubahan (Delta)
                        <input type="text" name="perubahan" id="add-beranda-change" placeholder="Contoh: +5,4%" required>
                    </label>
                    <label>
                        Arah Tren
                        <select name="arah_tren" id="add-beranda-trend">
                            <option value="up">Naik (↗ Positive)</option>
                            <option value="down">Turun (↘ Negative)</option>
                        </select>
                    </label>
                    <label>
                        Status Capaian
                        <input type="text" name="status" id="add-beranda-status" placeholder="Contoh: Meningkat / Stabil / Perlu perhatian" required>
                    </label>
                    <label>
                        Tahun Referensi
                        <input type="number" name="tahun" id="add-beranda-year" value="<?= $selected_year ?>" required>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Deret Mini Sparkline (Opsional, 7 nilai)
                        <input type="text" name="deret_mini" id="add-beranda-series" placeholder="Contoh: [18, 25, 20, 31, 28, 39, 44]">
                    </label>
                </div>

                <div id="add-beranda-error" class="method-note" style="display:none; color:var(--red); margin-bottom:12px;"></div>

                <div class="modal-actions">
                    <button type="button" class="button ghost" id="modal-btn-cancel-add-beranda">Batal</button>
                    <button type="submit" class="button primary" id="modal-btn-submit-add-beranda">Simpan Indikator</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT INDIKATOR BERANDA -->
    <div class="modal-backdrop" id="edit-beranda-modal" style="display:none;">
        <div class="modal admin-data-modal" style="max-width:520px;">
            <div class="modal-head">
                <div>
                    <span class="badge warning">PERBARUI INDIKATOR KPI</span>
                    <h2>Edit Indikator Beranda</h2>
                    <p>Perbarui nilai, satuan, tren, atau status indikator yang tersimpan.</p>
                </div>
                <button class="icon-button" id="modal-btn-close-edit-beranda">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="form-edit-beranda" method="POST">
                <input type="hidden" name="id" id="edit-beranda-id">
                <div class="manual-form" style="grid-template-columns: 1fr 1fr;">
                    <label style="grid-column: 1 / 3;">
                        Nama Indikator KPI
                        <input type="text" name="nama_indikator" id="edit-beranda-name" required>
                    </label>
                    <label>
                        Nilai Capaian
                        <input type="text" name="nilai" id="edit-beranda-value" required>
                    </label>
                    <label>
                        Satuan Ukuran
                        <input type="text" name="satuan" id="edit-beranda-unit" required>
                    </label>
                    <label>
                        Perubahan (Delta)
                        <input type="text" name="perubahan" id="edit-beranda-change" required>
                    </label>
                    <label>
                        Arah Tren
                        <select name="arah_tren" id="edit-beranda-trend">
                            <option value="up">Naik (↗ Positive)</option>
                            <option value="down">Turun (↘ Negative)</option>
                        </select>
                    </label>
                    <label>
                        Status Capaian
                        <input type="text" name="status" id="edit-beranda-status" required>
                    </label>
                    <label>
                        Tahun Referensi
                        <input type="number" name="tahun" id="edit-beranda-year" required>
                    </label>
                </div>

                <div id="edit-beranda-error" class="method-note" style="display:none; color:var(--red); margin-bottom:12px;"></div>

                <div class="modal-actions">
                    <button type="button" class="button ghost" id="modal-btn-cancel-edit-beranda">Batal</button>
                    <button type="submit" class="button primary" id="modal-btn-submit-edit-beranda">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

