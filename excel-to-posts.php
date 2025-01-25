<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://moxcar.com
 * @since             1.0.0
 * @package           Excel_To_Posts
 *
 * @wordpress-plugin
 * Plugin Name:       Excel To Posts
 * Plugin URI:        https://moxcar.com
 * Description:       This is a description of the plugin.
 * Version:           1.0.0
 * Author:            Gino Peterson
 * Author URI:        https://moxcar.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       excel-to-posts
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Define plugin directory and URL
define( 'EXCELS_TO_POSTS_DIR', plugin_dir_path( __FILE__ ) );
define( 'EXCELS_TO_POSTS_URL', plugin_dir_url( __FILE__ ) );

class ExcelToPostsInit {
    // Plugin URL and DIR
    private $url;
    private $dir;
    private $classes_to_run;
    private $utils;

    public function __construct() {
        $this->url = EXCELS_TO_POSTS_URL;
        $this->dir = EXCELS_TO_POSTS_DIR;
        $this->init();
    }

    // Initialize hooks and classes
    public function init() {
        // Register activation, deactivation, and uninstall hooks
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
        register_uninstall_hook( __FILE__, array( 'ExcelToPostsInit', 'uninstall' ) );

        // Include utility functions
        require_once $this->dir . 'includes/utils.php';
        $this->utils = new ExcelToPostsUtils( $this->dir, $this->url );

        // Load other classes
        $this->load_classes();
    }

    // Load classes to run
    public function load_classes() {
        // Define the classes to be loaded
        $this->classes_to_run = [
            [
                'file' => 'shortcodes/admin-page.php',
                'class' => 'ExcelToPostsAdminPage',
                'args' => [
                    'dir' => $this->dir,
                    'url' => $this->url,
                    'utilsClass' => $this->utils
                ]
            ]
        ];

        // Loop through and load classes
        foreach ( $this->classes_to_run as $class ) {
            $file_path = $this->dir . 'includes/' . $class['file'];
            $file_exists = $this->utils->check_file_exists( $file_path );
            if ( ! $file_exists ) continue;

            require_once $file_path;

            $class_exists = $this->utils->check_class_exists( $class['class'] );
            if ( ! $class_exists ) continue;

            $class_name = $class['class'];
            new $class_name( $class['args'] ); // Instantiate the class with arguments
        }
    }

    public function activate() {
        // Activation code here
    }

    public function deactivate() {
        // Deactivation code here
    }

    public static function uninstall() {
        // Uninstall code here
    }
}

// Initialize the plugin
new ExcelToPostsInit();
?>