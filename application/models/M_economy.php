<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_economy extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_active_kode_wilayah($override = null) {
        if (!empty($override)) {
            return $override;
        }
        $ci =& get_instance();
        if (isset($ci->session)) {
            $user = $ci->session->userdata('user_logged');
            if (!empty($user['kode_wilayah'])) {
                return $user['kode_wilayah'];
            }
            $sessKode = $ci->session->userdata('active_kode_wilayah');
            if (!empty($sessKode)) {
                return $sessKode;
            }
        }
        return '35.10'; // Default Banyuwangi
    }

    public function get_nama_wilayah($kode = null) {
        if (empty($kode)) {
            $kode = $this->get_active_kode_wilayah();
        }
        if ($kode === '35.10') {
            return 'Kabupaten Banyuwangi';
        }
        if ($this->db->table_exists('kodewilayah')) {
            $row = $this->db->select('Nama')->where('Kode', $kode)->limit(1)->get('kodewilayah')->row_array();
            if ($row && !empty($row['Nama'])) {
                $nama = ucwords(strtolower($row['Nama']));
                $nama = str_replace('Kab.', 'Kabupaten', $nama);
                return $nama;
            }
        }
        return 'Wilayah ' . $kode;
    }

    public function get_daftar_kab_kota() {
        if (!$this->db->table_exists('kodewilayah')) {
            return array(
                array('kode' => '35.10', 'nama' => 'Kabupaten Banyuwangi', 'raw_nama' => 'KAB. BANYUWANGI')
            );
        }
        $rows = $this->db->select('Kode as kode, Nama as nama')
            ->like('Kode', '35.', 'after')
            ->where('LENGTH(Kode)', 5)
            ->group_by('Kode')
            ->order_by('Kode', 'ASC')
            ->get('kodewilayah')
            ->result_array();
        
        $result = array();
        foreach ($rows as $r) {
            $namaFormatted = ucwords(strtolower($r['nama']));
            $namaFormatted = str_replace('Kab.', 'Kabupaten', $namaFormatted);
            $result[] = array(
                'kode' => $r['kode'],
                'nama' => $namaFormatted,
                'raw_nama' => $r['nama']
            );
        }
        return $result;
    }

    public function get_years($kode_wilayah = null) {
        $kode = $this->get_active_kode_wilayah($kode_wilayah);
        $query = $this->db->select('DISTINCT(tahun) as th')
            ->where('is_deleted', 0)
            ->where('status_validasi !=', 'Ditolak')
            ->where('kode_wilayah', $kode)
            ->order_by('tahun', 'ASC')
            ->get('database_ekonomi');
        if ($query->num_rows() > 0) {
            $years = array();
            foreach ($query->result() as $row) {
                $years[] = (int)$row->th;
            }
            return $years;
        }
        return array();
    }

    public function get_economic_series($kode_wilayah = null) {
        $kode = $this->get_active_kode_wilayah($kode_wilayah);
        $query = $this->db->where('kode_wilayah', $kode)->order_by('tahun', 'ASC')->get('prospek_ekonomi');
        if ($query->num_rows() > 0) {
            $growth = array();
            $eastJavaGrowth = array();
            $nationalGrowth = array();
            $tpt = array();
            $investmentGrowth = array();
            $poverty = array();
            $inflation = array();
            $ipm = array();
            $perCapita = array();

            foreach ($query->result() as $row) {
                $growth[] = (float)$row->pertumbuhan_ekonomi;
                $eastJavaGrowth[] = (float)$row->pertumbuhan_jatim;
                $nationalGrowth[] = (float)$row->pertumbuhan_nasional;
                $tpt[] = (float)$row->tingkat_pengangguran;
                $investmentGrowth[] = (float)$row->pertumbuhan_investasi;
                $poverty[] = (float)$row->tingkat_kemiskinan;
                $inflation[] = (float)$row->inflasi;
                $ipm[] = (float)$row->ipm;
                $perCapita[] = (float)$row->pdrb_per_kapita;
            }

            return array(
                'growth' => $growth,
                'eastJavaGrowth' => $eastJavaGrowth,
                'nationalGrowth' => $nationalGrowth,
                'tpt' => $tpt,
                'investmentGrowth' => $investmentGrowth,
                'poverty' => $poverty,
                'inflation' => $inflation,
                'ipm' => $ipm,
                'perCapita' => $perCapita
            );
        }

        if ($kode === '35.10') {
            return array(
                'growth' => array(5.02, 5.15, 5.28, 5.61, 5.55, -3.58, 4.08, 4.43, 5.03, 5.18),
                'eastJavaGrowth' => array(5.44, 5.46, 5.45, 5.50, 5.52, -2.33, 3.56, 5.34, 4.95, 4.93),
                'nationalGrowth' => array(4.88, 5.03, 5.07, 5.17, 5.02, -2.07, 3.70, 5.31, 5.05, 5.03),
                'tpt' => array(3.20, 3.12, 3.05, 2.94, 3.84, 5.50, 5.10, 4.30, 4.10, 4.03),
                'investmentGrowth' => array(6.1, 7.2, 8.0, 8.8, 9.4, -2.0, 4.2, 8.8, 14.2, 12.42),
                'poverty' => array(9.17, 8.75, 8.64, 8.32, 7.52, 8.06, 7.45, 7.51, 7.34, 7.34),
                'inflation' => array(3.12, 2.71, 3.61, 2.88, 2.94, 1.68, 1.87, 6.12, 2.81, 2.41),
                'ipm' => array(68.11, 68.63, 69.13, 69.64, 70.06, 70.62, 71.26, 72.45, 73.08, 73.79),
                'perCapita' => array(35.2, 37.1, 39.4, 42.8, 45.9, 43.7, 47.6, 53.2, 58.4, 62.4)
            );
        }

        return array(
            'growth' => array(),
            'eastJavaGrowth' => array(),
            'nationalGrowth' => array(),
            'tpt' => array(),
            'investmentGrowth' => array(),
            'poverty' => array(),
            'inflation' => array(),
            'ipm' => array(),
            'perCapita' => array()
        );
    }

    public function get_sectors($status = null, $kode_wilayah = null) {
        $kode = $this->get_active_kode_wilayah($kode_wilayah);
        if ($status === 'Terhapus' || $status === 'Sampah') {
            $this->db->where('is_deleted', 1);
        } else {
            $this->db->where('is_deleted', 0);
        }
        $this->db->where('kode_wilayah', $kode);
        $query = $this->db->get('analisis_ekonomi');
        if ($query->num_rows() > 0) {
            $sectors = array();
            foreach ($query->result() as $row) {
                $sectors[] = array(
                    'key' => $row->kode_sektor,
                    'kode_sektor' => $row->kode_sektor,
                    'name' => $row->nama_sektor,
                    'nama_sektor' => $row->nama_sektor,
                    'short' => $row->nama_pendek,
                    'nama_pendek' => $row->nama_pendek,
                    'shareLocal' => (float)$row->porsi_daerah,
                    'porsi_daerah' => (float)$row->porsi_daerah,
                    'shareProvince' => (float)$row->porsi_provinsi,
                    'porsi_provinsi' => (float)$row->porsi_provinsi,
                    'growthLocal' => (float)$row->pertumbuhan_daerah,
                    'pertumbuhan_daerah' => (float)$row->pertumbuhan_daerah,
                    'growthProvince' => (float)$row->pertumbuhan_provinsi,
                    'pertumbuhan_provinsi' => (float)$row->pertumbuhan_provinsi,
                    'employmentBase' => (int)$row->tenaga_kerja_awal,
                    'tenaga_kerja_awal' => (int)$row->tenaga_kerja_awal,
                    'employmentCurrent' => (int)$row->tenaga_kerja_kini,
                    'tenaga_kerja_kini' => (int)$row->tenaga_kerja_kini,
                    'provinceEmploymentBase' => (int)$row->tenaga_kerja_provinsi_awal,
                    'tenaga_kerja_provinsi_awal' => (int)$row->tenaga_kerja_provinsi_awal,
                    'provinceEmploymentCurrent' => (int)$row->tenaga_kerja_provinsi_kini,
                    'tenaga_kerja_provinsi_kini' => (int)$row->tenaga_kerja_provinsi_kini,
                    'growthSeries' => json_decode($row->deret_pertumbuhan, true) ?: array(),
                    'growthProvinceSeries' => json_decode($row->deret_pertumbuhan_provinsi, true) ?: array(),
                    'id' => (int)$row->id,
                    'kode_wilayah' => $row->kode_wilayah ?? $kode,
                    'is_deleted' => (int)($row->is_deleted ?? 0),
                    'deleted_at' => $row->deleted_at ?? null
                );
            }
            return $sectors;
        }

        if ($kode === '35.10') {
            return array(
                array('key' => 'agri', 'name' => 'Pertanian, Kehutanan & Perikanan', 'short' => 'Pertanian', 'shareLocal' => 28.5, 'shareProvince' => 11.7, 'growthLocal' => 4.3, 'growthProvince' => 3.1, 'employmentBase' => 18000, 'employmentCurrent' => 20500, 'provinceEmploymentBase' => 1050000, 'provinceEmploymentCurrent' => 1120000, 'growthSeries' => array(3.9, 4.1, 4.4, 4.2, 3.8, 2.1, 3.0, 3.6, 4.0, 4.3), 'growthProvinceSeries' => array(3.7, 3.3, 3.4, 3.5, 3.4, -1.2, 2.0, 3.1, 3.0, 3.1)),
                array('key' => 'trade', 'name' => 'Perdagangan Besar & Eceran', 'short' => 'Perdagangan', 'shareLocal' => 18.5, 'shareProvince' => 18.2, 'growthLocal' => 5.4, 'growthProvince' => 5.2, 'employmentBase' => 7000, 'employmentCurrent' => 9800, 'provinceEmploymentBase' => 680000, 'provinceEmploymentCurrent' => 780000, 'growthSeries' => array(5.0, 5.2, 5.4, 5.8, 5.7, -4.2, 4.3, 5.0, 5.2, 5.4), 'growthProvinceSeries' => array(4.4, 5.0, 5.2, 5.4, 5.3, -4.1, 4.0, 5.1, 5.0, 5.2)),
                array('key' => 'manufacture', 'name' => 'Industri Pengolahan', 'short' => 'Industri', 'shareLocal' => 12.2, 'shareProvince' => 30.5, 'growthLocal' => 4.1, 'growthProvince' => 4.8, 'employmentBase' => 5200, 'employmentCurrent' => 7200, 'provinceEmploymentBase' => 520000, 'provinceEmploymentCurrent' => 600000, 'growthSeries' => array(4.0, 4.2, 4.4, 4.8, 4.7, -2.8, 3.1, 4.0, 4.0, 4.1), 'growthProvinceSeries' => array(5.0, 4.8, 4.6, 4.8, 4.7, -2.7, 3.2, 4.2, 4.6, 4.8)),
                array('key' => 'accommodation', 'name' => 'Akomodasi & Makan Minum', 'short' => 'Akomodasi', 'shareLocal' => 9.1, 'shareProvince' => 4.2, 'growthLocal' => 7.3, 'growthProvince' => 6.4, 'employmentBase' => 2100, 'employmentCurrent' => 5600, 'provinceEmploymentBase' => 110000, 'provinceEmploymentCurrent' => 190000, 'growthSeries' => array(6.0, 6.4, 6.9, 7.1, 6.8, -8.0, 5.9, 9.2, 7.0, 7.3), 'growthProvinceSeries' => array(6.5, 6.7, 6.9, 7.2, 6.8, -8.4, 5.4, 8.0, 6.5, 6.4)),
                array('key' => 'construction', 'name' => 'Konstruksi', 'short' => 'Konstruksi', 'shareLocal' => 8.0, 'shareProvince' => 9.2, 'growthLocal' => 4.8, 'growthProvince' => 5.1, 'employmentBase' => 4300, 'employmentCurrent' => 6500, 'provinceEmploymentBase' => 450000, 'provinceEmploymentCurrent' => 530000, 'growthSeries' => array(5.1, 5.3, 5.2, 5.4, 5.3, -3.9, 3.3, 4.5, 4.6, 4.8), 'growthProvinceSeries' => array(5.3, 5.2, 5.1, 5.4, 5.1, -4.2, 2.8, 5.5, 5.0, 5.1)),
                array('key' => 'transport', 'name' => 'Transportasi & Pergudangan', 'short' => 'Transportasi', 'shareLocal' => 5.0, 'shareProvince' => 5.2, 'growthLocal' => 5.1, 'growthProvince' => 5.0, 'employmentBase' => 2600, 'employmentCurrent' => 3600, 'provinceEmploymentBase' => 230000, 'provinceEmploymentCurrent' => 280000, 'growthSeries' => array(4.7, 4.9, 5.2, 5.4, 5.2, -7.2, 4.2, 7.8, 5.3, 5.1), 'growthProvinceSeries' => array(5.8, 5.5, 5.2, 5.5, 5.4, -8.0, 3.3, 8.0, 5.1, 5.0)),
                array('key' => 'services', 'name' => 'Jasa Lainnya', 'short' => 'Jasa lainnya', 'shareLocal' => 18.7, 'shareProvince' => 21.0, 'growthLocal' => 5.0, 'growthProvince' => 5.1, 'employmentBase' => 9000, 'employmentCurrent' => 13000, 'provinceEmploymentBase' => 850000, 'provinceEmploymentCurrent' => 1050000, 'growthSeries' => array(4.8, 4.9, 5.0, 5.1, 5.0, -1.8, 3.7, 4.8, 4.9, 5.0), 'growthProvinceSeries' => array(5.1, 5.0, 4.9, 5.0, 5.0, -2.0, 3.8, 4.8, 5.0, 5.1))
            );
        }

        return array();
    }

    public function get_regional_growth($status = null, $kode_wilayah = null) {
        $kode = $this->get_active_kode_wilayah($kode_wilayah);
        if ($status === 'Terhapus' || $status === 'Sampah') {
            $this->db->where('is_deleted', 1);
        } else {
            $this->db->where('is_deleted', 0);
        }
        $this->db->where('kode_wilayah', $kode);
        $query = $this->db->order_by('nilai_pertumbuhan', 'DESC')->get('perbandingan_daerah');
        if ($query->num_rows() > 0) {
            $list = array();
            foreach ($query->result() as $row) {
                $list[] = array(
                    'id' => (int)$row->id,
                    'name' => $row->nama_wilayah,
                    'nama_wilayah' => $row->nama_wilayah,
                    'value' => (float)$row->nilai_pertumbuhan,
                    'nilai_pertumbuhan' => (float)$row->nilai_pertumbuhan,
                    'change' => (float)$row->perubahan,
                    'perubahan' => (float)$row->perubahan,
                    'series' => json_decode($row->deret_historis, true) ?: array(),
                    'deret_historis' => $row->deret_historis,
                    'kode_wilayah' => $row->kode_wilayah ?? $kode,
                    'is_deleted' => (int)($row->is_deleted ?? 0),
                    'deleted_at' => $row->deleted_at ?? null
                );
            }
            return $list;
        }

        if ($kode === '35.10') {
            return array(
                array('id' => 1, 'name' => 'Kota Surabaya', 'value' => 5.76, 'change' => 0.34, 'series' => array(5.21, 5.42, 5.60, 6.00, 6.10, -4.85, 4.29, 6.51, 5.42, 5.76)),
                array('id' => 2, 'name' => 'Kab. Gresik', 'value' => 5.42, 'change' => 0.31, 'series' => array(5.10, 5.20, 5.30, 5.70, 5.60, -3.68, 3.80, 5.16, 5.11, 5.42)),
                array('id' => 3, 'name' => 'Kab. Banyuwangi', 'value' => 5.18, 'change' => 0.26, 'series' => array(5.02, 5.15, 5.28, 5.61, 5.55, -3.58, 4.08, 4.43, 4.92, 5.18)),
                array('id' => 4, 'name' => 'Kota Malang', 'value' => 5.06, 'change' => 0.18, 'series' => array(5.20, 5.30, 5.40, 5.60, 5.70, -2.26, 4.00, 5.41, 4.88, 5.06)),
                array('id' => 5, 'name' => 'Kab. Sidoarjo', 'value' => 4.98, 'change' => 0.12, 'series' => array(5.30, 5.40, 5.50, 5.70, 5.60, -3.69, 4.21, 5.20, 4.86, 4.98)),
                array('id' => 6, 'name' => 'Kota Kediri', 'value' => 4.88, 'change' => 0.15, 'series' => array(5.00, 5.10, 5.20, 5.50, 5.40, -4.10, 3.90, 4.85, 4.73, 4.88)),
                array('id' => 7, 'name' => 'Kab. Jember', 'value' => 4.62, 'change' => 0.08, 'series' => array(4.80, 4.90, 5.00, 5.20, 5.10, -3.20, 3.70, 4.53, 4.54, 4.62)),
                array('id' => 8, 'name' => 'Kab. Pasuruan', 'value' => 4.55, 'change' => 0.05, 'series' => array(4.90, 5.00, 5.10, 5.40, 5.30, -3.80, 3.60, 4.48, 4.50, 4.55))
            );
        }

        return array();
    }

    public function get_regional_by_id($id) {
        $row = $this->db->where('id', (int)$id)->get('perbandingan_daerah')->row_array();
        if (!$row) return null;
        return array(
            'id' => (int)$row['id'],
            'name' => $row['nama_wilayah'],
            'nama_wilayah' => $row['nama_wilayah'],
            'value' => (float)$row['nilai_pertumbuhan'],
            'nilai_pertumbuhan' => (float)$row['nilai_pertumbuhan'],
            'change' => (float)$row['perubahan'],
            'perubahan' => (float)$row['perubahan'],
            'series' => json_decode($row['deret_historis'], true) ?: array(),
            'deret_historis' => $row['deret_historis'],
            'kode_wilayah' => $row['kode_wilayah'] ?? '35.10',
            'is_deleted' => (int)($row['is_deleted'] ?? 0),
            'deleted_at' => $row['deleted_at'] ?? null
        );
    }

    public function add_regional($data) {
        $nama = trim($data['nama_wilayah'] ?? $data['name'] ?? '');
        $nilai = (float)($data['nilai_pertumbuhan'] ?? $data['value'] ?? 5.0);
        $perubahan = (float)($data['perubahan'] ?? $data['change'] ?? 0.2);
        $kodeWilayah = !empty($data['kode_wilayah']) ? trim($data['kode_wilayah']) : $this->get_active_kode_wilayah();

        if (empty($nama)) return false;

        $series = null;
        if (!empty($data['deret_historis'])) {
            if (is_array($data['deret_historis'])) {
                $series = $data['deret_historis'];
            } else {
                $decoded = json_decode($data['deret_historis'], true);
                if (is_array($decoded)) {
                    $series = $decoded;
                } else {
                    $parts = array_map('trim', explode(',', $data['deret_historis']));
                    $series = array_map('floatval', $parts);
                }
            }
        }
        if (empty($series) || !is_array($series)) {
            $series = array(
                round($nilai - 0.35, 2),
                round($nilai - 0.20, 2),
                round($nilai - 0.10, 2),
                round($nilai + 0.20, 2),
                round($nilai + 0.15, 2),
                round(-3.50, 2),
                round($nilai - 1.10, 2),
                round($nilai - 0.40, 2),
                round($nilai - $perubahan, 2),
                round($nilai, 2)
            );
        }

        $record = array(
            'nama_wilayah' => $nama,
            'nilai_pertumbuhan' => $nilai,
            'perubahan' => $perubahan,
            'deret_historis' => json_encode(array_values($series)),
            'kode_wilayah' => $kodeWilayah,
            'is_deleted' => 0,
            'deleted_at' => null
        );

        $exists = $this->db->where('nama_wilayah', $nama)->where('kode_wilayah', $kodeWilayah)->where('is_deleted', 0)->get('perbandingan_daerah')->num_rows();
        if ($exists > 0) {
            return false;
        }

        return $this->db->insert('perbandingan_daerah', $record);
    }

    public function update_regional($id, $data) {
        $record = array();
        if (isset($data['nama_wilayah']) || isset($data['name'])) {
            $record['nama_wilayah'] = trim($data['nama_wilayah'] ?? $data['name']);
        }
        if (isset($data['nilai_pertumbuhan']) || isset($data['value'])) {
            $record['nilai_pertumbuhan'] = (float)($data['nilai_pertumbuhan'] ?? $data['value']);
        }
        if (isset($data['perubahan']) || isset($data['change'])) {
            $record['perubahan'] = (float)($data['perubahan'] ?? $data['change']);
        }
        if (isset($data['kode_wilayah'])) {
            $record['kode_wilayah'] = trim($data['kode_wilayah']);
        }
        if (!empty($data['deret_historis'])) {
            if (is_array($data['deret_historis'])) {
                $record['deret_historis'] = json_encode(array_values($data['deret_historis']));
            } else {
                $decoded = json_decode($data['deret_historis'], true);
                if (is_array($decoded)) {
                    $record['deret_historis'] = json_encode(array_values($decoded));
                } else {
                    $parts = array_map('trim', explode(',', $data['deret_historis']));
                    $record['deret_historis'] = json_encode(array_values(array_map('floatval', $parts)));
                }
            }
        }

        if (empty($record)) return false;

        $this->db->where('id', (int)$id);
        return $this->db->update('perbandingan_daerah', $record);
    }

    public function soft_delete_regional($id) {
        $this->db->where('id', (int)$id);
        return $this->db->update('perbandingan_daerah', array(
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s')
        ));
    }

    public function restore_regional($id) {
        $this->db->where('id', (int)$id);
        return $this->db->update('perbandingan_daerah', array(
            'is_deleted' => 0,
            'deleted_at' => null
        ));
    }

    public function hard_delete_regional($id) {
        $this->db->where('id', (int)$id);
        return $this->db->delete('perbandingan_daerah');
    }

    public function count_trash_regional($kode_wilayah = null) {
        $kode = $this->get_active_kode_wilayah($kode_wilayah);
        return $this->db->where('is_deleted', 1)->where('kode_wilayah', $kode)->count_all_results('perbandingan_daerah');
    }

    public function get_districts($status = null, $kode_wilayah = null) {
        $kode = $this->get_active_kode_wilayah($kode_wilayah);
        if ($status === 'Terhapus' || $status === 'Sampah') {
            $this->db->where('is_deleted', 1);
        } else {
            $this->db->where('is_deleted', 0);
        }
        $this->db->where('kode_wilayah', $kode);
        $query = $this->db->get('peta_ekonomi');
        if ($query->num_rows() > 0) {
            $list = array();
            foreach ($query->result() as $row) {
                $list[] = array(
                    'name' => $row->nama_kecamatan,
                    'nama_kecamatan' => $row->nama_kecamatan,
                    'share' => (float)$row->porsi_pdrb,
                    'porsi_pdrb' => (float)$row->porsi_pdrb,
                    'growth' => (float)$row->pertumbuhan,
                    'pertumbuhan' => (float)$row->pertumbuhan,
                    'poverty' => (float)$row->kemiskinan,
                    'kemiskinan' => (float)$row->kemiskinan,
                    'density' => (int)$row->kepadatan,
                    'kepadatan' => (int)$row->kepadatan,
                    'category' => $row->kategori,
                    'kategori' => $row->kategori,
                    'lat' => (float)$row->garis_lintang,
                    'lng' => (float)$row->garis_bujur,
                    'id' => (int)$row->id,
                    'kode_wilayah' => $row->kode_wilayah ?? $kode,
                    'is_deleted' => (int)($row->is_deleted ?? 0),
                    'deleted_at' => $row->deleted_at ?? null
                );
            }
            return $list;
        }

        if ($kode === '35.10') {
            return array(
                array('name' => 'Banyuwangi', 'share' => 14.8, 'growth' => 5.82, 'poverty' => 5.2, 'density' => 2450, 'category' => 'Perkotaan & Jasa', 'lat' => -8.2192, 'lng' => 114.3692),
                array('name' => 'Rogojampi', 'share' => 8.9, 'growth' => 5.41, 'poverty' => 6.8, 'density' => 1320, 'category' => 'Perdagangan & Agro', 'lat' => -8.3045, 'lng' => 114.2925),
                array('name' => 'Genteng', 'share' => 9.2, 'growth' => 5.35, 'poverty' => 6.1, 'density' => 1480, 'category' => 'Pusat Niaga Selatan', 'lat' => -8.3619, 'lng' => 114.1528)
            );
        }
        return array();
    }

    public function get_indicator_records($status = null, $query = null, $sort = 'tahun', $year = null, $kode_wilayah = null, $cluster = null) {
        $kode = $this->get_active_kode_wilayah($kode_wilayah);
        $this->db->where('kode_wilayah', $kode);
        if ($status === 'Terhapus' || $status === 'Sampah') {
            $this->db->where('is_deleted', 1);
        } else {
            $this->db->where('is_deleted', 0);
            if ($status && $status !== 'Semua status') {
                if ($status === 'Belum lengkap' || $status === 'Ditolak') {
                    $this->db->where_in('status_validasi', array('Belum lengkap', 'Ditolak'));
                } else {
                    $this->db->where('status_validasi', $status);
                }
            }
        }

        if ($year && is_numeric($year)) {
            $this->db->where('tahun', (int)$year);
        }

        if (!empty($cluster) && $cluster !== 'Semua klaster' && $cluster !== 'all') {
            $this->db->where('klaster', $cluster);
        }

        if ($query) {
            $this->db->group_start();
            $this->db->like('kode_indikator', $query);
            $this->db->or_like('nama_indikator', $query);
            $this->db->or_like('klaster', $query);
            $this->db->or_like('wilayah', $query);
            $this->db->group_end();
        }

        if ($sort === 'indicator' || $sort === 'nama_indikator') {
            $this->db->order_by('nama_indikator', 'ASC');
        } elseif ($sort === 'value' || $sort === 'nilai') {
            $this->db->order_by('nilai', 'DESC');
        } elseif ($sort === 'status') {
            // Prioritaskan antrean: Perlu review (1), Belum lengkap / Ditolak (2), Tervalidasi (3)
            $this->db->order_by("CASE WHEN status_validasi = 'Perlu review' THEN 1 WHEN status_validasi IN ('Belum lengkap', 'Ditolak') THEN 2 ELSE 3 END", "ASC", false);
            $this->db->order_by('tahun', 'DESC');
            $this->db->order_by('id', 'DESC');
        } else {
            $this->db->order_by('tahun', 'DESC');
            $this->db->order_by('id', 'DESC');
        }

        $res = $this->db->get('database_ekonomi');
        $list = array();
        foreach ($res->result_array() as $row) {
            $list[] = array(
                'id' => (int)$row['id'],
                'code' => $row['kode_indikator'],
                'kode_indikator' => $row['kode_indikator'],
                'indicator' => $row['nama_indikator'],
                'nama_indikator' => $row['nama_indikator'],
                'cluster' => $row['klaster'],
                'klaster' => $row['klaster'],
                'region' => $row['wilayah'],
                'wilayah' => $row['wilayah'],
                'period' => $row['periode'],
                'periode' => $row['periode'],
                'year' => (int)$row['tahun'],
                'tahun' => (int)$row['tahun'],
                'value' => (float)$row['nilai'],
                'nilai' => (float)$row['nilai'],
                'unit' => $row['satuan'],
                'satuan' => $row['satuan'],
                'source' => $row['sumber_data'],
                'sumber_data' => $row['sumber_data'],
                'definition' => $row['definisi_operasional'] ?? '',
                'definisi_operasional' => $row['definisi_operasional'] ?? '',
                'status' => $row['status_validasi'],
                'status_validasi' => $row['status_validasi'],
                'verified_by' => $row['diverifikasi_oleh'],
                'diverifikasi_oleh' => $row['diverifikasi_oleh'],
                'notes' => $row['catatan'],
                'catatan' => $row['catatan'],
                'kode_wilayah' => $row['kode_wilayah'] ?? $kode,
                'is_deleted' => (int)($row['is_deleted'] ?? 0),
                'deleted_at' => $row['deleted_at'] ?? null,
                'created_at' => $row['dibuat_pada'],
                'dibuat_pada' => $row['dibuat_pada']
            );
        }
        return $list;
    }

    public function get_indicator_record_by_id($id) {
        $row = $this->db->where('id', (int)$id)->get('database_ekonomi')->row_array();
        if (!$row) return null;
        return array(
            'id' => (int)$row['id'],
            'code' => $row['kode_indikator'],
            'kode_indikator' => $row['kode_indikator'],
            'indicator' => $row['nama_indikator'],
            'nama_indikator' => $row['nama_indikator'],
            'cluster' => $row['klaster'],
            'klaster' => $row['klaster'],
            'region' => $row['wilayah'],
            'wilayah' => $row['wilayah'],
            'kode_wilayah' => $row['kode_wilayah'] ?? '35.10',
            'period' => $row['periode'],
            'periode' => $row['periode'],
            'year' => (int)$row['tahun'],
            'tahun' => (int)$row['tahun'],
            'value' => (float)$row['nilai'],
            'nilai' => (float)$row['nilai'],
            'unit' => $row['satuan'],
            'satuan' => $row['satuan'],
            'source' => $row['sumber_data'],
            'sumber_data' => $row['sumber_data'],
            'definition' => $row['definisi_operasional'] ?? '',
            'definisi_operasional' => $row['definisi_operasional'] ?? '',
            'status' => $row['status_validasi'],
            'status_validasi' => $row['status_validasi'],
            'verified_by' => $row['diverifikasi_oleh'],
            'diverifikasi_oleh' => $row['diverifikasi_oleh'],
            'notes' => $row['catatan'],
            'catatan' => $row['catatan'],
            'is_deleted' => (int)($row['is_deleted'] ?? 0),
            'deleted_at' => $row['deleted_at'] ?? null,
            'created_at' => $row['dibuat_pada']
        );
    }

    public function add_indicator_record($data) {
        $kodeWilayah = !empty($data['kode_wilayah']) ? trim($data['kode_wilayah']) : $this->get_active_kode_wilayah();
        $namaWilayah = $this->get_nama_wilayah($kodeWilayah);

        $clusterVal = trim($data['cluster'] ?? $data['klaster'] ?? '');
        if (($clusterVal === '__custom__' || $clusterVal === '__NEW__' || empty($clusterVal)) && !empty($data['cluster_custom'])) {
            $clusterVal = trim($data['cluster_custom']);
        }
        if (empty($clusterVal)) {
            $clusterVal = 'Output dan pertumbuhan';
        }

        $defVal = trim($data['definition'] ?? $data['definisi_operasional'] ?? '');
        if (empty($defVal)) {
            $catRow = $this->db->where('kode_indikator', trim($data['code'] ?? $data['kode_indikator'] ?? ''))
                ->where('kode_wilayah', $kodeWilayah)
                ->get('katalog_indikator')
                ->row_array();
            if ($catRow && !empty($catRow['definisi_operasional'])) {
                $defVal = trim($catRow['definisi_operasional']);
            }
        }

        $record = array(
            'kode_indikator'       => trim($data['code'] ?? $data['kode_indikator'] ?? ''),
            'nama_indikator'       => trim($data['indicator'] ?? $data['nama_indikator'] ?? ''),
            'klaster'              => $clusterVal,
            'wilayah'              => trim($data['region'] ?? $data['wilayah'] ?? $namaWilayah),
            'kode_wilayah'         => $kodeWilayah,
            'periode'              => trim($data['period'] ?? $data['periode'] ?? 'Tahunan'),
            'tahun'                => (int)($data['year'] ?? $data['tahun'] ?? 2024),
            'nilai'                => (float)($data['value'] ?? $data['nilai'] ?? 0),
            'satuan'               => trim($data['unit'] ?? $data['satuan'] ?? '%'),
            'sumber_data'          => trim($data['source'] ?? $data['sumber_data'] ?? 'Input Manual'),
            'definisi_operasional' => $defVal,
            'status_validasi'      => 'Perlu review',
            'diverifikasi_oleh'    => null,
            'catatan'              => null,
            'is_deleted'           => 0,
            'deleted_at'           => null,
            'dibuat_pada'          => date('Y-m-d H:i:s')
        );

        $exists = $this->db->where('kode_indikator', $record['kode_indikator'])
            ->where('tahun', $record['tahun'])
            ->where('kode_wilayah', $record['kode_wilayah'])
            ->where('is_deleted', 0)
            ->get('database_ekonomi')
            ->num_rows();

        if ($exists > 0) {
            return false;
        }
        $res = $this->db->insert('database_ekonomi', $record);
        if ($res) {
            if (!empty($record['definisi_operasional'])) {
                $catExists = $this->db->where('kode_indikator', $record['kode_indikator'])->where('kode_wilayah', $record['kode_wilayah'])->get('katalog_indikator')->row_array();
                if ($catExists && empty($catExists['definisi_operasional'])) {
                    $this->db->where('id', (int)$catExists['id'])->update('katalog_indikator', array('definisi_operasional' => $record['definisi_operasional']));
                }
            }
            // Sinkronkan ke early_warning jika kode indikator ada di tabel EWS
            $ewsMatch = $this->db->where('kode_indikator', $record['kode_indikator'])->where('kode_wilayah', $record['kode_wilayah'])->get('early_warning')->row_array();
            if ($ewsMatch) {
                $this->update_ews_threshold($record['kode_indikator'], (float)$ewsMatch['ambang_batas'], $ewsMatch['arah_risiko'], (float)$record['nilai'], $record['kode_wilayah']);
            }
        }
        return $res;
    }

    public function update_indicator_record($id, $data) {
        $existing = $this->db->where('id', (int)$id)->get('database_ekonomi')->row_array();
        $record = array();
        if (isset($data['code']) || isset($data['kode_indikator'])) {
            $record['kode_indikator'] = trim($data['code'] ?? $data['kode_indikator']);
        }
        if (isset($data['indicator']) || isset($data['nama_indikator'])) {
            $record['nama_indikator'] = trim($data['indicator'] ?? $data['nama_indikator']);
        }
        if (isset($data['cluster']) || isset($data['klaster'])) {
            $cVal = trim($data['cluster'] ?? $data['klaster']);
            if (($cVal === '__custom__' || $cVal === '__NEW__' || empty($cVal)) && !empty($data['cluster_custom'])) {
                $cVal = trim($data['cluster_custom']);
            }
            if (!empty($cVal)) {
                $record['klaster'] = $cVal;
            }
        }
        if (isset($data['region']) || isset($data['wilayah'])) {
            $record['wilayah'] = trim($data['region'] ?? $data['wilayah']);
        }
        if (isset($data['kode_wilayah']) && !empty($data['kode_wilayah'])) {
            $record['kode_wilayah'] = trim($data['kode_wilayah']);
        }
        if (isset($data['period']) || isset($data['periode'])) {
            $record['periode'] = trim($data['period'] ?? $data['periode']);
        }
        if (isset($data['year']) || isset($data['tahun'])) {
            $record['tahun'] = (int)($data['year'] ?? $data['tahun']);
        }
        if (isset($data['value']) || isset($data['nilai'])) {
            $record['nilai'] = (float)($data['value'] ?? $data['nilai']);
        }
        if (isset($data['unit']) || isset($data['satuan'])) {
            $record['satuan'] = trim($data['unit'] ?? $data['satuan']);
        }
        if (isset($data['source']) || isset($data['sumber_data'])) {
            $record['sumber_data'] = trim($data['source'] ?? $data['sumber_data']);
        }
        if (isset($data['definition']) || isset($data['definisi_operasional'])) {
            $record['definisi_operasional'] = trim($data['definition'] ?? $data['definisi_operasional']);
        }

        // Setiap data yang diedit/diperbarui otomatis berstatus 'Perlu review' dan catatan penolakan lama dihapus
        $record['status_validasi'] = 'Perlu review';
        $record['catatan'] = null;
        $record['diverifikasi_oleh'] = null;

        if (empty($record)) return false;

        $this->db->where('id', (int)$id);
        $res = $this->db->update('database_ekonomi', $record);

        if ($res && isset($record['definisi_operasional']) && $record['definisi_operasional'] !== '' && $existing) {
            $rowCode = $existing['kode_indikator'];
            $rowWil = $existing['kode_wilayah'];
            $this->db->where('kode_indikator', $rowCode)
                ->where('kode_wilayah', $rowWil)
                ->update('katalog_indikator', array('definisi_operasional' => $record['definisi_operasional']));
        }

        // Jika data diedit (otomatis jadi Perlu review), cek apakah indikator ini masih memiliki data lain yang Tervalidasi
        $code = $record['kode_indikator'] ?? $existing['kode_indikator'] ?? null;
        $rowWilayah = $record['kode_wilayah'] ?? $existing['kode_wilayah'] ?? $this->get_active_kode_wilayah();
        if ($code) {
            $hasValid = $this->db
                ->where('kode_indikator', $code)
                ->where('kode_wilayah', $rowWilayah)
                ->where('status_validasi', 'Tervalidasi')
                ->where('is_deleted', 0)
                ->count_all_results('database_ekonomi');
            if ($hasValid === 0) {
                $this->remove_from_catalog($code, $rowWilayah);
            }
        }
        return $res;
    }

    public function soft_delete_indicator_record($id) {
        $existing = $this->db->where('id', (int)$id)->get('database_ekonomi')->row_array();
        $this->db->where('id', (int)$id);
        $res = $this->db->update('database_ekonomi', array(
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s')
        ));
        if ($existing && !empty($existing['kode_indikator'])) {
            $hasValid = $this->db
                ->where('kode_indikator', $existing['kode_indikator'])
                ->where('kode_wilayah', $existing['kode_wilayah'])
                ->where('status_validasi', 'Tervalidasi')
                ->where('is_deleted', 0)
                ->count_all_results('database_ekonomi');
            if ($hasValid === 0) {
                $this->remove_from_catalog($existing['kode_indikator'], $existing['kode_wilayah']);
            }
        }
        return $res;
    }

    public function restore_indicator_record($id) {
        $this->db->where('id', (int)$id);
        $res = $this->db->update('database_ekonomi', array(
            'is_deleted' => 0,
            'deleted_at' => null
        ));
        $existing = $this->db->where('id', (int)$id)->get('database_ekonomi')->row_array();
        if ($existing && $existing['status_validasi'] === 'Tervalidasi') {
            $this->sync_to_catalog($existing['kode_indikator'], $existing['kode_wilayah']);
        }
        return $res;
    }

    public function hard_delete_indicator_record($id) {
        $existing = $this->db->where('id', (int)$id)->get('database_ekonomi')->row_array();
        $this->db->where('id', (int)$id);
        $res = $this->db->delete('database_ekonomi');
        if ($existing && !empty($existing['kode_indikator'])) {
            $hasValid = $this->db
                ->where('kode_indikator', $existing['kode_indikator'])
                ->where('kode_wilayah', $existing['kode_wilayah'])
                ->where('status_validasi', 'Tervalidasi')
                ->where('is_deleted', 0)
                ->count_all_results('database_ekonomi');
            if ($hasValid === 0) {
                $this->remove_from_catalog($existing['kode_indikator'], $existing['kode_wilayah']);
            }
        }
        return $res;
    }

    public function count_trash_records($kode_wilayah = null) {
        $kode = $this->get_active_kode_wilayah($kode_wilayah);
        return $this->db->where('is_deleted', 1)->where('kode_wilayah', $kode)->count_all_results('database_ekonomi');
    }

    public function update_record_status($code, $year, $status, $notes = null, $verifier = null, $kode_wilayah = null) {
        $kode = $this->get_active_kode_wilayah($kode_wilayah);
        $this->db->where('kode_indikator', $code)->where('tahun', $year)->where('kode_wilayah', $kode);
        $data = array('status_validasi' => $status);
        if ($status === 'Tervalidasi' || $status === 'Perlu review') {
            // Data sudah disetujui atau dikembalikan untuk review ulang (sudah dibenarkan) -> hapus catatan penolakan otomatis
            $data['catatan'] = null;
        } elseif ($notes !== null) {
            $noteVal = trim($notes);
            $data['catatan'] = ($noteVal !== '') ? $noteVal : null;
        }
        if ($verifier !== null) {
            $data['diverifikasi_oleh'] = $verifier;
        }
        $updated = $this->db->update('database_ekonomi', $data);

        // Jika status menjadi Tervalidasi, otomatis masukkan / sinkronkan indikator ke katalog & EWS
        if ($status === 'Tervalidasi' && $updated) {
            $this->sync_to_catalog($code, $kode);
            $this->sync_all_validated_to_ews($kode);
        } else {
            // Jika status bukan Tervalidasi (diubah ke Belum lengkap, Ditolak, atau Perlu review),
            // pastikan indikator tidak tampil di katalog & EWS jika tidak ada data aktif lain yang berstatus Tervalidasi
            $hasValid = $this->db
                ->where('kode_indikator', $code)
                ->where('kode_wilayah', $kode)
                ->where('status_validasi', 'Tervalidasi')
                ->where('is_deleted', 0)
                ->count_all_results('database_ekonomi');
            if ($hasValid === 0) {
                $this->remove_from_catalog($code, $kode);
                $this->clean_unvalidated_from_ews($kode);
            }
        }

        return $updated;
    }

    public function remove_from_catalog($code, $kode_wilayah = null) {
        if (empty($code)) return false;
        $kode = $this->get_active_kode_wilayah($kode_wilayah);
        return $this->db->where('kode_indikator', $code)->where('kode_wilayah', $kode)->delete('katalog_indikator');
    }

    public function sync_to_catalog($code, $kode_wilayah = null) {
        $kode_wil = $this->get_active_kode_wilayah($kode_wilayah);
        $record = $this->db
            ->where('kode_indikator', $code)
            ->where('kode_wilayah', $kode_wil)
            ->where('status_validasi', 'Tervalidasi')
            ->where('is_deleted', 0)
            ->order_by('tahun', 'DESC')
            ->order_by('id', 'DESC')
            ->get('database_ekonomi')
            ->row_array();

        if (!$record) {
            return false;
        }

        $kode = trim($record['kode_indikator']);
        $nama = trim($record['nama_indikator']);
        $klaster = trim($record['klaster'] ?: 'Output & Pertumbuhan');
        $satuan = trim($record['satuan'] ?: '%');
        $frekuensi = trim($record['periode'] ?: 'Tahunan');
        $sumber = trim($record['sumber_data'] ?: ('BPS ' . $this->get_nama_wilayah($kode_wil)));
        $defMap = array(
            'EKO-001' => 'Nilai tambah bruto seluruh lapangan usaha di wilayah pada harga yang berlaku pada periode berjalan.',
            'EKO-002' => 'Nilai tambah bruto seluruh lapangan usaha yang dinilai dengan harga pada tahun dasar untuk mengukur volume/pertumbuhan riil.',
            'EKO-003' => 'Nilai tambah bruto setiap lapangan usaha atas dasar harga berlaku.',
            'EKO-004' => 'Nilai tambah bruto setiap lapangan usaha atas dasar harga konstan.',
            'EKO-005' => 'Perubahan persentase PDRB ADHK suatu wilayah dibandingkan periode sebelumnya.',
            'EKO-006' => 'Perubahan persentase PDRB ADHK setiap lapangan usaha dibandingkan tahun sebelumnya.',
            'EKO-007' => 'Persentase nilai tambah suatu lapangan usaha terhadap total PDRB pada wilayah dan periode yang sama.',
            'EKO-008' => 'PDRB ADHB dibagi jumlah penduduk pertengahan tahun; pendekatan rata-rata output per penduduk, bukan pendapatan aktual rumah tangga.',
            'EKO-009' => 'PDRB ADHK dibagi penduduk pertengahan tahun untuk pendekatan output riil per penduduk.',
            'EKO-010' => 'Komponen PDRB pengeluaran yang mencatat konsumsi akhir rumah tangga.',
            'EKO-011' => 'Komponen PDRB pengeluaran yang mencerminkan penambahan aset tetap untuk kegiatan produksi.',
            'EKO-012' => 'Komponen konsumsi akhir pemerintah dalam pendekatan PDRB pengeluaran.',
            'EKO-013' => 'Komponen ekspor barang dan jasa pada pendekatan PDRB pengeluaran.',
            'EKO-014' => 'Komponen impor barang dan jasa sebagai pengurang dalam pendekatan PDRB pengeluaran.',
            'NAK-001' => 'Jumlah penduduk pada pertengahan tahun sebagai denominator beberapa indikator per kapita dan rasio.',
            'TK-001' => 'Penduduk berumur 15 tahun ke atas yang menjadi basis penghitungan indikator pasar kerja.',
            'TK-002' => 'Penduduk usia kerja yang bekerja atau menganggur menurut konsep statistik ketenagakerjaan.',
            'TK-003' => 'Penduduk usia kerja yang bekerja atau memiliki pekerjaan tetapi sementara tidak bekerja sesuai konsep Sakernas.',
            'TK-004' => 'Penduduk dalam angkatan kerja yang tidak bekerja dan memenuhi kriteria pengangguran Sakernas.',
            'TK-005' => 'Persentase jumlah pengangguran terhadap jumlah angkatan kerja.',
            'TK-006' => 'Persentase angkatan kerja terhadap penduduk usia kerja.',
            'TK-007' => 'Jumlah penduduk bekerja yang dikelompokkan menurut lapangan usaha utama.',
            'TK-008' => 'Penduduk bekerja yang dikelompokkan menurut status formal dan informal sesuai konsep BPS.',
            'TK-009' => 'Rata-rata upah/gaji bersih buruh/karyawan menurut periode referensi statistik BPS.',
            'TK-010' => 'Penduduk bekerja dengan jam kerja di bawah ambang yang ditetapkan dan masih mencari/bersedia menerima pekerjaan tambahan sesuai definisi BPS.',
            'TK-011' => 'Output ekonomi yang dihasilkan per pekerja pada suatu wilayah/sektor.',
            'INV-001' => 'Nilai investasi yang direalisasikan oleh pelaku usaha dan dilaporkan melalui sistem LKPM/OSS sesuai ketentuan.',
            'INV-002' => 'Nilai realisasi penanaman modal asing yang dilaporkan melalui LKPM.',
            'INV-003' => 'Nilai realisasi penanaman modal dalam negeri yang dilaporkan melalui LKPM.',
            'INV-004' => 'Jumlah proyek/kegiatan usaha yang melaporkan investasi dalam periode pelaporan.',
            'INV-005' => 'Jumlah tenaga kerja Indonesia yang dilaporkan terserap pada kegiatan investasi.',
            'INV-006' => 'Jumlah Nomor Induk Berusaha atau unit usaha aktif sesuai definisi dan status administrasi OSS.',
            'HRG-001' => 'Indeks yang mengukur perubahan rata-rata harga paket barang dan jasa konsumsi rumah tangga dalam suatu periode.',
            'HRG-002' => 'Indeks harga konsumen yang dirinci menurut kelompok dan subkelompok pengeluaran.',
            'HRG-003' => 'Persentase perubahan IHK bulan berjalan terhadap bulan sebelumnya.',
            'HRG-004' => 'Persentase perubahan IHK bulan berjalan dibandingkan bulan yang sama tahun sebelumnya.',
            'HRG-005' => 'Harga konsumen/eceran komoditas pangan pada pasar dan tanggal pemantauan tertentu.',
            'HRG-006' => 'Bobot pengeluaran komoditas/kelompok yang digunakan dalam penghitungan IHK.',
            'KES-001' => 'Proporsi penduduk dengan rata-rata pengeluaran per kapita per bulan di bawah garis kemiskinan.',
            'KES-002' => 'Estimasi jumlah penduduk yang pengeluaran per kapitanya berada di bawah garis kemiskinan.',
            'KES-003' => 'Nilai minimum pengeluaran per kapita per bulan untuk memenuhi kebutuhan dasar makanan dan bukan makanan.',
            'KES-004' => 'Ukuran rata-rata kesenjangan pengeluaran penduduk miskin terhadap garis kemiskinan.',
            'KES-005' => 'Ukuran penyebaran pengeluaran di antara penduduk miskin; memberi bobot lebih pada penduduk yang sangat jauh di bawah garis kemiskinan.',
            'KES-006' => 'Penduduk yang berada di bawah ambang kemiskinan ekstrem sesuai metodologi/garis yang berlaku pada periode pelaporan.',
            'KES-007' => 'Indeks komposit pembangunan manusia dari dimensi umur panjang dan hidup sehat, pengetahuan, serta standar hidup layak.',
            'KES-008' => 'Rata-rata perkiraan lama hidup yang akan dijalani bayi yang baru lahir berdasarkan pola mortalitas pada periode tertentu.',
            'KES-009' => 'Lamanya sekolah yang diharapkan akan dirasakan anak pada umur tertentu di masa mendatang.',
            'KES-010' => 'Rata-rata jumlah tahun yang digunakan penduduk pada usia yang ditetapkan BPS dalam menempuh pendidikan formal.',
            'KES-011' => 'Pengeluaran per kapita yang disesuaikan untuk merepresentasikan dimensi standar hidup dalam IPM.',
            'KES-012' => 'Rata-rata pengeluaran konsumsi per kapita rumah tangga pada periode survei.',
            'PAR-001' => 'Jumlah kunjungan wisatawan nusantara ke destinasi/objek wisata atau wilayah sesuai definisi pelaporan.',
            'PAR-002' => 'Jumlah kunjungan wisatawan berkewarganegaraan asing sesuai cakupan pencatatan destinasi/akomodasi.',
            'PAR-003' => 'Jumlah tamu yang menginap pada hotel dan jasa akomodasi yang dicakup statistik.',
            'PAR-004' => 'Perbandingan antara jumlah malam kamar terpakai dan jumlah malam kamar tersedia pada periode tertentu.',
            'PAR-005' => 'Jumlah kamar yang tersedia untuk tamu pada hotel dan akomodasi yang dicakup statistik.',
            'PAR-006' => 'Jumlah unit usaha hotel dan jasa akomodasi yang tercatat aktif dalam cakupan statistik.',
            'PRO-001' => 'Volume hasil produksi tanaman pangan, hortikultura, perkebunan, atau peternakan menurut komoditas.',
            'PRO-002' => 'Luas lahan yang dipanen untuk komoditas pertanian pada periode tertentu.',
            'PRO-003' => 'Hasil produksi komoditas per satuan luas panen.',
            'PRO-004' => 'Volume produksi perikanan tangkap dan/atau budidaya menurut jenis dan lokasi.',
            'PRO-005' => 'Jumlah atau nilai produksi industri pengolahan menurut subsektor pada cakupan usaha yang ditetapkan.',
            'FIS-001' => 'Pendapatan daerah yang diperoleh dari pajak daerah, retribusi daerah, hasil pengelolaan kekayaan daerah yang dipisahkan, dan lain-lain PAD yang sah.',
            'FIS-002' => 'Pendapatan daerah yang berasal dari transfer pemerintah pusat dan/atau antar daerah sesuai struktur APBD.',
            'FIS-003' => 'Pengeluaran pemerintah daerah yang diakui sebagai belanja daerah pada laporan realisasi anggaran.',
            'FIS-004' => 'Belanja pemerintah daerah untuk memperoleh aset tetap/aset lainnya sesuai klasifikasi APBD yang berlaku.',
            'FIS-005' => 'Pagu dan realisasi belanja pemerintah daerah yang dirinci menurut urusan, OPD, program, kegiatan/subkegiatan.',
            'GEO-001' => 'Kode unik provinsi, kabupaten/kota, kecamatan, desa/kelurahan dan relasi hierarkisnya.',
            'GEO-002' => 'Geometri batas administrasi untuk mengaitkan data indikator dengan area geografis.',
            'GEO-003' => 'Koordinat lokasi objek ekonomi yang dapat dipetakan, seperti pasar, destinasi wisata, proyek investasi, fasilitas produksi.',
            'GEO-004' => 'Nilai indikator yang diobservasi atau dihitung untuk masing-masing kecamatan/desa dengan definisi, cakupan, dan metode estimasi yang jelas.',
            'EWS-001' => 'Nilai terkini dari indikator yang dipilih sebagai pemicu peringatan dini.',
            'EWS-002' => 'Batas numerik yang digunakan untuk mengelompokkan kondisi indikator ke status normal, waspada, dan kritis.',
            'EWS-003' => 'Status hasil evaluasi nilai aktual terhadap threshold beserta waktu kejadian, tindak lanjut, dan penyelesaian.',
            'ANA-001' => 'Ukuran spesialisasi relatif suatu sektor di wilayah dibandingkan wilayah referensi.',
            'ANA-002' => 'Dekomposisi perubahan output/tenaga kerja sektoral lokal menjadi pengaruh pertumbuhan umum, bauran industri, dan daya saing lokal.',
            'ANA-003' => 'Klasifikasi empat kuadran berdasarkan perbandingan tingkat pertumbuhan dan indikator kontribusi/pendapatan relatif.',
            'ANA-004' => 'Ukuran arah dan kekuatan hubungan linear dua variabel numerik yang diamati pada pasangan observasi sepadan.',
            'ANA-005' => 'Model hubungan linear satu variabel dependen dengan satu variabel independen untuk eksplorasi asosiasi.',
            'ANA-006' => 'Kontribusi perubahan output sektor terhadap pertumbuhan PDRB total pada periode tertentu.',
            'ANA-007' => 'Estimasi nilai indikator pada periode mendatang berdasarkan pola historis dengan metode yang dinyatakan.',
            'ANA-008' => 'Simulasi perubahan hasil indikator berdasarkan asumsi perubahan input ekonomi tertentu.',
            // Legacy fallbacks
            'PDRB_01' => 'Laju perubahan Produk Domestik Regional Bruto (PDRB) atas dasar harga konstan tahun 2010 yang mencerminkan pertumbuhan ekonomi riil daerah.',
            'PDRB_02' => 'Total nilai tambah bruto seluruh sektor ekonomi di wilayah atas dasar harga konstan.',
            'PDRB_03' => 'PDRB atas dasar harga berlaku dibagi jumlah penduduk pertengahan tahun, mengukur tingkat pendapatan rata-rata masyarakat.',
            'INF_01'  => 'Persentase kenaikan harga barang dan jasa kebutuhan pokok konsumen dari waktu ke waktu berdasarkan survei IHK.',
            'TPT_01'  => 'Persentase jumlah pengangguran terhadap jumlah keseluruhan angkatan kerja aktif daerah.',
            'POV_01'  => 'Persentase penduduk yang memiliki rata-rata pengeluaran per kapita per bulan di bawah Garis Kemiskinan.',
            'IPM_01'  => 'Indeks komposit pengukuran capaian pembangunan manusia berbasis dimensi umur panjang, pengetahuan, dan standar hidup layak.',
            'INV_01'  => 'Total akumulasi nilai investasi penanaman modal dalam negeri (PMDN) dan penanaman modal asing (PMA) yang terealisasi.',
            'INV_02'  => 'Persentase laju pertumbuhan realisasi penanaman modal di daerah dibandingkan tahun sebelumnya.',
            'FIS_01'  => 'Penerimaan daerah dari sumber pajak daerah, retribusi, hasil pengelolaan kekayaan daerah yang dipisahkan, dan lain-lain PAD yang sah.',
            'PAR_01'  => 'Jumlah kunjungan wisatawan nusantara dan mancanegara ke berbagai destinasi wisata daerah.',
            'SEK_01'  => 'Pangsa atau kontribusi persentase sektor pertanian, kehutanan, dan perikanan terhadap total PDRB daerah.'
        );

        $definisi = !empty($record['definisi_operasional']) ? trim($record['definisi_operasional']) : ($defMap[$kode] ?? "Indikator {$nama} dalam klaster {$klaster} untuk pemantauan capaian pembangunan daerah.");

        // Cek apakah sudah ada di katalog_indikator
        $existing = $this->db->where('kode_indikator', $kode)->where('kode_wilayah', $kode_wil)->get('katalog_indikator')->row_array();
        if ($existing) {
            // Jika indikator di katalog_indikator sedang berstatus soft-deleted (is_deleted == 1), jangan di-un-delete secara otomatis
            if (!empty($existing['is_deleted'])) {
                return false;
            }

            $update = array(
                'nama_indikator'       => $nama,
                'klaster'              => $klaster,
                'satuan'               => $satuan,
                'frekuensi'            => $frekuensi,
                'sumber_data'          => $sumber
            );
            if (empty($existing['definisi_operasional']) || !empty($record['definisi_operasional'])) {
                $update['definisi_operasional'] = $definisi;
            }
            $this->db->where('id', (int)$existing['id'])->update('katalog_indikator', $update);
            return (int)$existing['id'];
        } else {
            $catalogData = array(
                'kode_indikator'       => $kode,
                'nama_indikator'       => $nama,
                'klaster'              => $klaster,
                'satuan'               => $satuan,
                'frekuensi'            => $frekuensi,
                'sumber_data'          => $sumber,
                'definisi_operasional' => $definisi,
                'kode_wilayah'         => $kode_wil,
                'is_deleted'           => 0,
                'deleted_at'           => null
            );
            $this->db->insert('katalog_indikator', $catalogData);
            return $this->db->insert_id();
        }
    }

    public function clean_unvalidated_from_catalog($kode_wilayah = null) {
        $kode_wil = $this->db->escape($this->get_active_kode_wilayah($kode_wilayah));
        // Hanya bersihkan entri katalog aktif yang berasal dari database_ekonomi tetapi sudah tidak ada rekaman Tervalidasi aktif
        $this->db->query("DELETE FROM katalog_indikator WHERE kode_wilayah = {$kode_wil} AND is_deleted = 0 AND kode_indikator IN (
            SELECT DISTINCT kode_indikator FROM database_ekonomi WHERE kode_wilayah = {$kode_wil}
        ) AND kode_indikator NOT IN (
            SELECT DISTINCT kode_indikator FROM database_ekonomi WHERE kode_wilayah = {$kode_wil} AND status_validasi = 'Tervalidasi' AND is_deleted = 0
        )");
    }

    public function sync_all_validated_to_catalog($kode_wilayah = null) {
        $kode_wil = $this->get_active_kode_wilayah($kode_wilayah);
        // 1. Bersihkan entri katalog yang statusnya belum tervalidasi atau diubah ke Belum lengkap / Ditolak / Perlu review
        $this->clean_unvalidated_from_catalog($kode_wil);

        // 2. Sinkronkan seluruh data yang berstatus Tervalidasi
        $indicators = $this->db
            ->select('DISTINCT(kode_indikator)')
            ->where('status_validasi', 'Tervalidasi')
            ->where('kode_wilayah', $kode_wil)
            ->where('is_deleted', 0)
            ->get('database_ekonomi')
            ->result_array();

        $count = 0;
        foreach ($indicators as $row) {
            if (!empty($row['kode_indikator'])) {
                if ($this->sync_to_catalog($row['kode_indikator'], $kode_wil)) {
                    $count++;
                }
            }
        }
        return $count;
    }

    public function get_catalog_years($kode_wilayah = null) {
        $kode_wil = $this->get_active_kode_wilayah($kode_wilayah);
        return $this->db
            ->select('tahun, COUNT(*) as total_records, COUNT(DISTINCT kode_indikator) as total_indicators')
            ->where('status_validasi', 'Tervalidasi')
            ->where('kode_wilayah', $kode_wil)
            ->where('is_deleted', 0)
            ->group_by('tahun')
            ->order_by('tahun', 'ASC')
            ->get('database_ekonomi')
            ->result_array();
    }

    public function get_catalog_clusters($status = null, $year = null, $kode_wilayah = null) {
        $kode_wil = $this->get_active_kode_wilayah($kode_wilayah);
        $escapedWil = $this->db->escape($kode_wil);

        $this->db->select('k.klaster, COUNT(*) as total_indicators');
        $this->db->from('katalog_indikator k');
        $this->db->where('k.kode_wilayah', $kode_wil);
        if ($status === 'Terhapus' || $status === 'Sampah') {
            $this->db->where('k.is_deleted', 1);
        } else {
            $this->db->where('k.is_deleted', 0);
            if ($year && is_numeric($year)) {
                $yInt = (int)$year;
                $this->db->where("EXISTS (SELECT 1 FROM database_ekonomi d WHERE d.kode_indikator = k.kode_indikator AND d.kode_wilayah = {$escapedWil} AND d.status_validasi = 'Tervalidasi' AND d.is_deleted = 0 AND d.tahun = {$yInt})", null, false);
            } else {
                $this->db->where("EXISTS (SELECT 1 FROM database_ekonomi d WHERE d.kode_indikator = k.kode_indikator AND d.kode_wilayah = {$escapedWil} AND d.status_validasi = 'Tervalidasi' AND d.is_deleted = 0)", null, false);
            }
        }
        $this->db->group_by('k.klaster');
        $this->db->order_by('total_indicators', 'DESC');
        return $this->db->get()->result_array();
    }

    public function get_database_clusters($kode_wilayah = null) {
        $kode_wil = $this->get_active_kode_wilayah($kode_wilayah);
        return $this->db
            ->select('klaster, COUNT(DISTINCT kode_indikator) as total_indicators, COUNT(*) as total_records')
            ->where('is_deleted', 0)
            ->where('kode_wilayah', $kode_wil)
            ->group_by('klaster')
            ->order_by('total_records', 'DESC')
            ->get('database_ekonomi')
            ->result_array();
    }

    /**
     * Mengambil daftar seluruh nama klaster unik yang sudah pernah di-input sebelumnya
     * baik yang tercatat pada katalog master indikator maupun database repositori ekonomi.
     */
    public function get_all_distinct_clusters($kode_wilayah = null) {
        $kode_wil = $this->get_active_kode_wilayah($kode_wilayah);

        $q1 = $this->db->select('klaster')
            ->where('is_deleted', 0)
            ->where('kode_wilayah', $kode_wil)
            ->where("klaster IS NOT NULL AND klaster != ''", null, false)
            ->get('katalog_indikator')
            ->result_array();

        $q2 = $this->db->select('klaster')
            ->where('is_deleted', 0)
            ->where('kode_wilayah', $kode_wil)
            ->where("klaster IS NOT NULL AND klaster != ''", null, false)
            ->get('database_ekonomi')
            ->result_array();

        $clusterList = array();
        foreach (array_merge($q1, $q2) as $row) {
            $c = trim($row['klaster'] ?? '');
            if ($c !== '' && !in_array($c, $clusterList)) {
                $clusterList[] = $c;
            }
        }
        sort($clusterList, SORT_NATURAL | SORT_FLAG_CASE);
        return $clusterList;
    }

    public function get_indicator_catalog($status = null, $year = null, $kode_wilayah = null, $cluster = null) {
        $kode_wil = $this->get_active_kode_wilayah($kode_wilayah);
        $escapedWil = $this->db->escape($kode_wil);

        $this->db->from('katalog_indikator k');
        $this->db->where('k.kode_wilayah', $kode_wil);
        if ($status === 'Terhapus' || $status === 'Sampah') {
            $this->db->where('k.is_deleted', 1);
        } else {
            $this->db->where('k.is_deleted', 0);
            // Hanya tampilkan indikator yang SUDAH Tervalidasi di database_ekonomi (atau master indikator baru yang belum ada data di database_ekonomi)
            if ($year && is_numeric($year)) {
                $yInt = (int)$year;
                $this->db->where("EXISTS (SELECT 1 FROM database_ekonomi d WHERE d.kode_indikator = k.kode_indikator AND d.kode_wilayah = {$escapedWil} AND d.status_validasi = 'Tervalidasi' AND d.is_deleted = 0 AND d.tahun = {$yInt})", null, false);
            } else {
                $this->db->where("(EXISTS (SELECT 1 FROM database_ekonomi d WHERE d.kode_indikator = k.kode_indikator AND d.kode_wilayah = {$escapedWil} AND d.status_validasi = 'Tervalidasi' AND d.is_deleted = 0) OR NOT EXISTS (SELECT 1 FROM database_ekonomi d WHERE d.kode_indikator = k.kode_indikator AND d.kode_wilayah = {$escapedWil}))", null, false);
            }
        }
        if (!empty($cluster) && $cluster !== 'Semua klaster' && $cluster !== 'all') {
            $this->db->where('k.klaster', $cluster);
        }
        $res = $this->db->order_by('k.kode_indikator', 'ASC')->get();
        $list = array();
        foreach ($res->result_array() as $row) {
            $code = $row['kode_indikator'];
            // Ambil statistik rekaman data dari database_ekonomi
            $recordsInfo = $this->db
                ->select('COUNT(*) as total_records, MIN(tahun) as min_year, MAX(tahun) as max_year')
                ->where('kode_indikator', $code)
                ->where('kode_wilayah', $kode_wil)
                ->where('status_validasi', 'Tervalidasi')
                ->where('is_deleted', 0)
                ->get('database_ekonomi')
                ->row_array();

            $totalRecs = (int)($recordsInfo['total_records'] ?? 0);
            $minYr = $recordsInfo['min_year'];
            $maxYr = $recordsInfo['max_year'];
            $periodSpan = ($minYr && $maxYr) ? ($minYr == $maxYr ? $minYr : "{$minYr} - {$maxYr}") : '-';

            // Jika filter tahun spesifik dipilih, ambil nilai realisasi dan tahun tersebut
            $yearValue = null;
            if ($year && is_numeric($year)) {
                $recYear = $this->db
                    ->select('nilai, tahun, satuan')
                    ->where('kode_indikator', $code)
                    ->where('kode_wilayah', $kode_wil)
                    ->where('tahun', (int)$year)
                    ->where('status_validasi', 'Tervalidasi')
                    ->where('is_deleted', 0)
                    ->get('database_ekonomi')
                    ->row_array();
                if ($recYear) {
                    $yearValue = $recYear['nilai'];
                }
            }

            $list[] = array(
                'id' => (int)$row['id'],
                'code' => $row['kode_indikator'],
                'kode_indikator' => $row['kode_indikator'],
                'name' => $row['nama_indikator'],
                'nama_indikator' => $row['nama_indikator'],
                'cluster' => $row['klaster'],
                'klaster' => $row['klaster'],
                'unit' => $row['satuan'],
                'satuan' => $row['satuan'],
                'frequency' => $row['frekuensi'],
                'frekuensi' => $row['frekuensi'],
                'source' => $row['sumber_data'],
                'sumber_data' => $row['sumber_data'],
                'definition' => $row['definisi_operasional'],
                'definisi_operasional' => $row['definisi_operasional'],
                'kode_wilayah' => $row['kode_wilayah'] ?? $kode_wil,
                'total_records' => $totalRecs,
                'period_span' => $periodSpan,
                'selected_year' => ($year && is_numeric($year)) ? (int)$year : null,
                'selected_year_value' => $yearValue
            );
        }
        return $list;
    }

    public function clean_unvalidated_from_ews($kode_wilayah = null) {
        $kode_wil = $this->db->escape($this->get_active_kode_wilayah($kode_wilayah));
        // Hapus dari early_warning jika indikator tidak memiliki rekaman aktif Tervalidasi di database_ekonomi
        $this->db->query("DELETE FROM early_warning WHERE kode_wilayah = {$kode_wil} AND kode_indikator NOT IN (
            SELECT DISTINCT kode_indikator FROM database_ekonomi WHERE kode_wilayah = {$kode_wil} AND status_validasi = 'Tervalidasi' AND is_deleted = 0
        )");
    }

    public function sync_all_validated_to_ews($kode_wilayah = null) {
        $kode_wil = $this->get_active_kode_wilayah($kode_wilayah);
        // 1. Bersihkan indikator yang belum atau tidak lagi tervalidasi
        $this->clean_unvalidated_from_ews($kode_wil);

        // 2. Ambil seluruh indikator yang sudah tervalidasi di database_ekonomi
        $validated = $this->db
            ->select('kode_indikator, nama_indikator, satuan, AVG(nilai) as avg_val')
            ->where('status_validasi', 'Tervalidasi')
            ->where('kode_wilayah', $kode_wil)
            ->where('is_deleted', 0)
            ->group_by('kode_indikator, nama_indikator, satuan')
            ->order_by('kode_indikator', 'ASC')
            ->get('database_ekonomi')
            ->result_array();

        foreach ($validated as $v) {
            $code = trim($v['kode_indikator']);
            if (empty($code)) continue;

            $exists = $this->db->where('kode_indikator', $code)->where('kode_wilayah', $kode_wil)->get('early_warning')->row_array();
            if (!$exists) {
                // Tentukan arah kondisi kritis default (indikator yang nilainya makin tinggi makin berisiko)
                $aboveRiskIndicators = array(
                    'HRG-001', 'HRG-002', 'HRG-003', 'HRG-004', 'HRG-005', 'HRG-006',
                    'KES-001', 'KES-002', 'KES-004', 'KES-005', 'KES-006',
                    'TK-004', 'TK-005', 'TK-010', 'EKO-014', 'EWS-003',
                    // legacy compatibility
                    'INF_01', 'POV_01', 'TPT_01'
                );
                $dir = in_array($code, $aboveRiskIndicators) ? 'above' : 'below';
                $th = round((float)$v['avg_val'], 2);

                $insertData = array(
                    'kode_indikator'       => $code,
                    'nama_indikator'       => $v['nama_indikator'],
                    'nilai_terkini'        => $th,
                    'satuan'               => $v['satuan'],
                    'ambang_batas'         => $th,
                    'arah_risiko'          => $dir,
                    'status_risiko'        => 'Normal',
                    'sudah_ditindaklanjuti'=> 0,
                    'kode_wilayah'         => $kode_wil,
                    'is_deleted'           => 0
                );
                $this->db->insert('early_warning', $insertData);
            } else {
                // Update nama dan satuan jika ada perubahan, pastikan is_deleted = 0
                $updateData = array(
                    'nama_indikator' => $v['nama_indikator'],
                    'satuan'         => $v['satuan'],
                    'is_deleted'     => 0
                );
                $this->db->where('id', (int)$exists['id'])->update('early_warning', $updateData);
            }
        }
    }

    public function get_ews_indicators($status = null, $year = null, $kode_wilayah = null) {
        $kode_wil = $this->get_active_kode_wilayah($kode_wilayah);
        if ($status !== 'Terhapus' && $status !== 'Sampah') {
            $this->sync_all_validated_to_ews($kode_wil);
        }

        if ($status === 'Terhapus' || $status === 'Sampah') {
            $this->db->select("e.*, k.klaster as klaster_katalog");
            $this->db->from('early_warning e');
            $this->db->join("katalog_indikator k", "k.kode_indikator = e.kode_indikator AND k.is_deleted = 0", "left");
            $this->db->where('e.kode_wilayah', $kode_wil);
            $this->db->where('e.is_deleted', 1);
            $res = $this->db->order_by('e.kode_indikator', 'ASC')->get();
            $results = array();
            foreach ($res->result_array() as $row) {
                $results[] = array(
                    'id' => (int)$row['id'],
                    'code' => $row['kode_indikator'],
                    'kode_indikator' => $row['kode_indikator'],
                    'name' => $row['nama_indikator'],
                    'nama_indikator' => $row['nama_indikator'],
                    'value' => (float)$row['nilai_terkini'],
                    'nilai_terkini' => (float)$row['nilai_terkini'],
                    'currentValue' => (float)$row['nilai_terkini'],
                    'unit' => $row['satuan'],
                    'satuan' => $row['satuan'],
                    'klaster' => !empty($row['klaster_katalog']) ? $row['klaster_katalog'] : 'Umum',
                    'year' => null,
                    'tahun' => null,
                    'threshold' => (float)$row['ambang_batas'],
                    'ambang_batas' => (float)$row['ambang_batas'],
                    'direction' => $row['arah_risiko'],
                    'arah_risiko' => $row['arah_risiko'],
                    'breached' => false,
                    'distance' => 0,
                    'status' => $row['status_risiko'],
                    'status_risiko' => $row['status_risiko'],
                    'kode_wilayah' => $row['kode_wilayah'] ?? $kode_wil,
                    'is_followed_up' => (int)$row['sudah_ditindaklanjuti'],
                    'sudah_ditindaklanjuti' => (int)$row['sudah_ditindaklanjuti']
                );
            }
            return $results;
        }

        // Ambil data aktif dari early_warning
        $this->db->select("e.*, d.nilai as val_db, d.tahun as yr_db, d.satuan as sat_db, d.klaster as klaster_db, k.klaster as klaster_katalog");
        $this->db->from('early_warning e');
        $this->db->where('e.kode_wilayah', $kode_wil);
        $escapedWil = $this->db->escape($kode_wil);

        if ($year && is_numeric($year)) {
            $yInt = (int)$year;
            $this->db->join("database_ekonomi d", "d.kode_indikator = e.kode_indikator AND d.kode_wilayah = {$escapedWil} AND d.tahun = {$yInt} AND d.status_validasi = 'Tervalidasi' AND d.is_deleted = 0", "left");
        } else {
            $this->db->join("(SELECT d1.* FROM database_ekonomi d1 
                INNER JOIN (
                    SELECT kode_indikator, kode_wilayah, MAX(tahun) as max_yr 
                    FROM database_ekonomi 
                    WHERE status_validasi = 'Tervalidasi' AND is_deleted = 0 AND kode_wilayah = {$escapedWil}
                    GROUP BY kode_indikator, kode_wilayah
                ) d2 ON d1.kode_indikator = d2.kode_indikator AND d1.kode_wilayah = d2.kode_wilayah AND d1.tahun = d2.max_yr
                WHERE d1.status_validasi = 'Tervalidasi' AND d1.is_deleted = 0 AND d1.kode_wilayah = {$escapedWil}
            ) d", "d.kode_indikator = e.kode_indikator", "left");
        }
        $this->db->join("katalog_indikator k", "k.kode_indikator = e.kode_indikator AND k.is_deleted = 0", "left");

        $this->db->where('e.is_deleted', 0);
        $this->db->order_by('e.kode_indikator', 'ASC');
        $res = $this->db->get();

        $results = array();
        foreach ($res->result_array() as $row) {
            $val = ($row['val_db'] !== null) ? (float)$row['val_db'] : (float)$row['nilai_terkini'];
            $th = (float)$row['ambang_batas'];
            $dir = strtolower($row['arah_risiko'] ?? 'above');
            if ($dir !== 'below') {
                $dir = 'above';
            }

            if ($val == $th) {
                $statusRisiko = 'Waspada';
            } elseif ($dir === 'below') {
                $statusRisiko = ($val < $th) ? 'Kritis' : 'Normal';
            } else { // 'above'
                $statusRisiko = ($val > $th) ? 'Kritis' : 'Normal';
            }

            $distance = ($th == 0) ? (abs($val) * 100) : (abs($val - $th) / abs($th) * 100);
            $breached = ($dir === 'below') ? ($val <= $th) : ($val >= $th);

            $results[] = array(
                'id' => (int)$row['id'],
                'code' => $row['kode_indikator'],
                'kode_indikator' => $row['kode_indikator'],
                'name' => $row['nama_indikator'],
                'nama_indikator' => $row['nama_indikator'],
                'value' => $val,
                'nilai_terkini' => $val,
                'currentValue' => $val,
                'unit' => $row['sat_db'] ?: $row['satuan'],
                'satuan' => $row['sat_db'] ?: $row['satuan'],
                'klaster' => !empty($row['klaster_db']) ? $row['klaster_db'] : (!empty($row['klaster_katalog']) ? $row['klaster_katalog'] : 'Umum'),
                'year' => (int)$row['yr_db'],
                'tahun' => (int)$row['yr_db'],
                'threshold' => $th,
                'ambang_batas' => $th,
                'direction' => $dir,
                'arah_risiko' => $dir,
                'breached' => $breached,
                'distance' => round($distance, 2),
                'status' => $statusRisiko,
                'status_risiko' => $statusRisiko,
                'kode_wilayah' => $row['kode_wilayah'] ?? $kode_wil,
                'is_followed_up' => (int)$row['sudah_ditindaklanjuti'],
                'sudah_ditindaklanjuti' => (int)$row['sudah_ditindaklanjuti']
            );
        }
        return $results;
    }

    public function update_ews_threshold($code, $threshold, $direction = null, $value = null, $kode_wilayah = null) {
        $kode_wil = $this->get_active_kode_wilayah($kode_wilayah);
        $th = (float)$threshold;
        $row = $this->db->where('kode_indikator', $code)->where('kode_wilayah', $kode_wil)->get('early_warning')->row_array();

        if ($value !== null && is_numeric($value)) {
            $val = (float)$value;
        } else {
            // Ambil nilai riil terkini yang tervalidasi dari database_ekonomi
            $latest = $this->db->select('nilai')
                ->where('kode_indikator', $code)
                ->where('kode_wilayah', $kode_wil)
                ->where('status_validasi', 'Tervalidasi')
                ->where('is_deleted', 0)
                ->order_by('tahun', 'DESC')
                ->get('database_ekonomi')
                ->row_array();
            $val = $latest ? (float)$latest['nilai'] : ($row ? (float)$row['nilai_terkini'] : 0);
        }

        $dir = $direction ? strtolower($direction) : strtolower($row['arah_risiko'] ?? 'above');
        if ($dir !== 'below') {
            $dir = 'above';
        }

        if ($val == $th) {
            $st = 'Waspada';
        } elseif ($dir === 'below') {
            $st = ($val < $th) ? 'Kritis' : 'Normal';
        } else {
            $st = ($val > $th) ? 'Kritis' : 'Normal';
        }

        $updateData = array(
            'ambang_batas'  => $th,
            'nilai_terkini' => $val,
            'status_risiko' => $st
        );
        if ($direction !== null) {
            $updateData['arah_risiko'] = $dir;
        }

        $this->db->where('kode_indikator', $code)->where('kode_wilayah', $kode_wil);
        $res = $this->db->update('early_warning', $updateData);
        return array(
            'success'       => (bool)$res,
            'value'         => $val,
            'nilai_terkini' => $val,
            'threshold'     => $th,
            'direction'     => $dir,
            'status'        => $st
        );
    }

    public function toggle_ews_followup($code, $kode_wilayah = null) {
        $kode_wil = $this->get_active_kode_wilayah($kode_wilayah);
        $item = $this->db->where('kode_indikator', $code)->where('kode_wilayah', $kode_wil)->get('early_warning')->row();
        if ($item) {
            $next = $item->sudah_ditindaklanjuti ? 0 : 1;
            $this->db->where('kode_indikator', $code)->where('kode_wilayah', $kode_wil)->update('early_warning', array('sudah_ditindaklanjuti' => $next));
            return $next;
        }
        return false;
    }

    public function get_beranda_cards($year = null, $status = null, $kode_wilayah = null) {
        $kode = $this->get_active_kode_wilayah($kode_wilayah);
        $this->db->where('is_deleted', 0);
        $this->db->where('status_validasi !=', 'Ditolak');
        $this->db->where('kode_wilayah', $kode);

        if ($year && is_numeric($year)) {
            $yearInt = (int)$year;
            $this->db->where('tahun', $yearInt);
            $this->db->order_by('id', 'ASC');
            $records = $this->db->get('database_ekonomi')->result_array();
        } else {
            // Semua Tahun / Default: Ambil seluruh data indikator yang telah diinput (rekaman terbaru per kode)
            $this->db->order_by('kode_indikator', 'ASC');
            $this->db->order_by('tahun', 'DESC');
            $rawRecords = $this->db->get('database_ekonomi')->result_array();
            $byCode = array();
            foreach ($rawRecords as $r) {
                if (!isset($byCode[$r['kode_indikator']])) {
                    $byCode[$r['kode_indikator']] = $r;
                }
            }
            $records = array_values($byCode);
        }

        if (empty($records)) {
            return array();
        }

        $cards = array();
        $lower_is_better = array('inflasi', 'ihk', 'pengangguran', 'tpt', 'kemiskinan', 'miskin', 'gini');

        foreach ($records as $row) {
            $code = $row['kode_indikator'];
            $current_val = (float)$row['nilai'];
            $rowYear = (int)$row['tahun'];

            // Komparasi tahun sebelumnya
            $prev_row = $this->db->where('is_deleted', 0)
                ->where('kode_indikator', $code)
                ->where('kode_wilayah', $row['kode_wilayah'] ?? $kode)
                ->where('tahun', $rowYear - 1)
                ->get('database_ekonomi')
                ->row_array();

            $is_pct = (trim($row['satuan']) === '%');
            if (!empty($prev_row)) {
                $prev_val = (float)$prev_row['nilai'];
                $delta = round($current_val - $prev_val, 2);
                $unit_suffix = $is_pct ? ' pp' : (' ' . $row['satuan']);
                $perubahan = ($delta >= 0 ? '+' : '') . number_format($delta, 2, ',', '.') . $unit_suffix;
                $trend = ($delta >= 0) ? 'up' : 'down';
            } else {
                $delta = 0;
                $perubahan = '+0,00' . ($is_pct ? ' pp' : '');
                $trend = 'up';
            }

            // Logika tone & status indikator
            $is_lower_better = false;
            $ind_lower = strtolower($row['nama_indikator']);
            foreach ($lower_is_better as $needle) {
                if (strpos($ind_lower, $needle) !== false) {
                    $is_lower_better = true;
                    break;
                }
            }

            if ($is_lower_better) {
                if ($delta < 0) {
                    $card_status = (strpos($ind_lower, 'inflasi') !== false) ? 'Terkendali' : 'Membaik';
                } elseif ($delta > 0) {
                    $card_status = 'Perlu perhatian';
                } else {
                    $card_status = 'Stabil';
                }
            } else {
                if ($delta > 0) {
                    $card_status = 'Meningkat';
                } elseif ($delta < 0) {
                    $card_status = 'Menurun';
                } else {
                    $card_status = 'Stabil';
                }
            }

            // Deret historis untuk sparkline grafik mini
            $hist_rows = $this->db->where('is_deleted', 0)
                ->where('kode_indikator', $code)
                ->where('kode_wilayah', $row['kode_wilayah'] ?? $kode)
                ->where('tahun <=', $rowYear)
                ->order_by('tahun', 'ASC')
                ->get('database_ekonomi')
                ->result_array();

            $series_vals = array();
            if (count($hist_rows) > 1) {
                foreach ($hist_rows as $hr) {
                    $series_vals[] = (float)$hr['nilai'];
                }
            } else {
                $factor = ($trend === 'up') ? 0.95 : 1.05;
                $series_vals = array(
                    round($current_val * pow($factor, 3), 2),
                    round($current_val * pow($factor, 2), 2),
                    round($current_val * $factor, 2),
                    $current_val
                );
            }

            $cards[] = array(
                'id'              => (int)$row['id'],
                'kode_indikator'  => $row['kode_indikator'],
                'nama_indikator'  => $row['nama_indikator'],
                'klaster'         => !empty($row['klaster']) ? $row['klaster'] : 'Output & Pertumbuhan',
                'nilai'           => number_format($current_val, 2, ',', '.'),
                'satuan'          => $row['satuan'],
                'perubahan'       => $perubahan,
                'arah_tren'       => $trend,
                'status'          => $card_status,
                'deret_mini'      => json_encode($series_vals),
                'tahun'           => $rowYear,
                'wilayah'         => $row['wilayah'],
                'kode_wilayah'    => $row['kode_wilayah'] ?? $kode,
                'sumber_data'     => $row['sumber_data'],
                'status_validasi' => $row['status_validasi']
            );
        }

        return $cards;
    }

    public function get_beranda_categories($year = null, $kode_wilayah = null) {
        $kode = $this->get_active_kode_wilayah($kode_wilayah);
        $this->db->select('klaster, COUNT(*) as total');
        $this->db->where('is_deleted', 0);
        $this->db->where('status_validasi !=', 'Ditolak');
        $this->db->where('kode_wilayah', $kode);
        if ($year && is_numeric($year)) {
            $this->db->where('tahun', (int)$year);
        }
        $this->db->group_by('klaster');
        $this->db->order_by('total', 'DESC');
        $res = $this->db->get('database_ekonomi')->result_array();

        $cats = array();
        foreach ($res as $r) {
            $cluster = !empty($r['klaster']) ? $r['klaster'] : 'Output & Pertumbuhan';
            $cats[$cluster] = (int)$r['total'];
        }
        return $cats;
    }

    public function get_economic_insights($year = null, $status = null, $kode_wilayah = null) {
        $kode = $this->get_active_kode_wilayah($kode_wilayah);
        $this->db->where('kode_wilayah', $kode);
        if ($status === 'Terhapus' || $status === 'Sampah') {
            $this->db->where('is_deleted', 1);
        } else {
            $this->db->where('is_deleted', 0);
            if ($year) {
                $this->db->where('tahun', (int)$year);
            }
        }
        return $this->db->order_by('id', 'ASC')->get('economic_insight')->result_array();
    }

    public function get_laporan_ekonomi($status = null, $year = null, $kode_wilayah = null) {
        $kode = $this->get_active_kode_wilayah($kode_wilayah);
        $this->db->where('kode_wilayah', $kode);
        if ($status === 'Terhapus' || $status === 'Sampah') {
            $this->db->where('is_deleted', 1);
        } else {
            $this->db->where('is_deleted', 0);
            if ($year && is_numeric($year)) {
                $this->db->where('periode_tahun', (int)$year);
            }
        }
        return $this->db->order_by('id', 'DESC')->get('laporan_ekonomi')->result_array();
    }

    // Mathematical Economic Analysis Functions

    public function calculate_lq($sectors = null) {
        if ($sectors === null) {
            $sectors = $this->get_sectors();
        }

        $localTotal = 0;
        $referenceTotal = 0;
        foreach ($sectors as $s) {
            $localTotal += $s['employmentCurrent'];
            $referenceTotal += $s['provinceEmploymentCurrent'];
        }

        $rows = array();
        foreach ($sectors as $s) {
            $localShare = ($localTotal > 0) ? ($s['employmentCurrent'] / $localTotal) : 0;
            $referenceShare = ($referenceTotal > 0) ? ($s['provinceEmploymentCurrent'] / $referenceTotal) : 0;
            $lq = ($referenceShare > 0) ? ($localShare / $referenceShare) : 0;

            $s['localShare'] = round($localShare * 100, 2);
            $s['referenceShare'] = round($referenceShare * 100, 2);
            $s['lq'] = round($lq, 3);
            $s['classification'] = ($lq > 1) ? 'Basis' : 'Nonbasis';
            $s['interpretation'] = ($lq > 1) ? 'Konsentrasi relatif lebih tinggi daripada provinsi' : 'Konsentrasi relatif lebih rendah daripada provinsi';
            $rows[] = $s;
        }
        return $rows;
    }

    public function calculate_shift_share($sectors = null) {
        if ($sectors === null) {
            $sectors = $this->get_sectors();
        }

        $localTotalBase = 0;
        $localTotalCurrent = 0;
        $referenceTotalBase = 0;
        $referenceTotalCurrent = 0;

        foreach ($sectors as $s) {
            $localTotalBase += $s['employmentBase'];
            $localTotalCurrent += $s['employmentCurrent'];
            $referenceTotalBase += $s['provinceEmploymentBase'];
            $referenceTotalCurrent += $s['provinceEmploymentCurrent'];
        }

        $provincialGrowth = ($referenceTotalBase > 0) ? ($referenceTotalCurrent / $referenceTotalBase - 1) : 0;

        $rows = array();
        $sumProvEffect = 0;
        $sumIndustryMix = 0;
        $sumRegionalShift = 0;

        foreach ($sectors as $s) {
            $localGrowth = ($s['employmentBase'] > 0) ? ($s['employmentCurrent'] / $s['employmentBase'] - 1) : 0;
            $sectorRefGrowth = ($s['provinceEmploymentBase'] > 0) ? ($s['provinceEmploymentCurrent'] / $s['provinceEmploymentBase'] - 1) : 0;

            $provGrowthEffect = $s['employmentBase'] * $provincialGrowth;
            $industryMix = $s['employmentBase'] * ($sectorRefGrowth - $provincialGrowth);
            $regShift = $s['employmentBase'] * ($localGrowth - $sectorRefGrowth);
            $actualChange = $s['employmentCurrent'] - $s['employmentBase'];

            $sumProvEffect += $provGrowthEffect;
            $sumIndustryMix += $industryMix;
            $sumRegionalShift += $regShift;

            $s['localGrowth'] = round($localGrowth * 100, 2);
            $s['sectorReferenceGrowth'] = round($sectorRefGrowth * 100, 2);
            $s['provincialGrowthEffect'] = round($provGrowthEffect, 0);
            $s['industryMix'] = round($industryMix, 0);
            $s['regionalShift'] = round($regShift, 0);
            $s['actualChange'] = round($actualChange, 0);
            $s['totalEffect'] = round($provGrowthEffect + $industryMix + $regShift, 0);
            $rows[] = $s;
        }

        return array(
            'rows' => $rows,
            'provincialGrowth' => round($provincialGrowth * 100, 2),
            'localGrowth' => round((($localTotalCurrent / ($localTotalBase ?: 1)) - 1) * 100, 2),
            'localTotalBase' => $localTotalBase,
            'localTotalCurrent' => $localTotalCurrent,
            'components' => array(
                'provincialGrowthEffect' => round($sumProvEffect, 0),
                'industryMix' => round($sumIndustryMix, 0),
                'regionalShift' => round($sumRegionalShift, 0),
                'actualChange' => round($localTotalCurrent - $localTotalBase, 0)
            )
        );
    }

    public function calculate_klassen($sectors = null, $regionalGrowth = null) {
        if ($sectors === null) {
            $sectors = $this->get_sectors();
        }
        if ($regionalGrowth === null) {
            $series = $this->get_economic_series();
            $regionalGrowth = end($series['growth']);
        }

        $sumShare = 0;
        foreach ($sectors as $s) {
            $sumShare += $s['shareLocal'];
        }
        $avgShare = count($sectors) ? ($sumShare / count($sectors)) : 0;

        $rows = array();
        foreach ($sectors as $s) {
            $shareAbove = $s['shareLocal'] >= $avgShare;
            $growthAbove = $s['growthLocal'] >= $regionalGrowth;

            if ($shareAbove && $growthAbove) {
                $quadrant = 'I';
                $classification = 'Sektor maju dan tumbuh cepat';
            } elseif ($shareAbove && !$growthAbove) {
                $quadrant = 'II';
                $classification = 'Sektor maju tetapi tertekan';
            } elseif (!$shareAbove && $growthAbove) {
                $quadrant = 'III';
                $classification = 'Sektor potensial berkembang';
            } else {
                $quadrant = 'IV';
                $classification = 'Sektor relatif tertinggal';
            }

            $s['averageSectorShare'] = round($avgShare, 2);
            $s['regionalGrowth'] = round($regionalGrowth, 2);
            $s['quadrant'] = $quadrant;
            $s['classification'] = $classification;
            $s['shareAbove'] = $shareAbove;
            $s['growthAbove'] = $growthAbove;
            $s['shareGap'] = round($s['shareLocal'] - $avgShare, 2);
            $s['growthGap'] = round($s['growthLocal'] - $regionalGrowth, 2);
            $rows[] = $s;
        }

        return $rows;
    }

    public function pearson_correlation($x, $y) {
        $n = min(count($x), count($y));
        if ($n < 2) return 0;

        $xMean = array_sum(array_slice($x, 0, $n)) / $n;
        $yMean = array_sum(array_slice($y, 0, $n)) / $n;

        $num = 0;
        $denX = 0;
        $denY = 0;

        for ($i = 0; $i < $n; $i++) {
            $xd = $x[$i] - $xMean;
            $yd = $y[$i] - $yMean;
            $num += ($xd * $yd);
            $denX += ($xd * $xd);
            $denY += ($yd * $yd);
        }

        $den = sqrt($denX) * sqrt($denY);
        return ($den == 0) ? 0 : round($num / $den, 4);
    }

    public function correlation_strength($r) {
        $abs = abs($r);
        if ($abs >= 0.8) return 'Sangat kuat';
        if ($abs >= 0.6) return 'Kuat';
        if ($abs >= 0.4) return 'Sedang';
        if ($abs >= 0.2) return 'Lemah';
        return 'Sangat lemah';
    }

    public function forecast_series($values, $horizon = 3, $method = 'Tren Linear') {
        $safe = count($values) ? array_values($values) : array(0);
        $predictions = array();
        $rmse = 0.25;

        if ($method === 'Rata-rata Bergerak 3 Tahun') {
            $history = $safe;
            for ($i = 0; $i < $horizon; $i++) {
                $last3 = array_slice($history, -3);
                $next = array_sum($last3) / count($last3);
                $predictions[] = round($next, 2);
                $history[] = $next;
            }
            if (count($safe) > 3) {
                $errors = array();
                for ($i = 3; $i < count($safe); $i++) {
                    $m = array_sum(array_slice($safe, $i - 3, 3)) / 3;
                    $errors[] = pow($safe[$i] - $m, 2);
                }
                $rmse = sqrt(array_sum($errors) / count($errors)) ?: 0.25;
            }
        } elseif ($method === 'Pemulusan Eksponensial') {
            $alpha = 0.5;
            $level = $safe[0];
            $errors = array();
            for ($i = 1; $i < count($safe); $i++) {
                $errors[] = pow($safe[$i] - $level, 2);
                $level = $alpha * $safe[$i] + (1 - $alpha) * $level;
            }
            for ($i = 0; $i < $horizon; $i++) {
                $predictions[] = round($level, 2);
            }
            $rmse = count($errors) ? (sqrt(array_sum($errors) / count($errors)) ?: 0.25) : 0.25;
        } else {
            // Linear Trend Fit: y = a + bx
            $n = count($safe);
            $xMean = ($n - 1) / 2;
            $yMean = array_sum($safe) / $n;
            $num = 0;
            $den = 0;
            for ($i = 0; $i < $n; $i++) {
                $num += ($i - $xMean) * ($safe[$i] - $yMean);
                $den += pow($i - $xMean, 2);
            }
            $slope = ($den != 0) ? ($num / $den) : 0;
            $intercept = $yMean - $slope * $xMean;

            $errors = array();
            for ($i = 0; $i < $n; $i++) {
                $fitted = $intercept + $slope * $i;
                $errors[] = pow($safe[$i] - $fitted, 2);
            }
            $rmse = sqrt(array_sum($errors) / $n) ?: 0.25;

            for ($i = 0; $i < $horizon; $i++) {
                $pred = $intercept + $slope * ($n + $i);
                $predictions[] = round($pred, 2);
            }
        }

        $lower = array();
        $upper = array();
        foreach ($predictions as $idx => $p) {
            $margin = 1.96 * $rmse * sqrt(1 + $idx * 0.35);
            $lower[] = round($p - $margin, 2);
            $upper[] = round($p + $margin, 2);
        }

        return array(
            'forecast' => $predictions,
            'lower' => $lower,
            'upper' => $upper,
            'method' => $method,
            'error' => round($rmse, 3)
        );
    }

    // ==========================================
    // CRUD & SOFT DELETE: BERANDA (KPI METRICS)
    // ==========================================
    public function get_beranda_by_id($id) {
        return $this->db->where('id', (int)$id)->get('beranda')->row_array();
    }

    public function add_beranda($data) {
        $record = array(
            'nama_indikator' => trim($data['nama_indikator'] ?? $data['indicator'] ?? ''),
            'nilai'          => trim($data['nilai'] ?? $data['value'] ?? ''),
            'satuan'         => trim($data['satuan'] ?? $data['unit'] ?? ''),
            'perubahan'      => trim($data['perubahan'] ?? $data['change'] ?? '+0,0%'),
            'arah_tren'      => in_array($data['arah_tren'] ?? '', array('up','down')) ? $data['arah_tren'] : 'up',
            'status'         => trim($data['status'] ?? 'Stabil'),
            'deret_mini'     => !empty($data['deret_mini']) ? (is_array($data['deret_mini']) ? json_encode($data['deret_mini']) : $data['deret_mini']) : '[20, 25, 24, 28, 32, 36, 40]',
            'tahun'          => (int)($data['tahun'] ?? $data['year'] ?? 2024),
            'kode_wilayah'   => !empty($data['kode_wilayah']) ? trim($data['kode_wilayah']) : $this->get_active_kode_wilayah(),
            'is_deleted'     => 0,
            'deleted_at'     => null
        );
        if (empty($record['nama_indikator'])) return false;
        return $this->db->insert('beranda', $record);
    }

    public function update_beranda($id, $data) {
        $record = array();
        if (isset($data['nama_indikator']) || isset($data['indicator'])) $record['nama_indikator'] = trim($data['nama_indikator'] ?? $data['indicator']);
        if (isset($data['nilai']) || isset($data['value'])) $record['nilai'] = trim($data['nilai'] ?? $data['value']);
        if (isset($data['satuan']) || isset($data['unit'])) $record['satuan'] = trim($data['satuan'] ?? $data['unit']);
        if (isset($data['perubahan']) || isset($data['change'])) $record['perubahan'] = trim($data['perubahan'] ?? $data['change']);
        if (isset($data['arah_tren'])) $record['arah_tren'] = in_array($data['arah_tren'], array('up','down')) ? $data['arah_tren'] : 'up';
        if (isset($data['status'])) $record['status'] = trim($data['status']);
        if (isset($data['tahun']) || isset($data['year'])) $record['tahun'] = (int)($data['tahun'] ?? $data['year']);
        if (empty($record)) return false;
        return $this->db->where('id', (int)$id)->update('beranda', $record);
    }

    public function soft_delete_beranda($id) {
        return $this->db->where('id', (int)$id)->update('beranda', array('is_deleted' => 1, 'deleted_at' => date('Y-m-d H:i:s')));
    }

    public function restore_beranda($id) {
        return $this->db->where('id', (int)$id)->update('beranda', array('is_deleted' => 0, 'deleted_at' => null));
    }

    public function hard_delete_beranda($id) {
        return $this->db->where('id', (int)$id)->delete('beranda');
    }

    public function count_trash_beranda() {
        return $this->db->where('is_deleted', 1)->where('kode_wilayah', $this->get_active_kode_wilayah())->count_all_results('beranda');
    }

    // ==========================================
    // CRUD & SOFT DELETE: ANALISIS EKONOMI (SEKTOR)
    // ==========================================
    public function get_sector_by_id($id) {
        return $this->db->where('id', (int)$id)->get('analisis_ekonomi')->row_array();
    }

    public function add_sector($data) {
        $kode = trim($data['kode_sektor'] ?? $data['code'] ?? ('SEK-' . rand(100, 999)));
        $nama = trim($data['nama_sektor'] ?? $data['name'] ?? '');
        $short = trim($data['nama_pendek'] ?? $data['short'] ?? $nama);
        if (empty($nama)) return false;
        $kw = !empty($data['kode_wilayah']) ? trim($data['kode_wilayah']) : $this->get_active_kode_wilayah();

        $record = array(
            'kode_sektor'                 => $kode,
            'nama_sektor'                 => $nama,
            'nama_pendek'                 => $short,
            'porsi_daerah'                => (float)($data['porsi_daerah'] ?? 5.0),
            'porsi_provinsi'              => (float)($data['porsi_provinsi'] ?? 5.0),
            'pertumbuhan_daerah'          => (float)($data['pertumbuhan_daerah'] ?? 4.5),
            'pertumbuhan_provinsi'        => (float)($data['pertumbuhan_provinsi'] ?? 4.5),
            'tenaga_kerja_awal'           => (int)($data['tenaga_kerja_awal'] ?? 3000),
            'tenaga_kerja_kini'           => (int)($data['tenaga_kerja_kini'] ?? 3500),
            'tenaga_kerja_provinsi_awal'  => (int)($data['tenaga_kerja_provinsi_awal'] ?? 200000),
            'tenaga_kerja_provinsi_kini'  => (int)($data['tenaga_kerja_provinsi_kini'] ?? 240000),
            'deret_pertumbuhan'           => !empty($data['deret_pertumbuhan']) ? (is_array($data['deret_pertumbuhan']) ? json_encode($data['deret_pertumbuhan']) : $data['deret_pertumbuhan']) : '[4.0, 4.2, 4.5, 4.8, 5.0, -2.5, 3.5, 4.2, 4.5, 4.8]',
            'deret_pertumbuhan_provinsi'  => !empty($data['deret_pertumbuhan_provinsi']) ? (is_array($data['deret_pertumbuhan_provinsi']) ? json_encode($data['deret_pertumbuhan_provinsi']) : $data['deret_pertumbuhan_provinsi']) : '[4.2, 4.5, 4.6, 4.7, 4.9, -2.8, 3.2, 4.0, 4.4, 4.6]',
            'kode_wilayah'                => $kw,
            'is_deleted'                  => 0,
            'deleted_at'                  => null
        );

        $exists = $this->db->where('kode_sektor', $kode)->where('kode_wilayah', $kw)->where('is_deleted', 0)->get('analisis_ekonomi')->num_rows();
        if ($exists > 0) return false;
        return $this->db->insert('analisis_ekonomi', $record);
    }

    public function update_sector($id, $data) {
        $record = array();
        if (isset($data['kode_sektor'])) $record['kode_sektor'] = trim($data['kode_sektor']);
        if (isset($data['nama_sektor'])) $record['nama_sektor'] = trim($data['nama_sektor']);
        if (isset($data['nama_pendek'])) $record['nama_pendek'] = trim($data['nama_pendek']);
        if (isset($data['porsi_daerah'])) $record['porsi_daerah'] = (float)$data['porsi_daerah'];
        if (isset($data['porsi_provinsi'])) $record['porsi_provinsi'] = (float)$data['porsi_provinsi'];
        if (isset($data['pertumbuhan_daerah'])) $record['pertumbuhan_daerah'] = (float)$data['pertumbuhan_daerah'];
        if (isset($data['pertumbuhan_provinsi'])) $record['pertumbuhan_provinsi'] = (float)$data['pertumbuhan_provinsi'];
        if (isset($data['tenaga_kerja_awal'])) $record['tenaga_kerja_awal'] = (int)$data['tenaga_kerja_awal'];
        if (isset($data['tenaga_kerja_kini'])) $record['tenaga_kerja_kini'] = (int)$data['tenaga_kerja_kini'];
        if (empty($record)) return false;
        return $this->db->where('id', (int)$id)->update('analisis_ekonomi', $record);
    }

    public function soft_delete_sector($id) {
        return $this->db->where('id', (int)$id)->update('analisis_ekonomi', array('is_deleted' => 1, 'deleted_at' => date('Y-m-d H:i:s')));
    }

    public function restore_sector($id) {
        return $this->db->where('id', (int)$id)->update('analisis_ekonomi', array('is_deleted' => 0, 'deleted_at' => null));
    }

    public function hard_delete_sector($id) {
        return $this->db->where('id', (int)$id)->delete('analisis_ekonomi');
    }

    public function count_trash_sectors() {
        return $this->db->where('is_deleted', 1)->where('kode_wilayah', $this->get_active_kode_wilayah())->count_all_results('analisis_ekonomi');
    }

    // ==========================================
    // CRUD & SOFT DELETE: PETA EKONOMI (KECAMATAN)
    // ==========================================
    public function get_district_by_id($id) {
        return $this->db->where('id', (int)$id)->get('peta_ekonomi')->row_array();
    }

    public function add_district($data) {
        $nama = trim($data['nama_kecamatan'] ?? $data['name'] ?? '');
        if (empty($nama)) return false;
        $kw = !empty($data['kode_wilayah']) ? trim($data['kode_wilayah']) : $this->get_active_kode_wilayah();

        $record = array(
            'nama_kecamatan' => $nama,
            'porsi_pdrb'     => (float)($data['porsi_pdrb'] ?? $data['share'] ?? 5.0),
            'pertumbuhan'    => (float)($data['pertumbuhan'] ?? $data['growth'] ?? 5.0),
            'kemiskinan'     => (float)($data['kemiskinan'] ?? $data['poverty'] ?? 6.0),
            'kepadatan'      => (int)($data['kepadatan'] ?? $data['density'] ?? 1000),
            'kategori'       => trim($data['kategori'] ?? $data['category'] ?? 'Perdagangan & Jasa'),
            'garis_lintang'  => (float)($data['garis_lintang'] ?? $data['lat'] ?? -8.2192),
            'garis_bujur'    => (float)($data['garis_bujur'] ?? $data['lng'] ?? 114.3692),
            'kode_wilayah'   => $kw,
            'is_deleted'     => 0,
            'deleted_at'     => null
        );

        $exists = $this->db->where('nama_kecamatan', $nama)->where('kode_wilayah', $kw)->where('is_deleted', 0)->get('peta_ekonomi')->num_rows();
        if ($exists > 0) return false;
        return $this->db->insert('peta_ekonomi', $record);
    }

    public function update_district($id, $data) {
        $record = array();
        if (isset($data['nama_kecamatan'])) $record['nama_kecamatan'] = trim($data['nama_kecamatan']);
        if (isset($data['porsi_pdrb'])) $record['porsi_pdrb'] = (float)$data['porsi_pdrb'];
        if (isset($data['pertumbuhan'])) $record['pertumbuhan'] = (float)$data['pertumbuhan'];
        if (isset($data['kemiskinan'])) $record['kemiskinan'] = (float)$data['kemiskinan'];
        if (isset($data['kepadatan'])) $record['kepadatan'] = (int)$data['kepadatan'];
        if (isset($data['kategori'])) $record['kategori'] = trim($data['kategori']);
        if (isset($data['garis_lintang'])) $record['garis_lintang'] = (float)$data['garis_lintang'];
        if (isset($data['garis_bujur'])) $record['garis_bujur'] = (float)$data['garis_bujur'];
        if (empty($record)) return false;
        return $this->db->where('id', (int)$id)->update('peta_ekonomi', $record);
    }

    public function soft_delete_district($id) {
        return $this->db->where('id', (int)$id)->update('peta_ekonomi', array('is_deleted' => 1, 'deleted_at' => date('Y-m-d H:i:s')));
    }

    public function restore_district($id) {
        return $this->db->where('id', (int)$id)->update('peta_ekonomi', array('is_deleted' => 0, 'deleted_at' => null));
    }

    public function hard_delete_district($id) {
        return $this->db->where('id', (int)$id)->delete('peta_ekonomi');
    }

    public function count_trash_districts() {
        return $this->db->where('is_deleted', 1)->where('kode_wilayah', $this->get_active_kode_wilayah())->count_all_results('peta_ekonomi');
    }

    // ==========================================
    // CRUD & SOFT DELETE: PROSPEK EKONOMI (PROYEKSI)
    // ==========================================
    public function get_prospek_records($status = null) {
        $kw = $this->get_active_kode_wilayah();
        $this->db->where('kode_wilayah', $kw);
        if ($status === 'Terhapus' || $status === 'Sampah') {
            $this->db->where('is_deleted', 1);
        } else {
            $this->db->where('is_deleted', 0);
        }
        return $this->db->order_by('tahun', 'ASC')->get('prospek_ekonomi')->result_array();
    }

    public function get_prospek_by_id($id) {
        return $this->db->where('id', (int)$id)->get('prospek_ekonomi')->row_array();
    }

    public function add_prospek($data) {
        $tahun = (int)($data['tahun'] ?? $data['year'] ?? 0);
        if (!$tahun) return false;
        $kw = !empty($data['kode_wilayah']) ? trim($data['kode_wilayah']) : $this->get_active_kode_wilayah();

        $record = array(
            'tahun'                 => $tahun,
            'pertumbuhan_ekonomi'   => (float)($data['pertumbuhan_ekonomi'] ?? 5.2),
            'pertumbuhan_jatim'     => (float)($data['pertumbuhan_jatim'] ?? 5.0),
            'pertumbuhan_nasional'  => (float)($data['pertumbuhan_nasional'] ?? 5.1),
            'tingkat_pengangguran'  => (float)($data['tingkat_pengangguran'] ?? 4.0),
            'pertumbuhan_investasi' => (float)($data['pertumbuhan_investasi'] ?? 10.0),
            'tingkat_kemiskinan'    => (float)($data['tingkat_kemiskinan'] ?? 7.0),
            'inflasi'               => (float)($data['inflasi'] ?? 2.5),
            'ipm'                   => (float)($data['ipm'] ?? 74.0),
            'pdrb_per_kapita'       => (float)($data['pdrb_per_kapita'] ?? 65.0),
            'kode_wilayah'          => $kw,
            'is_deleted'            => 0,
            'deleted_at'            => null
        );

        $exists = $this->db->where('tahun', $tahun)->where('kode_wilayah', $kw)->where('is_deleted', 0)->get('prospek_ekonomi')->num_rows();
        if ($exists > 0) return false;
        return $this->db->insert('prospek_ekonomi', $record);
    }

    public function update_prospek($id, $data) {
        $record = array();
        if (isset($data['tahun'])) $record['tahun'] = (int)$data['tahun'];
        if (isset($data['pertumbuhan_ekonomi'])) $record['pertumbuhan_ekonomi'] = (float)$data['pertumbuhan_ekonomi'];
        if (isset($data['pertumbuhan_jatim'])) $record['pertumbuhan_jatim'] = (float)$data['pertumbuhan_jatim'];
        if (isset($data['pertumbuhan_nasional'])) $record['pertumbuhan_nasional'] = (float)$data['pertumbuhan_nasional'];
        if (isset($data['tingkat_pengangguran'])) $record['tingkat_pengangguran'] = (float)$data['tingkat_pengangguran'];
        if (isset($data['pertumbuhan_investasi'])) $record['pertumbuhan_investasi'] = (float)$data['pertumbuhan_investasi'];
        if (isset($data['tingkat_kemiskinan'])) $record['tingkat_kemiskinan'] = (float)$data['tingkat_kemiskinan'];
        if (isset($data['inflasi'])) $record['inflasi'] = (float)$data['inflasi'];
        if (isset($data['ipm'])) $record['ipm'] = (float)$data['ipm'];
        if (isset($data['pdrb_per_kapita'])) $record['pdrb_per_kapita'] = (float)$data['pdrb_per_kapita'];
        if (empty($record)) return false;
        return $this->db->where('id', (int)$id)->update('prospek_ekonomi', $record);
    }

    public function soft_delete_prospek($id) {
        return $this->db->where('id', (int)$id)->update('prospek_ekonomi', array('is_deleted' => 1, 'deleted_at' => date('Y-m-d H:i:s')));
    }

    public function restore_prospek($id) {
        return $this->db->where('id', (int)$id)->update('prospek_ekonomi', array('is_deleted' => 0, 'deleted_at' => null));
    }

    public function hard_delete_prospek($id) {
        return $this->db->where('id', (int)$id)->delete('prospek_ekonomi');
    }

    public function count_trash_prospek() {
        return $this->db->where('is_deleted', 1)->where('kode_wilayah', $this->get_active_kode_wilayah())->count_all_results('prospek_ekonomi');
    }

    // ==========================================
    // CRUD & SOFT DELETE: EARLY WARNING SYSTEM (EWS)
    // ==========================================
    public function get_ews_by_id($id) {
        $row = $this->db->where('id', (int)$id)->get('early_warning')->row_array();
        if (!$row) return null;
        $row['code'] = $row['kode_indikator'];
        $row['name'] = $row['nama_indikator'];
        $row['value'] = (float)$row['nilai_terkini'];
        $row['currentValue'] = (float)$row['nilai_terkini'];
        $row['unit'] = $row['satuan'];
        $row['threshold'] = (float)$row['ambang_batas'];
        $row['direction'] = $row['arah_risiko'];
        $row['status'] = $row['status_risiko'];
        return $row;
    }

    public function add_ews($data) {
        $kode = trim($data['kode_indikator'] ?? $data['code'] ?? '');
        $nama = trim($data['nama_indikator'] ?? $data['name'] ?? '');
        if (empty($kode) || empty($nama)) return false;
        $kw = !empty($data['kode_wilayah']) ? trim($data['kode_wilayah']) : $this->get_active_kode_wilayah();

        $val = (float)($data['nilai_terkini'] ?? $data['value'] ?? 0);
        $th = (float)($data['ambang_batas'] ?? $data['threshold'] ?? 0);
        $dir = strtolower($data['arah_risiko'] ?? $data['direction'] ?? 'above');
        if ($dir !== 'below') {
            $dir = 'above';
        }

        if ($val == $th) {
            $status_risiko = 'Waspada';
        } elseif ($dir === 'below') {
            $status_risiko = ($val < $th) ? 'Kritis' : 'Normal';
        } else {
            $status_risiko = ($val > $th) ? 'Kritis' : 'Normal';
        }

        $record = array(
            'kode_indikator'        => $kode,
            'nama_indikator'        => $nama,
            'nilai_terkini'         => $val,
            'satuan'                => trim($data['satuan'] ?? $data['unit'] ?? '%'),
            'ambang_batas'          => $th,
            'arah_risiko'           => $dir,
            'status_risiko'         => $status_risiko,
            'sudah_ditindaklanjuti' => (int)($data['sudah_ditindaklanjuti'] ?? 0),
            'kode_wilayah'          => $kw,
            'is_deleted'            => 0,
            'deleted_at'            => null
        );

        $exists = $this->db->where('kode_indikator', $kode)->where('kode_wilayah', $kw)->where('is_deleted', 0)->get('early_warning')->num_rows();
        if ($exists > 0) return false;
        return $this->db->insert('early_warning', $record);
    }

    public function update_ews($id, $data) {
        $record = array();
        if (isset($data['kode_indikator'])) $record['kode_indikator'] = trim($data['kode_indikator']);
        if (isset($data['nama_indikator'])) $record['nama_indikator'] = trim($data['nama_indikator']);
        if (isset($data['nilai_terkini'])) $record['nilai_terkini'] = (float)$data['nilai_terkini'];
        if (isset($data['satuan'])) $record['satuan'] = trim($data['satuan']);
        if (isset($data['ambang_batas'])) $record['ambang_batas'] = (float)$data['ambang_batas'];
        if (isset($data['sudah_ditindaklanjuti'])) $record['sudah_ditindaklanjuti'] = (int)$data['sudah_ditindaklanjuti'];
        if (isset($data['arah_risiko'])) {
            $record['arah_risiko'] = (strtolower($data['arah_risiko']) === 'below') ? 'below' : 'above';
        } elseif (isset($data['direction'])) {
            $record['arah_risiko'] = (strtolower($data['direction']) === 'below') ? 'below' : 'above';
        }

        if (isset($data['nilai_terkini']) || isset($data['ambang_batas']) || isset($record['arah_risiko'])) {
            $curr = $this->get_ews_by_id($id);
            $val = isset($data['nilai_terkini']) ? (float)$data['nilai_terkini'] : (float)($curr['nilai_terkini'] ?? 0);
            $th = isset($data['ambang_batas']) ? (float)$data['ambang_batas'] : (float)($curr['ambang_batas'] ?? 0);
            $dir = isset($record['arah_risiko']) ? $record['arah_risiko'] : strtolower($curr['arah_risiko'] ?? 'above');
            if ($dir !== 'below') {
                $dir = 'above';
            }

            if ($val == $th) {
                $record['status_risiko'] = 'Waspada';
            } elseif ($dir === 'below') {
                $record['status_risiko'] = ($val < $th) ? 'Kritis' : 'Normal';
            } else {
                $record['status_risiko'] = ($val > $th) ? 'Kritis' : 'Normal';
            }
        } elseif (isset($data['status_risiko'])) {
            $record['status_risiko'] = in_array($data['status_risiko'], array('Normal', 'Waspada', 'Kritis')) ? $data['status_risiko'] : 'Normal';
        }

        if (empty($record)) return false;
        return $this->db->where('id', (int)$id)->update('early_warning', $record);
    }

    public function soft_delete_ews($id) {
        return $this->db->where('id', (int)$id)->update('early_warning', array('is_deleted' => 1, 'deleted_at' => date('Y-m-d H:i:s')));
    }

    public function restore_ews($id) {
        return $this->db->where('id', (int)$id)->update('early_warning', array('is_deleted' => 0, 'deleted_at' => null));
    }

    public function hard_delete_ews($id) {
        return $this->db->where('id', (int)$id)->delete('early_warning');
    }

    public function count_trash_ews() {
        return $this->db->where('is_deleted', 1)->where('kode_wilayah', $this->get_active_kode_wilayah())->count_all_results('early_warning');
    }

    // ==========================================
    // CRUD & SOFT DELETE: ECONOMIC INSIGHT
    // ==========================================
    public function get_insight_by_id($id) {
        return $this->db->where('id', (int)$id)->get('economic_insight')->row_array();
    }

    public function add_insight($data) {
        $judul = trim($data['judul'] ?? $data['title'] ?? '');
        if (empty($judul)) return false;
        $kw = !empty($data['kode_wilayah']) ? trim($data['kode_wilayah']) : $this->get_active_kode_wilayah();

        $record = array(
            'judul'                 => $judul,
            'ringkasan'             => trim($data['ringkasan'] ?? $data['summary'] ?? ''),
            'kategori'              => trim($data['kategori'] ?? $data['category'] ?? 'Kebijakan Ekonomi'),
            'tingkat_urgensi'       => in_array($data['tingkat_urgensi'] ?? '', array('Tinggi','Sedang','Stabil')) ? $data['tingkat_urgensi'] : 'Sedang',
            'rekomendasi_kebijakan' => trim($data['rekomendasi_kebijakan'] ?? $data['recommendation'] ?? ''),
            'dampak_anggaran'       => trim($data['dampak_anggaran'] ?? $data['budget_impact'] ?? 'Efisiensi Belanja'),
            'opd_penanggung_jawab'  => trim($data['opd_penanggung_jawab'] ?? $data['opd'] ?? 'Bappeda Banyuwangi'),
            'tahun'                 => (int)($data['tahun'] ?? $data['year'] ?? 2024),
            'kode_wilayah'          => $kw,
            'is_deleted'            => 0,
            'deleted_at'            => null
        );

        return $this->db->insert('economic_insight', $record);
    }

    public function update_insight($id, $data) {
        $record = array();
        if (isset($data['judul'])) $record['judul'] = trim($data['judul']);
        if (isset($data['ringkasan'])) $record['ringkasan'] = trim($data['ringkasan']);
        if (isset($data['kategori'])) $record['kategori'] = trim($data['kategori']);
        if (isset($data['tingkat_urgensi'])) $record['tingkat_urgensi'] = $data['tingkat_urgensi'];
        if (isset($data['rekomendasi_kebijakan'])) $record['rekomendasi_kebijakan'] = trim($data['rekomendasi_kebijakan']);
        if (isset($data['dampak_anggaran'])) $record['dampak_anggaran'] = trim($data['dampak_anggaran']);
        if (isset($data['opd_penanggung_jawab'])) $record['opd_penanggung_jawab'] = trim($data['opd_penanggung_jawab']);
        if (isset($data['tahun'])) $record['tahun'] = (int)$data['tahun'];
        if (empty($record)) return false;
        return $this->db->where('id', (int)$id)->update('economic_insight', $record);
    }

    public function soft_delete_insight($id) {
        return $this->db->where('id', (int)$id)->update('economic_insight', array('is_deleted' => 1, 'deleted_at' => date('Y-m-d H:i:s')));
    }

    public function restore_insight($id) {
        return $this->db->where('id', (int)$id)->update('economic_insight', array('is_deleted' => 0, 'deleted_at' => null));
    }

    public function hard_delete_insight($id) {
        return $this->db->where('id', (int)$id)->delete('economic_insight');
    }

    public function count_trash_insights() {
        return $this->db->where('is_deleted', 1)->where('kode_wilayah', $this->get_active_kode_wilayah())->count_all_results('economic_insight');
    }

    // ==========================================
    // CRUD & SOFT DELETE: LAPORAN EKONOMI
    // ==========================================
    public function get_laporan_by_id($id) {
        return $this->db->where('id', (int)$id)->get('laporan_ekonomi')->row_array();
    }

    public function add_laporan($data) {
        $nomor = trim($data['nomor_laporan'] ?? $data['number'] ?? ('LAP-' . date('Y') . '-' . rand(10, 99)));
        $judul = trim($data['judul_laporan'] ?? $data['title'] ?? '');
        if (empty($judul)) return false;
        $kw = !empty($data['kode_wilayah']) ? trim($data['kode_wilayah']) : $this->get_active_kode_wilayah();

        $record = array(
            'nomor_laporan' => $nomor,
            'judul_laporan' => $judul,
            'jenis_laporan' => trim($data['jenis_laporan'] ?? $data['type'] ?? 'Laporan Eksekutif'),
            'periode_tahun' => (int)($data['periode_tahun'] ?? $data['year'] ?? 2024),
            'penyusun'      => trim($data['penyusun'] ?? 'Tim Analis BAPPEDA'),
            'status'        => in_array($data['status'] ?? '', array('Terpublikasi','Draf')) ? $data['status'] : 'Terpublikasi',
            'tanggal_terbit'=> !empty($data['tanggal_terbit']) ? $data['tanggal_terbit'] : date('Y-m-d'),
            'kode_wilayah'  => $kw,
            'is_deleted'    => 0,
            'deleted_at'    => null
        );

        $exists = $this->db->where('nomor_laporan', $nomor)->where('kode_wilayah', $kw)->where('is_deleted', 0)->get('laporan_ekonomi')->num_rows();
        if ($exists > 0) return false;
        return $this->db->insert('laporan_ekonomi', $record);
    }

    public function update_laporan($id, $data) {
        $record = array();
        if (isset($data['nomor_laporan'])) $record['nomor_laporan'] = trim($data['nomor_laporan']);
        if (isset($data['judul_laporan'])) $record['judul_laporan'] = trim($data['judul_laporan']);
        if (isset($data['jenis_laporan'])) $record['jenis_laporan'] = trim($data['jenis_laporan']);
        if (isset($data['periode_tahun'])) $record['periode_tahun'] = (int)$data['periode_tahun'];
        if (isset($data['penyusun'])) $record['penyusun'] = trim($data['penyusun']);
        if (isset($data['status'])) $record['status'] = $data['status'];
        if (isset($data['tanggal_terbit'])) $record['tanggal_terbit'] = $data['tanggal_terbit'];
        if (empty($record)) return false;
        return $this->db->where('id', (int)$id)->update('laporan_ekonomi', $record);
    }

    public function soft_delete_laporan($id) {
        return $this->db->where('id', (int)$id)->update('laporan_ekonomi', array('is_deleted' => 1, 'deleted_at' => date('Y-m-d H:i:s')));
    }

    public function restore_laporan($id) {
        return $this->db->where('id', (int)$id)->update('laporan_ekonomi', array('is_deleted' => 0, 'deleted_at' => null));
    }

    public function hard_delete_laporan($id) {
        return $this->db->where('id', (int)$id)->delete('laporan_ekonomi');
    }

    public function count_trash_laporan() {
        return $this->db->where('is_deleted', 1)->where('kode_wilayah', $this->get_active_kode_wilayah())->count_all_results('laporan_ekonomi');
    }

    // ==========================================
    // CRUD & SOFT DELETE: KATALOG INDIKATOR
    // ==========================================
    public function get_catalog_by_id($id) {
        return $this->db->where('id', (int)$id)->get('katalog_indikator')->row_array();
    }

    public function add_catalog($data) {
        $kode = trim($data['kode_indikator'] ?? $data['code'] ?? '');
        $nama = trim($data['nama_indikator'] ?? $data['name'] ?? '');
        if (empty($kode) || empty($nama)) return false;
        $kw = !empty($data['kode_wilayah']) ? trim($data['kode_wilayah']) : $this->get_active_kode_wilayah();

        $klasterVal = trim($data['klaster'] ?? $data['cluster'] ?? '');
        if (($klasterVal === '__custom__' || $klasterVal === '__NEW__' || empty($klasterVal)) && (!empty($data['klaster_custom']) || !empty($data['cluster_custom']))) {
            $klasterVal = trim($data['klaster_custom'] ?? $data['cluster_custom']);
        }
        if (empty($klasterVal)) {
            $klasterVal = 'Output dan pertumbuhan';
        }

        $record = array(
            'kode_indikator'       => $kode,
            'nama_indikator'       => $nama,
            'klaster'              => $klasterVal,
            'satuan'               => trim($data['satuan'] ?? $data['unit'] ?? '%'),
            'frekuensi'            => trim($data['frekuensi'] ?? $data['frequency'] ?? 'Tahunan'),
            'sumber_data'          => trim($data['sumber_data'] ?? $data['source'] ?? 'BPS'),
            'definisi_operasional' => trim($data['definisi_operasional'] ?? $data['definition'] ?? ''),
            'kode_wilayah'         => $kw,
            'is_deleted'           => 0,
            'deleted_at'           => null
        );

        $existing = $this->db->where('kode_indikator', $kode)->where('kode_wilayah', $kw)->get('katalog_indikator')->row_array();
        if ($existing) {
            if ($existing['is_deleted']) {
                $record['is_deleted'] = 0;
                $record['deleted_at'] = null;
                return $this->db->where('id', (int)$existing['id'])->update('katalog_indikator', $record);
            }
            return false;
        }
        return $this->db->insert('katalog_indikator', $record);
    }

    public function update_catalog($id, $data) {
        $existing = $this->db->where('id', (int)$id)->get('katalog_indikator')->row_array();
        if (!$existing) return false;

        $oldCode = $existing['kode_indikator'];
        $oldWilayah = $existing['kode_wilayah'];

        $record = array();
        if (isset($data['kode_indikator']) || isset($data['code'])) {
            $record['kode_indikator'] = trim($data['kode_indikator'] ?? $data['code']);
        }
        if (isset($data['nama_indikator']) || isset($data['name'])) {
            $record['nama_indikator'] = trim($data['nama_indikator'] ?? $data['name']);
        }
        if (isset($data['klaster']) || isset($data['cluster'])) {
            $klasterVal = trim($data['klaster'] ?? $data['cluster']);
            if (($klasterVal === '__custom__' || $klasterVal === '__NEW__' || empty($klasterVal)) && (!empty($data['klaster_custom']) || !empty($data['cluster_custom']))) {
                $klasterVal = trim($data['klaster_custom'] ?? $data['cluster_custom']);
            }
            if (!empty($klasterVal)) {
                $record['klaster'] = $klasterVal;
            }
        }
        if (isset($data['satuan']) || isset($data['unit'])) {
            $record['satuan'] = trim($data['satuan'] ?? $data['unit']);
        }
        if (isset($data['frekuensi']) || isset($data['frequency'])) {
            $record['frekuensi'] = trim($data['frekuensi'] ?? $data['frequency']);
        }
        if (isset($data['sumber_data']) || isset($data['source'])) {
            $record['sumber_data'] = trim($data['sumber_data'] ?? $data['source']);
        }
        if (isset($data['definisi_operasional']) || isset($data['definition'])) {
            $record['definisi_operasional'] = trim($data['definisi_operasional'] ?? $data['definition']);
        }

        if (empty($record)) return false;

        $res = $this->db->where('id', (int)$id)->update('katalog_indikator', $record);

        if ($res) {
            // Sinkronkan ke database_ekonomi (perbarui semua baris data dengan indikator ini)
            $dbUpdate = array();
            if (isset($record['kode_indikator'])) $dbUpdate['kode_indikator'] = $record['kode_indikator'];
            if (isset($record['nama_indikator'])) $dbUpdate['nama_indikator'] = $record['nama_indikator'];
            if (isset($record['klaster'])) $dbUpdate['klaster'] = $record['klaster'];
            if (isset($record['satuan'])) $dbUpdate['satuan'] = $record['satuan'];
            if (isset($record['frekuensi'])) $dbUpdate['periode'] = $record['frekuensi'];
            if (isset($record['sumber_data'])) $dbUpdate['sumber_data'] = $record['sumber_data'];
            if (isset($record['definisi_operasional'])) $dbUpdate['definisi_operasional'] = $record['definisi_operasional'];

            if (!empty($dbUpdate)) {
                $this->db->where('kode_indikator', $oldCode)
                         ->where('kode_wilayah', $oldWilayah)
                         ->update('database_ekonomi', $dbUpdate);
            }

            // Sinkronkan ke early_warning jika terdaftar di sana
            if (isset($record['kode_indikator']) || isset($record['nama_indikator'])) {
                $ewsUpdate = array();
                if (isset($record['kode_indikator'])) $ewsUpdate['kode_indikator'] = $record['kode_indikator'];
                if (isset($record['nama_indikator'])) $ewsUpdate['nama_indikator'] = $record['nama_indikator'];
                $this->db->where('kode_indikator', $oldCode)
                         ->where('kode_wilayah', $oldWilayah)
                         ->update('early_warning', $ewsUpdate);
            }
        }
        return $res;
    }

    public function soft_delete_catalog($id) {
        $cat = $this->db->where('id', (int)$id)->get('katalog_indikator')->row_array();
        if (!$cat) return false;

        $now = date('Y-m-d H:i:s');
        $res = $this->db->where('id', (int)$id)->update('katalog_indikator', array(
            'is_deleted' => 1,
            'deleted_at' => $now
        ));

        if ($res) {
            // Pindahkan seluruh rekaman tahunan data di database_ekonomi ke tempat sampah juga
            $this->db->where('kode_indikator', $cat['kode_indikator'])
                     ->where('kode_wilayah', $cat['kode_wilayah'])
                     ->update('database_ekonomi', array(
                         'is_deleted' => 1,
                         'deleted_at' => $now
                     ));
        }
        return $res;
    }

    public function restore_catalog($id) {
        $cat = $this->db->where('id', (int)$id)->get('katalog_indikator')->row_array();
        if (!$cat) return false;

        $res = $this->db->where('id', (int)$id)->update('katalog_indikator', array(
            'is_deleted' => 0,
            'deleted_at' => null
        ));

        if ($res) {
            // Pulihkan juga seluruh rekaman tahunan data di database_ekonomi
            $this->db->where('kode_indikator', $cat['kode_indikator'])
                     ->where('kode_wilayah', $cat['kode_wilayah'])
                     ->update('database_ekonomi', array(
                         'is_deleted' => 0,
                         'deleted_at' => null
                     ));
        }
        return $res;
    }

    public function hard_delete_catalog($id) {
        $cat = $this->db->where('id', (int)$id)->get('katalog_indikator')->row_array();
        if (!$cat) return false;

        $res = $this->db->where('id', (int)$id)->delete('katalog_indikator');

        if ($res) {
            // Hapus permanen dari database_ekonomi
            $this->db->where('kode_indikator', $cat['kode_indikator'])
                     ->where('kode_wilayah', $cat['kode_wilayah'])
                     ->delete('database_ekonomi');
        }
        return $res;
    }

    public function count_trash_catalog() {
        return $this->db->where('is_deleted', 1)->where('kode_wilayah', $this->get_active_kode_wilayah())->count_all_results('katalog_indikator');
    }
}
