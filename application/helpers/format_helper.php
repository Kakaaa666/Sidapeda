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
