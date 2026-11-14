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

    <article class="card">
        <div class="card-head">
            <div>
                <span class="eyebrow">METODE PROYEKSI STATISTIK</span>
                <h3>Daftar Metode Forecasting Tersedia</h3>
                <p>Metode proyeksi diimplementasikan di PHP model dan visualisasi grafik browser.</p>
            </div>
        </div>

        <div class="result-table-wrap">
            <table class="result-table">
                <thead>
                    <tr>
                        <th>Metode</th>
                        <th>Prinsip Kerja Matematis</th>
                        <th>Formula Inti</th>
                        <th>Karakteristik & Keterbatasan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Tren Linear (OLS)</strong></td>
                        <td>Ekstrapolasi persamaan garis lurus terhadap indeks waktu dengan meminimalkan kuadrat residual.</td>
                        <td><code>y = a + b·t</code></td>
                        <td>Sangat efektif untuk tren jangka menengah stabil; sensitif terhadap outlier struktural (misal kontraksi pandemi 2020).</td>
                    </tr>
                    <tr>
                        <td><strong>Rata-rata Bergerak 3 Tahun (3Y-MA)</strong></td>
                        <td>Menghitung rata-rata dari tiga observasi tahunan terakhir secara berulang untuk horizon berikutnya.</td>
                        <td><code>F(t+1) = (y_t + y_{t-1} + y_{t-2}) / 3</code></td>
                        <td>Meredam fluktuasi jangka pendek, namun cenderung lambat menangkap akselerasi tren yang tajam.</td>
                    </tr>
                    <tr>
                        <td><strong>Pemulusan Eksponensial (SES)</strong></td>
                        <td>Memperbarui level estimasi dengan bobot peluruhan eksponensial (faktor pemulus α = 0,5).</td>
                        <td><code>S_t = α·y_t + (1-α)·S_{t-1}</code></td>
                        <td>Memberikan bobot lebih besar pada data terbaru; cocok untuk deret tanpa tren musiman yang dominan.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="method-note">
            <strong>Catatan Model:</strong> Seluruh rentang indikatif dihitung menggunakan interval kepercayaan ±1,96 × RMSE residual deret in-sample.
        </div>
    </article>
</div>

