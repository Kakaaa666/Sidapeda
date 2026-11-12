<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Early_warning extends MY_Controller {

    public function index() {
        $ews = $this->M_economy->get_ews_indicators();

        $data = array(
            'active_page' => 'early_warning',
            'title' => 'Early Warning System',
            'eyebrow' => 'ECONOMIC RISK MONITORING CENTER',
            'subtitle' => 'Deteksi risiko, pantau ambang batas, dan tindak lanjuti alert ekonomi.',
            'ews' => $ews
        );

        $this->render('early_warning', $data, false);
    }
}

