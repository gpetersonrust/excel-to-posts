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
        if (empty($_FILES['excel_file']['tmp_name'])) wp_die('No file uploaded.');

        try {
            $spreadsheet = IOFactory::load($_FILES['excel_file']['tmp_name']);
            $rows = $spreadsheet->getActiveSheet()->toArray();
            $header = array_shift($rows);

            foreach ($rows as $row) {
                $this->process_row($header, $row);
            }

        wp_redirect(admin_url('admin.php?page=excels-to-posts'));
        exit;
    } catch (Exception $e) {
        wp_die('Error: ' . $e->getMessage());
    }
}


private function process_row($header, $row){
    $post_data = array_combine($header, $row);
    $this->utils->display_data('post_data.json', $post_data);
                 

    $args  = array(
        'post_title' => $post_data['post_title'],
        'post_content' => $post_data['post_content'],
        'post_type' => $post_data['post_type'],
        'post_thumbnail' => $post_data['post_thumbnail'], 
        'post_status' => 'publish'
    );

    $post_id= $this->utils->post_exists($post_data['post_title'], $post_data['post_type']);

    //  if there is a post_id update the post if not create a new post
    $post_id = $post_id ? wp_update_post(array_merge($args, ['ID' => $post_id])) : wp_insert_post($args);
   // Check if post_thumbnail is set

//    get keys from post_data that contain -categories
    $categories = array_filter(array_keys($post_data), function($key) {
        //  Check if the key contains '-categories' get the value of the key
        return strpos($key, '-category') !== false;
    });

    // turn categories to just values

    $this->utils->display_data('DormDorm.json', $categories);

    // Loop through each category key
    foreach ($categories as $category_key) {
        // Get the category name from the post_data
        $category_name = $post_data[$category_key];
        $category_array = explode(',', $category_name); // Split the category names by comma
        foreach ($category_array as $term_name) {
         
                $term_name = trim($term_name); // Trim whitespace
                if(empty($term_name)) continue; // Skip empty terms
            
                 // Check if the term exists
                 $term = term_exists($term_name, $category_key);
                 if ($term !== 0 && $term !== null) {
                      // Term exists, get its ID
                      $term_id = is_array($term) ? $term['term_id'] : $term;

                      $this->utils->display_data('term_id.json', $term_id);
                 } else {
                      // Term does not exist, create it
                      $new_term = wp_insert_term($term_name, $category_key);

                      $this->utils->display_data('new_term.json',  ['term_name' => $term_name, 'category_key' => $category_key]); 
                      if (!is_wp_error($new_term)) {
                            $term_id = $new_term['term_id'];
                      } else {
                            // Handle error if needed
                            continue;
                      }
                 }
              
                 // Assign the term to the post
                 wp_set_post_terms($post_id, [$term_id], $category_key, true);
              
        }
         
    }

$this->utils->handle_post_thumbnail_and_meta($post_id, $post_data);
}

private function set_post_categories(){}


}
?>