<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ews_admin extends MY_Controller {

    public function index() {
        $ews = $this->M_economy->get_ews_indicators();

        $data = array(
            'active_page' => 'ews_admin',
            'title' => 'Manajemen EWS',
            'eyebrow' => 'EARLY WARNING CONFIGURATION',
            'subtitle' => 'Konfigurasi ambang batas risiko ekonomi daerah terpadu.',
            'ews' => $ews
        );

        $this->render('ews_admin', $data, true);
    }
}

