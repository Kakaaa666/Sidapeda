<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Catalog extends MY_Controller {

    public function index() {
        // Sinkronkan seluruh data yang sudah tervalidasi agar otomatis masuk ke katalog indikator
        $this->M_economy->sync_all_validated_to_catalog();

        $status = $this->input->get('status');
        $raw_year = $this->input->get('year');
        $selected_year = ($raw_year && is_numeric($raw_year)) ? (int)$raw_year : null;
        $selected_cluster = $this->input->get('cluster');

        $catalog = $this->M_economy->get_indicator_catalog($status, $selected_year, null, null);
        $trash_count = $this->M_economy->count_trash_catalog();
        $available_years = $this->M_economy->get_catalog_years();
        $available_clusters = $this->M_economy->get_catalog_clusters($status, $selected_year);
        $all_clusters = $this->M_economy->get_all_distinct_clusters();

        $total_all_records = 0;
        foreach ($available_years as $ay) {
            $total_all_records += (int)$ay['total_records'];
        }

        // Hitung total master indikator unik (semua tahun & semua klaster)
        $master_catalog_all = $this->M_economy->get_indicator_catalog($status, null);
        $total_master_indicators = count($master_catalog_all);

        $data = array(
            'active_page' => 'catalog',
            'title' => 'Katalog Indikator',
            'eyebrow' => 'DATA GOVERNANCE & CATALOG • CRUD & SOFT DELETE',
            'subtitle' => 'Kamus data, metadata indikator pembangunan daerah, dan manajemen master indikator.',
            'catalog' => $catalog,
            'status' => $status,
            'trash_count' => $trash_count,
            'available_years' => $available_years,
            'selected_year' => $selected_year,
            'available_clusters' => $available_clusters,
            'all_clusters' => $all_clusters,
            'selected_cluster' => $selected_cluster,
            'total_all_records' => $total_all_records,
            'total_master_indicators' => $total_master_indicators
        );

        $this->render('admin/catalog', $data, true);
    }

    public function get_data($id = null) {
        $id = (int)($id ?: $this->input->get('id'));
        $record = $this->M_economy->get_catalog_by_id($id);
        if ($record) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'success', 'data' => $record)));
        } else {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Indikator katalog tidak ditemukan.')));
        }
    }

    public function create() {
        $data = $this->input->post(null, true);
        $result = $this->M_economy->add_catalog($data);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Indikator master katalog baru berhasil ditambahkan.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menambahkan indikator: kode atau nama indikator wajib diisi atau sudah terdaftar.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Indikator katalog baru berhasil ditambahkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan indikator katalog.');
        }
        redirect('catalog');
    }

    public function update($id = null) {
        $id = (int)($id ?: $this->input->post('id') ?: $this->input->get('id'));
        $data = $this->input->post(null, true);
        $result = $this->M_economy->update_catalog($id, $data);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Data indikator katalog berhasil diperbarui.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal memperbarui indikator katalog.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data indikator katalog berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data.');
        }
        redirect('catalog');
    }

    public function soft_delete($id = null) {
        $id = (int)($id ?: $this->input->post('id') ?: $this->input->get('id'));
        $result = $this->M_economy->soft_delete_catalog($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Indikator katalog berhasil dipindahkan ke tempat sampah (Soft Delete).'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menghapus indikator katalog.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Indikator katalog berhasil dipindahkan ke tempat sampah (Soft Delete).');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus indikator.');
        }
        redirect('catalog');
    }

    public function restore($id = null) {
        $id = (int)($id ?: $this->input->post('id') ?: $this->input->get('id'));
        $result = $this->M_economy->restore_catalog($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Indikator katalog berhasil dipulihkan dari tempat sampah.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal memulihkan indikator katalog.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Indikator katalog berhasil dipulihkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memulihkan data.');
        }
        redirect('catalog?status=Terhapus');
    }

    public function hard_delete($id = null) {
        $id = (int)($id ?: $this->input->post('id') ?: $this->input->get('id'));
        $result = $this->M_economy->hard_delete_catalog($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Indikator katalog berhasil dihapus permanen dari basis data.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menghapus permanen indikator katalog.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data berhasil dihapus permanen.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data.');
        }
        redirect('catalog?status=Terhapus');
    }
}

