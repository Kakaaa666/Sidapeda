<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Analisis_ekonomi extends MY_Controller {

    public function index() {
        $years = $this->M_economy->get_years();
        $series = $this->M_economy->get_economic_series();
        $sectors = $this->M_economy->get_sectors();

        $lq = $this->M_economy->calculate_lq($sectors);
        $shift_share = $this->M_economy->calculate_shift_share($sectors);
        $klassen = $this->M_economy->calculate_klassen($sectors, end($series['growth']));

        $data = array(
            'active_page' => 'analisis_ekonomi',
            'title' => 'Eksplorasi & Analisis Ekonomi',
            'eyebrow' => 'BUSINESS INTELLIGENCE WORKSPACE',
            'subtitle' => 'Jalankan analisis sektoral, korelasi, dan metode ekonomi regional menggunakan data contoh.',
            'years' => $years,
            'series' => $series,
            'sectors' => $sectors,
            'lq' => $lq,
            'shift_share' => $shift_share,
            'klassen' => $klassen
        );

        $this->render('analisis_ekonomi', $data, false);
    }
}

