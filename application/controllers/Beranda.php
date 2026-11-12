<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Beranda extends MY_Controller {

    public function index() {
        $years = $this->M_economy->get_years();
        $series = $this->M_economy->get_economic_series();
        $sectors = $this->M_economy->get_sectors();
        $ews = $this->M_economy->get_ews_indicators();
        $beranda_cards = $this->M_economy->get_beranda_cards();

        $selected_year = (int)$this->input->get('year') ?: 2024;
        if (!in_array($selected_year, $years)) $selected_year = 2024;
        
        $year_idx = array_search($selected_year, $years);
        if ($year_idx === false) $year_idx = count($years) - 1;
        $prev_idx = max(0, $year_idx - 1);

        $forecast = $this->M_economy->forecast_series($series['growth'], 1, 'Tren Linear');

        $data = array(
            'active_page' => 'beranda',
            'title' => 'Dashboard Perekonomian',
            'eyebrow' => 'EXECUTIVE ECONOMIC OVERVIEW',
            'subtitle' => 'Pantau perkembangan, analisis, dan prospek perekonomian daerah dalam satu platform intelijen ekonomi terintegrasi.',
            'years' => $years,
            'series' => $series,
            'sectors' => $sectors,
            'ews' => $ews,
            'beranda_cards' => $beranda_cards,
            'selected_year' => $selected_year,
            'year_idx' => $year_idx,
            'prev_idx' => $prev_idx,
            'forecast' => $forecast
        );

        $this->render('beranda', $data, false);
    }
}

