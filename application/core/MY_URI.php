<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_URI extends CI_URI {

    protected function _parse_request_uri() {
        if (!isset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'])) {
            return '';
        }

        $uri = parse_url('http://dummy' . $_SERVER['REQUEST_URI']);
        $query = isset($uri['query']) ? $uri['query'] : '';
        $uri = isset($uri['path']) ? $uri['path'] : '';

        if (isset($_SERVER['SCRIPT_NAME'][0])) {
            if (stripos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
                $uri = (string) substr($uri, strlen($_SERVER['SCRIPT_NAME']));
            } else {
                $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
                if ($dir !== '/' && $dir !== '.' && stripos($uri, $dir) === 0) {
                    $uri = (string) substr($uri, strlen($dir));
                }
            }
        }

        // Handle case where index.php is still at the beginning
        if (stripos($uri, '/index.php') === 0) {
            $uri = (string) substr($uri, 10);
        }

        return $this->_remove_relative_directory($uri);
    }
}
