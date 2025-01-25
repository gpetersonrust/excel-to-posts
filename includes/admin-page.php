<?php

/**
 * Plugin Name: Excels to Posts
 * Page : Admin Page
 * Description: Admin page for the Excels to Posts plugin
 * Author: Gino Peterosn
 * Author URI: https://moxcar.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: excels-to-posts
 */
// direct access security
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelToPostsAdminPage {
    public function __construct( $args) {
        // Plugin URL, DIR, and Utils class
        $this->dir = $args['dir']; // EXCELS_TO_POSTS_DIR 
        $this->url = $args['url']; // EXCELS_TO_POSTS_URL
        $this->utils = $args['utilsClass']; // ExcelToPostsUtils


        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_post_process_excel', array( $this, 'process_excel_file' ) );
    }

    public function add_admin_menu() {
        add_menu_page(
            'Excels to Posts',
            'Excels to Posts',
            'manage_options',
            'excels-to-posts',
            array( $this, 'admin_page' ),
            'dashicons-media-spreadsheet',
            20
        );
    }

    public function admin_page() {
        ?>
        <h1>Upload Excel File</h1>
        <form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post" enctype="multipart/form-data">
            <input type="file" name="excel_file" required />
            <input type="hidden" name="action" value="process_excel">
            <?php submit_button( 'Upload and Convert' ); ?>
        </form>
        <?php
    }

    public function process_excel_file() {
        if ( isset( $_FILES['excel_file'] ) && ! empty( $_FILES['excel_file']['tmp_name'] ) ) {
            $file = $_FILES['excel_file']['tmp_name'];
            try {
                // Load the Excel file
                $spreadsheet = IOFactory::load( $file );
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();

                // Loop through the rows and create posts
                foreach ( $rows as $row ) {
                    // Example: Assuming row[0] is the title and row[1] is the content
                    $post_data = array(
                        'post_title'   => sanitize_text_field( $row[0] ),
                        'post_content' => sanitize_textarea_field( $row[1] ),
                        'post_status'  => 'publish',
                        'post_type'    => 'post', // or a custom post type
                    );
                    wp_insert_post( $post_data );
                }

                // Redirect after processing
                wp_redirect( admin_url( 'admin.php?page=excels-to-posts' ) );
                exit;
            } catch ( Exception $e ) {
                // Handle any errors here
                echo 'Error: ' . $e->getMessage();
            }
        }
    }
}
?>