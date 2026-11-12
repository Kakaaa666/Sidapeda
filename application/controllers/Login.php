<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'controllers/Akun.php';

/**
 * Controller Login
 * Diteruskan ke Controller Akun untuk kompatibilitas tautan lama /login
 */
class Login extends Akun {
    // Mewarisi semua fungsionalitas dari Akun
}
