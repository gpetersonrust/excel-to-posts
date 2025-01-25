<?php

/**
 * Plugin Name: Excels to Posts
 * Page: Utils
 * Description: Utility functions for the Excels to Posts plugin
 * Author: Gino Peterosn
 * Author URI: https://moxcar.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */


 class ExcelToPostsUtils {
     public function __construct($dir, $url) {
         $this->dir = $dir;
         $this->url = $url;

        $this->error_dir = $this->dir . 'errors/';

        // if errors directory does not exist, create it
        if ( ! file_exists( $this->error_dir ) ) {
            mkdir( $this->error_dir );
        }
        
     }

        public function log_error($fileName,$errorMessage, ) {
            $file = $this->error_dir . $fileName;
            $error = date('Y-m-d H:i:s') . ' - ' . $errorMessage . "\n";
            file_put_contents($file, $error, FILE_APPEND);
        }

    // check if file exists and if not log to error file and return false
       public function check_file_exists($file) {
        if ( ! file_exists( $file ) ) {
            $this->log_error('file-errors.log', 'File does not exist: ' . $file);
            return false;
        }
        return true;
    }

     

    // class exists function
    public function check_class_exists($class) {
        if ( ! class_exists( $class ) ) {
            $this->log_error('class-errors.log', 'Class does not exist: ' . $class);
            return false;
        }
        return true;
    }
 }