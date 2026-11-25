<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) . ' — ' : '' ?>Dashboard Ekonomi Daerah <?= htmlspecialchars($nama_wilayah ?? 'Kabupaten Banyuwangi') ?></title>
    <link rel="shortcut icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="icon" type="image/svg+xml" href="<?= base_url('assets/img/favicon.svg') ?>">
    <link rel="icon" type="image/png" sizes="48x48" href="<?= base_url('assets/img/favicon-48.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/img/favicon-32.png') ?>">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= base_url('assets/img/favicon-192.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/favicon-192.png') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css?v=' . (file_exists(FCPATH . 'assets/css/style.css') ? filemtime(FCPATH . 'assets/css/style.css') : time())) ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/sweetalert2/sweetalert2.min.css') ?>">
    <?php if (isset($active_page) && $active_page === 'peta_ekonomi'): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <?php endif; ?>
    <script src="<?= base_url('assets/vendor/sweetalert2/sweetalert2.all.min.js') ?>"></script>
    <script>
        const BASE_URL = '<?= rtrim(base_url(), '/') ?>';
        const SITE_URL = '<?= rtrim(site_url(), '/') ?>';

        function confirmLogout(event, targetUrl) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            const destination = targetUrl || `${SITE_URL}/logout`;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Konfirmasi Keluar',
                    html: 'Apakah Anda yakin ingin keluar dari sistem <strong>Dashboard Ekonomi Daerah</strong>?',
                    icon: 'question',
                    showCancelButton: true,
                    showDenyButton: false,
                    showCloseButton: false,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Keluar',
                    cancelButtonText: 'Tidak',
                    reverseButtons: false,
                    focusCancel: false
                }).then(result => {
                    if (result && result.isConfirmed) {
                        window.location.href = destination;
                    }
                });
            } else {
                if (confirm('Apakah Anda yakin ingin keluar dari sistem Dashboard Ekonomi Daerah?')) {
                    window.location.href = destination;
                }
            }
            return false;
        }
        window.confirmLogout = confirmLogout;
    </script>
</head>
<body>

<div class="app-shell">
    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-head">
            <a href="<?= site_url() ?>" class="logo" title="Dashboard Ekonomi Daerah">
                <img src="<?= base_url('assets/img/logo_white.png') ?>" alt="Dashboard Ekonomi Daerah" class="logo-icon">
                <div class="logo-text">
                    <strong>Dashboard</strong>
                    <small>Ekonomi Daerah</small>
                </div>
            </a>
        </div>

        <div class="region-chip" title="Wilayah Aktif: <?= htmlspecialchars($nama_wilayah ?? 'Kabupaten Banyuwangi') ?>">
            <span class="crest crest-logo">
                <img src="<?= get_logo_pemda_url($active_kode_wilayah ?? $nama_wilayah ?? '35.10') ?>" alt="Logo <?= htmlspecialchars($nama_wilayah ?? 'Kabupaten Banyuwangi') ?>" class="region-crest-img">
            </span>
            <div>
                <small>Wilayah aktif</small>
                <strong><?= htmlspecialchars($nama_wilayah ?? 'Kab. Banyuwangi') ?></strong>
            </div>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
        </div>

        <nav>
            <div class="nav-label"><?= !empty($is_admin) ? 'PENGELOLAAN SISTEM' : 'PUBLIC DASHBOARD' ?></div>
            
            <?php if (!empty($is_admin)): ?>
                <?php
                $adminNav = array(
                    array('admin', 'Dashboard Admin', '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>', 'admin'),
                    array('database', 'Database Ekonomi', '<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v7c0 1.7 3.6 3 8 3s8-1.3 8-3V5M4 12v7c0 1.7 3.6 3 8 3s8-1.3 8-3v-7"/>', 'database'),
                    array('input', 'Import Data', '<path d="M12 16V4M7 9l5-5 5 5M4 20h16"/>', 'input'),
                    array('validation', 'Validasi Data', '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>', 'validation'),
                    array('catalog', 'Katalog Indikator', '<path d="M6 2h9l5 5v15H6V2Z"/><path d="M14 2v6h6M9 13h8M9 17h8"/>', 'catalog'),
                    array('ews_admin', 'Manajemen EWS', '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="M12 8v5M12 17h.01"/>', 'ews_admin'),
                    array('forecast_admin', 'Konfigurasi Proyeksi', '<path d="M3 17 9 11l4 4 8-9"/><path d="M15 6h6v6"/>', 'forecast_admin'),
                    array('users', 'Pengguna & Level', '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>', 'users')
                );
                $allowedMenusByLevel = array(
                    1 => array('admin', 'database', 'input', 'validation', 'catalog', 'ews_admin', 'forecast_admin', 'users'),
                    2 => array('admin', 'database', 'validation', 'catalog', 'ews_admin', 'forecast_admin'),
                    3 => array('admin', 'database', 'input', 'validation', 'catalog'),
                    4 => array('admin', 'database', 'validation', 'catalog', 'ews_admin')
                );
                $uLevel = (int)($user['level'] ?? 1);
                $allowedList = $allowedMenusByLevel[$uLevel] ?? $allowedMenusByLevel[1];

                foreach ($adminNav as $item):
                    // Saring menu sesuai hierarki hak akses peran pengguna
                    if (!in_array($item[0], $allowedList)) {
                        continue;
                    }
                    $isActive = ($active_page === $item[3]);
                ?>
                    <a href="<?= site_url($item[0]) ?>" class="nav-item <?= $isActive ? 'active' : '' ?>" title="<?= htmlspecialchars($item[1]) ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= $item[2] ?></svg>
                        <span><?= $item[1] ?></span>
                        <?= $isActive ? '<i></i>' : '' ?>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <?php
                $publicNav = array(
                    array('beranda', 'Beranda', '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>', 'beranda'),
                    array('analisis_ekonomi', 'Analisis Ekonomi', '<path d="M4 19V9M10 19V5M16 19v-7M22 19H2"/>', 'analisis_ekonomi'),
                    array('peta_ekonomi', 'Peta Ekonomi', '<path d="m3 6 5-3 8 3 5-3v15l-5 3-8-3-5 3V6Z"/><path d="M8 3v15M16 6v15"/>', 'peta_ekonomi'),
                    array('prospek_ekonomi', 'Prospek Ekonomi', '<path d="M3 17 9 11l4 4 8-9"/><path d="M15 6h6v6"/>', 'prospek_ekonomi'),
                    array('early_warning', 'Early Warning', '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="M12 8v5M12 17h.01"/>', 'early_warning'),
                    array('economic_insight', 'Economic Insight', '<path d="M9 18h6M10 22h4M8.5 15a7 7 0 1 1 7 0c-.9.7-1.3 1.5-1.5 2h-4c-.2-.5-.6-1.3-1.5-2Z"/>', 'economic_insight'),
                    array('laporan_ekonomi', 'Laporan Ekonomi', '<path d="M6 2h9l5 5v15H6V2Z"/><path d="M14 2v6h6M9 13h8M9 17h8"/>', 'laporan_ekonomi')
                );
                foreach ($publicNav as $item):
                    $isActive = ($active_page === $item[3] || ($item[3] === 'analisis_ekonomi' && ($active_page === 'perbandingan_daerah' || $this->uri->segment(1) === 'perbandingan_daerah')));
                ?>
                    <a href="<?= site_url($item[0]) ?>" class="nav-item <?= $isActive ? 'active' : '' ?>" title="<?= htmlspecialchars($item[1]) ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= $item[2] ?></svg>
                        <span><?= $item[1] ?></span>
                        <?= $isActive ? '<i></i>' : '' ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- TOMBOL AKUN / LOGOUT -->
            <div class="sidebar-auth-group">
                <?php if (!empty($user)): ?>
                    <?php if (empty($is_admin)): ?>
                        <a href="<?= site_url('admin') ?>" class="nav-item nav-switch-item" title="Pengelolaan Sistem">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                            <span>Pengelolaan Sistem</span>
                        </a>
                    <?php else: ?>
                        <a href="<?= site_url('beranda') ?>" class="nav-item nav-switch-item" title="Dashboard Publik">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            <span>Dashboard Publik</span>
                        </a>
                    <?php endif; ?>

                    <a href="<?= site_url('logout') ?>" class="nav-item logout-nav-item" title="Keluar (Logout)" onclick="return confirmLogout(event, this.href);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        <span>Keluar (Logout)</span>
                    </a>
                <?php else: ?>
                    <a href="<?= site_url('akun') ?>" class="nav-item login-nav-item" title="Masuk (Login)">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                        <span>Masuk (Login)</span>
                    </a>
                <?php endif; ?>
            </div>
        </nav>

        <div class="sidebar-bottom">
            <div class="data-status">
                <span class="pulse"></span>
                <div>
                    <strong>Dashboard</strong>
                    <small>Ekonomi Daerah</small>
                </div>
            </div>
        </div>
    </aside>

    <div class="sidebar-scrim" id="sidebar-scrim"></div>

    <!-- MAIN SHELL -->
    <div class="main-shell">
        <header class="topbar">
            <div class="topbar-title">
                <button class="icon-button btn-sidebar-toggle" id="btn-open-sidebar" aria-label="Buka/Tutup Menu" title="Sembunyikan / Tampilkan Sidebar">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
                <div>
                    <small><?= !empty($is_admin) ? ('Pusat Pengelolaan Data &bull; ' . htmlspecialchars($nama_wilayah ?? 'Kabupaten Banyuwangi')) : htmlspecialchars($nama_wilayah ?? 'Kabupaten Banyuwangi') ?></small>
                    <strong><?= isset($title) ? htmlspecialchars($title) : 'Perekonomian Daerah' ?></strong>
                </div>
            </div>

            <div class="header-actions">
                <div class="search">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input id="header-search-input" placeholder="Cari indikator, laporan..." autocomplete="off">
                    <kbd>Enter</kbd>
                </div>

                <?php if (!empty($user)): ?>
                    <?php if (!empty($is_admin)): ?>
                        <a href="<?= site_url('beranda') ?>" class="profile" title="Beralih ke Dashboard Publik">
                            <span class="avatar"><?= strtoupper(substr($user['nama_lengkap'] ?? $user['name'] ?? 'AS', 0, 2)) ?></span>
                            <div>
                                <strong><?= htmlspecialchars($user['nama_lengkap'] ?? $user['name'] ?? 'Administrator') ?></strong>
                                <small><?= htmlspecialchars($user['level_label'] ?? $user['level'] ?? 'Level 1') ?> &bull; Ke Publik</small>
                            </div>
                        </a>
                    <?php else: ?>
                        <a href="<?= site_url('admin') ?>" class="profile" title="Masuk ke Pengelolaan Sistem">
                            <span class="avatar"><?= strtoupper(substr($user['nama_lengkap'] ?? $user['name'] ?? 'US', 0, 2)) ?></span>
                            <div>
                                <strong><?= htmlspecialchars($user['nama_lengkap'] ?? $user['name'] ?? 'Pengguna') ?></strong>
                                <small><?= htmlspecialchars($user['level_label'] ?? ('Level ' . ($user['level'] ?? 3))) ?> &bull; Ke Admin</small>
                            </div>
                        </a>
                    <?php endif; ?>

                    <a href="<?= site_url('logout') ?>" class="button button-danger-outline" title="Keluar dari Sistem" onclick="return confirmLogout(event, this.href);">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        <span>Keluar</span>
                    </a>
                <?php else: ?>
                    <a href="<?= site_url('akun') ?>" class="button primary" title="Masuk ke Akun">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                        <span>Masuk (Login)</span>
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <main id="main-content">

