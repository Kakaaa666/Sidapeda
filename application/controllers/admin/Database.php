<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Database extends MY_Controller {

    public function index() {
        $status = $this->input->get('status');
        $query = $this->input->get('q');
        $sort = $this->input->get('sort') ?: 'year';

        $records = $this->M_economy->get_indicator_records($status, $query, $sort);
        $trash_count = $this->M_economy->count_trash_records();
        $kab_kota = $this->M_economy->get_daftar_kab_kota();

        $data = array(
            'active_page' => 'database',
            'title' => 'Database Ekonomi',
            'eyebrow' => 'DATA MANAGEMENT • CRUD & SOFT DELETE',
            'subtitle' => 'Kelola seluruh rekaman indikator ekonomi daerah dengan form CRUD dan pemulihan data terhapus.',
            'records' => $records,
            'status' => $status,
            'query' => $query,
            'sort' => $sort,
            'trash_count' => $trash_count,
            'kab_kota' => $kab_kota
        );

        $this->render('admin/database', $data, true);
    }

    // Mendapatkan data 1 record untuk modal edit (JSON)
    public function get_record($id) {
        $record = $this->M_economy->get_indicator_record_by_id((int)$id);
        if ($record) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'success', 'data' => $record)));
        } else {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Data tidak ditemukan.')));
        }
    }

    // Alias get_data untuk konsistensi seluruh modul
    public function get_data($id = null) {
        $this->get_record($id ?: $this->input->get('id'));
    }

    // Simpan Data Baru (Create)
    public function create() {
        $data = $this->input->post(null, true);
        $result = $this->M_economy->add_indicator_record($data);
        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 'success', 'message' => 'Data berhasil disimpan.')));
            } else {
                $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 'error', 'message' => 'Data dengan kode dan tahun tersebut sudah ada.')));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data berhasil ditambahkan ke database.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan data: kode dan tahun sudah ada.');
        }
        redirect('database');
    }

    // Simpan Pembaruan Data (Update)
    public function update($id) {
        $data = $this->input->post(null, true);
        $result = $this->M_economy->update_indicator_record((int)$id, $data);
        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 'success', 'message' => 'Data berhasil diperbarui.')));
            } else {
                $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 'error', 'message' => 'Gagal memperbarui data.')));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data.');
        }
        redirect('database');
    }

    // Hapus Data Menggunakan Soft Delete (Delete -> Soft Delete)
    public function soft_delete($id) {
        $result = $this->M_economy->soft_delete_indicator_record((int)$id);
        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 'success', 'message' => 'Data berhasil dipindahkan ke tempat sampah (Soft Delete).')));
            } else {
                $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 'error', 'message' => 'Gagal menghapus data.')));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data berhasil dipindahkan ke tempat sampah (Soft Delete). Anda dapat memulihkannya kapan saja.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memproses soft delete data.');
        }
        redirect('database');
    }

    // Pulihkan Data dari Soft Delete (Restore)
    public function restore($id) {
        $result = $this->M_economy->restore_indicator_record((int)$id);
        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 'success', 'message' => 'Data berhasil dipulihkan.')));
            } else {
                $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 'error', 'message' => 'Gagal memulihkan data.')));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data berhasil dipulihkan kembali ke daftar aktif.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memulihkan data.');
        }
        redirect('database?status=Terhapus');
    }

    // Hapus Permanen
    public function hard_delete($id) {
        $result = $this->M_economy->hard_delete_indicator_record((int)$id);
        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 'success', 'message' => 'Data berhasil dihapus permanen.')));
            } else {
                $this->output->set_content_type('application/json')->set_output(json_encode(array('status' => 'error', 'message' => 'Gagal menghapus data permanen.')));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data berhasil dihapus secara permanen.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus permanen.');
        }
        redirect('database?status=Terhapus');
    }
}
