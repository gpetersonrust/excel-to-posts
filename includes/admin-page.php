<?php
/**
 * Plugin Name: Excels to Posts
 * Description: Admin page for the Excels to Posts plugin
 * Author: Gino Peterson
 * Author URI: https://moxcar.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: excels-to-posts
 */

if (!defined('ABSPATH')) {
    exit;
}

use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelToPostsAdminPage {
    private $dir;
    private $url;
    private $utils;

    public function __construct($args) {
        $this->dir = $args['dir'];
        $this->url = $args['url'];
        $this->utils = $args['utilsClass'];

        require_once $this->dir . 'vendor/autoload.php';

        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_post_process_excel', [$this, 'process_excel_file']);
    }

    public function add_admin_menu() {
        add_menu_page(
            'Excels to Posts',
            'Excels to Posts',
            'manage_options',
            'excels-to-posts',
            [$this, 'admin_page'],
            'dashicons-media-spreadsheet',
            20
        );
    }

    public function admin_page() {
        ?>
        <h1>Upload Excel File</h1>
        <form action="<?php echo admin_url('admin-post.php'); ?>" method="post" enctype="multipart/form-data">
            <input type="file" name="excel_file" required />
            <input type="hidden" name="action" value="process_excel">
            <?php submit_button('Upload and Convert'); ?>
        </form>
        <?php
    }

    public function process_excel_file() {
        if (empty($_FILES['excel_file']['tmp_name'])) {
            wp_die('No file uploaded.');
        }

        try {
            $spreadsheet = IOFactory::load($_FILES['excel_file']['tmp_name']);
            $rows = $spreadsheet->getActiveSheet()->toArray();
            $header = array_shift($rows);

            foreach ($rows as $row) {
                $post_data = array_combine($header, $row);
                $post_id = wp_insert_post([
                    'post_title'   => $post_data['post_title'],
                    'post_content' => $post_data['post_content'],
                    'post_type'    => $post_data['post_type'],
                    'post_status'  => 'publish'
                ]);


                // Check if post_thumbnail is set

            if (isset($post_data['post_thumbnail'])) { // Check if post_thumbnail is set
                $thumbnail = $post_data['post_thumbnail']; // Get the post_thumbnail value
                if (filter_var($thumbnail, FILTER_VALIDATE_URL)) { // Check if the post_thumbnail is a URL
                    $attachment_id = $this->utils->upload_image_from_url($thumbnail); // Upload the image and get the attachment ID
                    if (!is_wp_error($attachment_id)) { // Check if the image was uploaded successfully
                        set_post_thumbnail($post_id, $attachment_id); // Set the post thumbnail
                    }
                } elseif (is_numeric($thumbnail)) {
                    set_post_thumbnail($post_id, intval($thumbnail)); // Set the post thumbnail
                } // 
            }
                 
                if (!is_wp_error($post_id)) {
                    foreach ($post_data as $key => $value) {
                        if (!in_array($key, ['post_title', 'post_content', 'post_type'])) {
                            update_post_meta($post_id, $key, $value);
                        }
                    }
                }
            }

            wp_redirect(admin_url('admin.php?page=excels-to-posts'));
            exit;
        } catch (Exception $e) {
            wp_die('Error: ' . $e->getMessage());
        }
    }
} ?>