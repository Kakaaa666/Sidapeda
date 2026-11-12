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

    public function get_sectors() {
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
                    'growthProvinceSeries' => json_decode($row->deret_pertumbuhan_provinsi, true) ?: array()
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

    public function get_regional_growth() {
        $query = $this->db->get('perbandingan_daerah');
        if ($query->num_rows() > 0) {
            $list = array();
            foreach ($query->result() as $row) {
                $list[] = array(
                    'name' => $row->nama_wilayah,
                    'nama_wilayah' => $row->nama_wilayah,
                    'value' => (float)$row->nilai_pertumbuhan,
                    'nilai_pertumbuhan' => (float)$row->nilai_pertumbuhan,
                    'change' => (float)$row->perubahan,
                    'perubahan' => (float)$row->perubahan,
                    'series' => json_decode($row->deret_historis, true) ?: array()
                );
            }
            return $list;
        }

        return array(
            array('name' => 'Kota Surabaya', 'value' => 5.76, 'change' => 0.34, 'series' => array(5.21, 5.42, 5.60, 6.00, 6.10, -4.85, 4.29, 6.51, 5.42, 5.76)),
            array('name' => 'Kab. Gresik', 'value' => 5.42, 'change' => 0.31, 'series' => array(5.10, 5.20, 5.30, 5.70, 5.60, -3.68, 3.80, 5.16, 5.11, 5.42)),
            array('name' => 'Kab. Banyuwangi', 'value' => 5.18, 'change' => 0.26, 'series' => array(5.02, 5.15, 5.28, 5.61, 5.55, -3.58, 4.08, 4.43, 4.92, 5.18)),
            array('name' => 'Kota Malang', 'value' => 5.06, 'change' => 0.18, 'series' => array(5.20, 5.30, 5.40, 5.60, 5.70, -2.26, 4.00, 5.41, 4.88, 5.06)),
            array('name' => 'Kab. Sidoarjo', 'value' => 4.98, 'change' => 0.12, 'series' => array(5.30, 5.40, 5.50, 5.70, 5.60, -3.69, 4.21, 5.20, 4.86, 4.98)),
            array('name' => 'Kota Kediri', 'value' => 4.88, 'change' => 0.15, 'series' => array(5.00, 5.10, 5.20, 5.50, 5.40, -4.10, 3.90, 4.85, 4.73, 4.88)),
            array('name' => 'Kab. Jember', 'value' => 4.62, 'change' => 0.08, 'series' => array(4.80, 4.90, 5.00, 5.20, 5.10, -3.20, 3.70, 4.53, 4.54, 4.62)),
            array('name' => 'Kab. Pasuruan', 'value' => 4.55, 'change' => 0.05, 'series' => array(4.90, 5.00, 5.10, 5.40, 5.30, -3.80, 3.60, 4.48, 4.50, 4.55))
        );
    }

    public function get_districts() {
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
                    'lng' => (float)$row->garis_bujur
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
            'status_validasi'   => $data['status'] ?? $data['status_validasi'] ?? 'Perlu review',
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
        return $this->db->insert('database_ekonomi', $record);
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

    public function get_indicator_catalog() {
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

    public function get_ews_indicators() {
        $res = $this->db->get('early_warning');
        $results = array();
        foreach ($res->result_array() as $row) {
            $val = (float)$row['nilai_terkini'];
            $th = (float)$row['ambang_batas'];
            $breached = ($row['arah_risiko'] === 'above') ? ($val > $th) : ($val < $th);
            $distance = ($th == 0) ? ($breached ? 100 : 0) : (abs($val - $th) / abs($th) * 100);

            if (!$breached) {
                $status = 'Normal';
            } elseif ($distance >= 10) {
                $status = 'Kritis';
            } else {
                $status = 'Waspada';
            }

            $results[] = array(
                'id' => (int)$row['id'],
                'code' => $row['kode_indikator'],
                'kode_indikator' => $row['kode_indikator'],
                'name' => $row['nama_indikator'],
                'nama_indikator' => $row['nama_indikator'],
                'value' => $val,
                'nilai_terkini' => $val,
                'unit' => $row['satuan'],
                'satuan' => $row['satuan'],
                'threshold' => $th,
                'ambang_batas' => $th,
                'direction' => $row['arah_risiko'],
                'arah_risiko' => $row['arah_risiko'],
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

    public function update_ews_threshold($code, $threshold) {
        $this->db->where('kode_indikator', $code);
        return $this->db->update('early_warning', array('ambang_batas' => (float)$threshold));
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

    public function get_beranda_cards($year = 2024) {
        $this->db->where('tahun', (int)$year);
        $res = $this->db->get('beranda');
        if ($res->num_rows() > 0) {
            return $res->result_array();
        }
        return $this->db->get('beranda')->result_array();
    }

    public function get_economic_insights($year = null) {
        if ($year) {
            $this->db->where('tahun', (int)$year);
        }
        return $this->db->order_by('id', 'ASC')->get('economic_insight')->result_array();
    }

    public function get_laporan_ekonomi() {
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
}
