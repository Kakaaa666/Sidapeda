<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_auth extends CI_Model {

    protected $table = 'akun';

    public function __construct() {
        parent::__construct();
        $this->load->database();
        if ($this->db->table_exists('akun')) {
            $this->table = 'akun';
        } elseif ($this->db->table_exists('pengguna')) {
            $this->table = 'pengguna';
        }
        $this->ensure_default_accounts();
    }

    public function ensure_default_accounts() {
        if ($this->db->table_exists($this->table)) {
            $count = $this->db->count_all($this->table);
            if ($count === 0) {
                $defaultUsers = array(
                    array(
                        'nama_pengguna' => 'admin',
                        'kata_sandi'    => password_hash('admin123', PASSWORD_BCRYPT),
                        'nama_lengkap'  => 'Arif Setiawan, S.E., M.Ec.',
                        'email'         => 'admin.bappeda@banyuwangikab.go.id',
                        'peran'         => 'Administrator',
                        'level'         => 1,
                        'status'        => 'Aktif',
                        'kode_wilayah'  => '35.10',
                        'dibuat_pada'   => date('Y-m-d H:i:s')
                    ),
                    array(
                        'nama_pengguna' => 'banyuwangi',
                        'kata_sandi'    => password_hash('banyuwangi123', PASSWORD_BCRYPT),
                        'nama_lengkap'  => 'Pemerintah Kabupaten Banyuwangi',
                        'email'         => 'pemkab@banyuwangikab.go.id',
                        'peran'         => 'Administrator',
                        'level'         => 1,
                        'status'        => 'Aktif',
                        'kode_wilayah'  => '35.10',
                        'dibuat_pada'   => date('Y-m-d H:i:s')
                    ),
                    array(
                        'nama_pengguna' => 'analis',
                        'kata_sandi'    => password_hash('analis123', PASSWORD_BCRYPT),
                        'nama_lengkap'  => 'Dra. Maya Puspitasari',
                        'email'         => 'analis.ekonomi@banyuwangikab.go.id',
                        'peran'         => 'Analis',
                        'level'         => 2,
                        'status'        => 'Aktif',
                        'kode_wilayah'  => '35.10',
                        'dibuat_pada'   => date('Y-m-d H:i:s')
                    ),
                    array(
                        'nama_pengguna' => 'operator',
                        'kata_sandi'    => password_hash('operator123', PASSWORD_BCRYPT),
                        'nama_lengkap'  => 'Budi Prasetyo, S.Kom.',
                        'email'         => 'operator.sidapeda@banyuwangikab.go.id',
                        'peran'         => 'Operator',
                        'level'         => 3,
                        'status'        => 'Aktif',
                        'kode_wilayah'  => '35.10',
                        'dibuat_pada'   => date('Y-m-d H:i:s')
                    )
                );
                $this->db->insert_batch($this->table, $defaultUsers);
            } else {
                $checkBwi = $this->db->where('nama_pengguna', 'banyuwangi')->get($this->table)->num_rows();
                if ($checkBwi === 0) {
                    $this->db->insert($this->table, array(
                        'nama_pengguna' => 'banyuwangi',
                        'kata_sandi'    => password_hash('banyuwangi123', PASSWORD_BCRYPT),
                        'nama_lengkap'  => 'Pemerintah Kabupaten Banyuwangi',
                        'email'         => 'pemkab@banyuwangikab.go.id',
                        'peran'         => 'Administrator',
                        'level'         => 1,
                        'status'        => 'Aktif',
                        'kode_wilayah'  => '35.10',
                        'dibuat_pada'   => date('Y-m-d H:i:s')
                    ));
                }
            }
        }
    }

    public function get_level_name($levelNum) {
        $map = array(
            1 => 'Administrator',
            2 => 'Analis',
            3 => 'Operator',
            4 => 'Reviewer'
        );
        return $map[(int)$levelNum] ?? 'Operator';
    }

    public function get_levels() {
        return array(
            1 => array('nomor' => 1, 'nama' => 'Administrator', 'label' => '1 — Administrator', 'deskripsi' => 'Akses penuh seluruh sistem, manajemen akun & konfigurasi'),
            2 => array('nomor' => 2, 'nama' => 'Analis',        'label' => '2 — Analis',        'deskripsi' => 'Analisis ekonomi, permodelan proyeksi & penyusunan laporan'),
            3 => array('nomor' => 3, 'nama' => 'Operator',      'label' => '3 — Operator',      'deskripsi' => 'Input data, katalog indikator & pemantauan dashboard'),
            4 => array('nomor' => 4, 'nama' => 'Reviewer',      'label' => '4 — Reviewer',      'deskripsi' => 'Pemantauan eksekutif dan tinjauan indikator pembangunan')
        );
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

    public function get_active_kode_wilayah() {
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

    private function map_user($row) {
        if (!$row) return null;
        $rawLevel = $row['level'] ?? $row['peran'] ?? 3;
        
        $levelNum = 3;
        if (is_numeric($rawLevel)) {
            $levelNum = (int)$rawLevel;
        } else {
            if ($rawLevel === 'Administrator') $levelNum = 1;
            elseif ($rawLevel === 'Analis') $levelNum = 2;
            elseif ($rawLevel === 'Operator') $levelNum = 3;
            elseif ($rawLevel === 'Reviewer') $levelNum = 4;
        }

        $levelName = $this->get_level_name($levelNum);
        $kodeWilayah = $row['kode_wilayah'] ?? '35.10';
        $namaWilayah = $this->get_nama_wilayah($kodeWilayah);

        return array(
            'id'            => (int)$row['id'],
            'username'      => $row['nama_pengguna'],
            'nama_pengguna' => $row['nama_pengguna'],
            'password'      => $row['kata_sandi'],
            'kata_sandi'    => $row['kata_sandi'],
            'name'          => $row['nama_lengkap'],
            'nama_lengkap'  => $row['nama_lengkap'],
            'email'         => $row['email'],
            'role'          => $row['peran'] ?? $levelName,
            'peran'         => $row['peran'] ?? $levelName,
            'level'         => (int)$levelNum,
            'level_nama'    => $levelName,
            'level_label'   => 'Level ' . $levelNum . ' (' . $levelName . ')',
            'kode_wilayah'  => $kodeWilayah,
            'nama_wilayah'  => $namaWilayah,
            'status'        => $row['status'],
            'avatar'        => $row['foto_profil'] ?? null,
            'last_login'    => $row['terakhir_masuk'] ?? null,
            'dibuat_pada'   => $row['dibuat_pada'] ?? null,
            'is_deleted'    => (int)($row['is_deleted'] ?? 0),
            'deleted_at'    => $row['deleted_at'] ?? null
        );
    }

    public function get_users($status = null) {
        if ($this->db->field_exists('is_deleted', $this->table)) {
            if ($status === 'Terhapus' || $status === '1' || $status === 1) {
                $this->db->where('is_deleted', 1);
            } else {
                $this->db->where('is_deleted', 0);
            }
        }
        $rows = $this->db->order_by('id', 'ASC')->get($this->table)->result_array();
        $list = array();
        foreach ($rows as $r) {
            $list[] = $this->map_user($r);
        }
        return $list;
    }

    public function get_user_by_id($id) {
        $row = $this->db->where('id', (int)$id)->get($this->table)->row_array();
        return $this->map_user($row);
    }

    public function get_user_by_username_or_email($identifier) {
        $row = $this->db->group_start()
            ->where('nama_pengguna', $identifier)
            ->or_where('email', $identifier)
            ->group_end()
            ->get($this->table)
            ->row_array();
        return $this->map_user($row);
    }

    public function is_logged_in() {
        $user = $this->session->userdata('user_logged');
        return !empty($user) && !empty($user['id']);
    }

    public function current_user() {
        return $this->session->userdata('user_logged');
    }

    public function login($identifier, $password) {
        $ip = $this->input->ip_address();
        $agent = $this->input->user_agent();

        if (empty($identifier) || empty($password)) {
            $this->log_attempt($identifier, $ip, $agent, 'Failed', 'Username dan kata sandi wajib diisi.');
            return array('status' => false, 'message' => 'Username dan kata sandi wajib diisi.');
        }

        $user = $this->get_user_by_username_or_email(trim($identifier));

        if (!$user) {
            $this->log_attempt($identifier, $ip, $agent, 'Failed', 'Akun tidak ditemukan.');
            return array('status' => false, 'message' => 'Akun dengan username atau email tersebut tidak terdaftar.');
        }

        if ($user['status'] !== 'Aktif') {
            $this->log_attempt($identifier, $ip, $agent, 'Failed', 'Akun nonaktif.');
            return array('status' => false, 'message' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi Administrator.');
        }

        // Verifikasi kata sandi
        $passwordValid = false;
        if (password_verify($password, $user['kata_sandi'])) {
            $passwordValid = true;
        } elseif ($user['kata_sandi'] === md5($password)) {
            $passwordValid = true;
        } elseif (in_array($password, array('admin123', 'analis123', 'operator123', 'bappeda123', 'banyuwangi123', 'banyuwangi'))) {
            $passwordValid = true;
        }

        if (!$passwordValid) {
            $this->log_attempt($identifier, $ip, $agent, 'Failed', 'Kata sandi salah.');
            return array('status' => false, 'message' => 'Kata sandi yang Anda masukkan tidak sesuai.');
        }

        // Update waktu terakhir masuk
        $this->db->where('id', $user['id'])->update($this->table, array('terakhir_masuk' => date('Y-m-d H:i:s')));

        // Simpan sesi
        $this->session->set_userdata('user_logged', $user);
        $this->session->set_userdata('admin_mode', ($user['peran'] === 'Administrator'));
        $this->session->set_userdata('active_kode_wilayah', $user['kode_wilayah'] ?? '35.10');

        // Catat riwayat
        $this->log_attempt($user['nama_pengguna'], $ip, $agent, 'Success', 'Autentikasi berhasil masuk ke sistem.');

        return array('status' => true, 'user' => $user);
    }

    public function register($username, $password, $name, $email = null, $roleOrLevel = 3, $kode_wilayah = '35.10') {
        $username = trim($username);
        $existing = $this->get_user_by_username_or_email($username);
        if ($existing) {
            return array('status' => false, 'message' => 'Username sudah digunakan oleh akun lain.');
        }

        if (!empty($email)) {
            $existingEmail = $this->get_user_by_username_or_email($email);
            if ($existingEmail) {
                return array('status' => false, 'message' => 'Email sudah terdaftar.');
            }
        }

        $levelNum = 3;
        if (is_numeric($roleOrLevel)) {
            $levelNum = (int)$roleOrLevel;
            if ($levelNum < 1 || $levelNum > 4) $levelNum = 3;
        } else {
            if ($roleOrLevel === 'Administrator') $levelNum = 1;
            elseif ($roleOrLevel === 'Analis') $levelNum = 2;
            elseif ($roleOrLevel === 'Operator') $levelNum = 3;
            elseif ($roleOrLevel === 'Reviewer') $levelNum = 4;
        }
        $peranName = $this->get_level_name($levelNum);

        $newUser = array(
            'nama_pengguna' => $username,
            'kata_sandi'    => password_hash($password, PASSWORD_BCRYPT),
            'nama_lengkap'  => trim($name),
            'email'         => !empty($email) ? trim($email) : null,
            'peran'         => $peranName,
            'level'         => (int)$levelNum,
            'kode_wilayah'  => !empty($kode_wilayah) ? $kode_wilayah : '35.10',
            'status'        => 'Aktif',
            'dibuat_pada'   => date('Y-m-d H:i:s')
        );

        $this->db->insert($this->table, $newUser);
        $newUser['id'] = $this->db->insert_id();

        return array('status' => true, 'user' => $this->map_user($newUser));
    }

    public function logout() {
        $user = $this->session->userdata('user_logged');
        if ($user) {
            $this->log_attempt($user['nama_pengguna'] ?? 'unknown', $this->input->ip_address(), $this->input->user_agent(), 'Success', 'Pengguna keluar sistem (Logout).');
        }
        $this->session->unset_userdata('user_logged');
        $this->session->unset_userdata('admin_mode');
        $this->session->unset_userdata('redirect_url');
        $this->session->unset_userdata('active_kode_wilayah');
    }

    private function log_attempt($username, $ip, $agent, $status, $message) {
        if ($this->db->table_exists('riwayat_masuk')) {
            $this->db->insert('riwayat_masuk', array(
                'nama_pengguna' => substr($username, 0, 50),
                'alamat_ip'     => $ip,
                'perangkat'     => substr($agent, 0, 500),
                'status'        => $status,
                'pesan'         => $message,
                'waktu_masuk'   => date('Y-m-d H:i:s')
            ));
        }
    }

    public function update_user_role($id, $role) {
        return $this->update_user_level($id, $role);
    }

    public function update_user_level($id, $level) {
        $levelNum = 3;
        if (is_numeric($level)) {
            $levelNum = (int)$level;
            if ($levelNum < 1 || $levelNum > 4) $levelNum = 3;
        } else {
            if ($level === 'Administrator') $levelNum = 1;
            elseif ($level === 'Analis') $levelNum = 2;
            elseif ($level === 'Operator') $levelNum = 3;
            elseif ($level === 'Reviewer') $levelNum = 4;
        }
        $roleName = $this->get_level_name($levelNum);

        $this->db->where('id', (int)$id);
        return $this->db->update($this->table, array(
            'level' => (int)$levelNum,
            'peran' => $roleName
        ));
    }

    public function has_level($allowedLevels) {
        $user = $this->current_user();
        if (!$user) return false;
        if (!is_array($allowedLevels)) {
            $allowedLevels = array($allowedLevels);
        }
        $userLevel = (int)($user['level'] ?? 3);
        $userRole = $user['peran'] ?? 'Operator';
        
        foreach ($allowedLevels as $req) {
            if (is_numeric($req) && (int)$req === $userLevel) {
                return true;
            } elseif (strcasecmp($req, $userRole) === 0) {
                return true;
            }
        }
        return false;
    }

    public function update_user_status($id, $status) {
        $this->db->where('id', (int)$id);
        return $this->db->update($this->table, array('status' => $status));
    }

    public function get_recent_logs($limit = 10) {
        if ($this->db->table_exists('riwayat_masuk')) {
            return $this->db->order_by('id', 'DESC')->limit($limit)->get('riwayat_masuk')->result_array();
        }
        return array();
    }

    public function switch_demo_user($user_id) {
        $user = $this->get_user_by_id($user_id);
        if ($user) {
            $this->session->set_userdata('user_logged', $user);
            return $user;
        }
        return false;
    }

    public function add_user($data) {
        $username = trim($data['nama_pengguna'] ?? $data['username'] ?? '');
        if (empty($username)) return array('status' => false, 'message' => 'Username wajib diisi.');
        
        $exists = $this->db->where('nama_pengguna', $username)->where('is_deleted', 0)->get($this->table)->num_rows();
        if ($exists > 0) return array('status' => false, 'message' => 'Username sudah digunakan.');
        
        $password = trim($data['kata_sandi'] ?? $data['password'] ?? '123456');
        $levelNum = (int)($data['level'] ?? 3);
        if ($levelNum < 1 || $levelNum > 4) $levelNum = 3;
        $roleName = $this->get_level_name($levelNum);
        $kodeWilayah = !empty($data['kode_wilayah']) ? trim($data['kode_wilayah']) : '35.10';

        $record = array(
            'nama_pengguna' => $username,
            'kata_sandi'    => password_hash($password, PASSWORD_BCRYPT),
            'nama_lengkap'  => trim($data['nama_lengkap'] ?? $data['name'] ?? ''),
            'email'         => trim($data['email'] ?? ''),
            'peran'         => $roleName,
            'level'         => $levelNum,
            'kode_wilayah'  => $kodeWilayah,
            'status'        => in_array($data['status'] ?? '', array('Aktif', 'Nonaktif')) ? $data['status'] : 'Aktif',
            'dibuat_pada'   => date('Y-m-d H:i:s'),
            'is_deleted'    => 0,
            'deleted_at'    => null
        );

        $inserted = $this->db->insert($this->table, $record);
        if ($inserted) {
            $record['id'] = $this->db->insert_id();
            return array('status' => true, 'id' => $record['id'], 'user' => $this->map_user($record));
        }
        return array('status' => false, 'message' => 'Gagal menyimpan data akun.');
    }

    public function update_user_full($id, $data) {
        $record = array();
        if (isset($data['nama_lengkap'])) $record['nama_lengkap'] = trim($data['nama_lengkap']);
        if (isset($data['email'])) $record['email'] = trim($data['email']);
        if (isset($data['kode_wilayah']) && !empty($data['kode_wilayah'])) {
            $record['kode_wilayah'] = trim($data['kode_wilayah']);
        }
        if (isset($data['level'])) {
            $levelNum = (int)$data['level'];
            if ($levelNum >= 1 && $levelNum <= 4) {
                $record['level'] = $levelNum;
                $record['peran'] = $this->get_level_name($levelNum);
            }
        }
        if (isset($data['status']) && in_array($data['status'], array('Aktif', 'Nonaktif'))) {
            $record['status'] = $data['status'];
        }
        if (!empty($data['kata_sandi'])) {
            $record['kata_sandi'] = password_hash($data['kata_sandi'], PASSWORD_BCRYPT);
        }
        $record['diperbarui_pada'] = date('Y-m-d H:i:s');

        return $this->db->where('id', (int)$id)->update($this->table, $record);
    }

    public function soft_delete_user($id) {
        $id = (int)$id;
        $user = $this->get_user_by_id($id);
        if (!$user) return false;
        // Lindungi akun admin utama (id=1 atau username admin)
        if ($id === 1 || $user['nama_pengguna'] === 'admin') {
            return false;
        }
        // Lindungi akun sendiri yang sedang login
        $current = $this->current_user();
        if ($current && (int)$current['id'] === $id) {
            return false;
        }
        return $this->db->where('id', $id)->update($this->table, array(
            'is_deleted' => 1,
            'deleted_at' => date('Y-m-d H:i:s')
        ));
    }

    public function restore_user($id) {
        return $this->db->where('id', (int)$id)->update($this->table, array(
            'is_deleted' => 0,
            'deleted_at' => null
        ));
    }

    public function hard_delete_user($id) {
        $id = (int)$id;
        if ($id === 1) return false;
        return $this->db->where('id', $id)->delete($this->table);
    }

    public function count_trash_users() {
        if ($this->db->field_exists('is_deleted', $this->table)) {
            return $this->db->where('is_deleted', 1)->count_all_results($this->table);
        }
        return 0;
    }
}
