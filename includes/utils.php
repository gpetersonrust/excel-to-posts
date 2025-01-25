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


    public function post_exists($title, $post_type, $meta_key = '', $meta_value = '') {
        $args = array(
            'title' => $title, // title
            'post_type' => $post_type,  // post type
            'post_status' => 'any', // any status
            'meta_query' => array() // meta query
        );

        if (!empty($meta_key) && !empty($meta_value)) {
            $args['meta_query'][] = array(
                'key' => $meta_key, // meta key
                'value' => $meta_value, // meta value
                'compare' => '=' // compare
            );
        }

        $query = new WP_Query($args); // create new WP_Query

        if ($query->have_posts()) {
            return $query->posts[0]->ID; // return post ID
        }

        return false; // return false
    }

    public function handle_post_thumbnail_and_meta($post_id, $post_data) {
        if (isset($post_data['post_thumbnail'])) { // Check if post_thumbnail is set
            $thumbnail = $post_data['post_thumbnail']; // Get the post_thumbnail value
            if (filter_var($thumbnail, FILTER_VALIDATE_URL)) { // Check if the post_thumbnail is a URL
                $attachment_id = $this->utils->upload_image_from_url($thumbnail); // Upload the image and get the attachment ID
                if (!is_wp_error($attachment_id)) { // Check if the image was uploaded successfully
                    set_post_thumbnail($post_id, $attachment_id); // Set the post thumbnail
                }
            } elseif (is_numeric($thumbnail)) {
                set_post_thumbnail($post_id, intval($thumbnail)); // Set the post thumbnail
            }
        }
    
        if (!is_wp_error($post_id)) {
            foreach ($post_data as $key => $value) {
                if (!in_array($key, ['post_title', 'post_content', 'post_type'])) {
                    update_post_meta($post_id, $key, $value);
                }
            }
        }
    }
 }