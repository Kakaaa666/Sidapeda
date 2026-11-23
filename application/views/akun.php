<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Sistem — Dashboard Ekonomi Daerah Kabupaten Banyuwangi</title>
    <link rel="icon" type="image/svg+xml" href="<?= base_url('assets/img/favicon.svg') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/img/favicon-32.png') ?>">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= base_url('assets/img/favicon-192.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/favicon-192.png') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <style>
        :root {
            --login-bg: #091729;
            --login-card-bg: rgba(255, 255, 255, 0.98);
        }

        body.login-body {
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background: radial-gradient(circle at 15% 15%, #183b66 0%, #0a192f 55%, #050d18 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1a2a3a;
            position: relative;
            overflow-x: hidden;
            padding: 30px 16px;
            box-sizing: border-box;
        }

        /* Ambient Background Highlights */
        .login-glow-1 {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.16) 0%, rgba(14, 165, 233, 0) 70%);
            top: -120px;
            left: -120px;
            border-radius: 50%;
            pointer-events: none;
        }

        .login-glow-2 {
            position: absolute;
            width: 550px;
            height: 550px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.13) 0%, rgba(99, 102, 241, 0) 70%);
            bottom: -150px;
            right: -100px;
            border-radius: 50%;
            pointer-events: none;
        }

        .login-container {
            width: 100%;
            max-width: 470px;
            position: relative;
            z-index: 10;
        }

        .login-card {
            background: var(--login-card-bg);
            border-radius: 20px;
            padding: 38px 36px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.55), 0 0 0 1px rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(18px);
        }

        .brand-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 1px solid #edf2f7;
        }

        .brand-header .brand-logo-img {
            width: auto;
            height: 46px;
            max-height: 48px;
            object-fit: contain;
            border: none;
            background: transparent;
            box-shadow: none;
            flex-shrink: 0;
            filter: drop-shadow(0 2px 5px rgba(21, 53, 92, 0.15));
        }
        .brand-header .logo-box {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #0e5a96, #063158);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 18px rgba(14, 90, 150, 0.28);
            flex-shrink: 0;
        }

        .brand-header .logo-box span {
            display: block;
            width: 5px;
            height: 20px;
            background: #fff;
            margin: 0 2px;
            border-radius: 3px;
        }

        .brand-header .logo-box span:nth-child(2) {
            height: 30px;
            background: #38bdf8;
        }

        .brand-header .logo-box span:nth-child(3) {
            height: 16px;
        }

        .brand-text strong {
            display: block;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #0c2340;
            line-height: 1.2;
        }

        .brand-text small {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 3px;
        }

        .login-title {
            margin: 0 0 6px;
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .login-subtitle {
            margin: 0 0 20px;
            font-size: 13px;
            color: #64748b;
            line-height: 1.5;
        }

        /* Tabs for Akun Masuk / Register */
        .auth-tabs {
            display: flex;
            gap: 6px;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 10px;
            margin-bottom: 22px;
        }

        .auth-tab {
            flex: 1;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 700;
            border: none;
            background: transparent;
            color: #64748b;
            border-radius: 7px;
            cursor: pointer;
            transition: all 0.15s ease;
            text-align: center;
        }

        .auth-tab.active {
            background: #ffffff;
            color: #0369a1;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
        }

        /* Alerts */
        .alert {
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            line-height: 1.4;
        }

        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }

        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #15803d;
        }

        .alert svg {
            flex-shrink: 0;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 7px;
        }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            pointer-events: none;
            display: flex;
            align-items: center;
        }

        .form-input {
            width: 100%;
            height: 46px;
            padding: 0 14px 0 44px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 13.5px;
            color: #0f172a;
            background: #f8fafc;
            box-sizing: border-box;
            transition: all 0.15s ease;
            font-family: inherit;
        }

        .form-input:focus {
            outline: none;
            border-color: #0284c7;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.12);
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-password:hover {
            color: #334155;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
            font-size: 12px;
        }

        .remember-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #475569;
            cursor: pointer;
        }

        .forgot-link {
            color: #0284c7;
            text-decoration: none;
            font-weight: 600;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .btn-submit {
            width: 100%;
            height: 48px;
            border: none;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            box-shadow: 0 8px 20px rgba(2, 132, 199, 0.35);
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(2, 132, 199, 0.42);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .login-footer {
            margin-top: 24px;
            text-align: center;
        }

        .gov-seal {
            font-size: 12.5px;
            color: rgba(255, 255, 255, 0.55);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            line-height: 1.6;
        }
    </style>
</head>
<body class="login-body">

    <div class="login-glow-1"></div>
    <div class="login-glow-2"></div>

    <div class="login-container">
        <div class="login-card">
            
            <div class="brand-header">
                <img src="<?= base_url('assets/img/logo_navy.png') ?>" alt="Logo Dashboard Ekonomi Daerah" class="brand-logo-img">
                <div class="brand-text">
                    <strong>Dashboard</strong>
                    <small>Ekonomi Daerah</small>
                </div>
            </div>

            <h1 class="login-title">Autentikasi Akun</h1>
            <p class="login-subtitle">Silakan masuk dengan akun Anda untuk mengakses sistem intelijen ekonomi.</p>

            <div class="auth-tabs">
                <button type="button" class="auth-tab active" id="tabLoginBtn" onclick="switchAuthTab('login')">Masuk Akun</button>
                <button type="button" class="auth-tab" id="tabRegisterBtn" onclick="switchAuthTab('register')">Daftar Akun Baru</button>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <div><?= htmlspecialchars($error) ?></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <div><?= htmlspecialchars($success) ?></div>
                </div>
            <?php endif; ?>

            <!-- FORM MASUK AKUN -->
            <form action="<?= site_url('akun') ?>" method="post" id="formLogin">
                <input type="hidden" name="action" value="login">

                <div class="form-group">
                    <label class="form-label" for="username">Nama Pengguna / Akun / Email</label>
                    <div class="input-wrap">
                        <span class="input-icon">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                        <input type="text" id="username" name="username" class="form-input" placeholder="Masukkan nama akun atau email..." value="" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Kata Sandi</label>
                    <div class="input-wrap">
                        <span class="input-icon">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                        <input type="password" id="password" name="password" class="form-input" placeholder="Masukkan kata sandi..." value="" required>
                        <button type="button" class="toggle-password" id="btnTogglePassword" aria-label="Lihat kata sandi">
                            <svg id="eyeIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-wrap">
                        <input type="checkbox" name="remember" value="1" checked>
                        <span>Ingat akun di perangkat ini</span>
                    </label>
                    <a href="javascript:void(0)" onclick="alert('Untuk bantuan pemulihan akun silakan hubungi administrator BAPPEDA Kabupaten Banyuwangi.')" class="forgot-link">Lupa sandi?</a>
                </div>

                <button type="submit" class="btn-submit">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    <span>Masuk ke Akun</span>
                </button>
            </form>

            <!-- FORM DAFTAR AKUN BARU (Tersembunyi secara default) -->
            <form action="<?= site_url('akun') ?>" method="post" id="formRegister" style="display: none;">
                <input type="hidden" name="action" value="register">

                <div class="form-group">
                    <label class="form-label" for="reg_name">Nama Lengkap</label>
                    <div class="input-wrap">
                        <span class="input-icon">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                        <input type="text" id="reg_name" name="name" class="form-input" placeholder="Nama lengkap beserta gelar..." required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="reg_username">Nama Akun (Username)</label>
                    <div class="input-wrap">
                        <span class="input-icon">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-3.92 7.94"/></svg>
                        </span>
                        <input type="text" id="reg_username" name="username" class="form-input" placeholder="Pilih nama akun unik..." required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="reg_email">Email Dinas / Pribadi (Opsional)</label>
                    <div class="input-wrap">
                        <span class="input-icon">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        </span>
                        <input type="email" id="reg_email" name="email" class="form-input" placeholder="email@banyuwangikab.go.id">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="reg_password">Kata Sandi Akun</label>
                    <div class="input-wrap">
                        <span class="input-icon">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                        <input type="password" id="reg_password" name="password" class="form-input" placeholder="Minimal 6 karakter..." required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="reg_level">Level Akun Sistem (Angka 1–4)</label>
                    <select id="reg_level" name="level" class="form-input" style="padding-left:14px; padding-right:14px;">
                        <option value="3">3 — Operator Data OPD</option>
                        <option value="2">2 — Analis Ekonomi</option>
                        <option value="1">1 — Administrator</option>
                        <option value="4">4 — Reviewer Pembangunan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="reg_wilayah">Wilayah Kabupaten / Kota</label>
                    <select id="reg_wilayah" name="kode_wilayah" class="form-input" style="padding-left:14px; padding-right:14px;">
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
                </div>

                <button type="submit" class="btn-submit" style="margin-top:20px;">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                    <span>Daftar Akun Baru</span>
                </button>
            </form>

        </div>

        <div class="login-footer">
            <div class="gov-seal">
                SISTEM INFORMASI DATA & ANALISIS PEMBANGUNAN DAERAH<br>
                <strong>BAPPEDA &bull; PEMERINTAH KABUPATEN BANYUWANGI</strong>
            </div>
        </div>
    </div>

    <script>
        // Toggle Password Visibility
        const btnToggle = document.getElementById('btnTogglePassword');
        const passInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        if (btnToggle && passInput) {
            btnToggle.addEventListener('click', function() {
                if (passInput.type === 'password') {
                    passInput.type = 'text';
                    eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
                } else {
                    passInput.type = 'password';
                    eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
                }
            });
        }

        // Switch Tabs between Masuk Akun and Daftar Akun
        function switchAuthTab(tab) {
            const formLogin = document.getElementById('formLogin');
            const formRegister = document.getElementById('formRegister');
            const tabLoginBtn = document.getElementById('tabLoginBtn');
            const tabRegisterBtn = document.getElementById('tabRegisterBtn');

            if (tab === 'register') {
                formLogin.style.display = 'none';
                formRegister.style.display = 'block';
                tabLoginBtn.classList.remove('active');
                tabRegisterBtn.classList.add('active');
            } else {
                formLogin.style.display = 'block';
                formRegister.style.display = 'none';
                tabLoginBtn.classList.add('active');
                tabRegisterBtn.classList.remove('active');
            }
        }
    </script>
</body>
</html>

