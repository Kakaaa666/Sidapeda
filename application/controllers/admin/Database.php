<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Database extends MY_Controller {

    public function index() {
        $status = $this->input->get('status');
        $query = $this->input->get('q');
        $sort = $this->input->get('sort') ?: 'code';
        $cluster = $this->input->get('cluster');
        $year = $this->input->get('year');
        $selected_year = ($year && is_numeric($year)) ? (int)$year : null;
        $category = $this->input->get('category') ?: $this->input->get('prefix');
        $selected_category = $category ? strtoupper(trim($category)) : null;

        $records = $this->M_economy->get_indicator_records($status, $query, $sort, $selected_year, null, $cluster);
        $trash_count = $this->M_economy->count_trash_records();
        $kab_kota = $this->M_economy->get_daftar_kab_kota();
        $clusters = $this->M_economy->get_database_clusters();
        $all_clusters = $this->M_economy->get_all_distinct_clusters();
        $available_years = $this->M_economy->get_catalog_years();
        $catalog_indicators = $this->M_economy->get_indicator_catalog(null, null);

        $data = array(
            'active_page' => 'database',
            'title' => 'Database Ekonomi',
            'eyebrow' => 'DATA MANAGEMENT',
            'subtitle' => 'Kelola seluruh rekaman indikator ekonomi daerah dan pemulihan data terhapus.',
            'records' => $records,
            'status' => $status,
            'query' => $query,
            'sort' => $sort,
            'cluster' => $cluster,
            'selected_cluster' => $cluster,
            'category' => $selected_category,
            'selected_category' => $selected_category,
            'year' => $year,
            'selected_year' => $selected_year,
            'available_years' => $available_years,
            'clusters' => $clusters,
            'all_clusters' => $all_clusters,
            'catalog_indicators' => $catalog_indicators,
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

    // Mendapatkan seluruh deret data tahunan untuk 1 indikator (Multi-year series JSON)
    public function get_series($code = null) {
        $code = $code ?: $this->input->get('code');
        if (empty($code)) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Kode indikator wajib disertakan.')));
            return;
        }

        $series = $this->M_economy->get_indicator_series($code);
        if ($series) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'success', 'data' => $series)));
        } else {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Data indikator tidak ditemukan.')));
        }
    }

    // Perbarui Seluruh Deret Tahun untuk 1 Indikator Sekaligus (Multi-year Series Update)
    public function update_series() {
        $oldCode = $this->input->post('old_code', true);
        $code = $this->input->post('code', true);
        if (empty($code)) {
            $code = $oldCode;
        }
        $seriesValues = $this->input->post('series_values');
        $seriesYears = $this->input->post('series_years');
        $kodeWilayah = $this->input->post('kode_wilayah', true);

        if (empty($code)) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Kode indikator tidak boleh kosong.')));
            return;
        }

        if (!empty($seriesYears) && is_array($seriesYears)) {
            $seriesData = array(
                'years' => $seriesYears,
                'values' => $seriesValues
            );
        } elseif (is_array($seriesValues)) {
            $seriesData = $seriesValues;
        } else {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Format deret nilai tahunan tidak valid.')));
            return;
        }

        $meta = array(
            'old_code'   => $oldCode,
            'indicator'  => $this->input->post('indicator', true),
            'cluster'    => $this->input->post('cluster', true),
            'unit'       => $this->input->post('unit', true),
            'source'     => $this->input->post('source', true),
            'definition' => $this->input->post('definition', true)
        );

        $savedCount = $this->M_economy->update_indicator_series($code, $seriesData, $kodeWilayah, $meta);

        if ($this->input->is_ajax_request()) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Berhasil memperbarui data indikator ' . $code . '.'
                )));
            return;
        }

        $this->session->set_flashdata('success', 'Berhasil memperbarui data indikator ' . $code . '.');
        redirect('database');
    }

    // Ambil data matriks sektoral / komponen pengeluaran JSON
    public function get_sectoral_matrix() {
        $kodeWilayah = $this->input->get('kode_wilayah') ?: null;
        $parentCode = $this->input->get('parent_code') ?: 'EKO-003';
        $matrix = $this->M_economy->get_sectoral_matrix_data($kodeWilayah, $parentCode);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array('status' => 'success', 'data' => $matrix)));
    }

    // Simpan seluruh data matriks sektoral / komponen pengeluaran sekaligus (termasuk nama & label kategori yang diubah)
    public function save_sectoral_matrix() {
        $years = $this->input->post('years');
        $matrixValues = $this->input->post('matrix');
        $names = $this->input->post('names');
        $catLabels = $this->input->post('cat_labels');
        $kodeWilayah = $this->input->post('kode_wilayah', true);
        $parentCode = $this->input->post('parent_code', true) ?: 'EKO-003';

        if (empty($years) || !is_array($years) || empty($matrixValues) || !is_array($matrixValues)) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Data matriks tidak lengkap.')));
            return;
        }

        $res = $this->M_economy->save_sectoral_matrix_data($matrixValues, $years, $kodeWilayah, $parentCode, $names, $catLabels);
        if ($res) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Berhasil menyimpan seluruh data rincian kategori untuk indikator ' . $parentCode . '.'
                )));
        } else {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Gagal menyimpan data sektoral ke database.')));
        }
    }

    // Soft delete seluruh data indikator
    public function soft_delete_series($code = null) {
        $code = $code ?: $this->input->post('code');
        if (empty($code)) {
            $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array('status' => 'error', 'message' => 'Kode indikator kosong.')));
            return;
        }

        $result = $this->M_economy->soft_delete_indicator_series($code);
        if ($this->input->is_ajax_request()) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status' => $result ? 'success' : 'error',
                'message' => $result ? 'Seluruh data indikator dipindahkan ke tempat sampah.' : 'Gagal menghapus data.'
            )));
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Indikator ' . $code . ' dipindahkan ke tempat sampah.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memproses soft delete.');
        }
        redirect('database');
    }

    // Pulihkan seluruh data indikator dari tempat sampah
    public function restore_series($code = null) {
        $code = $code ?: $this->input->post('code');
        if (empty($code)) {
            $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array('status' => 'error', 'message' => 'Kode indikator kosong.')));
            return;
        }

        $result = $this->M_economy->restore_indicator_series($code);
        if ($this->input->is_ajax_request()) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status' => $result ? 'success' : 'error',
                'message' => $result ? 'Seluruh data indikator berhasil dipulihkan.' : 'Gagal memulihkan data.'
            )));
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Indikator ' . $code . ' berhasil dipulihkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memulihkan data.');
        }
        redirect('database?status=Terhapus');
    }

    // Hapus permanen seluruh data indikator
    public function hard_delete_series($code = null) {
        $code = $code ?: $this->input->post('code');
        if (empty($code)) {
            $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array('status' => 'error', 'message' => 'Kode indikator kosong.')));
            return;
        }

        $result = $this->M_economy->hard_delete_indicator_series($code);
        if ($this->input->is_ajax_request()) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'status' => $result ? 'success' : 'error',
                'message' => $result ? 'Seluruh data indikator berhasil dihapus permanen.' : 'Gagal menghapus data.'
            )));
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Indikator ' . $code . ' berhasil dihapus permanen.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data permanen.');
        }
        redirect('database?status=Terhapus');
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
