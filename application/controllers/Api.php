<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('M_economy');
        $this->load->model('M_auth');
    }

    private function json_response($data, $status = 200) {
        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function get_data() {
        $series = $this->M_economy->get_economic_series();
        $sectors = $this->M_economy->get_sectors();
        $regional = $this->M_economy->get_regional_growth();
        $districts = $this->M_economy->get_districts();
        $records = $this->M_economy->get_indicator_records();
        $catalog = $this->M_economy->get_indicator_catalog();
        $ews = $this->M_economy->get_ews_indicators();
        $user = $this->M_auth->current_user();

        $this->json_response(array(
            'status' => 'success',
            'years' => $this->M_economy->get_years(),
            'series' => $series,
            'sectors' => $sectors,
            'regional' => $regional,
            'districts' => $districts,
            'records' => $records,
            'catalog' => $catalog,
            'ews' => $ews,
            'user' => $user
        ));
    }

    public function update_threshold() {
        $code = $this->input->post('code');
        $threshold = $this->input->post('threshold');
        $direction = $this->input->post('direction');
        $value = $this->input->post('value');

        if (!$code || !is_numeric($threshold)) {
            $this->json_response(array('status' => 'error', 'message' => 'Kode dan threshold numerik diperlukan.'), 400);
            return;
        }

        $val = (is_numeric($value)) ? (float)$value : null;
        $res = $this->M_economy->update_ews_threshold($code, $threshold, $direction, $val);
        $this->json_response(array(
            'status' => 'success',
            'updated' => $res['success'],
            'value' => $res['value'],
            'threshold' => $res['threshold'],
            'direction' => $res['direction'],
            'status_risiko' => $res['status']
        ));
    }

    public function toggle_followup() {
        $code = $this->input->post('code');
        if (!$code) {
            $this->json_response(array('status' => 'error', 'message' => 'Kode diperlukan.'), 400);
            return;
        }

        $val = $this->M_economy->toggle_ews_followup($code);
        $this->json_response(array('status' => 'success', 'is_followed_up' => $val));
    }

    public function add_record() {
        $active_kw = $this->M_economy->get_active_kode_wilayah();
        $code = trim($this->input->post('code'));
        $indicator = trim($this->input->post('indicator'));
        $cluster = trim($this->input->post('cluster')) ?: 'Output & Pertumbuhan';
        $kode_wilayah = trim($this->input->post('kode_wilayah')) ?: $active_kw;
        $region = trim($this->input->post('region')) ?: $this->M_economy->get_nama_wilayah($kode_wilayah);
        $period = trim($this->input->post('period')) ?: 'Tahunan';
        $year = (int)$this->input->post('year');
        $value = (float)$this->input->post('value');
        $unit = trim($this->input->post('unit')) ?: '%';
        $source = trim($this->input->post('source')) ?: 'Input admin';
        $status = trim($this->input->post('status')) ?: 'Tervalidasi';

        if (!$code || !$indicator || !$year || !is_numeric($this->input->post('value'))) {
            $this->json_response(array('status' => 'error', 'message' => 'Kode, indikator, tahun, dan nilai wajib diisi dengan benar.'), 400);
            return;
        }

        $inserted = $this->M_economy->add_indicator_record(array(
            'code' => $code,
            'indicator' => $indicator,
            'cluster' => $cluster,
            'region' => $region,
            'kode_wilayah' => $kode_wilayah,
            'period' => $period,
            'year' => $year,
            'value' => $value,
            'unit' => $unit,
            'source' => $source,
            'status' => $status
        ));

        if (!$inserted) {
            $this->json_response(array('status' => 'error', 'message' => 'Kombinasi kode indikator dan tahun tersebut sudah ada di database.'), 409);
            return;
        }

        $this->json_response(array('status' => 'success', 'message' => 'Data indikator berhasil ditambahkan.'));
    }

    public function update_status() {
        $code = $this->input->post('code');
        $year = (int)$this->input->post('year');
        $status = $this->input->post('status');
        $notes = $this->input->post('notes');

        if (!in_array($status, array('Tervalidasi', 'Perlu review', 'Belum lengkap', 'Ditolak'))) {
            $this->json_response(array('status' => 'error', 'message' => 'Status tidak valid.'), 400);
            return;
        }

        $user = $this->session->userdata('user');
        $verifier = !empty($user['nama_lengkap']) ? $user['nama_lengkap'] : (!empty($user['name']) ? $user['name'] : 'Admin Verifikator');

        $this->M_economy->update_record_status($code, $year, $status, $notes, $verifier);
        $this->json_response(array('status' => 'success', 'message' => 'Status data berhasil diperbarui.'));
    }

    public function update_series_status() {
        $code = $this->input->post('code');
        $status = $this->input->post('status') ?: 'Tervalidasi';
        $notes = $this->input->post('notes');

        if (!in_array($status, array('Tervalidasi', 'Perlu review', 'Belum lengkap', 'Ditolak'))) {
            $this->json_response(array('status' => 'error', 'message' => 'Status tidak valid.'), 400);
            return;
        }

        $user = $this->session->userdata('user');
        $verifier = !empty($user['nama_lengkap']) ? $user['nama_lengkap'] : (!empty($user['name']) ? $user['name'] : 'Admin Verifikator');

        $this->M_economy->update_series_status($code, $status, $notes, $verifier);
        $this->json_response(array('status' => 'success', 'message' => 'Seluruh tahun untuk indikator ini berhasil divalidasi.'));
    }

    public function import_csv() {
        // Accepts JSON payload of preview rows or raw CSV
        $rows = json_decode($this->input->raw_input_stream, true);
        if (!is_array($rows) || empty($rows)) {
            $this->json_response(array('status' => 'error', 'message' => 'Tidak ada baris data CSV yang diterima.'), 400);
            return;
        }

        $active_kw = $this->M_economy->get_active_kode_wilayah();
        $imported = 0;
        $skipped = 0;

        foreach ($rows as $item) {
            if (empty($item['indicator']) || !isset($item['value']) || !is_numeric($item['value'])) {
                $skipped++;
                continue;
            }

            $code = !empty($item['code']) ? trim($item['code']) : ('IMP-' . rand(100, 999));
            $year = !empty($item['year']) ? (int)$item['year'] : 2024;
            $kode_wilayah = !empty($item['kode_wilayah']) ? trim($item['kode_wilayah']) : $active_kw;
            $region = !empty($item['region']) ? trim($item['region']) : $this->M_economy->get_nama_wilayah($kode_wilayah);

            $ok = $this->M_economy->add_indicator_record(array(
                'code' => $code,
                'indicator' => trim($item['indicator']),
                'cluster' => !empty($item['cluster']) ? trim($item['cluster']) : 'Output & Pertumbuhan',
                'region' => $region,
                'kode_wilayah' => $kode_wilayah,
                'period' => !empty($item['period']) ? trim($item['period']) : 'Tahunan',
                'year' => $year,
                'value' => (float)$item['value'],
                'unit' => !empty($item['unit']) ? trim($item['unit']) : '%',
                'source' => !empty($item['source']) ? trim($item['source']) : 'Import CSV',
                'status' => 'Perlu review'
            ));

            if ($ok) {
                $imported++;
            } else {
                $skipped++;
            }
        }

        $this->json_response(array(
            'status' => 'success',
            'imported' => $imported,
            'skipped' => $skipped,
            'message' => "Import berhasil diproses: {$imported} data ditambahkan, {$skipped} data dilewati."
        ));
    }

    public function update_user() {
        $id = (int)$this->input->post('id');
        $role = $this->input->post('role');
        $level = $this->input->post('level') ?: $role;
        $status = $this->input->post('status');

        if ($level) {
            $this->M_auth->update_user_level($id, $level);
        }
        if ($status) {
            $this->M_auth->update_user_status($id, $status);
        }

        $this->json_response(array('status' => 'success', 'message' => 'Level dan status akun berhasil diperbarui.'));
    }

    public function get_record($id = null) {
        $id = (int)($id ?: $this->input->get('id'));
        $record = $this->M_economy->get_indicator_record_by_id($id);
        if ($record) {
            $this->json_response(array('status' => 'success', 'data' => $record));
        } else {
            $this->json_response(array('status' => 'error', 'message' => 'Data tidak ditemukan.'), 404);
        }
    }

    public function update_record($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        if (!$id) {
            $this->json_response(array('status' => 'error', 'message' => 'ID data wajib diisi.'), 400);
            return;
        }

        $data = $this->input->post(null, true);
        $ok = $this->M_economy->update_indicator_record($id, $data);
        if ($ok) {
            $this->json_response(array('status' => 'success', 'message' => 'Data berhasil diperbarui.'));
        } else {
            $this->json_response(array('status' => 'error', 'message' => 'Gagal memperbarui data.'), 500);
        }
    }

    public function delete_record($id = null) {
        // Soft delete endpoint
        $id = (int)($id ?: $this->input->post('id'));
        if (!$id) {
            $this->json_response(array('status' => 'error', 'message' => 'ID data wajib diisi.'), 400);
            return;
        }

        $ok = $this->M_economy->soft_delete_indicator_record($id);
        if ($ok) {
            $this->json_response(array('status' => 'success', 'message' => 'Data berhasil dipindahkan ke tempat sampah (Soft Delete).'));
        } else {
            $this->json_response(array('status' => 'error', 'message' => 'Gagal menghapus data.'), 500);
        }
    }

    public function restore_record($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        if (!$id) {
            $this->json_response(array('status' => 'error', 'message' => 'ID data wajib diisi.'), 400);
            return;
        }

        $ok = $this->M_economy->restore_indicator_record($id);
        if ($ok) {
            $this->json_response(array('status' => 'success', 'message' => 'Data berhasil dipulihkan kembali.'));
        } else {
            $this->json_response(array('status' => 'error', 'message' => 'Gagal memulihkan data.'), 500);
        }
    }

    public function hard_delete_record($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        if (!$id) {
            $this->json_response(array('status' => 'error', 'message' => 'ID data wajib diisi.'), 400);
            return;
        }

        $ok = $this->M_economy->hard_delete_indicator_record($id);
        if ($ok) {
            $this->json_response(array('status' => 'success', 'message' => 'Data berhasil dihapus permanen.'));
        } else {
            $this->json_response(array('status' => 'error', 'message' => 'Gagal menghapus data permanen.'), 500);
        }
    }

    // REGIONAL DATA (Perbandingan Daerah) CRUD
    public function get_regional($id = null) {
        $id = (int)($id ?: $this->input->get('id'));
        $record = $this->M_economy->get_regional_by_id($id);
        if ($record) {
            $this->json_response(array('status' => 'success', 'data' => $record));
        } else {
            $this->json_response(array('status' => 'error', 'message' => 'Data wilayah tidak ditemukan.'), 404);
        }
    }

    public function add_regional() {
        $data = $this->input->post(null, true);
        $ok = $this->M_economy->add_regional($data);
        if ($ok) {
            $this->json_response(array('status' => 'success', 'message' => 'Data wilayah berhasil ditambahkan.'));
        } else {
            $this->json_response(array('status' => 'error', 'message' => 'Gagal menambahkan wilayah: nama wilayah sudah terdaftar atau kosong.'), 400);
        }
    }

    public function update_regional($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $data = $this->input->post(null, true);
        $ok = $this->M_economy->update_regional($id, $data);
        if ($ok) {
            $this->json_response(array('status' => 'success', 'message' => 'Data wilayah berhasil diperbarui.'));
        } else {
            $this->json_response(array('status' => 'error', 'message' => 'Gagal memperbarui data wilayah.'), 500);
        }
    }

    public function delete_regional($id = null) {
        // Soft delete regional
        $id = (int)($id ?: $this->input->post('id'));
        $ok = $this->M_economy->soft_delete_regional($id);
        if ($ok) {
            $this->json_response(array('status' => 'success', 'message' => 'Data wilayah berhasil dipindahkan ke tempat sampah (Soft Delete).'));
        } else {
            $this->json_response(array('status' => 'error', 'message' => 'Gagal memproses soft delete wilayah.'), 500);
        }
    }

    public function restore_regional($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $ok = $this->M_economy->restore_regional($id);
        if ($ok) {
            $this->json_response(array('status' => 'success', 'message' => 'Data wilayah berhasil dipulihkan.'));
        } else {
            $this->json_response(array('status' => 'error', 'message' => 'Gagal memulihkan wilayah.'), 500);
        }
    }

    public function hard_delete_regional($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $ok = $this->M_economy->hard_delete_regional($id);
        if ($ok) {
            $this->json_response(array('status' => 'success', 'message' => 'Data wilayah berhasil dihapus permanen.'));
        } else {
            $this->json_response(array('status' => 'error', 'message' => 'Gagal menghapus wilayah permanen.'), 500);
        }
    }
}

