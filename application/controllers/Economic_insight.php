<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Economic_insight extends MY_Controller {

    public function index() {
        $status = $this->input->get('status');
        $years = $this->M_economy->get_years();
        $series = $this->M_economy->get_economic_series();
        $sectors = $this->M_economy->get_sectors();
        $insights = $this->M_economy->get_economic_insights(null, $status);
        $trash_count = $this->M_economy->count_trash_insights();

        $data = array(
            'active_page' => 'economic_insight',
            'title' => 'Economic Insight',
            'eyebrow' => 'POLICY INTELLIGENCE • CRUD & SOFT DELETE',
            'subtitle' => 'Temuan strategis, rekomendasi kebijakan, dan kelola dataset intelijen kebijakan ekonomi daerah.',
            'years' => $years,
            'series' => $series,
            'sectors' => $sectors,
            'insights' => $insights,
            'status' => $status,
            'trash_count' => $trash_count
        );

        $this->render('economic_insight', $data, false);
    }

    public function get_data($id = null) {
        $id = (int)($id ?: $this->input->get('id'));
        $record = $this->M_economy->get_insight_by_id($id);
        if ($record) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'success', 'data' => $record)));
        } else {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Insight kebijakan tidak ditemukan.')));
        }
    }

    public function create() {
        $data = $this->input->post(null, true);
        $result = $this->M_economy->add_insight($data);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Insight & rekomendasi kebijakan baru berhasil ditambahkan.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menambahkan insight: judul insight wajib diisi.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Insight kebijakan berhasil ditambahkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan insight.');
        }
        redirect('economic_insight');
    }

    public function update($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $data = $this->input->post(null, true);
        $result = $this->M_economy->update_insight($id, $data);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Data insight kebijakan berhasil diperbarui.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal memperbarui insight kebijakan.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data insight kebijakan berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data.');
        }
        redirect('economic_insight');
    }

    public function soft_delete($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_economy->soft_delete_insight($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Insight berhasil dipindahkan ke tempat sampah (Soft Delete).'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menghapus insight.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Insight berhasil dipindahkan ke tempat sampah (Soft Delete).');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus insight.');
        }
        redirect('economic_insight');
    }

    public function restore($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_economy->restore_insight($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Insight kebijakan berhasil dipulihkan dari tempat sampah.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal memulihkan insight kebijakan.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Insight kebijakan berhasil dipulihkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memulihkan data insight.');
        }
        redirect('economic_insight?status=Terhapus');
    }

    public function hard_delete($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_economy->hard_delete_insight($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Insight berhasil dihapus permanen dari basis data.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menghapus permanen data insight.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data insight berhasil dihapus permanen.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data.');
        }
        redirect('economic_insight?status=Terhapus');
    }
}

