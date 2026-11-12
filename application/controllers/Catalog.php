<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Catalog extends MY_Controller {

    public function index() {
        $catalog = $this->M_economy->get_indicator_catalog();

        $data = array(
            'active_page' => 'catalog',
            'title' => 'Katalog Indikator',
            'eyebrow' => 'DATA GOVERNANCE & CATALOG',
            'subtitle' => 'Kamus data dan metadata indikator pembangunan daerah.',
            'catalog' => $catalog
        );

        $this->render('catalog', $data, true);
    }
}

