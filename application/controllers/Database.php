<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Database extends MY_Controller {

    public function index() {
        $status = $this->input->get('status');
        $query = $this->input->get('q');
        $sort = $this->input->get('sort') ?: 'year';

        $records = $this->M_economy->get_indicator_records($status, $query, $sort);

        $data = array(
            'active_page' => 'database',
            'title' => 'Database Ekonomi',
            'eyebrow' => 'DATA MANAGEMENT • DATABASE PERSISTENT',
            'subtitle' => 'Kelola seluruh rekaman indikator ekonomi daerah.',
            'records' => $records,
            'status' => $status,
            'query' => $query,
            'sort' => $sort
        );

        $this->render('database', $data, true);
    }
}

