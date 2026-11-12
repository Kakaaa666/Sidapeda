<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Validation extends MY_Controller {

    public function index() {
        $records = $this->M_economy->get_indicator_records('Perlu review');

        $data = array(
            'active_page' => 'validation',
            'title' => 'Validasi Data',
            'eyebrow' => 'DATA VERIFICATION QUEUE',
            'subtitle' => 'Pemeriksaan dan persetujuan indikator sebelum dipublikasikan.',
            'records' => $records
        );

        $this->render('validation', $data, true);
    }
}

