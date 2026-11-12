<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Perbandingan_daerah extends MY_Controller {

    public function index() {
        $years = $this->M_economy->get_years();
        $regional = $this->M_economy->get_regional_growth();
        $series = $this->M_economy->get_economic_series();
        $sectors = $this->M_economy->get_sectors();

        $data = array(
            'active_page' => 'perbandingan_daerah',
            'title' => 'Perbandingan Antar Daerah',
            'eyebrow' => 'REGIONAL BENCHMARKING',
            'subtitle' => 'Bandingkan posisi dan daya saing ekonomi Banyuwangi dengan wilayah setara.',
            'years' => $years,
            'regional' => $regional,
            'series' => $series,
            'sectors' => $sectors
        );

        $this->render('perbandingan_daerah', $data, false);
    }
}

