<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Beranda extends MY_Controller {

    public function index() {
        $status = $this->input->get('status');
        $available_years = $this->M_economy->get_catalog_years();
        $years = $this->M_economy->get_years();

        $raw_year = $this->input->get('year');
        $selected_year = ($raw_year && is_numeric($raw_year)) ? (int)$raw_year : null;
        $selected_category = $this->input->get('category') ?: $this->input->get('prefix');
        $selected_cluster = $this->input->get('cluster') ?: $this->input->get('category');

        $series = $this->M_economy->get_economic_series();
        $sectors = $this->M_economy->get_sectors();
        $ews = $this->M_economy->get_ews_indicators(null, $selected_year);
        $trash_count = $this->M_economy->count_trash_beranda();

        // Pada pemantauan publik EWS, tampilkan seluruh indikator resmi EWS (5 indikator terkurasi)
        $ews = array_values($ews);

        $beranda_cards = $this->M_economy->get_beranda_cards($selected_year, $status);
        $core_kpis = $this->M_economy->get_beranda_kpis_8($selected_year);
        $kpi_details = $this->M_economy->get_kpi_detail_configs_8($selected_year);
        $categories = $this->M_economy->get_beranda_categories($selected_year);

        $active_chart_year = $selected_year ?: (!empty($years) ? max($years) : 2024);
        $year_idx = array_search($active_chart_year, $years);
        if ($year_idx === false) $year_idx = count($years) - 1;
        $prev_idx = max(0, $year_idx - 1);

        $forecast = $this->M_economy->forecast_series($series['growth'], 1, 'Tren Linear');

        $data = array(
            'active_page'       => 'beranda',
            'title'             => 'Dashboard Perekonomian',
            'eyebrow'           => 'EXECUTIVE ECONOMIC OVERVIEW',
            'subtitle'          => 'Pantau perkembangan, analisis, dan prospek perekonomian daerah dalam satu platform intelijen ekonomi terintegrasi.',
            'years'             => $years,
            'available_years'   => $available_years,
            'series'            => $series,
            'sectors'           => $sectors,
            'ews'               => $ews,
            'core_kpis'         => $core_kpis,
            'kpi_details'       => $kpi_details,
            'beranda_cards'     => $beranda_cards,
            'categories'        => $categories,
            'selected_year'     => $selected_year,
            'selected_category' => $selected_category,
            'selected_cluster'  => $selected_cluster,
            'active_chart_year' => $active_chart_year,
            'year_idx'          => $year_idx,
            'prev_idx'          => $prev_idx,
            'forecast'          => $forecast,
            'status'            => $status,
            'trash_count'       => $trash_count
        );

        $this->render('dashboard/beranda', $data, false);
    }

    public function get_data($id = null) {
        $id = (int)($id ?: $this->input->get('id'));
        $record = $this->M_economy->get_beranda_by_id($id);
        if ($record) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'success', 'data' => $record)));
        } else {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(array('status' => 'error', 'message' => 'Data indikator beranda tidak ditemukan.')));
        }
    }

    public function create() {
        $data = $this->input->post(null, true);
        $result = $this->M_economy->add_beranda($data);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Indikator KPI beranda berhasil ditambahkan.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menambahkan indikator beranda: nama indikator wajib diisi.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Indikator KPI beranda berhasil ditambahkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menambahkan data indikator beranda.');
        }
        redirect('beranda');
    }

    public function update($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $data = $this->input->post(null, true);
        $result = $this->M_economy->update_beranda($id, $data);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Data indikator beranda berhasil diperbarui.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal memperbarui data indikator beranda.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data indikator beranda berhasil diperbarui.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memperbarui data indikator beranda.');
        }
        redirect('beranda');
    }

    public function soft_delete($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_economy->soft_delete_beranda($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Indikator beranda berhasil dipindahkan ke tempat sampah (Soft Delete).'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menghapus indikator beranda.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Indikator beranda berhasil dipindahkan ke tempat sampah (Soft Delete).');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus indikator beranda.');
        }
        redirect('beranda');
    }

    public function restore($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_economy->restore_beranda($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Indikator beranda berhasil dipulihkan dari tempat sampah.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal memulihkan data indikator beranda.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Indikator beranda berhasil dipulihkan.');
        } else {
            $this->session->set_flashdata('error', 'Gagal memulihkan data.');
        }
        redirect('beranda?status=Terhapus');
    }

    public function hard_delete($id = null) {
        $id = (int)($id ?: $this->input->post('id'));
        $result = $this->M_economy->hard_delete_beranda($id);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'success',
                    'message' => 'Indikator beranda berhasil dihapus permanen dari basis data.'
                )));
            } else {
                $this->output->set_status_header(400)->set_content_type('application/json')->set_output(json_encode(array(
                    'status' => 'error',
                    'message' => 'Gagal menghapus permanen data indikator beranda.'
                )));
            }
            return;
        }

        if ($result) {
            $this->session->set_flashdata('success', 'Data berhasil dihapus permanen.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus data.');
        }
        redirect('beranda?status=Terhapus');
    }
}

