<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Early_warning extends MY_Controller {

    public function index() {
        $status = $this->input->get('status');
        $ews = $this->M_economy->get_ews_indicators($status);
        $trash_count = $this->M_economy->count_trash_ews();

        $data = array(
            'active_page' => 'early_warning',
            'title' => 'Early Warning System',
            'eyebrow' => 'ECONOMIC RISK MONITORING CENTER • CRUD & SOFT DELETE',
            'subtitle' => 'Deteksi dini risiko, pantau ambang batas, dan kelola dataset indikator peringatan dini ekonomi daerah.',
            'ews' => $ews,
            'status' => $status,
            'trash_count' => $trash_count
        );

        $this->render('early_warning', $data, false);
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

