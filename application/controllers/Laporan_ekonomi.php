<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan_ekonomi extends MY_Controller {

    public function index() {
        $status = $this->input->get('status');
        $years = $this->M_economy->get_years();
        $series = $this->M_economy->get_economic_series();
        $sectors = $this->M_economy->get_sectors();
        $reports = $this->M_economy->get_laporan_ekonomi($status);
        $trash_count = $this->M_economy->count_trash_laporan();

        $data = array(
            'active_page' => 'laporan_ekonomi',
            'title' => 'Generator Laporan Ekonomi',
            'eyebrow' => 'AUTOMATED REPORTING • CRUD & SOFT DELETE',
            'subtitle' => 'Susun laporan analitis terstandardisasi, kelola arsip dokumen laporan, dan ekspor ringkasan ekonomi daerah.',
            'years' => $years,
            'series' => $series,
            'sectors' => $sectors,
            'reports' => $reports,
            'status' => $status,
            'trash_count' => $trash_count
        );

        $this->render('laporan_ekonomi', $data, false);
    }

    public function get_data($id = null) {
        $id = (int)($id ?: $this->input->get('id'));
        $record = $this->M_economy->get_laporan_by_id($id);
        if ($record) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'success', 'data' => $record)));
        } else {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Dokumen laporan tidak ditemukan.')));
        }
    }

    public function create() {
        $data = $this->input->post(null, true);
        $result = $this->M_economy->add_laporan($data);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Dokumen laporan baru berhasil didaftarkan.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal mendaftarkan laporan: judul laporan wajib diisi.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Dokumen laporan berhasil didaftarkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal mendaftarkan dokumen laporan.');
        }
        redirect('laporan_ekonomi');
    }

    public function update($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $data = $this->input->post(null, true);
        $result = $this->M_economy->update_laporan($id, $data);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Dokumen laporan berhasil diperbarui.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal memperbarui dokumen laporan.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Dokumen laporan berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data laporan.');
        }
        redirect('laporan_ekonomi');
    }

    public function soft_delete($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_economy->soft_delete_laporan($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Laporan berhasil dipindahkan ke tempat sampah (Soft Delete).'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menghapus dokumen laporan.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Laporan berhasil dipindahkan ke tempat sampah (Soft Delete).');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus laporan.');
        }
        redirect('laporan_ekonomi');
    }

    public function restore($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_economy->restore_laporan($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Dokumen laporan berhasil dipulihkan dari tempat sampah.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal memulihkan dokumen laporan.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Dokumen laporan berhasil dipulihkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memulihkan laporan.');
        }
        redirect('laporan_ekonomi?status=Terhapus');
    }

    public function hard_delete($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_economy->hard_delete_laporan($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Dokumen laporan berhasil dihapus permanen dari basis data.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menghapus permanen dokumen laporan.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Laporan berhasil dihapus permanen.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data laporan.');
        }
        redirect('laporan_ekonomi?status=Terhapus');
    }
}

