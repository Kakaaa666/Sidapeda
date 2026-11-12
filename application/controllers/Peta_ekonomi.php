<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Peta_ekonomi extends MY_Controller {

    public function index() {
        $status = $this->input->get('status');
        $districts = $this->M_economy->get_districts($status);
        $all_active_districts = $this->M_economy->get_districts();
        $trash_count = $this->M_economy->count_trash_districts();

        $data = array(
            'active_page' => 'peta_ekonomi',
            'title' => 'Peta Ekonomi Daerah',
            'eyebrow' => 'GEOSPATIAL ECONOMIC INTELLIGENCE • CRUD & SOFT DELETE',
            'subtitle' => 'Temukan pola, potensi, kesenjangan ekonomi, dan kelola dataset spasial kecamatan se-Kabupaten Banyuwangi.',
            'districts' => $districts,
            'all_active_districts' => $all_active_districts,
            'status' => $status,
            'trash_count' => $trash_count
        );

        $this->render('peta_ekonomi', $data, false);
    }

    public function get_data($id = null) {
        $id = (int)($id ?: $this->input->get('id'));
        $record = $this->M_economy->get_district_by_id($id);
        if ($record) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'success', 'data' => $record)));
        } else {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Data kecamatan tidak ditemukan.')));
        }
    }

    public function create() {
        $data = $this->input->post(null, true);
        $result = $this->M_economy->add_district($data);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Data kecamatan baru berhasil ditambahkan.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menambahkan data kecamatan: nama kecamatan wajib diisi atau sudah terdaftar.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data kecamatan baru berhasil ditambahkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan data kecamatan.');
        }
        redirect('peta_ekonomi');
    }

    public function update($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $data = $this->input->post(null, true);
        $result = $this->M_economy->update_district($id, $data);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Data spasial kecamatan berhasil diperbarui.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal memperbarui data kecamatan.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data spasial kecamatan berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data kecamatan.');
        }
        redirect('peta_ekonomi');
    }

    public function soft_delete($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_economy->soft_delete_district($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Kecamatan berhasil dipindahkan ke tempat sampah (Soft Delete).'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menghapus data kecamatan.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Kecamatan berhasil dipindahkan ke tempat sampah (Soft Delete).');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus kecamatan.');
        }
        redirect('peta_ekonomi');
    }

    public function restore($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_economy->restore_district($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Data kecamatan berhasil dipulihkan dari tempat sampah.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal memulihkan data kecamatan.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data kecamatan berhasil dipulihkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memulihkan data.');
        }
        redirect('peta_ekonomi?status=Terhapus');
    }

    public function hard_delete($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_economy->hard_delete_district($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Kecamatan berhasil dihapus permanen dari basis data.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menghapus permanen data kecamatan.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data kecamatan berhasil dihapus permanen.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data kecamatan.');
        }
        redirect('peta_ekonomi?status=Terhapus');
    }
}

