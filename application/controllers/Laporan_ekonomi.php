<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan_ekonomi extends MY_Controller {

    public function index() {
        $years = $this->M_economy->get_years();
        $series = $this->M_economy->get_economic_series();
        $sectors = $this->M_economy->get_sectors();
        $reports = $this->M_economy->get_laporan_ekonomi();

        $data = array(
            'active_page' => 'laporan_ekonomi',
            'title' => 'Generator Laporan Ekonomi',
            'eyebrow' => 'AUTOMATED REPORTING',
            'subtitle' => 'Susun laporan analitis terstandardisasi dengan cepat dan konsisten.',
            'years' => $years,
            'series' => $series,
            'sectors' => $sectors,
            'reports' => $reports
        );

        $this->render('laporan_ekonomi', $data, false);
    }
}

