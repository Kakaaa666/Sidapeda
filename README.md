# SI-DAPEDA — Sistem Informasi Data & Analisis Pembangunan Daerah

Aplikasi **SI-DAPEDA Kabupaten Banyuwangi** yang dibangun menggunakan **CodeIgniter 3.1.13 + MySQL + PHP 7.4/8.x**.

Sistem ini dirancang untuk memantau intelijen ekonomi daerah, visualisasi spasial tematik tingkat kecamatan, metode analisis regional kuantitatif (*Location Quotient*, *Shift-Share*, *Tipologi Klassen*, Korelasi Pearson), sistem peringatan dini *Early Warning System (EWS)*, generator laporan ekonomi otomatis, serta sistem autentikasi dengan hierarki level akun.

---

## 🚀 Fitur Utama & Struktur Menu

### 1. Public Dashboard
- **Beranda (`/beranda`):** KPI makro ekonomi, tren perbandingan daerah vs Jatim & Nasional, diagram sektoral, EWS donut chart, dan ringkasan proyeksi.
- **Perbandingan Daerah (`/perbandingan_daerah`):** Peringkat regional 8 kab/kota Jawa Timur, benchmarking lintasan historis, dan analisis kesenjangan.
- **Analisis Ekonomi (`/analisis_ekonomi`):** Perhitungan Location Quotient (LQ), Shift Share Analysis (Provincial Growth, Industrial Mix, Regional Shift), Tipologi Klassen kuadran I–IV, dan Korelasi Pearson.
- **Peta Ekonomi (`/peta_ekonomi`):** Peta SVG spasial 12 kecamatan Kabupaten Banyuwangi dengan multi-layer tematik.
- **Prospek Ekonomi (`/prospek_ekonomi`):** Model forecasting berkala (Tren Linear OLS, Rata-rata Bergerak 3 Tahun, Single Exponential Smoothing) dan simulasi skenario kebijakan.
- **Early Warning (`/early_warning`):** Pemantauan status risiko indikator makro (Normal, Waspada, Kritis) dan tindak lanjut monitoring OPD.
- **Economic Insight (`/economic_insight`):** Temuan strategis berkala dan checklist intervensi kebijakan daerah berbasis bukti data.
- **Laporan Ekonomi (`/laporan_ekonomi`):** Generator dokumen laporan analisis terstandarisasi, ekspor cetak/PDF, dan ekspor CSV.

### 2. Panel Pengelolaan & Administrasi
- **Admin Workspace (`/admin`):** Metrik kesehatan data, rasio validasi, dan antrean review dataset.
- **Database Ekonomi (`/database`):** Master rekaman indikator ekonomi dengan pencarian real-time dan filter status validasi.
- **Input Data (`/input`):** Formulir input manual dan import dataset CSV otomatis dengan validasi anti-duplikasi.
- **Validasi Data (`/validation`):** Antrean review verifikasi data oleh tim analis.
- **Katalog Indikator (`/catalog`):** Metadata dan definisi operasional indikator pembangunan.
- **Manajemen EWS (`/ews_admin`):** Konfigurasi ambang batas (*threshold*) indikator peringatan dini.
- **Konfigurasi Proyeksi (`/forecast_admin`):** Parameter matematis model peramalan tren ekonomi.
- **Pengguna & Level (`/users`):** Manajemen akun pengguna, pengaturan hierarki level akses (Level 1–4), dan live audit trail dari tabel `riwayat_masuk`.

---

## 🔐 Sistem Autentikasi & Level Akun

Sistem dilindungi gerbang autentikasi terpusat pada controller **`Akun`** (`/akun`):

| Level | Peran | Hak Akses & Kewenangan |
|:---:|:---|:---|
| **1** | **Administrator** | Akses penuh seluruh sistem, manajemen akun pengguna & ubah level, audit log, konfigurasi EWS & proyeksi |
| **2** | **Analis** | Analisis ekonomi sektoral, input formula forecast, tinjauan insight kebijakan, & generator laporan |
| **3** | **Operator** *(Default)* | Entri data indikator OPD, katalog indikator, & pemantauan data ekonomi daerah |
| **4** | **Reviewer** | Monitoring dashboard publik & evaluasi target capaian pembangunan (*read-only*) |

### Akun Bawaan untuk Pengujian:
- **Administrator (Level 1):** `admin` / `admin123`
- **Analis (Level 2):** `analis` / `analis123`
- **Operator (Level 3):** `operator` / `operator123`

---

## 🗄️ Database & Penamaan Tabel

Nama-nama tabel pada database `sidapeda` disesuaikan secara langsung dengan nama menu sistem:

1. `beranda` — Data kartu ringkasan KPI makro ekonomi
2. `perbandingan_daerah` — Data pertumbuhan kab/kota se-Jawa Timur
3. `analisis_ekonomi` — Data sektoral lapangan usaha & tenaga kerja
4. `peta_ekonomi` — Data spasial 12 kecamatan Kabupaten Banyuwangi
5. `prospek_ekonomi` — Deret waktu historis (2015–2024)
6. `early_warning` — Indikator peringatan dini & ambang batas risiko
7. `economic_insight` — Rekomendasi kebijakan & insight strategis
8. `laporan_ekonomi` — Daftar laporan publikasi analisis ekonomi
9. `database_ekonomi` — Master rekaman indikator ekonomi & validasi
10. `katalog_indikator` — Metadata definisi operasional indikator
11. `akun` — Tabel pengguna sistem beserta kolom `level` integer (1–4)
12. `riwayat_masuk` — Log audit aktivitas masuk & keluar sistem

Skema lengkap dan data awal tersedia di file **`database.sql`**.

---

## 🛠️ Instalasi & Menjalankan Aplikasi

1. Clone repositori ke folder web server Anda (misal XAMPP `htdocs`):
   ```bash
   git clone https://github.com/Kakaaa666/Sidapeda.git c:/xampp/htdocs/Sidapeda
   ```
2. Pastikan Apache dan MySQL aktif pada XAMPP Control Panel.
3. Buat database baru bernama `sidapeda` dan impor skema SQL:
   ```bash
   mysql -u root sidapeda < database.sql
   ```
4. Buka aplikasi melalui browser di:
   ```
   http://localhost/Sidapeda/
   ```
5. Masuk dengan akun `admin` / kata sandi `admin123`.
