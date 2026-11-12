<div class="page admin-page">
    <div class="admin-intro">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions">
            <button class="button secondary" id="btn-export-catalog-csv">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12M7 10l5 5 5-5M4 21h16"/></svg>
                <span>Ekspor Katalog CSV</span>
            </button>
            <span class="badge simulation">DATABASE CI 3</span>
        </div>
    </div>

    <article class="card data-card">
        <div class="card-head">
            <div>
                <span class="eyebrow">STANDAR METADATA</span>
                <h3>Daftar Master Indikator Daerah</h3>
                <p>Memuat metadata, definisi operasional, dan walidata indikator strategis.</p>
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
                        <th>Frekuensi</th>
                        <th>Walidata / Sumber</th>
                        <th>Definisi Operasional</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($catalog as $item): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($item['code']) ?></code></td>
                            <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
                            <td><?= htmlspecialchars($item['cluster']) ?></td>
                            <td><?= htmlspecialchars($item['unit']) ?></td>
                            <td><?= htmlspecialchars($item['frequency']) ?></td>
                            <td><?= htmlspecialchars($item['source']) ?></td>
                            <td><?= htmlspecialchars($item['definition']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</div>

