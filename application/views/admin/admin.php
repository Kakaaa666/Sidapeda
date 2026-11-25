<div class="page admin-page">
    <div class="admin-intro">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions">
            <a href="<?= site_url('database') ?>" class="button primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14M5 12h14"/></svg>
                <span>Kelola Data</span>
            </a>
        </div>
    </div>

    <!-- FILTER TAHUN DASHBOARD ADMIN (DROPDOWN) -->
    <div class="year-filter-bar" style="margin-top: 15px; margin-bottom: 22px;">
        <div class="year-filter-info">
            <div class="year-filter-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div class="year-filter-text">
                <strong>Periode Tahun Dashboard Admin</strong>
                <small>Pantau status kesehatan dataset, antrean verifikasi, dan aturan risiko sesuai tahun anggaran.</small>
            </div>
        </div>

        <div class="year-filter-control">
            <label for="admin-year-select">Pilih Tahun:</label>
            <select id="admin-year-select" class="year-filter-select" onchange="window.location.href=this.value">
                <option value="<?= site_url('admin?year=all') ?>" <?= empty($selected_year) ? 'selected' : '' ?>>Semua Tahun (Terkini)</option>
                <?php if (!empty($available_years)): ?>
                    <?php foreach ($available_years as $ay): ?>
                        <option value="<?= site_url('admin?year=' . $ay['tahun']) ?>" <?= (!empty($selected_year) && $selected_year == $ay['tahun']) ? 'selected' : '' ?>>
                            Tahun <?= $ay['tahun'] ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <!-- METRIC STATS ROW -->
    <?php
    $totalRecords = count($records);
    $validCount = 0;
    $reviewCount = 0;
    $incompleteCount = 0;

    foreach ($records as $r) {
        if ($r['status'] === 'Tervalidasi') $validCount++;
        elseif ($r['status'] === 'Perlu review') $reviewCount++;
        else $incompleteCount++;
    }
    ?>
    <div class="metric-row admin-metrics">
        <div class="metric-box">
            <strong><?= $totalRecords ?></strong>
            <span>Rekaman Data Indikator</span>
            <small>Tersimpan di Sistem</small>
        </div>
        <div class="metric-box">
            <strong><?= count($catalog) ?></strong>
            <span>Indikator Katalog</span>
            <small>Master Indikator Daerah</small>
        </div>
        <div class="metric-box">
            <strong><?= $validCount ?></strong>
            <span>Data Tervalidasi</span>
            <small>Siap Publikasi Publik</small>
        </div>
        <div class="metric-box">
            <strong><?= ($reviewCount + $incompleteCount) ?></strong>
            <span>Perlu Pemeriksaan</span>
            <small>Antrean Review & Draft</small>
        </div>
        <div class="metric-box">
            <strong><?= count($ews) ?></strong>
            <span>Aturan EWS Aktif</span>
            <small>Threshold Pemantauan</small>
        </div>
    </div>

    <!-- TWO COLUMNS -->
    <section class="two-col" style="margin-top: 18px;">
        <article class="card large">
            <div class="card-head">
                <div>
                    <span class="eyebrow">DATA HEALTH & INTEGRITY</span>
                    <h3>Status Validasi Rekaman Data</h3>
                </div>
                <span class="badge info"><?= $totalRecords ?> Total Rekaman</span>
            </div>

            <div class="admin-status-row">
                <span>Tervalidasi</span>
                <div>
                    <i style="width: <?= $totalRecords ? round($validCount / $totalRecords * 100) : 0 ?>%; background: var(--green);"></i>
                </div>
                <strong><?= $validCount ?></strong>
            </div>

            <div class="admin-status-row">
                <span>Perlu review</span>
                <div>
                    <i style="width: <?= $totalRecords ? round($reviewCount / $totalRecords * 100) : 0 ?>%; background: var(--yellow);"></i>
                </div>
                <strong><?= $reviewCount ?></strong>
            </div>

            <div class="admin-status-row">
                <span>Belum lengkap</span>
                <div>
                    <i style="width: <?= $totalRecords ? round($incompleteCount / $totalRecords * 100) : 0 ?>%; background: #94a3b8;"></i>
                </div>
                <strong><?= $incompleteCount ?></strong>
            </div>
        </article>

        <!-- PENDING ACTIONS -->
        <article class="card tasks">
            <div class="card-head">
                <div>
                    <span class="eyebrow">ACTION REQUIRED</span>
                    <h3>Antrean Review Terbaru</h3>
                </div>
                <a href="<?= site_url('admin/validation') ?>" class="text-button">
                    <span>Lihat Semua</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>

            <div class="task-list">
                <?php
                $canValidate = !empty($user) && in_array((int)($user['level'] ?? 1), array(1, 2));
                $pending = array_filter($records, function($item) {
                    return $item['status'] !== 'Tervalidasi';
                });
                if (empty($pending)):
                ?>
                    <div class="table-empty">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
                        <span>Semua data telah tervalidasi.</span>
                    </div>
                <?php else: ?>
                    <?php foreach (array_slice($pending, 0, 5) as $item): ?>
                        <?php if ($canValidate): ?>
                        <button class="btn-quick-verify" data-code="<?= $item['code'] ?>" data-year="<?= $item['year'] ?>" style="width:100%; border:0; border-bottom:1px solid #edf0f4; background:white; display:flex; align-items:center; text-align:left; gap:9px; padding:10px 2px; cursor:pointer;">
                        <?php else: ?>
                        <div style="width:100%; border:0; border-bottom:1px solid #edf0f4; background:white; display:flex; align-items:center; text-align:left; gap:9px; padding:10px 2px;">
                        <?php endif; ?>
                            <span class="task-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
                            </span>
                            <div style="flex:1;">
                                <strong style="font-size: 12.5px; display:block; color:var(--navy);"><?= htmlspecialchars($item['indicator']) ?> (<?= $item['year'] ?>)</strong>
                                <small style="font-size: 12px; color:var(--muted);"><?= htmlspecialchars($item['code']) ?> • <?= htmlspecialchars($item['source']) ?></small>
                                <?php if (!empty($item['notes']) && $item['status'] !== 'Tervalidasi'): ?>
                                    <small style="font-size: 11.5px; color:#b91c1c; display:block; margin-top:2px;">Catatan: <?= htmlspecialchars($item['notes']) ?></small>
                                <?php endif; ?>
                            </div>
                            <span class="badge warning"><?= $item['status'] ?></span>
                        <?= $canValidate ? '</button>' : '</div>' ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>
    </section>
</div>

