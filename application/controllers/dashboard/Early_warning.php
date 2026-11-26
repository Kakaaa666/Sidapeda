<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Early_warning extends MY_Controller {

    public function index() {
        $status = $this->input->get('status');
        $raw_year = $this->input->get('year');
        $selected_year = ($raw_year && is_numeric($raw_year)) ? (int)$raw_year : null;
        $selected_category = $this->input->get('category') ?: $this->input->get('prefix');
        $selected_cluster = $this->input->get('cluster');

        $ews = $this->M_economy->get_ews_indicators($status, $selected_year);
        // Pertahankan sub-indikator resmi untuk parent sektoral, kuartal & kategori, dan singkirkan kode non-katalog
        $sectoralParents = array(
            'EKO-003', 'EKO-004', 'EKO-006', 'EKO-007',
            'INV-001', 'INV-002', 'INV-003',
            'TK-007', 'TK-008', 'TK-010',
            'PAR-001', 'PAR-002', 'PAR-003', 'PAR-004', 'PAR-005', 'PAR-006',
            'PRO-001', 'PRO-002', 'PRO-004',
            'FIS-001', 'FIS-002', 'FIS-003',
            'HRG-001', 'HRG-002', 'HRG-003', 'HRG-004', 'HRG-005', 'HRG-006'
        );
        $excludedCodes = array('kk1', 'EKO-015', 'EKO-016', 'EKO-010.K', 'EKO-011.K', 'EKO-012.K');

        $ews = array_values(array_filter($ews, function($item) use ($sectoralParents, $excludedCodes) {
            $c = $item['code'] ?? '';
            $main = explode('.', $c)[0];
            $prefix = strtoupper(explode('-', $main)[0]);
            if (in_array($c, $excludedCodes) || in_array($main, $excludedCodes) || in_array($prefix, array('KK1'))) {
                return false;
            }
            if (strpos($c, '.') !== false) {
                return in_array($main, $sectoralParents);
            }
            return true;
        }));

        $trash_count = $this->M_economy->count_trash_ews();
        $available_years = $this->M_economy->get_catalog_years();

        $categories = array();
        foreach ($ews as $item) {
            if (strpos($item['code'], '.') !== false) continue;
            $cl = !empty($item['klaster']) ? $item['klaster'] : 'Umum';
            $categories[$cl] = ($categories[$cl] ?? 0) + 1;
        }
        arsort($categories);

        $data = array(
            'active_page'       => 'early_warning',
            'title'             => 'Early Warning System',
            'eyebrow'           => 'ECONOMIC RISK MONITORING CENTER',
            'subtitle'          => 'Deteksi dini risiko, pantau ambang batas, dan evaluasi dataset indikator peringatan dini ekonomi daerah.',
            'ews'               => $ews,
            'status'            => $status,
            'trash_count'       => $trash_count,
            'available_years'   => $available_years,
            'selected_year'     => $selected_year,
            'selected_category' => $selected_category,
            'selected_cluster'  => $selected_cluster,
            'categories'        => $categories
        );

        $this->render('dashboard/early_warning', $data, false);
    }

    public function get_data($id = null) {
        $id = (int)($id ?: $this->input->get('id'));
        $record = $this->M_economy->get_ews_by_id($id);
        if ($record) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'success', 'data' => $record)));
        } else {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Indikator EWS tidak ditemukan.')));
        }
    }

    public function create() {
        $data = $this->input->post(null, true);
        $result = $this->M_economy->add_ews($data);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Indikator pemantauan EWS berhasil ditambahkan.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menambahkan EWS: kode atau nama indikator wajib diisi atau sudah terdaftar.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Indikator pemantauan EWS berhasil ditambahkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan indikator EWS.');
        }
        redirect('early_warning');
    }

    public function update($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $data = $this->input->post(null, true);
        $result = $this->M_economy->update_ews($id, $data);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Data indikator EWS berhasil diperbarui.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal memperbarui indikator EWS.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data indikator EWS berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data EWS.');
        }
        redirect('early_warning');
    }

    public function soft_delete($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_economy->soft_delete_ews($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Indikator EWS berhasil dipindahkan ke tempat sampah (Soft Delete).'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menghapus indikator EWS.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Indikator EWS berhasil dipindahkan ke tempat sampah (Soft Delete).');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus indikator EWS.');
        }
        redirect('early_warning');
    }

    public function restore($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_economy->restore_ews($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Indikator EWS berhasil dipulihkan dari tempat sampah.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal memulihkan indikator EWS.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Indikator EWS berhasil dipulihkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memulihkan data EWS.');
        }
        redirect('early_warning?status=Terhapus');
    }

    public function hard_delete($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_economy->hard_delete_ews($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Indikator EWS berhasil dihapus permanen dari basis data.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menghapus permanen indikator EWS.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data berhasil dihapus permanen.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data.');
        }
        redirect('early_warning?status=Terhapus');
    }
}

