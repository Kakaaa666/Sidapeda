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
                <strong id="hero-selected-year"><?= !empty($selected_year) ? "Tahun {$selected_year} • Tahunan" : "Semua Periode • Tahunan" ?></strong>
            </div>
        </div>
    </section>

    <!-- FILTER TAHUN DASHBOARD (DROPDOWN) -->
    <div class="year-filter-bar" style="margin-top: 15px; margin-bottom: 22px;">
        <div class="year-filter-info">
            <div class="year-filter-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div class="year-filter-text">
                <strong>Periode Tahun Dashboard</strong>
                <small>Pilih tahun data untuk menampilkan seluruh capaian dan indikator ekonomi daerah.</small>
            </div>
        </div>

        <div class="year-filter-control">
            <label for="filter-year">Pilih Tahun:</label>
            <select id="filter-year" class="year-filter-select" onchange="window.location.href=this.value">
                <option value="<?= site_url('beranda?year=all' . ($status ? '&status='.$status : '') . (!empty($selected_cluster) && $selected_cluster !== 'all' ? '&cluster='.urlencode($selected_cluster) : '')) ?>" <?= empty($selected_year) ? 'selected' : '' ?>>Semua Tahun (Terkini)</option>
                <?php if (!empty($available_years)): ?>
                    <?php foreach ($available_years as $ay): ?>
                        <option value="<?= site_url('beranda?year=' . $ay['tahun'] . ($status ? '&status='.$status : '') . (!empty($selected_cluster) && $selected_cluster !== 'all' ? '&cluster='.urlencode($selected_cluster) : '')) ?>" <?= (!empty($selected_year) && $selected_year == $ay['tahun']) ? 'selected' : '' ?>>
                            Tahun <?= $ay['tahun'] ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
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

    <!-- KPI GRID -->
    <?php
    $display_cards = !empty($beranda_cards) ? $beranda_cards : array();
    
    // Ambil capaian riil langsung dari data indikator yang telah diinput
    $macroByCode = array();
    foreach ($display_cards as $c) {
        $rawNum = (float)str_replace(',', '.', str_replace('.', '', $c['nilai']));
        $macroByCode[$c['kode_indikator']] = $rawNum;
    }

    $idx = $year_idx;
    $prev = $prev_idx;
    $display_year = !empty($selected_year) ? $selected_year : $active_chart_year;
    $gNow = $macroByCode['PDRB_01'] ?? ($series['growth'][$idx] ?? 5.18);
    $pcapNow = $macroByCode['PDRB_03'] ?? ($series['perCapita'][$idx] ?? 62.4);
    $infNow = $macroByCode['INF_01'] ?? ($series['inflation'][$idx] ?? 2.41);
    $tptNow = $macroByCode['TPT_01'] ?? ($series['tpt'][$idx] ?? 4.03);
    $povNow = $macroByCode['POV_01'] ?? ($series['poverty'][$idx] ?? 7.34);
    $ipmNow = $macroByCode['IPM_01'] ?? ($series['ipm'][$idx] ?? 73.79);
    $invNow = $macroByCode['INV_02'] ?? ($series['investmentGrowth'][$idx] ?? 12.42);

    $catPrefixCounts = [];
    foreach ($display_cards as $c) {
        $p = strtoupper(explode('-', $c['kode_indikator'])[0]);
        $catPrefixCounts[$p] = ($catPrefixCounts[$p] ?? 0) + 1;
    }
    $categoryMeta = [
        'EKO' => 'PDRB & Ekonomi',
        'NAK' => 'Kependudukan',
        'TK'  => 'Ketenagakerjaan',
        'INV' => 'Investasi & Usaha',
        'HRG' => 'Harga & Inflasi',
        'KES' => 'Kesejahteraan Sosial',
        'PAR' => 'Pariwisata & Ekraf',
        'PRO' => 'Produksi Sektoral',
        'FIS' => 'Fiskal & Keuangan',
        'GEO' => 'Geografi & Wilayah',
        'EWS' => 'Early Warning System',
        'ANA' => 'Analisis Makro'
    ];
    $activeCat = !empty($selected_category) ? strtoupper(trim($selected_category)) : '';
    ?>
    <section class="section">
        <div class="section-heading">
            <div>
                <span class="eyebrow">RINGKASAN KINERJA • <?= !empty($selected_year) ? "TAHUN {$selected_year}" : "SEMUA PERIODE" ?></span>
                <h2>Indikator Utama Ekonomi <?= !empty($selected_year) ? "(Tahun {$selected_year})" : "(Semua Periode)" ?></h2>
                <p>Menampilkan <span id="kpi-count-display"><?= count($display_cards) ?></span> indikator pembangunan daerah yang bersumber langsung dari data yang telah diinput ke dalam sistem.</p>
            </div>
            <div>
                <a href="<?= site_url('analisis_ekonomi') ?>" class="button ghost">
                    <span>Lihat analisis</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>
        </div>

        <!-- SLIDE FILTER KATEGORI (EKO, NAK, TK, INV, HRG, KES, PAR, PRO, FIS, GEO, EWS, ANA) -->
        <div class="category-slide-container" id="category-slide-container" style="margin-top:14px; margin-bottom:18px;">
            <div class="category-slide-label">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 6h16M4 12h16M4 18h7"/></svg>
                <span>Kategori:</span>
            </div>
            <button type="button" class="category-slide-nav-btn" id="btn-slide-cat-prev" title="Geser Kiri (Sebelumnya)" aria-label="Geser Kiri">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <div class="category-slide-track" id="category-slide-track">
                <button type="button" class="category-slide-tab <?= empty($activeCat) ? 'active' : '' ?>" data-prefix="" onclick="applyCategoryFilter('', this)">
                    <span>Semua</span>
                    <span class="cat-count-pill"><?= count($display_cards) ?></span>
                </button>
                <?php foreach ($categoryMeta as $pref => $label): ?>
                    <?php if (isset($catPrefixCounts[$pref]) || empty($display_cards)): ?>
                        <?php $cnt = $catPrefixCounts[$pref] ?? 0; ?>
                        <button type="button" class="category-slide-tab <?= ($activeCat === $pref) ? 'active' : '' ?>" data-prefix="<?= $pref ?>" onclick="applyCategoryFilter('<?= $pref ?>', this)">
                            <span class="cat-code-badge"><?= $pref ?></span>
                            <span><?= $label ?></span>
                            <span class="cat-count-pill"><?= $cnt ?></span>
                        </button>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <button type="button" class="category-slide-nav-btn" id="btn-slide-cat-next" title="Geser Kanan (Selanjutnya)" aria-label="Geser Kanan">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>

        <div class="kpi-grid" id="kpi-container">
            <div id="kpi-empty-message" style="display: none; grid-column: 1 / -1; padding: 36px 20px; text-align: center; color: var(--muted); background: white; border: 1px dashed var(--line); border-radius: 12px;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="margin-bottom: 8px; color: var(--muted);"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <strong style="display: block; font-size: 13px; color: var(--navy); margin-bottom: 3px;">Tidak ada indikator pada kategori ini</strong>
                <small style="font-size: 11px;">Silakan pilih kategori lain atau kembali ke "Semua Kategori".</small>
            </div>
            <?php
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
                <div class="kpi-card" 
                     data-code="<?= htmlspecialchars($item['kode_indikator']) ?>"
                     data-prefix="<?= strtoupper(explode('-', $item['kode_indikator'])[0]) ?>"
                     data-category="<?= htmlspecialchars($item['klaster'] ?? 'Output & Pertumbuhan') ?>" 
                     data-indicator-key="<?= $indKey ?>" 
                     style="position:relative; cursor:pointer;" 
                     title="Klik untuk menampilkan grafik tren indikator ini">
                    <div class="kpi-top" style="align-items:flex-start;">
                        <div style="display:flex; flex-direction:column; gap:4px;">
                            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                <span class="badge info" style="align-self:flex-start; font-size:9.5px; padding:2px 8px; font-weight:600; text-transform:none;">
                                    <?= htmlspecialchars($item['klaster'] ?? 'Output & Pertumbuhan') ?>
                                </span>
                                <?php if (in_array($item['kode_indikator'], array('EKO-003', 'EKO-004', 'EKO-006', 'EKO-007'))): ?>
                                    <span style="display:inline-flex; align-items:center; gap:3px; padding:2px 6px; font-size:9px; font-weight:700; background:#f0fdf4; color:#15803d; border:1px solid #86efac; border-radius:10px;">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                                        18 Sektor (A–U)
                                    </span>
                                <?php endif; ?>
                            </div>
                            <span style="font-weight:600; color:var(--navy); font-size:13px; line-height:1.3; margin-top:2px;">
                                <?= htmlspecialchars($item['nama_indikator']) ?>
                            </span>
                        </div>
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
                    <div class="kpi-period">
                        <span><?= $item['tahun'] ?? $selected_year ?> • <?= htmlspecialchars($item['wilayah'] ?? 'Banyuwangi') ?></span>
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
                    <span class="eyebrow" id="trend-eyebrow">HISTORIS HINGGA <?= $display_year ?></span>
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
                    <p>Pertumbuhan tahunan per sektor (Tahun <?= $display_year ?>)</p>
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
                <span><strong><?= $topSector['short'] ?></strong> mencatat laju pertumbuhan tertinggi pada tahun <?= $display_year ?>: <?= number_format($topSector['growthSeries'][$year_idx] ?? $topSector['growthLocal'], 1, ',', '.') ?>%.</span>
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
                    <p>Pertumbuhan ekonomi <?= number_format($gNow, 2, ',', '.') ?>% pada tahun <?= $display_year ?>; sektor <?= $topSector['short'] ?> memimpin laju pertumbuhan sektoral.</p>
                </div>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
            </a>

            <a href="<?= site_url('economic_insight') ?>" class="highlight-row">
                <span class="signal info"></span>
                <div>
                    <strong>Dinamika investasi daerah</strong>
                    <p>Realisasi investasi tumbuh <?= number_format($invNow, 2, ',', '.') ?>% pada tahun <?= $display_year ?> mendukung penyerapan tenaga kerja lokal.</p>
                </div>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
            </a>

            <a href="<?= site_url('economic_insight') ?>" class="highlight-row">
                <span class="signal <?= $infNow <= 4 ? 'success' : 'warning' ?>"></span>
                <div>
                    <strong>Perkembangan harga & inflasi</strong>
                    <p>Inflasi tercatat <?= number_format($infNow, 2, ',', '.') ?>% pada tahun <?= $display_year ?>. Stabilitas harga terjaga melalui koordinasi TPID.</p>
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
</div>

