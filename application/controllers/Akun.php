<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Akun extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('M_auth');
        $this->M_auth->ensure_default_accounts();
    }

    public function index() {
        // Jika sudah login, langsung arahkan ke Dashboard Admin (Pengelolaan Sistem)
        if ($this->M_auth->is_logged_in()) {
            redirect('admin');
            return;
        }

        $error = null;
        $success = $this->session->flashdata('success');

        // Tangani Request POST
        if ($this->input->method() === 'post') {
            $action = $this->input->post('action') ?: 'login';

            if ($action === 'register') {
                $name = $this->input->post('name', true);
                $username = $this->input->post('username', true);
                $password = $this->input->post('password', true);
                $email = $this->input->post('email', true);
                $level = $this->input->post('level') ?: $this->input->post('role') ?: 3;
                $kode_wilayah = $this->input->post('kode_wilayah', true) ?: '35.10';

                if (empty($username) || empty($password) || empty($name)) {
                    $error = 'Nama lengkap, nama akun/username, dan kata sandi wajib diisi.';
                } else {
                    $reg = $this->M_auth->register($username, $password, $name, $email, $level, $kode_wilayah);
                    if ($reg['status']) {
                        // Otomatis login setelah pendaftaran berhasil
                        $this->M_auth->login($username, $password);
                        $this->session->set_flashdata('success', 'Akun berhasil didaftarkan! Selamat datang, ' . $reg['user']['name']);
                        redirect('admin');
                        return;
                    } else {
                        $error = $reg['message'];
                    }
                }
            } else {
                // Proses Masuk Akun
                $username = $this->input->post('username', true);
                $password = $this->input->post('password', true);

                $result = $this->M_auth->login($username, $password);
                if ($result['status']) {
                    $this->session->set_flashdata('success', 'Selamat datang kembali, ' . $result['user']['name']);

                    // Redirect ke halaman yang semula dituju, atau default ke Dashboard Admin
                    $target = $this->session->userdata('redirect_url');
                    $this->session->unset_userdata('redirect_url');

                    redirect(!empty($target) ? $target : 'admin');
                    return;
                } else {
                    $error = $result['message'];
                }
            }
        }

        $data = array(
            'title' => 'Akun SI-DAPEDA — Masuk Sistem',
            'error' => $error,
            'success' => $success,
            'users' => $this->M_auth->get_users(),
            'kab_kota' => $this->M_auth->get_daftar_kab_kota()
        );

        $this->load->view('akun', $data);
    }

    public function quick($role = 'admin') {
        $passMap = array(
            'admin'      => 'admin123',
            'banyuwangi' => 'banyuwangi123',
            'analis'     => 'analis123',
            'operator'   => 'operator123'
        );

        if (isset($passMap[$role])) {
            $res = $this->M_auth->login($role, $passMap[$role]);
            if ($res['status']) {
                $this->session->set_flashdata('success', 'Berhasil masuk sebagai ' . $res['user']['name'] . ' (' . $res['user']['role'] . ')');
                
                $target = $this->session->userdata('redirect_url');
                $this->session->unset_userdata('redirect_url');
                
                redirect(!empty($target) ? $target : 'admin');
                return;
            }
        }
        redirect('akun');
    }

    public function logout() {
        $this->M_auth->logout();
        $this->session->set_flashdata('success', 'Anda telah berhasil keluar dari akun SI-DAPEDA.');
        redirect('beranda');
    }
}

