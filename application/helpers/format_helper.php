<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('is_indicator_rupiah')) {
    /**
     * Mengecek apakah suatu indikator memiliki satuan mata uang Rupiah.
     * Mengabaikan indikator kependudukan (NAK), ketenagakerjaan bukan upah (TK),
     * geografi (GEO), analisis regional (ANA), pariwisata (PAR), dan persentase.
     */
    function is_indicator_rupiah($unit = '', $code = '') {
        $c = strtoupper(trim((string)$code));
        $pCode = explode('-', $c)[0];
        
        // Prefix yang pasti bukan rupiah
        if (in_array($pCode, array('NAK', 'GEO', 'ANA', 'PAR'))) {
            return false;
        }
        
        // Pengecualian spesifik indikator yang berformat rupiah
        $rupiahExceptions = array('TK-009', 'TK-011', 'KES-003', 'KES-011', 'KES-012', 'PRO-005', 'HRG-005');
        if (in_array($c, $rupiahExceptions)) {
            return true;
        }
        
        // Untuk klaster/kode TK, KES, PRO selain pengecualian di atas: bukan rupiah
        if (in_array($pCode, array('TK', 'KES', 'PRO'))) {
            return false;
        }
        
        $u = strtolower(trim((string)$unit));
        if (preg_match('/(%|persen|jiwa|orang|tahun|indeks|proyek|kunjungan|ton|hektare|kamar|unit)/i', $u)) {
            return false;
        }
        
        return (bool)preg_match('/\b(rupiah|rp)\b/i', $u);
    }
}

if (!function_exists('format_indicator_value')) {
    /**
     * Memformat angka indikator dengan rapi:
     * - Tanpa trailing zero (,00) untuk bilangan bulat
     * - Format desimal fleksibel (misal 1.708,1 bukan 1.708,10)
     * - Menambahkan awalan 'Rp ' hanya bila satuan memang Rupiah
     */
    function format_indicator_value($val, $unit = '', $code = '', $showPrefix = true) {
        if ($val === null || $val === '') {
            return '-';
        }
        
        $num = (float)$val;
        $isRp = is_indicator_rupiah($unit, $code);
        $prefix = ($isRp && $showPrefix) ? 'Rp ' : '';
        
        if ($num == floor($num)) {
            $formatted = number_format($num, 0, ',', '.');
        } else {
            $formatted = rtrim(rtrim(number_format($num, 2, ',', '.'), '0'), ',');
        }
        
        return $prefix . $formatted;
    }
}

if (!function_exists('get_hrg005_excel_row_meta')) {
    /**
     * Metadata struktur hierarki HRG-005 (20 Kelompok Bahan Pokok / 48 Komoditas) sesuai Excel.
     * - group_header: judul kelompok (1..20) hanya untuk kelompok yang memiliki header terpisah
     * - sub_header: sub-judul di dalam kelompok (misal: Kental Manis & Susu Bubuk di bawah 6. SUSU)
     * - direct_no: nomor kelompok langsung pada item tunggal (7, 14, 15, 16, 17) tanpa header duplikat
     */
    function get_hrg005_excel_row_meta($subKey) {
        static $metaMap = array(
            '01' => array('group_header' => array('no' => 1, 'name' => 'BERAS')),
            '03' => array('group_header' => array('no' => 2, 'name' => 'GULA')),
            '04' => array('group_header' => array('no' => 3, 'name' => 'MINYAK GORENG')),
            '08' => array('group_header' => array('no' => 4, 'name' => 'DAGING')),
            '11' => array('group_header' => array('no' => 5, 'name' => 'TELUR AYAM')),
            '13' => array('group_header' => array('no' => 6, 'name' => 'SUSU'), 'sub_header' => 'Kental Manis'),
            '15' => array('sub_header' => 'Susu Bubuk'),
            '17' => array('direct_no' => 7),
            '18' => array('group_header' => array('no' => 8, 'name' => 'GARAM BERYODIUM')),
            '20' => array('group_header' => array('no' => 9, 'name' => 'TEPUNG TERIGU')),
            '21' => array('group_header' => array('no' => 10, 'name' => 'KACANG KEDELAI')),
            '23' => array('group_header' => array('no' => 11, 'name' => 'MIE INSTANT')),
            '24' => array('group_header' => array('no' => 12, 'name' => 'CABE')),
            '27' => array('group_header' => array('no' => 13, 'name' => 'BAWANG')),
            '29' => array('direct_no' => 14),
            '30' => array('direct_no' => 15),
            '31' => array('direct_no' => 16),
            '32' => array('direct_no' => 17),
            '33' => array('group_header' => array('no' => 18, 'name' => 'SAYUR MAYUR')),
            '38' => array('group_header' => array('no' => 19, 'name' => 'SEMEN')),
            '44' => array('group_header' => array('no' => 20, 'name' => 'IKAN SEGAR')),
        );
        return $metaMap[(string)$subKey] ?? array();
    }
}
