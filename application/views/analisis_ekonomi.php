<div class="page">
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

    <div class="module-intro">
        <div>
            <span class="eyebrow"><?= $eyebrow ?></span>
            <h1><?= $title ?></h1>
            <p><?= $subtitle ?></p>
        </div>
        <div class="intro-actions">
            <button class="button secondary" id="btn-export-analysis">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12M7 10l5 5 5-5M4 21h16"/></svg>
                <span>Ekspor CSV</span>
            </button>
        </div>
    </div>

    <div class="workspace analysis-workspace">
        <!-- SIDEBAR PANEL -->
        <aside class="analysis-panel">
            <div class="panel-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16l-6 7v6l-4 2v-8L4 5Z"/></svg>
                <strong>Metode Analisis Ekonomi Regional</strong>
            </div>

            <div id="analysis-menu-links">
                <button class="analysis-link active" data-mode="Location Quotient (LQ)">
                    <span>Location Quotient (LQ)</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
                </button>
                <button class="analysis-link" data-mode="Shift Share Analysis">
                    <span>Shift Share Analysis</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
                </button>
                <button class="analysis-link" data-mode="Tipologi Klassen">
                    <span>Tipologi Klassen</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
                </button>
                <button class="analysis-link" data-mode="Analisis Korelasi">
                    <span>Analisis Korelasi</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>

            <div class="divider"></div>
            <span class="eyebrow">DATASET DIGUNAKAN</span>
            <p class="panel-help">Dataset sektoral 7 lapangan usaha dan deret berkala indikator makro 2015–2024 Kabupaten Banyuwangi.</p>

            <button class="text-button" id="btn-toggle-formula">
                <span id="formula-btn-text">Tampilkan Formula</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 9 6 6 6-6"/></svg>
            </button>

            <div class="formula-panel" id="formula-box" style="display:none;">
                LQ = (Tenaga kerja sektor lokal / total lokal) ÷ (Tenaga kerja sektor provinsi / total provinsi).
            </div>
        </aside>

        <!-- MAIN ANALYSIS WORKSPACE -->
        <main class="correlation-workspace">
            <div class="correlation-toolbar">
                <div>
                    <span class="eyebrow">METODE ANALISIS</span>
                    <h2 id="active-method-heading">Location Quotient (LQ)</h2>
                    <p id="active-method-sub">Perhitungan ekonomi regional berbasis database SI-DAPEDA Banyuwangi.</p>
                </div>
            </div>

            <!-- DYNAMIC METHOD CONTENT -->
            <div id="analysis-content-container">
                <?php
                $localTot = 0;
                $provTot = 0;
                foreach ($sectors as $s) {
                    $localTot += (int)($s['tenaga_kerja_kini'] ?? $s['employmentCurrent'] ?? 1);
                    $provTot += (int)($s['tenaga_kerja_provinsi_kini'] ?? $s['provinceEmploymentCurrent'] ?? 1);
                }
                $localTot = $localTot ?: 1;
                $provTot = $provTot ?: 1;
                $basisCount = 0;
                $lqList = [];
                foreach ($sectors as $s) {
                    $lCur = (int)($s['tenaga_kerja_kini'] ?? $s['employmentCurrent'] ?? 1);
                    $pCur = (int)($s['tenaga_kerja_provinsi_kini'] ?? $s['provinceEmploymentCurrent'] ?? 1);
                    $lShare = ($lCur / $localTot) * 100;
                    $pShare = ($pCur / $provTot) * 100;
                    $lq = $pShare > 0 ? ($lShare / $pShare) : 0;
                    $isBasis = ($lq > 1.0);
                    if ($isBasis) $basisCount++;
                    $lqList[] = [
                        'name' => $s['nama_sektor'] ?? $s['name'] ?? '',
                        'short' => $s['nama_pendek'] ?? $s['short'] ?? '',
                        'lShare' => $lShare,
                        'pShare' => $pShare,
                        'lq' => $lq,
                        'isBasis' => $isBasis
                    ];
                }
                $maxLq = !empty($lqList) ? max(array_column($lqList, 'lq')) : 1.5;
                $nonBasis = count($sectors) - $basisCount;
                ?>
                <div class="method-summary-grid">
                    <div class="method-metric"><span>Sektor Basis</span><strong><?= $basisCount ?></strong><small>Nilai LQ > 1.0</small></div>
                    <div class="method-metric"><span>Sektor Nonbasis</span><strong><?= $nonBasis ?></strong><small>Nilai LQ ≤ 1.0</small></div>
                    <div class="method-metric"><span>LQ Tertinggi</span><strong><?= number_format($maxLq, 2, ',', '.') ?></strong><small>Sektor Unggulan</small></div>
                </div>
                <article class="card method-card">
                    <div class="card-head">
                        <div><span class="eyebrow">BARS HASIL PERHITUNGAN</span><h3>Location Quotient per Lapangan Usaha</h3></div>
                    </div>
                    <div class="lq-bars" style="margin: 16px 0;">
                        <?php foreach ($lqList as $r):
                            $w = min(100, round(($r['lq'] / max(2.0, $maxLq)) * 100));
                            $benchmarkX = round((1.0 / max(2.0, $maxLq)) * 100);
                        ?>
                            <div class="lq-bar-row">
                                <span title="<?= htmlspecialchars($r['name']) ?>"><?= htmlspecialchars($r['short']) ?></span>
                                <div class="lq-track">
                                    <i class="<?= $r['isBasis'] ? 'basis' : '' ?>" style="width: <?= $w ?>%"></i>
                                    <b style="left: <?= $benchmarkX ?>%"></b>
                                </div>
                                <strong><?= number_format($r['lq'], 2, ',', '.') ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="result-table-wrap">
                        <table class="result-table">
                            <thead><tr><th>Lapangan Usaha</th><th>Share Lokal</th><th>Share Provinsi</th><th>Nilai LQ</th><th>Klasifikasi</th></tr></thead>
                            <tbody>
                                <?php foreach ($lqList as $r): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($r['name']) ?></strong></td>
                                        <td><?= number_format($r['lShare'], 2, ',', '.') ?>%</td>
                                        <td><?= number_format($r['pShare'], 2, ',', '.') ?>%</td>
                                        <td><strong><?= number_format($r['lq'], 3, ',', '.') ?></strong></td>
                                        <td><span class="badge <?= $r['isBasis'] ? 'success' : 'neutral' ?>"><?= $r['isBasis'] ? 'Basis' : 'Nonbasis' ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
            </div>
        </main>
    </div>


</div>

