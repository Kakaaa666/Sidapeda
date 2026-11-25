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

$route['default_controller'] = 'dashboard/Beranda';
$route['404_override'] = '';
$route['translate_uri_dashes'] = TRUE;

// Akun & Autentikasi Routes
$route['akun']                = 'Akun';
$route['akun/quick/(:any)']   = 'Akun/quick/$1';
$route['login']               = 'Login';
$route['login/quick/(:any)']  = 'Login/quick/$1';
$route['logout']              = 'Logout';
$route['keluar']              = 'Logout';

// Public Menu Routes (Sesuai nama menu di sidebar - diarahkan ke controller dashboard/)
$route['beranda']                            = 'dashboard/Beranda';
$route['beranda/(:any)']                     = 'dashboard/Beranda/$1';
$route['beranda/(:any)/(:any)']              = 'dashboard/Beranda/$1/$2';

$route['perbandingan_daerah']                = 'dashboard/Perbandingan_daerah';
$route['perbandingan_daerah/(:any)']        = 'dashboard/Perbandingan_daerah/$1';
$route['perbandingan_daerah/(:any)/(:any)'] = 'dashboard/Perbandingan_daerah/$1/$2';
$route['perbandingan-daerah']                = 'dashboard/Perbandingan_daerah';
$route['perbandingan-daerah/(:any)']        = 'dashboard/Perbandingan_daerah/$1';
$route['perbandingan-daerah/(:any)/(:any)'] = 'dashboard/Perbandingan_daerah/$1/$2';

$route['analisis_ekonomi']                   = 'dashboard/Analisis_ekonomi';
$route['analisis_ekonomi/(:any)']           = 'dashboard/Analisis_ekonomi/$1';
$route['analisis_ekonomi/(:any)/(:any)']    = 'dashboard/Analisis_ekonomi/$1/$2';
$route['analisis-ekonomi']                   = 'dashboard/Analisis_ekonomi';
$route['analisis-ekonomi/(:any)']           = 'dashboard/Analisis_ekonomi/$1';
$route['analisis-ekonomi/(:any)/(:any)']    = 'dashboard/Analisis_ekonomi/$1/$2';

$route['peta_ekonomi']                       = 'dashboard/Peta_ekonomi';
$route['peta_ekonomi/(:any)']               = 'dashboard/Peta_ekonomi/$1';
$route['peta_ekonomi/(:any)/(:any)']        = 'dashboard/Peta_ekonomi/$1/$2';
$route['peta-ekonomi']                       = 'dashboard/Peta_ekonomi';
$route['peta-ekonomi/(:any)']               = 'dashboard/Peta_ekonomi/$1';
$route['peta-ekonomi/(:any)/(:any)']        = 'dashboard/Peta_ekonomi/$1/$2';

$route['prospek_ekonomi']                    = 'dashboard/Prospek_ekonomi';
$route['prospek_ekonomi/(:any)']            = 'dashboard/Prospek_ekonomi/$1';
$route['prospek_ekonomi/(:any)/(:any)']     = 'dashboard/Prospek_ekonomi/$1/$2';
$route['prospek-ekonomi']                    = 'dashboard/Prospek_ekonomi';
$route['prospek-ekonomi/(:any)']            = 'dashboard/Prospek_ekonomi/$1';
$route['prospek-ekonomi/(:any)/(:any)']     = 'dashboard/Prospek_ekonomi/$1/$2';

$route['early_warning']                      = 'dashboard/Early_warning';
$route['early_warning/(:any)']              = 'dashboard/Early_warning/$1';
$route['early_warning/(:any)/(:any)']       = 'dashboard/Early_warning/$1/$2';
$route['early-warning']                      = 'dashboard/Early_warning';
$route['early-warning/(:any)']              = 'dashboard/Early_warning/$1';
$route['early-warning/(:any)/(:any)']       = 'dashboard/Early_warning/$1/$2';

$route['economic_insight']                   = 'dashboard/Economic_insight';
$route['economic_insight/(:any)']           = 'dashboard/Economic_insight/$1';
$route['economic_insight/(:any)/(:any)']    = 'dashboard/Economic_insight/$1/$2';
$route['economic-insight']                   = 'dashboard/Economic_insight';
$route['economic-insight/(:any)']           = 'dashboard/Economic_insight/$1';
$route['economic-insight/(:any)/(:any)']    = 'dashboard/Economic_insight/$1/$2';

$route['laporan_ekonomi']                    = 'dashboard/Laporan_ekonomi';
$route['laporan_ekonomi/(:any)']            = 'dashboard/Laporan_ekonomi/$1';
$route['laporan_ekonomi/(:any)/(:any)']     = 'dashboard/Laporan_ekonomi/$1/$2';
$route['laporan-ekonomi']                    = 'dashboard/Laporan_ekonomi';
$route['laporan-ekonomi/(:any)']            = 'dashboard/Laporan_ekonomi/$1';
$route['laporan-ekonomi/(:any)/(:any)']     = 'dashboard/Laporan_ekonomi/$1/$2';

// Backward compatibility aliases
$route['overview']   = 'dashboard/Beranda';
$route['comparison'] = 'dashboard/Perbandingan_daerah';
$route['analysis']   = 'dashboard/Analisis_ekonomi';
$route['map']        = 'dashboard/Peta_ekonomi';
$route['outlook']    = 'dashboard/Prospek_ekonomi';
$route['ews']        = 'dashboard/Early_warning';
$route['insight']    = 'dashboard/Economic_insight';
$route['report']     = 'dashboard/Laporan_ekonomi';

// Admin Navigation Routes (Yang Sudah Login - diarahkan ke controller admin/)
// Submodule routes spesifik (baik dengan prefix admin/ maupun tanpa prefix)
$route['admin/validation']                = 'admin/Validation';
$route['admin/validation/(:any)']         = 'admin/Validation/$1';
$route['admin/validation/(:any)/(:any)']  = 'admin/Validation/$1/$2';
$route['validation']                      = 'admin/Validation';
$route['validation/(:any)']               = 'admin/Validation/$1';
$route['validation/(:any)/(:any)']        = 'admin/Validation/$1/$2';

$route['admin/database']                  = 'admin/Database';
$route['admin/database/(:any)']           = 'admin/Database/$1';
$route['admin/database/(:any)/(:any)']    = 'admin/Database/$1/$2';
$route['database']                        = 'admin/Database';
$route['database/(:any)']                 = 'admin/Database/$1';
$route['database/(:any)/(:any)']          = 'admin/Database/$1/$2';
$route['database_ekonomi']                = 'admin/Database';
$route['database_ekonomi/(:any)']         = 'admin/Database/$1';
$route['database_ekonomi/(:any)/(:any)']  = 'admin/Database/$1/$2';
$route['database-ekonomi']                = 'admin/Database';
$route['database-ekonomi/(:any)']         = 'admin/Database/$1';
$route['database-ekonomi/(:any)/(:any)']  = 'admin/Database/$1/$2';

$route['admin/input']                     = 'admin/Input';
$route['admin/input/(:any)']              = 'admin/Input/$1';
$route['admin/input/(:any)/(:any)']       = 'admin/Input/$1/$2';
$route['input']                           = 'admin/Input';
$route['input/(:any)']                    = 'admin/Input/$1';
$route['input/(:any)/(:any)']             = 'admin/Input/$1/$2';

$route['admin/catalog']                   = 'admin/Catalog';
$route['admin/catalog/(:any)']            = 'admin/Catalog/$1';
$route['admin/catalog/(:any)/(:any)']     = 'admin/Catalog/$1/$2';
$route['catalog']                         = 'admin/Catalog';
$route['catalog/(:any)']                  = 'admin/Catalog/$1';
$route['catalog/(:any)/(:any)']           = 'admin/Catalog/$1/$2';
$route['katalog_indikator']               = 'admin/Catalog';
$route['katalog_indikator/(:any)']        = 'admin/Catalog/$1';
$route['katalog_indikator/(:any)/(:any)'] = 'admin/Catalog/$1/$2';
$route['katalog-indikator']               = 'admin/Catalog';
$route['katalog-indikator/(:any)']        = 'admin/Catalog/$1';
$route['katalog-indikator/(:any)/(:any)'] = 'admin/Catalog/$1/$2';

$route['admin/ews-admin']                 = 'admin/Ews_admin';
$route['admin/ews-admin/(:any)']          = 'admin/Ews_admin/$1';
$route['admin/ews-admin/(:any)/(:any)']   = 'admin/Ews_admin/$1/$2';
$route['admin/ews_admin']                 = 'admin/Ews_admin';
$route['admin/ews_admin/(:any)']          = 'admin/Ews_admin/$1';
$route['admin/ews_admin/(:any)/(:any)']   = 'admin/Ews_admin/$1/$2';
$route['ews_admin']                       = 'admin/Ews_admin';
$route['ews_admin/(:any)']                = 'admin/Ews_admin/$1';
$route['ews_admin/(:any)/(:any)']         = 'admin/Ews_admin/$1/$2';
$route['ews-admin']                       = 'admin/Ews_admin';
$route['ews-admin/(:any)']                = 'admin/Ews_admin/$1';
$route['ews-admin/(:any)/(:any)']         = 'admin/Ews_admin/$1/$2';

$route['admin/forecast-admin']            = 'admin/Forecast_admin';
$route['admin/forecast-admin/(:any)']     = 'admin/Forecast_admin/$1';
$route['admin/forecast-admin/(:any)/(:any)'] = 'admin/Forecast_admin/$1/$2';
$route['admin/forecast_admin']            = 'admin/Forecast_admin';
$route['admin/forecast_admin/(:any)']     = 'admin/Forecast_admin/$1';
$route['admin/forecast_admin/(:any)/(:any)'] = 'admin/Forecast_admin/$1/$2';
$route['forecast_admin']                  = 'admin/Forecast_admin';
$route['forecast_admin/(:any)']           = 'admin/Forecast_admin/$1';
$route['forecast_admin/(:any)/(:any)']    = 'admin/Forecast_admin/$1/$2';
$route['forecast-admin']                  = 'admin/Forecast_admin';
$route['forecast-admin/(:any)']           = 'admin/Forecast_admin/$1';
$route['forecast-admin/(:any)/(:any)']    = 'admin/Forecast_admin/$1/$2';

$route['admin/users']                     = 'admin/Users';
$route['admin/users/(:any)']              = 'admin/Users/$1';
$route['admin/users/(:any)/(:any)']       = 'admin/Users/$1/$2';
$route['users']                           = 'admin/Users';
$route['users/(:any)']                    = 'admin/Users/$1';
$route['users/(:any)/(:any)']             = 'admin/Users/$1/$2';

$route['admin/logout']                    = 'admin/Admin/logout';
$route['admin']                           = 'admin/Admin';
$route['admin/(:any)']                    = 'admin/Admin/$1';
