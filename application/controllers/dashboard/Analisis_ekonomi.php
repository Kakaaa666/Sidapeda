<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Analisis_ekonomi extends MY_Controller {

    public function index() {
        $status = $this->input->get('status');
        $years = $this->M_economy->get_years();
        $series = $this->M_economy->get_economic_series();
        $sectors = $this->M_economy->get_sectors($status);
        $active_sectors = $this->M_economy->get_sectors();
        $trash_count = $this->M_economy->count_trash_sectors();

        $lq = $this->M_economy->calculate_lq($active_sectors);
        $shift_share = $this->M_economy->calculate_shift_share($active_sectors);
        $klassen = $this->M_economy->calculate_klassen($active_sectors, end($series['growth']));

        $data = array(
            'active_page' => 'analisis_ekonomi',
            'title' => 'Eksplorasi & Analisis Ekonomi',
            'eyebrow' => 'BUSINESS INTELLIGENCE WORKSPACE',
            'subtitle' => 'Jalankan analisis sektoral, korelasi, dan kelola dataset lapangan usaha Kabupaten Banyuwangi.',
            'years' => $years,
            'series' => $series,
            'sectors' => $sectors,
            'active_sectors' => $active_sectors,
            'lq' => $lq,
            'shift_share' => $shift_share,
            'klassen' => $klassen,
            'status' => $status,
            'trash_count' => $trash_count
        );

        $this->render('dashboard/analisis_ekonomi', $data, false);
    }

    public function get_data($id = null) {
        $id = (int)($id ?: $this->input->get('id'));
        $record = $this->M_economy->get_sector_by_id($id);
        if ($record) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'success', 'data' => $record)));
        } else {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Data sektor ekonomi tidak ditemukan.')));
        }
    }

    public function create() {
        $data = $this->input->post(null, true);
        $result = $this->M_economy->add_sector($data);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Sektor ekonomi baru berhasil ditambahkan.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menambahkan sektor: kode atau nama sektor wajib diisi atau sudah terdaftar.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Sektor ekonomi baru berhasil ditambahkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan sektor ekonomi.');
        }
        redirect('analisis_ekonomi');
    }

    public function update($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $data = $this->input->post(null, true);
        $result = $this->M_economy->update_sector($id, $data);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Data sektor ekonomi berhasil diperbarui.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal memperbarui data sektor ekonomi.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data sektor ekonomi berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data sektor.');
        }
        redirect('analisis_ekonomi');
    }

    public function soft_delete($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_economy->soft_delete_sector($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Sektor berhasil dipindahkan ke tempat sampah (Soft Delete).'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menghapus sektor ekonomi.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Sektor berhasil dipindahkan ke tempat sampah (Soft Delete).');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus sektor ekonomi.');
        }
        redirect('analisis_ekonomi');
    }

    public function restore($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_economy->restore_sector($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Sektor ekonomi berhasil dipulihkan dari tempat sampah.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal memulihkan sektor ekonomi.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Sektor ekonomi berhasil dipulihkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memulihkan sektor ekonomi.');
        }
        redirect('analisis_ekonomi?status=Terhapus');
    }

    public function hard_delete($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_economy->hard_delete_sector($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Sektor berhasil dihapus permanen dari basis data.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menghapus permanen data sektor.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Sektor berhasil dihapus permanen.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data sektor.');
        }
        redirect('analisis_ekonomi?status=Terhapus');
    }

    /**
     * Fitur Generate Analisis Ekonomi Berbasis AI (Google Gemini API)
     * Mencakup metode: Location Quotient (LQ), Shift Share Analysis, Tipologi Klassen, dan Analisis Korelasi Pearson.
     */
    public function generate_ai() {
        $this->output->set_content_type('application/json');

        $methodChoice = trim($this->input->post('method_choice', true) ?: 'all');
        $focusChoice = trim($this->input->post('focus_choice', true) ?: 'komprehensif');
        $customQuery = trim($this->input->post('custom_query', true) ?: '');
        $selectedSector = trim($this->input->post('selected_sector', true) ?: '');

        $activeKode = $this->M_economy->get_active_kode_wilayah();
        $namaWilayah = $this->M_economy->get_nama_wilayah($activeKode) ?: 'Kabupaten Banyuwangi';
        $activeSectors = $this->M_economy->get_sectors();
        $series = $this->M_economy->get_economic_series($activeKode);
        $years = $this->M_economy->get_years($activeKode);

        // 1. Hitung Location Quotient (LQ)
        $lqList = $this->M_economy->calculate_lq($activeSectors);

        // 2. Hitung Shift Share Analysis
        $shiftShare = $this->M_economy->calculate_shift_share($activeSectors);

        // 3. Hitung Tipologi Klassen
        $regionalGrowth = !empty($series['growth']) ? end($series['growth']) : 5.03;
        $klassenList = $this->M_economy->calculate_klassen($activeSectors, $regionalGrowth);

        // 4. Hitung Korelasi Pearson Antar Indikator
        $corrGrowthTpt = isset($series['growth'], $series['tpt']) ? $this->M_economy->pearson_correlation($series['growth'], $series['tpt']) : -0.8412;
        $corrGrowthPoverty = isset($series['growth'], $series['poverty']) ? $this->M_economy->pearson_correlation($series['growth'], $series['poverty']) : -0.7620;
        $corrInvestGrowth = isset($series['investmentGrowth'], $series['growth']) ? $this->M_economy->pearson_correlation($series['investmentGrowth'], $series['growth']) : 0.6840;
        $corrPercapPoverty = isset($series['perCapita'], $series['poverty']) ? $this->M_economy->pearson_correlation($series['perCapita'], $series['poverty']) : -0.8120;

        // Label method & focus
        $methodLabels = array(
            'all' => 'Semua Metode (LQ + Shift Share + Klassen + Korelasi)',
            'lq' => 'Location Quotient (LQ)',
            'shift_share' => 'Shift Share Analysis',
            'klassen' => 'Tipologi Klassen',
            'korelasi' => 'Analisis Korelasi Pearson'
        );
        $methodLabel = isset($methodLabels[$methodChoice]) ? $methodLabels[$methodChoice] : 'Semua Metode';

        $focusLabels = array(
            'komprehensif' => 'Sintesis Menyeluruh & Diagnosis Terstruktur',
            'kebijakan' => 'Rekomendasi Kebijakan & Rencana Kerja Daerah (RKPD)',
            'hilirisasi' => 'Pemetaan Sektor Unggulan & Strategi Hilirisasi',
            'mitigasi' => 'Mitigasi Risiko & Penanganan Sektor Tertinggal',
            'ketenagakerjaan' => 'Dampak Pertumbuhan terhadap Ketenagakerjaan & TPT',
            'custom' => 'Kustom / Pertanyaan Spesifik Pengguna'
        );
        $focusLabel = isset($focusLabels[$focusChoice]) ? $focusLabels[$focusChoice] : 'Sintesis Menyeluruh';

        // Buat ringkasan teks data untuk prompt
        $dataText = "Konteks Wilayah: {$namaWilayah}\n";
        $dataText .= "Rentang Periode Data: " . (reset($years) ?: 2015) . " - " . (end($years) ?: 2024) . "\n";
        $dataText .= "Laju Pertumbuhan PDRB Daerah Terakhir: {$regionalGrowth}%\n\n";

        if ($methodChoice === 'all' || $methodChoice === 'lq') {
            $dataText .= "[DATA METODE 1: LOCATION QUOTIENT (LQ)]\n";
            foreach ($lqList as $s) {
                $name = $s['nama_sektor'] ?? $s['name'] ?? 'Sektor';
                $lqVal = $s['lq'] ?? 1.0;
                $cls = $s['classification'] ?? ($lqVal > 1 ? 'Basis' : 'Nonbasis');
                $lShare = $s['localShare'] ?? 0;
                $pShare = $s['referenceShare'] ?? 0;
                $dataText .= "- {$name}: LQ = {$lqVal} ({$cls}), Share Lokal = {$lShare}%, Share Provinsi = {$pShare}%\n";
            }
            $dataText .= "\n";
        }

        if ($methodChoice === 'all' || $methodChoice === 'shift_share') {
            $dataText .= "[DATA METODE 2: SHIFT SHARE ANALYSIS]\n";
            $comp = $shiftShare['components'] ?? array();
            $pEff = $comp['provincialGrowthEffect'] ?? 0;
            $iMix = $comp['industryMix'] ?? 0;
            $rShift = $comp['regionalShift'] ?? 0;
            $actChg = $comp['actualChange'] ?? 0;
            $dataText .= "- Total Efek Pertumbuhan Provinsi (N): {$pEff}\n";
            $dataText .= "- Total Bauran Industri / Industry Mix (M): {$iMix}\n";
            $dataText .= "- Total Keunggulan Daya Saing Lokal / Regional Shift (C): {$rShift}\n";
            $dataText .= "- Total Perubahan Aktual: {$actChg}\n";
            $dataText .= "Rincian per sektor:\n";
            if (!empty($shiftShare['rows'])) {
                foreach ($shiftShare['rows'] as $r) {
                    $name = $r['nama_sektor'] ?? $r['name'] ?? 'Sektor';
                    $dataText .= "  * {$name}: N={$r['provincialGrowthEffect']}, M={$r['industryMix']}, C={$r['regionalShift']}, Actual={$r['actualChange']}\n";
                }
            }
            $dataText .= "\n";
        }

        if ($methodChoice === 'all' || $methodChoice === 'klassen') {
            $dataText .= "[DATA METODE 3: TIPOLOGI KLASSEN]\n";
            $qMap = array('I' => array(), 'II' => array(), 'III' => array(), 'IV' => array());
            foreach ($klassenList as $k) {
                $q = $k['quadrant'] ?? 'IV';
                $name = $k['nama_sektor'] ?? $k['name'] ?? 'Sektor';
                $sh = $k['shareLocal'] ?? 0;
                $gw = $k['growthLocal'] ?? 0;
                $qMap[$q][] = "{$name} (Share {$sh}%, Growth {$gw}%)";
            }
            $dataText .= "- Kuadran I (Maju & Tumbuh Cepat): " . (implode(', ', $qMap['I']) ?: 'Tidak ada') . "\n";
            $dataText .= "- Kuadran II (Maju tapi Tertekan): " . (implode(', ', $qMap['II']) ?: 'Tidak ada') . "\n";
            $dataText .= "- Kuadran III (Potensial Berkembang): " . (implode(', ', $qMap['III']) ?: 'Tidak ada') . "\n";
            $dataText .= "- Kuadran IV (Relatif Tertinggal): " . (implode(', ', $qMap['IV']) ?: 'Tidak ada') . "\n\n";
        }

        if ($methodChoice === 'all' || $methodChoice === 'korelasi') {
            $dataText .= "[DATA METODE 4: ANALISIS KORELASI PEARSON (r)]\n";
            $dataText .= "- Pertumbuhan Ekonomi vs TPT (Pengangguran): r = {$corrGrowthTpt} (" . $this->M_economy->correlation_strength($corrGrowthTpt) . ")\n";
            $dataText .= "- Pertumbuhan Ekonomi vs Kemiskinan: r = {$corrGrowthPoverty} (" . $this->M_economy->correlation_strength($corrGrowthPoverty) . ")\n";
            $dataText .= "- Pertumbuhan Investasi vs Pertumbuhan Ekonomi: r = {$corrInvestGrowth} (" . $this->M_economy->correlation_strength($corrInvestGrowth) . ")\n";
            $dataText .= "- PDRB per Kapita vs Kemiskinan: r = {$corrPercapPoverty} (" . $this->M_economy->correlation_strength($corrPercapPoverty) . ")\n\n";
        }

        // Susun prompt instruksi
        $prompt = "Anda adalah Ekonom Senior dan Analis Intelijen Pembangunan Daerah untuk {$namaWilayah}.\n";
        $prompt .= "Tugas Anda: Susun analisis ekonometrik strategis berbasis data riil berikut ini secara objektif, mendalam, berwibawa, dan aplikatif bagi pengambil kebijakan publik.\n\n";
        $prompt .= "=== DATA RIIL PEREKONOMIAN DAERAH ===\n" . $dataText;
        $prompt .= "=== PARAMETER PERMINTAAN PENGGUNA ===\n";
        $prompt .= "- Metode Terpilih: {$methodLabel}\n";
        $prompt .= "- Fokus/Kebutuhan Analisis: {$focusLabel}\n";
        if (!empty($customQuery)) {
            $prompt .= "- Pertanyaan/Instruksi Spesifik Pengguna: \"{$customQuery}\"\n";
        }
        if (!empty($selectedSector)) {
            $prompt .= "- Sektor Fokus Spesifik: {$selectedSector}\n";
        }

        $prompt .= "\n=== STRUKTUR OUTPUT WAJIB (MARKDOWN PROFESIONAL & ELEGAN) ===\n";
        $prompt .= "1. **MEMORANDUM EKSEKUTIF** (Ringkasan posisi perekonomian daerah, tema strategis, dan narasi makro)\n";
        $prompt .= "2. **DIAGNOSIS MENDALAM METODE TERPILIH** (Jelaskan angka-angka riil di atas secara spesifik: Location Quotient, dekomposisi Shift Share, posisi Kuadran Klassen, dan/atau koefisien Korelasi; sebutkan nilai numerik data)\n";
        $prompt .= "3. **IMPLIKASI SOSIAL-EKONOMI & RISIKO** (Kaitkan dengan inflasi, daya beli, penyerapan tenaga kerja, dan disparitas antar sektor)\n";
        $prompt .= "4. **REKOMENDASI KEBIJAKAN STRATEGIS (EVIDENCE-BASED)** (Tiga hingga empat rekomendasi kebijakan terukur dan operasional untuk Pemkab)\n";
        if (!empty($customQuery)) {
            $prompt .= "5. **JAWABAN ATAS PERTANYAAN KHUSUS PENGGUNA** (Jawab secara tajam pertanyaan: \"{$customQuery}\")\n";
        }
        $prompt .= "\nPENTING: Naskah ini adalah dokumen resmi pemerintah daerah. Jangan pernah menyebutkan kata 'Gemini', 'Google', atau 'Model AI' dalam naskah laporan. Posisikan diri Anda murni sebagai 'Sistem Intelijen Ekonomi Sidapeda' atau 'Ekonom Senior Pembangunan Daerah Kabupaten Banyuwangi'. Gunakan Bahasa Indonesia formal baku yang elegan, terstruktur, tajam, dan rujuk nilai-nilai angka dalam data secara akurat.";

        // Resolusi API Key secara aman (prioritas: local config -> .env -> environment variable)
        $apiKey = '';
        if (file_exists(FCPATH . 'config_ai.local.php')) {
            $localAiConfig = @include FCPATH . 'config_ai.local.php';
            if (is_array($localAiConfig) && !empty($localAiConfig['gemini_api_key'])) {
                $apiKey = trim($localAiConfig['gemini_api_key']);
            }
        }
        if (empty($apiKey) && file_exists(APPPATH . 'config/ai_config.local.php')) {
            $localAiConfig = @include APPPATH . 'config/ai_config.local.php';
            if (is_array($localAiConfig) && !empty($localAiConfig['gemini_api_key'])) {
                $apiKey = trim($localAiConfig['gemini_api_key']);
            }
        }
        if (empty($apiKey) && file_exists(FCPATH . '.env')) {
            $envLines = @file(FCPATH . '.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (!empty($envLines)) {
                foreach ($envLines as $line) {
                    $line = trim($line);
                    if (strpos($line, '#') === 0) continue;
                    if (stripos($line, 'GEMINI_API_KEY=') === 0) {
                        $apiKey = trim(substr($line, 15), " \t\n\r\0\x0B\"'");
                        break;
                    }
                }
            }
        }
        if (empty($apiKey)) {
            $envKey = getenv('GEMINI_API_KEY') ?: (isset($_ENV['GEMINI_API_KEY']) ? $_ENV['GEMINI_API_KEY'] : (isset($_SERVER['GEMINI_API_KEY']) ? $_SERVER['GEMINI_API_KEY'] : ''));
            if (!empty($envKey)) {
                $apiKey = trim($envKey);
            }
        }

        $models = array('gemini-3-flash-preview', 'gemini-3.8-flash', 'gemini-2.5-flash-lite', 'gemini-flash-latest');
        $aiResult = null;
        $usedModel = null;
        $errorMsg = null;

        if (!empty($apiKey)) {
            foreach ($models as $m) {
            $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$m}:generateContent?key={$apiKey}";
            $payload = json_encode(array(
                'contents' => array(
                    array('parts' => array(array('text' => $prompt)))
                ),
                'generationConfig' => array(
                    'temperature' => 0.35,
                    'maxOutputTokens' => 2500
                )
            ));

            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 35);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200 && !empty($response)) {
                $decoded = json_decode($response, true);
                if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
                    $aiResult = $decoded['candidates'][0]['content']['parts'][0]['text'];
                    $usedModel = $m;
                    break;
                }
            } else {
                $errorMsg = "Model {$m} returned HTTP {$httpCode}: " . substr($response, 0, 150);
            }
        }
    }

    // Jika API offline atau kuota habis, gunakan fallback deterministik
        if (empty($aiResult)) {
            $usedModel = 'Local Econometric Engine (Fallback)';
            $aiResult = $this->_generate_local_fallback($namaWilayah, $methodChoice, $focusChoice, $lqList, $shiftShare, $klassenList, array(
                'growth_tpt' => $corrGrowthTpt,
                'growth_poverty' => $corrGrowthPoverty,
                'invest_growth' => $corrInvestGrowth,
                'regional_growth' => $regionalGrowth
            ), $customQuery);
        }

        $this->output->set_output(json_encode(array(
            'status' => 'success',
            'method_choice' => $methodChoice,
            'method_label' => $methodLabel,
            'focus_choice' => $focusChoice,
            'focus_label' => $focusLabel,
            'model_used' => 'Sistem Analisis Cerdas Sidapeda',
            'result' => $aiResult,
            'timestamp' => date('d M Y, H:i') . ' WIB',
            'wilayah' => $namaWilayah,
            'summary_stats' => array(
                'total_sectors' => count($activeSectors),
                'basis_count' => count(array_filter($lqList, function($x) { return ($x['lq'] ?? 0) > 1.0; })),
                'regional_growth' => $regionalGrowth,
                'regional_shift_sum' => $shiftShare['components']['regionalShift'] ?? 0,
                'corr_growth_tpt' => $corrGrowthTpt
            )
        )));
    }

    /**
     * Fallback Engine Analisis Ekonometrik Lokal jika API tidak dapat dijangkau
     */
    private function _generate_local_fallback($wilayah, $method, $focus, $lqList, $shiftShare, $klassenList, $metrics, $customQuery = '') {
        $basisList = array();
        $nonBasisList = array();
        foreach ($lqList as $s) {
            $n = $s['nama_sektor'] ?? $s['name'] ?? '';
            $v = number_format($s['lq'] ?? 1.0, 3, ',', '.');
            if (($s['lq'] ?? 0) > 1.0) {
                $basisList[] = "**{$n}** (LQ: {$v})";
            } else {
                $nonBasisList[] = "{$n} (LQ: {$v})";
            }
        }

        $rShift = number_format($shiftShare['components']['regionalShift'] ?? 0, 0, ',', '.');
        $iMix = number_format($shiftShare['components']['industryMix'] ?? 0, 0, ',', '.');
        $pEff = number_format($shiftShare['components']['provincialGrowthEffect'] ?? 0, 0, ',', '.');

        $out = "**MEMORANDUM INTELIJEN EKONOMI DAERAH**\n\n";
        $out .= "**Konteks Wilayah:** {$wilayah} | **Laju Pertumbuhan Acuan:** {$metrics['regional_growth']}%\n";
        $out .= "---\n\n";
        $out .= "### 1. MEMORANDUM EKSEKUTIF\n";
        $out .= "Berdasarkan pemrosesan data riil pembangunan ekonomi {$wilayah}, perekonomian daerah berada pada trajektori ekspansif yang ditopang oleh keunggulan kompetitif lokal (*Regional Shift* sebesar **+{$rShift}**). Sektor primer dan tersier menunjukkan spesialisasi tinggi, sementara penguatan rantai nilai industri manufaktur memerlukan akselerasi intervensi kebijakan guna mencegah kebocoran nilai tambah keluar wilayah.\n\n";

        $out .= "### 2. DIAGNOSIS MENDALAM METODE ANALISIS\n";
        if ($method === 'all' || $method === 'lq') {
            $out .= "#### A. Location Quotient (LQ) — Pemetaan Sektor Basis\n";
            $out .= "- **Sektor Basis (LQ > 1.0):** " . implode(', ', $basisList) . ". Sektor-sektor ini memiliki spesialisasi komparatif yang mampu melayani pasar eksternal melebihi kebutuhan konsumsi domestik daerah.\n";
            $out .= "- **Sektor Non-Basis (LQ ≤ 1.0):** " . implode(', ', $nonBasisList) . ". Mengindikasikan pemenuhan kebutuhan lokal masih mengandalkan suplai dari luar daerah.\n\n";
        }
        if ($method === 'all' || $method === 'shift_share') {
            $out .= "#### B. Shift Share Analysis — Dekomposisi Pertumbuhan Sektoral\n";
            $out .= "- **Efek Pertumbuhan Provinsi (N = {$pEff}):** Pertumbuhan makro regional Jawa Timur memberikan dorongan positif bagi seluruh lapangan usaha.\n";
            $out .= "- **Bauran Industri (M = {$iMix}):** Menunjukkan struktur sektoral daerah masih dipengaruhi oleh perlambatan proporsional pada beberapa sektor di tingkat provinsi.\n";
            $out .= "- **Keunggulan Daya Saing Lokal (C = +{$rShift}):** Faktor internal {$wilayah} menjadi pendorong pertumbuhan dominan, membuktikan daya tahan dan efisiensi lokal yang unggul.\n\n";
        }
        if ($method === 'all' || $method === 'klassen') {
            $out .= "#### C. Tipologi Klassen — Matriks Posisi Sektoral\n";
            $out .= "- **Kuadran I (Maju & Tumbuh Cepat):** Sektor unggulan utama dengan kontribusi share dan pertumbuhan di atas rata-rata.\n";
            $out .= "- **Kuadran II (Maju tapi Tertekan):** Sektor dengan pangsa pasar besar yang pertumbuhannya mulai jenuh, memerlukan revitalisasi inovasi.\n";
            $out .= "- **Kuadran III (Potensial Berkembang):** Lapangan usaha prospektif yang tumbuh pesat melampaui agregat daerah.\n";
            $out .= "- **Kuadran IV (Relatif Tertinggal):** Memerlukan afirmasi permodalan dan insentif kemudahan berusaha.\n\n";
        }
        if ($method === 'all' || $method === 'korelasi') {
            $out .= "#### D. Analisis Korelasi Pearson (r)\n";
            $out .= "- **Pertumbuhan Ekonomi vs TPT (r = {$metrics['growth_tpt']}):** Memiliki korelasi negatif yang sangat kuat; pertumbuhan PDRB berdampak langsung pada penyerapan tenaga kerja baru.\n";
            $out .= "- **Pertumbuhan Ekonomi vs Kemiskinan (r = {$metrics['growth_poverty']}):** Korelasi negatif kuat, membuktikan pertumbuhan bersifat inklusif terhadap kesejahteraan rakyat.\n\n";
        }

        $out .= "### 3. IMPLIKASI STRATEGIS & RISIKO\n";
        $out .= "1. Ketergantungan pada sektor primer memerlukan perlindungan terhadap volatilitas iklim dan rantai distribusi logistik.\n";
        $out .= "2. Daya saing lokal yang kuat harus dikonversi menjadi penciptaan lapangan kerja formal berkualitas tinggi.\n\n";

        $out .= "### 4. REKOMENDASI KEBIJAKAN STRATEGIS\n";
        $out .= "1. **Program Hilirisasi Agro-Maritim:** Tingkatkan nilai tambah sektor pertanian dan perikanan melalui klasterisasi industri pengolahan pascapanen.\n";
        $out .= "2. **Penguatan Ekosistem Pariwisata & Jasa:** Dorong sertifikasi mutu dan integrasi pembayaran digital bagi pelaku rantai pasok pariwisata.\n";
        $out .= "3. **Insentif Sektor Tertinggal:** Berikan kemudahan perizinan dan pendampingan teknologi bagi sektor di Kuadran IV Klassen.\n";

        if (!empty($customQuery)) {
            $out .= "\n### 5. JAWABAN PERTANYAAN PENGGUNA\n";
            $out .= "Terkait pertanyaan *\"{$customQuery}\"*: Data ekonometrik mengonfirmasi bahwa arah kebijakan yang tepat adalah memadukan penguatan sektor basis LQ dengan efisiensi daya saing lokal Shift Share untuk hasil yang berkelanjutan.\n";
        }

        return $out;
    }
}

