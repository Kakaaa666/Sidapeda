<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Economic_insight extends MY_Controller {

    public function index() {
        $years = $this->M_economy->get_years();
        $series = $this->M_economy->get_economic_series();
        $sectors = $this->M_economy->get_sectors();
        $insights = $this->M_economy->get_economic_insights();

        $data = array(
            'active_page' => 'economic_insight',
            'title' => 'Economic Insight',
            'eyebrow' => 'POLICY INTELLIGENCE',
            'subtitle' => 'Temuan strategis dan rekomendasi kebijakan berbasis bukti data.',
            'years' => $years,
            'series' => $series,
            'sectors' => $sectors,
            'insights' => $insights
        );

        $this->render('economic_insight', $data, false);
    }
}

