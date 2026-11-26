<?php
define('BASEPATH', 'C:/xampp/htdocs/Sidapeda/system/');
require_once 'C:/xampp/htdocs/Sidapeda/index.php';
$CI =& get_instance();
$CI->load->model('M_economy');
$res = $CI->M_economy->get_sectoral_matrix_data('HRG-002', '35.10');
echo "is_monthly_group: " . var_export($res['is_monthly_group'], true) . "\n";
echo "years: " . json_encode($res['years']) . "\n";
echo "definitions count: " . count($res['definitions']) . "\n";
echo "monthly_values count: " . count($res['monthly_values']) . "\n";
