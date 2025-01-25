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



    // upload_image_from_url( 
    public function upload_image_from_url($url) {
        $image = file_get_contents($url); // download image
        $filename = basename($url); // get filename from url
        $upload_dir = wp_upload_dir(); // get upload directory
        $upload_path = $upload_dir['path'] . '/' . $filename; // create upload path
        file_put_contents($upload_path, $image); // save image to upload path

        $wp_filetype = wp_check_filetype($filename, null); // get file type
        $attachment = array(  // create attachment array
            'post_mime_type' => $wp_filetype['type'], // set mime type
            'post_title' => sanitize_file_name($filename), // set title
            'post_content' => '', // set content
            'post_status' => 'inherit' // set status
        );
        $attach_id = wp_insert_attachment($attachment, $upload_path); // insert attachment
        require_once(ABSPATH . 'wp-admin/includes/image.php'); // include image.php
        $attach_data = wp_generate_attachment_metadata($attach_id, $upload_path); // generate attachment metadata
        wp_update_attachment_metadata($attach_id, $attach_data); // update attachment metadata

        return $attach_id;
    }
 }