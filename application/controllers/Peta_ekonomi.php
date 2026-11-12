<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Peta_ekonomi extends MY_Controller {

    public function index() {
        $districts = $this->M_economy->get_districts();

        $data = array(
            'active_page' => 'peta_ekonomi',
            'title' => 'Peta Ekonomi Daerah',
            'eyebrow' => 'GEOSPATIAL ECONOMIC INTELLIGENCE',
            'subtitle' => 'Temukan pola, potensi, dan kesenjangan ekonomi hingga tingkat kecamatan.',
            'districts' => $districts
        );

        $this->render('peta_ekonomi', $data, false);
    }
}

