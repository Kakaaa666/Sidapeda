/**
 * SI-DAPEDA — Sistem Informasi Data & Analisis Pembangunan Daerah
 * CodeIgniter 3 Frontend & Interactive Intelligence Engine
 */

(function () {
    'use strict';

    // Global App State
    const AppState = {
        years: [2015, 2016, 2017, 2018, 2019, 2020, 2021, 2022, 2023, 2024],
        series: {
            growth: [5.02, 5.15, 5.28, 5.61, 5.55, -3.58, 4.08, 4.43, 5.03, 5.18],
            eastJavaGrowth: [5.44, 5.46, 5.45, 5.50, 5.52, -2.33, 3.56, 5.34, 4.95, 4.93],
            nationalGrowth: [4.88, 5.03, 5.07, 5.17, 5.02, -2.07, 3.70, 5.31, 5.05, 5.03],
            tpt: [3.20, 3.12, 3.05, 2.94, 3.84, 5.50, 5.10, 4.30, 4.10, 4.03],
            investmentGrowth: [6.1, 7.2, 8.0, 8.8, 9.4, -2.0, 4.2, 8.8, 14.2, 12.42],
            poverty: [9.17, 8.75, 8.64, 8.32, 7.52, 8.06, 7.45, 7.51, 7.34, 7.34],
            inflation: [3.12, 2.71, 3.61, 2.88, 2.94, 1.68, 1.87, 6.12, 2.81, 2.41],
            ipm: [68.11, 68.63, 69.13, 69.64, 70.06, 70.62, 71.26, 72.45, 73.08, 73.79],
            perCapita: [35.2, 37.1, 39.4, 42.8, 45.9, 43.7, 47.6, 53.2, 58.4, 62.4]
        },
        sectors: [],
        regional: [],
        districts: [],
        records: [],
        catalog: [],
        ews: [],
        user: null,
        selectedYear: 2024,
        selectedRegion: 'Kabupaten Banyuwangi',
        loaded: false
    };

    // Helper: Number Formatter
    function formatNum(val, digits = 2) {
        if (!Number.isFinite(val)) return '—';
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: digits,
            maximumFractionDigits: digits
        }).format(val);
    }

    function formatPct(val, digits = 2) {
        return formatNum(val, digits) + '%';
    }

    // Helper: Download CSV
    function downloadCSV(filename, rows) {
        const csvContent = rows.map(r => r.map(c => `"${String(c).replace(/"/g, '""')}"`).join(',')).join('\n');
        const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        setTimeout(() => URL.revokeObjectURL(url), 1000);
    }

    // Mathematical calculations
    function mean(arr) {
        return arr.length ? arr.reduce((a, b) => a + b, 0) / arr.length : 0;
    }

    function pearson(x, y) {
        const n = Math.min(x.length, y.length);
        if (n < 2) return 0;
        const xM = mean(x.slice(0, n));
        const yM = mean(y.slice(0, n));
        let num = 0, denX = 0, denY = 0;
        for (let i = 0; i < n; i++) {
            const xd = x[i] - xM;
            const yd = y[i] - yM;
            num += xd * yd;
            denX += xd * xd;
            denY += yd * yd;
        }
        const den = Math.sqrt(denX) * Math.sqrt(denY);
        return den === 0 ? 0 : num / den;
    }

    function corrStrength(r) {
        const a = Math.abs(r);
        if (a >= 0.8) return 'Sangat kuat';
        if (a >= 0.6) return 'Kuat';
        if (a >= 0.4) return 'Sedang';
        if (a >= 0.2) return 'Lemah';
        return 'Sangat lemah';
    }

    function forecast(values, horizon = 3, method = 'Tren Linear') {
        const safe = values.length ? [...values] : [0];
        const preds = [];
        let rmse = 0.25;

        if (method === 'Rata-rata Bergerak 3 Tahun') {
            let hist = [...safe];
            for (let i = 0; i < horizon; i++) {
                const next = mean(hist.slice(-3));
                preds.push(next);
                hist.push(next);
            }
            if (safe.length > 3) {
                const errs = safe.slice(3).map((v, i) => v - mean(safe.slice(i, i + 3)));
                rmse = Math.sqrt(mean(errs.map(e => e * e))) || 0.25;
            }
        } else if (method === 'Pemulusan Eksponensial') {
            const alpha = 0.5;
            let level = safe[0];
            const errs = [];
            for (let i = 1; i < safe.length; i++) {
                errs.push(safe[i] - level);
                level = alpha * safe[i] + (1 - alpha) * level;
            }
            for (let i = 0; i < horizon; i++) preds.push(level);
            rmse = Math.sqrt(mean(errs.map(e => e * e))) || 0.25;
        } else {
            // Linear Trend Fit
            const n = safe.length;
            const x = safe.map((_, i) => i);
            const xM = mean(x);
            const yM = mean(safe);
            const num = x.reduce((t, xi, i) => t + (xi - xM) * (safe[i] - yM), 0);
            const den = x.reduce((t, xi) => t + Math.pow(xi - xM, 2), 0) || 1;
            const slope = num / den;
            const intercept = yM - slope * xM;
            const residuals = safe.map((v, i) => v - (intercept + slope * i));
            rmse = Math.sqrt(mean(residuals.map(r => r * r))) || 0.25;

            for (let i = 0; i < horizon; i++) {
                preds.push(intercept + slope * (n + i));
            }
        }

        const lower = preds.map((p, idx) => p - 1.96 * rmse * Math.sqrt(1 + idx * 0.35));
        const upper = preds.map((p, idx) => p + 1.96 * rmse * Math.sqrt(1 + idx * 0.35));

        return { forecast: preds, lower, upper, method, error: rmse };
    }

    // Chart SVG Renderers
    function createHorizontalBarChartSVG(items, opts = {}) {
        if (!items || !items.length) {
            return `<div class="table-empty" style="padding:16px; text-align:center; color:var(--muted); font-size:11px;">Belum ada data untuk grafik.</div>`;
        }
        const unit = opts.unit || '%';
        const showRank = opts.showRank !== false;
        const vals = items.map(d => Number(d.value)).filter(Number.isFinite);
        const maxVal = opts.maxVal !== undefined ? opts.maxVal : (Math.max(0, ...vals) || 1);
        const minVal = Math.min(0, ...vals);
        const range = maxVal - minVal || 1;
        const zeroPct = minVal < 0 ? ((0 - minVal) / range) * 100 : 0;

        let rowsHtml = '';
        items.forEach((item, idx) => {
            const v = Number(item.value);
            const isPos = v >= 0;
            const w = Math.min(100, Math.max(1, (Math.abs(v) / range) * 100));
            const leftPct = minVal < 0 ? (isPos ? zeroPct : zeroPct - w) : 0;
            const isHighlight = item.isHighlight;
            const bgGradient = item.color || (isHighlight ? 'linear-gradient(90deg, #0284c7, #06b6d4)' : (isPos ? 'linear-gradient(90deg, #2463a6, #38bdf8)' : 'linear-gradient(90deg, #ef4444, #f87171)'));
            const borderStyle = isHighlight ? 'background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px;' : 'background: #ffffff; border: 1px solid #f1f5f9; border-radius: 8px;';

            rowsHtml += `
                <div class="h-bar-row ${isHighlight ? 'highlighted' : ''}" data-index="${idx}" data-label="${item.label}" style="display:grid; grid-template-columns: ${showRank ? '28px ' : ''}160px 1fr 105px; align-items:center; gap:10px; padding:7px 10px; margin:4px 0; font-size:11px; ${borderStyle} cursor:pointer; transition: all 0.2s ease;">
                    ${showRank ? `<span class="badge ${isHighlight ? 'primary' : 'neutral'}" style="font-size:9px; padding:2px 4px; text-align:center; font-weight:700;">#${idx + 1}</span>` : ''}
                    <div style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="${item.label}">
                        <strong style="color:var(--navy); font-size:11px;">${item.label}</strong>
                        ${item.subLabel ? `<br><small style="color:var(--muted); font-size:9px;">${item.subLabel}</small>` : ''}
                    </div>
                    <div style="position:relative; height:10px; background:#e2e8f0; border-radius:6px; overflow:hidden;">
                        ${minVal < 0 ? `<div style="position:absolute; left:${zeroPct}%; top:0; bottom:0; width:1px; background:#64748b; z-index:2;"></div>` : ''}
                        <div style="position:absolute; left:${leftPct}%; width:${w}%; top:0; bottom:0; background:${bgGradient}; border-radius:6px; transition:width 0.4s ease;"></div>
                    </div>
                    <div style="text-align:right;">
                        <strong style="color:${isHighlight ? 'var(--blue)' : 'var(--navy)'}; font-size:11px;">${formatNum(v, opts.decimals ?? 2)}${unit}</strong>
                        ${item.badge ? `<br><span class="badge ${item.badgeClass || 'info'}" style="font-size:8px; padding:1px 5px; font-weight:600;">${item.badge}</span>` : ''}
                    </div>
                </div>`;
        });

        return `<div class="horizontal-bar-chart" style="width:100%; display:flex; flex-direction:column; gap:3px;">${rowsHtml}</div>`;
    }

    function createLineChartSVG(years, series, height = 230, unit = '%') {
        const allVals = series.flatMap(s => s.values).filter(Number.isFinite);
        if (!allVals.length || !years.length) {
            return `<div class="table-empty">Belum ada observasi untuk grafik.</div>`;
        }

        const min = Math.min(0, ...allVals);
        const max = Math.max(...allVals);
        const range = max - min || 1;
        const left = 48, right = 620, top = 20, bottom = height - 32;

        const x = i => left + (years.length <= 1 ? 0 : (i / (years.length - 1)) * (right - left));
        const y = v => bottom - ((v - min) / range) * (bottom - top);

        let gridLines = '';
        [0, 0.25, 0.5, 0.75, 1].forEach(frac => {
            const v = min + range * frac;
            const yPos = bottom - frac * (bottom - top);
            gridLines += `
                <g>
                    <line x1="${left}" x2="${right}" y1="${yPos}" y2="${yPos}" class="gridline"/>
                    <text x="2" y="${yPos + 3}">${formatNum(v, 1)}${unit}</text>
                </g>`;
        });

        let polylines = '';
        series.forEach((s, sIdx) => {
            const pts = s.values.map((v, i) => `${x(i)},${y(v)}`).join(' ');
            const circles = s.values.map((v, i) =>
                `<circle cx="${x(i)}" cy="${y(v)}" r="3.5" class="dynamic-point ${s.className || 'series-' + sIdx}">
                    <title>${years[i]}: ${formatNum(v, 2)}${unit}</title>
                 </circle>`
            ).join('');

            polylines += `
                <g>
                    <polyline points="${pts}" class="dynamic-line ${s.className || 'series-' + sIdx}"/>
                    ${circles}
                </g>`;
        });

        const xLabels = years.map((yr, i) =>
            `<text x="${x(i)}" y="${height - 8}" text-anchor="middle">${String(yr).slice(-2)}</text>`
        ).join('');

        const legend = series.map((s, idx) =>
            `<span><i class="${s.className || 'series-' + idx}"></i>${s.name}</span>`
        ).join('');

        return `
            <svg class="line-chart" viewBox="0 0 640 ${height}" preserveAspectRatio="none" role="img">
                ${gridLines}
                ${polylines}
                ${xLabels}
            </svg>
            <div class="dynamic-chart-legend">${legend}</div>`;
    }

    function createScatterPlotSVG(xs, ys, xLabel, yLabel, years) {
        const count = Math.min(xs.length, ys.length);
        if (count < 2) return `<div class="table-empty">Data tidak mencukupi untuk scatter plot.</div>`;

        const subX = xs.slice(0, count);
        const subY = ys.slice(0, count);
        const xMin = Math.min(...subX), xMax = Math.max(...subX);
        const yMin = Math.min(...subY), yMax = Math.max(...subY);
        const xR = xMax - xMin || 1;
        const yR = yMax - yMin || 1;

        const px = v => 50 + ((v - xMin) / xR) * 590;
        const py = v => 295 - ((v - yMin) / yR) * 250;

        const r = pearson(subX, subY);
        const xM = mean(subX);
        const yM = mean(subY);
        let num = 0, den = 0;
        for (let i = 0; i < count; i++) {
            num += (subX[i] - xM) * (subY[i] - yM);
            den += Math.pow(subX[i] - xM, 2);
        }
        const slope = den !== 0 ? num / den : 0;
        const intercept = yM - slope * xM;

        let pointsSVG = '';
        subX.forEach((v, i) => {
            pointsSVG += `
                <g class="scatter-point">
                    <circle cx="${px(v)}" cy="${py(subY[i])}" r="6">
                        <title>${years[i] || i + 1}: ${formatNum(v)}; ${formatNum(subY[i])}</title>
                    </circle>
                </g>`;
        });

        return `
            <svg viewBox="0 0 700 360" preserveAspectRatio="none" role="img">
                <g class="correlation-grid">
                    ${[45, 100, 155, 210, 265, 320].map(y => `<line x1="48" x2="660" y1="${y}" y2="${y}"/>`).join('')}
                    ${[60, 160, 260, 360, 460, 560, 660].map(x => `<line x1="${x}" x2="${x}" y1="30" y2="320"/>`).join('')}
                </g>
                <line class="axis" x1="48" x2="660" y1="320" y2="320"/>
                <line class="axis" x1="48" x2="48" y1="30" y2="320"/>
                <line class="regression ${r >= 0 ? 'positive-line' : ''}" x1="${px(xMin)}" y1="${py(intercept + slope * xMin)}" x2="${px(xMax)}" y2="${py(intercept + slope * xMax)}"/>
                ${pointsSVG}
                <text class="axis-label" x="355" y="350" text-anchor="middle">${xLabel}</text>
                <text class="axis-label y-label" x="-175" y="15" text-anchor="middle" transform="rotate(-90)">${yLabel}</text>
                <text class="axis-label" x="650" y="20" text-anchor="end">r = ${formatNum(r, 3)}</text>
            </svg>`;
    }

    function createKlassenSVG(rows) {
        if (!rows.length) return `<div class="table-empty">Belum ada sektor untuk Klassen.</div>`;

        const left = 76, right = 570, top = 28, bottom = 260;
        const xVals = rows.map(r => r.growthGap);
        const yVals = rows.map(r => r.shareGap);

        const xMinRaw = Math.min(0, ...xVals), xMaxRaw = Math.max(0, ...xVals);
        const yMinRaw = Math.min(0, ...yVals), yMaxRaw = Math.max(0, ...yVals);
        const xPad = (xMaxRaw - xMinRaw || 1) * 0.15;
        const yPad = (yMaxRaw - yMinRaw || 1) * 0.15;
        const xMin = xMinRaw - xPad, xMax = xMaxRaw + xPad;
        const yMin = yMinRaw - yPad, yMax = yMaxRaw + yPad;

        const xScale = v => left + ((v - xMin) / (xMax - xMin)) * (right - left);
        const yScale = v => bottom - ((v - yMin) / (yMax - yMin)) * (bottom - top);

        const zeroX = xScale(0);
        const zeroY = yScale(0);

        let pointsSVG = '';
        rows.forEach((item, idx) => {
            const cx = xScale(item.growthGap);
            const cy = yScale(item.shareGap);
            const labelY = cy + (idx % 2 === 0 ? -9 : 15);
            pointsSVG += `
                <g class="klassen-point q-${item.quadrant.toLowerCase()}">
                    <circle cx="${cx}" cy="${cy}" r="5">
                        <title>${item.name}: Gap Pangsa ${formatNum(item.shareGap)} pp, Gap Pertumbuhan ${formatNum(item.growthGap)} pp</title>
                    </circle>
                    <text x="${cx + 8}" y="${labelY}">${item.short}</text>
                </g>`;
        });

        return `
            <svg class="klassen-chart" viewBox="0 0 650 325" role="img">
                <rect x="${zeroX}" y="${top}" width="${right - zeroX}" height="${zeroY - top}" class="klassen-quadrant-fill q-i" />
                <rect x="${left}" y="${top}" width="${zeroX - left}" height="${zeroY - top}" class="klassen-quadrant-fill q-ii" />
                <rect x="${left}" y="${zeroY}" width="${zeroX - left}" height="${bottom - zeroY}" class="klassen-quadrant-fill q-iii" />
                <rect x="${zeroX}" y="${zeroY}" width="${right - zeroX}" height="${bottom - zeroY}" class="klassen-quadrant-fill q-iv" />

                <line x1="${zeroX}" x2="${zeroX}" y1="${top}" y2="${bottom}" class="klassen-zero-axis" />
                <line x1="${left}" x2="${right}" y1="${zeroY}" y2="${zeroY}" class="klassen-zero-axis" />

                <text x="${zeroX + 7}" y="${top + 13}" class="klassen-quadrant-label">KUADRAN I</text>
                <text x="${left + 7}" y="${top + 13}" class="klassen-quadrant-label">KUADRAN II</text>
                <text x="${left + 7}" y="${bottom - 8}" class="klassen-quadrant-label">KUADRAN III</text>
                <text x="${zeroX + 7}" y="${bottom - 8}" class="klassen-quadrant-label">KUADRAN IV</text>

                ${pointsSVG}

                <text x="${(left + right) / 2}" y="315" text-anchor="middle" class="klassen-axis-label">Gap pertumbuhan sektor terhadap agregat Banyuwangi (pp)</text>
                <text x="-145" y="16" text-anchor="middle" transform="rotate(-90)" class="klassen-axis-label">Gap pangsa sektor terhadap rata-rata lokal (pp)</text>
            </svg>
            <div class="klassen-chart-note">Titik di atas garis horizontal = pangsa di atas rata-rata. Titik di kanan garis vertikal = pertumbuhan di atas ambang daerah.</div>`;
    }

    function createShiftWaterfallSVG(comp) {
        const deltas = [
            { label: ['Efek pertumbuhan', 'provinsi'], value: comp.provincialGrowthEffect },
            { label: ['Efek bauran', 'industri'], value: comp.industryMix },
            { label: ['Efek daya saing', 'lokal'], value: comp.regionalShift }
        ];

        let cum = 0;
        const steps = deltas.map(d => {
            const start = cum;
            cum += d.value;
            return { ...d, start, end: cum };
        });

        const bars = [...steps, { label: ['Perubahan', 'aktual'], value: comp.actualChange, start: 0, end: comp.actualChange }];
        const vals = [0, ...bars.flatMap(b => [b.start, b.end])];
        const min = Math.min(0, ...vals);
        const max = Math.max(0, ...vals);
        const range = max - min || 1;

        const left = 44, right = 620, top = 24, bottom = 184;
        const y = v => bottom - ((v - min) / range) * (bottom - top);
        const positions = [86, 222, 358, 494];
        const barWidth = 70;

        let barsSVG = '';
        bars.forEach((b, i) => {
            const topY = Math.min(y(b.start), y(b.end));
            const h = Math.max(2, Math.abs(y(b.start) - y(b.end)));
            const x = positions[i];
            const cls = (i === 3) ? 'shift-total-bar' : (b.value >= 0 ? 'shift-positive-bar' : 'shift-negative-bar');

            let connector = '';
            if (i > 0 && i < 3) {
                connector = `<line x1="${positions[i - 1] + barWidth}" x2="${x}" y1="${y(steps[i - 1].end)}" y2="${y(b.start)}" class="shift-connector"/>`;
            }

            barsSVG += `
                <g>
                    ${connector}
                    <rect x="${x}" y="${topY}" width="${barWidth}" height="${h}" rx="4" class="${cls}"/>
                    <text x="${x + barWidth / 2}" y="${topY - 7}" text-anchor="middle" class="shift-value-label">${b.value >= 0 ? '+' : ''}${formatNum(b.value, 0)}</text>
                    ${b.label.map((line, lIdx) => `<text x="${x + barWidth / 2}" y="${bottom + 20 + lIdx * 12}" text-anchor="middle" class="shift-category-label">${line}</text>`).join('')}
                </g>`;
        });

        return `
            <svg class="shift-waterfall" viewBox="0 0 650 260" role="img">
                <line x1="${left}" x2="${right}" y1="${y(0)}" y2="${y(0)}" class="shift-zero-line"/>
                ${barsSVG}
            </svg>
            <div class="method-note">Identitas dekomposisi: Efek Pertumbuhan Provinsi + Efek Bauran Industri + Efek Daya Saing Lokal = Perubahan Aktual Tenaga Kerja.</div>`;
    }

    function createForecastChartSVG(history, forecastVals, lower, upper, years) {
        const vals = [...history, ...forecastVals, ...lower, ...upper].filter(Number.isFinite);
        const min = Math.min(...vals), max = Math.max(...vals);
        const range = max - min || 1;
        const left = 38, right = 620, top = 20, bottom = 220;

        const x = i => left + (i / Math.max(1, years.length - 1)) * (right - left);
        const y = v => bottom - ((v - min) / range) * (bottom - top);

        const histPts = history.map((v, i) => `${x(i)},${y(v)}`).join(' ');
        const forePts = [
            `${x(history.length - 1)},${y(history[history.length - 1])}`,
            ...forecastVals.map((v, i) => `${x(history.length + i)},${y(v)}`)
        ].join(' ');

        const upperPts = upper.map((v, i) => `${x(history.length + i)},${y(v)}`).join(' ');
        const lowerPts = [...lower].reverse().map((v, rIdx) => {
            const i = lower.length - rIdx - 1;
            return `${x(history.length + i)},${y(v)}`;
        }).join(' ');

        const grid = [0, 0.25, 0.5, 0.75, 1].map(frac => {
            const v = min + range * frac;
            const yP = bottom - frac * (bottom - top);
            return `<line x1="${left}" x2="${right}" y1="${yP}" y2="${yP}" class="gridline"/><text x="0" y="${yP + 3}">${formatNum(v, 1)}</text>`;
        }).join('');

        const yrTexts = years.map((yr, i) =>
            `<text x="${x(i)}" y="242" text-anchor="middle">${yr}</text>`
        ).join('');

        const foreCircles = forecastVals.map((v, i) =>
            `<circle cx="${x(history.length + i)}" cy="${y(v)}" r="3.5" class="dynamic-point series-1">
                <title>${years[history.length + i]}: ${formatNum(v, 2)}</title>
             </circle>`
        ).join('');

        return `
            <svg class="line-chart" viewBox="0 0 640 250" preserveAspectRatio="none">
                <g>${grid}</g>
                <polygon points="${upperPts} ${lowerPts}" class="forecast-band"/>
                <polyline points="${histPts}" class="dynamic-line series-0"/>
                <polyline points="${forePts}" class="dynamic-line series-1"/>
                <line x1="${x(history.length - 1)}" x2="${x(history.length - 1)}" y1="${top}" y2="${bottom}" class="forecast-divider"/>
                ${yrTexts}
                ${foreCircles}
            </svg>
            <div class="dynamic-chart-legend">
                <span><i class="series-0"></i>Historis</span>
                <span><i class="series-1"></i>Proyeksi</span>
                <span><i class="forecast-interval-key"></i>Rentang Indikatif (±1,96 RMSE)</span>
            </div>`;
    }

    // Initialize UI on DOM Ready (robust check against already-loaded state)
    function runInit() {
        initNavigation();
        initDataLoad();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runInit);
    } else {
        runInit();
    }

    // Navigation & Global Events
    function initNavigation() {
        const btnOpen = document.getElementById('btn-open-sidebar');
        const btnClose = document.getElementById('btn-close-sidebar');
        const sidebar = document.getElementById('sidebar');
        const scrim = document.getElementById('sidebar-scrim');

        if (btnOpen && sidebar && scrim) {
            btnOpen.addEventListener('click', () => {
                sidebar.classList.add('mobile-open');
                scrim.style.display = 'block';
            });
        }

        const closeSidebar = () => {
            if (sidebar) sidebar.classList.remove('mobile-open');
            if (scrim) scrim.style.display = 'none';
        };

        if (btnClose) btnClose.addEventListener('click', closeSidebar);
        if (scrim) scrim.addEventListener('click', closeSidebar);

        // Global Search
        const searchInput = document.getElementById('header-search-input');
        if (searchInput) {
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && this.value.trim()) {
                    const q = this.value.toLowerCase().trim();
                    let target = 'beranda';
                    if (q.includes('peta') || q.includes('spasial') || q.includes('kecamatan')) target = 'peta_ekonomi';
                    else if (q.includes('banding') || q.includes('ranking') || q.includes('benchmark')) target = 'perbandingan_daerah';
                    else if (q.includes('proyeksi') || q.includes('forecast') || q.includes('skenario')) target = 'prospek_ekonomi';
                    else if (q.includes('peringatan') || q.includes('risiko') || q.includes('ews')) target = 'early_warning';
                    else if (q.includes('laporan') || q.includes('report') || q.includes('pdf')) target = 'laporan_ekonomi';
                    else if (q.includes('insight') || q.includes('rekomendasi')) target = 'economic_insight';
                    else if (q.includes('analisis') || q.includes('korelasi') || q.includes('shift') || q.includes('lq') || q.includes('klassen')) target = 'analisis_ekonomi';
                    else if (q.includes('admin') || q.includes('database') || q.includes('validasi')) target = 'database';

                    window.location.href = SITE_URL + '/' + target;
                }
            });
        }
    }

    // Load Central Data from CI 3 API
    function initDataLoad() {
        fetch(BASE_URL + 'api/get_data')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    AppState.years = data.years || AppState.years;
                    AppState.series = data.series || AppState.series;
                    AppState.sectors = data.sectors || [];
                    AppState.regional = data.regional || [];
                    AppState.districts = data.districts || [];
                    AppState.records = data.records || [];
                    AppState.catalog = data.catalog || [];
                    AppState.ews = data.ews || [];
                    AppState.user = data.user || null;
                    AppState.loaded = true;

                    // Initialize Page-Specific Logic
                    dispatchPageLogic();
                }
            })
            .catch(err => {
                console.error('Gagal memuat API state, menggunakan fallback internal:', err);
                dispatchPageLogic();
            });
    }

    function dispatchPageLogic() {
        const path = window.location.pathname.toLowerCase();

        if (document.getElementById('overview-line-chart-container') || document.getElementById('btn-open-add-beranda')) {
            initOverviewPage();
        } else if (document.getElementById('comp-feature-tabs') || document.getElementById('form-add-regional')) {
            initComparisonPage();
        } else if (document.getElementById('analysis-menu-links') || document.getElementById('form-add-sector')) {
            initAnalysisPage();
        } else if (document.getElementById('map-canvas-container') || document.getElementById('form-add-district')) {
            initMapPage();
        } else if (document.getElementById('btn-mode-forecast') || document.getElementById('form-add-prospek')) {
            initOutlookPage();
        } else if (document.getElementById('ews-stats-container') || document.getElementById('form-add-ews')) {
            initEwsPage();
        } else if (document.getElementById('insight-modal-backdrop') || document.getElementById('form-add-insight')) {
            initInsightPage();
        } else if (document.getElementById('printable-report') || document.getElementById('form-add-report')) {
            initReportPage();
        } else if (document.getElementById('admin-table-body')) {
            initAdminDatabasePage();
        } else if (document.getElementById('csv-file-input')) {
            initAdminInputPage();
        } else if (document.getElementById('validation-table-body')) {
            initAdminValidationPage();
        } else if (document.getElementById('threshold-admin-rows')) {
            initAdminEwsPage();
        } else if (document.getElementById('form-add-catalog') || document.getElementById('btn-export-catalog-csv')) {
            initAdminCatalogPage();
        } else if (document.querySelector('.user-role-select') || document.getElementById('form-add-user')) {
            initAdminUsersPage();
        }
    }

    // ==========================================
    // GENERIC MODULE CRUD & SOFT DELETE HANDLER
    // ==========================================
    function setupModuleCrud(cfg) {
        const addModal = document.getElementById(cfg.addModalId);
        const editModal = document.getElementById(cfg.editModalId);
        const formAdd = document.getElementById(cfg.formAddId);
        const formEdit = document.getElementById(cfg.formEditId);
        const addErr = cfg.addErrorId ? document.getElementById(cfg.addErrorId) : null;
        const editErr = cfg.editErrorId ? document.getElementById(cfg.editErrorId) : null;

        // Open Add Modal
        (cfg.openAddBtnIds || []).forEach(id => {
            const btn = document.getElementById(id);
            if (btn && addModal) {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (addErr) { addErr.style.display = 'none'; addErr.textContent = ''; }
                    addModal.style.display = 'grid';
                });
            }
        });

        // Close Add Modal
        (cfg.closeAddBtnIds || []).forEach(id => {
            const btn = document.getElementById(id);
            if (btn && addModal) {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    addModal.style.display = 'none';
                });
            }
        });
        if (addModal) {
            addModal.addEventListener('click', e => {
                if (e.target === addModal) addModal.style.display = 'none';
            });
        }

        // Close Edit Modal
        (cfg.closeEditBtnIds || []).forEach(id => {
            const btn = document.getElementById(id);
            if (btn && editModal) {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    editModal.style.display = 'none';
                });
            }
        });
        if (editModal) {
            editModal.addEventListener('click', e => {
                if (e.target === editModal) editModal.style.display = 'none';
            });
        }

        // Submit Add Form
        if (formAdd) {
            formAdd.addEventListener('submit', function (e) {
                e.preventDefault();
                if (addErr) { addErr.style.display = 'none'; addErr.textContent = ''; }
                const fd = new FormData(formAdd);
                fetch(`${SITE_URL}/${cfg.controller}/create`, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.status === 'success') {
                            location.reload();
                        } else {
                            if (addErr) {
                                addErr.style.display = 'block';
                                addErr.textContent = data.message || 'Gagal menyimpan data.';
                            } else {
                                alert(data.message || 'Gagal menyimpan data.');
                            }
                        }
                    })
                    .catch(() => {
                        formAdd.submit();
                    });
            });
        }

        // Submit Edit Form
        if (formEdit) {
            formEdit.addEventListener('submit', function (e) {
                e.preventDefault();
                if (editErr) { editErr.style.display = 'none'; editErr.textContent = ''; }
                const fd = new FormData(formEdit);
                const id = fd.get('id') || (formEdit.querySelector('input[name="id"]') ? formEdit.querySelector('input[name="id"]').value : '');
                fetch(`${SITE_URL}/${cfg.controller}/update/${id}`, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.status === 'success') {
                            location.reload();
                        } else {
                            if (editErr) {
                                editErr.style.display = 'block';
                                editErr.textContent = data.message || 'Gagal memperbarui data.';
                            } else {
                                alert(data.message || 'Gagal memperbarui data.');
                            }
                        }
                    })
                    .catch(() => {
                        formEdit.submit();
                    });
            });
        }

        // Bind Row Buttons
        function bindButtons() {
            // Edit
            if (cfg.editBtnClass) {
                document.querySelectorAll('.' + cfg.editBtnClass).forEach(btn => {
                    btn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        const id = this.getAttribute('data-id');
                        if (!id) return;
                        if (editErr) { editErr.style.display = 'none'; editErr.textContent = ''; }
                        fetch(`${SITE_URL}/${cfg.controller}/get_data/${id}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                            .then(r => r.json())
                            .then(res => {
                                if (res.status === 'success' && res.data) {
                                    const preFillFn = cfg.onEditPreFill || cfg.populateForm;
                                    if (typeof preFillFn === 'function') {
                                        preFillFn(res.data);
                                    }
                                    if (editModal) editModal.style.display = 'grid';
                                } else {
                                    alert(res.message || 'Gagal memuat data.');
                                }
                            })
                            .catch(() => alert('Gagal mengambil data dari server.'));
                    });
                });
            }

            // Soft delete
            if (cfg.deleteBtnClass) {
                document.querySelectorAll('.' + cfg.deleteBtnClass).forEach(btn => {
                    btn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        const id = this.getAttribute('data-id');
                        const name = this.getAttribute('data-name') || 'item ini';
                        if (!id) return;
                        if (confirm(`Pindahkan "${name}" ke tempat sampah (Soft Delete)?\n\nData tidak akan dihapus permanen dan dapat dipulihkan sewaktu-waktu dari Tempat Sampah.`)) {
                            fetch(`${SITE_URL}/${cfg.controller}/soft_delete/${id}`, {
                                method: 'POST',
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            })
                                .then(r => r.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        location.reload();
                                    } else {
                                        alert(data.message || 'Gagal memproses soft delete.');
                                    }
                                })
                                .catch(() => {
                                    window.location.href = `${SITE_URL}/${cfg.controller}/soft_delete/${id}`;
                                });
                        }
                    });
                });
            }

            // Restore
            if (cfg.restoreBtnClass) {
                document.querySelectorAll('.' + cfg.restoreBtnClass).forEach(btn => {
                    btn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        const id = this.getAttribute('data-id');
                        const name = this.getAttribute('data-name') || 'item ini';
                        if (!id) return;
                        if (confirm(`Pulihkan "${name}" kembali ke daftar aktif?`)) {
                            fetch(`${SITE_URL}/${cfg.controller}/restore/${id}`, {
                                method: 'POST',
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            })
                                .then(r => r.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        location.reload();
                                    } else {
                                        alert(data.message || 'Gagal memulihkan data.');
                                    }
                                })
                                .catch(() => {
                                    window.location.href = `${SITE_URL}/${cfg.controller}/restore/${id}`;
                                });
                        }
                    });
                });
            }

            // Hard delete
            if (cfg.hardDeleteBtnClass) {
                document.querySelectorAll('.' + cfg.hardDeleteBtnClass).forEach(btn => {
                    btn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        const id = this.getAttribute('data-id');
                        const name = this.getAttribute('data-name') || 'item ini';
                        if (!id) return;
                        if (confirm(`PERINGATAN: Apakah Anda yakin ingin MENGHAPUS PERMANEN "${name}"?\n\nData yang dihapus permanen akan hilang selamanya dari database dan TIDAK DAPAT DIPULIHKAN LAGI!`)) {
                            fetch(`${SITE_URL}/${cfg.controller}/hard_delete/${id}`, {
                                method: 'POST',
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            })
                                .then(r => r.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        location.reload();
                                    } else {
                                        alert(data.message || 'Gagal menghapus permanen.');
                                    }
                                })
                                .catch(() => {
                                    window.location.href = `${SITE_URL}/${cfg.controller}/hard_delete/${id}`;
                                });
                        }
                    });
                });
            }
        }

        bindButtons();
        return { bindButtons };
    }

    // 1. OVERVIEW PAGE
    function initOverviewPage() {
        const chartContainer = document.getElementById('overview-line-chart-container');
        const filterYear = document.getElementById('filter-year');
        const filterRegion = document.getElementById('filter-region');
        const btnApply = document.getElementById('btn-apply-filters');
        const indSelect = document.getElementById('overview-indicator-select');
        const chartTitle = document.getElementById('overview-chart-title');
        const chartDesc = document.getElementById('overview-chart-desc');
        const legend0 = document.getElementById('legend-label-0');
        const legend1 = document.getElementById('legend-label-1');
        const legend2 = document.getElementById('legend-label-2');

        const indicatorConfigs = {
            'growth': {
                title: 'Tren Pertumbuhan Ekonomi',
                desc: 'Persentase pertumbuhan PDRB ADHK • perbandingan wilayah',
                unit: '%',
                hasPeers: true,
                getSeries: (endIdx) => [
                    { name: 'Banyuwangi', values: AppState.series.growth.slice(0, endIdx + 1), className: 'series-0' },
                    { name: 'Jawa Timur', values: AppState.series.eastJavaGrowth.slice(0, endIdx + 1), className: 'series-1' },
                    { name: 'Nasional', values: AppState.series.nationalGrowth.slice(0, endIdx + 1), className: 'series-2' }
                ]
            },
            'perCapita': {
                title: 'Tren PDRB Per Kapita',
                desc: 'Tingkat pendapatan per kapita Kabupaten Banyuwangi (Juta Rupiah)',
                unit: ' Jt',
                hasPeers: false,
                getSeries: (endIdx) => [
                    { name: 'PDRB Per Kapita', values: AppState.series.perCapita.slice(0, endIdx + 1), className: 'series-0' }
                ]
            },
            'inflation': {
                title: 'Tren Inflasi Tahunan',
                desc: 'Perkembangan laju inflasi tahunan (yoy) Kabupaten Banyuwangi',
                unit: '%',
                hasPeers: false,
                getSeries: (endIdx) => [
                    { name: 'Inflasi Banyuwangi', values: AppState.series.inflation.slice(0, endIdx + 1), className: 'series-0' }
                ]
            },
            'tpt': {
                title: 'Tren Tingkat Pengangguran Terbuka (TPT)',
                desc: 'Proporsi angkatan kerja terbuka Kabupaten Banyuwangi (%)',
                unit: '%',
                hasPeers: false,
                getSeries: (endIdx) => [
                    { name: 'TPT Banyuwangi', values: AppState.series.tpt.slice(0, endIdx + 1), className: 'series-0' }
                ]
            },
            'poverty': {
                title: 'Tren Tingkat Kemiskinan',
                desc: 'Persentase penduduk miskin Kabupaten Banyuwangi (%)',
                unit: '%',
                hasPeers: false,
                getSeries: (endIdx) => [
                    { name: 'Kemiskinan Banyuwangi', values: AppState.series.poverty.slice(0, endIdx + 1), className: 'series-0' }
                ]
            },
            'investmentGrowth': {
                title: 'Tren Pertumbuhan Realisasi Investasi',
                desc: 'Laju pertumbuhan penanaman modal daerah tahunan (%)',
                unit: '%',
                hasPeers: false,
                getSeries: (endIdx) => [
                    { name: 'Investasi Banyuwangi', values: AppState.series.investmentGrowth.slice(0, endIdx + 1), className: 'series-0' }
                ]
            },
            'ipm': {
                title: 'Tren Indeks Pembangunan Manusia (IPM)',
                desc: 'Capaian IPM komposit pendidikan, kesehatan, dan standar hidup (poin)',
                unit: ' pt',
                hasPeers: false,
                getSeries: (endIdx) => [
                    { name: 'IPM Banyuwangi', values: AppState.series.ipm.slice(0, endIdx + 1), className: 'series-0' }
                ]
            }
        };

        let currentIndicatorKey = 'growth';

        function updateOverviewChart() {
            const yr = parseInt(filterYear ? filterYear.value : 2024);
            const endIdx = Math.max(0, AppState.years.indexOf(yr));
            const subYears = AppState.years.slice(0, endIdx + 1);

            const cfg = indicatorConfigs[currentIndicatorKey] || indicatorConfigs['growth'];
            if (chartTitle) chartTitle.textContent = cfg.title;
            if (chartDesc) chartDesc.textContent = cfg.desc;

            const chosenSeries = cfg.getSeries(endIdx);

            if (legend0) legend0.textContent = chosenSeries[0]?.name || 'Banyuwangi';
            if (legend1) {
                if (chosenSeries[1]) {
                    legend1.parentElement.style.display = 'flex';
                    legend1.textContent = chosenSeries[1].name;
                } else {
                    legend1.parentElement.style.display = 'none';
                }
            }

            if (chartContainer) {
                chartContainer.innerHTML = createLineChartSVG(subYears, chosenSeries, 230, cfg.unit);
            }
        }

        if (indSelect) {
            indSelect.addEventListener('change', function () {
                currentIndicatorKey = this.value;
                updateOverviewChart();
            });
        }

        // Klik kartu KPI untuk langsung menyesuaikan grafik
        document.querySelectorAll('.kpi-card').forEach(card => {
            card.addEventListener('click', function () {
                const key = this.getAttribute('data-indicator-key');
                if (key && indicatorConfigs[key]) {
                    currentIndicatorKey = key;
                    if (indSelect) indSelect.value = key;
                    updateOverviewChart();
                    if (chartContainer) {
                        chartContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                }
            });
        });

        updateOverviewChart();

        if (btnApply) {
            btnApply.addEventListener('click', () => {
                const yr = filterYear ? filterYear.value : 2024;
                window.location.href = SITE_URL + '?year=' + yr;
            });
        }

        // Setup CRUD & Soft Delete for Beranda KPI
        setupModuleCrud({
            controller: 'beranda',
            addModalId: 'add-beranda-modal',
            editModalId: 'edit-beranda-modal',
            openAddBtnIds: ['btn-open-add-beranda'],
            closeAddBtnIds: ['modal-btn-close-add-beranda', 'modal-btn-cancel-add-beranda'],
            closeEditBtnIds: ['modal-btn-close-edit-beranda', 'modal-btn-cancel-edit-beranda'],
            formAddId: 'form-add-beranda',
            formEditId: 'form-edit-beranda',
            addErrorId: 'add-beranda-error',
            editErrorId: 'edit-beranda-error',
            editBtnClass: 'btn-edit-beranda',
            deleteBtnClass: 'btn-delete-beranda',
            restoreBtnClass: 'btn-restore-beranda',
            hardDeleteBtnClass: 'btn-hard-delete-beranda',
            onEditPreFill: (d) => {
                document.getElementById('edit-beranda-id').value = d.id;
                document.getElementById('edit-beranda-name').value = d.nama_indikator || '';
                document.getElementById('edit-beranda-value').value = d.nilai || '';
                document.getElementById('edit-beranda-unit').value = d.satuan || '';
                document.getElementById('edit-beranda-change').value = d.perubahan || '';
                document.getElementById('edit-beranda-trend').value = d.arah_tren || 'up';
                document.getElementById('edit-beranda-status').value = d.status || '';
                document.getElementById('edit-beranda-year').value = d.tahun || 2024;
            }
        });
    }

    // 2. COMPARISON PAGE
    function initComparisonPage() {
        const periodSelect = document.getElementById('comp-period');
        const periodLabel = document.getElementById('comp-period-label');
        const kpiContainer = document.getElementById('comp-kpi-container');
        const featureTabs = document.getElementById('comp-feature-tabs');
        const tabContent = document.getElementById('comp-tab-content');
        const tabEyebrow = document.getElementById('comp-tab-eyebrow');
        const tabTitle = document.getElementById('comp-tab-title');
        const tabDesc = document.getElementById('comp-tab-desc');
        const btnReset = document.getElementById('btn-reset-regions');
        const btnExport = document.getElementById('btn-export-comparison');

        // Modal Elements
        const addRegionalModal = document.getElementById('add-regional-modal');
        const editRegionalModal = document.getElementById('edit-regional-modal');
        const btnOpenAdd = document.getElementById('btn-open-add-regional-modal');
        const btnCloseAdd = document.getElementById('modal-btn-close-add-regional');
        const btnCancelAdd = document.getElementById('modal-btn-cancel-add-regional');
        const btnCloseEdit = document.getElementById('modal-btn-close-edit-regional');
        const btnCancelEdit = document.getElementById('modal-btn-cancel-edit-regional');
        const formAdd = document.getElementById('form-add-regional');
        const formEdit = document.getElementById('form-edit-regional');
        const addRegionalErr = document.getElementById('add-regional-error');
        const editRegionalErr = document.getElementById('edit-regional-error');

        let activeTab = 'Regional Ranking';
        let selectedRegions = AppState.regional.map(r => r.name);

        function bindRegionalActionButtons() {
            // Edit button handler
            document.querySelectorAll('.btn-edit-regional').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const id = this.getAttribute('data-id');
                    if (!id) return;

                    if (editRegionalErr) {
                        editRegionalErr.style.display = 'none';
                        editRegionalErr.textContent = '';
                    }

                    fetch(`${SITE_URL}/perbandingan_daerah/get_data/${id}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(res => res.json())
                        .then(res => {
                            if (res.status === 'success' && res.data) {
                                const d = res.data;
                                document.getElementById('edit-regional-id').value = d.id;
                                document.getElementById('edit-regional-name').value = d.name || d.nama_wilayah || '';
                                document.getElementById('edit-regional-value').value = d.value !== undefined ? d.value : d.nilai_pertumbuhan;
                                document.getElementById('edit-regional-change').value = d.change !== undefined ? d.change : d.perubahan;
                                const seriesArr = Array.isArray(d.series) ? d.series : [];
                                document.getElementById('edit-regional-series').value = seriesArr.join(', ');

                                if (editRegionalModal) editRegionalModal.style.display = 'grid';
                            } else {
                                alert(res.message || 'Gagal memuat data wilayah.');
                            }
                        })
                        .catch(() => alert('Gagal mengambil data wilayah dari server.'));
                });
            });

            // Soft delete handler
            document.querySelectorAll('.btn-delete-regional').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name') || 'wilayah ini';
                    if (!id) return;

                    if (confirm(`Pindahkan wilayah "${name}" ke tempat sampah (Soft Delete)?\n\nWilayah tidak akan dihapus permanen dan dapat dipulihkan sewaktu-waktu dari Tempat Sampah.`)) {
                        fetch(`${SITE_URL}/perbandingan_daerah/soft_delete/${id}`, {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                            .then(res => res.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    location.reload();
                                } else {
                                    alert(data.message || 'Gagal memproses soft delete wilayah.');
                                }
                            })
                            .catch(() => {
                                window.location.href = `${SITE_URL}/perbandingan_daerah/soft_delete/${id}`;
                            });
                    }
                });
            });

            // Restore from soft delete handler
            document.querySelectorAll('.btn-restore-regional').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name') || 'wilayah ini';
                    if (!id) return;

                    if (confirm(`Pulihkan wilayah "${name}" kembali ke daftar aktif?`)) {
                        fetch(`${SITE_URL}/perbandingan_daerah/restore/${id}`, {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                            .then(res => res.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    location.reload();
                                } else {
                                    alert(data.message || 'Gagal memulihkan wilayah.');
                                }
                            })
                            .catch(() => {
                                window.location.href = `${SITE_URL}/perbandingan_daerah/restore/${id}`;
                            });
                    }
                });
            });

            // Hard delete handler
            document.querySelectorAll('.btn-hard-delete-regional').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name') || 'wilayah ini';
                    if (!id) return;

                    if (confirm(`PERINGATAN: Apakah Anda yakin ingin MENGHAPUS PERMANEN "${name}"?\n\nData yang dihapus permanen akan hilang selamanya dari database dan TIDAK DAPAT DIPULIHKAN LAGI!`)) {
                        fetch(`${SITE_URL}/perbandingan_daerah/hard_delete/${id}`, {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                            .then(res => res.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    location.reload();
                                } else {
                                    alert(data.message || 'Gagal menghapus wilayah secara permanen.');
                                }
                            })
                            .catch(() => {
                                window.location.href = `${SITE_URL}/perbandingan_daerah/hard_delete/${id}`;
                            });
                    }
                });
            });
        }

        // Modal Open / Close Handlers
        const btnOpenAdd2 = document.getElementById('btn-open-add-regional-2');
        if (btnOpenAdd && addRegionalModal) {
            btnOpenAdd.addEventListener('click', () => { addRegionalModal.style.display = 'grid'; });
        }
        if (btnOpenAdd2 && addRegionalModal) {
            btnOpenAdd2.addEventListener('click', () => { addRegionalModal.style.display = 'grid'; });
        }

        const closeAddModal = () => { if (addRegionalModal) addRegionalModal.style.display = 'none'; };
        if (btnCloseAdd) btnCloseAdd.addEventListener('click', closeAddModal);
        if (btnCancelAdd) btnCancelAdd.addEventListener('click', closeAddModal);
        if (addRegionalModal) {
            addRegionalModal.addEventListener('click', e => {
                if (e.target === addRegionalModal) closeAddModal();
            });
        }

        const closeEditModal = () => { if (editRegionalModal) editRegionalModal.style.display = 'none'; };
        if (btnCloseEdit) btnCloseEdit.addEventListener('click', closeEditModal);
        if (btnCancelEdit) btnCancelEdit.addEventListener('click', closeEditModal);
        if (editRegionalModal) {
            editRegionalModal.addEventListener('click', e => {
                if (e.target === editRegionalModal) closeEditModal();
            });
        }

        // Form Submit: Tambah Wilayah
        if (formAdd) {
            formAdd.addEventListener('submit', function (e) {
                e.preventDefault();
                const fd = new FormData(this);
                if (addRegionalErr) addRegionalErr.style.display = 'none';

                fetch(`${SITE_URL}/perbandingan_daerah/create`, {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            location.reload();
                        } else {
                            if (addRegionalErr) {
                                addRegionalErr.style.display = 'block';
                                addRegionalErr.textContent = data.message || 'Gagal menyimpan wilayah.';
                            }
                        }
                    })
                    .catch(() => {
                        formAdd.submit();
                    });
            });
        }

        // Form Submit: Edit Wilayah
        if (formEdit) {
            formEdit.addEventListener('submit', function (e) {
                e.preventDefault();
                const id = document.getElementById('edit-regional-id').value;
                if (!id) return;

                const fd = new FormData(this);
                if (editRegionalErr) editRegionalErr.style.display = 'none';

                fetch(`${SITE_URL}/perbandingan_daerah/update/${id}`, {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            location.reload();
                        } else {
                            if (editRegionalErr) {
                                editRegionalErr.style.display = 'block';
                                editRegionalErr.textContent = data.message || 'Gagal memperbarui wilayah.';
                            }
                        }
                    })
                    .catch(() => {
                        if (editRegionalErr) {
                            editRegionalErr.style.display = 'block';
                            editRegionalErr.textContent = 'Terjadi kesalahan jaringan saat menyimpan pembaruan.';
                        }
                    });
            });
        }

        function renderComparison() {
            const period = periodSelect ? periodSelect.value : '10';
            const sliceStart = period === '5' ? 5 : 0;
            const years = AppState.years.slice(sliceStart);
            if (periodLabel) periodLabel.textContent = period === '5' ? '2020–2024' : '2015–2024';

            // Calculate regional rows
            const rows = AppState.regional.map(item => {
                const vals = item.series.slice(sliceStart);
                const avg = mean(vals);
                const chg = vals.length > 1 ? vals[vals.length - 1] - vals[vals.length - 2] : 0;
                return { ...item, avg, chg };
            }).filter(item => selectedRegions.includes(item.name)).sort((a, b) => b.avg - a.avg);

            const bwi = rows.find(r => r.name === 'Kab. Banyuwangi') || { avg: 5.18, chg: 0.26 };
            const peerAvg = mean(rows.map(r => r.avg));
            const rank = rows.findIndex(r => r.name === 'Kab. Banyuwangi') + 1;
            const gap = bwi.avg - peerAvg;

            // Render KPIs
            if (kpiContainer) {
                kpiContainer.innerHTML = `
                    <div class="comparison-kpi">
                        <div><span>Peringkat Banyuwangi</span><span class="badge success">Posisi #${rank}</span></div>
                        <strong>#${rank || '—'}</strong>
                        <small>dari ${rows.length} wilayah terpilih</small>
                    </div>
                    <div class="comparison-kpi">
                        <div><span>Rata-rata Banyuwangi</span><span class="badge info">Periode</span></div>
                        <strong>${formatPct(bwi.avg)}</strong>
                        <small>periode ${period === '5' ? '2020–2024' : '2015–2024'}</small>
                    </div>
                    <div class="comparison-kpi">
                        <div><span>Rerata Wilayah Pembanding</span><span class="badge neutral">Benchmark</span></div>
                        <strong>${formatPct(peerAvg)}</strong>
                        <small>${rows.length} kabupaten/kota</small>
                    </div>
                    <div class="comparison-kpi">
                        <div><span>Gap terhadap Rerata</span><span class="badge ${gap >= 0 ? 'success' : 'warning'}">Selisih</span></div>
                        <strong class="${gap >= 0 ? 'positive' : 'negative'}">${gap >= 0 ? '+' : ''}${formatNum(gap)} pp</strong>
                        <small>${gap >= 0 ? 'Lebih tinggi' : 'Lebih rendah'} dari rerata</small>
                    </div>`;
            }

            // Render Active Tab Content
            if (!tabContent) return;

            if (activeTab === 'Regional Ranking') {
                tabEyebrow.textContent = 'REGIONAL RANKING';
                tabTitle.textContent = 'Peringkat Rata-rata Pertumbuhan Ekonomi';
                tabDesc.textContent = 'Peringkat dihitung dari rata-rata tahunan selama periode terpilih.';

                const rankingBarData = rows.map((r, idx) => ({
                    label: r.name,
                    value: r.avg,
                    subLabel: `Perubahan: ${r.chg >= 0 ? '+' : ''}${formatNum(r.chg)} pp | Gap ke rerata: ${r.avg >= peerAvg ? '+' : ''}${formatNum(r.avg - peerAvg)} pp`,
                    badge: r.name === 'Kab. Banyuwangi' ? 'Fokus Analisis' : `Peringkat #${idx + 1}`,
                    badgeClass: r.name === 'Kab. Banyuwangi' ? 'primary' : (r.avg >= peerAvg ? 'success' : 'neutral'),
                    isHighlight: r.name === 'Kab. Banyuwangi'
                }));

                let listHtml = `
                    <div style="margin-bottom:20px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                            <span class="eyebrow">VISUALISASI PERINGKAT KOMPARASI</span>
                            <span class="badge info">Periode ${period === '5' ? '5 Tahun' : '10 Tahun'}</span>
                        </div>
                        <div class="chart-wrap dynamic-chart" style="height:auto; min-height:160px;">
                            ${createHorizontalBarChartSVG(rankingBarData, { unit: '%', decimals: 2 })}
                        </div>
                    </div>

                    <div class="comparison-table-head" style="grid-template-columns: 50px 1.3fr .7fr .7fr .7fr 80px;">
                        <span>Peringkat</span>
                        <span>Wilayah</span>
                        <span>Rata-rata Pertumbuhan</span>
                        <span>Perubahan Tahunan</span>
                        <span>Gap Terhadap Rerata</span>
                        <span>Aksi</span>
                    </div>
                    <div class="comparison-ranking">`;

                rows.forEach((r, idx) => {
                    const isBwi = r.name === 'Kab. Banyuwangi';
                    const diff = r.avg - peerAvg;
                    const regionId = r.id || AppState.regional.find(item => item.name === r.name)?.id;
                    listHtml += `
                        <div class="comparison-table-row ${isBwi ? 'selected' : ''}" style="grid-template-columns: 50px 1.3fr .7fr .7fr .7fr 80px;">
                            <b>${idx + 1}</b>
                            <span>
                                <i class="region-avatar">${r.name.replace('Kab. ', '').replace('Kota ', '').slice(0, 2).toUpperCase()}</i>
                                <strong>${r.name}</strong>
                            </span>
                            <strong>${formatPct(r.avg)}</strong>
                            <small class="${r.chg >= 0 ? 'positive' : 'negative'}">${r.chg >= 0 ? '+' : ''}${formatNum(r.chg)} pp</small>
                            <span class="badge ${diff >= 0 ? 'success' : 'warning'}">${diff >= 0 ? '+' : ''}${formatNum(diff)} pp</span>
                            <span class="row-actions">
                                <button type="button" class="btn-edit-regional" data-id="${regionId}" title="Edit Data Wilayah">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                ${!isBwi ? `
                                <button type="button" class="btn-delete-regional danger" data-id="${regionId}" data-name="${r.name}" title="Hapus Wilayah (Soft Delete)">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>` : ''}
                            </span>
                        </div>`;
                });
                listHtml += `</div><div class="method-note">Peringkat didasarkan pada data PDRB ADHK Kabupaten/Kota di Jawa Timur. Anda dapat menambahkan, mengedit, atau menghapus wilayah dengan fitur Soft Delete.</div>`;
                tabContent.innerHTML = listHtml;
                bindRegionalActionButtons();

            } else if (activeTab === 'Benchmarking') {
                tabEyebrow.textContent = 'BENCHMARKING';
                tabTitle.textContent = 'Perbandingan Tren Antar-Wilayah Terpilih';
                tabDesc.textContent = 'Menunjukkan lintasan pertumbuhan ekonomi Banyuwangi dan wilayah pembanding yang dicentang.';

                const bwiSeries = AppState.series.growth.slice(sliceStart);
                const jatimSeries = AppState.series.eastJavaGrowth.slice(sliceStart);
                
                // Bangun deret grafik yang sesuai dengan wilayah yang dicentang pengguna
                const benchmarkSeries = [
                    { name: 'Banyuwangi', values: bwiSeries, className: 'series-0' },
                    { name: 'Jawa Timur', values: jatimSeries, className: 'series-1' }
                ];

                const colorClasses = ['series-2', 'series-3', 'series-4', 'series-5'];
                let cIdx = 0;
                selectedRegions.forEach(regName => {
                    if (regName !== 'Kab. Banyuwangi') {
                        const regItem = AppState.regional.find(r => r.name === regName);
                        if (regItem && regItem.series && regItem.series.length > 0) {
                            benchmarkSeries.push({
                                name: regItem.name.replace('Kab. ', '').replace('Kota ', ''),
                                values: regItem.series.slice(sliceStart),
                                className: colorClasses[cIdx % colorClasses.length]
                            });
                            cIdx++;
                        }
                    }
                });

                tabContent.innerHTML = `
                    <div class="comparison-kpis" style="margin-bottom:15px;">
                        <div class="comparison-kpi"><span>Rerata Banyuwangi</span><strong>${formatPct(mean(bwiSeries))}</strong><small>Periode terpilih</small></div>
                        <div class="comparison-kpi"><span>Rerata Jawa Timur</span><strong>${formatPct(mean(jatimSeries))}</strong><small>Benchmark Provinsi</small></div>
                        <div class="comparison-kpi"><span>Wilayah Dicentang</span><strong>${selectedRegions.length}</strong><small>Daerah pembanding</small></div>
                    </div>
                    <div class="chart-wrap dynamic-chart" style="height:290px;">
                        ${createLineChartSVG(years, benchmarkSeries, 290, '%')}
                    </div>`;

            } else if (activeTab === 'Gap Analysis') {
                tabEyebrow.textContent = 'GAP ANALYSIS';
                tabTitle.textContent = 'Selisih Kinerja terhadap Benchmark Utama';
                tabDesc.textContent = 'Analisis komparatif selisih pertumbuhan ekonomi secara langsung.';

                const jatimAvg = mean(AppState.series.eastJavaGrowth.slice(sliceStart));
                const gapJatim = bwi.avg - jatimAvg;

                const gapBarData = rows.map(r => {
                    const diff = r.avg - bwi.avg;
                    return {
                        label: r.name,
                        value: diff,
                        subLabel: `Rata-rata: ${formatPct(r.avg)} (Banyuwangi: ${formatPct(bwi.avg)})`,
                        badge: diff > 0 ? `+${formatNum(diff)} pp` : (diff === 0 ? 'Acuan' : `${formatNum(diff)} pp`),
                        badgeClass: diff > 0 ? 'success' : (diff === 0 ? 'primary' : 'warning'),
                        isHighlight: r.name === 'Kab. Banyuwangi'
                    };
                });

                tabContent.innerHTML = `
                    <div class="gap-overview">
                        <div>
                            <span>Gap terhadap Jawa Timur</span>
                            <strong class="${gapJatim >= 0 ? 'positive' : 'negative'}">${gapJatim >= 0 ? '+' : ''}${formatNum(gapJatim)} pp</strong>
                        </div>
                        <div>
                            <span>Gap terhadap Rerata ${rows.length} Daerah</span>
                            <strong class="${gap >= 0 ? 'positive' : 'negative'}">${gap >= 0 ? '+' : ''}${formatNum(gap)} pp</strong>
                        </div>
                    </div>

                    <div style="margin: 18px 0;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                            <span class="eyebrow">GRAFIK DIVERGENSI GAP PERTUMBUHAN EKONOMI (PP)</span>
                            <small style="color:var(--muted); font-size:9px;">Relatif terhadap Banyuwangi</small>
                        </div>
                        <div class="chart-wrap dynamic-chart" style="height:auto; min-height:160px;">
                            ${createHorizontalBarChartSVG(gapBarData, { unit: ' pp', decimals: 2 })}
                        </div>
                    </div>

                    <div class="result-table-wrap">
                        <table class="result-table">
                            <thead><tr><th>Benchmark</th><th>Nilai Banyuwangi</th><th>Nilai Pembanding</th><th>Gap</th><th>Status</th></tr></thead>
                            <tbody>
                                <tr>
                                    <td><strong>Provinsi Jawa Timur</strong></td>
                                    <td>${formatPct(bwi.avg)}</td>
                                    <td>${formatPct(jatimAvg)}</td>
                                    <td><strong>${gapJatim >= 0 ? '+' : ''}${formatNum(gapJatim)} pp</strong></td>
                                    <td><span class="badge ${gapJatim >= 0 ? 'success' : 'warning'}">${gapJatim >= 0 ? 'Di atas provinsi' : 'Di bawah provinsi'}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Rerata Daerah Terpilih</strong></td>
                                    <td>${formatPct(bwi.avg)}</td>
                                    <td>${formatPct(peerAvg)}</td>
                                    <td><strong>${gap >= 0 ? '+' : ''}${formatNum(gap)} pp</strong></td>
                                    <td><span class="badge ${gap >= 0 ? 'success' : 'warning'}">${gap >= 0 ? 'Di atas rerata' : 'Di bawah rerata'}</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>`;

            } else if (activeTab === 'Regional Profiling') {
                                    <td>${formatPct(bwi.avg)}</td>
                                    <td>${formatPct(peerAvg)}</td>
                                    <td><strong>${gap >= 0 ? '+' : ''}${formatNum(gap)} pp</strong></td>
                                    <td><span class="badge ${gap >= 0 ? 'success' : 'warning'}">${gap >= 0 ? 'Di atas rerata' : 'Di bawah rerata'}</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>`;

            } else if (activeTab === 'Regional Profiling') {
                tabEyebrow.textContent = 'REGIONAL PROFILING';
                tabTitle.textContent = 'Struktur Pangsa Sektoral Banyuwangi vs Jawa Timur';
                tabDesc.textContent = 'Perbandingan kontribusi lapangan usaha terhadap total perekonomian.';

                let profHtml = `<div class="profile-comparison">`;
                AppState.sectors.forEach(s => {
                    profHtml += `
                        <div class="profile-row">
                            <span>${s.short}</span>
                            <div class="profile-bars">
                                <i class="series-0" style="width: ${s.shareLocal}%"></i>
                                <i class="series-1" style="width: ${s.shareProvince}%"></i>
                            </div>
                            <strong>${formatNum(s.shareLocal, 1)}%</strong>
                        </div>`;
                });
                profHtml += `</div><div class="dynamic-chart-legend"><span><i class="series-0"></i>Pangsa Banyuwangi</span><span><i class="series-1"></i>Pangsa Jawa Timur</span></div>`;
                tabContent.innerHTML = profHtml;

            } else if (activeTab === 'Tipologi Klassen') {
                tabEyebrow.textContent = 'TIPOLOGI KLASSEN';
                tabTitle.textContent = 'Matriks Posisi Kuadran Sektoral';
                tabDesc.textContent = 'Klasifikasi sektor berdasarkan gap pangsa dan gap pertumbuhan.';

                const avgShare = mean(AppState.sectors.map(s => s.shareLocal));
                const bwiGrowth = AppState.series.growth[AppState.series.growth.length - 1];

                const klassenRows = AppState.sectors.map(s => {
                    const shareAbove = s.shareLocal >= avgShare;
                    const growthAbove = s.growthLocal >= bwiGrowth;
                    let quadrant = 'IV';
                    let cls = 'Sektor relatif tertinggal';
                    if (shareAbove && growthAbove) { quadrant = 'I'; cls = 'Maju & tumbuh cepat'; }
                    else if (shareAbove && !growthAbove) { quadrant = 'II'; cls = 'Maju tapi tertekan'; }
                    else if (!shareAbove && growthAbove) { quadrant = 'III'; cls = 'Potensial berkembang'; }

                    return {
                        ...s,
                        quadrant,
                        classification: cls,
                        shareGap: s.shareLocal - avgShare,
                        growthGap: s.growthLocal - bwiGrowth
                    };
                });

                tabContent.innerHTML = createKlassenSVG(klassenRows);

            } else if (activeTab === 'Peer Comparison') {
                tabEyebrow.textContent = 'PEER COMPARISON';
                tabTitle.textContent = 'Kartu Profil Wilayah Setara';
                tabDesc.textContent = 'Komparasi profil ringkas masing-masing daerah pembanding.';

                let peerHtml = `<div class="peer-grid">`;
                rows.filter(r => r.name !== 'Kab. Banyuwangi').forEach((r, idx) => {
                    peerHtml += `
                        <article>
                            <div>
                                <i class="region-avatar">${r.name.replace('Kab. ', '').replace('Kota ', '').slice(0, 2).toUpperCase()}</i>
                                <span class="badge neutral">Peringkat #${idx + 1}</span>
                            </div>
                            <h4>${r.name}</h4>
                            <p>Pertumbuhan ekonomi rata-rata periode terpilih.</p>
                            <span>Pertumbuhan: <strong>${formatPct(r.avg)}</strong></span>
                            <span>Perubahan tahunan: <strong>${r.chg >= 0 ? '+' : ''}${formatNum(r.chg)} pp</strong></span>
                        </article>`;
                });
                peerHtml += `</div>`;
                tabContent.innerHTML = peerHtml;
            }
        }

        if (featureTabs) {
            featureTabs.querySelectorAll('button').forEach(btn => {
                btn.addEventListener('click', function () {
                    featureTabs.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    activeTab = this.getAttribute('data-tab');
                    renderComparison();
                });
            });
        }

        if (periodSelect) periodSelect.addEventListener('change', renderComparison);

        document.querySelectorAll('.region-cb').forEach(cb => {
            cb.addEventListener('change', function () {
                const val = this.value;
                if (this.checked) {
                    if (!selectedRegions.includes(val)) selectedRegions.push(val);
                } else {
                    selectedRegions = selectedRegions.filter(r => r !== val);
                }
                renderComparison();
            });
        });

        if (btnReset) {
            btnReset.addEventListener('click', () => {
                selectedRegions = AppState.regional.map(r => r.name);
                document.querySelectorAll('.region-cb').forEach(cb => cb.checked = true);
                renderComparison();
            });
        }

        if (btnExport) {
            btnExport.addEventListener('click', () => {
                const csvRows = [
                    ['Peringkat', 'Wilayah', 'Rata-rata Pertumbuhan (%)', 'Perubahan Tahunan (pp)'],
                    ...AppState.regional.map((r, i) => [i + 1, r.name, r.value, r.change])
                ];
                downloadCSV('perbandingan-regional-banyuwangi.csv', csvRows);
            });
        }

        renderComparison();
        bindRegionalActionButtons();
    }

    // 3. ANALYSIS PAGE
    function initAnalysisPage() {
        const menuLinks = document.querySelectorAll('.analysis-link');
        const heading = document.getElementById('active-method-heading');
        const container = document.getElementById('analysis-content-container');
        const btnFormula = document.getElementById('btn-toggle-formula');
        const formulaBox = document.getElementById('formula-box');
        const btnExport = document.getElementById('btn-export-analysis');

        let activeMethod = 'Location Quotient (LQ)';
        let corrPair = 'growth-tpt';
        let corrPeriod = '10';

        const formulas = {
            'Location Quotient (LQ)': 'LQ = (Tenaga kerja sektor lokal / total lokal) ÷ (Tenaga kerja sektor provinsi / total provinsi). LQ > 1 menunjukkan sektor basis.',
            'Shift Share Analysis': 'ΔEij = Eij·r + Eij·(rin - r) + Eij·(rij - rin). Komponen: Efek Pertumbuhan Provinsi, Efek Bauran Industri, Efek Daya Saing Lokal.',
            'Tipologi Klassen': 'Kuadran I: Pangsa > rata-rata lokal & Pertumbuhan > agregat daerah; Kuadran II: Pangsa > rata-rata, Pertumbuhan < agregat; Kuadran III: Pangsa < rata-rata, Pertumbuhan > agregat; Kuadran IV: Keduanya di bawah ambang.',
            'Analisis Korelasi': 'r = Σ[(x - x̄)(y - ȳ)] ÷ √[Σ(x - x̄)² Σ(y - ȳ)²]. R² = r² mengukur persentase variasi bersama in-sample.'
        };

        if (btnFormula && formulaBox) {
            btnFormula.addEventListener('click', () => {
                const isHidden = formulaBox.style.display === 'none';
                formulaBox.style.display = isHidden ? 'block' : 'none';
                formulaBox.textContent = formulas[activeMethod] || '';
            });
        }

        function renderAnalysis() {
            if (heading) heading.textContent = activeMethod;
            if (formulaBox) formulaBox.textContent = formulas[activeMethod] || '';

            if (activeMethod === 'Location Quotient (LQ)') {
                const localTot = AppState.sectors.reduce((t, s) => t + s.employmentCurrent, 0) || 1;
                const provTot = AppState.sectors.reduce((t, s) => t + s.provinceEmploymentCurrent, 0) || 1;

                const lqRows = AppState.sectors.map(s => {
                    const lShare = s.employmentCurrent / localTot;
                    const pShare = s.provinceEmploymentCurrent / provTot;
                    const lq = pShare > 0 ? lShare / pShare : 0;
                    return {
                        ...s,
                        lSharePct: lShare * 100,
                        pSharePct: pShare * 100,
                        lq,
                        isBasis: lq > 1
                    };
                });

                const basisCount = lqRows.filter(r => r.isBasis).length;
                const maxLq = Math.max(...lqRows.map(r => r.lq));

                let html = `
                    <div class="method-summary-grid">
                        <div class="method-metric"><span>Sektor Basis</span><strong>${basisCount}</strong><small>Nilai LQ > 1.0</small></div>
                        <div class="method-metric"><span>Sektor Nonbasis</span><strong>${lqRows.length - basisCount}</strong><small>Nilai LQ ≤ 1.0</small></div>
                        <div class="method-metric"><span>LQ Tertinggi</span><strong>${formatNum(maxLq, 2)}</strong><small>${lqRows.find(r => r.lq === maxLq)?.short || ''}</small></div>
                    </div>
                    <article class="card method-card">
                        <div class="card-head">
                            <div><span class="eyebrow">BARS HASIL PERHITUNGAN</span><h3>Location Quotient per Lapangan Usaha</h3></div>
                        </div>
                        <div class="lq-bars" style="margin: 16px 0;">`;

                lqRows.forEach(r => {
                    const w = Math.min(100, (r.lq / Math.max(2, maxLq)) * 100);
                    const benchmarkX = (1.0 / Math.max(2, maxLq)) * 100;
                    html += `
                        <div class="lq-bar-row">
                            <span title="${r.name}">${r.short}</span>
                            <div class="lq-track">
                                <i class="${r.isBasis ? 'basis' : ''}" style="width: ${w}%"></i>
                                <b style="left: ${benchmarkX}%"></b>
                            </div>
                            <strong>${formatNum(r.lq, 2)}</strong>
                        </div>`;
                });

                html += `</div>
                        <div class="result-table-wrap">
                            <table class="result-table">
                                <thead><tr><th>Lapangan Usaha</th><th>Share Lokal</th><th>Share Provinsi</th><th>Nilai LQ</th><th>Klasifikasi</th></tr></thead>
                                <tbody>
                                    ${lqRows.map(r => `
                                        <tr>
                                            <td><strong>${r.name}</strong></td>
                                            <td>${formatPct(r.lSharePct)}</td>
                                            <td>${formatPct(r.pSharePct)}</td>
                                            <td><strong>${formatNum(r.lq, 3)}</strong></td>
                                            <td><span class="badge ${r.isBasis ? 'success' : 'neutral'}">${r.isBasis ? 'Basis' : 'Nonbasis'}</span></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                        <div class="method-note">Garis vertikal hitam menandai ambang LQ = 1,0. Nilai di atas 1,0 menunjukkan spesialisasi tenaga kerja daerah melampaui Jawa Timur.</div>
                    </article>`;
                container.innerHTML = html;

            } else if (activeMethod === 'Shift Share Analysis') {
                const locBase = AppState.sectors.reduce((t, s) => t + s.employmentBase, 0) || 1;
                const locCur = AppState.sectors.reduce((t, s) => t + s.employmentCurrent, 0) || 1;
                const provBase = AppState.sectors.reduce((t, s) => t + s.provinceEmploymentBase, 0) || 1;
                const provCur = AppState.sectors.reduce((t, s) => t + s.provinceEmploymentCurrent, 0) || 1;

                const provGrowth = (provCur / provBase) - 1;

                let sumProv = 0, sumMix = 0, sumReg = 0;
                const rows = AppState.sectors.map(s => {
                    const locG = (s.employmentCurrent / (s.employmentBase || 1)) - 1;
                    const secRefG = (s.provinceEmploymentCurrent / (s.provinceEmploymentBase || 1)) - 1;

                    const provEff = s.employmentBase * provGrowth;
                    const mixEff = s.employmentBase * (secRefG - provGrowth);
                    const regEff = s.employmentBase * (locG - secRefG);
                    const actChg = s.employmentCurrent - s.employmentBase;

                    sumProv += provEff;
                    sumMix += mixEff;
                    sumReg += regEff;

                    return {
                        ...s,
                        locG: locG * 100,
                        secRefG: secRefG * 100,
                        provEff,
                        mixEff,
                        regEff,
                        actChg
                    };
                });

                const components = {
                    provincialGrowthEffect: sumProv,
                    industryMix: sumMix,
                    regionalShift: sumReg,
                    actualChange: locCur - locBase
                };

                let html = `
                    <div class="method-summary-grid four">
                        <div class="method-metric"><span>Perubahan Aktual</span><strong>${formatNum(components.actualChange, 0)}</strong><small>orang</small></div>
                        <div class="method-metric"><span>Efek Pertumbuhan Prov</span><strong>${formatNum(components.provincialGrowthEffect, 0)}</strong><small>Jawa Timur</small></div>
                        <div class="method-metric"><span>Efek Bauran Industri</span><strong>${formatNum(components.industryMix, 0)}</strong><small>Industry Mix</small></div>
                        <div class="method-metric"><span>Efek Daya Saing Lokal</span><strong>${formatNum(components.regionalShift, 0)}</strong><small>Competitive Advantage</small></div>
                    </div>
                    <article class="card method-card">
                        <div class="card-head">
                            <div><span class="eyebrow">WATERFALL DEKOMPOSISI</span><h3>Kontribusi Komponen Perubahan Tenaga Kerja</h3></div>
                        </div>
                        <div class="shift-waterfall-wrap">${createShiftWaterfallSVG(components)}</div>
                        <div class="result-table-wrap" style="margin-top:16px;">
                            <table class="result-table">
                                <thead><tr><th>Sektor</th><th>Laju Lokal</th><th>Laju Jatim</th><th>Efek Prov</th><th>Bauran Industri</th><th>Daya Saing</th><th>Total Aktual</th></tr></thead>
                                <tbody>
                                    ${rows.map(r => `
                                        <tr>
                                            <td><strong>${r.short}</strong></td>
                                            <td>${formatPct(r.locG)}</td>
                                            <td>${formatPct(r.secRefG)}</td>
                                            <td>${formatNum(r.provEff, 0)}</td>
                                            <td>${formatNum(r.mixEff, 0)}</td>
                                            <td><strong class="${r.regEff >= 0 ? 'positive' : 'negative'}">${formatNum(r.regEff, 0)}</strong></td>
                                            <td>${formatNum(r.actChg, 0)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </article>`;
                container.innerHTML = html;

            } else if (activeMethod === 'Tipologi Klassen') {
                const avgShare = mean(AppState.sectors.map(s => s.shareLocal));
                const bwiGrowth = AppState.series.growth[AppState.series.growth.length - 1];

                const rows = AppState.sectors.map(s => {
                    const shareAbove = s.shareLocal >= avgShare;
                    const growthAbove = s.growthLocal >= bwiGrowth;
                    let q = 'IV', cls = 'Sektor relatif tertinggal';
                    if (shareAbove && growthAbove) { q = 'I'; cls = 'Sektor maju dan tumbuh cepat'; }
                    else if (shareAbove && !growthAbove) { q = 'II'; cls = 'Sektor maju tetapi tertekan'; }
                    else if (!shareAbove && growthAbove) { q = 'III'; cls = 'Sektor potensial berkembang'; }

                    return {
                        ...s,
                        quadrant: q,
                        classification: cls,
                        shareGap: s.shareLocal - avgShare,
                        growthGap: s.growthLocal - bwiGrowth
                    };
                });

                const qCounts = {
                    I: rows.filter(r => r.quadrant === 'I').length,
                    II: rows.filter(r => r.quadrant === 'II').length,
                    III: rows.filter(r => r.quadrant === 'III').length,
                    IV: rows.filter(r => r.quadrant === 'IV').length
                };

                let html = `
                    <div class="klassen-grid">
                        <div class="klassen-box q-i"><span>KUADRAN I</span><strong>${qCounts.I}</strong><b>Maju & Tumbuh Cepat</b><small>Pangsa & laju di atas ambang</small></div>
                        <div class="klassen-box q-ii"><span>KUADRAN II</span><strong>${qCounts.II}</strong><b>Maju Tapi Tertekan</b><small>Pangsa tinggi, laju di bawah</small></div>
                        <div class="klassen-box q-iii"><span>KUADRAN III</span><strong>${qCounts.III}</strong><b>Potensial Berkembang</b><small>Pangsa rendah, laju di atas</small></div>
                        <div class="klassen-box q-iv"><span>KUADRAN IV</span><strong>${qCounts.IV}</strong><b>Relatif Tertinggal</b><small>Keduanya di bawah ambang</small></div>
                    </div>
                    <article class="card method-card">
                        <div class="card-head">
                            <div><span class="eyebrow">DIAGRAM KUADRAN</span><h3>Pemetaan Kuadran Klassen Lapangan Usaha</h3></div>
                        </div>
                        <div class="klassen-chart-wrap">${createKlassenSVG(rows)}</div>
                        <div class="result-table-wrap" style="margin-top:16px;">
                            <table class="result-table">
                                <thead><tr><th>Sektor</th><th>Pangsa Lokal</th><th>Pertumbuhan</th><th>Gap Pangsa</th><th>Gap Laju</th><th>Kuadran</th></tr></thead>
                                <tbody>
                                    ${rows.map(r => `
                                        <tr>
                                            <td><strong>${r.short}</strong></td>
                                            <td>${formatPct(r.shareLocal)}</td>
                                            <td>${formatPct(r.growthLocal)}</td>
                                            <td>${formatNum(r.shareGap)} pp</td>
                                            <td>${formatNum(r.growthGap)} pp</td>
                                            <td><span class="badge ${r.quadrant === 'I' ? 'success' : (r.quadrant === 'II' ? 'warning' : (r.quadrant === 'III' ? 'info' : 'neutral'))}">Kuadran ${r.quadrant}</span></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </article>`;
                container.innerHTML = html;

            } else if (activeMethod === 'Analisis Korelasi') {
                const pairs = [
                    { key: 'growth-tpt', label: 'Pertumbuhan ekonomi × TPT', xName: 'Pertumbuhan ekonomi (%)', yName: 'TPT (%)', x: AppState.series.growth, y: AppState.series.tpt },
                    { key: 'investment-growth', label: 'Pertumbuhan investasi × Pertumbuhan ekonomi', xName: 'Pertumbuhan investasi (%)', yName: 'Pertumbuhan ekonomi (%)', x: AppState.series.investmentGrowth, y: AppState.series.growth },
                    { key: 'growth-poverty', label: 'Pertumbuhan ekonomi × Kemiskinan', xName: 'Pertumbuhan ekonomi (%)', yName: 'Kemiskinan (%)', x: AppState.series.growth, y: AppState.series.poverty },
                    { key: 'percapita-poverty', label: 'PDRB per kapita × Kemiskinan', xName: 'PDRB per kapita (juta)', yName: 'Kemiskinan (%)', x: AppState.series.perCapita, y: AppState.series.poverty },
                    { key: 'investment-tpt', label: 'Pertumbuhan investasi × TPT', xName: 'Pertumbuhan investasi (%)', yName: 'TPT (%)', x: AppState.series.investmentGrowth, y: AppState.series.tpt }
                ];

                const selectedPair = pairs.find(p => p.key === corrPair) || pairs[0];
                const sliceStart = corrPeriod === '5' ? 5 : 0;
                const subX = selectedPair.x.slice(sliceStart);
                const subY = selectedPair.y.slice(sliceStart);
                const subYears = AppState.years.slice(sliceStart);

                const r = pearson(subX, subY);
                const r2 = r * r;
                const strength = corrStrength(r);

                let html = `
                    <div class="correlation-summary">
                        <div><span>Koefisien Korelasi (r)</span><strong class="${r >= 0 ? 'positive' : 'negative'}">${formatNum(r, 3)}</strong><span class="badge ${Math.abs(r) >= 0.6 ? 'success' : 'warning'}">${strength}</span></div>
                        <div><span>Koefisien Determinasi (R²)</span><strong>${formatNum(r2, 3)}</strong><small>${formatPct(r2 * 100, 1)} variasi bersama</small></div>
                        <div><span>Jumlah Observasi</span><strong>${subYears.length}</strong><small>Data tahunan ${subYears[0]}–${subYears[subYears.length - 1]}</small></div>
                        <div><span>Arah Hubungan</span><strong>${r >= 0 ? 'Positif' : 'Negatif'}</strong><small>${r >= 0 ? 'Searah' : 'Berlawanan'}</small></div>
                    </div>

                    <section class="correlation-layout">
                        <article class="card correlation-card">
                            <div class="card-head">
                                <div><span class="eyebrow">SCATTER PLOT & REGRESI</span><h3>${selectedPair.xName} vs ${selectedPair.yName}</h3></div>
                                <div class="chart-types">
                                    <select id="corr-pair-select" style="height:32px; border:1px solid var(--line); border-radius:6px; font-size:9px; padding:0 6px;">
                                        ${pairs.map(p => `<option value="${p.key}" ${p.key === corrPair ? 'selected' : ''}>${p.label}</option>`).join('')}
                                    </select>
                                    <select id="corr-period-select" style="height:32px; border:1px solid var(--line); border-radius:6px; font-size:9px; padding:0 6px;">
                                        <option value="10" ${corrPeriod === '10' ? 'selected' : ''}>10 Tahun</option>
                                        <option value="5" ${corrPeriod === '5' ? 'selected' : ''}>5 Tahun Terakhir</option>
                                    </select>
                                </div>
                            </div>
                            <div class="correlation-chart dynamic-scatter">${createScatterPlotSVG(subX, subY, selectedPair.xName, selectedPair.yName, subYears)}</div>
                            <div class="correlation-legend"><span><i></i>Observasi Tahunan</span><span><i class="trend"></i>Garis Regresi OLS</span></div>
                        </article>

                        <aside class="correlation-side">
                            <article class="card correlation-interpretation">
                                <div class="card-head"><div><span class="eyebrow">INTERPRETASI</span><h3>${strength}</h3></div><span class="relation-icon ${r >= 0 ? 'up' : 'down'}">${r >= 0 ? '↗' : '↘'}</span></div>
                                <p>Hubungan antara ${selectedPair.xName} dan ${selectedPair.yName} menunjukkan arah ${r >= 0 ? 'positif (bergerak searah)' : 'negatif (bergerak berlawanan)'} dengan derajat korelasi ${strength.toLowerCase()}.</p>
                                <div class="interpretation-note">Korelasi mengukur asosiasi linear dan bukan hubungan sebab-akibat kausal.</div>
                            </article>
                        </aside>
                    </section>`;
                container.innerHTML = html;

                const pairSel = document.getElementById('corr-pair-select');
                const periodSel = document.getElementById('corr-period-select');
                if (pairSel) pairSel.addEventListener('change', e => { corrPair = e.target.value; renderAnalysis(); });
                if (periodSel) periodSel.addEventListener('change', e => { corrPeriod = e.target.value; renderAnalysis(); });
            }
        }

        menuLinks.forEach(link => {
            link.addEventListener('click', function () {
                menuLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
                activeMethod = this.getAttribute('data-mode');
                renderAnalysis();
            });
        });

        if (btnExport) {
            btnExport.addEventListener('click', () => {
                if (activeMethod.includes('LQ')) {
                    downloadCSV('hasil-lq-banyuwangi.csv', [
                        ['Sektor', 'Pangsa Lokal (%)', 'Pangsa Provinsi (%)', 'LQ', 'Klasifikasi'],
                        ...AppState.sectors.map(s => [s.name, s.shareLocal, s.shareProvince, (s.shareLocal / (s.shareProvince || 1)).toFixed(2), s.shareLocal > s.shareProvince ? 'Basis' : 'Nonbasis'])
                    ]);
                } else if (activeMethod.includes('Shift')) {
                    downloadCSV('hasil-shift-share-banyuwangi.csv', [
                        ['Sektor', 'Laju Pertumbuhan Banyuwangi (%)', 'Laju Pertumbuhan Jatim (%)'],
                        ...AppState.sectors.map(s => [s.name, s.growthLocal, s.growthProvince])
                    ]);
                } else if (activeMethod.includes('Klassen')) {
                    downloadCSV('hasil-tipologi-klassen-banyuwangi.csv', [
                        ['Sektor', 'Pangsa Lokal (%)', 'Pertumbuhan Lokal (%)'],
                        ...AppState.sectors.map(s => [s.name, s.shareLocal, s.growthLocal])
                    ]);
                } else {
                    downloadCSV('data-korelasi-makro-banyuwangi.csv', [
                        ['Tahun', 'Pertumbuhan Ekonomi (%)', 'TPT (%)', 'Inflasi (%)', 'Kemiskinan (%)'],
                        ...AppState.years.map((y, i) => [y, AppState.series.growth[i], AppState.series.tpt[i], AppState.series.inflation[i], AppState.series.poverty[i]])
                    ]);
                }
            });
        }

        renderAnalysis();

        // Setup CRUD & Soft Delete for Sektor Ekonomi
        setupModuleCrud({
            controller: 'analisis_ekonomi',
            addModalId: 'add-sector-modal',
            editModalId: 'edit-sector-modal',
            openAddBtnIds: ['btn-open-add-sector', 'btn-open-add-sector-2'],
            closeAddBtnIds: ['modal-btn-close-add-sector', 'modal-btn-cancel-add-sector'],
            closeEditBtnIds: ['modal-btn-close-edit-sector', 'modal-btn-cancel-edit-sector'],
            formAddId: 'form-add-sector',
            formEditId: 'form-edit-sector',
            addErrorId: 'add-sector-error',
            editErrorId: 'edit-sector-error',
            editBtnClass: 'btn-edit-sector',
            deleteBtnClass: 'btn-delete-sector',
            restoreBtnClass: 'btn-restore-sector',
            hardDeleteBtnClass: 'btn-hard-delete-sector',
            onEditPreFill: (d) => {
                document.getElementById('edit-sector-id').value = d.id;
                document.getElementById('edit-sector-code').value = d.kode_sektor || '';
                document.getElementById('edit-sector-short').value = d.nama_pendek || '';
                document.getElementById('edit-sector-name').value = d.nama_sektor || '';
                document.getElementById('edit-sector-share-local').value = d.porsi_daerah || 0;
                document.getElementById('edit-sector-share-prov').value = d.porsi_provinsi || 0;
                document.getElementById('edit-sector-growth-local').value = d.pertumbuhan_daerah || 0;
                document.getElementById('edit-sector-growth-prov').value = d.pertumbuhan_provinsi || 0;
                document.getElementById('edit-sector-workers').value = d.tenaga_kerja_kini || 0;
                document.getElementById('edit-sector-workers-prov').value = d.tenaga_kerja_provinsi_kini || 0;
            }
        });
    }

    // 4. MAP PAGE
    function initMapPage() {
        const groupSelect = document.getElementById('map-group-select');
        const indicatorSelect = document.getElementById('map-indicator-select');
        const levelSelect = document.getElementById('map-level-select');
        const markersGroup = document.getElementById('layer-markers');
        const legendContainer = document.getElementById('legend-items-container');
        const locDetailName = document.getElementById('loc-detail-name');
        const locBodyContainer = document.getElementById('loc-body-container');
        const searchInput = document.getElementById('map-search-input');
        const svg = document.getElementById('banyuwangi-svg');
        const btnZoomIn = document.getElementById('btn-zoom-in');
        const btnZoomOut = document.getElementById('btn-zoom-out');
        const zoomText = document.getElementById('zoom-level-text');
        const btnExportMap = document.getElementById('btn-export-map');
        const hotspotLayer = document.getElementById('layer-hotspots');
        const hotspotBox = document.getElementById('hotspot-note-box');
        const modeTabs = document.getElementById('map-mode-tabs');

        let zoomLevel = 1.0;
        let selectedDistrict = 'Banyuwangi';
        let activeMode = 'Thematic Layer';

        const mapGroups = {
            'Aktivitas ekonomi': ['Pertumbuhan ekonomi', 'PDRB per kapita', 'Kontribusi sektor'],
            'Kesejahteraan': ['Tingkat kemiskinan', 'IPM', 'Ketimpangan'],
            'Investasi': ['Indeks investasi kecamatan', 'Persebaran proyek'],
            'Produksi sektoral': ['Produksi pertanian', 'Produksi perikanan'],
            'Ketenagakerjaan': ['Tingkat Pengangguran Terbuka', 'Konsentrasi pekerja'],
            'Pariwisata': ['Destinasi wisata', 'Kunjungan wisatawan'],
            'Harga': ['Harga beras medium', 'Stabilitas pangan']
        };

        function updateIndicators() {
            const grp = groupSelect ? groupSelect.value : 'Aktivitas ekonomi';
            const list = mapGroups[grp] || ['Pertumbuhan ekonomi'];
            if (indicatorSelect) {
                indicatorSelect.innerHTML = list.map(item => `<option value="${item}">${item}</option>`).join('');
            }
            renderMap();
        }

        function renderMap() {
            const ind = indicatorSelect ? indicatorSelect.value : 'Pertumbuhan ekonomi';
            const isGrowth = ind.includes('Pertumbuhan');
            const isPov = ind.includes('kemiskinan');
            const isInv = ind.includes('investasi');

            // Values by district
            const vals = AppState.districts.map(d => {
                if (isPov) return d.poverty;
                if (isInv) return d.investment;
                return d.growth;
            });

            const minV = Math.min(...vals);
            const maxV = Math.max(...vals);
            const range = maxV - minV || 1;

            const getClass = v => Math.min(5, Math.floor(((v - minV) / range) * 5) + 1);

            // Render Legend
            if (legendContainer) {
                legendContainer.innerHTML = [1, 2, 3, 4, 5].map(cls => {
                    const low = minV + (range * (cls - 1)) / 5;
                    const high = minV + (range * cls) / 5;
                    return `
                        <span>
                            <i class="l${cls}"></i>
                            <b>Kelas ${cls}</b>
                            <small>${formatNum(low, 1)} – ${formatNum(high, 1)}</small>
                        </span>`;
                }).join('');
            }

            // Render SVG District Markers
            if (markersGroup) {
                const searchQ = searchInput ? searchInput.value.toLowerCase().trim() : '';
                markersGroup.innerHTML = AppState.districts.map(d => {
                    const v = isPov ? d.poverty : (isInv ? d.investment : d.growth);
                    const cls = getClass(v);
                    const isSelected = d.name === selectedDistrict;
                    const matchesSearch = !searchQ || d.name.toLowerCase().includes(searchQ);
                    if (!matchesSearch) return '';

                    return `
                        <g class="area-marker l${cls} ${isSelected ? 'selected' : ''}" data-name="${d.name}" style="cursor:pointer;">
                            <circle cx="${d.coord_x}" cy="${d.coord_y}" r="${isSelected ? 12 : 8}"/>
                            <text x="${d.coord_x + 12}" y="${d.coord_y + 3}">${d.name}</text>
                        </g>`;
                }).join('');

                markersGroup.querySelectorAll('.area-marker').forEach(marker => {
                    marker.addEventListener('click', function () {
                        selectedDistrict = this.getAttribute('data-name');
                        renderMap();
                    });
                });
            }

            // Render Selected District Detail Aside
            const dist = AppState.districts.find(d => d.name === selectedDistrict) || AppState.districts[0];
            if (locDetailName && dist) {
                locDetailName.textContent = dist.name;
            }

            if (locBodyContainer && dist) {
                const currentVal = isPov ? dist.poverty : (isInv ? dist.investment : dist.growth);
                const avgV = mean(vals);
                const gapV = currentVal - avgV;

                locBodyContainer.innerHTML = `
                    <div class="location-score">
                        <div>
                            <span>${ind}</span>
                            <strong>${formatNum(currentVal, 2)}${isInv ? ' poin' : '%'}</strong>
                        </div>
                        <span class="badge ${dist.risk === 'Kritis' ? 'danger' : (dist.risk === 'Waspada' ? 'warning' : 'success')}">
                            ${dist.risk}
                        </span>
                    </div>

                    <div class="spatial-position">
                        <span>
                            <small>Posisi terhadap rerata daerah</small>
                            <strong class="${gapV >= 0 ? 'positive' : 'negative'}">${gapV >= 0 ? '+' : ''}${formatNum(gapV, 2)} pp</strong>
                        </span>
                        <div><i style="width: ${Math.min(100, Math.max(10, (currentVal / maxV) * 100))}%"></i></div>
                        <small>Rata-rata 12 kecamatan: ${formatNum(avgV, 2)}${isInv ? ' poin' : '%'}</small>
                    </div>

                    <div class="data-list">
                        <span><small>Potensi Unggulan</small><strong>${dist.potential}</strong></span>
                        <span><small>Indeks Investasi</small><strong>${formatNum(dist.investment, 2)} poin</strong></span>
                        <span><small>Tingkat Kemiskinan</small><strong>${formatPct(dist.poverty)}</strong></span>
                        <span><small>Pertumbuhan Ekonomi</small><strong>${formatPct(dist.growth)}</strong></span>
                    </div>

                    <button class="button secondary" id="btn-export-district-profile" style="width:100%;">Unduh Profil Wilayah</button>`;

                const btnExpDist = document.getElementById('btn-export-district-profile');
                if (btnExpDist) {
                    btnExpDist.addEventListener('click', () => {
                        downloadCSV(`profil-kecamatan-${dist.name.toLowerCase()}.csv`, [
                            ['Kecamatan', 'Pertumbuhan Ekonomi (%)', 'Kemiskinan (%)', 'Indeks Investasi', 'Potensi', 'Status Risiko'],
                            [dist.name, dist.growth, dist.poverty, dist.investment, dist.potential, dist.risk]
                        ]);
                    });
                }
            }

            // Hotspot layer toggle
            if (hotspotLayer && hotspotBox) {
                if (activeMode === 'Hotspot Analysis') {
                    hotspotLayer.style.display = 'block';
                    hotspotBox.style.display = 'flex';
                    hotspotLayer.innerHTML = AppState.districts.filter(d => d.risk !== 'Normal').map(d =>
                        `<circle cx="${d.coord_x}" cy="${d.coord_y}" r="${d.risk === 'Kritis' ? 44 : 28}" class="${d.risk === 'Kritis' ? 'critical' : 'warning'}"/>`
                    ).join('');
                } else {
                    hotspotLayer.style.display = 'none';
                    hotspotBox.style.display = 'none';
                }
            }
        }

        if (groupSelect) groupSelect.addEventListener('change', updateIndicators);
        if (indicatorSelect) indicatorSelect.addEventListener('change', renderMap);
        if (searchInput) searchInput.addEventListener('input', renderMap);

        // Zoom Controls
        if (btnZoomIn && svg) {
            btnZoomIn.addEventListener('click', () => {
                zoomLevel = Math.min(2.0, zoomLevel + 0.15);
                svg.style.transform = `scale(${zoomLevel})`;
                if (zoomText) zoomText.textContent = Math.round(zoomLevel * 100) + '%';
            });
        }
        if (btnZoomOut && svg) {
            btnZoomOut.addEventListener('click', () => {
                zoomLevel = Math.max(0.7, zoomLevel - 0.15);
                svg.style.transform = `scale(${zoomLevel})`;
                if (zoomText) zoomText.textContent = Math.round(zoomLevel * 100) + '%';
            });
        }

        // Layer toggles
        document.querySelectorAll('.map-layer-cb').forEach(cb => {
            cb.addEventListener('change', function () {
                const layerId = {
                    'Batas kecamatan': 'layer-boundary',
                    'Infrastruktur ekonomi': 'layer-infra',
                    'Pusat industri': 'layer-industry',
                    'Konsentrasi tenaga kerja': 'layer-employment',
                    'Potensi sektoral': 'layer-potential',
                    'Destinasi wisata': 'layer-tourism'
                }[this.value];

                if (layerId) {
                    const el = document.getElementById(layerId);
                    if (el) el.style.display = this.checked ? 'block' : 'none';
                }
            });
        });

        // Mode tabs
        if (modeTabs) {
            modeTabs.querySelectorAll('button').forEach(btn => {
                btn.addEventListener('click', function () {
                    modeTabs.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    activeMode = this.getAttribute('data-mode');
                    const lbl = document.getElementById('active-map-mode-label');
                    if (lbl) lbl.textContent = activeMode;
                    renderMap();
                });
            });
        }

        if (btnExportMap) {
            btnExportMap.addEventListener('click', () => {
                downloadCSV('peta-spasial-banyuwangi.csv', [
                    ['Kecamatan', 'Pertumbuhan Ekonomi (%)', 'Kemiskinan (%)', 'Indeks Investasi', 'Potensi', 'Risiko'],
                    ...AppState.districts.map(d => [d.name, d.growth, d.poverty, d.investment, d.potential, d.risk])
                ]);
            });
        }

        updateIndicators();

        const metricSelect = document.getElementById('map-chart-metric-select');
        const mapChartTitle = document.getElementById('map-chart-title');
        const mapChartDesc = document.getElementById('map-chart-desc');
        const mapChartContainer = document.getElementById('map-comparison-chart-container');

        function renderMapComparisonChart() {
            if (!mapChartContainer) return;
            const metric = metricSelect ? metricSelect.value : 'growth';

            const metricConfigs = {
                'growth': { label: 'Pertumbuhan Ekonomi', unit: '%', key: 'growth', decimals: 2 },
                'poverty': { label: 'Tingkat Kemiskinan', unit: '%', key: 'poverty', decimals: 2 },
                'pdrbShare': { label: 'Porsi PDRB Daerah', unit: '%', key: 'pdrbShare', decimals: 2 },
                'investment': { label: 'Indeks Investasi', unit: ' poin', key: 'investment', decimals: 2 },
                'density': { label: 'Kepadatan Penduduk', unit: ' jiwa/km²', key: 'density', decimals: 0 }
            };

            const cfg = metricConfigs[metric] || metricConfigs['growth'];
            if (mapChartTitle) mapChartTitle.textContent = `Grafik Komparasi ${cfg.label} Antar-Kecamatan`;
            if (mapChartDesc) mapChartDesc.textContent = `Peringkat capaian ${cfg.label.toLowerCase()} seluruh kecamatan di Kabupaten Banyuwangi.`;

            const sortedDistricts = [...AppState.districts].sort((a, b) => {
                const valA = parseFloat(a[cfg.key] || 0);
                const valB = parseFloat(b[cfg.key] || 0);
                return valB - valA;
            });

            const barData = sortedDistricts.map(d => ({
                label: d.name,
                value: parseFloat(d[cfg.key] || 0),
                subLabel: `Kategori: ${d.category || 'Kembang'} • Potensi: ${d.potential || 'Unggulan'}`,
                badge: d.name === selectedDistrict ? 'Kecamatan Terpilih' : (d.risk || ''),
                badgeClass: d.name === selectedDistrict ? 'primary' : (d.risk === 'Kritis' ? 'danger' : (d.risk === 'Waspada' ? 'warning' : 'success')),
                isHighlight: d.name === selectedDistrict
            }));

            mapChartContainer.innerHTML = createHorizontalBarChartSVG(barData, { unit: cfg.unit, decimals: cfg.decimals });

            // Klik bar pada grafik untuk langsung memilih dan menyorot kecamatan pada peta
            mapChartContainer.querySelectorAll('.h-bar-row').forEach(row => {
                row.addEventListener('click', function () {
                    const label = this.getAttribute('data-label');
                    if (label) {
                        selectedDistrict = label;
                        renderMap();
                    }
                });
            });
        }

        if (metricSelect) {
            metricSelect.addEventListener('change', renderMapComparisonChart);
        }

        renderMapComparisonChart();

        // Setup CRUD & Soft Delete for Peta Ekonomi (Kecamatan)
        setupModuleCrud({
            controller: 'peta_ekonomi',
            addModalId: 'add-district-modal',
            editModalId: 'edit-district-modal',
            openAddBtnIds: ['btn-open-add-district', 'btn-open-add-district-2'],
            closeAddBtnIds: ['modal-btn-close-add-district', 'modal-btn-cancel-add-district'],
            closeEditBtnIds: ['modal-btn-close-edit-district', 'modal-btn-cancel-edit-district'],
            formAddId: 'form-add-district',
            formEditId: 'form-edit-district',
            addErrorId: 'add-district-error',
            editErrorId: 'edit-district-error',
            editBtnClass: 'btn-edit-district',
            deleteBtnClass: 'btn-delete-district',
            restoreBtnClass: 'btn-restore-district',
            hardDeleteBtnClass: 'btn-hard-delete-district',
            onEditPreFill: (d) => {
                document.getElementById('edit-district-id').value = d.id;
                document.getElementById('edit-district-name').value = d.name || d.nama_kecamatan || '';
                document.getElementById('edit-district-share').value = d.pdrbShare !== undefined ? d.pdrbShare : (d.porsi_pdrb || 0);
                document.getElementById('edit-district-growth').value = d.growth !== undefined ? d.growth : (d.pertumbuhan || 0);
                document.getElementById('edit-district-poverty').value = d.poverty !== undefined ? d.poverty : (d.kemiskinan || 0);
                document.getElementById('edit-district-density').value = d.density !== undefined ? d.density : (d.kepadatan || 0);
                document.getElementById('edit-district-category').value = d.category || d.kategori || 'Berkembang';
                document.getElementById('edit-district-lat').value = d.lat !== undefined ? d.lat : (d.garis_lintang || '');
                document.getElementById('edit-district-lng').value = d.lng !== undefined ? d.lng : (d.garis_bujur || '');
            }
        });
    }

    // 5. OUTLOOK PAGE
    function initOutlookPage() {
        const btnModeForecast = document.getElementById('btn-mode-forecast');
        const btnModeScenario = document.getElementById('btn-mode-scenario');
        const forecastSec = document.getElementById('forecast-section-container');
        const scenarioSec = document.getElementById('scenario-section-container');
        const noticeTitle = document.getElementById('outlook-notice-title');
        const noticeDesc = document.getElementById('outlook-notice-desc');
        const fcIndicator = document.getElementById('fc-indicator');
        const fcHistory = document.getElementById('fc-history');
        const fcHorizon = document.getElementById('fc-horizon');
        const fcMethod = document.getElementById('fc-method');
        const btnRecalc = document.getElementById('btn-recalculate-forecast');
        const summaryCards = document.getElementById('fc-summary-cards');
        const chartCanvas = document.getElementById('fc-chart-canvas');
        const resultTableWrap = document.getElementById('fc-result-table-wrap');
        const modelDetails = document.getElementById('fc-model-details');
        const btnExport = document.getElementById('btn-export-outlook');

        // Mode switch
        btnModeForecast.addEventListener('click', () => {
            btnModeForecast.classList.add('active');
            btnModeScenario.classList.remove('active');
            forecastSec.style.display = 'block';
            scenarioSec.style.display = 'none';
            noticeTitle.textContent = 'Estimasi model statistik indikator ekonomi';
            noticeDesc.textContent = 'Metode yang tersedia mencakup Tren Linear, Rata-rata Bergerak 3 Tahun, dan Pemulusan Eksponensial sederhana.';
        });

        btnModeScenario.addEventListener('click', () => {
            btnModeScenario.classList.add('active');
            btnModeForecast.classList.remove('active');
            scenarioSec.style.display = 'block';
            forecastSec.style.display = 'none';
            noticeTitle.textContent = 'Simulasi skenario kebijakan perekonomian';
            noticeDesc.textContent = 'Gunakan slider sensitivitas investasi, pertanian, dan pariwisata untuk melihat simulasi dampak terhadap pertumbuhan ekonomi daerah.';
        });

        const seriesMap = {
            'Pertumbuhan ekonomi': { vals: AppState.series.growth, unit: '%' },
            'Inflasi': { vals: AppState.series.inflation, unit: '%' },
            'Tingkat pengangguran': { vals: AppState.series.tpt, unit: '%' },
            'Pertumbuhan investasi': { vals: AppState.series.investmentGrowth, unit: '%' },
            'Tingkat kemiskinan': { vals: AppState.series.poverty, unit: '%' },
            'IPM': { vals: AppState.series.ipm, unit: ' poin' },
            'PDRB per kapita': { vals: AppState.series.perCapita, unit: ' juta' }
        };

        function runForecast() {
            const ind = fcIndicator ? fcIndicator.value : 'Pertumbuhan ekonomi';
            const histLen = parseInt(fcHistory ? fcHistory.value : 10);
            const horizon = parseInt(fcHorizon ? fcHorizon.value : 3);
            const method = fcMethod ? fcMethod.value : 'Tren Linear';

            const sData = seriesMap[ind] || seriesMap['Pertumbuhan ekonomi'];
            const sliceStart = histLen === 5 ? 5 : 0;
            const history = sData.vals.slice(sliceStart);
            const subYears = AppState.years.slice(sliceStart);

            const res = forecast(history, horizon, method);
            const futureYears = Array.from({ length: horizon }, (_, i) => 2025 + i);
            const allYears = [...subYears, ...futureYears];

            if (summaryCards) {
                summaryCards.innerHTML = `
                    <div><span>Estimasi 2025</span><strong>${formatNum(res.forecast[0])}${sData.unit}</strong><small>Metode ${method}</small><span class="badge info">Model</span></div>
                    <div><span>Batas Bawah</span><strong>${formatNum(res.lower[0])}${sData.unit}</strong><small>Interval 95% (indikatif)</small><span class="badge neutral">Min</span></div>
                    <div><span>Batas Atas</span><strong>${formatNum(res.upper[0])}${sData.unit}</strong><small>Interval 95% (indikatif)</small><span class="badge neutral">Max</span></div>
                    <div><span>RMSE Galat</span><strong>${formatNum(res.error, 3)}</strong><small>Residual in-sample</small><span class="badge warning">Galat</span></div>`;
            }

            if (chartCanvas) {
                chartCanvas.innerHTML = createForecastChartSVG(history, res.forecast, res.lower, res.upper, allYears);
            }

            if (resultTableWrap) {
                resultTableWrap.innerHTML = `
                    <div class="result-table-wrap" style="margin-top:14px;">
                        <table class="result-table">
                            <thead><tr><th>Tahun Proyeksi</th><th>Estimasi Nilai</th><th>Batas Bawah Indikatif</th><th>Batas Atas Indikatif</th></tr></thead>
                            <tbody>
                                ${res.forecast.map((val, i) => `
                                    <tr>
                                        <td><strong>Tahun ${2025 + i}</strong></td>
                                        <td><strong>${formatNum(val, 2)}${sData.unit}</strong></td>
                                        <td>${formatNum(res.lower[i], 2)}${sData.unit}</td>
                                        <td>${formatNum(res.upper[i], 2)}${sData.unit}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>`;
            }

            if (modelDetails) {
                modelDetails.innerHTML = `
                    <div class="model-row"><span>Metode Proyeksi</span><strong>${method}</strong></div>
                    <div class="model-row"><span>Observasi Historis</span><strong>${history.length} tahun</strong></div>
                    <div class="model-row"><span>Horizon Prediksi</span><strong>${horizon} tahun</strong></div>
                    <div class="model-row"><span>RMSE Residual</span><strong>${formatNum(res.error, 3)}</strong></div>`;
            }
        }

        if (btnRecalc) btnRecalc.addEventListener('click', runForecast);
        if (fcIndicator) fcIndicator.addEventListener('change', runForecast);
        if (fcHistory) fcHistory.addEventListener('change', runForecast);
        if (fcHorizon) fcHorizon.addEventListener('change', runForecast);
        if (fcMethod) fcMethod.addEventListener('change', runForecast);

        // Scenario calculation
        const sliderInv = document.getElementById('slider-inv');
        const sliderAgri = document.getElementById('slider-agri');
        const sliderTour = document.getElementById('slider-tour');
        const heroGrowth = document.getElementById('scenario-hero-growth');
        const heroImpact = document.getElementById('scenario-hero-impact');

        let scenarioBaseline = 5.18;

        function updateScenario() {
            const inv = parseInt(sliderInv ? sliderInv.value : 10);
            const agri = parseInt(sliderAgri ? sliderAgri.value : 2);
            const tour = parseInt(sliderTour ? sliderTour.value : 7);

            document.getElementById('val-slider-inv').textContent = (inv >= 0 ? '+' : '') + inv + '%';
            document.getElementById('val-slider-agri').textContent = (agri >= 0 ? '+' : '') + agri + '%';
            document.getElementById('val-slider-tour').textContent = (tour >= 0 ? '+' : '') + tour + '%';

            document.getElementById('bar-impact-inv-val').textContent = (inv >= 0 ? '+' : '') + inv + '%';
            document.getElementById('bar-impact-agri-val').textContent = (agri >= 0 ? '+' : '') + agri + '%';
            document.getElementById('bar-impact-tour-val').textContent = (tour >= 0 ? '+' : '') + tour + '%';

            // Sensitivity impact coefficients
            const impact = (inv - 10) * 0.025 + (agri - 2) * 0.035 + (tour - 7) * 0.018;
            const finalGrowth = scenarioBaseline + impact;

            if (heroGrowth) heroGrowth.innerHTML = formatNum(finalGrowth, 2) + '<small>%</small>';
            if (heroImpact) {
                heroImpact.textContent = (impact >= 0 ? '+' : '') + formatNum(impact, 2) + ' pp';
                heroImpact.className = impact >= 0 ? 'positive' : 'negative';
            }

            const scenChart = document.getElementById('scenario-comparison-chart');
            if (scenChart) {
                const sYears = [2024, 2025, 2026, 2027];
                const baseVals = [5.18, 5.25, 5.32, 5.40];
                const scenVals = [5.18, Number(finalGrowth.toFixed(2)), Number((finalGrowth + 0.12).toFixed(2)), Number((finalGrowth + 0.25).toFixed(2))];
                scenChart.innerHTML = createLineChartSVG(sYears, [
                    { name: 'Baseline Proyeksi', values: baseVals, className: 'series-0' },
                    { name: 'Simulasi Skenario', values: scenVals, className: 'series-1' }
                ], 180, '%');
            }
        }

        if (sliderInv) sliderInv.addEventListener('input', updateScenario);
        if (sliderAgri) sliderAgri.addEventListener('input', updateScenario);
        if (sliderTour) sliderTour.addEventListener('input', updateScenario);

        document.querySelectorAll('.scenario-tabs button').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.scenario-tabs button').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const sc = this.getAttribute('data-scenario');
                if (sc === 'Optimistis') {
                    scenarioBaseline = 5.73;
                    if (sliderInv) sliderInv.value = 16;
                    if (sliderAgri) sliderAgri.value = 5;
                    if (sliderTour) sliderTour.value = 14;
                } else if (sc === 'Pesimistis') {
                    scenarioBaseline = 4.63;
                    if (sliderInv) sliderInv.value = 2;
                    if (sliderAgri) sliderAgri.value = -3;
                    if (sliderTour) sliderTour.value = 0;
                } else {
                    scenarioBaseline = 5.18;
                    if (sliderInv) sliderInv.value = 10;
                    if (sliderAgri) sliderAgri.value = 2;
                    if (sliderTour) sliderTour.value = 7;
                }
                updateScenario();
            });
        });

        if (btnExport) {
            btnExport.addEventListener('click', () => {
                downloadCSV('proyeksi-ekonomi-banyuwangi.csv', [
                    ['Tahun', 'Pertumbuhan Ekonomi (%)', 'Batas Bawah', 'Batas Atas'],
                    [2025, 5.25, 4.80, 5.70],
                    [2026, 5.32, 4.75, 5.89],
                    [2027, 5.40, 4.70, 6.10]
                ]);
            });
        }

        runForecast();
        updateScenario();

        // Setup CRUD & Soft Delete for Prospek Ekonomi
        setupModuleCrud({
            controller: 'prospek_ekonomi',
            addModalId: 'add-prospek-modal',
            editModalId: 'edit-prospek-modal',
            openAddBtnIds: ['btn-open-add-prospek', 'btn-open-add-prospek-2'],
            closeAddBtnIds: ['modal-btn-close-add-prospek', 'modal-btn-cancel-add-prospek'],
            closeEditBtnIds: ['modal-btn-close-edit-prospek', 'modal-btn-cancel-edit-prospek'],
            formAddId: 'form-add-prospek',
            formEditId: 'form-edit-prospek',
            addErrorId: 'add-prospek-error',
            editErrorId: 'edit-prospek-error',
            editBtnClass: 'btn-edit-prospek',
            deleteBtnClass: 'btn-delete-prospek',
            restoreBtnClass: 'btn-restore-prospek',
            hardDeleteBtnClass: 'btn-hard-delete-prospek',
            onEditPreFill: (d) => {
                document.getElementById('edit-prospek-id').value = d.id;
                document.getElementById('edit-prospek-year').value = d.tahun || '';
                document.getElementById('edit-prospek-growth').value = d.pertumbuhan_ekonomi || 0;
                document.getElementById('edit-prospek-growth-jatim').value = d.pertumbuhan_jatim || 0;
                document.getElementById('edit-prospek-growth-nasional').value = d.pertumbuhan_nasional || 0;
                document.getElementById('edit-prospek-tpt').value = d.tingkat_pengangguran || 0;
                document.getElementById('edit-prospek-poverty').value = d.tingkat_kemiskinan || 0;
                document.getElementById('edit-prospek-inflation').value = d.inflasi || 0;
                document.getElementById('edit-prospek-ipm').value = d.ipm || 0;
                document.getElementById('edit-prospek-investment').value = d.pertumbuhan_investasi || 0;
                document.getElementById('edit-prospek-per-capita').value = d.pdrb_per_kapita || 0;
            }
        });
    }

    // 6. EWS PAGE
    function initEwsPage() {
        const filterTabs = document.getElementById('ews-filter-tabs');
        const statsContainer = document.getElementById('ews-stats-container');
        const tableRows = document.getElementById('ews-table-rows');
        const distBars = document.getElementById('ews-distribution-bars');
        const modalBackdrop = document.getElementById('ews-modal-backdrop');
        const modalClose = document.getElementById('modal-btn-close');
        const modalCancel = document.getElementById('modal-btn-cancel');
        const modalSave = document.getElementById('modal-btn-save-threshold');
        const modalToggleFollow = document.getElementById('modal-btn-toggle-followup');
        const btnExport = document.getElementById('btn-export-ews');

        let activeFilter = 'Semua';
        let selectedAlertCode = null;

        // Seed AppState.ews from server-rendered global state if needed
        if ((!AppState.ews || AppState.ews.length === 0) && window.__EWS_DATA__ && Array.isArray(window.__EWS_DATA__)) {
            AppState.ews = window.__EWS_DATA__;
        }

        function renderEWS() {
            let normalC = 0, warnC = 0, critC = 0, followedC = 0;
            AppState.ews.forEach(item => {
                const st = item.status || item.status_risiko || 'Normal';
                if (st === 'Normal') normalC++;
                else if (st === 'Waspada') warnC++;
                else if (st === 'Kritis') critC++;

                if (item.is_followed_up || item.sudah_ditindaklanjuti) followedC++;
            });

            if (statsContainer) {
                statsContainer.innerHTML = `
                    <div class="risk-stat danger"><span>Status Kritis</span><strong>${critC}</strong><small>Melampaui ambang batas</small></div>
                    <div class="risk-stat warning"><span>Status Waspada</span><strong>${warnC}</strong><small>Mendekati ambang batas</small></div>
                    <div class="risk-stat success"><span>Status Normal</span><strong>${normalC}</strong><small>Dalam batas aman</small></div>
                    <div class="risk-stat neutral"><span>Total Indikator</span><strong>${AppState.ews.length}</strong><small>Dipantau sistem (${followedC} ditindaklanjuti)</small></div>`;
            }

            const filtered = AppState.ews.filter(item => {
                const st = item.status || item.status_risiko || 'Normal';
                return activeFilter === 'Semua' || st === activeFilter;
            });

            if (tableRows) {
                tableRows.innerHTML = `
                    <div class="risk-table-header">
                        <span>Indikator Pemantauan</span>
                        <span>Nilai Riil</span>
                        <span>Batas Ambang</span>
                        <span>Status Risiko</span>
                        <span style="text-align:right;">Aksi</span>
                    </div>
                    ${filtered.map(item => {
                        const st = item.status || item.status_risiko || 'Normal';
                        const badgeCls = (st === 'Kritis') ? 'danger' : ((st === 'Waspada') ? 'warning' : 'success');
                        const rowStateCls = (st === 'Kritis') ? 'alerted' : ((st === 'Waspada') ? 'warning-row' : 'normal-row');
                        const dir = item.direction || item.arah_risiko || 'above';
                        const dirText = (dir === 'above') ? 'Peringatan jika > Ambang' : 'Peringatan jika < Ambang';
                        const unit = item.unit || item.satuan || '%';
                        const code = item.code || item.kode_indikator || '';
                        const name = item.name || item.nama_indikator || '';
                        const val = Number(item.value !== undefined ? item.value : (item.nilai_terkini || 0));
                        const th = Number(item.threshold !== undefined ? item.threshold : (item.ambang_batas || 0));
                        return `
                        <div class="risk-row btn-alert-row ${rowStateCls}" data-code="${code}" role="button" tabindex="0" style="cursor:pointer;">
                            <div class="risk-indicator">
                                <strong>${name}</strong>
                                <small>
                                    <code>${code}</code>
                                    <span>• ${dirText}</span>
                                </small>
                            </div>
                            <div class="risk-value">
                                <span>Nilai Riil</span>
                                <strong>${formatNum(val, 2)} <span style="font-size:11px; font-weight:500; color:var(--muted);">${unit}</span></strong>
                            </div>
                            <div class="risk-threshold">
                                <span>Batas Ambang</span>
                                <strong>${formatNum(th, 2)} <span style="font-size:11px; font-weight:500; color:var(--muted);">${unit}</span></strong>
                            </div>
                            <div class="risk-status">
                                <span class="badge ${badgeCls}">${st}</span>
                            </div>
                            <div class="risk-action">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                            </div>
                        </div>
                        `;
                    }).join('')}`;

                tableRows.querySelectorAll('.btn-alert-row').forEach(row => {
                    row.addEventListener('click', function () {
                        openAlertModal(this.getAttribute('data-code'));
                    });
                });
            }

            if (distBars) {
                distBars.innerHTML = AppState.ews.map(item => {
                    const st = item.status || item.status_risiko || 'Normal';
                    const badgeCls = (st === 'Kritis') ? 'danger' : ((st === 'Waspada') ? 'warning' : 'success');
                    const dist = Math.min(100, Math.max(5, item.distance || 20));
                    return `
                    <div>
                        <span>${item.name || item.nama_indikator}</span>
                        <div><i class="${badgeCls}" style="width: ${dist}%"></i></div>
                        <span class="badge ${badgeCls}">${st}</span>
                    </div>
                `;
                }).join('');
            }

            const ewsChartContainer = document.getElementById('ews-comparison-chart-container');
            if (ewsChartContainer && AppState.ews.length > 0) {
                const ewsBars = AppState.ews.map(item => {
                    const val = Number(item.value !== undefined ? item.value : (item.nilai_terkini || 0));
                    const th = Number(item.threshold !== undefined ? item.threshold : (item.ambang_batas || 0));
                    const unit = item.unit || item.satuan || '%';
                    const dir = item.direction || item.arah_risiko || 'above';
                    const diffPct = th !== 0 ? ((val - th) / Math.abs(th)) * 100 : 0;
                    const st = item.status || item.status_risiko || 'Normal';
                    const badgeCls = (st === 'Kritis') ? 'danger' : ((st === 'Waspada') ? 'warning' : 'success');
                    return {
                        label: item.name || item.nama_indikator,
                        value: val,
                        subLabel: `Ambang: ${formatNum(th, 2)} ${unit} • Deviasi: ${diffPct >= 0 ? '+' : ''}${formatNum(diffPct, 1)}% (${dir === 'above' ? 'Peringatan saat > Ambang' : 'Peringatan saat < Ambang'})`,
                        badge: st,
                        badgeClass: badgeCls,
                        color: (st === 'Kritis') ? 'linear-gradient(90deg, #ef4444, #f87171)' : ((st === 'Waspada') ? 'linear-gradient(90deg, #f59e0b, #fbbf24)' : 'linear-gradient(90deg, #10b981, #34d399)')
                    };
                });
                ewsChartContainer.innerHTML = createHorizontalBarChartSVG(ewsBars, { unit: '', decimals: 2 });
            }

            const pBar = document.getElementById('ews-progress-bar');
            const pTxt = document.getElementById('ews-progress-text');
            const pPct = document.getElementById('ews-progress-pct');
            const pct = AppState.ews.length ? Math.round((followedC / AppState.ews.length) * 100) : 0;
            if (pBar) pBar.style.width = pct + '%';
            if (pTxt) pTxt.textContent = `${followedC} dari ${AppState.ews.length} indikator ditandai`;
            if (pPct) pPct.textContent = pct + '%';
        }

        function openAlertModal(code) {
            selectedAlertCode = code;
            let item = AppState.ews.find(i => (i.code === code || i.kode_indikator === code));
            if (!item && window.__EWS_DATA__ && Array.isArray(window.__EWS_DATA__)) {
                item = window.__EWS_DATA__.find(i => (i.code === code || i.kode_indikator === code));
            }
            if (!item || !modalBackdrop) return;

            const name = item.name || item.nama_indikator || '';
            const src = item.source || item.sumber_data || 'Sistem EWS Bappeda';
            const val = Number(item.value !== undefined ? item.value : (item.nilai_terkini || 0));
            const th = Number(item.threshold !== undefined ? item.threshold : (item.ambang_batas || 0));
            const unit = item.unit || item.satuan || '%';
            const st = item.status || item.status_risiko || 'Normal';
            const dir = item.direction || item.arah_risiko || 'above';
            const distance = item.distance !== undefined ? Number(item.distance) : (th !== 0 ? Math.abs((val - th) / th * 100) : 0);
            const isBreached = item.breached !== undefined ? Boolean(item.breached) : ((dir === 'above') ? (val > th) : (val < th));

            document.getElementById('modal-alert-name').textContent = name;
            document.getElementById('modal-alert-meta').textContent = `${src} • ${code}`;
            const badge = document.getElementById('modal-alert-badge');
            badge.textContent = st.toUpperCase();
            badge.className = 'badge ' + (st === 'Kritis' ? 'danger' : (st === 'Waspada' ? 'warning' : 'success'));

            document.getElementById('modal-metric-val').textContent = `${formatNum(val, 2)} ${unit}`;
            document.getElementById('modal-metric-th').textContent = `${formatNum(th, 2)} ${unit}`;
            const devEl = document.getElementById('modal-metric-dev');
            devEl.textContent = `${isBreached ? '+' : ''}${formatNum(distance, 1)}%`;
            devEl.className = isBreached ? 'negative' : 'positive';

            document.getElementById('modal-editor-label').textContent = `${formatNum(th, 2)} ${unit}`;
            document.getElementById('modal-input-threshold').value = th;

            document.getElementById('modal-alert-interpretation').textContent = isBreached
                ? `Nilai telah ${dir === 'above' ? 'melampaui' : 'berada di bawah'} ambang toleransi sebesar ${formatNum(distance, 1)}%. Segera lakukan evaluasi terpadu.`
                : `Nilai saat ini (${formatNum(val, 2)} ${unit}) masih berada dalam zona aman di bawah batas toleransi.`;

            const btnFollow = document.getElementById('modal-btn-toggle-followup');
            if (btnFollow) {
                const isFollowed = Boolean(item.is_followed_up || item.sudah_ditindaklanjuti);
                btnFollow.textContent = isFollowed ? 'Batalkan Tindak Lanjut' : 'Tandai Ditindaklanjuti';
            }

            modalBackdrop.style.display = 'grid';
        }

        function closeModal() {
            if (modalBackdrop) modalBackdrop.style.display = 'none';
        }

        if (modalClose) modalClose.addEventListener('click', closeModal);
        if (modalCancel) modalCancel.addEventListener('click', closeModal);
        if (modalBackdrop) {
            modalBackdrop.addEventListener('click', e => {
                if (e.target === modalBackdrop) closeModal();
            });
        }

        if (modalSave) {
            modalSave.addEventListener('click', () => {
                const val = parseFloat(document.getElementById('modal-input-threshold').value);
                if (selectedAlertCode && Number.isFinite(val)) {
                    const fd = new FormData();
                    fd.append('code', selectedAlertCode);
                    fd.append('threshold', val);

                    fetch(BASE_URL + 'api/update_threshold', { method: 'POST', body: fd })
                        .then(res => res.json())
                        .then(() => {
                            const target = AppState.ews.find(i => (i.code === selectedAlertCode || i.kode_indikator === selectedAlertCode));
                            if (target) {
                                const v = Number(target.value !== undefined ? target.value : (target.nilai_terkini || 0));
                                const d = target.direction || target.arah_risiko || 'above';
                                target.threshold = val;
                                target.ambang_batas = val;
                                target.breached = (d === 'above') ? (v > val) : (v < val);
                                target.distance = (val === 0) ? (target.breached ? 100 : 0) : (Math.abs(v - val) / Math.abs(val) * 100);
                                target.status = !target.breached ? 'Normal' : (target.distance >= 10 ? 'Kritis' : 'Waspada');
                                target.status_risiko = target.status;
                            }
                            renderEWS();
                            closeModal();
                        });
                }
            });
        }

        if (modalToggleFollow) {
            modalToggleFollow.addEventListener('click', () => {
                if (selectedAlertCode) {
                    const fd = new FormData();
                    fd.append('code', selectedAlertCode);
                    fetch(BASE_URL + 'api/toggle_followup', { method: 'POST', body: fd })
                        .then(res => res.json())
                        .then(data => {
                            const target = AppState.ews.find(i => (i.code === selectedAlertCode || i.kode_indikator === selectedAlertCode));
                            if (target) {
                                target.is_followed_up = data.is_followed_up;
                                target.sudah_ditindaklanjuti = data.is_followed_up;
                            }
                            renderEWS();
                            closeModal();
                        });
                }
            });
        }

        if (filterTabs) {
            filterTabs.querySelectorAll('button').forEach(btn => {
                btn.addEventListener('click', function () {
                    filterTabs.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    activeFilter = this.getAttribute('data-status');
                    renderEWS();
                });
            });
        }

        if (btnExport) {
            btnExport.addEventListener('click', () => {
                downloadCSV('monitoring-ews-banyuwangi.csv', [
                    ['Kode', 'Indikator', 'Nilai Terkini', 'Threshold', 'Kondisi Risiko', 'Status'],
                    ...AppState.ews.map(i => [i.code || i.kode_indikator, i.name || i.nama_indikator, i.value || i.nilai_terkini, i.threshold || i.ambang_batas, i.direction || i.arah_risiko, i.status || i.status_risiko])
                ]);
            });
        }

        // Bind initial click handler for server-rendered rows immediately
        if (tableRows) {
            tableRows.querySelectorAll('.btn-alert-row').forEach(row => {
                row.addEventListener('click', function () {
                    openAlertModal(this.getAttribute('data-code'));
                });
            });
        }

        renderEWS();

        // Setup CRUD & Soft Delete for Early Warning
        setupModuleCrud({
            controller: 'early_warning',
            editBtnClass: 'btn-edit-ews',
            deleteBtnClass: 'btn-delete-ews',
            restoreBtnClass: 'btn-restore-ews',
            hardDeleteBtnClass: 'btn-hard-delete-ews',
            addModalId: 'add-ews-modal',
            editModalId: 'edit-ews-modal',
            openAddBtnIds: ['btn-open-add-ews', 'btn-open-add-ews-2'],
            closeAddBtnIds: ['modal-btn-close-add-ews', 'modal-btn-cancel-add-ews'],
            closeEditBtnIds: ['modal-btn-close-edit-ews', 'modal-btn-cancel-edit-ews'],
            formAddId: 'form-add-ews',
            formEditId: 'form-edit-ews',
            addErrorId: 'add-ews-error',
            editErrorId: 'edit-ews-error',
            populateForm: function (item) {
                document.getElementById('edit-ews-id').value = item.id;
                document.getElementById('edit-ews-code').value = item.kode_indikator || item.code || '';
                document.getElementById('edit-ews-unit').value = item.satuan || item.unit || '';
                document.getElementById('edit-ews-name').value = item.nama_indikator || item.name || '';
                document.getElementById('edit-ews-current').value = item.nilai_terkini !== undefined ? item.nilai_terkini : (item.value || '');
                document.getElementById('edit-ews-threshold').value = item.ambang_batas !== undefined ? item.ambang_batas : (item.threshold || '');
                document.getElementById('edit-ews-direction').value = item.arah_risiko || item.direction || 'above';
                document.getElementById('edit-ews-status').value = item.status_risiko || item.status || 'Normal';
            }
        });
    }

    // 7. INSIGHT PAGE
    function initInsightPage() {
        const modal = document.getElementById('insight-modal-backdrop');
        const modalTitle = document.getElementById('insight-modal-title');
        const modalBody = document.getElementById('insight-modal-body');
        const modalClose = document.getElementById('insight-modal-close');
        const modalOk = document.getElementById('insight-modal-btn-ok');

        document.querySelectorAll('.btn-read-insight').forEach(btn => {
            btn.addEventListener('click', function () {
                const title = this.getAttribute('data-title');
                const detail = this.getAttribute('data-detail');
                if (modalTitle) modalTitle.textContent = title;
                if (modalBody) modalBody.textContent = detail;
                if (modal) modal.style.display = 'grid';
            });
        });

        const closeInsight = () => {
            if (modal) modal.style.display = 'none';
        };

        if (modalClose) modalClose.addEventListener('click', closeInsight);
        if (modalOk) modalOk.addEventListener('click', closeInsight);
        if (modal) {
            modal.addEventListener('click', e => {
                if (e.target === modal) closeInsight();
            });
        }

        // Render Grafik Distribusi Rekomendasi per Kategori & Tingkat Urgensi
        const insightChart = document.getElementById('insight-chart-container');
        if (insightChart) {
            const cards = document.querySelectorAll('.insight-card');
            const categoryCounts = {};
            cards.forEach(card => {
                const categoryEl = card.querySelector('p strong');
                const urgencyEl = card.querySelector('.badge');
                let cat = categoryEl ? categoryEl.textContent.replace(':', '').trim() : 'Umum';
                let urg = urgencyEl ? urgencyEl.textContent.trim() : 'Sedang';
                if (!categoryCounts[cat]) categoryCounts[cat] = { total: 0, tinggi: 0, sedang: 0, rendah: 0 };
                categoryCounts[cat].total++;
                if (urg === 'Tinggi') categoryCounts[cat].tinggi++;
                else if (urg === 'Sedang') categoryCounts[cat].sedang++;
                else categoryCounts[cat].rendah++;
            });

            const catList = Object.keys(categoryCounts).map(cat => ({
                label: cat,
                value: categoryCounts[cat].total,
                subLabel: `Tinggi: ${categoryCounts[cat].tinggi} • Sedang: ${categoryCounts[cat].sedang} • Rendah: ${categoryCounts[cat].rendah}`,
                badge: `${categoryCounts[cat].tinggi > 0 ? categoryCounts[cat].tinggi + ' Prioritas Tinggi' : 'Terkendali'}`,
                badgeClass: categoryCounts[cat].tinggi > 0 ? 'danger' : 'success',
                color: categoryCounts[cat].tinggi > 0 ? 'linear-gradient(90deg, #ef4444, #f87171)' : 'linear-gradient(90deg, #0284c7, #38bdf8)'
            })).sort((a, b) => b.value - a.value);

            if (catList.length > 0) {
                insightChart.innerHTML = createHorizontalBarChartSVG(catList, { unit: ' Rekomendasi', decimals: 0, showRank: true });
            } else {
                insightChart.innerHTML = '<div class="table-empty" style="padding:16px; text-align:center; color:var(--muted); font-size:11px;">Belum ada temuan rekomendasi kebijakan aktif.</div>';
            }
        }

        // Setup CRUD & Soft Delete for Economic Insight
        setupModuleCrud({
            controller: 'economic_insight',
            addModalId: 'add-insight-modal',
            editModalId: 'edit-insight-modal',
            openAddBtnIds: ['btn-open-add-insight'],
            closeAddBtnIds: ['modal-btn-close-add-insight', 'modal-btn-cancel-add-insight'],
            closeEditBtnIds: ['modal-btn-close-edit-insight', 'modal-btn-cancel-edit-insight'],
            formAddId: 'form-add-insight',
            formEditId: 'form-edit-insight',
            addErrorId: 'add-insight-error',
            editErrorId: 'edit-insight-error',
            populateForm: function (item) {
                document.getElementById('edit-insight-id').value = item.id;
                document.getElementById('edit-insight-title').value = item.judul || '';
                document.getElementById('edit-insight-summary').value = item.ringkasan || '';
                document.getElementById('edit-insight-category').value = item.kategori || '';
                document.getElementById('edit-insight-urgency').value = item.tingkat_urgensi || 'Sedang';
                document.getElementById('edit-insight-rec').value = item.rekomendasi_kebijakan || '';
                document.getElementById('edit-insight-impact').value = item.dampak_anggaran || '';
                document.getElementById('edit-insight-opd').value = item.opd_penanggung_jawab || '';
                document.getElementById('edit-insight-year').value = item.tahun || '';
            }
        });
    }

    // 8. REPORT PAGE
    function initReportPage() {
        const typeSelect = document.getElementById('rep-type-select');
        const yearSelect = document.getElementById('rep-year-select');
        const paperHeading = document.getElementById('rep-paper-heading');
        const paperBadge = document.getElementById('rep-paper-badge');
        const sumYear = document.getElementById('rep-sum-year');
        const sumGrowth = document.getElementById('rep-sum-growth');
        const statGrowth = document.getElementById('rep-stat-growth');
        const statInflation = document.getElementById('rep-stat-inflation');
        const statTpt = document.getElementById('rep-stat-tpt');
        const sectoralTableWrap = document.getElementById('rep-sectoral-table-wrap');
        const btnSelectAll = document.getElementById('btn-select-all-sections');
        const btnExportCsv = document.getElementById('btn-export-report-csv');
        const btnExportPrev = document.getElementById('btn-export-preview-csv');

        function updateReport() {
            const t = typeSelect ? typeSelect.value : 'Perkembangan Ekonomi Daerah';
            const y = parseInt(yearSelect ? yearSelect.value : 2024);
            const yIdx = Math.max(0, AppState.years.indexOf(y));

            const g = AppState.series.growth[yIdx] || 5.18;
            const inf = AppState.series.inflation[yIdx] || 2.41;
            const tpt = AppState.series.tpt[yIdx] || 4.03;

            if (paperHeading) paperHeading.textContent = t;
            if (paperBadge) paperBadge.textContent = `${t.toUpperCase()} • ${y}`;
            if (sumYear) sumYear.textContent = y;
            if (sumGrowth) sumGrowth.textContent = formatPct(g);
            if (statGrowth) statGrowth.textContent = formatPct(g);
            if (statInflation) statInflation.textContent = formatPct(inf);
            if (statTpt) statTpt.textContent = formatPct(tpt);

            if (sectoralTableWrap) {
                const sorted = [...AppState.sectors].sort((a, b) => {
                    const gA = a.growthSeries[yIdx] ?? a.growthLocal;
                    const gB = b.growthSeries[yIdx] ?? b.growthLocal;
                    return gB - gA;
                }).slice(0, 5);

                sectoralTableWrap.innerHTML = `
                    <div class="result-table-wrap">
                        <table class="result-table">
                            <thead><tr><th>Lapangan Usaha</th><th>Laju Pertumbuhan Daerah</th><th>Laju Provinsi</th></tr></thead>
                            <tbody>
                                ${sorted.map(s => `
                                    <tr>
                                        <td><strong>${s.name}</strong></td>
                                        <td>${formatPct(s.growthSeries ? (s.growthSeries[yIdx] ?? s.growthLocal) : s.growthLocal)}</td>
                                        <td>${formatPct(s.growthProvinceSeries ? (s.growthProvinceSeries[yIdx] ?? s.growthProvince) : s.growthProvince)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>`;
            }

            // Render Macro Historical Trend Chart for report
            const repMacroChart = document.getElementById('rep-macro-chart-container');
            if (repMacroChart) {
                const subYears = AppState.years.slice(0, yIdx + 1);
                const gVals = AppState.series.growth.slice(0, yIdx + 1);
                const infVals = AppState.series.inflation.slice(0, yIdx + 1);
                const tptVals = AppState.series.tpt.slice(0, yIdx + 1);
                repMacroChart.innerHTML = createLineChartSVG(subYears, [
                    { name: 'Pertumbuhan (%)', values: gVals, className: 'series-0' },
                    { name: 'Inflasi (%)', values: infVals, className: 'series-1' },
                    { name: 'TPT (%)', values: tptVals, className: 'series-2' }
                ], 180, '%');
            }

            // Render Sectoral Horizontal Bar Chart for report
            const repSecChart = document.getElementById('rep-sectoral-chart-container');
            if (repSecChart && AppState.sectors && AppState.sectors.length > 0) {
                const topSectors = [...AppState.sectors]
                    .sort((a, b) => {
                        const gA = a.growthSeries ? (a.growthSeries[yIdx] ?? a.growthLocal) : a.growthLocal;
                        const gB = b.growthSeries ? (b.growthSeries[yIdx] ?? b.growthLocal) : b.growthLocal;
                        return gB - gA;
                    })
                    .slice(0, 5)
                    .map(s => {
                        const curG = s.growthSeries ? (s.growthSeries[yIdx] ?? s.growthLocal) : s.growthLocal;
                        return {
                            label: s.short || s.name,
                            value: curG,
                            subLabel: `Pangsa: ${formatNum(s.shareLocal, 1)}% PDRB`,
                            badge: `${formatNum(curG, 2)}%`,
                            badgeClass: curG >= 5 ? 'success' : 'info'
                        };
                    });
                repSecChart.innerHTML = createHorizontalBarChartSVG(topSectors, { unit: '%', decimals: 2 });
            }
        }

        if (typeSelect) typeSelect.addEventListener('change', updateReport);
        if (yearSelect) yearSelect.addEventListener('change', updateReport);

        // Checkbox toggles for report sections
        document.querySelectorAll('.rep-section-cb').forEach(cb => {
            cb.addEventListener('change', function () {
                const map = {
                    'Ringkasan eksekutif': 'rep-sec-summary',
                    'Indikator utama': 'rep-sec-indicators',
                    'Analisis sektoral': 'rep-sec-sectoral',
                    'Outlook ekonomi': 'rep-sec-outlook',
                    'Rekomendasi': 'rep-sec-recommendation'
                };
                const secId = map[this.value];
                if (secId) {
                    const el = document.getElementById(secId);
                    if (el) el.style.display = this.checked ? 'block' : 'none';
                }
            });
        });

        if (btnSelectAll) {
            btnSelectAll.addEventListener('click', () => {
                document.querySelectorAll('.rep-section-cb').forEach(cb => {
                    cb.checked = true;
                    cb.dispatchEvent(new Event('change'));
                });
            });
        }

        const exportHandler = () => {
            const y = yearSelect ? yearSelect.value : 2024;
            downloadCSV(`laporan-ekonomi-${y}-banyuwangi.csv`, [
                ['Indikator', 'Nilai', 'Satuan', 'Tahun', 'Wilayah'],
                ['Pertumbuhan Ekonomi', statGrowth ? statGrowth.textContent : '5,18%', '%', y, 'Kabupaten Banyuwangi'],
                ['Inflasi Tahunan', statInflation ? statInflation.textContent : '2,41%', '%', y, 'Kabupaten Banyuwangi'],
                ['Tingkat Pengangguran Terbuka', statTpt ? statTpt.textContent : '4,03%', '%', y, 'Kabupaten Banyuwangi']
            ]);
        };

        if (btnExportCsv) btnExportCsv.addEventListener('click', exportHandler);
        if (btnExportPrev) btnExportPrev.addEventListener('click', exportHandler);

        updateReport();

        // Setup CRUD & Soft Delete for Laporan Ekonomi
        setupModuleCrud({
            controller: 'laporan_ekonomi',
            addModalId: 'add-report-modal',
            editModalId: 'edit-report-modal',
            openAddBtnIds: ['btn-open-add-report', 'btn-open-add-report-2'],
            closeAddBtnIds: ['modal-btn-close-add-report', 'modal-btn-cancel-add-report'],
            closeEditBtnIds: ['modal-btn-close-edit-report', 'modal-btn-cancel-edit-report'],
            formAddId: 'form-add-report',
            formEditId: 'form-edit-report',
            addErrorId: 'add-report-error',
            editErrorId: 'edit-report-error',
            populateForm: function (item) {
                document.getElementById('edit-report-id').value = item.id;
                document.getElementById('edit-report-number').value = item.nomor_laporan || '';
                document.getElementById('edit-report-year').value = item.periode_tahun || '';
                document.getElementById('edit-report-title').value = item.judul_laporan || '';
                document.getElementById('edit-report-type').value = item.jenis_laporan || 'Eksekutif';
                document.getElementById('edit-report-status').value = item.status || 'Draft';
                document.getElementById('edit-report-author').value = item.penyusun || '';
                document.getElementById('edit-report-date').value = item.tanggal_terbit || '';
            }
        });
    }

    // 9. ADMIN DATABASE PAGE
    function initAdminDatabasePage() {
        const searchInput = document.getElementById('admin-search-input');
        const filterStatus = document.getElementById('admin-filter-status');
        const sortSelect = document.getElementById('admin-sort');
        const btnOpenModal = document.getElementById('btn-open-add-modal');
        const addModal = document.getElementById('add-data-modal');
        const btnCloseAdd = document.getElementById('modal-btn-close-add');
        const btnCancelAdd = document.getElementById('modal-btn-cancel-add');
        const formAdd = document.getElementById('form-manual-add');
        const btnExportCsv = document.getElementById('btn-export-database-csv');

        // Edit Modal Elements
        const editModal = document.getElementById('edit-data-modal');
        const btnCloseEdit = document.getElementById('modal-btn-close-edit');
        const btnCancelEdit = document.getElementById('modal-btn-cancel-edit');
        const formEdit = document.getElementById('form-manual-edit');
        const editErrBox = document.getElementById('edit-modal-error');

        function reloadFiltered() {
            const q = searchInput ? encodeURIComponent(searchInput.value) : '';
            const st = filterStatus ? encodeURIComponent(filterStatus.value) : '';
            const srt = sortSelect ? encodeURIComponent(sortSelect.value) : 'year';
            window.location.href = `${SITE_URL}/database?q=${q}&status=${st}&sort=${srt}`;
        }

        if (searchInput) {
            searchInput.addEventListener('keydown', e => {
                if (e.key === 'Enter') reloadFiltered();
            });
        }
        if (filterStatus) filterStatus.addEventListener('change', reloadFiltered);
        if (sortSelect) sortSelect.addEventListener('change', reloadFiltered);

        // Toggle record status via API
        document.querySelectorAll('.btn-toggle-record-status').forEach(btn => {
            btn.addEventListener('click', function () {
                const code = this.getAttribute('data-code');
                const year = this.getAttribute('data-year');
                const currentStatus = this.getAttribute('data-status');
                const nextStatus = currentStatus === 'Tervalidasi' ? 'Perlu review' : 'Tervalidasi';

                const fd = new FormData();
                fd.append('code', code);
                fd.append('year', year);
                fd.append('status', nextStatus);

                fetch(BASE_URL + 'api/update_status', { method: 'POST', body: fd })
                    .then(res => res.json())
                    .then(() => location.reload());
            });
        });

        // Add modal (Create)
        if (btnOpenModal && addModal) {
            btnOpenModal.addEventListener('click', () => { addModal.style.display = 'grid'; });
        }
        const closeAddModal = () => { if (addModal) addModal.style.display = 'none'; };
        if (btnCloseAdd) btnCloseAdd.addEventListener('click', closeAddModal);
        if (btnCancelAdd) btnCancelAdd.addEventListener('click', closeAddModal);
        if (addModal) {
            addModal.addEventListener('click', e => {
                if (e.target === addModal) closeAddModal();
            });
        }

        if (formAdd) {
            formAdd.addEventListener('submit', function (e) {
                e.preventDefault();
                const fd = new FormData(this);
                const errBox = document.getElementById('add-modal-error');

                fetch(`${SITE_URL}/database/create`, {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            location.reload();
                        } else {
                            if (errBox) {
                                errBox.style.display = 'block';
                                errBox.textContent = data.message || 'Gagal menyimpan data.';
                            }
                        }
                    })
                    .catch(() => {
                        // Fallback to normal form submit
                        formAdd.submit();
                    });
            });
        }

        // Edit modal (Update)
        const closeEditModal = () => { if (editModal) editModal.style.display = 'none'; };
        if (btnCloseEdit) btnCloseEdit.addEventListener('click', closeEditModal);
        if (btnCancelEdit) btnCancelEdit.addEventListener('click', closeEditModal);
        if (editModal) {
            editModal.addEventListener('click', e => {
                if (e.target === editModal) closeEditModal();
            });
        }

        document.querySelectorAll('.btn-edit-record').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                if (!id) return;

                if (editErrBox) {
                    editErrBox.style.display = 'none';
                    editErrBox.textContent = '';
                }

                fetch(`${SITE_URL}/database/get_record/${id}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => {
                        if (!res.ok) throw new Error('Data record tidak ditemukan.');
                        return res.json();
                    })
                    .then(res => {
                        if (res.status === 'success' && res.data) {
                            const d = res.data;
                            document.getElementById('edit-id').value = d.id;
                            document.getElementById('edit-code').value = d.code || d.kode_indikator || '';
                            document.getElementById('edit-indicator').value = d.indicator || d.nama_indikator || '';
                            document.getElementById('edit-cluster').value = d.cluster || d.klaster || 'Output & Pertumbuhan';
                            document.getElementById('edit-region').value = d.region || d.wilayah || 'Kabupaten Banyuwangi';
                            document.getElementById('edit-period').value = d.period || d.periode || 'Tahunan';
                            document.getElementById('edit-year').value = d.year || d.tahun || 2024;
                            document.getElementById('edit-value').value = d.value !== undefined ? d.value : d.nilai;
                            document.getElementById('edit-unit').value = d.unit || d.satuan || '%';
                            document.getElementById('edit-source').value = d.source || d.sumber_data || '';
                            document.getElementById('edit-status').value = d.status || d.status_validasi || 'Perlu review';
                            document.getElementById('edit-notes').value = d.notes || d.catatan || '';

                            if (editModal) editModal.style.display = 'grid';
                        } else {
                            alert(res.message || 'Gagal memuat rekaman data.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Gagal mengambil data indikator untuk diedit.');
                    });
            });
        });

        if (formEdit) {
            formEdit.addEventListener('submit', function (e) {
                e.preventDefault();
                const id = document.getElementById('edit-id').value;
                if (!id) return;

                const fd = new FormData(this);
                if (editErrBox) editErrBox.style.display = 'none';

                fetch(`${SITE_URL}/database/update/${id}`, {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            location.reload();
                        } else {
                            if (editErrBox) {
                                editErrBox.style.display = 'block';
                                editErrBox.textContent = data.message || 'Gagal memperbarui data.';
                            }
                        }
                    })
                    .catch(() => {
                        if (editErrBox) {
                            editErrBox.style.display = 'block';
                            editErrBox.textContent = 'Terjadi kesalahan jaringan saat menyimpan pembaruan.';
                        }
                    });
            });
        }

        // Soft Delete
        document.querySelectorAll('.btn-delete-record').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const indicator = this.getAttribute('data-indicator') || 'data ini';
                if (!id) return;

                if (confirm(`Pindahkan indikator "${indicator}" ke tempat sampah (Soft Delete)?\n\nData tidak akan dihapus permanen dan dapat dipulihkan sewaktu-waktu dari filter Tempat Sampah.`)) {
                    fetch(`${SITE_URL}/database/soft_delete/${id}`, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                location.reload();
                            } else {
                                alert(data.message || 'Gagal memproses soft delete.');
                            }
                        })
                        .catch(() => {
                            window.location.href = `${SITE_URL}/database/soft_delete/${id}`;
                        });
                }
            });
        });

        // Restore from Soft Delete
        document.querySelectorAll('.btn-restore-record').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const indicator = this.getAttribute('data-indicator') || 'data ini';
                if (!id) return;

                if (confirm(`Pulihkan indikator "${indicator}" kembali ke daftar aktif?`)) {
                    fetch(`${SITE_URL}/database/restore/${id}`, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                location.reload();
                            } else {
                                alert(data.message || 'Gagal memulihkan data.');
                            }
                        })
                        .catch(() => {
                            window.location.href = `${SITE_URL}/database/restore/${id}`;
                        });
                }
            });
        });

        // Hard Delete (Permanent Delete)
        document.querySelectorAll('.btn-hard-delete-record').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const indicator = this.getAttribute('data-indicator') || 'data ini';
                if (!id) return;

                if (confirm(`PERINGATAN: Apakah Anda yakin ingin MENGHAPUS PERMANEN "${indicator}"?\n\nData yang dihapus permanen akan hilang selamanya dari database dan TIDAK DAPAT DIPULIHKAN LAGI!`)) {
                    fetch(`${SITE_URL}/database/hard_delete/${id}`, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                location.reload();
                            } else {
                                alert(data.message || 'Gagal menghapus data secara permanen.');
                            }
                        })
                        .catch(() => {
                            window.location.href = `${SITE_URL}/database/hard_delete/${id}`;
                        });
                }
            });
        });

        if (btnExportCsv) {
            btnExportCsv.addEventListener('click', () => {
                downloadCSV('database-ekonomi-banyuwangi.csv', [
                    ['Kode', 'Indikator', 'Klaster', 'Wilayah', 'Periode', 'Tahun', 'Nilai', 'Satuan', 'Sumber', 'Status'],
                    ...AppState.records.map(r => [r.code, r.indicator, r.cluster, r.region, r.period, r.year, r.value, r.unit, r.source, r.status])
                ]);
            });
        }
    }

    // 10. ADMIN INPUT & CSV IMPORT PAGE
    function initAdminInputPage() {
        const fileInput = document.getElementById('csv-file-input');
        const filenameLabel = document.getElementById('csv-filename-label');
        const previewContainer = document.getElementById('csv-preview-container');
        const previewTbody = document.getElementById('csv-preview-tbody');
        const countLabel = document.getElementById('csv-preview-count-label');
        const btnCommit = document.getElementById('btn-commit-import');
        const feedbackBox = document.getElementById('csv-feedback-box');
        const btnDownloadTemplate = document.getElementById('btn-download-template-csv');
        const formDirect = document.getElementById('form-direct-input');
        const dirFeedback = document.getElementById('dir-feedback-box');

        let parsedRows = [];

        if (btnDownloadTemplate) {
            btnDownloadTemplate.addEventListener('click', () => {
                downloadCSV('template-import-si-dapeda.csv', [
                    ['kode', 'indikator', 'klaster', 'wilayah', 'periode', 'tahun', 'nilai', 'satuan', 'sumber'],
                    ['EKO-101', 'Pertumbuhan Ekonomi Triwulanan', 'Output & Pertumbuhan', 'Kabupaten Banyuwangi', 'Triwulanan', '2024', '5.20', '%', 'BPS']
                ]);
            });
        }

        if (fileInput) {
            fileInput.addEventListener('change', function () {
                const file = this.files[0];
                if (!file) return;

                filenameLabel.textContent = file.name;
                const reader = new FileReader();

                reader.onload = function (e) {
                    const text = e.target.result;
                    const lines = text.split(/\r?\n/).filter(l => l.trim().length > 0);
                    if (lines.length < 2) {
                        feedbackBox.style.display = 'block';
                        feedbackBox.textContent = 'File CSV harus memiliki minimal 1 baris header dan 1 baris data.';
                        return;
                    }

                    const delimiter = lines[0].includes(';') ? ';' : ',';
                    const headers = lines[0].split(delimiter).map(h => h.replace(/["']/g, '').trim().toLowerCase());

                    parsedRows = [];
                    for (let i = 1; i < lines.length; i++) {
                        const cols = lines[i].split(delimiter).map(c => c.replace(/["']/g, '').trim());
                        if (cols.length >= 2) {
                            parsedRows.push({
                                code: cols[headers.indexOf('kode')] || `IMP-${i}`,
                                indicator: cols[headers.indexOf('indikator')] || cols[1] || 'Indikator',
                                cluster: cols[headers.indexOf('klaster')] || 'Output & Pertumbuhan',
                                region: cols[headers.indexOf('wilayah')] || 'Kabupaten Banyuwangi',
                                period: cols[headers.indexOf('periode')] || 'Tahunan',
                                year: parseInt(cols[headers.indexOf('tahun')]) || 2024,
                                value: parseFloat(cols[headers.indexOf('nilai')] ? cols[headers.indexOf('nilai')].replace(',', '.') : 0),
                                unit: cols[headers.indexOf('satuan')] || '%',
                                source: cols[headers.indexOf('sumber')] || file.name
                            });
                        }
                    }

                    if (previewContainer && previewTbody) {
                        previewContainer.style.display = 'block';
                        countLabel.textContent = `Preview ${parsedRows.length} baris data dari ${file.name}`;
                        previewTbody.innerHTML = parsedRows.slice(0, 8).map(r => `
                            <tr>
                                <td><code>${r.code}</code></td>
                                <td><strong>${r.indicator}</strong></td>
                                <td>${r.year}</td>
                                <td>${formatNum(r.value)}</td>
                                <td>${r.unit}</td>
                                <td><span class="badge warning">Perlu review</span></td>
                            </tr>
                        `).join('');
                    }
                };

                reader.readAsText(file);
            });
        }

        if (btnCommit) {
            btnCommit.addEventListener('click', () => {
                if (!parsedRows.length) return;

                fetch(BASE_URL + 'api/import_csv', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(parsedRows)
                })
                    .then(res => res.json())
                    .then(data => {
                        feedbackBox.style.display = 'block';
                        feedbackBox.textContent = data.message || 'Import berhasil diselesaikan.';
                        previewContainer.style.display = 'none';
                        parsedRows = [];
                    })
                    .catch(() => {
                        feedbackBox.style.display = 'block';
                        feedbackBox.textContent = 'Gagal mengirim data import.';
                    });
            });
        }

        // Direct manual single input
        if (formDirect) {
            formDirect.addEventListener('submit', function (e) {
                e.preventDefault();
                const fd = new FormData();
                fd.append('code', document.getElementById('dir-code').value);
                fd.append('indicator', document.getElementById('dir-indicator').value);
                fd.append('cluster', document.getElementById('dir-cluster').value);
                fd.append('region', document.getElementById('dir-region').value);
                fd.append('period', document.getElementById('dir-period').value);
                fd.append('year', document.getElementById('dir-year').value);
                fd.append('value', document.getElementById('dir-value').value);
                fd.append('unit', document.getElementById('dir-unit').value);
                fd.append('source', document.getElementById('dir-source').value);

                fetch(BASE_URL + 'api/add_record', { method: 'POST', body: fd })
                    .then(res => res.json())
                    .then(data => {
                        dirFeedback.style.display = 'block';
                        dirFeedback.textContent = data.message;
                        if (data.status === 'success') {
                            formDirect.reset();
                        }
                    });
            });
        }
    }

    // 11. ADMIN VALIDATION PAGE
    function initAdminValidationPage() {
        document.querySelectorAll('.btn-verify-record').forEach(btn => {
            btn.addEventListener('click', function () {
                const code = this.getAttribute('data-code');
                const year = this.getAttribute('data-year');
                const status = this.getAttribute('data-status');

                const fd = new FormData();
                fd.append('code', code);
                fd.append('year', year);
                fd.append('status', status);

                fetch(BASE_URL + 'api/update_status', { method: 'POST', body: fd })
                    .then(res => res.json())
                    .then(() => location.reload());
            });
        });
    }

    // 12. ADMIN EWS PAGE
    function initAdminEwsPage() {
        const btnSaveAll = document.getElementById('btn-save-all-thresholds');
        const feedback = document.getElementById('ews-admin-feedback');

        if (btnSaveAll) {
            btnSaveAll.addEventListener('click', () => {
                const inputs = document.querySelectorAll('.threshold-input');
                let count = 0;

                inputs.forEach(input => {
                    const code = input.getAttribute('data-code');
                    const th = parseFloat(input.value);

                    const fd = new FormData();
                    fd.append('code', code);
                    fd.append('threshold', th);

                    fetch(BASE_URL + 'api/update_threshold', { method: 'POST', body: fd })
                        .then(() => {
                            count++;
                            if (count === inputs.length && feedback) {
                                feedback.style.display = 'block';
                                feedback.textContent = 'Seluruh threshold berhasil disimpan ke database MySQL.';
                            }
                        });
                });
            });
        }
    }

    // 13. ADMIN USERS PAGE
    function initAdminUsersPage() {
        const feedback = document.getElementById('users-feedback-box');

        document.querySelectorAll('.user-role-select').forEach(sel => {
            sel.addEventListener('change', function () {
                const id = this.getAttribute('data-user-id');
                const role = this.value;

                const fd = new FormData();
                fd.append('id', id);
                fd.append('role', role);
                fd.append('level', role);

                fetch(BASE_URL + 'api/update_user', { method: 'POST', body: fd })
                    .then(res => res.json())
                    .then(() => {
                        if (feedback) {
                            feedback.style.display = 'block';
                            feedback.textContent = 'Level dan hak akses akun berhasil diperbarui.';
                        }
                    });
            });
        });

        document.querySelectorAll('.btn-toggle-user-status').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-user-id');
                const cur = this.getAttribute('data-current-status');
                const next = cur === 'Aktif' ? 'Nonaktif' : 'Aktif';

                const fd = new FormData();
                fd.append('id', id);
                fd.append('status', next);

                fetch(BASE_URL + 'api/update_user', { method: 'POST', body: fd })
                    .then(res => res.json())
                    .then(() => location.reload());
            });
        });

        // Render Grafik Distribusi Level Akun Pengguna
        const userChart = document.getElementById('users-level-chart');
        if (userChart) {
            const levelMap = { '1': 0, '2': 0, '3': 0, '4': 0 };
            document.querySelectorAll('.user-role-select').forEach(sel => {
                const val = String(sel.value).trim();
                if (levelMap[val] !== undefined) levelMap[val]++;
            });
            const levelData = [
                { label: 'Level 1 - Administrator', value: levelMap['1'], subLabel: 'Otoritas tertinggi & konfigurasi penuh sistem', badge: `${levelMap['1']} Akun`, badgeClass: 'primary', color: 'linear-gradient(90deg, #0284c7, #38bdf8)' },
                { label: 'Level 2 - Analis Data', value: levelMap['2'], subLabel: 'Kajian ekonomi, validasi data & generator laporan', badge: `${levelMap['2']} Akun`, badgeClass: 'info', color: 'linear-gradient(90deg, #06b6d4, #67e8f9)' },
                { label: 'Level 3 - Operator OPD', value: levelMap['3'], subLabel: 'Entri indikator sektoral & master dataset', badge: `${levelMap['3']} Akun`, badgeClass: 'success', color: 'linear-gradient(90deg, #10b981, #6ee7b7)' },
                { label: 'Level 4 - Reviewer / Eksekutif', value: levelMap['4'], subLabel: 'Monitoring dashboard publik & evaluasi capaian', badge: `${levelMap['4']} Akun`, badgeClass: 'warning', color: 'linear-gradient(90deg, #f59e0b, #fcd34d)' }
            ];
            userChart.innerHTML = createHorizontalBarChartSVG(levelData, { unit: ' Akun', decimals: 0, showRank: false });
        }

        // Setup CRUD & Soft Delete for Akun Pengguna
        setupModuleCrud({
            controller: 'users',
            addModalId: 'add-user-modal',
            editModalId: 'edit-user-modal',
            openAddBtnIds: ['btn-open-add-user'],
            closeAddBtnIds: ['modal-btn-close-add-user', 'modal-btn-cancel-add-user'],
            closeEditBtnIds: ['modal-btn-close-edit-user', 'modal-btn-cancel-edit-user'],
            formAddId: 'form-add-user',
            formEditId: 'form-edit-user',
            addErrorId: 'add-user-error',
            editErrorId: 'edit-user-error',
            populateForm: function (item) {
                document.getElementById('edit-user-id').value = item.id;
                document.getElementById('edit-user-username').value = item.nama_pengguna || '';
                document.getElementById('edit-user-fullname').value = item.nama_lengkap || '';
                document.getElementById('edit-user-email').value = item.email || '';
                document.getElementById('edit-user-level').value = item.level || '1';
                document.getElementById('edit-user-status').value = item.status || 'Aktif';
                document.getElementById('edit-user-password').value = '';
            }
        });
    }

    // ADMIN CATALOG PAGE
    function initAdminCatalogPage() {
        const btnExportCsv = document.getElementById('btn-export-catalog-csv');
        if (btnExportCsv) {
            btnExportCsv.addEventListener('click', function () {
                window.location.href = SITE_URL + '/catalog';
            });
        }

        // Render Grafik Distribusi Klaster Indikator
        const catChart = document.getElementById('catalog-cluster-chart');
        if (catChart) {
            const clusterMap = {};
            document.querySelectorAll('.data-card table tbody tr').forEach(tr => {
                const tds = tr.querySelectorAll('td');
                if (tds.length >= 3) {
                    const cl = tds[2].textContent.trim();
                    if (cl && cl !== 'Tidak ada indikator katalog ditemukan.') clusterMap[cl] = (clusterMap[cl] || 0) + 1;
                }
            });
            const clusterItems = Object.keys(clusterMap).map(k => ({
                label: k,
                value: clusterMap[k],
                subLabel: 'Klaster Tematik Data',
                badge: `${clusterMap[k]} Indikator`,
                badgeClass: 'info'
            })).sort((a, b) => b.value - a.value);

            if (clusterItems.length > 0) {
                catChart.innerHTML = createHorizontalBarChartSVG(clusterItems, { unit: ' Indikator', decimals: 0, showRank: true });
            }
        }

        // Setup CRUD & Soft Delete for Katalog Indikator
        setupModuleCrud({
            controller: 'catalog',
            addModalId: 'add-catalog-modal',
            editModalId: 'edit-catalog-modal',
            openAddBtnIds: ['btn-open-add-catalog'],
            closeAddBtnIds: ['modal-btn-close-add-catalog', 'modal-btn-cancel-add-catalog'],
            closeEditBtnIds: ['modal-btn-close-edit-catalog', 'modal-btn-cancel-edit-catalog'],
            formAddId: 'form-add-catalog',
            formEditId: 'form-edit-catalog',
            addErrorId: 'add-catalog-error',
            editErrorId: 'edit-catalog-error',
            populateForm: function (item) {
                document.getElementById('edit-catalog-id').value = item.id;
                document.getElementById('edit-catalog-code').value = item.kode_indikator || '';
                document.getElementById('edit-catalog-unit').value = item.satuan || '';
                document.getElementById('edit-catalog-name').value = item.nama_indikator || '';
                document.getElementById('edit-catalog-cluster').value = item.klaster || '';
                document.getElementById('edit-catalog-freq').value = item.frekuensi || 'Tahunan';
                document.getElementById('edit-catalog-source').value = item.sumber_data || '';
                document.getElementById('edit-catalog-def').value = item.definisi_operasional || '';
            }
        });
    }

})();

