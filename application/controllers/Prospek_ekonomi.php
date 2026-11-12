<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Prospek_ekonomi extends MY_Controller {

    public function index() {
        $status = $this->input->get('status');
        $years = $this->M_economy->get_years();
        $series = $this->M_economy->get_economic_series();
        $sectors = $this->M_economy->get_sectors();
        $prospek_records = $this->M_economy->get_prospek_records($status);
        $trash_count = $this->M_economy->count_trash_prospek();

        $data = array(
            'active_page' => 'prospek_ekonomi',
            'title' => 'Prospek Perekonomian',
            'eyebrow' => 'FORECASTING & SCENARIO • CRUD & SOFT DELETE',
            'subtitle' => 'Uji tren historis, skenario ekonomi, dan kelola dataset proyeksi tahunan Kabupaten Banyuwangi.',
            'years' => $years,
            'series' => $series,
            'sectors' => $sectors,
            'prospek_records' => $prospek_records,
            'status' => $status,
            'trash_count' => $trash_count
        );

        $this->render('prospek_ekonomi', $data, false);
    }

    public function get_data($id = null) {
        $id = (int)($id ?: $this->input->get('id'));
        $record = $this->M_economy->get_prospek_by_id($id);
        if ($record) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'success', 'data' => $record)));
        } else {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Data prospek tidak ditemukan.')));
        }
    }

    public function create() {
        $data = $this->input->post(null, true);
        $result = $this->M_economy->add_prospek($data);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Data tahun prospek baru berhasil ditambahkan.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menambahkan prospek: tahun wajib diisi atau sudah terdaftar.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data prospek tahunan berhasil ditambahkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan data prospek.');
        }
        redirect('prospek_ekonomi');
    }

    public function update($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $data = $this->input->post(null, true);
        $result = $this->M_economy->update_prospek($id, $data);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Data prospek berhasil diperbarui.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal memperbarui data prospek.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data prospek berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data.');
        }
        redirect('prospek_ekonomi');
    }

    public function soft_delete($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_economy->soft_delete_prospek($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Data prospek berhasil dipindahkan ke tempat sampah (Soft Delete).'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menghapus data prospek.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data prospek berhasil dipindahkan ke tempat sampah (Soft Delete).');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data prospek.');
        }
        redirect('prospek_ekonomi');
    }

    public function restore($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_economy->restore_prospek($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Data prospek berhasil dipulihkan dari tempat sampah.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal memulihkan data prospek.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data prospek berhasil dipulihkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memulihkan data.');
        }
        redirect('prospek_ekonomi?status=Terhapus');
    }

    public function hard_delete($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_economy->hard_delete_prospek($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Data prospek berhasil dihapus permanen dari basis data.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menghapus permanen data prospek.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data berhasil dihapus permanen.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data.');
        }
        redirect('prospek_ekonomi?status=Terhapus');
    }
}

