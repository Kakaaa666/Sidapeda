<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Validation extends MY_Controller {

    public function index() {
        $status = $this->input->get('status');
        $query = $this->input->get('q');
        $sort = $this->input->get('sort') ?: 'status';

        // Tampilkan semua data aktif secara default agar data yang sudah divalidasi atau ditolak tidak hilang dari menu
        $filterStatus = $status;
        if (empty($filterStatus)) {
            $filterStatus = 'Semua status';
        }

        $records = $this->M_economy->get_indicator_records($filterStatus, $query, $sort);

        // Hitung statistik untuk badge ringkasan
        $allActive = $this->M_economy->get_indicator_records('Semua status');
        $stats = array(
            'total'       => count($allActive),
            'review'      => 0,
            'tervalidasi' => 0,
            'ditolak'     => 0
        );
        foreach ($allActive as $item) {
            $st = $item['status'] ?? $item['status_validasi'] ?? '';
            if ($st === 'Perlu review') {
                $stats['review']++;
            } elseif ($st === 'Tervalidasi') {
                $stats['tervalidasi']++;
            } elseif ($st === 'Belum lengkap' || $st === 'Ditolak') {
                $stats['ditolak']++;
            }
        }

        $data = array(
            'active_page' => 'validation',
            'title'       => 'Validasi Data',
            'eyebrow'     => 'DATA VERIFICATION QUEUE & HISTORY',
            'subtitle'    => 'Pemeriksaan, persetujuan, dan riwayat validasi indikator sebelum dipublikasikan.',
            'records'     => $records,
            'status'      => $filterStatus,
            'query'       => $query,
            'sort'        => $sort,
            'stats'       => $stats
        );

        $this->render('admin/validation', $data, true);
    }
}
