<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING - SI-DAPEDA
| -------------------------------------------------------------------------
| Controller dan view diberi nama sama persis dengan menu pada antarmuka:
| 1. Beranda             -> Beranda.php           (views/beranda)
| 2. Perbandingan Daerah -> Perbandingan_daerah.php (views/perbandingan_daerah)
| 3. Analisis Ekonomi    -> Analisis_ekonomi.php  (views/analisis_ekonomi)
| 4. Peta Ekonomi        -> Peta_ekonomi.php      (views/peta_ekonomi)
| 5. Prospek Ekonomi     -> Prospek_ekonomi.php   (views/prospek_ekonomi)
| 6. Early Warning       -> Early_warning.php     (views/early_warning)
| 7. Economic Insight    -> Economic_insight.php  (views/economic_insight)
| 8. Laporan Ekonomi     -> Laporan_ekonomi.php   (views/laporan_ekonomi)
*/

$route['default_controller'] = 'Akun';
$route['404_override'] = '';
$route['translate_uri_dashes'] = TRUE;

// Akun & Autentikasi Routes
$route['akun']                = 'Akun';
$route['akun/quick/(:any)']   = 'Akun/quick/$1';
$route['login']               = 'Akun';
$route['login/quick/(:any)']  = 'Akun/quick/$1';

// Public Menu Routes (Sesuai nama menu di sidebar)
$route['beranda']                            = 'Beranda';
$route['perbandingan_daerah']                = 'Perbandingan_daerah';
$route['perbandingan_daerah/(:any)']        = 'Perbandingan_daerah/$1';
$route['perbandingan_daerah/(:any)/(:any)'] = 'Perbandingan_daerah/$1/$2';
$route['perbandingan-daerah']                = 'Perbandingan_daerah';
$route['perbandingan-daerah/(:any)']        = 'Perbandingan_daerah/$1';
$route['perbandingan-daerah/(:any)/(:any)'] = 'Perbandingan_daerah/$1/$2';
$route['analisis_ekonomi']    = 'Analisis_ekonomi';
$route['analisis-ekonomi']    = 'Analisis_ekonomi';
$route['peta_ekonomi']        = 'Peta_ekonomi';
$route['peta-ekonomi']        = 'Peta_ekonomi';
$route['prospek_ekonomi']     = 'Prospek_ekonomi';
$route['prospek-ekonomi']     = 'Prospek_ekonomi';
$route['early_warning']       = 'Early_warning';
$route['early-warning']       = 'Early_warning';
$route['economic_insight']    = 'Economic_insight';
$route['economic-insight']    = 'Economic_insight';
$route['laporan_ekonomi']     = 'Laporan_ekonomi';
$route['laporan-ekonomi']     = 'Laporan_ekonomi';

// Backward compatibility aliases
$route['overview']   = 'Beranda';
$route['comparison'] = 'Perbandingan_daerah';
$route['analysis']   = 'Analisis_ekonomi';
$route['map']        = 'Peta_ekonomi';
$route['outlook']    = 'Prospek_ekonomi';
$route['ews']        = 'Early_warning';
$route['insight']    = 'Economic_insight';
$route['report']     = 'Laporan_ekonomi';

// Admin Navigation Routes
$route['admin']                   = 'Admin';
$route['database']                = 'Database';
$route['database/(:any)']         = 'Database/$1';
$route['database/(:any)/(:any)']  = 'Database/$1/$2';
$route['input']                   = 'Input';
$route['validation']              = 'Validation';
$route['catalog']                 = 'Catalog';
$route['ews_admin']               = 'Ews_admin';
$route['ews-admin']               = 'Ews_admin';
$route['forecast_admin']          = 'Forecast_admin';
$route['forecast-admin']          = 'Forecast_admin';
$route['users']                   = 'Users';
$route['login']                   = 'Login';
$route['logout']                  = 'Logout';
$route['keluar']                  = 'Logout';

// Compatibility aliases for admin/* URLs
$route['admin/database']                = 'Database';
$route['admin/database/(:any)']         = 'Database/$1';
$route['admin/database/(:any)/(:any)']  = 'Database/$1/$2';
$route['admin/input']                   = 'Input';
$route['admin/validation']              = 'Validation';
$route['admin/catalog']                 = 'Catalog';
$route['admin/ews-admin']               = 'Ews_admin';
$route['admin/ews_admin']               = 'Ews_admin';
$route['admin/forecast-admin']          = 'Forecast_admin';
$route['admin/forecast_admin']          = 'Forecast_admin';
$route['admin/users']                   = 'Users';
$route['admin/login']                   = 'Login';
$route['admin/logout']                  = 'Logout';
