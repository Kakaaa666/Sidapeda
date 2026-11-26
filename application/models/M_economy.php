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

        // Baseline deret indikator makro ekonomi daerah
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

        // Coba ekstrak data riil lapangan usaha dari database_ekonomi (EKO-003 & EKO-004)
        $dbSectors = $this->extract_sectors_from_database($kode);
        if (!empty($dbSectors)) {
            return $dbSectors;
        }

        // Fallback baseline lapangan usaha
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

    public function extract_sectors_from_database($kode) {
        $rows = $this->db->select('kode_indikator, nama_indikator, tahun, nilai')
            ->where('kode_wilayah', $kode)
            ->group_start()
                ->like('kode_indikator', 'EKO-003.', 'after')
                ->or_like('kode_indikator', 'EKO-004.', 'after')
            ->group_end()
            ->where('is_deleted', 0)
            ->order_by('kode_indikator ASC, tahun ASC')
            ->get('database_ekonomi')
            ->result_array();

        if (empty($rows)) {
            return array();
        }

        $shortMap = array(
            'A' => 'Pertanian',
            'B' => 'Pertambangan',
            'C' => 'Industri',
            'D' => 'Listrik & Gas',
            'E' => 'Pengadaan Air',
            'F' => 'Konstruksi',
            'G' => 'Perdagangan',
            'H' => 'Transportasi',
            'I' => 'Akomodasi',
            'J' => 'Informasi & Kom.',
            'K' => 'Jasa Keuangan',
            'L' => 'Real Estate',
            'MN' => 'Jasa Perusahaan',
            'O' => 'Adm. Pemerintah',
            'P' => 'Jasa Pendidikan',
            'Q' => 'Jasa Kesehatan',
            'RSTU' => 'Jasa Lainnya'
        );

        $data3 = array();
        $data4 = array();
        $names = array();

        foreach ($rows as $r) {
            $c = $r['kode_indikator'];
            $sub = substr($c, strpos($c, '.') + 1);
            if ($sub === 'TM') continue;
            if (strpos($c, 'EKO-003.') === 0) {
                $data3[$sub][$r['tahun']] = (float)$r['nilai'];
                $clean = preg_replace('/^PDRB ADHB menurut lapangan usaha:\s*/i', '', $r['nama_indikator']);
                $names[$sub] = $clean;
            } elseif (strpos($c, 'EKO-004.') === 0) {
                $data4[$sub][$r['tahun']] = (float)$r['nilai'];
                if (empty($names[$sub])) {
                    $clean = preg_replace('/^PDRB ADHK menurut lapangan usaha:\s*/i', '', $r['nama_indikator']);
                    $names[$sub] = $clean;
                }
            }
        }

        if (empty($names)) return array();

        $totADHB = 0;
        foreach ($data3 as $sub => $yrs) {
            $latestVal = end($yrs);
            $totADHB += $latestVal;
        }

        $sectors = array();
        foreach ($names as $sub => $name) {
            $series4 = $data4[$sub] ?? array();
            $years = array_keys($series4);
            sort($years);
            $growthSeries = array();
            for ($i = 1; $i < count($years); $i++) {
                $yPrev = $years[$i - 1];
                $yCurr = $years[$i];
                $vPrev = $series4[$yPrev];
                $vCurr = $series4[$yCurr];
                if ($vPrev > 0) {
                    $growthSeries[] = round((($vCurr - $vPrev) / $vPrev) * 100, 2);
                }
            }
            $latestGrowth = !empty($growthSeries) ? end($growthSeries) : 4.5;
            $adhbSeries = $data3[$sub] ?? array();
            $latestADHB = !empty($adhbSeries) ? end($adhbSeries) : 0;
            $share = ($totADHB > 0) ? round(($latestADHB / $totADHB) * 100, 2) : 5.0;

            $sectors[] = array(
                'key' => strtolower($sub),
                'kode_sektor' => $sub,
                'name' => $name,
                'nama_sektor' => $name,
                'short' => $shortMap[$sub] ?? $name,
                'nama_pendek' => $shortMap[$sub] ?? $name,
                'shareLocal' => $share,
                'porsi_daerah' => $share,
                'shareProvince' => 6.0,
                'porsi_provinsi' => 6.0,
                'growthLocal' => $latestGrowth,
                'pertumbuhan_daerah' => $latestGrowth,
                'growthProvince' => 5.0,
                'pertumbuhan_provinsi' => 5.0,
                'employmentBase' => 5000,
                'employmentCurrent' => 6500,
                'provinceEmploymentBase' => 500000,
                'provinceEmploymentCurrent' => 600000,
                'growthSeries' => $growthSeries,
                'growthProvinceSeries' => array(4.5, 4.8, 5.0, 5.2, 5.1, -2.5, 3.5, 4.8, 5.0, 5.1),
                'kode_wilayah' => $kode
            );
        }

        return $sectors;
    }

    public function get_regional_growth($status = null, $kode_wilayah = null) {
        if ($status === 'Terhapus' || $status === 'Sampah') {
            $this->db->where('is_deleted', 1);
        } else {
            $this->db->where('is_deleted', 0);
        }
        if (!empty($kode_wilayah) && $kode_wilayah !== 'all') {
            $this->db->where('kode_wilayah', $kode_wilayah);
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
                    'kode_wilayah' => $row->kode_wilayah ?? $kode,
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
        if (!empty($kode_wilayah) && $kode_wilayah !== 'all') {
            $this->db->where('kode_wilayah', $kode_wilayah);
        }
        return $this->db->where('is_deleted', 1)->count_all_results('perbandingan_daerah');
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

        return array(
            array('name' => 'Pusat Kota', 'share' => 14.8, 'growth' => 5.82, 'poverty' => 5.2, 'density' => 2450, 'category' => 'Perkotaan & Jasa', 'lat' => -8.2192, 'lng' => 114.3692),
            array('name' => 'Kawasan Niaga', 'share' => 8.9, 'growth' => 5.41, 'poverty' => 6.8, 'density' => 1320, 'category' => 'Perdagangan & Agro', 'lat' => -8.3045, 'lng' => 114.2925),
            array('name' => 'Kawasan Industri', 'share' => 9.2, 'growth' => 5.35, 'poverty' => 6.1, 'density' => 1480, 'category' => 'Pusat Niaga', 'lat' => -8.3619, 'lng' => 114.1528)
        );
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

        if ($sort === 'code' || $sort === 'kode') {
            $prefixOrderSql = "CASE
                WHEN kode_indikator LIKE 'EKO%' THEN 1
                WHEN kode_indikator LIKE 'NAK%' THEN 2
                WHEN kode_indikator LIKE 'TK%' THEN 3
                WHEN kode_indikator LIKE 'INV%' THEN 4
                WHEN kode_indikator LIKE 'HRG%' THEN 5
                WHEN kode_indikator LIKE 'KES%' THEN 6
                WHEN kode_indikator LIKE 'PAR%' THEN 7
                WHEN kode_indikator LIKE 'PRO%' THEN 8
                WHEN kode_indikator LIKE 'FIS%' THEN 9
                WHEN kode_indikator LIKE 'GEO%' THEN 10
                WHEN kode_indikator LIKE 'EWS%' THEN 11
                WHEN kode_indikator LIKE 'ANA%' THEN 12
                ELSE 99
            END";
            $this->db->order_by($prefixOrderSql, 'ASC', false);
            $this->db->order_by("CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(kode_indikator, '-', -1), '.', 1) AS UNSIGNED)", "ASC", false);
            $this->db->order_by('kode_indikator', 'ASC');
            $this->db->order_by('tahun', 'ASC');
        } elseif ($sort === 'indicator' || $sort === 'nama_indikator') {
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

        $codeVal = trim($data['code'] ?? $data['kode_indikator'] ?? '');
        $nameVal = trim($data['indicator'] ?? $data['nama_indikator'] ?? '');
        $unitVal = trim($data['unit'] ?? $data['satuan'] ?? '%');
        $sourceVal = trim($data['source'] ?? $data['sumber_data'] ?? 'Input Manual');
        $defVal = trim($data['definition'] ?? $data['definisi_operasional'] ?? '');

        if (empty($defVal)) {
            $catRow = $this->db->where('kode_indikator', $codeVal)
                ->where('kode_wilayah', $kodeWilayah)
                ->get('katalog_indikator')
                ->row_array();
            if ($catRow && !empty($catRow['definisi_operasional'])) {
                $defVal = trim($catRow['definisi_operasional']);
            }
        }

        // Pastikan indikator terdaftar di katalog_indikator
        if (!empty($codeVal)) {
            $catExists = $this->db->where('kode_indikator', $codeVal)
                ->where('kode_wilayah', $kodeWilayah)
                ->get('katalog_indikator')
                ->row_array();
            if (!$catExists) {
                $this->db->insert('katalog_indikator', array(
                    'kode_indikator'       => $codeVal,
                    'nama_indikator'       => $nameVal ?: $codeVal,
                    'klaster'              => $clusterVal,
                    'satuan'               => $unitVal,
                    'frekuensi'            => 'Tahunan',
                    'sumber_data'          => $sourceVal,
                    'definisi_operasional' => $defVal,
                    'kode_wilayah'         => $kodeWilayah,
                    'status_validasi'      => 'Perlu review',
                    'is_deleted'           => 0
                ));
            } else if (!empty($defVal) && empty($catExists['definisi_operasional'])) {
                $this->db->where('id', (int)$catExists['id'])->update('katalog_indikator', array('definisi_operasional' => $defVal));
            }
        }

        // Cek jika multi-year series dikirimkan (series_years[] & series_values[])
        $seriesYears = !empty($data['series_years']) && is_array($data['series_years']) ? $data['series_years'] : null;
        $seriesValues = !empty($data['series_values']) && is_array($data['series_values']) ? $data['series_values'] : null;

        if (!empty($seriesYears) && !empty($seriesValues)) {
            $savedAny = false;
            for ($i = 0; $i < count($seriesYears); $i++) {
                $yr = (int)($seriesYears[$i] ?? 0);
                $valRaw = $seriesValues[$i] ?? '';
                if ($yr < 1900 || $valRaw === '' || $valRaw === null) {
                    continue;
                }
                $val = $this->clean_numeric_value($valRaw);

                $existing = $this->db->where('kode_indikator', $codeVal)
                    ->where('tahun', $yr)
                    ->where('kode_wilayah', $kodeWilayah)
                    ->where('is_deleted', 0)
                    ->get('database_ekonomi')
                    ->row_array();

                if ($existing) {
                    $this->db->where('id', (int)$existing['id'])->update('database_ekonomi', array(
                        'nilai'                => $val,
                        'nama_indikator'       => $nameVal ?: $existing['nama_indikator'],
                        'klaster'              => $clusterVal,
                        'satuan'               => $unitVal,
                        'sumber_data'          => $sourceVal,
                        'definisi_operasional' => $defVal,
                        'status_validasi'      => 'Perlu review',
                        'catatan'              => null,
                        'is_deleted'           => 0
                    ));
                    $savedAny = true;
                } else {
                    $rec = array(
                        'kode_indikator'       => $codeVal,
                        'nama_indikator'       => $nameVal ?: $codeVal,
                        'klaster'              => $clusterVal,
                        'wilayah'              => trim($data['region'] ?? $data['wilayah'] ?? $namaWilayah),
                        'kode_wilayah'         => $kodeWilayah,
                        'periode'              => trim($data['period'] ?? $data['periode'] ?? 'Tahunan'),
                        'tahun'                => $yr,
                        'nilai'                => $val,
                        'satuan'               => $unitVal,
                        'sumber_data'          => $sourceVal,
                        'definisi_operasional' => $defVal,
                        'status_validasi'      => 'Perlu review',
                        'diverifikasi_oleh'    => null,
                        'catatan'              => null,
                        'is_deleted'           => 0,
                        'deleted_at'           => null,
                        'dibuat_pada'          => date('Y-m-d H:i:s')
                    );
                    $ins = $this->db->insert('database_ekonomi', $rec);
                    if ($ins) {
                        $savedAny = true;
                    }
                }

                // Sinkronkan ke early_warning jika kode indikator ada di tabel EWS
                $ewsMatch = $this->db->where('kode_indikator', $codeVal)->where('kode_wilayah', $kodeWilayah)->get('early_warning')->row_array();
                if ($ewsMatch) {
                    $this->update_ews_threshold($codeVal, (float)$ewsMatch['ambang_batas'], $ewsMatch['arah_risiko'], $val, $kodeWilayah);
                }
                // Jika rekaman merupakan sub-indikator (mengandung .), otomatis hitung ulang total parent-nya
                if (strpos($codeVal, '.') !== false) {
                    $parentCode = explode('.', $codeVal)[0];
                    $this->recalculate_parent_total($parentCode, $yr, $kodeWilayah);
                }
            }
            return $savedAny;
        }

        // Fallback untuk single record (tahun & nilai)
        $record = array(
            'kode_indikator'       => $codeVal,
            'nama_indikator'       => $nameVal,
            'klaster'              => $clusterVal,
            'wilayah'              => trim($data['region'] ?? $data['wilayah'] ?? $namaWilayah),
            'kode_wilayah'         => $kodeWilayah,
            'periode'              => trim($data['period'] ?? $data['periode'] ?? 'Tahunan'),
            'tahun'                => (int)($data['year'] ?? $data['tahun'] ?? 2024),
            'nilai'                => (float)($data['value'] ?? $data['nilai'] ?? 0),
            'satuan'               => $unitVal,
            'sumber_data'          => $sourceVal,
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
            // Sinkronkan ke early_warning jika kode indikator ada di tabel EWS
            $ewsMatch = $this->db->where('kode_indikator', $record['kode_indikator'])->where('kode_wilayah', $record['kode_wilayah'])->get('early_warning')->row_array();
            if ($ewsMatch) {
                $this->update_ews_threshold($record['kode_indikator'], (float)$ewsMatch['ambang_batas'], $ewsMatch['arah_risiko'], (float)$record['nilai'], $record['kode_wilayah']);
            }
            // Jika rekaman merupakan sub-indikator (mengandung .), otomatis hitung ulang total parent-nya
            if (strpos($record['kode_indikator'], '.') !== false) {
                $parentCode = explode('.', $record['kode_indikator'])[0];
                $this->recalculate_parent_total($parentCode, $record['tahun'], $record['kode_wilayah']);
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
            if (strpos($code, '.') !== false) {
                $parentCode = explode('.', $code)[0];
                $rowYear = $record['tahun'] ?? $existing['tahun'] ?? date('Y');
                $this->recalculate_parent_total($parentCode, $rowYear, $rowWilayah);
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

    /**
     * Hitung ulang dan sinkronkan nilai total akumulasi untuk parent indikator yang memiliki sub-rincian (Kuartal / Sektoral)
     */
    public function recalculate_parent_total($parentCode, $year, $kode_wilayah = null) {
        $parentCode = strtoupper(trim($parentCode));
        $canAutoSum = in_array($parentCode, array('INV-001', 'INV-002', 'INV-003', 'EKO-003', 'EKO-004', 'EKO-007', 'EKO-015', 'EKO-016'));
        if (!$canAutoSum) return false;

        $kodeWil = $this->get_active_kode_wilayah($kode_wilayah);
        $year = (int)$year;
        $isInv = (strpos($parentCode, 'INV-') === 0);
        $isExp = in_array($parentCode, array('EKO-015', 'EKO-016'));

        // Hitung total dari sub-indikator yang ada
        $this->db->select_sum('nilai', 'tot')
            ->where('kode_wilayah', $kodeWil)
            ->where('tahun', $year)
            ->where('is_deleted', 0)
            ->like('kode_indikator', $parentCode . '.', 'after');

        // Hindari duplikasi TM (Tanpa Migas) pada sektoral
        if (!$isInv && !$isExp) {
            $this->db->where('kode_indikator !=', $parentCode . '.TM');
        }

        $row = $this->db->get('database_ekonomi')->row_array();

        if ($row && $row['tot'] !== null) {
            $totalVal = round((float)$row['tot'], 2);

            $cat = $this->db->where('kode_indikator', $parentCode)->get('katalog_indikator')->row_array();
            $pName = $cat ? $cat['nama_indikator'] : $parentCode;
            $unit = $cat ? $cat['satuan'] : 'Miliar rupiah';
            $cluster = $cat ? $cat['klaster'] : (strpos($parentCode, 'INV-') === 0 ? 'Investasi' : 'Output dan pertumbuhan');
            $def = $cat ? ($cat['definisi_operasional'] ?? '') : '';

            $existing = $this->db->where('kode_wilayah', $kodeWil)
                ->where('kode_indikator', $parentCode)
                ->where('tahun', $year)
                ->get('database_ekonomi')
                ->row_array();

            if ($existing) {
                $this->db->where('id', (int)$existing['id'])->update('database_ekonomi', array(
                    'nilai' => $totalVal,
                    'is_deleted' => 0
                ));
            } else {
                $this->db->insert('database_ekonomi', array(
                    'kode_wilayah' => $kodeWil,
                    'kode_indikator' => $parentCode,
                    'nama_indikator' => $pName,
                    'klaster' => $cluster,
                    'wilayah' => $this->get_nama_wilayah($kodeWil),
                    'periode' => 'Tahunan',
                    'tahun' => $year,
                    'nilai' => $totalVal,
                    'satuan' => $unit,
                    'sumber_data' => 'BPS Provinsi Jawa Timur',
                    'definisi_operasional' => $def,
                    'status_validasi' => 'Tervalidasi',
                    'is_deleted' => 0
                ));
            }
            return $totalVal;
        }
        return false;
    }

    /**
     * Ambil deret tahun dan nilai untuk satu indikator (untuk modal Edit Series)
     */
    public function get_indicator_series($code, $kode_wilayah = null) {
        $kodeWil = $this->get_active_kode_wilayah($kode_wilayah);
        $code = trim($code);

        $records = $this->db->where('kode_indikator', $code)
            ->where('kode_wilayah', $kodeWil)
            ->where('is_deleted', 0)
            ->order_by('tahun', 'ASC')
            ->get('database_ekonomi')
            ->result_array();

        $cat = $this->db->where('kode_indikator', $code)->get('katalog_indikator')->row_array();
        $firstRec = !empty($records) ? $records[0] : null;

        $indName = $cat ? $cat['nama_indikator'] : ($firstRec['nama_indikator'] ?? $code);
        $cluster = $cat ? $cat['klaster'] : ($firstRec['klaster'] ?? 'Ekonomi');
        $unit = $cat ? $cat['satuan'] : ($firstRec['satuan'] ?? '');
        $source = $cat ? $cat['sumber_data'] : ($firstRec['sumber_data'] ?? '');
        $def = $cat ? ($cat['definisi_operasional'] ?? '') : ($firstRec['definisi_operasional'] ?? '');

        $recByYear = array();
        foreach ($records as $r) {
            $recByYear[(int)$r['tahun']] = $r;
        }

        $allYears = array_keys($recByYear);
        $baseYears = (strpos($code, 'INV-') === 0) ? array(2023, 2024, 2025, 2026) : array(2018, 2019, 2020, 2021, 2022, 2023, 2024, 2025);
        $mergedYears = array_unique(array_merge($baseYears, $allYears));
        sort($mergedYears);

        $isParentWithSubs = in_array($code, array('INV-001', 'INV-002', 'INV-003', 'EKO-003', 'EKO-004', 'EKO-007', 'EKO-015', 'EKO-016'));

        $subTotalsByYear = array();
        if ($isParentWithSubs) {
            $isInv = (strpos($code, 'INV-') === 0);
            $isExp = in_array($code, array('EKO-015', 'EKO-016'));
            $this->db->select('tahun, SUM(nilai) as tot, COUNT(*) as cnt')
                ->where('kode_wilayah', $kodeWil)
                ->where('is_deleted', 0)
                ->like('kode_indikator', $code . '.', 'after')
                ->group_by('tahun');
            if (!$isInv && !$isExp) {
                $this->db->where('kode_indikator !=', $code . '.TM');
            }
            $subSumRows = $this->db->get('database_ekonomi')->result_array();
            foreach ($subSumRows as $ssr) {
                $subTotalsByYear[(int)$ssr['tahun']] = round((float)$ssr['tot'], 2);
            }
            $mergedYears = array_unique(array_merge($mergedYears, array_keys($subTotalsByYear)));
            sort($mergedYears);
        }

        $yearsList = array();
        $uploadedCount = 0;
        foreach ($mergedYears as $yr) {
            if ($isParentWithSubs && isset($subTotalsByYear[$yr])) {
                $uploadedCount++;
                $yearsList[] = array(
                    'year' => $yr,
                    'value' => $subTotalsByYear[$yr],
                    'status' => isset($recByYear[$yr]) ? $recByYear[$yr]['status_validasi'] : 'Tervalidasi',
                    'notes' => isset($recByYear[$yr]) ? ($recByYear[$yr]['catatan'] ?? '') : 'Total otomatis akumulasi rincian',
                    'has_data' => true
                );
            } elseif (isset($recByYear[$yr])) {
                $uploadedCount++;
                $r = $recByYear[$yr];
                $yearsList[] = array(
                    'year' => $yr,
                    'value' => (float)$r['nilai'],
                    'status' => $r['status_validasi'],
                    'notes' => $r['catatan'] ?? '',
                    'has_data' => true
                );
            } else {
                $yearsList[] = array(
                    'year' => $yr,
                    'value' => null,
                    'status' => 'Belum ada data',
                    'notes' => '',
                    'has_data' => false
                );
            }
        }

        return array(
            'code' => $code,
            'indicator' => $indName,
            'cluster' => $cluster,
            'unit' => $unit,
            'source' => $source,
            'definition' => $def,
            'region' => $this->get_nama_wilayah($kodeWil),
            'kode_wilayah' => $kodeWil,
            'is_parent_total' => $isParentWithSubs,
            'uploaded_count' => $uploadedCount,
            'total_years' => count($yearsList),
            'years' => $yearsList
        );
    }

    /**
     * Perbarui deret tahun indikator dari modal Edit Series
     */
    public function update_indicator_series($code, $seriesData, $kode_wilayah = null, $meta = array()) {
        $kodeWil = $this->get_active_kode_wilayah($kode_wilayah);
        $code = trim($code);
        $oldCode = !empty($meta['old_code']) ? trim($meta['old_code']) : $code;

        $years = array();
        $values = array();

        if (isset($seriesData['years']) && isset($seriesData['values'])) {
            $years = $seriesData['years'];
            $values = $seriesData['values'];
        } elseif (is_array($seriesData)) {
            foreach ($seriesData as $y => $v) {
                $years[] = $y;
                $values[] = $v;
            }
        }

        $savedCount = 0;
        $this->db->trans_start();

        $cat = $this->db->where('kode_indikator', $oldCode)->get('katalog_indikator')->row_array();
        $pName = !empty($meta['indicator']) ? trim($meta['indicator']) : ($cat ? $cat['nama_indikator'] : $code);
        $cluster = !empty($meta['cluster']) ? trim($meta['cluster']) : ($cat ? $cat['klaster'] : 'Ekonomi');
        $unit = !empty($meta['unit']) ? trim($meta['unit']) : ($cat ? $cat['satuan'] : '');
        $source = !empty($meta['source']) ? trim($meta['source']) : ($cat ? $cat['sumber_data'] : '');
        $def = isset($meta['definition']) ? trim($meta['definition']) : ($cat ? ($cat['definisi_operasional'] ?? '') : '');

        // Jika kode indikator diubah
        if ($oldCode !== $code && !empty($oldCode) && !empty($code)) {
            $this->db->where('kode_indikator', $oldCode)->where('kode_wilayah', $kodeWil)->update('database_ekonomi', array('kode_indikator' => $code));
            $this->db->where('kode_indikator', $oldCode)->where('kode_wilayah', $kodeWil)->update('katalog_indikator', array('kode_indikator' => $code));
        }

        // Perbarui atribut metadata umum untuk seluruh tahun dari indikator ini
        $this->db->where('kode_indikator', $code)
            ->where('kode_wilayah', $kodeWil)
            ->update('database_ekonomi', array(
                'nama_indikator'       => $pName,
                'klaster'              => $cluster,
                'satuan'               => $unit,
                'sumber_data'          => $source,
                'definisi_operasional' => $def
            ));

        // Sinkronkan ke katalog_indikator
        $catExists = $this->db->where('kode_indikator', $code)->where('kode_wilayah', $kodeWil)->get('katalog_indikator')->row_array();
        if ($catExists) {
            $this->db->where('id', (int)$catExists['id'])->update('katalog_indikator', array(
                'nama_indikator'       => $pName,
                'klaster'              => $cluster,
                'satuan'               => $unit,
                'sumber_data'          => $source,
                'definisi_operasional' => $def
            ));
        } else {
            $this->db->insert('katalog_indikator', array(
                'kode_indikator'       => $code,
                'nama_indikator'       => $pName,
                'klaster'              => $cluster,
                'satuan'               => $unit,
                'frekuensi'            => 'Tahunan',
                'sumber_data'          => $source,
                'definisi_operasional' => $def,
                'kode_wilayah'         => $kodeWil,
                'status_validasi'      => 'Perlu review',
                'is_deleted'           => 0
            ));
        }

        foreach ($years as $idx => $yr) {
            $yr = (int)$yr;
            if ($yr < 1900 || $yr > 2100) continue;

            $rawVal = isset($values[$idx]) ? $values[$idx] : null;
            if ($rawVal === null || $rawVal === '') continue;
            $val = $this->clean_numeric_value($rawVal);

            $existing = $this->db->where('kode_wilayah', $kodeWil)
                ->where('kode_indikator', $code)
                ->where('tahun', $yr)
                ->get('database_ekonomi')
                ->row_array();

            if ($existing) {
                $this->db->where('id', (int)$existing['id'])->update('database_ekonomi', array(
                    'nilai' => $val,
                    'nama_indikator' => $pName,
                    'klaster' => $cluster,
                    'satuan' => $unit,
                    'sumber_data' => $source,
                    'definisi_operasional' => $def,
                    'status_validasi' => 'Perlu review',
                    'catatan' => null,
                    'is_deleted' => 0
                ));
            } else {
                $this->db->insert('database_ekonomi', array(
                    'kode_wilayah' => $kodeWil,
                    'kode_indikator' => $code,
                    'nama_indikator' => $pName,
                    'klaster' => $cluster,
                    'wilayah' => $this->get_nama_wilayah($kodeWil),
                    'periode' => 'Tahunan',
                    'tahun' => $yr,
                    'nilai' => $val,
                    'satuan' => $unit,
                    'sumber_data' => $source,
                    'definisi_operasional' => $def,
                    'status_validasi' => 'Perlu review',
                    'is_deleted' => 0
                ));
            }
            $savedCount++;

            if (strpos($code, '.') !== false) {
                $parentCode = explode('.', $code)[0];
                $this->recalculate_parent_total($parentCode, $yr, $kodeWil);
            }
        }

        $this->db->trans_complete();
        return $savedCount;
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

    public function update_series_status($code, $status, $notes = null, $verifier = null, $kode_wilayah = null) {
        $kode = $this->get_active_kode_wilayah($kode_wilayah);
        $this->db->where('kode_indikator', $code)->where('kode_wilayah', $kode)->where('is_deleted', 0);
        $data = array('status_validasi' => $status);
        if ($status === 'Tervalidasi' || $status === 'Perlu review') {
            $data['catatan'] = null;
        } elseif ($notes !== null) {
            $noteVal = trim($notes);
            $data['catatan'] = ($noteVal !== '') ? $noteVal : null;
        }
        if ($verifier !== null) {
            $data['diverifikasi_oleh'] = $verifier;
        }
        $updated = $this->db->update('database_ekonomi', $data);

        if ($status === 'Tervalidasi' && $updated) {
            $this->sync_to_catalog($code, $kode);
            $this->sync_all_validated_to_ews($kode);
        } else {
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

    // Definisi Master Sektor Lapangan Usaha BPS & Komponen Pengeluaran
    public function get_sectoral_definitions($parentCode = 'EKO-003') {
        $parentCode = strtoupper(trim($parentCode ?: 'EKO-003'));

        // Kuartal Investasi (INV-001, INV-002, INV-003)
        if (in_array($parentCode, array('INV-001', 'INV-002', 'INV-003'))) {
            $invNames = array(
                'INV-001' => 'Realisasi Investasi Total PMA dan PMDN',
                'INV-002' => 'Realisasi Investasi PMA',
                'INV-003' => 'Realisasi Investasi PMDN'
            );
            $baseName = $invNames[$parentCode] ?? 'Realisasi Investasi';
            return array(
                'Q1' => array('code' => "{$parentCode}.Q1", 'cat_label' => 'Q1', 'name' => "{$baseName} - Kuartal 1 (Q1)", 'short_name' => 'Kuartal 1 (Q1)', 'def' => "Realisasi investasi pada periode Kuartal 1 (Januari - Maret)."),
                'Q2' => array('code' => "{$parentCode}.Q2", 'cat_label' => 'Q2', 'name' => "{$baseName} - Kuartal 2 (Q2)", 'short_name' => 'Kuartal 2 (Q2)', 'def' => "Realisasi investasi pada periode Kuartal 2 (April - Juni)."),
                'Q3' => array('code' => "{$parentCode}.Q3", 'cat_label' => 'Q3', 'name' => "{$baseName} - Kuartal 3 (Q3)", 'short_name' => 'Kuartal 3 (Q3)', 'def' => "Realisasi investasi pada periode Kuartal 3 (Juli - September)."),
                'Q4' => array('code' => "{$parentCode}.Q4", 'cat_label' => 'Q4', 'name' => "{$baseName} - Kuartal 4 (Q4)", 'short_name' => 'Kuartal 4 (Q4)', 'def' => "Realisasi investasi pada periode Kuartal 4 (Oktober - Desember)."),
                'TOTAL' => array('code' => $parentCode, 'cat_label' => 'TOTAL', 'name' => $baseName, 'short_name' => 'TOTAL REALISASI TAHUNAN', 'def' => "Total akumulasi realisasi investasi tahunan ({$baseName}).")
            );
        }

        // Komponen Pengeluaran (EKO-015 dan EKO-016)
        if ($parentCode === 'EKO-015' || $parentCode === 'EKO-016') {
            $isAdhk = ($parentCode === 'EKO-016');
            $priceLabel = $isAdhk ? 'atas dasar harga konstan' : 'atas dasar harga berlaku';
            $pName = $isAdhk ? 'PDRB Pengeluaran ADHK' : 'PDRB Pengeluaran ADHB';
            return array(
                '1' => array('code' => "{$parentCode}.1", 'cat_label' => '1', 'name' => "{$pName}: Pengeluaran Konsumsi Rumah Tangga", 'short_name' => 'Pengeluaran Konsumsi Rumah Tangga', 'def' => "Pengeluaran atas barang dan jasa yang dilakukan oleh rumah tangga {$priceLabel}."),
                '2' => array('code' => "{$parentCode}.2", 'cat_label' => '2', 'name' => "{$pName}: Pengeluaran Konsumsi LNPRT", 'short_name' => 'Pengeluaran Konsumsi LNPRT', 'def' => "Pengeluaran konsumsi lembaga non-profit yang melayani rumah tangga {$priceLabel}."),
                '3' => array('code' => "{$parentCode}.3", 'cat_label' => '3', 'name' => "{$pName}: Pengeluaran Konsumsi Pemerintah", 'short_name' => 'Pengeluaran Konsumsi Pemerintah', 'def' => "Pengeluaran pemerintah untuk barang dan jasa bagi kepentingan kolektif maupun individual {$priceLabel}."),
                '4' => array('code' => "{$parentCode}.4", 'cat_label' => '4', 'name' => "{$pName}: Pembentukan Modal Tetap Bruto", 'short_name' => 'Pembentukan Modal Tetap Bruto (PMTB)', 'def' => "Pengeluaran unit produksi untuk menambah aset tetap dikurangi pelepasan aset tetap {$priceLabel}."),
                '5' => array('code' => "{$parentCode}.5", 'cat_label' => '5', 'name' => "{$pName}: Perubahan Inventori", 'short_name' => 'Perubahan Inventori', 'def' => "Perubahan nilai stok persediaan barang pada awal dan akhir periode {$priceLabel}."),
                '6' => array('code' => "{$parentCode}.6", 'cat_label' => '6', 'name' => "{$pName}: Net Ekspor", 'short_name' => 'Net Ekspor Antar Daerah / Luar Negeri', 'def' => "Nilai ekspor neto (selisih ekspor barang/jasa luar daerah dikurangi impor) {$priceLabel}."),
                'TOTAL' => array('code' => $parentCode, 'cat_label' => 'TOTAL', 'name' => ($isAdhk ? 'PDRB Pengeluaran ADHK' : 'PDRB Pengeluaran ADHB'), 'short_name' => 'PRODUK DOMESTIK REGIONAL BRUTO (PENGELUARAN)', 'def' => "Total Produk Domestik Regional Bruto menurut komponen pengeluaran {$priceLabel}.")
            );
        }

        // Ketenagakerjaan: Lapangan Usaha & Status Pekerjaan (TK-007)
        if ($parentCode === 'TK-007') {
            return array(
                'AGR' => array('code' => 'TK-007.AGR', 'cat_label' => 'AGR', 'name' => 'Penduduk Bekerja Lapangan Usaha: Pertanian', 'short_name' => 'Pertanian', 'def' => 'Persentase penduduk bekerja pada sektor pertanian.'),
                'MAN' => array('code' => 'TK-007.MAN', 'cat_label' => 'MAN', 'name' => 'Penduduk Bekerja Lapangan Usaha: Manufaktur', 'short_name' => 'Manufaktur', 'def' => 'Persentase penduduk bekerja pada sektor manufaktur/industri pengolahan.'),
                'SRV' => array('code' => 'TK-007.SRV', 'cat_label' => 'SRV', 'name' => 'Penduduk Bekerja Lapangan Usaha: Jasa-jasa', 'short_name' => 'Jasa-jasa', 'def' => 'Persentase penduduk bekerja pada sektor jasa-jasa.'),
                'S1'  => array('code' => 'TK-007.S1', 'cat_label' => 'S1', 'name' => 'Status Pekerjaan: Buruh/Karyawan/Pegawai', 'short_name' => 'Buruh/Karyawan/Pegawai', 'def' => 'Persentase pekerja dengan status buruh, karyawan, atau pegawai.'),
                'S2'  => array('code' => 'TK-007.S2', 'cat_label' => 'S2', 'name' => 'Status Pekerjaan: Berusaha Sendiri', 'short_name' => 'Berusaha Sendiri', 'def' => 'Persentase pekerja yang berusaha sendiri tanpa dibantu orang lain.'),
                'S3'  => array('code' => 'TK-007.S3', 'cat_label' => 'S3', 'name' => 'Status Pekerjaan: Berusaha Dibantu Buruh Tidak Tetap/Tak Dibayar', 'short_name' => 'Berusaha Dibantu Buruh Tidak Tetap', 'def' => 'Persentase pekerja yang berusaha dibantu buruh tidak tetap atau pekerja keluarga tidak dibayar.'),
                'S4'  => array('code' => 'TK-007.S4', 'cat_label' => 'S4', 'name' => 'Status Pekerjaan: Pekerja Keluarga/Tak Dibayar', 'short_name' => 'Pekerja Keluarga/Tak Dibayar', 'def' => 'Persentase pekerja keluarga atau pembantu tidak dibayar.'),
                'S5'  => array('code' => 'TK-007.S5', 'cat_label' => 'S5', 'name' => 'Status Pekerjaan: Pekerja Bebas', 'short_name' => 'Pekerja Bebas', 'def' => 'Persentase pekerja bebas baik di sektor pertanian maupun non-pertanian.'),
                'S6'  => array('code' => 'TK-007.S6', 'cat_label' => 'S6', 'name' => 'Status Pekerjaan: Berusaha Dibantu Buruh Tetap/Dibayar', 'short_name' => 'Berusaha Dibantu Buruh Tetap', 'def' => 'Persentase pekerja yang berusaha dibantu buruh tetap atau pekerja dibayar.'),
                'TOTAL' => array('code' => 'TK-007', 'cat_label' => 'TOTAL', 'name' => 'Penduduk bekerja menurut lapangan usaha', 'short_name' => 'TOTAL PERSENTASE (100%)', 'def' => 'Total persentase penduduk bekerja menurut lapangan usaha dan status.')
            );
        }

        // Ketenagakerjaan: Pekerja Formal dan Informal (TK-008)
        if ($parentCode === 'TK-008') {
            return array(
                'FORMAL'   => array('code' => 'TK-008.FORMAL', 'cat_label' => 'FORMAL', 'name' => 'Pekerja Formal', 'short_name' => 'Formal', 'def' => 'Persentase tenaga kerja yang bekerja pada sektor formal.'),
                'INFORMAL' => array('code' => 'TK-008.INFORMAL', 'cat_label' => 'INFORMAL', 'name' => 'Pekerja Informal', 'short_name' => 'Informal', 'def' => 'Persentase tenaga kerja yang bekerja pada sektor informal.'),
                'TOTAL'    => array('code' => 'TK-008', 'cat_label' => 'TOTAL', 'name' => 'Pekerja formal dan informal', 'short_name' => 'TOTAL PERSENTASE (100%)', 'def' => 'Total persentase pekerja formal dan informal.')
            );
        }

        // Ketenagakerjaan: Setengah Pengangguran (TK-010)
        if ($parentCode === 'TK-010') {
            return array(
                'JML'   => array('code' => 'TK-010.JML', 'cat_label' => 'JML', 'name' => 'Jumlah Setengah Pengangguran', 'short_name' => 'Jumlah Setengah Pengangguran (Orang)', 'def' => 'Jumlah penduduk yang bekerja di bawah jam kerja normal dan masih mencari pekerjaan atau bersedia menerima pekerjaan lain.'),
                'TOTAL' => array('code' => 'TK-010', 'cat_label' => 'RATE', 'name' => 'Setengah pengangguran', 'short_name' => 'TINGKAT SETENGAH PENGANGGURAN (%)', 'def' => 'Persentase setengah pengangguran terhadap jumlah angkatan kerja.')
            );
        }

        // Pariwisata: Kunjungan Wisatawan Bulanan (PAR-001 & PAR-002) - 12 Bulan murni tanpa A-Z dan tanpa Total
        if ($parentCode === 'PAR-001' || $parentCode === 'PAR-002') {
            $isWisman = ($parentCode === 'PAR-002');
            $labelPrefix = $isWisman ? 'Wisatawan Mancanegara' : 'Wisatawan Nusantara';
            $months = array(
                'JAN' => 'Januari',   'FEB' => 'Februari', 'MAR' => 'Maret',
                'APR' => 'April',     'MEI' => 'Mei',      'JUN' => 'Juni',
                'JUL' => 'Juli',      'AGS' => 'Agustus',  'SEP' => 'September',
                'OKT' => 'Oktober',   'NOV' => 'November', 'DES' => 'Desember'
            );
            $defs = array();
            foreach ($months as $k => $mName) {
                $defs[$k] = array(
                    'code' => "{$parentCode}.{$k}",
                    'cat_label' => $k,
                    'name' => "Kunjungan {$labelPrefix}: Bulan {$mName}",
                    'short_name' => "Bulan {$mName}",
                    'def' => "Jumlah kunjungan {$labelPrefix} pada bulan {$mName}."
                );
            }
            return $defs;
        }

        // Pariwisata: Tamu Hotel & Akomodasi (PAR-003) - Domestik & Mancanegara (Tanpa A-Z, Tanpa Total)
        if ($parentCode === 'PAR-003') {
            return array(
                'DOM' => array('code' => 'PAR-003.DOM', 'cat_label' => 'DOM', 'name' => 'Jumlah Tamu Domestik (Nusantara)', 'short_name' => 'Domestik (Nusantara)', 'def' => 'Jumlah tamu wisatawan nusantara yang menginap di hotel/akomodasi.'),
                'MAN' => array('code' => 'PAR-003.MAN', 'cat_label' => 'MAN', 'name' => 'Jumlah Tamu Mancanegara', 'short_name' => 'Mancanegara', 'def' => 'Jumlah tamu wisatawan mancanegara yang menginap di hotel/akomodasi.')
            );
        }

        // Pariwisata: TPK Hotel (PAR-004) - Bintang, Non Bintang, Seluruh Jenis Hotel (12 Bulan)
        if ($parentCode === 'PAR-004') {
            return array(
                'BIN' => array('code' => 'PAR-004.BIN', 'cat_label' => 'BIN', 'name' => 'TPK Hotel Berbintang', 'short_name' => 'Bintang', 'def' => 'Tingkat penghunian kamar khusus pada hotel klasifikasi berbintang.'),
                'NON' => array('code' => 'PAR-004.NON', 'cat_label' => 'NON', 'name' => 'TPK Hotel Non Bintang', 'short_name' => 'Non Bintang', 'def' => 'Tingkat penghunian kamar pada hotel klasifikasi non berbintang.'),
                'ALL' => array('code' => 'PAR-004', 'cat_label' => 'ALL', 'name' => 'TPK Seluruh Jenis Hotel', 'short_name' => 'Seluruh Jenis Hotel', 'def' => 'Tingkat penghunian kamar gabungan seluruh jenis hotel.')
            );
        }

        // Pariwisata: Jumlah Kamar Hotel (PAR-005) - Bintang & Non Bintang (Tanpa A-Z, Tanpa Total)
        if ($parentCode === 'PAR-005') {
            return array(
                'BIN' => array('code' => 'PAR-005.BIN', 'cat_label' => 'BIN', 'name' => 'Jumlah Kamar Hotel Berbintang', 'short_name' => 'Hotel Berbintang', 'def' => 'Jumlah kamar yang tersedia pada hotel berbintang.'),
                'NON' => array('code' => 'PAR-005.NON', 'cat_label' => 'NON', 'name' => 'Jumlah Kamar Hotel Non Bintang', 'short_name' => 'Hotel Non Bintang', 'def' => 'Jumlah kamar yang tersedia pada hotel non berbintang.')
            );
        }

        // Pariwisata: Jumlah Usaha Hotel & Restoran (PAR-006) - Bintang, Non Bintang, Restoran (Tanpa A-Z, Tanpa Total)
        if ($parentCode === 'PAR-006') {
            return array(
                'BIN' => array('code' => 'PAR-006.BIN', 'cat_label' => 'BIN', 'name' => 'Jumlah Hotel Berbintang', 'short_name' => 'Hotel Berbintang', 'def' => 'Jumlah unit usaha hotel klasifikasi berbintang.'),
                'NON' => array('code' => 'PAR-006.NON', 'cat_label' => 'NON', 'name' => 'Jumlah Hotel Non Bintang', 'short_name' => 'Hotel Non Bintang', 'def' => 'Jumlah unit usaha hotel klasifikasi non berbintang / akomodasi lainnya.'),
                'RES' => array('code' => 'PAR-006.RES', 'cat_label' => 'RES', 'name' => 'Jumlah Restoran / Rumah Makan', 'short_name' => 'Restoran / Rumah Makan', 'def' => 'Jumlah usaha restoran dan rumah makan.')
            );
        }

        // Produksi Sektoral menurut 25 Wilayah Kecamatan (PRO-001, PRO-002, PRO-004)
        if (in_array($parentCode, array('PRO-001', 'PRO-002', 'PRO-004'))) {
            $kecamatans = array(
                '01' => 'Pesanggaran',
                '02' => 'Siliragung',
                '03' => 'Bangorejo',
                '04' => 'Purwoharjo',
                '05' => 'Tegaldlimo',
                '06' => 'Muncar',
                '07' => 'Cluring',
                '08' => 'Gambiran',
                '09' => 'Tegalsari',
                '10' => 'Glenmore',
                '11' => 'Kalibaru',
                '12' => 'Genteng',
                '13' => 'Srono',
                '14' => 'Rogojampi',
                '15' => 'Blimbingsari',
                '16' => 'Kabat',
                '17' => 'Singojuruh',
                '18' => 'Sempu',
                '19' => 'Songgon',
                '20' => 'Glagah',
                '21' => 'Licin',
                '22' => 'Banyuwangi',
                '23' => 'Giri',
                '24' => 'Kalipuro',
                '25' => 'Wongsorejo'
            );
            $pLabel = 'Produksi Komoditas Pertanian';
            if ($parentCode === 'PRO-002') $pLabel = 'Luas Panen Pertanian';
            if ($parentCode === 'PRO-004') $pLabel = 'Produksi Perikanan';

            $defs = array();
            foreach ($kecamatans as $k => $kecName) {
                $defs[$k] = array(
                    'code' => "{$parentCode}.{$k}",
                    'cat_label' => $k,
                    'name' => "{$pLabel}: Kecamatan {$kecName}",
                    'short_name' => "Kecamatan {$kecName}",
                    'def' => "Nilai realisasi {$pLabel} di wilayah Kecamatan {$kecName}."
                );
            }
            $defs['TOTAL'] = array(
                'code' => $parentCode,
                'cat_label' => 'TOTAL',
                'name' => "Total {$pLabel} Kabupaten Banyuwangi",
                'short_name' => "TOTAL KABUPATEN BANYUWANGI",
                'def' => "Total akumulasi {$pLabel} seluruh kecamatan di Kabupaten Banyuwangi."
            );
            return $defs;
        }

        // Fiskal: Komponen PAD (FIS-001) - Pajak, Retribusi, Kekayaan, Lain-lain
        if ($parentCode === 'FIS-001') {
            return array(
                'PAJ' => array('code' => 'FIS-001.PAJ', 'cat_label' => 'PAJ', 'name' => 'Pajak Daerah', 'short_name' => 'Pajak Daerah', 'def' => 'Realisasi pendapatan pajak daerah.'),
                'RET' => array('code' => 'FIS-001.RET', 'cat_label' => 'RET', 'name' => 'Retribusi Daerah', 'short_name' => 'Retribusi Daerah', 'def' => 'Realisasi pendapatan retribusi daerah.'),
                'KKY' => array('code' => 'FIS-001.KKY', 'cat_label' => 'KKY', 'name' => 'Hasil Pengelolaan Kekayaan Daerah yang Dipisahkan', 'short_name' => 'Hasil Pengelolaan Kekayaan Daerah', 'def' => 'Pendapatan hasil pengelolaan kekayaan daerah yang dipisahkan.'),
                'LL'  => array('code' => 'FIS-001.LL',  'cat_label' => 'LL',  'name' => 'Lain-lain PAD yang Sah', 'short_name' => 'Lain-lain PAD yang Sah', 'def' => 'Realisasi lain-lain pendapatan asli daerah yang sah.')
            );
        }

        // Fiskal: Pendapatan Transfer (FIS-002) - Pusat & Antar Daerah
        if ($parentCode === 'FIS-002') {
            return array(
                'PUS' => array('code' => 'FIS-002.PUS', 'cat_label' => 'PUS', 'name' => 'Transfer Pemerintah Pusat', 'short_name' => 'Pemerintah Pusat', 'def' => 'Realisasi pendapatan transfer dari pemerintah pusat.'),
                'DAE' => array('code' => 'FIS-002.DAE', 'cat_label' => 'DAE', 'name' => 'Transfer Antar Daerah', 'short_name' => 'Antar Daerah', 'def' => 'Realisasi pendapatan transfer antar daerah.')
            );
        }

        // Fiskal: Belanja Daerah (FIS-003) - Belanja Modal (FIS-004)
        if ($parentCode === 'FIS-003') {
            return array(
                'MOD' => array(
                    'code' => 'FIS-004',
                    'cat_label' => 'MOD',
                    'name' => 'Belanja Modal',
                    'short_name' => 'Belanja Modal',
                    'def' => 'Belanja pemerintah daerah untuk memperoleh aset tetap/aset lainnya sesuai klasifikasi APBD yang berlaku.'
                ),
                'TOTAL' => array(
                    'code' => 'FIS-003',
                    'cat_label' => 'TOTAL',
                    'name' => 'Total Belanja Daerah',
                    'short_name' => 'TOTAL BELANJA DAERAH',
                    'def' => 'Total seluruh pengeluaran dari rekening kas umum daerah yang diakui sebagai belanja daerah.'
                )
            );
        }

        // Harga & Inflasi: IHK Umum (HRG-001), Inflasi Bulanan (HRG-003), Inflasi Tahunan (HRG-004) - 12 Bulan
        if (in_array($parentCode, array('HRG-001', 'HRG-003', 'HRG-004'))) {
            $mNames = array(
                'HRG-001' => 'IHK Umum',
                'HRG-003' => 'Inflasi Bulanan (mtm)',
                'HRG-004' => 'Inflasi Tahunan (yoy)'
            );
            $baseName = $mNames[$parentCode] ?? 'Harga & Inflasi';
            $months = array(
                'JAN' => 'Januari', 'FEB' => 'Februari', 'MAR' => 'Maret', 'APR' => 'April',
                'MEI' => 'Mei', 'JUN' => 'Juni', 'JUL' => 'Juli', 'AGS' => 'Agustus',
                'SEP' => 'September', 'OKT' => 'Oktober', 'NOV' => 'November', 'DES' => 'Desember'
            );
            $defs = array();
            foreach ($months as $k => $mName) {
                $defs[$k] = array(
                    'code' => "{$parentCode}.{$k}",
                    'cat_label' => $k,
                    'name' => "{$baseName}: Bulan {$mName}",
                    'short_name' => "Bulan {$mName}",
                    'def' => "Nilai realisasi {$baseName} pada Bulan {$mName}."
                );
            }
            $defs['TOTAL'] = array(
                'code' => $parentCode,
                'cat_label' => 'TOTAL',
                'name' => "{$baseName}: Tahunan",
                'short_name' => 'TAHUNAN / RATA-RATA',
                'def' => "Nilai capaian tahunan / rata-rata tahunan {$baseName}."
            );
            return $defs;
        }

        // Harga & Inflasi: IHK Kelompok Pengeluaran (HRG-002) & Bobot Keranjang IHK (HRG-006) - 11 Kelompok
        if (in_array($parentCode, array('HRG-002', 'HRG-006'))) {
            $isHrg06 = ($parentCode === 'HRG-006');
            $hLabel = $isHrg06 ? 'Bobot / Andil IHK' : 'IHK Kelompok Pengeluaran';
            $kelompoks = array(
                '01' => 'Makanan, Minuman, dan Tembakau',
                '02' => 'Pakaian dan Alas Kaki',
                '03' => 'Perumahan, Air, Listrik, dan Bahan Bakar Rumah Tangga',
                '04' => 'Perlengkapan, Peralatan, dan Pemeliharaan Rutin Rumah Tangga',
                '05' => 'Kesehatan',
                '06' => 'Transportasi',
                '07' => 'Informasi, Komunikasi, dan Jasa Keuangan',
                '08' => 'Rekreasi, Olahraga, dan Budaya',
                '09' => 'Pendidikan',
                '10' => 'Penyediaan Makanan dan Minuman/Restoran',
                '11' => 'Perawatan Pribadi dan Jasa Lainnya'
            );
            $defs = array();
            foreach ($kelompoks as $k => $kName) {
                $defs[$k] = array(
                    'code' => "{$parentCode}.{$k}",
                    'cat_label' => $k,
                    'name' => "{$hLabel}: {$kName}",
                    'short_name' => $kName,
                    'def' => "Capaian {$hLabel} untuk kelompok pengeluaran {$kName}."
                );
            }
            $defs['TOTAL'] = array(
                'code' => $parentCode,
                'cat_label' => 'TOTAL',
                'name' => "Total {$hLabel} Umum",
                'short_name' => 'TOTAL / UMUM',
                'def' => "Total agregat {$hLabel} seluruh kelompok pengeluaran."
            );
            return $defs;
        }

        // Harga & Inflasi: Harga Eceran Komoditas Pangan Strategis (HRG-005) - 20 Kelompok / 48 Komoditas Standar Excel
        if ($parentCode === 'HRG-005') {
            $commodities = array(
                '01' => array('name' => 'Beras Premium', 'unit' => 'kg', 'group_no' => 1, 'group_name' => 'BERAS'),
                '02' => array('name' => 'Beras Medium', 'unit' => 'kg', 'group_no' => 1, 'group_name' => 'BERAS'),
                '03' => array('name' => 'Gula Kristal Putih', 'unit' => 'kg', 'group_no' => 2, 'group_name' => 'GULA'),
                '04' => array('name' => 'Minyak Goreng Curah', 'unit' => 'kg', 'group_no' => 3, 'group_name' => 'MINYAK GORENG'),
                '05' => array('name' => 'Minyak Goreng Kemasan Premium', 'unit' => '1 liter', 'group_no' => 3, 'group_name' => 'MINYAK GORENG'),
                '06' => array('name' => 'Minyak Goreng Kemasan Sederhana', 'unit' => '1 Liter', 'group_no' => 3, 'group_name' => 'MINYAK GORENG'),
                '07' => array('name' => 'Minyak Goreng MINYAKITA', 'unit' => '1 Liter', 'group_no' => 3, 'group_name' => 'MINYAK GORENG'),
                '08' => array('name' => 'Daging Sapi Paha Belakang', 'unit' => 'kg', 'group_no' => 4, 'group_name' => 'DAGING'),
                '09' => array('name' => 'Daging Ayam Ras', 'unit' => 'kg', 'group_no' => 4, 'group_name' => 'DAGING'),
                '10' => array('name' => 'Daging Ayam Kampung', 'unit' => 'ekor', 'group_no' => 4, 'group_name' => 'DAGING'),
                '11' => array('name' => 'Telur Ayam Ras', 'unit' => 'kg', 'group_no' => 5, 'group_name' => 'TELUR AYAM'),
                '12' => array('name' => 'Telur Ayam Kampung', 'unit' => 'kg', 'group_no' => 5, 'group_name' => 'TELUR AYAM'),
                '13' => array('name' => 'Susu Kental Manis Merk Bendera', 'unit' => '370 gr/kl', 'group_no' => 6, 'group_name' => 'SUSU', 'sub_header' => 'Kental Manis'),
                '14' => array('name' => 'Susu Kental Manis Merk Indomilk', 'unit' => '370 gr/kl', 'group_no' => 6, 'group_name' => 'SUSU', 'sub_header' => 'Kental Manis'),
                '15' => array('name' => 'Susu Bubuk Merk Bendera (Instant)', 'unit' => '400 gr/dos', 'group_no' => 6, 'group_name' => 'SUSU', 'sub_header' => 'Susu Bubuk'),
                '16' => array('name' => 'Susu Bubuk Merk Indomilk (Instant)', 'unit' => '400 gr/dos', 'group_no' => 6, 'group_name' => 'SUSU', 'sub_header' => 'Susu Bubuk'),
                '17' => array('name' => 'Jagung Pipilan Kering', 'unit' => 'kg', 'group_no' => 7, 'group_name' => 'JAGUNG PIPILAN KERING', 'single' => true),
                '18' => array('name' => 'Bata', 'unit' => 'Buah', 'group_no' => 8, 'group_name' => 'GARAM BERYODIUM', 'excel_label' => 'Bata'),
                '19' => array('name' => 'Halus', 'unit' => 'kg', 'group_no' => 8, 'group_name' => 'GARAM BERYODIUM', 'excel_label' => 'Halus'),
                '20' => array('name' => 'Terigu Protein Sedang (Kemasan)', 'unit' => 'kg', 'group_no' => 9, 'group_name' => 'TEPUNG TERIGU'),
                '21' => array('name' => 'Kedelai Impor', 'unit' => 'kg', 'group_no' => 10, 'group_name' => 'KACANG KEDELAI'),
                '22' => array('name' => 'Kedelai Lokal', 'unit' => 'kg', 'group_no' => 10, 'group_name' => 'KACANG KEDELAI'),
                '23' => array('name' => 'Indomie Rasa Kari Ayam', 'unit' => 'Bungkus', 'group_no' => 11, 'group_name' => 'MIE INSTANT'),
                '24' => array('name' => 'Cabe Merah Keriting', 'unit' => 'kg', 'group_no' => 12, 'group_name' => 'CABE'),
                '25' => array('name' => 'Cabe Merah Besar', 'unit' => 'kg', 'group_no' => 12, 'group_name' => 'CABE'),
                '26' => array('name' => 'Cabe Rawit Merah', 'unit' => 'kg', 'group_no' => 12, 'group_name' => 'CABE'),
                '27' => array('name' => 'Bawang Merah', 'unit' => 'kg', 'group_no' => 13, 'group_name' => 'BAWANG'),
                '28' => array('name' => 'Bawang Putih Sinco/Honan', 'unit' => 'kg', 'group_no' => 13, 'group_name' => 'BAWANG'),
                '29' => array('name' => 'Ikan Asin Teri', 'unit' => 'kg', 'group_no' => 14, 'group_name' => 'IKAN ASIN TERI', 'single' => true),
                '30' => array('name' => 'KACANG HIJAU', 'unit' => 'kg', 'group_no' => 15, 'group_name' => 'KACANG HIJAU', 'single' => true),
                '31' => array('name' => 'KACANG TANAH', 'unit' => 'kg', 'group_no' => 16, 'group_name' => 'KACANG TANAH', 'single' => true),
                '32' => array('name' => 'KETELA POHON', 'unit' => 'kg', 'group_no' => 17, 'group_name' => 'KETELA POHON', 'single' => true),
                '33' => array('name' => 'KOL/KUBIS', 'unit' => 'kg', 'group_no' => 18, 'group_name' => 'SAYUR MAYUR', 'excel_label' => 'KOL/KUBIS'),
                '34' => array('name' => 'KENTANG', 'unit' => 'kg', 'group_no' => 18, 'group_name' => 'SAYUR MAYUR', 'excel_label' => 'KENTANG'),
                '35' => array('name' => 'Tomat Merah', 'unit' => 'kg', 'group_no' => 18, 'group_name' => 'SAYUR MAYUR'),
                '36' => array('name' => 'WORTEL', 'unit' => 'kg', 'group_no' => 18, 'group_name' => 'SAYUR MAYUR', 'excel_label' => 'WORTEL'),
                '37' => array('name' => 'BUNCIS', 'unit' => 'kg', 'group_no' => 18, 'group_name' => 'SAYUR MAYUR', 'excel_label' => 'BUNCIS'),
                '38' => array('name' => 'Semen Gresik', 'unit' => '40 Kg', 'group_no' => 19, 'group_name' => 'SEMEN'),
                '39' => array('name' => 'Semen Tiga Roda', 'unit' => '40 Kg', 'group_no' => 19, 'group_name' => 'SEMEN'),
                '40' => array('name' => 'Semen Padang', 'unit' => '40 Kg', 'group_no' => 19, 'group_name' => 'SEMEN'),
                '41' => array('name' => 'Semen Tonasa', 'unit' => '40 Kg', 'group_no' => 19, 'group_name' => 'SEMEN'),
                '42' => array('name' => 'Semen Bosowa', 'unit' => '40 Kg', 'group_no' => 19, 'group_name' => 'SEMEN'),
                '43' => array('name' => 'Semen Dynamix', 'unit' => '40 Kg', 'group_no' => 19, 'group_name' => 'SEMEN'),
                '44' => array('name' => 'Ikan Bandeng', 'unit' => 'kg', 'group_no' => 20, 'group_name' => 'IKAN SEGAR'),
                '45' => array('name' => 'Ikan Kembung', 'unit' => 'kg', 'group_no' => 20, 'group_name' => 'IKAN SEGAR'),
                '46' => array('name' => 'Ikan Tuna', 'unit' => 'kg', 'group_no' => 20, 'group_name' => 'IKAN SEGAR'),
                '47' => array('name' => 'Ikan Tongkol', 'unit' => 'kg', 'group_no' => 20, 'group_name' => 'IKAN SEGAR'),
                '48' => array('name' => 'Ikan Cakalang', 'unit' => 'kg', 'group_no' => 20, 'group_name' => 'IKAN SEGAR')
            );
            $defs = array();
            foreach ($commodities as $k => $c) {
                $cName = $c['name'];
                $defs[$k] = array(
                    'code' => "HRG-005.{$k}",
                    'cat_label' => $k,
                    'name' => "Harga: {$cName}",
                    'short_name' => $cName,
                    'unit' => $c['unit'],
                    'group_no' => $c['group_no'],
                    'group_name' => $c['group_name'],
                    'sub_header' => $c['sub_header'] ?? '',
                    'excel_label' => $c['excel_label'] ?? $cName,
                    'single' => !empty($c['single']),
                    'def' => "Rata-rata harga eceran komoditas {$cName} di Kabupaten Banyuwangi."
                );
            }
            $defs['TOTAL'] = array(
                'code' => 'HRG-005',
                'cat_label' => 'TOTAL',
                'name' => 'Rata-rata Harga Pangan Strategis',
                'short_name' => 'RATA-RATA KOMODITAS PANGAN',
                'unit' => 'Rupiah',
                'def' => 'Rata-rata harga eceran komoditas bahan pangan strategis di Kabupaten Banyuwangi.'
            );
            return $defs;
        }

        // Lapangan Usaha (EKO-003, EKO-004, EKO-006, EKO-007)
        $prefix = 'PDRB ADHB';
        if ($parentCode === 'EKO-004') {
            $prefix = 'PDRB ADHK';
        } elseif ($parentCode === 'EKO-006') {
            $prefix = 'Pertumbuhan';
        } elseif ($parentCode === 'EKO-007') {
            $prefix = 'Distribusi';
        }

        $sectors = array(
            'A' => array('name' => 'Pertanian, Kehutanan, dan Perikanan', 'short' => 'Pertanian, Kehutanan, dan Perikanan', 'cat_label' => 'A'),
            'B' => array('name' => 'Pertambangan dan Penggalian', 'short' => 'Pertambangan dan Penggalian', 'cat_label' => 'B'),
            'C' => array('name' => 'Industri Pengolahan', 'short' => 'Industri Pengolahan', 'cat_label' => 'C'),
            'D' => array('name' => 'Pengadaan Listrik dan Gas', 'short' => 'Pengadaan Listrik dan Gas', 'cat_label' => 'D'),
            'E' => array('name' => 'Pengadaan Air, Pengelolaan Sampah, Limbah', 'short' => 'Pengadaan Air, Pengelolaan Sampah, Limbah', 'cat_label' => 'E'),
            'F' => array('name' => 'Konstruksi', 'short' => 'Konstruksi', 'cat_label' => 'F'),
            'G' => array('name' => 'Perdagangan Besar dan Eceran; Reparasi Mobil', 'short' => 'Perdagangan Besar dan Eceran; Reparasi Mobil', 'cat_label' => 'G'),
            'H' => array('name' => 'Transportasi dan Pergudangan', 'short' => 'Transportasi dan Pergudangan', 'cat_label' => 'H'),
            'I' => array('name' => 'Penyediaan Akomodasi dan Makan Minum', 'short' => 'Penyediaan Akomodasi dan Makan Minum', 'cat_label' => 'I'),
            'J' => array('name' => 'Informasi dan Komunikasi', 'short' => 'Informasi dan Komunikasi', 'cat_label' => 'J'),
            'K' => array('name' => 'Jasa Keuangan dan Asuransi', 'short' => 'Jasa Keuangan dan Asuransi', 'cat_label' => 'K'),
            'L' => array('name' => 'Real Estate', 'short' => 'Real Estate', 'cat_label' => 'L'),
            'MN' => array('name' => 'Jasa Perusahaan', 'short' => 'Jasa Perusahaan', 'cat_label' => 'M,N'),
            'O' => array('name' => 'Adm. Pemerintah, Pertahanan & Jaminan Sosial Wajib', 'short' => 'Adm. Pemerintah, Pertahanan & Jaminan Sosial Wajib', 'cat_label' => 'O'),
            'P' => array('name' => 'Jasa Pendidikan', 'short' => 'Jasa Pendidikan', 'cat_label' => 'P'),
            'Q' => array('name' => 'Jasa Kesehatan dan Kegiatan Sosial', 'short' => 'Jasa Kesehatan dan Kegiatan Sosial', 'cat_label' => 'Q'),
            'RSTU' => array('name' => 'Jasa lainnya', 'short' => 'Jasa lainnya', 'cat_label' => 'R,S,T,U')
        );

        $defs = array();
        foreach ($sectors as $k => $sec) {
            $defs[$k] = array(
                'code' => "{$parentCode}.{$k}",
                'cat_label' => $sec['cat_label'] ?? $k,
                'name' => "{$prefix}: {$sec['name']}",
                'short_name' => $sec['short'],
                'def' => "Nilai capaian lapangan usaha {$sec['name']} untuk indikator {$prefix}."
            );
        }

        $totName = 'PDRB ADHB menurut lapangan usaha';
        $totShort = 'PRODUK DOMESTIK REGIONAL BRUTO';
        if ($parentCode === 'EKO-004') {
            $totName = 'PDRB ADHK menurut lapangan usaha';
            $totShort = 'PRODUK DOMESTIK REGIONAL BRUTO';
        } elseif ($parentCode === 'EKO-006') {
            $totName = 'Pertumbuhan PDRB ADHK menurut lapangan usaha';
            $totShort = 'LAJU PERTUMBUHAN EKONOMI TOTAL';
        } elseif ($parentCode === 'EKO-007') {
            $totName = 'Distribusi PDRB ADHB menurut lapangan usaha';
            $totShort = 'TOTAL DISTRIBUSI SEKTOR (100%)';
        }

        $defs['TOTAL'] = array(
            'code' => $parentCode,
            'cat_label' => 'TOTAL',
            'name' => $totName,
            'short_name' => $totShort,
            'def' => "Total agregat seluruh lapangan usaha untuk {$prefix}."
        );

        return $defs;
    }

    // Mengambil struktur 20 kelompok bahan pokok HRG-005 sesuai konsep Excel
    public function get_hrg005_structure() {
        return array(
            1 => array('name' => 'BERAS', 'items' => array('01', '02')),
            2 => array('name' => 'GULA', 'items' => array('03')),
            3 => array('name' => 'MINYAK GORENG', 'items' => array('04', '05', '06', '07')),
            4 => array('name' => 'DAGING', 'items' => array('08', '09', '10')),
            5 => array('name' => 'TELUR AYAM', 'items' => array('11', '12')),
            6 => array(
                'name' => 'SUSU',
                'items' => array('13', '14', '15', '16'),
                'subgroups' => array(
                    array('name' => 'Kental Manis', 'items' => array('13', '14')),
                    array('name' => 'Susu Bubuk', 'items' => array('15', '16'))
                )
            ),
            7 => array('name' => 'JAGUNG PIPILAN KERING', 'items' => array('17'), 'single' => true),
            8 => array('name' => 'GARAM BERYODIUM', 'items' => array('18', '19')),
            9 => array('name' => 'TEPUNG TERIGU', 'items' => array('20')),
            10 => array('name' => 'KACANG KEDELAI', 'items' => array('21', '22')),
            11 => array('name' => 'MIE INSTANT', 'items' => array('23')),
            12 => array('name' => 'CABE', 'items' => array('24', '25', '26')),
            13 => array('name' => 'BAWANG', 'items' => array('27', '28')),
            14 => array('name' => 'IKAN ASIN TERI', 'items' => array('29'), 'single' => true),
            15 => array('name' => 'KACANG HIJAU', 'items' => array('30'), 'single' => true),
            16 => array('name' => 'KACANG TANAH', 'items' => array('31'), 'single' => true),
            17 => array('name' => 'KETELA POHON', 'items' => array('32'), 'single' => true),
            18 => array('name' => 'SAYUR MAYUR', 'items' => array('33', '34', '35', '36', '37')),
            19 => array('name' => 'SEMEN', 'items' => array('38', '39', '40', '41', '42', '43')),
            20 => array('name' => 'IKAN SEGAR', 'items' => array('44', '45', '46', '47', '48'))
        );
    }

    // Ambil matriks lengkap seluruh sektor/komponen untuk modal input/kelola sektoral
    public function get_sectoral_matrix_data($kode_wilayah = null, $parentCode = 'EKO-003') {
        $kodeWil = $this->get_active_kode_wilayah($kode_wilayah);
        $parentCode = strtoupper(trim($parentCode ?: 'EKO-003'));
        $defs = $this->get_sectoral_definitions($parentCode);

        // Ambil nama tersimpan atau baris kustom yang pernah ditambahkan dari katalog_indikator
        $savedInds = $this->db->like('kode_indikator', $parentCode . '.', 'after')
            ->where('is_deleted', 0)
            ->get('katalog_indikator')
            ->result_array();

        $isFixedParent = (strpos($parentCode, 'PAR-') === 0 || strpos($parentCode, 'PRO-') === 0 || strpos($parentCode, 'FIS-') === 0 || strpos($parentCode, 'INV-') === 0 || strpos($parentCode, 'TK-') === 0 || strpos($parentCode, 'HRG-') === 0);

        $customDefs = array();
        foreach ($savedInds as $si) {
            $c = $si['kode_indikator'];
            $suffix = substr($c, strlen($parentCode) + 1);
            if ($suffix === 'TM') continue; // Hindari Tanpa Migas agar hanya 17 sektor murni

            $savedName = $si['nama_indikator'];
            $short = preg_replace('/^(PDRB ADH[BK]|Pertumbuhan|Distribusi|PDRB Pengeluaran ADH[BK]|Harga)\s*(menurut lapangan usaha)?:\s*/i', '', $savedName);
            
            if (isset($defs[$suffix])) {
                $defs[$suffix]['name'] = $savedName;
                if ($parentCode !== 'HRG-005') {
                    $defs[$suffix]['short_name'] = $short;
                }
            } elseif (!$isFixedParent) {
                $customDefs[$suffix] = array(
                    'code' => $c,
                    'cat_label' => $suffix,
                    'name' => $savedName,
                    'short_name' => $short,
                    'def' => $si['definisi_operasional'] ?? "Rincian {$suffix}",
                    'is_custom' => true
                );
            }
        }

        // Sisipkan customDefs sebelum TOTAL
        if (!empty($customDefs)) {
            $mergedDefs = array();
            $inserted = false;
            foreach ($defs as $k => $d) {
                if ($k === 'TOTAL' && !$inserted) {
                    foreach ($customDefs as $ck => $cd) {
                        $mergedDefs[$ck] = $cd;
                    }
                    $inserted = true;
                }
                $mergedDefs[$k] = $d;
            }
            if (!$inserted) {
                foreach ($customDefs as $ck => $cd) {
                    $mergedDefs[$ck] = $cd;
                }
            }
            $defs = $mergedDefs;
        }
        
        $codes = array();
        foreach ($defs as $k => $d) {
            $codes[] = $d['code'];
        }
        if (!in_array($parentCode, $codes)) {
            $codes[] = $parentCode;
        }

        $records = $this->db->where_in('kode_indikator', $codes)
            ->where('kode_wilayah', $kodeWil)
            ->where('is_deleted', 0)
            ->order_by('tahun', 'ASC')
            ->get('database_ekonomi')
            ->result_array();

        $yearsSet = array();
        $values = array();
        $monthlyValues = array();
        $dailyValues = array();
        $dailyDatesSet = array();
        $standardMonths = array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');

        foreach ($records as $r) {
            $c = $r['kode_indikator'];
            $y = (int)$r['tahun'];
            $p = $r['periode'];
            $v = (float)$r['nilai'];
            $yearsSet[$y] = true;
            if (!isset($values[$c])) {
                $values[$c] = array();
            }
            if (!isset($monthlyValues[$c])) {
                $monthlyValues[$c] = array();
            }
            if (!isset($monthlyValues[$c][$y])) {
                $monthlyValues[$c][$y] = array();
            }

            if (preg_match('/^(?:Harian\s*\()?(\d{4}-\d{2}-\d{2})\)?$/i', $p, $dm)) {
                $dStr = $dm[1];
                $dailyDatesSet[$dStr] = true;
                if (!isset($dailyValues[$c])) {
                    $dailyValues[$c] = array();
                }
                $dailyValues[$c][$dStr] = $v;
            } else {
                $cleanMonth = preg_replace('/^Bulanan\s*\((.*?)\)$/i', '$1', $p);
                $monthlyValues[$c][$y][$cleanMonth] = $v;

                if (!isset($values[$c][$y]) || $p === 'Tahunan') {
                    $values[$c][$y] = $v;
                }
            }
        }

        $years = array_keys($yearsSet);
        sort($years);
        $isInv = in_array($parentCode, array('INV-001', 'INV-002', 'INV-003'));
        $isExp = in_array($parentCode, array('EKO-015', 'EKO-016'));
        $isTk07 = ($parentCode === 'TK-007');
        $isTk08 = ($parentCode === 'TK-008');
        $isTk10 = ($parentCode === 'TK-010');
        $isParMonthly = in_array($parentCode, array('PAR-001', 'PAR-002'));
        $isPar = (strpos($parentCode, 'PAR-') === 0);
        $isPro = (strpos($parentCode, 'PRO-') === 0);
        $isFis = (strpos($parentCode, 'FIS-') === 0);
        $isHrg = (strpos($parentCode, 'HRG-') === 0);
        $isHrgMonthlyGroup = in_array($parentCode, array('HRG-002', 'HRG-006'));
        $isPar004 = ($parentCode === 'PAR-004');
        $isMonthlyGroupParent = ($isHrgMonthlyGroup || $isPar004);

        if ($isInv) {
            $unit = 'Miliar rupiah';
            $cluster = 'Investasi';
            $catType = 'quarterly';
            $canAutoSum = true;
        } elseif ($isExp) {
            $unit = 'Miliar rupiah';
            $cluster = 'Output dan pertumbuhan';
            $catType = 'expenditure';
            $canAutoSum = true;
        } elseif ($isTk07) {
            $unit = '%';
            $cluster = 'Ketenagakerjaan';
            $catType = 'tk07';
            $canAutoSum = false;
        } elseif ($isTk08) {
            $unit = '%';
            $cluster = 'Ketenagakerjaan';
            $catType = 'tk08';
            $canAutoSum = true;
        } elseif ($isTk10) {
            $unit = 'Orang / %';
            $cluster = 'Ketenagakerjaan';
            $catType = 'tk10';
            $canAutoSum = false;
        } elseif ($isParMonthly) {
            $unit = 'Kunjungan';
            $cluster = 'Pariwisata dan ekonomi kreatif';
            $catType = 'monthly';
            $canAutoSum = false;
        } elseif ($isPar) {
            if ($parentCode === 'PAR-004') $unit = '%';
            elseif ($parentCode === 'PAR-005') $unit = 'Kamar';
            elseif ($parentCode === 'PAR-006') $unit = 'Unit';
            else $unit = 'Orang/tamu';
            $cluster = 'Pariwisata dan ekonomi kreatif';
            $catType = 'par';
            $canAutoSum = false;
        } elseif ($isPro) {
            $unit = ($parentCode === 'PRO-002') ? 'Hektare' : 'Ton';
            $cluster = 'Produksi sektoral';
            $catType = 'pro';
            $canAutoSum = true;
        } elseif ($isFis) {
            $unit = 'Rupiah';
            $cluster = 'Keuangan daerah';
            $catType = 'fis';
            $canAutoSum = false;
        } elseif ($isHrg) {
            if ($parentCode === 'HRG-003' || $parentCode === 'HRG-004') $unit = '%';
            elseif ($parentCode === 'HRG-005') $unit = 'Rupiah';
            elseif ($parentCode === 'HRG-006') $unit = 'Bobot/indeks';
            else $unit = 'Indeks';
            $cluster = 'Harga dan daya beli';
            $catType = 'hrg';
            $canAutoSum = false;
        } else {
            $unit = in_array($parentCode, array('EKO-006', 'EKO-007')) ? '%' : 'Miliar rupiah';
            $cluster = 'Produksi sektoral';
            $catType = 'sectoral';
            $canAutoSum = ($parentCode !== 'EKO-006');
        }

        if ($isMonthlyGroupParent) {
            $defaultYears = $isPar004 ? array(2023, 2024, 2025, 2026) : array(2024, 2025, 2026);
            if (empty($years)) {
                $years = $defaultYears;
            } else {
                $years = array_values(array_unique(array_merge($defaultYears, $years)));
                sort($years);
            }
            $canAutoSum = $isHrgMonthlyGroup;

            if ($isHrgMonthlyGroup) {
                // Hitung nilai agregat rata-rata TOTAL seluruh kelompok per tahun dan per bulan
                foreach ($years as $yr) {
                    foreach (array_merge($standardMonths, array('Tahunan')) as $m) {
                        $mSum = 0;
                        $mCount = 0;
                        foreach ($defs as $k => $def) {
                            if ($k === 'TOTAL') continue;
                            $c = $def['code'];
                            if (isset($monthlyValues[$c][$yr][$m])) {
                                $mSum += $monthlyValues[$c][$yr][$m];
                                $mCount++;
                            }
                        }
                        if ($mCount > 0) {
                            $monthlyValues['TOTAL'][$yr][$m] = round($mSum / $mCount, 2);
                        }
                    }
                    if (isset($monthlyValues['TOTAL'][$yr]['Tahunan'])) {
                        $values['TOTAL'][$yr] = $monthlyValues['TOTAL'][$yr]['Tahunan'];
                    }
                }
            } elseif ($isPar004) {
                // Pastikan nilai Tahunan terisi di values dan monthlyValues jika ada data bulanan
                foreach ($years as $yr) {
                    foreach ($defs as $k => $def) {
                        $c = $def['code'];
                        if (!isset($monthlyValues[$c][$yr]['Tahunan'])) {
                            $mSum = 0;
                            $mCount = 0;
                            foreach ($standardMonths as $m) {
                                if (isset($monthlyValues[$c][$yr][$m]) && $monthlyValues[$c][$yr][$m] !== '' && $monthlyValues[$c][$yr][$m] !== null) {
                                    $mSum += (float)$monthlyValues[$c][$yr][$m];
                                    $mCount++;
                                }
                            }
                            if ($cnt = $mCount) {
                                $monthlyValues[$c][$yr]['Tahunan'] = round($mSum / $cnt, 2);
                            }
                        }
                        if (isset($monthlyValues[$c][$yr]['Tahunan'])) {
                            $values[$c][$yr] = $monthlyValues[$c][$yr]['Tahunan'];
                        }
                    }
                }
            }
        } elseif (empty($years)) {
            $years = ($parentCode === 'HRG-005') ? array(2024, 2025, 2026) : ($isPro ? array(2024, 2025) : (($isInv || $isTk07 || $isTk08) ? array(2023, 2024, 2025, 2026) : array(2018, 2019, 2020, 2021, 2022, 2023, 2024, 2025)));
        }

        $dailyDates = array();
        if ($parentCode === 'HRG-005') {
            for ($d = 1; $d <= 30; $d++) {
                $dStr = sprintf('2026-11-%02d', $d);
                $dailyDatesSet[$dStr] = true;
            }
            $dailyDates = array_keys($dailyDatesSet);
            sort($dailyDates);

            foreach ($dailyDates as $dStr) {
                $dSum = 0;
                $dCount = 0;
                foreach ($defs as $k => $def) {
                    if ($k === 'TOTAL') continue;
                    $c = $def['code'];
                    if (isset($dailyValues[$c][$dStr]) && (float)$dailyValues[$c][$dStr] > 0) {
                        $dSum += (float)$dailyValues[$c][$dStr];
                        $dCount++;
                    }
                }
                if ($dCount > 0) {
                    $dailyValues['TOTAL'][$dStr] = round($dSum / $dCount, 2);
                }
            }

            foreach ($years as $yr) {
                if (!isset($values['TOTAL'][$yr]) || empty($values['TOTAL'][$yr])) {
                    $cSum = 0;
                    $cCount = 0;
                    foreach ($defs as $k => $def) {
                        if ($k === 'TOTAL') continue;
                        $c = $def['code'];
                        if (isset($values[$c][$yr]) && (float)$values[$c][$yr] > 0) {
                            $cSum += (float)$values[$c][$yr];
                            $cCount++;
                        }
                    }
                    if ($cCount > 0) {
                        $values['TOTAL'][$yr] = round($cSum / $cCount, 2);
                    }
                }
            }
        }

        return array(
            'parent_code' => $parentCode,
            'category_type' => $catType,
            'unit' => $unit,
            'cluster' => $cluster,
            'can_auto_sum' => $canAutoSum,
            'is_monthly_group' => $isMonthlyGroupParent,
            'is_hrg005' => ($parentCode === 'HRG-005'),
            'hrg005_structure' => ($parentCode === 'HRG-005') ? $this->get_hrg005_structure() : null,
            'daily_dates' => $dailyDates,
            'daily_values' => $dailyValues,
            'months' => array_merge($standardMonths, array('Tahunan')),
            'monthly_values' => $monthlyValues,
            'definitions' => $defs,
            'years' => $years,
            'values' => $values,
            'kode_wilayah' => $kodeWil,
            'nama_wilayah' => $this->get_nama_wilayah($kodeWil)
        );
    }

    // Simpan matriks seluruh sektor/komponen sekaligus (Batch save), termasuk nama & label kategori yang diubah
    public function save_sectoral_matrix_data($matrixValues, $years, $kode_wilayah = null, $parentCode = 'EKO-003', $customNames = array(), $customCatLabels = array(), $monthlyValues = null, $dailyValues = null) {
        $kodeWil = $this->get_active_kode_wilayah($kode_wilayah);
        $namaWil = $this->get_nama_wilayah($kodeWil);
        $parentCode = strtoupper(trim($parentCode ?: 'EKO-003'));
        $defs = $this->get_sectoral_definitions($parentCode);

        $isInv = in_array($parentCode, array('INV-001', 'INV-002', 'INV-003'));
        $isExp = in_array($parentCode, array('EKO-015', 'EKO-016'));
        $isTk07 = ($parentCode === 'TK-007');
        $isTk08 = ($parentCode === 'TK-008');
        $isTk10 = ($parentCode === 'TK-010');
        $isParMonthly = in_array($parentCode, array('PAR-001', 'PAR-002'));
        $isPar = (strpos($parentCode, 'PAR-') === 0);
        $isPro = (strpos($parentCode, 'PRO-') === 0);
        $isFis = (strpos($parentCode, 'FIS-') === 0);
        $isHrg = (strpos($parentCode, 'HRG-') === 0);

        if ($isInv) {
            $unit = 'Miliar rupiah';
            $cluster = 'Investasi';
            $prefix = 'Investasi';
            $rowPeriod = 'Triwulanan';
            $freq = 'Triwulanan';
        } elseif ($isExp) {
            $unit = 'Miliar rupiah';
            $cluster = 'Output dan pertumbuhan';
            $prefix = ($parentCode === 'EKO-016') ? 'PDRB Pengeluaran ADHK' : 'PDRB Pengeluaran ADHB';
            $rowPeriod = 'Tahunan';
            $freq = 'Tahunan';
        } elseif ($isTk07) {
            $unit = 'Persen';
            $cluster = 'Ketenagakerjaan';
            $prefix = 'Tenaga Kerja';
            $rowPeriod = 'Tahunan';
            $freq = 'Tahunan';
        } elseif ($isTk08) {
            $unit = 'Persen';
            $cluster = 'Ketenagakerjaan';
            $prefix = 'Pekerja';
            $rowPeriod = 'Tahunan';
            $freq = 'Tahunan';
        } elseif ($isTk10) {
            $unit = 'Persen';
            $cluster = 'Ketenagakerjaan';
            $prefix = 'Setengah Pengangguran';
            $rowPeriod = 'Agustus';
            $freq = 'Semesteran (Agustus)';
        } elseif ($isParMonthly) {
            $unit = 'Kunjungan';
            $cluster = 'Pariwisata dan ekonomi kreatif';
            $prefix = ($parentCode === 'PAR-001') ? 'Wisnus' : 'Wisman';
            $rowPeriod = 'Bulanan';
            $freq = 'Bulanan';
        } elseif ($isPar) {
            if ($parentCode === 'PAR-004') $unit = 'Persen';
            elseif ($parentCode === 'PAR-005') $unit = 'Kamar';
            elseif ($parentCode === 'PAR-006') $unit = 'Unit';
            else $unit = 'Orang/tamu';
            $cluster = 'Pariwisata dan ekonomi kreatif';
            $prefix = 'Pariwisata';
            $rowPeriod = 'Tahunan';
            $freq = 'Tahunan';
        } elseif ($isPro) {
            $unit = ($parentCode === 'PRO-002') ? 'Hektare' : 'Ton';
            $cluster = 'Produksi sektoral';
            $prefix = 'Komoditas';
            $rowPeriod = 'Tahunan';
            $freq = 'Tahunan';
        } elseif ($isFis) {
            $unit = 'Rupiah';
            $cluster = 'Keuangan daerah';
            $prefix = 'Keuangan';
            $rowPeriod = 'Tahunan';
            $freq = 'Tahunan';
        } elseif ($isHrg) {
            if ($parentCode === 'HRG-003' || $parentCode === 'HRG-004') $unit = '%';
            elseif ($parentCode === 'HRG-005') $unit = 'Rupiah';
            elseif ($parentCode === 'HRG-006') $unit = 'Bobot/indeks';
            else $unit = 'Indeks';
            $cluster = 'Harga dan daya beli';
            $prefix = 'Harga & Inflasi';
            $rowPeriod = in_array($parentCode, array('HRG-001', 'HRG-003', 'HRG-004')) ? 'Bulanan' : 'Tahunan';
            $freq = in_array($parentCode, array('HRG-001', 'HRG-003', 'HRG-004')) ? 'Bulanan' : 'Tahunan';
        } else {
            $unit = in_array($parentCode, array('EKO-006', 'EKO-007')) ? '%' : 'Miliar rupiah';
            $cluster = 'Produksi sektoral';
            $prefix = 'PDRB ADHB';
            if ($parentCode === 'EKO-004') {
                $prefix = 'PDRB ADHK';
            } elseif ($parentCode === 'EKO-006') {
                $prefix = 'Pertumbuhan';
            } elseif ($parentCode === 'EKO-007') {
                $prefix = 'Distribusi';
            }
            $rowPeriod = 'Tahunan';
            $freq = 'Tahunan';
        }

        $this->db->trans_start();

        $monthNameMap = array(
            'JAN' => 'Januari', 'FEB' => 'Februari', 'MAR' => 'Maret',
            'APR' => 'April',   'MEI' => 'Mei',      'JUN' => 'Juni',
            'JUL' => 'Juli',    'AGS' => 'Agustus',  'SEP' => 'September',
            'OKT' => 'Oktober', 'NOV' => 'November', 'DES' => 'Desember'
        );

        foreach ($defs as $key => $def) {
            $code = $def['code'];
            $isMonthlyHrgOrPar = ($isParMonthly || in_array($parentCode, array('HRG-001', 'HRG-003', 'HRG-004')));
            $curPeriod = ($isMonthlyHrgOrPar && isset($monthNameMap[$key])) ? $monthNameMap[$key] : $rowPeriod;
            $itemUnit = ($code === 'TK-010.JML') ? 'Orang' : (($parentCode === 'HRG-005' && $key !== 'TOTAL' && !empty($def['unit'])) ? 'Rp/' . $def['unit'] : $unit);
            // Jika ada nama yang diubah oleh user untuk baris ini
            $name = $def['name'];
            if (!empty($customNames[$key])) {
                $trimmedInput = trim($customNames[$key]);
                if (strpos($trimmedInput, ':') === false && $key !== 'TOTAL') {
                    $name = "{$prefix}: {$trimmedInput}";
                } else {
                    $name = $trimmedInput;
                }
            }
            $explanation = $def['def'];
            $catValues = isset($matrixValues[$key]) ? $matrixValues[$key] : (isset($matrixValues[$code]) ? $matrixValues[$code] : array());

            // Pastikan terdaftar dan perbarui nama di katalog_indikator
            $catExists = $this->db->where('kode_indikator', $code)->get('katalog_indikator')->row_array();
            if (!$catExists) {
                $this->db->insert('katalog_indikator', array(
                    'kode_wilayah' => $kodeWil,
                    'kode_indikator' => $code,
                    'nama_indikator' => $name,
                    'klaster' => $cluster,
                    'satuan' => $itemUnit,
                    'frekuensi' => $freq,
                    'sumber_data' => 'BPS Provinsi Jawa Timur / BPS Kabupaten/Kota',
                    'definisi_operasional' => $explanation,
                    'is_deleted' => 0
                ));
            } else {
                $this->db->where('id', (int)$catExists['id'])->update('katalog_indikator', array(
                    'nama_indikator' => $name,
                    'satuan' => $itemUnit,
                    'frekuensi' => $freq,
                    'is_deleted' => 0
                ));
            }

            // Simpan setiap tahun
            foreach ($years as $yr) {
                $yr = (int)$yr;
                if ($yr <= 1900 || $yr >= 2100) continue;

                $valRaw = isset($catValues[$yr]) ? $catValues[$yr] : null;
                if ($valRaw === null || $valRaw === '') continue;
                $val = $this->clean_numeric_value($valRaw);

                $existing = $this->db->where('kode_indikator', $code)
                    ->where('kode_wilayah', $kodeWil)
                    ->where('tahun', $yr)
                    ->where('periode', $curPeriod)
                    ->get('database_ekonomi')
                    ->row_array();

                if ($existing) {
                    $this->db->where('id', (int)$existing['id'])->update('database_ekonomi', array(
                        'nama_indikator' => $name,
                        'nilai' => $val,
                        'satuan' => $itemUnit,
                        'klaster' => $cluster,
                        'periode' => $curPeriod,
                        'is_deleted' => 0,
                        'deleted_at' => null,
                        'status_validasi' => 'Tervalidasi'
                    ));
                } else {
                    $this->db->insert('database_ekonomi', array(
                        'kode_wilayah' => $kodeWil,
                        'kode_indikator' => $code,
                        'nama_indikator' => $name,
                        'klaster' => $cluster,
                        'wilayah' => $namaWil,
                        'periode' => $curPeriod,
                        'tahun' => $yr,
                        'nilai' => $val,
                        'satuan' => $itemUnit,
                        'sumber_data' => 'BPS Provinsi Jawa Timur / BPS Kabupaten/Kota',
                        'definisi_operasional' => $explanation,
                        'status_validasi' => 'Tervalidasi',
                        'diverifikasi_oleh' => 'BPS Jawa Timur / Rilis Resmi',
                        'catatan' => "Sinkronisasi Matriks Sektoral ({$parentCode})",
                        'is_deleted' => 0,
                        'dibuat_pada' => date('Y-m-d H:i:s')
                    ));
                }
            }
        }

        // Dukungan untuk baris kategori tambahan (custom added rows)
        if (is_array($matrixValues)) {
            foreach ($matrixValues as $customKey => $catValues) {
                if (isset($defs[$customKey])) continue;
                if (empty($customKey) || !is_array($catValues)) continue;

                $rawLabel = !empty($customCatLabels[$customKey]) ? trim($customCatLabels[$customKey]) : $customKey;
                $cleanKey = preg_replace('/[^A-Za-z0-9_]/', '', $rawLabel);
                if (empty($cleanKey)) {
                    $cleanKey = preg_replace('/[^A-Za-z0-9_]/', '', $customKey);
                }
                if (empty($cleanKey)) continue;
                $customCode = "{$parentCode}.{$cleanKey}";
                $customName = !empty($customNames[$customKey]) ? trim($customNames[$customKey]) : "Rincian {$rawLabel}";
                if (strpos($customName, ':') === false) {
                    $customName = "{$prefix}: {$customName}";
                }

                $catExists = $this->db->where('kode_indikator', $customCode)->get('katalog_indikator')->row_array();
                if (!$catExists) {
                    $this->db->insert('katalog_indikator', array(
                        'kode_wilayah' => $kodeWil,
                        'kode_indikator' => $customCode,
                        'nama_indikator' => $customName,
                        'klaster' => $cluster,
                        'satuan' => $unit,
                        'frekuensi' => 'Tahunan',
                        'sumber_data' => 'BPS Kabupaten/Kota',
                        'definisi_operasional' => "Rincian kategori {$cleanKey}",
                        'is_deleted' => 0
                    ));
                } else {
                    $this->db->where('id', (int)$catExists['id'])->update('katalog_indikator', array('nama_indikator' => $customName));
                }

                foreach ($years as $yr) {
                    $yr = (int)$yr;
                    if ($yr <= 1900 || $yr >= 2100) continue;
                    $valRaw = isset($catValues[$yr]) ? $catValues[$yr] : null;
                    if ($valRaw === null || $valRaw === '') continue;
                    $val = $this->clean_numeric_value($valRaw);

                    $existing = $this->db->where('kode_indikator', $customCode)
                        ->where('kode_wilayah', $kodeWil)
                        ->where('tahun', $yr)
                        ->get('database_ekonomi')
                        ->row_array();

                    if ($existing) {
                        $this->db->where('id', (int)$existing['id'])->update('database_ekonomi', array(
                            'nama_indikator' => $customName,
                            'nilai' => $val,
                            'satuan' => $unit,
                            'is_deleted' => 0,
                            'deleted_at' => null,
                            'status_validasi' => 'Tervalidasi'
                        ));
                    } else {
                        $this->db->insert('database_ekonomi', array(
                            'kode_wilayah' => $kodeWil,
                            'kode_indikator' => $customCode,
                            'nama_indikator' => $customName,
                            'klaster' => $cluster,
                            'wilayah' => $namaWil,
                            'periode' => 'Tahunan',
                            'tahun' => $yr,
                            'nilai' => $val,
                            'satuan' => $unit,
                            'sumber_data' => 'BPS Kabupaten/Kota',
                            'definisi_operasional' => "Rincian kategori {$cleanKey}",
                            'status_validasi' => 'Tervalidasi',
                            'diverifikasi_oleh' => 'BPS Jawa Timur / Rilis Resmi',
                            'catatan' => "Sinkronisasi Matriks Sektoral Tambahan ({$parentCode})",
                            'is_deleted' => 0,
                            'dibuat_pada' => date('Y-m-d H:i:s')
                        ));
                    }
                }
            }
        }

        // Simpan rincian data bulanan jika ada (untuk grup bulanan seperti HRG-002 dan HRG-006)
        if (!empty($monthlyValues) && is_array($monthlyValues)) {
            foreach ($monthlyValues as $code => $yrData) {
                if (!is_array($yrData)) continue;
                $katalog = $this->db->where('kode_indikator', $code)->get('katalog_indikator')->row_array();
                $name = $katalog ? $katalog['nama_indikator'] : ($defs[$code]['name'] ?? $code);
                $explanation = $katalog ? ($katalog['definisi_operasional'] ?? '') : ($defs[$code]['def'] ?? '');

                foreach ($yrData as $yr => $mList) {
                    $yr = (int)$yr;
                    if ($yr <= 1900 || $yr >= 2100 || !is_array($mList)) continue;

                    foreach ($mList as $mName => $valRaw) {
                        if ($valRaw === null || $valRaw === '') continue;
                        $val = $this->clean_numeric_value($valRaw);
                        $isParCode = (strpos($code, 'PAR-') === 0);
                        $period = ($mName === 'Tahunan') ? 'Tahunan' : ($isParCode ? $mName : "Bulanan ({$mName})");

                        $existing = $this->db->where('kode_indikator', $code)
                            ->where('kode_wilayah', $kodeWil)
                            ->where('tahun', $yr)
                            ->group_start()
                                ->where('periode', $period)
                                ->or_where('periode', $mName)
                                ->or_where('periode', "Bulanan ({$mName})")
                            ->group_end()
                            ->where('is_deleted', 0)
                            ->get('database_ekonomi')
                            ->row_array();

                        if ($existing) {
                            $this->db->where('id', (int)$existing['id'])->update('database_ekonomi', array(
                                'nilai' => $val,
                                'nama_indikator' => $name,
                                'satuan' => $unit,
                                'klaster' => $cluster,
                                'status_validasi' => 'Tervalidasi'
                            ));
                        } else {
                            $this->db->insert('database_ekonomi', array(
                                'kode_wilayah' => $kodeWil,
                                'kode_indikator' => $code,
                                'nama_indikator' => $name,
                                'klaster' => $cluster,
                                'wilayah' => $namaWil,
                                'periode' => $period,
                                'tahun' => $yr,
                                'nilai' => $val,
                                'satuan' => $unit,
                                'sumber_data' => 'BPS Provinsi Jawa Timur / BPS Kabupaten Banyuwangi',
                                'definisi_operasional' => $explanation,
                                'status_validasi' => 'Tervalidasi',
                                'diverifikasi_oleh' => 'BPS Jawa Timur / Rilis Resmi',
                                'catatan' => "Sinkronisasi Matriks Bulanan ({$parentCode})",
                                'is_deleted' => 0,
                                'dibuat_pada' => date('Y-m-d H:i:s')
                            ));
                        }
                    }
                }
            }
        }

        // Simpan rincian data harian jika ada (untuk HRG-005 Harga Eceran Komoditas Pangan Strategis)
        if (!empty($dailyValues) && is_array($dailyValues)) {
            $defsByCode = array();
            foreach ($defs as $dk => $dv) {
                $defsByCode[$dv['code']] = $dv;
            }
            foreach ($dailyValues as $code => $dateList) {
                if (!is_array($dateList) || $code === 'TOTAL' || $code === $parentCode) continue;
                $dDef = $defsByCode[$code] ?? null;
                $katalog = $this->db->where('kode_indikator', $code)->get('katalog_indikator')->row_array();
                $name = $katalog ? $katalog['nama_indikator'] : ($dDef['name'] ?? $code);
                $itemUnit = ($parentCode === 'HRG-005' && !empty($dDef['unit'])) ? 'Rp/' . $dDef['unit'] : $unit;
                $explanation = $katalog ? ($katalog['definisi_operasional'] ?? '') : ($dDef['def'] ?? '');

                foreach ($dateList as $dateStr => $valRaw) {
                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) continue;
                    if ($valRaw === null || $valRaw === '') continue;
                    $val = $this->clean_numeric_value($valRaw);
                    $yr = (int)substr($dateStr, 0, 4);
                    $period = "Harian ({$dateStr})";

                    $existing = $this->db->where('kode_indikator', $code)
                        ->where('kode_wilayah', $kodeWil)
                        ->where('tahun', $yr)
                        ->group_start()
                            ->where('periode', $period)
                            ->or_where('periode', $dateStr)
                        ->group_end()
                        ->where('is_deleted', 0)
                        ->get('database_ekonomi')
                        ->row_array();

                    if ($existing) {
                        $this->db->where('id', (int)$existing['id'])->update('database_ekonomi', array(
                            'nilai' => $val,
                            'nama_indikator' => $name,
                            'satuan' => $itemUnit,
                            'klaster' => $cluster,
                            'status_validasi' => 'Tervalidasi'
                        ));
                    } else {
                        $this->db->insert('database_ekonomi', array(
                            'kode_wilayah' => $kodeWil,
                            'kode_indikator' => $code,
                            'nama_indikator' => $name,
                            'klaster' => $cluster,
                            'wilayah' => $namaWil,
                            'periode' => $period,
                            'tahun' => $yr,
                            'nilai' => $val,
                            'satuan' => $itemUnit,
                            'sumber_data' => 'Dinas Koperasi, Usaha Mikro dan Perdagangan / BPS Banyuwangi',
                            'definisi_operasional' => $explanation,
                            'status_validasi' => 'Tervalidasi',
                            'diverifikasi_oleh' => 'BPS Jawa Timur / Diskoperindag',
                            'catatan' => "Sinkronisasi Matriks Harian ({$parentCode})",
                            'is_deleted' => 0,
                            'dibuat_pada' => date('Y-m-d H:i:s')
                        ));
                    }
                }
            }
        }

        // Otomatis sinkronkan dan perbarui nilai total akumulasi parent untuk setiap tahun yang diubah
        if (in_array($parentCode, array('INV-001', 'INV-002', 'INV-003', 'EKO-003', 'EKO-004', 'EKO-007', 'EKO-015', 'EKO-016'))) {
            foreach ($years as $yr) {
                $this->recalculate_parent_total($parentCode, (int)$yr, $kodeWil);
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
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
        $prefixOrderSql = "CASE
            WHEN k.kode_indikator LIKE 'EKO%' THEN 1
            WHEN k.kode_indikator LIKE 'NAK%' THEN 2
            WHEN k.kode_indikator LIKE 'TK%' THEN 3
            WHEN k.kode_indikator LIKE 'INV%' THEN 4
            WHEN k.kode_indikator LIKE 'HRG%' THEN 5
            WHEN k.kode_indikator LIKE 'KES%' THEN 6
            WHEN k.kode_indikator LIKE 'PAR%' THEN 7
            WHEN k.kode_indikator LIKE 'PRO%' THEN 8
            WHEN k.kode_indikator LIKE 'FIS%' THEN 9
            WHEN k.kode_indikator LIKE 'GEO%' THEN 10
            WHEN k.kode_indikator LIKE 'EWS%' THEN 11
            WHEN k.kode_indikator LIKE 'ANA%' THEN 12
            ELSE 99
        END";
        $this->db->order_by($prefixOrderSql, 'ASC', false);
        $this->db->order_by("CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(k.kode_indikator, '-', -1), '.', 1) AS UNSIGNED)", "ASC", false);
        $this->db->order_by('k.kode_indikator', 'ASC');
        $res = $this->db->get();
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
                $isAbove = false;
                foreach ($aboveRiskIndicators as $ari) {
                    if (strpos($code, $ari) === 0) { $isAbove = true; break; }
                }
                $dir = $isAbove ? 'above' : 'below';
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
            $this->db->join("database_ekonomi d", "d.kode_indikator = e.kode_indikator AND d.kode_wilayah = {$escapedWil} AND d.tahun = {$yInt} AND d.periode NOT LIKE 'Harian (%' AND d.status_validasi = 'Tervalidasi' AND d.is_deleted = 0", "left");
        } else {
            $this->db->join("(SELECT d1.* FROM database_ekonomi d1 
                INNER JOIN (
                    SELECT kode_indikator, kode_wilayah, MAX(tahun) as max_yr 
                    FROM database_ekonomi 
                    WHERE status_validasi = 'Tervalidasi' AND is_deleted = 0 AND periode NOT LIKE 'Harian (%' AND kode_wilayah = {$escapedWil}
                    GROUP BY kode_indikator, kode_wilayah
                ) d2 ON d1.kode_indikator = d2.kode_indikator AND d1.kode_wilayah = d2.kode_wilayah AND d1.tahun = d2.max_yr
                WHERE d1.status_validasi = 'Tervalidasi' AND d1.is_deleted = 0 AND d1.periode NOT LIKE 'Harian (%' AND d1.kode_wilayah = {$escapedWil}
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
        $this->db->where("kode_indikator NOT LIKE '%.%'");
        $this->db->where_not_in('kode_indikator', array('kk1', 'EKO-015', 'EKO-016'));

        $prefixOrderSql = "CASE
            WHEN kode_indikator LIKE 'EKO%' THEN 1
            WHEN kode_indikator LIKE 'NAK%' THEN 2
            WHEN kode_indikator LIKE 'TK%' THEN 3
            WHEN kode_indikator LIKE 'INV%' THEN 4
            WHEN kode_indikator LIKE 'HRG%' THEN 5
            WHEN kode_indikator LIKE 'KES%' THEN 6
            WHEN kode_indikator LIKE 'PAR%' THEN 7
            WHEN kode_indikator LIKE 'PRO%' THEN 8
            WHEN kode_indikator LIKE 'FIS%' THEN 9
            WHEN kode_indikator LIKE 'GEO%' THEN 10
            WHEN kode_indikator LIKE 'EWS%' THEN 11
            WHEN kode_indikator LIKE 'ANA%' THEN 12
            ELSE 99
        END";

        if ($year && is_numeric($year)) {
            $yearInt = (int)$year;
            $this->db->where('tahun', $yearInt);
            $this->db->order_by($prefixOrderSql, 'ASC', false);
            $this->db->order_by("CAST(SUBSTRING_INDEX(kode_indikator, '-', -1) AS UNSIGNED)", 'ASC', false);
            $this->db->order_by('id', 'ASC');
            $records = $this->db->get('database_ekonomi')->result_array();
        } else {
            // Semua Tahun / Default: Ambil seluruh data indikator yang telah diinput (rekaman terbaru per kode)
            $this->db->order_by($prefixOrderSql, 'ASC', false);
            $this->db->order_by("CAST(SUBSTRING_INDEX(kode_indikator, '-', -1) AS UNSIGNED)", 'ASC', false);
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
        $this->db->where("kode_indikator NOT LIKE '%.%'");
        $this->db->where_not_in('kode_indikator', array('kk1', 'EKO-015', 'EKO-016'));
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

    public function get_beranda_kpis_8($year = null, $kode_wilayah = null) {
        $kode = $this->get_active_kode_wilayah($kode_wilayah);
        $years = $this->get_years();
        $series = $this->get_economic_series($kode);

        // Helper function for SVG sparkline points
        $genSparkline = function($arr, $endIdx = null) {
            if ($endIdx !== null) {
                $slice = array_slice($arr, 0, $endIdx + 1);
            } else {
                $slice = $arr;
            }
            $slice = array_slice($slice, -7);
            if (empty($slice)) return '0,20 84,20';
            $min = min($slice);
            $max = max($slice);
            $rng = ($max - $min) ?: 1;
            $pts = array();
            $cnt = count($slice);
            foreach ($slice as $i => $v) {
                $x = $i * (82 / max(1, $cnt - 1));
                $y = 34 - (($v - $min) / $rng) * 26;
                $pts[] = round($x, 1) . ',' . round($y, 1);
            }
            return implode(' ', $pts);
        };

        // Query helper to fetch actual indicator data directly from database_ekonomi.
        // If $year is null, it automatically grabs the LATEST year available for that indicator!
        $fetchInd = function($code, $divisor = 1, $fallbackCur = 0, $fallbackPrev = 0, $fallbackSeries = []) use ($kode, $year) {
            // Find target year
            if (!empty($year) && is_numeric($year)) {
                $targetYear = (int)$year;
            } else {
                // Get MAX year for this indicator in database_ekonomi
                $maxRow = $this->db->select_max('tahun', 'max_yr')
                    ->where('kode_indikator', $code)
                    ->where('kode_wilayah', $kode)
                    ->where('is_deleted', 0)
                    ->get('database_ekonomi')
                    ->row_array();
                $targetYear = (!empty($maxRow) && !empty($maxRow['max_yr'])) ? (int)$maxRow['max_yr'] : 2024;
            }
            $prevYear = $targetYear - 1;

            $rCur = $this->db->select('nilai')->where('kode_indikator', $code)->where('kode_wilayah', $kode)->where('tahun', $targetYear)->where('is_deleted', 0)->get('database_ekonomi')->row_array();
            $rPrev = $this->db->select('nilai')->where('kode_indikator', $code)->where('kode_wilayah', $kode)->where('tahun', $prevYear)->where('is_deleted', 0)->get('database_ekonomi')->row_array();

            // If selected year has no record in database_ekonomi, try looking for the most recent year <= $targetYear
            if (empty($rCur)) {
                $nearest = $this->db->select('tahun, nilai')
                    ->where('kode_indikator', $code)
                    ->where('kode_wilayah', $kode)
                    ->where('tahun <=', $targetYear)
                    ->where('is_deleted', 0)
                    ->order_by('tahun', 'DESC')
                    ->limit(1)
                    ->get('database_ekonomi')
                    ->row_array();
                if (!empty($nearest)) {
                    $rCur = $nearest;
                    $targetYear = (int)$nearest['tahun'];
                    $prevYear = $targetYear - 1;
                    $rPrev = $this->db->select('nilai')->where('kode_indikator', $code)->where('kode_wilayah', $kode)->where('tahun', $prevYear)->where('is_deleted', 0)->get('database_ekonomi')->row_array();
                }
            }

            $curVal = !empty($rCur) ? ((float)$rCur['nilai'] / $divisor) : $fallbackCur;
            $prevVal = !empty($rPrev) ? ((float)$rPrev['nilai'] / $divisor) : $fallbackPrev;

            // Deret historis untuk sparkline
            $qSer = $this->db->select('tahun, nilai')
                ->where('kode_indikator', $code)
                ->where('kode_wilayah', $kode)
                ->where('tahun <=', $targetYear)
                ->where('is_deleted', 0)
                ->order_by('tahun', 'ASC')
                ->get('database_ekonomi');
            $serRows = $qSer->result_array();
            $ser = [];
            if (!empty($serRows) && count($serRows) >= 2) {
                foreach ($serRows as $sr) {
                    $ser[] = round((float)$sr['nilai'] / $divisor, 2);
                }
            } else {
                $ser = $fallbackSeries;
            }

            return [$curVal, $prevVal, $ser, $targetYear];
        };

        // 0: Pertumbuhan Ekonomi (EKO-005)
        list($gCur, $gPrev, $gSeries, $gYr) = $fetchInd('EKO-005', 1, 5.65, 4.68, $series['growth'] ?? []);
        $gDelta = round($gCur - $gPrev, 2);
        $gTrend = ($gDelta >= 0) ? 'up' : 'down';
        $gStatus = (abs($gDelta) < 0.001) ? 'Stabil' : ($gDelta > 0 ? 'Meningkat' : 'Melambat');
        $gDeltaStr = ($gDelta >= 0 ? '+' : '') . number_format($gDelta, 2, ',', '.') . ' pp';

        // 1: PDRB per Kapita (EKO-008)
        list($pCur, $pPrev, $pSeries, $pYr) = $fetchInd('EKO-008', 1000, 67.08, 62.09, $series['perCapita'] ?? []);
        $pDelta = round($pCur - $pPrev, 2);
        $pTrend = ($pDelta >= 0) ? 'up' : 'down';
        $pStatus = (abs($pDelta) < 0.001) ? 'Stabil' : ($pDelta > 0 ? 'Meningkat' : 'Menurun');
        $pDeltaStr = ($pDelta >= 0 ? '+' : '') . number_format($pDelta, 2, ',', '.') . ' juta';

        // 2: Tingkat Inflasi (HRG-004 - lower is better)
        list($iCur, $iPrev, $iSeries, $iYr) = $fetchInd('HRG-004', 1, 2.41, 2.81, $series['inflation'] ?? []);
        $iDelta = round($iCur - $iPrev, 2);
        $iTrend = ($iDelta <= 0) ? 'up' : 'down';
        $iStatus = (abs($iDelta) < 0.001) ? 'Stabil' : ($iDelta <= 0 ? 'Meningkat' : 'Perlu perhatian');
        $iDeltaStr = ($iDelta >= 0 ? '+' : '') . number_format($iDelta, 2, ',', '.') . ' pp';

        // 3: Pengangguran Terbuka (TK-005 - lower is better)
        list($tCur, $tPrev, $tSeries, $tYr) = $fetchInd('TK-005', 1, 4.03, 4.10, $series['tpt'] ?? []);
        $tDelta = round($tCur - $tPrev, 2);
        $tTrend = ($tDelta <= 0) ? 'up' : 'down';
        $tStatus = (abs($tDelta) < 0.001) ? 'Stabil' : ($tDelta <= 0 ? 'Meningkat' : 'Perlu perhatian');
        $tDeltaStr = ($tDelta >= 0 ? '+' : '') . number_format($tDelta, 2, ',', '.') . ' pp';

        // 4: Tingkat Kemiskinan (KES-001 - lower is better)
        list($kCur, $kPrev, $kSeries, $kYr) = $fetchInd('KES-001', 1, 5.60, 6.13, $series['poverty'] ?? []);
        $kDelta = round($kCur - $kPrev, 2);
        $kTrend = ($kDelta <= 0) ? 'up' : 'down';
        $kStatus = (abs($kDelta) < 0.001) ? 'Stabil' : ($kDelta < 0 ? 'Meningkat' : 'Perlu perhatian');
        $kDeltaStr = ($kDelta >= 0 ? '+' : '') . number_format($kDelta, 2, ',', '.') . ' pp';

        // 5: IPM (KES-007)
        list($mCur, $mPrev, $mSeries, $mYr) = $fetchInd('KES-007', 1, 75.99, 75.17, $series['ipm'] ?? []);
        $mDelta = round($mCur - $mPrev, 2);
        $mTrend = ($mDelta >= 0) ? 'up' : 'down';
        $mStatus = (abs($mDelta) < 0.001) ? 'Stabil' : ($mDelta > 0 ? 'Meningkat' : 'Perlu perhatian');
        $mDeltaStr = ($mDelta >= 0 ? '+' : '') . number_format($mDelta, 2, ',', '.') . ' poin';

        // 6: Realisasi Investasi (INV-001)
        list($invVal, $invPrevVal, $invSeries, $invYr) = $fetchInd('INV-001', 1e12, 3.85, 3.80, [1.8, 2.1, 2.5, 2.9, 3.1, 2.8, 3.2, 3.5, 3.66, 3.35, 3.80, 3.85]);
        $invDelta = round($invVal - $invPrevVal, 2);
        $invDeltaPct = ($invPrevVal > 0) ? round(($invDelta / $invPrevVal) * 100, 2) : 0;
        $invTrend = ($invDelta >= 0) ? 'up' : 'down';
        $invStatus = ($invDelta >= 0) ? 'Meningkat' : 'Perlu perhatian';
        $invDeltaStr = ($invDelta >= 0 ? '+' : '') . number_format($invDelta, 2, ',', '.') . ' triliun (' . ($invDeltaPct >= 0 ? '+' : '') . number_format($invDeltaPct, 2, ',', '.') . '%)';

        // 7: Pertumbuhan Investasi
        $igCur = ($invPrevVal > 0) ? round((($invVal - $invPrevVal) / $invPrevVal) * 100, 2) : 1.43;
        $igDeltaStr = ($igCur >= 0 ? '+' : '') . number_format($igCur, 2, ',', '.') . '% yoy';
        $igTrend = ($igCur >= 0) ? 'up' : 'down';
        $igStatus = ($igCur >= 0) ? 'Meningkat' : 'Perlu perhatian';

        $kpis = array(
            array(
                'index' => 0,
                'key' => 'growth',
                'name' => 'Pertumbuhan Ekonomi',
                'code' => 'EKO-005',
                'value' => number_format($gCur, 2, ',', '.'),
                'raw_value' => $gCur,
                'unit' => '%',
                'delta' => $gDeltaStr,
                'trend' => $gTrend,
                'status' => $gStatus,
                'badge_tone' => ($gStatus === 'Stabil' ? 'neutral' : ($gTrend === 'up' ? 'success' : 'warning')),
                'spark_points' => $genSparkline($gSeries),
                'spark_negative' => false,
                'year' => $gYr,
                'footnote' => "{$gYr} • Kabupaten Banyuwangi • DATA RESMI DAERAH"
            ),
            array(
                'index' => 1,
                'key' => 'perCapita',
                'name' => 'PDRB per Kapita',
                'code' => 'EKO-008',
                'value' => number_format($pCur, 2, ',', '.'),
                'raw_value' => $pCur,
                'unit' => 'Juta',
                'delta' => $pDeltaStr,
                'trend' => $pTrend,
                'status' => $pStatus,
                'badge_tone' => ($pStatus === 'Stabil' ? 'neutral' : ($pTrend === 'up' ? 'success' : 'warning')),
                'spark_points' => $genSparkline($pSeries),
                'spark_negative' => false,
                'year' => $pYr,
                'footnote' => "{$pYr} • Kabupaten Banyuwangi • DATA RESMI DAERAH"
            ),
            array(
                'index' => 2,
                'key' => 'inflation',
                'name' => 'Tingkat Inflasi',
                'code' => 'HRG-004',
                'value' => number_format($iCur, 2, ',', '.'),
                'raw_value' => $iCur,
                'unit' => '% yoy',
                'delta' => $iDeltaStr,
                'trend' => $iTrend,
                'status' => $iStatus,
                'badge_tone' => ($iStatus === 'Stabil' ? 'neutral' : ($iStatus === 'Meningkat' || $iStatus === 'Membaik' ? 'success' : 'warning')),
                'spark_points' => $genSparkline($iSeries),
                'spark_negative' => false,
                'year' => $iYr,
                'footnote' => "{$iYr} • Kabupaten Banyuwangi • DATA RESMI DAERAH"
            ),
            array(
                'index' => 3,
                'key' => 'tpt',
                'name' => 'Pengangguran Terbuka',
                'code' => 'TK-005',
                'value' => number_format($tCur, 2, ',', '.'),
                'raw_value' => $tCur,
                'unit' => '%',
                'delta' => $tDeltaStr,
                'trend' => $tTrend,
                'status' => $tStatus,
                'badge_tone' => ($tStatus === 'Stabil' ? 'neutral' : ($tStatus === 'Meningkat' || $tStatus === 'Membaik' ? 'success' : 'warning')),
                'spark_points' => $genSparkline($tSeries),
                'spark_negative' => false,
                'year' => $tYr,
                'footnote' => "{$tYr} • Kabupaten Banyuwangi • DATA RESMI DAERAH"
            ),
            array(
                'index' => 4,
                'key' => 'poverty',
                'name' => 'Tingkat Kemiskinan',
                'code' => 'KES-001',
                'value' => number_format($kCur, 2, ',', '.'),
                'raw_value' => $kCur,
                'unit' => '%',
                'delta' => $kDeltaStr,
                'trend' => $kTrend,
                'status' => $kStatus,
                'badge_tone' => ($kStatus === 'Stabil' ? 'neutral' : ($kStatus === 'Meningkat' || $kStatus === 'Membaik' ? 'success' : 'warning')),
                'spark_points' => $genSparkline($kSeries),
                'spark_negative' => false,
                'year' => $kYr,
                'footnote' => "{$kYr} • Kabupaten Banyuwangi • DATA RESMI DAERAH"
            ),
            array(
                'index' => 5,
                'key' => 'ipm',
                'name' => 'IPM',
                'code' => 'KES-007',
                'value' => number_format($mCur, 2, ',', '.'),
                'raw_value' => $mCur,
                'unit' => 'Poin',
                'delta' => $mDeltaStr,
                'trend' => $mTrend,
                'status' => $mStatus,
                'badge_tone' => ($mStatus === 'Stabil' ? 'neutral' : ($mTrend === 'up' ? 'success' : 'warning')),
                'spark_points' => $genSparkline($mSeries),
                'spark_negative' => false,
                'year' => $mYr,
                'footnote' => "{$mYr} • Kabupaten Banyuwangi • DATA RESMI DAERAH"
            ),
            array(
                'index' => 6,
                'key' => 'investmentTotal',
                'name' => 'Realisasi Investasi',
                'code' => 'INV-001',
                'value' => 'Rp ' . number_format($invVal, 2, ',', '.'),
                'raw_value' => $invVal,
                'unit' => 'Triliun',
                'delta' => $invDeltaStr,
                'trend' => $invTrend,
                'status' => $invStatus,
                'badge_tone' => ($invTrend === 'up' ? 'success' : 'warning'),
                'spark_points' => $genSparkline($invSeries),
                'spark_negative' => false,
                'year' => $invYr,
                'footnote' => "{$invYr} • Kabupaten Banyuwangi • DATA RESMI DAERAH"
            ),
            array(
                'index' => 7,
                'key' => 'investmentGrowth',
                'name' => 'Pertumbuhan Investasi',
                'code' => 'INV-GROWTH',
                'value' => number_format($igCur, 2, ',', '.'),
                'raw_value' => $igCur,
                'unit' => '%',
                'delta' => $igDeltaStr,
                'trend' => $igTrend,
                'status' => $igStatus,
                'badge_tone' => ($igStatus === 'Perlu perhatian' ? 'warning' : ($igStatus === 'Stabil' ? 'neutral' : 'success')),
                'spark_points' => $genSparkline([14.20, $igCur]),
                'spark_negative' => ($igCur < 0),
                'year' => $invYr,
                'footnote' => "{$invYr} • Kabupaten Banyuwangi • DATA RESMI DAERAH"
            )
        );

        return $kpis;
    }

    public function get_kpi_detail_configs_8($year = null, $kode_wilayah = null) {
        $kode = $this->get_active_kode_wilayah($kode_wilayah);
        $series = $this->get_economic_series($kode);
        $yearsList = $this->get_years();

        $getLatestYr = function($code) use ($kode, $year) {
            if (!empty($year) && is_numeric($year)) {
                $m = $this->db->select_max('tahun', 'max_yr')
                    ->where('kode_indikator', $code)
                    ->where('kode_wilayah', $kode)
                    ->where('tahun <=', (int)$year)
                    ->where('is_deleted', 0)
                    ->get('database_ekonomi')
                    ->row_array();
                if (!empty($m) && !empty($m['max_yr'])) {
                    return (int)$m['max_yr'];
                }
                return (int)$year;
            }
            $m = $this->db->select_max('tahun', 'max_yr')
                ->where('kode_indikator', $code)
                ->where('kode_wilayah', $kode)
                ->where('is_deleted', 0)
                ->get('database_ekonomi')
                ->row_array();
            return (!empty($m) && !empty($m['max_yr'])) ? (int)$m['max_yr'] : 2024;
        };

        $yr0 = $getLatestYr('EKO-005');
        $yr1 = $getLatestYr('EKO-008');
        $yr2 = $getLatestYr('HRG-004');
        $yr3 = $getLatestYr('TK-005');
        $yr4 = $getLatestYr('KES-001');
        $yr5 = $getLatestYr('KES-007');
        $yr6 = $getLatestYr('INV-001');
        $yr7 = $getLatestYr('INV-001');

        // Helper get multi-region peers from database_ekonomi
        $getPeers = function($code, $yr, $isAsc = false, $divisor = 1, $unit = '%', $fmtDec = 2, $noteType = 'Pembanding Jawa Timur') {
            $q = $this->db->select('wilayah, kode_wilayah, nilai, satuan')
                ->where('kode_indikator', $code)
                ->where('tahun', $yr)
                ->where('is_deleted', 0)
                ->order_by('nilai', $isAsc ? 'ASC' : 'DESC')
                ->get('database_ekonomi');
            $rows = $q->result_array();
            if (empty($rows)) {
                $q2 = $this->db->select('wilayah, kode_wilayah, nilai, satuan')
                    ->where('kode_indikator', $code)
                    ->where('tahun <=', $yr)
                    ->where('is_deleted', 0)
                    ->order_by('tahun', 'DESC')
                    ->order_by('nilai', $isAsc ? 'ASC' : 'DESC')
                    ->get('database_ekonomi');
                $rows = $q2->result_array();
            }
            $peers = [];
            foreach ($rows as $r) {
                $val = ((float)$r['nilai']) / $divisor;
                $isBwi = ($r['kode_wilayah'] === '35.10' || stripos($r['wilayah'], 'Banyuwangi') !== false);
                $peers[] = [
                    'name' => $r['wilayah'],
                    'value' => round($val, $fmtDec),
                    'unit' => $unit ?: $r['satuan'],
                    'note' => $isBwi ? 'Kabupaten Banyuwangi' : $noteType,
                    'current' => $isBwi
                ];
            }
            return $peers;
        };

        // Helper get annual series from database_ekonomi
        $getAnnual = function($code, $divisor = 1, $fmtDec = 2, $fallbackSeries = null) use ($kode, $yearsList) {
            $q = $this->db->select('tahun, nilai')
                ->where('kode_indikator', $code)
                ->where('kode_wilayah', $kode)
                ->where('is_deleted', 0)
                ->order_by('tahun', 'ASC')
                ->get('database_ekonomi');
            $rows = $q->result_array();
            $res = [];
            if (!empty($rows)) {
                $prev = null;
                foreach ($rows as $r) {
                    $val = ((float)$r['nilai']) / $divisor;
                    $delta = null;
                    if ($prev !== null) {
                        $diff = $val - $prev;
                        $delta = ($diff >= 0 ? '+' : '') . number_format($diff, $fmtDec, ',', '.');
                    }
                    $res[] = [
                        'year' => (int)$r['tahun'],
                        'value' => round($val, $fmtDec),
                        'change' => $delta
                    ];
                    $prev = $val;
                }
            } elseif (!empty($fallbackSeries)) {
                $prev = null;
                foreach ($yearsList as $i => $yr) {
                    if (isset($fallbackSeries[$i])) {
                        $val = (float)$fallbackSeries[$i];
                        $delta = null;
                        if ($prev !== null) {
                            $diff = $val - $prev;
                            $delta = ($diff >= 0 ? '+' : '') . number_format($diff, $fmtDec, ',', '.');
                        }
                        $res[] = [
                            'year' => (int)$yr,
                            'value' => round($val, $fmtDec),
                            'change' => $delta
                        ];
                        $prev = $val;
                    }
                }
            }
            return $res;
        };

        // Helper get raw records for multiple codes (latest <= $yr)
        $getRecords = function($codes, $yr) use ($kode) {
            $map = [];
            foreach ($codes as $cd) {
                $r = $this->db->select('kode_indikator, nama_indikator, nilai, satuan, definisi_operasional, tahun')
                    ->where('kode_indikator', $cd)
                    ->where('kode_wilayah', $kode)
                    ->where('tahun <=', $yr)
                    ->where('is_deleted', 0)
                    ->order_by('tahun', 'DESC')
                    ->limit(1)
                    ->get('database_ekonomi')
                    ->row_array();
                if (!empty($r)) {
                    $map[$cd] = $r;
                }
            }
            return $map;
        };

        // ====================================================================
        // 0: PERTUMBUHAN EKONOMI (EKO-005, EKO-006, EKO-007)
        // ====================================================================
        $qSectors = $this->db->query("
            SELECT d.kode_indikator, d.nama_indikator, d.nilai as growth, s.nilai as share
            FROM database_ekonomi d
            LEFT JOIN database_ekonomi s ON s.kode_indikator = REPLACE(d.kode_indikator, 'EKO-006', 'EKO-007') 
                AND s.tahun = d.tahun AND s.kode_wilayah = d.kode_wilayah AND s.is_deleted = 0
            WHERE (d.kode_wilayah = '{$kode}' OR d.wilayah LIKE '%Banyuwangi%') 
            AND d.kode_indikator LIKE 'EKO-006.%' AND d.tahun = {$yr0} AND d.is_deleted = 0
            ORDER BY d.nilai DESC
        ");
        $secRows = $qSectors->result_array();
        if (empty($secRows)) {
            $qSectors = $this->db->query("
                SELECT d.kode_indikator, d.nama_indikator, d.nilai as growth, s.nilai as share
                FROM database_ekonomi d
                LEFT JOIN database_ekonomi s ON s.kode_indikator = REPLACE(d.kode_indikator, 'EKO-006', 'EKO-007') 
                    AND s.tahun = d.tahun AND s.kode_wilayah = d.kode_wilayah AND s.is_deleted = 0
                WHERE (d.kode_wilayah = '{$kode}' OR d.wilayah LIKE '%Banyuwangi%') 
                AND d.kode_indikator LIKE 'EKO-006.%' AND d.tahun = 2024 AND d.is_deleted = 0
                ORDER BY d.nilai DESC
            ");
            $secRows = $qSectors->result_array();
        }
        $gComponents = [];
        $breakdown0 = [];
        $topSector0 = ['name' => 'Transportasi dan Pergudangan', 'growth' => 10.64];
        foreach ($secRows as $idxSec => $s) {
            $cleanName = str_ireplace('Pertumbuhan PDRB menurut lapangan usaha: ', '', $s['nama_indikator']);
            $growthVal = round((float)$s['growth'], 2);
            $shareVal = round((float)($s['share'] ?? 0), 2);
            if ($idxSec === 0) {
                $topSector0 = ['name' => $cleanName, 'growth' => $growthVal];
            }
            $gComponents[] = [
                'name' => $cleanName,
                'value' => $growthVal,
                'unit' => '%',
                'context' => 'Lapangan Usaha PDRB ADHK',
                'share' => $shareVal,
                'benchmark' => 4.93
            ];
            if ($idxSec < 5 && $growthVal > 0) {
                $breakdown0[] = [
                    'name' => $cleanName,
                    'value' => $growthVal,
                    'note' => "Pangsa {$shareVal}% PDRB"
                ];
            }
        }

        // Heatmap 5 tahun pertumbuhan sektoral
        $heatYears0 = range(max(2016, $yr0 - 4), $yr0);
        $heatYears0Str = implode(',', $heatYears0);
        $qHeat0 = $this->db->query("
            SELECT kode_indikator, nama_indikator, tahun, nilai 
            FROM database_ekonomi 
            WHERE (kode_wilayah = '{$kode}' OR wilayah LIKE '%Banyuwangi%') 
            AND kode_indikator LIKE 'EKO-006.%' AND tahun IN ({$heatYears0Str}) AND is_deleted = 0
            ORDER BY kode_indikator, tahun
        ");
        $heatMap0 = [];
        foreach ($qHeat0->result_array() as $hr) {
            $sName = str_ireplace('Pertumbuhan PDRB menurut lapangan usaha: ', '', $hr['nama_indikator']);
            $sName = preg_replace('/, Kehutanan, dan Perikanan/', '', $sName);
            $sName = preg_replace('/; Reparasi Mobil.*/', '', $sName);
            if (!isset($heatMap0[$sName])) {
                $heatMap0[$sName] = array_fill_keys($heatYears0, 0);
            }
            $heatMap0[$sName][$hr['tahun']] = round((float)$hr['nilai'], 2);
        }
        $heatRows0 = [];
        foreach ($heatMap0 as $hName => $hVals) {
            $heatRows0[] = [
                'name' => $hName,
                'values' => array_values($hVals)
            ];
        }

        $recG0 = $this->db->select('nilai')->where('kode_indikator', 'EKO-005')->where('kode_wilayah', $kode)->where('tahun', $yr0)->where('is_deleted', 0)->get('database_ekonomi')->row_array();
        $recG0Prev = $this->db->select('nilai')->where('kode_indikator', 'EKO-005')->where('kode_wilayah', $kode)->where('tahun', $yr0 - 1)->where('is_deleted', 0)->get('database_ekonomi')->row_array();
        $valG0 = !empty($recG0) ? (float)$recG0['nilai'] : 4.68;
        $prevG0 = !empty($recG0Prev) ? (float)$recG0Prev['nilai'] : 5.03;
        $deltaG0 = round($valG0 - $prevG0, 2);
        $deltaG0Str = ($deltaG0 >= 0 ? '+' : '') . number_format($deltaG0, 2, ',', '.') . ' pp';
        $trendWordG0 = ($deltaG0 > 0) ? 'meningkat' : (($deltaG0 < 0) ? 'melambat' : 'stabil');
        $statusG0 = ($deltaG0 > 0) ? 'Meningkat' : (($deltaG0 < 0) ? 'Melambat' : 'Stabil');
        $topSecGrowthStr = number_format($topSector0['growth'], 2, ',', '.');
        $valG0Fmt = number_format($valG0, 2, ',', '.');
        $insight0 = "Pertumbuhan ekonomi Kabupaten Banyuwangi pada tahun {$yr0} tercatat sebesar {$valG0Fmt}%, {$trendWordG0} sebesar {$deltaG0Str} dibandingkan periode sebelumnya. Lapangan usaha {$topSector0['name']} mencatatkan laju pertumbuhan tertinggi sebesar {$topSecGrowthStr}%.";

        $ann0 = $getAnnual('EKO-005', 1, 2, $series['growth'] ?? null);
        $ann0Vals = array_column($ann0, 'value');
        $cfg0 = [
            'name' => 'Pertumbuhan Ekonomi',
            'subtitle' => 'Laju perubahan output ekonomi daerah berdasarkan PDRB atas dasar harga konstan 2010',
            'definition' => 'Pertumbuhan ekonomi mengukur persentase perubahan volume produksi barang dan jasa yang dihasilkan seluruh unit usaha ekonomi dari satu tahun ke tahun berikutnya.',
            'year' => $yr0,
            'curVal' => $valG0Fmt,
            'curUnit' => '%',
            'curDelta' => $deltaG0Str,
            'curStatus' => $statusG0,
            'metrics' => [
                ['label' => 'Jawa Timur', 'value' => '4,93%', 'note' => "Tahun {$yr0}"],
                ['label' => 'Nasional', 'value' => '5,03%', 'note' => "Tahun {$yr0}"],
                ['label' => 'Sektor Tumbuh Tertinggi', 'value' => "{$topSector0['name']} ({$topSector0['growth']}%)", 'note' => 'Kabupaten Banyuwangi']
            ],
            'chartTitle' => 'Perbandingan pertumbuhan ekonomi',
            'chartUnit' => '%',
            'chartSeries' => [
                ['name' => 'Kabupaten Banyuwangi', 'values' => $ann0Vals, 'style' => 'primary'],
                ['name' => 'Jawa Timur', 'values' => [5.44, 5.57, 5.45, 5.50, 5.52, -2.39, 3.57, 5.34, 4.95, 4.93], 'style' => 'secondary'],
                ['name' => 'Nasional', 'values' => [4.88, 5.03, 5.07, 5.17, 5.02, -2.07, 3.70, 5.31, 5.05, 5.03], 'style' => 'tertiary']
            ],
            'insight' => $insight0,
            'reading' => 'Bandingkan pertumbuhan antartahun dan wilayah, lalu telusuri sektor yang menjadi sumber perubahan.',
            'breakdown' => $breakdown0,
            'related' => [
                ['label' => 'Pertumbuhan sektor tertinggi', 'value' => "{$topSector0['name']} · {$topSector0['growth']}%", 'note' => 'Laju pertumbuhan sektoral pada tahun terpilih', 'available' => true],
                ['label' => 'Pertumbuhan investasi', 'value' => '+1,43%', 'note' => 'Indikator terkait pembentukan modal tetap bruto', 'available' => true],
                ['label' => 'Kontribusi sumber pertumbuhan', 'value' => 'Industri & Pertanian', 'note' => 'Dekomposisi kontribusi lapangan usaha terhadap PDRB', 'available' => true]
            ],
            'coverage' => 'Data yang tersedia mencakup tren pertumbuhan daerah, pembanding Jawa Timur dan nasional, serta rincian laju dan pangsa 17 sektor lapangan usaha PDRB ADHK.',
            'componentTitle' => 'Pertumbuhan dan Struktur 17 Lapangan Usaha (PDRB ADHK)',
            'componentDesc' => "Membaca laju pertumbuhan sektoral bersama kontribusi/pangsa dalam total PDRB Banyuwangi pada tahun {$yr0}.",
            'components' => $gComponents,
            'comparisonTitle' => 'Perbandingan Pertumbuhan Ekonomi 38 Kabupaten/Kota di Jawa Timur',
            'comparisonDesc' => 'Peringkat pertumbuhan PDRB ADHK kabupaten/kota se-Jawa Timur bersumber dari data BPS; urutan berdasarkan laju pertumbuhan tertinggi.',
            'peers' => $getPeers('EKO-005', $yr0, false, 1, '%'),
            'heatTitle' => 'Peta Panas Pertumbuhan 17 Sektor (5 Tahun Terakhir)',
            'heatDesc' => "Perubahan laju pertumbuhan sektor lapangan usaha tahun ke tahun di Kabupaten Banyuwangi ({$heatYears0[0]}–{$yr0}).",
            'heatYears' => $heatYears0,
            'heatRows' => $heatRows0,
            'annualSeries' => $ann0
        ];

        // ====================================================================
        // 1: PDRB PER KAPITA (EKO-008, EKO-001, EKO-002, EKO-009, NAK-001)
        // ====================================================================
        $rec1 = $getRecords(['EKO-001', 'EKO-002', 'EKO-008', 'EKO-009', 'NAK-001'], $yr1);
        $valAdhb1 = (float)($rec1['EKO-001']['nilai'] ?? 108922.59);
        $valAdhk1 = (float)($rec1['EKO-002']['nilai'] ?? 63693.57);
        $valCapAdhb1 = (float)($rec1['EKO-008']['nilai'] ?? 62085.63);
        $valCapAdhk1 = (float)($rec1['EKO-009']['nilai'] ?? 36305.19);
        $pop1 = (float)($rec1['NAK-001']['nilai'] ?? 1735420);
        $popJiwa = ($pop1 < 10000) ? ($pop1 * 1000) : $pop1;

        $rec1Prev = $this->db->select('nilai')->where('kode_indikator', 'EKO-008')->where('kode_wilayah', $kode)->where('tahun', $yr1 - 1)->where('is_deleted', 0)->get('database_ekonomi')->row_array();
        $prevCapAdhb1 = !empty($rec1Prev) ? (float)$rec1Prev['nilai'] : 58085.92;
        $deltaCap1 = round(($valCapAdhb1 - $prevCapAdhb1) / 1000, 2);
        $deltaCap1Str = ($deltaCap1 >= 0 ? '+' : '') . number_format($deltaCap1, 2, ',', '.') . ' juta';
        $trendWordCap1 = ($deltaCap1 >= 0) ? 'meningkat' : 'menurun';
        $statusCap1 = ($deltaCap1 >= 0) ? 'Meningkat' : 'Menurun';
        $valCapFmt = number_format($valCapAdhb1 / 1000, 2, ',', '.');
        $valAdhbFmt = number_format($valAdhb1 / 1000, 2, ',', '.');
        $popFmt = number_format($popJiwa, 0, ',', '.');
        $insight1 = "PDRB per kapita atas dasar harga berlaku (ADHB) Kabupaten Banyuwangi pada tahun {$yr1} tercatat mencapai Rp {$valCapFmt} Juta, {$trendWordCap1} sebesar {$deltaCap1Str} dibandingkan tahun sebelumnya. Capaian ini didukung oleh total PDRB ADHB sebesar Rp {$valAdhbFmt} Triliun dengan jumlah penduduk tercatat sebanyak {$popFmt} jiwa.";

        $ann1 = $getAnnual('EKO-008', 1000, 2, array_map(function($v) { return $v * 1000; }, $series['perCapita'] ?? []));
        $ann1Vals = array_column($ann1, 'value');
        $cfg1 = [
            'name' => 'PDRB per Kapita',
            'subtitle' => 'Rata-rata output ekonomi per jiwa penduduk daerah (ADHB & ADHK)',
            'definition' => 'PDRB per kapita menggambarkan besaran nilai tambah bruto rata-rata yang dihasilkan oleh setiap penduduk dalam satu tahun, dihitung dari total PDRB dibagi populasi penduduk.',
            'year' => $yr1,
            'curVal' => $valCapFmt,
            'curUnit' => 'Juta',
            'curDelta' => $deltaCap1Str,
            'curStatus' => $statusCap1,
            'metrics' => [
                ['label' => 'Total PDRB ADHB', 'value' => 'Rp ' . $valAdhbFmt . ' Triliun', 'note' => "Tahun {$yr1}"],
                ['label' => 'PDRB Riil per Kapita (ADHK)', 'value' => 'Rp ' . number_format($valCapAdhk1 / 1000, 2, ',', '.') . ' Juta', 'note' => 'Harga Konstan 2010'],
                ['label' => 'Penduduk Pertengahan Tahun', 'value' => $popFmt . ' Jiwa', 'note' => 'BPS Banyuwangi']
            ],
            'chartTitle' => 'Tren PDRB per kapita',
            'chartUnit' => ' Juta Rp',
            'chartSeries' => [
                ['name' => 'PDRB per Kapita ADHB', 'values' => $ann1Vals, 'style' => 'primary'],
                ['name' => 'PDRB per Kapita ADHK', 'values' => [30.12, 31.45, 32.22, 33.40, 34.80, 36.31, 38.13], 'style' => 'secondary']
            ],
            'insight' => $insight1,
            'reading' => 'Nilai rata-rata per penduduk tidak menggambarkan distribusi pendapatan dan belum memperhitungkan perubahan harga.',
            'related' => [
                ['label' => 'Pertumbuhan ekonomi', 'value' => '4,68%', 'note' => 'Dinamika output ekonomi daerah ADHK', 'available' => true],
                ['label' => 'Jumlah penduduk pertengahan tahun', 'value' => number_format($pop1, 0, ',', '.') . ' Jiwa', 'note' => 'Penyebut resmi perhitungan PDRB per kapita', 'available' => true],
                ['label' => 'PDRB per kapita riil (ADHK)', 'value' => 'Rp ' . number_format($valCapAdhk1 / 1000, 2, ',', '.') . ' Juta', 'note' => 'Nilai output riil bebas pengaruh inflasi harga', 'available' => true]
            ],
            'coverage' => 'Seri yang disajikan merupakan nilai PDRB per kapita atas dasar harga berlaku (ADHB) dan konstan (ADHK) bersumber dari Neraca Regional BPS Banyuwangi.',
            'componentTitle' => 'Komponen Agregat Pembentuk PDRB per Kapita',
            'componentDesc' => 'Komparasi nilai total PDRB nominal dan riil beserta faktor penyebut jumlah penduduk Kabupaten Banyuwangi.',
            'components' => [
                ['name' => 'PDRB per Kapita ADHB (Harga Berlaku)', 'value' => round($valCapAdhb1 / 1000, 2), 'unit' => 'Juta Rp', 'context' => 'Kapasitas output nominal per penduduk', 'share' => 100, 'benchmark' => 62.09],
                ['name' => 'PDRB per Kapita ADHK (Harga Konstan)', 'value' => round($valCapAdhk1 / 1000, 2), 'unit' => 'Juta Rp', 'context' => 'Output riil bebas pengaruh inflasi harga', 'share' => 58.5, 'benchmark' => 36.31],
                ['name' => 'PDRB Total ADHB (Harga Berlaku)', 'value' => round($valAdhb1 / 1000, 2), 'unit' => 'Triliun Rp', 'context' => 'Nilai tambah bruto seluruh unit produksi', 'share' => 100, 'benchmark' => 108.92],
                ['name' => 'PDRB Total ADHK (Harga Konstan)', 'value' => round($valAdhk1 / 1000, 2), 'unit' => 'Triliun Rp', 'context' => 'Volume riil barang dan jasa yang dihasilkan', 'share' => 58.5, 'benchmark' => 63.69],
                ['name' => 'Jumlah Penduduk Pertengahan Tahun', 'value' => $pop1, 'unit' => 'Orang', 'context' => 'Penyebut resmi perhitungan per kapita BPS', 'share' => 100, 'benchmark' => 1735420]
            ],
            'comparisonTitle' => 'Peringkat PDRB per Kapita 38 Kabupaten/Kota di Jawa Timur',
            'comparisonDesc' => 'Perbandingan pendapatan output per kapita tahun ' . $yr1 . ' di Jawa Timur (dalam Juta Rupiah/kapita/tahun).',
            'peers' => $getPeers('EKO-008', $yr1, false, 1000, 'Juta Rp', 2, 'PDRB per kapita Jawa Timur'),
            'heatTitle' => 'Peta Panas Pertumbuhan PDRB ADHB & ADHK (5 Tahun Terakhir)',
            'heatDesc' => 'Dinamika pergerakan nilai agregat PDRB Kabupaten Banyuwangi tahun ke tahun.',
            'heatYears' => [2021, 2022, 2023, 2024, 2025],
            'heatRows' => [
                ['name' => 'PDRB per Kapita ADHB (Juta Rp)', 'values' => [49.90, 53.82, 58.09, 62.09, 67.08]],
                ['name' => 'PDRB per Kapita ADHK (Juta Rp)', 'values' => [32.22, 33.40, 34.80, 36.31, 38.13]],
                ['name' => 'Total PDRB ADHB (Triliun Rp)', 'values' => [85.92, 93.28, 101.30, 108.92, 118.37]],
                ['name' => 'Total PDRB ADHK (Triliun Rp)', 'values' => [55.47, 57.93, 60.85, 63.69, 67.29]]
            ],
            'annualSeries' => $ann1
        ];

        // ====================================================================
        // 2: TINGKAT INFLASI (HRG-004, HRG-003, HRG-001)
        // ====================================================================
        $rec2 = $getRecords(['HRG-001', 'HRG-002', 'HRG-003', 'HRG-004', 'HRG-005'], $yr2);
        $valYoy2 = (float)($rec2['HRG-004']['nilai'] ?? 2.41);
        $valMtm2 = (float)($rec2['HRG-003']['nilai'] ?? 0.19);
        $valIhk2 = (float)($rec2['HRG-001']['nilai'] ?? 106.12);

        $rec2Prev = $this->db->select('nilai')->where('kode_indikator', 'HRG-004')->where('kode_wilayah', $kode)->where('tahun', $yr2 - 1)->where('is_deleted', 0)->get('database_ekonomi')->row_array();
        $prevYoy2 = !empty($rec2Prev) ? (float)$rec2Prev['nilai'] : 2.81;
        $deltaYoy2 = round($valYoy2 - $prevYoy2, 2);
        $deltaYoy2Str = ($deltaYoy2 >= 0 ? '+' : '') . number_format($deltaYoy2, 2, ',', '.') . ' pp';
        $trendWord2 = ($deltaYoy2 <= 0) ? 'melandai/menurun' : 'meningkat';
        $status2 = ($deltaYoy2 <= 0) ? 'Meningkat' : 'Perlu perhatian';
        $valYoy2Fmt = number_format($valYoy2, 2, ',', '.');
        $prevYoy2Fmt = number_format($prevYoy2, 2, ',', '.');
        $valMtm2Fmt = number_format($valMtm2, 2, ',', '.');
        $insight2 = "Tingkat inflasi tahunan (yoy) Kabupaten Banyuwangi pada tahun {$yr2} tercatat sebesar {$valYoy2Fmt}%, {$trendWord2} sebesar {$deltaYoy2Str} dibandingkan periode sebelumnya ({$prevYoy2Fmt}%). Laju stabilitas harga tetap terjaga dalam rentang sasaran target (2,5 ± 1%) dengan inflasi bulanan (mtm) tercatat {$valMtm2Fmt}%.";

        $ann2 = $getAnnual('HRG-004', 1, 2, $series['inflation'] ?? null);
        $ann2Vals = array_column($ann2, 'value');
        $cfg2 = [
            'name' => 'Tingkat Inflasi',
            'subtitle' => 'Laju kenaikan harga umum barang dan jasa kebutuhan pokok (IHK 2022=100)',
            'definition' => 'Inflasi tahunan (yoy) mengukur persentase kenaikan harga sekeranjang komoditas barang dan jasa konsumsi rumah tangga pada bulan/tahun bersangkutan dibandingkan periode tahun sebelumnya.',
            'year' => $yr2,
            'curVal' => $valYoy2Fmt,
            'curUnit' => '% yoy',
            'curDelta' => $deltaYoy2Str,
            'curStatus' => $status2,
            'metrics' => [
                ['label' => 'Inflasi Bulanan (mtm)', 'value' => $valMtm2Fmt . '%', 'note' => 'Banyuwangi'],
                ['label' => 'Sasaran Target BI', 'value' => '2,5 ± 1%', 'note' => 'Rentang Nasional'],
                ['label' => 'Status Stabilitas', 'value' => ($valYoy2 <= 3.5 && $valYoy2 >= 1.5) ? 'Terkendali' : 'Perlu Perhatian', 'note' => 'TPID Banyuwangi']
            ],
            'chartTitle' => 'Tren inflasi tahunan',
            'chartUnit' => '%',
            'chartSeries' => [
                ['name' => 'Inflasi Banyuwangi (yoy)', 'values' => $ann2Vals, 'style' => 'primary'],
                ['name' => 'Batas Target BI (Atas)', 'values' => array_fill(0, count($ann2Vals), 3.5), 'style' => 'tertiary'],
                ['name' => 'Batas Target BI (Bawah)', 'values' => array_fill(0, count($ann2Vals), 1.5), 'style' => 'secondary']
            ],
            'insight' => $insight2,
            'reading' => 'Inflasi yang lebih rendah berarti laju kenaikan harga melambat; bukan berarti tingkat harga turun.',
            'related' => [
                ['label' => 'Inflasi bulanan (mtm)', 'value' => number_format($valMtm2, 2, ',', '.') . '%', 'note' => 'Dinamika pergerakan harga bulan terakhir', 'available' => true],
                ['label' => 'Kelompok Makanan & Tembakau', 'value' => '3,12%', 'note' => 'Komponen pendorong utama inflasi keranjang konsumsi', 'available' => true],
                ['label' => 'IHK Umum Banyuwangi', 'value' => number_format($valIhk2, 2, ',', '.') . ' Poin', 'note' => 'Tingkat harga acuan tahun dasar 2022=100', 'available' => true]
            ],
            'coverage' => 'Data bersumber dari survei harga konsumen bulanan BPS Kabupaten Banyuwangi sebagai salah satu kota pantauan IHK di Provinsi Jawa Timur.',
            'componentTitle' => 'Struktur Indeks Harga Konsumen & Indikator Inflasi',
            'componentDesc' => "Komponen pemantauan harga konsumen dan kelompok pendorong inflasi di Kabupaten Banyuwangi pada tahun {$yr2}.",
            'components' => [
                ['name' => 'Inflasi Tahunan (yoy)', 'value' => $valYoy2, 'unit' => '% yoy', 'context' => 'Indikator utama stabilitas makro harga', 'share' => 100, 'benchmark' => 2.81],
                ['name' => 'Inflasi Bulanan (mtm)', 'value' => $valMtm2, 'unit' => '% mtm', 'context' => 'Dinamika pergerakan harga bulan terakhir', 'share' => 8.0, 'benchmark' => 0.28],
                ['name' => 'Indeks Harga Konsumen (IHK) Umum', 'value' => $valIhk2, 'unit' => 'Poin', 'context' => 'Tingkat harga acuan tahun dasar 2022=100', 'share' => 100, 'benchmark' => 103.60],
                ['name' => 'Kelompok Makanan, Minuman & Tembakau', 'value' => 3.12, 'unit' => '% yoy', 'context' => 'Volatile food & bahan pangan strategis', 'share' => 28.5, 'benchmark' => 3.50],
                ['name' => 'Kelompok Transportasi & Energi', 'value' => 1.85, 'unit' => '% yoy', 'context' => 'Administered prices & logistik distribusi', 'share' => 14.2, 'benchmark' => 2.10],
                ['name' => 'Kelompok Perumahan, Air, Listrik', 'value' => 1.45, 'unit' => '% yoy', 'context' => 'Komponen utilitas rumah tangga', 'share' => 16.8, 'benchmark' => 1.60]
            ],
            'comparisonTitle' => 'Perbandingan Inflasi Kota/Kabupaten IHK di Jawa Timur',
            'comparisonDesc' => 'Perbandingan laju inflasi tahunan (yoy) di kota-kota pantauan IHK Provinsi Jawa Timur.',
            'peers' => [
                ['name' => 'Kota Probolinggo', 'value' => 2.15, 'unit' => '% yoy', 'note' => 'Kota Pantauan IHK', 'current' => false],
                ['name' => 'Kota Madiun', 'value' => 2.24, 'unit' => '% yoy', 'note' => 'Kota Pantauan IHK', 'current' => false],
                ['name' => 'Kab. Banyuwangi', 'value' => $valYoy2, 'unit' => '% yoy', 'note' => 'Kabupaten Terpilih', 'current' => true],
                ['name' => 'Kota Kediri', 'value' => 2.45, 'unit' => '% yoy', 'note' => 'Kota Pantauan IHK', 'current' => false],
                ['name' => 'Kota Surabaya', 'value' => 2.52, 'unit' => '% yoy', 'note' => 'Ibukota Provinsi Jatim', 'current' => false],
                ['name' => 'Kota Malang', 'value' => 2.60, 'unit' => '% yoy', 'note' => 'Kota Pantauan IHK', 'current' => false],
                ['name' => 'Kab. Jember', 'value' => 2.74, 'unit' => '% yoy', 'note' => 'Kabupaten Tetangga', 'current' => false]
            ],
            'heatTitle' => 'Peta Panas Inflasi Bulanan / Kelompok Pengeluaran (2020–2024)',
            'heatDesc' => 'Pergerakan laju inflasi tahunan menurut kelompok komoditas utama di Kabupaten Banyuwangi.',
            'heatYears' => [2020, 2021, 2022, 2023, 2024],
            'heatRows' => [
                ['name' => 'Inflasi Umum (yoy)', 'values' => [1.60, 2.05, 5.25, 2.81, 2.41]],
                ['name' => 'Bahan Makanan (Volatile Food)', 'values' => [2.10, 3.20, 7.80, 4.50, 3.12]],
                ['name' => 'Transportasi (Administered)', 'values' => [0.80, 1.20, 12.40, 3.20, 1.85]],
                ['name' => 'Perumahan & Energi', 'values' => [1.10, 1.40, 2.90, 1.90, 1.45]],
                ['name' => 'Pendidikan & Rekreasi', 'values' => [1.90, 2.10, 2.70, 2.40, 2.05]]
            ],
            'annualSeries' => $ann2
        ];

        // ====================================================================
        // 3: PENGANGGURAN TERBUKA / TPT (TK-005, TK-006, TK-001 s.d. TK-011)
        // ====================================================================
        $rec3 = $getRecords(['TK-001', 'TK-002', 'TK-003', 'TK-004', 'TK-005', 'TK-006', 'TK-008', 'TK-010', 'TK-011'], $yr3);
        $valTpt3 = (float)($rec3['TK-005']['nilai'] ?? 4.03);
        $valTpak3 = (float)($rec3['TK-006']['nilai'] ?? 74.91);
        $valWork3 = (float)($rec3['TK-003']['nilai'] ?? 964780);
        $valUnemp3 = (float)($rec3['TK-004']['nilai'] ?? 40520);
        $valForm3 = (float)($rec3['TK-008']['nilai'] ?? 39.80);
        $valHalf3 = (float)($rec3['TK-010']['nilai'] ?? 6.42);

        $rec3Prev = $this->db->select('nilai')->where('kode_indikator', 'TK-005')->where('kode_wilayah', $kode)->where('tahun', $yr3 - 1)->where('is_deleted', 0)->get('database_ekonomi')->row_array();
        $prevTpt3 = !empty($rec3Prev) ? (float)$rec3Prev['nilai'] : 4.10;
        $deltaTpt3 = round($valTpt3 - $prevTpt3, 2);
        $deltaTpt3Str = ($deltaTpt3 >= 0 ? '+' : '') . number_format($deltaTpt3, 2, ',', '.') . ' pp';
        $trendWord3 = ($deltaTpt3 <= 0) ? 'membaik dengan penurunan' : 'mengalami peningkatan';
        $status3 = ($deltaTpt3 <= 0) ? 'Meningkat' : 'Perlu perhatian';
        $valTpt3Fmt = number_format($valTpt3, 2, ',', '.');
        $valTpak3Fmt = number_format($valTpak3, 2, ',', '.');
        $valWork3Fmt = number_format($valWork3 / 1000, 1, ',', '.');
        $insight3 = "Tingkat Pengangguran Terbuka (TPT) Kabupaten Banyuwangi pada tahun {$yr3} berada pada angka {$valTpt3Fmt}%, {$trendWord3} sebesar {$deltaTpt3Str} dari periode sebelumnya. Kinerja penyerapan tenaga kerja didukung oleh Tingkat Partisipasi Angkatan Kerja (TPAK) sebesar {$valTpak3Fmt}% dengan total penduduk bekerja mencapai {$valWork3Fmt} ribu orang.";

        $ann3 = $getAnnual('TK-005', 1, 2, $series['tpt'] ?? null);
        $ann3Vals = array_column($ann3, 'value');
        $cfg3 = [
            'name' => 'Pengangguran Terbuka',
            'subtitle' => 'Persentase jumlah penganggur terhadap total angkatan kerja daerah (TPT Sakernas)',
            'definition' => 'Tingkat Pengangguran Terbuka (TPT) mengukur proporsi penduduk usia kerja dalam angkatan kerja yang tidak bekerja, sedang mencari pekerjaan, atau mempersiapkan usaha baru.',
            'year' => $yr3,
            'curVal' => $valTpt3Fmt,
            'curUnit' => '%',
            'curDelta' => $deltaTpt3Str,
            'curStatus' => $status3,
            'metrics' => [
                ['label' => 'TPAK (Partisipasi Kerja)', 'value' => $valTpak3Fmt . '%', 'note' => 'Penduduk Aktif'],
                ['label' => 'Penduduk Bekerja', 'value' => $valWork3Fmt . ' Ribu', 'note' => 'Jiwa Terserap'],
                ['label' => 'Jumlah Penganggur', 'value' => number_format($valUnemp3 / 1000, 1, ',', '.') . ' Ribu', 'note' => 'Jiwa Mencari Kerja']
            ],
            'chartTitle' => 'Tren Tingkat Pengangguran Terbuka',
            'chartUnit' => '%',
            'chartSeries' => [
                ['name' => 'TPT Banyuwangi', 'values' => $ann3Vals, 'style' => 'primary']
            ],
            'insight' => $insight3,
            'reading' => 'TPT yang turun perlu dibaca bersama partisipasi angkatan kerja dan kualitas pekerjaan.',
            'related' => [
                ['label' => 'Tingkat Partisipasi Kerja (TPAK)', 'value' => number_format($valTpak3, 2, ',', '.') . '%', 'note' => 'Keterlibatan penduduk usia kerja aktif', 'available' => true],
                ['label' => 'Penduduk bekerja', 'value' => number_format($valWork3 / 1000, 1, ',', '.') . ' Ribu', 'note' => 'Total tenaga kerja produktif terserap', 'available' => true],
                ['label' => 'Pekerja sektor formal', 'value' => number_format($valForm3, 2, ',', '.') . '%', 'note' => 'Pangsa pekerja dengan kepastian status', 'available' => true]
            ],
            'coverage' => 'Data bersumber dari Survei Angkatan Kerja Nasional (Sakernas) BPS. Pembacaan TPT dilengkapi dengan indikator TPAK, pekerja formal-informal, dan setengah pengangguran.',
            'componentTitle' => 'Struktur Pasar Kerja & Ketenagakerjaan (Sakernas BPS)',
            'componentDesc' => "Komposisi angkatan kerja, penyerapan kerja, dan kualitas tenaga kerja Kabupaten Banyuwangi tahun {$yr3}.",
            'components' => [
                ['name' => 'Tingkat Pengangguran Terbuka (TPT)', 'value' => $valTpt3, 'unit' => '%', 'context' => 'Rasio penganggur terhadap total angkatan kerja', 'share' => $valTpt3, 'benchmark' => 4.10],
                ['name' => 'Tingkat Partisipasi Angkatan Kerja (TPAK)', 'value' => $valTpak3, 'unit' => '%', 'context' => 'Proporsi usia kerja yang aktif berekonomi', 'share' => $valTpak3, 'benchmark' => 74.20],
                ['name' => 'Jumlah Angkatan Kerja', 'value' => round(($valWork3 + $valUnemp3) / 1000, 1), 'unit' => 'Ribu Orang', 'context' => 'Total angkatan kerja produktif', 'share' => 100, 'benchmark' => 985.00],
                ['name' => 'Penduduk Bekerja', 'value' => round($valWork3 / 1000, 1), 'unit' => 'Ribu Orang', 'context' => 'Tenaga kerja terserap dalam lapangan usaha', 'share' => 95.97, 'benchmark' => 944.60],
                ['name' => 'Penganggur Terbuka', 'value' => round($valUnemp3 / 1000, 1), 'unit' => 'Ribu Orang', 'context' => 'Sedang mencari atau belum mendapatkan kerja', 'share' => 4.03, 'benchmark' => 40.40],
                ['name' => 'Pekerja Sektor Formal', 'value' => $valForm3, 'unit' => '%', 'context' => 'Karyawan / buruh dengan jaminan upah teratur', 'share' => $valForm3, 'benchmark' => 38.50],
                ['name' => 'Tingkat Setengah Pengangguran', 'value' => $valHalf3, 'unit' => '%', 'context' => 'Bekerja < 35 jam & masih mencari jam tambahan', 'share' => $valHalf3, 'benchmark' => 7.10]
            ],
            'comparisonTitle' => 'Perbandingan Tingkat Pengangguran (TPT) Kabupaten/Kota di Jawa Timur',
            'comparisonDesc' => 'Perbandingan persentase TPT daerah di Jawa Timur; urutan berdasarkan tingkat pengangguran terendah.',
            'peers' => [
                ['name' => 'Kab. Situbondo', 'value' => 3.42, 'unit' => '%', 'note' => 'Pembanding Jawa Timur', 'current' => false],
                ['name' => 'Kab. Pacitan', 'value' => 3.65, 'unit' => '%', 'note' => 'Pembanding Jawa Timur', 'current' => false],
                ['name' => 'Kab. Bondowoso', 'value' => 3.82, 'unit' => '%', 'note' => 'Kabupaten Tetangga', 'current' => false],
                ['name' => 'Kab. Banyuwangi', 'value' => $valTpt3, 'unit' => '%', 'note' => 'Kabupaten Terpilih', 'current' => true],
                ['name' => 'Kab. Jember', 'value' => 4.25, 'unit' => '%', 'note' => 'Kabupaten Tetangga', 'current' => false],
                ['name' => 'Kota Malang', 'value' => 5.48, 'unit' => '%', 'note' => 'Kota Pusat Pendidikan', 'current' => false],
                ['name' => 'Kota Surabaya', 'value' => 5.92, 'unit' => '%', 'note' => 'Pusat Industri Jatim', 'current' => false],
                ['name' => 'Kab. Sidoarjo', 'value' => 6.20, 'unit' => '%', 'note' => 'Kawasan Industri', 'current' => false]
            ],
            'heatTitle' => 'Peta Panas Ketenagakerjaan & Partisipasi Kerja (2020–2024)',
            'heatDesc' => 'Dinamika pasar kerja Kabupaten Banyuwangi selama lima tahun terakhir.',
            'heatYears' => [2020, 2021, 2022, 2023, 2024],
            'heatRows' => [
                ['name' => 'Pengangguran Terbuka (TPT %)', 'values' => [5.34, 5.02, 4.45, 4.10, 4.03]],
                ['name' => 'Partisipasi Angkatan Kerja (TPAK %)', 'values' => [72.80, 73.40, 74.15, 74.20, 74.91]],
                ['name' => 'Pekerja Formal (%)', 'values' => [35.20, 36.80, 38.10, 38.90, 39.80]],
                ['name' => 'Setengah Pengangguran (%)', 'values' => [8.90, 8.10, 7.45, 6.90, 6.42]]
            ],
            'annualSeries' => $ann3
        ];

        // ====================================================================
        // 4: TINGKAT KEMISKINAN (KES-001 s.d. KES-006, KES-012)
        // ====================================================================
        $rec4 = $getRecords(['KES-001', 'KES-002', 'KES-003', 'KES-004', 'KES-005', 'KES-006', 'KES-012'], $yr4);
        $valP0_4 = (float)($rec4['KES-001']['nilai'] ?? 6.54);
        $valPpl4 = (float)($rec4['KES-002']['nilai'] ?? 106.61);
        $valGk4 = (float)($rec4['KES-003']['nilai'] ?? 470713);
        $valP1_4 = (float)($rec4['KES-004']['nilai'] ?? 0.44);
        $valP2_4 = (float)($rec4['KES-005']['nilai'] ?? 0.06);
        $valExt4 = (float)($rec4['KES-006']['nilai'] ?? 0.43);
        $valExp4 = (float)($rec4['KES-012']['nilai'] ?? 1516296);

        $rec4Prev = $this->db->select('nilai')->where('kode_indikator', 'KES-001')->where('kode_wilayah', $kode)->where('tahun', $yr4 - 1)->where('is_deleted', 0)->get('database_ekonomi')->row_array();
        $prevP0_4 = !empty($rec4Prev) ? (float)$rec4Prev['nilai'] : 7.34;
        $deltaP0_4 = round($valP0_4 - $prevP0_4, 2);
        $deltaP0_4Str = ($deltaP0_4 >= 0 ? '+' : '') . number_format($deltaP0_4, 2, ',', '.') . ' pp';
        $trendWord4 = ($deltaP0_4 <= 0) ? 'berkurang/menurun' : 'meningkat';
        $status4 = ($deltaP0_4 <= 0) ? 'Meningkat' : 'Perlu perhatian';
        $valP0_4Fmt = number_format($valP0_4, 2, ',', '.');
        $valPpl4Fmt = number_format($valPpl4, 1, ',', '.');
        $valGk4Fmt = number_format($valGk4, 0, ',', '.');
        $insight4 = "Persentase penduduk miskin (P0) di Kabupaten Banyuwangi pada tahun {$yr4} tercatat sebesar {$valP0_4Fmt}%, {$trendWord4} sebesar {$deltaP0_4Str} dibandingkan tahun sebelumnya. Jumlah warga miskin tercatat sebanyak {$valPpl4Fmt} ribu jiwa pada batas Garis Kemiskinan Rp {$valGk4Fmt}/kapita/bulan.";

        $ann4 = $getAnnual('KES-001', 1, 2, $series['poverty'] ?? null);
        $ann4Vals = array_column($ann4, 'value');
        $cfg4 = [
            'name' => 'Tingkat Kemiskinan',
            'subtitle' => 'Persentase penduduk dengan pengeluaran di bawah Garis Kemiskinan (P0 Susenas BPS)',
            'definition' => 'Tingkat Kemiskinan (P0) mengukur persentase penduduk yang memiliki rata-rata pengeluaran per kapita per bulan di bawah Garis Kemiskinan (GK) makanan dan non-makanan.',
            'year' => $yr4,
            'curVal' => $valP0_4Fmt,
            'curUnit' => '%',
            'curDelta' => $deltaP0_4Str,
            'curStatus' => $status4,
            'metrics' => [
                ['label' => 'Jumlah Penduduk Miskin', 'value' => $valPpl4Fmt . ' Ribu', 'note' => "Tahun {$yr4}"],
                ['label' => 'Garis Kemiskinan (GK)', 'value' => 'Rp ' . $valGk4Fmt, 'note' => 'kapita/bulan'],
                ['label' => 'Miskin Ekstrem', 'value' => number_format($valExt4, 2, ',', '.') . '%', 'note' => 'Standar PPP']
            ],
            'chartTitle' => 'Tren tingkat kemiskinan',
            'chartUnit' => '%',
            'chartSeries' => [
                ['name' => 'Tingkat Kemiskinan (P0)', 'values' => $ann4Vals, 'style' => 'primary'],
                ['name' => 'Jawa Timur', 'values' => [10.38, 10.33, 9.79, 9.50, 9.20], 'style' => 'secondary']
            ],
            'insight' => $insight4,
            'reading' => 'Penurunan persentase kemiskinan perlu diverifikasi bersama kedalaman, keparahan, dan jumlah penduduk miskin.',
            'related' => [
                ['label' => 'Indeks kedalaman kemiskinan (P1)', 'value' => number_format($valP1_4, 2, ',', '.') . ' Poin', 'note' => 'Jarak rata-rata pengeluaran miskin terhadap GK', 'available' => true],
                ['label' => 'Indeks keparahan kemiskinan (P2)', 'value' => number_format($valP2_4, 2, ',', '.') . ' Poin', 'note' => 'Ketimpangan pengeluaran di antara warga miskin', 'available' => true],
                ['label' => 'Garis Kemiskinan (GK)', 'value' => 'Rp ' . number_format($valGk4, 0, ',', '.'), 'note' => 'Batas pengeluaran per kapita per bulan', 'available' => true]
            ],
            'coverage' => 'Data kemiskinan makro bersumber dari Survei Sosial Ekonomi Nasional (Susenas) BPS Kabupaten Banyuwangi.',
            'componentTitle' => 'Komponen Indikator Kemiskinan & Perlindungan Sosial',
            'componentDesc' => "Rincian headcount index, kedalaman, keparahan kemiskinan, dan rata-rata pengeluaran per kapita tahun {$yr4}.",
            'components' => [
                ['name' => 'Persentase Penduduk Miskin (P0)', 'value' => $valP0_4, 'unit' => '%', 'context' => 'Headcount index kemiskinan makro BPS', 'share' => $valP0_4, 'benchmark' => 6.54],
                ['name' => 'Jumlah Penduduk Miskin', 'value' => $valPpl4, 'unit' => 'Ribu Orang', 'context' => 'Penduduk dengan pengeluaran < Garis Kemiskinan', 'share' => $valP0_4, 'benchmark' => 106.61],
                ['name' => 'Garis Kemiskinan (GK)', 'value' => $valGk4, 'unit' => 'Rp/kapita/bln', 'context' => 'Nilai pengeluaran minimum kebutuhan pangan/non-pangan', 'share' => 100, 'benchmark' => 470713],
                ['name' => 'Indeks Kedalaman Kemiskinan (P1)', 'value' => $valP1_4, 'unit' => 'Poin', 'context' => 'Rata-rata kesenjangan pengeluaran miskin terhadap GK', 'share' => $valP1_4, 'benchmark' => 0.44],
                ['name' => 'Indeks Keparahan Kemiskinan (P2)', 'value' => $valP2_4, 'unit' => 'Poin', 'context' => 'Ketimpangan pengeluaran di antara penduduk miskin', 'share' => $valP2_4, 'benchmark' => 0.06],
                ['name' => 'Penduduk Miskin Ekstrem', 'value' => $valExt4, 'unit' => '%', 'context' => 'Berdasarkan standar PPP 1,90 USD per hari', 'share' => $valExt4, 'benchmark' => 0.43],
                ['name' => 'Rata-rata Pengeluaran per Kapita Rumah Tangga', 'value' => $valExp4, 'unit' => 'Rp/kapita/bln', 'context' => 'Hasil survei konsumsi Susenas BPS Banyuwangi', 'share' => 100, 'benchmark' => 1516296]
            ],
            'comparisonTitle' => 'Peringkat Kemiskinan 38 Kabupaten/Kota di Jawa Timur',
            'comparisonDesc' => 'Peringkat persentase penduduk miskin tahun ' . $yr4 . ' di Jawa Timur; urutan berdasarkan angka kemiskinan terendah.',
            'peers' => $getPeers('KES-001', $yr4, true, 1, '%', 2, 'Tingkat kemiskinan Jawa Timur'),
            'heatTitle' => 'Peta Panas Dinamika Indikator Kemiskinan (5 Tahun Terakhir)',
            'heatDesc' => 'Penurunan angka kemiskinan makro Kabupaten Banyuwangi.',
            'heatYears' => [2022, 2023, 2024, 2025, 2026],
            'heatRows' => [
                ['name' => 'Persentase Miskin P0 (%)', 'values' => [7.51, 7.34, 6.54, 6.13, 5.60]],
                ['name' => 'Jumlah Penduduk Miskin (Ribu)', 'values' => [122.01, 119.52, 106.61, 100.13, 91.57]],
                ['name' => 'Garis Kemiskinan (Ribu Rp)', 'values' => [414.88, 448.93, 470.71, 485.01, 485.01]],
                ['name' => 'Indeks Kedalaman P1', 'values' => [1.18, 0.89, 0.44, 0.41, 0.26]],
                ['name' => 'Indeks Keparahan P2', 'values' => [0.25, 0.17, 0.06, 0.06, 0.02]]
            ],
            'annualSeries' => $ann4
        ];

        // ====================================================================
        // 5: INDEKS PEMBANGUNAN MANUSIA / IPM (KES-007 s.d. KES-011)
        // ====================================================================
        $rec5 = $getRecords(['KES-007', 'KES-008', 'KES-009', 'KES-010', 'KES-011'], $yr5);
        $valIpm5 = (float)($rec5['KES-007']['nilai'] ?? 74.30);
        $valUhh5 = (float)($rec5['KES-008']['nilai'] ?? 71.38);
        $valHls5 = (float)($rec5['KES-009']['nilai'] ?? 13.14);
        $valRls5 = (float)($rec5['KES-010']['nilai'] ?? 7.78);
        $valInc5 = (float)($rec5['KES-011']['nilai'] ?? 13320);

        $rec5Prev = $this->db->select('nilai')->where('kode_indikator', 'KES-007')->where('kode_wilayah', $kode)->where('tahun', $yr5 - 1)->where('is_deleted', 0)->get('database_ekonomi')->row_array();
        $prevIpm5 = !empty($rec5Prev) ? (float)$rec5Prev['nilai'] : 73.79;
        $deltaIpm5 = round($valIpm5 - $prevIpm5, 2);
        $deltaIpm5Str = ($deltaIpm5 >= 0 ? '+' : '') . number_format($deltaIpm5, 2, ',', '.') . ' poin';
        $trendWord5 = ($deltaIpm5 >= 0) ? 'meningkat' : 'berkurang';
        $status5 = ($deltaIpm5 >= 0) ? 'Meningkat' : 'Perlu perhatian';
        $ipmTier = ($valIpm5 >= 80) ? 'Sangat Tinggi' : (($valIpm5 >= 70) ? 'Tinggi' : 'Sedang');
        $valIpm5Fmt = number_format($valIpm5, 2, ',', '.');
        $valUhh5Fmt = number_format($valUhh5, 2, ',', '.');
        $valRls5Fmt = number_format($valRls5, 2, ',', '.');
        $insight5 = "Indeks Pembangunan Manusia (IPM) Kabupaten Banyuwangi pada tahun {$yr5} mencapai {$valIpm5Fmt} poin, {$trendWord5} sebesar {$deltaIpm5Str} dari tahun sebelumnya (status '{$ipmTier}'). Capaian ini ditopang oleh Umur Harapan Hidup (UHH) {$valUhh5Fmt} tahun dan Rata-rata Lama Sekolah (RLS) {$valRls5Fmt} tahun.";

        $ann5 = $getAnnual('KES-007', 1, 2, $series['ipm'] ?? null);
        $ann5Vals = array_column($ann5, 'value');
        $cfg5 = [
            'name' => 'Indeks Pembangunan Manusia (IPM)',
            'subtitle' => 'Capaian komposit kualitas kesehatan, pendidikan, dan standar hidup layak masyarakat',
            'definition' => 'IPM mengukur pencapaian rata-rata suatu wilayah dalam tiga dimensi dasar pembangunan manusia: umur panjang dan hidup sehat (UHH), pengetahuan (HLS & RLS), serta standar hidup layak (pengeluaran riil disesuaikan).',
            'year' => $yr5,
            'curVal' => $valIpm5Fmt,
            'curUnit' => 'Poin',
            'curDelta' => $deltaIpm5Str,
            'curStatus' => $status5,
            'metrics' => [
                ['label' => 'Kategori Status IPM', 'value' => $ipmTier, 'note' => "Tahun {$yr5}"],
                ['label' => 'Umur Harapan Hidup (UHH)', 'value' => $valUhh5Fmt . ' Tahun', 'note' => 'Dimensi Kesehatan'],
                ['label' => 'Rata-rata Lama Sekolah (RLS)', 'value' => $valRls5Fmt . ' Tahun', 'note' => 'Dimensi Pendidikan']
            ],
            'chartTitle' => 'Tren Indeks Pembangunan Manusia',
            'chartUnit' => ' Poin',
            'chartSeries' => [
                ['name' => 'IPM Banyuwangi', 'values' => $ann5Vals, 'style' => 'primary'],
                ['name' => 'Jawa Timur', 'values' => [72.14, 72.75, 73.38, 74.05, 74.65], 'style' => 'secondary']
            ],
            'insight' => $insight5,
            'reading' => 'Kenaikan IPM perlu dilihat dari kontribusi dimensi kesehatan, pendidikan, dan standar hidup.',
            'related' => [
                ['label' => 'Umur Harapan Hidup (UHH)', 'value' => number_format($valUhh5, 2, ',', '.') . ' Tahun', 'note' => 'Dimensi umur panjang dan hidup sehat', 'available' => true],
                ['label' => 'Harapan Lama Sekolah (HLS)', 'value' => number_format($valHls5, 2, ',', '.') . ' Tahun', 'note' => 'Dimensi akses jenjang pendidikan', 'available' => true],
                ['label' => 'Pengeluaran riil disesuaikan', 'value' => 'Rp ' . number_format($valInc5 / 1000, 2, ',', '.') . ' Juta', 'note' => 'Dimensi standar hidup layak per kapita/tahun', 'available' => true]
            ],
            'coverage' => 'Perhitungan IPM menggunakan metode baru BPS dengan penggabungan geometrik UHH, HLS, RLS, dan pengeluaran riil per kapita disesuaikan.',
            'componentTitle' => 'Dimensi & Komponen Penyusun IPM (Metode Baru BPS)',
            'componentDesc' => "Komponen pembentuk indeks komposit pembangunan manusia Kabupaten Banyuwangi pada tahun {$yr5}.",
            'components' => [
                ['name' => 'Indeks Pembangunan Manusia (IPM)', 'value' => $valIpm5, 'unit' => 'Poin', 'context' => 'Indeks komposit pembangunan kualitas manusia', 'share' => $valIpm5, 'benchmark' => 74.30],
                ['name' => 'Umur Harapan Hidup saat Lahir (UHH)', 'value' => $valUhh5, 'unit' => 'Tahun', 'context' => 'Dimensi kesehatan & kelangsungan hidup layak', 'share' => $valUhh5, 'benchmark' => 71.38],
                ['name' => 'Harapan Lama Sekolah (HLS)', 'value' => $valHls5, 'unit' => 'Tahun', 'context' => 'Peluang durasi sekolah bagi anak umur 7 tahun', 'share' => $valHls5, 'benchmark' => 13.14],
                ['name' => 'Rata-rata Lama Sekolah (RLS)', 'value' => $valRls5, 'unit' => 'Tahun', 'context' => 'Durasi sekolah penduduk umur 25 tahun ke atas', 'share' => $valRls5, 'benchmark' => 7.78],
                ['name' => 'Pengeluaran Riil per Kapita Disesuaikan', 'value' => round($valInc5 / 1000, 2), 'unit' => 'Juta Rp/thn', 'context' => 'Standar hidup layak daya beli masyarakat', 'share' => round($valInc5 / 1000, 2), 'benchmark' => 13.32]
            ],
            'comparisonTitle' => 'Peringkat IPM 38 Kabupaten/Kota di Jawa Timur',
            'comparisonDesc' => 'Peringkat capaian IPM kabupaten/kota se-Jawa Timur tahun ' . $yr5 . '; urutan berdasarkan skor tertinggi.',
            'peers' => $getPeers('KES-007', $yr5, false, 1, 'Poin', 2, 'IPM Jawa Timur'),
            'heatTitle' => 'Peta Panas Komponen IPM (5 Tahun Terakhir)',
            'heatDesc' => 'Perkembangan nilai komponen penyusun IPM Kabupaten Banyuwangi.',
            'heatYears' => [2022, 2023, 2024, 2025, 2026],
            'heatRows' => [
                ['name' => 'Skor IPM Komposit (Poin)', 'values' => [73.15, 73.79, 74.30, 75.17, 75.99]],
                ['name' => 'Umur Harapan Hidup UHH (Tahun)', 'values' => [71.06, 71.38, 71.57, 71.57, 71.57]],
                ['name' => 'Harapan Lama Sekolah HLS (Tahun)', 'values' => [13.11, 13.12, 13.14, 13.32, 13.52]],
                ['name' => 'Rata-rata Lama Sekolah RLS (Tahun)', 'values' => [7.66, 7.76, 7.78, 7.84, 7.85]],
                ['name' => 'Pengeluaran Riil Disesuaikan (Juta Rp)', 'values' => [12.32, 12.82, 13.32, 13.96, 14.73]]
            ],
            'annualSeries' => $ann5
        ];

        // ====================================================================
        // 6: REALISASI INVESTASI (INV-001, INV-001.Q1 s.d. Q4, INV-002, INV-003)
        // ====================================================================
        $recInv = $getRecords(['INV-001', 'INV-001.Q1', 'INV-001.Q2', 'INV-001.Q3', 'INV-001.Q4', 'INV-002', 'INV-003', 'INV-004', 'INV-005'], $yr6);
        $invTotalRaw = (float)($recInv['INV-001']['nilai'] ?? 3350000000000);
        $invTotalTril = round($invTotalRaw / 1e12, 2);
        $invQ1 = round((float)($recInv['INV-001.Q1']['nilai'] ?? 200000000000) / 1e9, 2);
        $invQ2 = round((float)($recInv['INV-001.Q2']['nilai'] ?? 270000000000) / 1e9, 2);
        $invQ3 = round((float)($recInv['INV-001.Q3']['nilai'] ?? 1510000000000) / 1e9, 2);
        $invQ4 = round((float)($recInv['INV-001.Q4']['nilai'] ?? 1370000000000) / 1e9, 2);
        $invPma = round((float)($recInv['INV-002']['nilai'] ?? 110000000000) / 1e9, 2);
        $invPmdn = round((float)($recInv['INV-003']['nilai'] ?? 3240000000000) / 1e9, 2);
        $pmaShare = ($invTotalRaw > 0) ? round(($invPma * 1e9 / $invTotalRaw) * 100, 1) : 3.3;
        $pmdnShare = ($invTotalRaw > 0) ? round(($invPmdn * 1e9 / $invTotalRaw) * 100, 1) : 96.7;

        $recInvPrev = $this->db->select('nilai')->where('kode_indikator', 'INV-001')->where('kode_wilayah', $kode)->where('tahun', $yr6 - 1)->where('is_deleted', 0)->get('database_ekonomi')->row_array();
        $prevInvRaw = !empty($recInvPrev) ? (float)$recInvPrev['nilai'] : 3660000000000;
        $prevInvTril = round($prevInvRaw / 1e12, 2);
        $invDeltaTril = round($invTotalTril - $prevInvTril, 2);
        $invDeltaPct = ($prevInvRaw > 0) ? round((($invTotalRaw - $prevInvRaw) / $prevInvRaw) * 100, 2) : 0;
        $invDeltaStr = ($invDeltaTril >= 0 ? '+' : '') . number_format($invDeltaTril, 2, ',', '.') . ' triliun (' . ($invDeltaPct >= 0 ? '+' : '') . number_format($invDeltaPct, 2, ',', '.') . '%)';
        $statusInv = ($invDeltaTril >= 0) ? 'Meningkat' : 'Perlu perhatian';
        $invTotalFmt = number_format($invTotalTril, 2, ',', '.');
        $invPmdnFmt = number_format($invPmdn, 0, ',', '.');
        $invPmaFmt = number_format($invPma, 0, ',', '.');
        $insight6 = "Total realisasi penanaman modal fisik di Kabupaten Banyuwangi pada tahun {$yr6} tercatat sebesar Rp {$invTotalFmt} Triliun ({$invDeltaStr} dibandingkan tahun lalu). Realisasi didominasi oleh investasi modal dalam negeri (PMDN) sebesar Rp {$invPmdnFmt} Miliar ({$pmdnShare}%) dan penanaman modal asing (PMA) sebesar Rp {$invPmaFmt} Miliar ({$pmaShare}%).";

        $ann6 = $getAnnual('INV-001', 1e12, 2, [
            2023 => 3.66,
            2024 => 3.35,
            2025 => 3.80,
            2026 => 3.85
        ]);
        $ann6Vals = array_column($ann6, 'value');
        $cfg6 = [
            'name' => 'Realisasi Investasi',
            'subtitle' => 'Nilai realisasi penanaman modal fisik PMA & PMDN berdasarkan laporan LKPM DPMPTSP',
            'definition' => 'Realisasi investasi mencatat arus masuk modal riil dari Penanaman Modal Asing (PMA) dan Penanaman Modal Dalam Negeri (PMDN) yang dilaporkan berkala per kuartal melalui sistem OSS/LKPM.',
            'year' => $yr6,
            'curVal' => "Rp {$invTotalFmt}",
            'curUnit' => 'Triliun',
            'curDelta' => $invDeltaStr,
            'curStatus' => $statusInv,
            'metrics' => [
                ['label' => 'Investasi PMDN', 'value' => 'Rp ' . $invPmdnFmt . ' Miliar', 'note' => "Porsi {$pmdnShare}% Total"],
                ['label' => 'Investasi PMA', 'value' => 'Rp ' . $invPmaFmt . ' Miliar', 'note' => "Porsi {$pmaShare}% Total"],
                ['label' => 'Total LKPM Daerah', 'value' => "Rp {$invTotalFmt} Triliun", 'note' => "Tahun {$yr6}"]
            ],
            'chartTitle' => 'Tren Realisasi Investasi Nominal',
            'chartUnit' => ' Triliun Rp',
            'chartSeries' => [
                ['name' => 'Total Investasi (LKPM)', 'values' => $ann6Vals, 'style' => 'primary']
            ],
            'insight' => $insight6,
            'reading' => 'Bedakan perubahan nilai nominal realisasi investasi dengan laju pertumbuhan investasi.',
            'breakdown' => [
                ['name' => 'Penanaman Modal Dalam Negeri (PMDN)', 'value' => $invPmdn, 'note' => "Rp " . number_format($invPmdn, 0, ',', '.') . " Miliar ({$pmdnShare}%)"],
                ['name' => 'Penanaman Modal Asing (PMA)', 'value' => $invPma, 'note' => "Rp " . number_format($invPma, 0, ',', '.') . " Miliar ({$pmaShare}%)"],
                ['name' => 'Realisasi Kuartal 1 (Q1)', 'value' => $invQ1, 'note' => "Rp " . number_format($invQ1, 0, ',', '.') . " Miliar"],
                ['name' => 'Realisasi Kuartal 2 (Q2)', 'value' => $invQ2, 'note' => "Rp " . number_format($invQ2, 0, ',', '.') . " Miliar"],
                ['name' => 'Realisasi Kuartal 3 (Q3)', 'value' => $invQ3, 'note' => "Rp " . number_format($invQ3, 0, ',', '.') . " Miliar"],
                ['name' => 'Realisasi Kuartal 4 (Q4)', 'value' => $invQ4, 'note' => "Rp " . number_format($invQ4, 0, ',', '.') . " Miliar"]
            ],
            'related' => [
                ['label' => 'Investasi PMDN (Domestik)', 'value' => 'Rp ' . number_format($invPmdn, 0, ',', '.') . ' Miliar', 'note' => "Porsi {$pmdnShare}% dari total investasi daerah", 'available' => true],
                ['label' => 'Investasi PMA (Asing)', 'value' => 'Rp ' . number_format($invPma, 0, ',', '.') . ' Miliar', 'note' => "Porsi {$pmaShare}% dari total investasi daerah", 'available' => true],
                ['label' => 'Kepatuhan LKPM Online', 'value' => 'Tersedia Q1–Q4', 'note' => 'Pelaporan berkala melalui platform OSS Kementerian Investasi', 'available' => true]
            ],
            'coverage' => 'Data realisasi investasi mencatat proyek fisik PMA dan PMDN berdasarkan Laporan Kegiatan Penanaman Modal (LKPM) yang dihimpun DPMPTSP Banyuwangi.',
            'componentTitle' => 'Rincian Kuartal (Q1–Q4) & Komposisi Modal (PMA vs PMDN)',
            'componentDesc' => "Rincian realisasi triwulanan dan sumber asal modal usaha di Kabupaten Banyuwangi pada tahun {$yr6}.",
            'components' => [
                ['name' => 'Realisasi Kuartal 1 (Q1)', 'value' => $invQ1, 'unit' => 'Miliar Rp', 'context' => 'Laporan triwulan pertama LKPM', 'share' => round($invQ1 / max(1, $invTotalRaw/1e9) * 100, 1), 'benchmark' => 25.0],
                ['name' => 'Realisasi Kuartal 2 (Q2)', 'value' => $invQ2, 'unit' => 'Miliar Rp', 'context' => 'Laporan triwulan kedua LKPM', 'share' => round($invQ2 / max(1, $invTotalRaw/1e9) * 100, 1), 'benchmark' => 25.0],
                ['name' => 'Realisasi Kuartal 3 (Q3)', 'value' => $invQ3, 'unit' => 'Miliar Rp', 'context' => 'Laporan triwulan ketiga LKPM', 'share' => round($invQ3 / max(1, $invTotalRaw/1e9) * 100, 1), 'benchmark' => 25.0],
                ['name' => 'Realisasi Kuartal 4 (Q4)', 'value' => $invQ4, 'unit' => 'Miliar Rp', 'context' => 'Laporan triwulan keempat LKPM', 'share' => round($invQ4 / max(1, $invTotalRaw/1e9) * 100, 1), 'benchmark' => 25.0],
                ['name' => 'Penanaman Modal Dalam Negeri (PMDN)', 'value' => $invPmdn, 'unit' => 'Miliar Rp', 'context' => 'Investasi modal investor domestik', 'share' => $pmdnShare, 'benchmark' => 80.0],
                ['name' => 'Penanaman Modal Asing (PMA)', 'value' => $invPma, 'unit' => 'Miliar Rp', 'context' => 'Investasi modal asing multinasional', 'share' => $pmaShare, 'benchmark' => 20.0]
            ],
            'comparisonTitle' => 'Peringkat Realisasi Investasi 38 Kabupaten/Kota di Jawa Timur',
            'comparisonDesc' => 'Perbandingan nilai total investasi (PMA + PMDN) tahun ' . $yr6 . ' di Jawa Timur (dalam Triliun Rupiah).',
            'peers' => $getPeers('INV-001', $yr6, false, 1e12, 'Triliun Rp', 2, 'Realisasi investasi LKPM'),
            'heatTitle' => 'Peta Panas Realisasi Investasi per Kuartal (2023–2026)',
            'heatDesc' => 'Distribusi pergerakan nilai investasi triwulanan di Kabupaten Banyuwangi (dalam Miliar Rupiah).',
            'heatYears' => [2023, 2024, 2025, 2026],
            'heatRows' => [
                ['name' => 'Kuartal 1 (Q1)', 'values' => [820.0, 200.0, 912.0, 925.0]],
                ['name' => 'Kuartal 2 (Q2)', 'values' => [950.0, 270.0, 968.0, 980.0]],
                ['name' => 'Kuartal 3 (Q3)', 'values' => [910.0, 1510.0, 985.0, 995.0]],
                ['name' => 'Kuartal 4 (Q4)', 'values' => [980.0, 1370.0, 934.0, 954.0]],
                ['name' => 'Total PMA (Asing)', 'values' => [230.0, 110.0, 139.4, 301.3]],
                ['name' => 'Total PMDN (Domestik)', 'values' => [3430.0, 3240.0, 3660.6, 3553.0]]
            ],
            'annualSeries' => $ann6
        ];

        // ====================================================================
        // 7: PERTUMBUHAN INVESTASI (INV-001 YoY, INV-002 YoY, INV-003 YoY)
        // ====================================================================
        $igCur = ($prevInvRaw > 0) ? round((($invTotalRaw - $prevInvRaw) / $prevInvRaw) * 100, 2) : -8.47;
        $igCurFmt = ($igCur >= 0 ? '+' : '') . number_format($igCur, 2, ',', '.');
        $igStatus = ($igCur >= 0) ? 'Meningkat' : 'Perlu perhatian';
        $trendNote7 = ($igCur >= 0) ? 'mencerminkan ekspansi positif minat pemodal dan perluasan skala usaha' : 'mencerminkan dinamika siklus penyelesaian proyek modal fisik dan evaluasi perizinan usaha';
        $insight7 = "Laju pertumbuhan investasi tahunan Kabupaten Banyuwangi pada tahun {$yr7} tercatat sebesar {$igCurFmt}%, dengan status {$igStatus}. Pergerakan laju investasi ini {$trendNote7} di Kabupaten Banyuwangi.";

        $cfg7 = [
            'name' => 'Pertumbuhan Investasi',
            'subtitle' => 'Laju pertumbuhan nilai realisasi modal usaha tahun ke tahun (year-on-year)',
            'definition' => 'Pertumbuhan investasi mengukur dinamika ekspansi modal bisnis baru dan penambahan kapasitas produksi eksisting di Kabupaten Banyuwangi dibandingkan tahun sebelumnya.',
            'year' => $yr7,
            'curVal' => $igCurFmt,
            'curUnit' => '%',
            'curDelta' => "{$igCurFmt}% yoy",
            'curStatus' => $igStatus,
            'metrics' => [
                ['label' => 'Pertumbuhan PMDN', 'value' => ($pmdnShare > 50 ? '-2,94%' : '+12,40%'), 'note' => "Komponen Domestik"],
                ['label' => 'Pertumbuhan PMA', 'value' => ($pmaShare < 50 ? '+116,11%' : '+8,50%'), 'note' => "Komponen Asing"],
                ['label' => 'Total Laju Investasi', 'value' => "{$igCurFmt}%", 'note' => "Tahun {$yr7}"]
            ],
            'chartTitle' => 'Pertumbuhan Investasi dan Ekonomi',
            'chartUnit' => '%',
            'chartSeries' => [
                ['name' => 'Pertumbuhan Investasi (yoy)', 'values' => [-8.47, 13.43, 1.43], 'style' => 'primary'],
                ['name' => 'Pertumbuhan Ekonomi (ADHK)', 'values' => [4.68, 5.03, 5.18], 'style' => 'secondary']
            ],
            'insight' => $insight7,
            'reading' => 'Perbandingan pertumbuhan investasi dan ekonomi tidak dengan sendirinya membuktikan hubungan sebab-akibat.',
            'related' => [
                ['label' => 'Total Nilai Realisasi Investasi', 'value' => "Rp {$invTotalTril} Triliun", 'note' => 'Nilai dasar pembentukan modal riil daerah', 'available' => true],
                ['label' => 'Pertumbuhan PMA', 'value' => '+116,11%', 'note' => 'Pertumbuhan arus masuk modal luar negeri', 'available' => true],
                ['label' => 'Pertumbuhan PMDN', 'value' => '-2,94%', 'note' => 'Dinamika permodalan investor dalam negeri', 'available' => true]
            ],
            'coverage' => 'Seri laju pertumbuhan investasi tahunan dihitung dari laporan LKPM berkala yang konsisten. Bedakan pertumbuhan persentase dengan nilai absolut nominal.',
            'componentTitle' => 'Pertumbuhan Menurut Kategori Permodalan (PMA vs PMDN)',
            'componentDesc' => "Pertumbuhan tahunan per kategori asal penanaman modal di Kabupaten Banyuwangi pada tahun {$yr7}.",
            'components' => [
                ['name' => 'Pertumbuhan Total Investasi', 'value' => 1.43, 'unit' => '% yoy', 'context' => 'Laju tahunan nilai total realisasi modal', 'share' => 100, 'benchmark' => 5.20],
                ['name' => 'Pertumbuhan PMDN (Dalam Negeri)', 'value' => -2.94, 'unit' => '% yoy', 'context' => 'Dinamika modal investor dalam negeri', 'share' => 92.2, 'benchmark' => 6.10],
                ['name' => 'Pertumbuhan PMA (Asing)', 'value' => 116.11, 'unit' => '% yoy', 'context' => 'Dinamika proyek penanaman modal asing', 'share' => 7.8, 'benchmark' => 2.80],
                ['name' => 'Realisasi Total Nominal', 'value' => $invTotalTril, 'unit' => 'Triliun Rp', 'context' => 'Nilai nominal dasar perhitungan pertumbuhan', 'share' => 100, 'benchmark' => 3.80]
            ],
            'comparisonTitle' => 'Perbandingan Pertumbuhan Investasi Wilayah Jawa Timur',
            'comparisonDesc' => 'Perbandingan dinamika laju pertumbuhan investasi antardaerah di Jawa Timur.',
            'peers' => [
                ['name' => 'Kab. Gresik', 'value' => 24.50, 'unit' => '%', 'note' => 'Kawasan Industri Smelter', 'current' => false],
                ['name' => 'Kota Surabaya', 'value' => 18.20, 'unit' => '%', 'note' => 'Pusat Jasa & Perdagangan', 'current' => false],
                ['name' => 'Kab. Pasuruan', 'value' => 14.10, 'unit' => '%', 'note' => 'Kawasan Industri PIER', 'current' => false],
                ['name' => 'Kab. Banyuwangi', 'value' => 1.43, 'unit' => '%', 'note' => 'Kabupaten Terpilih', 'current' => true],
                ['name' => 'Kab. Sidoarjo', 'value' => 8.60, 'unit' => '%', 'note' => 'Penyangga Surabaya', 'current' => false],
                ['name' => 'Kab. Jember', 'value' => 4.30, 'unit' => '%', 'note' => 'Wilayah Sekitar', 'current' => false]
            ],
            'heatTitle' => 'Peta Panas Pertumbuhan Investasi Kuartalan (2023–2025)',
            'heatDesc' => 'Fluktuasi pertumbuhan realisasi investasi triwulanan di Kabupaten Banyuwangi.',
            'heatYears' => [2023, 2024, 2025],
            'heatRows' => [
                ['name' => 'Kuartal 1 (Q1)', 'values' => [15.2, -75.6, 356.0]],
                ['name' => 'Kuartal 2 (Q2)', 'values' => [22.4, -71.5, 258.5]],
                ['name' => 'Kuartal 3 (Q3)', 'values' => [18.9, 65.9, -34.7]],
                ['name' => 'Kuartal 4 (Q4)', 'values' => [12.0, 39.8, -31.8]]
            ],
            'annualSeries' => [
                ['year' => 2023, 'value' => 3.66, 'change' => null],
                ['year' => 2024, 'value' => 3.35, 'change' => '-0,31'],
                ['year' => 2025, 'value' => 3.80, 'change' => '+0,45'],
                ['year' => 2026, 'value' => 3.85, 'change' => '+0,05']
            ]
        ];

        return [
            0 => $cfg0,
            1 => $cfg1,
            2 => $cfg2,
            3 => $cfg3,
            4 => $cfg4,
            5 => $cfg5,
            6 => $cfg6,
            7 => $cfg7
        ];
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

    private function clean_numeric_value($valRaw) {
        if ($valRaw === null || $valRaw === '') return null;
        if (is_numeric($valRaw)) return (float)$valRaw;
        if (is_string($valRaw)) {
            $cleanVal = trim($valRaw);
            if (strpos($cleanVal, ',') !== false) {
                $cleanVal = str_replace('.', '', $cleanVal);
                $cleanVal = str_replace(',', '.', $cleanVal);
            } elseif (substr_count($cleanVal, '.') > 1) {
                $cleanVal = str_replace('.', '', $cleanVal);
            } elseif (preg_match('/^\d+\.\d{3}$/', $cleanVal)) {
                $cleanVal = str_replace('.', '', $cleanVal);
            }
            return (float)$cleanVal;
        }
        return (float)$valRaw;
    }
}
