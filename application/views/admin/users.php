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
        <!-- TEMPAT SAMPAH AKUN -->
        <article class="card large" style="margin-bottom:24px; border:1px dashed #f43f5e; background:rgba(244,63,94,0.03);">
            <div class="card-head">
                <div>
                    <span class="badge danger">TEMPAT SAMPAH</span>
                    <h3>Daftar Akun Terhapus (<?= count($users) ?>)</h3>
                    <p>Klik tombol Pulihkan untuk mengaktifkan kembali akun pengguna.</p>
                </div>
                <a href="<?= site_url('users') ?>" class="button ghost">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    <span>Kembali ke Akun Aktif</span>
                </a>
            </div>
            <div class="data-table">
                <div class="data-tr data-th" style="grid-template-columns: 80px 2fr 1.5fr 1fr 1.2fr 120px;">
                    <span>ID</span>
                    <span>Nama Pengguna</span>
                    <span>Username</span>
                    <span>Level</span>
                    <span>Dihapus Pada</span>
                    <span>Aksi</span>
                </div>
                <?php if (empty($users)): ?>
                    <div style="padding:24px; text-align:center; color:var(--muted); font-size:11px;">
                        Tempat sampah kosong. Tidak ada akun yang terhapus.
                    </div>
                <?php else: ?>
                    <?php foreach ($users as $item): ?>
                        <div class="data-tr" style="grid-template-columns: 80px 2fr 1.5fr 1fr 1.2fr 120px;">
                            <span>#<?= $item['id'] ?></span>
                            <span><strong><?= htmlspecialchars($item['name']) ?></strong></span>
                            <span><code><?= htmlspecialchars($item['username']) ?></code></span>
                            <span>Level <?= $item['level'] ?></span>
                            <span><small><?= htmlspecialchars($item['deleted_at'] ?? '-') ?></small></span>
                            <span class="row-actions">
                                <button type="button" class="btn-restore-user" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>" title="Pulihkan Akun">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                    <span>Pulihkan</span>
                                </button>
                                <button type="button" class="btn-hard-delete-user danger" data-id="<?= $item['id'] ?>" data-name="<?= htmlspecialchars($item['name']) ?>" title="Hapus Permanen">
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
                    <a href="<?= site_url('users') ?>" class="button ghost">
                        <span>Kembali ke Aktif</span>
                    </a>
                <?php else: ?>
                    <a href="<?= site_url('users?status=Terhapus') ?>" class="button ghost danger">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        <span>Sampah (<?= $trash_count ?>)</span>
                    </a>
                <?php endif; ?>
            <?php endif; ?>
            <button class="button primary" id="btn-open-add-user">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Tambah Akun Baru</span>
            </button>
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

    <!-- GRAFIK KOMPOSISI LEVEL PENGGUNA (Sesuai Data Akun Pengguna) -->
    <article class="card large" style="margin-bottom:20px;">
        <div class="card-head">
            <div>
                <span class="eyebrow">KOMPOSISI HAK AKSES PENGGUNA</span>
                <h3>Grafik Distribusi Akun Berdasarkan Level Otorisasi</h3>
                <p>Visualisasi sebaran akun aktif pada setiap tingkatan level hak akses sistem.</p>
            </div>
        </div>
        <div id="users-level-chart" class="chart-wrap dynamic-chart" style="height:auto; min-height:130px; padding:6px 0;">
            <!-- Rendered dynamically by JS -->
        </div>
    </article>

    <article class="card">
        <div class="card-head">
            <div>
                <span class="eyebrow">DAFTAR AKUN & TINGKATAN AKSES</span>
                <h3>Manajemen Akun dan Pengaturan Level</h3>
                <p>Daftar seluruh pengguna dan pengaturan hak akses sistem SI-DAPEDA.</p>
            </div>
        </div>

        <?php if ($status !== 'Terhapus' && !empty($users)): ?>
            <?php
            $lvlCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
            $activeCnt = 0; $inactiveCnt = 0;
            foreach ($users as $u) {
                $lvl = (int)($u['level'] ?? 1);
                if (isset($lvlCounts[$lvl])) $lvlCounts[$lvl]++;
                else $lvlCounts[$lvl] = 1;
                if (($u['status'] ?? 'Aktif') === 'Aktif') $activeCnt++;
                else $inactiveCnt++;
            }
            $totUsr = count($users) ?: 1;
            $maxLvl = max(array_values($lvlCounts)) ?: 1;
            $lvlLabels = [
                1 => ['Administrator', 'var(--blue)'],
                2 => ['Analis Data', 'var(--teal)'],
                3 => ['Operator OPD', '#6366f1'],
                4 => ['Reviewer Eksekutif', 'var(--yellow)']
            ];
            ?>
            <div style="display:grid; grid-template-columns: 1.3fr 1fr; gap:16px; padding:14px 18px; background:#f8fafc; border-bottom:1px solid var(--line);">
                <div>
                    <strong style="font-size:9px; color:var(--navy); display:block; margin-bottom:8px;">Komposisi Pengguna per Level Hak Akses</strong>
                    <div style="display:grid; gap:6px;">
                        <?php foreach ($lvlLabels as $lNum => $lInfo): ?>
                            <div style="display:grid; grid-template-columns: 130px 1fr 50px; align-items:center; gap:8px; font-size:8px;">
                                <span><strong>Level <?= $lNum ?>:</strong> <?= $lInfo[0] ?></span>
                                <div style="height:7px; border-radius:4px; background:#e2e8f0; overflow:hidden;">
                                    <div style="width:<?= round(($lvlCounts[$lNum]/$maxLvl)*100) ?>%; height:100%; background:<?= $lInfo[1] ?>;"></div>
                                </div>
                                <strong style="text-align:right; font-size:8px;"><?= $lvlCounts[$lNum] ?> akun</strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div>
                    <strong style="font-size:9px; color:var(--navy); display:block; margin-bottom:8px;">Status Keaktifan Akun</strong>
                    <div style="display:flex; height:10px; border-radius:5px; overflow:hidden; background:#e2e8f0; margin-bottom:8px;">
                        <div style="width:<?= round(($activeCnt/$totUsr)*100) ?>%; background:var(--green);" title="Aktif: <?= $activeCnt ?>"></div>
                        <div style="width:<?= round(($inactiveCnt/$totUsr)*100) ?>%; background:var(--red);" title="Nonaktif: <?= $inactiveCnt ?>"></div>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:8px; color:var(--muted);">
                        <span><i style="display:inline-block; width:7px; height:7px; border-radius:50%; background:var(--green); margin-right:3px;"></i>Aktif: <strong><?= $activeCnt ?></strong></span>
                        <span><i style="display:inline-block; width:7px; height:7px; border-radius:50%; background:var(--red); margin-right:3px;"></i>Nonaktif: <strong><?= $inactiveCnt ?></strong></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="result-table-wrap">
            <table class="result-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Pengguna</th>
                        <th>Username / Akun</th>
                        <th>Wilayah (Kab/Kota)</th>
                        <th>Level Akun (Tingkat Akses)</th>
                        <th>Status</th>
                        <th>Aksi Status</th>
                        <th style="width:110px;">Aksi</th>
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
                                <span class="badge info" style="font-size:11px; font-weight:600;">
                                    <?= htmlspecialchars($u['kode_wilayah'] ?? '35.10') ?>
                                </span>
                            </td>
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
                            <td>
                                <div class="row-actions">
                                    <button type="button" class="btn-edit-user" data-id="<?= $u['id'] ?>" title="Edit Akun">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </button>
                                    <?php if ((int)$u['id'] !== 1 && $u['username'] !== 'admin'): ?>
                                        <button type="button" class="btn-delete-user danger" data-id="<?= $u['id'] ?>" data-name="<?= htmlspecialchars($u['name']) ?>" title="Hapus">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    <?php endif; ?>
                                </div>
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

    <!-- MODAL TAMBAH AKUN -->
    <div class="modal-backdrop" id="add-user-modal" style="display:none;">
        <div class="modal admin-data-modal" style="max-width:520px;">
            <div class="modal-head">
                <div>
                    <span class="badge info">TAMBAH AKUN</span>
                    <h2>Daftarkan Akun Pengguna Baru</h2>
                    <p>Simpan kredensial dan level akses akun ke tabel database <code>akun</code>.</p>
                </div>
                <button class="icon-button" id="modal-btn-close-add-user">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="form-add-user" method="POST" action="<?= site_url('users/create') ?>">
                <div class="manual-form" style="grid-template-columns: 1fr 1fr;">
                    <label>
                        Nama Pengguna (Username)
                        <input type="text" name="nama_pengguna" id="add-user-username" placeholder="Contoh: analis_bps" required>
                    </label>
                    <label>
                        Kata Sandi (Password)
                        <input type="password" name="kata_sandi" id="add-user-password" placeholder="Minimal 6 karakter" required>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Nama Lengkap & Gelar
                        <input type="text" name="nama_lengkap" id="add-user-fullname" placeholder="Contoh: Rahmat Hidayat, S.E." required>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Alamat Email
                        <input type="email" name="email" id="add-user-email" placeholder="Contoh: rahmat@banyuwangikab.go.id">
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Wilayah Kabupaten / Kota
                        <select name="kode_wilayah" id="add-user-wilayah">
                            <?php if (!empty($kab_kota)): ?>
                                <?php foreach ($kab_kota as $item): ?>
                                    <option value="<?= htmlspecialchars($item['kode']) ?>" <?= ($item['kode'] === '35.10') ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($item['kode']) ?> — <?= htmlspecialchars($item['nama']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="35.10" selected>35.10 — KAB. BANYUWANGI</option>
                            <?php endif; ?>
                        </select>
                    </label>
                    <label>
                        Level Akun (Tingkat Akses)
                        <select name="level" id="add-user-level">
                            <option value="1">1 — Administrator (Akses Penuh)</option>
                            <option value="2">2 — Analis (Analisis & Laporan)</option>
                            <option value="3" selected>3 — Operator (Input & Validasi)</option>
                            <option value="4">4 — Reviewer (Tinjauan Eksekutif)</option>
                        </select>
                    </label>
                    <label>
                        Status Akun
                        <select name="status" id="add-user-status">
                            <option value="Aktif" selected>Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </label>
                </div>

                <div id="add-user-error" class="method-note" style="display:none; color:var(--red); margin-bottom:12px;"></div>

                <div class="modal-actions">
                    <button type="button" class="button ghost" id="modal-btn-cancel-add-user">Batal</button>
                    <button type="submit" class="button primary" id="modal-btn-submit-add-user">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT AKUN -->
    <div class="modal-backdrop" id="edit-user-modal" style="display:none;">
        <div class="modal admin-data-modal" style="max-width:520px;">
            <div class="modal-head">
                <div>
                    <span class="badge warning">PERBARUI AKUN</span>
                    <h2>Edit Akun Pengguna</h2>
                    <p>Perbarui profil, level akses (Level 1–4), status akun, atau atur ulang kata sandi.</p>
                </div>
                <button class="icon-button" id="modal-btn-close-edit-user">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 6 12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="form-edit-user" method="POST">
                <input type="hidden" name="id" id="edit-user-id">
                <div class="manual-form" style="grid-template-columns: 1fr 1fr;">
                    <label style="grid-column: 1 / 3;">
                        Username (Tidak Dapat Diubah)
                        <input type="text" id="edit-user-username" disabled style="background:#f1f5f9;">
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Nama Lengkap & Gelar
                        <input type="text" name="nama_lengkap" id="edit-user-fullname" required>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Alamat Email
                        <input type="email" name="email" id="edit-user-email">
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Wilayah Kabupaten / Kota
                        <select name="kode_wilayah" id="edit-user-wilayah">
                            <?php if (!empty($kab_kota)): ?>
                                <?php foreach ($kab_kota as $item): ?>
                                    <option value="<?= htmlspecialchars($item['kode']) ?>">
                                        <?= htmlspecialchars($item['kode']) ?> — <?= htmlspecialchars($item['nama']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="35.10">35.10 — KAB. BANYUWANGI</option>
                            <?php endif; ?>
                        </select>
                    </label>
                    <label>
                        Level Akun (Tingkat Akses)
                        <select name="level" id="edit-user-level">
                            <option value="1">1 — Administrator (Akses Penuh)</option>
                            <option value="2">2 — Analis (Analisis & Laporan)</option>
                            <option value="3">3 — Operator (Input & Validasi)</option>
                            <option value="4">4 — Reviewer (Tinjauan Eksekutif)</option>
                        </select>
                    </label>
                    <label>
                        Status Akun
                        <select name="status" id="edit-user-status">
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </label>
                    <label style="grid-column: 1 / 3;">
                        Kata Sandi Baru (Kosongkan jika tidak ingin mengubah)
                        <input type="password" name="kata_sandi" id="edit-user-password" placeholder="Kosongkan jika tidak diubah">
                    </label>
                </div>

                <div id="edit-user-error" class="method-note" style="display:none; color:var(--red); margin-bottom:12px;"></div>

                <div class="modal-actions">
                    <button type="button" class="button ghost" id="modal-btn-cancel-edit-user">Batal</button>
                    <button type="submit" class="button primary" id="modal-btn-submit-edit-user">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
