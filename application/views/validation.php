<div class="page admin-page">
    <div class="admin-intro">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions">
            <span class="badge simulation">DATABASE CI 3</span>
        </div>
    </div>

    <article class="card data-card">
        <div class="card-head">
            <div>
                <span class="eyebrow">ANTREAN REVIEW</span>
                <h3>Daftar Data Perlu Verifikasi</h3>
                <p>Tinjau data masuk dari input manual atau import sebelum dipublikasikan ke publik.</p>
            </div>
        </div>

        <div class="data-table">
            <div class="data-tr data-th">
                <span>Kode</span>
                <span>Indikator</span>
                <span>Klaster</span>
                <span>Periode</span>
                <span>Nilai</span>
                <span>Status</span>
                <span>Aksi Validasi</span>
            </div>

            <div id="validation-table-body">
                <?php if (empty($records)): ?>
                    <div class="table-empty">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
                        <strong>Tidak ada data dalam antrean verifikasi saat ini.</strong>
                    </div>
                <?php else: ?>
                    <?php foreach ($records as $item): ?>
                        <div class="data-tr" data-code="<?= $item['code'] ?>" data-year="<?= $item['year'] ?>">
                            <span><code><?= htmlspecialchars($item['code']) ?></code></span>
                            <span>
                                <strong><?= htmlspecialchars($item['indicator']) ?></strong>
                                <small><?= htmlspecialchars($item['region']) ?> • <?= htmlspecialchars($item['source']) ?></small>
                            </span>
                            <span><?= htmlspecialchars($item['cluster']) ?></span>
                            <span><?= htmlspecialchars($item['period']) ?> <?= $item['year'] ?></span>
                            <span><strong><?= number_format($item['value'], 2, ',', '.') ?></strong> <?= htmlspecialchars($item['unit']) ?></span>
                            <span>
                                <span class="badge warning"><?= $item['status'] ?></span>
                            </span>
                            <span class="row-actions">
                                <button class="btn-verify-record" title="Setujui Data" data-code="<?= $item['code'] ?>" data-year="<?= $item['year'] ?>" data-status="Tervalidasi">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
                                </button>
                                <button class="btn-verify-record" title="Tandai Belum Lengkap" data-code="<?= $item['code'] ?>" data-year="<?= $item['year'] ?>" data-status="Belum lengkap">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 8v5M12 17h.01"/></svg>
                                </button>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="pagination">
            <span>Antrean verifikasi aktif: <?= count($records) ?> data</span>
            <span>Verifikasi langsung mengubah status data di database</span>
        </div>
    </article>
</div>

