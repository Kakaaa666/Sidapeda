<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ews_admin extends MY_Controller {

    public function index() {
        // Sinkronkan seluruh data yang sudah tervalidasi ke EWS
        $this->M_economy->sync_all_validated_to_ews();

        $raw_year = $this->input->get('year');
        $selected_year = ($raw_year && is_numeric($raw_year)) ? (int)$raw_year : null;

        $ews = $this->M_economy->get_ews_indicators(null, $selected_year);
        $available_years = $this->M_economy->get_catalog_years();

        $data = array(
            'active_page'     => 'ews_admin',
            'title'           => 'Manajemen EWS',
            'eyebrow'         => 'EARLY WARNING CONFIGURATION • DATA TERVALIDASI',
            'subtitle'        => 'Konfigurasi ambang batas risiko ekonomi daerah terpadu berbasis data tervalidasi.',
            'ews'             => $ews,
            'available_years' => $available_years,
            'selected_year'   => $selected_year
        );

        $this->render('ews_admin', $data, true);
    }
}
