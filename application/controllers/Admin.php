<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends MY_Controller {

    public function index() {
        $records = $this->M_economy->get_indicator_records();
        $catalog = $this->M_economy->get_indicator_catalog();
        $ews = $this->M_economy->get_ews_indicators();

        $data = array(
            'active_page' => 'admin',
            'title' => 'Dashboard Admin',
            'eyebrow' => 'ADMINISTRATION CENTER • LOCAL STATE',
            'subtitle' => 'Pusat pengelolaan data dan sistem intelijen ekonomi Banyuwangi.',
            'records' => $records,
            'catalog' => $catalog,
            'ews' => $ews
        );

        $this->render('admin', $data, true);
    }

    public function logout() {
        $this->session->unset_userdata('user_logged');
        $this->session->unset_userdata('admin_mode');
        redirect('overview');
    }
}
