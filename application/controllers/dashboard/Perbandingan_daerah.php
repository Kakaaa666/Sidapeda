<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Perbandingan_daerah extends MY_Controller {

    public function index() {
        $status = $this->input->get('status');
        $years = $this->M_economy->get_years();
        $regional = $this->M_economy->get_regional_growth($status);
        $all_active_regional = $this->M_economy->get_regional_growth(); // Selalu untuk ranking/chart
        $series = $this->M_economy->get_economic_series();
        $sectors = $this->M_economy->get_sectors();
        $trash_count = $this->M_economy->count_trash_regional();

        $data = array(
            'active_page' => 'analisis_ekonomi',
            'title' => 'Perbandingan Antar Daerah',
            'eyebrow' => 'REGIONAL BENCHMARKING',
            'subtitle' => 'Bandingkan posisi dan daya saing ekonomi antar-wilayah kabupaten/kota di Jawa Timur.',
            'years' => $years,
            'regional' => $regional,
            'all_active_regional' => $all_active_regional,
            'series' => $series,
            'sectors' => $sectors,
            'status' => $status,
            'trash_count' => $trash_count
        );

        $this->render('dashboard/perbandingan_daerah', $data, false);
    }

    // Mengambil 1 data wilayah untuk modal edit (JSON)
    public function get_data($id = null) {
        $id = (int)($id ?: $this->input->get('id'));
        $record = $this->M_economy->get_regional_by_id($id);
        if ($record) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'success', 'data' => $record)));
        } else {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Data wilayah tidak ditemukan.')));
        }
    }

    // Tambah Data Wilayah Baru (Create)
    public function create() {
        $data = $this->input->post(null, true);
        $result = $this->M_economy->add_regional($data);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Data wilayah perbandingan berhasil ditambahkan.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menambahkan wilayah: Nama wilayah sudah ada atau belum diisi.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data wilayah perbandingan berhasil ditambahkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan data wilayah: nama wilayah sudah terdaftar.');
        }
        redirect('perbandingan_daerah');
    }

    // Perbarui Data Wilayah (Update)
    public function update($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $data = $this->input->post(null, true);
        $result = $this->M_economy->update_regional($id, $data);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Data wilayah berhasil diperbarui.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal memperbarui data wilayah.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data wilayah berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data wilayah.');
        }
        redirect('perbandingan_daerah');
    }

    // Hapus Data Wilayah secara Soft Delete (Delete -> Soft Delete)
    public function soft_delete($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_economy->soft_delete_regional($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Wilayah berhasil dipindahkan ke tempat sampah (Soft Delete).'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menghapus data wilayah.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data wilayah berhasil dipindahkan ke tempat sampah (Soft Delete). Anda dapat memulihkannya kapan saja.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memproses soft delete wilayah.');
        }
        redirect('perbandingan_daerah');
    }

    // Pulihkan Wilayah dari Tempat Sampah (Restore)
    public function restore($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_economy->restore_regional($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Wilayah berhasil dipulihkan kembali ke daftar aktif.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal memulihkan wilayah.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Wilayah berhasil dipulihkan kembali ke daftar aktif.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memulihkan data wilayah.');
        }
        redirect('perbandingan_daerah?status=Terhapus');
    }

    // Hapus Permanen (Hard Delete)
    public function hard_delete($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_economy->hard_delete_regional($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Wilayah berhasil dihapus permanen dari database.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menghapus data wilayah secara permanen.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data wilayah berhasil dihapus permanen.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus wilayah secara permanen.');
        }
        redirect('perbandingan_daerah?status=Terhapus');
    }
}
