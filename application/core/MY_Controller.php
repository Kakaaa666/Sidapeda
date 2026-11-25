<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('M_economy');
        $this->load->model('M_auth');

        // Pastikan akun awal siap di database
        $this->M_auth->ensure_default_accounts();

        // Autentikasi: Hanya modul Pengelolaan Sistem (Admin) yang mewajibkan login
        $currentClass = strtolower($this->router->fetch_class());
        $currentDir = trim(str_replace('\\', '/', (string)$this->router->directory), '/');
        $adminControllers = array('admin', 'database', 'input', 'validation', 'catalog', 'ews_admin', 'forecast_admin', 'users');

        if (in_array($currentClass, $adminControllers) || $currentDir === 'admin') {
            if (!$this->M_auth->is_logged_in()) {
                // Simpan URL yang dituju untuk kembali setelah login
                $intended = uri_string();
                if (!empty($intended) && $intended !== 'akun' && $intended !== 'login' && $intended !== 'logout') {
                    $this->session->set_userdata('redirect_url', $intended);
                }
                redirect('akun');
                exit;
            }

            // Pengecekan Hak Akses Berdasarkan Level Akun (Angka: 1 = Administrator, 2 = Analis, 3 = Operator, 4 = Reviewer)
            $user = $this->M_auth->current_user();
            $level = (int)($user['level'] ?? 3);

            // 1. Menu Pengguna (/users) khusus Level 1 (Administrator)
            if ($currentClass === 'users' && $level !== 1) {
                $this->session->set_flashdata('error', 'Akses ditolak: Menu Manajemen Akun hanya dapat diakses oleh Akun Level 1 (Administrator).');
                redirect('admin');
                exit;
            }

            // 2. Menu Import Data (/input) khusus Level 1 (Administrator) dan Level 3 (Operator)
            if ($currentClass === 'input' && !in_array($level, array(1, 3))) {
                $this->session->set_flashdata('error', 'Akses ditolak: Fitur Import Data diperuntukkan bagi Operator Data dan Administrator.');
                redirect('admin');
                exit;
            }

            // 3. Konfigurasi Proyeksi (/forecast_admin) khusus Level 1 (Administrator) dan Level 2 (Analis)
            if ($currentClass === 'forecast_admin' && !in_array($level, array(1, 2))) {
                $this->session->set_flashdata('error', 'Akses ditolak: Konfigurasi Proyeksi hanya dapat diakses oleh Analis dan Administrator.');
                redirect('admin');
                exit;
            }

            // 4. Manajemen EWS (/ews_admin) untuk Level 1, 2, dan 4 (Reviewer eksekutif)
            if ($currentClass === 'ews_admin' && !in_array($level, array(1, 2, 4))) {
                $this->session->set_flashdata('error', 'Akses ditolak: Menu Manajemen EWS hanya dapat diakses oleh Analis, Reviewer, dan Administrator.');
                redirect('admin');
                exit;
            }
        }
    }

    protected function render($view, $data = array(), $is_admin = false) {
        $data['user'] = $this->M_auth->current_user();
        $data['is_admin'] = $is_admin;
        $data['active_kode_wilayah'] = $this->M_economy->get_active_kode_wilayah();
        $data['nama_wilayah'] = $this->M_economy->get_nama_wilayah($data['active_kode_wilayah']);
        $data['ews_list'] = $this->M_economy->get_ews_indicators();
        
        $this->load->view('layout/header', $data);

        // Resolusi folder view: dashboard/ atau admin/ atau path langsung
        if (file_exists(APPPATH . 'views/' . $view . '.php')) {
            $this->load->view($view, $data);
        } elseif (file_exists(APPPATH . 'views/dashboard/' . $view . '.php')) {
            $this->load->view('dashboard/' . $view, $data);
        } elseif (file_exists(APPPATH . 'views/admin/' . $view . '.php')) {
            $this->load->view('admin/' . $view, $data);
        } else {
            $this->load->view($view, $data);
        }

        $this->load->view('layout/footer', $data);
    }
}
