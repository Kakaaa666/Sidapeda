<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Prospek_ekonomi extends MY_Controller {

    public function index() {
        $years = $this->M_economy->get_years();
        $series = $this->M_economy->get_economic_series();
        $sectors = $this->M_economy->get_sectors();

        $data = array(
            'active_page' => 'prospek_ekonomi',
            'title' => 'Prospek Perekonomian',
            'eyebrow' => 'FORECASTING & SCENARIO',
            'subtitle' => 'Uji tren historis dan skenario ekonomi menggunakan metode proyeksi sederhana.',
            'years' => $years,
            'series' => $series,
            'sectors' => $sectors
        );

        $this->render('prospek_ekonomi', $data, false);
    }
}

