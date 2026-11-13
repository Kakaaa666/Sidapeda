<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_economy extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_years() {
        $query = $this->db->select('tahun')->order_by('tahun', 'ASC')->get('prospek_ekonomi');
        if ($query->num_rows() > 0) {
            $years = array();
            foreach ($query->result() as $row) {
                $years[] = (int)$row->tahun;
            }
            return $years;
        }
        return array(2015, 2016, 2017, 2018, 2019, 2020, 2021, 2022, 2023, 2024);
    }

    public function get_economic_series() {
        $query = $this->db->order_by('tahun', 'ASC')->get('prospek_ekonomi');
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

    public function get_sectors($status = null) {
        if ($status === 'Terhapus' || $status === 'Sampah') {
            $this->db->where('is_deleted', 1);
        } else {
            $this->db->where('is_deleted', 0);
        }
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
                    'is_deleted' => (int)($row->is_deleted ?? 0),
                    'deleted_at' => $row->deleted_at ?? null
                );
            }
            return $sectors;
        }

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

    public function get_regional_growth($status = null) {
        if ($status === 'Terhapus' || $status === 'Sampah') {
            $this->db->where('is_deleted', 1);
        } else {
            $this->db->where('is_deleted', 0);
        }
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
                    'is_deleted' => (int)($row->is_deleted ?? 0),
                    'deleted_at' => $row->deleted_at ?? null
                );
            }
            return $list;
        }

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
            'is_deleted' => (int)($row['is_deleted'] ?? 0),
            'deleted_at' => $row['deleted_at'] ?? null
        );
    }

    public function add_regional($data) {
        $nama = trim($data['nama_wilayah'] ?? $data['name'] ?? '');
        $nilai = (float)($data['nilai_pertumbuhan'] ?? $data['value'] ?? 5.0);
        $perubahan = (float)($data['perubahan'] ?? $data['change'] ?? 0.2);

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
            'is_deleted' => 0,
            'deleted_at' => null
        );

        $exists = $this->db->where('nama_wilayah', $nama)->where('is_deleted', 0)->get('perbandingan_daerah')->num_rows();
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

    public function count_trash_regional() {
        return $this->db->where('is_deleted', 1)->count_all_results('perbandingan_daerah');
    }

    public function get_districts($status = null) {
        if ($status === 'Terhapus' || $status === 'Sampah') {
            $this->db->where('is_deleted', 1);
        } else {
            $this->db->where('is_deleted', 0);
        }
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
                    'is_deleted' => (int)($row->is_deleted ?? 0),
                    'deleted_at' => $row->deleted_at ?? null
                );
            }
            return $list;
        }

        return array(
            array('name' => 'Banyuwangi', 'share' => 14.8, 'growth' => 5.82, 'poverty' => 5.2, 'density' => 2450, 'category' => 'Perkotaan & Jasa', 'lat' => -8.2192, 'lng' => 114.3692),
            array('name' => 'Rogojampi', 'share' => 8.9, 'growth' => 5.41, 'poverty' => 6.8, 'density' => 1320, 'category' => 'Perdagangan & Agro', 'lat' => -8.3045, 'lng' => 114.2925),
            array('name' => 'Genteng', 'share' => 9.2, 'growth' => 5.35, 'poverty' => 6.1, 'density' => 1480, 'category' => 'Pusat Niaga Selatan', 'lat' => -8.3619, 'lng' => 114.1528)
        );
    }

    public function get_indicator_records($status = null, $query = null, $sort = 'tahun') {
        if ($status === 'Terhapus' || $status === 'Sampah') {
            $this->db->where('is_deleted', 1);
        } else {
            $this->db->where('is_deleted', 0);
            if ($status && $status !== 'Semua status') {
                $this->db->where('status_validasi', $status);
            }
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
                'status' => $row['status_validasi'],
                'status_validasi' => $row['status_validasi'],
                'verified_by' => $row['diverifikasi_oleh'],
                'diverifikasi_oleh' => $row['diverifikasi_oleh'],
                'notes' => $row['catatan'],
                'catatan' => $row['catatan'],
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
        $record = array(
            'kode_indikator'    => $data['code'] ?? $data['kode_indikator'],
            'nama_indikator'    => $data['indicator'] ?? $data['nama_indikator'],
            'klaster'           => $data['cluster'] ?? $data['klaster'] ?? 'Output & Pertumbuhan',
            'wilayah'           => $data['region'] ?? $data['wilayah'] ?? 'Kabupaten Banyuwangi',
            'periode'           => $data['period'] ?? $data['periode'] ?? 'Tahunan',
            'tahun'             => (int)($data['year'] ?? $data['tahun']),
            'nilai'             => (float)($data['value'] ?? $data['nilai']),
            'satuan'            => $data['unit'] ?? $data['satuan'] ?? '%',
            'sumber_data'       => $data['source'] ?? $data['sumber_data'] ?? 'Input Manual',
            'status_validasi'   => $data['status'] ?? $data['status_validasi'] ?? 'Tervalidasi',
            'diverifikasi_oleh' => $data['verified_by'] ?? $data['diverifikasi_oleh'] ?? null,
            'catatan'           => $data['notes'] ?? $data['catatan'] ?? null,
            'is_deleted'        => 0,
            'deleted_at'        => null,
            'dibuat_pada'       => date('Y-m-d H:i:s')
        );

        $exists = $this->db->where('kode_indikator', $record['kode_indikator'])
            ->where('tahun', $record['tahun'])
            ->where('is_deleted', 0)
            ->get('database_ekonomi')
            ->num_rows();

        if ($exists > 0) {
            return false;
        }
        $res = $this->db->insert('database_ekonomi', $record);
        if ($res) {
            // Sinkronkan ke early_warning jika kode indikator ada di tabel EWS
            $ewsMatch = $this->db->where('kode_indikator', $record['kode_indikator'])->get('early_warning')->row_array();
            if ($ewsMatch) {
                $this->update_ews_threshold($record['kode_indikator'], (float)$ewsMatch['ambang_batas'], $ewsMatch['arah_risiko'], (float)$record['nilai']);
            }
        }
        return $res;
    }

    public function update_indicator_record($id, $data) {
        $record = array();
        if (isset($data['code']) || isset($data['kode_indikator'])) {
            $record['kode_indikator'] = $data['code'] ?? $data['kode_indikator'];
        }
        if (isset($data['indicator']) || isset($data['nama_indikator'])) {
            $record['nama_indikator'] = $data['indicator'] ?? $data['nama_indikator'];
        }
        if (isset($data['cluster']) || isset($data['klaster'])) {
            $record['klaster'] = $data['cluster'] ?? $data['klaster'];
        }
        if (isset($data['region']) || isset($data['wilayah'])) {
            $record['wilayah'] = $data['region'] ?? $data['wilayah'];
        }
        if (isset($data['period']) || isset($data['periode'])) {
            $record['periode'] = $data['period'] ?? $data['periode'];
        }
        if (isset($data['year']) || isset($data['tahun'])) {
            $record['tahun'] = (int)($data['year'] ?? $data['tahun']);
        }
        if (isset($data['value']) || isset($data['nilai'])) {
            $record['nilai'] = (float)($data['value'] ?? $data['nilai']);
        }
        if (isset($data['unit']) || isset($data['satuan'])) {
            $record['satuan'] = $data['unit'] ?? $data['satuan'];
        }
        if (isset($data['source']) || isset($data['sumber_data'])) {
            $record['sumber_data'] = $data['source'] ?? $data['sumber_data'];
        }
        if (isset($data['status']) || isset($data['status_validasi'])) {
            $record['status_validasi'] = $data['status'] ?? $data['status_validasi'];
        }
        if (isset($data['notes']) || isset($data['catatan'])) {
            $record['catatan'] = $data['notes'] ?? $data['catatan'];
        }

        if (empty($record)) return false;

        $this->db->where('id', (int)$id);
        return $this->db->update('database_ekonomi', $record);
    }

    public function soft_delete_indicator_record($id) {
        $this->db->where('id', (int)$id);
        return $this->db->update('database_ekonomi', array(
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s')
        ));
    }

    public function restore_indicator_record($id) {
        $this->db->where('id', (int)$id);
        return $this->db->update('database_ekonomi', array(
            'is_deleted' => 0,
            'deleted_at' => null
        ));
    }

    public function hard_delete_indicator_record($id) {
        $this->db->where('id', (int)$id);
        return $this->db->delete('database_ekonomi');
    }

    public function count_trash_records() {
        return $this->db->where('is_deleted', 1)->count_all_results('database_ekonomi');
    }

    public function update_record_status($code, $year, $status) {
        $this->db->where('kode_indikator', $code)->where('tahun', $year);
        return $this->db->update('database_ekonomi', array('status_validasi' => $status));
    }

    public function get_indicator_catalog($status = null) {
        if ($status === 'Terhapus' || $status === 'Sampah') {
            $this->db->where('is_deleted', 1);
        } else {
            $this->db->where('is_deleted', 0);
        }
        $res = $this->db->order_by('kode_indikator', 'ASC')->get('katalog_indikator');
        $list = array();
        foreach ($res->result_array() as $row) {
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
                'definisi_operasional' => $row['definisi_operasional']
            );
        }
        return $list;
    }

    public function get_ews_indicators($status = null) {
        if ($status === 'Terhapus' || $status === 'Sampah') {
            $this->db->where('is_deleted', 1);
        } else {
            $this->db->where('is_deleted', 0);
        }
        $res = $this->db->get('early_warning');
        $results = array();
        foreach ($res->result_array() as $row) {
            $val = (float)$row['nilai_terkini'];
            $th = (float)$row['ambang_batas'];
            $dir = strtolower($row['arah_risiko'] ?? 'above');
            if ($dir !== 'below') {
                $dir = 'above';
            }

            if ($val == $th) {
                $status = 'Waspada';
            } elseif ($dir === 'below') {
                $status = ($val < $th) ? 'Kritis' : 'Normal';
            } else { // 'above'
                $status = ($val > $th) ? 'Kritis' : 'Normal';
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
                'unit' => $row['satuan'],
                'satuan' => $row['satuan'],
                'threshold' => $th,
                'ambang_batas' => $th,
                'direction' => $dir,
                'arah_risiko' => $dir,
                'breached' => $breached,
                'distance' => round($distance, 2),
                'status' => $status,
                'status_risiko' => $status,
                'is_followed_up' => (int)$row['sudah_ditindaklanjuti'],
                'sudah_ditindaklanjuti' => (int)$row['sudah_ditindaklanjuti']
            );
        }
        return $results;
    }

    public function update_ews_threshold($code, $threshold, $direction = null, $value = null) {
        $th = (float)$threshold;
        $row = $this->db->where('kode_indikator', $code)->get('early_warning')->row_array();
        $val = ($value !== null) ? (float)$value : ($row ? (float)$row['nilai_terkini'] : 0);
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

        $updateData = array('ambang_batas' => $th, 'status_risiko' => $st);
        if ($direction !== null) {
            $updateData['arah_risiko'] = $dir;
        }
        if ($value !== null) {
            $updateData['nilai_terkini'] = $val;
        }

        $this->db->where('kode_indikator', $code);
        $res = $this->db->update('early_warning', $updateData);
        return array(
            'success' => (bool)$res,
            'value' => $val,
            'nilai_terkini' => $val,
            'threshold' => $th,
            'direction' => $dir,
            'status' => $st
        );
    }

    public function toggle_ews_followup($code) {
        $item = $this->db->where('kode_indikator', $code)->get('early_warning')->row();
        if ($item) {
            $next = $item->sudah_ditindaklanjuti ? 0 : 1;
            $this->db->where('kode_indikator', $code)->update('early_warning', array('sudah_ditindaklanjuti' => $next));
            return $next;
        }
        return false;
    }

    public function get_beranda_cards($year = null, $status = null) {
        $year = $year ? (int)$year : 2024;

        $this->db->where('is_deleted', 0);
        $this->db->where('tahun', $year);
        $this->db->where('status_validasi !=', 'Ditolak');
        $records = $this->db->order_by('id', 'ASC')->get('database_ekonomi')->result_array();

        if (empty($records)) {
            // Fallback ke tahun terbaru yang tersedia di database_ekonomi
            $latest = $this->db->select_max('tahun')->where('is_deleted', 0)->where('status_validasi !=', 'Ditolak')->get('database_ekonomi')->row();
            if ($latest && $latest->tahun) {
                $year = (int)$latest->tahun;
                $this->db->where('is_deleted', 0);
                $this->db->where('tahun', $year);
                $this->db->where('status_validasi !=', 'Ditolak');
                $records = $this->db->order_by('id', 'ASC')->get('database_ekonomi')->result_array();
            }
        }

        if (empty($records)) {
            return array();
        }

        $cards = array();
        $lower_is_better = array('inflasi', 'ihk', 'pengangguran', 'tpt', 'kemiskinan', 'miskin', 'gini');

        foreach ($records as $row) {
            $code = $row['kode_indikator'];
            $current_val = (float)$row['nilai'];

            // Komparasi tahun sebelumnya
            $prev_row = $this->db->where('is_deleted', 0)
                ->where('kode_indikator', $code)
                ->where('tahun', $year - 1)
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
                ->where('tahun <=', $year)
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
                'tahun'           => (int)$row['tahun'],
                'wilayah'         => $row['wilayah'],
                'sumber_data'     => $row['sumber_data'],
                'status_validasi' => $row['status_validasi']
            );
        }

        return $cards;
    }

    public function get_beranda_categories($year = null) {
        $year = $year ? (int)$year : 2024;
        $this->db->select('klaster, COUNT(*) as total');
        $this->db->where('is_deleted', 0);
        $this->db->where('tahun', $year);
        $this->db->where('status_validasi !=', 'Ditolak');
        $this->db->group_by('klaster');
        $this->db->order_by('total', 'DESC');
        $res = $this->db->get('database_ekonomi')->result_array();

        if (empty($res)) {
            $latest = $this->db->select_max('tahun')->where('is_deleted', 0)->where('status_validasi !=', 'Ditolak')->get('database_ekonomi')->row();
            if ($latest && $latest->tahun) {
                $year = (int)$latest->tahun;
                $this->db->select('klaster, COUNT(*) as total');
                $this->db->where('is_deleted', 0);
                $this->db->where('tahun', $year);
                $this->db->where('status_validasi !=', 'Ditolak');
                $this->db->group_by('klaster');
                $this->db->order_by('total', 'DESC');
                $res = $this->db->get('database_ekonomi')->result_array();
            }
        }

        $cats = array();
        foreach ($res as $r) {
            $cluster = !empty($r['klaster']) ? $r['klaster'] : 'Output & Pertumbuhan';
            $cats[$cluster] = (int)$r['total'];
        }
        return $cats;
    }

    public function get_economic_insights($year = null, $status = null) {
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

    public function get_laporan_ekonomi($status = null) {
        if ($status === 'Terhapus' || $status === 'Sampah') {
            $this->db->where('is_deleted', 1);
        } else {
            $this->db->where('is_deleted', 0);
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
        return $this->db->where('is_deleted', 1)->count_all_results('beranda');
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
            'is_deleted'                  => 0,
            'deleted_at'                  => null
        );

        $exists = $this->db->where('kode_sektor', $kode)->where('is_deleted', 0)->get('analisis_ekonomi')->num_rows();
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
        return $this->db->where('is_deleted', 1)->count_all_results('analisis_ekonomi');
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

        $record = array(
            'nama_kecamatan' => $nama,
            'porsi_pdrb'     => (float)($data['porsi_pdrb'] ?? $data['share'] ?? 5.0),
            'pertumbuhan'    => (float)($data['pertumbuhan'] ?? $data['growth'] ?? 5.0),
            'kemiskinan'     => (float)($data['kemiskinan'] ?? $data['poverty'] ?? 6.0),
            'kepadatan'      => (int)($data['kepadatan'] ?? $data['density'] ?? 1000),
            'kategori'       => trim($data['kategori'] ?? $data['category'] ?? 'Perdagangan & Jasa'),
            'garis_lintang'  => (float)($data['garis_lintang'] ?? $data['lat'] ?? -8.2192),
            'garis_bujur'    => (float)($data['garis_bujur'] ?? $data['lng'] ?? 114.3692),
            'is_deleted'     => 0,
            'deleted_at'     => null
        );

        $exists = $this->db->where('nama_kecamatan', $nama)->where('is_deleted', 0)->get('peta_ekonomi')->num_rows();
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
        return $this->db->where('is_deleted', 1)->count_all_results('peta_ekonomi');
    }

    // ==========================================
    // CRUD & SOFT DELETE: PROSPEK EKONOMI (PROYEKSI)
    // ==========================================
    public function get_prospek_records($status = null) {
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
            'is_deleted'            => 0,
            'deleted_at'            => null
        );

        $exists = $this->db->where('tahun', $tahun)->where('is_deleted', 0)->get('prospek_ekonomi')->num_rows();
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
        return $this->db->where('is_deleted', 1)->count_all_results('prospek_ekonomi');
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
            'is_deleted'            => 0,
            'deleted_at'            => null
        );

        $exists = $this->db->where('kode_indikator', $kode)->where('is_deleted', 0)->get('early_warning')->num_rows();
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
        return $this->db->where('is_deleted', 1)->count_all_results('early_warning');
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

        $record = array(
            'judul'                 => $judul,
            'ringkasan'             => trim($data['ringkasan'] ?? $data['summary'] ?? ''),
            'kategori'              => trim($data['kategori'] ?? $data['category'] ?? 'Kebijakan Ekonomi'),
            'tingkat_urgensi'       => in_array($data['tingkat_urgensi'] ?? '', array('Tinggi','Sedang','Stabil')) ? $data['tingkat_urgensi'] : 'Sedang',
            'rekomendasi_kebijakan' => trim($data['rekomendasi_kebijakan'] ?? $data['recommendation'] ?? ''),
            'dampak_anggaran'       => trim($data['dampak_anggaran'] ?? $data['budget_impact'] ?? 'Efisiensi Belanja'),
            'opd_penanggung_jawab'  => trim($data['opd_penanggung_jawab'] ?? $data['opd'] ?? 'Bappeda Banyuwangi'),
            'tahun'                 => (int)($data['tahun'] ?? $data['year'] ?? 2024),
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
        return $this->db->where('is_deleted', 1)->count_all_results('economic_insight');
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

        $record = array(
            'nomor_laporan' => $nomor,
            'judul_laporan' => $judul,
            'jenis_laporan' => trim($data['jenis_laporan'] ?? $data['type'] ?? 'Laporan Eksekutif'),
            'periode_tahun' => (int)($data['periode_tahun'] ?? $data['year'] ?? 2024),
            'penyusun'      => trim($data['penyusun'] ?? 'Tim Analis BAPPEDA'),
            'status'        => in_array($data['status'] ?? '', array('Terpublikasi','Draf')) ? $data['status'] : 'Terpublikasi',
            'tanggal_terbit'=> !empty($data['tanggal_terbit']) ? $data['tanggal_terbit'] : date('Y-m-d'),
            'is_deleted'    => 0,
            'deleted_at'    => null
        );

        $exists = $this->db->where('nomor_laporan', $nomor)->where('is_deleted', 0)->get('laporan_ekonomi')->num_rows();
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
        return $this->db->where('is_deleted', 1)->count_all_results('laporan_ekonomi');
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

        $record = array(
            'kode_indikator'       => $kode,
            'nama_indikator'       => $nama,
            'klaster'              => trim($data['klaster'] ?? $data['cluster'] ?? 'Output & Pertumbuhan'),
            'satuan'               => trim($data['satuan'] ?? $data['unit'] ?? '%'),
            'frekuensi'            => trim($data['frekuensi'] ?? $data['frequency'] ?? 'Tahunan'),
            'sumber_data'          => trim($data['sumber_data'] ?? $data['source'] ?? 'BPS Banyuwangi'),
            'definisi_operasional' => trim($data['definisi_operasional'] ?? $data['definition'] ?? ''),
            'is_deleted'           => 0,
            'deleted_at'           => null
        );

        $exists = $this->db->where('kode_indikator', $kode)->where('is_deleted', 0)->get('katalog_indikator')->num_rows();
        if ($exists > 0) return false;
        return $this->db->insert('katalog_indikator', $record);
    }

    public function update_catalog($id, $data) {
        $record = array();
        if (isset($data['kode_indikator'])) $record['kode_indikator'] = trim($data['kode_indikator']);
        if (isset($data['nama_indikator'])) $record['nama_indikator'] = trim($data['nama_indikator']);
        if (isset($data['klaster'])) $record['klaster'] = trim($data['klaster']);
        if (isset($data['satuan'])) $record['satuan'] = trim($data['satuan']);
        if (isset($data['frekuensi'])) $record['frekuensi'] = trim($data['frekuensi']);
        if (isset($data['sumber_data'])) $record['sumber_data'] = trim($data['sumber_data']);
        if (isset($data['definisi_operasional'])) $record['definisi_operasional'] = trim($data['definisi_operasional']);
        if (empty($record)) return false;
        return $this->db->where('id', (int)$id)->update('katalog_indikator', $record);
    }

    public function soft_delete_catalog($id) {
        return $this->db->where('id', (int)$id)->update('katalog_indikator', array('is_deleted' => 1, 'deleted_at' => date('Y-m-d H:i:s')));
    }

    public function restore_catalog($id) {
        return $this->db->where('id', (int)$id)->update('katalog_indikator', array('is_deleted' => 0, 'deleted_at' => null));
    }

    public function hard_delete_catalog($id) {
        return $this->db->where('id', (int)$id)->delete('katalog_indikator');
    }

    public function count_trash_catalog() {
        return $this->db->where('is_deleted', 1)->count_all_results('katalog_indikator');
    }
}
