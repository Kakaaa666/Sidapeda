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
                <span class="eyebrow">EWS DATA & THRESHOLD CONFIGURATION</span>
                <h3>Konfigurasi Nilai Riil & Ambang Batas Early Warning System</h3>
                <p>Kelola nilai riil terkini, ambang batas, dan kondisi risiko untuk pemantauan dini ekonomi daerah.</p>
            </div>
            <button class="button primary" id="btn-save-all-thresholds">Simpan Semua Perubahan</button>
        </div>

        <div id="threshold-admin-rows">
            <?php foreach ($ews as $item): ?>
                <div class="threshold-admin-row" data-code="<?= $item['code'] ?>" style="display:grid; grid-template-columns: 1.4fr 120px 120px 200px; gap: 14px; align-items: center; padding: 14px 0; border-top: 1px solid #edf0f4;">
                    <span>
                        <strong><?= htmlspecialchars($item['name']) ?></strong>
                        <small>Kode: <?= htmlspecialchars($item['code']) ?> • Satuan: <?= htmlspecialchars($item['unit']) ?></small>
                    </span>
                    <div>
                        <label style="display:block; font-size:10px; font-weight:600; color:var(--muted); margin-bottom:3px;">Nilai Riil:</label>
                        <input type="number" step="any" class="value-input" data-code="<?= $item['code'] ?>" value="<?= $item['value'] ?? $item['nilai_terkini'] ?>" style="width:100%; height:34px; border:1px solid var(--line); border-radius:6px; padding:0 8px; font-weight:600;">
                    </div>
                    <div>
                        <label style="display:block; font-size:10px; font-weight:600; color:var(--muted); margin-bottom:3px;">Ambang Batas:</label>
                        <input type="number" step="any" class="threshold-input" data-code="<?= $item['code'] ?>" value="<?= $item['threshold'] ?>" style="width:100%; height:34px; border:1px solid var(--line); border-radius:6px; padding:0 8px; font-weight:600;">
                    </div>
                    <div>
                        <label style="display:block; font-size:10px; font-weight:600; color:var(--muted); margin-bottom:3px;">Arah Kondisi Kritis:</label>
                        <select class="direction-input" data-code="<?= $item['code'] ?>" style="width:100%; height:34px; border:1px solid var(--line); border-radius:6px; padding:0 8px; font-size:11px;">
                            <option value="above" <?= ($item['direction'] === 'above') ? 'selected' : '' ?>>Kritis jika &gt; Ambang (Beban)</option>
                            <option value="below" <?= ($item['direction'] === 'below') ? 'selected' : '' ?>>Kritis jika &lt; Ambang (Target)</option>
                        </select>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="ews-admin-feedback" class="method-note" style="display:none; margin-top: 15px;"></div>
    </article>
</div>

