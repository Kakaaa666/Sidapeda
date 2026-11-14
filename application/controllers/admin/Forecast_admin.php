<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Forecast_admin extends MY_Controller {

    public function index() {
        $data = array(
            'active_page' => 'forecast_admin',
            'title' => 'Konfigurasi Proyeksi',
            'eyebrow' => 'FORECAST ENGINE CONFIGURATION',
            'subtitle' => 'Pengaturan parameter algoritma proyeksi dan horizon estimasi.'
        );

        $this->render('admin/forecast_admin', $data, true);
    }
}
