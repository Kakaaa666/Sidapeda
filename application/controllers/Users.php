<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller {

    public function index() {
        $users = $this->M_auth->get_users();
        $levels = $this->M_auth->get_levels();
        $logs = $this->M_auth->get_recent_logs(8);

        $data = array(
            'active_page' => 'users',
            'title' => 'Pengguna & Level Akun',
            'eyebrow' => 'USER LEVEL & ACCESS CONTROL',
            'subtitle' => 'Daftar akun terdaftar, hierarki level akses (Level 1–4), dan audit riwayat autentikasi.',
            'users' => $users,
            'levels' => $levels,
            'logs' => $logs
        );

        $this->render('users', $data, true);
    }
}

