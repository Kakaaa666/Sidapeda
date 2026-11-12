<div class="page admin-page">
    <div class="admin-intro">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions">
            <span class="badge simulation">TABEL AKUN & LEVEL AKTIF</span>
        </div>
    </div>

    <!-- HIERARKI LEVEL AKUN EXPLANATION -->
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:12px; margin-bottom: 22px;">
        <div class="card" style="padding:14px; border-top: 3px solid #0284c7;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                <span class="badge primary" style="font-weight:700;">Level 1</span>
                <strong style="font-size:12px; color:#0f172a;">Administrator</strong>
            </div>
            <p style="font-size:11px; color:#64748b; margin:0; line-height:1.4;">Akses penuh seluruh sistem: Kelola akun & level, konfigurasi EWS, proyeksi, validasi dan audit log.</p>
        </div>
        <div class="card" style="padding:14px; border-top: 3px solid #06b6d4;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                <span class="badge info" style="font-weight:700;">Level 2</span>
                <strong style="font-size:12px; color:#0f172a;">Analis</strong>
            </div>
            <p style="font-size:11px; color:#64748b; margin:0; line-height:1.4;">Akses analitis strategis: Analisis LQ/Shift-share, generator laporan ekonomi, insight kebijakan & validasi data.</p>
        </div>
        <div class="card" style="padding:14px; border-top: 3px solid #10b981;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                <span class="badge success" style="font-weight:700;">Level 3</span>
                <strong style="font-size:12px; color:#0f172a;">Operator</strong>
            </div>
            <p style="font-size:11px; color:#64748b; margin:0; line-height:1.4;">Akses operasional data: Input data indikator OPD, katalog indikator, dan pemantauan dataset ekonomi daerah.</p>
        </div>
        <div class="card" style="padding:14px; border-top: 3px solid #f59e0b;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                <span class="badge warning" style="font-weight:700;">Level 4</span>
                <strong style="font-size:12px; color:#0f172a;">Reviewer</strong>
            </div>
            <p style="font-size:11px; color:#64748b; margin:0; line-height:1.4;">Akses peninjau eksekutif: Monitoring dashboard publik, telaah insight pembangunan & evaluasi target capaian.</p>
        </div>
    </div>

    <article class="card">
        <div class="card-head">
            <div>
                <span class="eyebrow">DAFTAR AKUN & TINGKATAN AKSES</span>
                <h3>Manajemen Akun dan Pengaturan Level</h3>
                <p>Data tersimpan di tabel <code>akun</code> database MySQL <code>sidapeda</code>.</p>
            </div>
        </div>

        <div class="result-table-wrap">
            <table class="result-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Pengguna</th>
                        <th>Username / Akun</th>
                        <th>Level Akun (Tingkat Akses)</th>
                        <th>Status</th>
                        <th>Aksi Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr data-user-id="<?= $u['id'] ?>">
                            <td>#<?= $u['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($u['name']) ?></strong>
                                <?php if (!empty($u['email'])): ?>
                                    <div style="font-size:11px; color:#64748b;"><?= htmlspecialchars($u['email']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><code><?= htmlspecialchars($u['username']) ?></code></td>
                            <td>
                                <select class="user-role-select" data-user-id="<?= $u['id'] ?>" style="height:34px; border:1px solid #cbd5e1; border-radius:7px; padding:0 8px; font-weight:700; font-size:12px; background:#fff; cursor:pointer;">
                                    <option value="1" <?= (int)$u['level'] === 1 ? 'selected' : '' ?>>1 — Administrator</option>
                                    <option value="2" <?= (int)$u['level'] === 2 ? 'selected' : '' ?>>2 — Analis</option>
                                    <option value="3" <?= (int)$u['level'] === 3 ? 'selected' : '' ?>>3 — Operator</option>
                                    <option value="4" <?= (int)$u['level'] === 4 ? 'selected' : '' ?>>4 — Reviewer</option>
                                </select>
                            </td>
                            <td>
                                <span class="badge user-status-badge <?= $u['status'] === 'Aktif' ? 'success' : 'neutral' ?>">
                                    <?= $u['status'] ?>
                                </span>
                            </td>
                            <td>
                                <button class="button secondary btn-toggle-user-status" data-user-id="<?= $u['id'] ?>" data-current-status="<?= $u['status'] ?>" style="height:30px; font-size:11px; padding:0 10px;">
                                    <?= $u['status'] === 'Aktif' ? 'Nonaktifkan' : 'Aktifkan' ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="users-feedback-box" class="method-note" style="display:none; margin-top: 15px;"></div>
    </article>

    <!-- RIWAYAT AUDIT LOGIN / MASUK -->
    <article class="card" style="margin-top: 20px;">
        <div class="card-head">
            <div>
                <span class="eyebrow">AUDIT TRAIL • RIWAYAT MASUK</span>
                <h3>Catatan Autentikasi Akun Terbaru</h3>
                <p>Log real-time tercatat di tabel <code>riwayat_masuk</code>.</p>
            </div>
        </div>

        <div style="margin-top: 12px;">
            <?php if (!empty($logs)): ?>
                <?php foreach ($logs as $log): ?>
                    <div style="padding: 10px 0; border-bottom: 1px solid #edf0f4; font-size: 11px; display: flex; justify-content: space-between; align-items:center;">
                        <div>
                            <span class="badge <?= $log['status'] === 'Success' ? 'success' : 'danger' ?>" style="font-size:9px; margin-right:8px;"><?= $log['status'] ?></span>
                            <strong><?= htmlspecialchars($log['nama_pengguna']) ?></strong> &bull; <?= htmlspecialchars($log['pesan']) ?>
                            <span style="color:#94a3b8; font-size:10px; margin-left:6px;">IP: <?= htmlspecialchars($log['alamat_ip']) ?></span>
                        </div>
                        <small style="color:var(--muted); font-size:10px;"><?= date('d M Y H:i:s', strtotime($log['waktu_masuk'])) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="padding: 14px; text-align:center; color:#94a3b8; font-size:12px;">Belum ada catatan log masuk.</div>
            <?php endif; ?>
        </div>
    </article>
</div>
