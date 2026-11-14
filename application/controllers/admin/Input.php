<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Input extends MY_Controller {

    public function index() {
        $data = array(
            'active_page' => 'input',
            'title' => 'Import Data CSV',
            'eyebrow' => 'DATA INGESTION • BATCH CSV IMPORT',
            'subtitle' => 'Unggah dan impor berkas dataset indikator ekonomi daerah secara massal melalui format CSV.',
            'catalog' => $this->M_economy->get_indicator_catalog()
        );

        $this->render('admin/input', $data, true);
    }
}

