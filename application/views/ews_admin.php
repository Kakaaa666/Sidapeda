<div class="page admin-page">
    <div class="admin-intro">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions">
        </div>
    </div>

    <article class="card threshold-admin">
        <div class="card-head">
            <div>
                <span class="eyebrow">THRESHOLD CONFIGURATION</span>
                <h3>Konfigurasi Ambang Batas Early Warning System</h3>
                <p>Ambang batas digunakan bersama oleh modul publik dan sistem pemantauan dini.</p>
            </div>
            <button class="button primary" id="btn-save-all-thresholds">Simpan Semua Perubahan</button>
        </div>

        <div id="threshold-admin-rows">
            <?php foreach ($ews as $item): ?>
                <label class="threshold-admin-row" data-code="<?= $item['code'] ?>">
                    <span>
                        <strong><?= htmlspecialchars($item['name']) ?></strong>
                        <small>Kode: <?= htmlspecialchars($item['code']) ?> • Satuan: <?= htmlspecialchars($item['unit']) ?> • Kondisi risiko: <?= $item['direction'] === 'above' ? 'jika melampaui ambang batas' : 'jika berada di bawah ambang batas' ?></small>
                    </span>
                    <input type="number" step="any" class="threshold-input" data-code="<?= $item['code'] ?>" value="<?= $item['threshold'] ?>">
                </label>
            <?php endforeach; ?>
        </div>

        <div id="ews-admin-feedback" class="method-note" style="display:none; margin-top: 15px;"></div>
    </article>
</div>

