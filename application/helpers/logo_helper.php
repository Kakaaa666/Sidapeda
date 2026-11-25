<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('get_logo_pemda_filename')) {
    function get_logo_pemda_filename($query) {
        if (empty($query)) {
            return 'Kab. Banyuwangi.png';
        }

        $codeMap = array(
            '35.01' => 'Kab. Pacitan.png',
            '35.02' => 'Kab. Ponorogo.png',
            '35.03' => 'Kab. Trenggalek.png',
            '35.04' => 'Kab. Tulungagung.png',
            '35.05' => 'Kab. Blitar.png',
            '35.06' => 'Kab. Kediri.png',
            '35.07' => 'Kab. Malang.png',
            '35.08' => 'Kab. Lumajang.png',
            '35.09' => 'Kab. Jember.png',
            '35.10' => 'Kab. Banyuwangi.png',
            '35.11' => 'Kab. Bondowoso.png',
            '35.12' => 'Kab. Situbondo.png',
            '35.13' => 'Kab. Probolinggo.png',
            '35.14' => 'Kab. Pasuruan.png',
            '35.15' => 'Kab. Sidoarjo.png',
            '35.16' => 'Kab. Mojokerto.png',
            '35.17' => 'Kab. Jombang.png',
            '35.18' => 'Kab. Nganjuk.png',
            '35.19' => 'Kab. Madiun.png',
            '35.20' => 'Kab. Magetan.png',
            '35.21' => 'Kab. Ngawi.png',
            '35.22' => 'Kab. Bojonegoro.png',
            '35.23' => 'Kab. Tuban.png',
            '35.24' => 'Kab. Lamongan.png',
            '35.25' => 'Kab. Gresik.png',
            '35.26' => 'Kab. Bangkalan.png',
            '35.27' => 'Kab. Sampang.png',
            '35.28' => 'Kab. Pamekasan.png',
            '35.29' => 'Kab. Sumenep.png',
            '35.71' => 'Kota Kediri.png',
            '35.72' => 'Kota Blitar.png',
            '35.73' => 'Kota Malang.png',
            '35.74' => 'Kota Probolinggo.png',
            '35.75' => 'Kota Pasuruan.png',
            '35.76' => 'Kota Mojokerto.png',
            '35.77' => 'Kota Madiun.png',
            '35.78' => 'Kota Surabaya.png',
            '35.79' => 'Kota Batu.png',
            '35'    => 'Jawa Timur.png',
            '35.00' => 'Jawa Timur.png'
        );

        $q = trim((string)$query);

        // Jika cocok dengan kode wilayah
        if (isset($codeMap[$q])) {
            return $codeMap[$q];
        }

        // Cek jika mengandung Jawa Timur / Jatim
        if (stripos($q, 'jawa timur') !== false || strcasecmp($q, 'jatim') === 0 || stripos($q, 'provinsi') !== false) {
            return 'Jawa Timur.png';
        }

        // Normalisasi teks
        $qClean = preg_replace('/^(pemerintah\s+kabupaten|pemerintah\s+kota|pemerintah\s+kab\.|kabupaten|kab\.|kota)\s+/i', '', $q);
        $qClean = trim($qClean);
        $qLower = strtolower($qClean);

        // Cek Kota
        if (stripos($q, 'kota') !== false || in_array($qLower, array('batu', 'surabaya'))) {
            $kotaMap = array(
                'kediri'      => 'Kota Kediri.png',
                'blitar'      => 'Kota Blitar.png',
                'malang'      => 'Kota Malang.png',
                'probolinggo' => 'Kota Probolinggo.png',
                'pasuruan'    => 'Kota Pasuruan.png',
                'mojokerto'   => 'Kota Mojokerto.png',
                'madiun'      => 'Kota Madiun.png',
                'surabaya'    => 'Kota Surabaya.png',
                'batu'        => 'Kota Batu.png'
            );
            foreach ($kotaMap as $key => $file) {
                if (stripos($qLower, $key) !== false) {
                    return $file;
                }
            }
        }

        // Cek Kabupaten
        $kabMap = array(
            'pacitan'     => 'Kab. Pacitan.png',
            'ponorogo'    => 'Kab. Ponorogo.png',
            'trenggalek'  => 'Kab. Trenggalek.png',
            'tulungagung' => 'Kab. Tulungagung.png',
            'blitar'      => 'Kab. Blitar.png',
            'kediri'      => 'Kab. Kediri.png',
            'malang'      => 'Kab. Malang.png',
            'lumajang'    => 'Kab. Lumajang.png',
            'jember'      => 'Kab. Jember.png',
            'banyuwangi'  => 'Kab. Banyuwangi.png',
            'bondowoso'   => 'Kab. Bondowoso.png',
            'situbondo'   => 'Kab. Situbondo.png',
            'probolinggo' => 'Kab. Probolinggo.png',
            'pasuruan'    => 'Kab. Pasuruan.png',
            'sidoarjo'    => 'Kab. Sidoarjo.png',
            'mojokerto'   => 'Kab. Mojokerto.png',
            'jombang'     => 'Kab. Jombang.png',
            'nganjuk'     => 'Kab. Nganjuk.png',
            'madiun'      => 'Kab. Madiun.png',
            'magetan'     => 'Kab. Magetan.png',
            'ngawi'       => 'Kab. Ngawi.png',
            'bojonegoro'  => 'Kab. Bojonegoro.png',
            'tuban'       => 'Kab. Tuban.png',
            'lamongan'    => 'Kab. Lamongan.png',
            'gresik'      => 'Kab. Gresik.png',
            'bangkalan'   => 'Kab. Bangkalan.png',
            'sampang'     => 'Kab. Sampang.png',
            'pamekasan'   => 'Kab. Pamekasan.png',
            'sumenep'     => 'Kab. Sumenep.png'
        );

        foreach ($kabMap as $key => $file) {
            if (stripos($qLower, $key) !== false) {
                return $file;
            }
        }

        // Default fallback
        return 'Kab. Banyuwangi.png';
    }
}

if (!function_exists('get_logo_pemda_url')) {
    function get_logo_pemda_url($query) {
        $file = get_logo_pemda_filename($query);
        return base_url('assets/img/Logo Pemda/' . rawurlencode($file));
    }
}
