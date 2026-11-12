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
            'eyebrow' => 'BUSINESS INTELLIGENCE WORKSPACE • CRUD & SOFT DELETE',
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

        $this->render('analisis_ekonomi', $data, false);
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
}

