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
                    <span class="badge danger">TEMPAT SAMPAH (SOFT DELETE)</span>
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
                        <th style="width:110px;">Aksi CRUD</th>
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
                            <td>
                                <div class="row-actions">
                                    <button type="button" class="btn-edit-user" data-id="<?= $u['id'] ?>" title="Edit Akun">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        <span>Edit</span>
                                    </button>
                                    <?php if ((int)$u['id'] !== 1 && $u['username'] !== 'admin'): ?>
                                        <button type="button" class="btn-delete-user danger" data-id="<?= $u['id'] ?>" data-name="<?= htmlspecialchars($u['name']) ?>" title="Hapus (Soft Delete)">
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
