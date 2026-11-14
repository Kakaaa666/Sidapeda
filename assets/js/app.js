/**
 * SI-DAPEDA — Sistem Informasi Data & Analisis Pembangunan Daerah
 * SI-DAPEDA Frontend & Interactive Intelligence Engine
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

            const valFormatted = `${formatNum(v, opts.decimals ?? 2)}${unit}`.trim();
            const hasDistinctBadge = item.badge && item.badge.trim() !== valFormatted && item.badge.trim() !== `${v}${unit}`.trim() && item.badge.trim() !== `${v}`.trim();

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
                        <strong style="color:${isHighlight ? 'var(--blue)' : 'var(--navy)'}; font-size:11px;">${valFormatted}</strong>
                        ${hasDistinctBadge ? `<br><span class="badge ${item.badgeClass || 'info'}" style="font-size:8px; padding:1px 5px; font-weight:600;">${item.badge}</span>` : ''}
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

    function createKlassenSVG(rows, opts = {}) {
        if (!rows.length) return `<div class="table-empty">Belum ada data untuk Klassen.</div>`;

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

        const xLabel = opts.xLabel || 'Gap pertumbuhan sektor terhadap agregat Banyuwangi (pp)';
        const yLabel = opts.yLabel || 'Gap pangsa sektor terhadap rata-rata lokal (pp)';
        const note = opts.note || 'Titik di atas garis horizontal = pangsa di atas rata-rata. Titik di kanan garis vertikal = pertumbuhan di atas ambang daerah.';

        let pointsSVG = '';
        rows.forEach((item, idx) => {
            const cx = xScale(item.growthGap);
            const cy = yScale(item.shareGap);
            const labelY = cy + (idx % 2 === 0 ? -9 : 15);
            pointsSVG += `
                <g class="klassen-point q-${item.quadrant.toLowerCase()}">
                    <circle cx="${cx}" cy="${cy}" r="5">
                        <title>${item.name}: Gap Y ${formatNum(item.shareGap)} pp, Gap X ${formatNum(item.growthGap)} pp</title>
                    </circle>
                    <text x="${cx + 8}" y="${labelY}">${item.short || item.name}</text>
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

                <text x="${(left + right) / 2}" y="315" text-anchor="middle" class="klassen-axis-label">${xLabel}</text>
                <text x="-145" y="16" text-anchor="middle" transform="rotate(-90)" class="klassen-axis-label">${yLabel}</text>
            </svg>
            <div class="klassen-chart-note">${note}</div>`;
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
    // Initialize UI on DOM Ready (robust check against already-loaded state)
    function runInit() {
        initNavigation();
        initGlobalCrudDelegation();
        dispatchPageLogic(); // Immediately bind all CRUD modals and interactive buttons on page load
        initDataLoad();      // Then asynchronously fetch central state / time series
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', runInit);
    } else {
        runInit();
    }

    // Universal Fail-safe Event Delegation for all CRUD & Modal buttons across all menus
    function initGlobalCrudDelegation() {
        document.addEventListener('click', function (e) {
            // 1. OPEN ADD MODAL
            const addBtn = e.target.closest('[id^="btn-open-add-"]');
            if (addBtn) {
                e.preventDefault();
                const id = addBtn.id;
                let modalId = null;
                if (id === 'btn-open-add-user') modalId = 'add-user-modal';
                else if (id === 'btn-open-add-catalog') modalId = 'add-catalog-modal';
                else if (id === 'btn-open-add-modal') modalId = 'add-data-modal';

                if (modalId) {
                    const m = document.getElementById(modalId);
                    if (m) {
                        m.style.display = 'grid';
                        return;
                    }
                }
            }

            // 2. CLOSE MODAL (Close/Cancel buttons or backdrop click)
            const closeBtn = e.target.closest('[id^="modal-btn-close"], [id^="modal-btn-cancel"], .modal-close, #insight-modal-close, #insight-modal-btn-ok');
            if (closeBtn) {
                e.preventDefault();
                const modal = closeBtn.closest('.modal-backdrop');
                if (modal) {
                    modal.style.display = 'none';
                    return;
                }
            }

            if (e.target.classList.contains('modal-backdrop')) {
                e.target.style.display = 'none';
                return;
            }
        });
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

                    const cleanSite = (typeof SITE_URL !== 'undefined' ? SITE_URL : (typeof BASE_URL !== 'undefined' ? BASE_URL : '')).replace(/\/+$/, '');
                    window.location.href = cleanSite + '/' + target;
                }
            });
        }
    }

    // Load Central Data from API
    function initDataLoad() {
        const cleanBase = (typeof BASE_URL !== 'undefined' ? BASE_URL : '').replace(/\/+$/, '');
        fetch(cleanBase + '/api/get_data')
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

                    // Refresh page components that rely on AppState
                    dispatchPageLogic();
                }
            })
            .catch(err => {
                console.warn('Gagal memuat API state, menggunakan fallback data server-side:', err);
            });
    }

    function dispatchPageLogic() {
        const path = window.location.pathname.toLowerCase();

        if (document.getElementById('overview-line-chart-container') || document.getElementById('btn-open-add-beranda')) {
            initOverviewPage();
        } else if (document.getElementById('comp-feature-tabs') || document.getElementById('form-add-regional') || document.getElementById('btn-open-add-regional-modal')) {
            initComparisonPage();
        } else if (document.getElementById('analysis-menu-links') || document.getElementById('form-add-sector') || document.getElementById('btn-open-add-sector')) {
            initAnalysisPage();
        } else if (document.getElementById('map-canvas-container') || document.getElementById('form-add-district') || document.getElementById('btn-open-add-district')) {
            initMapPage();
        } else if (document.getElementById('btn-mode-forecast') || document.getElementById('form-add-prospek') || document.getElementById('btn-open-add-prospek')) {
            initOutlookPage();
        } else if (document.getElementById('ews-stats-container') || document.getElementById('form-add-ews') || document.getElementById('btn-open-add-ews')) {
            initEwsPage();
        } else if (document.getElementById('insight-modal-backdrop') || document.getElementById('form-add-insight') || document.getElementById('btn-open-add-insight')) {
            initInsightPage();
        } else if (document.getElementById('printable-report') || document.getElementById('form-add-report') || document.getElementById('btn-open-add-report')) {
            initReportPage();
        } else if (document.getElementById('admin-table-body')) {
            initAdminDatabasePage();
        } else if (document.querySelector('.btn-quick-verify') || document.querySelector('.admin-status-row')) {
            initAdminDashboardPage();
        } else if (document.getElementById('csv-file-input')) {
            initAdminInputPage();
        } else if (document.getElementById('validation-table-body')) {
            initAdminValidationPage();
        } else if (document.getElementById('threshold-admin-rows')) {
            initAdminEwsPage();
        } else if (document.getElementById('form-add-catalog') || document.getElementById('btn-export-catalog-csv') || document.getElementById('btn-open-add-catalog')) {
            initAdminCatalogPage();
        } else if (document.querySelector('.user-role-select') || document.getElementById('form-add-user') || document.getElementById('btn-open-add-user')) {
            initAdminUsersPage();
        }
    }

    // ==========================================
    // GENERIC MODULE CRUD & SOFT DELETE HANDLER
    // ==========================================
    function setupModuleCrud(cfg) {
        const cleanSite = (typeof SITE_URL !== 'undefined' ? SITE_URL : (typeof BASE_URL !== 'undefined' ? BASE_URL : '')).replace(/\/+$/, '');
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
                btn.onclick = function (e) {
                    e.preventDefault();
                    if (addErr) { addErr.style.display = 'none'; addErr.textContent = ''; }
                    addModal.style.display = 'grid';
                };
            }
        });

        // Close Add Modal
        (cfg.closeAddBtnIds || []).forEach(id => {
            const btn = document.getElementById(id);
            if (btn && addModal) {
                btn.onclick = function (e) {
                    e.preventDefault();
                    addModal.style.display = 'none';
                };
            }
        });
        if (addModal && !addModal._hasBackdropBound) {
            addModal._hasBackdropBound = true;
            addModal.addEventListener('click', e => {
                if (e.target === addModal) addModal.style.display = 'none';
            });
        }

        // Close Edit Modal
        (cfg.closeEditBtnIds || []).forEach(id => {
            const btn = document.getElementById(id);
            if (btn && editModal) {
                btn.onclick = function (e) {
                    e.preventDefault();
                    editModal.style.display = 'none';
                };
            }
        });
        if (editModal && !editModal._hasBackdropBound) {
            editModal._hasBackdropBound = true;
            editModal.addEventListener('click', e => {
                if (e.target === editModal) editModal.style.display = 'none';
            });
        }

        // Submit Add Form
        if (formAdd && !formAdd._hasCrudBound) {
            formAdd._hasCrudBound = true;
            formAdd.addEventListener('submit', function (e) {
                e.preventDefault();
                if (addErr) { addErr.style.display = 'none'; addErr.textContent = ''; }
                const fd = new FormData(formAdd);
                fetch(`${cleanSite}/${cfg.controller}/create`, {
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
        if (formEdit && !formEdit._hasCrudBound) {
            formEdit._hasCrudBound = true;
            formEdit.addEventListener('submit', function (e) {
                e.preventDefault();
                if (editErr) { editErr.style.display = 'none'; editErr.textContent = ''; }
                const fd = new FormData(formEdit);
                const id = fd.get('id') || (formEdit.querySelector('input[name="id"]') ? formEdit.querySelector('input[name="id"]').value : '');
                fetch(`${cleanSite}/${cfg.controller}/update/${id}`, {
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

        function matchClass(elem, classStr) {
            if (!elem || !classStr) return null;
            const list = classStr.trim().split(/\s+/);
            for (let i = 0; i < list.length; i++) {
                const found = elem.closest('.' + list[i]);
                if (found) return found;
            }
            return null;
        }

        // Global Delegated Handler for this controller to guarantee click responsiveness
        window._registeredCrudDelegates = window._registeredCrudDelegates || {};
        if (!window._registeredCrudDelegates[cfg.controller]) {
            window._registeredCrudDelegates[cfg.controller] = true;

            document.addEventListener('click', function (e) {
                // 1. Edit Button
                if (cfg.editBtnClass) {
                    const btn = matchClass(e.target, cfg.editBtnClass);
                    if (btn) {
                        e.preventDefault();
                        e.stopPropagation();
                        const id = btn.getAttribute('data-id');
                        if (!id) return;
                        if (editErr) { editErr.style.display = 'none'; editErr.textContent = ''; }
                        fetch(`${cleanSite}/${cfg.controller}/get_data/${id}`, {
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
                        return;
                    }
                }

                // 2. Soft Delete Button
                if (cfg.deleteBtnClass) {
                    const btn = matchClass(e.target, cfg.deleteBtnClass);
                    if (btn) {
                        e.preventDefault();
                        e.stopPropagation();
                        const id = btn.getAttribute('data-id');
                        const name = btn.getAttribute('data-name') || 'item ini';
                        if (!id) return;
                        if (confirm(`Pindahkan "${name}" ke tempat sampah (Soft Delete)?\n\nData tidak akan dihapus permanen dan dapat dipulihkan sewaktu-waktu dari Tempat Sampah.`)) {
                            fetch(`${cleanSite}/${cfg.controller}/soft_delete/${id}`, {
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
                                    window.location.href = `${cleanSite}/${cfg.controller}/soft_delete/${id}`;
                                });
                        }
                        return;
                    }
                }

                // 3. Restore Button
                if (cfg.restoreBtnClass) {
                    const btn = matchClass(e.target, cfg.restoreBtnClass);
                    if (btn) {
                        e.preventDefault();
                        e.stopPropagation();
                        const id = btn.getAttribute('data-id');
                        const name = btn.getAttribute('data-name') || 'item ini';
                        if (!id) return;
                        if (confirm(`Pulihkan "${name}" kembali ke daftar aktif?`)) {
                            fetch(`${cleanSite}/${cfg.controller}/restore/${id}`, {
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
                                    window.location.href = `${cleanSite}/${cfg.controller}/restore/${id}`;
                                });
                        }
                        return;
                    }
                }

                // 4. Hard Delete Button
                if (cfg.hardDeleteBtnClass) {
                    const btn = matchClass(e.target, cfg.hardDeleteBtnClass);
                    if (btn) {
                        e.preventDefault();
                        e.stopPropagation();
                        const id = btn.getAttribute('data-id');
                        const name = btn.getAttribute('data-name') || 'item ini';
                        if (!id) return;
                        if (confirm(`PERINGATAN: Apakah Anda yakin ingin MENGHAPUS PERMANEN "${name}"?\n\nData yang dihapus permanen akan hilang selamanya dari database dan TIDAK DAPAT DIPULIHKAN LAGI!`)) {
                            fetch(`${cleanSite}/${cfg.controller}/hard_delete/${id}`, {
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
                                    window.location.href = `${cleanSite}/${cfg.controller}/hard_delete/${id}`;
                                });
                        }
                        return;
                    }
                }
            });
        }

        return { bindButtons: function () { } };
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

        // Tab Filter Kategori Indikator Beranda
        const catTabs = document.getElementById('beranda-category-tabs');
        if (catTabs) {
            const tabButtons = catTabs.querySelectorAll('button');
            const kpiCards = document.querySelectorAll('#kpi-container .kpi-card');

            tabButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    tabButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    const targetCat = this.getAttribute('data-category');
                    kpiCards.forEach(card => {
                        const cardCat = card.getAttribute('data-category');
                        if (targetCat === 'all' || cardCat === targetCat) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });
        }

        updateOverviewChart();

        if (btnApply) {
            btnApply.addEventListener('click', () => {
                const yr = filterYear ? filterYear.value : 2024;
                const cleanSite = (typeof SITE_URL !== 'undefined' ? SITE_URL : (typeof BASE_URL !== 'undefined' ? BASE_URL : '')).replace(/\/+$/, '');
                window.location.href = cleanSite + '/beranda?year=' + yr;
            });
        }
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

        function bindRegionalActionButtons() {}

        // Setup CRUD & Soft Delete for Perbandingan Daerah
        setupModuleCrud({
            controller: 'perbandingan_daerah',
            addModalId: 'add-regional-modal',
            editModalId: 'edit-regional-modal',
            openAddBtnIds: ['btn-open-add-regional-modal'],
            closeAddBtnIds: ['modal-btn-close-add-regional', 'modal-btn-cancel-add-regional'],
            closeEditBtnIds: ['modal-btn-close-edit-regional', 'modal-btn-cancel-edit-regional'],
            formAddId: 'form-add-regional',
            formEditId: 'form-edit-regional',
            addErrorId: 'add-regional-error',
            editErrorId: 'edit-regional-error',
            editBtnClass: 'btn-edit-regional',
            deleteBtnClass: 'btn-delete-regional',
            restoreBtnClass: 'btn-restore-regional',
            hardDeleteBtnClass: 'btn-hard-delete-regional',
            onEditPreFill: (d) => {
                document.getElementById('edit-regional-id').value = d.id;
                document.getElementById('edit-regional-name').value = d.name || d.nama_wilayah || '';
                document.getElementById('edit-regional-value').value = d.value !== undefined ? d.value : d.nilai_pertumbuhan;
                document.getElementById('edit-regional-change').value = d.change !== undefined ? d.change : d.perubahan;
                const seriesArr = Array.isArray(d.series) ? d.series : [];
                document.getElementById('edit-regional-series').value = seriesArr.join(', ');
            }
        });

        // Inisialisasi data lokal segera bila AppState belum terisi
        if ((!AppState.regional || AppState.regional.length === 0) && window.__REGIONAL_DATA__ && Array.isArray(window.__REGIONAL_DATA__)) {
            AppState.regional = window.__REGIONAL_DATA__;
        }
        if (window.__SERIES_DATA__ && window.__SERIES_DATA__.growth) {
            AppState.series = window.__SERIES_DATA__;
        }
        if ((!AppState.sectors || AppState.sectors.length === 0) && window.__SECTORS_DATA__ && Array.isArray(window.__SECTORS_DATA__)) {
            AppState.sectors = window.__SECTORS_DATA__;
        }

        let activeTab = 'Regional Ranking';
        let selectedRegions = Array.from(document.querySelectorAll('.region-cb:checked')).map(cb => cb.value);
        if (selectedRegions.length === 0 && AppState.regional && AppState.regional.length > 0) {
            selectedRegions = AppState.regional.map(r => r.name);
        }

        function renderComparison() {
            const period = periodSelect ? periodSelect.value : '10';
            const sliceStart = period === '5' ? 5 : 0;
            const years = AppState.years.slice(sliceStart);
            const periodBadge = document.getElementById('comp-tab-period-badge');
            if (periodLabel) periodLabel.textContent = period === '5' ? '2020 - 2024' : '2015 - 2024';
            if (periodBadge) periodBadge.textContent = period === '5' ? '2020 - 2024' : '2015 - 2024';

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
                        <small>periode ${period === '5' ? '2020 - 2024' : '2015 - 2024'}</small>
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
                            <span class="badge info">Periode ${period === '5' ? '2020 - 2024' : '2015 - 2024'} (${rows.length} Wilayah)</span>
                        </div>
                        <div class="chart-wrap dynamic-chart" style="height:auto; min-height:160px;">
                            ${createHorizontalBarChartSVG(rankingBarData, { unit: '%', decimals: 2 })}
                        </div>
                    </div>

                    <div class="comparison-table-head" style="grid-template-columns: 50px 1.4fr .8fr .8fr .8fr;">
                        <span>Peringkat</span>
                        <span>Wilayah</span>
                        <span>Rata-rata Pertumbuhan</span>
                        <span>Perubahan Tahunan</span>
                        <span>Gap Terhadap Rerata</span>
                    </div>
                    <div class="comparison-ranking">`;

                rows.forEach((r, idx) => {
                    const isBwi = r.name === 'Kab. Banyuwangi';
                    const diff = r.avg - peerAvg;
                    listHtml += `
                        <div class="comparison-table-row ${isBwi ? 'selected' : ''}" style="grid-template-columns: 50px 1.4fr .8fr .8fr .8fr;">
                            <b>${idx + 1}</b>
                            <span>
                                <i class="region-avatar">${r.name.replace('Kab. ', '').replace('Kota ', '').slice(0, 2).toUpperCase()}</i>
                                <strong>${r.name}</strong>
                            </span>
                            <strong>${formatPct(r.avg)}</strong>
                            <small class="${r.chg >= 0 ? 'positive' : 'negative'}">${r.chg >= 0 ? '+' : ''}${formatNum(r.chg)} pp</small>
                            <span class="badge ${diff >= 0 ? 'success' : 'warning'}">${diff >= 0 ? '+' : ''}${formatNum(diff)} pp</span>
                        </div>`;
                });
                listHtml += `</div><div class="method-note">Peringkat didasarkan pada data PDRB ADHK Kabupaten/Kota di Jawa Timur selama periode terpilih. Kelola dataset melalui tabel Manajemen Dataset Wilayah di bawah.</div>`;
                tabContent.innerHTML = listHtml;

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
                rows.forEach(r => {
                    if (r.name !== 'Kab. Banyuwangi' && r.series && r.series.length > 0) {
                        benchmarkSeries.push({
                            name: r.name.replace('Kab. ', '').replace('Kota ', ''),
                            values: r.series.slice(sliceStart),
                            className: colorClasses[cIdx % colorClasses.length]
                        });
                        cIdx++;
                    }
                });

                tabContent.innerHTML = `
                    <div class="comparison-kpis" style="margin-bottom:15px; grid-template-columns: repeat(4, 1fr);">
                        <div class="comparison-kpi"><span>Rerata Banyuwangi</span><strong>${formatPct(mean(bwiSeries))}</strong><small>Periode terpilih</small></div>
                        <div class="comparison-kpi"><span>Rerata Jawa Timur</span><strong>${formatPct(mean(jatimSeries))}</strong><small>Benchmark Provinsi</small></div>
                        <div class="comparison-kpi"><span>Rerata Pembanding</span><strong>${formatPct(peerAvg)}</strong><small>${rows.length} kabupaten/kota</small></div>
                        <div class="comparison-kpi"><span>Wilayah Dicentang</span><strong>${rows.length}</strong><small>Daerah aktif</small></div>
                    </div>
                    <div class="chart-wrap dynamic-chart" style="height:290px;">
                        ${createLineChartSVG(years, benchmarkSeries, 290, '%')}
                    </div>
                    <div class="method-note">Garis biru menunjukkan Kab. Banyuwangi, garis hijau toska menunjukkan Provinsi Jawa Timur, dan garis berwarna lainnya mewakili kabupaten/kota pembanding yang dipilih.</div>`;

            } else if (activeTab === 'Gap Analysis') {
                tabEyebrow.textContent = 'GAP ANALYSIS';
                tabTitle.textContent = 'Selisih Kinerja terhadap Benchmark Utama';
                tabDesc.textContent = 'Analisis komparatif selisih pertumbuhan ekonomi Kab. Banyuwangi terhadap provinsi dan wilayah pembanding.';

                const jatimAvg = mean(AppState.series.eastJavaGrowth.slice(sliceStart));
                const gapJatim = bwi.avg - jatimAvg;

                const gapBarData = rows.map(r => {
                    const diff = r.avg - bwi.avg;
                    return {
                        label: r.name,
                        value: diff,
                        subLabel: `Rata-rata: ${formatPct(r.avg)} | Basis Banyuwangi: ${formatPct(bwi.avg)}`,
                        badge: diff > 0 ? `+${formatNum(diff)} pp` : (diff === 0 ? 'Acuan (0 pp)' : `${formatNum(diff)} pp`),
                        badgeClass: diff > 0 ? 'warning' : (diff === 0 ? 'primary' : 'success'),
                        isHighlight: r.name === 'Kab. Banyuwangi'
                    };
                });

                let gapTableRows = `
                    <tr>
                        <td><strong>Provinsi Jawa Timur</strong></td>
                        <td>${formatPct(bwi.avg)}</td>
                        <td>${formatPct(jatimAvg)}</td>
                        <td><strong class="${gapJatim >= 0 ? 'positive' : 'negative'}">${gapJatim >= 0 ? '+' : ''}${formatNum(gapJatim)} pp</strong></td>
                        <td><span class="badge ${gapJatim >= 0 ? 'success' : 'warning'}">${gapJatim >= 0 ? 'Di atas provinsi' : 'Di bawah provinsi'}</span></td>
                    </tr>
                    <tr>
                        <td><strong>Rerata Daerah Terpilih</strong></td>
                        <td>${formatPct(bwi.avg)}</td>
                        <td>${formatPct(peerAvg)}</td>
                        <td><strong class="${gap >= 0 ? 'positive' : 'negative'}">${gap >= 0 ? '+' : ''}${formatNum(gap)} pp</strong></td>
                        <td><span class="badge ${gap >= 0 ? 'success' : 'warning'}">${gap >= 0 ? 'Di atas rerata' : 'Di bawah rerata'}</span></td>
                    </tr>`;

                rows.filter(r => r.name !== 'Kab. Banyuwangi').forEach(r => {
                    const diffVsBwi = bwi.avg - r.avg;
                    gapTableRows += `
                        <tr>
                            <td><span>${r.name}</span></td>
                            <td>${formatPct(bwi.avg)}</td>
                            <td>${formatPct(r.avg)}</td>
                            <td><strong class="${diffVsBwi >= 0 ? 'positive' : 'negative'}">${diffVsBwi >= 0 ? '+' : ''}${formatNum(diffVsBwi)} pp</strong></td>
                            <td><span class="badge ${diffVsBwi >= 0 ? 'success' : 'warning'}">${diffVsBwi >= 0 ? 'Unggul vs Daerah' : 'Tertinggal'}</span></td>
                        </tr>`;
                });

                tabContent.innerHTML = `
                    <div class="gap-overview" style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:16px;">
                        <div class="metric-box" style="padding:12px;">
                            <span>Gap Banyuwangi terhadap Jawa Timur</span>
                            <strong class="${gapJatim >= 0 ? 'positive' : 'negative'}" style="font-size:18px;">${gapJatim >= 0 ? '+' : ''}${formatNum(gapJatim)} pp</strong>
                            <small>${gapJatim >= 0 ? 'Banyuwangi tumbuh lebih cepat dari provinsi' : 'Banyuwangi tumbuh di bawah provinsi'}</small>
                        </div>
                        <div class="metric-box" style="padding:12px;">
                            <span>Gap Banyuwangi terhadap Rerata ${rows.length} Daerah</span>
                            <strong class="${gap >= 0 ? 'positive' : 'negative'}" style="font-size:18px;">${gap >= 0 ? '+' : ''}${formatNum(gap)} pp</strong>
                            <small>${gap >= 0 ? 'Banyuwangi di atas rata-rata pembanding' : 'Banyuwangi di bawah rata-rata pembanding'}</small>
                        </div>
                    </div>

                    <div style="margin: 18px 0;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                            <span class="eyebrow">GRAFIK DIVERGENSI GAP PERTUMBUHAN EKONOMI (PP)</span>
                            <small style="color:var(--muted); font-size:9px;">Relatif terhadap Banyuwangi (${formatPct(bwi.avg)})</small>
                        </div>
                        <div class="chart-wrap dynamic-chart" style="height:auto; min-height:160px;">
                            ${createHorizontalBarChartSVG(gapBarData, { unit: ' pp', decimals: 2 })}
                        </div>
                    </div>

                    <div class="result-table-wrap">
                        <table class="result-table">
                            <thead><tr><th>Benchmark / Wilayah</th><th>Nilai Banyuwangi</th><th>Nilai Pembanding</th><th>Gap (Banyuwangi - Pembanding)</th><th>Status Relatif</th></tr></thead>
                            <tbody>${gapTableRows}</tbody>
                        </table>
                    </div>`;

            } else if (activeTab === 'Regional Profiling') {
                tabEyebrow.textContent = 'REGIONAL PROFILING';
                tabTitle.textContent = 'Profil Pertumbuhan & Stabilitas Wilayah';
                tabDesc.textContent = 'Karakteristik kinerja pertumbuhan ekonomi antar-kabupaten/kota selama periode terpilih.';

                let profCards = `<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap:16px; margin-bottom:22px;">`;
                rows.forEach((r, idx) => {
                    const vals = r.series.slice(sliceStart);
                    const maxV = Math.max(...vals);
                    const minV = Math.min(...vals);
                    const isBwi = r.name === 'Kab. Banyuwangi';
                    const avatarLetters = r.name.replace('Kab. ', '').replace('Kota ', '').slice(0, 2).toUpperCase();
                    profCards += `
                        <div style="background:${isBwi ? '#f0f9ff' : '#ffffff'}; border:1px solid ${isBwi ? '#93c5fd' : 'var(--line)'}; border-radius:12px; padding:20px; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div style="width:36px; height:36px; border-radius:10px; background:${isBwi ? '#0284c7' : '#eff6ff'}; color:${isBwi ? '#fff' : '#0284c7'}; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; border:1px solid ${isBwi ? '#0284c7' : '#bfdbfe'};">
                                        ${avatarLetters}
                                    </div>
                                    <div>
                                        <span style="font-weight:700; color:${isBwi ? 'var(--blue)' : 'var(--navy)'}; font-size:14px; display:block; margin-bottom:2px;">${r.name}</span>
                                        <small style="color:var(--muted); font-size:11px;">${isBwi ? 'Basis Acuan Analisis' : 'Wilayah Pembanding'}</small>
                                    </div>
                                </div>
                                <span class="badge ${isBwi ? 'primary' : 'neutral'}" style="font-size:10px; font-weight:700; padding:3px 8px;">#${idx + 1}</span>
                            </div>
                            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                                <div style="background:#f8fafc; border:1px solid #edf2f7; border-radius:8px; padding:10px 12px;">
                                    <span style="color:var(--muted); font-size:10px; text-transform:uppercase; font-weight:600; display:block; margin-bottom:4px;">Rata-rata:</span>
                                    <strong style="font-size:15px; color:var(--navy); display:block;">${formatPct(r.avg)}</strong>
                                </div>
                                <div style="background:#f8fafc; border:1px solid #edf2f7; border-radius:8px; padding:10px 12px;">
                                    <span style="color:var(--muted); font-size:10px; text-transform:uppercase; font-weight:600; display:block; margin-bottom:4px;">Perubahan:</span>
                                    <strong class="${r.chg >= 0 ? 'positive' : 'negative'}" style="font-size:15px; display:block;">${r.chg >= 0 ? '+' : ''}${formatNum(r.chg)} pp</strong>
                                </div>
                                <div style="background:#f8fafc; border:1px solid #edf2f7; border-radius:8px; padding:10px 12px;">
                                    <span style="color:var(--muted); font-size:10px; text-transform:uppercase; font-weight:600; display:block; margin-bottom:4px;">Tertinggi (Peak):</span>
                                    <strong style="font-size:15px; color:var(--green); display:block;">${formatPct(maxV)}</strong>
                                </div>
                                <div style="background:#f8fafc; border:1px solid #edf2f7; border-radius:8px; padding:10px 12px;">
                                    <span style="color:var(--muted); font-size:10px; text-transform:uppercase; font-weight:600; display:block; margin-bottom:4px;">Terendah (Trough):</span>
                                    <strong style="font-size:15px; color:var(--red); display:block;">${formatPct(minV)}</strong>
                                </div>
                            </div>
                        </div>`;
                });
                profCards += `</div>`;

                let profTable = `
                    <div class="result-table-wrap" style="margin-top:16px;">
                        <table class="result-table">
                            <thead><tr><th>Peringkat</th><th>Kabupaten / Kota</th><th>Rata-rata Pertumbuhan</th><th>Pertumbuhan Terkini</th><th>Tertinggi (Peak)</th><th>Terendah (Trough)</th><th>Gap vs Banyuwangi</th></tr></thead>
                            <tbody>`;
                rows.forEach((r, idx) => {
                    const vals = r.series.slice(sliceStart);
                    const maxV = Math.max(...vals);
                    const minV = Math.min(...vals);
                    const gapVsBwi = r.avg - bwi.avg;
                    profTable += `
                        <tr style="${r.name === 'Kab. Banyuwangi' ? 'background:#eff6ff; font-weight:600;' : ''}">
                            <td><strong>#${idx + 1}</strong></td>
                            <td><strong>${r.name}</strong> ${r.name === 'Kab. Banyuwangi' ? '<span class="badge primary" style="font-size:7px; margin-left:4px;">Acuan</span>' : ''}</td>
                            <td><strong>${formatPct(r.avg)}</strong></td>
                            <td>${formatPct(vals[vals.length - 1])}</td>
                            <td style="color:var(--green); font-weight:600;">${formatPct(maxV)}</td>
                            <td style="color:var(--red); font-weight:600;">${formatPct(minV)}</td>
                            <td><span class="badge ${gapVsBwi > 0 ? 'warning' : (gapVsBwi === 0 ? 'primary' : 'success')}">${gapVsBwi > 0 ? '+' : ''}${formatNum(gapVsBwi)} pp</span></td>
                        </tr>`;
                });
                profTable += `</tbody></table></div>`;

                tabContent.innerHTML = profCards + profTable;

            } else if (activeTab === 'Tipologi Klassen') {
                tabEyebrow.textContent = 'TIPOLOGI KLASSEN';
                tabTitle.textContent = 'Tipologi Klassen Pertumbuhan Wilayah';
                tabDesc.textContent = 'Klasifikasi posisi kuadran wilayah berdasarkan rata-rata laju pertumbuhan dan tren akselerasi tahunan.';

                const peerAvgChg = mean(rows.map(r => r.chg));
                const klassenRows = rows.map(r => {
                    const growthAbove = r.avg >= peerAvg;
                    const chgAbove = r.chg >= peerAvgChg;
                    let quadrant = 'IV';
                    let cls = 'Relatif Tertinggal';
                    if (growthAbove && chgAbove) { quadrant = 'I'; cls = 'Maju & Tumbuh Cepat'; }
                    else if (growthAbove && !chgAbove) { quadrant = 'II'; cls = 'Maju tapi Melambat'; }
                    else if (!growthAbove && chgAbove) { quadrant = 'III'; cls = 'Potensial Berkembang'; }

                    return {
                        name: r.name,
                        short: r.name.replace('Kab. ', '').replace('Kota ', ''),
                        quadrant,
                        classification: cls,
                        growthGap: r.avg - peerAvg,
                        shareGap: r.chg - peerAvgChg,
                        avg: r.avg,
                        chg: r.chg
                    };
                });

                let klassenTable = `
                    <div style="margin-top:16px;" class="result-table-wrap">
                        <table class="result-table">
                            <thead><tr><th>Wilayah</th><th>Kuadran</th><th>Klasifikasi</th><th>Rata-rata Pertumbuhan</th><th>Gap Pertumbuhan</th><th>Akselerasi Tahunan</th><th>Gap Akselerasi</th></tr></thead>
                            <tbody>`;
                klassenRows.forEach(item => {
                    const isBwi = item.name === 'Kab. Banyuwangi';
                    const qBadgeClass = item.quadrant === 'I' ? 'success' : (item.quadrant === 'II' ? 'warning' : (item.quadrant === 'III' ? 'info' : 'neutral'));
                    klassenTable += `
                        <tr style="${isBwi ? 'background:#edf5fc; font-weight:600;' : ''}">
                            <td><strong>${item.name}</strong> ${isBwi ? '<span class="badge primary" style="font-size:7px;">Banyuwangi</span>' : ''}</td>
                            <td><span class="badge ${qBadgeClass}">Kuadran ${item.quadrant}</span></td>
                            <td><strong>${item.classification}</strong></td>
                            <td>${formatPct(item.avg)}</td>
                            <td class="${item.growthGap >= 0 ? 'positive' : 'negative'}">${item.growthGap >= 0 ? '+' : ''}${formatNum(item.growthGap)} pp</td>
                            <td>${item.chg >= 0 ? '+' : ''}${formatNum(item.chg)} pp</td>
                            <td class="${item.shareGap >= 0 ? 'positive' : 'negative'}">${item.shareGap >= 0 ? '+' : ''}${formatNum(item.shareGap)} pp</td>
                        </tr>`;
                });
                klassenTable += `</tbody></table></div>`;

                tabContent.innerHTML = createKlassenSVG(klassenRows, {
                    xLabel: `Gap laju pertumbuhan wilayah terhadap rata-rata (${formatPct(peerAvg)}) (pp)`,
                    yLabel: `Gap akselerasi tahunan terhadap rata-rata (${formatNum(peerAvgChg)} pp)`,
                    note: 'Sumbu horizontal: Gap pertumbuhan terhadap rata-rata wilayah. Sumbu vertikal: Gap akselerasi tahunan. Kuadran I: Maju & Cepat, Kuadran II: Maju tapi Melambat, Kuadran III: Potensial, Kuadran IV: Relatif Tertinggal.'
                }) + klassenTable;

            } else if (activeTab === 'Peer Comparison') {
                tabEyebrow.textContent = 'PEER COMPARISON';
                tabTitle.textContent = 'Kartu Profil Head-to-Head Wilayah Pembanding';
                tabDesc.textContent = 'Perbandingan langsung indikator kinerja Kab. Banyuwangi dengan masing-masing daerah pembanding.';

                const peerList = rows.filter(r => r.name !== 'Kab. Banyuwangi');
                
                let peerHtml = `
                    <div style="background:#f8fafc; border:1px solid var(--line); border-radius:10px; padding:12px 16px; margin-bottom:18px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div style="width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg, #1e40af, #0284c7); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; box-shadow:0 2px 6px rgba(30,64,175,0.2);">
                                BW
                            </div>
                            <div>
                                <strong style="font-size:13px; color:var(--navy); display:block;">Basis Acuan: Kab. Banyuwangi</strong>
                                <small style="color:var(--muted); font-size:10px;">Rata-rata: <b style="color:var(--blue);">${formatPct(bwi.avg)}</b> | Perubahan: <b class="${bwi.chg >= 0 ? 'positive' : 'negative'}">${bwi.chg >= 0 ? '+' : ''}${formatNum(bwi.chg)} pp</b> (${period === '5' ? '2020 - 2024' : '2015 - 2024'})</small>
                            </div>
                        </div>
                        <span class="badge primary" style="font-size:10px; padding:4px 10px;">Wilayah Acuan Utama</span>
                    </div>`;

                if (peerList.length === 0) {
                    peerHtml += `<div class="table-empty" style="padding:32px; text-align:center; color:var(--muted); font-size:11px;">Tidak ada wilayah pembanding lain yang dipilih. Centang wilayah tambahan pada filter di atas.</div>`;
                } else {
                    peerHtml += `<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:16px;">`;
                    peerList.forEach((r, idx) => {
                        const bwiLead = bwi.avg - r.avg;
                        const avatarLetters = r.name.replace('Kab. ', '').replace('Kota ', '').slice(0, 2).toUpperCase();
                        peerHtml += `
                            <div style="background:#ffffff; border:1px solid var(--line); border-radius:14px; padding:20px; box-shadow:0 2px 10px rgba(15,23,42,0.04); display:flex; flex-direction:column; justify-content:space-between; transition:transform 0.15s ease, box-shadow 0.15s ease;">
                                <div>
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <div style="width:38px; height:38px; border-radius:10px; background:#eff6ff; color:#0284c7; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px; border:1px solid #bfdbfe;">
                                                ${avatarLetters}
                                            </div>
                                            <div>
                                                <h4 style="font-size:15px; font-weight:700; color:var(--navy); margin:0 0 3px 0;">${r.name}</h4>
                                                <small style="color:var(--muted); font-size:11px;">Wilayah Pembanding #${idx + 1}</small>
                                            </div>
                                        </div>
                                        <span class="badge neutral" style="font-size:10px; font-weight:700; padding:3px 10px;">Peringkat #${idx + 1}</span>
                                    </div>

                                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:14px;">
                                        <div style="background:#f8fafc; border:1px solid #edf2f7; border-radius:10px; padding:10px 12px;">
                                            <span style="display:block; font-size:10px; color:var(--muted); font-weight:600; text-transform:uppercase; margin-bottom:3px;">Rata-rata</span>
                                            <strong style="display:block; font-size:16px; font-weight:700; color:var(--navy);">${formatPct(r.avg)}</strong>
                                            <small style="font-size:11px; color:var(--muted);">${period === '5' ? '2020 - 2024' : '2015 - 2024'}</small>
                                        </div>
                                        <div style="background:#f8fafc; border:1px solid #edf2f7; border-radius:10px; padding:10px 12px;">
                                            <span style="display:block; font-size:10px; color:var(--muted); font-weight:600; text-transform:uppercase; margin-bottom:3px;">Perubahan</span>
                                            <strong class="${r.chg >= 0 ? 'positive' : 'negative'}" style="display:block; font-size:16px; font-weight:700;">${r.chg >= 0 ? '+' : ''}${formatNum(r.chg)} pp</strong>
                                            <small style="font-size:11px; color:var(--muted);">${r.chg >= 0 ? 'Menguat' : 'Melemah'}</small>
                                        </div>
                                    </div>
                                </div>

                                <div style="background:${bwiLead >= 0 ? '#f0fdf4' : '#fef2f2'}; border:1px solid ${bwiLead >= 0 ? '#bbf7d0' : '#fecaca'}; border-radius:10px; padding:12px 14px; margin-top:6px;">
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                        <span style="font-size:11px; font-weight:600; color:${bwiLead >= 0 ? '#166534' : '#991b1b'};">Posisi Banyuwangi:</span>
                                        <span class="badge ${bwiLead >= 0 ? 'success' : 'warning'}" style="font-size:10px; font-weight:700; padding:2px 8px;">
                                            ${bwiLead >= 0 ? 'Banyuwangi Unggul' : 'Banyuwangi Tertinggal'}
                                        </span>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; align-items:baseline;">
                                        <strong style="font-size:14px; color:${bwiLead >= 0 ? '#15803d' : '#b91c1c'}; font-weight:700;">
                                            ${bwiLead >= 0 ? '+' : ''}${formatNum(bwiLead)} pp
                                        </strong>
                                        <small style="font-size:11px; color:${bwiLead >= 0 ? '#166534' : '#991b1b'};">
                                            ${bwiLead >= 0 ? 'di atas rerata wilayah ini' : 'di bawah rerata wilayah ini'}
                                        </small>
                                    </div>
                                </div>
                            </div>`;
                    });
                    peerHtml += `</div>`;
                }
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

        // Multiselect Dropdown Logic
        const dropdownTrigger = document.getElementById('btn-toggle-region-dropdown');
        const dropdownPanel = document.getElementById('region-dropdown-panel');
        const searchInput = document.getElementById('region-search-input');
        const clearSearchBtn = document.getElementById('btn-clear-region-search');
        const selectAllBtn = document.getElementById('btn-select-all-regions');
        const deselectAllBtn = document.getElementById('btn-deselect-all-regions');
        const dropdownLabel = document.getElementById('region-dropdown-label');
        const counterBadge = document.getElementById('region-selected-counter');
        const emptyMsg = document.getElementById('region-search-not-found');

        function updateDropdownUI() {
            const allCbs = document.querySelectorAll('.region-cb');
            const checkedCbs = document.querySelectorAll('.region-cb:checked');
            const total = allCbs.length;
            const count = checkedCbs.length;

            if (counterBadge) {
                counterBadge.textContent = `${count} / ${total} dipilih`;
            }

            if (dropdownLabel) {
                if (count === 0) {
                    dropdownLabel.textContent = 'Tidak ada wilayah dipilih';
                } else if (count === total) {
                    dropdownLabel.textContent = `Semua Wilayah Terpilih (${total})`;
                } else if (count === 1) {
                    dropdownLabel.textContent = checkedCbs[0].value;
                } else if (count === 2) {
                    dropdownLabel.textContent = `${checkedCbs[0].value}, ${checkedCbs[1].value}`;
                } else {
                    dropdownLabel.textContent = `${count} Wilayah Terpilih`;
                }
            }
        }

        window.toggleRegionDropdown = function(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            if (!dropdownPanel) return;
            const isHidden = dropdownPanel.style.display === 'none' || !dropdownPanel.style.display || getComputedStyle(dropdownPanel).display === 'none';
            dropdownPanel.style.display = isHidden ? 'block' : 'none';
            if (dropdownTrigger) {
                dropdownTrigger.classList.toggle('active', isHidden);
                dropdownTrigger.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
            }
            if (isHidden && searchInput) {
                setTimeout(() => searchInput.focus(), 60);
            }
        };

        if (dropdownPanel && !dropdownPanel.dataset.bound) {
            dropdownPanel.dataset.bound = 'true';

            dropdownPanel.addEventListener('click', (e) => {
                e.stopPropagation();
            });

            document.addEventListener('click', (e) => {
                if (dropdownPanel && dropdownPanel.style.display !== 'none') {
                    if (!dropdownPanel.contains(e.target) && (!dropdownTrigger || !dropdownTrigger.contains(e.target))) {
                        dropdownPanel.style.display = 'none';
                        if (dropdownTrigger) {
                            dropdownTrigger.classList.remove('active');
                            dropdownTrigger.setAttribute('aria-expanded', 'false');
                        }
                    }
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && dropdownPanel && dropdownPanel.style.display !== 'none') {
                    dropdownPanel.style.display = 'none';
                    if (dropdownTrigger) {
                        dropdownTrigger.classList.remove('active');
                        dropdownTrigger.setAttribute('aria-expanded', 'false');
                        dropdownTrigger.focus();
                    }
                }
            });
        }

        if (searchInput && !searchInput.dataset.bound) {
            searchInput.dataset.bound = 'true';
            searchInput.addEventListener('input', function () {
                const query = this.value.toLowerCase().trim();
                const items = document.querySelectorAll('.region-option-item');
                let visibleCount = 0;

                items.forEach(item => {
                    const name = item.getAttribute('data-name') || '';
                    const match = name.includes(query);
                    item.style.display = match ? 'flex' : 'none';
                    if (match) visibleCount++;
                });

                if (emptyMsg) emptyMsg.style.display = visibleCount === 0 ? 'block' : 'none';
                if (clearSearchBtn) clearSearchBtn.style.display = query ? 'block' : 'none';
            });
        }

        if (clearSearchBtn && searchInput && !clearSearchBtn.dataset.bound) {
            clearSearchBtn.dataset.bound = 'true';
            clearSearchBtn.addEventListener('click', () => {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));
                searchInput.focus();
            });
        }

        if (selectAllBtn && !selectAllBtn.dataset.bound) {
            selectAllBtn.dataset.bound = 'true';
            selectAllBtn.addEventListener('click', () => {
                document.querySelectorAll('.region-cb').forEach(cb => {
                    const parent = cb.closest('.region-option-item');
                    if (!parent || parent.style.display !== 'none') {
                        cb.checked = true;
                        if (!selectedRegions.includes(cb.value)) {
                            selectedRegions.push(cb.value);
                        }
                    }
                });
                renderComparison();
                updateDropdownUI();
            });
        }

        if (deselectAllBtn && !deselectAllBtn.dataset.bound) {
            deselectAllBtn.dataset.bound = 'true';
            deselectAllBtn.addEventListener('click', () => {
                document.querySelectorAll('.region-cb').forEach(cb => {
                    if (!cb.disabled) {
                        const parent = cb.closest('.region-option-item');
                        if (!parent || parent.style.display !== 'none') {
                            cb.checked = false;
                            selectedRegions = selectedRegions.filter(r => r !== cb.value);
                        }
                    }
                });
                renderComparison();
                updateDropdownUI();
            });
        }

        document.querySelectorAll('.region-cb').forEach(cb => {
            if (!cb.dataset.bound) {
                cb.dataset.bound = 'true';
                cb.addEventListener('change', function () {
                    const val = this.value;
                    if (this.checked) {
                        if (!selectedRegions.includes(val)) selectedRegions.push(val);
                    } else {
                        selectedRegions = selectedRegions.filter(r => r !== val);
                    }
                    renderComparison();
                    updateDropdownUI();
                });
            }
        });

        if (btnReset && !btnReset.dataset.bound) {
            btnReset.dataset.bound = 'true';
            btnReset.addEventListener('click', () => {
                selectedRegions = AppState.regional.map(r => r.name);
                document.querySelectorAll('.region-cb').forEach(cb => cb.checked = true);
                renderComparison();
                updateDropdownUI();
            });
        }

        updateDropdownUI();

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
            openAddBtnIds: ['btn-open-add-sector'],
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
        const btnReverseLegend = document.getElementById('btn-reverse-legend');

        let zoomLevel = 1.0;
        let selectedDistrict = 'Banyuwangi';
        let activeMode = 'Thematic Layer';
        let legendReversed = false;

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
                const classOrder = legendReversed ? [5, 4, 3, 2, 1] : [1, 2, 3, 4, 5];
                const step = range / 5;
                const decimals = step < 0.2 ? 2 : (step >= 10 ? 0 : 1);
                const unit = isInv ? ' poin' : '%';

                const mapContextUnit = document.getElementById('map-context-unit');
                if (mapContextUnit) {
                    mapContextUnit.textContent = isInv ? 'Poin' : '%';
                }

                const barOrder = legendReversed ? [5, 4, 3, 2, 1] : [1, 2, 3, 4, 5];
                const barHtml = `
                    <div class="legend-color-bar" title="Rentang nilai visual: ${formatNum(minV, decimals)}${unit} s.d. ${formatNum(maxV, decimals)}${unit}">
                        ${barOrder.map(c => `<span class="l${c}"></span>`).join('')}
                    </div>
                    <div class="legend-bar-labels">
                        <small>${legendReversed ? 'Tinggi' : 'Rendah'} (${formatNum(legendReversed ? maxV : minV, decimals)}${unit})</small>
                        <small>${legendReversed ? 'Rendah' : 'Tinggi'} (${formatNum(legendReversed ? minV : maxV, decimals)}${unit})</small>
                    </div>
                `;

                const itemsHtml = classOrder.map(cls => {
                    const low = minV + (range * (cls - 1)) / 5;
                    const high = minV + (range * cls) / 5;
                    return `
                        <div class="legend-row" data-class="${cls}" title="Arahkan kursor atau klik untuk menyorot kecamatan Kelas ${cls}">
                            <div class="legend-row-main">
                                <span class="legend-chip l${cls}"></span>
                                <b class="legend-class-lbl">Kelas ${cls}</b>
                            </div>
                            <span class="legend-val-range">${formatNum(low, decimals)} – ${formatNum(high, decimals)}${unit}</span>
                        </div>`;
                }).join('');

                legendContainer.innerHTML = barHtml + itemsHtml;

                // Interactive highlight on hover
                legendContainer.querySelectorAll('.legend-row').forEach(row => {
                    const targetCls = row.getAttribute('data-class');
                    row.addEventListener('mouseenter', () => {
                        if (!markersGroup) return;
                        markersGroup.querySelectorAll('.area-marker').forEach(m => {
                            if (m.classList.contains(`l${targetCls}`)) {
                                m.style.filter = 'drop-shadow(0 0 6px rgba(21,154,156,0.95))';
                                m.style.opacity = '1';
                            } else {
                                m.style.opacity = '0.3';
                            }
                        });
                    });
                    row.addEventListener('mouseleave', () => {
                        if (!markersGroup) return;
                        markersGroup.querySelectorAll('.area-marker').forEach(m => {
                            m.style.filter = '';
                            m.style.opacity = '1';
                        });
                    });
                });
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

        if (btnReverseLegend) {
            btnReverseLegend.addEventListener('click', function (e) {
                e.preventDefault();
                legendReversed = !legendReversed;
                this.classList.toggle('active', legendReversed);
                renderMap();
            });
        }

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
            openAddBtnIds: ['btn-open-add-district'],
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
            openAddBtnIds: ['btn-open-add-prospek'],
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

        function calcEwsStatus(val, th, direction) {
            const v = Number(val);
            const t = Number(th);
            const dir = String(direction || 'above').toLowerCase();
            if (isNaN(v) || isNaN(t)) return 'Normal';
            if (v === t) return 'Waspada';
            if (dir === 'below') {
                return (v < t) ? 'Kritis' : 'Normal';
            } else {
                return (v > t) ? 'Kritis' : 'Normal';
            }
        }

        // Seed AppState.ews from server-rendered global state if needed
        if ((!AppState.ews || AppState.ews.length === 0) && window.__EWS_DATA__ && Array.isArray(window.__EWS_DATA__)) {
            AppState.ews = window.__EWS_DATA__;
        }

        function renderEWS() {
            let normalC = 0, warnC = 0, critC = 0, followedC = 0;
            AppState.ews.forEach(item => {
                const val = Number(item.value !== undefined ? item.value : (item.nilai_terkini || 0));
                const th = Number(item.threshold !== undefined ? item.threshold : (item.ambang_batas || 0));
                const dir = item.direction || item.arah_risiko || 'above';
                const st = calcEwsStatus(val, th, dir);
                item.status = st;
                item.status_risiko = st;

                if (st === 'Normal') normalC++;
                else if (st === 'Waspada') warnC++;
                else if (st === 'Kritis') critC++;

                if (item.is_followed_up || item.sudah_ditindaklanjuti) followedC++;
            });

            if (statsContainer) {
                statsContainer.innerHTML = `
                    <div class="risk-stat danger"><span>Status Kritis</span><strong>${critC}</strong><small>Memerlukan intervensi segera</small></div>
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
                        <span style="text-align:right;">Status Risiko</span>
                    </div>
                    ${filtered.map(item => {
                        const val = Number(item.value !== undefined ? item.value : (item.nilai_terkini || 0));
                        const th = Number(item.threshold !== undefined ? item.threshold : (item.ambang_batas || 0));
                        const dir = item.direction || item.arah_risiko || 'above';
                        const st = calcEwsStatus(val, th, dir);
                        const badgeCls = (st === 'Kritis') ? 'danger' : ((st === 'Waspada') ? 'warning' : 'success');
                        const rowStateCls = (st === 'Kritis') ? 'alerted' : ((st === 'Waspada') ? 'warning-row' : 'normal-row');
                        const unit = item.unit || item.satuan || '%';
                        const code = item.code || item.kode_indikator || '';
                        const name = item.name || item.nama_indikator || '';
                        return `
                        <div class="risk-row btn-alert-row ${rowStateCls}" data-code="${code}" role="button" tabindex="0" style="cursor:pointer;" title="Klik untuk analisis & penyesuaian threshold">
                            <div class="risk-indicator">
                                <strong>${name}</strong>
                                <small>
                                    <code>${code}</code>
                                    <span style="color:var(--muted);">• ${dir === 'above' ? 'Kritis jika > Ambang' : 'Kritis jika < Ambang'}</span>
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
                    const val = Number(item.value !== undefined ? item.value : (item.nilai_terkini || 0));
                    const th = Number(item.threshold !== undefined ? item.threshold : (item.ambang_batas || 0));
                    const dir = item.direction || item.arah_risiko || 'above';
                    const st = calcEwsStatus(val, th, dir);
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
                    const dir = item.direction || item.arah_risiko || 'above';
                    const unit = item.unit || item.satuan || '%';
                    const diffPct = th !== 0 ? ((val - th) / Math.abs(th)) * 100 : 0;
                    const st = calcEwsStatus(val, th, dir);
                    const badgeCls = (st === 'Kritis') ? 'danger' : ((st === 'Waspada') ? 'warning' : 'success');
                    return {
                        label: item.name || item.nama_indikator,
                        value: val,
                        subLabel: `Ambang: ${formatNum(th, 2)} ${unit} • Deviasi: ${diffPct >= 0 ? '+' : ''}${formatNum(diffPct, 1)}%`,
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
            const dir = String(item.direction || item.arah_risiko || 'above').toLowerCase();
            const unit = item.unit || item.satuan || '%';
            const st = calcEwsStatus(val, th, dir);
            const distance = (th !== 0) ? Math.abs((val - th) / th * 100) : 0;

            const nameEl = document.getElementById('modal-alert-name');
            if (nameEl) nameEl.textContent = name;
            const metaEl = document.getElementById('modal-alert-meta');
            if (metaEl) metaEl.textContent = `${src} • ${code}`;
            const badge = document.getElementById('modal-alert-badge');
            if (badge) {
                badge.textContent = st.toUpperCase();
                badge.className = 'badge ' + (st === 'Kritis' ? 'danger' : (st === 'Waspada' ? 'warning' : 'success'));
            }

            const mVal = document.getElementById('modal-metric-val');
            if (mVal) mVal.textContent = `${formatNum(val, 2)} ${unit}`;
            const mTh = document.getElementById('modal-metric-th');
            if (mTh) mTh.textContent = `${formatNum(th, 2)} ${unit}`;
            const devEl = document.getElementById('modal-metric-dev');
            if (devEl) {
                const sign = (val >= th) ? '+' : '-';
                devEl.textContent = `${sign}${formatNum(distance, 1)}%`;
                devEl.className = (st === 'Kritis') ? 'negative' : ((st === 'Waspada') ? 'warning' : 'positive');
            }

            const editorUnit = document.getElementById('modal-editor-unit');
            if (editorUnit) editorUnit.textContent = unit;
            document.querySelectorAll('.modal-unit-label').forEach(el => el.textContent = unit);
            const editorLabel = document.getElementById('modal-editor-label');
            if (editorLabel) editorLabel.textContent = `${formatNum(th, 2)} ${unit}`;
            const inputVal = document.getElementById('modal-input-value');
            if (inputVal) inputVal.value = val;
            const inputTh = document.getElementById('modal-input-threshold');
            if (inputTh) inputTh.value = th;
            const inputDir = document.getElementById('modal-input-direction');
            if (inputDir) inputDir.value = dir;

            const liveBadge = document.getElementById('modal-live-preview-badge');
            if (liveBadge) {
                liveBadge.textContent = 'Status: ' + st.toUpperCase();
                liveBadge.className = 'badge ' + (st === 'Kritis' ? 'danger' : (st === 'Waspada' ? 'warning' : 'success'));
            }

            let interpretationText = '';
            if (st === 'Waspada') {
                interpretationText = `Nilai riil saat ini (${formatNum(val, 2)} ${unit}) sama persis dengan batas ambang (${formatNum(th, 2)} ${unit}). Status Waspada, pantau tren perkembangan indikator secara berkala.`;
            } else if (st === 'Kritis') {
                if (dir === 'below') {
                    interpretationText = `Nilai riil saat ini (${formatNum(val, 2)} ${unit}) berada di bawah target batas ambang (${formatNum(th, 2)} ${unit}) sebesar ${formatNum(distance, 1)}%. Status Kritis, koordinasikan langkah percepatan dan intervensi segera.`;
                } else {
                    interpretationText = `Nilai riil saat ini (${formatNum(val, 2)} ${unit}) telah melampaui batas ambang risiko (${formatNum(th, 2)} ${unit}) sebesar ${formatNum(distance, 1)}%. Status Kritis, koordinasikan langkah intervensi segera.`;
                }
            } else {
                if (dir === 'below') {
                    interpretationText = `Nilai riil saat ini (${formatNum(val, 2)} ${unit}) melampaui target ambang batas minimum (${formatNum(th, 2)} ${unit}). Status Normal dan berada dalam performa baik.`;
                } else {
                    interpretationText = `Nilai riil saat ini (${formatNum(val, 2)} ${unit}) berada di bawah batas ambang risiko (${formatNum(th, 2)} ${unit}). Status Normal dan berada dalam batas aman.`;
                }
            }
            const interpEl = document.getElementById('modal-alert-interpretation');
            if (interpEl) interpEl.textContent = interpretationText;

            const btnFollow = document.getElementById('modal-btn-toggle-followup');
            if (btnFollow) {
                const isFollowed = Boolean(item.is_followed_up || item.sudah_ditindaklanjuti);
                btnFollow.textContent = isFollowed ? 'Batalkan Tindak Lanjut' : 'Tandai Ditindaklanjuti';
            }

            modalBackdrop.style.display = 'grid';
        }

        function updateModalLivePreview() {
            if (!selectedAlertCode) return;
            const item = AppState.ews.find(i => (i.code === selectedAlertCode || i.kode_indikator === selectedAlertCode));
            if (!item) return;

            const unit = item.unit || item.satuan || '%';
            const inputValEl = document.getElementById('modal-input-value');
            const inputThEl = document.getElementById('modal-input-threshold');
            const inputDirEl = document.getElementById('modal-input-direction');
            if (!inputThEl) return;

            const currentVal = inputValEl ? parseFloat(inputValEl.value) : Number(item.value !== undefined ? item.value : (item.nilai_terkini || 0));
            const newTh = parseFloat(inputThEl.value);
            const newDir = inputDirEl ? (inputDirEl.value || 'above').toLowerCase() : 'above';
            if (isNaN(newTh) || isNaN(currentVal)) return;

            const st = calcEwsStatus(currentVal, newTh, newDir);
            const badgeCls = (st === 'Kritis') ? 'danger' : ((st === 'Waspada') ? 'warning' : 'success');
            const distance = (newTh !== 0) ? Math.abs((currentVal - newTh) / newTh * 100) : 0;

            const mVal = document.getElementById('modal-metric-val');
            if (mVal) mVal.textContent = `${formatNum(currentVal, 2)} ${unit}`;
            const mTh = document.getElementById('modal-metric-th');
            if (mTh) mTh.textContent = `${formatNum(newTh, 2)} ${unit}`;

            const liveBadge = document.getElementById('modal-live-preview-badge');
            if (liveBadge) {
                liveBadge.textContent = 'Status: ' + st.toUpperCase();
                liveBadge.className = 'badge ' + badgeCls;
            }

            const headerBadge = document.getElementById('modal-alert-badge');
            if (headerBadge) {
                headerBadge.textContent = st.toUpperCase();
                headerBadge.className = 'badge ' + badgeCls;
            }

            const devEl = document.getElementById('modal-metric-dev');
            if (devEl) {
                const sign = (currentVal >= newTh) ? '+' : '-';
                devEl.textContent = `${sign}${formatNum(distance, 1)}%`;
                devEl.className = (st === 'Kritis') ? 'negative' : ((st === 'Waspada') ? 'warning' : 'positive');
            }

            let interp = '';
            if (st === 'Waspada') {
                interp = `Nilai riil (${formatNum(currentVal, 2)} ${unit}) sama persis dengan batas ambang (${formatNum(newTh, 2)} ${unit}). Status Waspada.`;
            } else if (st === 'Kritis') {
                if (newDir === 'below') {
                    interp = `Nilai riil (${formatNum(currentVal, 2)} ${unit}) berada di bawah target batas ambang (${formatNum(newTh, 2)} ${unit}) sebesar ${formatNum(distance, 1)}%. Status Kritis, koordinasikan intervensi.`;
                } else {
                    interp = `Nilai riil (${formatNum(currentVal, 2)} ${unit}) melampaui batas ambang risiko (${formatNum(newTh, 2)} ${unit}) sebesar ${formatNum(distance, 1)}%. Status Kritis, koordinasikan intervensi.`;
                }
            } else {
                if (newDir === 'below') {
                    interp = `Nilai riil (${formatNum(currentVal, 2)} ${unit}) berhasil melampaui target ambang batas minimum (${formatNum(newTh, 2)} ${unit}). Status Normal dan aman.`;
                } else {
                    interp = `Nilai riil (${formatNum(currentVal, 2)} ${unit}) berada di bawah batas ambang risiko (${formatNum(newTh, 2)} ${unit}). Status Normal dan aman.`;
                }
            }
            const interpEl = document.getElementById('modal-alert-interpretation');
            if (interpEl) interpEl.textContent = interp;
        }

        const inputValEl = document.getElementById('modal-input-value');
        const inputThresholdEl = document.getElementById('modal-input-threshold');
        const inputDirEl = document.getElementById('modal-input-direction');
        if (inputValEl) inputValEl.addEventListener('input', updateModalLivePreview);
        if (inputThresholdEl) inputThresholdEl.addEventListener('input', updateModalLivePreview);
        if (inputDirEl) inputDirEl.addEventListener('change', updateModalLivePreview);

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
                const inputValEl = document.getElementById('modal-input-value');
                const inputThEl = document.getElementById('modal-input-threshold');
                const inputDirEl = document.getElementById('modal-input-direction');
                const th = inputThEl ? parseFloat(inputThEl.value) : NaN;
                const val = inputValEl ? parseFloat(inputValEl.value) : NaN;
                const dir = inputDirEl ? (inputDirEl.value || 'above').toLowerCase() : 'above';

                if (selectedAlertCode && Number.isFinite(th)) {
                    modalSave.disabled = true;
                    const prevTxt = modalSave.textContent;
                    modalSave.textContent = 'Menyimpan...';

                    const fd = new FormData();
                    fd.append('code', selectedAlertCode);
                    fd.append('threshold', th);
                    fd.append('direction', dir);
                    if (Number.isFinite(val)) {
                        fd.append('value', val);
                    }

                    fetch(BASE_URL + 'api/update_threshold', { method: 'POST', body: fd })
                        .then(res => res.json())
                        .then(data => {
                            modalSave.disabled = false;
                            modalSave.textContent = prevTxt;

                            const target = AppState.ews.find(i => (i.code === selectedAlertCode || i.kode_indikator === selectedAlertCode));
                            if (target) {
                                const finalVal = Number.isFinite(val) ? val : Number(target.value !== undefined ? target.value : (target.nilai_terkini || 0));
                                target.value = finalVal;
                                target.nilai_terkini = finalVal;
                                target.threshold = th;
                                target.ambang_batas = th;
                                target.direction = dir;
                                target.arah_risiko = dir;
                                target.status = calcEwsStatus(finalVal, th, dir);
                                target.status_risiko = target.status;
                            }
                            renderEWS();
                            closeModal();
                        })
                        .catch(() => {
                            modalSave.disabled = false;
                            modalSave.textContent = prevTxt;
                            alert('Gagal menyimpan pembaruan. Silakan coba kembali.');
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
                    ['Kode', 'Indikator', 'Nilai Terkini', 'Threshold', 'Arah Risiko', 'Status'],
                    ...AppState.ews.map(i => {
                        const v = Number(i.value !== undefined ? i.value : (i.nilai_terkini || 0));
                        const t = Number(i.threshold !== undefined ? i.threshold : (i.ambang_batas || 0));
                        const dir = i.direction || i.arah_risiko || 'above';
                        return [i.code || i.kode_indikator, i.name || i.nama_indikator, v, t, dir, calcEwsStatus(v, t, dir)];
                    })
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
            openAddBtnIds: ['btn-open-add-ews'],
            closeAddBtnIds: ['modal-btn-close-add-ews', 'modal-btn-cancel-add-ews'],
            closeEditBtnIds: ['modal-btn-close-edit-ews', 'modal-btn-cancel-edit-ews'],
            formAddId: 'form-add-ews',
            formEditId: 'form-edit-ews',
            addErrorId: 'add-ews-error',
            editErrorId: 'edit-ews-error',
            populateForm: function (item) {
                const curVal = item.nilai_terkini !== undefined ? item.nilai_terkini : (item.value || '');
                const curTh = item.ambang_batas !== undefined ? item.ambang_batas : (item.threshold || '');
                document.getElementById('edit-ews-id').value = item.id;
                document.getElementById('edit-ews-code').value = item.kode_indikator || item.code || '';
                document.getElementById('edit-ews-unit').value = item.satuan || item.unit || '';
                document.getElementById('edit-ews-name').value = item.nama_indikator || item.name || '';
                document.getElementById('edit-ews-current').value = curVal;
                document.getElementById('edit-ews-threshold').value = curTh;
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
            editBtnClass: 'btn-edit-insight',
            deleteBtnClass: 'btn-delete-insight',
            restoreBtnClass: 'btn-restore-insight',
            hardDeleteBtnClass: 'btn-hard-delete-insight',
            editBtnClass: 'btn-edit-insight',
            deleteBtnClass: 'btn-delete-insight',
            restoreBtnClass: 'btn-restore-insight',
            hardDeleteBtnClass: 'btn-hard-delete-insight',
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
            openAddBtnIds: ['btn-open-add-report'],
            closeAddBtnIds: ['modal-btn-close-add-report', 'modal-btn-cancel-add-report'],
            closeEditBtnIds: ['modal-btn-close-edit-report', 'modal-btn-cancel-edit-report'],
            formAddId: 'form-add-report',
            formEditId: 'form-edit-report',
            addErrorId: 'add-report-error',
            editErrorId: 'edit-report-error',
            editBtnClass: 'btn-edit-report',
            deleteBtnClass: 'btn-delete-report',
            restoreBtnClass: 'btn-restore-laporan',
            hardDeleteBtnClass: 'btn-hard-delete-laporan',
            editBtnClass: 'btn-edit-report',
            deleteBtnClass: 'btn-delete-report',
            restoreBtnClass: 'btn-restore-laporan',
            hardDeleteBtnClass: 'btn-hard-delete-laporan',
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

    // 8.5. ADMIN DASHBOARD PAGE
    function initAdminDashboardPage() {
        const cleanSite = (typeof SITE_URL !== 'undefined' ? SITE_URL : (typeof BASE_URL !== 'undefined' ? BASE_URL : '')).replace(/\/+$/, '');
        document.querySelectorAll('.btn-quick-verify').forEach(btn => {
            btn.addEventListener('click', function () {
                const code = this.getAttribute('data-code');
                const year = this.getAttribute('data-year');
                
                const self = this;
                self.disabled = true;
                self.style.opacity = '0.6';

                const fd = new FormData();
                fd.append('code', code);
                fd.append('year', year);
                fd.append('status', 'Tervalidasi');

                fetch(`${cleanSite}/api/update_status`, { method: 'POST', body: fd })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'success') {
                            location.reload();
                        } else {
                            self.disabled = false;
                            self.style.opacity = '1';
                            alert(res.message || 'Gagal memvalidasi data.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        self.disabled = false;
                        self.style.opacity = '1';
                    });
            });
        });
    }

    // 9. ADMIN DATABASE PAGE
    function initAdminDatabasePage() {
        const cleanSite = (typeof SITE_URL !== 'undefined' ? SITE_URL : (typeof BASE_URL !== 'undefined' ? BASE_URL : '')).replace(/\/+$/, '');
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
            window.location.href = `${cleanSite}/database?q=${q}&status=${st}&sort=${srt}`;
        }

        if (searchInput) {
            searchInput.addEventListener('keydown', e => {
                if (e.key === 'Enter') reloadFiltered();
            });
        }
        if (filterStatus) filterStatus.addEventListener('change', reloadFiltered);
        if (sortSelect) sortSelect.addEventListener('change', reloadFiltered);

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

                fetch(`${cleanSite}/database/create`, {
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

        if (formEdit && !formEdit._hasCrudBound) {
            formEdit._hasCrudBound = true;
            formEdit.addEventListener('submit', function (e) {
                e.preventDefault();
                const id = document.getElementById('edit-id').value;
                if (!id) return;

                const fd = new FormData(this);
                if (editErrBox) editErrBox.style.display = 'none';

                fetch(`${cleanSite}/database/update/${id}`, {
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

        // Delegated Click Handler for Database Records (Edit, Delete, Restore, Hard Delete)
        if (!window._registeredDbDelegates) {
            window._registeredDbDelegates = true;
            document.addEventListener('click', function (e) {
                // Edit
                const editBtn = e.target.closest('.btn-edit-record');
                if (editBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const id = editBtn.getAttribute('data-id');
                    if (!id) return;
                    if (editErrBox) { editErrBox.style.display = 'none'; editErrBox.textContent = ''; }
                    fetch(`${cleanSite}/database/get_record/${id}`, {
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
                                
                                const clusterVal = (d.cluster || d.klaster || 'Output & Pertumbuhan').trim();
                                const editClusterEl = document.getElementById('edit-cluster');
                                if (editClusterEl) {
                                    if (editClusterEl.tagName === 'SELECT') {
                                        let found = false;
                                        for (let opt of editClusterEl.options) {
                                            if (opt.value.trim().toLowerCase() === clusterVal.toLowerCase() || opt.text.trim().toLowerCase() === clusterVal.toLowerCase()) {
                                                editClusterEl.value = opt.value;
                                                found = true;
                                                break;
                                            }
                                        }
                                        if (!found && clusterVal) {
                                            const newOpt = document.createElement('option');
                                            newOpt.value = clusterVal;
                                            newOpt.textContent = clusterVal;
                                            newOpt.selected = true;
                                            editClusterEl.appendChild(newOpt);
                                            editClusterEl.value = clusterVal;
                                        }
                                    } else {
                                        editClusterEl.value = clusterVal;
                                    }
                                }

                                document.getElementById('edit-region').value = d.region || d.wilayah || 'Kabupaten Banyuwangi';
                                document.getElementById('edit-period').value = d.period || d.periode || 'Tahunan';
                                document.getElementById('edit-year').value = d.year || d.tahun || 2024;
                                document.getElementById('edit-value').value = d.value !== undefined ? d.value : d.nilai;
                                document.getElementById('edit-unit').value = d.unit || d.satuan || '%';
                                document.getElementById('edit-source').value = d.source || d.sumber_data || '';
                                const noteVal = d.notes || d.catatan || '';
                                const st = d.status || d.status_validasi || 'Perlu review';
                                const wasRejected = (st === 'Belum lengkap' || st === 'Ditolak');

                                const editStatusEl = document.getElementById('edit-status');
                                if (editStatusEl) editStatusEl.value = wasRejected ? 'Perlu review' : st;
                                const editNotesEl = document.getElementById('edit-notes');
                                if (editNotesEl) editNotesEl.value = '';

                                const rejectCallout = document.getElementById('edit-rejection-callout');
                                const rejectCalloutText = document.getElementById('edit-rejection-reason-text');
                                if (rejectCallout && rejectCalloutText) {
                                    if (noteVal && wasRejected) {
                                        rejectCallout.style.display = 'block';
                                        rejectCalloutText.textContent = noteVal;
                                    } else {
                                        rejectCallout.style.display = 'none';
                                    }
                                }

                                if (editModal) editModal.style.display = 'grid';
                            } else {
                                alert(res.message || 'Gagal memuat rekaman data.');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            alert('Gagal mengambil data indikator untuk diedit.');
                        });
                    return;
                }

                // Soft Delete
                const delBtn = e.target.closest('.btn-delete-record');
                if (delBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const id = delBtn.getAttribute('data-id');
                    const indicator = delBtn.getAttribute('data-indicator') || 'data ini';
                    if (!id) return;
                    if (confirm(`Pindahkan indikator "${indicator}" ke tempat sampah (Soft Delete)?\n\nData tidak akan dihapus permanen dan dapat dipulihkan sewaktu-waktu dari filter Tempat Sampah.`)) {
                        fetch(`${cleanSite}/database/soft_delete/${id}`, {
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
                                window.location.href = `${cleanSite}/database/soft_delete/${id}`;
                            });
                    }
                    return;
                }

                // Restore
                const resBtn = e.target.closest('.btn-restore-record');
                if (resBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const id = resBtn.getAttribute('data-id');
                    const indicator = resBtn.getAttribute('data-indicator') || 'data ini';
                    if (!id) return;
                    if (confirm(`Pulihkan indikator "${indicator}" kembali ke daftar aktif?`)) {
                        fetch(`${cleanSite}/database/restore/${id}`, {
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
                                window.location.href = `${cleanSite}/database/restore/${id}`;
                            });
                    }
                    return;
                }

                // Hard Delete
                const hardBtn = e.target.closest('.btn-hard-delete-record');
                if (hardBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const id = hardBtn.getAttribute('data-id');
                    const indicator = hardBtn.getAttribute('data-indicator') || 'data ini';
                    if (!id) return;
                    if (confirm(`PERINGATAN: Apakah Anda yakin ingin MENGHAPUS PERMANEN "${indicator}"?\n\nData yang dihapus permanen akan hilang selamanya dari database dan TIDAK DAPAT DIPULIHKAN LAGI!`)) {
                        fetch(`${cleanSite}/database/hard_delete/${id}`, {
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
                                window.location.href = `${cleanSite}/database/hard_delete/${id}`;
                            });
                    }
                    return;
                }
            });
        }

        if (btnExportCsv) {
            btnExportCsv.addEventListener('click', () => {
                downloadCSV('database-ekonomi-banyuwangi.csv', [
                    ['Kode', 'Indikator', 'Klaster', 'Wilayah', 'Periode', 'Tahun', 'Nilai', 'Satuan', 'Sumber', 'Status'],
                    ...AppState.records.map(r => [r.code, r.indicator, r.cluster, r.region, r.period, r.year, r.value, r.unit, r.source, r.status])
                ]);
            });
        }
    }

    // 10. ADMIN CSV IMPORT PAGE
    function initAdminInputPage() {
        const cleanSite = (typeof SITE_URL !== 'undefined' ? SITE_URL : (typeof BASE_URL !== 'undefined' ? BASE_URL : '')).replace(/\/+$/, '');
        const fileInput = document.getElementById('csv-file-input');
        const filenameLabel = document.getElementById('csv-filename-label');
        const previewContainer = document.getElementById('csv-preview-container');
        const previewTbody = document.getElementById('csv-preview-tbody');
        const countLabel = document.getElementById('csv-preview-count-label');
        const btnCommit = document.getElementById('btn-commit-import');
        const feedbackBox = document.getElementById('csv-feedback-box');
        const btnDownloadTemplate = document.getElementById('btn-download-template-csv');

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

                fetch(`${cleanSite}/api/import_csv`, {
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
    }

    // 11. ADMIN VALIDATION PAGE
    function initAdminValidationPage() {
        const cleanSite = (typeof SITE_URL !== 'undefined' ? SITE_URL : (typeof BASE_URL !== 'undefined' ? BASE_URL : '')).replace(/\/+$/, '');

        // Search, Filter & Sort
        const searchInput = document.getElementById('validation-search-input');
        const filterStatus = document.getElementById('validation-filter-status');
        const sortSelect = document.getElementById('validation-sort');

        function reloadValidationFiltered() {
            const q = searchInput ? encodeURIComponent(searchInput.value) : '';
            const st = filterStatus ? encodeURIComponent(filterStatus.value) : '';
            const srt = sortSelect ? encodeURIComponent(sortSelect.value) : 'status';
            window.location.href = `${cleanSite}/validation?q=${q}&status=${st}&sort=${srt}`;
        }

        if (searchInput) {
            searchInput.addEventListener('keydown', e => {
                if (e.key === 'Enter') reloadValidationFiltered();
            });
        }
        if (filterStatus) filterStatus.addEventListener('change', reloadValidationFiltered);
        if (sortSelect) sortSelect.addEventListener('change', reloadValidationFiltered);

        // 1. Approve (Centang / ctg) - Langsung setujui tanpa popup alert / confirm
        document.querySelectorAll('.btn-verify-record').forEach(btn => {
            btn.addEventListener('click', function () {
                const code = this.getAttribute('data-code');
                const year = this.getAttribute('data-year');
                const status = this.getAttribute('data-status') || 'Tervalidasi';

                const self = this;
                self.disabled = true;
                self.style.opacity = '0.6';

                const fd = new FormData();
                fd.append('code', code);
                fd.append('year', year);
                fd.append('status', status);

                fetch(`${cleanSite}/api/update_status`, { method: 'POST', body: fd })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'success') {
                            location.reload();
                        } else {
                            self.disabled = false;
                            self.style.opacity = '1';
                            alert(res.message || 'Gagal menyetujui validasi data.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        self.disabled = false;
                        self.style.opacity = '1';
                    });
            });
        });

        // 2. Reject (Silang / X) Modal & Form Handling
        const rejectModal = document.getElementById('modal-reject-record');
        const rejectForm = document.getElementById('form-reject-record');
        const btnCloseReject = document.getElementById('btn-close-reject-modal');
        const btnCancelReject = document.getElementById('btn-cancel-reject');
        const rejectReasonInput = document.getElementById('reject-reason');
        const rejectReasonError = document.getElementById('reject-reason-error');

        function closeRejectModal() {
            if (rejectModal) {
                rejectModal.style.display = 'none';
            }
            if (rejectForm) {
                rejectForm.reset();
            }
            if (rejectReasonError) {
                rejectReasonError.style.display = 'none';
            }
        }

        if (btnCloseReject) btnCloseReject.addEventListener('click', closeRejectModal);
        if (btnCancelReject) btnCancelReject.addEventListener('click', closeRejectModal);
        if (rejectModal) {
            rejectModal.addEventListener('click', function (e) {
                if (e.target === rejectModal) closeRejectModal();
            });
        }

        document.querySelectorAll('.btn-reject-record').forEach(btn => {
            btn.addEventListener('click', function () {
                const code = this.getAttribute('data-code') || '';
                const year = this.getAttribute('data-year') || '';
                const indicator = this.getAttribute('data-indicator') || '';
                const val = this.getAttribute('data-val') || '';
                const period = this.getAttribute('data-period') || '';
                const existingNotes = this.getAttribute('data-notes') || '';

                const codeInput = document.getElementById('reject-code');
                const yearInput = document.getElementById('reject-year');
                const dispCode = document.getElementById('reject-display-code');
                const dispInd = document.getElementById('reject-display-indicator');
                const dispPer = document.getElementById('reject-display-period');
                const dispVal = document.getElementById('reject-display-value');

                if (codeInput) codeInput.value = code;
                if (yearInput) yearInput.value = year;
                if (dispCode) dispCode.textContent = code;
                if (dispInd) dispInd.textContent = indicator;
                if (dispPer) dispPer.textContent = period;
                if (dispVal) dispVal.textContent = val;

                if (rejectReasonInput) rejectReasonInput.value = existingNotes;
                if (rejectReasonError) rejectReasonError.style.display = 'none';

                if (rejectModal) {
                    rejectModal.style.display = 'grid';
                    setTimeout(() => {
                        if (rejectReasonInput) rejectReasonInput.focus();
                    }, 50);
                }
            });
        });

        if (rejectForm) {
            rejectForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const reason = (rejectReasonInput ? rejectReasonInput.value : '').trim();
                if (!reason) {
                    if (rejectReasonError) rejectReasonError.style.display = 'block';
                    if (rejectReasonInput) rejectReasonInput.focus();
                    return;
                }

                const submitBtn = document.getElementById('btn-submit-reject');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span>Menyimpan...</span>';
                }

                const code = document.getElementById('reject-code').value;
                const year = document.getElementById('reject-year').value;

                const fd = new FormData();
                fd.append('code', code);
                fd.append('year', year);
                fd.append('status', 'Belum lengkap');
                fd.append('notes', reason);

                fetch(`${cleanSite}/api/update_status`, { method: 'POST', body: fd })
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'success') {
                            location.reload();
                        } else {
                            alert(res.message || 'Gagal menyimpan penolakan data.');
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/></svg><span>Simpan Penolakan</span>';
                            }
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Terjadi kesalahan jaringan.');
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m15 9-6 6M9 9l6 6"/></svg><span>Simpan Penolakan</span>';
                        }
                    });
            });
        }
    }

    // 12. ADMIN EWS PAGE
    function initAdminEwsPage() {
        const cleanSite = (typeof SITE_URL !== 'undefined' ? SITE_URL : (typeof BASE_URL !== 'undefined' ? BASE_URL : '')).replace(/\/+$/, '');

        // Auto-scroll active year tab into view on load
        const slideTrack = document.getElementById('year-slide-track');
        if (slideTrack) {
            const activeTab = slideTrack.querySelector('.year-slide-tab.active');
            if (activeTab) {
                setTimeout(() => {
                    activeTab.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }, 100);
            }
        }

        // Live calculation of Status Risiko based on real value vs threshold and direction
        function updateRowStatus(row) {
            const code = row.getAttribute('data-code');
            const thInput = row.querySelector('.threshold-input');
            const valInput = row.querySelector('.value-input');
            const dirSelect = row.querySelector('.direction-input');
            const badge = document.getElementById(`status-badge-${code}`);

            if (!thInput || !valInput || !dirSelect || !badge) return;

            const val = parseFloat(valInput.value);
            const th = parseFloat(thInput.value);
            const dir = dirSelect.value || 'above';

            if (!Number.isFinite(th)) return;

            let st = 'Normal';
            if (val === th) {
                st = 'Waspada';
            } else if (dir === 'below') {
                st = (val < th) ? 'Kritis' : 'Normal';
            } else {
                st = (val > th) ? 'Kritis' : 'Normal';
            }

            const badgeCls = (st === 'Kritis') ? 'badge danger' : ((st === 'Waspada') ? 'badge warning' : 'badge success');
            badge.className = badgeCls;
            badge.textContent = st;
        }

        document.querySelectorAll('.threshold-admin-row').forEach(row => {
            const thInput = row.querySelector('.threshold-input');
            const dirSelect = row.querySelector('.direction-input');

            if (thInput) {
                thInput.addEventListener('input', () => updateRowStatus(row));
            }
            if (dirSelect) {
                dirSelect.addEventListener('change', () => updateRowStatus(row));
            }
        });

        const btnSaveAll = document.getElementById('btn-save-all-thresholds');
        const feedback = document.getElementById('ews-admin-feedback');

        if (btnSaveAll) {
            btnSaveAll.addEventListener('click', () => {
                const inputs = document.querySelectorAll('.threshold-input');
                if (!inputs.length) return;

                let count = 0;
                let errorCount = 0;
                btnSaveAll.disabled = true;
                const prevHtml = btnSaveAll.innerHTML;
                btnSaveAll.textContent = 'Menyimpan...';

                inputs.forEach(input => {
                    const code = input.getAttribute('data-code');
                    const th = parseFloat(input.value);
                    const valEl = document.querySelector(`.value-input[data-code="${code}"]`);
                    const val = valEl ? parseFloat(valEl.value) : null;
                    const dirEl = document.querySelector(`.direction-input[data-code="${code}"]`);
                    const dir = dirEl ? dirEl.value : 'above';

                    const fd = new FormData();
                    fd.append('code', code);
                    fd.append('threshold', th);
                    fd.append('direction', dir);
                    if (val !== null && Number.isFinite(val)) {
                        fd.append('value', val);
                    }

                    fetch(cleanSite + '/api/update_threshold', { method: 'POST', body: fd })
                        .then(res => res.json())
                        .then(data => {
                            count++;
                            if (data.status === 'success' && data.status_risiko) {
                                const badge = document.getElementById(`status-badge-${code}`);
                                if (badge) {
                                    const st = data.status_risiko;
                                    badge.className = (st === 'Kritis') ? 'badge danger' : ((st === 'Waspada') ? 'badge warning' : 'badge success');
                                    badge.textContent = st;
                                }
                            }
                            if (count === inputs.length) {
                                btnSaveAll.disabled = false;
                                btnSaveAll.innerHTML = prevHtml;
                                if (feedback) {
                                    feedback.style.display = 'block';
                                    feedback.className = 'method-notice forecast';
                                    feedback.innerHTML = `<strong>Berhasil!</strong><p>Konfigurasi ambang batas untuk ${count} indikator berhasil disimpan dan status risiko telah diperbarui.</p>`;
                                    setTimeout(() => {
                                        feedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                                    }, 100);
                                }
                            }
                        })
                        .catch(() => {
                            count++;
                            errorCount++;
                            if (count === inputs.length) {
                                btnSaveAll.disabled = false;
                                btnSaveAll.innerHTML = prevHtml;
                                if (feedback) {
                                    feedback.style.display = 'block';
                                    feedback.className = 'method-notice danger';
                                    feedback.innerHTML = `<strong>Selesai dengan catatan!</strong><p>${count - errorCount} indikator tersimpan, ${errorCount} gagal.</p>`;
                                }
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
            const totUser = (levelMap['1'] + levelMap['2'] + levelMap['3'] + levelMap['4']) || 1;
            const levelData = [
                { label: 'Level 1 - Administrator', value: levelMap['1'], subLabel: 'Otoritas tertinggi & konfigurasi penuh sistem', badge: `${Math.round((levelMap['1'] / totUser) * 100)}%`, badgeClass: 'primary', color: 'linear-gradient(90deg, #0284c7, #38bdf8)' },
                { label: 'Level 2 - Analis Data', value: levelMap['2'], subLabel: 'Kajian ekonomi, validasi data & generator laporan', badge: `${Math.round((levelMap['2'] / totUser) * 100)}%`, badgeClass: 'info', color: 'linear-gradient(90deg, #06b6d4, #67e8f9)' },
                { label: 'Level 3 - Operator OPD', value: levelMap['3'], subLabel: 'Entri indikator sektoral & master dataset', badge: `${Math.round((levelMap['3'] / totUser) * 100)}%`, badgeClass: 'success', color: 'linear-gradient(90deg, #10b981, #6ee7b7)' },
                { label: 'Level 4 - Reviewer / Eksekutif', value: levelMap['4'], subLabel: 'Monitoring dashboard publik & evaluasi capaian', badge: `${Math.round((levelMap['4'] / totUser) * 100)}%`, badgeClass: 'warning', color: 'linear-gradient(90deg, #f59e0b, #fcd34d)' }
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
            editBtnClass: 'btn-edit-user',
            deleteBtnClass: 'btn-delete-user',
            restoreBtnClass: 'btn-restore-user',
            hardDeleteBtnClass: 'btn-hard-delete-user',
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
        // Auto-scroll active year tab into view on load
        const slideTrack = document.getElementById('year-slide-track');
        if (slideTrack) {
            const activeTab = slideTrack.querySelector('.year-slide-tab.active');
            if (activeTab) {
                setTimeout(() => {
                    activeTab.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }, 100);
            }
        }

        const btnExportCsv = document.getElementById('btn-export-catalog-csv');
        if (btnExportCsv) {
            btnExportCsv.addEventListener('click', function () {
                const rows = [];
                const table = document.querySelector('.result-table');
                if (table) {
                    const headers = [];
                    table.querySelectorAll('thead th').forEach((th, idx) => {
                        // Skip last column (Aksi)
                        if (idx < table.querySelectorAll('thead th').length - 1) {
                            headers.push(th.textContent.trim());
                        }
                    });
                    rows.push(headers);
                    table.querySelectorAll('tbody tr').forEach(tr => {
                        const rowData = [];
                        const tds = tr.querySelectorAll('td');
                        if (tds.length > 1) {
                            tds.forEach((td, idx) => {
                                if (idx < tds.length - 1) {
                                    rowData.push(td.innerText.replace(/\s+/g, ' ').trim());
                                }
                            });
                            rows.push(rowData);
                        }
                    });
                }
                const yr = new URLSearchParams(window.location.search).get('year') || 'semua';
                downloadCSV(`katalog-indikator-banyuwangi-${yr}.csv`, rows);
            });
        }

        // Render Grafik Distribusi Klaster Indikator
        const catChart = document.getElementById('catalog-cluster-chart');
        if (catChart) {
            const clusterMap = {};
            document.querySelectorAll('.data-card table tbody tr').forEach(tr => {
                const cl = tr.getAttribute('data-cluster') || (tr.querySelectorAll('td')[2] ? tr.querySelectorAll('td')[2].textContent.trim() : '');
                if (cl && cl !== 'Tidak ada indikator katalog ditemukan.') {
                    clusterMap[cl] = (clusterMap[cl] || 0) + 1;
                }
            });
            const totalInd = Object.values(clusterMap).reduce((a, b) => a + b, 0) || 1;
            const clusterItems = Object.keys(clusterMap).map(k => {
                const count = clusterMap[k];
                const pct = Math.round((count / totalInd) * 100);
                return {
                    label: k,
                    value: count,
                    subLabel: `${pct}% dari total indikator`
                };
            }).sort((a, b) => b.value - a.value);

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
            editBtnClass: 'btn-edit-catalog',
            deleteBtnClass: 'btn-delete-catalog',
            restoreBtnClass: 'btn-restore-catalog',
            hardDeleteBtnClass: 'btn-hard-delete-catalog',
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

