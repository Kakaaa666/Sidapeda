<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Input extends MY_Controller {

    public function index() {
        $data = array(
            'active_page' => 'input',
            'title' => 'Input & Import Data',
            'eyebrow' => 'LOCAL CSV & MANUAL INPUT',
            'subtitle' => 'Tambah indikator baru secara manual atau upload file CSV.',
            'catalog' => $this->M_economy->get_indicator_catalog()
        );

        $this->render('input', $data, true);
    }
}

