<div class="page admin-page">
    <div class="admin-intro">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions">
            <a href="<?= site_url('admin/database') ?>" class="button primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14M5 12h14"/></svg>
                <span>Kelola Data</span>
            </a>
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
                        <button class="btn-quick-verify" data-code="<?= $item['code'] ?>" data-year="<?= $item['year'] ?>" style="width:100%; border:0; border-bottom:1px solid #edf0f4; background:white; display:flex; align-items:center; text-align:left; gap:9px; padding:10px 2px; cursor:pointer;">
                            <span class="task-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
                            </span>
                            <div style="flex:1;">
                                <strong style="font-size:10px; display:block; color:var(--navy);"><?= htmlspecialchars($item['indicator']) ?> (<?= $item['year'] ?>)</strong>
                                <small style="font-size:9px; color:var(--muted);"><?= htmlspecialchars($item['code']) ?> • <?= htmlspecialchars($item['source']) ?></small>
                                <?php if (!empty($item['notes']) && $item['status'] !== 'Tervalidasi'): ?>
                                    <small style="font-size:8.5px; color:#b91c1c; display:block; margin-top:2px;">Catatan: <?= htmlspecialchars($item['notes']) ?></small>
                                <?php endif; ?>
                            </div>
                            <span class="badge warning"><?= $item['status'] ?></span>
                        </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>
    </section>
</div>

