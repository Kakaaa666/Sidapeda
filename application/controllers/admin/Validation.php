<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Validation extends MY_Controller {

    public function index() {
        $status = $this->input->get('status');
        $query = $this->input->get('q');
        $sort = $this->input->get('sort') ?: 'code';
        $cluster = $this->input->get('cluster');
        $year = $this->input->get('year');
        $selected_year = ($year && is_numeric($year)) ? (int)$year : null;
        $category = $this->input->get('category') ?: $this->input->get('prefix');
        $selected_category = $category ? strtoupper(trim($category)) : null;
        $available_years = $this->M_economy->get_catalog_years();

        // Tampilkan semua data aktif secara default agar data yang sudah divalidasi atau ditolak tidak hilang dari menu
        $filterStatus = $status;
        if (empty($filterStatus)) {
            $filterStatus = 'Semua status';
        }

        $records = $this->M_economy->get_indicator_records($filterStatus, $query, $sort, $selected_year, null, $cluster);
        $clusters = $this->M_economy->get_database_clusters();

        // Hitung statistik untuk badge ringkasan
        $allActive = $this->M_economy->get_indicator_records('Semua status', null, 'status', $selected_year, null, $cluster);
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
            'cluster'     => $cluster,
            'selected_cluster' => $cluster,
            'category'    => $selected_category,
            'selected_category' => $selected_category,
            'year'        => $year,
            'selected_year' => $selected_year,
            'available_years' => $available_years,
            'clusters'    => $clusters,
            'stats'       => $stats
        );

        $this->render('admin/validation', $data, true);
    }
}
