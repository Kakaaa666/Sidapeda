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
        $adminControllers = array('admin', 'database', 'input', 'validation', 'catalog', 'ews_admin', 'forecast_admin', 'users');

        if (in_array($currentClass, $adminControllers)) {
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

            // Menu Pengguna (/users) khusus Level 1 (Administrator)
            if ($currentClass === 'users' && $level !== 1) {
                $this->session->set_flashdata('error', 'Akses ditolak: Menu Manajemen Akun hanya dapat diakses oleh Akun Level 1 (Administrator).');
                redirect('admin');
                exit;
            }
        }
    }

    protected function render($view, $data = array(), $is_admin = false) {
        $data['user'] = $this->M_auth->current_user();
        $data['is_admin'] = $is_admin;
        $data['ews_list'] = $this->M_economy->get_ews_indicators();
        
        $this->load->view('layout/header', $data);
        $this->load->view($view, $data);
        $this->load->view('layout/footer', $data);
    }
}
