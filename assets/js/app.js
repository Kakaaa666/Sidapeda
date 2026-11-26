/**
 * SI-DAPEDA â€” Sistem Informasi Data & Analisis Pembangunan Daerah
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
        if (!Number.isFinite(val)) return 'â€”';
        return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: digits,
            maximumFractionDigits: digits
        }).format(val);
    }

    function formatPct(val, digits = 2) {
        return formatNum(val, digits) + '%';
    }

    // Helper: Pemda Logo URL Resolver
    function getLogoPemdaUrl(query) {
        const base = (typeof BASE_URL !== 'undefined' ? BASE_URL : '').replace(/\/+$/, '');
        if (!query) return base + '/assets/img/Logo%20Pemda/Kab.%20Banyuwangi.png';
        const q = String(query).trim();
        
        const codeMap = {
            '35.01': 'Kab. Pacitan.png',
            '35.02': 'Kab. Ponorogo.png',
            '35.03': 'Kab. Trenggalek.png',
            '35.04': 'Kab. Tulungagung.png',
            '35.05': 'Kab. Blitar.png',
            '35.06': 'Kab. Kediri.png',
            '35.07': 'Kab. Malang.png',
            '35.08': 'Kab. Lumajang.png',
            '35.09': 'Kab. Jember.png',
            '35.10': 'Kab. Banyuwangi.png',
            '35.11': 'Kab. Bondowoso.png',
            '35.12': 'Kab. Situbondo.png',
            '35.13': 'Kab. Probolinggo.png',
            '35.14': 'Kab. Pasuruan.png',
            '35.15': 'Kab. Sidoarjo.png',
            '35.16': 'Kab. Mojokerto.png',
            '35.17': 'Kab. Jombang.png',
            '35.18': 'Kab. Nganjuk.png',
            '35.19': 'Kab. Madiun.png',
            '35.20': 'Kab. Magetan.png',
            '35.21': 'Kab. Ngawi.png',
            '35.22': 'Kab. Bojonegoro.png',
            '35.23': 'Kab. Tuban.png',
            '35.24': 'Kab. Lamongan.png',
            '35.25': 'Kab. Gresik.png',
            '35.26': 'Kab. Bangkalan.png',
            '35.27': 'Kab. Sampang.png',
            '35.28': 'Kab. Pamekasan.png',
            '35.29': 'Kab. Sumenep.png',
            '35.71': 'Kota Kediri.png',
            '35.72': 'Kota Blitar.png',
            '35.73': 'Kota Malang.png',
            '35.74': 'Kota Probolinggo.png',
            '35.75': 'Kota Pasuruan.png',
            '35.76': 'Kota Mojokerto.png',
            '35.77': 'Kota Madiun.png',
            '35.78': 'Kota Surabaya.png',
            '35.79': 'Kota Batu.png',
            '35': 'Jawa Timur.png',
            '35.00': 'Jawa Timur.png'
        };
        if (codeMap[q]) {
            return base + '/assets/img/Logo%20Pemda/' + encodeURIComponent(codeMap[q]);
        }

        if (/jawa\s*timur|jatim|provinsi/i.test(q)) {
            return base + '/assets/img/Logo%20Pemda/Jawa%20Timur.png';
        }

        const qClean = q.replace(/^(pemerintah\s+kabupaten|pemerintah\s+kota|pemerintah\s+kab\.|kabupaten|kab\.|kota)\s+/i, '').trim().toLowerCase();

        if (/kota/i.test(q) || ['batu', 'surabaya'].includes(qClean)) {
            const kotaMap = {
                'kediri': 'Kota Kediri.png',
                'blitar': 'Kota Blitar.png',
                'malang': 'Kota Malang.png',
                'probolinggo': 'Kota Probolinggo.png',
                'pasuruan': 'Kota Pasuruan.png',
                'mojokerto': 'Kota Mojokerto.png',
                'madiun': 'Kota Madiun.png',
                'surabaya': 'Kota Surabaya.png',
                'batu': 'Kota Batu.png'
            };
            for (const [k, f] of Object.entries(kotaMap)) {
                if (qClean.includes(k)) return base + '/assets/img/Logo%20Pemda/' + encodeURIComponent(f);
            }
        }

        const kabMap = {
            'pacitan': 'Kab. Pacitan.png',
            'ponorogo': 'Kab. Ponorogo.png',
            'trenggalek': 'Kab. Trenggalek.png',
            'tulungagung': 'Kab. Tulungagung.png',
            'blitar': 'Kab. Blitar.png',
            'kediri': 'Kab. Kediri.png',
            'malang': 'Kab. Malang.png',
            'lumajang': 'Kab. Lumajang.png',
            'jember': 'Kab. Jember.png',
            'banyuwangi': 'Kab. Banyuwangi.png',
            'bondowoso': 'Kab. Bondowoso.png',
            'situbondo': 'Kab. Situbondo.png',
            'probolinggo': 'Kab. Probolinggo.png',
            'pasuruan': 'Kab. Pasuruan.png',
            'sidoarjo': 'Kab. Sidoarjo.png',
            'mojokerto': 'Kab. Mojokerto.png',
            'jombang': 'Kab. Jombang.png',
            'nganjuk': 'Kab. Nganjuk.png',
            'madiun': 'Kab. Madiun.png',
            'magetan': 'Kab. Magetan.png',
            'ngawi': 'Kab. Ngawi.png',
            'bojonegoro': 'Kab. Bojonegoro.png',
            'tuban': 'Kab. Tuban.png',
            'lamongan': 'Kab. Lamongan.png',
            'gresik': 'Kab. Gresik.png',
            'bangkalan': 'Kab. Bangkalan.png',
            'sampang': 'Kab. Sampang.png',
            'pamekasan': 'Kab. Pamekasan.png',
            'sumenep': 'Kab. Sumenep.png'
        };
        for (const [k, f] of Object.entries(kabMap)) {
            if (qClean.includes(k)) return base + '/assets/img/Logo%20Pemda/' + encodeURIComponent(f);
        }

        return base + '/assets/img/Logo%20Pemda/Kab.%20Banyuwangi.png';
    }
    window.getLogoPemdaUrl = getLogoPemdaUrl;

    // =========================================================================
    // GLOBAL SWEETALERT2 EXECUTIVE HELPER (SIDAPEDA)
    // =========================================================================
    const AppSwal = {
        fire: function (options) {
            if (typeof Swal !== 'undefined') {
                return Swal.fire(options);
            } else {
                return new Promise(resolve => {
                    const ok = confirm((options.title || '') + (options.html ? '\n\n' + options.html.replace(/<[^>]+>/g, '') : (options.text ? '\n\n' + options.text : '')));
                    resolve({ isConfirmed: ok, isDismissed: !ok });
                });
            }
        },
        confirm: function (options) {
            const opts = Object.assign({
                title: 'Apakah Anda yakin?',
                html: '',
                icon: 'warning',
                showCancelButton: true,
                showDenyButton: false,
                showCloseButton: false,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Tidak',
                reverseButtons: false,
                focusCancel: false
            }, options);
            return this.fire(opts);
        },
        dangerConfirm: function (options) {
            const opts = Object.assign({
                title: 'Konfirmasi Tindakan Kritis',
                html: '',
                icon: 'error',
                showCancelButton: true,
                showDenyButton: false,
                showCloseButton: false,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Tidak',
                reverseButtons: false,
                focusCancel: false
            }, options);
            return this.fire(opts);
        },
        success: function (title, html) {
            return this.fire({
                title: title || 'Berhasil',
                html: html || '',
                icon: 'success',
                confirmButtonColor: '#0d9488',
                confirmButtonText: 'Selesai'
            });
        },
        error: function (title, html) {
            return this.fire({
                title: title || 'Terjadi Kesalahan',
                html: html || '',
                icon: 'error',
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Tutup'
            });
        },
        info: function (title, html) {
            return this.fire({
                title: title || 'Informasi',
                html: html || '',
                icon: 'info',
                confirmButtonColor: '#2463a6',
                confirmButtonText: 'Mengerti'
            });
        }
    };
    window.AppSwal = AppSwal;

    // Global Logout Confirmation via SweetAlert2
    window.confirmLogout = function (event, targetUrl) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        const destination = targetUrl || (event && event.currentTarget ? event.currentTarget.href : `${(typeof SITE_URL !== 'undefined' ? SITE_URL : '').replace(/\/+$/, '')}/logout`);

        AppSwal.confirm({
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
        return false;
    };

    // Override native window.alert with modern SweetAlert2
    const _nativeAlert = window.alert;
    window.alert = function (msg) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Pemberitahuan Sistem',
                html: String(msg || ''),
                icon: 'info',
                confirmButtonColor: '#2463a6',
                confirmButtonText: 'Tutup'
            });
        } else if (_nativeAlert) {
            _nativeAlert(msg);
        }
    };

    // Global Helper: Toggle Sub-Indikator Sektoral / Komponen (A s.d. U / 1 s.d. 6)
    let _lastIndicatorToggle = { code: '', time: 0 };
    window.toggleIndicatorSubRows = function (parentCode, event) {
        if (event) {
            if (event.preventDefault) event.preventDefault();
            if (event.stopPropagation) event.stopPropagation();
            if (event.stopImmediatePropagation) event.stopImmediatePropagation();
        }
        if (!parentCode) return;

        // Debounce lock: cegah duplicate toggle dalam rentang 200ms
        const now = Date.now();
        if (_lastIndicatorToggle.code === parentCode && (now - _lastIndicatorToggle.time) < 200) {
            return;
        }
        _lastIndicatorToggle = { code: parentCode, time: now };

        const subRows = document.querySelectorAll(`tr.matrix-sub-row[data-parent="${parentCode}"], tr.sub-row-${parentCode}, div.risk-sub-row[data-parent="${parentCode}"], div.sub-row-${parentCode}`);
        if (!subRows || subRows.length === 0) return;

        const parentRows = document.querySelectorAll(`tr.matrix-row[data-code="${parentCode}"], div.risk-row[data-code="${parentCode}"]:not(.risk-sub-row)`);

        let isCurrentlyExpanded = false;
        if (parentRows.length > 0 && parentRows[0].classList.contains('is-expanded')) {
            isCurrentlyExpanded = true;
        } else if (subRows.length > 0 && subRows[0].classList.contains('is-open')) {
            isCurrentlyExpanded = true;
        } else {
            const firstDisplay = subRows[0].style.display;
            if (firstDisplay === 'table-row' || firstDisplay === 'grid') {
                isCurrentlyExpanded = true;
            }
        }

        const willExpand = !isCurrentlyExpanded;

        subRows.forEach(sr => {
            if (willExpand) {
                sr.classList.add('is-open');
                const isTr = (sr.tagName.toLowerCase() === 'tr');
                sr.style.setProperty('display', isTr ? 'table-row' : 'grid', 'important');
            } else {
                sr.classList.remove('is-open');
                sr.style.setProperty('display', 'none', 'important');
            }
        });

        parentRows.forEach(pr => {
            if (willExpand) {
                pr.classList.add('is-expanded');
            } else {
                pr.classList.remove('is-expanded');
            }

            const arrows = pr.querySelectorAll('.sub-arrow-icon, .toggle-chevron-icon');
            arrows.forEach(arr => {
                arr.style.transform = willExpand ? 'rotate(180deg)' : 'rotate(0deg)';
            });

            const btn = pr.querySelector('.btn-toggle-subcategories');
            const lbl = pr.querySelector('.sub-toggle-label');
            if (btn && lbl) {
                if (willExpand) {
                    lbl.textContent = 'Tutup Rincian';
                    btn.style.background = '#dcfce7';
                    btn.style.borderColor = '#16a34a';
                    btn.style.color = '#14532d';
                } else {
                    const openText = btn.getAttribute('data-label-open') || 'Rincian Sektor (A\u2013U)';
                    lbl.textContent = openText;
                    btn.style.background = '#f0fdf4';
                    btn.style.borderColor = '#86efac';
                    btn.style.color = '#15803d';
                }
            }
        });
    };

    // Global Capture-Phase Delegated Click Listener for .btn-toggle-subcategories
    document.addEventListener('click', function (e) {
        const toggleBtn = e.target.closest('.btn-toggle-subcategories');
        if (toggleBtn) {
            e.preventDefault();
            e.stopPropagation();
            if (e.stopImmediatePropagation) e.stopImmediatePropagation();
            const pCode = toggleBtn.getAttribute('data-parent') || (toggleBtn.closest('[data-code]') ? toggleBtn.closest('[data-code]').getAttribute('data-code') : '');
            if (pCode && typeof window.toggleIndicatorSubRows === 'function') {
                window.toggleIndicatorSubRows(pCode, e);
            }
        }
    }, true);

    // Global Helper: Slide Filter Kategori Indikator (EKO, NAK, TK, INV, HRG, KES, PAR, PRO, FIS, GEO, EWS, ANA)
    window._activeCategoryFilter = '';
    window.applyCategoryFilter = function (targetPrefix, triggerEl) {
        let cleanPrefix = (targetPrefix || '').trim().toUpperCase();
        // Selalu wajib memilih salah 1 kategori (tanpa opsi "Semua")
        if (!cleanPrefix || cleanPrefix === 'ALL' || cleanPrefix === 'SEMUA') {
            const activeTab = document.querySelector('.category-slide-tab.active');
            if (activeTab && activeTab.getAttribute('data-prefix')) {
                cleanPrefix = activeTab.getAttribute('data-prefix').trim().toUpperCase();
            } else {
                const firstTab = document.querySelector('.category-slide-tab');
                if (firstTab && firstTab.getAttribute('data-prefix')) {
                    cleanPrefix = firstTab.getAttribute('data-prefix').trim().toUpperCase();
                } else {
                    cleanPrefix = 'EKO';
                }
            }
        }
        window._activeCategoryFilter = cleanPrefix;

        // 1. Update UI tabs on category slide track
        const tracks = document.querySelectorAll('.category-slide-track');
        tracks.forEach(track => {
            track.querySelectorAll('.category-slide-tab').forEach(tab => {
                const tabPrefix = (tab.getAttribute('data-prefix') || '').trim().toUpperCase();
                const isActive = (tabPrefix === cleanPrefix);
                tab.classList.toggle('active', isActive);
                if (isActive) {
                    try {
                        tab.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                    } catch (e) { }
                }
            });
        });

        // 2. Filter rows in database table, validation table, or threshold admin table
        const tableBodies = [
            document.getElementById('admin-table-body'),
            document.getElementById('validation-table-body'),
            document.getElementById('threshold-admin-rows')
        ].filter(Boolean);

        tableBodies.forEach(tbody => {
            let visibleCount = 0;
            const masterRows = tbody.querySelectorAll('tr.matrix-row:not(.matrix-sub-row)');
            masterRows.forEach(row => {
                const rowPrefix = (row.getAttribute('data-prefix') || (row.getAttribute('data-code') || '').split('-')[0]).trim().toUpperCase();
                const isMatch = (rowPrefix === cleanPrefix);

                if (isMatch) {
                    row.style.setProperty('display', 'table-row', 'important');
                    visibleCount++;
                    // If row was expanded before, keep its sub-rows visible, otherwise keep them hidden
                    const isExpanded = row.classList.contains('is-expanded');
                    const pCode = row.getAttribute('data-code');
                    if (pCode) {
                        tbody.querySelectorAll(`tr.matrix-sub-row[data-parent="${pCode}"], tr.sub-row-${pCode}`).forEach(sr => {
                            if (isExpanded) {
                                sr.classList.add('is-open');
                                sr.style.setProperty('display', 'table-row', 'important');
                            } else {
                                sr.classList.remove('is-open');
                                sr.style.setProperty('display', 'none', 'important');
                            }
                        });
                    }
                } else {
                    row.style.setProperty('display', 'none', 'important');
                    // Hide sub-rows of non-matching categories
                    const pCode = row.getAttribute('data-code');
                    if (pCode) {
                        tbody.querySelectorAll(`tr.matrix-sub-row[data-parent="${pCode}"], tr.sub-row-${pCode}`).forEach(sr => {
                            sr.classList.remove('is-open');
                            sr.style.setProperty('display', 'none', 'important');
                        });
                    }
                }
            });

            // Handle empty state row for category with zero results
            let emptyRow = tbody.querySelector('.tr-category-empty');
            if (visibleCount === 0) {
                if (!emptyRow) {
                    emptyRow = document.createElement('tr');
                    emptyRow.className = 'tr-category-empty';
                    const colCount = tbody.closest('table') ? tbody.closest('table').querySelectorAll('thead th').length : 15;
                    emptyRow.innerHTML = `<td colspan="${colCount}" style="padding:36px 20px; text-align:center; color:var(--muted);"><div style="display:flex; flex-direction:column; align-items:center; gap:8px;"><strong>Tidak ada indikator untuk kategori ${cleanPrefix}.</strong></div></td>`;
                    tbody.appendChild(emptyRow);
                } else {
                    emptyRow.style.display = 'table-row';
                    const strong = emptyRow.querySelector('strong');
                    if (strong) strong.textContent = `Tidak ada indikator untuk kategori ${cleanPrefix}.`;
                }
            } else if (emptyRow) {
                emptyRow.style.display = 'none';
            }

            // Update counter info
            const countLabel = document.getElementById('cat-filtered-count-label');
            if (countLabel) {
                if (tbody.id === 'threshold-admin-rows') {
                    countLabel.textContent = `Total: ${visibleCount} Indikator Kategori ${cleanPrefix}`;
                } else {
                    countLabel.textContent = `Menampilkan ${visibleCount} indikator kategori ${cleanPrefix}`;
                }
            }
            if (tbody.id === 'threshold-admin-rows' && typeof window._refreshEwsAdminSummary === 'function') {
                window._refreshEwsAdminSummary();
            }
        });

        // 3. Filter KPI cards in Beranda (#kpi-container)
        const kpiContainer = document.getElementById('kpi-container');
        if (kpiContainer) {
            let kpiVisibleCount = 0;
            const cards = kpiContainer.querySelectorAll('.kpi-card');
            cards.forEach(card => {
                const cardPrefix = (card.getAttribute('data-prefix') || (card.getAttribute('data-code') || '').split('-')[0]).trim().toUpperCase();
                const isMatch = (!cleanPrefix || cardPrefix === cleanPrefix);
                if (isMatch) {
                    card.style.display = '';
                    kpiVisibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            const kpiEmpty = document.getElementById('kpi-empty-message');
            if (kpiEmpty) {
                kpiEmpty.style.display = (kpiVisibleCount === 0) ? 'block' : 'none';
            }
            const kpiCountDisp = document.getElementById('kpi-count-display');
            if (kpiCountDisp) {
                kpiCountDisp.textContent = kpiVisibleCount;
            }
        }

        // 4. Filter risk rows in Early Warning (#ews-table-rows)
        const ewsTable = document.getElementById('ews-table-rows');
        if (ewsTable) {
            let ewsVisibleCount = 0;
            const masterRows = ewsTable.querySelectorAll('.risk-row:not(.risk-sub-row)');
            masterRows.forEach(row => {
                const rowPrefix = (row.getAttribute('data-prefix') || (row.getAttribute('data-code') || '').split('-')[0]).trim().toUpperCase();
                const rowStatus = row.getAttribute('data-status') || '';
                const matchPrefix = (rowPrefix === cleanPrefix);
                const matchStatus = (!window._activeEwsStatusFilter || window._activeEwsStatusFilter === 'Semua' || rowStatus === window._activeEwsStatusFilter);
                const isMatch = matchPrefix && matchStatus;

                const pCode = row.getAttribute('data-code');
                const subRows = pCode ? ewsTable.querySelectorAll(`.risk-sub-row.sub-row-${pCode}`) : [];

                if (isMatch) {
                    row.style.setProperty('display', 'grid', 'important');
                    ewsVisibleCount++;
                    const isExpanded = row.classList.contains('is-expanded');
                    subRows.forEach(sr => {
                        if (isExpanded) {
                            sr.classList.add('is-open');
                            sr.style.setProperty('display', 'grid', 'important');
                        } else {
                            sr.classList.remove('is-open');
                            sr.style.setProperty('display', 'none', 'important');
                        }
                    });
                } else {
                    row.style.setProperty('display', 'none', 'important');
                    subRows.forEach(sr => {
                        sr.classList.remove('is-open');
                        sr.style.setProperty('display', 'none', 'important');
                    });
                }
            });

            const emptyMsg = document.getElementById('ews-empty-message');
            if (emptyMsg) {
                emptyMsg.style.display = (ewsVisibleCount === 0) ? 'block' : 'none';
            }
        }

        // 5. Update URL without refreshing page
        try {
            const url = new URL(window.location.href);
            if (cleanPrefix) {
                url.searchParams.set('category', cleanPrefix);
            } else {
                url.searchParams.delete('category');
                url.searchParams.delete('prefix');
            }
            window.history.replaceState({}, '', url.toString());
        } catch (e) { }

        if (ewsTable && typeof window._refreshEwsPublicVisuals === 'function') {
            window._refreshEwsPublicVisuals();
        }
    };

    // Global listener for slide navigation buttons
    document.addEventListener('click', function (e) {
        const prevBtn = e.target.closest('#btn-slide-cat-prev');
        if (prevBtn) {
            e.preventDefault();
            const container = prevBtn.closest('.category-slide-container');
            const track = container ? container.querySelector('.category-slide-track') : document.getElementById('category-slide-track');
            if (track) track.scrollBy({ left: -220, behavior: 'smooth' });
            return;
        }
        const nextBtn = e.target.closest('#btn-slide-cat-next');
        if (nextBtn) {
            e.preventDefault();
            const container = nextBtn.closest('.category-slide-container');
            const track = container ? container.querySelector('.category-slide-track') : document.getElementById('category-slide-track');
            if (track) track.scrollBy({ left: 220, behavior: 'smooth' });
            return;
        }
    });

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
        const unit = (opts.unit !== undefined) ? opts.unit : '%';
        const showRank = opts.showRank !== false;
        const showLogo = Boolean(opts.showLogo || items.some(d => d.logo));
        const colLabelWidth = opts.labelWidth || (showLogo ? '195px' : '160px');
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

            const valFormatted = item.formattedValue ? String(item.formattedValue) : `${formatNum(v, opts.decimals ?? 2)}${unit}`.trim();
            const hasDistinctBadge = item.badge && item.badge.trim() !== valFormatted && item.badge.trim() !== `${v}${unit}`.trim() && item.badge.trim() !== `${v}`.trim();
            const itemLogoUrl = item.logo || (showLogo && typeof getLogoPemdaUrl === 'function' ? getLogoPemdaUrl(item.label) : null);

            rowsHtml += `
                <div class="h-bar-row ${isHighlight ? 'highlighted' : ''}" data-index="${idx}" data-label="${item.label}" style="display:grid; grid-template-columns: ${showRank ? '28px ' : ''}${colLabelWidth} 1fr 105px; align-items:center; gap:10px; padding:7px 10px; margin:4px 0; font-size:11px; ${borderStyle} cursor:pointer; transition: all 0.2s ease;">
                    ${showRank ? `<span class="badge ${isHighlight ? 'primary' : 'neutral'}" style="font-size:9px; padding:2px 4px; text-align:center; font-weight:700;">#${idx + 1}</span>` : ''}
                    <div style="display:flex; align-items:center; gap:8px; min-width:0; overflow:hidden;" title="${item.label}">
                        ${itemLogoUrl ? `<img src="${itemLogoUrl}" alt="" class="region-table-logo" style="width:20px; height:20px; max-width:20px; max-height:20px; object-fit:contain; flex-shrink:0; margin-right:0; filter:drop-shadow(0 1px 2px rgba(0,0,0,0.12));">` : ''}
                        <div style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis; min-width:0; flex:1;">
                            <strong style="color:var(--navy); font-size:11px;">${item.label}</strong>
                            ${item.subLabel ? `<br><small style="color:var(--muted); font-size:9px;">${item.subLabel}</small>` : ''}
                        </div>
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

        const legend = series.map((s, idx) => {
            const logo = s.logo || (typeof getLogoPemdaUrl === 'function' ? getLogoPemdaUrl(s.name) : null);
            return `<span style="display:inline-flex; align-items:center; gap:5px;"><i class="${s.className || 'series-' + idx}"></i>${logo ? `<img src="${logo}" alt="" style="width:13px; height:13px; object-fit:contain; flex-shrink:0;">` : ''}<span>${s.name}</span></span>`;
        }).join('');

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
                <span><i class="forecast-interval-key"></i>Rentang Indikatif (Â±1,96 RMSE)</span>
            </div>`;
    }

    // Initialize UI on DOM Ready (robust check against already-loaded state)
    function runInit() {
        initNavigation();
        initGlobalCrudDelegation();
        initComboboxDelegation();
        dispatchPageLogic(); // Immediately bind all CRUD modals and interactive buttons on page load
        initDataLoad();      // Then asynchronously fetch central state / time series
        initEconomicAiAssistant(); // Initialize AI Assistant if present on the page

        // Pastikan seluruh sub-indikator tertutup secara default saat halaman dimuat
        document.querySelectorAll('tr.matrix-sub-row, div.risk-sub-row').forEach(sr => {
            sr.classList.remove('is-open');
            sr.style.setProperty('display', 'none', 'important');
        });
        document.querySelectorAll('tr.matrix-row.has-subcategories, div.risk-row.has-subcategories').forEach(pr => {
            pr.classList.remove('is-expanded');
        });

        // Auto-apply category filter (from URL or active tab or first category tab)
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const catParam = urlParams.get('category') || urlParams.get('prefix');
            if (catParam) {
                applyCategoryFilter(catParam);
            } else {
                const activeTab = document.querySelector('.category-slide-tab.active');
                if (activeTab && activeTab.getAttribute('data-prefix')) {
                    applyCategoryFilter(activeTab.getAttribute('data-prefix'));
                } else {
                    const firstTab = document.querySelector('.category-slide-tab');
                    if (firstTab && firstTab.getAttribute('data-prefix')) {
                        applyCategoryFilter(firstTab.getAttribute('data-prefix'));
                    }
                }
            }
        } catch (e) { }
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

    // ========================================================
    // SINGLE COMBOBOX DELEGATION (PILIH DARI DAFTAR ATAU KETIK MANUAL)
    // ========================================================
    function initComboboxDelegation() {
        if (window._comboboxDelegationInitialized) return;
        window._comboboxDelegationInitialized = true;

        // Klik handler untuk toggle button, input, dan pilihan item
        document.addEventListener('click', function (e) {
            const toggleBtn = e.target.closest('.combobox-toggle-btn');
            const inputEl = e.target.closest('.cluster-combobox-input');
            const item = e.target.closest('.combobox-item');

            // 1. Jika klik pada salah satu opsi dalam dropdown
            if (item) {
                e.preventDefault();
                e.stopPropagation();
                const wrap = item.closest('.combobox-wrap');
                if (wrap) {
                    const inp = wrap.querySelector('.cluster-combobox-input');
                    const selectedVal = item.getAttribute('data-value') || item.textContent.trim();
                    if (inp) {
                        inp.value = selectedVal;
                        inp.dispatchEvent(new Event('input', { bubbles: true }));
                        inp.dispatchEvent(new Event('change', { bubbles: true }));
                        inp.focus();
                    }
                    wrap.classList.remove('is-open');
                }
                return;
            }

            // 2. Jika klik pada tombol toggle panah (chevron)
            if (toggleBtn) {
                e.preventDefault();
                e.stopPropagation();
                const wrap = toggleBtn.closest('.combobox-wrap');
                if (wrap) {
                    const wasOpen = wrap.classList.contains('is-open');
                    // Tutup semua combobox lain
                    document.querySelectorAll('.combobox-wrap.is-open').forEach(w => {
                        if (w !== wrap) w.classList.remove('is-open');
                    });
                    if (wasOpen) {
                        wrap.classList.remove('is-open');
                    } else {
                        // Tampilkan semua opsi & buka menu
                        const inp = wrap.querySelector('.cluster-combobox-input');
                        const curVal = inp ? (inp.value || '').trim().toLowerCase() : '';
                        wrap.querySelectorAll('.combobox-item').forEach(it => {
                            it.style.display = 'block';
                            const itVal = (it.getAttribute('data-value') || it.textContent).trim().toLowerCase();
                            if (curVal && itVal === curVal) {
                                it.classList.add('active');
                            } else {
                                it.classList.remove('active');
                            }
                        });
                        wrap.classList.add('is-open');
                        if (inp) inp.focus();
                    }
                }
                return;
            }

            // 3. Jika klik pada input field combobox
            if (inputEl) {
                const wrap = inputEl.closest('.combobox-wrap');
                if (wrap) {
                    // Tutup combobox lain
                    document.querySelectorAll('.combobox-wrap.is-open').forEach(w => {
                        if (w !== wrap) w.classList.remove('is-open');
                    });
                    // Jika belum terbuka, buka dan reset tampilan opsi
                    if (!wrap.classList.contains('is-open')) {
                        const curVal = (inputEl.value || '').trim().toLowerCase();
                        wrap.querySelectorAll('.combobox-item').forEach(it => {
                            const itVal = (it.getAttribute('data-value') || it.textContent).trim().toLowerCase();
                            if (!curVal || itVal.includes(curVal)) {
                                it.style.display = 'block';
                            } else {
                                it.style.display = 'none';
                            }
                            if (curVal && itVal === curVal) {
                                it.classList.add('active');
                            } else {
                                it.classList.remove('active');
                            }
                        });
                        wrap.classList.add('is-open');
                    }
                }
                return;
            }

            // 4. Klik di luar combobox: tutup seluruh dropdown
            document.querySelectorAll('.combobox-wrap.is-open').forEach(w => {
                w.classList.remove('is-open');
            });
        });

        // Filter opsi secara live saat pengguna mengetik manual
        document.addEventListener('input', function (e) {
            if (e.target && e.target.classList.contains('cluster-combobox-input')) {
                const inp = e.target;
                const wrap = inp.closest('.combobox-wrap');
                if (wrap) {
                    const q = inp.value.toLowerCase().trim();
                    wrap.classList.add('is-open');
                    wrap.querySelectorAll('.combobox-item').forEach(item => {
                        const txt = (item.getAttribute('data-value') || item.textContent).toLowerCase().trim();
                        if (!q || txt.includes(q)) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                }
            }
        });

        // Tutup menu combobox saat menekan Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.combobox-wrap.is-open').forEach(w => {
                    w.classList.remove('is-open');
                });
            }
        });
    }

    // Navigation & Global Events
    function initNavigation() {
        const btnToggle = document.getElementById('btn-open-sidebar');
        const sidebar = document.getElementById('sidebar');
        const scrim = document.getElementById('sidebar-scrim');

        // Restore desktop collapsed state from localStorage
        try {
            if (window.innerWidth > 780 && localStorage.getItem('sidapeda_sidebar_collapsed') === '1') {
                document.body.classList.add('sidebar-collapsed');
            }
        } catch (e) { }

        const toggleSidebar = () => {
            if (window.innerWidth <= 780) {
                // Mobile behavior: drawer
                if (sidebar) {
                    const isOpen = sidebar.classList.contains('mobile-open');
                    if (isOpen) {
                        sidebar.classList.remove('mobile-open');
                        if (scrim) scrim.style.display = 'none';
                    } else {
                        sidebar.classList.add('mobile-open');
                        if (scrim) scrim.style.display = 'block';
                    }
                }
            } else {
                // Desktop behavior: collapse / expand mini-sidebar (preserving logo)
                const isCollapsed = document.body.classList.toggle('sidebar-collapsed');
                try {
                    localStorage.setItem('sidapeda_sidebar_collapsed', isCollapsed ? '1' : '0');
                } catch (e) { }
                setTimeout(() => window.dispatchEvent(new Event('resize')), 270);
            }
        };

        if (btnToggle) btnToggle.addEventListener('click', toggleSidebar);
        if (scrim) {
            scrim.addEventListener('click', () => {
                if (sidebar) sidebar.classList.remove('mobile-open');
                scrim.style.display = 'none';
            });
        }

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
                    AppState.ews = (window.__EWS_DATA__ && Array.isArray(window.__EWS_DATA__)) ? window.__EWS_DATA__ : (data.ews || []);
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
        // Auto-scroll active year tab into view on load if Slide Tahun is present
        const slideTrack = document.getElementById('year-slide-track');
        if (slideTrack) {
            const activeTab = slideTrack.querySelector('.year-slide-tab.active');
            if (activeTab) {
                setTimeout(() => {
                    activeTab.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                }, 100);
            }
        }

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
    // RUPIAH & NUMBER FORMATTING HELPERS (CRUD)
    // ==========================================
    function formatRupiahNumber(val) {
        if (val === null || val === undefined || val === '') return '';
        let str = String(val).trim();
        if (!str) return '';
        let isNeg = str.startsWith('-');
        str = str.replace(/^-/, '');

        let intPart = '';
        let decPart = '';

        if (str.indexOf(',') !== -1) {
            let parts = str.split(',');
            intPart = parts[0].replace(/\D/g, '');
            decPart = parts[1] ? parts[1].replace(/\D/g, '') : '';
        } else if (str.indexOf('.') !== -1) {
            let parts = str.split('.');
            if (parts.length > 2) {
                intPart = parts.join('');
            } else if (parts[1] && parts[1].length <= 2) {
                intPart = parts[0].replace(/\D/g, '');
                decPart = parts[1].replace(/\D/g, '');
                if (decPart === '00' || decPart === '0') decPart = '';
            } else {
                intPart = parts.join('');
            }
        } else {
            intPart = str.replace(/\D/g, '');
        }

        if (decPart) {
            decPart = decPart.replace(/0+$/, '');
        }

        if (!intPart && !decPart) return '';
        let formattedInt = (intPart || '0').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return (isNeg ? '-' : '') + formattedInt + (decPart ? ',' + decPart : '');
    }

    function isIndicatorRupiah(unit, code) {
        if (!unit && !code) return false;
        const c = (code || '').toUpperCase().trim();
        const pCode = c.split('-')[0];
        const nonRupiahPrefixes = ['NAK', 'GEO', 'ANA', 'PAR'];
        if (nonRupiahPrefixes.includes(pCode)) return false;
        const rupiahExceptions = ['TK-009', 'TK-011', 'KES-003', 'KES-011', 'KES-012', 'PRO-005', 'HRG-005'];
        if (rupiahExceptions.includes(c)) return true;
        if (['TK', 'KES', 'PRO'].includes(pCode)) return false;
        const u = (unit || '').toLowerCase().trim();
        if (u.includes('%') || u.includes('persen') || u.includes('jiwa') || u.includes('orang') || u.includes('tahun') || u.includes('indeks') || u.includes('proyek') || u.includes('kunjungan') || u.includes('ton') || u.includes('hektare') || u.includes('kamar') || u.includes('unit')) {
            return false;
        }
        return /\b(rupiah|rp)\b/i.test(u);
    }

    function parseRupiahNumber(val) {
        if (val === null || val === undefined || val === '') return 0;
        let str = String(val).trim();
        if (!str) return 0;
        let isNeg = str.startsWith('-');
        str = str.replace(/^-/, '').replace(/[^0-9,\.]/g, '');

        let clean = '';
        if (str.indexOf(',') !== -1) {
            clean = str.replace(/\./g, '').replace(',', '.');
        } else if (str.indexOf('.') !== -1) {
            let parts = str.split('.');
            if (parts.length > 2) {
                clean = str.replace(/\./g, '');
            } else if (parts[1].length === 3) {
                clean = parts[0] + parts[1];
            } else {
                clean = str;
            }
        } else {
            clean = str;
        }
        let num = parseFloat(clean);
        if (isNaN(num)) return 0;
        return isNeg ? -num : num;
    }

    function handleRupiahInputMask(e) {
        const input = e.target;
        if (!input.classList.contains('is-rupiah-mask')) return;
        let cursor = input.selectionStart;
        let oldVal = input.value;
        let oldLen = oldVal.length;

        let hasComma = oldVal.includes(',');
        let parts = oldVal.split(',');
        let rawInt = parts[0].replace(/\D/g, '');
        let decPart = parts.length > 1 ? parts[1].replace(/\D/g, '') : '';

        if (!rawInt && !decPart) {
            input.value = '';
            return;
        }

        let formattedInt = rawInt ? rawInt.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '0';
        let newVal = formattedInt + (hasComma ? ',' + decPart : '');
        input.value = newVal;

        let newLen = newVal.length;
        let newCursor = cursor + (newLen - oldLen);
        if (newCursor >= 0 && newCursor <= newLen) {
            input.setSelectionRange(newCursor, newCursor);
        }
    }

    document.addEventListener('input', function (e) {
        if (e.target && e.target.classList && e.target.classList.contains('is-rupiah-mask')) {
            handleRupiahInputMask(e);
        }
    });

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
                    if (formAdd) formAdd.reset();
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
                if (fd.get('klaster') === '__custom__' && fd.get('klaster_custom')) {
                    fd.set('klaster', fd.get('klaster_custom').trim());
                }
                if (fd.get('cluster') === '__custom__' && fd.get('cluster_custom')) {
                    fd.set('cluster', fd.get('cluster_custom').trim());
                }
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
                if (fd.get('klaster') === '__custom__' && fd.get('klaster_custom')) {
                    fd.set('klaster', fd.get('klaster_custom').trim());
                }
                if (fd.get('cluster') === '__custom__' && fd.get('cluster_custom')) {
                    fd.set('cluster', fd.get('cluster_custom').trim());
                }
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
                        AppSwal.confirm({
                            title: 'Pindahkan ke Tempat Sampah?',
                            html: `Apakah Anda yakin ingin memindahkan <strong>"${name}"</strong> ke tempat sampah?<br><small style="color:#64748b; font-size:11.5px; margin-top:6px; display:block;">Data tidak akan dihapus permanen dan dapat dipulihkan sewaktu-waktu dari Tempat Sampah.</small>`,
                            icon: 'warning',
                            confirmButtonColor: '#e11d48',
                            confirmButtonText: 'Ya',
                            cancelButtonText: 'Tidak'
                        }).then(result => {
                            if (result && result.isConfirmed) {
                                fetch(`${cleanSite}/${cfg.controller}/soft_delete/${id}`, {
                                    method: 'POST',
                                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                })
                                    .then(r => r.json())
                                    .then(data => {
                                        if (data.status === 'success') {
                                            location.reload();
                                        } else {
                                            AppSwal.error('Gagal Memproses', data.message || 'Gagal memproses soft delete.');
                                        }
                                    })
                                    .catch(() => {
                                        window.location.href = `${cleanSite}/${cfg.controller}/soft_delete/${id}`;
                                    });
                            }
                        });
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
                        AppSwal.confirm({
                            title: 'Pulihkan Data?',
                            html: `Pulihkan <strong>"${name}"</strong> kembali ke daftar aktif?`,
                            icon: 'question',
                            confirmButtonColor: '#0d9488',
                            confirmButtonText: 'Ya',
                            cancelButtonText: 'Tidak'
                        }).then(result => {
                            if (result && result.isConfirmed) {
                                fetch(`${cleanSite}/${cfg.controller}/restore/${id}`, {
                                    method: 'POST',
                                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                })
                                    .then(r => r.json())
                                    .then(data => {
                                        if (data.status === 'success') {
                                            location.reload();
                                        } else {
                                            AppSwal.error('Gagal Memulihkan', data.message || 'Gagal memulihkan data.');
                                        }
                                    })
                                    .catch(() => {
                                        window.location.href = `${cleanSite}/${cfg.controller}/restore/${id}`;
                                    });
                            }
                        });
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
                        AppSwal.dangerConfirm({
                            title: 'Hapus Permanen?',
                            html: `Peringatan: Apakah Anda yakin ingin <strong>MENGHAPUS PERMANEN</strong> <strong>"${name}"</strong>?<br><br><span style="color:#e11d48; font-weight:600; font-size:12px;">Data yang dihapus permanen akan hilang selamanya dari database dan TIDAK DAPAT DIPULIHKAN LAGI!</span>`,
                            icon: 'error',
                            confirmButtonColor: '#b91c1c',
                            confirmButtonText: 'Ya',
                            cancelButtonText: 'Tidak'
                        }).then(result => {
                            if (result && result.isConfirmed) {
                                fetch(`${cleanSite}/${cfg.controller}/hard_delete/${id}`, {
                                    method: 'POST',
                                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                })
                                    .then(r => r.json())
                                    .then(data => {
                                        if (data.status === 'success') {
                                            location.reload();
                                        } else {
                                            AppSwal.error('Gagal Menghapus', data.message || 'Gagal menghapus permanen.');
                                        }
                                    })
                                    .catch(() => {
                                        window.location.href = `${cleanSite}/${cfg.controller}/hard_delete/${id}`;
                                    });
                            }
                        });
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

        const rawRegionName = (window.__ACTIVE_REGION_NAME__ || 'Kabupaten Banyuwangi').trim();
        const shortRegionName = rawRegionName.replace(/^kabupaten\s+|^kab\.\s+|^kota\s+/i, '').trim() || 'Banyuwangi';

        if (legend0) legend0.textContent = shortRegionName;

        const indicatorConfigs = {
            'growth': {
                title: 'Tren Pertumbuhan Ekonomi',
                desc: 'Persentase pertumbuhan PDRB ADHK â€¢ perbandingan wilayah',
                unit: '%',
                hasPeers: true,
                getSeries: (endIdx) => [
                    { name: shortRegionName, values: AppState.series.growth.slice(0, endIdx + 1), className: 'series-0' },
                    { name: 'Jawa Timur', values: AppState.series.eastJavaGrowth.slice(0, endIdx + 1), className: 'series-1' },
                    { name: 'Nasional', values: AppState.series.nationalGrowth.slice(0, endIdx + 1), className: 'series-2' }
                ]
            },
            'perCapita': {
                title: 'Tren PDRB Per Kapita',
                desc: `Tingkat pendapatan per kapita ${rawRegionName} (Juta Rupiah)`,
                unit: ' Jt',
                hasPeers: false,
                getSeries: (endIdx) => [
                    { name: 'PDRB Per Kapita', values: AppState.series.perCapita.slice(0, endIdx + 1), className: 'series-0' }
                ]
            },
            'inflation': {
                title: 'Tren Inflasi Tahunan',
                desc: `Perkembangan laju inflasi tahunan (yoy) ${rawRegionName}`,
                unit: '%',
                hasPeers: false,
                getSeries: (endIdx) => [
                    { name: `Inflasi ${shortRegionName}`, values: AppState.series.inflation.slice(0, endIdx + 1), className: 'series-0' }
                ]
            },
            'tpt': {
                title: 'Tren Tingkat Pengangguran Terbuka (TPT)',
                desc: `Proporsi angkatan kerja terbuka ${rawRegionName} (%)`,
                unit: '%',
                hasPeers: false,
                getSeries: (endIdx) => [
                    { name: `TPT ${shortRegionName}`, values: AppState.series.tpt.slice(0, endIdx + 1), className: 'series-0' }
                ]
            },
            'poverty': {
                title: 'Tren Tingkat Kemiskinan',
                desc: `Persentase penduduk miskin ${rawRegionName} (%)`,
                unit: '%',
                hasPeers: false,
                getSeries: (endIdx) => [
                    { name: `Kemiskinan ${shortRegionName}`, values: AppState.series.poverty.slice(0, endIdx + 1), className: 'series-0' }
                ]
            },
            'investmentGrowth': {
                title: 'Tren Pertumbuhan Realisasi Investasi',
                desc: 'Laju pertumbuhan penanaman modal daerah tahunan (%)',
                unit: '%',
                hasPeers: false,
                getSeries: (endIdx) => [
                    { name: `Investasi ${shortRegionName}`, values: AppState.series.investmentGrowth.slice(0, endIdx + 1), className: 'series-0' }
                ]
            },
            'ipm': {
                title: 'Tren Indeks Pembangunan Manusia (IPM)',
                desc: 'Capaian IPM komposit pendidikan, kesehatan, dan standar hidup (poin)',
                unit: ' pt',
                hasPeers: false,
                getSeries: (endIdx) => [
                    { name: `IPM ${shortRegionName}`, values: AppState.series.ipm.slice(0, endIdx + 1), className: 'series-0' }
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

        // ======================================================================
        // MODAL DETAIL INDIKATOR BERANDA (8 INDIKATOR UTAMA EKONOMI)
        // ======================================================================
        const modalOverlay = document.getElementById('kpi-detail-overlay');
        const modalBox = document.getElementById('kpi-detail-modal-box');

        function downloadDetailCsv(filename, rows) {
            let csvContent = '';
            rows.forEach(row => {
                const rowStr = row.map(val => {
                    let s = (val === null || val === undefined) ? '' : String(val);
                    if (s.includes('"') || s.includes(',') || s.includes('\n')) {
                        s = '"' + s.replace(/"/g, '""') + '"';
                    }
                    return s;
                }).join(',');
                csvContent += rowStr + '\r\n';
            });
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.setAttribute('download', filename);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function closeKpiDetailModal() {
            if (modalOverlay) {
                modalOverlay.style.display = 'none';
                document.body.style.overflow = '';
            }
        }

        if (modalOverlay) {
            modalOverlay.addEventListener('click', function (e) {
                if (e.target === modalOverlay) {
                    closeKpiDetailModal();
                }
            });
        }
        window.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modalOverlay && modalOverlay.style.display !== 'none') {
                closeKpiDetailModal();
            }
        });

        function getKpiDetailConfig(idx) {
            const card = document.querySelector(`.kpi-card[data-index="${idx}"]`);
            const curVal = card ? (card.dataset.value || (card.querySelector('.kpi-value strong') ? card.querySelector('.kpi-value strong').textContent.trim() : '')) : '';
            const curUnit = card ? (card.dataset.unit || (card.querySelector('.kpi-value span') ? card.querySelector('.kpi-value span').textContent.trim() : '')) : '';
            const curStatus = card ? (card.dataset.status || (card.querySelector('.badge') ? card.querySelector('.badge').textContent.trim() : 'Meningkat')) : 'Meningkat';
            const curDelta = card ? (card.dataset.delta || (card.querySelector('.kpi-bottom span') ? card.querySelector('.kpi-bottom span').textContent.replace(/[â†—â†˜]/g, '').trim() : '')) : '';
            const cardYear = card ? (card.dataset.year || (card.querySelector('.kpi-period') && card.querySelector('.kpi-period').textContent.match(/\b(20\d\d)\b/) ? card.querySelector('.kpi-period').textContent.match(/\b(20\d\d)\b/)[1] : null)) : null;
            const curYear = cardYear || (filterYear && filterYear.value && filterYear.value !== 'all' ? filterYear.value : '2024');
            const yearNum = parseInt(curYear, 10) || 2024;

            // Gunakan data riil resmi yang dioper dari database backend jika tersedia
            if (window.KPI_REAL_DATA && window.KPI_REAL_DATA[idx]) {
                const real = JSON.parse(JSON.stringify(window.KPI_REAL_DATA[idx]));
                if (curVal) real.curVal = curVal;
                if (curUnit) real.curUnit = curUnit;
                if (curStatus) real.curStatus = curStatus;
                if (curDelta) real.curDelta = curDelta;
                if (curYear) real.year = curYear;
                return real;
            }

            const configs = [
                // 0: Pertumbuhan Ekonomi
                {
                    name: 'Pertumbuhan Ekonomi',
                    subtitle: 'Laju perubahan output ekonomi daerah berdasarkan PDRB atas dasar harga konstan',
                    definition: 'Pertumbuhan ekonomi mengukur perubahan volume produksi barang dan jasa dari satu tahun ke tahun berikutnya. Untuk membaca kualitas pertumbuhan, nilai agregat perlu dilihat bersama dinamika lapangan usaha dan pembanding wilayah.',
                    metrics: [
                        { label: 'Jawa Timur', value: '4,93%', note: `Tahun ${curYear}` },
                        { label: 'Nasional', value: '5,03%', note: `Tahun ${curYear}` },
                        { label: 'Sektor tumbuh tertinggi', value: 'Akomodasi & Makan Minum (7,3%)', note: 'Kabupaten Banyuwangi' }
                    ],
                    componentTitle: 'Pertumbuhan dan struktur 7 lapangan usaha',
                    componentDesc: 'Membaca pertumbuhan sektoral bersama pangsa sektor dalam PDRB. Pangsa dan laju pertumbuhan adalah dua ukuran yang berbeda.',
                    components: [
                        { name: 'Akomodasi & Makan Minum', value: 7.3, unit: '%', context: 'Pariwisata & jasa boga', share: 9.1, benchmark: 6.4 },
                        { name: 'Perdagangan Besar & Eceran', value: 5.4, unit: '%', context: 'Pusat distribusi barang', share: 18.5, benchmark: 5.2 },
                        { name: 'Transportasi & Pergudangan', value: 5.1, unit: '%', context: 'Logistik & konektivitas wilayah', share: 5.0, benchmark: 5.0 },
                        { name: 'Jasa Lainnya', value: 5.0, unit: '%', context: 'Jasa kemasyarakatan & perorangan', share: 18.7, benchmark: 5.1 },
                        { name: 'Konstruksi', value: 4.8, unit: '%', context: 'Pembangunan fisik & sarana', share: 8.0, benchmark: 5.1 },
                        { name: 'Pertanian, Kehutanan & Perikanan', value: 4.3, unit: '%', context: 'Sektor basis utama Banyuwangi', share: 28.5, benchmark: 3.1 },
                        { name: 'Industri Pengolahan', value: 4.1, unit: '%', context: 'Manufaktur & agroindustri hilir', share: 12.2, benchmark: 4.8 }
                    ],
                    comparisonTitle: 'Perbandingan pertumbuhan kabupaten/kota',
                    comparisonDesc: 'Pembanding wilayah dalam dataset Jawa Timur; urutan berdasarkan laju pertumbuhan tahunan.',
                    peers: [
                        { name: 'Kota Surabaya', value: 5.76, note: 'Laju pertumbuhan ekonomi tahunan', current: false },
                        { name: 'Kab. Gresik', value: 5.42, note: 'Laju pertumbuhan ekonomi tahunan', current: false },
                        { name: 'Kab. Banyuwangi', value: parseFloat(curVal.replace(',', '.')) || 5.18, note: 'Kabupaten terpilih', current: true },
                        { name: 'Kota Malang', value: 5.06, note: 'Laju pertumbuhan ekonomi tahunan', current: false },
                        { name: 'Kab. Sidoarjo', value: 4.98, note: 'Laju pertumbuhan ekonomi tahunan', current: false },
                        { name: 'Kab. Jember', value: 4.76, note: 'Laju pertumbuhan ekonomi tahunan', current: false },
                        { name: 'Kab. Situbondo', value: 4.61, note: 'Laju pertumbuhan ekonomi tahunan', current: false },
                        { name: 'Kab. Bondowoso', value: 4.48, note: 'Laju pertumbuhan ekonomi tahunan', current: false }
                    ],
                    heatTitle: 'Peta panas pertumbuhan sektoral',
                    heatDesc: 'Perubahan laju pertumbuhan sektor selama lima tahun terakhir pada data resmi BPS.',
                    heatRows: [
                        { name: 'Akomodasi & Makan Minum', values: [-8.0, 5.9, 9.2, 7.0, 7.3] },
                        { name: 'Perdagangan', values: [-4.2, 4.3, 5.0, 5.2, 5.4] },
                        { name: 'Transportasi', values: [-7.2, 4.2, 7.8, 5.3, 5.1] },
                        { name: 'Konstruksi', values: [-3.9, 3.3, 4.5, 4.6, 4.8] },
                        { name: 'Pertanian', values: [2.1, 3.0, 3.6, 4.0, 4.3] },
                        { name: 'Industri Pengolahan', values: [-2.8, 3.1, 4.0, 4.0, 4.1] },
                        { name: 'Jasa Lainnya', values: [-1.8, 3.7, 4.8, 4.9, 5.0] }
                    ],
                    annualSeries: [
                        { year: 2015, value: 5.02, change: null },
                        { year: 2016, value: 5.15, change: '+0,13' },
                        { year: 2017, value: 5.28, change: '+0,13' },
                        { year: 2018, value: 5.61, change: '+0,33' },
                        { year: 2019, value: 5.55, change: '-0,06' },
                        { year: 2020, value: -3.58, change: '-9,13' },
                        { year: 2021, value: 4.08, change: '+7,66' },
                        { year: 2022, value: 4.43, change: '+0,35' },
                        { year: 2023, value: 5.03, change: '+0,60' },
                        { year: 2024, value: 5.18, change: '+0,15' }
                    ]
                },
                // 1: PDRB per Kapita
                {
                    name: 'PDRB per Kapita',
                    subtitle: 'Rata-rata nilai PDRB yang dihasilkan untuk setiap penduduk',
                    definition: 'PDRB per kapita diperoleh dari PDRB dibagi jumlah penduduk pertengahan tahun. Indikator ini menggambarkan rata-rata output ekonomi per penduduk, bukan pendapatan yang benar-benar diterima setiap orang.',
                    metrics: [
                        { label: 'Perubahan tahunan', value: '+4,00 juta', note: 'Selisih nilai terhadap tahun sebelumnya' },
                        { label: 'Rata-rata pertumbuhan tahunan', value: '6,58%', note: 'Rata-rata majemuk sejak 2015' },
                        { label: 'Perubahan sejak 2015', value: '+27,20 juta', note: 'Kenaikan nilai nominal per kapita' }
                    ],
                    componentTitle: 'Perbandingan indikator per kapita',
                    componentDesc: 'PDRB per kapita merupakan rasio output regional terhadap jumlah penduduk. Nilai nominal bukan pendapatan rumah tangga dan tidak otomatis menunjukkan pemerataan.',
                    components: [
                        { name: 'PDRB per kapita ADHB', value: parseFloat(curVal.replace(',', '.')) || 62.4, unit: 'juta rupiah', context: 'Ukuran output nominal per penduduk' },
                        { name: 'Perubahan tahunan', value: 4.0, unit: 'juta rupiah', context: 'Selisih nilai nominal dari tahun sebelumnya' },
                        { name: 'Rata-rata pertumbuhan nominal', value: 6.58, unit: '%', context: 'CAGR selama seri yang tersedia' },
                        { name: 'Pertumbuhan ekonomi', value: 5.18, unit: '%', context: 'Konteks pertumbuhan output daerah' }
                    ],
                    comparisonTitle: 'Posisi PDRB per kapita antarwilayah',
                    comparisonDesc: 'Perbandingan kabupaten/kota sekitar untuk melihat posisi relatif Banyuwangi.',
                    peers: [
                        { name: 'Nasional', value: 78.6, note: 'Pembanding nasional Â· Data BPS', current: false },
                        { name: 'Jawa Timur', value: 70.8, note: 'Pembanding provinsi Â· Data BPS', current: false },
                        { name: 'Kab. Banyuwangi', value: parseFloat(curVal.replace(',', '.')) || 62.4, note: 'Kabupaten terpilih', current: true },
                        { name: 'Kab. Jember', value: 48.7, note: 'PDRB per kapita nominal', current: false },
                        { name: 'Kab. Situbondo', value: 45.2, note: 'PDRB per kapita nominal', current: false },
                        { name: 'Kab. Bondowoso', value: 42.8, note: 'PDRB per kapita nominal', current: false }
                    ],
                    heatTitle: 'Peta panas tren PDRB per kapita',
                    heatDesc: 'Tren nominal PDRB per kapita pada seri tahun yang tersedia.',
                    heatRows: [
                        { name: 'Kab. Banyuwangi', values: [43.7, 47.6, 53.2, 58.4, 62.4] },
                        { name: 'Jawa Timur', values: [49.8, 54.2, 60.6, 66.5, 70.8] },
                        { name: 'Nasional', values: [57.2, 62.3, 67.5, 74.2, 78.6] }
                    ],
                    annualSeries: [
                        { year: 2015, value: 35.2, change: null },
                        { year: 2016, value: 37.1, change: '+1,90' },
                        { year: 2017, value: 39.4, change: '+2,30' },
                        { year: 2018, value: 42.8, change: '+3,40' },
                        { year: 2019, value: 45.9, change: '+3,10' },
                        { year: 2020, value: 43.7, change: '-2,20' },
                        { year: 2021, value: 47.6, change: '+3,90' },
                        { year: 2022, value: 53.2, change: '+5,60' },
                        { year: 2023, value: 58.4, change: '+5,20' },
                        { year: 2024, value: 62.4, change: '+4,00' }
                    ]
                },
                // 2: Tingkat Inflasi
                {
                    name: 'Tingkat Inflasi',
                    subtitle: 'Perubahan tingkat harga barang dan jasa yang dikonsumsi rumah tangga',
                    definition: 'Inflasi menunjukkan kenaikan tingkat harga secara umum dalam periode tertentu. Untuk memahami penyebabnya, angka inflasi agregat perlu dilengkapi kelompok pengeluaran dan komoditas penyumbang inflasi maupun deflasi.',
                    metrics: [
                        { label: 'Perubahan tahunan', value: '-0,40 pp', note: 'Perubahan terhadap tahun sebelumnya' },
                        { label: 'Inflasi tertinggi', value: '6,12%', note: 'Terjadi pada tahun 2022' },
                        { label: 'Inflasi terendah', value: '1,68%', note: 'Terjadi pada tahun 2020' }
                    ],
                    componentTitle: 'Inflasi menurut kelompok pengeluaran',
                    componentDesc: 'Rincian kelompok pengeluaran menunjukkan kelompok yang mengalami tekanan harga relatif lebih tinggi.',
                    components: [
                        { name: 'Makanan, Minuman & Tembakau', value: 3.85, unit: '%', context: 'Kelompok pengeluaran berbobot utama' },
                        { name: 'Penyediaan Makanan & Minuman / Restoran', value: 2.95, unit: '%', context: 'Jasa konsumsi makan di luar rumah' },
                        { name: 'Kesehatan', value: 2.20, unit: '%', context: 'Obat-obatan dan layanan kesehatan' },
                        { name: 'Transportasi', value: 2.12, unit: '%', context: 'Tarif angkutan dan bahan bakar' },
                        { name: 'Pendidikan', value: 1.80, unit: '%', context: 'Biaya pendidikan dan perlengkapan' },
                        { name: 'Perumahan, Air, Listrik & Bahan Bakar', value: 1.45, unit: '%', context: 'Beban utilitas rumah tangga' }
                    ],
                    comparisonTitle: 'Perbandingan inflasi wilayah',
                    comparisonDesc: 'Pembanding daerah dan nasional untuk membaca apakah tekanan harga bersifat lokal atau lebih luas.',
                    peers: [
                        { name: 'Kab. Situbondo', value: 2.31, note: 'Kabupaten pembanding Â· Data BPS', current: false },
                        { name: 'Kab. Banyuwangi', value: parseFloat(curVal.replace(',', '.')) || 2.41, note: 'Kabupaten terpilih', current: true },
                        { name: 'Nasional', value: 2.51, note: 'Inflasi nasional Â· Data BPS', current: false },
                        { name: 'Jawa Timur', value: 2.67, note: 'Inflasi provinsi Â· Data BPS', current: false },
                        { name: 'Kab. Jember', value: 2.73, note: 'Kabupaten pembanding Â· Data BPS', current: false }
                    ],
                    heatTitle: 'Peta panas inflasi bulanan per kelompok',
                    heatDesc: 'Intensitas inflasi berdasarkan 12 bulan referensi dan kelompok pengeluaran.',
                    heatColumns: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    heatRows: [
                        { name: 'Makanan & Tembakau', values: [3.2, 3.4, 3.8, 4.1, 3.9, 3.7, 3.5, 3.8, 3.9, 3.7, 3.8, 3.85] },
                        { name: 'Restoran / Jasa Boga', values: [2.5, 2.6, 2.7, 2.9, 3.0, 3.1, 3.0, 2.9, 2.8, 2.9, 2.9, 2.95] },
                        { name: 'Kesehatan', values: [2.1, 2.1, 2.2, 2.2, 2.2, 2.2, 2.3, 2.2, 2.2, 2.2, 2.2, 2.20] },
                        { name: 'Transportasi', values: [2.8, 2.6, 2.4, 2.3, 2.1, 2.0, 2.1, 2.2, 2.1, 2.1, 2.1, 2.12] },
                        { name: 'Pendidikan', values: [1.5, 1.5, 1.5, 1.5, 1.6, 1.8, 2.0, 2.2, 2.0, 1.9, 1.8, 1.80] },
                        { name: 'Perumahan & Utilitas', values: [1.6, 1.5, 1.5, 1.4, 1.4, 1.4, 1.5, 1.5, 1.5, 1.4, 1.4, 1.45] }
                    ],
                    annualSeries: [
                        { year: 2015, value: 3.12, change: null },
                        { year: 2016, value: 2.71, change: '-0,41' },
                        { year: 2017, value: 3.61, change: '+0,90' },
                        { year: 2018, value: 2.88, change: '-0,73' },
                        { year: 2019, value: 2.94, change: '+0,06' },
                        { year: 2020, value: 1.68, change: '-1,26' },
                        { year: 2021, value: 1.87, change: '+0,19' },
                        { year: 2022, value: 6.12, change: '+4,25' },
                        { year: 2023, value: 2.81, change: '-3,31' },
                        { year: 2024, value: 2.41, change: '-0,40' }
                    ]
                },
                // 3: Pengangguran Terbuka
                {
                    name: 'Pengangguran Terbuka',
                    subtitle: 'Persentase penganggur terhadap total angkatan kerja',
                    definition: 'Tingkat Pengangguran Terbuka (TPT) menunjukkan proporsi angkatan kerja yang belum terserap dalam pekerjaan. Pembacaan pasar kerja perlu dilakukan bersama partisipasi angkatan kerja, kualitas pekerjaan, dan karakteristik penganggur.',
                    metrics: [
                        { label: 'Perubahan tahunan', value: '-0,07 pp', note: 'Penurunan TPT merupakan perbaikan indikator' },
                        { label: 'TPT tertinggi', value: '5,50%', note: 'Terjadi pada tahun 2020' },
                        { label: 'TPT terendah', value: '2,94%', note: 'Terjadi pada tahun 2018' }
                    ],
                    componentTitle: 'Struktur pasar kerja & kualitas pekerjaan',
                    componentDesc: 'TPT harus dibaca bersama partisipasi angkatan kerja, status pekerjaan, serta kualitas pekerjaan.',
                    components: [
                        { name: 'Penduduk Bekerja', value: 95.97, unit: '% angkatan kerja', context: 'Proporsi angkatan kerja yang terserap kerja' },
                        { name: 'Penganggur Terbuka (TPT)', value: parseFloat(curVal.replace(',', '.')) || 4.03, unit: '%', context: 'Proporsi penganggur terhadap angkatan kerja' },
                        { name: 'TPAK (Partisipasi Kerja)', value: 72.8, unit: '%', context: 'Tingkat partisipasi angkatan kerja' },
                        { name: 'Pekerja Formal', value: 38.6, unit: '%', context: 'Pangsa pekerja dengan status formal' },
                        { name: 'Pekerja Informal', value: 61.4, unit: '%', context: 'Pangsa pekerja dengan status informal' },
                        { name: 'Setengah Pengangguran', value: 8.2, unit: '%', context: 'Pekerja yang mencari tambahan jam kerja' }
                    ],
                    comparisonTitle: 'Perbandingan TPT wilayah sekitar',
                    comparisonDesc: 'Posisi TPT kabupaten dibandingkan daerah sekitar di Jawa Timur.',
                    peers: [
                        { name: 'Kab. Bondowoso', value: 3.71, note: 'Tingkat Pengangguran Terbuka', current: false },
                        { name: 'Kab. Situbondo', value: 3.88, note: 'Tingkat Pengangguran Terbuka', current: false },
                        { name: 'Kab. Lumajang', value: 3.96, note: 'Tingkat Pengangguran Terbuka', current: false },
                        { name: 'Kab. Banyuwangi', value: parseFloat(curVal.replace(',', '.')) || 4.03, note: 'Kabupaten terpilih', current: true },
                        { name: 'Jawa Timur', value: 4.19, note: 'Pembanding provinsi', current: false },
                        { name: 'Kab. Jember', value: 4.21, note: 'Tingkat Pengangguran Terbuka', current: false },
                        { name: 'Kab. Probolinggo', value: 4.62, note: 'Tingkat Pengangguran Terbuka', current: false },
                        { name: 'Nasional', value: 4.91, note: 'Pembanding nasional', current: false },
                        { name: 'Kab. Malang', value: 5.05, note: 'Tingkat Pengangguran Terbuka', current: false },
                        { name: 'Kab. Pasuruan', value: 5.12, note: 'Tingkat Pengangguran Terbuka', current: false }
                    ],
                    heatTitle: 'Peta panas TPT menurut kelompok',
                    heatDesc: 'Pola TPT menurut karakteristik angkatan kerja lima tahun terakhir.',
                    heatRows: [
                        { name: 'Laki-laki', values: [4.4, 4.1, 3.9, 3.8, 3.7] },
                        { name: 'Perempuan', values: [4.9, 4.6, 4.1, 4.3, 4.4] },
                        { name: 'Usia Muda (15â€“24)', values: [11.4, 10.3, 9.1, 8.9, 8.7] },
                        { name: 'Pendidikan SMA/SMK', values: [5.1, 4.8, 4.4, 4.2, 4.0] }
                    ],
                    annualSeries: [
                        { year: 2015, value: 3.20, change: null },
                        { year: 2016, value: 3.12, change: '-0,08' },
                        { year: 2017, value: 3.05, change: '-0,07' },
                        { year: 2018, value: 2.94, change: '-0,11' },
                        { year: 2019, value: 3.84, change: '+0,90' },
                        { year: 2020, value: 5.50, change: '+1,66' },
                        { year: 2021, value: 5.10, change: '-0,40' },
                        { year: 2022, value: 4.30, change: '-0,80' },
                        { year: 2023, value: 4.10, change: '-0,20' },
                        { year: 2024, value: 4.03, change: '-0,07' }
                    ]
                },
                // 4: Tingkat Kemiskinan
                {
                    name: 'Tingkat Kemiskinan',
                    subtitle: 'Proporsi penduduk dengan pengeluaran per kapita di bawah garis kemiskinan',
                    definition: 'Tingkat kemiskinan mengukur persentase penduduk miskin (P0). Evaluasi kemiskinan sebaiknya tidak hanya melihat persentase penduduk miskin, tetapi juga kedalaman (P1), keparahan (P2), garis kemiskinan, dan persebaran spasialnya.',
                    metrics: [
                        { label: 'Perubahan tahunan', value: '+0,00 pp', note: 'Stabil dibandingkan tahun sebelumnya' },
                        { label: 'Perubahan sejak 2015', value: '-1,83 pp', note: 'Penurunan angka kemiskinan jangka panjang' },
                        { label: 'Tingkat terendah', value: '7,34%', note: 'Capaian terendah tercatat 2023â€“2024' }
                    ],
                    componentTitle: 'Dimensi kemiskinan dan kerentanan',
                    componentDesc: 'Tingkat kemiskinan (P0) perlu dibaca bersama kedalaman (P1), keparahan (P2), garis kemiskinan, dan persebaran spasial.',
                    components: [
                        { name: 'P0 Â· Tingkat Kemiskinan', value: parseFloat(curVal.replace(',', '.')) || 7.34, unit: '%', context: 'Persentase penduduk di bawah garis kemiskinan' },
                        { name: 'P1 Â· Kedalaman Kemiskinan', value: 1.18, unit: 'indeks', context: 'Rata-rata jarak pengeluaran dari garis kemiskinan' },
                        { name: 'P2 Â· Keparahan Kemiskinan', value: 0.26, unit: 'indeks', context: 'Ketimpangan pengeluaran di antara penduduk miskin' },
                        { name: 'Garis Kemiskinan (GK)', value: 528450, unit: 'Rp/kapita/bulan', context: 'Standar minimum pemenuhan kebutuhan dasar' },
                        { name: 'Kemiskinan Perkotaan', value: 6.42, unit: '%', context: 'Persentase penduduk miskin wilayah perkotaan' },
                        { name: 'Kemiskinan Perdesaan', value: 8.12, unit: '%', context: 'Persentase penduduk miskin wilayah perdesaan' }
                    ],
                    comparisonTitle: 'Perbandingan kemiskinan kecamatan',
                    comparisonDesc: 'Kecamatan dengan tingkat kemiskinan relatif rendah hingga tinggi di Banyuwangi.',
                    peers: [
                        { name: 'Banyuwangi (Kota)', value: 6.9, note: 'Pusat pemerintahan & komersial', current: true },
                        { name: 'Rogojampi', value: 7.2, note: 'Kawasan perdagangan & jasa', current: false },
                        { name: 'Glagah', value: 7.6, note: 'Kawasan pariwisata & agrowisata', current: false },
                        { name: 'Genteng', value: 8.1, note: 'Kawasan perdagangan & pertanian', current: false },
                        { name: 'Muncar', value: 8.4, note: 'Sentra perikanan & industri maritim', current: false },
                        { name: 'Kalibaru', value: 8.7, note: 'Agroindustri & perkebunan kopi', current: false },
                        { name: 'Singojuruh', value: 8.9, note: 'Pertanian tanaman pangan', current: false },
                        { name: 'Siliragung', value: 9.1, note: 'Pertanian & perkebunan', current: false },
                        { name: 'Glenmore', value: 9.2, note: 'Kawasan perkebunan terpadu', current: false },
                        { name: 'Wongsorejo', value: 9.4, note: 'Pertanian lahan kering & energi', current: false },
                        { name: 'Tegaldlimo', value: 9.8, note: 'Pertanian & pesisir pantai', current: false },
                        { name: 'Pesanggaran', value: 10.2, note: 'Pertambangan & wisata bahari', current: false }
                    ],
                    heatTitle: 'Peta panas kemiskinan kecamatan',
                    heatDesc: 'Persebaran tingkat kemiskinan per kecamatan selama lima tahun terakhir.',
                    heatRows: [
                        { name: 'Banyuwangi', values: [8.6, 8.2, 7.7, 7.3, 6.9] },
                        { name: 'Rogojampi', values: [8.9, 8.5, 8.0, 7.6, 7.2] },
                        { name: 'Glagah', values: [9.3, 8.9, 8.4, 8.0, 7.6] },
                        { name: 'Genteng', values: [9.8, 9.4, 8.9, 8.5, 8.1] },
                        { name: 'Muncar', values: [10.1, 9.7, 9.2, 8.8, 8.4] },
                        { name: 'Kalibaru', values: [10.4, 10.0, 9.5, 9.1, 8.7] },
                        { name: 'Singojuruh', values: [10.6, 10.2, 9.7, 9.3, 8.9] },
                        { name: 'Pesanggaran', values: [11.9, 11.5, 11.0, 10.6, 10.2] }
                    ],
                    annualSeries: [
                        { year: 2015, value: 9.17, change: null },
                        { year: 2016, value: 8.75, change: '-0,42' },
                        { year: 2017, value: 8.64, change: '-0,11' },
                        { year: 2018, value: 8.32, change: '-0,32' },
                        { year: 2019, value: 7.52, change: '-0,80' },
                        { year: 2020, value: 8.06, change: '+0,54' },
                        { year: 2021, value: 7.45, change: '-0,61' },
                        { year: 2022, value: 7.51, change: '+0,06' },
                        { year: 2023, value: 7.34, change: '-0,17' },
                        { year: 2024, value: 7.34, change: '+0,00' }
                    ]
                },
                // 5: IPM
                {
                    name: 'IPM (Indeks Pembangunan Manusia)',
                    subtitle: 'Indeks komposit yang menggambarkan capaian pembangunan manusia',
                    definition: 'Indeks Pembangunan Manusia (IPM) merangkum dimensi umur panjang dan hidup sehat, pengetahuan, serta standar hidup layak. Nilai komposit perlu dibaca bersama indikator pembentuk masing-masing dimensi.',
                    metrics: [
                        { label: 'Perubahan tahunan', value: '+0,71 poin', note: 'Kenaikan poin IPM tahun berjalan' },
                        { label: 'Perubahan sejak 2015', value: '+5,68 poin', note: 'Akumulasi pertumbuhan IPM sejak 2015' },
                        { label: 'Rata-rata kenaikan tahunan', value: '+0,63 poin', note: 'Rata-rata perubahan per tahun sejak 2015' }
                    ],
                    componentTitle: 'Tiga dimensi pembentuk IPM',
                    componentDesc: 'IPM merupakan indeks komposit; evaluasi capaian perlu menelusuri kesehatan, pendidikan, dan standar hidup layak.',
                    components: [
                        { name: 'Umur Harapan Hidup (UHH)', value: 72.48, unit: 'tahun', context: 'Dimensi umur panjang dan hidup sehat' },
                        { name: 'Harapan Lama Sekolah (HLS)', value: 13.42, unit: 'tahun', context: 'Perkiraan lama sekolah anak usia sekolah' },
                        { name: 'Rata-rata Lama Sekolah (RLS)', value: 7.68, unit: 'tahun', context: 'Rata-rata pendidikan penduduk usia 25+ tahun' },
                        { name: 'Pengeluaran Riil Per Kapita Disesuaikan', value: 12340, unit: 'ribu rupiah', context: 'Dimensi standar hidup layak' },
                        { name: 'IPM Agregat', value: parseFloat(curVal.replace(',', '.')) || 73.79, unit: 'poin', context: 'Indeks komposit tiga dimensi pembangunan manusia' }
                    ],
                    comparisonTitle: 'Perbandingan IPM wilayah',
                    comparisonDesc: 'Benchmarking kabupaten/kota dan provinsi untuk mengukur posisi relatif capaian pembangunan manusia.',
                    peers: [
                        { name: 'Kota Surabaya', value: 84.2, note: 'Kota di Jawa Timur', current: false },
                        { name: 'Kota Malang', value: 83.1, note: 'Kota di Jawa Timur', current: false },
                        { name: 'Jawa Timur', value: 75.3, note: 'Provinsi Jawa Timur', current: false },
                        { name: 'Nasional', value: 75.0, note: 'Nasional', current: false },
                        { name: 'Kab. Banyuwangi', value: parseFloat(curVal.replace(',', '.')) || 73.79, note: 'Kabupaten terpilih', current: true },
                        { name: 'Kab. Jember', value: 72.9, note: 'Kabupaten pembanding', current: false },
                        { name: 'Kab. Situbondo', value: 71.8, note: 'Kabupaten pembanding', current: false }
                    ],
                    heatTitle: 'Peta panas dimensi IPM',
                    heatDesc: 'Tren dimensi kesehatan, pendidikan, dan standar hidup selama lima tahun terakhir.',
                    heatRows: [
                        { name: 'UHH (tahun)', values: [72.1, 72.2, 72.3, 72.4, 72.48] },
                        { name: 'HLS (tahun)', values: [13.1, 13.2, 13.3, 13.35, 13.42] },
                        { name: 'RLS (tahun)', values: [7.2, 7.3, 7.5, 7.6, 7.68] }
                    ],
                    annualSeries: [
                        { year: 2015, value: 68.11, change: null },
                        { year: 2016, value: 68.63, change: '+0,52' },
                        { year: 2017, value: 69.13, change: '+0,50' },
                        { year: 2018, value: 69.64, change: '+0,51' },
                        { year: 2019, value: 70.06, change: '+0,42' },
                        { year: 2020, value: 70.62, change: '+0,56' },
                        { year: 2021, value: 71.26, change: '+0,64' },
                        { year: 2022, value: 72.45, change: '+1,19' },
                        { year: 2023, value: 73.08, change: '+0,63' },
                        { year: 2024, value: 73.79, change: '+0,71' }
                    ]
                },
                // 6: Realisasi Investasi
                {
                    name: 'Realisasi Investasi',
                    subtitle: 'Nilai realisasi penanaman modal PMA dan PMDN',
                    definition: 'Realisasi investasi menunjukkan nilai penanaman modal yang telah direalisasikan. Nilai nominal perlu dilihat bersama pertumbuhan investasi, komposisi PMA/PMDN, sektor usaha, dan sebaran proyek.',
                    metrics: [
                        { label: 'Nilai realisasi nominal', value: curVal + ' Triliun', note: `Tahun ${curYear} Kabupaten Banyuwangi` },
                        { label: 'Pertumbuhan investasi', value: '12,42%', note: `Tahun ${curYear}` },
                        { label: 'Perubahan pertumbuhan', value: '-1,78 pp', note: 'Perubahan laju investasi terhadap tahun sebelumnya' }
                    ],
                    componentTitle: 'Struktur realisasi investasi',
                    componentDesc: 'Komposisi investasi berdasarkan asal modal dan sektor tujuan penanaman modal.',
                    components: [
                        { name: 'PMDN (Modal Dalam Negeri)', value: 63.0, unit: '%', context: 'Pangsa penanaman modal investor domestik' },
                        { name: 'PMA (Modal Asing)', value: 37.0, unit: '%', context: 'Pangsa penanaman modal investor asing' },
                        { name: 'Industri Pengolahan', value: 28.4, unit: '% pangsa', context: 'Investasi manufaktur dan pabrikasi' },
                        { name: 'Perdagangan & Reparasi', value: 19.6, unit: '% pangsa', context: 'Perdagangan besar dan ritel modern' },
                        { name: 'Akomodasi & Makan Minum', value: 16.3, unit: '% pangsa', context: 'Hotel, resort dan kuliner pariwisata' },
                        { name: 'Pertanian & Perikanan', value: 14.2, unit: '% pangsa', context: 'Agroindustri, cold storage & tambak' },
                        { name: 'Konstruksi & Properti', value: 11.5, unit: '% pangsa', context: 'Bangunan fisik dan kawasan industri' },
                        { name: 'Sektor Lainnya', value: 10.0, unit: '% pangsa', context: 'Energi terbarukan, logistik & jasa' }
                    ],
                    comparisonTitle: 'Pemetaan investasi kecamatan',
                    comparisonDesc: 'Sebaran realisasi investasi dan potensi wilayah di Kabupaten Banyuwangi.',
                    peers: [
                        { name: 'Pesanggaran', value: 7.1, note: 'Potensi: Pertambangan & wisata bahari', current: false },
                        { name: 'Banyuwangi (Kota)', value: 6.2, note: 'Pusat pemerintahan & komersial', current: true },
                        { name: 'Muncar', value: 5.7, note: 'Potensi: Perikanan & agroindustri', current: false },
                        { name: 'Rogojampi', value: 4.8, note: 'Potensi: Transportasi & perdagangan', current: false },
                        { name: 'Glagah', value: 4.6, note: 'Potensi: Pariwisata & hospitality', current: false },
                        { name: 'Wongsorejo', value: 4.2, note: 'Potensi: Pertanian & energi baru', current: false },
                        { name: 'Genteng', value: 3.9, note: 'Potensi: Perdagangan & jasa logistik', current: false },
                        { name: 'Siliragung', value: 3.7, note: 'Potensi: Pertanian & perkebunan', current: false }
                    ],
                    heatTitle: 'Komposisi investasi sektoral',
                    heatDesc: 'Perbandingan pangsa indikatif sektor investasi dalam lima tahun terakhir.',
                    heatRows: [
                        { name: 'Industri pengolahan', values: [20.4, 22.4, 23.8, 26.4, 28.4] },
                        { name: 'Perdagangan & reparasi', values: [14.1, 15.5, 16.4, 18.2, 19.6] },
                        { name: 'Akomodasi & makan minum', values: [11.7, 12.9, 13.7, 15.1, 16.3] },
                        { name: 'Pertanian & perikanan', values: [10.2, 11.2, 11.9, 13.2, 14.2] },
                        { name: 'Konstruksi & properti', values: [8.3, 9.1, 9.7, 10.7, 11.5] },
                        { name: 'Sektor lainnya', values: [7.2, 7.9, 8.4, 9.3, 10.0] }
                    ],
                    annualSeries: [
                        { year: 2015, value: 1.80, change: null },
                        { year: 2016, value: 2.10, change: '+0,30' },
                        { year: 2017, value: 2.50, change: '+0,40' },
                        { year: 2018, value: 2.90, change: '+0,40' },
                        { year: 2019, value: 3.10, change: '+0,20' },
                        { year: 2020, value: 2.80, change: '-0,30' },
                        { year: 2021, value: 3.20, change: '+0,40' },
                        { year: 2022, value: 3.50, change: '+0,30' },
                        { year: 2023, value: 3.66, change: '+0,16' },
                        { year: 2024, value: 3.82, change: '+0,16' }
                    ]
                },
                // 7: Pertumbuhan Investasi
                {
                    name: 'Pertumbuhan Investasi',
                    subtitle: 'Perubahan tahunan nilai investasi yang direalisasikan',
                    definition: 'Pertumbuhan investasi menunjukkan perubahan laju realisasi penanaman modal dari tahun ke tahun. Interpretasinya perlu dilengkapi nilai nominal investasi, komposisi PMA/PMDN, sektor tujuan, dan persebaran proyek.',
                    metrics: [
                        { label: 'Perubahan tahunan', value: '-1,78 pp', note: 'Perubahan laju pertumbuhan investasi' },
                        { label: 'Pertumbuhan ekonomi', value: '5,18%', note: `Tahun ${curYear}` },
                        { label: 'Pertumbuhan tertinggi', value: '14,20%', note: 'Terjadi pada tahun 2023' }
                    ],
                    componentTitle: 'Pertumbuhan investasi menurut sektor',
                    componentDesc: 'Perkembangan laju investasi sektoral memberi konteks arah alokasi modal.',
                    components: [
                        { name: 'Akomodasi & Makan Minum', value: 16.8, unit: '%', context: 'Aktivitas investasi sektor pariwisata' },
                        { name: 'Industri Pengolahan', value: 14.6, unit: '%', context: 'Investasi manufaktur dan hilirisasi' },
                        { name: 'Perdagangan', value: 12.9, unit: '%', context: 'Perdagangan besar dan eceran' },
                        { name: 'Pertanian & Perikanan', value: 11.7, unit: '%', context: 'Agroindustri dan perikanan' },
                        { name: 'Konstruksi', value: 9.8, unit: '%', context: 'Bangunan dan infrastruktur' },
                        { name: 'Transportasi', value: 8.9, unit: '%', context: 'Transportasi dan pergudangan' }
                    ],
                    comparisonTitle: 'Pertumbuhan investasi menurut wilayah',
                    comparisonDesc: 'Perbandingan indeks pertumbuhan realisasi investasi antarkecamatan.',
                    peers: [
                        { name: 'Pesanggaran', value: 14.9, note: 'Indeks pertumbuhan Â· Pertambangan & wisata', current: false },
                        { name: 'Banyuwangi (Kota)', value: 13.0, note: 'Indeks pertumbuhan Â· Perdagangan & jasa', current: true },
                        { name: 'Muncar', value: 12.0, note: 'Indeks pertumbuhan Â· Perikanan', current: false },
                        { name: 'Rogojampi', value: 10.1, note: 'Indeks pertumbuhan Â· Perdagangan', current: false },
                        { name: 'Glagah', value: 9.7, note: 'Indeks pertumbuhan Â· Pariwisata', current: false }
                    ],
                    heatTitle: 'Peta panas pertumbuhan investasi',
                    heatDesc: 'Tren pertumbuhan investasi sektoral pada lima tahun terakhir.',
                    heatRows: [
                        { name: 'Akomodasi & Makan Minum', values: [7.1, 9.2, 11.1, 13.8, 16.8] },
                        { name: 'Industri Pengolahan', values: [6.1, 8.0, 9.6, 12.0, 14.6] },
                        { name: 'Perdagangan', values: [5.4, 7.1, 8.5, 10.6, 12.9] },
                        { name: 'Pertanian & Perikanan', values: [4.9, 6.4, 7.7, 9.6, 11.7] },
                        { name: 'Konstruksi', values: [4.1, 5.4, 6.5, 8.0, 9.8] },
                        { name: 'Transportasi', values: [3.7, 4.9, 5.9, 7.3, 8.9] }
                    ],
                    annualSeries: [
                        { year: 2015, value: 6.10, change: null },
                        { year: 2016, value: 7.20, change: '+1,10' },
                        { year: 2017, value: 8.00, change: '+0,80' },
                        { year: 2018, value: 8.80, change: '+0,80' },
                        { year: 2019, value: 9.40, change: '+0,60' },
                        { year: 2020, value: -2.00, change: '-11,40' },
                        { year: 2021, value: 4.20, change: '+6,20' },
                        { year: 2022, value: 8.80, change: '+4,60' },
                        { year: 2023, value: 14.20, change: '+5,40' },
                        { year: 2024, value: 12.42, change: '-1,78' }
                    ]
                }
            ];

            return configs[idx] || configs[0];
        }

        function renderDetailTrendChart(series, years, unit) {
            if (!series || !series.length || !years || !years.length) {
                return '<div style="padding:24px; text-align:center; color:#94a3b8; font-size:11px;">Belum ada observasi tren untuk divisualisasikan.</div>';
            }
            const allVals = series.flatMap(s => s.values).filter(v => typeof v === 'number' && !isNaN(v));
            if (!allVals.length) {
                return '<div style="padding:24px; text-align:center; color:#94a3b8; font-size:11px;">Belum ada data numerik untuk grafik.</div>';
            }
            const rawMin = Math.min(...allVals);
            const rawMax = Math.max(...allVals);
            const padding = (rawMax - rawMin || Math.max(Math.abs(rawMax) * 0.08, 1)) * 0.16;
            const min = rawMin - padding;
            const max = rawMax + padding;
            const range = (max - min) || 1;
            const left = 52, right = 688, top = 22, bottom = 158;

            const x = i => left + (years.length <= 1 ? 0 : (i / (years.length - 1)) * (right - left));
            const y = v => bottom - ((v - min) / range) * (bottom - top);

            let gridSvg = '';
            [0, 0.33, 0.66, 1].forEach(frac => {
                const tick = min + frac * range;
                const py = y(tick);
                gridSvg += `
                    <g>
                        <line x1="${left}" x2="${right}" y1="${py}" y2="${py}" class="detail-gridline" />
                        <text x="44" y="${py + 3}" text-anchor="end" class="detail-axis-text">${tick.toFixed(1)}${unit}</text>
                    </g>
                `;
            });

            let seriesSvg = '';
            series.forEach(item => {
                const pts = item.values.map((pt, i) => `${x(i)},${y(pt)}`).join(' ');
                const lastIdx = item.values.length - 1;
                let pointsSvg = '';
                item.values.forEach((pt, i) => {
                    const r = (i === lastIdx) ? 4.5 : 2.5;
                    pointsSvg += `
                        <circle cx="${x(i)}" cy="${y(pt)}" r="${r}" class="detail-point detail-point-${item.style || 'primary'}">
                            <title>${item.name}: ${typeof pt === 'number' ? pt.toFixed(2) : pt}${unit}</title>
                        </circle>
                    `;
                });
                seriesSvg += `
                    <g>
                        <polyline points="${pts}" class="detail-line detail-line-${item.style || 'primary'}" />
                        ${pointsSvg}
                    </g>
                `;
            });

            const labelIndices = [...new Set([0, Math.round((years.length - 1) / 3), Math.round(((years.length - 1) * 2) / 3), years.length - 1])];
            let xAxisSvg = '';
            labelIndices.forEach(i => {
                if (years[i] !== undefined) {
                    xAxisSvg += `<text x="${x(i)}" y="184" text-anchor="middle" class="detail-axis-text">${years[i]}</text>`;
                }
            });

            let legendHtml = '<div class="detail-chart-legend">';
            series.forEach(item => {
                legendHtml += `<span><i class="legend-dot ${item.style || 'primary'}"></i>${item.name}</span>`;
            });
            legendHtml += '</div>';

            return `
                <div class="detail-trend-wrap">
                    <svg class="detail-trend-svg" viewBox="0 0 720 198" role="img" aria-label="Grafik tren historis indikator">
                        ${gridSvg}
                        ${seriesSvg}
                        ${xAxisSvg}
                    </svg>
                    ${legendHtml}
                </div>
            `;
        }

        function openKpiDetailModal(idx) {
            if (!modalBox || !modalOverlay) return;
            const card = document.querySelector(`.kpi-card[data-index="${idx}"]`);
            const cfg = getKpiDetailConfig(idx);
            if (!cfg) return;

            const curVal = (card && card.dataset.value) ? card.dataset.value : (cfg.curVal || (card && card.querySelector('.kpi-value strong') ? card.querySelector('.kpi-value strong').textContent.trim() : ''));
            const curUnit = (card && card.dataset.unit) ? card.dataset.unit : (cfg.curUnit || (card && card.querySelector('.kpi-value span') ? card.querySelector('.kpi-value span').textContent.trim() : ''));
            const curStatus = (card && card.dataset.status) ? card.dataset.status : (cfg.curStatus || (card && card.querySelector('.badge') ? card.querySelector('.badge').textContent.trim() : 'Meningkat'));
            const curDelta = (card && card.dataset.delta) ? card.dataset.delta : (cfg.curDelta || (card && card.querySelector('.kpi-bottom span') ? card.querySelector('.kpi-bottom span').textContent.replace(/[â†—â†˜]/g, '').trim() : ''));
            const badgeClass = card && card.querySelector('.badge') ? card.querySelector('.badge').className.replace('badge', '').trim() : 'success';
            const curYear = (card && card.dataset.year) ? card.dataset.year : (cfg.year || (card && card.querySelector('.kpi-period') && card.querySelector('.kpi-period').textContent.match(/\b(20\d\d)\b/) ? card.querySelector('.kpi-period').textContent.match(/\b(20\d\d)\b/)[1] : (filterYear && filterYear.value && filterYear.value !== 'all' ? filterYear.value : '2024')));
            const cleanSite = (typeof SITE_URL !== 'undefined' ? SITE_URL : (typeof BASE_URL !== 'undefined' ? BASE_URL : '')).replace(/\/+$/, '');

            // Calculate max for component bars
            const maxCompVal = Math.max(...cfg.components.map(c => Math.abs(c.value)), 1);
            const maxPeerVal = Math.max(...cfg.peers.map(p => Math.abs(p.value)), 1);

            let compRowsHtml = '';
            cfg.components.forEach(c => {
                const pct = Math.max(2, Math.min(100, (Math.abs(c.value) / maxCompVal) * 100));
                compRowsHtml += `
                    <div class="detail-component-row">
                        <div class="detail-component-name">
                            <strong>${c.name}</strong>
                            <small>${c.context}${c.share ? ' Â· Pangsa ' + c.share + '%' : ''}</small>
                        </div>
                        <div class="detail-component-track">
                            <i style="width: ${pct}%"></i>
                        </div>
                        <b>${c.value} <small>${c.unit}</small></b>
                    </div>
                `;
            });

            let compTableRowsHtml = '';
            cfg.components.forEach(c => {
                compTableRowsHtml += `
                    <tr class="comp-table-row" data-search="${c.name.toLowerCase()} ${c.context.toLowerCase()}">
                        <td><strong>${c.name}</strong></td>
                        <td class="numeric">${c.value}</td>
                        <td>${c.unit}</td>
                        <td class="numeric">${c.share ? c.share + '%' : 'â€”'}</td>
                        <td class="numeric">${c.benchmark ? c.benchmark + '%' : 'â€”'}</td>
                        <td>${c.context}</td>
                    </tr>
                `;
            });

            let peerBarsHtml = '';
            cfg.peers.forEach((p, i) => {
                const pct = Math.max(2, Math.min(100, (Math.abs(p.value) / maxPeerVal) * 100));
                peerBarsHtml += `
                    <div class="detail-rank-row ${p.current ? 'selected' : ''}">
                        <div class="detail-rank-label">
                            <span>${i + 1}</span>
                            <img src="${getLogoPemdaUrl(p.name)}" alt="" class="region-table-logo">
                            <strong>${p.name}</strong>
                            <small>${p.note}</small>
                        </div>
                        <div class="detail-rank-track">
                            <i style="width: ${pct}%"></i>
                        </div>
                        <b>${p.value}</b>
                    </div>
                `;
            });

            let peerTableRowsHtml = '';
            cfg.peers.forEach(p => {
                peerTableRowsHtml += `
                    <tr class="${p.current ? 'selected-row' : ''}">
                        <td><img src="${getLogoPemdaUrl(p.name)}" alt="" class="region-table-logo"><strong>${p.name}</strong></td>
                        <td class="numeric">${p.value}</td>
                        <td>${p.note}</td>
                        <td>${p.current ? '<span class="badge info">Wilayah Dipilih</span>' : '<span class="table-muted">Pembanding</span>'}</td>
                    </tr>
                `;
            });

            // Heatmap columns
            const heatCols = cfg.heatColumns || ['2020', '2021', '2022', '2023', '2024'];
            let heatColsTh = '';
            heatCols.forEach(col => { heatColsTh += `<th>${col}</th>`; });

            // Flatten all heat values for max
            const allHeatVals = cfg.heatRows.flatMap(r => r.values).map(Math.abs);
            const maxHeat = Math.max(...allHeatVals, 1);

            let heatRowsHtml = '';
            cfg.heatRows.forEach(r => {
                let tds = '';
                r.values.forEach(v => {
                    const ratio = Math.min(0.65, Math.max(0.08, (Math.abs(v) / maxHeat) * 0.65));
                    tds += `<td style="background: rgba(2, 132, 199, ${ratio});">${v}</td>`;
                });
                heatRowsHtml += `
                    <tr>
                        <th>${r.name}</th>
                        ${tds}
                    </tr>
                `;
            });

            let histTableRowsHtml = '';
            cfg.annualSeries.forEach(s => {
                histTableRowsHtml += `
                    <tr class="hist-row">
                        <td><strong>${s.year}</strong></td>
                        <td class="numeric">${s.value}</td>
                        <td>${cfg.components[0] ? cfg.components[0].unit : '%'}</td>
                        <td class="numeric">${s.change === null ? 'â€”' : s.change}</td>
                    </tr>
                `;
            });

            // 1. DETAIL TREND CHART & INSIGHT PREPARATION
            const defaultReadings = [
                'Bandingkan pertumbuhan antartahun dan wilayah, lalu telusuri sektor yang menjadi sumber perubahan.',
                'Nilai rata-rata per penduduk tidak menggambarkan distribusi pendapatan dan belum memperhitungkan perubahan harga.',
                'Inflasi yang lebih rendah berarti laju kenaikan harga melambat; bukan berarti tingkat harga turun.',
                'TPT yang turun perlu dibaca bersama partisipasi angkatan kerja dan kualitas pekerjaan.',
                'Penurunan persentase kemiskinan perlu diverifikasi bersama kedalaman, keparahan, dan jumlah penduduk miskin.',
                'Kenaikan IPM perlu dilihat dari kontribusi dimensi kesehatan, pendidikan, dan standar hidup.',
                'Bedakan perubahan nilai nominal realisasi investasi dengan laju pertumbuhan investasi.',
                'Perbandingan pertumbuhan investasi dan ekonomi tidak dengan sendirinya membuktikan hubungan sebab-akibat.'
            ];
            function buildDynamicInsightText(idx, cfg, curVal, curUnit, curDelta, curStatus, curYear) {
                const cleanCurVal = String(curVal).replace(/^Rp\s*/i, '').trim();
                // If cfg.insight exists AND already matches curYear AND curVal, use cfg.insight
                if (cfg.insight && cfg.insight.includes(String(curYear)) && (cfg.insight.includes(cleanCurVal) || (cfg.curVal && cfg.insight.includes(String(cfg.curVal))))) {
                    return cfg.insight;
                }

                const isNeg = (curDelta || '').includes('-') || curStatus === 'Melambat' || curStatus === 'Menurun';
                const hasDelta = !!curDelta;

                switch (idx) {
                    case 0: { // Pertumbuhan Ekonomi
                        const trend = isNeg ? 'melambat' : (curStatus === 'Stabil' ? 'stabil' : 'meningkat');
                        const topSec = (cfg.breakdown && cfg.breakdown.length > 0) ? cfg.breakdown[0] : ((cfg.components && cfg.components.length > 0) ? cfg.components[0] : null);
                        const secPhrase = topSec ? ` Lapangan usaha ${topSec.name} mencatatkan laju pertumbuhan tertinggi sebesar ${topSec.value}%${topSec.note ? ' (' + topSec.note + ')' : ''}.` : '';
                        const deltaPhrase = hasDelta ? `, ${trend} sebesar ${curDelta} dibandingkan periode sebelumnya` : '';
                        return `Pertumbuhan ekonomi Kabupaten Banyuwangi pada tahun ${curYear} tercatat sebesar ${curVal}%${deltaPhrase}.${secPhrase}`;
                    }
                    case 1: { // PDRB per Kapita
                        const trend = isNeg ? 'menurun' : 'meningkat';
                        const deltaPhrase = hasDelta ? `, ${trend} sebesar ${curDelta} dibandingkan tahun sebelumnya` : '';
                        const adhb = (cfg.metrics && cfg.metrics[0]) ? cfg.metrics[0].value : 'Rp 108,92 Triliun';
                        const pop = (cfg.metrics && cfg.metrics[2]) ? cfg.metrics[2].value : '1.735.420 Jiwa';
                        return `PDRB per kapita atas dasar harga berlaku (ADHB) Kabupaten Banyuwangi pada tahun ${curYear} tercatat mencapai Rp ${cleanCurVal} Juta${deltaPhrase}. Capaian ini didukung oleh total PDRB ADHB ${adhb} dengan jumlah penduduk tercatat sebanyak ${pop}.`;
                    }
                    case 2: { // Tingkat Inflasi
                        const trend = isNeg ? 'terkendali dengan penurunan' : 'mengalami pergerakan';
                        const deltaPhrase = hasDelta ? `, ${trend} sebesar ${curDelta} dari periode sebelumnya` : '';
                        const mtm = (cfg.metrics && cfg.metrics[0]) ? cfg.metrics[0].value : '0,19%';
                        return `Tingkat inflasi tahunan (yoy) Kabupaten Banyuwangi pada tahun ${curYear} tercatat sebesar ${curVal} ${curUnit}${deltaPhrase}. Kondisi stabilitas harga konsumen tetap berada dalam rentang sasaran target (2,5 Â± 1%), didukung oleh dinamika inflasi bulanan (mtm) ${mtm}.`;
                    }
                    case 3: { // Pengangguran Terbuka
                        const trend = isNeg ? 'membaik dengan penurunan' : 'mengalami pergerakan';
                        const deltaPhrase = hasDelta ? `, ${trend} sebesar ${curDelta} dari periode sebelumnya` : '';
                        const tpak = (cfg.metrics && cfg.metrics[0]) ? cfg.metrics[0].value : '74,91%';
                        const work = (cfg.metrics && cfg.metrics[1]) ? cfg.metrics[1].value : '964,8 Ribu';
                        return `Tingkat Pengangguran Terbuka (TPT) Kabupaten Banyuwangi pada tahun ${curYear} berada pada angka ${curVal}%${deltaPhrase}. Kinerja penyerapan tenaga kerja didukung oleh Tingkat Partisipasi Angkatan Kerja (TPAK) sebesar ${tpak} dengan total penduduk bekerja mencapai ${work} jiwa.`;
                    }
                    case 4: { // Tingkat Kemiskinan
                        const trend = isNeg ? 'berkurang/menurun' : 'mengalami pergerakan';
                        const deltaPhrase = hasDelta ? `, ${trend} sebesar ${curDelta} dibandingkan tahun sebelumnya` : '';
                        const ppl = (cfg.metrics && cfg.metrics[0]) ? cfg.metrics[0].value : '106,6 Ribu';
                        const gk = (cfg.metrics && cfg.metrics[1]) ? cfg.metrics[1].value : 'Rp 470.713';
                        return `Persentase penduduk miskin (P0) di Kabupaten Banyuwangi pada tahun ${curYear} tercatat sebesar ${curVal}%${deltaPhrase}. Jumlah warga miskin tercatat sebanyak ${ppl} jiwa pada batas Garis Kemiskinan ${gk}/kapita/bulan.`;
                    }
                    case 5: { // IPM
                        const deltaPhrase = hasDelta ? `, meningkat sebesar ${curDelta} dari tahun sebelumnya` : '';
                        const numVal = parseFloat(String(cleanCurVal).replace(',', '.'));
                        const tier = numVal >= 80 ? 'Sangat Tinggi' : (numVal >= 70 ? 'Tinggi' : 'Sedang');
                        const uhh = (cfg.metrics && cfg.metrics[1]) ? cfg.metrics[1].value : '71,38 Tahun';
                        const rls = (cfg.metrics && cfg.metrics[2]) ? cfg.metrics[2].value : '7,78 Tahun';
                        return `Indeks Pembangunan Manusia (IPM) Kabupaten Banyuwangi pada tahun ${curYear} mencapai ${cleanCurVal} Poin${deltaPhrase} (status '${tier}'). Capaian ini ditopang oleh Umur Harapan Hidup (UHH) ${uhh} dan Rata-rata Lama Sekolah (RLS) ${rls}.`;
                    }
                    case 6: { // Realisasi Investasi
                        const deltaPhrase = hasDelta ? ` (${curDelta} dibandingkan periode lalu)` : '';
                        const pmdn = (cfg.metrics && cfg.metrics[0]) ? cfg.metrics[0].value : 'Rp 3.240 Miliar';
                        const pma = (cfg.metrics && cfg.metrics[1]) ? cfg.metrics[1].value : 'Rp 110 Miliar';
                        return `Total realisasi penanaman modal fisik di Kabupaten Banyuwangi pada tahun ${curYear} tercatat sebesar ${curVal.startsWith('Rp') ? curVal : 'Rp ' + curVal} ${curUnit}${deltaPhrase}. Realisasi modal didominasi oleh investasi dalam negeri (PMDN) sebesar ${pmdn} dan penanaman modal asing (PMA) sebesar ${pma}.`;
                    }
                    case 7: { // Pertumbuhan Investasi
                        const numVal = parseFloat(String(cleanCurVal).replace(',', '.'));
                        const note = numVal >= 0
                            ? 'mencerminkan ekspansi positif minat pemodal dan perluasan skala usaha di daerah'
                            : 'mencerminkan dinamika siklus penyelesaian proyek modal fisik dan evaluasi perizinan usaha di daerah';
                        return `Laju pertumbuhan investasi tahunan Kabupaten Banyuwangi pada tahun ${curYear} tercatat sebesar ${curVal}% (status ${curStatus}). Pergerakan laju investasi ini ${note}.`;
                    }
                    default:
                        return cfg.insight || `Capaian indikator ${cfg.name} Kabupaten Banyuwangi pada tahun ${curYear} tercatat sebesar ${curVal} ${curUnit}. Data ini mencerminkan stabilitas indikator makro daerah yang terverifikasi resmi.`;
                }
            }
            const insightText = buildDynamicInsightText(idx, cfg, curVal, curUnit, curDelta, curStatus, curYear);
            const readingText = cfg.reading || defaultReadings[idx] || 'Bandingkan data dengan periode sebelumnya dan wilayah pembanding.';
            const chartTitle = cfg.chartTitle || `Tren Historis ${cfg.name}`;
            const chartUnit = cfg.chartUnit || (cfg.components && cfg.components[0] ? cfg.components[0].unit : '%');
            const years = (cfg.annualSeries && cfg.annualSeries.length) ? cfg.annualSeries.map(s => s.year) : [2015, 2016, 2017, 2018, 2019, 2020, 2021, 2022, 2023, 2024];
            const chartSeries = (cfg.chartSeries && cfg.chartSeries.length) ? cfg.chartSeries : [
                { name: `Kabupaten Banyuwangi (${cfg.name})`, values: cfg.annualSeries ? cfg.annualSeries.map(s => s.value) : [], style: 'primary' }
            ];
            const trendChartHtml = renderDetailTrendChart(chartSeries, years, chartUnit);

            // 2. BREAKDOWN CARD (e.g. for Pertumbuhan Ekonomi or Investasi)
            let breakdownCardHtml = '';
            if (cfg.breakdown && cfg.breakdown.length > 0) {
                const maxBreakdown = Math.max(...cfg.breakdown.map(b => Math.abs(b.value)), 1);
                let breakdownRowsHtml = '';
                cfg.breakdown.forEach(item => {
                    const pct = Math.max(3, Math.min(100, (Math.abs(item.value) / maxBreakdown) * 100));
                    breakdownRowsHtml += `
                        <div class="detail-sector-row">
                            <div>
                                <strong>${item.name}</strong>
                                <small>${item.note}</small>
                            </div>
                            <div class="detail-sector-track">
                                <i style="width: ${pct}%;"></i>
                            </div>
                            <b>${typeof item.value === 'number' ? item.value.toFixed(2) : item.value}%</b>
                        </div>
                    `;
                });
                breakdownCardHtml = `
                    <article class="card detail-breakdown-card">
                        <div class="card-head">
                            <div>
                                <span class="eyebrow" style="font-size:10px; font-weight:700; color:#0284c7;">${idx === 6 ? 'RINCIAN PERMODALAN' : 'LAPANGAN USAHA'}</span>
                                <h3>${idx === 6 ? 'Komposisi Modal & Realisasi Kuartal' : 'Pertumbuhan Sektor Tertinggi'}</h3>
                                <p>${idx === 6 ? 'Komposisi modal dan laporan triwulanan tahun ' + curYear : 'Urutan berdasarkan laju pertumbuhan pada tahun ' + curYear + '; bukan kontribusi terhadap pertumbuhan PDRB.'}</p>
                            </div>
                        </div>
                        <div class="detail-sector-bars">
                            ${breakdownRowsHtml}
                        </div>
                    </article>
                `;
            }

            // 3. RELATED SECTION (3 SUPPORTING INDICATORS)
            const relatedList = (cfg.related && cfg.related.length) ? cfg.related : [
                { label: 'Indikator Makro Terkait', value: `${curVal} ${curUnit}`, note: `Tahun referensi aktif ${curYear}`, available: true },
                { label: 'Pembanding Wilayah Jawa Timur', value: '38 Daerah', note: 'Data pembanding kabupaten/kota', available: true },
                { label: 'Observasi Seri Historis', value: `${years.length} Periode`, note: 'Publikasi resmi BPS / OPD terkait', available: true }
            ];
            let relatedCardsHtml = '';
            relatedList.forEach(item => {
                const isAvail = (item.available !== false);
                relatedCardsHtml += `
                    <article class="detail-related-card ${isAvail ? '' : 'unavailable'}">
                        <div>
                            <span class="related-status">
                                <i class="${isAvail ? 'available' : ''}"></i>
                                ${isAvail ? 'Tersedia pada basis data resmi' : 'Belum tersedia'}
                            </span>
                            <strong>${item.label}</strong>
                        </div>
                        <b>${item.value}</b>
                        <p>${item.note}</p>
                    </article>
                `;
            });
            const relatedSectionHtml = `
                <section class="detail-related-section">
                    <div class="card-head">
                        <div>
                            <span class="eyebrow" style="font-size:10px; font-weight:700; color:#0284c7;">INDIKATOR TURUNAN &amp; TERKAIT</span>
                            <h3>Informasi Pendukung</h3>
                            <p>Komponen yang membantu menjelaskan dinamika indikator utama secara komprehensif.</p>
                        </div>
                    </div>
                    <div class="detail-related-grid">
                        ${relatedCardsHtml}
                    </div>
                </section>
            `;

            // 4. COVERAGE NOTE
            const coverageText = cfg.coverage || 'Data bersumber dari publikasi statistik resmi BPS dan instansi terkait Pemerintah Kabupaten Banyuwangi.';
            const coverageNoteHtml = `
                <div class="detail-coverage-note">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                    <div>
                        <strong>Cakupan dan Keterbatasan Data</strong>
                        <p>${coverageText}</p>
                        <small>Sumber resmi: Publikasi BPS Kabupaten Banyuwangi / BPS Provinsi Jawa Timur / DPMPTSP Kabupaten Banyuwangi.</small>
                    </div>
                </div>
            `;

            modalBox.innerHTML = `
                <header class="indicator-detail-header">
                    <div>
                        <span class="eyebrow" style="font-size:11px; font-weight:700; color:#0284c7; letter-spacing:0.04em;">DETAIL INDIKATOR EKONOMI</span>
                        <h2 id="indicator-detail-title">${cfg.name}</h2>
                        <p>${cfg.subtitle}</p>
                    </div>
                    <div class="detail-header-actions">
                        <span class="badge simulation" style="background:#e0f2fe; color:#0369a1; border-color:#bae6fd; font-weight:700;">DATA RESMI DAERAH</span>
                        <button type="button" class="icon-button" id="btn-close-detail-modal" aria-label="Tutup detail" style="width:34px; height:34px; border-radius:8px; border:1px solid #cbd5e1; background:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </div>
                </header>

                <div class="detail-context">
                    <span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> Tahun ${cfg.year || curYear} â€¢ Tahunan</span>
                    <span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> Kabupaten Banyuwangi</span>
                    <span><i class="pulse"></i> Basis Data Resmi Terintegrasi</span>
                </div>

                <div class="detail-metric-overview">
                    <div class="detail-primary-metric">
                        <span>Nilai indikator utama</span>
                        <strong>${curVal} <small>${curUnit}</small></strong>
                        <span class="badge ${badgeClass}">${curStatus}</span>
                        <p>${cfg.definition}</p>
                    </div>
                    ${cfg.metrics.map(m => `
                        <div class="detail-secondary-metric">
                            <span>${m.label}</span>
                            <strong>${m.value}</strong>
                            <small>${m.note}</small>
                        </div>
                    `).join('')}
                </div>

                <!-- BODY GRID: TREN HISTORIS SVG LINE CHART & ECONOMIC INSIGHT -->
                <div class="detail-body-grid">
                    <article class="card detail-chart-card">
                        <div class="card-head">
                            <div>
                                <span class="eyebrow" style="font-size:10px; font-weight:700; color:#0284c7;">TREN HISTORIS</span>
                                <h3>${chartTitle}</h3>
                                <p>Perkembangan ${years[0] || '2015'} hingga ${curYear}</p>
                            </div>
                            <span class="badge info">${curYear}</span>
                        </div>
                        ${trendChartHtml}
                    </article>

                    <article class="card detail-insight-card">
                        <div class="card-head">
                            <div>
                                <span class="eyebrow" style="font-size:10px; font-weight:700; color:#0284c7;">ECONOMIC INSIGHT</span>
                                <h3>Sorotan Indikator</h3>
                            </div>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:#0d9488;"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                        </div>
                        <p class="detail-insight-text">${insightText}</p>
                        <div class="detail-definition">
                            <strong>Cara Membaca</strong>
                            <span>${readingText}</span>
                        </div>
                    </article>
                </div>

                <!-- BREAKDOWN CARD (SEKTORAL / KUARTAL) -->
                ${breakdownCardHtml}

                <!-- DEEP DIVE Â· DATA EXPLORER -->
                <section class="detail-explorer">
                    <div class="detail-explorer-heading">
                        <div>
                            <span class="eyebrow" style="font-size:10.5px; font-weight:700; color:#0284c7;">DEEP DIVE Â· DATA EXPLORER</span>
                            <h3>Eksplorasi Lanjutan Indikator</h3>
                            <p>Telusuri rincian komponen pembentuk, posisi relatif antarwilayah Jawa Timur, dan observasi seri historis.</p>
                        </div>
                        <span class="data-tag">SI-DAPEDA</span>
                    </div>

                    <div class="detail-explorer-tabs" role="tablist">
                        <button type="button" class="active" id="btn-tab-components" role="tab">01 Â· Struktur &amp; Komponen</button>
                        <button type="button" id="btn-tab-benchmark" role="tab">02 Â· Benchmarking Wilayah</button>
                        <button type="button" id="btn-tab-history" role="tab">03 Â· Data Historis</button>
                    </div>

                    <!-- TAB 1: STRUKTUR & KOMPONEN -->
                    <div id="tab-panel-components">
                        <div class="detail-explorer-panel-head">
                            <div>
                                <span class="eyebrow">KOMPOSISI / BREAKDOWN</span>
                                <h4>${cfg.componentTitle}</h4>
                                <p>${cfg.componentDesc}</p>
                            </div>
                        </div>

                        <div class="detail-component-grid">
                            <div class="detail-component-chart">
                                <div class="detail-subhead">
                                    <strong>Distribusi Nilai Rincian Komponen</strong>
                                    <small>Periode Tahun ${curYear}</small>
                                </div>
                                ${compRowsHtml}
                            </div>
                            <aside class="detail-component-aside">
                                <span class="eyebrow">PANDUAN ANALISIS</span>
                                <h4>Membaca Kualitas Indikator</h4>
                                <p>Periksa komponen dengan bobot kontribusi terbesar, laju perubahan, dan arah pergerakannya terhadap agregat daerah. Perhatikan bahwa nilai pangsa, indeks, dan laju pertumbuhan memiliki interpretasi yang berbeda.</p>
                                <div class="detail-aside-stat">
                                    <strong>${cfg.components.length}</strong>
                                    <span>rincian komponen utama</span>
                                </div>
                                <div class="detail-aside-stat">
                                    <strong>${curYear}</strong>
                                    <span>periode referensi aktif</span>
                                </div>
                            </aside>
                        </div>

                        <div class="detail-data-table-wrap">
                            <div class="detail-table-toolbar">
                                <div>
                                    <strong>Tabel Rincian Komponen</strong>
                                    <small id="comp-table-count">${cfg.components.length} baris tersedia</small>
                                </div>
                                <label class="detail-search">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                    <input type="text" id="input-search-comp" placeholder="Cari rincian...">
                                </label>
                                <button type="button" class="button small secondary" id="btn-dl-csv-comp" style="padding:5px 12px; font-size:11px; font-weight:700; cursor:pointer;">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    Ekspor CSV
                                </button>
                            </div>
                            <table class="detail-data-table">
                                <thead>
                                    <tr>
                                        <th>Komponen / Lapangan Usaha</th>
                                        <th style="text-align:right;">Nilai</th>
                                        <th>Satuan</th>
                                        <th style="text-align:right;">Pangsa</th>
                                        <th style="text-align:right;">Pembanding Jatim</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody id="comp-table-tbody">
                                    ${compTableRowsHtml}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 2: BENCHMARKING WILAYAH -->
                    <div id="tab-panel-benchmark" style="display:none;">
                        <div class="detail-explorer-panel-head">
                            <div>
                                <span class="eyebrow">REGIONAL BENCHMARKING</span>
                                <h4>${cfg.comparisonTitle}</h4>
                                <p>${cfg.comparisonDesc}</p>
                            </div>
                        </div>

                        <div class="detail-benchmark-layout">
                            <article class="detail-benchmark-chart">
                                <div class="detail-subhead">
                                    <strong>Posisi Relatif Antarwilayah</strong>
                                    <small>Periode Tahun ${curYear}</small>
                                </div>
                                <div class="detail-rank-bars">
                                    ${peerBarsHtml}
                                </div>
                            </article>
                            <aside class="detail-benchmark-summary">
                                <span class="eyebrow">RINGKASAN PEMBANDING</span>
                                <strong>Kabupaten Banyuwangi</strong>
                                <p>Posisi wilayah Banyuwangi ditandai latar biru. Bandingkan jarak capaian terhadap wilayah tetangga di Jawa Timur untuk merumuskan respon kebijakan yang tepat.</p>
                                <div class="detail-benchmark-numbers">
                                    <div>
                                        <small>Nilai Tertinggi</small>
                                        <b>${Math.max(...cfg.peers.map(p => p.value))}</b>
                                    </div>
                                    <div>
                                        <small>Nilai Terendah</small>
                                        <b>${Math.min(...cfg.peers.map(p => p.value))}</b>
                                    </div>
                                    <div>
                                        <small>Jumlah Wilayah</small>
                                        <b>${cfg.peers.length} wilayah</b>
                                    </div>
                                </div>
                            </aside>
                        </div>

                        <div class="detail-data-table-wrap">
                            <div class="detail-table-toolbar">
                                <div>
                                    <strong>Tabel Perbandingan Wilayah</strong>
                                    <small>Urutan berdasarkan capaian indikator</small>
                                </div>
                                <button type="button" class="button small secondary" id="btn-dl-csv-peer" style="padding:5px 12px; font-size:11px; font-weight:700; cursor:pointer;">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    Ekspor CSV
                                </button>
                            </div>
                            <table class="detail-data-table">
                                <thead>
                                    <tr>
                                        <th>Wilayah</th>
                                        <th style="text-align:right;">Nilai</th>
                                        <th>Keterangan</th>
                                        <th>Status Wilayah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${peerTableRowsHtml}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 3: DATA HISTORIS -->
                    <div id="tab-panel-history" style="display:none;">
                        <div class="detail-explorer-panel-head">
                            <div>
                                <span class="eyebrow">HISTORICAL DATA</span>
                                <h4>${cfg.heatTitle}</h4>
                                <p>${cfg.heatDesc}</p>
                            </div>
                        </div>

                        <div class="detail-heatmap-card">
                            <div class="detail-subhead">
                                <strong>Matriks Peta Panas Historis</strong>
                                <small>${heatCols.length} periode referensi</small>
                            </div>
                            <div class="detail-heatmap-scroll">
                                <table class="detail-heatmap">
                                    <thead>
                                        <tr>
                                            <th>Komponen</th>
                                            ${heatColsTh}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${heatRowsHtml}
                                    </tbody>
                                </table>
                            </div>
                            <div class="detail-heatmap-legend">
                                <span>Rendah</span>
                                <i></i><i></i><i></i><i></i>
                                <span>Tinggi</span>
                            </div>
                        </div>

                        <div class="detail-data-table-wrap">
                            <div class="detail-table-toolbar">
                                <div>
                                    <strong>Seri Historis Indikator Utama</strong>
                                    <small>${cfg.annualSeries.length} observasi tahunan</small>
                                </div>
                                <label class="detail-history-filter">
                                    <span>Tampilkan:</span>
                                    <select id="select-history-period" style="font-size:11px; padding:3px 8px; border-radius:6px; border:1px solid #cbd5e1; cursor:pointer;">
                                        <option value="5">5 tahun terakhir</option>
                                        <option value="7">7 tahun terakhir</option>
                                        <option value="10" selected>Seluruh periode (10 tahun)</option>
                                    </select>
                                </label>
                                <button type="button" class="button small secondary" id="btn-dl-csv-hist" style="padding:5px 12px; font-size:11px; font-weight:700; cursor:pointer;">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    Ekspor CSV
                                </button>
                            </div>
                            <table class="detail-data-table">
                                <thead>
                                    <tr>
                                        <th>Tahun</th>
                                        <th style="text-align:right;">Nilai Realisasi</th>
                                        <th>Satuan</th>
                                        <th style="text-align:right;">Perubahan Tahunan</th>
                                    </tr>
                                </thead>
                                <tbody id="hist-table-tbody">
                                    ${histTableRowsHtml}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="detail-source-method">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        <div>
                            <strong>Catatan Metodologi &amp; Sumber Data</strong>
                            <p>Data bersumber dari publikasi resmi BPS Kabupaten Banyuwangi, BPS Provinsi Jawa Timur, dan DPMPTSP Kabupaten Banyuwangi. Analisis mengacu pada konsep statistik resmi Sistem Neraca Regional dan Survei Sosial Ekonomi Nasional.</p>
                        </div>
                    </div>
                </section>

                <!-- INDIKATOR TURUNAN & TERKAIT -->
                ${relatedSectionHtml}

                <!-- CAKUPAN DAN KETERBATASAN DATA -->
                ${coverageNoteHtml}

                <footer class="detail-modal-footer">
                    <span>Periode data: 2015â€“${curYear} â€¢ Frekuensi tahunan terintegrasi</span>
                    <div>
                        <button type="button" class="button secondary" id="btn-footer-close-modal">Tutup</button>
                        <a href="${cleanSite}/analisis_ekonomi" class="button primary" style="text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                            <span>Buka Analisis Ekonomi</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                    </div>
                </footer>
            `;

            // Bind Event Listeners inside Modal
            const btnCloseX = document.getElementById('btn-close-detail-modal');
            const btnCloseFooter = document.getElementById('btn-footer-close-modal');
            if (btnCloseX) btnCloseX.addEventListener('click', closeKpiDetailModal);
            if (btnCloseFooter) btnCloseFooter.addEventListener('click', closeKpiDetailModal);

            // Tab Buttons
            const btnTabComp = document.getElementById('btn-tab-components');
            const btnTabPeer = document.getElementById('btn-tab-benchmark');
            const btnTabHist = document.getElementById('btn-tab-history');
            const panelComp = document.getElementById('tab-panel-components');
            const panelPeer = document.getElementById('tab-panel-benchmark');
            const panelHist = document.getElementById('tab-panel-history');

            function switchTab(target) {
                [btnTabComp, btnTabPeer, btnTabHist].forEach(b => b && b.classList.remove('active'));
                if (panelComp) panelComp.style.display = 'none';
                if (panelPeer) panelPeer.style.display = 'none';
                if (panelHist) panelHist.style.display = 'none';

                if (target === 'comp') {
                    if (btnTabComp) btnTabComp.classList.add('active');
                    if (panelComp) panelComp.style.display = 'block';
                } else if (target === 'peer') {
                    if (btnTabPeer) btnTabPeer.classList.add('active');
                    if (panelPeer) panelPeer.style.display = 'block';
                } else if (target === 'hist') {
                    if (btnTabHist) btnTabHist.classList.add('active');
                    if (panelHist) panelHist.style.display = 'block';
                }
            }

            if (btnTabComp) btnTabComp.addEventListener('click', () => switchTab('comp'));
            if (btnTabPeer) btnTabPeer.addEventListener('click', () => switchTab('peer'));
            if (btnTabHist) btnTabHist.addEventListener('click', () => switchTab('hist'));

            // Live Search in Tab 1
            const searchInput = document.getElementById('input-search-comp');
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    const q = this.value.toLowerCase().trim();
                    const rows = modalBox.querySelectorAll('.comp-table-row');
                    let count = 0;
                    rows.forEach(r => {
                        const str = r.getAttribute('data-search') || '';
                        if (!q || str.includes(q)) {
                            r.style.display = '';
                            count++;
                        } else {
                            r.style.display = 'none';
                        }
                    });
                    const countEl = document.getElementById('comp-table-count');
                    if (countEl) countEl.textContent = count + ' baris ditemukan';
                });
            }

            // Period Filter in Tab 3
            const histSelect = document.getElementById('select-history-period');
            if (histSelect) {
                histSelect.addEventListener('change', function () {
                    const limit = parseInt(this.value, 10) || 10;
                    const rows = modalBox.querySelectorAll('.hist-row');
                    const total = rows.length;
                    rows.forEach((r, idx) => {
                        if (idx >= total - limit) {
                            r.style.display = '';
                        } else {
                            r.style.display = 'none';
                        }
                    });
                });
            }

            // CSV Download Buttons
            const btnCsvComp = document.getElementById('btn-dl-csv-comp');
            if (btnCsvComp) {
                btnCsvComp.addEventListener('click', () => {
                    const rows = [['Komponen', 'Nilai', 'Satuan', 'Pangsa', 'Pembanding Jatim', 'Keterangan']];
                    cfg.components.forEach(c => {
                        rows.push([c.name, c.value, c.unit, c.share ? c.share + '%' : '', c.benchmark ? c.benchmark + '%' : '', c.context]);
                    });
                    downloadDetailCsv(`sidapeda-${cfg.name.toLowerCase().replace(/[^a-z0-9]/g, '-')}-komponen.csv`, rows);
                });
            }

            const btnCsvPeer = document.getElementById('btn-dl-csv-peer');
            if (btnCsvPeer) {
                btnCsvPeer.addEventListener('click', () => {
                    const rows = [['Wilayah', 'Nilai', 'Keterangan', 'Status']];
                    cfg.peers.forEach(p => {
                        rows.push([p.name, p.value, p.note, p.current ? 'Wilayah Terpilih' : 'Pembanding']);
                    });
                    downloadDetailCsv(`sidapeda-${cfg.name.toLowerCase().replace(/[^a-z0-9]/g, '-')}-benchmarking.csv`, rows);
                });
            }

            const btnCsvHist = document.getElementById('btn-dl-csv-hist');
            if (btnCsvHist) {
                btnCsvHist.addEventListener('click', () => {
                    const rows = [['Tahun', 'Nilai', 'Perubahan Tahunan']];
                    cfg.annualSeries.forEach(s => {
                        rows.push([s.year, s.value, s.change || '']);
                    });
                    downloadDetailCsv(`sidapeda-${cfg.name.toLowerCase().replace(/[^a-z0-9]/g, '-')}-historis.csv`, rows);
                });
            }

            // Show Modal Overlay
            modalOverlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            modalBox.scrollTop = 0;
        }

        // Global expose for inline triggers and external accessibility
        window.openKpiDetailModal = openKpiDetailModal;
        window.closeKpiDetailModal = closeKpiDetailModal;

        // Klik kartu KPI untuk membuka modal detail dan menyesuaikan grafik
        document.querySelectorAll('.kpi-card').forEach(card => {
            card.addEventListener('click', function () {
                const idx = parseInt(this.getAttribute('data-index'), 10);
                const key = this.getAttribute('data-indicator-key');
                if (key && indicatorConfigs[key]) {
                    currentIndicatorKey = key;
                    if (indSelect) indSelect.value = key;
                    updateOverviewChart();
                }
                if (!isNaN(idx)) {
                    openKpiDetailModal(idx);
                }
            });
        });

        // Filter Kategori Indikator Beranda (Dropdown & Tabs)
        const catSelect = document.getElementById('beranda-category-select');
        const catTabs = document.getElementById('beranda-category-tabs');
        const kpiCards = document.querySelectorAll('#kpi-container .kpi-card');
        const kpiEmpty = document.getElementById('kpi-empty-message');
        const countDisplay = document.getElementById('kpi-count-display');

        function applyBerandaCategoryFilter(targetCat) {
            let visibleCount = 0;
            kpiCards.forEach(card => {
                const cardCat = card.getAttribute('data-category');
                if (!targetCat || targetCat === 'all' || cardCat === targetCat) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (kpiEmpty) {
                kpiEmpty.style.display = (visibleCount === 0) ? 'block' : 'none';
            }
            if (countDisplay) {
                countDisplay.textContent = visibleCount;
            }
        }

        if (catSelect) {
            catSelect.addEventListener('change', function () {
                const val = this.value;
                applyBerandaCategoryFilter(val);
                try {
                    const url = new URL(window.location.href);
                    if (val && val !== 'all') {
                        url.searchParams.set('cluster', val);
                    } else {
                        url.searchParams.delete('cluster');
                        url.searchParams.delete('category');
                    }
                    window.history.replaceState({}, '', url.toString());
                } catch (e) { }
            });

            // Initial check if pre-selected via server / URL
            if (catSelect.value && catSelect.value !== 'all') {
                applyBerandaCategoryFilter(catSelect.value);
            }
        }

        if (catTabs) {
            const tabButtons = catTabs.querySelectorAll('button');
            tabButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    tabButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    const targetCat = this.getAttribute('data-category');
                    if (catSelect) catSelect.value = targetCat;
                    applyBerandaCategoryFilter(targetCat);
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

        function bindRegionalActionButtons() { }

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

        const norm = s => (s || '').toLowerCase().replace(/^kabupaten\s+|^kab\.\s+|^kota\s+/i, '').trim();
        const activeRegionName = (window.__ACTIVE_REGION_NAME__ || 'Kabupaten Banyuwangi').trim();
        const isFocalRegion = (name) => norm(name) === norm(activeRegionName);

        function renderComparison() {
            const period = periodSelect ? periodSelect.value : '10';
            const sliceStart = period === '5' ? 5 : 0;
            const years = AppState.years ? AppState.years.slice(sliceStart) : [2015, 2016, 2017, 2018, 2019, 2020, 2021, 2022, 2023, 2024].slice(sliceStart);
            const periodBadge = document.getElementById('comp-tab-period-badge');
            if (periodLabel) periodLabel.textContent = period === '5' ? '2020 - 2024' : '2015 - 2024';
            if (periodBadge) periodBadge.textContent = period === '5' ? '2020 - 2024' : '2015 - 2024';

            // Calculate regional rows - HANYA untuk wilayah yang dicentang pengguna
            const rows = (AppState.regional || []).map(item => {
                const vals = (item.series || []).slice(sliceStart);
                const avg = mean(vals);
                const chg = vals.length > 1 ? vals[vals.length - 1] - vals[vals.length - 2] : 0;
                return { ...item, avg, chg };
            }).filter(item => selectedRegions.some(sel => sel === item.name || norm(sel) === norm(item.name))).sort((a, b) => b.avg - a.avg);

            // JIKA TIDAK ADA WILAYAH YANG DIPILIH, JANGAN TAMPILKAN DATA (EMPTY STATE)
            if (rows.length === 0) {
                if (kpiContainer) {
                    kpiContainer.innerHTML = `
                        <div class="comparison-kpi">
                            <div><span>Wilayah Terpilih</span><span class="badge neutral">0 Daerah</span></div>
                            <strong>0</strong>
                            <small>Belum ada wilayah dipilih</small>
                        </div>
                        <div class="comparison-kpi">
                            <div><span>Rata-rata Pertumbuhan</span><span class="badge neutral">â€”</span></div>
                            <strong>â€”</strong>
                            <small>Pilih wilayah pembanding</small>
                        </div>
                        <div class="comparison-kpi">
                            <div><span>Pertumbuhan Tertinggi</span><span class="badge neutral">â€”</span></div>
                            <strong>â€”</strong>
                            <small>Pilih wilayah pembanding</small>
                        </div>
                        <div class="comparison-kpi">
                            <div><span>Disparitas Laju</span><span class="badge neutral">â€”</span></div>
                            <strong>â€”</strong>
                            <small>Pilih wilayah pembanding</small>
                        </div>`;
                }

                if (tabContent) {
                    tabContent.innerHTML = `
                        <div class="empty-state" style="padding: 48px 24px; text-align: center; background: #f8fafc; border: 1px dashed var(--line); border-radius: 12px; margin: 16px 0;">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--muted); margin-bottom: 12px;"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"/><circle cx="12" cy="10" r="3"/></svg>
                            <h4 style="font-size: 14px; font-weight: 700; color: var(--navy); margin: 0 0 6px 0;">Tidak Ada Wilayah yang Dipilih</h4>
                            <p style="font-size: 11px; color: var(--muted); max-width: 440px; margin: 0 auto 16px;">Silakan pilih satu atau lebih kabupaten/kota pada menu dropdown wilayah di atas untuk menampilkan analisis perbandingan dan ranking data.</p>
                            <button type="button" class="button primary" onclick="document.getElementById('btn-select-all-regions').click()" style="display:inline-flex; align-items:center; gap:6px; font-size:11px; padding:0 16px; height:34px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                                <span>Pilih Semua Wilayah</span>
                            </button>
                        </div>`;
                }
                return;
            }

            const hasActive = rows.some(r => isFocalRegion(r.name));
            const activeRow = rows.find(r => isFocalRegion(r.name));
            const cleanFocalName = activeRow ? activeRow.name.replace(/^Kabupaten\s+|^Kab\.\s+|^Kota\s+/i, '') : activeRegionName.replace(/^Kabupaten\s+|^Kab\.\s+|^Kota\s+/i, '');
            const peerAvg = mean(rows.map(r => r.avg));

            // Render KPIs
            if (kpiContainer) {
                if (hasActive) {
                    const rank = rows.findIndex(r => isFocalRegion(r.name)) + 1;
                    const gap = activeRow.avg - peerAvg;
                    kpiContainer.innerHTML = `
                        <div class="comparison-kpi">
                            <div><span style="display:inline-flex; align-items:center; gap:5px;"><img src="${getLogoPemdaUrl(activeRow.name)}" alt="" class="region-table-logo" style="width:16px; height:16px; margin-right:0;"> Peringkat ${cleanFocalName}</span><span class="badge success">Posisi #${rank}</span></div>
                            <strong>#${rank}</strong>
                            <small>dari ${rows.length} wilayah terpilih</small>
                        </div>
                        <div class="comparison-kpi">
                            <div><span style="display:inline-flex; align-items:center; gap:5px;"><img src="${getLogoPemdaUrl(activeRow.name)}" alt="" class="region-table-logo" style="width:16px; height:16px; margin-right:0;"> Rata-rata ${cleanFocalName}</span><span class="badge info">Periode</span></div>
                            <strong>${formatPct(activeRow.avg)}</strong>
                            <small>periode ${period === '5' ? '2020 - 2024' : '2015 - 2024'}</small>
                        </div>
                        <div class="comparison-kpi">
                            <div><span>Rerata Wilayah Terpilih</span><span class="badge neutral">Benchmark</span></div>
                            <strong>${formatPct(peerAvg)}</strong>
                            <small>${rows.length} kabupaten/kota</small>
                        </div>
                        <div class="comparison-kpi">
                            <div><span>Gap terhadap Rerata</span><span class="badge ${gap >= 0 ? 'success' : 'warning'}">Selisih</span></div>
                            <strong class="${gap >= 0 ? 'positive' : 'negative'}">${gap >= 0 ? '+' : ''}${formatNum(gap)} pp</strong>
                            <small>${gap >= 0 ? 'Lebih tinggi' : 'Lebih rendah'} dari rerata</small>
                        </div>`;
                } else {
                    const topRegion = rows[0];
                    const bottomRegion = rows[rows.length - 1];
                    const disparity = topRegion.avg - bottomRegion.avg;
                    kpiContainer.innerHTML = `
                        <div class="comparison-kpi">
                            <div><span>Wilayah Terpilih</span><span class="badge info">${rows.length} Daerah</span></div>
                            <strong>${rows.length}</strong>
                            <small>kabupaten/kota dalam ranking</small>
                        </div>
                        <div class="comparison-kpi">
                            <div><span style="display:inline-flex; align-items:center; gap:5px;"><img src="${getLogoPemdaUrl(topRegion.name)}" alt="" class="region-table-logo" style="width:16px; height:16px; margin-right:0;"> Pertumbuhan Tertinggi</span><span class="badge success">#1 ${topRegion.name.replace(/^Kabupaten\s+|^Kab\.\s+|^Kota\s+/i, '')}</span></div>
                            <strong>${formatPct(topRegion.avg)}</strong>
                            <small>${topRegion.name}</small>
                        </div>
                        <div class="comparison-kpi">
                            <div><span>Rerata Wilayah Terpilih</span><span class="badge neutral">Benchmark</span></div>
                            <strong>${formatPct(peerAvg)}</strong>
                            <small>rerata ${rows.length} kabupaten/kota</small>
                        </div>
                        <div class="comparison-kpi">
                            <div><span>Disparitas Laju</span><span class="badge warning">Rentang</span></div>
                            <strong class="positive">${formatNum(disparity)} pp</strong>
                            <small>selisih tertinggi vs terendah</small>
                        </div>`;
                }
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
                    logo: getLogoPemdaUrl(r.name),
                    subLabel: `Perubahan: ${r.chg >= 0 ? '+' : ''}${formatNum(r.chg)} pp | Gap ke rerata: ${r.avg >= peerAvg ? '+' : ''}${formatNum(r.avg - peerAvg)} pp`,
                    badge: (hasActive && isFocalRegion(r.name)) ? 'Fokus Analisis' : `Peringkat #${idx + 1}`,
                    badgeClass: (hasActive && isFocalRegion(r.name)) ? 'primary' : (r.avg >= peerAvg ? 'success' : 'neutral'),
                    isHighlight: (hasActive && isFocalRegion(r.name))
                }));

                let listHtml = `
                    <div style="margin-bottom:20px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                            <span class="eyebrow">VISUALISASI PERINGKAT KOMPARASI</span>
                            <span class="badge info">Periode ${period === '5' ? '2020 - 2024' : '2015 - 2024'} (${rows.length} Wilayah)</span>
                        </div>
                        <div class="chart-wrap dynamic-chart" style="height:auto; min-height:160px;">
                            ${createHorizontalBarChartSVG(rankingBarData, { unit: '%', decimals: 2, showLogo: true })}
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
                    const isFocal = hasActive && isFocalRegion(r.name);
                    const diff = r.avg - peerAvg;
                    listHtml += `
                        <div class="comparison-table-row ${isFocal ? 'selected' : ''}" style="grid-template-columns: 50px 1.4fr .8fr .8fr .8fr;">
                            <b>${idx + 1}</b>
                            <span>
                                <img src="${getLogoPemdaUrl(r.name)}" alt="" class="region-table-logo">
                                <strong>${r.name}</strong>
                                ${isFocal ? '<span class="badge info" style="font-size:8px; padding:1px 5px; margin-left:6px;">Daerah Anda</span>' : ''}
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
                tabDesc.textContent = 'Menunjukkan lintasan pertumbuhan ekonomi wilayah pembanding yang dipilih.';

                const jatimSeries = (AppState.series && AppState.series.eastJavaGrowth) ? AppState.series.eastJavaGrowth.slice(sliceStart) : [];

                // Bangun deret grafik yang sesuai HANYA dengan wilayah yang dicentang pengguna
                const benchmarkSeries = [
                    { name: 'Jawa Timur', values: jatimSeries, logo: getLogoPemdaUrl('Jawa Timur'), className: 'series-1' }
                ];

                const colorClasses = ['series-0', 'series-2', 'series-3', 'series-4', 'series-5'];
                let cIdx = 0;
                rows.forEach(r => {
                    let vals = r.series && r.series.length > 0 ? r.series.slice(sliceStart) : [];
                    if (isFocalRegion(r.name) && AppState.series && AppState.series.growth) {
                        vals = AppState.series.growth.slice(sliceStart);
                    }
                    if (vals.length > 0) {
                        benchmarkSeries.push({
                            name: r.name.replace(/^Kabupaten\s+|^Kab\.\s+|^Kota\s+/i, ''),
                            values: vals,
                            logo: getLogoPemdaUrl(r.name),
                            className: isFocalRegion(r.name) ? 'series-0' : colorClasses[(cIdx++) % colorClasses.length]
                        });
                    }
                });

                let benchKpiHtml = `
                    <div class="comparison-kpis" style="margin-bottom:15px; grid-template-columns: repeat(4, 1fr);">`;
                if (hasActive) {
                    benchKpiHtml += `
                        <div class="comparison-kpi"><span><img src="${getLogoPemdaUrl(activeRow.name)}" alt="" class="region-table-logo" style="width:16px;height:16px;vertical-align:middle;margin-right:4px;">Rerata ${cleanFocalName}</span><strong>${formatPct(activeRow.avg)}</strong><small>Periode terpilih</small></div>
                        <div class="comparison-kpi"><span><img src="${getLogoPemdaUrl('Jawa Timur')}" alt="" class="region-table-logo" style="width:16px;height:16px;vertical-align:middle;margin-right:4px;">Rerata Jawa Timur</span><strong>${formatPct(mean(jatimSeries))}</strong><small>Benchmark Provinsi</small></div>
                        <div class="comparison-kpi"><span>Rerata Pembanding</span><strong>${formatPct(peerAvg)}</strong><small>${rows.length} kabupaten/kota</small></div>
                        <div class="comparison-kpi"><span>Wilayah Dicentang</span><strong>${rows.length}</strong><small>Daerah aktif</small></div>`;
                } else {
                    benchKpiHtml += `
                        <div class="comparison-kpi"><span><img src="${getLogoPemdaUrl(rows[0].name)}" alt="" class="region-table-logo" style="width:16px;height:16px;vertical-align:middle;margin-right:4px;">Pertumbuhan Tertinggi</span><strong>${formatPct(rows[0].avg)}</strong><small>${rows[0].name}</small></div>
                        <div class="comparison-kpi"><span><img src="${getLogoPemdaUrl('Jawa Timur')}" alt="" class="region-table-logo" style="width:16px;height:16px;vertical-align:middle;margin-right:4px;">Rerata Jawa Timur</span><strong>${formatPct(mean(jatimSeries))}</strong><small>Benchmark Provinsi</small></div>
                        <div class="comparison-kpi"><span>Rerata Wilayah Terpilih</span><strong>${formatPct(peerAvg)}</strong><small>${rows.length} kabupaten/kota</small></div>
                        <div class="comparison-kpi"><span>Wilayah Dicentang</span><strong>${rows.length}</strong><small>Daerah aktif</small></div>`;
                }
                benchKpiHtml += `</div>`;

                tabContent.innerHTML = `
                    ${benchKpiHtml}
                    <div class="chart-wrap dynamic-chart" style="height:290px;">
                        ${createLineChartSVG(years, benchmarkSeries, 290, '%')}
                    </div>
                    <div class="method-note">Garis hijau toska menunjukkan Provinsi Jawa Timur (Benchmark), dan garis berwarna lainnya mewakili masing-masing kabupaten/kota yang dicentang.</div>`;

            } else if (activeTab === 'Gap Analysis') {
                tabEyebrow.textContent = 'GAP ANALYSIS';
                const jatimAvg = mean((AppState.series && AppState.series.eastJavaGrowth) ? AppState.series.eastJavaGrowth.slice(sliceStart) : []);

                if (hasActive) {
                    tabTitle.textContent = `Selisih Kinerja terhadap Benchmark Utama (${cleanFocalName})`;
                    tabDesc.textContent = `Analisis komparatif selisih pertumbuhan ekonomi ${activeRow.name} terhadap provinsi dan wilayah pembanding yang dipilih.`;

                    const gapJatim = activeRow.avg - jatimAvg;

                    const gapBarData = rows.map(r => {
                        const diff = r.avg - activeRow.avg;
                        return {
                            label: r.name,
                            value: diff,
                            logo: getLogoPemdaUrl(r.name),
                            subLabel: `Rata-rata: ${formatPct(r.avg)} | Basis ${cleanFocalName}: ${formatPct(activeRow.avg)}`,
                            badge: diff > 0 ? `+${formatNum(diff)} pp` : (diff === 0 ? 'Acuan (0 pp)' : `${formatNum(diff)} pp`),
                            badgeClass: diff > 0 ? 'warning' : (diff === 0 ? 'primary' : 'success'),
                            isHighlight: isFocalRegion(r.name)
                        };
                    });

                    let gapTableRows = `
                        <tr>
                            <td><img src="${getLogoPemdaUrl('Jawa Timur')}" alt="" class="region-table-logo"><strong>Provinsi Jawa Timur</strong></td>
                            <td>${formatPct(activeRow.avg)}</td>
                            <td>${formatPct(jatimAvg)}</td>
                            <td><strong class="${gapJatim >= 0 ? 'positive' : 'negative'}">${gapJatim >= 0 ? '+' : ''}${formatNum(gapJatim)} pp</strong></td>
                            <td><span class="badge ${gapJatim >= 0 ? 'success' : 'warning'}">${gapJatim >= 0 ? 'Di atas provinsi' : 'Di bawah provinsi'}</span></td>
                        </tr>
                        <tr>
                            <td><span class="badge neutral" style="font-size:9.5px; margin-right:6px; padding:2px 6px;">Kelompok</span><strong>Rerata Daerah Terpilih</strong></td>
                            <td>${formatPct(activeRow.avg)}</td>
                            <td>${formatPct(peerAvg)}</td>
                            <td><strong class="${(activeRow.avg - peerAvg) >= 0 ? 'positive' : 'negative'}">${(activeRow.avg - peerAvg) >= 0 ? '+' : ''}${formatNum(activeRow.avg - peerAvg)} pp</strong></td>
                            <td><span class="badge ${(activeRow.avg - peerAvg) >= 0 ? 'success' : 'warning'}">${(activeRow.avg - peerAvg) >= 0 ? 'Di atas rerata' : 'Di bawah rerata'}</span></td>
                        </tr>`;

                    rows.filter(r => !isFocalRegion(r.name)).forEach(r => {
                        const diffVsFocal = activeRow.avg - r.avg;
                        gapTableRows += `
                            <tr>
                                <td><img src="${getLogoPemdaUrl(r.name)}" alt="" class="region-table-logo"><span>${r.name}</span></td>
                                <td>${formatPct(activeRow.avg)}</td>
                                <td>${formatPct(r.avg)}</td>
                                <td><strong class="${diffVsFocal >= 0 ? 'positive' : 'negative'}">${diffVsFocal >= 0 ? '+' : ''}${formatNum(diffVsFocal)} pp</strong></td>
                                <td><span class="badge ${diffVsFocal >= 0 ? 'success' : 'warning'}">${diffVsFocal >= 0 ? 'Unggul vs Daerah' : 'Tertinggal'}</span></td>
                            </tr>`;
                    });

                    tabContent.innerHTML = `
                        <div class="gap-overview" style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:16px;">
                            <div class="metric-box" style="padding:12px;">
                                <span>Gap ${cleanFocalName} terhadap Jawa Timur</span>
                                <strong class="${gapJatim >= 0 ? 'positive' : 'negative'}" style="font-size:18px;">${gapJatim >= 0 ? '+' : ''}${formatNum(gapJatim)} pp</strong>
                                <small>${gapJatim >= 0 ? cleanFocalName + ' tumbuh lebih cepat dari provinsi' : cleanFocalName + ' tumbuh di bawah provinsi'}</small>
                            </div>
                            <div class="metric-box" style="padding:12px;">
                                <span>Gap ${cleanFocalName} terhadap Rerata ${rows.length} Daerah</span>
                                <strong class="${(activeRow.avg - peerAvg) >= 0 ? 'positive' : 'negative'}" style="font-size:18px;">${(activeRow.avg - peerAvg) >= 0 ? '+' : ''}${formatNum(activeRow.avg - peerAvg)} pp</strong>
                                <small>${(activeRow.avg - peerAvg) >= 0 ? cleanFocalName + ' di atas rata-rata pembanding' : cleanFocalName + ' di bawah rata-rata pembanding'}</small>
                            </div>
                        </div>

                        <div style="margin: 18px 0;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                <span class="eyebrow">GRAFIK DIVERGENSI GAP PERTUMBUHAN EKONOMI (PP)</span>
                                <small style="color:var(--muted); font-size:9px;">Relatif terhadap ${cleanFocalName} (${formatPct(activeRow.avg)})</small>
                            </div>
                            <div class="chart-wrap dynamic-chart" style="height:auto; min-height:160px;">
                                ${createHorizontalBarChartSVG(gapBarData, { unit: ' pp', decimals: 2, showLogo: true })}
                            </div>
                        </div>

                        <div class="result-table-wrap">
                            <table class="result-table">
                                <thead><tr><th>Benchmark / Wilayah</th><th>Nilai ${cleanFocalName}</th><th>Nilai Pembanding</th><th>Gap (${cleanFocalName} - Pembanding)</th><th>Status Relatif</th></tr></thead>
                                <tbody>${gapTableRows}</tbody>
                            </table>
                        </div>`;
                } else {
                    tabTitle.textContent = 'Selisih Kinerja terhadap Rata-rata Wilayah Terpilih';
                    tabDesc.textContent = 'Analisis komparatif selisih pertumbuhan ekonomi masing-masing wilayah terhadap rata-rata kelompok terpilih.';

                    const gapBarData = rows.map(r => {
                        const diff = r.avg - peerAvg;
                        return {
                            label: r.name,
                            value: diff,
                            logo: getLogoPemdaUrl(r.name),
                            subLabel: `Rata-rata: ${formatPct(r.avg)} | Rerata Kelompok: ${formatPct(peerAvg)}`,
                            badge: diff > 0 ? `+${formatNum(diff)} pp` : (diff === 0 ? '0 pp' : `${formatNum(diff)} pp`),
                            badgeClass: diff > 0 ? 'success' : (diff === 0 ? 'neutral' : 'warning'),
                            isHighlight: false
                        };
                    });

                    let gapTableRows = '';
                    rows.forEach(r => {
                        const diff = r.avg - peerAvg;
                        gapTableRows += `
                            <tr>
                                <td><img src="${getLogoPemdaUrl(r.name)}" alt="" class="region-table-logo"><strong>${r.name}</strong></td>
                                <td>${formatPct(r.avg)}</td>
                                <td>${formatPct(peerAvg)}</td>
                                <td><strong class="${diff >= 0 ? 'positive' : 'negative'}">${diff >= 0 ? '+' : ''}${formatNum(diff)} pp</strong></td>
                                <td><span class="badge ${diff >= 0 ? 'success' : 'warning'}">${diff >= 0 ? 'Di atas rerata' : 'Di bawah rerata'}</span></td>
                            </tr>`;
                    });

                    tabContent.innerHTML = `
                        <div class="gap-overview" style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:16px;">
                            <div class="metric-box" style="padding:12px;">
                                <span>Rata-rata Pertumbuhan Terpilih</span>
                                <strong style="font-size:18px; color:var(--navy);">${formatPct(peerAvg)}</strong>
                                <small>Benchmark rata-rata ${rows.length} kabupaten/kota</small>
                            </div>
                            <div class="metric-box" style="padding:12px;">
                                <span>Rata-rata Jawa Timur</span>
                                <strong style="font-size:18px; color:var(--blue);">${formatPct(jatimAvg)}</strong>
                                <small>Benchmark pertumbuhan provinsi</small>
                            </div>
                        </div>

                        <div style="margin: 18px 0;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                <span class="eyebrow">GRAFIK DIVERGENSI TERHADAP RERATA KELOMPOK (PP)</span>
                                <small style="color:var(--muted); font-size:9px;">Rata-rata kelompok: ${formatPct(peerAvg)}</small>
                            </div>
                            <div class="chart-wrap dynamic-chart" style="height:auto; min-height:160px;">
                                ${createHorizontalBarChartSVG(gapBarData, { unit: ' pp', decimals: 2, showLogo: true })}
                            </div>
                        </div>

                        <div class="result-table-wrap">
                            <table class="result-table">
                                <thead><tr><th>Wilayah</th><th>Nilai Wilayah</th><th>Rerata Kelompok</th><th>Gap vs Rerata</th><th>Status</th></tr></thead>
                                <tbody>${gapTableRows}</tbody>
                            </table>
                        </div>`;
                }

            } else if (activeTab === 'Regional Profiling') {
                tabEyebrow.textContent = 'REGIONAL PROFILING';
                tabTitle.textContent = 'Profil Pertumbuhan & Stabilitas Wilayah';
                tabDesc.textContent = 'Karakteristik kinerja pertumbuhan ekonomi antar-kabupaten/kota selama periode terpilih.';

                let profCards = `<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap:16px; margin-bottom:22px;">`;
                rows.forEach((r, idx) => {
                    const vals = r.series.slice(sliceStart);
                    const maxV = Math.max(...vals);
                    const minV = Math.min(...vals);
                    const isHighlight = hasActive && isFocalRegion(r.name);
                    const avatarLetters = r.name.replace(/^Kabupaten\s+|^Kab\.\s+|^Kota\s+/i, '').slice(0, 2).toUpperCase();
                    profCards += `
                        <div style="background:${isHighlight ? '#f0f9ff' : '#ffffff'}; border:1px solid ${isHighlight ? '#93c5fd' : 'var(--line)'}; border-radius:12px; padding:20px; box-shadow:0 2px 8px rgba(15,23,42,0.04);">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div class="region-card-logo-wrap" style="${isHighlight ? 'border-color:#38bdf8;' : ''}">
                                        <img src="${getLogoPemdaUrl(r.name)}" alt="" class="region-card-logo">
                                    </div>
                                    <div>
                                        <span style="font-weight:700; color:${isHighlight ? 'var(--blue)' : 'var(--navy)'}; font-size:14px; display:block; margin-bottom:2px;">${r.name}</span>
                                        <small style="color:var(--muted); font-size:11px;">Peringkat #${idx + 1}</small>
                                    </div>
                                </div>
                                <span class="badge ${isHighlight ? 'primary' : 'neutral'}" style="font-size:10px; font-weight:700; padding:3px 8px;">#${idx + 1}</span>
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
                            <thead><tr><th>Peringkat</th><th>Kabupaten / Kota</th><th>Rata-rata Pertumbuhan</th><th>Pertumbuhan Terkini</th><th>Tertinggi (Peak)</th><th>Terendah (Trough)</th><th>Gap vs Rerata</th></tr></thead>
                            <tbody>`;
                rows.forEach((r, idx) => {
                    const vals = r.series.slice(sliceStart);
                    const maxV = Math.max(...vals);
                    const minV = Math.min(...vals);
                    const gapVsPeer = r.avg - peerAvg;
                    const isHighlight = hasActive && isFocalRegion(r.name);
                    profTable += `
                        <tr style="${isHighlight ? 'background:#eff6ff; font-weight:600;' : ''}">
                            <td><strong>#${idx + 1}</strong></td>
                            <td><img src="${getLogoPemdaUrl(r.name)}" alt="" class="region-table-logo"><strong>${r.name}</strong> ${isHighlight ? `<span class="badge primary" style="font-size:7px; margin-left:4px;">${cleanFocalName}</span>` : ''}</td>
                            <td><strong>${formatPct(r.avg)}</strong></td>
                            <td>${formatPct(vals[vals.length - 1])}</td>
                            <td style="color:var(--green); font-weight:600;">${formatPct(maxV)}</td>
                            <td style="color:var(--red); font-weight:600;">${formatPct(minV)}</td>
                            <td><span class="badge ${gapVsPeer > 0 ? 'success' : (gapVsPeer === 0 ? 'neutral' : 'warning')}">${gapVsPeer > 0 ? '+' : ''}${formatNum(gapVsPeer)} pp</span></td>
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
                        short: r.name.replace(/^Kabupaten\s+|^Kab\.\s+|^Kota\s+/i, ''),
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
                    const isFocal = hasActive && isFocalRegion(item.name);
                    const qBadgeClass = item.quadrant === 'I' ? 'success' : (item.quadrant === 'II' ? 'warning' : (item.quadrant === 'III' ? 'info' : 'neutral'));
                    klassenTable += `
                        <tr style="${isFocal ? 'background:#edf5fc; font-weight:600;' : ''}">
                            <td><img src="${getLogoPemdaUrl(item.name)}" alt="" class="region-table-logo"><strong>${item.name}</strong> ${isFocal ? `<span class="badge primary" style="font-size:7px;">${cleanFocalName}</span>` : ''}</td>
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
                tabDesc.textContent = 'Perbandingan langsung indikator kinerja antar-daerah yang dipilih.';

                const baseRegion = hasActive ? activeRow : rows[0];
                const baseName = baseRegion.name;
                const peerList = rows.filter(r => r.name !== baseName);

                let peerHtml = `
                    <div style="background:#f8fafc; border:1px solid var(--line); border-radius:10px; padding:12px 16px; margin-bottom:18px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div class="region-card-logo-wrap" style="border-color:#3b82f6;">
                                <img src="${getLogoPemdaUrl(baseName)}" alt="" class="region-card-logo">
                            </div>
                            <div>
                                <strong style="font-size:13px; color:var(--navy); display:block;">Basis Acuan: ${baseName}</strong>
                                <small style="color:var(--muted); font-size:10px;">Rata-rata: <b style="color:var(--blue);">${formatPct(baseRegion.avg)}</b> | Perubahan: <b class="${baseRegion.chg >= 0 ? 'positive' : 'negative'}">${baseRegion.chg >= 0 ? '+' : ''}${formatNum(baseRegion.chg)} pp</b> (${period === '5' ? '2020 - 2024' : '2015 - 2024'})</small>
                            </div>
                        </div>
                        <span class="badge primary" style="font-size:10px; padding:4px 10px;">Wilayah Acuan</span>
                    </div>`;

                if (peerList.length === 0) {
                    peerHtml += `<div class="table-empty" style="padding:32px; text-align:center; color:var(--muted); font-size:11px;">Hanya satu wilayah yang dipilih. Centang wilayah tambahan pada filter di atas untuk membandingkan secara head-to-head.</div>`;
                } else {
                    peerHtml += `<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:16px;">`;
                    peerList.forEach((r, idx) => {
                        const lead = baseRegion.avg - r.avg;
                        peerHtml += `
                            <div style="background:#ffffff; border:1px solid var(--line); border-radius:14px; padding:20px; box-shadow:0 2px 10px rgba(15,23,42,0.04); display:flex; flex-direction:column; justify-content:space-between; transition:transform 0.15s ease, box-shadow 0.15s ease;">
                                <div>
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <div class="region-card-logo-wrap">
                                                <img src="${getLogoPemdaUrl(r.name)}" alt="" class="region-card-logo">
                                            </div>
                                            <div>
                                                <h4 style="font-size:15px; font-weight:700; color:var(--navy); margin:0 0 3px 0;">${r.name}</h4>
                                                <small style="color:var(--muted); font-size:11px;">Wilayah Pembanding #${idx + 1}</small>
                                            </div>
                                        </div>
                                        <span class="badge neutral" style="font-size:10px; font-weight:700; padding:3px 10px;">Peringkat #${rows.findIndex(x => x.name === r.name) + 1}</span>
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

                                <div style="background:${lead >= 0 ? '#f0fdf4' : '#fef2f2'}; border:1px solid ${lead >= 0 ? '#bbf7d0' : '#fecaca'}; border-radius:10px; padding:12px 14px; margin-top:6px;">
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:4px;">
                                        <span style="font-size:11px; font-weight:600; color:${lead >= 0 ? '#166534' : '#991b1b'};">Posisi ${baseName}:</span>
                                        <span class="badge ${lead >= 0 ? 'success' : 'warning'}" style="font-size:10px; font-weight:700; padding:2px 8px;">
                                            ${lead >= 0 ? baseName + ' Unggul' : baseName + ' Tertinggal'}
                                        </span>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; align-items:baseline;">
                                        <strong style="font-size:14px; color:${lead >= 0 ? '#15803d' : '#b91c1c'}; font-weight:700;">
                                            ${lead >= 0 ? '+' : ''}${formatNum(lead)} pp
                                        </strong>
                                        <small style="font-size:11px; color:${lead >= 0 ? '#166534' : '#991b1b'};">
                                            ${lead >= 0 ? 'di atas rerata wilayah ini' : 'di bawah rerata wilayah ini'}
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
            const chipsContainer = document.getElementById('selected-region-chips');

            if (counterBadge) {
                counterBadge.textContent = `${count} / ${total} dipilih`;
            }

            if (dropdownLabel) {
                if (count === 0) {
                    dropdownLabel.textContent = 'Pilih Wilayah (0 dipilih)';
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

            if (chipsContainer) {
                if (count === 0) {
                    chipsContainer.innerHTML = '<span style="font-size:11px; color:#94a3b8; font-style:italic;">Tidak ada wilayah dipilih</span>';
                } else if (count === total) {
                    chipsContainer.innerHTML = `<span class="badge info" style="font-size:11px; padding:4px 10px; display:inline-flex; align-items:center; gap:5px;"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Seluruh Jawa Timur (${total} Daerah)</span>`;
                } else {
                    let chipsHtml = '';
                    const maxChips = 5;
                    checkedCbs.forEach((cb, idx) => {
                        if (idx < maxChips) {
                            const val = cb.value;
                            const shortName = val.replace(/^Kabupaten\s+|^Kab\.\s+|^Kota\s+/i, '');
                            chipsHtml += `
                                <span class="region-chip-badge" style="display:inline-flex; align-items:center; gap:5px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:14px; padding:2px 8px; font-size:11px; font-weight:600; color:#1e40af;">
                                    <img src="${getLogoPemdaUrl(val)}" alt="" style="width:13px; height:13px; object-fit:contain;">
                                    <span>${shortName}</span>
                                    <button type="button" class="btn-remove-chip" data-value="${val}" style="border:none; background:none; cursor:pointer; color:#93c5fd; font-size:13px; line-height:1; padding:0 0 0 2px;" title="Hapus ${val}">&times;</button>
                                </span>`;
                        }
                    });
                    if (count > maxChips) {
                        chipsHtml += `<span class="badge neutral" style="font-size:10.5px; padding:2px 7px;">+${count - maxChips} lainnya</span>`;
                    }
                    chipsContainer.innerHTML = chipsHtml;

                    chipsContainer.querySelectorAll('.btn-remove-chip').forEach(btn => {
                        btn.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            const valToRemove = this.dataset.value;
                            const targetCb = document.querySelector(`.region-cb[value="${valToRemove}"]`);
                            if (targetCb) {
                                targetCb.checked = false;
                                targetCb.dispatchEvent(new Event('change'));
                            }
                        });
                    });
                }
            }
        }

        window.toggleRegionDropdown = function (e) {
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
                    const parent = cb.closest('.region-option-item');
                    if (!parent || parent.style.display !== 'none') {
                        cb.checked = false;
                        selectedRegions = selectedRegions.filter(r => r !== cb.value);
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
                selectedRegions = [];
                document.querySelectorAll('.region-cb').forEach(cb => cb.checked = false);
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
                downloadCSV('perbandingan-regional.csv', csvRows);
            });
        }

        renderComparison();
        bindRegionalActionButtons();
    }

    // ECONOMIC AI ASSISTANT COMPONENT
    function initEconomicAiAssistant() {
        const assistantEl = document.getElementById('economic-ai-assistant');
        if (!assistantEl) return;

        const tabs = assistantEl.querySelectorAll('.ai-tab-btn');
        const panels = assistantEl.querySelectorAll('.ai-tab-panel');
        const chips = assistantEl.querySelectorAll('.ai-prompt-chip');
        const input = document.getElementById('ai-query-input');
        const btnSubmit = document.getElementById('btn-submit-ai');
        const resBox = document.getElementById('ai-response-container');
        const resLoading = document.getElementById('ai-response-loading');
        const resContent = document.getElementById('ai-response-content');

        // Helper to sanitize HTML
        function esc(str) {
            return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        // Tab switching
        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                const target = this.getAttribute('data-tab');
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                panels.forEach(p => {
                    p.style.display = (p.id === 'ai-panel-' + target) ? 'block' : 'none';
                });
            });
        });

        // Prompt Chips
        chips.forEach(chip => {
            chip.addEventListener('click', function () {
                const promptText = this.getAttribute('data-prompt') || this.textContent.trim();
                if (input) {
                    input.value = promptText;
                    input.focus();
                }
                processAiQuery(promptText);
            });
        });

        // Submit query button
        if (btnSubmit) {
            btnSubmit.addEventListener('click', function () {
                const query = input ? input.value.trim() : '';
                if (!query) {
                    if (input) input.focus();
                    return;
                }
                processAiQuery(query);
            });
        }

        // Enter key in textarea
        if (input) {
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if (btnSubmit) btnSubmit.click();
                }
            });
        }

        function processAiQuery(query) {
            if (!resBox || !resContent || !resLoading) return;
            resBox.style.display = 'block';
            resLoading.style.display = 'flex';
            resContent.style.display = 'none';
            resContent.innerHTML = '';

            setTimeout(() => {
                resLoading.style.display = 'none';
                resContent.style.display = 'block';

                const q = query.toLowerCase();
                let answer = '';

                if (q.includes('penting') || q.includes('perubahan')) {
                    answer = `
                        <div style="margin-bottom:8px;">
                            <strong style="color:#0284c7; font-size:13px;">Analisis Perubahan Penting Perekonomian Banyuwangi:</strong>
                        </div>
                        <ul style="margin:0; padding-left:18px; line-height:1.65; color:#334155;">
                            <li><strong>Pertumbuhan Ekonomi Stabil:</strong> Laju PDRB mencapai <strong>5.03%</strong>, mencerminkan pemulihan kuat sektor basis lokal dan konsumsi domestik.</li>
                            <li><strong>Penguatan Sektor Pariwisata & Jasa:</strong> Terjadi percepatan indeks Location Quotient (LQ) pada sektor penyediaan akomodasi & makanan minuman mendekati kriteria sektor basis (> 1.0).</li>
                            <li><strong>Dekomposisi Pertumbuhan Sektoral:</strong> Komponen keunggulan kompetitif lokal (<em>regional shift</em>) menyumbang dampak positif signifikan (+5.255), membuktikan ketahanan ekonomi Banyuwangi berada di atas rata-rata provinsi.</li>
                        </ul>
                    `;
                } else if (q.includes('waspada') || q.includes('risiko') || q.includes('bahaya')) {
                    answer = `
                        <div style="margin-bottom:8px;">
                            <strong style="color:#dc2626; font-size:13px;">Titik Kritis & Indikator yang Perlu Diwaspadai:</strong>
                        </div>
                        <ul style="margin:0; padding-left:18px; line-height:1.65; color:#334155;">
                            <li><strong>Industry Mix Negatif (-380):</strong> Struktur lapangan usaha daerah masih terkonsentrasi pada sektor-sektor yang di level Jawa Timur mengalami perlambatan pertumbuhan.</li>
                            <li><strong>Sensitivitas Inflasi Volatile Food:</strong> Meskipun inflasi umum terkendali (2.8%), fluktuasi harga komoditas pangan pokok berpotensi menekan daya beli masyarakat desil terbawah.</li>
                            <li><strong>Elastisitas Penyerapan Tenaga Kerja:</strong> Penyerapan tenaga kerja baru terkonsentrasi di sektor informal perkotaan, perlu percepatan hilirisasi padat karya di pedesaan.</li>
                        </ul>
                    `;
                } else if (q.includes('rekomendasi') || q.includes('tindak lanjut') || q.includes('langkah')) {
                    answer = `
                        <div style="margin-bottom:8px;">
                            <strong style="color:#16a34a; font-size:13px;">Rekomendasi Kebijakan Tindak Lanjut:</strong>
                        </div>
                        <ul style="margin:0; padding-left:18px; line-height:1.65; color:#334155;">
                            <li><strong>Integrasi Rantai Pasok Lokal:</strong> Hubungkan produsen pangan pertanian & perikanan lokal langsung dengan jaringan hotel/restoran pariwisata Banyuwangi.</li>
                            <li><strong>Optimalisasi Sektor Kuadran Klassen:</strong> Berikan insentif kemudahan usaha bagi pelaku usaha di Kuadran IV guna mengakselerasi transformasi menuju sektor potensial (Kuadran III).</li>
                            <li><strong>Fasilitasi Akses Permodalan UMKM:</strong> Perkuat klasterisasi usaha mikro dan perluas pasar ekspor produk unggulan daerah ke kawasan Tapal Kuda dan Bali.</li>
                        </ul>
                    `;
                } else {
                    answer = `
                        <div style="margin-bottom:8px;">
                            <strong style="color:#0f2942; font-size:13px;">Hasil Analisis Cerdas Asisten AI:</strong>
                        </div>
                        <p style="margin:0 0 8px 0; color:#334155;">Berdasarkan pemrosesan model ekonometrik lokal terhadap pertanyaan <em>"${esc(query)}"</em>:</p>
                        <ul style="margin:0; padding-left:18px; line-height:1.65; color:#334155;">
                            <li><strong>Korelasi Antar Indikator:</strong> Analisis korelasi Pearson menunjukkan korelasi negatif yang sangat kuat antara Pertumbuhan Ekonomi dan TPT (r = -0.84), membuktikan pertumbuhan berkontribusi langsung menurunkan pengangguran seiring jeda waktu (<em>time lag</em>).</li>
                            <li><strong>Performa Relatif Daerah:</strong> Posisi Kabupaten Banyuwangi berada pada kelompok atas daya saing regional di Jawa Timur bagian timur.</li>
                            <li><strong>Fokus Rekomendasi:</strong> Pertahankan stabilitas iklim investasi dan prioritaskan belanja infrastruktur konektivitas logistik antar-wilayah.</li>
                        </ul>
                    `;
                }

                resContent.innerHTML = answer;
            }, 350);
        }
    }

    // 3. ANALYSIS PAGE
    function initAnalysisPage() {
        const menuLinks = document.querySelectorAll('#analysis-menu-links .analysis-link');
        const heading = document.getElementById('active-method-heading');
        const subHeading = document.getElementById('active-method-sub');
        const container = document.getElementById('analysis-content-container');
        const btnFormula = document.getElementById('btn-toggle-formula');
        const formulaBox = document.getElementById('formula-box');
        const btnExport = document.getElementById('btn-export-analysis');

        let activeMethod = 'Location Quotient (LQ)';
        let corrPair = 'growth-tpt';
        let corrPeriod = '10';

        // Check URL parameter 'mode'
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const modeParam = urlParams.get('mode');
            const modeMap = {
                'lq': 'Location Quotient (LQ)',
                'shift-share': 'Shift Share Analysis',
                'klassen': 'Tipologi Klassen',
                'korelasi': 'Analisis Korelasi'
            };
            if (modeParam && modeMap[modeParam]) {
                activeMethod = modeMap[modeParam];
            } else {
                const savedMethod = localStorage.getItem('sidapeda_active_analysis');
                if (savedMethod) {
                    activeMethod = savedMethod;
                }
            }
        } catch (e) { }

        const methodDescriptions = {
            'Location Quotient (LQ)': 'Identifikasi sektor basis dan keunggulan komparatif ekonomi daerah Kabupaten Banyuwangi.',
            'Shift Share Analysis': 'Dekomposisi pertumbuhan sektoral terhadap komponen pertumbuhan provinsi, bauran industri, dan keunggulan daya saing lokal.',
            'Tipologi Klassen': 'Klasifikasi performa dan posisi pertumbuhan sektoral daerah relatif terhadap rata-rata pertumbuhan agregat.',
            'Analisis Korelasi': 'Analisis kekuatan dan arah asosiasi linear Pearson antar indikator makroekonomi daerah deret 2015â€“2024.'
        };

        const formulas = {
            'Location Quotient (LQ)': 'LQ = (Tenaga kerja sektor lokal / total lokal) Ã· (Tenaga kerja sektor provinsi / total provinsi). LQ > 1 menunjukkan sektor basis.',
            'Shift Share Analysis': 'Î”Eij = EijÂ·r + EijÂ·(rin - r) + EijÂ·(rij - rin). Komponen: Efek Pertumbuhan Provinsi, Efek Bauran Industri, Efek Daya Saing Lokal.',
            'Tipologi Klassen': 'Kuadran I: Pangsa > rata-rata lokal & Pertumbuhan > agregat daerah; Kuadran II: Pangsa > rata-rata, Pertumbuhan < agregat; Kuadran III: Pangsa < rata-rata, Pertumbuhan > agregat; Kuadran IV: Keduanya di bawah ambang.',
            'Analisis Korelasi': 'r = Î£[(x - xÌ„)(y - È³)] Ã· âˆš[Î£(x - xÌ„)Â² Î£(y - È³)Â²]. RÂ² = rÂ² mengukur persentase variasi bersama in-sample.'
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
            if (subHeading && methodDescriptions[activeMethod]) {
                subHeading.textContent = methodDescriptions[activeMethod];
            }
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
                        <div class="method-metric"><span>Sektor Nonbasis</span><strong>${lqRows.length - basisCount}</strong><small>Nilai LQ â‰¤ 1.0</small></div>
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
                    { key: 'growth-tpt', label: 'Pertumbuhan ekonomi Ã— TPT', xName: 'Pertumbuhan ekonomi (%)', yName: 'TPT (%)', x: AppState.series.growth, y: AppState.series.tpt },
                    { key: 'investment-growth', label: 'Pertumbuhan investasi Ã— Pertumbuhan ekonomi', xName: 'Pertumbuhan investasi (%)', yName: 'Pertumbuhan ekonomi (%)', x: AppState.series.investmentGrowth, y: AppState.series.growth },
                    { key: 'growth-poverty', label: 'Pertumbuhan ekonomi Ã— Kemiskinan', xName: 'Pertumbuhan ekonomi (%)', yName: 'Kemiskinan (%)', x: AppState.series.growth, y: AppState.series.poverty },
                    { key: 'percapita-poverty', label: 'PDRB per kapita Ã— Kemiskinan', xName: 'PDRB per kapita (juta)', yName: 'Kemiskinan (%)', x: AppState.series.perCapita, y: AppState.series.poverty },
                    { key: 'investment-tpt', label: 'Pertumbuhan investasi Ã— TPT', xName: 'Pertumbuhan investasi (%)', yName: 'TPT (%)', x: AppState.series.investmentGrowth, y: AppState.series.tpt }
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
                        <div><span>Koefisien Determinasi (RÂ²)</span><strong>${formatNum(r2, 3)}</strong><small>${formatPct(r2 * 100, 1)} variasi bersama</small></div>
                        <div><span>Jumlah Observasi</span><strong>${subYears.length}</strong><small>Data tahunan ${subYears[0]}â€“${subYears[subYears.length - 1]}</small></div>
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
                                <div class="card-head">
                                    <div>
                                        <span class="eyebrow">INTERPRETASI</span>
                                        <h3>${strength}</h3>
                                    </div>
                                    <span class="relation-icon ${r >= 0 ? 'up' : 'down'}" title="${r >= 0 ? 'Arah Korelasi Positif (Searah)' : 'Arah Korelasi Negatif (Berlawanan)'}">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            ${r >= 0
                        ? '<line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/>'
                        : '<line x1="7" y1="7" x2="17" y2="17"/><polyline points="17 7 17 17 7 17"/>'}
                                        </svg>
                                    </span>
                                </div>
                                <p class="interpretation-desc">
                                    Hubungan antara <strong>${selectedPair.xName}</strong> dan <strong>${selectedPair.yName}</strong> menunjukkan arah <strong class="${r >= 0 ? 'positive' : 'negative'}">${r >= 0 ? 'positif (bergerak searah)' : 'negatif (bergerak berlawanan)'}</strong> dengan derajat korelasi <strong>${strength.toLowerCase()}</strong>.
                                </p>
                                <div class="interpretation-note">
                                    <p>Korelasi mengukur asosiasi linear dan bukan hubungan sebab-akibat kausal.</p>
                                </div>
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
                try { localStorage.setItem('sidapeda_active_analysis', activeMethod); } catch (e) { }
                renderAnalysis();
            });
        });

        // Set initial active link and render
        try {
            const targetLink = Array.from(menuLinks).find(l => l.getAttribute('data-mode') === activeMethod);
            if (targetLink) {
                menuLinks.forEach(l => l.classList.remove('active'));
                targetLink.classList.add('active');
                renderAnalysis();
            }
        } catch (e) { }

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

    // 4. MAP PAGE (OPENSTREETMAP API & GEOSPATIAL ECONOMIC INTELLIGENCE)
    function initMapPage() {
        const mapContainer = document.getElementById('leaflet-osm-map');
        if (!mapContainer) return;

        // Elements
        const groupSelect = document.getElementById('map-group-select');
        const indicatorSelect = document.getElementById('map-indicator-select');
        const levelSelect = document.getElementById('map-level-select');
        const pinDisplaySelect = document.getElementById('map-pin-display-select');
        const legendContainer = document.getElementById('legend-items-container');
        const locDetailName = document.getElementById('loc-detail-name');
        const locDetailLevel = document.getElementById('loc-detail-level');
        const locDetailCode = document.getElementById('loc-detail-code');
        const locBodyContainer = document.getElementById('loc-body-container');
        const searchInput = document.getElementById('map-search-input');
        const btnZoomIn = document.getElementById('btn-zoom-in');
        const btnZoomOut = document.getElementById('btn-zoom-out');
        const zoomText = document.getElementById('zoom-level-text');
        const btnExportMap = document.getElementById('btn-export-map');
        const hotspotBox = document.getElementById('hotspot-note-box');
        const modeTabs = document.getElementById('map-mode-tabs');
        const btnReverseLegend = document.getElementById('btn-reverse-legend');
        const btnResetLocation = document.getElementById('btn-reset-location');

        // Quick Focus Buttons
        const btnFocusJatim = document.getElementById('btn-focus-jatim');
        const btnFocusBanyuwangi = document.getElementById('btn-focus-banyuwangi');
        const btnFocusSurabaya = document.getElementById('btn-focus-surabaya');
        const btnFocusMalang = document.getElementById('btn-focus-malang');

        // Layer Checkboxes
        const cbOsm = document.getElementById('layer-cb-osm');
        const cbBoundaries = document.getElementById('layer-cb-boundaries');
        const cbMarkers = document.getElementById('layer-cb-markers');
        const cbLabels = document.getElementById('layer-cb-labels');
        const cbHotspot = document.getElementById('layer-cb-hotspot');

        // Datasets
        // Datasets
        const jatimRegions = (window.__JATIM_REGIONS__ && Array.isArray(window.__JATIM_REGIONS__) && window.__JATIM_REGIONS__.length > 0)
            ? window.__JATIM_REGIONS__
            : [];
        
        const districtList = (window.__DISTRICTS__ && Array.isArray(window.__DISTRICTS__) && window.__DISTRICTS__.length > 0)
            ? window.__DISTRICTS__
            : (AppState.districts || []);

        let jatimRegenciesSummary = [];
        let jatimAllKecamatan = [];

        function getValueTier(val) {
            const v = parseFloat(val) || 0;
            if (v >= 300) return { tierClass: 'tier-red', cls: 4, name: 'Sangat Tinggi', color: '#ef4444' };
            if (v >= 50) return { tierClass: 'tier-orange', cls: 3, name: 'Tinggi', color: '#f97316' };
            if (v >= 10) return { tierClass: 'tier-yellow', cls: 2, name: 'Sedang', color: '#eab308' };
            return { tierClass: 'tier-green', cls: 1, name: 'Rendah', color: '#22c55e' };
        }

        if (window.__JATIM_REGENCIES_SUMMARY_URL__) {
            fetch(window.__JATIM_REGENCIES_SUMMARY_URL__)
                .then(r => r.json())
                .then(res => {
                    if (res && res.data && Array.isArray(res.data)) {
                        jatimRegenciesSummary = res.data;
                        renderMarkers();
                    }
                }).catch(e => console.warn('Gagal memuat summary kab/kota:', e));
        }

        if (window.__JATIM_KECAMATAN_URL__) {
            fetch(window.__JATIM_KECAMATAN_URL__)
                .then(r => r.json())
                .then(res => {
                    if (res && res.data && Array.isArray(res.data)) {
                        jatimAllKecamatan = res.data;
                        if (isDistrictsVisible) {
                            renderDistricts(false);
                        }
                    }
                }).catch(e => console.warn('Gagal memuat dataset kecamatan se-Jatim:', e));
        }

        let selectedRegionCode = '35.10'; // Default: Kabupaten Banyuwangi
        let selectedDistrictName = districtList.length > 0 ? (districtList[0].name || districtList[0].nama_kecamatan) : 'Banyuwangi';
        let activeMode = 'Thematic Layer';
        let legendReversed = false;
        let mapLevel = levelSelect ? levelSelect.value : 'Jawa Timur';
        let pinDisplayMode = pinDisplaySelect ? pinDisplaySelect.value : 'value';

        let jatimGeoJson = null;
        let layerGeoJsonBoundaries = null;
        const polygonLayers = {}; // mapping code -> Leaflet polygon layer
        let isDistrictsVisible = false;
        let activeBreakoutCityCode = null; // Menyimpan kode kota/kabupaten yang sedang dipilih untuk pecah kecamatan
        const KEC_ZOOM_THRESHOLD = 10;
        const btnBackToProvince = document.getElementById('btn-back-to-province');
        const mapBreadKab = document.getElementById('map-bread-kab');
        const mapBreadLevel = document.getElementById('map-bread-level');

        // 4 KELAS PEMISAHAN SPASIAL GIS
        const classColors = {
            4: '#059669', // Kelas 4: Sangat Tinggi (Emerald)
            3: '#2563eb', // Kelas 3: Tinggi (Royal Blue)
            2: '#d97706', // Kelas 2: Sedang (Golden Amber)
            1: '#dc2626'  // Kelas 1: Rendah (Coral Red)
        };
        const classNames = {
            4: 'Sangat Tinggi',
            3: 'Tinggi',
            2: 'Sedang',
            1: 'Rendah'
        };

        function get4Classes() {
            const vals = jatimRegions.map(r => r.growth).filter(v => typeof v === 'number');
            const minV = Math.min(...vals) || 1.7;
            const maxV = Math.max(...vals) || 6.4;
            const range = (maxV - minV) || 1;
            const step = range / 4;
            return {
                minV,
                maxV,
                range,
                step,
                t1: minV + step,
                t2: minV + (2 * step),
                t3: minV + (3 * step)
            };
        }

        function getRegionClass(reg) {
            if (!reg || typeof reg.growth !== 'number') return 2;
            const c = get4Classes();
            if (reg.growth >= c.t3) return 4;
            if (reg.growth >= c.t2) return 3;
            if (reg.growth >= c.t1) return 2;
            return 1;
        }

        const mapGroups = {
            'Aktivitas ekonomi': ['Pertumbuhan ekonomi', 'PDRB per kapita', 'Kontribusi sektoral'],
            'Kesejahteraan': ['Tingkat kemiskinan', 'Indeks Pembangunan Manusia (IPM)'],
            'Investasi': ['Laju pertumbuhan PMDN', 'Realisasi investasi daerah'],
            'Produksi sektoral': ['Produksi pertanian', 'Produksi industri pengolahan'],
            'Ketenagakerjaan': ['Tingkat Pengangguran Terbuka (TPT)'],
            'Pariwisata': ['Destinasi wisata & kunjungan'],
            'Harga': ['Tingkat inflasi daerah', 'Stabilitas pangan']
        };

        function updateIndicators() {
            const grp = groupSelect ? groupSelect.value : 'Aktivitas ekonomi';
            const list = mapGroups[grp] || ['Pertumbuhan ekonomi'];
            if (indicatorSelect) {
                indicatorSelect.innerHTML = list.map(item => `<option value="${item}">${item}</option>`).join('');
            }
        }
        updateIndicators();

        // 1. Inisialisasi OpenStreetMap Leaflet
        let map = null;
        let osmLayer = null;
        let layerBoundaries = null;
        let layerMarkers = null;
        let layerDistricts = null;
        let layerHotspots = null;

        const boundsLayers = {}; // mapping code -> Leaflet layer (fallback)
        const markerLayers = {}; // mapping code -> Leaflet marker

        if (typeof L !== 'undefined') {
            try {
                // Bounds Jawa Timur resmi (mencakup pulau Bawean Gresik & kepulauan Sumenep)
                const maxPanBounds = L.latLngBounds([[-9.5, 110.0], [-4.8, 116.8]]);

                map = L.map('leaflet-osm-map', {
                    center: [-7.75, 112.75],
                    zoom: 8,
                    minZoom: 7,
                    maxZoom: 18,
                    zoomControl: false,
                    maxBounds: maxPanBounds,
                    maxBoundsViscosity: 0.8
                });

                // Variasi Peta Dasar (Basemaps): OpenStreetMap, Google Maps Satelit (Hybrid & Murni), dan CartoDB Light
                const basemaps = {
                    'osm': L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap'
                    }),
                    'google-hybrid': L.tileLayer('https://mt{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                        maxZoom: 20,
                        subdomains: ['0', '1', '2', '3'],
                        attribution: '&copy; Google Maps Satelit'
                    }),
                    'google-sat': L.tileLayer('https://mt{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                        maxZoom: 20,
                        subdomains: ['0', '1', '2', '3'],
                        attribution: '&copy; Google Maps Satelit'
                    }),
                    'carto-light': L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                        maxZoom: 20,
                        subdomains: 'abcd',
                        attribution: '&copy; CartoDB'
                    })
                };
                let currentBasemapKey = 'osm';
                let currentBasemapLayer = basemaps['osm'];
                currentBasemapLayer.addTo(map);

                function switchBasemap(key) {
                    if (!map || !basemaps[key]) return;
                    if (currentBasemapLayer && map.hasLayer(currentBasemapLayer)) {
                        map.removeLayer(currentBasemapLayer);
                    }
                    currentBasemapKey = key;
                    currentBasemapLayer = basemaps[key];
                    if (cbOsm && cbOsm.checked) {
                        currentBasemapLayer.addTo(map);
                    }
                    if (layerGeoJsonBoundaries && map.hasLayer(layerGeoJsonBoundaries)) {
                        layerGeoJsonBoundaries.bringToFront();
                    }
                    const bmSelect = document.getElementById('map-basemap-select');
                    if (bmSelect && bmSelect.value !== key) bmSelect.value = key;

                    document.querySelectorAll('.basemap-pill-btn').forEach(b => {
                        b.classList.toggle('active', b.getAttribute('data-basemap') === key);
                    });
                }

                const basemapSelect = document.getElementById('map-basemap-select');
                if (basemapSelect) {
                    basemapSelect.addEventListener('change', () => {
                        switchBasemap(basemapSelect.value);
                    });
                }
                document.querySelectorAll('.basemap-pill-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const bm = btn.getAttribute('data-basemap');
                        if (bm) switchBasemap(bm);
                    });
                });

                layerBoundaries = L.layerGroup();
                layerMarkers = L.layerGroup().addTo(map);
                layerDistricts = L.layerGroup();
                layerHotspots = L.layerGroup();

                map.on('zoom', () => {
                    if (zoomText) zoomText.textContent = 'Z' + map.getZoom();
                });
                map.on('zoomend', () => {
                    const z = map.getZoom();
                    if (zoomText) zoomText.textContent = 'Z' + z;
                    // Jika pengguna melakukan zoom out manual ke level provinsi (Z < 10) saat mode pecah kota aktif, kembalikan ke peta provinsi
                    if (z < KEC_ZOOM_THRESHOLD && activeBreakoutCityCode) {
                        resetToProvinceView(false);
                    }
                });
                map.on('moveend', () => {
                    if (map && activeBreakoutCityCode && isDistrictsVisible) {
                        renderDistrictsForCity(activeBreakoutCityCode, false);
                    }
                });
                if (zoomText) zoomText.textContent = 'Z' + map.getZoom();

            } catch (err) {
                console.error('Error saat inisialisasi Leaflet OpenStreetMap:', err);
            }
        } else {
            console.warn('Leaflet library belum termuat.');
            const overlay = document.getElementById('map-overlay-message');
            if (overlay) {
                overlay.style.display = 'block';
                const ot = document.getElementById('map-overlay-title');
                const os = document.getElementById('map-overlay-sub');
                if (ot) ot.textContent = 'Peta Memerlukan Leaflet Library';
                if (os) os.textContent = 'Pastikan koneksi CDN Leaflet aktif untuk menampilkan OpenStreetMap tile.';
            }
        }

        // Fungsi Kontrol Visibilitas Penanda Berdasarkan Zoom Level
        // Zoom Out (Z < 10): Hanya Kota/Kabupaten Jawa Timur yang muncul
        // Zoom In (Z >= 10): Titik Kecamatan muncul terpecah/berkembang dari titik kota Banyuwangi
        // Alur Interaksi: Pengguna harus memilih/menyorot kota terlebih dahulu baru kecamatan terpecah
        function selectAndBreakoutCity(code) {
            if (!map) return;
            activeBreakoutCityCode = code;
            selectedRegionCode = code;

            const summary = jatimRegenciesSummary.find(s => s.kode === code);
            const reg = jatimRegions.find(r => r.kode === code);
            if (!reg && !summary) return;

            const lat = (summary && summary.lat) ? summary.lat : reg.lat;
            const lng = (summary && summary.lng) ? summary.lng : reg.lng;
            const cityName = reg ? reg.nama : summary.nama;

            // 1. Tampilkan tombol floating kembali ke provinsi
            if (btnBackToProvince) {
                btnBackToProvince.style.display = 'inline-flex';
            }

            // 2. Perbarui breadcrumb
            if (mapBreadLevel) {
                mapBreadLevel.textContent = cityName + ' (Pecah Kecamatan)';
            }
            const metaLbl = document.getElementById('active-map-meta-label');
            if (metaLbl) {
                metaLbl.textContent = 'Fokus ' + cityName + ' • Mode Pecah Kecamatan';
            }

            // 3. Redupkan (dim) seluruh kota lain, sembunyikan titik pusat kota terpilih agar tidak tumpang tindih
            Object.keys(markerLayers).forEach(c => {
                const m = markerLayers[c];
                if (!m) return;
                const pin = m._icon ? m._icon.querySelector('.gis-halo-pin') : null;
                if (c === code) {
                    m.setOpacity(0);
                    if (m._icon) m._icon.style.pointerEvents = 'none';
                } else {
                    m.setOpacity(1);
                    if (pin) pin.classList.add('dimmed');
                    if (m._icon) m._icon.style.pointerEvents = 'auto';
                }
            });

            // 4. Perbarui status tombol quick focus bar
            document.querySelectorAll('.quick-focus-bar button').forEach(b => b.classList.remove('active'));
            if (code === '35.73' && btnFocusMalang) btnFocusMalang.classList.add('active');
            else if (code === '35.10' && btnFocusBanyuwangi) btnFocusBanyuwangi.classList.add('active');
            else if (code === '35.78' && btnFocusSurabaya) btnFocusSurabaya.classList.add('active');

            // 5. Terbangkan peta ke pusat kota dengan zoom 12
            isDistrictsVisible = true;
            if (cbMarkers && cbMarkers.checked && !map.hasLayer(layerDistricts)) {
                map.addLayer(layerDistricts);
            }

            map.flyTo([lat, lng], 12, { duration: 0.70 });

            // 6. Jalankan animasi pecah radial keluar dari titik pusat kota menuju titik koordinat per kecamatan
            setTimeout(() => {
                renderDistrictsForCity(code, true);
            }, 120);

            // 7. Perbarui panel profil detail di sebelah kanan
            selectRegion(code, false);
        }

        // Kembalikan tampilan ke seluruh Jawa Timur (38 Kab/Kota)
        function resetToProvinceView(fly = true) {
            if (!map) return;
            activeBreakoutCityCode = null;
            isDistrictsVisible = false;

            // 1. Bersihkan layer kecamatan
            if (layerDistricts) {
                layerDistricts.clearLayers();
                if (map.hasLayer(layerDistricts)) map.removeLayer(layerDistricts);
            }

            // 2. Pulihkan seluruh 38 penanda kota/kabupaten ke kondisi normal (tanpa dimmed)
            Object.keys(markerLayers).forEach(c => {
                const m = markerLayers[c];
                if (!m) return;
                m.setOpacity(1);
                if (m._icon) {
                    m._icon.style.pointerEvents = 'auto';
                    const pin = m._icon.querySelector('.gis-halo-pin');
                    if (pin) pin.classList.remove('dimmed', 'selected');
                }
            });

            // 3. Sembunyikan tombol floating
            if (btnBackToProvince) {
                btnBackToProvince.style.display = 'none';
            }

            // 4. Perbarui breadcrumb & label meta
            if (mapBreadLevel) {
                mapBreadLevel.textContent = '38 Kabupaten & Kota';
            }
            const metaLbl = document.getElementById('active-map-meta-label');
            if (metaLbl) {
                metaLbl.textContent = '38 Kab/Kota • Jawa Timur';
            }

            // 5. Reset status tombol quick focus
            document.querySelectorAll('.quick-focus-bar button').forEach(b => b.classList.remove('active'));
            if (btnFocusJatim) btnFocusJatim.classList.add('active');

            // 6. Terbangkan kembali ke pandangan provinsi Jawa Timur
            if (fly) {
                map.flyTo([-7.75, 112.75], 8, { duration: 0.75 });
            }
        }

        function updateMarkerVisibilityByZoom(animateBreakout = false) {
            if (!map) return;
            const currentZoom = map.getZoom();
            if (zoomText) zoomText.textContent = 'Z' + currentZoom;

            if (currentZoom < KEC_ZOOM_THRESHOLD && activeBreakoutCityCode) {
                resetToProvinceView(false);
            }
        }

        // 2. Render Batas Riil Antar Kota/Kabupaten (GeoJSON Polygons untuk GIS)
        function renderGeoJsonBoundaries(data) {
            if (!map || !data) return;
            if (layerGeoJsonBoundaries) {
                map.removeLayer(layerGeoJsonBoundaries);
            }

            layerGeoJsonBoundaries = L.geoJSON(data, {
                smoothFactor: 0.8,
                style: function (feature) {
                    const code = feature.properties.code;
                    const reg = jatimRegions.find(r => r.kode === code);
                    const cls = getRegionClass(reg);
                    const color = classColors[cls] || '#2563eb';
                    const isSelected = (code === selectedRegionCode);
                    const isCurrent = (code === '35.10');

                    return {
                        fillColor: color,
                        fillOpacity: isSelected ? 0.62 : (isCurrent ? 0.44 : 0.30),
                        weight: isSelected ? 3.5 : (isCurrent ? 2.5 : 1.4),
                        color: isSelected ? '#0f172a' : (isCurrent ? '#0d9488' : '#334155'),
                        opacity: 0.95
                    };
                },
                onEachFeature: function (feature, layer) {
                    const code = feature.properties.code;
                    const reg = jatimRegions.find(r => r.kode === code);
                    const nama = reg ? reg.nama : feature.properties.name;
                    const cls = getRegionClass(reg);
                    const clsName = classNames[cls];

                    polygonLayers[code] = layer;

                    layer.bindTooltip(`
                        <div class="gis-polygon-tooltip">
                            <strong>${nama}</strong>
                            <div class="tip-sub">Kode: <code>${code}</code> • Kelas ${cls} (${clsName})</div>
                            <div class="tip-val">${reg ? formatNum(reg.growth, 2) + '% Pertumbuhan' : ''}</div>
                        </div>
                    `, { sticky: true, className: 'enterprise-boundary-tooltip' });

                    layer.on('mouseover', function () {
                        if (code !== selectedRegionCode) {
                            layer.setStyle({
                                fillOpacity: 0.58,
                                weight: 2.2,
                                color: '#0f172a'
                            });
                            if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
                                layer.bringToFront();
                            }
                        }
                    });

                    layer.on('mouseout', function () {
                        if (code !== selectedRegionCode) {
                            const isCurrent = (code === '35.10');
                            const color = classColors[cls] || '#2563eb';
                            layer.setStyle({
                                fillColor: color,
                                fillOpacity: isCurrent ? 0.44 : 0.30,
                                weight: isCurrent ? 2.5 : 1.4,
                                color: isCurrent ? '#0d9488' : '#334155'
                            });
                        }
                    });

                    layer.on('click', function () {
                        selectRegion(code, true);
                        if (layer.getBounds) {
                            map.fitBounds(layer.getBounds(), { padding: [35, 35], maxZoom: 11 });
                        }
                    });
                }
            });

            if (cbBoundaries && cbBoundaries.checked) {
                layerGeoJsonBoundaries.addTo(map);
            }
        }

        // Kotak-kotak bounding box dihilangkan total agar hanya batas riil GIS yang tersisa
        function renderBoundaries() {
            if (layerBoundaries) {
                layerBoundaries.clearLayers();
                if (map && map.hasLayer(layerBoundaries)) {
                    map.removeLayer(layerBoundaries);
                }
            }
        }

        // 3. Render GIS Cluster Markers (Kota/Kabupaten Jawa Timur - Bersih & Sederhana Tanpa Efek Neon)
        function renderMarkers() {
            if (!layerMarkers || !map) return;
            layerMarkers.clearLayers();

            const searchQ = searchInput ? searchInput.value.toLowerCase().trim() : '';

            jatimRegions.forEach(region => {
                const summary = jatimRegenciesSummary.find(s => s.kode === region.kode);
                const lat = summary && summary.lat ? summary.lat : region.lat;
                const lng = summary && summary.lng ? summary.lng : region.lng;
                if (!lat || !lng) return;

                // Search filtering
                if (searchQ) {
                    const matchName = region.nama.toLowerCase().includes(searchQ);
                    const matchCode = region.kode.includes(searchQ);
                    const matchCapital = (region.ibu_kota || '').toLowerCase().includes(searchQ);
                    if (!matchName && !matchCode && !matchCapital) return;
                }

                const isSelected = (region.kode === selectedRegionCode);
                const isCurrent = (region.kode === '35.10');
                const isDimmed = (activeBreakoutCityCode !== null && region.kode !== activeBreakoutCityCode);
                const isHidden = (activeBreakoutCityCode !== null && region.kode === activeBreakoutCityCode);

                // Nilai agregat kota/kabupaten (Kota Malang: 251)
                let val = summary && summary.total_nilai !== undefined ? summary.total_nilai : Math.round(region.growth * 10);
                if (pinDisplayMode === 'rank') {
                    const sorted = [...jatimRegions].sort((a, b) => b.growth - a.growth);
                    val = '#' + (sorted.findIndex(r => r.kode === region.kode) + 1);
                } else if (pinDisplayMode === 'code') {
                    val = region.kode.replace('35.', '');
                }

                const tier = getValueTier(val);
                const shortName = region.nama.replace('Kabupaten ', 'Kab. ').replace('Kota ', 'Kota ');

                const haloIcon = L.divIcon({
                    className: 'custom-gis-halo',
                    html: `
                        <div class="gis-halo-pin size-lg ${tier.tierClass} ${isSelected ? 'selected' : ''} ${isDimmed ? 'dimmed' : ''}" data-code="${region.kode}" title="${region.nama}: ${val} (Klik untuk pecah kecamatan)">
                            <div class="halo-ring"></div>
                            <div class="halo-core">
                                <span class="halo-num">${val}</span>
                            </div>
                            <div class="gis-halo-nametag">${shortName}</div>
                        </div>
                    `,
                    iconSize: [52, 52],
                    iconAnchor: [26, 26],
                    popupAnchor: [0, -26]
                });

                const marker = L.marker([lat, lng], {
                    icon: haloIcon,
                    zIndexOffset: isSelected ? 1200 : (isCurrent ? 800 : 100),
                    opacity: isHidden ? 0 : 1
                });

                const popupHtml = `
                    <div class="osm-enterprise-popup">
                        <div class="popup-head">
                            <span class="popup-badge" style="background:${tier.color}; color:#fff;">KELAS ${tier.cls} • ${tier.name.toUpperCase()}</span>
                            <span class="popup-type">KODE: ${region.kode}</span>
                        </div>
                        <h4 class="popup-title">${region.nama}</h4>
                        <div class="popup-stat-grid">
                            <div class="popup-stat">
                                <span class="stat-lbl">Total Indikator Spasial</span>
                                <strong class="stat-val" style="color:${tier.color}; font-size:16px;">${val}</strong>
                            </div>
                            <div class="popup-stat">
                                <span class="stat-lbl">Pertumbuhan Ekonomi</span>
                                <strong class="stat-val">${formatNum(region.growth, 2)}%</strong>
                            </div>
                            <div class="popup-stat">
                                <span class="stat-lbl">Jumlah Kecamatan</span>
                                <strong class="stat-val">${summary ? summary.jumlah_kecamatan : '-'} Wilayah</strong>
                            </div>
                            <div class="popup-stat">
                                <span class="stat-lbl">Luas Wilayah Resmi</span>
                                <strong class="stat-val">${formatNum(region.luas, 0)} km²</strong>
                            </div>
                            <div class="popup-stat" style="grid-column: 1 / 3;">
                                <span class="stat-lbl">Titik Koordinat Centroid</span>
                                <span class="stat-coord" style="font-family:monospace;">${lat.toFixed(5)}° S, ${lng.toFixed(5)}° E</span>
                            </div>
                        </div>
                        <div class="popup-actions">
                            <button class="popup-btn primary" onclick="window.__selectAndBreakoutCity('${region.kode}')">Pecah Kecamatan</button>
                            <button class="popup-btn secondary" onclick="window.__selectRegionFromMap('${region.kode}')">Lihat Profil</button>
                        </div>
                    </div>
                `;

                marker.bindPopup(popupHtml);

                // Klik kota langsung memicu sorotan dan animasi pecah ke kecamatan
                marker.on('click', () => {
                    selectAndBreakoutCity(region.kode);
                });

                markerLayers[region.kode] = marker;
                marker.addTo(layerMarkers);
            });
        }

        // 4. Render Sub-District Markers: Hanya terpecah saat kota dipilih, animasi memancar keluar dari titik pusat kota
        function renderDistrictsForCity(cityCode, animate = true) {
            if (!layerDistricts || !map) return;
            layerDistricts.clearLayers();

            const searchQ = searchInput ? searchInput.value.toLowerCase().trim() : '';

            const summary = jatimRegenciesSummary.find(s => s.kode === cityCode);
            const reg = jatimRegions.find(r => r.kode === cityCode);
            const cityLat = (summary && summary.lat) ? summary.lat : (reg ? reg.lat : -7.9785);
            const cityLng = (summary && summary.lng) ? summary.lng : (reg ? reg.lng : 112.6315);
            const cityName = reg ? reg.nama : (summary ? summary.nama : 'Kota/Kabupaten');

            // Ambil daftar kecamatan KHUSUS untuk kota yang dipilih
            let list = [];
            if (jatimAllKecamatan && jatimAllKecamatan.length > 0) {
                list = jatimAllKecamatan.filter(k => k.kode_kab === cityCode);
            }
            // Fallback untuk Banyuwangi jika belum termuat
            if (list.length === 0 && cityCode === '35.10' && districtList && districtList.length > 0) {
                list = districtList.map(d => ({
                    id: d.id || '35.10',
                    nama: d.name || d.nama_kecamatan || '',
                    kode_kab: '35.10',
                    nama_kab: 'Kabupaten Banyuwangi',
                    lat: parseFloat(d.lat || d.garis_lintang || 0),
                    lng: parseFloat(d.lng || d.garis_bujur || 0),
                    nilai: Math.round(parseFloat(d.share !== undefined ? d.share : (d.porsi_pdrb || 5)) * 2)
                }));
            }

            if (searchQ) {
                list = list.filter(k => k.nama.toLowerCase().includes(searchQ));
            }

            // Hitung titik koordinat pixel pusat kota pada layar peta
            let parentPt = null;
            try {
                parentPt = map.latLngToLayerPoint([cityLat, cityLng]);
            } catch (e) {
                parentPt = null;
            }

            list.forEach((k, idx) => {
                const lat = parseFloat(k.lat);
                const lng = parseFloat(k.lng);
                if (!lat || !lng) return;

                const val = parseInt(k.nilai !== undefined ? k.nilai : 5, 10);
                const name = k.nama;
                const isSelected = (name === selectedDistrictName);

                const tier = getValueTier(val);
                const isLarge = val >= 100;
                const sizeClass = isLarge ? 'size-lg' : '';

                // Hitung lintasan vektor animasi pecah dari titik pusat kota menuju titik kecamatan
                let breakoutStyle = '';
                let breakoutClass = '';
                if (animate && parentPt) {
                    try {
                        const kecPt = map.latLngToLayerPoint([lat, lng]);
                        const dx = Math.round(parentPt.x - kecPt.x);
                        const dy = Math.round(parentPt.y - kecPt.y);
                        if (!isNaN(dx) && !isNaN(dy)) {
                            const delayMs = Math.min(Math.round(idx * 22), 260);
                            breakoutStyle = `--origin-dx: ${dx}px; --origin-dy: ${dy}px; animation-delay: ${delayMs}ms;`;
                            breakoutClass = 'cluster-bursting';
                        }
                    } catch (e) {}
                }

                const iconSize = isLarge ? [52, 52] : [44, 44];
                const iconAnchor = isLarge ? [26, 26] : [22, 22];
                const popupAnchor = isLarge ? [0, -26] : [0, -22];

                const kecIcon = L.divIcon({
                    className: `custom-gis-halo ${breakoutClass}`,
                    html: `
                        <div class="gis-halo-pin ${sizeClass} ${tier.tierClass} ${isSelected ? 'selected' : ''}" data-name="${name}" style="${breakoutStyle}" title="Kecamatan ${name} (${cityName}): Nilai ${val}">
                            <div class="halo-ring"></div>
                            <div class="halo-core">
                                <span class="halo-num">${val}</span>
                            </div>
                            <div class="gis-halo-nametag">${name}</div>
                        </div>
                    `,
                    iconSize: iconSize,
                    iconAnchor: iconAnchor,
                    popupAnchor: popupAnchor
                });

                const marker = L.marker([lat, lng], {
                    icon: kecIcon,
                    zIndexOffset: isSelected ? 2000 : (isLarge ? 1200 : 800)
                });

                const kecPopup = `
                    <div class="osm-enterprise-popup">
                        <div class="popup-head">
                            <span class="popup-badge" style="background:${tier.color}; color:#fff;">KELAS ${tier.cls} • ${tier.name.toUpperCase()}</span>
                            <span class="popup-type">${cityName.toUpperCase()} (${cityCode})</span>
                        </div>
                        <h4 class="popup-title">Kecamatan ${name}</h4>
                        <div class="popup-stat-grid">
                            <div class="popup-stat">
                                <span class="stat-lbl">Nilai Indikator Spasial</span>
                                <strong class="stat-val" style="color:${tier.color}; font-size:16px;">${val}</strong>
                            </div>
                            <div class="popup-stat">
                                <span class="stat-lbl">Tingkat Klasifikasi</span>
                                <strong class="stat-val">${tier.name}</strong>
                            </div>
                            <div class="popup-stat">
                                <span class="stat-lbl">Kabupaten / Kota</span>
                                <strong class="stat-val">${cityName}</strong>
                            </div>
                            <div class="popup-stat">
                                <span class="stat-lbl">Status Wilayah</span>
                                <strong class="stat-val">Kecamatan Aktif</strong>
                            </div>
                            <div class="popup-stat" style="grid-column: 1 / 3;">
                                <span class="stat-lbl">Titik Koordinat Geografis</span>
                                <span class="stat-coord" style="font-family:monospace;">${lat.toFixed(5)}° S, ${lng.toFixed(5)}° E</span>
                            </div>
                        </div>
                        <div class="popup-actions">
                            <button class="popup-btn primary" onclick="window.__selectDistrictFromMap('${name}')">Lihat Profil</button>
                            <button class="popup-btn secondary" onclick="window.__zoomDistrictFromMap('${name}')">Pusatkan Titik</button>
                        </div>
                    </div>
                `;

                marker.bindPopup(kecPopup);
                marker.on('click', () => {
                    selectDistrict(name, k);
                });

                marker.addTo(layerDistricts);
            });

            if (animate) {
                setTimeout(() => {
                    document.querySelectorAll('.custom-gis-halo.cluster-bursting').forEach(el => {
                        el.classList.remove('cluster-bursting');
                    });
                }, 850);
            }
        }

        // Backward compatibility
        function renderDistricts(animate = false) {
            if (activeBreakoutCityCode) {
                renderDistrictsForCity(activeBreakoutCityCode, animate);
            }
        }

        // 5. Render Legend (4 Kelas Terkalibrasi Kuartil/Interval)
        function renderLegend() {
            if (!legendContainer) return;

            const c = get4Classes();
            const decimals = 2;
            const unit = '%';

            const barOrder = legendReversed ? [4, 3, 2, 1] : [1, 2, 3, 4];
            const classOrder = legendReversed ? [4, 3, 2, 1] : [1, 2, 3, 4];

            const barHtml = `
                <div class="legend-color-bar" title="Rentang 4 kelas pertumbuhan: ${formatNum(c.minV, decimals)}${unit} s.d. ${formatNum(c.maxV, decimals)}${unit}">
                    ${barOrder.map(cl => `<span class="l${cl}" style="background:${classColors[cl]} !important;"></span>`).join('')}
                </div>
                <div class="legend-bar-labels">
                    <small>${legendReversed ? 'Tinggi' : 'Rendah'} (${formatNum(legendReversed ? c.maxV : c.minV, decimals)}${unit})</small>
                    <small>${legendReversed ? 'Rendah' : 'Tinggi'} (${formatNum(legendReversed ? c.minV : c.maxV, decimals)}${unit})</small>
                </div>
            `;

            const itemsHtml = classOrder.map(cls => {
                const low = c.minV + (c.step * (cls - 1));
                const high = c.minV + (c.step * cls);
                const color = classColors[cls];
                const name = classNames[cls];
                return `
                    <div class="legend-row" data-class="${cls}" title="Wilayah Kelas ${cls} (${name})">
                        <div class="legend-row-main">
                            <span class="legend-chip l${cls}" style="background:${color} !important;"></span>
                            <b class="legend-class-lbl">Kelas ${cls} <small style="font-weight:500; color:#64748b;">(${name})</small></b>
                        </div>
                        <span class="legend-val-range">${formatNum(low, decimals)} – ${formatNum(high, decimals)}${unit}</span>
                    </div>`;
            }).join('');

            legendContainer.innerHTML = barHtml + itemsHtml;
        }

        // 6. Select Region (Kabupaten / Kota)
        function selectRegion(code, openPopup = false) {
            selectedRegionCode = code;
            const reg = jatimRegions.find(r => r.kode === code);
            if (!reg) return;

            const cls = getRegionClass(reg);
            const color = classColors[cls];
            const clsName = classNames[cls];

            // Update Breadcrumb & Header
            const breadKab = document.getElementById('map-bread-kab');
            const breadLevel = document.getElementById('map-bread-level');
            if (breadKab) breadKab.textContent = reg.nama;
            if (breadLevel) breadLevel.textContent = `Kode: ${reg.kode} • Kelas ${cls} (${clsName})`;

            if (locDetailName) locDetailName.textContent = reg.nama;
            if (locDetailLevel) locDetailLevel.innerHTML = `PROFIL WILAYAH • <span style="color:#fef08a; font-weight:700;">KELAS ${cls} (${clsName.toUpperCase()})</span>`;
            if (locDetailCode) locDetailCode.textContent = `Kode Kemendagri: ${reg.kode} • Sumber Resmi BIG / Kemendagri`;

            // Compute ranking in East Java
            const sortedByGrowth = [...jatimRegions].sort((a, b) => b.growth - a.growth);
            const rank = sortedByGrowth.findIndex(r => r.kode === code) + 1;
            const avgGrowth = (jatimRegions.reduce((sum, r) => sum + r.growth, 0) / (jatimRegions.length || 1));
            const gap = reg.growth - avgGrowth;

            // Render Right-Hand Location Aside
            if (locBodyContainer) {
                let seriesSvg = '';
                if (Array.isArray(reg.series) && reg.series.length > 0) {
                    const sMin = Math.min(...reg.series);
                    const sMax = Math.max(...reg.series);
                    const sRange = (sMax - sMin) || 1;
                    const w = 210;
                    const h = 42;
                    const points = reg.series.map((val, idx) => {
                        const x = (idx / (reg.series.length - 1)) * w;
                        const y = h - ((val - sMin) / sRange) * (h - 8) - 4;
                        return `${x.toFixed(1)},${y.toFixed(1)}`;
                    }).join(' ');

                    seriesSvg = `
                        <div style="background:#ffffff; border:1px solid #e2e8f0; border-radius:8px; padding:8px 10px; margin:10px 0;">
                            <div style="display:flex; justify-content:space-between; font-size:10.5px; color:#64748b; margin-bottom:4px;">
                                <span>Tren Historis Pertumbuhan (2016–2025)</span>
                                <strong>${reg.series.length} Tahun</strong>
                            </div>
                            <svg width="100%" height="45" viewBox="0 0 210 45" style="overflow:visible;">
                                <polyline fill="none" stroke="${color}" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" points="${points}" />
                            </svg>
                        </div>
                    `;
                }

                locBodyContainer.innerHTML = `
                    <div class="location-score">
                        <div>
                            <span>Pertumbuhan Ekonomi (2025)</span>
                            <strong style="color:${color};">${formatNum(reg.growth, 2)}%</strong>
                        </div>
                        <span class="badge" style="background:${color}; color:#fff; font-weight:700;">
                            Kelas ${cls} • #${rank} / 38
                        </span>
                    </div>

                    <div class="spatial-position">
                        <span>
                            <small>Selisih terhadap rata-rata Jatim (${formatNum(avgGrowth, 2)}%)</small>
                            <strong class="${gap >= 0 ? 'positive' : 'negative'}">${gap >= 0 ? '+' : ''}${formatNum(gap, 2)} pp</strong>
                        </span>
                        <div><i style="width: ${Math.min(100, Math.max(12, ((reg.growth - 1.5) / 5) * 100))}%; background:${color};"></i></div>
                    </div>

                    <div class="data-list">
                        <span><small>Ibu Kota / Pusat</small><strong>${reg.ibu_kota || '-'}</strong></span>
                        <span><small>Klasifikasi GIS</small><strong style="color:${color};">Kelas ${cls} (${clsName})</strong></span>
                        <span><small>Luas Wilayah Resmi</small><strong>${formatNum(reg.luas, 0)} km²</strong></span>
                        <span><small>Perubahan YoY</small><strong>${reg.change >= 0 ? '+' : ''}${formatNum(reg.change, 2)} pp</strong></span>
                        <span><small>Titik Centroid (Lintang)</small><strong style="font-family:monospace;">${reg.lat.toFixed(4)}° S</strong></span>
                        <span><small>Titik Centroid (Bujur)</small><strong style="font-family:monospace;">${reg.lng.toFixed(4)}° E</strong></span>
                        <span><small>Batas BBox Latitude</small><strong style="font-size:10px; font-family:monospace;">${reg.bounds[0][0].toFixed(4)}° s.d. ${reg.bounds[1][0].toFixed(4)}°</strong></span>
                        <span><small>Batas BBox Longitude</small><strong style="font-size:10px; font-family:monospace;">${reg.bounds[0][1].toFixed(4)}° s.d. ${reg.bounds[1][1].toFixed(4)}°</strong></span>
                    </div>

                    ${seriesSvg}

                    <div style="display:flex; gap:8px; margin-top:12px;">
                        <button class="button secondary" id="btn-export-reg-profile" style="flex:1;">Unduh Profil CSV</button>
                        <button class="button" id="btn-zoom-to-sel" style="background:#142d4e; color:#fff;">Pusatkan Peta</button>
                    </div>
                `;

                const btnExp = document.getElementById('btn-export-reg-profile');
                if (btnExp) {
                    btnExp.addEventListener('click', () => {
                        downloadCSV(`profil-${reg.nama.toLowerCase().replace(/\\s+/g, '-')}.csv`, [
                            ['Kode Wilayah', 'Nama Wilayah', 'Tipe', 'Kelas GIS', 'Ibu Kota', 'Lintang', 'Bujur', 'Pertumbuhan (%)', 'Perubahan YoY (pp)', 'Luas (km2)'],
                            [reg.kode, reg.nama, reg.tipe, `Kelas ${cls} (${clsName})`, reg.ibu_kota, reg.lat, reg.lng, reg.growth, reg.change, reg.luas]
                        ]);
                    });
                }

                const btnZoomSel = document.getElementById('btn-zoom-to-sel');
                if (btnZoomSel) {
                    btnZoomSel.addEventListener('click', () => {
                        if (polygonLayers[code] && polygonLayers[code].getBounds) {
                            map.fitBounds(polygonLayers[code].getBounds(), { padding: [35, 35], maxZoom: 12 });
                        } else if (map && reg.bounds) {
                            map.fitBounds(reg.bounds, { padding: [35, 35], maxZoom: 12 });
                        }
                    });
                }
            }

            // Update Poligon Riil GeoJSON
            Object.keys(polygonLayers).forEach(k => {
                const ply = polygonLayers[k];
                if (!ply) return;
                const r = jatimRegions.find(item => item.kode === k);
                const rCls = getRegionClass(r);
                const rColor = classColors[rCls] || '#2563eb';
                const isCur = (k === '35.10');
                const isSel = (k === code);

                ply.setStyle({
                    fillColor: rColor,
                    fillOpacity: isSel ? 0.62 : (isCur ? 0.44 : 0.30),
                    weight: isSel ? 3.5 : (isCur ? 2.5 : 1.4),
                    color: isSel ? '#0f172a' : (isCur ? '#0d9488' : '#334155')
                });

                if (isSel && !L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
                    ply.bringToFront();
                }
            });

            // Tidak ada kotak-kotak bounding box

            // Update active state pada balon pin GIS
            document.querySelectorAll('.gis-balloon-pin').forEach(el => {
                if (el.getAttribute('data-code') === code) {
                    el.classList.add('selected');
                } else {
                    el.classList.remove('selected');
                }
            });

            if (openPopup && markerLayers[code]) {
                markerLayers[code].openPopup();
            }

            renderMapComparisonChart();
        }

        // 7. Select District (Kecamatan di Banyuwangi)
        function selectDistrict(name, kecObj = null) {
            selectedDistrictName = name;
            let dist = kecObj;
            if (!dist && jatimAllKecamatan) {
                dist = jatimAllKecamatan.find(k => k.nama === name);
            }
            if (!dist && districtList) {
                dist = districtList.find(d => (d.name || d.nama_kecamatan) === name);
            }
            if (!dist) return;

            const lat = parseFloat(dist.lat || dist.garis_lintang || 0);
            const lng = parseFloat(dist.lng || dist.garis_bujur || 0);
            const val = dist.nilai !== undefined ? dist.nilai : (dist.share || dist.porsi_pdrb || 5);
            const namaKab = dist.nama_kab || 'Kabupaten Banyuwangi';
            const kodeKab = dist.kode_kab || '35.10';
            const tier = getValueTier(val);

            // Update state aktif pada pin halo kecamatan
            document.querySelectorAll('.gis-halo-pin').forEach(el => {
                if (el.getAttribute('data-name') === name) {
                    el.classList.add('selected');
                } else {
                    el.classList.remove('selected');
                }
            });

            if (locDetailName) locDetailName.textContent = `Kecamatan ${name}`;
            if (locDetailLevel) locDetailLevel.textContent = 'PROFIL WILAYAH • KECAMATAN';
            if (locDetailCode) locDetailCode.textContent = `${namaKab} (Kode: ${kodeKab})`;

            if (locBodyContainer) {
                locBodyContainer.innerHTML = `
                    <div class="location-score">
                        <div>
                            <span>Nilai Indikator Spasial</span>
                            <strong style="color:${tier.color};">${val}</strong>
                        </div>
                        <span class="badge info">Kelas ${tier.cls} (${tier.name})</span>
                    </div>

                    <div class="data-list" style="margin-top:14px;">
                        <span><small>Wilayah Induk</small><strong>${namaKab}</strong></span>
                        <span><small>Kode Wilayah</small><strong style="font-family:monospace;">${dist.id || kodeKab}</strong></span>
                        <span><small>Tingkat Aktivitas</small><strong>${tier.name}</strong></span>
                        <span><small>Status Data</small><strong>Terintegrasi BPS / Kemendagri</strong></span>
                        <span><small>Garis Lintang</small><strong style="font-family:monospace;">${lat ? lat.toFixed(5) + '° S' : '-'}</strong></span>
                        <span><small>Garis Bujur</small><strong style="font-family:monospace;">${lng ? lng.toFixed(5) + '° E' : '-'}</strong></span>
                    </div>

                    <div style="display:flex; gap:8px; margin-top:14px;">
                        <button class="button secondary" id="btn-export-dist-csv" style="flex:1;">Unduh CSV</button>
                        <button class="button" id="btn-zoom-dist" style="background:#0d9488; color:#fff;">Pusatkan Kecamatan</button>
                    </div>
                `;

                const btnExp = document.getElementById('btn-export-dist-csv');
                if (btnExp) {
                    btnExp.addEventListener('click', () => {
                        downloadCSV(`kecamatan-${name.toLowerCase().replace(/[^a-z0-9]/g, '-')}.csv`, [
                            ['Kecamatan', 'Kab/Kota', 'Nilai Spasial', 'Lintang', 'Bujur'],
                            [name, namaKab, val, lat, lng]
                        ]);
                    });
                }

                const btnZoom = document.getElementById('btn-zoom-dist');
                if (btnZoom && lat && lng && map) {
                    btnZoom.addEventListener('click', () => {
                        map.setView([lat, lng], 13);
                    });
                }
            }
        }

        // Global functions for marker popup clicks
        window.__selectRegionFromMap = function(code) {
            selectRegion(code);
        };
        window.__selectAndBreakoutCity = function(code) {
            selectAndBreakoutCity(code);
        };
        window.__resetToProvinceView = function() {
            resetToProvinceView(true);
        };
        window.__zoomRegionFromMap = function(code) {
            selectRegion(code, true);
            if (polygonLayers[code] && polygonLayers[code].getBounds) {
                map.fitBounds(polygonLayers[code].getBounds(), { padding: [40, 40], maxZoom: 12 });
            } else {
                const reg = jatimRegions.find(r => r.kode === code);
                if (reg && map && reg.bounds) {
                    map.fitBounds(reg.bounds, { padding: [40, 40], maxZoom: 12 });
                }
            }
        };
        window.__selectDistrictFromMap = function(name) {
            selectDistrict(name);
        };
        window.__zoomDistrictFromMap = function(name) {
            const k = (jatimAllKecamatan && jatimAllKecamatan.find(x => x.nama === name)) ||
                      (districtList && districtList.find(d => (d.name || d.nama_kecamatan) === name));
            if (k && map) {
                const lat = parseFloat(k.lat || k.garis_lintang);
                const lng = parseFloat(k.lng || k.garis_bujur);
                if (lat && lng) map.setView([lat, lng], 13);
            }
        };

        // 8. Layer Controls & Toggles
        if (cbOsm) {
            cbOsm.addEventListener('change', () => {
                if (!map || !currentBasemapLayer) return;
                if (cbOsm.checked) {
                    if (!map.hasLayer(currentBasemapLayer)) map.addLayer(currentBasemapLayer);
                } else {
                    if (map.hasLayer(currentBasemapLayer)) map.removeLayer(currentBasemapLayer);
                }
            });
        }
        if (cbBoundaries) {
            cbBoundaries.addEventListener('change', () => {
                if (!map || !layerGeoJsonBoundaries) return;
                if (cbBoundaries.checked) {
                    map.addLayer(layerGeoJsonBoundaries);
                } else {
                    map.removeLayer(layerGeoJsonBoundaries);
                }
            });
        }
        if (cbMarkers) {
            cbMarkers.addEventListener('change', () => {
                if (!map || !layerMarkers) return;
                if (cbMarkers.checked) {
                    map.addLayer(layerMarkers);
                } else {
                    map.removeLayer(layerMarkers);
                }
            });
        }
        if (cbLabels) {
            cbLabels.addEventListener('change', () => {
                const pins = document.querySelectorAll('.custom-gis-balloon, .custom-osm-pin, .custom-kec-pin');
                pins.forEach(p => p.style.display = cbLabels.checked ? 'block' : 'none');
            });
        }
        if (cbHotspot) {
            cbHotspot.addEventListener('change', () => {
                if (!map || !layerHotspots) return;
                if (cbHotspot.checked) {
                    layerHotspots.clearLayers();
                    const highGrowths = jatimRegions.filter(r => r.growth >= 5.8);
                    highGrowths.forEach(r => {
                        const circle = L.circle([r.lat, r.lng], {
                            radius: 18000,
                            color: '#10b981',
                            fillColor: '#34d399',
                            fillOpacity: 0.2,
                            weight: 1.5,
                            dashArray: '3, 4'
                        });
                        circle.bindTooltip(`Kawasan Pertumbuhan Tinggi: <strong>${r.nama} (${formatNum(r.growth, 2)}%)</strong>`);
                        circle.addTo(layerHotspots);
                    });
                    map.addLayer(layerHotspots);
                    if (hotspotBox) hotspotBox.style.display = 'flex';
                } else {
                    map.removeLayer(layerHotspots);
                    if (hotspotBox) hotspotBox.style.display = 'none';
                }
            });
        }

        // Tampilan Angka Balon GIS
        if (pinDisplaySelect) {
            pinDisplaySelect.addEventListener('change', () => {
                pinDisplayMode = pinDisplaySelect.value;
                renderMarkers();
            });
        }

        // Quick Focus Buttons & Breadcrumb / Return Navigation
        if (btnFocusJatim) {
            btnFocusJatim.addEventListener('click', () => {
                resetToProvinceView(true);
            });
        }
        if (btnFocusMalang) {
            btnFocusMalang.addEventListener('click', () => {
                selectAndBreakoutCity('35.73');
            });
        }
        if (btnFocusBanyuwangi) {
            btnFocusBanyuwangi.addEventListener('click', () => {
                selectAndBreakoutCity('35.10');
            });
        }
        if (btnFocusSurabaya) {
            btnFocusSurabaya.addEventListener('click', () => {
                selectAndBreakoutCity('35.78');
            });
        }
        if (btnBackToProvince) {
            btnBackToProvince.addEventListener('click', () => {
                resetToProvinceView(true);
            });
        }
        if (btnResetLocation) {
            btnResetLocation.addEventListener('click', () => {
                resetToProvinceView(true);
            });
        }
        if (mapBreadKab) {
            mapBreadKab.addEventListener('click', () => {
                resetToProvinceView(true);
            });
        }

        // Level Select
        if (levelSelect) {
            levelSelect.addEventListener('change', () => {
                mapLevel = levelSelect.value;
                if (layerGeoJsonBoundaries && !map.hasLayer(layerGeoJsonBoundaries)) {
                    layerGeoJsonBoundaries.addTo(map);
                }
                if (mapLevel === 'Jawa Timur') {
                    if (layerMarkers && !map.hasLayer(layerMarkers)) layerMarkers.addTo(map);
                    if (map) map.setView([-7.75, 112.75], 8);
                    updateMarkerVisibilityByZoom(false);
                } else if (mapLevel === 'Kecamatan') {
                    selectRegion('35.10', true);
                    if (polygonLayers['35.10'] && polygonLayers['35.10'].getBounds) {
                        map.fitBounds(polygonLayers['35.10'].getBounds(), { padding: [30, 30] });
                    } else if (map) {
                        map.fitBounds([[-8.78, 113.88], [-7.72, 114.61]], { padding: [30, 30] });
                    }
                    setTimeout(() => updateMarkerVisibilityByZoom(true), 350);
                } else {
                    // Gabungan
                    if (layerMarkers && !map.hasLayer(layerMarkers)) layerMarkers.addTo(map);
                    if (layerDistricts && !map.hasLayer(layerDistricts)) layerDistricts.addTo(map);
                }
                renderMapComparisonChart();
            });
        }

        // Search Input
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                renderMarkers();
                renderDistricts();
            });
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    const q = searchInput.value.toLowerCase().trim();
                    const match = jatimRegions.find(r => r.nama.toLowerCase().includes(q) || r.kode.includes(q));
                    if (match) {
                        selectRegion(match.kode, true);
                        if (polygonLayers[match.kode] && polygonLayers[match.kode].getBounds) {
                            map.fitBounds(polygonLayers[match.kode].getBounds(), { padding: [35, 35], maxZoom: 12 });
                        } else if (map && match.bounds) {
                            map.fitBounds(match.bounds, { padding: [35, 35], maxZoom: 12 });
                        }
                    }
                }
            });
        }

        // Zoom buttons (+ / -)
        if (btnZoomIn && map) {
            btnZoomIn.addEventListener('click', () => map.zoomIn());
        }
        if (btnZoomOut && map) {
            btnZoomOut.addEventListener('click', () => map.zoomOut());
        }

        // Reverse legend
        if (btnReverseLegend) {
            btnReverseLegend.addEventListener('click', (e) => {
                e.preventDefault();
                legendReversed = !legendReversed;
                btnReverseLegend.classList.toggle('active', legendReversed);
                renderLegend();
            });
        }

        // Reset Location
        if (btnResetLocation) {
            btnResetLocation.addEventListener('click', () => {
                selectRegion('35.10');
                if (map) map.setView([-7.75, 112.75], 8);
            });
        }

        // Export All Map Data CSV
        if (btnExportMap) {
            btnExportMap.addEventListener('click', () => {
                downloadCSV('peta-ekonomi-jawa-timur-sidapeda.csv', [
                    ['Kode Wilayah', 'Nama Wilayah', 'Tipe', 'Kelas GIS', 'Ibu Kota', 'Latitude', 'Longitude', 'Pertumbuhan (%)', 'Perubahan YoY (pp)', 'Luas (km2)'],
                    ...jatimRegions.map(r => {
                        const cls = getRegionClass(r);
                        return [r.kode, r.nama, r.tipe, `Kelas ${cls} (${classNames[cls]})`, r.ibu_kota, r.lat, r.lng, r.growth, r.change, r.luas];
                    })
                ]);
            });
        }

        // Mode tabs
        if (modeTabs) {
            modeTabs.querySelectorAll('button').forEach(btn => {
                btn.addEventListener('click', function () {
                    modeTabs.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    activeMode = this.getAttribute('data-mode');
                    const lbl = document.getElementById('active-map-mode-label');
                    if (lbl) lbl.textContent = activeMode.toUpperCase();
                });
            });
        }

        // 9. Bottom Comparison Chart
        const metricSelect = document.getElementById('map-chart-metric-select');
        const mapChartTitle = document.getElementById('map-chart-title');
        const mapChartDesc = document.getElementById('map-chart-desc');
        const mapChartContainer = document.getElementById('map-comparison-chart-container');

        function renderMapComparisonChart() {
            if (!mapChartContainer) return;
            const metric = metricSelect ? metricSelect.value : 'growth';

            const isKecamatanOnly = (mapLevel === 'Kecamatan');

            if (isKecamatanOnly) {
                if (mapChartTitle) mapChartTitle.textContent = 'Grafik Komparasi Antar-Kecamatan di Kabupaten Banyuwangi';
                if (mapChartDesc) mapChartDesc.textContent = 'Peringkat capaian seluruh kecamatan berdasarkan porsi PDRB dan pertumbuhan ekonomi.';

                const sortedKec = [...districtList].sort((a, b) => {
                    const valA = parseFloat(a.share !== undefined ? a.share : (a.porsi_pdrb || 0));
                    const valB = parseFloat(b.share !== undefined ? b.share : (b.porsi_pdrb || 0));
                    return valB - valA;
                });

                const barData = sortedKec.map(d => {
                    const name = d.name || d.nama_kecamatan;
                    const val = parseFloat(d.share !== undefined ? d.share : (d.porsi_pdrb || 0));
                    const isSel = (name === selectedDistrictName);
                    return {
                        name,
                        val,
                        color: isSel ? '#0d9488' : '#64748b',
                        formattedVal: formatNum(val, 2) + '%'
                    };
                });

                const maxVal = Math.max(...barData.map(d => d.val)) || 10;

                mapChartContainer.innerHTML = `
                    <div class="bars-scroll" style="display:flex; align-items:flex-end; gap:6px; height:180px; padding:10px 5px; overflow-x:auto;">
                        ${barData.map(d => `
                            <div class="bar-col" style="display:flex; flex-direction:column; align-items:center; flex:1; min-width:32px; height:100%; justify-content:flex-end; cursor:pointer;" onclick="window.__selectDistrictFromMap('${d.name}')" title="${d.name}: ${d.formattedVal}">
                                <span style="font-size:9px; color:#64748b; margin-bottom:4px;">${d.val.toFixed(1)}%</span>
                                <div style="width:18px; height:${Math.max(6, (d.val / maxVal) * 120)}px; background:${d.color}; border-radius:3px 3px 0 0; transition:height 0.2s;"></div>
                                <span style="font-size:9.5px; color:#1e293b; margin-top:4px; max-width:34px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${d.name}</span>
                            </div>
                        `).join('')}
                    </div>
                `;

            } else {
                if (mapChartTitle) mapChartTitle.textContent = 'Grafik Komparasi 38 Kabupaten / Kota se-Jawa Timur';
                if (mapChartDesc) mapChartDesc.textContent = 'Peringkat capaian seluruh wilayah berdasarkan indikator ekonomi spasial (4 Kelas Klasifikasi).';

                const sortedRegs = [...jatimRegions].sort((a, b) => {
                    const valA = parseFloat(a[metric] || 0);
                    const valB = parseFloat(b[metric] || 0);
                    return valB - valA;
                });

                const maxVal = Math.max(...sortedRegs.map(r => parseFloat(r[metric] || 0))) || 10;

                mapChartContainer.innerHTML = `
                    <div class="bars-scroll" style="display:flex; align-items:flex-end; gap:4px; height:190px; padding:10px 5px; overflow-x:auto;">
                        ${sortedRegs.map(r => {
                            const val = parseFloat(r[metric] || 0);
                            const cls = getRegionClass(r);
                            const col = classColors[cls] || '#2563eb';
                            const isSel = (r.kode === selectedRegionCode);
                            const isCur = (r.kode === '35.10');
                            const barColor = isSel ? '#0f172a' : (isCur ? '#0d9488' : col);
                            const suffix = (metric === 'growth') ? '%' : ((metric === 'change') ? ' pp' : ((metric === 'pdrb') ? ' M' : ' km²'));
                            return `
                                <div class="bar-col" style="display:flex; flex-direction:column; align-items:center; flex:1; min-width:28px; height:100%; justify-content:flex-end; cursor:pointer; opacity:${isSel ? 1 : 0.85};" onclick="window.__zoomRegionFromMap('${r.kode}')" title="${r.nama} (Kelas ${cls}): ${formatNum(val, 2)}${suffix}">
                                    <span style="font-size:8.5px; color:#64748b; margin-bottom:3px;">${val.toFixed(1)}</span>
                                    <div style="width:14px; height:${Math.max(5, (val / maxVal) * 125)}px; background:${barColor}; border-radius:3px 3px 0 0; ${isSel ? 'border:1.5px solid #fef08a;' : ''}"></div>
                                    <span style="font-size:9px; font-weight:${isSel || isCur ? 700 : 500}; color:${isSel ? '#0f172a' : '#475569'}; margin-top:4px; max-width:28px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${r.kode.replace('35.', '')}</span>
                                </div>
                            `;
                        }).join('')}
                    </div>
                `;
            }
        }

        if (metricSelect) {
            metricSelect.addEventListener('change', renderMapComparisonChart);
        }

        // Initial Executions
        renderLegend();
        renderMarkers();
        updateMarkerVisibilityByZoom(false); // Zoom out awal: hanya kab/kota
        selectRegion('35.10');
        renderMapComparisonChart();

        // Load Real Administrative Boundaries GeoJSON (Hanya Batas Wilayah Riil)
        const geojsonUrl = window.__JATIM_GEOJSON_URL__ || 'assets/data/jatim_kab_kota.geojson';
        fetch(geojsonUrl)
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(data => {
                jatimGeoJson = data;
                renderGeoJsonBoundaries(data);
                selectRegion(selectedRegionCode);
            })
            .catch(err => {
                console.error('Gagal memuat batas wilayah riil GeoJSON:', err);
            });


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
        const catSelect = document.getElementById('ews-category-select');
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
        let activeCategory = (catSelect && catSelect.value) ? catSelect.value : 'all';
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

        // Always prioritize server-rendered page dataset (__EWS_DATA__) if available
        if (window.__EWS_DATA__ && Array.isArray(window.__EWS_DATA__)) {
            AppState.ews = window.__EWS_DATA__;
        }

        function renderEWS(skipCategoryApply = false) {
            let normalC = 0, warnC = 0, critC = 0, followedC = 0, totalMatching = 0;
            const currentCatFilter = window._activeCategoryFilter || '';

            // Master indicators only (exclude sub-indicators with dots)
            const masterList = (AppState.ews || []).filter(item => {
                const code = item.code || item.kode_indikator || '';
                return !code.includes('.');
            });

            masterList.forEach(item => {
                const val = Number(item.value !== undefined ? item.value : (item.nilai_terkini || 0));
                const th = Number(item.threshold !== undefined ? item.threshold : (item.ambang_batas || 0));
                const dir = item.direction || item.arah_risiko || 'above';
                const st = calcEwsStatus(val, th, dir);
                item.status = st;
                item.status_risiko = st;

                const code = item.code || item.kode_indikator || '';
                const prefix = code.split('-')[0].trim().toUpperCase();

                if (!currentCatFilter || prefix === currentCatFilter) {
                    totalMatching++;
                    if (st === 'Normal') normalC++;
                    else if (st === 'Waspada') warnC++;
                    else if (st === 'Kritis') critC++;

                    if (item.is_followed_up || item.sudah_ditindaklanjuti) followedC++;
                }
            });

            const statKritis = document.getElementById('stat-kritis-count');
            const statWaspada = document.getElementById('stat-waspada-count');
            const statNormal = document.getElementById('stat-normal-count');
            const statTotal = document.getElementById('stat-total-count');

            if (statKritis) statKritis.textContent = critC;
            if (statWaspada) statWaspada.textContent = warnC;
            if (statNormal) statNormal.textContent = normalC;
            if (statTotal) statTotal.textContent = totalMatching;

            // Trigger category/status filtering across table rows when not already invoked by applyCategoryFilter
            if (!skipCategoryApply && typeof window.applyCategoryFilter === 'function') {
                window.applyCategoryFilter(currentCatFilter);
            }

            if (distBars) {
                const distFiltered = masterList.filter(item => {
                    const code = item.code || item.kode_indikator || '';
                    const prefix = code.split('-')[0].trim().toUpperCase();
                    return (!currentCatFilter || prefix === currentCatFilter);
                }).slice(0, 8);

                distBars.innerHTML = distFiltered.map(item => {
                    const val = Number(item.value !== undefined ? item.value : (item.nilai_terkini || 0));
                    const th = Number(item.threshold !== undefined ? item.threshold : (item.ambang_batas || 0));
                    const dir = item.direction || item.arah_risiko || 'above';
                    const st = calcEwsStatus(val, th, dir);
                    const badgeCls = (st === 'Kritis') ? 'danger' : ((st === 'Waspada') ? 'warning' : 'success');
                    const diffPct = th !== 0 ? Math.abs((val - th) / th) * 100 : (val !== 0 ? 100 : 5);
                    const dist = Math.min(100, Math.max(6, Math.round(diffPct)));
                    const nm = item.name || item.nama_indikator || '';
                    return `
                    <div>
                        <span title="${nm}">${nm}</span>
                        <div><i class="${badgeCls}" style="width: ${dist}%"></i></div>
                        <span class="badge ${badgeCls}">${st}</span>
                    </div>
                `;
                }).join('');
            }

            const ewsChartContainer = document.getElementById('ews-comparison-chart-container');
            if (ewsChartContainer && masterList.length > 0) {
                const chartFiltered = masterList.filter(item => {
                    const code = item.code || item.kode_indikator || '';
                    const prefix = code.split('-')[0].trim().toUpperCase();
                    return (!currentCatFilter || prefix === currentCatFilter);
                }).slice(0, 10);

                const formatEwsVal = (numVal, unitStr, indCode) => {
                    const isRp = isIndicatorRupiah(unitStr, indCode);
                    if (isRp) {
                        const rpFmt = formatRupiahNumber(numVal);
                        return `Rp ${rpFmt || formatNum(numVal, 0)}`;
                    }
                    const u = (unitStr || '').trim();
                    return u ? `${formatNum(numVal, 2)} ${u}` : formatNum(numVal, 2);
                };

                const ewsBars = chartFiltered.map(item => {
                    const code = item.code || item.kode_indikator || '';
                    const val = Number(item.value !== undefined ? item.value : (item.nilai_terkini || 0));
                    const th = Number(item.threshold !== undefined ? item.threshold : (item.ambang_batas || 0));
                    const dir = item.direction || item.arah_risiko || 'above';
                    const unit = item.unit || item.satuan || '%';
                    const diffPct = th !== 0 ? ((val - th) / Math.abs(th)) * 100 : 0;
                    const st = calcEwsStatus(val, th, dir);
                    const badgeCls = (st === 'Kritis') ? 'danger' : ((st === 'Waspada') ? 'warning' : 'success');
                    return {
                        label: item.name || item.nama_indikator,
                        value: Math.min(100, Math.max(4, Math.abs(diffPct))),
                        formattedValue: formatEwsVal(val, unit, code),
                        subLabel: `Ambang: ${formatEwsVal(th, unit, code)} \u2022 Deviasi: ${diffPct >= 0 ? '+' : ''}${formatNum(diffPct, 1)}%`,
                        badge: st,
                        badgeClass: badgeCls,
                        color: (st === 'Kritis') ? 'linear-gradient(90deg, #ef4444, #f87171)' : ((st === 'Waspada') ? 'linear-gradient(90deg, #f59e0b, #fbbf24)' : 'linear-gradient(90deg, #10b981, #34d399)')
                    };
                });
                ewsChartContainer.innerHTML = createHorizontalBarChartSVG(ewsBars, { unit: '', decimals: 2, maxVal: 100 });
            }

            const pBar = document.getElementById('ews-progress-bar');
            const pTxt = document.getElementById('ews-progress-text');
            const pPct = document.getElementById('ews-progress-pct');
            const progressDenom = totalMatching || masterList.length;
            const pct = progressDenom ? Math.round((followedC / progressDenom) * 100) : 0;
            if (pBar) pBar.style.width = pct + '%';
            if (pTxt) pTxt.textContent = `${followedC} dari ${progressDenom} indikator ditandai`;
            if (pPct) pPct.textContent = pct + '%';
        }
        window._refreshEwsPublicVisuals = () => renderEWS(true);

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
            if (metaEl) metaEl.textContent = `${src} \u2022 ${code}`;
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

                                const unit = target.unit || target.satuan || '%';
                                if (tableRows) {
                                    const row = tableRows.querySelector(`.risk-row[data-code="${selectedAlertCode}"]`);
                                    if (row) {
                                        const newSt = target.status;
                                        row.setAttribute('data-status', newSt);
                                        const badge = row.querySelector('.risk-status .badge');
                                        if (badge) {
                                            badge.className = 'badge ' + (newSt === 'Kritis' ? 'danger' : (newSt === 'Waspada' ? 'warning' : 'success'));
                                            badge.textContent = newSt;
                                        }
                                        const isRp = isIndicatorRupiah(unit, selectedAlertCode);
                                        const rpPrefix = isRp ? 'Rp ' : '';
                                        const fmtVal = isRp ? (formatRupiahNumber(finalVal) || formatNum(finalVal, 0)) : formatNum(finalVal, 2);
                                        const fmtTh = isRp ? (formatRupiahNumber(th) || formatNum(th, 0)) : formatNum(th, 2);
                                        const valEl = row.querySelector('.risk-value strong');
                                        if (valEl) {
                                            valEl.innerHTML = `${rpPrefix}${fmtVal} <span class="risk-unit-text">${unit}</span>`;
                                        }
                                        const thEl = row.querySelector('.risk-threshold strong');
                                        if (thEl) {
                                            thEl.innerHTML = `${rpPrefix}${fmtTh} <span class="risk-unit-text">${unit}</span>`;
                                        }
                                        row.classList.remove('alerted', 'warning-row', 'normal-row');
                                        row.classList.add(newSt === 'Kritis' ? 'alerted' : (newSt === 'Waspada' ? 'warning-row' : 'normal-row'));
                                    }
                                }
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
                    window._activeEwsStatusFilter = activeFilter;
                    if (typeof window.applyCategoryFilter === 'function') {
                        window.applyCategoryFilter(window._activeCategoryFilter || '');
                    }
                    renderEWS();
                });
            });
        }

        if (catSelect) {
            catSelect.addEventListener('change', function () {
                activeCategory = this.value;
                if (typeof window.applyCategoryFilter === 'function') {
                    window.applyCategoryFilter(activeCategory === 'all' ? '' : activeCategory);
                }
                renderEWS();
            });
        }

        if (btnExport) {
            btnExport.addEventListener('click', () => {
                downloadCSV('monitoring-ews-banyuwangi.csv', [
                    ['Kode', 'Indikator', 'Klaster', 'Tahun', 'Nilai Terkini', 'Threshold', 'Arah Risiko', 'Status'],
                    ...AppState.ews.map(i => {
                        const v = Number(i.value !== undefined ? i.value : (i.nilai_terkini || 0));
                        const t = Number(i.threshold !== undefined ? i.threshold : (i.ambang_batas || 0));
                        const dir = i.direction || i.arah_risiko || 'above';
                        const yr = i.tahun || i.year || '-';
                        const cl = i.klaster || i.cluster || 'Umum';
                        return [i.code || i.kode_indikator, i.name || i.nama_indikator, cl, yr, v, t, dir, calcEwsStatus(v, t, dir)];
                    })
                ]);
            });
        }

        // Delegated click handler on tableRows: handles both toggle sub-indicators and alert modal
        if (tableRows) {
            tableRows.addEventListener('click', function (e) {
                const toggleBtn = e.target.closest('.btn-toggle-subcategories');
                if (toggleBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const pCode = toggleBtn.getAttribute('data-parent') || (toggleBtn.closest('[data-code]') ? toggleBtn.closest('[data-code]').getAttribute('data-code') : '');
                    if (pCode && typeof window.toggleIndicatorSubRows === 'function') {
                        window.toggleIndicatorSubRows(pCode, e);
                    }
                    return;
                }

                // Jika pengguna mengklik baris/sel indikator utama yang memiliki sub-kategori sektoral
                const parentRow = e.target.closest('.risk-row[data-has-sub="true"]:not(.risk-sub-row)');
                if (parentRow && !e.target.closest('button, a, input, select, .risk-value, .risk-threshold')) {
                    const pCode = parentRow.getAttribute('data-code');
                    if (pCode && typeof window.toggleIndicatorSubRows === 'function') {
                        window.toggleIndicatorSubRows(pCode, e);
                        return;
                    }
                }

                const alertRow = e.target.closest('.btn-alert-row');
                if (alertRow) {
                    const code = alertRow.getAttribute('data-code');
                    if (code) {
                        openAlertModal(code);
                    }
                }
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
                subLabel: `Tinggi: ${categoryCounts[cat].tinggi} â€¢ Sedang: ${categoryCounts[cat].sedang} â€¢ Rendah: ${categoryCounts[cat].rendah}`,
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
            if (paperBadge) paperBadge.textContent = `${t.toUpperCase()} â€¢ ${y}`;
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

        // Toggle Report Configuration Sidebar
        const btnToggleConfig = document.getElementById('btn-toggle-report-config');
        const reportConfig = document.querySelector('.report-config');
        const reportBuilder = document.querySelector('.report-builder');
        if (btnToggleConfig && reportConfig && reportBuilder) {
            btnToggleConfig.addEventListener('click', () => {
                const isHidden = reportConfig.classList.toggle('config-hidden');
                reportBuilder.classList.toggle('no-config', isHidden);
                const textSpan = document.getElementById('btn-toggle-report-config-text');
                if (textSpan) {
                    textSpan.textContent = isHidden ? 'Tampilkan Panel Filter' : 'Sembunyikan Panel Filter';
                }
                btnToggleConfig.classList.toggle('active', isHidden);
                setTimeout(() => window.dispatchEvent(new Event('resize')), 200);
            });
        }

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
        const filterCluster = document.getElementById('admin-filter-cluster');
        const filterYear = document.getElementById('admin-filter-year');
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
            const cl = filterCluster && filterCluster.value ? encodeURIComponent(filterCluster.value) : '';
            const yr = filterYear && filterYear.value ? encodeURIComponent(filterYear.value) : '';
            const cat = window._activeCategoryFilter ? encodeURIComponent(window._activeCategoryFilter) : 'EKO';
            window.location.href = `${cleanSite}/database?q=${q}&status=${st}${cl ? '&cluster=' + cl : ''}${yr ? '&year=' + yr : ''}${cat ? '&category=' + cat : ''}`;
        }

        if (searchInput) {
            searchInput.addEventListener('keydown', e => {
                if (e.key === 'Enter') reloadFiltered();
            });
        }
        if (filterStatus) filterStatus.addEventListener('change', reloadFiltered);
        if (filterCluster) filterCluster.addEventListener('change', reloadFiltered);
        if (filterYear) filterYear.addEventListener('change', reloadFiltered);

        // Add modal (Create)
        if (btnOpenModal && addModal) {
            btnOpenModal.addEventListener('click', () => {
                const addDefEl = document.getElementById('add-definition');
                if (addDefEl) addDefEl.value = '';
                const addCatFilter = document.getElementById('add-category-filter-select');
                if (addCatFilter) addCatFilter.value = '';
                const addMasterSelect = document.getElementById('add-indicator-master-select');
                if (addMasterSelect) {
                    addMasterSelect.value = '';
                    addMasterSelect.querySelectorAll('option').forEach(opt => { opt.style.display = ''; });
                }
                const addSecGrp = document.getElementById('add-sectoral-category-group');
                if (addSecGrp) addSecGrp.style.display = 'none';
                const addSecSel = document.getElementById('add-sectoral-category-select');
                if (addSecSel) addSecSel.innerHTML = '';
                ['add-code', 'add-indicator', 'add-cluster', 'add-unit', 'add-source', 'add-value'].forEach(id => {
                    const el = document.getElementById(id); if (el) el.value = '';
                });
                document.querySelectorAll('.add-series-unit-cell').forEach(c => { c.textContent = '%'; });
                const errBox = document.getElementById('add-modal-error');
                if (errBox) { errBox.style.display = 'none'; errBox.textContent = ''; }
                addModal.style.display = 'grid';
                const addCodeEl = document.getElementById('add-code');
                if (addCodeEl) setTimeout(() => addCodeEl.focus(), 80);
            });
        }
        const closeAddModal = () => { if (addModal) addModal.style.display = 'none'; };
        if (btnCloseAdd) btnCloseAdd.addEventListener('click', closeAddModal);
        if (btnCancelAdd) btnCancelAdd.addEventListener('click', closeAddModal);
        if (addModal) {
            addModal.addEventListener('click', e => {
                if (e.target === addModal) closeAddModal();
            });
        }

        const addUnitInput = document.getElementById('add-unit');
        if (addUnitInput) {
            addUnitInput.addEventListener('input', function () {
                const u = this.value.trim() || '%';
                document.querySelectorAll('.add-series-unit-cell').forEach(c => { c.textContent = u; });
            });
        }

        const editUnitInput = document.getElementById('edit-unit');
        if (editUnitInput) {
            editUnitInput.addEventListener('input', function () {
                const u = this.value.trim() || '%';
                document.querySelectorAll('#edit-series-grid .series-unit-label-cell').forEach(c => { c.textContent = u; });
            });
        }

        const addCategoryFilter = document.getElementById('add-category-filter-select');
        const addMasterSelect = document.getElementById('add-indicator-master-select');
        const addSectoralGroup = document.getElementById('add-sectoral-category-group');
        const addSectoralSelect = document.getElementById('add-sectoral-category-select');
        const addSectoralBadge = document.getElementById('add-sectoral-badge-label');
        const addSectoralLabel = document.getElementById('add-sectoral-category-label');
        const btnQuickOpenSectoral = document.getElementById('btn-quick-open-sectoral-matrix');

        // 1. Ketika Kategori / Klaster dipilih: filter indikator & otomatis isi indikator pertama
        if (addCategoryFilter && addMasterSelect) {
            addCategoryFilter.addEventListener('change', function () {
                const chosenCat = (this.value || '').trim();
                const options = addMasterSelect.querySelectorAll('option');
                let firstMatchVal = null;

                options.forEach(opt => {
                    if (!opt.value) return;
                    const optCluster = opt.getAttribute('data-cluster') || '';
                    const isMatch = !chosenCat || (optCluster.toLowerCase() === chosenCat.toLowerCase());
                    opt.style.display = isMatch ? '' : 'none';
                    if (isMatch && firstMatchVal === null) {
                        firstMatchVal = opt.value;
                    }
                });

                if (firstMatchVal) {
                    addMasterSelect.value = firstMatchVal;
                    addMasterSelect.dispatchEvent(new Event('change'));
                } else if (!chosenCat) {
                    addMasterSelect.value = '';
                    addMasterSelect.dispatchEvent(new Event('change'));
                }
            });
        }

        if (btnQuickOpenSectoral) {
            btnQuickOpenSectoral.addEventListener('click', () => {
                const curParent = (addMasterSelect && addMasterSelect.value) ? addMasterSelect.value.trim().toUpperCase() : 'EKO-003';
                if (addModal) addModal.style.display = 'none';
                openSectoralModal(curParent);
            });
        }

        // 2. Ketika Indikator Master dipilih: otomatis mengisi sendiri seluruh kolom
        if (addMasterSelect) {
            addMasterSelect.addEventListener('change', function () {
                const val = (this.value || '').trim().toUpperCase();
                if (!val) {
                    ['add-code', 'add-indicator', 'add-cluster', 'add-unit', 'add-source', 'add-definition'].forEach(id => {
                        const el = document.getElementById(id); if (el) el.value = '';
                    });
                    if (addSectoralGroup) addSectoralGroup.style.display = 'none';
                    return;
                }

                // Cek apakah indikator master memiliki rincian sub-kategori (misal EKO-003.A, EKO-004.A, EKO-015.1, INV-001.Q1, dsb)
                const subItems = (window.CATALOG_MASTER_LOOKUP && Array.isArray(window.CATALOG_MASTER_LOOKUP))
                    ? window.CATALOG_MASTER_LOOKUP.filter(m => m.code && m.code.toUpperCase().startsWith(val + '.'))
                    : [];

                if (subItems.length > 0 && addSectoralGroup && addSectoralSelect) {
                    addSectoralGroup.style.display = 'block';
                    const isInv = val.startsWith('INV-');
                    const isExp = (val === 'EKO-015' || val === 'EKO-016');
                    if (addSectoralBadge) {
                        if (isInv) addSectoralBadge.textContent = 'Kuartal Investasi';
                        else if (isExp) addSectoralBadge.textContent = 'Komponen Pengeluaran';
                        else addSectoralBadge.textContent = 'Sektor Lapangan Usaha';
                    }
                    if (addSectoralLabel) {
                        let lblText = 'Rincian Kategori Lapangan Usaha (Kategori A s.d. U & Tanpa Migas)';
                        if (isInv) lblText = 'Pilih Kuartal Investasi (Kuartal 1 s.d. 4)';
                        else if (isExp) lblText = 'Rincian Komponen Pengeluaran (Komponen 1 s.d. 6)';
                        addSectoralLabel.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h7"/></svg> ${lblText}`;
                    }

                    const parentMatch = window.CATALOG_MASTER_LOOKUP.find(m => (m.code || '').toUpperCase() === val);
                    let optHtml = `<option value="${val}">[TOTAL] ${parentMatch ? parentMatch.name : 'Total ' + val} (Total Agregat)</option>`;
                    if (isInv) {
                        const qOrder = { 'Q1': 1, 'Q2': 2, 'Q3': 3, 'Q4': 4 };
                        subItems.sort((a, b) => {
                            const kA = (a.code || '').substring(val.length + 1);
                            const kB = (b.code || '').substring(val.length + 1);
                            return (qOrder[kA] || 99) - (qOrder[kB] || 99);
                        });
                        subItems.forEach(sub => {
                            const subKey = sub.code.substring(val.length + 1);
                            optHtml += `<option value="${sub.code}">[${subKey}] ${sub.name || ('Kuartal ' + subKey)}</option>`;
                        });
                    } else {
                        subItems.forEach(sub => {
                            let subKey = sub.code.substring(val.length + 1);
                            if (subKey === 'MN') subKey = 'M,N';
                            else if (subKey === 'RSTU') subKey = 'R,S,T,U';
                            const cleanSubName = (sub.name || '').replace(/^(PDRB\s+(ADHB|ADHK)|Pertumbuhan(\s+PDRB(\s+ADHK)?)?|Distribusi(\s+PDRB(\s+ADHB)?)?|Kontribusi|PDRB\s+Pengeluaran\s+(ADHB|ADHK)|Pengeluaran\s+(ADHB|ADHK)):\s*/i, '');
                            optHtml += `<option value="${sub.code}">[${subKey}] ${cleanSubName}</option>`;
                        });
                    }
                    addSectoralSelect.innerHTML = optHtml;
                    addSectoralSelect.value = val;
                } else if (addSectoralGroup) {
                    addSectoralGroup.style.display = 'none';
                }

                if (window.CATALOG_MASTER_LOOKUP && Array.isArray(window.CATALOG_MASTER_LOOKUP)) {
                    const match = window.CATALOG_MASTER_LOOKUP.find(m => (m.code || '').toUpperCase() === val);
                    if (match) {
                        if (document.getElementById('add-code')) document.getElementById('add-code').value = match.code;
                        if (document.getElementById('add-indicator')) document.getElementById('add-indicator').value = match.name;
                        if (document.getElementById('add-cluster')) document.getElementById('add-cluster').value = match.cluster;
                        if (document.getElementById('add-unit')) document.getElementById('add-unit').value = match.unit;
                        if (document.getElementById('add-source')) document.getElementById('add-source').value = match.source;
                        if (document.getElementById('add-definition')) document.getElementById('add-definition').value = match.definition || '';

                        // Sinkronkan dropdown kategori / klaster langkah 1 agar mengisi sendiri
                        if (addCategoryFilter && match.cluster) {
                            addCategoryFilter.value = match.cluster;
                        }

                        document.querySelectorAll('.add-series-unit-cell').forEach(c => { c.textContent = match.unit || '%'; });
                        const firstVal = document.querySelector('#add-series-table-body input[name="series_values[]"]');
                        if (firstVal) firstVal.focus();
                    }
                }
            });
        }

        // 3. Ketika Kategori / Sub-Indikator dipilih: otomatis MENGISI INDIKATOR
        if (addSectoralSelect) {
            addSectoralSelect.addEventListener('change', function () {
                const chosenCode = (this.value || '').trim().toUpperCase();
                if (!chosenCode || !window.CATALOG_MASTER_LOOKUP) return;
                const match = window.CATALOG_MASTER_LOOKUP.find(m => (m.code || '').toUpperCase() === chosenCode);
                if (match) {
                    if (document.getElementById('add-code')) document.getElementById('add-code').value = match.code;
                    if (document.getElementById('add-indicator')) document.getElementById('add-indicator').value = match.name;
                    if (document.getElementById('add-cluster')) document.getElementById('add-cluster').value = match.cluster;
                    if (document.getElementById('add-unit')) document.getElementById('add-unit').value = match.unit;
                    if (document.getElementById('add-source')) document.getElementById('add-source').value = match.source;
                    if (document.getElementById('add-definition')) document.getElementById('add-definition').value = match.definition || '';
                    document.querySelectorAll('.add-series-unit-cell').forEach(c => { c.textContent = match.unit || '%'; });
                }
            });
        }

        const addCodeInput = document.getElementById('add-code');
        if (addCodeInput) {
            const handleCodeAutocomplete = function () {
                if (!window.CATALOG_MASTER_LOOKUP || !Array.isArray(window.CATALOG_MASTER_LOOKUP)) return;
                const val = (addCodeInput.value || '').trim().toUpperCase();
                if (!val) return;
                const match = window.CATALOG_MASTER_LOOKUP.find(m => (m.code || '').toUpperCase() === val);
                if (match) {
                    const indEl = document.getElementById('add-indicator');
                    if (indEl) indEl.value = match.name;
                    const clEl = document.getElementById('add-cluster');
                    if (clEl) clEl.value = match.cluster;
                    const unEl = document.getElementById('add-unit');
                    if (unEl) unEl.value = match.unit;
                    const srcEl = document.getElementById('add-source');
                    if (srcEl) srcEl.value = match.source;
                    const defEl = document.getElementById('add-definition');
                    if (defEl) defEl.value = match.definition || '';
                    document.querySelectorAll('.add-series-unit-cell').forEach(c => { c.textContent = match.unit || '%'; });
                }
            };
            addCodeInput.addEventListener('input', handleCodeAutocomplete);
            addCodeInput.addEventListener('change', handleCodeAutocomplete);
        }

        if (formAdd) {
            formAdd.addEventListener('submit', function (e) {
                e.preventDefault();

                // Bersihkan baris yang sepenuhnya kosong sebelum submit
                this.querySelectorAll('#add-series-table-body tr.series-table-row').forEach(tr => {
                    const yrInput = tr.querySelector('input[name="series_years[]"]');
                    const valInput = tr.querySelector('input[name="series_values[]"]');
                    if ((!yrInput || !yrInput.value.trim()) && (!valInput || !valInput.value.trim())) {
                        tr.remove();
                    }
                });

                let hasIncomplete = false;
                this.querySelectorAll('#add-series-table-body tr.series-table-row').forEach(tr => {
                    const yrInput = tr.querySelector('input[name="series_years[]"]');
                    const valInput = tr.querySelector('input[name="series_values[]"]');
                    if ((!yrInput || !yrInput.value.trim()) && (valInput && valInput.value.trim())) {
                        hasIncomplete = true;
                    }
                });
                if (hasIncomplete) {
                    alert('Mohon isi kolom Tahun pada baris yang telah diberi nilai.');
                    return;
                }

                const fd = new FormData(this);
                if (fd.get('cluster') === '__custom__' && fd.get('cluster_custom')) {
                    fd.set('cluster', fd.get('cluster_custom').trim());
                }
                // Bersihkan series_values[] dari format titik ribuan agar tersimpan float murni
                fd.delete('series_values[]');
                this.querySelectorAll('#add-series-table-body input[name="series_values[]"]').forEach(inp => {
                    const raw = inp.value.trim();
                    if (raw !== '') {
                        fd.append('series_values[]', parseRupiahNumber(raw));
                    } else {
                        fd.append('series_values[]', '');
                    }
                });
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

                // Bersihkan baris yang sepenuhnya kosong sebelum submit
                this.querySelectorAll('#edit-series-grid tr.series-table-row').forEach(tr => {
                    const yrInput = tr.querySelector('input[name="series_years[]"]');
                    const valInput = tr.querySelector('input[name="series_values[]"]');
                    if ((!yrInput || !yrInput.value.trim()) && (!valInput || !valInput.value.trim())) {
                        tr.remove();
                    }
                });

                let hasIncomplete = false;
                this.querySelectorAll('#edit-series-grid tr.series-table-row').forEach(tr => {
                    const yrInput = tr.querySelector('input[name="series_years[]"]');
                    const valInput = tr.querySelector('input[name="series_values[]"]');
                    if ((!yrInput || !yrInput.value.trim()) && (valInput && valInput.value.trim())) {
                        hasIncomplete = true;
                    }
                });
                if (hasIncomplete) {
                    alert('Mohon isi kolom Tahun pada baris yang telah diberi nilai.');
                    return;
                }

                const code = (document.getElementById('edit-code') ? document.getElementById('edit-code').value : '').trim();
                const id = document.getElementById('edit-id') ? document.getElementById('edit-id').value : '';

                const fd = new FormData(this);
                if (fd.get('cluster') === '__custom__' && fd.get('cluster_custom')) {
                    fd.set('cluster', fd.get('cluster_custom').trim());
                }
                // Bersihkan series_values[] dari format titik ribuan agar tersimpan float murni
                fd.delete('series_values[]');
                this.querySelectorAll('#edit-series-grid input[name="series_values[]"]').forEach(inp => {
                    const raw = inp.value.trim();
                    if (raw !== '') {
                        fd.append('series_values[]', parseRupiahNumber(raw));
                    } else {
                        fd.append('series_values[]', '');
                    }
                });
                if (editErrBox) { editErrBox.style.display = 'none'; editErrBox.textContent = ''; }

                const btnSubmit = document.getElementById('modal-btn-submit-edit');
                const origBtnHtml = btnSubmit ? btnSubmit.innerHTML : '';
                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.textContent = 'Menyimpan Perubahan...';
                }

                // Jika memiliki kode indikator deret, simpan multi-tahun melalui update_series
                const endpoint = code ? `${cleanSite}/database/update_series` : `${cleanSite}/database/update/${id}`;

                fetch(endpoint, {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            location.reload();
                        } else {
                            if (btnSubmit) {
                                btnSubmit.disabled = false;
                                btnSubmit.innerHTML = origBtnHtml;
                            }
                            if (editErrBox) {
                                editErrBox.style.display = 'block';
                                editErrBox.textContent = data.message || 'Gagal memperbarui data.';
                            }
                        }
                    })
                    .catch(() => {
                        if (btnSubmit) {
                            btnSubmit.disabled = false;
                            btnSubmit.innerHTML = origBtnHtml;
                        }
                        if (editErrBox) {
                            editErrBox.style.display = 'block';
                            editErrBox.textContent = 'Terjadi kesalahan jaringan saat menyimpan pembaruan.';
                        }
                    });
            });
        }

        // Helper untuk Mengurutkan Baris Tahun secara Kronologis (Hanya jika semua tahun sudah terisi)
        function sortSeriesRows(tbody) {
            if (!tbody) return;
            const rows = Array.from(tbody.querySelectorAll('tr.series-table-row'));
            // Jangan urutkan jika ada baris yang tahunnya masih kosong (sedang diketik manual oleh operator)
            const hasEmpty = rows.some(r => {
                const inp = r.querySelector('input[name="series_years[]"]');
                return !inp || !inp.value.trim();
            });
            if (hasEmpty) return;

            rows.sort((a, b) => {
                const inpA = a.querySelector('input[name="series_years[]"]');
                const inpB = b.querySelector('input[name="series_years[]"]');
                const yA = inpA ? parseInt(inpA.value, 10) || 0 : 0;
                const yB = inpB ? parseInt(inpB.value, 10) || 0 : 0;
                return yA - yB;
            });
            rows.forEach(r => tbody.appendChild(r));
        }

        // Helper untuk Toggle Rincian Sub-Kategori Sektoral (A s.d. U)
        function toggleSectoralSubRows(parentCode, event) {
            if (typeof window.toggleIndicatorSubRows === 'function') {
                window.toggleIndicatorSubRows(parentCode, event);
            }
        }

        // Helper untuk Menambah Baris Tahun & Nilai pada Modal Tambah / Edit
        function appendSeriesRow(tbody, year, value, unit, isEdit, position, code) {
            if (!tbody) return;
            const tr = document.createElement('tr');
            tr.className = 'series-table-row is-new-row';

            const yearVal = (year !== undefined && year !== null) ? year : '';
            const numVal = (value !== undefined && value !== null) ? value : '';
            const isRp = isIndicatorRupiah(unit, code);
            const maskClass = isRp ? 'is-rupiah-mask' : '';

            if (isEdit) {
                tr.innerHTML = `
                    <td style="padding: 7px 10px; vertical-align: middle; border-bottom: 1px solid #edf2f7;">
                        <input type="number" name="series_years[]" class="series-input-year" value="${yearVal}" placeholder="Tahun" min="1900" max="2100" style="width:100%; height:36px; padding:0 8px; font-weight:800; font-size:13px; border:1.5px solid #cbd5e1; border-radius:6px; text-align:center; background:#fff;">
                    </td>
                    <td style="padding: 7px 10px; vertical-align: middle; border-bottom: 1px solid #edf2f7;">
                        <span class="status-pill pill-empty">Baru</span>
                    </td>
                    <td style="padding: 6px 10px; vertical-align: middle; border-bottom: 1px solid #edf2f7;">
                        <input type="text" inputmode="decimal" name="series_values[]" class="series-val-input-row ${maskClass}" value="${formatRupiahNumber(numVal)}" placeholder="0" style="width:100%; height:36px; padding:0 12px; font-weight:700; font-size:13px; border:1.5px solid #cbd5e1; border-radius:6px; text-align:right; background:#fff;">
                    </td>
                    <td class="series-unit-label-cell" style="padding: 7px 10px; vertical-align: middle; border-bottom: 1px solid #edf2f7; font-size:11.5px; font-weight:700; color:#475569; white-space:nowrap;">
                        ${unit || '%'}
                    </td>
                    <td style="padding: 7px 8px; vertical-align: middle; text-align: center; border-bottom: 1px solid #edf2f7;">
                        <button type="button" class="btn-remove-series-row icon-button small danger" title="Hapus Baris Ini" style="width:28px; height:28px; margin:0 auto; color:#dc2626; background:#fee2e2; border:1px solid #fca5a5;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                        </button>
                    </td>
                `;
            } else {
                tr.innerHTML = `
                    <td style="padding: 7px 10px; vertical-align: middle; border-bottom: 1px solid #edf2f7;">
                        <input type="number" name="series_years[]" class="series-input-year" value="${yearVal}" placeholder="Tahun" min="1900" max="2100" style="width:100%; height:36px; padding:0 8px; font-weight:800; font-size:13px; border:1.5px solid #cbd5e1; border-radius:6px; text-align:center; background:#fff;">
                    </td>
                    <td style="padding: 6px 10px; vertical-align: middle; border-bottom: 1px solid #edf2f7;">
                        <input type="text" inputmode="decimal" name="series_values[]" class="series-val-input-row ${maskClass}" value="${formatRupiahNumber(numVal)}" placeholder="0" required style="width:100%; height:36px; padding:0 12px; font-weight:700; font-size:13px; border:1.5px solid #cbd5e1; border-radius:6px; text-align:right; background:#fff;">
                    </td>
                    <td class="add-series-unit-cell" style="padding: 7px 10px; vertical-align: middle; border-bottom: 1px solid #edf2f7; font-size:12px; font-weight:700; color:#475569; white-space:nowrap;">
                        ${unit || '%'}
                    </td>
                    <td style="padding: 7px 8px; vertical-align: middle; text-align: center; border-bottom: 1px solid #edf2f7;">
                        <button type="button" class="btn-remove-series-row icon-button small danger" title="Hapus Baris Ini" style="width:28px; height:28px; margin:0 auto; color:#dc2626; background:#fee2e2; border:1px solid #fca5a5;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                        </button>
                    </td>
                `;
            }

            const wrap = tbody.closest('.edit-series-table-wrap');

            // Posisi penambahan: 'top' untuk Tahun Sebelumnya (di atas), 'bottom' untuk Tahun Sesudahnya (di bawah)
            if (position === 'top') {
                tbody.insertBefore(tr, tbody.firstChild);
                if (wrap) wrap.scrollTop = 0;
            } else {
                tbody.appendChild(tr);
                if (wrap) wrap.scrollTop = wrap.scrollHeight;
            }

            // Fokus ke kolom input tahun tanpa memblokir rendering DOM agar baris muncul seketika (tanpa lag)
            requestAnimationFrame(() => {
                const yrInput = tr.querySelector('.series-input-year');
                if (yrInput) {
                    yrInput.focus();
                }
            });

            if (isEdit) {
                const editCountEl = document.getElementById('edit-series-uploaded-count');
                if (editCountEl) {
                    editCountEl.textContent = `${tbody.querySelectorAll('tr').length} Tahun Terdaftar`;
                }
            }

            return tr;
        }

        // Delegated Change Handler untuk Input Tahun agar Otomatis Mengurutkan Baris jika semua sudah terisi
        document.addEventListener('change', function (e) {
            if (e.target && e.target.classList.contains('series-input-year')) {
                const tbody = e.target.closest('tbody');
                if (tbody) {
                    sortSeriesRows(tbody);
                }
            }
        });

        // Delegated Click Handler for Database Records (Edit Series, Delete, Restore, Hard Delete, Row Actions)
        if (!window._registeredDbDelegates) {
            window._registeredDbDelegates = true;
            document.addEventListener('click', function (e) {
                // Toggle Rincian Sub-Kategori saat Kolom / Baris Indikator Ditekan
                const parentRow = e.target.closest('tr.has-subcategories, tr[data-has-sub="true"]');
                if (parentRow) {
                    // Jangan toggle jika user mengklik tombol aksi edit/hapus, link, input, atau kolom aksi paling kanan
                    if (!e.target.closest('button:not(.btn-toggle-subcategories), a, input, select, .matrix-sticky-col-last')) {
                        const pCode = parentRow.getAttribute('data-code');
                        if (pCode && typeof window.toggleIndicatorSubRows === 'function') {
                            window.toggleIndicatorSubRows(pCode, e);
                            return;
                        }
                    }
                }

                // Tambah Tahun Sebelumnya (+ Tahun Sebelumnya): Tertambah TEPAT 1 BARIS di ATAS
                const addPrevBtn = e.target.closest('.btn-add-year-prev');
                if (addPrevBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const target = addPrevBtn.getAttribute('data-target');
                    const isEdit = (target === 'edit');
                    const tbody = isEdit ? document.getElementById('edit-series-grid') : document.getElementById('add-series-table-body');
                    const unitInput = isEdit ? document.getElementById('edit-unit') : document.getElementById('add-unit');
                    const unit = unitInput ? unitInput.value : '%';
                    const codeInput = isEdit ? document.getElementById('edit-code') : document.getElementById('add-code');
                    const code = codeInput ? codeInput.value : '';

                    appendSeriesRow(tbody, '', '', unit, isEdit, 'top', code);
                    return;
                }

                // Tambah Tahun Sesudahnya (+ Tahun Sesudahnya): Tertambah TEPAT 1 BARIS di BAWAH
                const addNextBtn = e.target.closest('.btn-add-year-next');
                if (addNextBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const target = addNextBtn.getAttribute('data-target');
                    const isEdit = (target === 'edit');
                    const tbody = isEdit ? document.getElementById('edit-series-grid') : document.getElementById('add-series-table-body');
                    const unitInput = isEdit ? document.getElementById('edit-unit') : document.getElementById('add-unit');
                    const unit = unitInput ? unitInput.value : '%';
                    const codeInput = isEdit ? document.getElementById('edit-code') : document.getElementById('add-code');
                    const code = codeInput ? codeInput.value : '';

                    appendSeriesRow(tbody, '', '', unit, isEdit, 'bottom', code);
                    return;
                }

                // Fallback untuk selector lama jika terpanggil
                const addBulkYearsBtn = e.target.closest('.btn-add-bulk-years, .btn-add-year-row, .btn-add-series-year');
                if (addBulkYearsBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const target = addBulkYearsBtn.getAttribute('data-target');
                    const isEdit = (target === 'edit');
                    const tbody = isEdit ? document.getElementById('edit-series-grid') : document.getElementById('add-series-table-body');
                    const unitInput = isEdit ? document.getElementById('edit-unit') : document.getElementById('add-unit');
                    const unit = unitInput ? unitInput.value : '%';
                    const codeInput = isEdit ? document.getElementById('edit-code') : document.getElementById('add-code');
                    const code = codeInput ? codeInput.value : '';

                    appendSeriesRow(tbody, '', '', unit, isEdit, 'bottom', code);
                    return;
                }

                // Hapus Baris Tahun Beserta Nilainya (Remove Row)
                const removeRowBtn = e.target.closest('.btn-remove-series-row');
                if (removeRowBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const tr = removeRowBtn.closest('tr');
                    if (!tr) return;

                    const tbody = tr.closest('tbody');
                    const isEdit = tbody && (tbody.id === 'edit-series-grid');
                    const yearInput = tr.querySelector('input[name="series_years[]"]');
                    const yrLabel = yearInput && yearInput.value ? yearInput.value : 'baris ini';

                    AppSwal.confirm({
                        title: 'Hapus Baris Tahun?',
                        html: `Hapus tahun <strong>${yrLabel}</strong> beserta angkanya dari daftar?`,
                        confirmButtonText: 'Ya',
                        cancelButtonText: 'Tidak',
                        icon: 'warning'
                    }).then(result => {
                        if (result && result.isConfirmed) {
                            tr.remove();
                            if (isEdit) {
                                const editCountEl = document.getElementById('edit-series-uploaded-count');
                                if (editCountEl && tbody) {
                                    editCountEl.textContent = `${tbody.querySelectorAll('tr').length} Tahun Terdaftar`;
                                }
                            } else {
                                if (tbody && tbody.querySelectorAll('tr').length === 0) {
                                    const addUnitInput = document.getElementById('add-unit');
                                    const addCodeInput = document.getElementById('add-code');
                                    appendSeriesRow(tbody, 2024, '', addUnitInput ? addUnitInput.value : '%', false, 'bottom', addCodeInput ? addCodeInput.value : '');
                                }
                            }
                        }
                    });
                    return;
                }

                // Edit Deret Indikator Sekaligus (Multi-year Series)
                const editSeriesBtn = e.target.closest('.btn-edit-series');
                if (editSeriesBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const code = editSeriesBtn.getAttribute('data-code');
                    if (!code) return;

                    if (editErrBox) { editErrBox.style.display = 'none'; editErrBox.textContent = ''; }
                    const seriesGrid = document.getElementById('edit-series-grid');
                    const uploadedCountEl = document.getElementById('edit-series-uploaded-count');
                    if (seriesGrid) {
                        seriesGrid.innerHTML = '<tr><td colspan="5" style="padding: 24px; text-align: center; color: #64748b;">Memuat data deret tahun...</td></tr>';
                    }

                    fetch(`${cleanSite}/database/get_series?code=${encodeURIComponent(code)}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(res => {
                            if (!res.ok) throw new Error('Data deret indikator tidak ditemukan.');
                            return res.json();
                        })
                        .then(res => {
                            if (res.status === 'success' && res.data) {
                                const d = res.data;
                                if (document.getElementById('edit-code')) document.getElementById('edit-code').value = d.code;
                                const codeDisp = document.getElementById('edit-code-display');
                                if (codeDisp) codeDisp.value = d.code;
                                if (document.getElementById('edit-indicator')) document.getElementById('edit-indicator').value = d.indicator || '';

                                const editClusterEl = document.getElementById('edit-cluster');
                                if (editClusterEl) {
                                    const cVal = (d.cluster || '').trim();
                                    editClusterEl.value = cVal;
                                    if (cVal && editClusterEl.value !== cVal) {
                                        const newOpt = document.createElement('option');
                                        newOpt.value = cVal;
                                        newOpt.textContent = cVal;
                                        newOpt.selected = true;
                                        editClusterEl.appendChild(newOpt);
                                    }
                                }

                                if (document.getElementById('edit-region')) document.getElementById('edit-region').value = d.region || 'Kabupaten Banyuwangi';
                                const regionLabel = document.getElementById('edit-region-label');
                                if (regionLabel) regionLabel.textContent = d.region || 'Kabupaten Banyuwangi';

                                if (document.getElementById('edit-kode-wilayah')) {
                                    document.getElementById('edit-kode-wilayah').value = d.kode_wilayah || '35.10';
                                }
                                if (document.getElementById('edit-unit')) document.getElementById('edit-unit').value = d.unit || '%';
                                if (document.getElementById('edit-source')) document.getElementById('edit-source').value = d.source || '';
                                const editDefEl = document.getElementById('edit-definition');
                                if (editDefEl) {
                                    editDefEl.value = d.definition || '';
                                }

                                if (uploadedCountEl) {
                                    uploadedCountEl.textContent = `${d.uploaded_count || 0} dari ${d.total_years || 10} Tahun Ter-upload`;
                                }

                                // Render Tabel Input Deret Tahun (Tersusun ke Bawah dengan Input Tahun & Tombol Hapus)
                                if (seriesGrid && Array.isArray(d.years)) {
                                    let rowsHtml = '';
                                    const isParentTotal = !!d.is_parent_total;
                                    if (isParentTotal) {
                                        rowsHtml += `
                                            <tr>
                                                <td colspan="5" style="padding:10px 14px; background:#f0fdf4; border:1.5px solid #86efac; border-radius:8px;">
                                                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                                                        <div style="font-size:12px; color:#166534; font-weight:700; display:flex; align-items:center; gap:6px;">
                                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                                            <span>Nilai Total Tahunan ini terhitung secara otomatis dari akumulasi seluruh rincian kuartal/sektoral.</span>
                                                        </div>
                                                        <button type="button" class="btn-open-matrix-from-edit button small secondary" data-parent="${d.code}" style="padding:4px 10px; font-size:11px; font-weight:700; background:#dcfce7; color:#15803d; border:1px solid #86efac; border-radius:6px; cursor:pointer; display:inline-flex; align-items:center; gap:4px;">
                                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg>
                                                            <span>Buka Spreadsheet Matriks Lengkap &rarr;</span>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>`;
                                    }

                                    d.years.forEach(item => {
                                        const hasData = item.has_data;
                                        const valStr = (hasData && item.value !== null && item.value !== undefined) ? item.value : '';
                                        const stLabel = isParentTotal ? 'Otomatis' : (hasData ? (item.status || 'Tervalidasi') : 'Baru');
                                        const isWarning = item.status === 'Perlu review' || item.status === 'Ditolak';

                                        const isRp = isIndicatorRupiah(d.unit, d.code);
                                        const maskClass = isRp ? 'is-rupiah-mask' : '';
                                        const formattedVal = (hasData && item.value !== null && item.value !== undefined) ? formatRupiahNumber(item.value) : '';
                                        const valInputHtml = isParentTotal
                                            ? `<input type="text" readonly name="series_values[]" class="series-val-input-row ${maskClass}" value="${formattedVal}" style="background:#dcfce7; color:#15803d; font-weight:800; border:1.5px solid #86efac; cursor:not-allowed;" title="Total otomatis terhitung dari akumulasi seluruh rincian">`
                                            : `<input type="text" inputmode="decimal" name="series_values[]" class="series-val-input-row ${maskClass}" value="${formattedVal}" placeholder="0" title="Nilai realisasi ${d.indicator} tahun ${item.year}">`;

                                        rowsHtml += `
                                            <tr class="series-table-row ${hasData ? 'has-data' : 'is-empty'}">
                                                <td style="padding: 7px 10px; vertical-align: middle; border-bottom: 1px solid #edf2f7;">
                                                    <input type="number" name="series_years[]" class="series-input-year" value="${item.year}" min="1900" max="2100" required style="width:100%; height:36px; padding:0 8px; font-weight:800; font-size:13px; border:1.5px solid #cbd5e1; border-radius:6px; text-align:center; background:#fff;">
                                                </td>
                                                <td style="padding: 7px 10px; vertical-align: middle; border-bottom: 1px solid #edf2f7;">
                                                    <span class="status-pill ${isParentTotal ? 'pill-success' : (hasData ? (isWarning ? 'pill-warning' : 'pill-success') : 'pill-empty')}">
                                                         ${stLabel}
                                                    </span>
                                                </td>
                                                <td style="padding: 6px 10px; vertical-align: middle; border-bottom: 1px solid #edf2f7;">
                                                    <div class="series-input-wrap">
                                                        ${valInputHtml}
                                                    </div>
                                                </td>
                                                <td class="series-unit-label-cell" style="padding: 7px 10px; vertical-align: middle; border-bottom: 1px solid #edf2f7; font-size:11.5px; font-weight:700; color:#475569; white-space:nowrap;">
                                                    ${d.unit || '%'}
                                                </td>
                                                <td style="padding: 7px 8px; vertical-align: middle; text-align: center; border-bottom: 1px solid #edf2f7;">
                                                    <button type="button" class="btn-remove-series-row icon-button small danger" title="Hapus Tahun ${item.year} Beserta Nilainya" style="width:28px; height:28px; margin:0 auto; color:#dc2626; background:#fee2e2; border:1px solid #fca5a5;">
                                                         <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        `;
                                    });
                                    seriesGrid.innerHTML = rowsHtml;
                                }

                                if (editModal) editModal.style.display = 'grid';
                            } else {
                                alert(res.message || 'Gagal memuat deret data indikator.');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            alert('Gagal mengambil data indikator untuk diedit.');
                        });
                    return;
                }

                // Soft Delete Seluruh Data Indikator (Series)
                const delSeriesBtn = e.target.closest('.btn-delete-series');
                if (delSeriesBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const code = delSeriesBtn.getAttribute('data-code');
                    const indicator = delSeriesBtn.getAttribute('data-indicator') || code;
                    if (!code) return;
                    AppSwal.confirm({
                        title: 'Pindahkan ke Tempat Sampah?',
                        html: `Pindahkan seluruh data indikator <strong>"${indicator}"</strong> (${code}) ke tempat sampah?<br><small style="color:#64748b; font-size:11.5px; margin-top:6px; display:block;">Seluruh data tahunan indikator ini akan dipindahkan ke tempat sampah dan dapat dipulihkan sewaktu-waktu.</small>`,
                        confirmButtonText: 'Ya',
                        cancelButtonText: 'Tidak',
                        icon: 'warning'
                    }).then(result => {
                        if (result && result.isConfirmed) {
                            const fd = new FormData();
                            fd.append('code', code);
                            fetch(`${cleanSite}/database/soft_delete_series`, {
                                method: 'POST',
                                body: fd,
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            })
                                .then(res => res.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        location.reload();
                                    } else {
                                        AppSwal.error('Gagal Memproses', data.message || 'Gagal memproses soft delete.');
                                    }
                                })
                                .catch(() => {
                                    location.reload();
                                });
                        }
                    });
                    return;
                }

                // Restore Seluruh Data Indikator (Series)
                const resSeriesBtn = e.target.closest('.btn-restore-series');
                if (resSeriesBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const code = resSeriesBtn.getAttribute('data-code');
                    const indicator = resSeriesBtn.getAttribute('data-indicator') || code;
                    if (!code) return;
                    AppSwal.confirm({
                        title: 'Pulihkan Seluruh Data?',
                        html: `Pulihkan seluruh data indikator <strong>"${indicator}"</strong> (${code}) kembali ke daftar aktif?`,
                        confirmButtonText: 'Ya',
                        cancelButtonText: 'Tidak',
                        icon: 'question'
                    }).then(result => {
                        if (result && result.isConfirmed) {
                            const fd = new FormData();
                            fd.append('code', code);
                            fetch(`${cleanSite}/database/restore_series`, {
                                method: 'POST',
                                body: fd,
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            })
                                .then(res => res.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        location.reload();
                                    } else {
                                        AppSwal.error('Gagal Memulihkan', data.message || 'Gagal memulihkan data.');
                                    }
                                })
                                .catch(() => {
                                    location.reload();
                                });
                        }
                    });
                    return;
                }

                // Hard Delete Seluruh Data Indikator (Series)
                const hardSeriesBtn = e.target.closest('.btn-hard-delete-series');
                if (hardSeriesBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const code = hardSeriesBtn.getAttribute('data-code');
                    const indicator = hardSeriesBtn.getAttribute('data-indicator') || code;
                    if (!code) return;
                    AppSwal.dangerConfirm({
                        title: 'Hapus Permanen Seluruh Deret?',
                        html: `Peringatan: Apakah Anda yakin ingin <strong>MENGHAPUS PERMANEN</strong> seluruh rekaman data <strong>"${indicator}"</strong> (${code})?<br><br><span style="color:#e11d48; font-weight:600; font-size:12px;">Data yang dihapus permanen akan hilang selamanya dari database dan TIDAK DAPAT DIPULIHKAN LAGI!</span>`,
                        confirmButtonText: 'Ya',
                        cancelButtonText: 'Tidak'
                    }).then(result => {
                        if (result && result.isConfirmed) {
                            const fd = new FormData();
                            fd.append('code', code);
                            fetch(`${cleanSite}/database/hard_delete_series`, {
                                method: 'POST',
                                body: fd,
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            })
                                .then(res => res.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        location.reload();
                                    } else {
                                        AppSwal.error('Gagal Menghapus', data.message || 'Gagal menghapus data secara permanen.');
                                    }
                                })
                                .catch(() => {
                                    location.reload();
                                });
                        }
                    });
                    return;
                }

                // Quick Add for specific indicator code and year
                const quickAddBtn = e.target.closest('.btn-quick-add-year');
                if (quickAddBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const code = quickAddBtn.getAttribute('data-code');
                    const yr = quickAddBtn.getAttribute('data-year');

                    if (btnOpenModal) {
                        btnOpenModal.click();
                    }

                    setTimeout(() => {
                        if (code) {
                            const addCodeEl = document.getElementById('add-code');
                            if (addCodeEl) {
                                addCodeEl.value = code;
                                addCodeEl.dispatchEvent(new Event('input'));
                                addCodeEl.dispatchEvent(new Event('change'));
                            }
                            const addMasterSelect = document.getElementById('add-indicator-master-select');
                            if (addMasterSelect) {
                                addMasterSelect.value = code;
                                addMasterSelect.dispatchEvent(new Event('change'));
                            }
                        }
                        if (yr) {
                            const addYrEl = document.getElementById('add-year');
                            if (addYrEl) {
                                addYrEl.value = yr;
                            }
                        }
                        const valEl = document.getElementById('add-value');
                        if (valEl) valEl.focus();
                    }, 120);
                    return;
                }

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

                                const clusterVal = (d.cluster || d.klaster || 'Output dan pertumbuhan').trim();
                                const editClusterEl = document.getElementById('edit-cluster');
                                if (editClusterEl) {
                                    editClusterEl.value = clusterVal;
                                }

                                document.getElementById('edit-region').value = d.region || d.wilayah || 'Kabupaten Banyuwangi';
                                if (document.getElementById('edit-kode-wilayah')) {
                                    document.getElementById('edit-kode-wilayah').value = d.kode_wilayah || '35.10';
                                }
                                document.getElementById('edit-period').value = d.period || d.periode || 'Tahunan';
                                document.getElementById('edit-year').value = d.year || d.tahun || 2024;
                                document.getElementById('edit-value').value = d.value !== undefined ? d.value : d.nilai;
                                document.getElementById('edit-unit').value = d.unit || d.satuan || '%';
                                document.getElementById('edit-source').value = d.source || d.sumber_data || '';
                                const editDefEl = document.getElementById('edit-definition');
                                if (editDefEl) {
                                    editDefEl.value = d.definition || d.definisi_operasional || '';
                                }
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
                    AppSwal.confirm({
                        title: 'Pindahkan ke Tempat Sampah?',
                        html: `Pindahkan indikator <strong>"${indicator}"</strong> ke tempat sampah?<br><small style="color:#64748b; font-size:11.5px; margin-top:6px; display:block;">Data tidak akan dihapus permanen dan dapat dipulihkan sewaktu-waktu dari Tempat Sampah.</small>`,
                        confirmButtonText: 'Ya',
                        cancelButtonText: 'Tidak',
                        icon: 'warning'
                    }).then(result => {
                        if (result && result.isConfirmed) {
                            fetch(`${cleanSite}/database/soft_delete/${id}`, {
                                method: 'POST',
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            })
                                .then(res => res.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        location.reload();
                                    } else {
                                        AppSwal.error('Gagal Memproses', data.message || 'Gagal memproses soft delete.');
                                    }
                                })
                                .catch(() => {
                                    window.location.href = `${cleanSite}/database/soft_delete/${id}`;
                                });
                        }
                    });
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
                    AppSwal.confirm({
                        title: 'Pulihkan Data?',
                        html: `Pulihkan indikator <strong>"${indicator}"</strong> kembali ke daftar aktif?`,
                        confirmButtonText: 'Ya',
                        cancelButtonText: 'Tidak',
                        icon: 'question'
                    }).then(result => {
                        if (result && result.isConfirmed) {
                            fetch(`${cleanSite}/database/restore/${id}`, {
                                method: 'POST',
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            })
                                .then(res => res.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        location.reload();
                                    } else {
                                        AppSwal.error('Gagal Memulihkan', data.message || 'Gagal memulihkan data.');
                                    }
                                })
                                .catch(() => {
                                    window.location.href = `${cleanSite}/database/restore/${id}`;
                                });
                        }
                    });
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
                    AppSwal.dangerConfirm({
                        title: 'Hapus Permanen?',
                        html: `Peringatan: Apakah Anda yakin ingin <strong>MENGHAPUS PERMANEN</strong> <strong>"${indicator}"</strong>?<br><br><span style="color:#e11d48; font-weight:600; font-size:12px;">Data yang dihapus permanen akan hilang selamanya dari database dan TIDAK DAPAT DIPULIHKAN LAGI!</span>`,
                        confirmButtonText: 'Ya',
                        cancelButtonText: 'Tidak'
                    }).then(result => {
                        if (result && result.isConfirmed) {
                            fetch(`${cleanSite}/database/hard_delete/${id}`, {
                                method: 'POST',
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            })
                                .then(res => res.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        location.reload();
                                    } else {
                                        AppSwal.error('Gagal Menghapus', data.message || 'Gagal menghapus data secara permanen.');
                                    }
                                })
                                .catch(() => {
                                    window.location.href = `${cleanSite}/database/hard_delete/${id}`;
                                });
                        }
                    });
                    return;
                }
            });
        }

        if (btnExportCsv) {
            btnExportCsv.addEventListener('click', () => {
                const matrixTable = document.getElementById('admin-matrix-table');
                if (matrixTable) {
                    const rows = [];
                    const headerRow = [];
                    matrixTable.querySelectorAll('thead tr th').forEach((th, idx) => {
                        // Skip last column (Aksi)
                        if (idx < matrixTable.querySelectorAll('thead tr th').length - 1) {
                            headerRow.push(th.textContent.trim().replace(/\s+/g, ' '));
                        }
                    });
                    rows.push(headerRow);

                    matrixTable.querySelectorAll('tbody tr.matrix-row').forEach(tr => {
                        if (tr.style.display === 'none') return;
                        const row = [];
                        const tds = tr.querySelectorAll('td');
                        for (let i = 0; i < tds.length - 1; i++) {
                            const td = tds[i];
                            const numEl = td.querySelector('.matrix-val-text');
                            if (numEl) {
                                row.push(numEl.textContent.trim());
                            } else if (td.querySelector('.matrix-dash')) {
                                row.push('');
                            } else {
                                const strong = td.querySelector('strong');
                                if (strong) {
                                    row.push(strong.textContent.trim());
                                } else {
                                    row.push(td.textContent.trim().replace(/\s+/g, ' '));
                                }
                            }
                        }
                        rows.push(row);
                    });
                    downloadCSV('database-ekonomi-tahunan.csv', rows);
                } else {
                    downloadCSV('database-ekonomi-banyuwangi.csv', [
                        ['Kode', 'Indikator', 'Klaster', 'Definisi Operasional', 'Wilayah', 'Periode', 'Tahun', 'Nilai', 'Satuan', 'Sumber', 'Status'],
                        ...AppState.records.map(r => [r.code, r.indicator, r.cluster, r.definition || r.definisi_operasional || '', r.region, r.period, r.year, r.value, r.unit, r.source, r.status])
                    ]);
                }
            });
        }

        // ==========================================
        // FITUR PDRB SEKTORAL (17 LAPANGAN USAHA BPS)
        // ==========================================
        const modalSectoral = document.getElementById('modal-sectoral-matrix');
        const btnOpenSectoral = document.getElementById('btn-open-sectoral-modal');
        const btnCloseSectoral = document.getElementById('modal-btn-close-sectoral');
        const btnCancelSectoral = document.getElementById('modal-btn-cancel-sectoral');
        const btnSaveSectoral = document.getElementById('modal-btn-save-sectoral');
        const btnSecAddPrev = document.getElementById('btn-sec-add-prev-year');
        const btnSecAddNext = document.getElementById('btn-sec-add-next-year');
        const secThead = document.getElementById('sectoral-matrix-thead');
        const secTbody = document.getElementById('sectoral-matrix-tbody');
        const secMsgBox = document.getElementById('sec-modal-msg');

        let sectoralState = {
            years: [2018, 2019, 2020, 2021, 2022, 2023, 2024, 2025],
            definitions: {},
            values: {},
            daily_dates: [],
            daily_values: {},
            hrg005_structure: null,
            is_hrg005: false,
            kode_wilayah: '35.10',
            parent_code: 'EKO-003',
            category_type: 'sectoral',
            unit: 'Miliar rupiah',
            can_auto_sum: true
        };

        function closeSectoralModal() {
            if (modalSectoral) modalSectoral.style.display = 'none';
        }

        if (btnCloseSectoral) btnCloseSectoral.addEventListener('click', closeSectoralModal);
        if (btnCancelSectoral) btnCancelSectoral.addEventListener('click', closeSectoralModal);
        if (modalSectoral) {
            modalSectoral.addEventListener('click', e => {
                if (e.target === modalSectoral) closeSectoralModal();
            });
        }

        // Buka Modal Matriks langsung dari tombol di dalam Modal Edit
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-open-matrix-from-edit');
            if (btn) {
                e.preventDefault();
                const pCode = btn.getAttribute('data-parent') || 'EKO-003';
                if (editModal) editModal.style.display = 'none';
                openSectoralModal(pCode);
            }
        });

        // Live auto-calculation untuk kategori yang dapat dijumlahkan (Total PDRB / Total Pengeluaran / Total Investasi)
        function calculateSectoralTotals() {
            if (!sectoralState.years || !Array.isArray(sectoralState.years)) return;
            if (!sectoralState.can_auto_sum) return; // e.g. Laju pertumbuhan (EKO-006) tidak dijumlahkan langsung

            const isInv = (sectoralState.category_type === 'quarterly' || (sectoralState.parent_code && sectoralState.parent_code.startsWith('INV-')));
            const isExp = (sectoralState.category_type === 'expenditure');
            const isTk08 = (sectoralState.category_type === 'tk08' || sectoralState.parent_code === 'TK-008');
            const isPro = (sectoralState.category_type === 'pro' || (sectoralState.parent_code && sectoralState.parent_code.startsWith('PRO-')));
            let keysToSum = [];
            if (isInv) {
                keysToSum = ['Q1', 'Q2', 'Q3', 'Q4'];
            } else if (isExp) {
                keysToSum = ['1', '2', '3', '4', '5', '6'];
            } else if (isTk08) {
                keysToSum = ['FORMAL', 'INFORMAL'];
            } else if (isPro) {
                keysToSum = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25'];
            } else {
                keysToSum = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'MN', 'O', 'P', 'Q', 'RSTU'];
            }

            sectoralState.years.forEach(yr => {
                let sum = 0;
                let hasAnyVal = false;
                keysToSum.forEach(k => {
                    const input = document.querySelector(`.sec-val-input[data-cat="${k}"][data-year="${yr}"]`);
                    if (input && input.value !== '') {
                        const v = parseRupiahNumber(input.value);
                        if (!isNaN(v) && v !== 0) {
                            sum += v;
                            hasAnyVal = true;
                        } else if (input.value.trim() === '0') {
                            hasAnyVal = true;
                        }
                    }
                });

                const totalInput = document.querySelector(`.sec-val-input[data-cat="TOTAL"][data-year="${yr}"]`);
                if (totalInput) {
                    const roundedSum = hasAnyVal ? (Math.round(sum * 100) / 100).toFixed(2) : '';
                    totalInput.value = hasAnyVal ? formatRupiahNumber(roundedSum) : '';
                    const parentCode = sectoralState.parent_code;
                    if (parentCode) {
                        if (!sectoralState.values[parentCode]) sectoralState.values[parentCode] = {};
                        sectoralState.values[parentCode][yr] = hasAnyVal ? parseFloat(roundedSum) : '';
                    }
                }
            });
        }

        function calculateMonthlyGroupTotals() {
            if (!sectoralState.is_monthly_group) return;
            const defs = sectoralState.definitions;
            const yearsList = sectoralState.years || [2024, 2025, 2026];
            const standardMonths = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

            if (!sectoralState.monthly_values) sectoralState.monthly_values = {};

            // 1. Hitung Tahunan (Rata-rata 12 bulan) untuk setiap baris kelompok
            Object.keys(defs).forEach(k => {
                if (k === 'TOTAL') return;
                const code = defs[k].code;
                if (!sectoralState.monthly_values[code]) sectoralState.monthly_values[code] = {};

                yearsList.forEach(yr => {
                    if (!sectoralState.monthly_values[code][yr]) sectoralState.monthly_values[code][yr] = {};
                    let sum = 0;
                    let count = 0;

                    standardMonths.forEach(m => {
                        const inp = document.querySelector(`.sec-monthly-input[data-code="${code}"][data-year="${yr}"][data-month="${m}"]`);
                        let val = (inp && inp.value !== '') ? parseRupiahNumber(inp.value) : (sectoralState.monthly_values[code][yr][m] !== undefined ? sectoralState.monthly_values[code][yr][m] : null);
                        if (val !== null && val !== '' && !isNaN(val)) {
                            sum += val;
                            count++;
                        }
                    });

                    const annVal = count > 0 ? (Math.round((sum / count) * 100) / 100) : '';
                    sectoralState.monthly_values[code][yr]['Tahunan'] = annVal;
                    const annInp = document.querySelector(`.sec-monthly-annual-auto[data-code="${code}"][data-year="${yr}"]`);
                    if (annInp) {
                        annInp.value = (annVal !== '') ? formatRupiahNumber(annVal) : '';
                    }
                });
            });

            // 2. Hitung baris TOTAL (Rata-rata 11 kelompok) untuk setiap bulan dan tahun
            if (!sectoralState.monthly_values['TOTAL']) sectoralState.monthly_values['TOTAL'] = {};
            const allMonths = standardMonths.concat(['Tahunan']);

            yearsList.forEach(yr => {
                if (!sectoralState.monthly_values['TOTAL'][yr]) sectoralState.monthly_values['TOTAL'][yr] = {};
                allMonths.forEach(m => {
                    let mSum = 0;
                    let mCount = 0;
                    Object.keys(defs).forEach(k => {
                        if (k === 'TOTAL') return;
                        const code = defs[k].code;
                        const val = sectoralState.monthly_values[code] && sectoralState.monthly_values[code][yr] ? sectoralState.monthly_values[code][yr][m] : null;
                        if (val !== null && val !== undefined && val !== '' && !isNaN(val)) {
                            mSum += val;
                            mCount++;
                        }
                    });
                    const totVal = mCount > 0 ? (Math.round((mSum / mCount) * 100) / 100) : '';
                    sectoralState.monthly_values['TOTAL'][yr][m] = totVal;
                    const totInp = document.querySelector(`.sec-monthly-total-auto[data-code="TOTAL"][data-year="${yr}"][data-month="${m}"]`);
                    if (totInp) {
                        totInp.value = (totVal !== '') ? formatRupiahNumber(totVal) : '';
                    }
                });
            });
        }

        function renderPar004MonthlyTable() {
            if (!secThead || !secTbody) return;

            const mVals = sectoralState.monthly_values || {};
            const standardMonths = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

            const parCols = [
                { key: 'BIN', code: 'PAR-004.BIN', label: 'Bintang', width: '105px' },
                { key: 'NON', code: 'PAR-004.NON', label: 'Non Bintang', width: '115px' },
                { key: 'ALL', code: 'PAR-004', label: 'Seluruh Jenis Hotel', width: '140px', isTotal: true }
            ];

            let yearsList = (sectoralState.years && sectoralState.years.length > 0) ? sectoralState.years : [2023, 2024, 2025, 2026];
            if (yearsList.includes(2018) && yearsList.length > 6) {
                yearsList = [2023, 2024, 2025, 2026];
            }
            sectoralState.years = yearsList;

            const defaultMonthlyThemes = [
                { bg: '#ffedd5', text: '#9a3412', border: '#cbd5e1' }, // Orange (2023)
                { bg: '#e0f2fe', text: '#0369a1', border: '#cbd5e1' }, // Sky (2024)
                { bg: '#f0fdf4', text: '#166534', border: '#cbd5e1' }, // Emerald (2025)
                { bg: '#fef3c7', text: '#92400e', border: '#cbd5e1' }, // Amber (2026)
                { bg: '#f3e8ff', text: '#6b21a8', border: '#cbd5e1' }, // Purple (2027)
                { bg: '#ffe4e6', text: '#9f1239', border: '#cbd5e1' }  // Rose (2028)
            ];
            const getYearTheme = function (yr) {
                if (yr === 2023) return defaultMonthlyThemes[0];
                if (yr === 2024) return defaultMonthlyThemes[1];
                if (yr === 2025) return defaultMonthlyThemes[2];
                if (yr === 2026) return defaultMonthlyThemes[3];
                if (yr === 2027) return defaultMonthlyThemes[4];
                if (yr === 2028) return defaultMonthlyThemes[5];
                return defaultMonthlyThemes[Math.abs(yr % defaultMonthlyThemes.length)];
            };

            // THEAD
            let theadHtml = `<tr>
                <th rowspan="2" style="width:45px; min-width:45px; max-width:45px; position:sticky; left:0; top:0; background:#f1f5f9; z-index:30; text-align:center; vertical-align:middle; border-bottom:1.5px solid #cbd5e1; border-right:1px solid #e2e8f0; font-size:11.5px; font-weight:800; padding:8px 4px;">No</th>
                <th rowspan="2" style="width:140px; min-width:140px; max-width:160px; position:sticky; left:45px; top:0; background:#f1f5f9; z-index:30; text-align:left; vertical-align:middle; border-bottom:1.5px solid #cbd5e1; border-right:2px solid #94a3b8; font-size:11.5px; font-weight:800; padding:8px 12px;">Bulan</th>`;

            yearsList.forEach(yr => {
                const theme = getYearTheme(yr);
                const canDel = (yearsList.length > 1);
                theadHtml += `<th colspan="3" style="text-align:center; background:${theme.bg}; color:${theme.text}; font-weight:800; font-size:12.5px; padding:7px 10px; border-bottom:1px solid #cbd5e1; border-right:2px solid #94a3b8; position:sticky; top:0; z-index:20;">
                    <div style="display:inline-flex; align-items:center; justify-content:center; gap:8px;">
                        <span>Tahun ${yr}</span>
                        ${canDel ? `<button type="button" class="btn-sec-del-year" data-year="${yr}" title="Hapus Tahun ${yr} dari tabel" style="background:rgba(239,68,68,0.12); color:#dc2626; border:1px solid rgba(239,68,68,0.3); border-radius:4px; width:18px; height:18px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; font-size:13px; line-height:1; font-weight:bold; padding:0; transition:all 0.15s ease;" onmouseover="this.style.background='#dc2626';this.style.color='#fff';" onmouseout="this.style.background='rgba(239,68,68,0.12)';this.style.color='#dc2626';">&times;</button>` : ''}
                    </div>
                </th>`;
            });
            theadHtml += `</tr><tr>`;

            yearsList.forEach(yr => {
                parCols.forEach(col => {
                    const isTotalCol = col.isTotal;
                    const colBg = isTotalCol ? '#dcfce7' : '#f8fafc';
                    const colColor = isTotalCol ? '#166534' : '#1e293b';
                    const borderR = isTotalCol ? '2px solid #94a3b8' : '1px solid #e2e8f0';
                    theadHtml += `<th style="width:${col.width}; min-width:${col.width}; padding:6px 6px; font-weight:800; font-size:11px; color:${colColor}; border-bottom:1.5px solid #cbd5e1; border-right:${borderR}; text-align:center; background:${colBg}; position:sticky; top:36px; z-index:20;" title="${col.label} ${yr} (%)">${col.label}</th>`;
                });
            });
            theadHtml += `</tr>`;
            secThead.innerHTML = theadHtml;

            // TBODY
            let tbodyHtml = '';
            standardMonths.forEach((m, idx) => {
                tbodyHtml += `<tr>`;
                tbodyHtml += `<td style="position:sticky; left:0; background:#ffffff; z-index:10; padding:6px 4px; font-weight:800; font-size:11.5px; border-bottom:1px solid #e2e8f0; border-right:1px solid #e2e8f0; text-align:center; vertical-align:middle; width:45px;">
                    <span style="display:inline-block; min-width:24px; padding:2px 4px; font-size:11px; font-weight:800; border-radius:5px; background:#f1f5f9; color:#0369a1; border:1px solid #cbd5e1;">${idx + 1}</span>
                </td>`;
                tbodyHtml += `<td style="position:sticky; left:45px; background:#ffffff; z-index:10; padding:6px 12px; font-weight:700; font-size:12px; border-bottom:1px solid #e2e8f0; border-right:2px solid #94a3b8; vertical-align:middle; color:#0f172a; width:140px;">
                    ${m}
                </td>`;

                yearsList.forEach(yr => {
                    parCols.forEach(col => {
                        const existingVal = (mVals[col.code] && mVals[col.code][yr] && mVals[col.code][yr][m] !== undefined) ? mVals[col.code][yr][m] : '';
                        const formattedVal = (existingVal !== '' && existingVal !== null) ? formatRupiahNumber(existingVal) : '';
                        const isTotalCol = col.isTotal;
                        const cellBg = isTotalCol ? '#f0fdf4' : '#ffffff';
                        const inputStyle = isTotalCol 
                            ? 'width:100%; height:28px; padding:0 6px; font-weight:800; font-size:11.5px; border:1.5px solid #86efac; border-radius:4px; text-align:right; background:#dcfce7; color:#15803d;'
                            : 'width:100%; height:28px; padding:0 6px; font-weight:600; font-size:11.5px; border:1px solid #cbd5e1; border-radius:4px; text-align:right; background:#ffffff; color:#0f172a;';
                        const borderR = isTotalCol ? '2px solid #94a3b8' : '1px solid #e2e8f0';

                        tbodyHtml += `<td style="padding:4px 4px; border-bottom:1px solid #e2e8f0; border-right:${borderR}; text-align:right; vertical-align:middle; background:${cellBg};">
                            <input type="text" inputmode="decimal" class="sec-monthly-input is-rupiah-mask" data-code="${col.code}" data-year="${yr}" data-month="${m}" value="${formattedVal}" placeholder="-" style="${inputStyle}">
                        </td>`;
                    });
                });

                tbodyHtml += `</tr>`;
            });

            // ROW TAHUNAN (RATA-RATA TAHUNAN)
            tbodyHtml += `<tr style="background:#f0fdf4; border-top:2px solid #86efac;">`;
            tbodyHtml += `<td style="position:sticky; left:0; background:#dcfce7; z-index:10; padding:6px 4px; font-weight:800; font-size:10px; border-bottom:1px solid #86efac; border-right:1px solid #86efac; text-align:center; vertical-align:middle;">
                <span class="badge success" style="font-size:9px; font-weight:800; padding:3px 5px; border-radius:4px;">AVG</span>
            </td>`;
            tbodyHtml += `<td style="position:sticky; left:45px; background:#dcfce7; z-index:10; padding:6px 12px; font-weight:800; font-size:12px; border-bottom:1px solid #86efac; border-right:2px solid #94a3b8; vertical-align:middle; color:#166534;">
                Rata-rata Tahunan
            </td>`;

            yearsList.forEach(yr => {
                parCols.forEach(col => {
                    const existingAnn = (mVals[col.code] && mVals[col.code][yr] && mVals[col.code][yr]['Tahunan'] !== undefined) ? mVals[col.code][yr]['Tahunan'] : '';
                    const formattedAnn = (existingAnn !== '' && existingAnn !== null) ? formatRupiahNumber(existingAnn) : '';
                    const isTotalCol = col.isTotal;
                    const borderR = isTotalCol ? '2px solid #94a3b8' : '1px solid #86efac';

                    tbodyHtml += `<td style="padding:4px 4px; border-bottom:1px solid #86efac; border-right:${borderR}; text-align:right; vertical-align:middle; background:#f0fdf4;">
                        <input type="text" readonly class="sec-monthly-annual-auto is-rupiah-mask" data-code="${col.code}" data-year="${yr}" data-month="Tahunan" value="${formattedAnn}" placeholder="-" title="Rata-rata Tahunan ${col.label} ${yr}" style="width:100%; height:28px; padding:0 6px; font-weight:800; font-size:11.5px; border:1.5px solid #86efac; border-radius:4px; text-align:right; background:#dcfce7; color:#166534; cursor:not-allowed;">
                    </td>`;
                });
            });
            tbodyHtml += `</tr>`;

            secTbody.innerHTML = tbodyHtml;
            calculatePar004Totals();
        }

        function calculatePar004Totals() {
            if (sectoralState.parent_code !== 'PAR-004') return;
            const years = sectoralState.years || [];
            const parCodes = ['PAR-004.BIN', 'PAR-004.NON', 'PAR-004'];

            years.forEach(yr => {
                parCodes.forEach(code => {
                    const monthInputs = document.querySelectorAll(`.sec-monthly-input[data-code="${code}"][data-year="${yr}"]`);
                    let sum = 0;
                    let count = 0;
                    monthInputs.forEach(inp => {
                        const val = inp.value.trim();
                        if (val !== '' && val !== '-') {
                            const num = parseRupiahNumber(val);
                            if (!isNaN(num)) {
                                sum += num;
                                count++;
                            }
                        }
                    });

                    const annualInput = document.querySelector(`.sec-monthly-annual-auto[data-code="${code}"][data-year="${yr}"][data-month="Tahunan"]`);
                    if (count > 0) {
                        const avg = Math.round((sum / count) * 100) / 100;
                        if (annualInput) annualInput.value = formatRupiahNumber(avg);
                        if (!sectoralState.monthly_values) sectoralState.monthly_values = {};
                        if (!sectoralState.monthly_values[code]) sectoralState.monthly_values[code] = {};
                        if (!sectoralState.monthly_values[code][yr]) sectoralState.monthly_values[code][yr] = {};
                        sectoralState.monthly_values[code][yr]['Tahunan'] = avg;
                        if (!sectoralState.values) sectoralState.values = {};
                        if (!sectoralState.values[code]) sectoralState.values[code] = {};
                        sectoralState.values[code][yr] = avg;
                    } else {
                        if (annualInput) annualInput.value = '';
                    }
                });
            });
        }

        function renderHrg005ExcelTable() {
            if (!secThead || !secTbody) return;

            const defs = sectoralState.definitions || {};
            const vals = sectoralState.values || {};
            const dVals = sectoralState.daily_values || {};
            let yearsList = (sectoralState.years && sectoralState.years.length > 0) ? sectoralState.years : [2024, 2025, 2026];
            sectoralState.years = yearsList;

            let dailyDates = (sectoralState.daily_dates && sectoralState.daily_dates.length > 0) ? sectoralState.daily_dates : [];
            if (dailyDates.length === 0) {
                for (let d = 1; d <= 30; d++) {
                    dailyDates.push(`2026-11-${String(d).padStart(2, '0')}`);
                }
                sectoralState.daily_dates = dailyDates;
            }

            const escapeAttr = function (str) {
                if (!str) return '';
                return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            };

            const monthNamesId = {
                '01': 'Januari', '02': 'Februari', '03': 'Maret', '04': 'April',
                '05': 'Mei', '06': 'Juni', '07': 'Juli', '08': 'Agustus',
                '09': 'September', '10': 'Oktober', '11': 'November', '12': 'Desember'
            };

            // Kelompokkan tanggal harian menurut bulan (YYYY-MM)
            const dateGroups = [];
            const dateGroupMap = {};
            dailyDates.forEach(dStr => {
                const ym = dStr.slice(0, 7);
                if (!dateGroupMap[ym]) {
                    const [yPart, mPart] = ym.split('-');
                    const grp = { ym, label: `${monthNamesId[mPart] || mPart} ${yPart}`, dates: [] };
                    dateGroupMap[ym] = grp;
                    dateGroups.push(grp);
                }
                dateGroupMap[ym].dates.push(dStr);
            });

            // THEAD (2 baris: Sticky NO, NAMA BAHAN POKOK, SATUAN + Ringkasan Tahunan + Pemantauan Harian)
            let theadHtml = `<tr>
                <th rowspan="2" style="width:46px; min-width:46px; max-width:46px; position:sticky; left:0; top:0; background:#f1f5f9; z-index:30; text-align:center; vertical-align:middle; border-bottom:1.5px solid #cbd5e1; border-right:1px solid #e2e8f0; font-size:11px; font-weight:800; padding:8px 4px; color:#334155;">NO</th>
                <th rowspan="2" style="width:280px; min-width:280px; max-width:300px; position:sticky; left:46px; top:0; background:#f1f5f9; z-index:30; text-align:left; vertical-align:middle; border-bottom:1.5px solid #cbd5e1; border-right:1px solid #e2e8f0; font-size:11px; font-weight:800; padding:8px 10px; color:#334155;">NAMA BAHAN POKOK</th>
                <th rowspan="2" style="width:92px; min-width:92px; max-width:92px; position:sticky; left:326px; top:0; background:#f1f5f9; z-index:30; text-align:center; vertical-align:middle; border-bottom:1.5px solid #cbd5e1; border-right:2px solid #94a3b8; font-size:11px; font-weight:800; padding:8px 6px; color:#334155;">SATUAN</th>
                <th colspan="${yearsList.length}" style="text-align:center; background:#e0f2fe; color:#0369a1; font-weight:800; font-size:12px; padding:6px 8px; border-bottom:1px solid #cbd5e1; border-right:2px solid #94a3b8; position:sticky; top:0; z-index:20;">Ringkasan Tahunan (Rp)</th>`;

            dateGroups.forEach(dg => {
                theadHtml += `<th colspan="${dg.dates.length}" style="text-align:center; background:#fef3c7; color:#92400e; font-weight:800; font-size:12px; padding:6px 10px; border-bottom:1px solid #cbd5e1; border-right:2px solid #94a3b8; position:sticky; top:0; z-index:20;">Pemantauan Harian: ${escapeAttr(dg.label)}</th>`;
            });
            theadHtml += `</tr><tr>`;

            yearsList.forEach((yr, yIdx) => {
                const isLastYr = (yIdx === yearsList.length - 1);
                const canDel = (yearsList.length > 1);
                theadHtml += `<th style="width:105px; min-width:105px; padding:6px 4px; font-weight:800; font-size:11px; color:#0369a1; border-bottom:1.5px solid #cbd5e1; border-right:${isLastYr ? '2px solid #94a3b8' : '1px solid #e2e8f0'}; text-align:center; background:#f0f9ff; position:sticky; top:35px; z-index:20;">
                    <div style="display:inline-flex; align-items:center; justify-content:center; gap:4px;">
                        <span>${yr}</span>
                        ${canDel ? `<button type="button" class="btn-sec-del-year" data-year="${yr}" title="Hapus Kolom Tahun ${yr}" style="background:none; border:none; color:#94a3b8; cursor:pointer; font-size:12px; line-height:1; padding:0 2px;">&times;</button>` : ''}
                    </div>
                </th>`;
            });

            dateGroups.forEach(dg => {
                dg.dates.forEach((dStr, dIdx) => {
                    const isLastD = (dIdx === dg.dates.length - 1);
                    theadHtml += `<th style="width:94px; min-width:94px; padding:6px 4px; font-weight:800; font-size:10.5px; color:#1e293b; border-bottom:1.5px solid #cbd5e1; border-right:${isLastD ? '2px solid #94a3b8' : '1px solid #e2e8f0'}; text-align:center; background:#f8fafc; position:sticky; top:35px; z-index:20; white-space:nowrap;">${dStr}</th>`;
                });
            });
            theadHtml += `</tr>`;
            secThead.innerHTML = theadHtml;

            const structure = sectoralState.hrg005_structure || {};
            const groupKeys = Object.keys(structure).sort((a, b) => Number(a) - Number(b));

            const buildGroupHeaderRow = (noVal, title, isSubHeader) => {
                const bg = isSubHeader ? '#f8fafc' : '#e2e8f0';
                const stickyBg = isSubHeader ? '#f8fafc' : '#e2e8f0';
                const txtColor = isSubHeader ? '#334155' : '#0f172a';
                const fontStyle = isSubHeader ? 'font-weight:700; font-style:italic;' : 'font-weight:800; text-transform:uppercase;';
                let rowHtml = `<tr style="background:${bg};">
                    <td style="position:sticky; left:0; background:${stickyBg}; z-index:10; padding:6px 4px; font-weight:800; font-size:11.5px; border-bottom:1px solid #cbd5e1; border-right:1px solid #cbd5e1; text-align:center; color:#0369a1;">${noVal ? escapeAttr(noVal) : ''}</td>
                    <td style="position:sticky; left:46px; background:${stickyBg}; z-index:10; padding:6px 10px; ${fontStyle} font-size:12px; border-bottom:1px solid #cbd5e1; border-right:1px solid #cbd5e1; color:${txtColor};">${escapeAttr(title)}</td>
                    <td style="position:sticky; left:326px; background:${stickyBg}; z-index:10; padding:6px 6px; border-bottom:1px solid #cbd5e1; border-right:2px solid #94a3b8; text-align:center; color:#94a3b8; font-size:11px;"></td>`;
                yearsList.forEach((yr, yIdx) => {
                    const isLastYr = (yIdx === yearsList.length - 1);
                    rowHtml += `<td style="padding:5px 6px; border-bottom:1px solid #cbd5e1; border-right:${isLastYr ? '2px solid #94a3b8' : '1px solid #cbd5e1'}; text-align:right; font-size:11px; color:#64748b; background:${bg};">0</td>`;
                });
                dateGroups.forEach(dg => {
                    dg.dates.forEach((dStr, dIdx) => {
                        const isLastD = (dIdx === dg.dates.length - 1);
                        rowHtml += `<td style="padding:5px 6px; border-bottom:1px solid #cbd5e1; border-right:${isLastD ? '2px solid #94a3b8' : '1px solid #cbd5e1'}; text-align:right; font-size:11px; color:#64748b; background:${bg};">0</td>`;
                    });
                });
                rowHtml += `</tr>`;
                return rowHtml;
            };

            const buildItemRow = (itemKey, noOverride) => {
                const item = defs[itemKey];
                if (!item) return '';
                const labelText = `- ${item.excel_label || item.short_name || item.name || ''}`;
                const unitText = item.unit || 'kg';

                let rowHtml = `<tr style="background:#ffffff;">
                    <td style="position:sticky; left:0; background:#ffffff; z-index:10; padding:5px 4px; font-weight:800; font-size:11.5px; border-bottom:1px solid #e2e8f0; border-right:1px solid #e2e8f0; text-align:center; vertical-align:middle; color:#0369a1;">${noOverride ? escapeAttr(noOverride) : ''}</td>
                    <td style="position:sticky; left:46px; background:#ffffff; z-index:10; padding:5px 10px 5px ${noOverride ? '10px' : '16px'}; font-weight:600; font-size:12px; border-bottom:1px solid #e2e8f0; border-right:1px solid #e2e8f0; vertical-align:middle; color:#0f172a;">${escapeAttr(labelText)}</td>
                    <td style="position:sticky; left:326px; background:#ffffff; z-index:10; padding:5px 6px; font-weight:600; font-size:11.5px; border-bottom:1px solid #e2e8f0; border-right:2px solid #94a3b8; text-align:center; vertical-align:middle; color:#475569; white-space:nowrap;">${escapeAttr(unitText)}</td>`;

                yearsList.forEach((yr, yIdx) => {
                    const isLastYr = (yIdx === yearsList.length - 1);
                    const existingVal = (vals[item.code] && vals[item.code][yr] !== undefined) ? vals[item.code][yr] : '';
                    const formattedVal = (existingVal !== '' && existingVal !== null) ? formatRupiahNumber(existingVal) : '';
                    rowHtml += `<td style="padding:4px 4px; border-bottom:1px solid #e2e8f0; border-right:${isLastYr ? '2px solid #94a3b8' : '1px solid #e2e8f0'}; text-align:right; vertical-align:middle; background:#f8fafc;">
                        <input type="text" inputmode="decimal" class="sec-val-input is-rupiah-mask" data-cat="${itemKey}" data-code="${item.code}" data-year="${yr}" value="${formattedVal}" placeholder="-" style="width:100%; height:28px; padding:0 5px; font-weight:700; font-size:11.5px; border:1px solid #cbd5e1; border-radius:3px; text-align:right; background:#ffffff; color:#0f172a;">
                    </td>`;
                });

                dateGroups.forEach(dg => {
                    dg.dates.forEach((dStr, dIdx) => {
                        const isLastD = (dIdx === dg.dates.length - 1);
                        const existingDaily = (dVals[item.code] && dVals[item.code][dStr] !== undefined) ? dVals[item.code][dStr] : '';
                        const formattedDaily = (existingDaily !== '' && existingDaily !== null) ? formatRupiahNumber(existingDaily) : '';
                        rowHtml += `<td style="padding:4px 3px; border-bottom:1px solid #e2e8f0; border-right:${isLastD ? '2px solid #94a3b8' : '1px solid #e2e8f0'}; text-align:right; vertical-align:middle; background:#ffffff;">
                            <input type="text" inputmode="decimal" class="sec-daily-input is-rupiah-mask" data-cat="${itemKey}" data-code="${item.code}" data-date="${dStr}" value="${formattedDaily}" placeholder="-" style="width:100%; height:28px; padding:0 4px; font-weight:600; font-size:11.5px; border:1px solid #cbd5e1; border-radius:3px; text-align:right; background:#ffffff; color:#0f172a;">
                        </td>`;
                    });
                });

                rowHtml += `</tr>`;
                return rowHtml;
            };

            let tbodyHtml = '';
            groupKeys.forEach(gNo => {
                const grp = structure[gNo];
                if (!grp) return;
                if (grp.single && grp.items && grp.items.length === 1) {
                    tbodyHtml += buildItemRow(grp.items[0], gNo);
                } else {
                    tbodyHtml += buildGroupHeaderRow(gNo, grp.name, false);
                    if (Array.isArray(grp.subgroups) && grp.subgroups.length > 0) {
                        grp.subgroups.forEach(subGrp => {
                            tbodyHtml += buildGroupHeaderRow('', subGrp.name, true);
                            (subGrp.items || []).forEach(k => {
                                tbodyHtml += buildItemRow(k, '');
                            });
                        });
                    } else {
                        (grp.items || []).forEach(k => {
                            tbodyHtml += buildItemRow(k, '');
                        });
                    }
                }
            });

            // Summary Row (AVG)
            const totalDef = defs['TOTAL'] || { code: 'HRG-005', short_name: 'RATA-RATA KOMODITAS PANGAN', unit: 'Rupiah' };
            tbodyHtml += `<tr style="background:#f0fdf4; border-top:2px solid #86efac;">
                <td style="position:sticky; left:0; background:#dcfce7; z-index:10; padding:6px 4px; font-weight:800; font-size:10px; border-bottom:1px solid #86efac; border-right:1px solid #86efac; text-align:center; vertical-align:middle;">
                    <span class="badge success" style="font-size:9px; font-weight:800; padding:3px 5px; border-radius:4px;">AVG</span>
                </td>
                <td style="position:sticky; left:46px; background:#dcfce7; z-index:10; padding:6px 10px; font-weight:800; font-size:12px; border-bottom:1px solid #86efac; border-right:1px solid #86efac; vertical-align:middle; color:#15803d;">${escapeAttr(totalDef.short_name || 'RATA-RATA KOMODITAS PANGAN')}</td>
                <td style="position:sticky; left:326px; background:#dcfce7; z-index:10; padding:6px 6px; font-weight:800; font-size:11.5px; border-bottom:1px solid #86efac; border-right:2px solid #94a3b8; text-align:center; vertical-align:middle; color:#15803d;">${escapeAttr(totalDef.unit || 'Rupiah')}</td>`;

            yearsList.forEach((yr, yIdx) => {
                const isLastYr = (yIdx === yearsList.length - 1);
                const existingTot = (vals['TOTAL'] && vals['TOTAL'][yr] !== undefined) ? vals['TOTAL'][yr] : (vals['HRG-005'] && vals['HRG-005'][yr] !== undefined ? vals['HRG-005'][yr] : '');
                const fmtTot = (existingTot !== '' && existingTot !== null) ? formatRupiahNumber(existingTot) : '';
                tbodyHtml += `<td style="padding:4px 4px; border-bottom:1px solid #86efac; border-right:${isLastYr ? '2px solid #94a3b8' : '1px solid #86efac'}; text-align:right; vertical-align:middle; background:#f0fdf4;">
                    <input type="text" readonly class="sec-val-input sec-hrg005-avg-annual is-rupiah-mask" data-cat="TOTAL" data-code="HRG-005" data-year="${yr}" value="${fmtTot}" placeholder="-" style="width:100%; height:28px; padding:0 5px; font-weight:800; font-size:11.5px; border:1.5px solid #86efac; border-radius:3px; text-align:right; background:#dcfce7; color:#15803d; cursor:not-allowed;">
                </td>`;
            });

            dateGroups.forEach(dg => {
                dg.dates.forEach((dStr, dIdx) => {
                    const isLastD = (dIdx === dg.dates.length - 1);
                    const existingDTot = (dVals['TOTAL'] && dVals['TOTAL'][dStr] !== undefined) ? dVals['TOTAL'][dStr] : '';
                    const fmtDTot = (existingDTot !== '' && existingDTot !== null) ? formatRupiahNumber(existingDTot) : '';
                    tbodyHtml += `<td style="padding:4px 3px; border-bottom:1px solid #86efac; border-right:${isLastD ? '2px solid #94a3b8' : '1px solid #86efac'}; text-align:right; vertical-align:middle; background:#f0fdf4;">
                        <input type="text" readonly class="sec-hrg005-avg-daily is-rupiah-mask" data-date="${dStr}" value="${fmtDTot}" placeholder="-" style="width:100%; height:28px; padding:0 4px; font-weight:800; font-size:11.5px; border:1.5px solid #86efac; border-radius:3px; text-align:right; background:#dcfce7; color:#15803d; cursor:not-allowed;">
                    </td>`;
                });
            });

            tbodyHtml += `</tr>`;
            secTbody.innerHTML = tbodyHtml;
            calculateHrg005Totals(false);
        }

        function calculateHrg005Totals(syncYearFromDaily = true) {
            if (sectoralState.parent_code !== 'HRG-005') return;
            const defs = sectoralState.definitions || {};
            const yearsList = sectoralState.years || [];
            const dailyDates = sectoralState.daily_dates || [];

            if (syncYearFromDaily) {
                Object.keys(defs).forEach(k => {
                    if (k === 'TOTAL') return;
                    const code = defs[k].code;
                    const byYr = {};
                    dailyDates.forEach(dStr => {
                        const yr = parseInt(dStr.slice(0, 4), 10);
                        const inp = document.querySelector(`.sec-daily-input[data-code="${code}"][data-date="${dStr}"]`);
                        const raw = inp ? inp.value.trim() : '';
                        if (raw !== '' && raw !== '-') {
                            const num = parseRupiahNumber(raw);
                            if (!isNaN(num)) {
                                if (!byYr[yr]) byYr[yr] = { sum: 0, count: 0 };
                                byYr[yr].sum += num;
                                byYr[yr].count++;
                            }
                        }
                    });
                    Object.keys(byYr).forEach(yrStr => {
                        const yr = parseInt(yrStr, 10);
                        const info = byYr[yr];
                        if (info.count > 0) {
                            const avg = Math.round((info.sum / info.count) * 100) / 100;
                            const yrInp = document.querySelector(`.sec-val-input[data-code="${code}"][data-year="${yr}"]`);
                            if (yrInp) yrInp.value = formatRupiahNumber(avg);
                            if (!sectoralState.values[code]) sectoralState.values[code] = {};
                            sectoralState.values[code][yr] = avg;
                        }
                    });
                });
            }

            // Rata-rata per Tahun
            yearsList.forEach(yr => {
                let sum = 0;
                let count = 0;
                Object.keys(defs).forEach(k => {
                    if (k === 'TOTAL') return;
                    const code = defs[k].code;
                    const inp = document.querySelector(`.sec-val-input[data-code="${code}"][data-year="${yr}"]`);
                    const raw = inp ? inp.value.trim() : '';
                    if (raw !== '' && raw !== '-') {
                        const num = parseRupiahNumber(raw);
                        if (!isNaN(num) && num > 0) {
                            sum += num;
                            count++;
                        }
                    }
                });
                const totInp = document.querySelector(`.sec-hrg005-avg-annual[data-year="${yr}"]`);
                if (count > 0) {
                    const avg = Math.round((sum / count) * 100) / 100;
                    if (totInp) totInp.value = formatRupiahNumber(avg);
                    if (!sectoralState.values['HRG-005']) sectoralState.values['HRG-005'] = {};
                    sectoralState.values['HRG-005'][yr] = avg;
                    if (!sectoralState.values['TOTAL']) sectoralState.values['TOTAL'] = {};
                    sectoralState.values['TOTAL'][yr] = avg;
                } else if (totInp) {
                    totInp.value = '';
                }
            });

            // Rata-rata per Tanggal Harian
            dailyDates.forEach(dStr => {
                let sum = 0;
                let count = 0;
                Object.keys(defs).forEach(k => {
                    if (k === 'TOTAL') return;
                    const code = defs[k].code;
                    const inp = document.querySelector(`.sec-daily-input[data-code="${code}"][data-date="${dStr}"]`);
                    const raw = inp ? inp.value.trim() : '';
                    if (raw !== '' && raw !== '-') {
                        const num = parseRupiahNumber(raw);
                        if (!isNaN(num) && num > 0) {
                            sum += num;
                            count++;
                        }
                    }
                });
                const totInp = document.querySelector(`.sec-hrg005-avg-daily[data-date="${dStr}"]`);
                if (count > 0) {
                    const avg = Math.round((sum / count) * 100) / 100;
                    if (totInp) totInp.value = formatRupiahNumber(avg);
                    if (!sectoralState.daily_values['TOTAL']) sectoralState.daily_values['TOTAL'] = {};
                    sectoralState.daily_values['TOTAL'][dStr] = avg;
                } else if (totInp) {
                    totInp.value = '';
                }
            });
        }

        function renderSectoralTable() {
            if (!secThead || !secTbody) return;

            const pCode = sectoralState.parent_code || '';
            const isPar004 = (pCode === 'PAR-004');
            const isHrgMonthly = ['HRG-002', 'HRG-006'].includes(pCode);
            const isMonthlyGroup = (sectoralState.is_monthly_group === true || isHrgMonthly || isPar004);
            sectoralState.is_monthly_group = isMonthlyGroup;

            if (isMonthlyGroup) {
                const btnAddCat = document.getElementById('btn-sec-add-cat-row');
                if (btnAddCat) btnAddCat.style.display = 'none';
            }

            if (isPar004) {
                renderPar004MonthlyTable();
                return;
            }

            if (pCode === 'HRG-005') {
                renderHrg005ExcelTable();
                return;
            }

            if (isMonthlyGroup) {
                const btnAddCat = document.getElementById('btn-sec-add-cat-row');
                if (btnAddCat) btnAddCat.style.display = 'none';

                let defs = sectoralState.definitions || {};
                const mVals = sectoralState.monthly_values || {};
                const standardMonths = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                let defKeys = Object.keys(defs);

                // Fallback jika definisi belum terload dari server
                if (defKeys.length === 0) {
                    defs = {};
                    const fallbackKelompoks = [
                        { k: '01', name: 'Makanan, Minuman, dan Tembakau' },
                        { k: '02', name: 'Pakaian dan Alas Kaki' },
                        { k: '03', name: 'Perumahan, Air, Listrik, dan Bahan Bakar Rumah Tangga' },
                        { k: '04', name: 'Perlengkapan, Peralatan, dan Pemeliharaan Rutin Rumah Tangga' },
                        { k: '05', name: 'Kesehatan' },
                        { k: '06', name: 'Transportasi' },
                        { k: '07', name: 'Informasi, Komunikasi, dan Jasa Keuangan' },
                        { k: '08', name: 'Rekreasi, Olahraga, dan Budaya' },
                        { k: '09', name: 'Pendidikan' },
                        { k: '10', name: 'Penyediaan Makanan dan Minuman/Restoran' },
                        { k: '11', name: 'Perawatan Pribadi dan Jasa Lainnya' },
                        { k: 'TOTAL', name: 'TOTAL / UMUM' }
                    ];
                    fallbackKelompoks.forEach(item => {
                        defs[item.k] = {
                            code: item.k === 'TOTAL' ? sectoralState.parent_code : `${sectoralState.parent_code}.${item.k}`,
                            cat_label: item.k,
                            name: item.name,
                            short_name: item.name
                        };
                    });
                    sectoralState.definitions = defs;
                    defKeys = Object.keys(defs);
                }

                const escapeAttr = function (str) {
                    if (!str) return '';
                    return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                };

                // ALL YEARS VIEW (Format Spreadsheet Excel: seluruh tahun berdampingan beserta 12 bulan lengkap)
                let yearsList = (sectoralState.years && sectoralState.years.length > 0) ? sectoralState.years : [2024, 2025, 2026];
                if (yearsList.includes(2018) && yearsList.length > 5) {
                    yearsList = [2024, 2025, 2026];
                }
                sectoralState.years = yearsList;

                const defaultMonthlyThemes = [
                    { bg: '#e0f2fe', text: '#0369a1', border: '#94a3b8' }, // Sky (2024)
                    { bg: '#f0fdf4', text: '#166534', border: '#94a3b8' }, // Emerald (2025)
                    { bg: '#fef3c7', text: '#92400e', border: '#94a3b8' }, // Amber (2026)
                    { bg: '#f3e8ff', text: '#6b21a8', border: '#94a3b8' }, // Purple (2027)
                    { bg: '#ffe4e6', text: '#9f1239', border: '#94a3b8' }, // Rose (2028)
                    { bg: '#ffedd5', text: '#9a3412', border: '#94a3b8' }, // Orange (2023)
                    { bg: '#e0e7ff', text: '#3730a3', border: '#94a3b8' }, // Indigo (2022)
                    { bg: '#ccfbf1', text: '#115e59', border: '#cbd5e1' }  // Teal
                ];
                const getMonthlyYearTheme = function (yr) {
                    if (yr === 2024) return defaultMonthlyThemes[0];
                    if (yr === 2025) return defaultMonthlyThemes[1];
                    if (yr === 2026) return defaultMonthlyThemes[2];
                    if (yr === 2027) return defaultMonthlyThemes[3];
                    if (yr === 2028) return defaultMonthlyThemes[4];
                    if (yr === 2023) return defaultMonthlyThemes[5];
                    if (yr === 2022) return defaultMonthlyThemes[6];
                    return defaultMonthlyThemes[Math.abs(yr % defaultMonthlyThemes.length)];
                };

                let theadHtml = `<tr>
                    <th rowspan="2" style="width:50px; min-width:50px; max-width:50px; position:sticky; left:0; top:0; background:#f1f5f9; z-index:30; text-align:center; vertical-align:middle; border-bottom:1.5px solid #cbd5e1; border-right:1px solid #e2e8f0; font-size:11.5px; font-weight:800; padding:8px 4px;">No</th>
                    <th rowspan="2" style="width:280px; min-width:280px; max-width:320px; position:sticky; left:50px; top:0; background:#f1f5f9; z-index:30; text-align:left; vertical-align:middle; border-bottom:1.5px solid #cbd5e1; border-right:2px solid #94a3b8; font-size:11.5px; font-weight:800; padding:8px 12px;">Kelompok Pengeluaran</th>`;
                
                yearsList.forEach(yr => {
                    const colTheme = getMonthlyYearTheme(yr);
                    const canDelete = (yearsList.length > 1);
                    theadHtml += `<th colspan="13" style="text-align:center; background:${colTheme.bg}; color:${colTheme.text}; font-weight:800; font-size:12.5px; padding:6px 8px; border-bottom:1px solid #cbd5e1; border-right:2px solid ${colTheme.border}; position:sticky; top:0; z-index:20;">
                        <div style="display:inline-flex; align-items:center; justify-content:center; gap:8px;">
                            <span>Tahun ${yr}</span>
                            ${canDelete ? `<button type="button" class="btn-sec-del-year" data-year="${yr}" title="Hapus Tahun ${yr} beserta seluruh 12 bulannya dari tabel" style="background:rgba(239,68,68,0.12); color:#dc2626; border:1px solid rgba(239,68,68,0.3); border-radius:4px; width:18px; height:18px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; font-size:13px; line-height:1; font-weight:bold; padding:0; transition:all 0.15s ease;" onmouseover="this.style.background='#dc2626';this.style.color='#fff';" onmouseout="this.style.background='rgba(239,68,68,0.12)';this.style.color='#dc2626';">&times;</button>` : ''}
                        </div>
                    </th>`;
                });
                theadHtml += `</tr><tr>`;

                yearsList.forEach(yr => {
                    const colTheme = getMonthlyYearTheme(yr);
                    standardMonths.forEach(m => {
                        theadHtml += `<th style="width:80px; min-width:80px; padding:6px 3px; font-weight:800; font-size:11px; color:#1e293b; border-bottom:1.5px solid #cbd5e1; border-right:1px solid #e2e8f0; text-align:center; background:#f8fafc; position:sticky; top:36px; z-index:20;" title="${m} ${yr}">${m}</th>`;
                    });
                    theadHtml += `<th style="width:85px; min-width:85px; padding:6px 3px; font-weight:800; font-size:11px; color:#166534; border-bottom:1.5px solid #86efac; border-right:2px solid ${colTheme.border}; text-align:center; background:#dcfce7; position:sticky; top:36px; z-index:20;" title="Rata-rata Tahunan ${yr}">Tahunan</th>`;
                });
                theadHtml += `</tr>`;
                secThead.innerHTML = theadHtml;

                let tbodyHtml = '';
                defKeys.forEach(k => {
                    const item = defs[k];
                    if (!item) return;
                    const isTotal = (k === 'TOTAL');
                    const rowBg = isTotal ? '#f0fdf4' : '#ffffff';
                    const codeBg = isTotal ? '#dcfce7' : '#ffffff';
                    const rowWeight = isTotal ? '800' : '600';
                    const textColor = isTotal ? '#15803d' : '#0f172a';

                    tbodyHtml += `<tr style="background:${rowBg};">`;
                    tbodyHtml += `<td style="position:sticky; left:0; background:${codeBg}; z-index:10; padding:6px 4px; font-weight:800; font-size:11.5px; border-bottom:1px solid ${isTotal ? '#86efac' : '#e2e8f0'}; border-right:1px solid ${isTotal ? '#86efac' : '#e2e8f0'}; text-align:center; vertical-align:middle; width:50px; min-width:50px; max-width:50px;">
                        ${isTotal ? '<span class="badge success" style="font-size:9.5px; font-weight:800; padding:3px 6px; border-radius:5px;">TOTAL</span>' : `<span style="display:inline-block; min-width:28px; padding:2px 4px; font-size:11.5px; font-weight:800; border-radius:5px; background:#f1f5f9; color:#0369a1; border:1px solid #cbd5e1;">${escapeAttr(item.cat_label || k)}</span>`}
                    </td>`;

                    const safeName = escapeAttr(item.short_name || item.name || '');
                    tbodyHtml += `<td style="position:sticky; left:50px; background:${codeBg}; z-index:10; padding:6px 10px; font-weight:${rowWeight}; font-size:12px; border-bottom:1px solid ${isTotal ? '#86efac' : '#e2e8f0'}; border-right:2px solid #94a3b8; vertical-align:middle; width:280px; min-width:280px; max-width:320px;">
                        <span style="font-weight:${rowWeight}; font-size:12px; color:${textColor}; line-height:1.4;">${safeName}</span>
                    </td>`;

                    yearsList.forEach(yr => {
                        const colTheme = getMonthlyYearTheme(yr);
                        standardMonths.forEach(m => {
                            const existingVal = (mVals[item.code] && mVals[item.code][yr] && mVals[item.code][yr][m] !== undefined) ? mVals[item.code][yr][m] : '';
                            const formattedVal = (existingVal !== '' && existingVal !== null) ? formatRupiahNumber(existingVal) : '';

                            if (isTotal) {
                                tbodyHtml += `<td style="padding:4px 3px; border-bottom:1px solid #86efac; border-right:1px solid #86efac; text-align:right; vertical-align:middle; background:#f0fdf4;">
                                    <input type="text" readonly class="sec-monthly-total-auto is-rupiah-mask" data-code="TOTAL" data-year="${yr}" data-month="${m}" value="${formattedVal}" placeholder="0" style="width:100%; height:28px; padding:0 4px; font-weight:800; font-size:11.5px; border:1px solid #86efac; border-radius:3px; text-align:right; background:#dcfce7; color:#15803d; cursor:not-allowed;">
                                </td>`;
                            } else {
                                tbodyHtml += `<td style="padding:4px 3px; border-bottom:1px solid #e2e8f0; border-right:1px solid #e2e8f0; text-align:right; vertical-align:middle; background:${rowBg};">
                                    <input type="text" inputmode="decimal" class="sec-monthly-input is-rupiah-mask" data-code="${item.code}" data-year="${yr}" data-month="${m}" value="${formattedVal}" placeholder="-" style="width:100%; height:28px; padding:0 4px; font-weight:${rowWeight}; font-size:11.5px; border:1px solid #cbd5e1; border-radius:3px; text-align:right; background:#ffffff; color:${textColor};">
                                </td>`;
                            }
                        });

                        const existingAnn = (mVals[item.code] && mVals[item.code][yr] && mVals[item.code][yr]['Tahunan'] !== undefined) ? mVals[item.code][yr]['Tahunan'] : '';
                        const formattedAnn = (existingAnn !== '' && existingAnn !== null) ? formatRupiahNumber(existingAnn) : '';
                        tbodyHtml += `<td style="padding:4px 3px; border-bottom:1px solid ${isTotal ? '#86efac' : '#e2e8f0'}; border-right:2px solid ${colTheme.border}; text-align:right; vertical-align:middle; background:#f0fdf4;">
                            <input type="text" readonly class="${isTotal ? 'sec-monthly-total-auto' : 'sec-monthly-annual-auto'} is-rupiah-mask" data-code="${item.code}" data-year="${yr}" data-month="Tahunan" value="${formattedAnn}" placeholder="-" title="Rata-rata tahun ${yr}" style="width:100%; height:28px; padding:0 4px; font-weight:800; font-size:11.5px; border:1.5px solid #86efac; border-radius:3px; text-align:right; background:#dcfce7; color:#15803d; cursor:not-allowed;">
                        </td>`;
                    });

                    tbodyHtml += `</tr>`;
                });
                secTbody.innerHTML = tbodyHtml;

                calculateMonthlyGroupTotals();
                return;
            }
            const years = sectoralState.years;
            const defs = sectoralState.definitions;
            const vals = sectoralState.values;
            // pCode already declared above
            const isMonthly = (sectoralState.category_type === 'monthly' || pCode === 'PAR-001' || pCode === 'PAR-002');
            const isInv = (sectoralState.category_type === 'quarterly' || pCode.startsWith('INV-'));
            const isExp = (sectoralState.category_type === 'expenditure');
            const isTk07 = (pCode === 'TK-007');
            const isTk08 = (pCode === 'TK-008');
            const isTk10 = (pCode === 'TK-010');
            const isPar = pCode.startsWith('PAR-');
            const isPro = pCode.startsWith('PRO-');
            const isFis = pCode.startsWith('FIS-');

            let col1Title = 'Kategori';
            let col2Title = 'Lapangan Usaha';
            if (isMonthly) {
                col1Title = 'Bulan';
                col2Title = 'Nama Periode Bulan';
            } else if (isInv) {
                col1Title = 'Kuartal';
                col2Title = 'Rincian Periode Kuartal';
            } else if (isExp) {
                col1Title = 'Komponen';
                col2Title = 'Komponen Pengeluaran';
            } else if (isPar) {
                col1Title = 'Kategori';
                col2Title = 'Kategori Pariwisata';
            } else if (isPro) {
                col1Title = 'No / Wilayah';
                col2Title = 'Nama Wilayah Kecamatan';
            } else if (isFis) {
                col1Title = 'Komponen';
                col2Title = 'Komponen Keuangan Daerah';
            } else if (isTk07 || isTk08 || isTk10) {
                col1Title = 'Kategori';
                col2Title = 'Rincian Lapangan Usaha / Status';
            }

            const escapeAttr = function (str) {
                if (!str) return '';
                return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            };

            // Render Header
            let theadHtml = `
                <tr>
                    <th style="min-width:70px; width:90px; position:sticky; left:0; background:#f1f5f9; z-index:11; padding:9px 10px; font-weight:800; font-size:11.5px; color:#334155; border-bottom:1px solid #cbd5e1; border-right:1px solid #e2e8f0; text-align:center;">
                        <span>${col1Title}</span>
                    </th>
                    <th style="min-width:270px; max-width:340px; position:sticky; left:90px; background:#f1f5f9; z-index:11; padding:9px 12px; font-weight:800; font-size:11.5px; color:#334155; border-bottom:1px solid #cbd5e1; border-right:1.5px solid #cbd5e1; text-align:left;">
                        <span>${col2Title}</span>
                    </th>`;

            years.forEach(yr => {
                theadHtml += `
                    <th style="min-width:115px; padding:8px 8px; font-weight:800; font-size:12px; color:#1e293b; border-bottom:1px solid #cbd5e1; border-right:1px solid #e2e8f0; text-align:center; background:#f8fafc;">
                        <div style="display:flex; align-items:center; justify-content:center; gap:4px;">
                            <span>${yr}</span>
                            <button type="button" class="btn-sec-del-year" data-year="${yr}" title="Hapus Kolom Tahun ${yr}" style="background:none; border:none; color:#94a3b8; cursor:pointer; font-size:13px; line-height:1; padding:1px 3px; border-radius:3px;">&times;</button>
                        </div>
                    </th>`;
            });
            theadHtml += `</tr>`;
            secThead.innerHTML = theadHtml;

            // Render Body
            let tbodyHtml = '';
            const defKeys = Object.keys(defs);

            defKeys.forEach(k => {
                if (k === 'TM') return; // Tanpa Migas tidak ditampilkan
                if (k === 'TOTAL' && isPar) return; // PAR tidak memakai total
                const item = defs[k];
                if (!item) return;

                const isTotal = (k === 'TOTAL');
                const rowBg = isTotal ? '#f0fdf4' : '#ffffff';
                const codeBg = isTotal ? '#dcfce7' : '#ffffff';
                const rowWeight = isTotal ? '800' : '600';
                const textColor = isTotal ? '#15803d' : '#0f172a';

                tbodyHtml += `<tr style="background:${rowBg};">`;

                // Col 1: Kode / Kategori
                tbodyHtml += `
                    <td style="position:sticky; left:0; background:${codeBg}; z-index:9; padding:6px 6px; font-weight:800; font-size:11.5px; border-bottom:1px solid ${isTotal ? '#86efac' : '#e2e8f0'}; border-right:1px solid ${isTotal ? '#86efac' : '#e2e8f0'}; text-align:center; vertical-align:middle;">
                        ${isTotal
                        ? '<span class="badge success" style="font-size:9.5px; font-weight:800; padding:4px 8px; border-radius:6px; letter-spacing:0.04em;">TOTAL (OTOMATIS)</span>'
                        : (item.is_custom
                            ? `<input type="text" class="sec-cat-label-input" data-cat="${k}" value="${escapeAttr(item.cat_label || k)}" style="width:100%; min-width:44px; max-width:68px; height:32px; padding:0 4px; font-weight:800; font-size:11.5px; border:1px solid #cbd5e1; border-radius:5px; text-align:center; background:#ffffff; color:#0369a1; outline:none;" title="Ubah Kode/Label Kategori">`
                            : `<span style="display:inline-block; min-width:38px; padding:3px 7px; font-size:11.5px; font-weight:800; border-radius:5px; background:#f1f5f9; color:#0369a1; border:1px solid #cbd5e1;">${escapeAttr(item.cat_label || k)}</span>`
                        )
                    }
                    </td>`;

                // Col 2: Nama Lapangan Usaha / Bulan / Komponen
                const safeName = escapeAttr(item.short_name || item.name || '');
                tbodyHtml += `
                    <td style="position:sticky; left:90px; background:${codeBg}; z-index:9; padding:6px 10px; font-weight:${rowWeight}; font-size:12px; border-bottom:1px solid ${isTotal ? '#86efac' : '#e2e8f0'}; border-right:1.5px solid ${isTotal ? '#86efac' : '#cbd5e1'}; vertical-align:middle;">
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:6px;">
                            ${isTotal
                        ? `<span style="font-weight:800; font-size:12px; color:#15803d;">${safeName}</span>`
                        : (item.is_custom
                            ? `<input type="text" class="sec-name-input" data-cat="${k}" data-code="${item.code}" value="${safeName}" placeholder="Ketik nama rincian..." style="width:100%; min-width:240px; height:32px; padding:0 8px; font-weight:${rowWeight}; font-size:12px; border:1px solid #cbd5e1; border-radius:5px; background:#ffffff; color:${textColor}; outline:none;">`
                            : `<span style="font-weight:${rowWeight}; font-size:12px; color:${textColor}; line-height:1.4;">${safeName}</span>`
                        )
                    }
                            ${item.is_custom ? `<button type="button" class="btn-sec-del-cat-row icon-button small danger" data-cat="${k}" title="Hapus Baris Kategori Ini" style="width:26px; height:26px; min-width:26px; padding:0; display:flex; align-items:center; justify-content:center; color:#dc2626; background:#fee2e2; border:1px solid #fca5a5; border-radius:4px; cursor:pointer;">&times;</button>` : ''}
                        </div>
                    </td>`;

                // Year Input Columns
                years.forEach(yr => {
                    const existingVal = (vals[item.code] && vals[item.code][yr] !== undefined) ? vals[item.code][yr] : '';
                    const formattedExisting = (existingVal !== '' && existingVal !== null) ? formatRupiahNumber(existingVal) : '';
                    if (isTotal) {
                        tbodyHtml += `
                            <td style="padding:5px 6px; border-bottom:1px solid #86efac; border-right:1px solid #86efac; text-align:right; vertical-align:middle; background:#f0fdf4;">
                                <div style="position:relative; display:flex; align-items:center;">
                                    <input type="text" readonly class="sec-val-input total-auto-input is-rupiah-mask" data-cat="TOTAL" data-code="${item.code}" data-year="${yr}" value="${formattedExisting}" placeholder="0" title="Total otomatis terhitung dari akumulasi seluruh rincian" style="width:100%; height:32px; padding:0 24px 0 8px; font-weight:800; font-size:12.5px; border:1.5px solid #86efac; border-radius:5px; text-align:right; background:#dcfce7; color:#15803d; cursor:not-allowed;">
                                    <span style="position:absolute; right:7px; font-size:10px; color:#16a34a; font-weight:800; pointer-events:none;" title="Otomatis">âˆ‘</span>
                                </div>
                            </td>`;
                    } else {
                        tbodyHtml += `
                            <td style="padding:5px 6px; border-bottom:1px solid #e2e8f0; border-right:1px solid #e2e8f0; text-align:right; vertical-align:middle; background:${rowBg};">
                                <input type="text" inputmode="decimal" class="sec-val-input is-rupiah-mask" data-cat="${k}" data-code="${item.code}" data-year="${yr}" value="${formattedExisting}" placeholder="-" style="width:100%; height:32px; padding:0 8px; font-weight:${rowWeight}; font-size:12px; border:1px solid #cbd5e1; border-radius:5px; text-align:right; background:#ffffff; color:${textColor};">
                            </td>`;
                    }
                });

                tbodyHtml += `</tr>`;
            });

            secTbody.innerHTML = tbodyHtml;

            // Recalculate totals
            calculateSectoralTotals();
        }

        function loadSectoralMatrix() {
            const pCode = sectoralState.parent_code || 'EKO-003';
            const wil = sectoralState.kode_wilayah || '35.10';
            if (secMsgBox) { secMsgBox.innerHTML = `<span style="color:#0284c7;">Memuat data matriks ${pCode}...</span>`; }

            fetch(`${cleanSite}/database/get_sectoral_matrix?kode_wilayah=${encodeURIComponent(wil)}&parent_code=${encodeURIComponent(pCode)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success' && res.data) {
                        const isPar004 = (sectoralState.parent_code === 'PAR-004');
                        const isMonthly = (res.data.is_monthly_group === true || ['HRG-002', 'HRG-006'].includes(sectoralState.parent_code) || isPar004);
                        sectoralState.is_monthly_group = isMonthly;
                        if (isMonthly) {
                            const defaultYrs = isPar004 ? [2023, 2024, 2025, 2026] : [2024, 2025, 2026];
                            const resYears = (res.data.years && Array.isArray(res.data.years) && res.data.years.length > 0) ? res.data.years : defaultYrs;
                            sectoralState.years = resYears;
                        } else {
                            sectoralState.years = res.data.years || [2018, 2019, 2020, 2021, 2022, 2023, 2024, 2025];
                        }
                        sectoralState.definitions = res.data.definitions || {};
                        sectoralState.values = res.data.values || {};
                        sectoralState.kode_wilayah = res.data.kode_wilayah || wil;
                        sectoralState.parent_code = res.data.parent_code || pCode;
                        sectoralState.unit = res.data.unit || 'Miliar rupiah';
                        sectoralState.category_type = res.data.category_type || 'sectoral';
                        sectoralState.can_auto_sum = (res.data.can_auto_sum !== false);
                        sectoralState.months = res.data.months || [];
                        sectoralState.monthly_values = res.data.monthly_values || {};
                        sectoralState.is_hrg005 = (res.data.is_hrg005 === true || pCode === 'HRG-005');
                        sectoralState.hrg005_structure = res.data.hrg005_structure || null;
                        sectoralState.daily_dates = res.data.daily_dates || [];
                        sectoralState.daily_values = res.data.daily_values || {};
                        if (sectoralState.is_monthly_group && !sectoralState.active_year) {
                            sectoralState.active_year = isPar004 ? 2023 : 2024;
                        }

                        // Update header badges & labels
                        const unitBadge = document.getElementById('sec-modal-unit-badge');
                        if (unitBadge) unitBadge.textContent = sectoralState.unit;

                        const badgeEl = document.getElementById('sec-modal-badge');
                        const titleEl = document.getElementById('sec-modal-title');
                        const descEl = document.getElementById('sec-modal-desc');
                        const helpEl = document.getElementById('sec-modal-help-text');
                        const catSwitcher = document.getElementById('sec-select-indicator-category');
                        if (catSwitcher && catSwitcher.value !== sectoralState.parent_code) {
                            catSwitcher.value = sectoralState.parent_code;
                        }

                        const btnAddCat = document.getElementById('btn-sec-add-cat-row');
                        const btnPrevYr = document.getElementById('btn-sec-add-prev-year');
                        const btnNextYr = document.getElementById('btn-sec-add-next-year');

                        if (btnAddCat) btnAddCat.style.display = 'none';
                        if (btnPrevYr) {
                            btnPrevYr.style.display = 'inline-flex';
                            btnPrevYr.title = sectoralState.is_monthly_group 
                                ? 'Tambah kolom 1 tahun sebelumnya (lengkap dengan 12 bulan & tahunan)' 
                                : 'Tambah kolom tahun sebelumnya';
                        }
                        if (btnNextYr) {
                            btnNextYr.style.display = 'inline-flex';
                            btnNextYr.title = sectoralState.is_monthly_group 
                                ? 'Tambah kolom 1 tahun sesudahnya (lengkap dengan 12 bulan & tahunan)' 
                                : 'Tambah kolom tahun sesudahnya';
                        }

                        const isInv = (sectoralState.category_type === 'quarterly' || (pCode && pCode.startsWith('INV-')));
                        const isExp = (sectoralState.category_type === 'expenditure');
                        const isTk07 = (pCode === 'TK-007');
                        const isTk08 = (pCode === 'TK-008');
                        const isTk10 = (pCode === 'TK-010');
                        const isParMonthly = (sectoralState.category_type === 'monthly' || pCode === 'PAR-001' || pCode === 'PAR-002');
                        const isPar = (pCode && pCode.startsWith('PAR-'));
                        const isPro = (pCode && pCode.startsWith('PRO-'));
                        const isFis = (pCode && pCode.startsWith('FIS-'));
                        const isHrg = (pCode && pCode.startsWith('HRG-'));

                        if (badgeEl) {
                            if (pCode === 'PAR-004') badgeEl.textContent = 'PARIWISATA â€¢ DATA BULANAN TPK HOTEL BPS / DISBUDPAR';
                            else if (isHrg) badgeEl.textContent = 'HARGA & INFLASI â€¢ DATA RESMI BPS / DISKOPERINDAG';
                            else if (isParMonthly) badgeEl.textContent = 'PARIWISATA â€¢ DATA BULANAN BPS / DISBUDPAR';
                            else if (isPar) badgeEl.textContent = 'PARIWISATA â€¢ DATA AKOMODASI & HOTEL';
                            else if (isPro) badgeEl.textContent = 'PRODUKSI SEKTORAL â€¢ 25 KECAMATAN BANYUWANGI';
                            else if (isFis) badgeEl.textContent = 'FISKAL & KEUANGAN â€¢ PENDAPATAN & TRANSFER DAERAH';
                            else if (isInv) badgeEl.textContent = 'KUARTAL INVESTASI â€¢ BKPM / DPMPTSP';
                            else if (isExp) badgeEl.textContent = 'PENGELUARAN PDRB â€¢ STANDAR BPS';
                            else if (isTk07 || isTk08 || isTk10) badgeEl.textContent = 'KETENAGAKERJAAN â€¢ SURVEI SAKERNAS BPS';
                            else badgeEl.textContent = 'SEKTORAL PDRB â€¢ STANDAR BPS';
                        }
                        if (titleEl) {
                            let tText = 'Input & Kelola PDRB Sektoral (17 Lapangan Usaha)';
                            if (pCode === 'HRG-001') tText = 'Input & Kelola IHK Umum (12 Bulan)';
                            else if (pCode === 'HRG-002') tText = 'Input & Kelola IHK Kelompok Pengeluaran (11 Kelompok)';
                            else if (pCode === 'HRG-003') tText = 'Input & Kelola Inflasi Bulanan / mtm (12 Bulan)';
                            else if (pCode === 'HRG-004') tText = 'Input & Kelola Inflasi Tahunan / yoy (12 Bulan)';
                            else if (pCode === 'HRG-005') tText = 'Input & Kelola Harga Eceran Komoditas Pangan Strategis (20 Kelompok Bahan Pokok)';
                            else if (pCode === 'HRG-006') tText = 'Input & Kelola Bobot Keranjang IHK (11 Kelompok)';
                            else if (pCode === 'PAR-001') tText = 'Input & Kelola Kunjungan Wisatawan Mancanegara (12 Bulan)';
                            else if (pCode === 'PAR-002') tText = 'Input & Kelola Kunjungan Wisatawan Nusantara (12 Bulan)';
                            else if (pCode === 'PAR-003') tText = 'Input & Kelola Tamu Hotel (Domestik & Mancanegara)';
                            else if (pCode === 'PAR-004') tText = 'Input & Kelola Tingkat Penghunian Kamar / TPK (Bintang, Non Bintang & 12 Bulan)';
                            else if (pCode === 'PAR-005') tText = 'Input & Kelola Jumlah Kamar Hotel (Bintang & Non Bintang)';
                            else if (pCode === 'PAR-006') tText = 'Input & Kelola Usaha Hotel & Restoran (Bintang, Non Bintang, Restoran)';
                            else if (pCode === 'PRO-001') tText = 'Input & Kelola Produksi Pertanian (25 Kecamatan)';
                            else if (pCode === 'PRO-002') tText = 'Input & Kelola Luas Panen Pertanian (25 Kecamatan)';
                            else if (pCode === 'PRO-004') tText = 'Input & Kelola Produksi Perikanan (25 Kecamatan)';
                            else if (pCode === 'FIS-001') tText = 'Input & Kelola Pendapatan Asli Daerah / PAD (4 Komponen)';
                            else if (pCode === 'FIS-002') tText = 'Input & Kelola Pendapatan Transfer Pemerintah (2 Komponen)';
                            else if (pCode === 'FIS-003') tText = 'Input & Kelola Belanja Daerah Menurut Komponen';
                            else if (pCode === 'INV-001') tText = 'Input & Kelola Realisasi Investasi Total PMA & PMDN (4 Kuartal)';
                            else if (pCode === 'INV-002') tText = 'Input & Kelola Realisasi Investasi PMA (4 Kuartal)';
                            else if (pCode === 'INV-003') tText = 'Input & Kelola Realisasi Investasi PMDN (4 Kuartal)';
                            else if (pCode === 'EKO-004') tText = 'Input & Kelola PDRB ADHK Sektoral (17 Lapangan Usaha)';
                            else if (pCode === 'EKO-006') tText = 'Input & Kelola Laju Pertumbuhan PDRB Sektoral (17 Lapangan Usaha)';
                            else if (pCode === 'EKO-007') tText = 'Input & Kelola Distribusi / Share Sektoral (17 Lapangan Usaha)';
                            else if (pCode === 'EKO-015') tText = 'Input & Kelola PDRB Pengeluaran ADHB (6 Komponen)';
                            else if (pCode === 'EKO-016') tText = 'Input & Kelola PDRB Pengeluaran ADHK (6 Komponen)';
                            else if (pCode === 'TK-007') tText = 'Input & Kelola Penduduk Bekerja (3 Sektor & 6 Status Pekerjaan)';
                            else if (pCode === 'TK-008') tText = 'Input & Kelola Pekerja Formal dan Informal (2 Kategori)';
                            else if (pCode === 'TK-010') tText = 'Input & Kelola Setengah Pengangguran (Jumlah Orang & Tingkat %)';
                            titleEl.textContent = tText;
                        }
                        if (descEl) {
                            if (pCode === 'HRG-001' || pCode === 'HRG-003' || pCode === 'HRG-004') {
                                descEl.textContent = 'Kelola rincian data bulanan inflasi dan IHK (Januari s.d. Desember) per tahun dalam satu tabel matriks spreadsheet.';
                            } else if (pCode === 'HRG-002' || pCode === 'HRG-006') {
                                descEl.textContent = 'Kelola rincian data IHK dan bobot menurut 11 kelompok pengeluaran standar BPS per tahun.';
                            } else if (pCode === 'HRG-005') {
                                descEl.textContent = 'Kelola harga eceran harian (2026-11-01 s.d. 2026-11-30) dan ringkasan tahunan untuk 20 kelompok bahan pokok (48 komoditas) sesuai konsep Excel.';
                            } else if (pCode === 'PAR-004') {
                                descEl.textContent = 'Kelola rincian data bulanan (Januari s.d. Desember) Tingkat Penghunian Kamar (TPK) Hotel Bintang, Non Bintang, dan Seluruh Jenis Hotel per tahun.';
                            } else if (isParMonthly) {
                                descEl.textContent = 'Kelola rincian data bulanan (Januari s.d. Desember) per tahun dalam satu tabel matriks spreadsheet.';
                            } else if (isPar) {
                                descEl.textContent = 'Kelola rincian data kategori perhotelan dan akomodasi pariwisata per tahun dalam satu tabel matriks spreadsheet.';
                            } else if (isPro) {
                                descEl.textContent = 'Kelola rincian data seluruh 25 wilayah kecamatan di Kabupaten Banyuwangi per tahun dalam satu tabel matriks spreadsheet. Nilai total kabupaten otomatis terhitung.';
                            } else if (pCode === 'FIS-003') {
                                descEl.textContent = 'Kelola rincian data komponen belanja daerah per tahun dalam satu tabel matriks spreadsheet.';
                            } else if (isFis) {
                                descEl.textContent = 'Kelola rincian data komponen pendapatan daerah dan transfer pemerintah per tahun dalam satu tabel matriks spreadsheet.';
                            } else if (isInv) {
                                descEl.textContent = 'Kelola nilai realisasi seluruh 4 kuartal (Q1 s.d. Q4) per tahun dalam satu tabel matriks spreadsheet. Nilai total tahunan otomatis terhitung.';
                            } else if (isExp) {
                                descEl.textContent = 'Kelola nilai realisasi seluruh komponen pengeluaran PDRB per tahun dalam satu tabel matriks spreadsheet.';
                            } else if (pCode === 'TK-007') {
                                descEl.textContent = 'Kelola persentase penduduk bekerja menurut 3 sektor lapangan usaha utama dan 6 status pekerjaan utama per tahun.';
                            } else if (pCode === 'TK-008') {
                                descEl.textContent = 'Kelola persentase tenaga kerja formal dan informal per tahun. Nilai total (100%) otomatis terhitung.';
                            } else if (pCode === 'TK-010') {
                                descEl.textContent = 'Kelola jumlah setengah pengangguran (orang) dan tingkat setengah pengangguran (%) per tahun.';
                            } else {
                                descEl.textContent = 'Kelola nilai realisasi seluruh 17 sektor ekonomi per tahun sekaligus dalam satu tabel matriks (Kategori A s.d. U).';
                            }
                        }
                        if (helpEl) {
                            if (pCode === 'PAR-004') {
                                helpEl.style.display = 'inline-flex';
                                helpEl.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Rata-rata tahunan otomatis terhitung real-time`;
                            } else if (sectoralState.can_auto_sum) {
                                helpEl.style.display = 'inline-flex';
                                helpEl.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Total otomatis terhitung real-time saat Anda mengetik nilai`;
                            } else if (isHrg) {
                                helpEl.style.display = 'inline-flex';
                                helpEl.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg> Data harga dan inflasi resmi BPS / Pasar Daerah`;
                            } else if (isParMonthly) {
                                helpEl.style.display = 'inline-flex';
                                helpEl.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg> Data bulanan (Januari s.d. Desember) per tahun`;
                            } else if (isPar || isPro || isFis) {
                                helpEl.style.display = 'inline-flex';
                                helpEl.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg> Rincian data kategori per tahun`;
                            } else {
                                helpEl.style.display = 'inline-flex';
                                helpEl.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg> Pertumbuhan per sektor diinput manual dalam satuan persen (%)`;
                            }
                        }

                        renderSectoralTable();
                        if (secMsgBox) { secMsgBox.innerHTML = `<span style="color:#16a34a;">Data matriks ${sectoralState.parent_code} siap dikelola.</span>`; }
                    } else {
                        if (secMsgBox) { secMsgBox.innerHTML = '<span style="color:#dc2626;">Gagal memuat data matriks.</span>'; }
                    }
                })
                .catch(() => {
                    if (secMsgBox) { secMsgBox.innerHTML = '<span style="color:#dc2626;">Koneksi gagal saat mengambil data.</span>'; }
                });
        }

        // Switcher dropdown pilihan kategori di toolbar modal spreadsheet
        const catSwitcher = document.getElementById('sec-select-indicator-category');
        if (catSwitcher) {
            catSwitcher.addEventListener('change', function () {
                sectoralState.parent_code = this.value;
                const isPar004 = (this.value === 'PAR-004');
                const isMonthly = ['HRG-002', 'HRG-006', 'PAR-004'].includes(this.value);
                sectoralState.is_monthly_group = isMonthly;
                if (isMonthly) {
                    sectoralState.years = isPar004 ? [2023, 2024, 2025, 2026] : [2024, 2025, 2026];
                }
                const pInput = document.getElementById('sec-form-parent-code');
                if (pInput) pInput.value = this.value;
                loadSectoralMatrix();
            });
        }

        function openSectoralModal(parentCode) {
            if (!modalSectoral) return;
            if (parentCode) {
                sectoralState.parent_code = parentCode;
                const isPar004 = (parentCode === 'PAR-004');
                const isMonthly = ['HRG-002', 'HRG-006', 'PAR-004'].includes(parentCode);
                sectoralState.is_monthly_group = isMonthly;
                if (isMonthly) {
                    sectoralState.years = isPar004 ? [2023, 2024, 2025, 2026] : [2024, 2025, 2026];
                }
                const catSwitcher = document.getElementById('sec-select-indicator-category');
                if (catSwitcher) catSwitcher.value = parentCode;
                const pInput = document.getElementById('sec-form-parent-code');
                if (pInput) pInput.value = parentCode;
            }
            modalSectoral.style.display = 'flex';
            loadSectoralMatrix();
        }

        if (btnOpenSectoral) btnOpenSectoral.addEventListener('click', () => openSectoralModal('EKO-003'));

        // Open from row shortcut button
        document.addEventListener('click', e => {
            const btn = e.target.closest('.btn-open-sectoral-from-row, .btn-open-sectoral-direct');
            if (btn) {
                e.preventDefault();
                const pCode = btn.getAttribute('data-parent') || 'EKO-003';
                openSectoralModal(pCode);
            }
        });


        // Live calculation and state preservation on input change
        if (secTbody) {
            secTbody.addEventListener('input', e => {
                if (e.target.classList.contains('sec-val-input')) {
                    const code = e.target.getAttribute('data-code');
                    const yr = parseInt(e.target.getAttribute('data-year'), 10);
                    if (code && yr) {
                        if (!sectoralState.values[code]) sectoralState.values[code] = {};
                        sectoralState.values[code][yr] = e.target.value !== '' ? parseRupiahNumber(e.target.value) : '';
                    }
                    if (sectoralState.parent_code === 'HRG-005') {
                        calculateHrg005Totals(false);
                    } else {
                        calculateSectoralTotals();
                    }
                } else if (e.target.classList.contains('sec-daily-input')) {
                    const code = e.target.getAttribute('data-code');
                    const dStr = e.target.getAttribute('data-date');
                    const rawVal = e.target.value.trim();
                    const numVal = (rawVal !== '') ? parseRupiahNumber(rawVal) : '';
                    if (code && dStr) {
                        if (!sectoralState.daily_values) sectoralState.daily_values = {};
                        if (!sectoralState.daily_values[code]) sectoralState.daily_values[code] = {};
                        sectoralState.daily_values[code][dStr] = numVal;
                    }
                    calculateHrg005Totals(true);
                } else if (e.target.classList.contains('sec-monthly-input')) {
                    const code = e.target.getAttribute('data-code');
                    const yr = parseInt(e.target.getAttribute('data-year'), 10);
                    const month = e.target.getAttribute('data-month');
                    const rawVal = e.target.value.trim();
                    const numVal = (rawVal !== '') ? parseRupiahNumber(rawVal) : '';

                    if (!sectoralState.monthly_values[code]) sectoralState.monthly_values[code] = {};
                    if (!sectoralState.monthly_values[code][yr]) sectoralState.monthly_values[code][yr] = {};
                    sectoralState.monthly_values[code][yr][month] = numVal;

                    if (sectoralState.parent_code === 'PAR-004') {
                        calculatePar004Totals();
                    } else {
                        calculateMonthlyGroupTotals();
                    }
                }
            });

            secTbody.addEventListener('click', e => {
                const delCatBtn = e.target.closest('.btn-sec-del-cat-row');
                if (delCatBtn) {
                    e.preventDefault();
                    const cat = delCatBtn.getAttribute('data-cat');
                    if (cat && sectoralState.definitions[cat]) {
                        AppSwal.confirm({
                            title: 'Hapus Baris Kategori?',
                            html: `Hapus baris kategori <strong>"${cat}"</strong> dari tabel matriks?`,
                            confirmButtonText: 'Ya', cancelButtonText: 'Tidak',
                            icon: 'warning'
                        }).then(result => {
                            if (result.isConfirmed) {
                                delete sectoralState.definitions[cat];
                                if (sectoralState.values) {
                                    delete sectoralState.values[cat];
                                }
                                renderSectoralTable();
                            }
                        });
                    }
                }
            });
        }

        // Tambah Baris Kategori Baru (Dapat Diubah)
        const btnSecAddCatRow = document.getElementById('btn-sec-add-cat-row');
        if (btnSecAddCatRow) {
            btnSecAddCatRow.addEventListener('click', () => {
                const parentCode = sectoralState.parent_code || 'EKO-003';
                const customCount = Object.keys(sectoralState.definitions).filter(k => sectoralState.definitions[k].is_custom).length + 1;
                const newKey = `CUSTOM_${Date.now()}`;
                const defaultLabel = `KUST_${customCount}`;
                const newCode = `${parentCode}.${defaultLabel}`;

                const newDefs = {};
                let inserted = false;
                Object.keys(sectoralState.definitions).forEach(k => {
                    if (k === 'TOTAL' && !inserted) {
                        newDefs[newKey] = {
                            code: newCode,
                            cat_label: '',
                            name: '',
                            short_name: '',
                            def: `Rincian Kategori Tambahan ${customCount}`,
                            is_custom: true
                        };
                        inserted = true;
                    }
                    newDefs[k] = sectoralState.definitions[k];
                });
                if (!inserted) {
                    newDefs[newKey] = {
                        code: newCode,
                        cat_label: '',
                        name: '',
                        short_name: '',
                        def: `Rincian Kategori Tambahan ${customCount}`,
                        is_custom: true
                    };
                }

                sectoralState.definitions = newDefs;
                renderSectoralTable();

                // Auto fokus ke input Kategori pada baris baru
                requestAnimationFrame(() => {
                    const newCatInput = secTbody.querySelector(`.sec-cat-label-input[data-cat="${newKey}"]`);
                    if (newCatInput) {
                        newCatInput.focus();
                    }
                });
            });
        }

        // Helper untuk sinkronkan nilai input di DOM saat ini ke sectoralState sebelum re-render
        function syncCurrentInputsToState() {
            if (sectoralState.is_monthly_group) {
                document.querySelectorAll('.sec-monthly-input').forEach(inp => {
                    const code = inp.getAttribute('data-code');
                    const yr = parseInt(inp.getAttribute('data-year'), 10);
                    const m = inp.getAttribute('data-month');
                    const v = inp.value !== '' ? parseRupiahNumber(inp.value) : '';
                    if (code && yr && m) {
                        if (!sectoralState.monthly_values) sectoralState.monthly_values = {};
                        if (!sectoralState.monthly_values[code]) sectoralState.monthly_values[code] = {};
                        if (!sectoralState.monthly_values[code][yr]) sectoralState.monthly_values[code][yr] = {};
                        sectoralState.monthly_values[code][yr][m] = v;
                    }
                });
            } else {
                document.querySelectorAll('.sec-val-input').forEach(inp => {
                    const cat = inp.getAttribute('data-cat');
                    const code = inp.getAttribute('data-code');
                    const yr = parseInt(inp.getAttribute('data-year'), 10);
                    const val = inp.value;
                    if ((cat || code) && yr) {
                        if (!sectoralState.values) sectoralState.values = {};
                        const targetKey = (sectoralState.parent_code === 'HRG-005' && code) ? code : cat;
                        if (!sectoralState.values[targetKey]) sectoralState.values[targetKey] = {};
                        sectoralState.values[targetKey][yr] = (val !== '' && val !== null) ? parseRupiahNumber(val) : '';
                    }
                });
                if (sectoralState.parent_code === 'HRG-005') {
                    document.querySelectorAll('.sec-daily-input').forEach(inp => {
                        const code = inp.getAttribute('data-code');
                        const dStr = inp.getAttribute('data-date');
                        const val = inp.value;
                        if (code && dStr) {
                            if (!sectoralState.daily_values) sectoralState.daily_values = {};
                            if (!sectoralState.daily_values[code]) sectoralState.daily_values[code] = {};
                            sectoralState.daily_values[code][dStr] = (val !== '' && val !== null) ? parseRupiahNumber(val) : '';
                        }
                    });
                }
            }
        }

        // Add Previous Year button (Adds column to the left)
        if (btnSecAddPrev) {
            btnSecAddPrev.addEventListener('click', () => {
                syncCurrentInputsToState();
                const isPar004 = (sectoralState.parent_code === 'PAR-004');
                const isMonthly = (sectoralState.is_monthly_group === true || ['HRG-002', 'HRG-006'].includes(sectoralState.parent_code) || isPar004);
                sectoralState.is_monthly_group = isMonthly;
                let currentYears = sectoralState.years || [];
                if (isMonthly && (currentYears.length === 0 || (currentYears.includes(2018) && currentYears.length > 5))) {
                    currentYears = isPar004 ? [2023, 2024, 2025, 2026] : [2024, 2025, 2026];
                }
                const minYear = currentYears.length > 0 ? Math.min(...currentYears) : (isPar004 ? 2023 : 2024);
                const newYear = minYear - 1;
                if (!currentYears.includes(newYear)) {
                    currentYears.unshift(newYear);
                    currentYears.sort((a, b) => a - b);
                    sectoralState.years = currentYears;
                    renderSectoralTable();
                }
            });
        }

        // Add Next Year button (Adds column to the right)
        if (btnSecAddNext) {
            btnSecAddNext.addEventListener('click', () => {
                syncCurrentInputsToState();
                const isPar004 = (sectoralState.parent_code === 'PAR-004');
                const isMonthly = (sectoralState.is_monthly_group === true || ['HRG-002', 'HRG-006'].includes(sectoralState.parent_code) || isPar004);
                sectoralState.is_monthly_group = isMonthly;
                let currentYears = sectoralState.years || [];
                if (isMonthly && (currentYears.length === 0 || (currentYears.includes(2018) && currentYears.length > 5))) {
                    currentYears = isPar004 ? [2023, 2024, 2025, 2026] : [2024, 2025, 2026];
                }
                const maxYear = currentYears.length > 0 ? Math.max(...currentYears) : 2026;
                const newYear = maxYear + 1;
                if (!currentYears.includes(newYear)) {
                    currentYears.push(newYear);
                    currentYears.sort((a, b) => a - b);
                    sectoralState.years = currentYears;
                    renderSectoralTable();
                }
            });
        }

        // Delete Column Year button
        if (secThead) {
            secThead.addEventListener('click', e => {
                const delBtn = e.target.closest('.btn-sec-del-year');
                if (delBtn) {
                    const yr = parseInt(delBtn.getAttribute('data-year'), 10);
                    const yrHtml = sectoralState.is_monthly_group 
                        ? `Hapus tahun <strong>${yr}</strong> beserta seluruh 12 bulannya dari tabel matriks ini?` 
                        : `Hapus kolom tahun <strong>${yr}</strong> dari tabel matriks ini?`;
                    AppSwal.confirm({
                        title: 'Hapus Kolom Tahun?',
                        html: yrHtml,
                        confirmButtonText: 'Ya',
                        cancelButtonText: 'Tidak',
                        icon: 'warning'
                    }).then(result => {
                        if (result && result.isConfirmed) {
                            syncCurrentInputsToState();
                            sectoralState.years = sectoralState.years.filter(y => y !== yr);
                            if (sectoralState.is_monthly_group && sectoralState.monthly_values) {
                                Object.keys(sectoralState.monthly_values).forEach(code => {
                                    if (sectoralState.monthly_values[code] && sectoralState.monthly_values[code][yr]) {
                                        delete sectoralState.monthly_values[code][yr];
                                    }
                                });
                            }
                            renderSectoralTable();
                        }
                    });
                }
            });
        }

        // Save All Sectoral Data Button
        if (btnSaveSectoral) {
            btnSaveSectoral.addEventListener('click', () => {
                const matrix = {};
                const years = sectoralState.years;

                document.querySelectorAll('.sec-val-input').forEach(inp => {
                    const cat = inp.getAttribute('data-cat');
                    const yr = parseInt(inp.getAttribute('data-year'), 10);
                    const val = inp.value;
                    if (!matrix[cat]) matrix[cat] = {};
                    if (val !== '' && val !== null) {
                        matrix[cat][yr] = parseRupiahNumber(val);
                    }
                });

                if (secMsgBox) { secMsgBox.innerHTML = '<span style="color:#0284c7;">Menyimpan seluruh data matriks ke database...</span>'; }
                btnSaveSectoral.disabled = true;

                const formData = new FormData();
                formData.append('kode_wilayah', sectoralState.kode_wilayah);
                formData.append('parent_code', sectoralState.parent_code || 'EKO-003');
                years.forEach((yr, idx) => {
                    formData.append(`years[${idx}]`, yr);
                });

                // Kumpulkan nama Lapangan Usaha dan label Kategori
                const names = {};
                const catLabels = {};
                if (sectoralState.definitions) {
                    Object.keys(sectoralState.definitions).forEach(cat => {
                        const def = sectoralState.definitions[cat];
                        if (def) {
                            names[cat] = def.name || def.short_name || '';
                            catLabels[cat] = def.cat_label || cat;
                        }
                    });
                }
                document.querySelectorAll('.sec-name-input').forEach(inp => {
                    const cat = inp.getAttribute('data-cat');
                    if (cat && inp.value.trim() !== '') names[cat] = inp.value.trim();
                });
                document.querySelectorAll('.sec-cat-label-input').forEach(inp => {
                    const cat = inp.getAttribute('data-cat');
                    if (cat && inp.value.trim() !== '') catLabels[cat] = inp.value.trim();
                });

                Object.keys(names).forEach(cat => {
                    formData.append(`names[${cat}]`, names[cat]);
                });
                Object.keys(catLabels).forEach(cat => {
                    formData.append(`cat_labels[${cat}]`, catLabels[cat]);
                });

                Object.keys(matrix).forEach(cat => {
                    Object.keys(matrix[cat]).forEach(yr => {
                        formData.append(`matrix[${cat}][${yr}]`, matrix[cat][yr]);
                    });
                });

                if (sectoralState.is_monthly_group) {
                    document.querySelectorAll('.sec-monthly-input').forEach(inp => {
                        const code = inp.getAttribute('data-code');
                        const yr = inp.getAttribute('data-year');
                        const m = inp.getAttribute('data-month');
                        const v = inp.value !== '' ? parseRupiahNumber(inp.value) : '';
                        if (code && yr && m) {
                            if (!sectoralState.monthly_values[code]) sectoralState.monthly_values[code] = {};
                            if (!sectoralState.monthly_values[code][yr]) sectoralState.monthly_values[code][yr] = {};
                            sectoralState.monthly_values[code][yr][m] = v;
                        }
                    });

                    document.querySelectorAll('.sec-monthly-annual-auto, .sec-monthly-total-auto').forEach(inp => {
                        const code = inp.getAttribute('data-code');
                        const yr = inp.getAttribute('data-year');
                        const m = inp.getAttribute('data-month') || 'Tahunan';
                        const v = inp.value !== '' ? parseRupiahNumber(inp.value) : '';
                        if (code && yr && m) {
                            if (!sectoralState.monthly_values[code]) sectoralState.monthly_values[code] = {};
                            if (!sectoralState.monthly_values[code][yr]) sectoralState.monthly_values[code][yr] = {};
                            sectoralState.monthly_values[code][yr][m] = v;
                        }
                    });

                    if (sectoralState.monthly_values) {
                        const mVals = sectoralState.monthly_values;
                        Object.keys(mVals).forEach(code => {
                            if (code === 'TOTAL') return;
                            Object.keys(mVals[code]).forEach(yr => {
                                Object.keys(mVals[code][yr]).forEach(m => {
                                    const v = mVals[code][yr][m];
                                    if (v !== '' && v !== null && v !== undefined) {
                                        formData.append(`monthly_matrix[${code}][${yr}][${m}]`, v);
                                    }
                                });
                            });
                        });
                    }
                }

                if (sectoralState.parent_code === 'HRG-005') {
                    document.querySelectorAll('.sec-daily-input').forEach(inp => {
                        const code = inp.getAttribute('data-code');
                        const dStr = inp.getAttribute('data-date');
                        const v = inp.value !== '' ? parseRupiahNumber(inp.value) : '';
                        if (code && dStr) {
                            if (!sectoralState.daily_values) sectoralState.daily_values = {};
                            if (!sectoralState.daily_values[code]) sectoralState.daily_values[code] = {};
                            sectoralState.daily_values[code][dStr] = v;
                        }
                    });
                    if (sectoralState.daily_values) {
                        const dVals = sectoralState.daily_values;
                        Object.keys(dVals).forEach(code => {
                            if (code === 'TOTAL') return;
                            Object.keys(dVals[code]).forEach(dStr => {
                                const v = dVals[code][dStr];
                                if (v !== '' && v !== null && v !== undefined) {
                                    formData.append(`daily_matrix[${code}][${dStr}]`, v);
                                }
                            });
                        });
                    }
                }

                fetch(`${cleanSite}/database/save_sectoral_matrix`, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                })
                    .then(r => r.json())
                    .then(data => {
                        btnSaveSectoral.disabled = false;
                        if (data.status === 'success') {
                            if (secMsgBox) { secMsgBox.innerHTML = `<span style="color:#16a34a;">${data.message || 'Berhasil disimpan!'}</span>`; }
                            setTimeout(() => {
                                closeSectoralModal();
                                location.reload();
                            }, 800);
                        } else {
                            if (secMsgBox) { secMsgBox.innerHTML = `<span style="color:#dc2626;">${data.message || 'Gagal menyimpan data.'}</span>`; }
                        }
                    })
                    .catch(() => {
                        btnSaveSectoral.disabled = false;
                        if (secMsgBox) { secMsgBox.innerHTML = '<span style="color:#dc2626;">Koneksi gagal saat menyimpan data.</span>'; }
                    });
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
        const filterCluster = document.getElementById('validation-filter-cluster');
        const filterYear = document.getElementById('validation-filter-year');

        function reloadValidationFiltered() {
            const q = searchInput ? encodeURIComponent(searchInput.value) : '';
            const st = filterStatus ? encodeURIComponent(filterStatus.value) : '';
            const cl = filterCluster && filterCluster.value ? encodeURIComponent(filterCluster.value) : '';
            const yr = filterYear && filterYear.value ? encodeURIComponent(filterYear.value) : '';
            const cat = window._activeCategoryFilter ? encodeURIComponent(window._activeCategoryFilter) : '';
            window.location.href = `${cleanSite}/validation?q=${q}&status=${st}${cl ? '&cluster=' + cl : ''}${yr ? '&year=' + yr : ''}${cat ? '&category=' + cat : ''}`;
        }

        if (searchInput) {
            searchInput.addEventListener('keydown', e => {
                if (e.key === 'Enter') reloadValidationFiltered();
            });
        }
        if (filterStatus) filterStatus.addEventListener('change', reloadValidationFiltered);
        if (filterCluster) filterCluster.addEventListener('change', reloadValidationFiltered);
        if (filterYear) filterYear.addEventListener('change', reloadValidationFiltered);

        // Toggle Rincian Sub-Indikator saat Baris Indikator di Halaman Validasi Diklik
        const valTableBody = document.getElementById('validation-table-body');
        if (valTableBody) {
            valTableBody.addEventListener('click', function (e) {
                const parentRow = e.target.closest('tr.matrix-row.has-subcategories, tr[data-has-sub="true"]');
                if (parentRow) {
                    // Jangan toggle jika user mengklik tombol centang/silang/setujui, link, input, atau kolom aksi paling kanan
                    if (!e.target.closest('button:not(.btn-toggle-subcategories), a, input, select, .matrix-sticky-col-last')) {
                        const pCode = parentRow.getAttribute('data-code');
                        if (pCode && typeof window.toggleIndicatorSubRows === 'function') {
                            window.toggleIndicatorSubRows(pCode, e);
                        }
                    }
                }
            });
        }

        // 1. Approve (Centang / ctg) - Langsung setujui tanpa popup alert / confirm
        document.querySelectorAll('.btn-verify-record').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
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

        // 1b. Approve Seluruh Tahun (Setujui Semua) untuk satu Indikator
        document.querySelectorAll('.btn-verify-series').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const code = this.getAttribute('data-code');
                const indicator = this.getAttribute('data-indicator') || code;
                if (!code) return;

                AppSwal.confirm({
                    title: 'Setujui Seluruh Rekaman?',
                    html: `Apakah Anda yakin ingin menyetujui dan memvalidasi seluruh data tahunan untuk indikator <strong>"${indicator}"</strong> (${code})?`,
                    confirmButtonText: 'Ya', cancelButtonText: 'Tidak',
                    icon: 'question'
                }).then(result => {
                    if (result.isConfirmed) {
                        const self = this;
                        self.disabled = true;
                        self.style.opacity = '0.6';

                        const fd = new FormData();
                        fd.append('code', code);
                        fd.append('status', 'Tervalidasi');

                        fetch(`${cleanSite}/api/update_series_status`, { method: 'POST', body: fd })
                            .then(res => res.json())
                            .then(res => {
                                if (res.status === 'success') {
                                    location.reload();
                                } else {
                                    self.disabled = false;
                                    self.style.opacity = '1';
                                    AppSwal.error('Gagal Memvalidasi', res.message || 'Gagal menyetujui seluruh data indikator.');
                                }
                            })
                            .catch(err => {
                                console.error(err);
                                self.disabled = false;
                                self.style.opacity = '1';
                            });
                    }
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
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
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

        function updateAdminEwsSummaryPills() {
            const tbody = document.getElementById('threshold-admin-rows');
            if (!tbody) return;
            let k = 0, w = 0, n = 0;
            tbody.querySelectorAll('tr.threshold-admin-row:not(.matrix-sub-row)').forEach(row => {
                if (row.style.display === 'none') return;
                const badge = row.querySelector('.ews-status-pill, [id^="status-badge-"]');
                const txt = badge ? badge.textContent.trim() : '';
                if (txt === 'Kritis') k++;
                else if (txt === 'Waspada') w++;
                else if (txt === 'Normal') n++;
            });
            const kEl = document.querySelector('#admin-ews-kritis-pill strong');
            const wEl = document.querySelector('#admin-ews-waspada-pill strong');
            const nEl = document.querySelector('#admin-ews-normal-pill strong');
            if (kEl) kEl.textContent = k;
            if (wEl) wEl.textContent = w;
            if (nEl) nEl.textContent = n;
        }
        window._refreshEwsAdminSummary = updateAdminEwsSummaryPills;
        updateAdminEwsSummaryPills();

        // Live calculation of Status Risiko based on real value vs threshold and direction
        function updateRowStatus(row) {
            const code = row.getAttribute('data-code');
            const thInput = row.querySelector('.threshold-input');
            const valInput = row.querySelector('.value-input');
            const dirSelect = row.querySelector('.direction-input');
            const badge = document.getElementById(`status-badge-${code}`);

            if (!thInput || !valInput || !dirSelect || !badge) return;

            const val = typeof parseRupiahNumber === 'function' ? parseRupiahNumber(valInput.value) : parseFloat(valInput.value);
            const th = typeof parseRupiahNumber === 'function' ? parseRupiahNumber(thInput.value) : parseFloat(thInput.value);
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

            const isSub = row.classList.contains('matrix-sub-row');
            const badgeCls = ((st === 'Kritis') ? 'badge danger' : ((st === 'Waspada') ? 'badge warning' : 'badge success')) + (isSub ? ' ews-status-pill is-sub' : ' ews-status-pill');
            badge.className = badgeCls;
            badge.textContent = st;
            updateAdminEwsSummaryPills();
        }

        // Toggle Sub-Indikator Sektoral saat Baris / Tombol Indikator di Manajemen EWS Diklik
        const thTableBody = document.getElementById('threshold-admin-rows');
        if (thTableBody) {
            thTableBody.addEventListener('click', function (e) {
                const toggleBtn = e.target.closest('.btn-toggle-subcategories');
                if (toggleBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const pCode = toggleBtn.getAttribute('data-parent') || (toggleBtn.closest('[data-code]') ? toggleBtn.closest('[data-code]').getAttribute('data-code') : '');
                    if (pCode && typeof window.toggleIndicatorSubRows === 'function') {
                        window.toggleIndicatorSubRows(pCode, e);
                    }
                    return;
                }
                if (e.target.closest('input, select, a, button:not(.btn-toggle-subcategories)')) return;
                const row = e.target.closest('tr.threshold-admin-row:not(.matrix-sub-row)');
                if (row && row.getAttribute('data-has-sub') === 'true') {
                    const code = row.getAttribute('data-code');
                    if (code && typeof window.toggleIndicatorSubRows === 'function') {
                        toggleIndicatorSubRows(code, e);
                    }
                }
            });
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
                    const th = typeof parseRupiahNumber === 'function' ? parseRupiahNumber(input.value) : parseFloat(input.value);
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
                                    const parentTr = badge.closest('tr');
                                    const isSub = parentTr && parentTr.classList.contains('matrix-sub-row');
                                    badge.className = ((st === 'Kritis') ? 'badge danger' : ((st === 'Waspada') ? 'badge warning' : 'badge success')) + (isSub ? ' ews-status-pill is-sub' : ' ews-status-pill');
                                    badge.textContent = st;
                                }
                                updateAdminEwsSummaryPills();
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
                if (document.getElementById('edit-user-wilayah')) {
                    document.getElementById('edit-user-wilayah').value = item.kode_wilayah || '35.10';
                }
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
                        if (tr.id === 'catalog-filter-empty-row' || tr.style.display === 'none') return;
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

        // Cluster & Search Filter logic for Catalog Indikator (Dropdown Only)
        const catalogClusterSelect = document.getElementById('catalog-filter-cluster-select');
        const catalogSearchInput = document.getElementById('catalog-search-input');
        const catalogVisibleCount = document.getElementById('catalog-visible-count');
        const catalogEmptyRow = document.getElementById('catalog-filter-empty-row');
        const catalogTableRows = document.querySelectorAll('.result-table tbody tr:not(#catalog-filter-empty-row)');

        let currentCatalogCluster = 'all';
        if (catalogClusterSelect && catalogClusterSelect.value && catalogClusterSelect.value !== 'all') {
            currentCatalogCluster = catalogClusterSelect.value;
        }

        function filterCatalogData(selectedCluster, searchQuery) {
            const targetCluster = (selectedCluster !== undefined) ? selectedCluster : currentCatalogCluster;
            currentCatalogCluster = targetCluster;
            const q = (searchQuery !== undefined ? searchQuery : (catalogSearchInput ? catalogSearchInput.value : '')).toLowerCase().trim();

            let visibleCount = 0;
            catalogTableRows.forEach(tr => {
                const rowCluster = tr.getAttribute('data-cluster') || '';
                const clusterMatch = (targetCluster === 'all' || !targetCluster || rowCluster === targetCluster);

                let textMatch = true;
                if (q) {
                    const rowText = tr.textContent.toLowerCase();
                    textMatch = rowText.includes(q);
                }

                if (clusterMatch && textMatch) {
                    tr.style.display = '';
                    visibleCount++;
                } else {
                    tr.style.display = 'none';
                }
            });

            if (catalogEmptyRow) {
                catalogEmptyRow.style.display = (visibleCount === 0) ? '' : 'none';
            }

            if (catalogVisibleCount) {
                catalogVisibleCount.textContent = visibleCount;
            }

            // Sync select
            if (catalogClusterSelect && catalogClusterSelect.value !== targetCluster) {
                catalogClusterSelect.value = targetCluster;
            }
        }

        if (catalogClusterSelect) {
            catalogClusterSelect.addEventListener('change', function () {
                filterCatalogData(this.value);
            });
        }

        if (catalogSearchInput) {
            catalogSearchInput.addEventListener('input', function () {
                filterCatalogData();
            });
        }

        if (currentCatalogCluster && currentCatalogCluster !== 'all') {
            filterCatalogData(currentCatalogCluster);
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
                if (document.getElementById('edit-catalog-id')) document.getElementById('edit-catalog-id').value = item.id || '';
                if (document.getElementById('edit-catalog-code')) document.getElementById('edit-catalog-code').value = item.kode_indikator || item.code || '';
                if (document.getElementById('edit-catalog-unit')) document.getElementById('edit-catalog-unit').value = item.satuan || item.unit || '';
                if (document.getElementById('edit-catalog-name')) document.getElementById('edit-catalog-name').value = item.nama_indikator || item.name || item.indicator || '';

                const catClusterVal = (item.klaster || item.cluster || 'Output dan pertumbuhan').trim();
                const editCatClusterEl = document.getElementById('edit-catalog-cluster');
                if (editCatClusterEl) {
                    editCatClusterEl.value = catClusterVal;
                    if (catClusterVal && editCatClusterEl.value !== catClusterVal) {
                        const newOpt = document.createElement('option');
                        newOpt.value = catClusterVal;
                        newOpt.textContent = catClusterVal;
                        newOpt.selected = true;
                        editCatClusterEl.appendChild(newOpt);
                    }
                }

                if (document.getElementById('edit-catalog-freq')) document.getElementById('edit-catalog-freq').value = item.frekuensi || item.frequency || 'Tahunan';
                if (document.getElementById('edit-catalog-source')) document.getElementById('edit-catalog-source').value = item.sumber_data || item.source || '';
                if (document.getElementById('edit-catalog-def')) document.getElementById('edit-catalog-def').value = item.definisi_operasional || item.definition || '';
            }
        });
    }

})();
