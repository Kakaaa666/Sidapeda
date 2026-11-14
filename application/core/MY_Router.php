<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Router extends CI_Router {

    protected function _set_default_controller() {
        if (empty($this->default_controller)) {
            show_error('Unable to determine what should be displayed. A default route has not been specified in the routing file.');
        }

        $controller = $this->default_controller;
        $method = 'index';

        if (sscanf($controller, '%[^/]/%s', $class, $sub) === 2) {
            // Check if $class is a subdirectory
            if (is_dir(APPPATH . 'controllers/' . $class)) {
                $this->set_directory($class);
                $segments = explode('/', $sub);
                $class = array_shift($segments);
                $method = !empty($segments) ? array_shift($segments) : 'index';
            } else {
                $method = $sub;
            }
        }

        if (!file_exists(APPPATH . 'controllers/' . $this->directory . ucfirst($class) . '.php')) {
            return;
        }

        $this->set_class($class);
        $this->set_method($method);

        $this->uri->rsegments = array(
            1 => $class,
            2 => $method
        );

        log_message('debug', 'No URI present. Default controller set.');
    }
}

