<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends MY_Controller {

    public function index() {
        $raw_year = $this->input->get('year');
        $selected_year = ($raw_year && is_numeric($raw_year)) ? (int)$raw_year : null;

        $records = $this->M_economy->get_indicator_records(null, null, 'tahun', $selected_year);
        $catalog = $this->M_economy->get_indicator_catalog(null, $selected_year);
        $ews = $this->M_economy->get_ews_indicators(null, $selected_year);
        $available_years = $this->M_economy->get_catalog_years();

        $data = array(
            'active_page'     => 'admin',
            'title'           => 'Dashboard Admin',
            'eyebrow'         => 'ADMINISTRATION CENTER • LOCAL STATE',
            'subtitle'        => 'Pusat pengelolaan data dan sistem intelijen ekonomi Banyuwangi.',
            'records'         => $records,
            'catalog'         => $catalog,
            'ews'             => $ews,
            'available_years' => $available_years,
            'selected_year'   => $selected_year
        );

        $this->render('admin/admin', $data, true);
    }

    public function logout() {
        $this->session->unset_userdata('user_logged');
        $this->session->unset_userdata('admin_mode');
        redirect('overview');
    }
}
