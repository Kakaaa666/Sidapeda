<div class="page admin-page">
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
        <!-- TEMPAT SAMPAH KATALOG -->
        <article class="card large" style="margin-bottom:24px; border:1px dashed #f43f5e; background:rgba(244,63,94,0.03);">
            <div class="card-head">
                <div>
                    <span class="badge danger">TEMPAT SAMPAH (SOFT DELETE)</span>
                    <h3>Daftar Master Indikator Terhapus (<?= count($catalog) ?>)</h3>
                    <p>Klik tombol Pulihkan untuk mengembalikan indikator ke katalog aktif.</p>
                </div>
                <a href="<?= site_url('catalog') ?>" class="button ghost">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    <span>Kembali ke Katalog Aktif</span>
                </a>
            </div>
            <div class="data-table">
                <div class="data-tr data-th" style="grid-template-columns: 120px 2fr 1.5fr 1.2fr 120px;">
                    <span>Kode</span>
                    <span>Nama Indikator</span>
                    <span>Klaster</span>
                    <span>Dihapus Pada</span>
                    <span>Aksi</span>
                </div>
                <?php if (empty($catalog)): ?>
                    <div style="padding:24px; text-align:center; color:var(--muted); font-size:11px;">
                        Tempat sampah kosong. Tidak ada indikator katalog yang terhapus.
                    </div>
                <?php else: ?>
                    <?php foreach ($catalog as $item): ?>
                        <div class="data-tr" style="grid-template-columns: 120px 2fr 1.5fr 1.2fr 120px;">
                            <span style="white-space: nowrap;"><code style="white-space: nowrap;"><?= htmlspecialchars($item['code']) ?></code></span>
                            <span><strong><?= htmlspecialchars($item['name']) ?></strong></span>
                            <span><?= htmlspecialchars($item['cluster']) ?></span>
                            <span><small><?= htmlspecialchars($item['deleted_at'] ?? '-') ?></small></span>
                            <span class="row-actions">
                                <button type="button" class="btn-restore-catalog" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>" title="Pulihkan Indikator">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                    <span>Pulihkan</span>
                                </button>
                                <button type="button" class="btn-hard-delete-catalog danger" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>" title="Hapus Permanen">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>
    <?php endif; ?>

    <div class="admin-intro">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions">
            <?php if ($trash_count > 0): ?>
                <?php if ($status === 'Terhapus'): ?>
                    <a href="<?= site_url('catalog') ?>" class="button ghost">
                        <span>Kembali ke Aktif</span>
                    </a>
                <?php else: ?>
                    <a href="<?= site_url('catalog?status=Terhapus') ?>" class="button ghost danger">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        <span>Sampah (<?= $trash_count ?>)</span>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            <button class="button primary" id="btn-open-add-catalog">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Tambah Indikator Master</span>
            </button>
            <button class="button secondary" id="btn-export-catalog-csv">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12M7 10l5 5 5-5M4 21h16"/></svg>
                <span>Ekspor CSV</span>
            </button>
        </div>
    </div>

    <!-- FILTER TAHUN KATALOG INDIKATOR (DROPDOWN) -->
    <div class="year-filter-bar">
        <div class="year-filter-info">
            <div class="year-filter-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div class="year-filter-text">
                <strong>Periode Tahun Indikator</strong>
                <small>Filter katalog indikator, grafik komposisi klaster, dan capaian realisasi berdasarkan tahun rilis.</small>
            </div>
        </div>

        <div class="year-filter-control">
            <label for="catalog-year-select">Pilih Tahun:</label>
            <select id="catalog-year-select" class="year-filter-select" onchange="window.location.href=this.value">
                <option value="<?= site_url('catalog?year=all' . ($status ? '&status='.$status : '') . (!empty($selected_cluster) ? '&cluster='.urlencode($selected_cluster) : '')) ?>" <?= empty($selected_year) ? 'selected' : '' ?>>Semua Tahun</option>
                <?php if (!empty($available_years)): ?>
                    <?php foreach ($available_years as $ay): ?>
                        <option value="<?= site_url('catalog?year=' . $ay['tahun'] . ($status ? '&status='.$status : '') . (!empty($selected_cluster) ? '&cluster='.urlencode($selected_cluster) : '')) ?>" <?= (!empty($selected_year) && $selected_year == $ay['tahun']) ? 'selected' : '' ?>>
                            Tahun <?= $ay['tahun'] ?> (<?= $ay['total_records'] ?> Indikator)
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <!-- GRAFIK DISTRIBUSI KLASTER INDIKATOR (Sesuai Data Katalog) -->
    <article class="card large" style="margin-bottom:20px;">
        <div class="card-head">
            <div>
                <span class="eyebrow"><?= !empty($selected_year) ? "DISTRIBUSI KLASTER PEMBANGUNAN • TAHUN {$selected_year}" : "DISTRIBUSI KLASTER PEMBANGUNAN • SEMUA PERIODE" ?></span>
                <h3>Grafik Komposisi Indikator per Klaster Data <?= !empty($selected_year) ? "(Tahun {$selected_year})" : "(Semua Periode)" ?></h3>
                <p>Proporsi sebaran indikator strategis daerah menurut kelompok klaster tema pembangunan<?= !empty($selected_year) ? " pada tahun {$selected_year}." : "." ?></p>
            </div>
            <?php if (!empty($selected_year)): ?>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span class="badge info" style="font-size:11px; padding:5px 12px; font-weight:600; border-radius:20px;">
                        Tahun Terpilih: <strong><?= $selected_year ?></strong> (<?= count($catalog) ?> Indikator)
                    </span>
                    <a href="<?= site_url('catalog?year=all' . ($status ? '&status='.$status : '')) ?>" class="button ghost" style="font-size:11px; height:30px;">
                        Lihat Semua Periode
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <div id="catalog-cluster-chart" class="chart-wrap dynamic-chart" style="height:auto; min-height:140px; padding:6px 0;">
            <!-- Rendered dynamically by JS -->
        </div>
    </article>

    <article class="card data-card">
        <div class="card-head">
            <div>
                <span class="eyebrow"><?= !empty($selected_year) ? "STANDAR METADATA • INDIKATOR TAHUN {$selected_year}" : "STANDAR METADATA • KAMUS DATA MASTER" ?></span>
                <?php $totalRecCount = array_sum(array_column($catalog, 'total_records')); ?>
                <?php if (!empty($selected_year)): ?>
                    <h3>Daftar Indikator Daerah Tahun <?= $selected_year ?> (<?= count($catalog) ?> Indikator • <?= count($catalog) ?> Rekaman Data)</h3>
                    <p>Menampilkan metadata dan capaian realisasi indikator pembangunan daerah yang tervalidasi pada tahun anggaran <?= $selected_year ?>.</p>
                <?php else: ?>
                    <h3>Daftar Master Indikator Daerah (<?= count($catalog) ?> Indikator Master • <?= $totalRecCount ?> Rekaman Data)</h3>
                    <p>Kamus data master indikator pembangunan daerah. Setiap 1 entri master indikator mencakup rekaman data berkala (tahunan) yang tersimpan di repositori database.</p>
                <?php endif; ?>
            </div>
            <?php if (!empty($selected_year)): ?>
                <span class="badge primary" style="font-size:11px; padding:6px 14px; font-weight:700; border-radius:20px; box-shadow:0 2px 6px rgba(36,99,166,0.2);">
                    Filter Aktif: Tahun <?= $selected_year ?>
                </span>
            <?php endif; ?>
        </div>

        <div style="padding: 11px 18px; background: #eff6ff; border-left: 4px solid var(--blue); border-bottom: 1px solid #bfdbfe; font-size: 11.5px; line-height: 1.5; color: #1e40af; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <div>
                <strong>Standar Kamus Data Umum:</strong> Atribut <em>Kode, Nama Indikator, Klaster Pembangunan, Satuan, Frekuensi, Walidata / Sumber, dan Definisi Operasional</em> merupakan <strong>Data Umum Standar</strong> yang berlaku seragam untuk seluruh Kabupaten/Kota. Setiap daerah secara operasional hanya mengisi <strong>Nilai Realisasi</strong> pada Database Ekonomi serta <strong>Ambang Batas (Threshold)</strong> pada Early Warning System.
            </div>
        </div>

        <?php if ($status !== 'Terhapus' && !empty($catalog)): ?>
            <?php
            $clCounts = [];
            $freqCounts = [];
            foreach ($catalog as $c) {
                $cl = $c['klaster'] ?: 'Umum';
                $clCounts[$cl] = ($clCounts[$cl] ?? 0) + 1;
                $fq = $c['frekuensi'] ?: 'Tahunan';
                $freqCounts[$fq] = ($freqCounts[$fq] ?? 0) + 1;
            }
            $maxCl = !empty($clCounts) ? max($clCounts) : 1;
            $totCat = count($catalog) ?: 1;
            ?>
            <div style="display:grid; grid-template-columns: 1.2fr 1fr; gap:16px; padding:14px 18px; background:#f8fafc; border-bottom:1px solid var(--line);">
                <div>
                    <strong style="font-size: 12px; color:var(--navy); display:block; margin-bottom:6px;">Distribusi Indikator per Klaster Pembangunan <?= !empty($selected_year) ? "(Tahun {$selected_year})" : "(Semua Periode)" ?></strong>
                    <div style="display:grid; gap:5px;">
                        <?php foreach ($clCounts as $cName => $cCnt): ?>
                            <div style="display:grid; grid-template-columns: 140px 1fr 45px; align-items:center; gap:6px; font-size: 11.5px;">
                                <span style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= htmlspecialchars($cName) ?></span>
                                <div style="height:6px; border-radius:3px; background:#e2e8f0; overflow:hidden;">
                                    <div style="width:<?= round(($cCnt/$maxCl)*100) ?>%; height:100%; background:var(--teal);"></div>
                                </div>
                                <strong style="text-align:right; font-size: 11.5px;"><?= $cCnt ?> <small style="color:var(--muted); font-size: 11px;">(<?= round(($cCnt/$totCat)*100) ?>%)</small></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div>
                    <strong style="font-size: 12px; color:var(--navy); display:block; margin-bottom:6px;">Frekuensi Pembaruan Data</strong>
                    <div style="display:flex; gap:8px; flex-wrap:wrap; font-size: 11.5px;">
                        <?php foreach ($freqCounts as $fName => $fCnt): ?>
                            <span class="badge info" style="font-size: 11.5px; padding:4px 8px;">
                                <strong><?= htmlspecialchars($fName) ?></strong>: <?= $fCnt ?> indikator
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- TOOLBAR PENCARIAN & FILTER KLASTER KATALOG -->
        <div class="management-toolbar" style="padding: 12px 18px; border-bottom: 1px solid var(--line); display:flex; gap:12px; align-items:center; flex-wrap:wrap; background:#ffffff;">
            <div class="search wide" style="flex:1; min-width:220px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                <input type="text" id="catalog-search-input" placeholder="Cari kode indikator, nama, klaster, walidata...">
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <label for="catalog-toolbar-year-select" style="font-size:12px; font-weight:600; color:var(--navy); white-space:nowrap;">Filter Tahun:</label>
                <select id="catalog-toolbar-year-select" onchange="window.location.href=this.value" style="min-width: 170px; font-weight: 600;">
                    <option value="<?= site_url('catalog?year=all' . ($status ? '&status='.$status : '') . (!empty($selected_cluster) ? '&cluster='.urlencode($selected_cluster) : '')) ?>" <?= empty($selected_year) ? 'selected' : '' ?>>Semua Tahun</option>
                    <?php if (!empty($available_years)): ?>
                        <?php foreach ($available_years as $ay): ?>
                            <option value="<?= site_url('catalog?year=' . $ay['tahun'] . ($status ? '&status='.$status : '') . (!empty($selected_cluster) ? '&cluster='.urlencode($selected_cluster) : '')) ?>" <?= (!empty($selected_year) && $selected_year == $ay['tahun']) ? 'selected' : '' ?>>
                                Tahun <?= $ay['tahun'] ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
                <label for="catalog-filter-cluster-select" style="font-size:12px; font-weight:600; color:var(--navy); white-space:nowrap;">Filter Klaster:</label>
                <select id="catalog-filter-cluster-select" style="min-width: 250px; font-weight: 600;">
                    <option value="all">Semua Klaster (9 Klaster)</option>
                    <?php if (!empty($available_clusters)): ?>
                        <?php foreach ($available_clusters as $ac): ?>
                            <option value="<?= htmlspecialchars($ac['klaster']) ?>" <?= (!empty($selected_cluster) && $selected_cluster === $ac['klaster']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ac['klaster']) ?> (<?= $ac['total_indicators'] ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <span class="badge info" id="catalog-filter-counter-badge" style="font-size:11px; padding:6px 12px; font-weight:600; white-space:nowrap;">
                    <span id="catalog-visible-count"><?php
                        if (!empty($selected_cluster) && $selected_cluster !== 'all') {
                            $cnt = 0;
                            foreach ($catalog as $it) {
                                if (($it['cluster'] ?? '') === $selected_cluster) $cnt++;
                            }
                            echo $cnt;
                        } else {
                            echo count($catalog);
                        }
                    ?></span> Indikator
                </span>
            </div>
        </div>

        <div class="result-table-wrap">
            <table class="result-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Indikator</th>
                        <th>Klaster Pembangunan</th>
                        <th>Satuan</th>
                        <?php if (!empty($selected_year)): ?>
                            <th>Nilai Realisasi (<?= $selected_year ?>)</th>
                        <?php endif; ?>
                        <th>Frekuensi</th>
                        <th>Walidata / Sumber</th>
                        <th>Definisi Operasional</th>
                        <th style="width:110px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr id="catalog-filter-empty-row" style="display:none;">
                        <td colspan="<?= !empty($selected_year) ? 9 : 8 ?>" style="text-align:center; padding:32px 16px; color:var(--muted); font-size:12px;">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:6px; display:inline-block;"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                            <div>Tidak ada indikator yang sesuai dengan filter klaster dan pencarian saat ini.</div>
                        </td>
                    </tr>
                    <?php if (empty($catalog)): ?>
                        <tr>
                            <td colspan="<?= !empty($selected_year) ? 9 : 8 ?>" style="text-align:center; padding:32px 16px; color:var(--muted);">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:8px; display:inline-block;"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                                <div><strong><?= ($status === 'Terhapus') ? 'Tempat sampah kosong. Tidak ada indikator yang dihapus.' : 'Tidak ada indikator katalog ditemukan.' ?></strong></div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($catalog as $item): ?>
                            <?php $isClHidden = (!empty($selected_cluster) && $selected_cluster !== 'all' && ($item['cluster'] ?? '') !== $selected_cluster); ?>
                            <tr data-cluster="<?= htmlspecialchars($item['cluster']) ?>" style="<?= $isClHidden ? 'display:none;' : '' ?>">
                                <td style="white-space: nowrap;"><code style="white-space: nowrap;"><?= htmlspecialchars($item['code']) ?></code></td>
                                <td>
                                    <strong><?= htmlspecialchars($item['name']) ?></strong>
                                    <?php if (empty($selected_year) && !empty($item['total_records'])): ?>
                                        <small style="display:block; font-size: 12.5px; color:var(--teal); font-weight:600; margin-top:3px;">
                                            <?= $item['total_records'] ?> rekaman tahunan (<?= htmlspecialchars($item['period_span']) ?>)
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($item['cluster']) ?></td>
                                <td><?= htmlspecialchars($item['unit']) ?></td>
                                <?php if (!empty($selected_year)): ?>
                                    <td>
                                        <?php if ($item['selected_year_value'] !== null): ?>
                                            <strong style="color:var(--navy); font-size:12.5px; font-variant-numeric:tabular-nums;">
                                                <?= number_format($item['selected_year_value'], 2, ',', '.') ?>
                                            </strong>
                                            <span class="badge info" style="font-size: 12px; margin-left:4px; padding:1px 5px;"><?= htmlspecialchars($item['unit']) ?></span>
                                        <?php else: ?>
                                            <span style="color:var(--muted); font-size:11px;">-</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                <td><?= htmlspecialchars($item['frequency']) ?></td>
                                <td><?= htmlspecialchars($item['source']) ?></td>
                                <td><?= htmlspecialchars($item['definition']) ?></td>
                                <td>
                                    <div class="row-actions">
                                        <?php if ($status === 'Terhapus'): ?>
                                            <button type="button" class="btn-restore-catalog button small" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>" title="Pulihkan Indikator">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                                <span>Pulihkan</span>
                                            </button>
                                            <button type="button" class="btn-hard-delete-catalog danger button small" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>" title="Hapus Permanen">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn-edit-catalog" data-id="<?= $item['id'] ?>" title="Edit Indikator">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </button>
                                            <button type="button" class="btn-delete-catalog danger" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>" title="Hapus (Soft Delete)">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </article>

    <!-- MODAL TAMBAH KATALOG -->
    <div class="modal-backdrop" id="add-catalog-modal" style="display:none;">
        <div class="modal admin-data-modal" style="max-width:540px;">
            <div class="modal-head">
                <div>
                    <span class="badge info">TAMBAH KATALOG</span>
                    <h2>Tambah Master Indikator</h2>
                    <p>Simpan metadata indikator ke tabel database <code>katalog_indikator</code>.</p>
                </div>
                <button class="icon-button" id="modal-btn-close-add-catalog">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="form-add-catalog" method="POST" action="<?= site_url('catalog/create') ?>">
                <div class="manual-form" style="grid-template-columns: 1fr 1fr;">
                    <label>
                        Kode Indikator
                        <input type="text" name="kode_indikator" id="add-catalog-code" placeholder="Contoh: ind_pdrb_kapita" required>
                    </label>
                    <label>
                        Satuan Ukuran
                        <input type="text" name="satuan" id="add-catalog-unit" placeholder="Contoh: % / Poin / Juta Rp" required>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Nama Indikator
                        <input type="text" name="nama_indikator" id="add-catalog-name" placeholder="Contoh: PDRB per Kapita ADHB" required>
                    </label>
                    <label>
                        Klaster Pembangunan
                        <div class="combobox-wrap">
                            <input type="text" name="klaster" id="add-catalog-cluster" class="cluster-combobox-input" placeholder="Pilih dari daftar atau ketik manual..." autocomplete="off" required>
                            <button type="button" class="combobox-toggle-btn" tabindex="-1" title="Lihat daftar klaster yang sudah di-input">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <div class="combobox-menu">
                                <?php if (!empty($all_clusters)): ?>
                                    <?php foreach ($all_clusters as $clName): ?>
                                        <div class="combobox-item" data-value="<?= htmlspecialchars($clName) ?>">
                                            <?= htmlspecialchars($clName) ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </label>
                    <label>
                        Frekuensi Rilis
                        <select name="frekuensi" id="add-catalog-freq">
                            <option value="Tahunan">Tahunan</option>
                            <option value="Semesteran">Semesteran</option>
                            <option value="Triwulanan">Triwulanan</option>
                            <option value="Bulanan">Bulanan</option>
                        </select>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Sumber Data / Walidata
                        <input type="text" name="sumber_data" id="add-catalog-source" placeholder="Contoh: BPS Kabupaten Banyuwangi" required>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Definisi Operasional
                        <textarea name="definisi_operasional" id="add-catalog-def" rows="2" placeholder="Uraikan konsep dan batasan penghitungan indikator..."></textarea>
                    </label>
                </div>

                <div id="add-catalog-error" class="method-note" style="display:none; color:var(--red); margin-bottom:12px;"></div>

                <div class="modal-actions">
                    <button type="button" class="button ghost" id="modal-btn-cancel-add-catalog">Batal</button>
                    <button type="submit" class="button primary" id="modal-btn-submit-add-catalog">Simpan Indikator</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT KATALOG -->
    <div class="modal-backdrop" id="edit-catalog-modal" style="display:none;">
        <div class="modal admin-data-modal" style="max-width:540px;">
            <div class="modal-head">
                <div>
                    <span class="badge warning">PERBARUI KATALOG</span>
                    <h2>Edit Master Indikator</h2>
                    <p>Perbarui definisi operasional, walidata, atau klaster indikator.</p>
                </div>
                <button class="icon-button" id="modal-btn-close-edit-catalog">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="form-edit-catalog" method="POST">
                <input type="hidden" name="id" id="edit-catalog-id">
                <div class="manual-form" style="grid-template-columns: 1fr 1fr;">
                    <label>
                        Kode Indikator
                        <input type="text" name="kode_indikator" id="edit-catalog-code" required>
                    </label>
                    <label>
                        Satuan Ukuran
                        <input type="text" name="satuan" id="edit-catalog-unit" required>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Nama Indikator
                        <input type="text" name="nama_indikator" id="edit-catalog-name" required>
                    </label>
                    <label>
                        Klaster Pembangunan
                        <div class="combobox-wrap">
                            <input type="text" name="klaster" id="edit-catalog-cluster" class="cluster-combobox-input" placeholder="Pilih dari daftar atau ketik manual..." autocomplete="off" required>
                            <button type="button" class="combobox-toggle-btn" tabindex="-1" title="Lihat daftar klaster yang sudah di-input">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <div class="combobox-menu">
                                <?php if (!empty($all_clusters)): ?>
                                    <?php foreach ($all_clusters as $clName): ?>
                                        <div class="combobox-item" data-value="<?= htmlspecialchars($clName) ?>">
                                            <?= htmlspecialchars($clName) ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </label>
                    <label>
                        Frekuensi Rilis
                        <select name="frekuensi" id="edit-catalog-freq">
                            <option value="Tahunan">Tahunan</option>
                            <option value="Semesteran">Semesteran</option>
                            <option value="Triwulanan">Triwulanan</option>
                            <option value="Bulanan">Bulanan</option>
                        </select>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Sumber Data / Walidata
                        <input type="text" name="sumber_data" id="edit-catalog-source" required>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Definisi Operasional
                        <textarea name="definisi_operasional" id="edit-catalog-def" rows="2"></textarea>
                    </label>
                </div>

                <div id="edit-catalog-error" class="method-note" style="display:none; color:var(--red); margin-bottom:12px;"></div>

                <div class="modal-actions">
                    <button type="button" class="button ghost" id="modal-btn-cancel-edit-catalog">Batal</button>
                    <button type="submit" class="button primary" id="modal-btn-submit-edit-catalog">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

</div>

