<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends MY_Controller {

    public function index() {
        $status = $this->input->get('status');
        $users = $this->M_auth->get_users($status);
        $levels = $this->M_auth->get_levels();
        $logs = $this->M_auth->get_recent_logs(8);
        $trash_count = $this->M_auth->count_trash_users();

        $data = array(
            'active_page' => 'users',
            'title' => 'Pengguna & Level Akun',
            'eyebrow' => 'USER LEVEL & ACCESS CONTROL • CRUD & SOFT DELETE',
            'subtitle' => 'Daftar akun terdaftar, hierarki level akses (Level 1–4), audit riwayat autentikasi, dan manajemen akun.',
            'users' => $users,
            'levels' => $levels,
            'logs' => $logs,
            'status' => $status,
            'trash_count' => $trash_count
        );

        $this->render('admin/users', $data, true);
    }

    public function get_data($id = null) {
        $id = (int)($id ?: $this->input->get('id'));
        $record = $this->M_auth->get_user_by_id($id);
        if ($record) {
            unset($record['password'], $record['kata_sandi']);
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'success', 'data' => $record)));
        } else {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Akun tidak ditemukan.')));
        }
    }

    public function create() {
        $data = $this->input->post(null, true);
        $result = $this->M_auth->add_user($data);

        if ($this->input->is_ajax_request()) {
            if ($result && !empty($result['status'])) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Akun baru berhasil didaftarkan.'
                )));
            } else {
                $msg = $result['message'] ?? 'Gagal mendaftarkan akun.';
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => $msg
                )));
            }
            return;
        }

        if ($result && !empty($result['status'])) {
            $this->session->set_flashdata('success', 'Akun baru berhasil didaftarkan.');
        } else {
            $this->session->set_flashdata('error', $result['message'] ?? 'Gagal menambahkan akun.');
        }
        redirect('users');
    }

    public function update($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $data = $this->input->post(null, true);
        $result = $this->M_auth->update_user_full($id, $data);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Data akun berhasil diperbarui.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal memperbarui data akun.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data akun berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data akun.');
        }
        redirect('users');
    }

    public function soft_delete($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_auth->soft_delete_user($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Akun berhasil dipindahkan ke tempat sampah (Soft Delete).'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menghapus akun: Akun Administrator utama atau akun yang sedang aktif tidak dapat dihapus.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Akun berhasil dipindahkan ke tempat sampah (Soft Delete).');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus akun: Akun Administrator utama atau akun aktif dilindungi.');
        }
        redirect('users');
    }

    public function restore($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_auth->restore_user($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Akun berhasil dipulihkan dari tempat sampah.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal memulihkan akun.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Akun berhasil dipulihkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memulihkan akun.');
        }
        redirect('users?status=Terhapus');
    }

    public function hard_delete($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_auth->hard_delete_user($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Akun berhasil dihapus permanen dari basis data.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menghapus permanen akun.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Akun berhasil dihapus permanen.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus akun.');
        }
        redirect('users?status=Terhapus');
    }
}

